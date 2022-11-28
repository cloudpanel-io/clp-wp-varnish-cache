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

if (false ===  function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('CLP_VARNISH_VERSION', '1.0.0');
$is_admin = is_admin();

if (true === $is_admin) {
    define('CLP_VARNISH_PLUGIN_DIR', plugin_dir_path( __FILE__));
    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-manager.php';
    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-admin.php';
    $clp_varnish_cache_admin = new ClpVarnishCacheAdmin();
}
