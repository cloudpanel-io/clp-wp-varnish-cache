<?php

/**
 * CLP Varnish Cache purge logic hooked into WordPress events.
 *
 * This class connects WordPress hooks (like save_post) with the
 * ClpVarnishCacheManager so that cache is purged automatically
 * when content changes.
 */
if (false === class_exists('ClpVarnishCachePurge')) {

    class ClpVarnishCachePurge
    {
        /**
         * @var ClpVarnishCacheManager
         */
        private $manager;

        /**
         * Constructor.
         *
         * @param ClpVarnishCacheManager $manager Cache manager instance.
         */
        public function __construct($manager)
        {
            $this->manager = $manager;

            // Register hooks.
            add_action('save_post', array($this, 'auto_purge_on_save'), 20, 3);

            // Show admin notice after redirect, if purge happened.
            add_action('admin_notices', array($this, 'maybe_show_purge_notice'));
        }

        /**
         * Automatically purge the post URL and related sitemaps
         * when public content is saved and published.
         *
         * @param int     $post_id Post ID.
         * @param WP_Post $post    Post object.
         * @param bool    $update  Whether this is an existing post being updated.
         */
        public function auto_purge_on_save($post_id, $post, $update)
        {
            // Ignore autosaves and revisions.
            if (true === wp_is_post_autosave($post_id) || true === wp_is_post_revision($post_id)) {
                return;
            }

            // Only handle public post types (posts, pages, public CPTs).
            $post_type_object = get_post_type_object($post->post_type);
            if (null === $post_type_object || true !== $post_type_object->public) {
                return;
            }

            // Only when the post is published.
            if ('publish' !== $post->post_status) {
                return;
            }

            // Only run when cache is enabled.
            if (true !== $this->manager->is_enabled()) {
                return;
            }

            // Purge the post URL.
            $url = get_permalink($post_id);
            if (empty($url)) {
                return;
            }

            $purged = false;

            try {
                $this->manager->purge_url($url);
                $purged = true;
            } catch (Exception $e) {
                // Note:
                // ClpVarnishCacheManager::purge() currently echoes and exits on error.
                // This catch is mainly for future compatibility if that behaviour changes.
            }

            if (true === $purged) {
                // Purge relevant sitemaps.
                $this->purge_sitemaps();

                // Flag for notice on redirect back to the edit screen.
                add_filter('redirect_post_location', array($this, 'add_purged_query_arg'), 99, 1);
            }
        }

        /**
         * Purge sitemap URLs that are likely to change when content is updated.
         *
         * In a future enhancement, these URLs could be made configurable via plugin settings.
         */
        private function purge_sitemaps()
        {
            $sitemaps = array(
                // Core WordPress sitemap.
                home_url('/wp-sitemap.xml'),

                // Common SEO plugin sitemaps (for example Yoast SEO).
                home_url('/sitemap_index.xml'),
                home_url('/post-sitemap.xml'),
                home_url('/page-sitemap.xml'),
            );

            $sitemaps = array_unique(array_filter($sitemaps));

            foreach ($sitemaps as $sitemap_url) {
                try {
                    $this->manager->purge_url($sitemap_url);
                } catch (Exception $e) {
                    // Same note as above about current purge() behaviour.
                }
            }
        }

        /**
         * Add a query arg to the post redirect URL to indicate purge success.
         *
         * @param string $location Redirect URL.
         * @return string
         */
        public function add_purged_query_arg($location)
        {
            return add_query_arg('clp_vc_purged', 1, $location);
        }

        /**
         * Show a success notice in the admin if a purge happened on the previous request.
         */
        public function maybe_show_purge_notice()
        {
            if (false === is_admin()) {
                return;
            }

            if (!isset($_GET['clp_vc_purged']) || '1' !== sanitize_text_field(wp_unslash($_GET['clp_vc_purged']))) {
                return;
            }

            // Optional: limit to post edit/list screens.
            $screen = function_exists('get_current_screen') ? get_current_screen() : null;
            if ($screen && !in_array($screen->base, array('post', 'edit'), true)) {
                // Only show on post edit or list screens.
                return;
            }
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong><?php esc_html_e('Varnish cache has been purged for this content (including sitemaps).', 'clp-varnish-cache'); ?></strong></p>
            </div>
            <?php
        }
    }
}