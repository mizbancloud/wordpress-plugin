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
    $mc_cdn_sites = get_sites();
    foreach ($mc_cdn_sites as $mc_cdn_site) {
        switch_to_blog($mc_cdn_site->blog_id);
        delete_option('mizbancloud_cdn_settings');
        restore_current_blog();
    }
}
