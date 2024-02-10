<?php
/*
 * Plugin Name: CLP Varnish Cache
 * Description: Varnish Cache Plugin by cloudpanel.io
 * Version: 1.0.0
 * Text Domain: clp-varnish-cache
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.1
 * Author: cloudpanel.io
 * Author URI: https://www.cloudpanel.io
 * GitHub Plugin URI: https://github.com/cloudpanel-io/clp-wp-varnish-cache
 * GitHub Branch: master
 */

if (false === function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('CLP_VARNISH_VERSION', '1.0.0');
$is_admin = is_admin();

if (true === $is_admin) {
    define('CLP_VARNISH_PLUGIN_DIR', plugin_dir_path(__FILE__));
    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-manager.php';
    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-admin.php';
    $clp_varnish_cache_admin = new ClpVarnishCacheAdmin();
}

function clear_cache_on_updates($upgrader_object, $options)
{

    // Return if not called because of an update to WordPress core, a plugin, a theme or a bulk update.
    if ('update' !== $options['action'] && 'bulk-update' !== $options['action']) {
        return;
    }

    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-manager.php';
    $clp_varnish_cache_manager = new ClpVarnishCacheManager();

    // Check if Varnish Cache is enabled and should be cleared on updates
    if (true === $clp_varnish_cache_manager->is_enabled() && true === $clp_varnish_cache_manager->should_clear_on_updates()) {

        // Get the host
        $site_url = get_site_url();
        $parsed_url = parse_url($site_url);
        $host = $parsed_url['host'];

        try {
            // Clear Varnish Cache
            $clp_varnish_cache_manager->purge_host($host);
        } catch (\Throwable $th) {
            error_log('Error: ' . $th);
        }

    }
}

add_action('upgrader_process_complete', 'clear_cache_on_updates', 10, 2);

