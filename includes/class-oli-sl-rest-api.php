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
						'sanitize_callback' => 'oli_sl_sanitize_latitude',
					),
					'lng'         => array(
						'required'          => false,
						'sanitize_callback' => 'oli_sl_sanitize_longitude',
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
					'name'        => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
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
	 * Restrict /geocode to users who can manage options or the dedicated geocode cap.
	 *
	 * A REST nonce alone is not enough. Visitors geocode through public search.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function geocode_permissions( $request ) {
		unset( $request );
		if ( self::can_access_geocode() ) {
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
	 * Dedicated capability granted to administrators by default.
	 *
	 * @return string
	 */
	public static function geocode_capability() {
		return 'oli_sl_geocode';
	}

	/**
	 * Whether the current user may call /geocode.
	 *
	 * @param object|null $request Unused. Kept so existing callers stay valid.
	 * @return bool
	 */
	public static function can_access_geocode( $request = null ) {
		unset( $request );
		if ( ! function_exists( 'current_user_can' ) ) {
			return false;
		}

		return current_user_can( 'manage_options' ) || current_user_can( self::geocode_capability() );
	}

	/**
	 * Grant the dedicated geocode capability to administrators.
	 */
	public static function grant_geocode_capability() {
		if ( ! function_exists( 'get_role' ) ) {
			return;
		}

		$role = get_role( 'administrator' );
		if ( $role && ! $role->has_cap( self::geocode_capability() ) ) {
			$role->add_cap( self::geocode_capability() );
		}
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
		$name    = $request->get_param( 'name' );

		if ( 'name' !== oli_sl_get_option( 'search_method', 'geocode' ) && ! empty( $address ) && ( empty( $lat ) || empty( $lng ) ) ) {
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
				'name'        => $name ? $name : $address,
				'search'      => $address ? $address : $name,
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
