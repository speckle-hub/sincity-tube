<?php
/**
 * SinCity — wp-config.php for Render.com (PHP Web Service)
 *
 * Reads ALL configuration from environment variables.
 * No hardcoded secrets. Safe to commit to Git.
 */

// ─── Database ─────────────────────────────────────────────
define('DB_NAME',     getenv('DB_NAME')     ?: 'sincity');
define('DB_USER',     getenv('DB_USER')     ?: 'sincity');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATE',  '');

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
define('DISALLOW_FILE_MODS', true);  // Prevents plugin/theme install via admin
define('FORCE_SSL_ADMIN',    getenv('FORCE_SSL') === 'true');
define('WP_AUTO_UPDATE_CORE', false);

// ─── Cron ─────────────────────────────────────────────────
// Use cron-job.org to ping /wp-cron.php every 15 min
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
