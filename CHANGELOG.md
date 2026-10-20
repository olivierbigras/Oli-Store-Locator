# Changelog

All notable changes to Oli Store Locator are documented in this file.

## 0.1.1 — 2026-10-20

### Security

- REST `/geocode` requires `manage_options` or a valid `wp_rest` nonce (no public `__return_true`).
- Public address search caches geocode results for 30 days and limits external lookups per IP (filterable).
- CSV import checks `is_uploaded_file`, upload error, max size, and `csv`/`txt` types.
- Google Sheets fetch uses `wp_safe_remote_get` and allows only `docs.google.com`, `sheets.googleapis.com`, and `*.googleusercontent.com`.
- Nominatim never sleeps on public requests; it returns an error (or a cached result from the factory).

### Changed

- Visible text and docs say “OpenStreetMap” instead of “OSM”. The stored Leaflet tile key remains `osm`.
- Added `fr_CA` translations alongside `fr_FR`.

## 0.1.0 — 2026-10-20

### Added

- Store custom post type with address, geocoding, lat/lng override, contact fields, hours, description, thumbnail, categories, and tags.
- Front-end locator with address/ZIP autocomplete, radius, max results, category filters, HTML5 geolocation, directions, clustering, and km/miles.
- Google Maps and Leaflet/OpenStreetMap providers, design settings, live admin preview, custom CSS.
- Shortcodes `[oli_store_locator]`, `[oli_store_address]`, `[oli_store_hours]`, `[oli_store_map]` and matching Gutenberg blocks.
- CSV import/export and Google Sheets sync (public CSV export URL, optional API key, 5/15/hourly/daily WP-Cron).
- REST/AJAX search, result caching, theme-overridable templates, hooks/filters, RTL, EN/FR translations (fr_FR).
- Unit tests for CSV parsing, column mapping, and sync diff; GitHub Actions CI.
