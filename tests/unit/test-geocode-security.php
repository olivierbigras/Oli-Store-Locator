<?php
/**
 * Geocode permission, cache, and IP-limit tests.
 *
 * @package Oli_Store_Locator
 */

class Oli_SL_Test_Geocode_Security {

	/**
	 * Run tests.
	 *
	 * @return array
	 */
	public static function run() {
		$results = array();
		$results[] = self::test_geocode_route_denied_anonymously();
		$results[] = self::test_geocode_route_allows_admin();
		$results[] = self::test_geocode_route_denies_rest_nonce_alone();
		$results[] = self::test_geocode_route_denies_editor_with_nonce();
		$results[] = self::test_geocode_route_denies_subscriber_with_nonce();
		$results[] = self::test_geocode_route_allows_dedicated_cap();
		$results[] = self::test_cache_used_on_second_call();
		$results[] = self::test_ip_limit_reached();
		$results[] = self::test_autocomplete_shares_ip_limit();
		$results[] = self::test_xff_is_ignored_for_ip_limit();
		$results[] = self::test_nominatim_public_does_not_sleep();
		return $results;
	}

	/**
	 * Assert helper.
	 *
	 * @param bool   $ok      Condition.
	 * @param string $message Message.
	 * @return array
	 */
	private static function assert_true( $ok, $message ) {
		return array(
			'ok'      => (bool) $ok,
			'message' => $message,
		);
	}

	/**
	 * Anonymous /geocode is refused.
	 *
	 * @return array
	 */
	private static function test_geocode_route_denied_anonymously() {
		oli_sl_test_reset();
		$ok = false === Oli_SL_Rest_API::can_access_geocode( null );
		$api = new Oli_SL_Rest_API();
		$denied = $api->geocode_permissions( null );
		$ok = $ok && is_wp_error( $denied ) && 'rest_forbidden' === $denied->get_error_code();
		return self::assert_true( $ok, '/geocode is refused for anonymous requests' );
	}

	/**
	 * Admins may call /geocode.
	 *
	 * @return array
	 */
	private static function test_geocode_route_allows_admin() {
		oli_sl_test_reset();
		$GLOBALS['oli_sl_test_caps'] = array( 'manage_options' );
		return self::assert_true( Oli_SL_Rest_API::can_access_geocode( null ), '/geocode allows manage_options' );
	}

	/**
	 * A REST nonce alone is not enough for /geocode.
	 *
	 * @return array
	 */
	private static function test_geocode_route_denies_rest_nonce_alone() {
		oli_sl_test_reset();
		$GLOBALS['oli_sl_test_nonces']['wp_rest'] = 'valid-rest-nonce';
		$request = new Oli_SL_Test_REST_Request( array( 'X-WP-Nonce' => 'valid-rest-nonce' ) );
		$ok      = false === Oli_SL_Rest_API::can_access_geocode( $request );
		return self::assert_true( $ok, '/geocode refuses a REST nonce without a capability' );
	}

	/**
	 * Editors cannot call /geocode even with a nonce.
	 *
	 * @return array
	 */
	private static function test_geocode_route_denies_editor_with_nonce() {
		oli_sl_test_reset();
		$GLOBALS['oli_sl_test_caps']             = array( 'edit_posts', 'edit_others_posts' );
		$GLOBALS['oli_sl_test_nonces']['wp_rest'] = 'valid-rest-nonce';
		$request = new Oli_SL_Test_REST_Request( array( 'X-WP-Nonce' => 'valid-rest-nonce' ) );
		$ok      = false === Oli_SL_Rest_API::can_access_geocode( $request );
		return self::assert_true( $ok, '/geocode refuses Editor even with a REST nonce' );
	}

	/**
	 * Subscribers cannot call /geocode even with a nonce.
	 *
	 * @return array
	 */
	private static function test_geocode_route_denies_subscriber_with_nonce() {
		oli_sl_test_reset();
		$GLOBALS['oli_sl_test_caps']             = array( 'read' );
		$GLOBALS['oli_sl_test_nonces']['wp_rest'] = 'valid-rest-nonce';
		$request = new Oli_SL_Test_REST_Request( array( 'X-WP-Nonce' => 'valid-rest-nonce' ) );
		$ok      = false === Oli_SL_Rest_API::can_access_geocode( $request );
		return self::assert_true( $ok, '/geocode refuses Subscriber even with a REST nonce' );
	}

	/**
	 * The dedicated geocode capability is enough.
	 *
	 * @return array
	 */
	private static function test_geocode_route_allows_dedicated_cap() {
		oli_sl_test_reset();
		$GLOBALS['oli_sl_test_caps'] = array( 'oli_sl_geocode' );
		return self::assert_true( Oli_SL_Rest_API::can_access_geocode( null ), '/geocode allows the dedicated oli_sl_geocode capability' );
	}

	/**
	 * The second geocode of the same address uses the transient cache.
	 *
	 * @return array
	 */
	private static function test_cache_used_on_second_call() {
		oli_sl_test_reset();
		$calls = 0;
		add_filter(
			'oli_sl_pre_geocode',
			function ( $pre, $address ) use ( &$calls ) {
				unset( $pre, $address );
				++$calls;
				return array(
					'lat' => 45.5,
					'lng' => -73.5,
				);
			}
		);

		$args = array(
			'allow_sleep'      => false,
			'enforce_ip_limit' => false,
		);

		$first  = Oli_SL_Geocoding::geocode( '  123 Rue Saint-Denis, Montreal  ', $args );
		$second = Oli_SL_Geocoding::geocode( '123 rue saint-denis, montreal', $args );

		$ok = ! is_wp_error( $first )
			&& ! is_wp_error( $second )
			&& 1 === $calls
			&& 45.5 === $second['lat']
			&& -73.5 === $second['lng'];

		return self::assert_true( $ok, 'Geocode cache is used on the second call' );
	}

