<?php
/**
 * MizbanCloud Admin Page Class
 *
 * @package MizbanCloudCDN
 */

// Block direct access
defined('ABSPATH') || exit;

/**
 * Admin class for MizbanCloud CDN
 */
class MizbanCloud_Admin {

    /**
     * Plugin instance
     */
    private $plugin;

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = MizbanCloud_CDN::get_instance();
    }

    /**
     * Render admin page
     */
    public function render() {
        $options = $this->plugin->get_options();
        ?>
        <div class="wrap mc-cdn-wrap">
            <h1>
                <span class="dashicons dashicons-cloud"></span>
                <?php esc_html_e('MizbanCloud CDN Settings', 'mizbancloud-cdn'); ?>
            </h1>

            <div class="mc-cdn-container">
                <!-- API Settings Section -->
                <div class="mc-cdn-section mc-cdn-api-section">
                    <h2><?php esc_html_e('API Configuration', 'mizbancloud-cdn'); ?></h2>

                    <div class="mc-cdn-field">
                        <label for="mc-api-token"><?php esc_html_e('API Token', 'mizbancloud-cdn'); ?></label>
                        <input type="password"
                               id="mc-api-token"
                               name="api_token"
                               value="<?php echo esc_attr($options['api_token']); ?>"
                               class="regular-text"
                               placeholder="<?php esc_attr_e('Enter your MizbanCloud API token', 'mizbancloud-cdn'); ?>">
                        <p class="description">
                            <?php esc_html_e('You can get your API token from MizbanCloud dashboard.', 'mizbancloud-cdn'); ?>
                        </p>
                        <button type="button" id="mc-toggle-token" class="button button-secondary">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>

                    <div class="mc-cdn-field">
                        <label for="mc-domain-select"><?php esc_html_e('Select Domain', 'mizbancloud-cdn'); ?></label>
                        <select id="mc-domain-select" name="domain_id" class="regular-text">
                            <option value=""><?php esc_html_e('-- Select a domain --', 'mizbancloud-cdn'); ?></option>
                        </select>
                        <button type="button" id="mc-refresh-domains" class="button button-secondary">
                            <span class="dashicons dashicons-update"></span>
                            <?php esc_html_e('Refresh', 'mizbancloud-cdn'); ?>
                        </button>
                        <input type="hidden" id="mc-selected-domain-id" value="<?php echo esc_attr($options['domain_id']); ?>">
                        <input type="hidden" id="mc-selected-domain-name" value="<?php echo esc_attr($options['domain_name']); ?>">
                    </div>

                    <div class="mc-cdn-actions">
                        <button type="button" id="mc-save-settings" class="button button-primary">
                            <span class="dashicons dashicons-yes"></span>
                            <?php esc_html_e('Save Settings', 'mizbancloud-cdn'); ?>
                        </button>
                        <span id="mc-save-status" class="mc-status"></span>
                    </div>
                </div>

                <!-- Cache Settings Section -->
                <div class="mc-cdn-section mc-cdn-cache-section" id="mc-cache-section" style="display: none;">
                    <h2><?php esc_html_e('Cache Settings', 'mizbancloud-cdn'); ?></h2>

                    <div class="mc-cdn-loading" id="mc-cache-loading">
                        <span class="spinner is-active"></span>
                        <?php esc_html_e('Loading cache settings...', 'mizbancloud-cdn'); ?>
                    </div>

                    <div class="mc-cdn-cache-settings" id="mc-cache-settings" style="display: none;">
                        <!-- Cache Mode -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Edge Cache Mode', 'mizbancloud-cdn'); ?></label>
                            <div class="mc-cdn-button-group">
                                <button type="button" class="button mc-cache-mode" data-mode="WITH_QUERY_STRING">
                                    <?php esc_html_e('With Query String', 'mizbancloud-cdn'); ?>
                                </button>
                                <button type="button" class="button mc-cache-mode" data-mode="NO_QUERY_STRING">
                                    <?php esc_html_e('No Query String', 'mizbancloud-cdn'); ?>
                                </button>
                                <button type="button" class="button mc-cache-mode" data-mode="OFF">
                                    <?php esc_html_e('Off', 'mizbancloud-cdn'); ?>
                                </button>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Choose how CDN should handle query strings in URLs for caching.', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>

                        <!-- Cache TTL -->
                        <div class="mc-cdn-field">
                            <label for="mc-cache-ttl"><?php esc_html_e('Edge Cache TTL (seconds)', 'mizbancloud-cdn'); ?></label>
                            <input type="number"
                                   id="mc-cache-ttl"
                                   min="0"
                                   step="1"
                                   class="small-text"
                                   placeholder="14400">
                            <button type="button" id="mc-save-cache-ttl" class="button button-secondary">
                                <?php esc_html_e('Apply', 'mizbancloud-cdn'); ?>
                            </button>
                            <p class="description">
                                <?php esc_html_e('Time in seconds to cache content on edge servers. Default: 14400 (4 hours)', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>

                        <!-- Developer Mode -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Developer Mode', 'mizbancloud-cdn'); ?></label>
                            <div class="mc-cdn-toggle-wrap">
                                <label class="mc-cdn-toggle">
                                    <input type="checkbox" id="mc-developer-mode">
                                    <span class="mc-cdn-toggle-slider"></span>
                                </label>
                                <span class="mc-cdn-toggle-label" id="mc-developer-mode-label"><?php esc_html_e('Off', 'mizbancloud-cdn'); ?></span>
                            </div>
                            <p class="description">
                                <?php esc_html_e('When enabled, bypasses CDN cache. Useful during development.', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>

                        <!-- Always Online -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Always Online', 'mizbancloud-cdn'); ?></label>
                            <div class="mc-cdn-toggle-wrap">
                                <label class="mc-cdn-toggle">
                                    <input type="checkbox" id="mc-always-online">
                                    <span class="mc-cdn-toggle-slider"></span>
                                </label>
                                <span class="mc-cdn-toggle-label" id="mc-always-online-label"><?php esc_html_e('Off', 'mizbancloud-cdn'); ?></span>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Serve cached content when your origin server is down.', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>

                        <!-- Cache Cookies -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Cache Cookies', 'mizbancloud-cdn'); ?></label>
                            <div class="mc-cdn-toggle-wrap">
                                <label class="mc-cdn-toggle">
                                    <input type="checkbox" id="mc-cache-cookies">
                                    <span class="mc-cdn-toggle-slider"></span>
                                </label>
                                <span class="mc-cdn-toggle-label" id="mc-cache-cookies-label"><?php esc_html_e('Off', 'mizbancloud-cdn'); ?></span>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Include cookies in cache key. Enable if your content varies by cookies.', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Purge Cache Section -->
                <div class="mc-cdn-section mc-cdn-purge-section" id="mc-purge-section" style="display: none;">
                    <h2><?php esc_html_e('Purge Cache', 'mizbancloud-cdn'); ?></h2>

                    <div class="mc-cdn-warning">
                        <span class="dashicons dashicons-warning"></span>
                        <p><?php esc_html_e('Purging cache will remove cached content from CDN edge servers. Use with caution.', 'mizbancloud-cdn'); ?></p>
                    </div>

                    <div class="mc-cdn-purge-options">
                        <!-- Purge All Cache -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Purge All Cache', 'mizbancloud-cdn'); ?></label>
                            <p class="description">
                                <?php esc_html_e('Remove all cached content from CDN. This will cause increased load on your origin server temporarily.', 'mizbancloud-cdn'); ?>
                            </p>
                            <button type="button" id="mc-purge-all" class="button button-secondary mc-button-danger">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e('Purge All Cache', 'mizbancloud-cdn'); ?>
                            </button>
                        </div>

                        <!-- Purge Specific Paths -->
                        <div class="mc-cdn-field mc-cdn-purge-paths">
                            <label for="mc-purge-paths"><?php esc_html_e('Purge Specific Paths', 'mizbancloud-cdn'); ?></label>
                            <p class="description">
                                <?php esc_html_e('Enter paths to purge (one per line). Example:', 'mizbancloud-cdn'); ?>
                                <code>/index.html</code>, <code>/assets/app.js</code>, <code>/images/logo.png</code>
                            </p>
                            <textarea id="mc-purge-paths"
                                      rows="5"
                                      class="large-text code"
                                      placeholder="/index.html&#10;/assets/style.css&#10;/images/"></textarea>
                            <button type="button" id="mc-purge-paths-btn" class="button button-secondary">
                                <span class="dashicons dashicons-editor-removeformatting"></span>
                                <?php esc_html_e('Purge Selected Paths', 'mizbancloud-cdn'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Browser Cache Section -->
                <div class="mc-cdn-section mc-cdn-browser-section" id="mc-browser-section" style="display: none;">
                    <h2><?php esc_html_e('Browser Cache Settings', 'mizbancloud-cdn'); ?></h2>

                    <div class="mc-cdn-cache-settings" id="mc-browser-settings">
                        <!-- Browser Cache Mode -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Browser Cache', 'mizbancloud-cdn'); ?></label>
                            <div class="mc-cdn-toggle-wrap">
                                <label class="mc-cdn-toggle">
                                    <input type="checkbox" id="mc-browser-cache-mode">
                                    <span class="mc-cdn-toggle-slider"></span>
                                </label>
                                <span class="mc-cdn-toggle-label" id="mc-browser-cache-mode-label"><?php esc_html_e('Off', 'mizbancloud-cdn'); ?></span>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Enable browser-side caching for static assets.', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>

                        <!-- Browser Cache TTL -->
                        <div class="mc-cdn-field">
                            <label for="mc-browser-cache-ttl"><?php esc_html_e('Browser Cache TTL (seconds)', 'mizbancloud-cdn'); ?></label>
                            <input type="number"
                                   id="mc-browser-cache-ttl"
                                   min="0"
                                   step="1"
                                   class="small-text"
                                   placeholder="14400">
                            <button type="button" id="mc-save-browser-cache-ttl" class="button button-secondary">
                                <?php esc_html_e('Apply', 'mizbancloud-cdn'); ?>
                            </button>
                            <p class="description">
                                <?php esc_html_e('Time in seconds for browser to cache content. Default: 14400 (4 hours)', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Acceleration Section -->
                <div class="mc-cdn-section mc-cdn-acceleration-section" id="mc-acceleration-section" style="display: none;">
                    <h2><?php esc_html_e('Acceleration Settings', 'mizbancloud-cdn'); ?></h2>

                    <div class="mc-cdn-cache-settings" id="mc-acceleration-settings">
                        <!-- Image Optimization -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Image Optimization (WebP)', 'mizbancloud-cdn'); ?></label>
                            <div class="mc-cdn-toggle-wrap">
                                <label class="mc-cdn-toggle">
                                    <input type="checkbox" id="mc-image-optimization">
                                    <span class="mc-cdn-toggle-slider"></span>
                                </label>
                                <span class="mc-cdn-toggle-label" id="mc-image-optimization-label"><?php esc_html_e('Off', 'mizbancloud-cdn'); ?></span>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Automatically convert images to WebP format for better performance.', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>

                        <!-- Image Resize -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Image Resize', 'mizbancloud-cdn'); ?></label>
                            <div class="mc-cdn-toggle-wrap">
                                <label class="mc-cdn-toggle">
                                    <input type="checkbox" id="mc-image-resize">
                                    <span class="mc-cdn-toggle-slider"></span>
                                </label>
                                <span class="mc-cdn-toggle-label" id="mc-image-resize-label"><?php esc_html_e('Off', 'mizbancloud-cdn'); ?></span>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Enable dynamic image resizing at the edge.', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>

                        <!-- Minify JS -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Minify JavaScript', 'mizbancloud-cdn'); ?></label>
                            <div class="mc-cdn-toggle-wrap">
                                <label class="mc-cdn-toggle">
                                    <input type="checkbox" id="mc-minify-js">
                                    <span class="mc-cdn-toggle-slider"></span>
                                </label>
                                <span class="mc-cdn-toggle-label" id="mc-minify-js-label"><?php esc_html_e('Off', 'mizbancloud-cdn'); ?></span>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Automatically minify JavaScript files.', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>

                        <!-- Minify CSS -->
                        <div class="mc-cdn-field">
                            <label><?php esc_html_e('Minify CSS', 'mizbancloud-cdn'); ?></label>
                            <div class="mc-cdn-toggle-wrap">
                                <label class="mc-cdn-toggle">
                                    <input type="checkbox" id="mc-minify-css">
                                    <span class="mc-cdn-toggle-slider"></span>
                                </label>
                                <span class="mc-cdn-toggle-label" id="mc-minify-css-label"><?php esc_html_e('Off', 'mizbancloud-cdn'); ?></span>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Automatically minify CSS files.', 'mizbancloud-cdn'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status Messages -->
                <div id="mc-cdn-notices"></div>
            </div>
        </div>
        <?php
    }
}
