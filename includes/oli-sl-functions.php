<?php
/**
 * Shared helpers and default options.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default plugin options.
 *
 * @return array
 */
function oli_sl_default_options() {
	return array(
		'map_provider'               => 'leaflet',
		'geocoding_provider'         => 'nominatim',
		'google_api_key'             => '',
		'google_server_key'          => '',
		'sheets_api_key'             => '',
		'distance_unit'              => 'km',
		'start_lat'                  => '45.5017',
		'start_lng'                  => '-73.5673',
		'start_zoom'                 => '10',
		'max_results'                => '25',
		'max_results_options'        => '10,25,50,100',
		'search_radius'              => '50',
		'radius_options'             => '10,25,50,100,250,500',
		'map_type'                   => 'roadmap',
		'scroll_wheel'               => '1',
		'map_controls'               => '1',
		'street_view'                => '1',
		'auto_locate'                => '1',
		'enable_autocomplete'        => '1',
		'enable_directions'          => '1',
		'in_map_directions'          => '1',
		'marker_clustering'          => '1',
		'fit_bounds'                 => '1',
		'search_on_load'             => '1',
		'draw_radius'                => '1',
		'list_layout'                => 'below',
		'mobile_layout'              => 'map_first',
		'map_height'                 => '500',
		'map_width'                  => '100%',
		'map_style_preset'           => 'default',
		'map_style_json'             => '',
		'leaflet_tiles'              => 'osm', // Stored OpenStreetMap tile key; do not rename.
		'leaflet_custom_url'         => '',
		'leaflet_attribution'        => '',
		'primary_color'              => '#0073aa',
		'button_color'               => '#0073aa',
		'button_text_color'          => '#ffffff',
		'text_color'                 => '#333333',
		'hover_color'                => '#f0f0f0',
		'cluster_color'              => '#0073aa',
		'cluster_text_color'         => '#ffffff',
		'default_marker'             => '',
		'custom_css'                 => '',
		'category_filter_type'       => 'dropdown',
		'hours_format'               => 'textarea',
		'enable_single_pages'        => '1',
		'store_slug'                 => 'store',
		'enable_caching'             => '1',
		'cache_duration'             => '3600',
		'nominatim_rate_limit'       => '1',
		'infowindow_template'        => '',
		'results_template'           => '',
		'marker_template'            => '',
		'sheets_url'                 => '',
		'sheets_sync_enabled'        => '0',
		'sheets_sync_frequency'      => 'hourly',
		'sheets_removed_action'      => 'none',
		'sheets_batch_size'          => '25',
		'sheets_column_map'          => array(),
		'label_search'               => '',
		'label_address'              => '',
		'label_radius'               => '',
		'label_results'              => '',
		'label_category'             => '',
		'label_locate'               => '',
		'label_reset'                => '',
		'label_directions'           => '',
		'label_no_results'           => '',
		'label_phone'                => '',
		'label_hours'                => '',
		'label_website'              => '',
		'search_method'              => 'geocode',
		'input_placeholder'          => '',
		'autosubmit_autocomplete'    => '1',
		'force_postalcode'           => '0',
		'hide_map_until_search'      => '0',
		'hide_results_list'          => '0',
		'show_radius_dropdown'       => '1',
		'show_results_dropdown'      => '1',
		'sort_by'                    => 'distance',
		'sort_order'                 => 'asc',
		'category_filter'            => '1',
		'category_default'           => '',
		'all_categories_required'    => '0',
		'category_filter_only'       => '0',
		'enforce_borders'            => '0',
		'full_search'                => '0',
		'show_number_results'        => '0',
		'find_nearest'               => '0',
		'api_language'               => '',
		'region_restriction_type'    => 'bias',
		'bias_country'               => '',
		'restrict_countries'         => '',
		'max_auto_zoom'              => '16',
		'open_links_new_window'      => '1',
		'show_reset_map'             => '1',
		'direction_redirect'         => '1',
		'clickable_contact'          => '1',
		'hide_closed_hours'          => '0',
		'show_hour_status'           => '1',
		'expand_hours'               => '1',
		'clickable_store_name'       => '1',
		'clickable_phone'            => '1',
		'show_streetview_link'       => '0',
		'show_zoom_here'             => '0',
		'infowindow_thumbnail'       => '1',
		'mouse_focus'                => '0',
		'hide_country'               => '0',
		'hide_distance'              => '0',
		'marker_effect'              => 'bounce',
		'address_opens_infowindow'   => '1',
		'hide_start_marker'          => '0',
		'start_marker_on_top'        => '0',
		'cluster_exclude_start'      => '1',
		'cluster_max_zoom'           => '16',
		'cluster_size'               => '80',
		'cluster_style'              => 'default',
		'editor_default_country'     => '',
		'editor_hide_hours'          => '0',
		'editor_hide_phone'          => '0',
		'editor_hide_fax'            => '0',
		'editor_hide_email'          => '0',
		'editor_hide_url'            => '0',
		'editor_map_type'            => 'roadmap',
		'editor_default_hours'       => '',
		'permalink_remove_front'     => '0',
		'permalink_category'         => '0',
		'gdpr_consent'               => '0',
		'gdpr_text'                  => '',
		'debug_mode'                 => '0',
		'admin_bar_menu'             => '1',
		'compatibility_mode'         => '0',
		'featured_color'             => '#c45c26',
		'show_categories_in_results' => '1',
		'infowindow_bg'              => '#ffffff',
		'infowindow_text_color'      => '#333333',
		'infowindow_radius'          => '8',
		'infowindow_shadow'          => '1',
		'sync_log_retention_days'    => '30',
		'label_your_location'        => '',
		'label_store_name'           => '',
		'label_category_first'       => '',
		'label_adjust_search'        => '',
		'label_preloader'            => '',
		'label_more_info'            => '',
		'label_fax'                  => '',
		'label_email'                => '',
		'label_url'                  => '',
		'label_more_details'         => '',
		'label_start_location'       => '',
		'label_get_directions'       => '',
		'label_street_view'          => '',
		'label_zoom_here'            => '',
		'label_number_results'       => '',
		'label_number_results_one'   => '',
		'label_open'                 => '',
		'label_closed'               => '',
		'label_consent'              => '',
	);
}

