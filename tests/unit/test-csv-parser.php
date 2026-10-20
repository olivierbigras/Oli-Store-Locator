<?php
/**
 * CSV parsing and column mapping tests.
 *
 * @package Oli_Store_Locator
 */

class Oli_SL_Test_CSV_Parser {

	/**
	 * Run tests.
	 *
	 * @return array
	 */
	public static function run() {
		$results = array();
		$results[] = self::test_parse_storepoint_headers();
		$results[] = self::test_quoted_commas();
		$results[] = self::test_empty_rows_skipped();
		$results[] = self::test_bom_stripped();
		$results[] = self::test_detect_does_not_confuse_lat_lng();
		$results[] = self::test_map_rows();
		$results[] = self::test_sheets_export_url();
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
	 * Fixture path.
	 *
	 * @return string
	 */
	private static function fixture() {
		return dirname( __DIR__ ) . '/fixtures/sample-storepoint.csv';
	}

	/**
	 * Parse Storepoint-like headers.
	 *
	 * @return array
	 */
	private static function test_parse_storepoint_headers() {
		$csv    = file_get_contents( self::fixture() );
		$parsed = Oli_SL_CSV_Parser::parse( $csv );
		$map    = Oli_SL_CSV_Parser::detect_columns( $parsed['headers'] );

		$ok = isset( $map['Name'], $map['Address'], $map['City'], $map['State'], $map['Zip'], $map['Country'], $map['Phone'], $map['Email'], $map['URL'], $map['Hours'], $map['Categories'], $map['Latitude'], $map['Longitude'], $map['Description'], $map['Image'], $map['ID'] );
		return self::assert_true( $ok, 'Auto-detect Storepoint-like headers' );
	}

	/**
	 * Quoted field with commas.
	 *
	 * @return array
	 */
	private static function test_quoted_commas() {
		$csv    = file_get_contents( self::fixture() );
		$parsed = Oli_SL_CSV_Parser::parse( $csv );
		$map    = Oli_SL_CSV_Parser::detect_columns( $parsed['headers'] );
		$rows   = Oli_SL_CSV_Parser::map_rows( $parsed['rows'], $parsed['headers'], $map );
		$ottawa = null;
		foreach ( $rows as $row ) {
			if ( isset( $row['ID'] ) && 'store-ott' === $row['ID'] ) {
				$ottawa = $row;
			}
		}
		$ok = $ottawa && 'Ottawa, Rideau' === $ottawa['Name'] && false !== strpos( $ottawa['Description'], 'commas' );
		return self::assert_true( $ok, 'Quoted CSV fields keep commas' );
	}

	/**
	 * Empty rows are skipped.
	 *
	 * @return array
	 */
	private static function test_empty_rows_skipped() {
		$csv    = file_get_contents( self::fixture() );
		$parsed = Oli_SL_CSV_Parser::parse( $csv );
		$ok     = 6 === count( $parsed['rows'] );
		return self::assert_true( $ok, 'Empty CSV rows are skipped (6 data rows remain)' );
	}

	/**
	 * UTF-8 BOM is stripped.
	 *
	 * @return array
	 */
	private static function test_bom_stripped() {
		$csv    = "\xEF\xBB\xBFName,Address\nA,B";
		$parsed = Oli_SL_CSV_Parser::parse( $csv );
		$ok     = isset( $parsed['headers'][0] ) && 'Name' === $parsed['headers'][0];
		return self::assert_true( $ok, 'UTF-8 BOM is stripped from headers' );
	}

	/**
	 * Latitude/longitude are not confused.
	 *
	 * @return array
	 */
	private static function test_detect_does_not_confuse_lat_lng() {
		$headers = array( 'Name', 'Longitude', 'Latitude', 'Valid' );
		$map     = Oli_SL_CSV_Parser::detect_columns( $headers );
		$ok      = isset( $map['Longitude'], $map['Latitude'] ) && 1 === $map['Longitude'] && 2 === $map['Latitude'] && ! isset( $map['URL'] );
		return self::assert_true( $ok, 'Longitude is not mapped as lat or URL' );
	}

	/**
	 * map_rows applies the column map.
	 *
	 * @return array
	 */
	private static function test_map_rows() {
		$headers = array( 'Store Name', 'Street', 'Town' );
		$rows    = array( array( 'Alpha', '1 Main', 'Montreal' ) );
		$mapped  = Oli_SL_CSV_Parser::map_rows( $rows, $headers );
		$ok      = isset( $mapped[0]['Name'], $mapped[0]['Address'], $mapped[0]['City'] ) && 'Alpha' === $mapped[0]['Name'];
		return self::assert_true( $ok, 'Rows map onto canonical fields' );
	}

	/**
	 * Sheets URL conversion.
	 *
	 * @return array
	 */
	private static function test_sheets_export_url() {
		$url = 'https://docs.google.com/spreadsheets/d/abc-123_ID/edit?usp=sharing#gid=42';
		$out = Oli_SL_CSV_Parser::sheets_export_url( $url );
		$ok  = 'https://docs.google.com/spreadsheets/d/abc-123_ID/export?format=csv&gid=42' === $out;
		$bad = false === Oli_SL_CSV_Parser::sheets_export_url( 'https://example.com/not-a-sheet' );
		return self::assert_true( $ok && $bad, 'Google Sheets share URL converts to public CSV export URL' );
	}
}
