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
	 * Cache lifetime for geocoded addresses (30 days).
	 *
	 * @var int
	 */
	const CACHE_TTL = 2592000;

	/**
	 * Default number of external geocode calls allowed per IP per minute.
	 *
	 * @var int
	 */
	const IP_LIMIT_DEFAULT = 10;

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
	 * Normalize an address for cache keys.
	 *
	 * @param string $address Address.
	 * @return string
	 */
	public static function normalize_address( $address ) {
		$address = strtolower( trim( (string) $address ) );
		$address = preg_replace( '/\s+/', ' ', $address );
		return is_string( $address ) ? $address : '';
	}

	/**
	 * Transient key for a normalized address.
	 *
	 * @param string $address Address.
	 * @return string
	 */
	public static function cache_key( $address ) {
		return 'oli_sl_geo_' . md5( self::normalize_address( $address ) );
	}

	/**
	 * Transient key for the per-IP external-call counter.
	 *
	 * @param string $ip IP address.
	 * @return string
	 */
	public static function ip_limit_key( $ip ) {
		return 'oli_sl_geo_ip_' . md5( (string) $ip );
	}

	/**
	 * Per-IP external geocode limit (filterable).
	 *
	 * @return int
	 */
	public static function ip_limit() {
		$limit = (int) apply_filters( 'oli_sl_geocode_ip_limit', self::IP_LIMIT_DEFAULT );
		return $limit > 0 ? $limit : self::IP_LIMIT_DEFAULT;
	}

	/**
	 * Client IP used for geocode rate limiting.
	 *
	 * Uses REMOTE_ADDR only. X-Forwarded-For and similar headers are ignored
	 * so clients cannot reset the per-IP budget by spoofing a proxy header.
	 *
	 * @return string
	 */
	public static function client_ip() {
		$ip = '';
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		/**
		 * Filter the IP used for public geocode rate limiting.
		 *
		 * Do not read X-Forwarded-For here unless the site sits behind a
		 * trusted proxy that overwrites REMOTE_ADDR.
		 *
		 * @param string $ip Detected IP.
		 */
		$ip = apply_filters( 'oli_sl_geocode_client_ip', $ip );

		if ( function_exists( 'rest_is_ip_address' ) && rest_is_ip_address( $ip ) ) {
			return $ip;
		}

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
	}

	/**
	 * Whether the current request is a public (front-end / anonymous) context.
	 *
	 * Admin screens, cron, WP-CLI, and users who can geocode in admin are not public.
	 *
	 * @return bool
	 */
	public static function is_public_context() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return false;
		}

		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return false;
		}

		if ( function_exists( 'current_user_can' ) && ( current_user_can( 'manage_options' ) || current_user_can( 'oli_sl_geocode' ) ) ) {
			return false;
		}

		if ( function_exists( 'is_admin' ) && is_admin() && ! ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Parse geocode options.
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public static function parse_args( $args = array() ) {
		$public   = self::is_public_context();
		$defaults = array(
			'allow_sleep'      => ! $public,
			'enforce_ip_limit' => $public,
			'use_cache'        => true,
		);

		if ( ! is_array( $args ) ) {
			$args = array();
		}

		return array_merge( $defaults, $args );
	}

	/**
	 * Read a cached geocode result.
	 *
	 * @param string $address Address.
	 * @return array|false
	 */
	public static function get_cached( $address ) {
		$cached = get_transient( self::cache_key( $address ) );
		if ( is_array( $cached ) && isset( $cached['lat'], $cached['lng'] ) ) {
			return $cached;
		}
		return false;
	}

	/**
	 * Store a geocode result in a 30-day transient.
	 *
	 * @param string $address Address.
	 * @param array  $result  Result with lat/lng.
	 * @return bool
	 */
	public static function set_cached( $address, $result ) {
		$ttl = (int) apply_filters( 'oli_sl_geocode_cache_ttl', self::CACHE_TTL );
		if ( $ttl < 1 ) {
			$ttl = self::CACHE_TTL;
		}
		return set_transient( self::cache_key( $address ), $result, $ttl );
	}

	/**
	 * Consume one external-geocode slot for an IP, or reject when the limit is reached.
	 *
	 * @param string $ip IP address.
	 * @return true|WP_Error
	 */
	public static function consume_ip_slot( $ip = '' ) {
		$ip    = '' !== $ip ? $ip : self::client_ip();
		$key   = self::ip_limit_key( $ip );
		$limit = self::ip_limit();
		$count = (int) get_transient( $key );

		if ( $count >= $limit ) {
			return new WP_Error(
				'geocode_rate_limited',
				__( 'Too many geocoding requests. Please try again in a minute.', 'oli-store-locator' ),
				array( 'status' => 429 )
			);
		}

		$ttl = (int) apply_filters( 'oli_sl_geocode_ip_window', MINUTE_IN_SECONDS );
		if ( $ttl < 1 ) {
			$ttl = MINUTE_IN_SECONDS;
		}

		set_transient( $key, $count + 1, $ttl );
		return true;
	}

	/**
	 * Geocode an address, with cache and optional per-IP limit.
	 *
	 * @param string $address The address to geocode.
	 * @param array  $args    {
	 *     Optional arguments.
	 *
	 *     @type bool $allow_sleep      Allow Nominatim to sleep. Default: non-public requests.
	 *     @type bool $enforce_ip_limit Apply the per-IP external-call limit. Default: public requests.
	 *     @type bool $use_cache        Read/write the address transient. Default true.
	 * }
	 * @return array|WP_Error Array with 'lat' and 'lng' keys or WP_Error on failure.
	 */
	public static function geocode( $address, $args = array() ) {
		$args    = self::parse_args( $args );
		$address = apply_filters( 'oli_sl_geocode_address', $address );

		if ( '' === self::normalize_address( $address ) ) {
			return new WP_Error( 'empty_address', __( 'Address is required.', 'oli-store-locator' ) );
		}

		if ( $args['use_cache'] ) {
			$cached = self::get_cached( $address );
			if ( $cached ) {
				return $cached;
			}
		}

		if ( $args['enforce_ip_limit'] ) {
			$slot = self::consume_ip_slot();
			if ( is_wp_error( $slot ) ) {
				return $slot;
			}
		}

		/**
		 * Short-circuit an external geocode (tests, custom providers).
		 *
		 * Return an array with lat/lng, a WP_Error, or null to continue.
		 *
		 * @param array|WP_Error|null $pre     Precomputed result.
		 * @param string              $address Address.
		 * @param array               $args    Geocode args.
		 */
		$pre = apply_filters( 'oli_sl_pre_geocode', null, $address, $args );
		if ( null !== $pre ) {
			if ( ! is_wp_error( $pre ) && is_array( $pre ) && isset( $pre['lat'], $pre['lng'] ) && $args['use_cache'] ) {
				self::set_cached( $address, $pre );
			}
			return $pre;
		}

		$provider = self::get_provider();
		if ( $provider instanceof Oli_SL_Geocoding_Nominatim ) {
			$result = $provider->geocode( $address, $args['allow_sleep'] );
		} else {
			$result = $provider->geocode( $address );
		}

		if ( ! is_wp_error( $result ) && is_array( $result ) && isset( $result['lat'], $result['lng'] ) && $args['use_cache'] ) {
			self::set_cached( $address, $result );
		}

		return $result;
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
		if ( $provider instanceof Oli_SL_Geocoding_Nominatim ) {
			return $provider->reverse_geocode( $lat, $lng, ! self::is_public_context() );
		}
		return $provider->reverse_geocode( $lat, $lng );
	}

	/**
	 * Autocomplete suggestions.
	 *
	 * Public requests share the same per-IP geocode budget as address search.
	 *
	 * @param string $query Search query.
	 * @param array  $args  Same keys as geocode(): allow_sleep, enforce_ip_limit, use_cache.
	 * @return array|WP_Error
	 */
	public static function autocomplete( $query, $args = array() ) {
		$args  = self::parse_args( $args );
		$query = trim( (string) $query );
		if ( strlen( $query ) < 3 ) {
			return array();
		}

		if ( $args['enforce_ip_limit'] ) {
			$slot = self::consume_ip_slot();
			if ( is_wp_error( $slot ) ) {
				return $slot;
			}
		}

		/**
		 * Short-circuit autocomplete (tests, custom providers).
		 *
		 * @param array|WP_Error|null $pre   Precomputed suggestions.
		 * @param string              $query Query.
		 * @param array               $args  Args.
		 */
		$pre = apply_filters( 'oli_sl_pre_autocomplete', null, $query, $args );
		if ( null !== $pre ) {
			return $pre;
		}

		$provider = self::get_provider();
		if ( ! method_exists( $provider, 'autocomplete' ) ) {
			return array();
		}
		if ( $provider instanceof Oli_SL_Geocoding_Nominatim ) {
			return $provider->autocomplete( $query, $args['allow_sleep'] );
		}
		return $provider->autocomplete( $query );
	}
}