/**
 * Get a plugin option from the combined settings array.
 *
 * @param string $key     Option key without the oli_sl_ prefix.
 * @param mixed  $fallback Default value.
 * @return mixed
 */
function oli_sl_get_option( $key, $fallback = null ) {
	$defaults = oli_sl_default_options();

	if ( null === $fallback && array_key_exists( $key, $defaults ) ) {
		$fallback = $defaults[ $key ];
	}

	$options = get_option( 'oli_sl_options', array() );

	if ( is_array( $options ) && array_key_exists( $key, $options ) ) {
		return $options[ $key ];
	}

	$legacy = get_option( 'oli_sl_' . $key, null );
	if ( null !== $legacy && false !== $legacy ) {
		return $legacy;
	}

	return $fallback;
}

/**
 * Update a single key inside oli_sl_options.
 *
 * @param string $key   Option key.
 * @param mixed  $value Value.
 * @return bool
 */
function oli_sl_update_option( $key, $value ) {
	$options         = get_option( 'oli_sl_options', array() );
	$options         = is_array( $options ) ? $options : array();
	$options[ $key ] = $value;
	return update_option( 'oli_sl_options', $options );
}

/**
 * Frontend / admin label with gettext fallback.
 *
 * @param string $key     Label key.
 * @param string $fallback Default string.
 * @return string
 */
function oli_sl_get_label( $key, $fallback ) {
	$stored = oli_sl_get_option( $key, '' );
	$text   = ( is_string( $stored ) && '' !== $stored ) ? $stored : $fallback;

	/**
	 * Filter a locator UI label (WPML / TranslatePress friendly).
	 *
	 * @param string $text     Label text.
	 * @param string $key      Label key.
	 * @param string $fallback Default text.
	 */
	return apply_filters( 'oli_sl_label', $text, $key, $fallback );
}

/**
 * Weekday keys used for structured opening hours.
 *
 * @return array
 */
function oli_sl_weekdays() {
	return array(
		'monday'    => __( 'Monday', 'oli-store-locator' ),
		'tuesday'   => __( 'Tuesday', 'oli-store-locator' ),
		'wednesday' => __( 'Wednesday', 'oli-store-locator' ),
		'thursday'  => __( 'Thursday', 'oli-store-locator' ),
		'friday'    => __( 'Friday', 'oli-store-locator' ),
		'saturday'  => __( 'Saturday', 'oli-store-locator' ),
		'sunday'    => __( 'Sunday', 'oli-store-locator' ),
	);
}

