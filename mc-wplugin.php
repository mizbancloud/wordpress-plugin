<?php
/**
 * MizbanCloud CDN WordPress Plugin
 *
 * Plugin Name:     MizbanCloud CDN
 * Plugin URI:      https://mizbancloud.com
 * Description:     WordPress Plugin for MizbanCloud CDN Management - Manage your CDN settings directly from WordPress admin panel
 * Author:          MizbanCloud
 * Author URI:      https://mizbancloud.com
 * Version:         1.0.0
 * Text Domain:     mizbancloud-cdn
 * License:         GPLv3 or later
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package         MizbanCloudCDN
 * @author          MizbanCloud
 * @license         GNU General Public License, version 3
 */

// Block direct access
defined('ABSPATH') || exit;

// Plugin constants
define('MC_CDN_VERSION', '1.0.0');
define('MC_CDN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MC_CDN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MC_CDN_API_BASE_URL', 'https://auth.mizbancloud.com/api/v1/cdn/ng');

/**
 * Main Plugin Class
 */
class MizbanCloud_CDN {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Option name for storing settings
     */
    private $option_name = 'mizbancloud_cdn_settings';

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Load dependencies
        $this->load_dependencies();

        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once MC_CDN_PLUGIN_DIR . 'includes/class-mc-api.php';
        require_once MC_CDN_PLUGIN_DIR . 'includes/class-mc-admin.php';
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // AJAX handlers
        add_action('wp_ajax_mc_cdn_get_domains', array($this, 'ajax_get_domains'));
        add_action('wp_ajax_mc_cdn_get_cache_settings', array($this, 'ajax_get_cache_settings'));
        add_action('wp_ajax_mc_cdn_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_mc_cdn_change_cache_mode', array($this, 'ajax_change_cache_mode'));
        add_action('wp_ajax_mc_cdn_change_cache_ttl', array($this, 'ajax_change_cache_ttl'));
        add_action('wp_ajax_mc_cdn_toggle_developer_mode', array($this, 'ajax_toggle_developer_mode'));
        add_action('wp_ajax_mc_cdn_toggle_always_online', array($this, 'ajax_toggle_always_online'));
        add_action('wp_ajax_mc_cdn_change_browser_cache', array($this, 'ajax_change_browser_cache'));
        add_action('wp_ajax_mc_cdn_toggle_image_optimization', array($this, 'ajax_toggle_image_optimization'));
        add_action('wp_ajax_mc_cdn_toggle_image_resize', array($this, 'ajax_toggle_image_resize'));
        add_action('wp_ajax_mc_cdn_save_minify', array($this, 'ajax_save_minify'));
        add_action('wp_ajax_mc_cdn_toggle_cache_cookies', array($this, 'ajax_toggle_cache_cookies'));
        add_action('wp_ajax_mc_cdn_purge_cache_all', array($this, 'ajax_purge_cache_all'));
        add_action('wp_ajax_mc_cdn_purge_cache_paths', array($this, 'ajax_purge_cache_paths'));

