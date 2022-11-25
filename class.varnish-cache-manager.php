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

    public function purge_host($host): void
    {
        $headers = [
           'Host' => $host
        ];
        $this->purge($headers);
    }

    public function purge_tag($tag): void
    {
        $this->purge_tags([$tag]);
    }

    public function purge_tags(array $tags): void
    {
        $headers = [
            'X-Cache-Tags' => implode(',', $tags)
        ];
        $this->purge($headers);
    }

    public function purge_url( $url): void
    {
        $parsed_url = parse_url($url);
        if (true === isset($parsed_url['host'])) {
            $server = $this->get_server();
            $host = $parsed_url['host'];
            $request_url = $server;
            if (true === isset($parsed_url['path'])) {
                $path = $parsed_url['path'];
                $request_url = sprintf('%s/%s', $request_url, ('/' == $path ? '' : ltrim($path, '/')));
            }
            $query_string = parse_url($url, PHP_URL_QUERY);
            if (false === empty($query_string)) {
                parse_str($query_string, $query_params);
                if (false === empty($query_params)) {
                    $query_string = http_build_query($query_params);
                    $request_url = sprintf('%s?%s', $request_url, $query_string);
                }
            }
            $headers = [
                'Host' => $host
            ];
            $this->purge($headers, $request_url);
        } else {
            throw new \Exception(sprintf('Not a valid url: %s', $url));
        }
    }

    private function purge(array $headers, $request_url = null): void
    {
        try {
            if (true === is_null($request_url)) {
                $request_url = $this->get_server();
            }
            $request_url = sprintf('http://%s', $request_url);
            $response = wp_remote_request(
                $request_url,
                [
                    'sslverify' => false,
                    'method'    => 'PURGE',
                    'headers'   => $headers,
                ]
            );
            $http_status_code = 0;
            if (true === isset($response['response']['code'])) {
                $http_status_code = $response['response']['code'];
            }
            if (200 != $http_status_code) {
                throw new \Exception(sprintf('HTTP Status Code: %s', $http_status_code));
            }
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
            echo esc_html(sprintf('Varnish Cache Purge Failed, Error Message: %s', $error_message));
            exit();
        }
    }
}