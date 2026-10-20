<?php
/**
 * REST API endpoints.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API endpoints.
 */
class Oli_SL_Rest_API {

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			'oli-store-locator/v1',
			'/search',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'search_stores' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'lat'         => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'lng'         => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'address'     => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'radius'      => array(
						'required'          => false,
						'default'           => (int) oli_sl_get_option( 'search_radius', '50' ),
						'sanitize_callback' => 'absint',
					),
					'max_results' => array(
						'required'          => false,
						'default'           => (int) oli_sl_get_option( 'max_results', '25' ),
						'sanitize_callback' => 'absint',
					),
					'category'    => array(
						'required'          => false,
						'sanitize_callback' => array( $this, 'sanitize_category_param' ),
					),
				),
			)
		);

		register_rest_route(
			'oli-store-locator/v1',
			'/geocode',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'geocode_address' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'address' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Sanitize category query param (single ID or comma-separated).
	 *
	 * @param mixed $value Raw value.
	 * @return array
	 */
	public function sanitize_category_param( $value ) {
		if ( is_array( $value ) ) {
			return array_filter( array_map( 'absint', $value ) );
		}
		$parts = array_map( 'trim', explode( ',', (string) $value ) );
		return array_filter( array_map( 'absint', $parts ) );
	}

	/**
	 * REST API search stores callback.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function search_stores( $request ) {
		$lat     = $request->get_param( 'lat' );
		$lng     = $request->get_param( 'lng' );
		$address = $request->get_param( 'address' );

		if ( ! empty( $address ) && ( empty( $lat ) || empty( $lng ) ) ) {
			$geocoded = Oli_SL_Geocoding::geocode( $address );
			if ( is_wp_error( $geocoded ) ) {
				return new WP_Error( 'geocoding_failed', $geocoded->get_error_message(), array( 'status' => 400 ) );
			}
			$lat = $geocoded['lat'];
			$lng = $geocoded['lng'];
		}

		$results = Oli_SL_Search::search_stores(
			array(
				'lat'         => $lat,
				'lng'         => $lng,
				'radius'      => $request->get_param( 'radius' ),
				'max_results' => $request->get_param( 'max_results' ),
				'category'    => $request->get_param( 'category' ),
			)
		);

		return rest_ensure_response(
			array(
				'stores'       => $results,
				'center_lat'   => $lat,
				'center_lng'   => $lng,
				'total_stores' => count( $results ),
			)
		);
	}

	/**
	 * REST API geocode address callback.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function geocode_address( $request ) {
		$address = $request->get_param( 'address' );
		$result  = Oli_SL_Geocoding::geocode( $address );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'geocoding_failed', $result->get_error_message(), array( 'status' => 400 ) );
		}

		return rest_ensure_response( $result );
	}
}