        // Plugin activation/deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('MizbanCloud CDN', 'mizbancloud-cdn'),
            __('MizbanCloud CDN', 'mizbancloud-cdn'),
            'manage_options',
            'mizbancloud-cdn',
            array($this, 'render_admin_page'),
            'dashicons-cloud',
            80
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_mizbancloud-cdn') {
            return;
        }

        wp_enqueue_style(
            'mc-cdn-admin',
            MC_CDN_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            MC_CDN_VERSION
        );

        wp_enqueue_script(
            'mc-cdn-admin',
            MC_CDN_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            MC_CDN_VERSION,
            true
        );

        wp_localize_script('mc-cdn-admin', 'mcCdnData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mc_cdn_nonce'),
            'strings' => array(
                'saving' => __('Saving...', 'mizbancloud-cdn'),
                'saved' => __('Settings saved successfully!', 'mizbancloud-cdn'),
                'error' => __('An error occurred. Please try again.', 'mizbancloud-cdn'),
                'loading' => __('Loading...', 'mizbancloud-cdn'),
                'confirm' => __('Are you sure?', 'mizbancloud-cdn'),
            )
        ));
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $admin = new MizbanCloud_Admin();
        $admin->render();
    }

    /**
     * Get plugin options
     */
    public function get_options() {
        return get_option($this->option_name, array(
            'api_token' => '',
            'domain_id' => '',
            'domain_name' => '',
        ));
    }

    /**
     * Update plugin options
     */
    public function update_options($options) {
        return update_option($this->option_name, $options);
    }

    /**
     * Get API instance
     */
    public function get_api() {
        $options = $this->get_options();
        return new MizbanCloud_API($options['api_token']);
    }

    /**
     * AJAX: Get domains list
     */
    public function ajax_get_domains() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $api = $this->get_api();
        $response = $api->get_domains();

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Get cache settings
     */
    public function ajax_get_cache_settings() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $api = $this->get_api();
        $response = $api->get_cache_settings($options['domain_id']);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $api_token = isset($_POST['api_token']) ? sanitize_text_field($_POST['api_token']) : '';
        $domain_id = isset($_POST['domain_id']) ? absint($_POST['domain_id']) : '';
        $domain_name = isset($_POST['domain_name']) ? sanitize_text_field($_POST['domain_name']) : '';

        $options = array(
            'api_token' => $api_token,
            'domain_id' => $domain_id,
            'domain_name' => $domain_name,
        );

        $this->update_options($options);

        wp_send_json_success(array('message' => __('Settings saved successfully!', 'mizbancloud-cdn')));
    }

    /**
     * AJAX: Change cache mode
     */
    public function ajax_change_cache_mode() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : '';
        $allowed_modes = array('WITH_QUERY_STRING', 'NO_QUERY_STRING', 'OFF');

        if (!in_array($mode, $allowed_modes)) {
            wp_send_json_error(array('message' => __('Invalid cache mode.', 'mizbancloud-cdn')));
        }

        $api = $this->get_api();
        $response = $api->set_cache_mode($options['domain_id'], $mode);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Change cache TTL
     */
    public function ajax_change_cache_ttl() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $ttl = isset($_POST['ttl']) ? absint($_POST['ttl']) : 0;

        $api = $this->get_api();
        $response = $api->set_cache_ttl($options['domain_id'], $ttl);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Toggle developer mode
     */
    public function ajax_toggle_developer_mode() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'OFF';

        $api = $this->get_api();
        $response = $api->set_developer_mode($options['domain_id'], $mode);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Toggle always online
     */
    public function ajax_toggle_always_online() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'OFF';

        $api = $this->get_api();
        $response = $api->set_always_online($options['domain_id'], $mode);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Change browser cache settings
     */
    public function ajax_change_browser_cache() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';

        $api = $this->get_api();

        if ($type === 'mode') {
            $response = $api->set_browser_cache_mode($options['domain_id'], $value === 'true');
        } elseif ($type === 'ttl') {
            $response = $api->set_browser_cache_ttl($options['domain_id'], absint($value));
        } else {
            wp_send_json_error(array('message' => __('Invalid request type.', 'mizbancloud-cdn')));
            return;
        }

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Toggle image optimization
     */
    public function ajax_toggle_image_optimization() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'OFF';

        $api = $this->get_api();
        $response = $api->set_image_optimization($options['domain_id'], $mode);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Toggle image resize
     */
    public function ajax_toggle_image_resize() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'OFF';

        $api = $this->get_api();
        $response = $api->set_image_resize($options['domain_id'], $mode);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Save minify settings
     */
    public function ajax_save_minify() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $minify_js = isset($_POST['minify_js']) && $_POST['minify_js'] === 'true';
        $minify_css = isset($_POST['minify_css']) && $_POST['minify_css'] === 'true';

        $api = $this->get_api();
        $response = $api->set_asset_minify($options['domain_id'], $minify_js, $minify_css);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Toggle cache cookies
     */
    public function ajax_toggle_cache_cookies() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $enabled = isset($_POST['enabled']) && $_POST['enabled'] === 'true';

        $api = $this->get_api();
        $response = $api->set_cache_cookies($options['domain_id'], $enabled);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Purge all cache
     */
    public function ajax_purge_cache_all() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $api = $this->get_api();
        $response = $api->purge_cache_all($options['domain_id']);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Purge cache for specific paths
     */
    public function ajax_purge_cache_paths() {
        check_ajax_referer('mc_cdn_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'mizbancloud-cdn')));
        }

        $options = $this->get_options();
        if (empty($options['domain_id'])) {
            wp_send_json_error(array('message' => __('Please select a domain first.', 'mizbancloud-cdn')));
        }

        $paths_input = isset($_POST['paths']) ? sanitize_textarea_field($_POST['paths']) : '';

        if (empty($paths_input)) {
            wp_send_json_error(array('message' => __('Please enter at least one path to purge.', 'mizbancloud-cdn')));
        }

        // Parse paths from textarea (one path per line)
        $paths = array_filter(array_map('trim', explode("\n", $paths_input)));

        if (empty($paths)) {
            wp_send_json_error(array('message' => __('Please enter valid paths to purge.', 'mizbancloud-cdn')));
        }

        $api = $this->get_api();
        $response = $api->purge_cache_paths($options['domain_id'], $paths);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success($response);
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        if (!get_option($this->option_name)) {
            add_option($this->option_name, array(
                'api_token' => '',
                'domain_id' => '',
                'domain_name' => '',
            ));
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }
}

// Initialize plugin
MizbanCloud_CDN::get_instance();
