<?php

class ClpVarnishCacheAutoPurge {

    private $purge_entire_cache = false;
    private $purge_post_ids = [];

    public function __construct() {
        add_action('upgrader_process_complete', array($this, 'auto_purge'), 100, 2);
        add_action('deactivated_plugin', array($this, 'auto_purge'), 100);
        add_action('activated_plugin', array($this, 'auto_purge'), 100);
        add_action('switch_theme', array($this, 'auto_purge'), 100);
        add_action('customize_save_after', array($this, 'auto_purge'), 100);
        add_action('wp_update_nav_menu', array($this, 'auto_purge'), 100);
        add_action('wp_update_nav_menu_item', array($this, 'auto_purge'), 100);
        add_action('wp_delete_nav_menu', array($this, 'auto_purge'), 100);
        add_action('updated_option', array($this, 'option_updated'), 100, 3);
        add_action('added_option', array($this, 'option_added'), 100, 2);
        add_action('delete_option', array($this, 'option_deleted'), 100);
        add_action('transition_post_status', array($this, 'post_status_changed'), 100, 3);
        add_action('before_delete_post', array($this, 'before_post_deleted'), 100);
        add_action('wp_insert_comment', array($this, 'comment_saved'), 100);
        add_action('edit_comment', array($this, 'comment_saved'), 100);
        add_action('transition_comment_status', array($this, 'comment_status_changed'), 100, 3);
        add_action('elementor/document/after_save', array($this, 'elementor_document_saved'), 100);
        add_action('elementor/core/files/clear_cache', array($this, 'auto_purge'), 100);
        add_action('shutdown', array($this, 'purge_queued'));
    }

    public function auto_purge($upgrader_or_plugin = null, $options = null) {
        if (current_action() === 'upgrader_process_complete') {
            if (empty($options['action']) || $options['action'] !== 'update') return;
            if (empty($options['type']) || ! in_array( $options['type'], ['plugin', 'theme', 'core'], true)) return;
        }

        $this->purge_entire_cache = true;
    }

    // Widgets and the site title/tagline render on every page.
    public function option_updated($option, $old_value = null, $value = null) {
        if ($option === 'sidebars_widgets' || $option === 'blogname' || $option === 'blogdescription' || strpos($option, 'widget_') === 0) {
            $this->purge_entire_cache = true;
        }
        if (strpos($option, 'theme_mods_') === 0) {
            $old_locations = is_array($old_value) && isset($old_value['nav_menu_locations']) ? $old_value['nav_menu_locations'] : [];
            $locations = is_array($value) && isset($value['nav_menu_locations']) ? $value['nav_menu_locations'] : [];
            if ($old_locations !== $locations) $this->purge_entire_cache = true;
        }
    }

    public function option_added($option, $value) {
        $this->option_updated($option, null, $value);
    }

    public function option_deleted($option) {
        // delete_option runs before removal, while the old menu locations exist.
        if (strpos($option, 'theme_mods_') === 0) {
            $this->option_updated($option, get_option($option), null);
        }
    }

    // Publishing or unpublishing changes listings (home, archives, feeds) that
    // don't carry the post's cache tag yet, so only in-place updates purge by tag.
    public function post_status_changed($new_status, $old_status, $post) {
        if ($new_status !== 'publish' && $old_status !== 'publish') return;
        // Navigation records are not viewable posts, but render across the site.
        if (in_array($post->post_type, ['wp_navigation', 'nav_menu_item'], true)) {
            $this->purge_entire_cache = true;
            return;
        }
        if (!is_post_type_viewable($post->post_type)) return;

        if ($new_status !== $old_status) {
            $this->purge_entire_cache = true;
        } else {
            $this->purge_post_ids[] = $post->ID;
        }
    }

    public function before_post_deleted($post_id) {
        // Permanent deletion bypasses transition_post_status. Capture the old
        // status before removal and invalidate listings as for unpublishing.
        $post = get_post($post_id);
        if ($post) $this->post_status_changed('delete', $post->post_status, $post);
    }

    public function comment_saved($comment_id) {
        $comment = get_comment($comment_id);
        if (!$comment || $comment->comment_approved !== '1') return;

        $this->purge_post_ids[] = (int)$comment->comment_post_ID;
    }

    public function comment_status_changed($new_status, $old_status, $comment) {
        if ($new_status !== 'approved' && $old_status !== 'approved') return;

        $this->purge_post_ids[] = (int)$comment->comment_post_ID;
    }

    public function elementor_document_saved($document) {
        if (method_exists($document, 'is_autosave') && $document->is_autosave()) return;

        $post_id = $document->get_main_id();
        // Templates and the site kit (headers, footers, global styles) render on other pages.
        if (get_post_type($post_id) === 'elementor_library') {
            $this->purge_entire_cache = true;
        } elseif (get_post_status($post_id) === 'publish') {
            $this->purge_post_ids[] = $post_id;
        }
    }

    public function purge_queued() {
        if (!$this->purge_entire_cache && empty($this->purge_post_ids)) return;

        if (!class_exists( 'ClpVarnishCacheManager')) return;
        $manager = new ClpVarnishCacheManager();
        $manager->set_exit_on_error(false);

        if ($manager->is_enabled()) {
            if ($this->purge_entire_cache) {
                $manager->purge_entire_cache();
            } else {
                foreach (array_unique($this->purge_post_ids) as $post_id) $manager->purge_post($post_id);
            }
        }

        $this->purge_entire_cache = false;
        $this->purge_post_ids = [];
    }
}
