<?php

class ClpVarnishCacheAdmin {

    private $clp_varnish_cache_manager;

    public function __construct() {
        $this->clp_varnish_cache_manager = new ClpVarnishCacheManager();
        $this->init();
    }

    public function init() {
        add_action('admin_bar_menu', array($this, 'add_adminbar'), 100);
        add_action('admin_menu', array($this, 'add_admin_menu'), 100);
        add_action('network_admin_menu', array($this, 'add_admin_menu'), 100);
        add_action('admin_enqueue_scripts', array($this, 'add_css'));
        $this->check_entire_cache_purge();
    }

    private function check_entire_cache_purge() {
        if (true === isset($_GET['clp-varnish-cache']) && 'purge-entire-cache' == sanitize_text_field($_GET['clp-varnish-cache'])) {
            $host = (true === isset($_SERVER['HTTP_HOST']) && false === empty(sanitize_text_field($_SERVER['HTTP_HOST'])) ? sanitize_text_field($_SERVER['HTTP_HOST']) : '');
            if (false === empty($host)) {
                $this->clp_varnish_cache_manager->purge_host($host);
            }
            $cache_tag_prefix = $this->clp_varnish_cache_manager->get_cache_tag_prefix();
            if (false === empty($cache_tag_prefix)) {
                $this->clp_varnish_cache_manager->purge_tag($cache_tag_prefix);
            }
            add_action('admin_notices', array( $this, 'admin_entire_cache_purge'));
        }
    }

    public function admin_entire_cache_purge() {
        echo '<div id="noice" class="notice notice-success fade is-dismissible"><p><strong>' . esc_html__( 'Varnish Cache has been purged.', 'clp-varnish-cache' ) . '</strong></p></div>';
    }

    public function get_clp_cache_manager() {
        return $this->clp_varnish_cache_manager;
    }

    public function add_adminbar($adminbar) {
        $is_admin = is_admin();
        if (true === $is_admin && true === current_user_can( 'edit_published_posts' )) {
            $varnish_cache_settings = $this->clp_varnish_cache_manager->get_cache_settings();
            if (false === empty($varnish_cache_settings)) {
                $menu_title = __( 'CLP Varnish Cache', 'clp-varnish-cache');
                $is_network = is_multisite() && is_network_admin();
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
                        'id'     => 'clp-varnish-cache-purge-entire-cache',
                        'title'  => __( 'Entire Cache', 'clp-varnish-cache' ),
                        'href'   => wp_nonce_url(add_query_arg('clp-varnish-cache', 'purge-entire-cache'), 'purge-entire-cache'),
                        'meta'   => [
                            'title' => __( 'Entire Cache', 'clp-varnish-cache' ),
                        ],
                    ],
                    [
                        'parent' => 'clp-varnish-cache-purge',
                        'id'     => 'clp-varnish-cache-purge-tags-urls',
                        'title'  => __( 'Cache Tags and Urls', 'clp-varnish-cache' ),
                        'href'   => (true === $is_network ? network_admin_url('settings.php?page=clp-varnish-cache') : admin_url('options-general.php?page=clp-varnish-cache')),
                        'meta'   => [
                            'title' => __( 'Cache Tags and Urls', 'clp-varnish-cache' ),
                        ],
                    ],
                    [
                        'parent' => 'clp-varnish-cache',
                        'id'     => 'clp-varnish-cache-enable',
                        'title'  => __( 'Settings', 'clp-varnish-cache' ),
                        'href'   => (true === $is_network ? network_admin_url('settings.php?page=clp-varnish-cache') : admin_url('options-general.php?page=clp-varnish-cache')),
                        'meta'   => [ 'tabindex' => '0' ],
                    ],
                ];
                foreach ($admin_bar_nodes as $node) {
                    $adminbar->add_node($node);
                }
            }
        }
    }

    public function add_admin_menu() {
        $is_network = is_multisite() && is_network_admin();
        if (true === $is_network) {
            add_submenu_page(
                'settings.php',
                __( 'CLP Varnish Cache', 'clp-varnish-cache' ),
                __( 'CLP Varnish Cache', 'clp-varnish-cache' ),
                'manage_options',
                'clp-varnish-cache',
                array( &$this, 'clp_varnish_cache_page' )
            );
        } else {
            add_submenu_page(
                'options-general.php',
                __( 'CLP Varnish Cache', 'clp-varnish-cache' ),
                __( 'CLP Varnish Cache', 'clp-varnish-cache' ),
                'manage_options',
                'clp-varnish-cache',
                array( &$this, 'clp_varnish_cache_page' )
            );
        }
    }

    public function clp_varnish_cache_page() {
        $plugin_dir_path = plugin_dir_path( __FILE__ );
        $varnish_cache_page_path = sprintf('%s/pages/clp-varnish-cache.php', rtrim($plugin_dir_path, '/'));
        include $varnish_cache_page_path;
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
            $admin_colors  = json_decode(wp_json_encode($_wp_admin_css_colors), true);
            $current_color = get_user_option( 'admin_color' );
            $fill          = $admin_colors[$current_color ]['icon_colors']['base'];
        }
        $fill = '#006ad0';
        $svg = '<svg width="100%" viewBox="0 5 20 20" xmlns="http://www.w3.org/2000/svg"><path fill="' . $fill . '" d="m15.676002,13.634a4.959,4.959 0 0 0 -2.363,-1.649l0,-0.06c0,-2.823 -2.208,-5.124 -4.93,-5.124c-2.724,0 -4.933,2.296 -4.933,5.125l0,0.07c-1.994,0.653 -3.45,2.595 -3.45,4.886c0,2.823 2.21,5.125 4.932,5.125a4.832,4.832 0 0 0 3.465,-1.475a4.817,4.817 0 0 0 3.461,1.475c2.717,0 4.933,-2.296 4.933,-5.125c0,-1.18 -0.4,-2.334 -1.115,-3.248zm-3.818,7.077c-2.031,0 -3.685,-1.718 -3.685,-3.83a0.637,0.637 0 0 0 -0.623,-0.646a0.634,0.634 0 0 0 -0.624,0.647c0,0.957 0.257,1.855 0.696,2.622a3.607,3.607 0 0 1 -2.69,1.213c-2.032,0 -3.687,-1.719 -3.687,-3.83c0,-2.11 1.655,-3.829 3.687,-3.829c0.44,0 0.868,0.082 1.278,0.234c0.005,0 0.009,0.005 0.014,0.005c0.142,0.05 0.342,0.147 0.404,0.201a0.6,0.6 0 0 0 0.874,-0.07a0.659,0.659 0 0 0 -0.068,-0.91c-0.272,-0.239 -0.696,-0.402 -0.8,-0.44a4.767,4.767 0 0 0 -1.697,-0.31c-0.079,0 -0.157,0 -0.236,0.005c0.084,-2.04 1.702,-3.671 3.687,-3.671c2.031,0 3.685,1.718 3.685,3.83a3.896,3.896 0 0 1 -1.55,3.122a0.663,0.663 0 0 0 -0.147,0.898c0.12,0.174 0.315,0.272 0.509,0.272c0.125,0 0.25,-0.038 0.361,-0.12a5.164,5.164 0 0 0 1.895,-2.812c1.424,0.549 2.413,1.979 2.413,3.595c-0.005,2.106 -1.659,3.824 -3.696,3.824z"/></svg>';
        if (true === $base64) {
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }
        return $svg;
    }
}