/**
 * Store meta keys.
 *
 * @return array
 */
function oli_sl_store_meta_keys() {
	return array(
		'address',
		'address_2',
		'city',
		'state',
		'zip',
		'country',
		'latitude',
		'longitude',
		'phone',
		'fax',
		'email',
		'url',
		'hours',
		'description',
		'image_url',
		'manual_coords',
		'sync_key',
		'external_id',
		'featured',
	);
}

/**
 * Get store meta with prefix.
 *
 * @param int    $store_id Store ID.
 * @param string $key      Meta key without prefix.
 * @param mixed  $fallback Default.
 * @return mixed
 */
function oli_sl_get_store_meta( $store_id, $key, $fallback = '' ) {
	$value = get_post_meta( $store_id, '_oli_sl_' . $key, true );
	return ( '' === $value || false === $value ) ? $fallback : $value;
}

/**
 * Build a full address string from parts.
 *
 * @param array $parts Address parts.
 * @return string
 */
function oli_sl_join_address( $parts ) {
	$parts = array_map( 'trim', array_filter( (array) $parts ) );
	return implode( ', ', $parts );
}

/**
 * Locate a theme-overridable plugin template.
 *
 * @param string $template_name Template file name.
 * @return string
 */
function oli_sl_locate_template( $template_name ) {
	$theme = locate_template( array( 'oli-store-locator/' . $template_name ) );
	if ( $theme ) {
		return $theme;
	}

	return OLI_SL_PLUGIN_DIR . 'templates/' . $template_name;
}

/**
 * Parse a comma-separated list of positive integers.
 *
 * @param string $value Raw value.
 * @return array
 */
function oli_sl_parse_int_list( $value ) {
	$items = array_map( 'absint', array_map( 'trim', explode( ',', (string) $value ) ) );
	$items = array_filter( $items );
	return array_values( array_unique( $items ) );
}

/**
 * Custom cron schedules (must be registered on every request).
 *
 * @param array $schedules Existing schedules.
 * @return array
 */
function oli_sl_cron_schedules( $schedules ) {
	$schedules['oli_sl_every_5min']  = array(
		'interval' => 300,
		'display'  => __( 'Every 5 Minutes', 'oli-store-locator' ),
	);
	$schedules['oli_sl_every_15min'] = array(
		'interval' => 900,
		'display'  => __( 'Every 15 Minutes', 'oli-store-locator' ),
	);
	return $schedules;
}

/**
 * Map config passed to the frontend.
 *
 * @return array
 */
