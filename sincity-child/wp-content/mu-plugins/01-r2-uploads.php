<?php
/**
 * SinCity — Cloudflare R2 Uploads Handler (mu-plugin)
 *
 * Optional. If R2 environment variables are set, uploads
 * are stored on Cloudflare R2 (free tier: 10GB) instead of
 * Render's ephemeral local storage.
 *
 * R2 is S3-compatible. Uses the AWS SDK or direct PUT uploads.
 *
 * Required env vars:
 *   R2_ACCOUNT_ID=<your-account-id>
 *   R2_ACCESS_KEY_ID=<your-access-key>
 *   R2_SECRET_ACCESS_KEY=<your-secret>
 *   R2_BUCKET=sincity-uploads
 *   R2_PUBLIC_URL=https://pub-<hash>.r2.dev
 */

if (!getenv('R2_ACCOUNT_ID') || !getenv('R2_BUCKET')) {
    return; // R2 not configured — use local uploads
}

// ─── Replace upload URL with R2 public URL ────────────
add_filter('upload_dir', function ($dirs) {
    $r2_url = sprintf(
        'https://%s.%s.r2.dev',
        getenv('R2_BUCKET'),
        getenv('R2_ACCOUNT_ID')
    );

    // Override if a custom public URL is set
    if (getenv('R2_PUBLIC_URL')) {
        $r2_url = rtrim(getenv('R2_PUBLIC_URL'), '/');
    }

    $dirs['baseurl'] = $r2_url . '/wp-content/uploads';
    $dirs['url']     = $r2_url . '/wp-content/uploads' . $dirs['subdir'];
    $dirs['basedir'] = WP_CONTENT_DIR . '/uploads'; // Keep local as fallback

    return $dirs;
});

// ─── Intercept upload and PUT to R2 ─────────────────
add_action('wp_handle_upload', function ($data) {
    if (empty($data['file']) || empty($data['url'])) {
        return $data;
    }

    // Upload to R2 in the background
    $local_file  = $data['file'];
    $remote_path = str_replace(WP_CONTENT_DIR . '/uploads', '', $local_file);
    $remote_path = 'wp-content/uploads' . $remote_path;

    sc_r2_put_object($remote_path, $local_file);

    return $data;
});

// ─── R2 S3-compatible PUT function ─────────────────
function sc_r2_put_object($key, $file_path) {
    $account_id   = getenv('R2_ACCOUNT_ID');
    $access_key   = getenv('R2_ACCESS_KEY_ID');
    $secret_key   = getenv('R2_SECRET_ACCESS_KEY');
    $bucket       = getenv('R2_BUCKET');

    if (!$account_id || !$access_key || !$secret_key || !$bucket) {
        return false;
    }

    $endpoint = "https://{$account_id}.r2.cloudflarestorage.com/{$bucket}/{$key}";
    $content  = file_get_contents($file_path);
    if ($content === false) return false;

    $content_type = mime_content_type($file_path) ?: 'application/octet-stream';
    $date         = gmdate('D, d M Y H:i:s T');

    // Simple HMAC-SHA256 signature (S3-compatible V2)
    $signature = base64_encode(
        hash_hmac('sha1', "PUT\n\n{$content_type}\n{$date}\n/{$bucket}/{$key}", $secret_key, true)
    );

    $response = wp_remote_request($endpoint, [
        'method'  => 'PUT',
        'headers' => [
            'Host'           => "{$account_id}.r2.cloudflarestorage.com",
            'Date'           => $date,
            'Content-Type'   => $content_type,
            'Content-Length' => strlen($content),
            'Authorization'  => "AWS {$access_key}:{$signature}",
        ],
        'body'    => $content,
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        error_log('SinCity R2 upload failed: ' . $response->get_error_message());
        return false;
    }

    $status = wp_remote_retrieve_response_code($response);
    if ($status >= 200 && $status < 300) {
        // Success — optionally delete local file
        // unlink($file_path);
        return true;
    }

    error_log("SinCity R2 upload failed with status {$status}");
    return false;
}
