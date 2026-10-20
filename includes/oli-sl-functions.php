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
		'map_provider'          => 'leaflet',
		'geocoding_provider'    => 'nominatim',
		'google_api_key'        => '',
		'google_server_key'     => '',
		'sheets_api_key'        => '',
		'distance_unit'         => 'km',
		'start_lat'             => '45.5017',
		'start_lng'             => '-73.5673',
		'start_zoom'            => '10',
		'max_results'           => '25',
		'max_results_options'   => '10,25,50,100',
		'search_radius'         => '50',
		'radius_options'        => '10,25,50,100,250,500',
		'map_type'              => 'roadmap',
		'scroll_wheel'          => '1',
		'map_controls'          => '1',
		'street_view'           => '1',
		'auto_locate'           => '1',
		'enable_autocomplete'   => '1',
		'enable_directions'     => '1',
		'in_map_directions'     => '1',
		'marker_clustering'     => '1',
		'fit_bounds'            => '1',
		'search_on_load'        => '1',
		'draw_radius'           => '1',
		'list_layout'           => 'below',
		'mobile_layout'         => 'map_first',
		'map_height'            => '500',
		'map_width'             => '100%',
		'map_style_preset'      => 'default',
		'map_style_json'        => '',
		'leaflet_tiles'         => 'osm', // Stored OpenStreetMap tile key; do not rename.
		'leaflet_custom_url'    => '',
		'leaflet_attribution'   => '',
		'primary_color'         => '#0073aa',
		'button_color'          => '#0073aa',
		'button_text_color'     => '#ffffff',
		'text_color'            => '#333333',
		'hover_color'           => '#f0f0f0',
		'cluster_color'         => '#0073aa',
		'cluster_text_color'    => '#ffffff',
		'default_marker'        => '',
		'custom_css'            => '',
		'category_filter_type'  => 'dropdown',
		'hours_format'          => 'textarea',
		'enable_single_pages'   => '1',
		'store_slug'            => 'store',
		'enable_caching'        => '1',
		'cache_duration'        => '3600',
		'nominatim_rate_limit'  => '1',
		'infowindow_template'   => '',
		'results_template'      => '',
		'marker_template'       => '',
		'sheets_url'            => '',
		'sheets_sync_enabled'   => '0',
		'sheets_sync_frequency' => 'hourly',
		'sheets_removed_action' => 'none',
		'sheets_batch_size'     => '25',
		'sheets_column_map'     => array(),
		'label_search'          => '',
		'label_address'         => '',
		'label_radius'          => '',
		'label_results'         => '',
		'label_category'        => '',
		'label_locate'          => '',
		'label_reset'           => '',
		'label_directions'      => '',
		'label_no_results'      => '',
		'label_phone'           => '',
		'label_hours'           => '',
		'label_website'         => '',
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
		'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
		'restUrl'           => esc_url_raw( rest_url( 'oli-store-locator/v1/' ) ),
		'nonce'             => wp_create_nonce( 'oli_sl_nonce' ),
		'provider'          => oli_sl_get_option( 'map_provider', 'leaflet' ),
		'geocodingProvider' => oli_sl_get_option( 'geocoding_provider', 'nominatim' ),
		'googleKey'         => oli_sl_get_option( 'google_api_key', '' ),
		'unit'              => oli_sl_get_option( 'distance_unit', 'km' ),
		'startLat'          => (float) oli_sl_get_option( 'start_lat', '45.5017' ),
		'startLng'          => (float) oli_sl_get_option( 'start_lng', '-73.5673' ),
		'startZoom'         => (int) oli_sl_get_option( 'start_zoom', '10' ),
		'mapType'           => oli_sl_get_option( 'map_type', 'roadmap' ),
		'scrollWheel'       => (bool) oli_sl_get_option( 'scroll_wheel', '1' ),
		'mapControls'       => (bool) oli_sl_get_option( 'map_controls', '1' ),
		'streetView'        => (bool) oli_sl_get_option( 'street_view', '1' ),
		'autoLocate'        => (bool) oli_sl_get_option( 'auto_locate', '1' ),
		'autocomplete'      => (bool) oli_sl_get_option( 'enable_autocomplete', '1' ),
		'directions'        => (bool) oli_sl_get_option( 'enable_directions', '1' ),
		'inMapDirections'   => (bool) oli_sl_get_option( 'in_map_directions', '1' ),
		'clustering'        => (bool) oli_sl_get_option( 'marker_clustering', '1' ),
		'fitBounds'         => (bool) oli_sl_get_option( 'fit_bounds', '1' ),
		'searchOnLoad'      => (bool) oli_sl_get_option( 'search_on_load', '1' ),
		'drawRadius'        => (bool) oli_sl_get_option( 'draw_radius', '1' ),
		'mapStylePreset'    => oli_sl_get_option( 'map_style_preset', 'default' ),
		'mapStyleJson'      => oli_sl_get_option( 'map_style_json', '' ),
		'leafletTiles'      => oli_sl_get_option( 'leaflet_tiles', 'osm' ),
		'leafletCustomUrl'  => oli_sl_get_option( 'leaflet_custom_url', '' ),
		'defaultMarker'     => oli_sl_get_option( 'default_marker', '' ),
		'clusterColor'      => oli_sl_get_option( 'cluster_color', '#0073aa' ),
		'clusterTextColor'  => oli_sl_get_option( 'cluster_text_color', '#ffffff' ),
		'enableSingle'      => (bool) oli_sl_get_option( 'enable_single_pages', '1' ),
		'tileProviders'     => Oli_SL_Map_Styles::leaflet_providers(),
		'googleStyles'      => Oli_SL_Map_Styles::google_presets(),
		'labels'            => array(
			'search'      => oli_sl_get_label( 'label_search', __( 'Search', 'oli-store-locator' ) ),
			'address'     => oli_sl_get_label( 'label_address', __( 'Address or ZIP', 'oli-store-locator' ) ),
			'radius'      => oli_sl_get_label( 'label_radius', __( 'Radius', 'oli-store-locator' ) ),
			'results'     => oli_sl_get_label( 'label_results', __( 'Results', 'oli-store-locator' ) ),
			'category'    => oli_sl_get_label( 'label_category', __( 'Category', 'oli-store-locator' ) ),
			'locate'      => oli_sl_get_label( 'label_locate', __( 'Use my location', 'oli-store-locator' ) ),
			'reset'       => oli_sl_get_label( 'label_reset', __( 'Reset', 'oli-store-locator' ) ),
			'directions'  => oli_sl_get_label( 'label_directions', __( 'Directions', 'oli-store-locator' ) ),
			'noResults'   => oli_sl_get_label( 'label_no_results', __( 'No stores found.', 'oli-store-locator' ) ),
			'phone'       => oli_sl_get_label( 'label_phone', __( 'Phone', 'oli-store-locator' ) ),
			'hours'       => oli_sl_get_label( 'label_hours', __( 'Hours', 'oli-store-locator' ) ),
			'website'     => oli_sl_get_label( 'label_website', __( 'Website', 'oli-store-locator' ) ),
			'km'          => __( 'km', 'oli-store-locator' ),
			'mi'          => __( 'mi', 'oli-store-locator' ),
			'loading'     => __( 'Searching…', 'oli-store-locator' ),
			'geoDenied'   => __( 'Location permission was denied.', 'oli-store-locator' ),
			'moreDetails' => __( 'More details', 'oli-store-locator' ),
		),
	);

	/**
	 * Filter frontend map/search config.
	 *
	 * @param array $config Config array.
	 */
	return apply_filters( 'oli_sl_map_config', $config );
}
