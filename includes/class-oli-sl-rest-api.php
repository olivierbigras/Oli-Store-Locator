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
				'permission_callback' => array( $this, 'geocode_permissions' ),
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
	 * Restrict /geocode to administrators or a valid wp_rest nonce.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function geocode_permissions( $request ) {
		if ( self::can_access_geocode( $request ) ) {
			return true;
		}

		$status = function_exists( 'rest_authorization_required_code' ) ? rest_authorization_required_code() : 401;

		return new WP_Error(
			'rest_forbidden',
			__( 'Sorry, you are not allowed to geocode addresses.', 'oli-store-locator' ),
			array( 'status' => $status )
		);
	}

	/**
	 * Whether a REST request may call /geocode.
	 *
	 * @param object|null $request Optional request with get_header()/get_param().
	 * @return bool
	 */
	public static function can_access_geocode( $request = null ) {
		if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			return true;
		}

		$nonce = '';
		if ( is_object( $request ) && method_exists( $request, 'get_header' ) ) {
			$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		}
		if ( '' === $nonce && is_object( $request ) && method_exists( $request, 'get_param' ) ) {
			$nonce = (string) $request->get_param( '_wpnonce' );
		}

		return ( '' !== $nonce && function_exists( 'wp_verify_nonce' ) && wp_verify_nonce( $nonce, 'wp_rest' ) );
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
			$geocoded = Oli_SL_Geocoding::geocode(
				$address,
				array(
					'allow_sleep'      => false,
					'enforce_ip_limit' => true,
				)
			);
			if ( is_wp_error( $geocoded ) ) {
				$data   = $geocoded->get_error_data();
				$status = ( is_array( $data ) && isset( $data['status'] ) ) ? (int) $data['status'] : 400;
				return new WP_Error( $geocoded->get_error_code(), $geocoded->get_error_message(), array( 'status' => $status ) );
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
		$result  = Oli_SL_Geocoding::geocode(
			$address,
			array(
				'allow_sleep'      => true,
				'enforce_ip_limit' => false,
			)
		);

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'geocoding_failed', $result->get_error_message(), array( 'status' => 400 ) );
		}

		return rest_ensure_response( $result );
	}
}
