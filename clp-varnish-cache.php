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

define('CLP_VARNISH_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-manager.php';

if (true === $is_admin) {
    require_once CLP_VARNISH_PLUGIN_DIR . 'class.varnish-cache-admin.php';
    $clp_varnish_cache_admin = new ClpVarnishCacheAdmin();
}

function clear_cache_on_updates($upgrader_object, $options) {
        
    // Return if not called because of an update to WordPress core, a plugin, a theme or a bulk update.
    if ('update' !== $options['action'] && 'bulk-update' !== $options['action']) {
        return;
    }

    $varnish_cache_manager = new ClpVarnishCacheManager();

    // Check if Varnish Cache is enabled and should be cleared on updates
    if (true === $varnish_cache_manager->is_enabled() && true === $varnish_cache_manager->should_clear_on_updates()) {

        $site_url = get_site_url();
        $parsed_url = parse_url($site_url);
        $host = $parsed_url['host'];
        try {
            $varnish_cache_manager->purge_host($host);
            error_log('Varnish Cache has been purged for host: ' . $host);
        } catch (\Throwable $th) {
            error_log('Error while trying to automatically purge host cache: ' . $th);
        }
    }
}

add_action('upgrader_process_complete', 'clear_cache_on_updates', 10, 2);

function clear_post_page($post_id, $post, $update) {

    error_log('Clear Post Page Function Called');

    // If this is just a revision, don't purge cache.
    if ( wp_is_post_revision( $post_id ) ) {
        error_log('Post is a revision, skipping cache update.');
        return;
    }

    // If this post is not published, don't purge cache.
    if ( 'publish' !== $post->post_status ) {
        error_log('Post is not published, skipping cache update.');
        return;
    }

    // If this post is not a post or page, don't purge cache.
    if ( !in_array( $post->post_type, array( 'post', 'page' ) ) ) {
        error_log('Post is not a post or page, skipping cache update.');
        return;
    }

    $varnish_cache_manager = new ClpVarnishCacheManager();

    // Check if Varnish Cache is enabled and should be cleared on post/page updates
    if (true === $varnish_cache_manager->is_enabled() && true === $varnish_cache_manager->clear_on_post_page_update()) {

        // Get the URL of the post
        $post_url = get_permalink($post_id);

        try {
            $varnish_cache_manager->purge_url($post_url);
            error_log('Varnish Cache has been purged for url: ' . $post_url);
        } catch (\Throwable $th) {
            error_log('Error while trying to automatically purge host cache: ' . $th);
        }
    } else {
        error_log('Varnish Cache is not enabled or should not be cleared on post/page updates.');
    }
}

add_action('save_post', 'clear_post_page', 10, 3);