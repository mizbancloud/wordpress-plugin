=== MizbanCloud CDN ===
Contributors: mizbancloud
Donate link: https://mizbancloud.com
Tags: cdn, cache, performance, speed, optimization, mizbancloud, cloudflare alternative, persian cdn, iran cdn
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Manage your MizbanCloud CDN settings directly from WordPress admin panel. Control caching, optimization, and purge cache with ease.

== Description ==

**MizbanCloud CDN** is the official WordPress plugin for [MizbanCloud](https://mizbancloud.com) CDN service. It allows you to manage your CDN settings directly from your WordPress dashboard without needing to log into the MizbanCloud panel.

= Key Features =

* **Easy Setup** - Simply enter your API token and select your domain
* **Cache Management** - Control edge cache mode, TTL, and developer mode
* **Browser Cache** - Configure browser-side caching settings
* **Cache Purge** - Purge all cache or specific paths with one click
* **Image Optimization** - Enable WebP conversion and image resizing
* **Asset Minification** - Automatically minify JavaScript and CSS files
* **Always Online** - Keep your site accessible even when origin is down
* **RTL Support** - Full support for Persian and Arabic languages

= Why MizbanCloud CDN? =

MizbanCloud is a leading CDN provider with:

* Edge servers optimized for Middle East and Iran
* Competitive pricing
* Persian language support
* 24/7 technical support
* Easy-to-use dashboard

= Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* Active MizbanCloud account with API access
* At least one domain configured in MizbanCloud

= Getting Started =

1. Install and activate the plugin
2. Go to **MizbanCloud CDN** in your WordPress admin menu
3. Enter your API token (get it from your MizbanCloud dashboard)
4. Select your domain from the dropdown
5. Start managing your CDN settings!

= Documentation =

For detailed documentation, visit [MizbanCloud Documentation](https://mizbancloud.com/docs).

= Support =

* **Documentation**: [docs.mizbancloud.com](https://docs.mizbancloud.com)
* **Support**: [support.mizbancloud.com](https://support.mizbancloud.com)
* **Email**: support@mizbancloud.com

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to **Plugins > Add New**
3. Search for "MizbanCloud CDN"
4. Click **Install Now** and then **Activate**

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Go to **Plugins > Add New > Upload Plugin**
4. Choose the ZIP file and click **Install Now**
5. Activate the plugin

= Configuration =

1. Navigate to **MizbanCloud CDN** in the admin menu
2. Enter your MizbanCloud API token
3. Click **Refresh** to load your domains
4. Select the domain you want to manage
5. Click **Save Settings**

== Frequently Asked Questions ==

= Where do I get my API token? =

Log in to your MizbanCloud dashboard, go to **Account Settings > API**, and generate a new API token.

= Can I manage multiple domains? =

Yes, but you need to select one domain at a time in the plugin. You can switch between domains by selecting a different one from the dropdown.

= What happens when I purge cache? =

Purging cache removes all cached content from CDN edge servers. Your origin server will need to serve fresh content until the cache is rebuilt. This may temporarily increase load on your server.

= Is this plugin compatible with other caching plugins? =

Yes, MizbanCloud CDN works alongside other caching plugins like WP Super Cache, W3 Total Cache, or WP Rocket. The CDN handles edge caching while your caching plugin manages server-side caching.

= Does this plugin slow down my site? =

No, the plugin only loads its assets on its own admin page and makes API calls only when you interact with the settings.

= Can I use this plugin without a MizbanCloud account? =

No, this plugin requires an active MizbanCloud account and API token to function.

== Screenshots ==

1. Main settings page - Configure API token and select domain
2. Cache settings - Control edge cache mode and TTL
3. Purge cache - Purge all or specific paths
4. Browser cache - Configure browser caching
5. Acceleration - Image optimization and minification settings

== Changelog ==

= 1.0.0 =
* Initial release
* Cache management (mode, TTL, developer mode, always online)
* Browser cache configuration
* Cache purge (all or specific paths)
* Image optimization (WebP conversion)
* Image resize at edge
* JS/CSS minification
* Cache cookies toggle
* RTL language support
* Secure API communication

== Upgrade Notice ==

= 1.0.0 =
Initial release of MizbanCloud CDN plugin.

== Privacy Policy ==

This plugin communicates with MizbanCloud API servers (auth.mizbancloud.com) to manage your CDN settings. The following data is transmitted:

* Your API token (for authentication)
* Domain settings you configure
* Cache purge requests

No personal user data from your WordPress site visitors is collected or transmitted by this plugin.

For MizbanCloud's privacy policy, visit [mizbancloud.com/privacy](https://mizbancloud.com/privacy).
