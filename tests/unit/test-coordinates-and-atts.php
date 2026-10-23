<?php
/**
 * Coordinate sanitization, HTML sheet detection, and empty block atts.
 *
 * @package Oli_Store_Locator
 */

class Oli_SL_Test_Coordinates_And_Atts {

	/**
	 * Run tests.
	 *
	 * @return array
	 */
	public static function run() {
		return array(
			self::test_valid_coordinates(),
			self::test_rejects_out_of_range_and_non_finite(),
			self::test_html_sheet_is_error(),
			self::test_empty_atts_do_not_override_defaults(),
			self::test_distance_ignores_nan_and_inf(),
			self::test_http_user_agent(),
		);
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
	 * Valid lat/lng stay numeric strings.
	 *
	 * @return array
	 */
	private static function test_valid_coordinates() {
		$ok = '45.5' === oli_sl_sanitize_latitude( '45.5' )
			&& '-73.56' === oli_sl_sanitize_longitude( '-73.56' )
			&& '90' === oli_sl_sanitize_latitude( '90' )
			&& '-180' === oli_sl_sanitize_longitude( '-180' );
		return self::assert_true( $ok, 'Valid coordinates are kept' );
	}

	/**
	 * QA cases: 91, -200, 1e999, =1+44, abc, NaN.
	 *
	 * @return array
	 */
	private static function test_rejects_out_of_range_and_non_finite() {
		$ok = '' === oli_sl_sanitize_latitude( '91' )
			&& '' === oli_sl_sanitize_longitude( '-200' )
			&& '' === oli_sl_sanitize_latitude( '1e999' )
			&& '' === oli_sl_sanitize_latitude( '=1+44' )
			&& '' === oli_sl_sanitize_latitude( 'abc' )
			&& '' === oli_sl_sanitize_latitude( 'NaN' )
			&& '' === oli_sl_sanitize_longitude( 'NAN' )
			&& '' === oli_sl_sanitize_latitude( 'INF' );
		return self::assert_true( $ok, 'Rejects 91, -200, 1e999, =1+44, abc, NaN, INF' );
	}

	/**
	 * Google sign-in HTML is not treated as an empty CSV.
	 *
	 * @return array
	 */
	private static function test_html_sheet_is_error() {
		$html = "<!DOCTYPE html><html><head><title>Sign in</title></head><body>accounts.google.com</body></html>";
		$ok   = Oli_SL_CSV_Parser::looks_like_html( $html )
			&& Oli_SL_CSV_Parser::looks_like_html( 'Name,Address', 'text/html; charset=UTF-8' )
			&& ! Oli_SL_CSV_Parser::looks_like_html( "Name,Address\nA,B\n" );
		return self::assert_true( $ok, 'HTML Google sign-in responses are detected' );
	}

	/**
	 * Empty block attributes must not become radius 0 / empty height.
	 *
	 * @return array
	 */
	private static function test_empty_atts_do_not_override_defaults() {
		$atts = oli_sl_drop_empty_atts(
			array(
				'radius'      => '',
				'max_results' => null,
				'height'      => '',
				'category'    => '12',
			)
		);
		$ok   = ! isset( $atts['radius'], $atts['max_results'], $atts['height'] ) && '12' === $atts['category'];
		return self::assert_true( $ok, 'Empty block attributes are dropped before shortcode_atts' );
	}

	/**
	 * Haversine never receives Inf/NaN from stored junk.
	 *
	 * @return array
	 */
	private static function test_distance_ignores_nan_and_inf() {
		$zero = Oli_SL_Search::calculate_distance( INF, 0, 45, -73 );
		$nan  = Oli_SL_Search::calculate_distance( NAN, NAN, 1, 1 );
		$ok   = 0.0 === $zero && 0.0 === $nan;
		return self::assert_true( $ok, 'Search distance ignores Inf and NaN' );
	}

	/**
	 * Sheets requests identify the plugin.
	 *
	 * @return array
	 */
	private static function test_http_user_agent() {
		$ua = oli_sl_http_user_agent();
		$ok = 0 === strpos( $ua, 'Oli-Store-Locator/' . OLI_SL_VERSION );
		return self::assert_true( $ok, 'Sheets User-Agent names the plugin and version' );
	}
}
