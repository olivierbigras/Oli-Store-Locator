<?php
/**
 * Geocoding factory class.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Geocoding factory class.
 */
class Oli_SL_Geocoding {

	/**
	 * Get the configured geocoding provider instance.
	 *
	 * @return Oli_SL_Geocoding_Google|Oli_SL_Geocoding_Nominatim
	 */
	public static function get_provider() {
		$provider = oli_sl_get_option( 'geocoding_provider', 'nominatim' );

		if ( 'google' === $provider ) {
			return new Oli_SL_Geocoding_Google();
		}

		return new Oli_SL_Geocoding_Nominatim();
	}

	/**
	 * Geocode an address.
	 *
	 * @param string $address The address to geocode.
	 * @return array|WP_Error Array with 'lat' and 'lng' keys or WP_Error on failure.
	 */
	public static function geocode( $address ) {
		$address  = apply_filters( 'oli_sl_geocode_address', $address );
		$provider = self::get_provider();
		return $provider->geocode( $address );
	}

	/**
	 * Reverse geocode coordinates.
	 *
	 * @param float $lat Latitude.
	 * @param float $lng Longitude.
	 * @return string|WP_Error Address or WP_Error on failure.
	 */
	public static function reverse_geocode( $lat, $lng ) {
		$provider = self::get_provider();
		return $provider->reverse_geocode( $lat, $lng );
	}

	/**
	 * Autocomplete suggestions.
	 *
	 * @param string $query Search query.
	 * @return array|WP_Error
	 */
	public static function autocomplete( $query ) {
		$provider = self::get_provider();
		if ( method_exists( $provider, 'autocomplete' ) ) {
			return $provider->autocomplete( $query );
		}
		return array();
	}
}
