# Changelog

All notable changes to Oli Store Locator are documented in this file.

## 0.1.0 — 2026-10-20

### Added

- Store custom post type with address, geocoding, lat/lng override, contact fields, hours, description, thumbnail, categories, and tags.
- Front-end locator with address/ZIP autocomplete, radius, max results, category filters, HTML5 geolocation, directions, clustering, and km/miles.
- Google Maps and Leaflet/OpenStreetMap providers, design settings, live admin preview, custom CSS.
- Shortcodes `[oli_store_locator]`, `[oli_store_address]`, `[oli_store_hours]`, `[oli_store_map]` and matching Gutenberg blocks.
- CSV import/export and Google Sheets sync (public CSV export URL, optional API key, 5/15/hourly/daily WP-Cron).
- REST/AJAX search, result caching, theme-overridable templates, hooks/filters, RTL, EN/FR translations.
- Unit tests for CSV parsing, column mapping, and sync diff; GitHub Actions CI.
