<?php

class ClpVarnishCacheAutoPurge {

    public function __construct() {
        add_action( 'upgrader_process_complete', array( $this, 'auto_purge' ), 10, 2 );
        add_action( 'deactivated_plugin', array( $this, 'auto_purge' ) );
        add_action( 'activated_plugin', array( $this, 'auto_purge' ) );
        add_action( 'switch_theme', array( $this, 'auto_purge' ) );
    }

    public function auto_purge( $upgrader_or_plugin = null, $options = null ) {
        if (current_action() === 'upgrader_process_complete') {
            if (empty($options['action']) || $options['action'] !== 'update' ) return;
            if (empty($options['type']) || ! in_array( $options['type'], ['plugin', 'theme', 'core'], true ) ) return;
        }

        if (!class_exists( 'ClpVarnishCacheManager')) return;
        $manager = new ClpVarnishCacheManager();

        if (!$manager->is_enabled()) return;

        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        if (!empty($host) ) $manager->purge_host($host);

        $prefix = $manager->get_cache_tag_prefix();
        if (!empty($prefix)) $manager->purge_tag($prefix);
    }
}