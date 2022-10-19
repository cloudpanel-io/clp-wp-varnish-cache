<?php 
/*
 * Plugin Name: CLP Varnish Cache
 * Description: Enable/Disable Varnish Cache, Purge All Pages, Cache Tags, or specific Urls
 * Version: 0.0.3
 * Requires at least: 5.0
 * Requires PHP: 7.1
 * Author: cloudpanel.io
 * Author URI: https://www.cloudpanel.io
 */

if (false ===  function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('CLP_VARNISH_VERSION', '0.0.2');
$is_admin = is_admin();

if (true === $is_admin) {
    define('CLP_VARNISH_PLUGIN_DIR', plugin_dir_path( __FILE__));
    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-manager.php';
    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-admin.php';
    $clp_varnish_cache_admin = new ClpVarnishCacheAdmin();
}
