# Correspondence: wordpress.org store locator → Oli Store Locator 0.2.0

Reference: the free plugin on wordpress.org (slug `wp-store-locator`, 3.1.0), downloaded read-only. Oli is independently written (GPL) and rebranded. Their name, logo, and links are not used.

Status: **done** (same job in Oli), **improved** (same job plus an Oli addition), **absent** (not reproduced).

## Screens

| Original screen | Oli screen | Status |
| --- | --- | --- |
| Stores list | Stores → All Stores (`oli_store`) | done |
| Add Store / store editor | Stores → Add New (address, hours, coords, featured) | improved |
| Categories (+ color column) | Stores → Categories (marker + color) | done |
| Settings → API | Settings → API | done |
| Settings → Search | Settings → Search | done |
| Settings → Map | Settings → Map | done |
| Settings → User Experience | Settings → User Experience | done |
| Settings → Markers | Settings → Markers | done |
| Settings → Store Editor | Settings → Store Editor | done |
| Settings → Local Pages | Settings → Local Pages | done |
| Settings → Labels | Settings → Labels | done |
| Settings → Tools | Settings → Tools | done |
| Appearance / live customizer | Settings → Design (colors, tiles, templates, CSS, preview) | improved |
| Fields Manager | — | absent |
| Section Editor | Design list / info-window templates | improved |
| Marker Studio | Custom marker upload + per-category icons | improved |
| Map Shapes | — | absent |
| Home / onboarding wizard | — | absent |
| Add-ons / Licenses | — | absent |
| CSV Manager (paid add-on) | Settings → Import / Export | improved |
| — | Settings → Google Sheets | improved |

## Functions

| Original function | Oli | Status |
| --- | --- | --- |
| Store CPT + address / city / state / zip / country | `_oli_sl_*` meta | done |
| Lat/lng + geocode on save | Google or Nominatim, manual override | done |
| Phone, fax, email, URL | Store editor | done |
| Opening hours (textarea or dropdowns) | Store Editor + open/closed status | improved |
| Categories + tags | `oli_store_category`, `oli_store_tag` | done |
| Category colors | Term meta + filter dots | done |
| Featured stores (“show first in results”) | `_oli_sl_featured` | done |
| Address / ZIP search, radius, max results | Search + shortcode | done |
| Name search | Search method = name | done |
| Autocomplete | Nominatim or Google Places | done |
| Auto-locate | HTML5 geolocation | done |
| Hide map until first search | Search setting | done |
| Hide results list | Search setting + `results_list="false"` | done |
| Marker clustering | Leaflet.markercluster / MarkerClusterer | done |
| Custom markers | Default upload + category icons | done |
| Hide start marker | Markers setting | done |
| Info window + directions | Design + UX | done |
| Thumbnail in info window | UX setting | done |
| Shortcode `[wpsl]` + attributes | `[oli_store_locator]` (`radius`, `max_results`, `category`, `height`, `width`, `zoom`, `map_type`, `auto_locate`, `auto_load`, `results_list`, `directions`, `start_lat`, `start_lng`, `name`, `id`, `template`) | done |
| `[wpsl_address]`, `[wpsl_hours]`, `[wpsl_map]` | `[oli_store_address]`, `[oli_store_hours]`, `[oli_store_map]` | done |
| Store Locator + Map blocks | Matching Gutenberg blocks | done |
| Local / single store pages | Local Pages + `templates/single-store.php` | done |
| Labels override | Labels tab | done |
| Translations | `en`, `fr_FR`, `fr_CA` | improved |
| Tools: clear caches, debug, admin bar | Tools tab | done |
| Tools: geocode missing | Tools tab | done |
| Settings import/export | Tools tab | done |
| GDPR consent before map load | UX tab (built-in, no third-party cookie plugins) | improved |
| Query string `?wpsl_address=` / `?wpsl_name=` | `?oli_sl_address=` / `?oli_sl_name=` | done |
| Mapbox / Stadia Maps providers | — (OpenStreetMap + Google only, as specified) | absent |
| Paid add-ons, licenses, analytics | — | absent |
| CSV import/export | Built-in, validated upload | improved |
| Google Sheets scheduled sync | Daily / hourly / 15 min / 5 min WP-Cron, Sync now, history log, overlap lock, Google-only `wp_safe_remote_get` | improved |
| Configurable map design | Tiles, colors, markers, clustering, info window, list template, custom CSS | improved |

## Option keys kept from 0.1.1

`leaflet_tiles=osm` and every other 0.1.1 `oli_sl_options` key are unchanged. New keys were added alongside them.
