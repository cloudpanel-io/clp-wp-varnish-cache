<?php 
/*
 * Plugin Name: CLP Varnish Cache
 * Description: Varnish Cache Plugin by cloudpanel.io
 * Version: 1.0.3
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

define('CLP_VARNISH_VERSION', '1.0.3');
$is_admin = is_admin();

// Always define plugin dir so it can be used in all contexts.
define('CLP_VARNISH_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Core classes used in both frontend and admin.
require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-manager.php';
require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-purge.php';

// Instantiate core services.
$clp_varnish_cache_manager = new ClpVarnishCacheManager();
$clp_varnish_cache_purge   = new ClpVarnishCachePurge($clp_varnish_cache_manager);

// Admin-specific functionality (unchanged behaviour).
if (true === $is_admin) {
    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-admin.php';
    $clp_varnish_cache_admin = new ClpVarnishCacheAdmin();
}
