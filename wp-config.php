<?php
/**
 * SinCity — wp-config.php for Render.com (Docker + MySQL)
 *
 * Reads ALL configuration from environment variables.
 * No hardcoded secrets. Safe to commit to Git.
 */

define('WP_HOME', 'https://sincity-web.onrender.com');
define('WP_SITEURL', 'https://sincity-web.onrender.com');
define('FORCE_SSL_ADMIN', true);
define('WP_ALLOW_MULTISITE', false);

// ─── Database (PostgreSQL / Supabase via PG4WP) ─────────────
define('DB_NAME',     getenv('DB_NAME')     ?: 'postgres');
define('DB_USER',     getenv('DB_USER')     ?: 'postgres');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_port = getenv('DB_PORT') ?: '5432';
define('DB_HOST',     $db_host . ':' . $db_port);
define('DB_DRIVER',   'pgsql');
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATE',  '');

// ─── Early connection test (better error messages) ────────
if (defined('WP_INSTALLING') === false && getenv('SKIP_DB_TEST') !== 'true') {
    $conn_string = "host=$db_host port=$db_port dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASSWORD . " sslmode=require";
    $test = @pg_connect($conn_string);
    if (!$test) {
        $err = error_get_last();
        $info = [];
        $info[] = 'DB_HOST: ' . $db_host;
        $info[] = 'DB_PORT: ' . $db_port;
        $info[] = 'DB_NAME: ' . DB_NAME;
        $info[] = 'DB_USER: ' . DB_USER;
        $info[] = 'DB_PASSWORD: ' . (DB_PASSWORD ? '(set)' : '(empty)');
        $info[] = 'Exact Error: ' . ($err ? $err['message'] : 'Unknown error');
        http_response_code(503);
        header('Content-Type: text/plain');
        echo "Error establishing a database connection to Supabase/PostgreSQL.\n\n";
        echo implode("\n", $info);
        exit(1);
    }
    pg_close($test);
}

// ─── Authentication Salts ─────────────────────────────────
define('AUTH_KEY',         getenv('AUTH_KEY')         ?: '');
define('SECURE_AUTH_KEY',  getenv('SECURE_AUTH_KEY')  ?: '');
define('LOGGED_IN_KEY',    getenv('LOGGED_IN_KEY')    ?: '');
define('NONCE_KEY',        getenv('NONCE_KEY')        ?: '');
define('AUTH_SALT',        getenv('AUTH_SALT')        ?: '');
define('SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT') ?: '');
define('LOGGED_IN_SALT',   getenv('LOGGED_IN_SALT')   ?: '');
define('NONCE_SALT',       getenv('NONCE_SALT')       ?: '');

// ─── WordPress URLs ───────────────────────────────────────
$home = getenv('WP_HOME') ?: 'http://localhost';
define('WP_HOME',    $home);
define('WP_SITEURL', getenv('WP_SITEURL') ?: $home);

// ─── Performance ──────────────────────────────────────────
define('WP_CACHE',            false);
define('WP_MEMORY_LIMIT',     '128M');
define('WP_MAX_MEMORY_LIMIT', '256M');

// ─── Security ─────────────────────────────────────────────
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);
define('FORCE_SSL_ADMIN',    getenv('FORCE_SSL') === 'true');
define('WP_AUTO_UPDATE_CORE', false);

// ─── Cron ─────────────────────────────────────────────────
define('DISABLE_WP_CRON', getenv('DISABLE_WP_CRON') === 'true');

// ─── Debug ────────────────────────────────────────────────
define('WP_DEBUG',         getenv('WP_DEBUG') === 'true');
define('WP_DEBUG_LOG',     false);
define('WP_DEBUG_DISPLAY', false);

// ─── Table Prefix ─────────────────────────────────────────
$table_prefix = getenv('WP_TABLE_PREFIX') ?: 'sc_';

// ─── Absolute path ────────────────────────────────────────
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';
