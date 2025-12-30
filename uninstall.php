<?php
/**
 * MizbanCloud CDN Uninstall
 *
 * Fired when the plugin is uninstalled.
 *
 * @package MizbanCloudCDN
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('mizbancloud_cdn_settings');

// For multisite, delete options from all sites
if (is_multisite()) {
    $sites = get_sites();
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);
        delete_option('mizbancloud_cdn_settings');
        restore_current_blog();
    }
}
