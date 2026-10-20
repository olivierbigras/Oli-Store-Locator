<?php
/**
 * Shortcodes and Gutenberg blocks.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes and Gutenberg blocks.
 */
class Oli_SL_Shortcodes {

	/**
	 * Register shortcodes.
	 */
	public function register_shortcodes() {
		add_shortcode( 'oli_store_locator', array( $this, 'store_locator_shortcode' ) );
		add_shortcode( 'oli_store_address', array( $this, 'store_address_shortcode' ) );
		add_shortcode( 'oli_store_hours', array( $this, 'store_hours_shortcode' ) );
		add_shortcode( 'oli_store_map', array( $this, 'store_map_shortcode' ) );
	}

	/**
	 * Register Gutenberg blocks.
	 */
	public function register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'oli-sl-blocks',
			OLI_SL_PLUGIN_URL . 'assets/js/oli-sl-blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
			OLI_SL_VERSION,
			true
		);

		register_block_type(
			'oli-store-locator/store-locator',
			array(
				'api_version'     => 2,
				'editor_script'   => 'oli-sl-blocks',
				'render_callback' => array( $this, 'store_locator_shortcode' ),
				'attributes'      => array(
					'radius'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'max_results' => array(
						'type'    => 'string',
						'default' => '',
					),
					'category'    => array(
						'type'    => 'string',
						'default' => '',
					),
					'height'      => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);

		register_block_type(
			'oli-store-locator/store-address',
			array(
				'api_version'     => 2,
				'editor_script'   => 'oli-sl-blocks',
				'render_callback' => array( $this, 'store_address_shortcode' ),
				'attributes'      => array(
					'id' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);

		register_block_type(
			'oli-store-locator/store-hours',
			array(
				'api_version'     => 2,
				'editor_script'   => 'oli-sl-blocks',
				'render_callback' => array( $this, 'store_hours_shortcode' ),
				'attributes'      => array(
					'id' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);

		register_block_type(
			'oli-store-locator/store-map',
			array(
				'api_version'     => 2,
				'editor_script'   => 'oli-sl-blocks',
				'render_callback' => array( $this, 'store_map_shortcode' ),
				'attributes'      => array(
					'id'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'height' => array(
						'type'    => 'string',
						'default' => '400px',
					),
				),
			)
		);
	}

	/**
	 * Store locator shortcode / block.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function store_locator_shortcode( $atts ) {
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		$atts = shortcode_atts(
			array(
				'radius'      => oli_sl_get_option( 'search_radius', '50' ),
				'max_results' => oli_sl_get_option( 'max_results', '25' ),
				'category'    => '',
				'height'      => oli_sl_get_option( 'map_height', '500' ),
				'width'       => oli_sl_get_option( 'map_width', '100%' ),
			),
			$atts,
			'oli_store_locator'
		);

		Oli_SL_Public::enqueue_locator_assets();

		ob_start();
		$template = oli_sl_locate_template( 'store-locator.php' );
		if ( file_exists( $template ) ) {
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * Store address shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function store_address_shortcode( $atts ) {
		global $post;

		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		$atts = shortcode_atts(
			array(
				'id' => $post ? $post->ID : 0,
			),
			$atts,
			'oli_store_address'
		);

		$store_id = absint( $atts['id'] );

		if ( ! $store_id || 'oli_store' !== get_post_type( $store_id ) ) {
			return '';
		}

		$address = oli_sl_get_store_meta( $store_id, 'address' );
		$city    = oli_sl_get_store_meta( $store_id, 'city' );
		$state   = oli_sl_get_store_meta( $store_id, 'state' );
		$zip     = oli_sl_get_store_meta( $store_id, 'zip' );
		$country = oli_sl_get_store_meta( $store_id, 'country' );

		$output = '<div class="oli-sl-address">';
		if ( $address ) {
			$output .= '<div class="oli-sl-address-street">' . esc_html( $address ) . '</div>';
		}
		$location_parts = array_filter( array( $city, $state, $zip ) );
		if ( ! empty( $location_parts ) ) {
			$output .= '<div class="oli-sl-address-location">' . esc_html( implode( ', ', $location_parts ) ) . '</div>';
		}
		if ( $country ) {
			$output .= '<div class="oli-sl-address-country">' . esc_html( $country ) . '</div>';
		}
		$output .= '</div>';

		return $output;
	}

	/**
	 * Store hours shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function store_hours_shortcode( $atts ) {
		global $post;

		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		$atts = shortcode_atts(
			array(
				'id' => $post ? $post->ID : 0,
			),
			$atts,
			'oli_store_hours'
		);

		$store_id = absint( $atts['id'] );

		if ( ! $store_id || 'oli_store' !== get_post_type( $store_id ) ) {
			return '';
		}

		$hours = oli_sl_get_store_meta( $store_id, 'hours' );

		if ( empty( $hours ) ) {
			return '';
		}

		return '<div class="oli-sl-hours">' . wp_kses_post( wpautop( $hours ) ) . '</div>';
	}

	/**
	 * Store map shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function store_map_shortcode( $atts ) {
		global $post;

		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		$atts = shortcode_atts(
			array(
				'id'     => $post ? $post->ID : 0,
				'height' => '400px',
				'width'  => '100%',
			),
			$atts,
			'oli_store_map'
		);

		$store_id = absint( $atts['id'] );

		if ( ! $store_id || 'oli_store' !== get_post_type( $store_id ) ) {
			return '';
		}

		$lat = oli_sl_get_store_meta( $store_id, 'latitude' );
		$lng = oli_sl_get_store_meta( $store_id, 'longitude' );

		if ( '' === $lat || '' === $lng ) {
			return '';
		}

		Oli_SL_Public::enqueue_locator_assets();

		$map_id = 'oli-sl-map-' . $store_id;

		return sprintf(
			'<div id="%1$s" class="oli-sl-single-map" style="height: %2$s; width: %3$s;" data-lat="%4$s" data-lng="%5$s" data-store-id="%6$d"></div>',
			esc_attr( $map_id ),
			esc_attr( $atts['height'] ),
			esc_attr( $atts['width'] ),
			esc_attr( $lat ),
			esc_attr( $lng ),
			(int) $store_id
		);
	}
}
