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
	 * Settings tabs in the same order as the reference store locator.
	 *
	 * @return array
	 */
	public static function tabs() {
		return array(
			'api'     => __( 'API', 'oli-store-locator' ),
			'search'  => __( 'Search', 'oli-store-locator' ),
			'map'     => __( 'Map', 'oli-store-locator' ),
			'ux'      => __( 'User Experience', 'oli-store-locator' ),
			'markers' => __( 'Markers', 'oli-store-locator' ),
			'editor'  => __( 'Store Editor', 'oli-store-locator' ),
			'local'   => __( 'Local Pages', 'oli-store-locator' ),
			'labels'  => __( 'Labels', 'oli-store-locator' ),
			'tools'   => __( 'Tools', 'oli-store-locator' ),
			'design'  => __( 'Design', 'oli-store-locator' ),
			'import'  => __( 'Import / Export', 'oli-store-locator' ),
			'sync'    => __( 'Google Sheets', 'oli-store-locator' ),
		);
	}

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
			'search_method',
			'input_placeholder',
			'sort_by',
			'sort_order',
			'category_default',
			'api_language',
			'region_restriction_type',
			'bias_country',
			'restrict_countries',
			'marker_effect',
			'cluster_style',
			'editor_default_country',
			'editor_map_type',
			'editor_default_hours',
			'infowindow_radius',
		);

		foreach ( $text_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		$textarea_keys = array_merge(
			array(
				'map_style_json',
				'custom_css',
				'infowindow_template',
				'results_template',
				'marker_template',
				'gdpr_text',
			),
			array_keys( oli_sl_label_catalog() )
		);

		foreach ( $textarea_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = sanitize_textarea_field( $input[ $key ] );
			}
		}

		$url_keys = array( 'leaflet_custom_url', 'default_marker' );
		foreach ( $url_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = esc_url_raw( $input[ $key ] );
			}
		}

		if ( isset( $input['sheets_url'] ) ) {
			$sheets_url = esc_url_raw( $input['sheets_url'] );
			if ( '' !== $sheets_url && ! Oli_SL_CSV_Parser::is_valid_sheets_url( $sheets_url ) ) {
				add_settings_error(
					'oli_sl_options',
					'invalid_sheets_url',
					__( 'The Google Sheet URL was not saved. Use a https://docs.google.com/spreadsheets/d/… link or a published CSV export URL.', 'oli-store-locator' )
				);
				$sheets_url = isset( $existing['sheets_url'] ) ? $existing['sheets_url'] : '';
			}
			$out['sheets_url'] = $sheets_url;
		}

		foreach ( array( 'google_api_key', 'google_server_key', 'sheets_api_key' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		$int_keys = array(
			'start_zoom',
			'max_results',
			'search_radius',
			'cache_duration',
			'sheets_batch_size',
			'max_auto_zoom',
			'cluster_max_zoom',
			'cluster_size',
			'sync_log_retention_days',
		);
		foreach ( $int_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = (string) absint( $input[ $key ] );
			}
		}

		foreach ( array( 'start_lat', 'start_lng', 'map_height', 'map_width' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		foreach ( oli_sl_color_option_keys() as $color ) {
			if ( ! isset( $input[ $color ] ) ) {
				continue;
			}
			$value         = sanitize_hex_color( $input[ $color ] );
			$out[ $color ] = $value ? $value : $defaults[ $color ];
		}

		foreach ( oli_sl_checkbox_option_keys() as $check ) {
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
			$existing_map             = oli_sl_get_option( 'sheets_column_map', array() );
			$out['sheets_column_map'] = is_array( $existing_map ) ? $existing_map : array();
		}

		if ( ! empty( $out['map_style_json'] ) ) {
			$decoded = json_decode( $out['map_style_json'] );
			if ( null === $decoded && JSON_ERROR_NONE !== json_last_error() ) {
				add_settings_error( 'oli_sl_options', 'invalid_json', __( 'Custom map style JSON is invalid and was kept as entered. Fix it to apply custom styles.', 'oli-store-locator' ) );
			}
		}

		$previous_slug = isset( $existing['store_slug'] ) ? $existing['store_slug'] : '';
		if ( isset( $input['store_slug'] ) && $previous_slug !== $out['store_slug'] ) {
			set_transient( 'oli_sl_flush_rewrites', '1', MINUTE_IN_SECONDS );
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

		if ( '1' === (string) get_transient( 'oli_sl_flush_rewrites' ) ) {
			flush_rewrite_rules();
			delete_transient( 'oli_sl_flush_rewrites' );
		}

		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'api'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab switch only.
		$tabs = self::tabs();

		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'api';
		}

		include OLI_SL_PLUGIN_DIR . 'admin/views/settings.php';
	}
}
