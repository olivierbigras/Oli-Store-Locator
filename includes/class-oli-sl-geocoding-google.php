<?php
/**
 * Google Maps Geocoding API provider.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Maps Geocoding API provider.
 */
class Oli_SL_Geocoding_Google {

	/**
	 * Geocoding endpoint.
	 *
	 * @var string
	 */
	const API_URL = 'https://maps.googleapis.com/maps/api/geocode/json';

	/**
	 * Places autocomplete endpoint.
	 *
	 * @var string
	 */
	const PLACES_URL = 'https://maps.googleapis.com/maps/api/place/autocomplete/json';

	/**
	 * Get the server-side API key.
	 *
	 * @return string
	 */
	private function get_api_key() {
		$server = oli_sl_get_option( 'google_server_key', '' );
		if ( is_string( $server ) && '' !== $server ) {
			return $server;
		}
		return (string) oli_sl_get_option( 'google_api_key', '' );
	}

	/**
	 * Geocode an address using Google Maps Geocoding API.
	 *
	 * @param string $address The address to geocode.
	 * @return array|WP_Error Array with 'lat' and 'lng' keys or WP_Error on failure.
	 */
	public function geocode( $address ) {
		$api_key = $this->get_api_key();

		if ( '' === $api_key ) {
			return new WP_Error( 'no_api_key', __( 'Google Maps API key is not configured.', 'oli-store-locator' ) );
		}

		$url = add_query_arg(
			array(
				'address' => $address,
				'key'     => $api_key,
			),
			self::API_URL
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data['results'][0]['geometry']['location'] ) ) {
			$message = isset( $data['error_message'] ) ? $data['error_message'] : __( 'Geocoding failed.', 'oli-store-locator' );
			return new WP_Error( 'geocoding_failed', $message );
		}

		return array(
			'lat' => $data['results'][0]['geometry']['location']['lat'],
			'lng' => $data['results'][0]['geometry']['location']['lng'],
		);
	}

	/**
	 * Reverse geocode coordinates using Google Maps Geocoding API.
	 *
	 * @param float $lat Latitude.
	 * @param float $lng Longitude.
	 * @return string|WP_Error Address or WP_Error on failure.
	 */
	public function reverse_geocode( $lat, $lng ) {
		$api_key = $this->get_api_key();

		if ( '' === $api_key ) {
			return new WP_Error( 'no_api_key', __( 'Google Maps API key is not configured.', 'oli-store-locator' ) );
		}

		$url = add_query_arg(
			array(
				'latlng' => $lat . ',' . $lng,
				'key'    => $api_key,
			),
			self::API_URL
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data['results'][0]['formatted_address'] ) ) {
			return new WP_Error( 'reverse_geocoding_failed', __( 'Reverse geocoding failed.', 'oli-store-locator' ) );
		}

		return $data['results'][0]['formatted_address'];
	}

	/**
	 * Address autocomplete via Places API.
	 *
	 * @param string $query Query.
	 * @return array|WP_Error
	 */
	public function autocomplete( $query ) {
		$api_key = $this->get_api_key();

		if ( '' === $api_key ) {
			return new WP_Error( 'no_api_key', __( 'Google Maps API key is not configured.', 'oli-store-locator' ) );
		}

		$url = add_query_arg(
			array(
				'input' => $query,
				'key'   => $api_key,
			),
			self::PLACES_URL
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		$suggestions = array();
		if ( ! empty( $data['predictions'] ) && is_array( $data['predictions'] ) ) {
			foreach ( $data['predictions'] as $prediction ) {
				if ( empty( $prediction['description'] ) ) {
					continue;
				}
				$suggestions[] = array(
					'label' => $prediction['description'],
					'value' => $prediction['description'],
				);
			}
		}

		return $suggestions;
	}
}
