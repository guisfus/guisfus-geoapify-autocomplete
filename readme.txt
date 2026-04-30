=== Geoapify Autocomplete ===
Contributors: guisfus
Tags: address, autocomplete, geoapify, forms, geocoding
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds Geoapify-powered address autocomplete to WordPress forms and fills city, state and postal code fields automatically.

== Description ==

Geoapify Autocomplete adds address suggestions to existing WordPress forms without requiring a specific form plugin.

The Geoapify API key is sent to the browser because autocomplete requests are made client-side. Restrict the key by HTTP referrer/domain in Geoapify before using it in production.

It works with form builders and plain HTML forms as long as the address field can be identified by an ID or by data attributes.

Features:

* Geoapify address autocomplete.
* Automatic city, state and postal code filling.
* Multiple form mappings.
* Keyboard navigation.
* Accessible listbox markup and ARIA live status updates.
* Support for dynamically injected forms and popups.

== Installation ==

The GitHub repository uses the `wp-` prefix only to identify it as a WordPress plugin repository. When installing the plugin in WordPress, use the plugin folder name without the `wp-` prefix.

Correct plugin folder: `/wp-content/plugins/geoapify-autocomplete/`

Correct ZIP structure: `geoapify-autocomplete.zip` containing a root `geoapify-autocomplete/` folder with `geoapify-autocomplete.php` inside it.

Do not install it as `/wp-content/plugins/wp-geoapify-autocomplete/`.

Backend installation:

1. Create a ZIP with `geoapify-autocomplete/` as the root folder.
2. Go to Plugins > Add New > Upload Plugin.
3. Upload `geoapify-autocomplete.zip`.
4. Activate Geoapify Autocomplete.
5. Go to Settings > Geoapify.
6. Enter your Geoapify API key.
7. Restrict the API key by HTTP referrer/domain in Geoapify before using it in production.

Manual installation:

1. Upload the `geoapify-autocomplete` folder to `/wp-content/plugins/`.
2. Activate Geoapify Autocomplete from the WordPress plugins screen.
3. Go to Settings > Geoapify and configure your API key.

== Usage ==

You can configure autocomplete in two ways.

Admin mode:

Add the ID of the address input and, optionally, the IDs of city, state and postal code inputs in the plugin settings.

HTML mode:

Add data attributes directly to the address input.

`<input data-geoapify="address" data-geoapify-city="#city" data-geoapify-state="#state" data-geoapify-zip="#postcode">`

== Security Notes ==

The Geoapify API key is sent to the browser because autocomplete requests are made client-side. This is expected for this type of integration.

For production websites, restrict the key in your Geoapify dashboard by allowed HTTP referrers/domains.

Only users with the `manage_options` capability can access and save plugin settings. Settings are saved through the WordPress Settings API and sanitized before storage.

The plugin does not create AJAX endpoints, REST routes, cookies, user tracking, or server-side external requests. Suggestions are rendered as text, not injected HTML.

== Frequently Asked Questions ==

= Do I need a Geoapify API key? =

Yes. You can create one at https://www.geoapify.com/.

= Does it work with multiple forms? =

Yes. You can add multiple field mappings from the settings screen or use data attributes in multiple address fields.

= Does it slow down my site? =

Frontend assets load only when an API key is configured, and requests are only made after the user types enough characters in a mapped address field.

= Is the Geoapify API key secret? =

No. Browser-based autocomplete requires the key to be available client-side. Restrict it by allowed HTTP referrers/domains in Geoapify.

== Changelog ==

= 1.0.0 =

* Initial public release.
