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

It works with form builders and plain HTML forms as long as the address field can be identified by an ID or by data attributes.

Features:

* Geoapify address autocomplete.
* Automatic city, state and postal code filling.
* Multiple form mappings.
* Keyboard navigation.
* Accessible listbox markup and ARIA live status updates.
* Support for dynamically injected forms and popups.

== Installation ==

1. Upload the `geoapify-autocomplete` folder to `/wp-content/plugins/`.
2. Activate the plugin from the WordPress Plugins screen.
3. Go to Settings > Geoapify.
4. Enter your Geoapify API key.
5. Restrict the API key by HTTP referrer in Geoapify before using it in production.

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

== Frequently Asked Questions ==

= Do I need a Geoapify API key? =

Yes. You can create one at https://www.geoapify.com/.

= Does it work with multiple forms? =

Yes. You can add multiple field mappings from the settings screen or use data attributes in multiple address fields.

= Does it slow down my site? =

Frontend assets load only when an API key is configured, and requests are only made after the user types enough characters in a mapped address field.

== Changelog ==

= 1.0.0 =

* Initial public release.
