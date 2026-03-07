<?php 
/*
 * Plugin Name: CLP Varnish Cache
 * Description: Varnish Cache Plugin by cloudpanel.io
 * Version: 1.1.0
 * Text Domain: clp-varnish-cache
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.1
 * Author: cloudpanel.io
 * Author URI: https://www.cloudpanel.io
 * GitHub Plugin URI: https://github.com/cloudpanel-io/clp-wp-varnish-cache
 * GitHub Branch: master
 */

if (false ===  function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('CLP_VARNISH_VERSION', '1.1.0');
define('CLP_VARNISH_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Manager is needed for both admin and hook contexts.
require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-manager.php';

$is_admin = is_admin();

if (true === $is_admin) {
    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-admin.php';
    $clp_varnish_cache_admin = new ClpVarnishCacheAdmin();
}

/**
 * Purge the entire host cache after WP core, theme, or plugin updates.
 *
 * @param WP_Upgrader $upgrader Upgrader instance.
 * @param array       $options  Update details.
 */
function clp_varnish_purge_on_update(WP_Upgrader $upgrader, array $options): void {
    if ($options['action'] !== 'update') {
        return;
    }

    $manager = new ClpVarnishCacheManager();
    if ($manager->is_enabled() === false || $manager->should_clear_on_updates() === false) {
        return;
    }

    $site_url = get_site_url();
    $parsed = parse_url($site_url);
    $host = $parsed['host'] ?? '';
    if (false === empty($host)) {
        $manager->purge_host($host);
    }
}
add_action('upgrader_process_complete', 'clp_varnish_purge_on_update', 10, 2);
