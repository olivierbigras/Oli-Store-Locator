# Changelog

All notable changes to Oli Store Locator are documented in this file.

## 0.2.3 — 2026-10-23

### Fixed

- On WordPress 6.5–6.6, `load_plugin_textdomain()` runs on `init` so bundled `/languages` files (e.g. fr_CA “Toutes les catégories”) load. WordPress 6.7+ still uses automatic loading only.
- Invalid Google Maps API keys trigger `gm_authFailure`: a public message plus Leaflet/OpenStreetMap fallback instead of a blank map.
- Detect columns shows a success status when the sheet CSV is valid.
- A successful address geocode after invalid CSV/metabox coordinates no longer leaves the “not geolocated” warning.

### Documentation

- External services names `googleusercontent.com` as the Sheets published-CSV redirect host.
- Nominatim receives the visitor-typed address/ZIP on public Geocode API searches.

## 0.2.2 — 2026-10-23

### Security

- Latitude/longitude must be numeric, finite, and in range (lat −90…90, lng −180…180) on CSV import, Sheets sync, the store metabox, settings, AJAX, and REST. Invalid values are cleared and the store is not geolocated.
- Search skips stored Inf/NaN coordinates so Haversine never returns HTTP 500.
- Sheets fetch allow-list is only `docs.google.com` and `sheets.googleapis.com` (plain hostnames). `wp_safe_remote_get` follows the published-CSV redirect. Requests send `Oli-Store-Locator/{version}; {site}`.
- An HTML Google sign-in page is treated as a fetch error, not a successful empty sheet.

### Fixed

- Gutenberg blocks no longer send empty attributes that overwrote shortcode defaults (radius 0, missing map).
- Google Maps selected without an API key does not load the Maps JS API; admin notice + Leaflet on the public map.
- Sync Now notices survive the settings redirect. Unknown Status values warn and become draft.

### Documentation

- External services: Nominatim (address, admin/import timing, ToS/privacy), CARTO (`basemaps.cartocdn.com`), OpenTopoMap, custom tile URLs, and `google.com/maps` directions.
- MarkerClustererPlus source: https://github.com/googlemaps/js-markerclustererplus

## 0.2.1 — 2026-10-23

### Packaging

- `.gitattributes` `export-ignore` for `tests/`, `bin/`, `.github/`, `phpcs.xml.dist`, Composer files, and other development paths. The wordpress.org ZIP is `git archive`.

### Security

- Locator theme variables and Custom CSS use `wp_add_inline_style` instead of raw `<style>` tags in `wp_head`.
- Uploaded CSV/JSON files and latitude/longitude inputs are sanitized more strictly.

### Documentation

- `readme.txt` `== External services ==` now states what Google Maps, Google Sheets, and map tiles do, when they run, which data is sent, and links to terms and privacy policies.

### Plugin Check

- Google Sheets host allow-list no longer embeds `googleusercontent.com` literals that Plugin Check treats as CDN offloading.
- CLI `bin/make-php.php` no longer `echo`s an unescaped path (the file is not shipped).

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
