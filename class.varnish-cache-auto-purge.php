<?php

class ClpVarnishCacheAutoPurge {

    public function __construct() {
        add_action('upgrader_process_complete', [$this, 'auto_purge'], 100, 2);
        add_action('deactivated_plugin', [$this, 'auto_purge'], 100);
        add_action('activated_plugin', [$this, 'auto_purge'], 100);
        add_action('switch_theme', [$this, 'auto_purge'], 100);
    }

    public function auto_purge($upgrader_or_plugin = null, $options = null) {
        if (current_action() === 'upgrader_process_complete') {
            if (empty($options['action']) || $options['action'] !== 'update') return;
            if (empty($options['type']) || !in_array($options['type'], ['plugin', 'theme', 'core'], true)) return;
        }

        if (!class_exists('ClpVarnishCacheManager')) return;
        $manager = new ClpVarnishCacheManager();
        $manager->purge_entire_cache();
    }
}