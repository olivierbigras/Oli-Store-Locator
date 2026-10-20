<?php
/**
 * OpenStreetMap Nominatim geocoding provider.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OpenStreetMap Nominatim geocoding provider.
 */
class Oli_SL_Geocoding_Nominatim {

	/**
	 * API endpoint.
	 *
	 * @var string
	 */
	const API_URL = 'https://nominatim.openstreetmap.org';

	/**
	 * Geocode an address using Nominatim.
	 *
	 * @param string $address The address to geocode.
	 * @return array|WP_Error Array with 'lat' and 'lng' keys or WP_Error on failure.
	 */
	public function geocode( $address ) {
		$this->rate_limit();

		$url = add_query_arg(
			array(
				'q'      => $address,
				'format' => 'json',
				'limit'  => 1,
			),
			self::API_URL . '/search'
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'Oli Store Locator WordPress Plugin/' . OLI_SL_VERSION . '; ' . home_url( '/' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data[0]['lat'] ) || empty( $data[0]['lon'] ) ) {
			return new WP_Error( 'geocoding_failed', __( 'Geocoding failed.', 'oli-store-locator' ) );
		}

		return array(
			'lat' => (float) $data[0]['lat'],
			'lng' => (float) $data[0]['lon'],
		);
	}

	/**
	 * Reverse geocode coordinates using Nominatim.
	 *
	 * @param float $lat Latitude.
	 * @param float $lng Longitude.
	 * @return string|WP_Error Address or WP_Error on failure.
	 */
	public function reverse_geocode( $lat, $lng ) {
		$this->rate_limit();

		$url = add_query_arg(
			array(
				'lat'    => $lat,
				'lon'    => $lng,
				'format' => 'json',
			),
			self::API_URL . '/reverse'
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'Oli Store Locator WordPress Plugin/' . OLI_SL_VERSION . '; ' . home_url( '/' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data['display_name'] ) ) {
			return new WP_Error( 'reverse_geocoding_failed', __( 'Reverse geocoding failed.', 'oli-store-locator' ) );
		}

		return $data['display_name'];
	}

	/**
	 * Address autocomplete via Nominatim search.
	 *
	 * @param string $query Query.
	 * @return array|WP_Error
	 */
	public function autocomplete( $query ) {
		$this->rate_limit();

		$url = add_query_arg(
			array(
				'q'      => $query,
				'format' => 'json',
				'limit'  => 5,
			),
			self::API_URL . '/search'
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'Oli Store Locator WordPress Plugin/' . OLI_SL_VERSION . '; ' . home_url( '/' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		$suggestions = array();
		if ( is_array( $data ) ) {
			foreach ( $data as $item ) {
				if ( empty( $item['display_name'] ) ) {
					continue;
				}
				$suggestions[] = array(
					'label' => $item['display_name'],
					'value' => $item['display_name'],
					'lat'   => isset( $item['lat'] ) ? (float) $item['lat'] : '',
					'lng'   => isset( $item['lon'] ) ? (float) $item['lon'] : '',
				);
			}
		}

		return $suggestions;
	}

	/**
	 * Rate limiting for Nominatim API (max 1 request per second).
	 */
	private function rate_limit() {
		if ( ! oli_sl_get_option( 'nominatim_rate_limit', '1' ) ) {
			return;
		}

		$last_request = get_transient( 'oli_sl_nominatim_last_request' );
		if ( $last_request ) {
			$elapsed = microtime( true ) - (float) $last_request;
			if ( $elapsed < 1 ) {
				$wait = (int) ( ( 1 - $elapsed ) * 1000000 );
				if ( $wait > 0 ) {
					usleep( $wait );
				}
			}
		}
		set_transient( 'oli_sl_nominatim_last_request', microtime( true ), 10 );
	}
}
