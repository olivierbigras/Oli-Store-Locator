# Oli Store Locator

WordPress plugin: searchable store locator with Google Maps or free Leaflet/OpenStreetMap, configurable map design, CSV import/export, and Google Sheets sync.

- **Author:** [Olivier Bigras](https://olivierbigras.com/)
- **WordPress.org contributor:** `bigrat95`
- **Repository:** https://github.com/olivierbigras/Oli-Store-Locator
- **License:** GPLv2 or later
- **Requires:** WordPress 6.5+, PHP 7.4+
- **Version:** 0.2.4

See [`readme.txt`](readme.txt) for the full WordPress.org description, including the **External services** section. Bundled MarkerClustererPlus (`assets/vendor/markerclusterer/index.min.js`) is from [googlemaps/js-markerclustererplus](https://github.com/googlemaps/js-markerclustererplus).

## Quick start

1. Activate the plugin.
2. Stores → Settings: choose Leaflet + Nominatim (free) or Google Maps (API key).
3. Add stores, or import CSV / connect a Google Sheet shared as “anyone with the link can view”.
4. Add `[oli_store_locator]` to a page.

## Development

```bash
php tests/run-tests.php
composer install
vendor/bin/phpcs
php bin/make-php.php fr_CA
```

See [`tests/README.md`](tests/README.md) for the QA Google Sheet sample URL.

CI runs `php -l`, PHPCS (WordPress standard), and the unit tests on PHP 7.4, 8.1, and 8.3.

## Known gaps

Documented in FEATURE-MAP.md versus the wordpress.org locator (slug `wp-store-locator`). Highlights:

- No paid add-ons or analytics dashboard.
- Google in-map directions require the Directions API; Leaflet uses an external directions link only.
- Nominatim is rate-limited to 1 request/second.
- Custom fields beyond the mapped store columns are not synced from Sheets.
