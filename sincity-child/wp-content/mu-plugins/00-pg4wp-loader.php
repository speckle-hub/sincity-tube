<?php
/**
 * SinCity — PG4WP Loader (PostgreSQL for WordPress)
 *
 * If Render PostgreSQL is detected, loads the PG4WP drop-in
 * to enable WordPress to run on PostgreSQL instead of MySQL.
 *
 * PG4WP: https://github.com/PostgreSQL-For-Wordpress/postgresql-for-wordpress
 */

// Only activate if DB_DRIVER is explicitly set to pgsql (via Render env)
if (!defined('DB_DRIVER') || DB_DRIVER !== 'pgsql') {
    return;
}

// Define PG4WP constants
if (!defined('PG4WP_ROOT')) {
    define('PG4WP_ROOT', __DIR__ . '/postgresql-for-wordpress');
}

// Load PG4WP if the files exist
$pg4wp_file = PG4WP_ROOT . '/pg4wp.php';
if (file_exists($pg4wp_file)) {
    require_once $pg4wp_file;
} else {
    // Log a warning — PG4WP not bundled
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('SinCity: PG4WP not found at ' . $pg4wp_file . '. Install PostgreSQL For WordPress plugin as mu-plugin.');
    }

    // Fallback: try loading from plugins directory
    $plugin_pg4wp = WP_PLUGIN_DIR . '/postgresql-for-wordpress/pg4wp.php';
    if (file_exists($plugin_pg4wp)) {
        require_once $plugin_pg4wp;
    }
}
