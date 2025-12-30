/**
 * MizbanCloud CDN Admin JavaScript
 *
 * @package MizbanCloudCDN
 */

(function($) {
    'use strict';

    // Cache DOM elements
    var elements = {
        apiToken: $('#mc-api-token'),
        domainSelect: $('#mc-domain-select'),
        refreshDomains: $('#mc-refresh-domains'),
        saveSettings: $('#mc-save-settings'),
        saveStatus: $('#mc-save-status'),
        toggleToken: $('#mc-toggle-token'),
        selectedDomainId: $('#mc-selected-domain-id'),
        selectedDomainName: $('#mc-selected-domain-name'),
        cacheSection: $('#mc-cache-section'),
        purgeSection: $('#mc-purge-section'),
        browserSection: $('#mc-browser-section'),
        accelerationSection: $('#mc-acceleration-section'),
        cacheLoading: $('#mc-cache-loading'),
        cacheSettings: $('#mc-cache-settings'),
        notices: $('#mc-cdn-notices')
    };

    // Current cache settings
    var currentSettings = {};

    /**
     * Initialize
     */
    function init() {
        bindEvents();
        loadSavedDomain();
    }

    /**
     * Bind events
     */
    function bindEvents() {
        // Toggle API token visibility
        elements.toggleToken.on('click', toggleTokenVisibility);

        // Refresh domains
        elements.refreshDomains.on('click', loadDomains);

        // Domain selection change
        elements.domainSelect.on('change', onDomainChange);

        // Save settings
        elements.saveSettings.on('click', saveSettings);

        // Cache mode buttons
        $(document).on('click', '.mc-cache-mode', function() {
            changeCacheMode($(this).data('mode'));
        });

        // Cache TTL
        $('#mc-save-cache-ttl').on('click', function() {
            changeCacheTTL($('#mc-cache-ttl').val());
        });

        // Developer mode toggle
        $('#mc-developer-mode').on('change', function() {
            toggleDeveloperMode($(this).is(':checked'));
        });

        // Always online toggle
        $('#mc-always-online').on('change', function() {
            toggleAlwaysOnline($(this).is(':checked'));
        });

        // Cache cookies toggle
        $('#mc-cache-cookies').on('change', function() {
            toggleCacheCookies($(this).is(':checked'));
        });

        // Browser cache mode toggle
        $('#mc-browser-cache-mode').on('change', function() {
            changeBrowserCacheMode($(this).is(':checked'));
        });

        // Browser cache TTL
        $('#mc-save-browser-cache-ttl').on('click', function() {
            changeBrowserCacheTTL($('#mc-browser-cache-ttl').val());
        });

        // Image optimization toggle
        $('#mc-image-optimization').on('change', function() {
            toggleImageOptimization($(this).is(':checked'));
        });

        // Image resize toggle
        $('#mc-image-resize').on('change', function() {
            toggleImageResize($(this).is(':checked'));
        });

        // Minify toggles
        $('#mc-minify-js, #mc-minify-css').on('change', function() {
            saveMinifySettings();
        });

        // Purge all cache
        $('#mc-purge-all').on('click', function() {
            purgeAllCache();
        });

        // Purge specific paths
        $('#mc-purge-paths-btn').on('click', function() {
            purgeSpecificPaths();
        });
    }

    /**
     * Toggle API token visibility
     */
    function toggleTokenVisibility() {
        var input = elements.apiToken;
        var icon = elements.toggleToken.find('.dashicons');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            input.attr('type', 'password');
            icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    }

    /**
     * Load saved domain
     */
    function loadSavedDomain() {
        var savedDomainId = elements.selectedDomainId.val();
        var savedDomainName = elements.selectedDomainName.val();

        if (savedDomainId && savedDomainName) {
            elements.domainSelect.append(
                $('<option>', {
                    value: savedDomainId,
                    text: savedDomainName,
                    selected: true
                })
            );
            showCacheSections();
            loadCacheSettings();
        }

        // Auto-load domains if token exists
        if (elements.apiToken.val()) {
            loadDomains();
        }
    }

    /**
     * Load domains from API
     */
    function loadDomains() {
        var token = elements.apiToken.val();

        if (!token) {
            showNotice('error', mcCdnData.strings.error + ' Please enter your API token first.');
            return;
        }

        elements.refreshDomains.prop('disabled', true).find('.dashicons').addClass('mc-spin');

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_get_domains',
                nonce: mcCdnData.nonce
            },
            success: function(response) {
                if (response.success && response.data && response.data.data) {
                    populateDomains(response.data.data);
                } else {
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                elements.refreshDomains.prop('disabled', false).find('.dashicons').removeClass('mc-spin');
            }
        });
    }

    /**
     * Populate domains dropdown
     */
    function populateDomains(domains) {
        var selectedId = elements.selectedDomainId.val();

        elements.domainSelect.empty().append(
            $('<option>', {
                value: '',
                text: '-- Select a domain --'
            })
        );

        $.each(domains, function(i, domain) {
            var option = $('<option>', {
                value: domain.id,
                text: domain.name + ' (' + domain.status + ')',
                'data-name': domain.name
            });

            if (domain.id == selectedId) {
                option.prop('selected', true);
            }

            elements.domainSelect.append(option);
        });

        if (selectedId) {
            elements.domainSelect.val(selectedId);
        }
    }

    /**
     * Handle domain change
     */
    function onDomainChange() {
        var selected = elements.domainSelect.find(':selected');
        var domainId = selected.val();
        var domainName = selected.data('name') || '';

        elements.selectedDomainId.val(domainId);
        elements.selectedDomainName.val(domainName);

        if (domainId) {
            showCacheSections();
            loadCacheSettings();
        } else {
            hideCacheSections();
        }
    }

    /**
     * Show cache sections
     */
    function showCacheSections() {
        elements.cacheSection.slideDown();
        elements.purgeSection.slideDown();
        elements.browserSection.slideDown();
        elements.accelerationSection.slideDown();
    }

    /**
     * Hide cache sections
     */
    function hideCacheSections() {
        elements.cacheSection.slideUp();
        elements.purgeSection.slideUp();
        elements.browserSection.slideUp();
        elements.accelerationSection.slideUp();
    }

    /**
     * Save settings
     */
    function saveSettings() {
        var token = elements.apiToken.val();
        var domainId = elements.domainSelect.val();
        var domainName = elements.domainSelect.find(':selected').data('name') || '';

        elements.saveSettings.prop('disabled', true);
        elements.saveStatus.text(mcCdnData.strings.saving).removeClass('success error');

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_save_settings',
                nonce: mcCdnData.nonce,
                api_token: token,
                domain_id: domainId,
                domain_name: domainName
            },
            success: function(response) {
                if (response.success) {
                    elements.saveStatus.text(mcCdnData.strings.saved).addClass('success');
                    showNotice('success', mcCdnData.strings.saved);

                    // Update hidden fields
                    elements.selectedDomainId.val(domainId);
                    elements.selectedDomainName.val(domainName);
                } else {
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    elements.saveStatus.text(msg).addClass('error');
                    showNotice('error', msg);
                }
            },
            error: function() {
                elements.saveStatus.text(mcCdnData.strings.error).addClass('error');
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                elements.saveSettings.prop('disabled', false);
                setTimeout(function() {
                    elements.saveStatus.text('');
                }, 3000);
            }
        });
    }

    /**
     * Load cache settings
     */
    function loadCacheSettings() {
        elements.cacheLoading.show();
        elements.cacheSettings.hide();

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_get_cache_settings',
                nonce: mcCdnData.nonce
            },
            success: function(response) {
                if (response.success && response.data && response.data.data) {
                    currentSettings = response.data.data;
                    updateCacheUI(currentSettings);
                    elements.cacheSettings.show();
                } else {
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                elements.cacheLoading.hide();
            }
        });
    }

    /**
     * Update cache UI with settings
     */
    function updateCacheUI(settings) {
        // Cache mode
        $('.mc-cache-mode').removeClass('button-primary');
        $('.mc-cache-mode[data-mode="' + settings.cache_mode + '"]').addClass('button-primary');

        // Cache TTL
        $('#mc-cache-ttl').val(settings.cache_ttl || 14400);

        // Developer mode
        var devMode = settings.developer_mode === true || settings.developer_mode === 1;
        $('#mc-developer-mode').prop('checked', devMode);
        $('#mc-developer-mode-label').text(devMode ? 'On' : 'Off');

        // Always online
        var alwaysOnline = settings.always_online === true || settings.always_online === 1;
        $('#mc-always-online').prop('checked', alwaysOnline);
        $('#mc-always-online-label').text(alwaysOnline ? 'On' : 'Off');

        // Cache cookies
        var cacheCookies = settings.cache_cookies === true || settings.cache_cookies === 1;
        $('#mc-cache-cookies').prop('checked', cacheCookies);
        $('#mc-cache-cookies-label').text(cacheCookies ? 'On' : 'Off');

        // Browser cache mode
        var browserCacheMode = settings.browser_cache_mode === true || settings.browser_cache_mode === 1;
        $('#mc-browser-cache-mode').prop('checked', browserCacheMode);
        $('#mc-browser-cache-mode-label').text(browserCacheMode ? 'On' : 'Off');

        // Browser cache TTL
        $('#mc-browser-cache-ttl').val(settings.browser_cache_ttl || 14400);

        // Image optimization
        var imageOptimization = settings.image_to_webp === true || settings.image_to_webp === 1;
        $('#mc-image-optimization').prop('checked', imageOptimization);
        $('#mc-image-optimization-label').text(imageOptimization ? 'On' : 'Off');

        // Image resize
        var imageResize = settings.image_resize === true || settings.image_resize === 1;
        $('#mc-image-resize').prop('checked', imageResize);
        $('#mc-image-resize-label').text(imageResize ? 'On' : 'Off');

        // Minify JS
        var minifyJs = settings.minify_js === true || settings.minify_js === 1;
        $('#mc-minify-js').prop('checked', minifyJs);
        $('#mc-minify-js-label').text(minifyJs ? 'On' : 'Off');

        // Minify CSS
        var minifyCss = settings.minify_css === true || settings.minify_css === 1;
        $('#mc-minify-css').prop('checked', minifyCss);
        $('#mc-minify-css-label').text(minifyCss ? 'On' : 'Off');
    }

    /**
     * Change cache mode
     */
    function changeCacheMode(mode) {
        $('.mc-cache-mode').prop('disabled', true);

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_change_cache_mode',
                nonce: mcCdnData.nonce,
                mode: mode
            },
            success: function(response) {
                if (response.success) {
                    $('.mc-cache-mode').removeClass('button-primary');
                    $('.mc-cache-mode[data-mode="' + mode + '"]').addClass('button-primary');
                    showNotice('success', 'Cache mode updated successfully!');
                } else {
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('.mc-cache-mode').prop('disabled', false);
            }
        });
    }

    /**
     * Change cache TTL
     */
    function changeCacheTTL(ttl) {
        $('#mc-save-cache-ttl').prop('disabled', true);

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_change_cache_ttl',
                nonce: mcCdnData.nonce,
                ttl: ttl
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Cache TTL updated successfully!');
                } else {
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('#mc-save-cache-ttl').prop('disabled', false);
            }
        });
    }

    /**
     * Toggle developer mode
     */
    function toggleDeveloperMode(enabled) {
        var mode = enabled ? 'ON' : 'OFF';
        $('#mc-developer-mode').prop('disabled', true);

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_toggle_developer_mode',
                nonce: mcCdnData.nonce,
                mode: mode
            },
            success: function(response) {
                if (response.success) {
                    $('#mc-developer-mode-label').text(enabled ? 'On' : 'Off');
                    showNotice('success', 'Developer mode ' + (enabled ? 'enabled' : 'disabled') + '!');
                } else {
                    // Revert checkbox
                    $('#mc-developer-mode').prop('checked', !enabled);
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                $('#mc-developer-mode').prop('checked', !enabled);
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('#mc-developer-mode').prop('disabled', false);
            }
        });
    }

    /**
     * Toggle always online
     */
    function toggleAlwaysOnline(enabled) {
        var mode = enabled ? 'ON' : 'OFF';
        $('#mc-always-online').prop('disabled', true);

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_toggle_always_online',
                nonce: mcCdnData.nonce,
                mode: mode
            },
            success: function(response) {
                if (response.success) {
                    $('#mc-always-online-label').text(enabled ? 'On' : 'Off');
                    showNotice('success', 'Always online ' + (enabled ? 'enabled' : 'disabled') + '!');
                } else {
                    $('#mc-always-online').prop('checked', !enabled);
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                $('#mc-always-online').prop('checked', !enabled);
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('#mc-always-online').prop('disabled', false);
            }
        });
    }

    /**
     * Toggle cache cookies
     */
    function toggleCacheCookies(enabled) {
        $('#mc-cache-cookies').prop('disabled', true);

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_change_browser_cache',
                nonce: mcCdnData.nonce,
                type: 'cookies',
                value: enabled ? 'true' : 'false'
            },
            success: function(response) {
                if (response.success) {
                    $('#mc-cache-cookies-label').text(enabled ? 'On' : 'Off');
                    showNotice('success', 'Cache cookies ' + (enabled ? 'enabled' : 'disabled') + '!');
                } else {
                    $('#mc-cache-cookies').prop('checked', !enabled);
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                $('#mc-cache-cookies').prop('checked', !enabled);
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('#mc-cache-cookies').prop('disabled', false);
            }
        });
    }

    /**
     * Change browser cache mode
     */
    function changeBrowserCacheMode(enabled) {
        $('#mc-browser-cache-mode').prop('disabled', true);

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_change_browser_cache',
                nonce: mcCdnData.nonce,
                type: 'mode',
                value: enabled ? 'true' : 'false'
            },
            success: function(response) {
                if (response.success) {
                    $('#mc-browser-cache-mode-label').text(enabled ? 'On' : 'Off');
                    showNotice('success', 'Browser cache ' + (enabled ? 'enabled' : 'disabled') + '!');
                } else {
                    $('#mc-browser-cache-mode').prop('checked', !enabled);
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                $('#mc-browser-cache-mode').prop('checked', !enabled);
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('#mc-browser-cache-mode').prop('disabled', false);
            }
        });
    }

    /**
     * Change browser cache TTL
     */
    function changeBrowserCacheTTL(ttl) {
        $('#mc-save-browser-cache-ttl').prop('disabled', true);

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_change_browser_cache',
                nonce: mcCdnData.nonce,
                type: 'ttl',
                value: ttl
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Browser cache TTL updated successfully!');
                } else {
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('#mc-save-browser-cache-ttl').prop('disabled', false);
            }
        });
    }

    /**
     * Toggle image optimization
     */
    function toggleImageOptimization(enabled) {
        $('#mc-image-optimization').prop('disabled', true);
        var mode = enabled ? 'ON' : 'OFF';

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_toggle_image_optimization',
                nonce: mcCdnData.nonce,
                mode: mode
            },
            success: function(response) {
                if (response.success) {
                    $('#mc-image-optimization-label').text(enabled ? 'On' : 'Off');
                    showNotice('success', 'Image optimization ' + (enabled ? 'enabled' : 'disabled') + '!');
                } else {
                    $('#mc-image-optimization').prop('checked', !enabled);
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                $('#mc-image-optimization').prop('checked', !enabled);
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('#mc-image-optimization').prop('disabled', false);
            }
        });
    }

    /**
     * Toggle image resize
     */
    function toggleImageResize(enabled) {
        $('#mc-image-resize').prop('disabled', true);
        var mode = enabled ? 'ON' : 'OFF';

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_toggle_image_resize',
                nonce: mcCdnData.nonce,
                mode: mode
            },
            success: function(response) {
                if (response.success) {
                    $('#mc-image-resize-label').text(enabled ? 'On' : 'Off');
                    showNotice('success', 'Image resize ' + (enabled ? 'enabled' : 'disabled') + '!');
                } else {
                    $('#mc-image-resize').prop('checked', !enabled);
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                $('#mc-image-resize').prop('checked', !enabled);
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('#mc-image-resize').prop('disabled', false);
            }
        });
    }

    /**
     * Save minify settings
     */
    function saveMinifySettings() {
        var minifyJs = $('#mc-minify-js').is(':checked');
        var minifyCss = $('#mc-minify-css').is(':checked');

        $('#mc-minify-js, #mc-minify-css').prop('disabled', true);

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_save_minify',
                nonce: mcCdnData.nonce,
                minify_js: minifyJs,
                minify_css: minifyCss
            },
            success: function(response) {
                if (response.success) {
                    $('#mc-minify-js-label').text(minifyJs ? 'On' : 'Off');
                    $('#mc-minify-css-label').text(minifyCss ? 'On' : 'Off');
                    showNotice('success', 'Minification settings updated!');
                } else {
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $('#mc-minify-js, #mc-minify-css').prop('disabled', false);
            }
        });
    }

    /**
     * Purge all cache
     */
    function purgeAllCache() {
        if (!confirm('Are you sure you want to purge ALL cache? This will remove all cached content from CDN edge servers.')) {
            return;
        }

        var $btn = $('#mc-purge-all');
        $btn.prop('disabled', true).find('.dashicons').addClass('mc-spin');

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_purge_cache_all',
                nonce: mcCdnData.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'All cache has been purged successfully!');
                } else {
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $btn.prop('disabled', false).find('.dashicons').removeClass('mc-spin');
            }
        });
    }

    /**
     * Purge specific paths
     */
    function purgeSpecificPaths() {
        var paths = $('#mc-purge-paths').val().trim();

        if (!paths) {
            showNotice('error', 'Please enter at least one path to purge.');
            return;
        }

        var $btn = $('#mc-purge-paths-btn');
        $btn.prop('disabled', true).find('.dashicons').addClass('mc-spin');

        $.ajax({
            url: mcCdnData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mc_cdn_purge_cache_paths',
                nonce: mcCdnData.nonce,
                paths: paths
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Selected paths have been purged successfully!');
                    $('#mc-purge-paths').val(''); // Clear textarea on success
                } else {
                    var msg = response.data && response.data.message ? response.data.message : mcCdnData.strings.error;
                    showNotice('error', msg);
                }
            },
            error: function() {
                showNotice('error', mcCdnData.strings.error);
            },
            complete: function() {
                $btn.prop('disabled', false).find('.dashicons').removeClass('mc-spin');
            }
        });
    }

    /**
     * Show notice
     */
    function showNotice(type, message) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');

        elements.notices.empty().append(notice);

        // Auto dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
