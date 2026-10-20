=== Oli Store Locator ===
Contributors: bigrat95
Donate link: https://olivierbigras.com/
Tags: store locator, maps, google maps, leaflet, google sheets
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Searchable store locator with Google Maps or free Leaflet/OpenStreetMap, design controls, CSV import, and Google Sheets sync.

== Description ==

Oli Store Locator lets you publish a store finder that is independently written for WordPress. Manage locations as a custom post type, search by address or ZIP, and style the map from the settings screen.

= Stores =

* Custom post type with address, city, province/state, postal code, country
* Google Geocoding API or free Nominatim (OpenStreetMap) with 1 req/s rate limiting
* Manual latitude / longitude override
* Phone, fax, email, website, opening hours (textarea or day dropdowns)
* Description, featured image / image URL
* Categories (optional custom marker icon per category) and tags
* Optional public single-store pages with a configurable slug

= Search & map =

* `[oli_store_locator]`, `[oli_store_address]`, `[oli_store_hours]`, `[oli_store_map]` shortcodes plus Gutenberg blocks
* Address / ZIP search with autocomplete
* Radius and max-results dropdowns, category dropdown or checkboxes
* HTML5 “Use my location”
* Directions link and in-map directions (Google)
* Distance in km or miles
* Result list below or beside the map
* Marker clustering, start location / zoom, map type, scroll wheel and controls
* Theme-overridable templates in `yourtheme/oli-store-locator/`
* AJAX + REST (`/wp-json/oli-store-locator/v1/search`) with optional result caching
* Hooks and filters (`oli_sl_*`), RTL styles, EN/fr_FR/fr_CA translations, WPML / TranslatePress friendly

= Design =

* Google style presets or custom JSON; Leaflet tile providers (OpenStreetMap, Carto, OpenTopo, custom URL)
* Colors for primary, buttons, text, list hover, and clusters
* Fonts inherit from the theme
* Marker upload, map height/width, mobile layout, custom CSS
* Live colour preview in admin

= Google Sheets sync =

Paste a sheet shared as “anyone with the link can view”. The plugin reads:

`https://docs.google.com/spreadsheets/d/{ID}/export?format=csv&gid={GID}`

No Google login is required. An optional Sheets API key is available when the public CSV export is blocked.

* Auto-detect columns (name, address, city, province/state, postal code, country, phone, email, website, hours, category, lat, lng, description, image, ID)
* Mapping saved per sync
* Every 5 minutes, 15 minutes, hourly, or daily via WP-Cron custom schedules
* If you set `DISABLE_WP_CRON`, call `wp-cron.php` from server cron at least every 5 minutes
* Save & Start Sync, Sync Now, Pause/Resume, Remove Sync
* Status panel: Active/paused, next sync, last checked, “All up to date”, created/updated/deleted/skipped, error log
* Idempotent matching on an ID column, otherwise hash of name+address
* Geocode only when the address changed; batched to avoid timeouts
* Optional draft or trash for stores removed from the sheet

= External services =

This plugin does not track visitors and does not include upsells. It only contacts the services below, and only when the corresponding feature is used.

1. **Google Maps JavaScript API** (`https://maps.googleapis.com/`) — front-end map, Places autocomplete, and Directions when the Google provider is selected. Requires an API key you configure. [Google Maps Platform Terms](https://cloud.google.com/maps-platform/terms).
2. **Google Geocoding API** (`https://maps.googleapis.com/maps/api/geocode/json`) — converts addresses to coordinates on the server when Google geocoding is selected. [Geocoding API policies](https://developers.google.com/maps/documentation/geocoding/policies).
3. **Google Places Autocomplete API** (`https://maps.googleapis.com/maps/api/place/autocomplete/json`) — address suggestions when Google geocoding is selected.
4. **OpenStreetMap Nominatim** (`https://nominatim.openstreetmap.org/`) — geocoding and autocomplete when the free provider is selected. [Nominatim usage policy](https://operations.osmfoundation.org/policies/nominatim/).
5. **OpenStreetMap / CARTO / OpenTopoMap tile servers** — map imagery when Leaflet is selected.
6. **Google Sheets CSV export** (`https://docs.google.com/spreadsheets/d/{ID}/export?format=csv`) — syncs store rows from the sheet URL you paste.
7. **Google Sheets API v4** (`https://sheets.googleapis.com/v4/spreadsheets/`) — optional, only if you enter a Sheets API key.

Leaflet, Leaflet.markercluster, and MarkerClustererPlus are bundled locally.

== Installation ==

1. Upload the `oli-store-locator` folder to `/wp-content/plugins/` or install the ZIP from Plugins → Add New.
2. Activate the plugin.
3. Go to Stores → Settings. For a free setup, keep Map provider = Leaflet and Geocoding = Nominatim.
4. Add stores under Stores → Add New, or import a CSV / start a Google Sheets sync.
5. Place `[oli_store_locator]` on a page (or the Store Locator block).

== Frequently Asked Questions ==

= Do I need a Google API key? =

No. Leaflet + Nominatim works without keys. Google Maps, Google geocoding, Places autocomplete, and in-map directions need a Google key.

= How do I sync a Google Sheet? =

Share the sheet with “anyone with the link can view”, paste the URL on Stores → Settings → Google Sheets, detect columns, then Save & Start Sync.

= Why is a 5-minute sync late? =

WP-Cron runs when someone visits the site. With `DISABLE_WP_CRON` defined, hit `https://example.com/wp-cron.php` from server cron every 5 minutes.

= Can I override templates? =

Copy files from `templates/` to `yourtheme/oli-store-locator/`.

== Changelog ==

= 0.2.0 =
* Settings screens match the free wordpress.org locator sections (API, Search, Map, User Experience, Markers, Store Editor, Local Pages, Labels, Tools).
* Featured stores, category colors, name search, GDPR consent, and more shortcode attributes.
* Google Sheets sync history with automatic purge and an overlap lock.
* Configurable map design: tiles, colors, markers, clustering, info window, list template, custom CSS.

= 0.1.1 =
* Restrict the REST /geocode route to administrators or a valid wp_rest nonce.
* Cache public geocoding for 30 days and limit external lookups per IP.
* Validate CSV uploads and only fetch Google Sheets from allowed hosts.
* Add French (Canada) translations and write OpenStreetMap in full in visible text.

= 0.1.0 =
* Initial release.

== Upgrade Notice ==

= 0.2.0 =
Settings screens, search options, map design, and Google Sheets history from 0.1.1. Existing option keys are unchanged.

= 0.1.1 =
Security and rate-limit fixes for geocoding, CSV import, and Google Sheets.

= 0.1.0 =
Initial release.
