<?php

class ClpVarnishCacheManager {

    private $is_enabled = false;
    private $cache_settings = [];

    public function is_enabled()
    {
        $settings = $this->get_cache_settings();
        $this->is_enabled = (true === isset($settings['enabled']) && true === $settings['enabled'] ? true : false);
        return $this->is_enabled;
    }

    public function get_cache_settings()
    {
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
}