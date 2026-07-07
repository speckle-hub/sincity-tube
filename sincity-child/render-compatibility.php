<?php
/**
 * SinCity — Render.com Compatibility Layer
 * Place in: sincity-child/render-compatibility.php
 * Include this from functions.php for Render-specific behavior.
 *
 * Handle: environment detection, PostgreSQL, R2 uploads, cron.
 */

// ─── 1. Environment Detection ────────────────────────────────
function sc_is_render() {
    return !empty(getenv('RENDER'));
}

function sc_is_render_free() {
    return sc_is_render() && getenv('RENDER_SERVICE_TYPE') === 'web' && empty(getenv('RENDER_DISK_PATH'));
}

// ─── 2. Adjust for Render's ephemeral filesystem ─────────────
add_action('init', function () {
    if (!sc_is_render()) return;

    // Disable file modifications (already in wp-config, but also here)
    if (!defined('DISALLOW_FILE_MODS')) {
        define('DISALLOW_FILE_MODS', true);
    }

    // Force WP-Cron off — use cron-job.org instead
    if (!defined('DISABLE_WP_CRON')) {
        define('DISABLE_WP_CRON', true);
    }
}, 0);

// ─── 3. PostgreSQL Support (via PG4WP) ────────────────────
// PG4WP must be installed as an mu-plugin.
// We detect it and apply Render DB env vars.

if (sc_is_render() && !empty(getenv('DB_HOST'))) {
    // PG4WP configuration constants
    if (!defined('DB_DRIVER')) {
        define('DB_DRIVER', 'pgsql');
        define('DB_PORT',    getenv('DB_PORT') ?: 5432);
    }
}

// ─── 4. Cloudflare R2 Uploads Proxy ──────────────────────────
// Optional: If R2 env vars are set, rewrite upload URLs to R2.

if (sc_is_render() && getenv('R2_PUBLIC_URL')) {
    add_filter('upload_dir', function ($dirs) {
        // Only redirect uploads to R2 if the bucket is configured
        $r2_url = rtrim(getenv('R2_PUBLIC_URL'), '/');

        $dirs['baseurl']  = $r2_url . '/wp-content/uploads';
        $dirs['url']      = $r2_url . '/wp-content/uploads/' . $dirs['subdir'];
        $dirs['basedir']  = WP_CONTENT_DIR . '/uploads'; // local fallback

        return $dirs;
    });
}

// ─── 5. Render-specific admin notices ────────────────────────
add_action('admin_notices', function () {
    if (!sc_is_render()) return;

    if (sc_is_render_free() && !get_user_meta(get_current_user_id(), 'sc_render_notice_dismissed', true)) {
        ?>
        <div class="notice notice-info is-dismissible" data-sc-render-notice>
            <p><strong>&#9889; SinCity on Render Free Tier</strong></p>
            <p>
                &#8226; Site sleeps after 15 min of inactivity (wakes on first request)<br>
                &#8226; Plugin/theme changes must be deployed via Git (not WP admin)<br>
                &#8226; Uploaded files are ephemeral — use Cloudflare R2 for persistence<br>
                &#8226; Use <a href="https://cron-job.org" target="_blank">cron-job.org</a> for scheduled imports
            </p>
        </div>
        <?php
    }
});

// Dismiss admin notice
add_action('wp_ajax_sc_dismiss_render_notice', function () {
    update_user_meta(get_current_user_id(), 'sc_render_notice_dismissed', true);
    wp_send_json_success();
});

// ─── 6. Override site URL for Render ─────────────────────────
// Required because Render uses ephemeral URLs with ports in dev.

add_filter('option_siteurl', function ($url) {
    if (sc_is_render() && getenv('WP_SITEURL')) {
        return getenv('WP_SITEURL');
    }
    return $url;
});

add_filter('option_home', function ($url) {
    if (sc_is_render() && getenv('WP_HOME')) {
        return getenv('WP_HOME');
    }
    return $url;
});

// ─── 7. Health check endpoint ────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('sincity/v1', '/health', [
        'methods'             => 'GET',
        'callback'            => function () {
            return new WP_REST_Response([
                'status'  => 'ok',
                'time'    => time(),
                'env'     => sc_is_render() ? 'render' : 'local',
                'db'      => wp_using_ext_object_cache() ? 'redis' : 'direct',
            ], 200);
        },
        'permission_callback' => '__return_true',
    ]);
});
