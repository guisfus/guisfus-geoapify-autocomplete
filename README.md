# Geoapify Autocomplete

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

The GitHub repository uses the `wp-` prefix only to identify it as a WordPress plugin repository. When installing the plugin in WordPress, use the plugin folder name without the `wp-` prefix.

Correct plugin folder:

```txt
wp-content/plugins/geoapify-autocomplete/
```

Correct ZIP structure:

```txt
geoapify-autocomplete.zip
`-- geoapify-autocomplete/
    |-- geoapify-autocomplete.php
    |-- includes/
    |-- assets/
    |-- README.md
    `-- readme.txt
```

Do not install it as:

```txt
wp-content/plugins/wp-geoapify-autocomplete/
```

Backend installation:

1. Create a ZIP with `geoapify-autocomplete/` as the root folder.
2. In WordPress, go to **Plugins > Add New > Upload Plugin**.
3. Upload `geoapify-autocomplete.zip`.
4. Activate **Geoapify Autocomplete**.
5. Go to **Settings > Geoapify**.
6. Add your Geoapify API key.
7. Restrict the API key by HTTP referrer/domain in Geoapify before using it in production.

Manual installation:

1. Upload the `geoapify-autocomplete` folder to `wp-content/plugins/`.
2. Activate **Geoapify Autocomplete** from the WordPress plugins screen.
3. Go to **Settings > Geoapify** and configure your API key.

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

Additional security notes:

- Only users with `manage_options` can edit plugin settings.
- Settings are saved through the WordPress Settings API and sanitized before storage.
- Field mappings accept plain HTML IDs from the admin screen.
- HTML data attributes are read from frontend markup and used as CSS selectors only after guarded `querySelector()` calls.
- Suggestions are rendered with `textContent`, not HTML injection.
- The plugin does not create AJAX endpoints, REST routes, cookies, user tracking, or server-side external requests.

## Frontend Footprint

- Loads `assets/css/frontend.css` and `assets/js/frontend.js` only when an API key is configured.
- Exposes `window.GeoapifyAutocompleteConfig` with public autocomplete settings.
- Adds dropdown/status elements to `document.body` while autocomplete fields are active.
- Marks bound address inputs with `data-geoapify-autocomplete-bound="1"` to avoid duplicate bindings.

## Developer Filter

The frontend configuration can be customized with:

```php
add_filter( 'geoapify_autocomplete_public_config', function ( $config, $settings ) {
    $config['countryCode'] = 'es';
    return $config;
}, 10, 2 );
```

## License

GPL-2.0-or-later.
