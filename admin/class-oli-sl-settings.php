<?php
/**
 * Plugin settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin settings.
 */
class Oli_SL_Settings {

	/**
	 * Add admin menu pages.
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=oli_store',
			__( 'Store Locator Settings', 'oli-store-locator' ),
			__( 'Settings', 'oli-store-locator' ),
			'manage_options',
			'oli-sl-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting(
			'oli_sl_settings_group',
			'oli_sl_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => oli_sl_default_options(),
			)
		);
	}

	/**
	 * Sanitize options array.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize_options( $input ) {
		$defaults = oli_sl_default_options();
		$existing = get_option( 'oli_sl_options', array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}
		$input = is_array( $input ) ? $input : array();
		$out   = wp_parse_args( $existing, $defaults );

		$text_keys = array(
			'map_provider',
			'geocoding_provider',
			'distance_unit',
			'map_type',
			'list_layout',
			'mobile_layout',
			'map_style_preset',
			'leaflet_tiles',
			'category_filter_type',
			'hours_format',
			'store_slug',
			'sheets_sync_frequency',
			'sheets_removed_action',
			'max_results_options',
			'radius_options',
			'leaflet_attribution',
		);

		foreach ( $text_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		$textarea_keys = array(
			'map_style_json',
			'custom_css',
			'infowindow_template',
			'results_template',
			'marker_template',
			'label_search',
			'label_address',
			'label_radius',
			'label_results',
			'label_category',
			'label_locate',
			'label_reset',
			'label_directions',
			'label_no_results',
			'label_phone',
			'label_hours',
			'label_website',
		);

		foreach ( $textarea_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = sanitize_textarea_field( $input[ $key ] );
			}
		}

		$url_keys = array( 'sheets_url', 'leaflet_custom_url', 'default_marker' );
		foreach ( $url_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = esc_url_raw( $input[ $key ] );
			}
		}

		if ( isset( $out['sheets_url'] ) && '' !== $out['sheets_url'] && ! Oli_SL_CSV_Parser::is_allowed_sheets_host( $out['sheets_url'] ) ) {
			$out['sheets_url'] = '';
		}

		foreach ( array( 'google_api_key', 'google_server_key', 'sheets_api_key' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		if ( isset( $input['start_lat'] ) ) {
			$out['start_lat'] = sanitize_text_field( $input['start_lat'] );
		}
		if ( isset( $input['start_lng'] ) ) {
			$out['start_lng'] = sanitize_text_field( $input['start_lng'] );
		}
		if ( isset( $input['start_zoom'] ) ) {
			$out['start_zoom'] = (string) absint( $input['start_zoom'] );
		}
		if ( isset( $input['max_results'] ) ) {
			$out['max_results'] = (string) absint( $input['max_results'] );
		}
		if ( isset( $input['search_radius'] ) ) {
			$out['search_radius'] = (string) absint( $input['search_radius'] );
		}
		if ( isset( $input['map_height'] ) ) {
			$out['map_height'] = sanitize_text_field( $input['map_height'] );
		}
		if ( isset( $input['map_width'] ) ) {
			$out['map_width'] = sanitize_text_field( $input['map_width'] );
		}
		if ( isset( $input['cache_duration'] ) ) {
			$out['cache_duration'] = (string) absint( $input['cache_duration'] );
		}
		if ( isset( $input['sheets_batch_size'] ) ) {
			$out['sheets_batch_size'] = (string) absint( $input['sheets_batch_size'] );
		}

		$colors = array( 'primary_color', 'button_color', 'button_text_color', 'text_color', 'hover_color', 'cluster_color', 'cluster_text_color' );
		foreach ( $colors as $color ) {
			if ( ! isset( $input[ $color ] ) ) {
				continue;
			}
			$value         = sanitize_hex_color( $input[ $color ] );
			$out[ $color ] = $value ? $value : $defaults[ $color ];
		}

		$checks = array(
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
		);

		foreach ( $checks as $check ) {
			if ( isset( $input[ $check ] ) ) {
				$out[ $check ] = ( '1' === (string) $input[ $check ] ) ? '1' : '0';
			}
		}

		if ( isset( $input['sheets_column_map'] ) && is_array( $input['sheets_column_map'] ) ) {
			$map = array();
			foreach ( $input['sheets_column_map'] as $field => $index ) {
				$field = sanitize_text_field( $field );
				if ( '' === $index || '-1' === (string) $index ) {
					continue;
				}
				$map[ $field ] = absint( $index );
			}
			$out['sheets_column_map'] = $map;
		} else {
			$existing                 = oli_sl_get_option( 'sheets_column_map', array() );
			$out['sheets_column_map'] = is_array( $existing ) ? $existing : array();
		}

		if ( ! empty( $out['map_style_json'] ) ) {
			$decoded = json_decode( $out['map_style_json'] );
			if ( null === $decoded && JSON_ERROR_NONE !== json_last_error() ) {
				add_settings_error( 'oli_sl_options', 'invalid_json', __( 'Custom map style JSON is invalid and was kept as entered. Fix it to apply custom styles.', 'oli-store-locator' ) );
			}
		}

		Oli_SL_Search::clear_cache();

		return $out;
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab switch only.
		$tabs = array(
			'general' => __( 'General', 'oli-store-locator' ),
			'map'     => __( 'Map', 'oli-store-locator' ),
			'search'  => __( 'Search', 'oli-store-locator' ),
			'design'  => __( 'Design', 'oli-store-locator' ),
			'labels'  => __( 'Labels', 'oli-store-locator' ),
			'import'  => __( 'Import / Export', 'oli-store-locator' ),
			'sync'    => __( 'Google Sheets', 'oli-store-locator' ),
		);

		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'general';
		}

		include OLI_SL_PLUGIN_DIR . 'admin/views/settings.php';
	}
}
