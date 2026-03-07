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
 * Purge the specific post URL when published content is saved.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated.
 */
function clp_varnish_purge_on_post_save(int $post_id, WP_Post $post, bool $update): void {
    // Ignore autosaves and revisions.
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    // Only purge published, public post types.
    if ($post->post_status !== 'publish') {
        return;
    }
    $post_type_object = get_post_type_object($post->post_type);
    if ($post_type_object === null || $post_type_object->public !== true) {
        return;
    }

    $manager = new ClpVarnishCacheManager();
    if ($manager->is_enabled() === false || $manager->should_clear_on_post_save() === false) {
        return;
    }

    $url = get_permalink($post_id);
    if (false === empty($url)) {
        $manager->purge_url($url);
    }
}
add_action('save_post', 'clp_varnish_purge_on_post_save', 20, 3);

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
