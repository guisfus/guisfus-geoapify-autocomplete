# GuisFus Geoapify Autocomplete

A lightweight WordPress plugin that adds Geoapify-powered address autocomplete to existing forms and automatically fills city, state and postal code fields.

## Features

- Works with plain HTML forms and common WordPress form builders.
- Supports multiple form mappings from the WordPress admin.
- Supports direct HTML integration with `data-*` attributes.
- Handles dynamically injected forms and popups.
- Provides keyboard navigation and ARIA live status updates.
- Loads frontend assets only when an API key is configured.

## Requirements

- WordPress 5.8 or later.
- PHP 7.4 or later.
- A Geoapify API key.

## Installation

1. Upload the plugin folder to `/wp-content/plugins/guisfus-geoapify-autocomplete`.
2. Activate **GuisFus Geoapify Autocomplete** in WordPress.
3. Go to **Settings > GuisFus Geoapify**.
4. Add your Geoapify API key.
5. Restrict the API key by HTTP referrer/domain in Geoapify before using it in production.

## Usage

### Admin Mapping

In the plugin settings, add the ID of the address input and the optional IDs for city, state and postal code fields.

### HTML Attributes

Add attributes directly to the address input:

```html
<input
  data-geoapify="address"
  data-geoapify-city="#city"
  data-geoapify-state="#state"
  data-geoapify-zip="#postcode"
>
```

## Security

This plugin calls Geoapify from the browser, so the API key is intentionally exposed client-side. This is normal for browser-based autocomplete integrations.

For production websites, restrict your Geoapify API key by allowed HTTP referrers/domains in the Geoapify dashboard.

## Developer Filter

The frontend configuration can be customized with:

```php
add_filter( 'guisfus_geoapify_autocomplete_public_config', function ( $config, $settings ) {
    $config['countryCode'] = 'es';
    return $config;
}, 10, 2 );
```

## License

GPL-2.0-or-later.
