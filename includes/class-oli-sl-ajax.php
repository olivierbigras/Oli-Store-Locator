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

		if ( ! empty( $address ) && ( '' === $lat || '' === $lng ) ) {
			$geocoded = Oli_SL_Geocoding::geocode( $address );
			if ( is_wp_error( $geocoded ) ) {
				wp_send_json_error(
					array(
						'message' => $geocoded->get_error_message(),
					)
				);
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

		$result = Oli_SL_Geocoding::autocomplete( $query );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}
}