	/**
	 * Per-IP limit rejects further external geocodes.
	 *
	 * @return array
	 */
	private static function test_ip_limit_reached() {
		oli_sl_test_reset();
		add_filter(
			'oli_sl_geocode_ip_limit',
			function () {
				return 2;
			}
		);
		add_filter(
			'oli_sl_pre_geocode',
			function () {
				return array(
					'lat' => 1.0,
					'lng' => 2.0,
				);
			}
		);

		$args = array(
			'allow_sleep'      => false,
			'enforce_ip_limit' => true,
			'use_cache'        => false,
		);

		$one   = Oli_SL_Geocoding::geocode( 'One Street', $args );
		$two   = Oli_SL_Geocoding::geocode( 'Two Street', $args );
		$three = Oli_SL_Geocoding::geocode( 'Three Street', $args );

		$ok = ! is_wp_error( $one )
			&& ! is_wp_error( $two )
			&& is_wp_error( $three )
			&& 'geocode_rate_limited' === $three->get_error_code();

		return self::assert_true( $ok, 'Per-IP geocode limit is enforced without an external call' );
	}

	/**
	 * Autocomplete consumes the same per-IP geocode budget.
	 *
	 * @return array
	 */
	private static function test_autocomplete_shares_ip_limit() {
		oli_sl_test_reset();
		add_filter(
			'oli_sl_geocode_ip_limit',
			function () {
				return 2;
			}
		);
		add_filter(
			'oli_sl_pre_geocode',
			function () {
				return array(
					'lat' => 1.0,
					'lng' => 2.0,
				);
			}
		);
		add_filter(
			'oli_sl_pre_autocomplete',
			function () {
				return array(
					array(
						'label' => 'Montreal',
						'lat'   => 45.5,
						'lng'   => -73.5,
					),
				);
			}
		);

		$args = array(
			'allow_sleep'      => false,
			'enforce_ip_limit' => true,
			'use_cache'        => false,
		);

		$one   = Oli_SL_Geocoding::geocode( 'One Street', $args );
		$two   = Oli_SL_Geocoding::autocomplete( 'Mon', $args );
		$three = Oli_SL_Geocoding::autocomplete( 'Mont', $args );

		$ok = ! is_wp_error( $one )
			&& ! is_wp_error( $two )
			&& is_wp_error( $three )
			&& 'geocode_rate_limited' === $three->get_error_code()
			&& 429 === (int) $three->get_error_data()['status'];

		return self::assert_true( $ok, 'Autocomplete shares the per-IP geocode limit and returns 429' );
	}

	/**
	 * X-Forwarded-For cannot reset the per-IP budget.
	 *
	 * @return array
	 */
	private static function test_xff_is_ignored_for_ip_limit() {
		oli_sl_test_reset();
		$_SERVER['REMOTE_ADDR']          = '203.0.113.10';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.20';

		$first = Oli_SL_Geocoding::client_ip();
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.21, 203.0.113.10';
		$second = Oli_SL_Geocoding::client_ip();

		add_filter(
			'oli_sl_geocode_ip_limit',
			function () {
				return 1;
			}
		);
		add_filter(
			'oli_sl_pre_geocode',
			function () {
				return array(
					'lat' => 1.0,
					'lng' => 2.0,
				);
			}
		);
		$args = array(
			'allow_sleep'      => false,
			'enforce_ip_limit' => true,
			'use_cache'        => false,
		);
		Oli_SL_Geocoding::geocode( 'First', $args );
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.99';
		$limited = Oli_SL_Geocoding::geocode( 'Second', $args );

		$ok = '203.0.113.10' === $first
			&& '203.0.113.10' === $second
			&& is_wp_error( $limited )
			&& 'geocode_rate_limited' === $limited->get_error_code();

		return self::assert_true( $ok, 'Per-IP limit uses REMOTE_ADDR and ignores X-Forwarded-For' );
	}

	/**
	 * Nominatim public rate limiting never sleeps.
	 *
	 * @return array
	 */
	private static function test_nominatim_public_does_not_sleep() {
		oli_sl_test_reset();
		set_transient( 'oli_sl_nominatim_last_request', microtime( true ), 10 );
		$provider = new Oli_SL_Geocoding_Nominatim();
		$start    = microtime( true );
		$result   = $provider->rate_limit( false );
		$elapsed  = microtime( true ) - $start;

		$ok = is_wp_error( $result )
			&& 'nominatim_rate_limited' === $result->get_error_code()
			&& $elapsed < 0.2;

		return self::assert_true( $ok, 'Nominatim public rate limit returns an error instead of sleeping' );
	}
}

/**
 * Minimal REST request stand-in.
 */
class Oli_SL_Test_REST_Request {

	/**
	 * Headers.
	 *
	 * @var array
	 */
	private $headers = array();

	/**
	 * Params.
	 *
	 * @var array
	 */
	private $params = array();

	/**
	 * Constructor.
	 *
	 * @param array $headers Headers.
	 * @param array $params  Params.
	 */
	public function __construct( $headers = array(), $params = array() ) {
		$this->headers = $headers;
		$this->params  = $params;
	}

	/**
	 * Header value.
	 *
	 * @param string $name Name.
	 * @return string
	 */
	public function get_header( $name ) {
		return isset( $this->headers[ $name ] ) ? $this->headers[ $name ] : '';
	}

	/**
	 * Param value.
	 *
	 * @param string $name Name.
	 * @return mixed
	 */
	public function get_param( $name ) {
		return isset( $this->params[ $name ] ) ? $this->params[ $name ] : null;
	}
}
