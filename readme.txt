=== Oli Store Locator ===
Contributors: bigrat95
Donate link: https://olivierbigras.com/
Tags: store locator, maps, google maps, leaflet, google sheets
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.2.3
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

== External services ==

This plugin does not track visitors and does not include upsells. It only contacts the services below, and only when the matching setting or feature is used. Leaflet, Leaflet.markercluster, and MarkerClustererPlus are bundled locally. The minified MarkerClustererPlus script is `assets/vendor/markerclusterer/index.min.js`; its source is [googlemaps/js-markerclustererplus](https://github.com/googlemaps/js-markerclustererplus).

= Google Maps (JavaScript, Geocoding, Places) =

* What: optional Google Maps JavaScript API (map, Places autocomplete, Directions) and server-side Geocoding / Places Autocomplete.
* When: only if Map provider or Geocoding is set to Google. The browser loads `https://maps.googleapis.com/maps/api/js` on locator pages. The server calls `https://maps.googleapis.com/maps/api/geocode/json` and `https://maps.googleapis.com/maps/api/place/autocomplete/json` when an address is geocoded or autocompleted.
* Data sent: the API key you configure; search addresses or coordinates; the visitor’s IP as seen by Google when the JS API loads. No store owner account data is sent.
* Terms: [Google Maps Platform Terms](https://cloud.google.com/maps-platform/terms), [Geocoding policies](https://developers.google.com/maps/documentation/geocoding/policies).
* Privacy: [Google Privacy Policy](https://policies.google.com/privacy).

= Google Sheets (published CSV, optional API) =

* What: import / scheduled sync of store rows from a spreadsheet you paste.
* When: when an administrator saves a sheet URL, clicks Detect columns / Sync, or a WP-Cron sync runs. The server uses `wp_safe_remote_get` on `https://docs.google.com/spreadsheets/d/{ID}/export?format=csv` (or a published CSV URL). WordPress follows the HTTPS redirect from that URL to `*.googleusercontent.com` (Google’s download host). If you enter a Sheets API key, the plugin also calls `https://sheets.googleapis.com/v4/spreadsheets/`. Requests send a plugin User-Agent (`Oli-Store-Locator/{version}; {site URL}`).
* Data sent: the sheet ID / gid from the URL you entered, and the optional API key. No visitor data is sent. The sheet must be shared as “anyone with the link can view” (or reachable with the key).
* Terms: [Google Terms of Service](https://policies.google.com/terms), [Google APIs Terms](https://developers.google.com/terms).
* Privacy: [Google Privacy Policy](https://policies.google.com/privacy).

= Nominatim (OpenStreetMap geocoding) =

* What: free server-side geocoding and reverse lookup when Geocoding is set to Nominatim.
* When: an administrator looks up coordinates from a store address; CSV import / Sheets sync geocodes a row that has no valid latitude/longitude; an admin tool geocodes missing stores; and, when Geocoding is Nominatim and Search method is Geocode API, a visitor search by address or ZIP (the typed query is geocoded so nearby stores can be ranked). Public visitor searches use cached results and a per-IP limit; they do not sleep on Nominatim.
* Data sent: the address string — a store address on admin/import, or the address/ZIP the visitor typed on a public search — plus the plugin User-Agent (plugin name/version and site URL) to `https://nominatim.openstreetmap.org/`. No visitor account data is sent.
* Terms: [Nominatim usage policy](https://operations.osmfoundation.org/policies/nominatim/).
* Privacy: [OSMF Privacy Policy](https://wiki.osmfoundation.org/wiki/Privacy_Policy).

= Map tiles (OpenStreetMap, CARTO, OpenTopoMap, or custom URL) =

* What: raster map tiles for the free Leaflet map.
* When: on locator pages when Map provider is Leaflet (or Google was selected without an API key) and the visitor loads the map (or after GDPR consent, if that option is on).
* Data sent: tile x/y/z coordinates and the visitor’s IP to the tile host. No store form data is sent.
* OpenStreetMap (`https://{s}.tile.openstreetmap.org/`): [OSMF Terms of Use](https://wiki.osmfoundation.org/wiki/Terms_of_Use). Privacy: [OSMF Privacy Policy](https://wiki.osmfoundation.org/wiki/Privacy_Policy).
* CARTO (`https://{s}.basemaps.cartocdn.com/`): [CARTO terms](https://carto.com/legal/), [CARTO privacy](https://carto.com/privacy/).
* OpenTopoMap (`https://{s}.tile.opentopomap.org/`): [OpenTopoMap](https://opentopomap.org/), [OpenTopoMap licence / terms](https://opentopomap.org/about), [OSMF Privacy Policy](https://wiki.osmfoundation.org/wiki/Privacy_Policy).
* Custom tile URL: if you enter a custom HTTPS tile template under Settings → Map, the visitor’s browser requests tiles from that host. Data sent is the tile x/y/z path and the visitor’s IP. Use a host whose terms and privacy policy you accept.

= Google Maps directions links =

* What: “Get directions” opens `https://www.google.com/maps/dir/` with the store coordinates as the destination. In-map directions (Google Maps provider + key only) use the Google Directions service in the browser.
* When: the visitor clicks the directions link (or an in-map route) on a locator result.
* Data sent: destination latitude/longitude (and, for in-map directions, the origin the visitor chose). Google also sees the visitor’s IP.
* Terms: [Google Maps Platform Terms](https://cloud.google.com/maps-platform/terms). Privacy: [Google Privacy Policy](https://policies.google.com/privacy).

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

= 0.2.3 =
* Load bundled translations with `load_plugin_textdomain()` on WordPress 6.5–6.6 (automatic from 6.7).
* Show a message and Leaflet fallback when Google Maps reports `gm_authFailure`.
* Detect columns reports success for a valid sheet CSV. Clear the “not geolocated” warning after a successful geocode.
* Document `googleusercontent.com` Sheets redirects and visitor addresses sent to Nominatim on search.

= 0.2.2 =
* Reject non-numeric, non-finite, and out-of-range coordinates on import, Sheets sync, metabox, AJAX, and REST.
* Empty Gutenberg block attributes no longer overwrite shortcode defaults (radius, map height).
* Google Maps without an API key falls back to Leaflet and shows an admin notice.
* Document Nominatim, CARTO, OpenTopoMap, custom tiles, and Google directions in External services.
* Treat a Google sign-in HTML response as a Sheets error. Show Sync Now and unknown-status notices.

= 0.2.1 =
* Exclude tests, bin, phpcs, GitHub workflows, and Composer files from the distribution ZIP.
* Move locator CSS variables and custom CSS to `wp_add_inline_style`.
* Document Google Maps, Google Sheets, and map tile services (what, when, data, terms, privacy).
* Tighten sanitization of uploads and coordinates. Plugin Check Offloading false positives on Sheets host checks removed.

= 0.2.0 =
* Settings screens match the free wordpress.org locator sections (API, Search, Map, User Experience, Markers, Store Editor, Local Pages, Labels, Tools).
* Featured stores, category colors, name search, GDPR consent, and more shortcode attributes.
* Google Sheets sync history with automatic purge and an overlap lock.
* Configurable map design: tiles, colors, markers, clustering, info window, list template, custom CSS.
* /geocode requires a capability (not a nonce alone). Public geocode and autocomplete share a per-IP limit. CSV formula cells and Status values are sanitized. Sheet URLs are validated before save.

= 0.1.1 =
* Restrict the REST /geocode route to administrators or a valid wp_rest nonce.
* Cache public geocoding for 30 days and limit external lookups per IP.
* Validate CSV uploads and only fetch Google Sheets from allowed hosts.
* Add French (Canada) translations and write OpenStreetMap in full in visible text.

= 0.1.0 =
* Initial release.

== Upgrade Notice ==

= 0.2.3 =
Translations on WordPress 6.5–6.6, Google key failure fallback, and External services clarifications. Settings keys are unchanged.

= 0.2.2 =
Coordinate validation, block defaults, Google-without-key fallback, and External services documentation. Settings keys are unchanged.

= 0.2.1 =
Packaging, security, and wordpress.org readme (External services) updates. Settings keys are unchanged.

= 0.2.0 =
Settings screens, search options, map design, and Google Sheets history from 0.1.1. Existing option keys are unchanged.

= 0.1.1 =
Security and rate-limit fixes for geocoding, CSV import, and Google Sheets.

= 0.1.0 =
Initial release.
