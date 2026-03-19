<?php

class ClpVarnishCacheManager {

    private $cache_settings = [];

    public function is_enabled() {
        return !empty($this->get_cache_settings()['enabled']);
    }

    public function get_server() {
        return $this->get_cache_settings()['server'] ?? '';
    }

    public function get_cache_lifetime() {
        return $this->get_cache_settings()['cacheLifetime'] ?? '';
    }

    public function get_cache_tag_prefix() {
        return $this->get_cache_settings()['cacheTagPrefix'] ?? '';
    }

    public function get_excluded_params() {
        $excluded_params = $this->get_cache_settings()['excludedParams'] ?? [];
        return implode(',', (array)$excluded_params);
    }

    public function get_excludes() {
        $excludes = $this->get_cache_settings()['excludes'] ?? [];
        return implode(PHP_EOL, (array)$excludes);
    }

    public function get_cache_settings() {
        if (!empty($this->cache_settings)) return $this->cache_settings;

        $settings_file = sprintf('%s/.varnish-cache/settings.json', rtrim(getenv('HOME'), '/'));
        if (!file_exists($settings_file) || !is_readable($settings_file)) return $this->cache_settings;

        $file_content = file_get_contents($settings_file);
        if ($file_content === false) return $this->cache_settings;

        $cache_settings = json_decode($file_content, true);
        if (json_last_error() === JSON_ERROR_NONE && !empty($cache_settings)) $this->cache_settings = $cache_settings;

        return $this->cache_settings;
    }

    public function purge_entire_cache(): bool {
        if (!$this->is_enabled()) return false;

        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        if (!empty($host)) $this->purge_host($host);

        $prefix = $this->get_cache_tag_prefix();
        if (!empty($prefix)) $this->purge_tag($prefix);

        return true;
    }

    public function write_cache_settings(array $settings) {
        $settings_file = sprintf('%s/.varnish-cache/settings.json', rtrim(getenv('HOME'), '/'));
        file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT));
    }

    public function reset_cache_settings() {
        $this->cache_settings = [];
    }

    public function purge_host($host): void {
        $this->purge(['Host' => $host]);
    }

    public function purge_tag($tag): void {
        $this->purge_tags([$tag]);
    }

    public function purge_tags(array $tags): void {
        $this->purge(['X-Cache-Tags' => implode(',', $tags)]);
    }

    public function purge_url($url): void {
        $parsed_url = parse_url($url);
        if (!isset($parsed_url['host'])) throw new \Exception(sprintf('Not a valid url: %s', $url));

        $server = $this->get_server();
        $host = $parsed_url['host'];
        $request_url = $server;

        if (isset($parsed_url['path'])) {
            $path = $parsed_url['path'];
            $request_url = sprintf('%s/%s', $request_url, ($path === '/' ? '' : ltrim($path, '/')));
        }

        $query_string = parse_url($url, PHP_URL_QUERY);
        if (!empty($query_string)) {
            parse_str($query_string, $query_params);
            if (!empty($query_params)) {
                $request_url = sprintf('%s?%s', $request_url, http_build_query($query_params));
            }
        }

        $this->purge(['Host' => $host], $request_url);
    }

    private function purge(array $headers, $request_url = null): void {
        try {
            if ($request_url === null) $request_url = $this->get_server();

            $response = wp_remote_request(
                sprintf('http://%s', $request_url),
                [
                    'sslverify' => false,
                    'method'    => 'PURGE',
                    'headers'   => $headers,
                ]
            );

            $http_status_code = $response['response']['code'] ?? 0;
            if ($http_status_code != 200) throw new \Exception(sprintf('HTTP Status Code: %s', $http_status_code));
        } catch (\Exception $e) {
            echo esc_html(sprintf('Varnish Cache Purge Failed, Error Message: %s', $e->getMessage()));
            exit();
        }
    }
}