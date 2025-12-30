<?php
/**
 * MizbanCloud API Wrapper Class
 *
 * @package MizbanCloudCDN
 */

// Block direct access
defined('ABSPATH') || exit;

/**
 * API class for MizbanCloud CDN
 */
class MizbanCloud_API {

    /**
     * API base URL
     */
    private $api_base_url;

    /**
     * API token
     */
    private $api_token;

    /**
     * Request timeout
     */
    private $timeout = 30;

    /**
     * Constructor
     *
     * @param string $api_token API bearer token
     */
    public function __construct($api_token = '') {
        $this->api_base_url = MC_CDN_API_BASE_URL;
        $this->api_token = $api_token;
    }

    /**
     * Set API token
     *
     * @param string $token API token
     */
    public function set_token($token) {
        $this->api_token = $token;
    }

    /**
     * Make API request
     *
     * @param string $endpoint API endpoint
     * @param string $method   HTTP method (GET, POST, DELETE, etc.)
     * @param array  $body     Request body
     * @return array|WP_Error Response data or WP_Error on failure
     */
    private function request($endpoint, $method = 'GET', $body = null) {
        if (empty($this->api_token)) {
            return new WP_Error('no_token', __('API token is not configured.', 'mizbancloud-cdn'));
        }

        $url = $this->api_base_url . $endpoint;

        $args = array(
            'method' => $method,
            'timeout' => $this->timeout,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ),
        );

        if ($body !== null && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        // Handle different response codes
        if ($response_code === 401) {
            return new WP_Error('unauthorized', __('Invalid API token. Please check your credentials.', 'mizbancloud-cdn'));
        }

        if ($response_code === 403) {
            return new WP_Error('forbidden', __('Access denied. You do not have permission for this action.', 'mizbancloud-cdn'));
        }

        if ($response_code === 404) {
            return new WP_Error('not_found', __('Resource not found.', 'mizbancloud-cdn'));
        }

        if ($response_code === 422) {
            $message = isset($data['message']) ? $data['message'] : __('Invalid request data.', 'mizbancloud-cdn');
            return new WP_Error('unprocessable', $message);
        }

        if ($response_code >= 400) {
            $message = isset($data['message']) ? $data['message'] : __('An error occurred.', 'mizbancloud-cdn');
            return new WP_Error('api_error', $message);
        }

        return $data;
    }

    /**
     * Get domains list
     *
     * @return array|WP_Error Domains list or error
     */
    public function get_domains() {
        return $this->request('/domains');
    }

    /**
     * Get single domain
     *
     * @param int $domain_id Domain ID
     * @return array|WP_Error Domain data or error
     */
    public function get_domain($domain_id) {
        return $this->request('/domains/' . intval($domain_id));
    }

    /**
     * Get cache settings
     *
     * @param int $domain_id Domain ID
     * @return array|WP_Error Cache settings or error
     */
    public function get_cache_settings($domain_id) {
        return $this->request('/domains/' . intval($domain_id) . '/cache');
    }

    /**
     * Set cache mode
     *
     * @param int    $domain_id Domain ID
     * @param string $mode      Cache mode (WITH_QUERY_STRING, NO_QUERY_STRING, OFF)
     * @return array|WP_Error Response or error
     */
    public function set_cache_mode($domain_id, $mode) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/cache/edge/change-mode',
            'POST',
            array('mode' => $mode)
        );
    }

    /**
     * Set cache TTL
     *
     * @param int $domain_id Domain ID
     * @param int $time      TTL in seconds
     * @return array|WP_Error Response or error
     */
    public function set_cache_ttl($domain_id, $time) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/cache/edge/change-ttl',
            'POST',
            array('time' => intval($time))
        );
    }

    /**
     * Set cache cookies
     *
     * @param int  $domain_id Domain ID
     * @param bool $enabled   Enable/disable cookie caching
     * @return array|WP_Error Response or error
     */
    public function set_cache_cookies($domain_id, $enabled) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/cache/edge/cache-cookies',
            'POST',
            array('mode' => $enabled ? 'ON' : 'OFF')
        );
    }

    /**
     * Set developer mode
     *
     * @param int    $domain_id Domain ID
     * @param string $mode      ON or OFF
     * @return array|WP_Error Response or error
     */
    public function set_developer_mode($domain_id, $mode) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/cache/edge/developer-mode',
            'POST',
            array('mode' => $mode)
        );
    }

    /**
     * Set always online mode
     *
     * @param int    $domain_id Domain ID
     * @param string $mode      ON or OFF
     * @return array|WP_Error Response or error
     */
    public function set_always_online($domain_id, $mode) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/cache/edge/always-online',
            'POST',
            array('mode' => $mode)
        );
    }

    /**
     * Set browser cache mode
     *
     * @param int  $domain_id Domain ID
     * @param bool $enabled   Enable/disable browser caching
     * @return array|WP_Error Response or error
     */
    public function set_browser_cache_mode($domain_id, $enabled) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/cache/browser/change-mode',
            'POST',
            array('mode' => $enabled ? 'ON' : 'OFF')
        );
    }

    /**
     * Set browser cache TTL
     *
     * @param int $domain_id Domain ID
     * @param int $time      TTL in seconds
     * @return array|WP_Error Response or error
     */
    public function set_browser_cache_ttl($domain_id, $time) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/cache/browser/change-ttl',
            'POST',
            array('time' => intval($time))
        );
    }

    /**
     * Set image optimization
     *
     * @param int    $domain_id Domain ID
     * @param string $mode      ON or OFF
     * @return array|WP_Error Response or error
     */
    public function set_image_optimization($domain_id, $mode) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/acceleration/images/optimize',
            'POST',
            array('mode' => $mode)
        );
    }

    /**
     * Set image resize
     *
     * @param int    $domain_id Domain ID
     * @param string $mode      ON or OFF
     * @return array|WP_Error Response or error
     */
    public function set_image_resize($domain_id, $mode) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/acceleration/images/resize',
            'POST',
            array('mode' => $mode)
        );
    }

    /**
     * Set asset minification
     *
     * @param int    $domain_id  Domain ID
     * @param bool   $minify_js  Minify JavaScript
     * @param bool   $minify_css Minify CSS
     * @return array|WP_Error Response or error
     */
    public function set_asset_minify($domain_id, $minify_js, $minify_css) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/acceleration/assets/minify',
            'POST',
            array(
                'minify_js' => $minify_js,
                'minify_css' => $minify_css,
            )
        );
    }

    /**
     * Purge all cache
     *
     * @param int $domain_id Domain ID
     * @return array|WP_Error Response or error
     */
    public function purge_cache_all($domain_id) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/cache/edge/purge-cache',
            'POST'
        );
    }

    /**
     * Purge cache for specific paths
     *
     * @param int   $domain_id Domain ID
     * @param array $paths     Array of paths to purge (e.g., ['/index.html', '/assets/app.js'])
     * @return array|WP_Error Response or error
     */
    public function purge_cache_paths($domain_id, $paths) {
        return $this->request(
            '/domains/' . intval($domain_id) . '/cache/edge/purge-cache',
            'POST',
            array('paths' => $paths)
        );
    }

    /**
     * Test API connection
     *
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function test_connection() {
        $response = $this->get_domains();

        if (is_wp_error($response)) {
            return $response;
        }

        return true;
    }
}
