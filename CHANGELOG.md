# Changelog

All notable changes to Oli Store Locator are documented in this file.

## 0.2.0 — 2026-10-20

### Added

- Settings sections aligned with the free wordpress.org store locator: API, Search, Map, User Experience, Markers, Store Editor, Local Pages, Labels, Tools, plus Design, Import / Export, and Google Sheets.
- Name search, featured stores, category colors, GDPR map consent, hide-map-until-search, hide results list, nearest-location fallback, country filtering, and query-string deep links (`oli_sl_address`, `oli_sl_name`).
- Opening-hours open/closed status, more shortcode attributes, admin bar shortcut, geocode-missing and settings import/export tools.
- Google Sheets sync overlap lock, persistent history (date, added, modified, deleted, errors) with automatic purge.
- Fully configurable map design: tile provider, colors, custom markers, clustering, info-window style, list template, optional custom CSS.

### Security

- `/geocode` requires `manage_options` or the dedicated `oli_sl_geocode` capability (granted to administrators). A REST nonce alone is no longer enough.
- The 10-per-minute per-IP geocode budget (429, `REMOTE_ADDR` only, never `X-Forwarded-For`) applies to every public geocode path, including autocomplete.
- CSV export prefixes formula-like cells (`=`, `+`, `-`, `@`, tab, CR) with an apostrophe; import does the same, including `@`.
- CSV Status accepts only `publish`, `draft`, `pending`, and `private`; anything else becomes draft with a warning.
- Google Sheet URLs are validated (`/spreadsheets/d/…` or a published CSV export) before they are saved.

### Fixed

- “Use my location” now sends the selected radius and draws the radius circle when that option is on.
- `fr_CA` `.l10n.php` is generated from the `.po` and covers the Settings screens plus “All categories”.

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
