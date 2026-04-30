# Maintenance Notes

This document highlights the parts of the plugin that are most likely to need attention when extending it.

## Architecture

- Admin PHP: `includes/admin/class-gaa-admin.php` registers the settings page and stores all settings in one serialized option.
- Settings PHP: `includes/class-gaa-settings.php` owns defaults and sanitization.
- Frontend PHP: `includes/public/class-gaa-public.php` enqueues assets and exposes `window.GeoapifyAutocompleteConfig`.
- Frontend JS: `assets/js/frontend.js` finds mapped address fields, calls Geoapify and renders the dropdown.

## Geoapify Response Mapping

The frontend expects Geoapify autocomplete results in `results[]` format with fields such as:

- `formatted`
- `address_line1`
- `street`
- `housenumber`
- `city`
- `state`
- `postcode`

Some countries return different administrative fields. Review `getCity()`, `getState()` and `fill()` in `assets/js/frontend.js` if mappings need to change.

## Dynamic Forms

The frontend uses `MutationObserver` to initialize forms injected by popups, builders or AJAX. Inputs are marked with `data-geoapify-autocomplete-bound="1"` to avoid duplicate bindings.

## Selectors And IDs

Admin mappings store HTML IDs without `#`. HTML data attributes can use CSS selectors, for example `data-geoapify-city="#city"`.

## Production Checklist

- Verify `GEOAPIFY_AUTOCOMPLETE_VERSION` and the readme stable tag.
- Restrict the Geoapify API key by HTTP referrer/domain.
- Test a static form and a dynamically injected form.
- Test keyboard navigation: arrow keys, enter and escape.
- Test results where Geoapify does not return city or postal code.
