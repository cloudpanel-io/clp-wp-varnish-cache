<?php

class ClpVarnishCacheManager {

    private $cache_settings = [];

    public function is_enabled() {
        $settings = $this->get_cache_settings();
        $is_enabled = (true === isset($settings['enabled']) && true === $settings['enabled'] ? true : false);
        return $is_enabled;
    }

    public function get_server() {
        $settings = $this->get_cache_settings();
        $server = (true === isset($settings['server']) ? $settings['server'] : '');
        return $server;
    }

    public function get_cache_lifetime() {
        $settings = $this->get_cache_settings();
        $cache_lifetime = (true === isset($settings['cacheLifetime']) ? $settings['cacheLifetime'] : '');
        return $cache_lifetime;
    }

    public function get_cache_tag_prefix() {
        $settings = $this->get_cache_settings();
        $cache_tag_prefix = (true === isset($settings['cacheTagPrefix']) ? $settings['cacheTagPrefix'] : '');
        return $cache_tag_prefix;
    }

    public function get_excluded_params() {
        $settings = $this->get_cache_settings();
        $excluded_params = (true === isset($settings['excludedParams']) ? (array)$settings['excludedParams'] : []);
        $excluded_params = implode(',', $excluded_params);
        return $excluded_params;
    }

    public function get_excludes() {
        $settings = $this->get_cache_settings();
        $excludes = (true === isset($settings['excludes']) ? (array)$settings['excludes'] : []);
        $excludes = implode(PHP_EOL, $excludes);
        return $excludes;
    }

    public function get_cache_settings() {
        if (true === empty($this->cache_settings)) {
            $settings_file = sprintf('%s/.varnish-cache/settings.json', rtrim(getenv('HOME'), '/'));
            if (true === file_exists($settings_file)) {
                $cache_settings = @json_decode(file_get_contents($settings_file), true);
                if (false === empty($cache_settings)) {
                    $this->cache_settings = $cache_settings;
                }
            }
        }
        return $this->cache_settings;
    }

    public function write_cache_settings(array $settings) {
        $settings_file = sprintf('%s/.varnish-cache/settings.json', rtrim(getenv('HOME'), '/'));
        $settings = json_encode($settings, JSON_PRETTY_PRINT);
        file_put_contents($settings_file, $settings);
    }

    public function reset_cache_settings() {
        $this->cache_settings = [];
    }
}