<?php

class ClpVarnishCacheAdmin {

    private $clp_varnish_cache_manager;

    public function __construct() {
        $this->clp_varnish_cache_manager = new ClpVarnishCacheManager();
        $this->init();
    }

    public function init() {
        add_action('admin_bar_menu', array($this, 'add_adminbar'), 100);
        add_action('admin_enqueue_scripts', array($this, 'add_css'));
    }

    public function add_adminbar($adminbar) {
        $is_admin = is_admin();
        if (true === $is_admin && true === current_user_can( 'edit_published_posts' )) {
            $varnish_cache_enabled = $this->clp_varnish_cache_manager->is_enabled();
            $menu_title = sprintf( __( 'CLP Varnish Cache (%s)', 'clp-varnish-cache' ), (true === $varnish_cache_enabled ? 'Enabled' : 'Disabled'));
            $admin_bar_nodes = [
                [
                    'id'    => 'clp-varnish-cache',
                    'title' => '<span class="ab-icon" style="background-image: url(' . self::get_svg_icon() . ') !important;"></span><span class="ab-label">' . $menu_title . '</span>',
                    'meta'  => [
                        'class' => 'clp-varnish-cache',
                    ],
                ],
                [
                    'parent' => 'clp-varnish-cache',
                    'id'     => 'clp-varnish-cache-purge',
                    'title'  => __( 'Purge', 'clp-varnish-cache' ),
                    'meta'   => [ 'tabindex' => '0' ],
                ],
                [
                    'parent' => 'clp-varnish-cache-purge',
                    'id'     => 'clp-varnish-cache-purge-all',
                    'title'  => __( 'All Pages', 'clp-varnish-cache' ),
                    'href'   => wp_nonce_url(add_query_arg( 'vhp_flush_do', 'all' ), 'vhp-flush-do'),
                    'meta'   => [
                        'title' => __( 'All Pages', 'clp-varnish-cache' ),
                    ],
                ],
                [
                    'parent' => 'clp-varnish-cache-purge',
                    'id'     => 'clp-varnish-cache-purge-tags-urls',
                    'title'  => __( 'Cache Tags and Urls', 'clp-varnish-cache' ),
                    'href'   => wp_nonce_url(add_query_arg( 'vhp_flush_do', 'all' ), 'vhp-flush-do'),
                    'meta'   => [
                        'title' => __( 'Cache Tags and Urls', 'clp-varnish-cache' ),
                    ],
                ],
                [
                    'parent' => 'clp-varnish-cache',
                    'id'     => 'clp-varnish-cache-settings',
                    'title'  => __( 'Settings', 'clp-varnish-cache' ),
                    'meta'   => [ 'tabindex' => '0' ],
                ],
            ];
            foreach ($admin_bar_nodes as $node) {
                $adminbar->add_node($node);
            }
        }
    }

    public function add_css() {
        if (true === is_user_logged_in() && true === is_admin_bar_showing() ) {
            wp_register_style('clp-varnish-cache', plugins_url('style.css', __FILE__), false, CLP_VARNISH_VERSION);
            wp_enqueue_style('clp-varnish-cache');
        }
    }

    public static function get_svg_icon($base64 = true, $icon_color = false) {
        global $_wp_admin_css_colors;

        $fill = ( false !== $icon_color ) ? sanitize_hex_color( $icon_color ) : '#82878c';
        if (true === is_admin() && false === $icon_color && get_user_option('admin_color') ) {
            $admin_colors  = json_decode( wp_json_encode($_wp_admin_css_colors), true);
            $current_color = get_user_option( 'admin_color' );
            $fill          = $admin_colors[ $current_color ]['icon_colors']['base'];
        }
        $svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="100%" height="100%" style="fill:' . $fill . '" viewBox="0 0 36.2 34.39" role="img" aria-hidden="true" focusable="false"><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path fill="' . $fill . '" d="M24.41,0H4L0,18.39H12.16v2a2,2,0,0,0,4.08,0v-2H24.1a8.8,8.8,0,0,1,4.09-1Z"/><path fill="' . $fill . '" d="M21.5,20.4H18.24a4,4,0,0,1-8.08,0v0H.2v8.68H19.61a9.15,9.15,0,0,1-.41-2.68A9,9,0,0,1,21.5,20.4Z"/><path fill="' . $fill . '" d="M28.7,33.85a7,7,0,1,1,7-7A7,7,0,0,1,28.7,33.85Zm-1.61-5.36h5V25.28H30.31v-3H27.09Z"/><path fill="' . $fill . '" d="M28.7,20.46a6.43,6.43,0,1,1-6.43,6.43,6.43,6.43,0,0,1,6.43-6.43M26.56,29h6.09V24.74H30.84V21.8H26.56V29m2.14-9.64a7.5,7.5,0,1,0,7.5,7.5,7.51,7.51,0,0,0-7.5-7.5ZM27.63,28V22.87h2.14v2.95h1.81V28Z"/></g></g></svg>';
        if (true === $base64) {
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }
        return $svg;
    }
}