function oli_sl_frontend_config() {
	$config = array(
		'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
		'restUrl'             => esc_url_raw( rest_url( 'oli-store-locator/v1/' ) ),
		'nonce'               => wp_create_nonce( 'oli_sl_nonce' ),
		'provider'            => oli_sl_get_option( 'map_provider', 'leaflet' ),
		'geocodingProvider'   => oli_sl_get_option( 'geocoding_provider', 'nominatim' ),
		'googleKey'           => oli_sl_get_option( 'google_api_key', '' ),
		'unit'                => oli_sl_get_option( 'distance_unit', 'km' ),
		'startLat'            => (float) oli_sl_get_option( 'start_lat', '45.5017' ),
		'startLng'            => (float) oli_sl_get_option( 'start_lng', '-73.5673' ),
		'startZoom'           => (int) oli_sl_get_option( 'start_zoom', '10' ),
		'mapType'             => oli_sl_get_option( 'map_type', 'roadmap' ),
		'scrollWheel'         => (bool) oli_sl_get_option( 'scroll_wheel', '1' ),
		'mapControls'         => (bool) oli_sl_get_option( 'map_controls', '1' ),
		'streetView'          => (bool) oli_sl_get_option( 'street_view', '1' ),
		'autoLocate'          => (bool) oli_sl_get_option( 'auto_locate', '1' ),
		'autocomplete'        => (bool) oli_sl_get_option( 'enable_autocomplete', '1' ),
		'directions'          => (bool) oli_sl_get_option( 'enable_directions', '1' ),
		'inMapDirections'     => (bool) oli_sl_get_option( 'in_map_directions', '1' ),
		'clustering'          => (bool) oli_sl_get_option( 'marker_clustering', '1' ),
		'fitBounds'           => (bool) oli_sl_get_option( 'fit_bounds', '1' ),
		'searchOnLoad'        => (bool) oli_sl_get_option( 'search_on_load', '1' ),
		'searchRadius'        => (int) oli_sl_get_option( 'search_radius', '50' ),
		'maxResults'          => (int) oli_sl_get_option( 'max_results', '25' ),
		'drawRadius'          => (bool) oli_sl_get_option( 'draw_radius', '1' ),
		'mapStylePreset'      => oli_sl_get_option( 'map_style_preset', 'default' ),
		'mapStyleJson'        => oli_sl_get_option( 'map_style_json', '' ),
		'leafletTiles'        => oli_sl_get_option( 'leaflet_tiles', 'osm' ),
		'leafletCustomUrl'    => oli_sl_get_option( 'leaflet_custom_url', '' ),
		'defaultMarker'       => oli_sl_get_option( 'default_marker', '' ),
		'clusterColor'        => oli_sl_get_option( 'cluster_color', '#0073aa' ),
		'clusterTextColor'    => oli_sl_get_option( 'cluster_text_color', '#ffffff' ),
		'enableSingle'        => (bool) oli_sl_get_option( 'enable_single_pages', '1' ),
		'tileProviders'       => Oli_SL_Map_Styles::leaflet_providers(),
		'googleStyles'        => Oli_SL_Map_Styles::google_presets(),
		'searchMethod'        => oli_sl_get_option( 'search_method', 'geocode' ),
		'autosubmit'          => (bool) oli_sl_get_option( 'autosubmit_autocomplete', '1' ),
		'hideMapUntilSearch'  => (bool) oli_sl_get_option( 'hide_map_until_search', '0' ),
		'hideResultsList'     => (bool) oli_sl_get_option( 'hide_results_list', '0' ),
		'showNumberResults'   => (bool) oli_sl_get_option( 'show_number_results', '0' ),
		'openLinksNewWindow'  => (bool) oli_sl_get_option( 'open_links_new_window', '1' ),
		'directionRedirect'   => (bool) oli_sl_get_option( 'direction_redirect', '1' ),
		'clickableContact'    => (bool) oli_sl_get_option( 'clickable_contact', '1' ),
		'clickablePhone'      => (bool) oli_sl_get_option( 'clickable_phone', '1' ),
		'clickableStoreName'  => (bool) oli_sl_get_option( 'clickable_store_name', '1' ),
		'showHourStatus'      => (bool) oli_sl_get_option( 'show_hour_status', '1' ),
		'expandHours'         => (bool) oli_sl_get_option( 'expand_hours', '1' ),
		'hideClosedHours'     => (bool) oli_sl_get_option( 'hide_closed_hours', '0' ),
		'infowindowThumbnail' => (bool) oli_sl_get_option( 'infowindow_thumbnail', '1' ),
		'mouseFocus'          => (bool) oli_sl_get_option( 'mouse_focus', '0' ),
		'hideCountry'         => (bool) oli_sl_get_option( 'hide_country', '0' ),
		'hideDistance'        => (bool) oli_sl_get_option( 'hide_distance', '0' ),
		'markerEffect'        => oli_sl_get_option( 'marker_effect', 'bounce' ),
		'addressOpensInfo'    => (bool) oli_sl_get_option( 'address_opens_infowindow', '1' ),
		'hideStartMarker'     => (bool) oli_sl_get_option( 'hide_start_marker', '0' ),
		'startMarkerOnTop'    => (bool) oli_sl_get_option( 'start_marker_on_top', '0' ),
		'clusterExcludeStart' => (bool) oli_sl_get_option( 'cluster_exclude_start', '1' ),
		'clusterMaxZoom'      => (int) oli_sl_get_option( 'cluster_max_zoom', '16' ),
		'clusterSize'         => (int) oli_sl_get_option( 'cluster_size', '80' ),
		'showStreetView'      => (bool) oli_sl_get_option( 'show_streetview_link', '0' ),
		'showZoomHere'        => (bool) oli_sl_get_option( 'show_zoom_here', '0' ),
		'maxAutoZoom'         => (int) oli_sl_get_option( 'max_auto_zoom', '16' ),
		'gdprConsent'         => (bool) oli_sl_get_option( 'gdpr_consent', '0' ),
		'featuredColor'       => oli_sl_get_option( 'featured_color', '#c45c26' ),
		'showCategories'      => (bool) oli_sl_get_option( 'show_categories_in_results', '1' ),
		'infowindowBg'        => oli_sl_get_option( 'infowindow_bg', '#ffffff' ),
		'infowindowText'      => oli_sl_get_option( 'infowindow_text_color', '#333333' ),
		'infowindowRadius'    => oli_sl_get_option( 'infowindow_radius', '8' ),
		'infowindowShadow'    => (bool) oli_sl_get_option( 'infowindow_shadow', '1' ),
		'resultsTemplate'     => oli_sl_get_option( 'results_template', '' ),
		'infowindowTemplate'  => oli_sl_get_option( 'infowindow_template', '' ),
		'queryAddress'        => isset( $_GET['oli_sl_address'] ) ? sanitize_text_field( wp_unslash( $_GET['oli_sl_address'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public locator deep-link.
		'queryName'           => isset( $_GET['oli_sl_name'] ) ? sanitize_text_field( wp_unslash( $_GET['oli_sl_name'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public locator deep-link.
		'labels'              => array(
			'search'        => oli_sl_get_label( 'label_search', __( 'Search', 'oli-store-locator' ) ),
			'address'       => oli_sl_get_label( 'label_address', __( 'Address or ZIP', 'oli-store-locator' ) ),
			'yourLocation'  => oli_sl_get_label( 'label_your_location', __( 'Your location', 'oli-store-locator' ) ),
			'storeName'     => oli_sl_get_label( 'label_store_name', __( 'Store name', 'oli-store-locator' ) ),
			'radius'        => oli_sl_get_label( 'label_radius', __( 'Search radius', 'oli-store-locator' ) ),
			'results'       => oli_sl_get_label( 'label_results', __( 'Results', 'oli-store-locator' ) ),
			'category'      => oli_sl_get_label( 'label_category', __( 'Category filter', 'oli-store-locator' ) ),
			'categoryFirst' => oli_sl_get_label( 'label_category_first', __( 'All categories', 'oli-store-locator' ) ),
			'locate'        => oli_sl_get_label( 'label_locate', __( 'Use my location', 'oli-store-locator' ) ),
			'reset'         => oli_sl_get_label( 'label_reset', __( 'Reset', 'oli-store-locator' ) ),
			'directions'    => oli_sl_get_label( 'label_get_directions', oli_sl_get_label( 'label_directions', __( 'Get directions', 'oli-store-locator' ) ) ),
			'noResults'     => oli_sl_get_label( 'label_no_results', __( 'No results found', 'oli-store-locator' ) ),
			'adjustSearch'  => oli_sl_get_label( 'label_adjust_search', __( 'Please adjust your search', 'oli-store-locator' ) ),
			'phone'         => oli_sl_get_label( 'label_phone', __( 'Phone', 'oli-store-locator' ) ),
			'fax'           => oli_sl_get_label( 'label_fax', __( 'Fax', 'oli-store-locator' ) ),
			'email'         => oli_sl_get_label( 'label_email', __( 'Email', 'oli-store-locator' ) ),
			'hours'         => oli_sl_get_label( 'label_hours', __( 'Hours', 'oli-store-locator' ) ),
			'website'       => oli_sl_get_label( 'label_website', oli_sl_get_label( 'label_url', __( 'Website', 'oli-store-locator' ) ) ),
			'moreInfo'      => oli_sl_get_label( 'label_more_info', __( 'More info', 'oli-store-locator' ) ),
			'moreDetails'   => oli_sl_get_label( 'label_more_details', __( 'More details', 'oli-store-locator' ) ),
			'startLocation' => oli_sl_get_label( 'label_start_location', __( 'Start location', 'oli-store-locator' ) ),
			'streetView'    => oli_sl_get_label( 'label_street_view', __( 'Street view', 'oli-store-locator' ) ),
			'zoomHere'      => oli_sl_get_label( 'label_zoom_here', __( 'Zoom here', 'oli-store-locator' ) ),
			'numberResults' => oli_sl_get_label( 'label_number_results', __( '{number} results', 'oli-store-locator' ) ),
			'numberResult'  => oli_sl_get_label( 'label_number_results_one', __( '{number} result', 'oli-store-locator' ) ),
			'open'          => oli_sl_get_label( 'label_open', __( 'Open', 'oli-store-locator' ) ),
			'closed'        => oli_sl_get_label( 'label_closed', __( 'Closed', 'oli-store-locator' ) ),
			'consent'       => oli_sl_get_label( 'label_consent', __( 'Load the map', 'oli-store-locator' ) ),
			'consentText'   => oli_sl_get_option( 'gdpr_text', '' ) ? oli_sl_get_option( 'gdpr_text' ) : __( 'This map loads tiles from an external map service. Accept to display the map.', 'oli-store-locator' ),
			'km'            => __( 'km', 'oli-store-locator' ),
			'mi'            => __( 'mi', 'oli-store-locator' ),
			'loading'       => oli_sl_get_label( 'label_preloader', __( 'Searching…', 'oli-store-locator' ) ),
			'geoDenied'     => __( 'Location permission was denied.', 'oli-store-locator' ),
		),
	);

	/**
	 * Filter frontend map/search config.
	 *
	 * @param array $config Config array.
	 */
	return apply_filters( 'oli_sl_map_config', $config );
}

/**
 * Whether a stored option value is enabled.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function oli_sl_is_enabled( $value ) {
	return in_array( (string) $value, array( '1', 'true', 'yes', 'on' ), true );
}

/**
 * Settings checkbox keys that stay stored as 1/0.
 *
 * @return array
 */
function oli_sl_checkbox_option_keys() {
	return array(
		'scroll_wheel',
		'map_controls',
		'street_view',
		'auto_locate',
		'enable_autocomplete',
		'enable_directions',
		'in_map_directions',
		'marker_clustering',
		'fit_bounds',
		'search_on_load',
		'draw_radius',
		'enable_single_pages',
		'enable_caching',
		'nominatim_rate_limit',
		'sheets_sync_enabled',
		'autosubmit_autocomplete',
		'force_postalcode',
		'hide_map_until_search',
		'hide_results_list',
		'show_radius_dropdown',
		'show_results_dropdown',
		'category_filter',
		'all_categories_required',
		'category_filter_only',
		'enforce_borders',
		'full_search',
		'show_number_results',
		'find_nearest',
		'open_links_new_window',
		'show_reset_map',
		'direction_redirect',
		'clickable_contact',
		'hide_closed_hours',
		'show_hour_status',
		'expand_hours',
		'clickable_store_name',
		'clickable_phone',
		'show_streetview_link',
		'show_zoom_here',
		'infowindow_thumbnail',
		'mouse_focus',
		'hide_country',
		'hide_distance',
		'address_opens_infowindow',
		'hide_start_marker',
		'start_marker_on_top',
		'cluster_exclude_start',
		'editor_hide_hours',
		'editor_hide_phone',
		'editor_hide_fax',
		'editor_hide_email',
		'editor_hide_url',
		'permalink_remove_front',
		'permalink_category',
		'gdpr_consent',
		'debug_mode',
		'admin_bar_menu',
		'compatibility_mode',
		'show_categories_in_results',
		'infowindow_shadow',
	);
}

/**
 * Color option keys.
 *
 * @return array
 */
function oli_sl_color_option_keys() {
	return array(
		'primary_color',
		'button_color',
		'button_text_color',
		'text_color',
		'hover_color',
		'cluster_color',
		'cluster_text_color',
		'featured_color',
		'infowindow_bg',
		'infowindow_text_color',
	);
}

/**
 * Frontend label catalog used by the Labels settings screen.
 *
 * @return array
 */
function oli_sl_label_catalog() {
	return array(
		'label_your_location'      => __( 'Your location', 'oli-store-locator' ),
		'label_store_name'         => __( 'Store name', 'oli-store-locator' ),
		'label_address'            => __( 'Address field', 'oli-store-locator' ),
		'label_radius'             => __( 'Search radius', 'oli-store-locator' ),
		'label_results'            => __( 'Results', 'oli-store-locator' ),
		'label_category'           => __( 'Category filter', 'oli-store-locator' ),
		'label_category_first'     => __( 'Category first item', 'oli-store-locator' ),
		'label_search'             => __( 'Search button', 'oli-store-locator' ),
		'label_locate'             => __( 'Use my location', 'oli-store-locator' ),
		'label_reset'              => __( 'Reset', 'oli-store-locator' ),
		'label_no_results'         => __( 'No results found', 'oli-store-locator' ),
		'label_adjust_search'      => __( 'Please adjust your search', 'oli-store-locator' ),
		'label_preloader'          => __( 'Searching (preloader text)', 'oli-store-locator' ),
		'label_more_info'          => __( 'More info', 'oli-store-locator' ),
		'label_phone'              => __( 'Phone', 'oli-store-locator' ),
		'label_fax'                => __( 'Fax', 'oli-store-locator' ),
		'label_email'              => __( 'Email', 'oli-store-locator' ),
		'label_website'            => __( 'Website', 'oli-store-locator' ),
		'label_url'                => __( 'URL', 'oli-store-locator' ),
		'label_more_details'       => __( 'More details', 'oli-store-locator' ),
		'label_hours'              => __( 'Hours', 'oli-store-locator' ),
		'label_start_location'     => __( 'Start location', 'oli-store-locator' ),
		'label_directions'         => __( 'Directions', 'oli-store-locator' ),
		'label_get_directions'     => __( 'Get directions', 'oli-store-locator' ),
		'label_street_view'        => __( 'Street view', 'oli-store-locator' ),
		'label_zoom_here'          => __( 'Zoom here', 'oli-store-locator' ),
		'label_number_results'     => __( 'Number of results', 'oli-store-locator' ),
		'label_number_results_one' => __( 'Number of results (single)', 'oli-store-locator' ),
		'label_open'               => __( 'Open', 'oli-store-locator' ),
		'label_closed'             => __( 'Closed', 'oli-store-locator' ),
		'label_consent'            => __( 'Load the map', 'oli-store-locator' ),
	);
}

/**
 * Common ISO country names for the store editor default.
 *
 * @return array
 */
function oli_sl_country_choices() {
	$countries = array(
		''               => __( 'No default', 'oli-store-locator' ),
		'Canada'         => __( 'Canada', 'oli-store-locator' ),
		'United States'  => __( 'United States', 'oli-store-locator' ),
		'France'         => __( 'France', 'oli-store-locator' ),
		'United Kingdom' => __( 'United Kingdom', 'oli-store-locator' ),
		'Belgium'        => __( 'Belgium', 'oli-store-locator' ),
		'Switzerland'    => __( 'Switzerland', 'oli-store-locator' ),
		'Germany'        => __( 'Germany', 'oli-store-locator' ),
		'Spain'          => __( 'Spain', 'oli-store-locator' ),
		'Italy'          => __( 'Italy', 'oli-store-locator' ),
		'Australia'      => __( 'Australia', 'oli-store-locator' ),
		'Mexico'         => __( 'Mexico', 'oli-store-locator' ),
	);

	/**
	 * Filter the country list used in settings and the store editor.
	 *
	 * @param array $countries Country names keyed by stored value.
	 */
	return apply_filters( 'oli_sl_country_choices', $countries );
}

/**
 * Render a checkbox that always posts 0/1.
 *
 * @param string $key   Option key.
 * @param string $label Label.
 */
function oli_sl_checkbox_field( $key, $label ) {
	printf(
		'<label><input type="hidden" name="oli_sl_options[%1$s]" value="0" /><input type="checkbox" name="oli_sl_options[%1$s]" value="1" %2$s /> %3$s</label>',
		esc_attr( $key ),
		checked( oli_sl_get_option( $key ), '1', false ),
		esc_html( $label )
	);
}

/**
 * Sanitize a latitude or longitude string.
 *
 * @param mixed $value Raw value.
 * @return string Empty string or a numeric coordinate.
 */
function oli_sl_sanitize_coordinate( $value ) {
	$value = sanitize_text_field( (string) $value );
	if ( '' === $value || ! is_numeric( $value ) ) {
		return '';
	}

	return (string) (float) $value;
}

/**
 * Copy an uploaded-file array into sanitized fields.
 *
 * @param mixed $file Value from $_FILES.
 * @return array
 */
function oli_sl_sanitize_uploaded_file( $file ) {
	if ( ! is_array( $file ) ) {
		return array();
	}

	$tmp = isset( $file['tmp_name'] ) ? $file['tmp_name'] : '';

	return array(
		'name'     => isset( $file['name'] ) ? sanitize_file_name( wp_unslash( $file['name'] ) ) : '',
		'type'     => isset( $file['type'] ) ? sanitize_text_field( wp_unslash( $file['type'] ) ) : '',
		'tmp_name' => is_string( $tmp ) ? $tmp : '',
		'error'    => isset( $file['error'] ) ? absint( $file['error'] ) : UPLOAD_ERR_NO_FILE,
		'size'     => isset( $file['size'] ) ? absint( $file['size'] ) : 0,
	);
}
