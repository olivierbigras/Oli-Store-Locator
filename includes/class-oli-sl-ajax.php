<?php
/**
 * AJAX handlers.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handlers.
 */
class Oli_SL_Ajax {

	/**
	 * Handle store search AJAX request.
	 */
	public function search_stores() {
		check_ajax_referer( 'oli_sl_nonce', 'nonce' );

		$lat         = isset( $_POST['lat'] ) ? sanitize_text_field( wp_unslash( $_POST['lat'] ) ) : '';
		$lng         = isset( $_POST['lng'] ) ? sanitize_text_field( wp_unslash( $_POST['lng'] ) ) : '';
		$address     = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$radius      = isset( $_POST['radius'] ) ? absint( $_POST['radius'] ) : (int) oli_sl_get_option( 'search_radius', '50' );
		$max_results = isset( $_POST['max_results'] ) ? absint( $_POST['max_results'] ) : (int) oli_sl_get_option( 'max_results', '25' );

		$category = array();
		if ( isset( $_POST['category'] ) ) {
			$raw = wp_unslash( $_POST['category'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
			if ( is_array( $raw ) ) {
				$category = array_map( 'absint', $raw );
			} elseif ( '' !== $raw ) {
				$category = array( absint( $raw ) );
			}
		}

		if ( oli_sl_is_enabled( oli_sl_get_option( 'force_postalcode', '0' ) ) && '' !== $address ) {
			$address = preg_replace( '/[^0-9A-Za-z\s-]/', '', $address );
		}

		$country = '';
		if ( 'restrict' === oli_sl_get_option( 'region_restriction_type', 'bias' ) ) {
			$country = oli_sl_get_option( 'restrict_countries', '' );
		} elseif ( oli_sl_get_option( 'bias_country' ) ) {
			$country = oli_sl_get_option( 'bias_country' );
		}

		if ( 'name' !== oli_sl_get_option( 'search_method', 'geocode' ) && ! empty( $address ) && ( '' === $lat || '' === $lng ) ) {
			$geocoded = Oli_SL_Geocoding::geocode(
				$address,
				array(
					'allow_sleep'      => false,
					'enforce_ip_limit' => true,
				)
			);
			if ( is_wp_error( $geocoded ) ) {
				self::send_geocode_error( $geocoded );
			}
			$lat = $geocoded['lat'];
			$lng = $geocoded['lng'];
		}

		$results = Oli_SL_Search::search_stores(
			array(
				'lat'         => $lat,
				'lng'         => $lng,
				'radius'      => $radius,
				'max_results' => $max_results,
				'category'    => $category,
				'name'        => $name ? $name : $address,
				'search'      => $address ? $address : $name,
				'country'     => $country,
			)
		);

		wp_send_json_success(
			array(
				'stores'       => $results,
				'center_lat'   => $lat,
				'center_lng'   => $lng,
				'total_stores' => count( $results ),
			)
		);
	}

	/**
	 * Address autocomplete.
	 */
	public function autocomplete() {
		check_ajax_referer( 'oli_sl_nonce', 'nonce' );

		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		if ( strlen( $query ) < 3 ) {
			wp_send_json_success( array() );
		}

		$result = Oli_SL_Geocoding::autocomplete(
			$query,
			array(
				'allow_sleep'      => false,
				'enforce_ip_limit' => true,
			)
		);
		if ( is_wp_error( $result ) ) {
			self::send_geocode_error( $result );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Return a geocoding WP_Error, preserving 429 when the IP limit is hit.
	 *
	 * @param WP_Error $error Error.
	 */
	private static function send_geocode_error( $error ) {
		$data   = $error->get_error_data();
		$status = ( is_array( $data ) && isset( $data['status'] ) ) ? (int) $data['status'] : 400;
		wp_send_json_error(
			array(
				'message' => $error->get_error_message(),
				'code'    => $error->get_error_code(),
			),
			$status
		);
	}
}
