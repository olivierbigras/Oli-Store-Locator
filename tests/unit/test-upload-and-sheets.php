<?php
/**
 * CSV upload validation and Google Sheets host tests.
 *
 * @package Oli_Store_Locator
 */

class Oli_SL_Test_Upload_And_Sheets {

	/**
	 * Run tests.
	 *
	 * @return array
	 */
	public static function run() {
		return array(
			self::test_invalid_csv_refused(),
			self::test_non_csv_extension_refused(),
			self::test_upload_error_refused(),
			self::test_oversized_csv_refused(),
			self::test_valid_csv_accepted_without_upload_flag(),
			self::test_sheets_host_outside_google_refused(),
			self::test_sheets_allowed_hosts(),
			self::test_sheets_safe_get_rejects_outside_google(),
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
	 * Write a temporary file.
	 *
	 * @param string $basename Basename.
	 * @param string $contents Contents.
	 * @return string
	 */
	private static function temp_file( $basename, $contents ) {
		$path = sys_get_temp_dir() . '/oli-sl-' . uniqid( '', true ) . '-' . $basename;
		file_put_contents( $path, $contents );
		return $path;
	}

	/**
	 * A local (non-uploaded) file is refused.
	 *
	 * @return array
	 */
	private static function test_invalid_csv_refused() {
		$path = self::temp_file( 'stores.csv', "Name,Address\nA,B\n" );
		$file = array(
			'name'     => 'stores.csv',
			'type'     => 'text/csv',
			'tmp_name' => $path,
			'error'    => UPLOAD_ERR_OK,
			'size'     => filesize( $path ),
		);
		$result = Oli_SL_CSV_Parser::validate_upload( $file );
		unlink( $path );
		$ok = is_wp_error( $result ) && 'import_invalid' === $result->get_error_code();
		return self::assert_true( $ok, 'Invalid CSV upload is refused (not an uploaded file)' );
	}

	/**
	 * PHP files are refused.
	 *
	 * @return array
	 */
	private static function test_non_csv_extension_refused() {
		$path = self::temp_file( 'evil.php', "<?php echo 'no';\n" );
		$file = array(
			'name'     => 'evil.php',
			'type'     => 'application/x-php',
			'tmp_name' => $path,
			'error'    => UPLOAD_ERR_OK,
			'size'     => filesize( $path ),
		);
		$result = Oli_SL_CSV_Parser::validate_upload( $file, array( 'require_uploaded' => false ) );
		unlink( $path );
		$ok = is_wp_error( $result ) && 'import_type' === $result->get_error_code();
		return self::assert_true( $ok, 'Non-CSV/TXT files are refused' );
	}

	/**
	 * Upload error codes are refused.
	 *
	 * @return array
	 */
	private static function test_upload_error_refused() {
		$result = Oli_SL_CSV_Parser::validate_upload(
			array(
				'name'     => 'stores.csv',
				'tmp_name' => '/tmp/x',
				'error'    => UPLOAD_ERR_INI_SIZE,
				'size'     => 0,
			)
		);
		$ok = is_wp_error( $result ) && 'import_error' === $result->get_error_code();
		return self::assert_true( $ok, 'CSV upload error codes are refused' );
	}

	/**
	 * Oversized files are refused.
	 *
	 * @return array
	 */
	private static function test_oversized_csv_refused() {
		$path = self::temp_file( 'big.csv', "Name\nA\n" );
		$file = array(
			'name'     => 'big.csv',
			'type'     => 'text/csv',
			'tmp_name' => $path,
			'error'    => UPLOAD_ERR_OK,
			'size'     => 3 * 1024 * 1024,
		);
		$result = Oli_SL_CSV_Parser::validate_upload( $file, array( 'require_uploaded' => false, 'max_bytes' => 2 * 1024 * 1024 ) );
		unlink( $path );
		$ok = is_wp_error( $result ) && 'import_too_large' === $result->get_error_code();
		return self::assert_true( $ok, 'Oversized CSV files are refused' );
	}

	/**
	 * A well-formed CSV is accepted when upload checks are relaxed for tests.
	 *
	 * @return array
	 */
	private static function test_valid_csv_accepted_without_upload_flag() {
		$path = self::temp_file( 'stores.csv', "Name,Address\nA,B\n" );
		$file = array(
			'name'     => 'stores.csv',
			'type'     => 'text/csv',
			'tmp_name' => $path,
			'error'    => UPLOAD_ERR_OK,
			'size'     => filesize( $path ),
		);
		$result = Oli_SL_CSV_Parser::validate_upload( $file, array( 'require_uploaded' => false ) );
		unlink( $path );
		return self::assert_true( true === $result, 'Valid CSV/TXT files are accepted' );
	}

	/**
	 * Non-Google Sheets URLs are refused.
	 *
	 * @return array
	 */
	private static function test_sheets_host_outside_google_refused() {
		$evil = 'https://evil.example/spreadsheets/d/abc-123/export?format=csv';
		$ok   = false === Oli_SL_CSV_Parser::is_allowed_sheets_host( $evil )
			&& false === Oli_SL_CSV_Parser::sheets_export_url( $evil )
			&& false === Oli_SL_Google_Sheets_Sync::is_allowed_host( $evil );
		return self::assert_true( $ok, 'Sheets URLs outside Google hosts are refused' );
	}

	/**
	 * Allowed Google hosts.
	 *
	 * @return array
	 */
	private static function test_sheets_allowed_hosts() {
		$ok = Oli_SL_CSV_Parser::is_allowed_sheets_host( 'https://docs.google.com/spreadsheets/d/abc/edit' )
			&& Oli_SL_CSV_Parser::is_allowed_sheets_host( 'https://sheets.googleapis.com/v4/spreadsheets/abc' )
			&& Oli_SL_CSV_Parser::is_allowed_sheets_host( 'https://doc-00.googleusercontent.com/docs/securesc/file' )
			&& ! Oli_SL_CSV_Parser::is_allowed_sheets_host( 'http://docs.google.com/spreadsheets/d/abc/edit' )
			&& ! Oli_SL_CSV_Parser::is_allowed_sheets_host( 'https://docs.google.com.evil.com/spreadsheets/d/abc' );
		return self::assert_true( $ok, 'Only docs.google.com, sheets.googleapis.com, and *.googleusercontent.com are allowed' );
	}

	/**
	 * safe_remote_get never reaches the network for a non-Google host.
	 *
	 * @return array
	 */
	private static function test_sheets_safe_get_rejects_outside_google() {
		$result = Oli_SL_Google_Sheets_Sync::safe_remote_get( 'https://127.0.0.1/internal' );
		$ok     = is_wp_error( $result ) && 'invalid_host' === $result->get_error_code();
		return self::assert_true( $ok, 'Sheets fetch refuses non-Google hosts without an HTTP call' );
	}
}
