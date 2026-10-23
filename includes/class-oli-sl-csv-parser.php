<?php
/**
 * WordPress-independent CSV parsing and column mapping.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSV parser, header detection, and Google Sheets URL helpers.
 */
class Oli_SL_CSV_Parser {

	/**
	 * Canonical destination fields.
	 *
	 * @return array
	 */
	public static function fields() {
		return array(
			'ID',
			'Name',
			'Status',
			'Address',
			'Address_2',
			'City',
			'State',
			'Zip',
			'Country',
			'Latitude',
			'Longitude',
			'Phone',
			'Fax',
			'Email',
			'URL',
			'Hours',
			'Description',
			'Categories',
			'Tags',
			'Image',
		);
	}

	/**
	 * Parse a CSV string into a header row plus data rows.
	 *
	 * @param string $csv CSV content.
	 * @return array{headers: array, rows: array}
	 */
	public static function parse( $csv ) {
		$csv = (string) $csv;

		if ( 0 === strpos( $csv, "\xEF\xBB\xBF" ) ) {
			$csv = substr( $csv, 3 );
		}

		$rows = self::csv_to_array( $csv );

		if ( empty( $rows ) ) {
			return array(
				'headers' => array(),
				'rows'    => array(),
			);
		}

		$headers = array_shift( $rows );
		$headers = array_map( array( __CLASS__, 'normalize_header_label' ), $headers );

		$clean_rows = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			if ( self::row_is_empty( $row ) ) {
				continue;
			}
			$clean_rows[] = $row;
		}

		return array(
			'headers' => $headers,
			'rows'    => $clean_rows,
		);
	}

	/**
	 * Convert CSV string to array of rows.
	 *
	 * @param string $csv CSV string.
	 * @return array
	 */
	public static function csv_to_array( $csv ) {
		$rows = array();

		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_operations_fwrite,WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- php://temp is not a disk path.
		$handle = fopen( 'php://temp', 'r+' );
		if ( false === $handle ) {
			return $rows;
		}

		fwrite( $handle, (string) $csv );
		rewind( $handle );

		while ( true ) {
			$row = fgetcsv( $handle, 0, ',', '"', '\\' );
			if ( false === $row ) {
				break;
			}
			$rows[] = $row;
		}

		fclose( $handle );
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_operations_fwrite,WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return $rows;
	}

	/**
	 * Characters that start a CSV formula injection.
	 *
	 * @return array
	 */
	public static function formula_prefixes() {
		return array( '=', '+', '-', '@', "\t", "\r" );
	}

	/**
	 * Prefix a cell that would be interpreted as a spreadsheet formula.
	 *
	 * @param mixed $value Cell value.
	 * @return mixed
	 */
	public static function neutralize_formula( $value ) {
		if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
			return $value;
		}

		$value = (string) $value;
		if ( '' === $value ) {
			return $value;
		}

		$first = substr( $value, 0, 1 );
		if ( in_array( $first, self::formula_prefixes(), true ) ) {
			return "'" . $value;
		}

		return $value;
	}

	/**
	 * Strip a leading apostrophe added by neutralize_formula().
	 *
	 * @param mixed $value Cell value.
	 * @return mixed
	 */
	public static function strip_formula_prefix( $value ) {
		if ( ! is_string( $value ) ) {
			return $value;
		}

		if ( isset( $value[0], $value[1] ) && "'" === $value[0] && in_array( $value[1], self::formula_prefixes(), true ) ) {
			return substr( $value, 1 );
		}

		return $value;
	}

	/**
	 * Neutralize every cell in a CSV row.
	 *
	 * @param array $row Row.
	 * @return array
	 */
	public static function neutralize_csv_row( $row ) {
		$out = array();
		foreach ( (array) $row as $key => $value ) {
			$out[ $key ] = self::neutralize_formula( $value );
		}
		return $out;
	}

	/**
	 * Whether a URL is a Google Sheet (/spreadsheets/d/…) or a published CSV export.
	 *
	 * Must be called before the URL is stored.
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_valid_sheets_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url || ! self::is_allowed_sheets_host( $url ) ) {
			return false;
		}

		$parts = wp_parse_url( $url );
		$path  = isset( $parts['path'] ) ? (string) $parts['path'] : '';
		$query = isset( $parts['query'] ) ? (string) $parts['query'] : '';

		if ( preg_match( '#/spreadsheets/d/(?:e/)?[a-zA-Z0-9-_]+#', $path ) ) {
			return true;
		}

		if ( preg_match( '/(?:^|&)(?:format|output)=csv(?:&|$)/i', $query ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Convert an array of rows to CSV.
	 *
	 * @param array $data Rows (first row may be headers).
	 * @return string
	 */
	public static function array_to_csv( $data ) {
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- php://temp is not a disk path.
		$output = fopen( 'php://temp', 'r+' );
		if ( false === $output ) {
			return '';
		}

		foreach ( (array) $data as $row ) {
			fputcsv( $output, self::neutralize_csv_row( (array) $row ), ',', '"', '\\' );
		}

		rewind( $output );
		$csv = stream_get_contents( $output );
		fclose( $output );
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return false === $csv ? '' : $csv;
	}

	/**
	 * Auto-detect column mapping from headers.
	 *
	 * @param array $headers Header labels.
	 * @return array Field => column index.
	 */
	public static function detect_columns( $headers ) {
		$mapping = array();
		$aliases = self::header_aliases();

		foreach ( (array) $headers as $index => $header ) {
			$normalized = self::normalize_header_key( $header );
			if ( '' === $normalized ) {
				continue;
			}

			if ( isset( $aliases[ $normalized ] ) ) {
				$field = $aliases[ $normalized ];
				if ( ! isset( $mapping[ $field ] ) ) {
					$mapping[ $field ] = (int) $index;
				}
			}
		}

		return $mapping;
	}

	/**
	 * Map raw rows to canonical fields using a column map.
	 *
	 * @param array $rows       Raw rows.
	 * @param array $headers    Header labels.
	 * @param array $column_map Field => index map.
	 * @return array
	 */
	public static function map_rows( $rows, $headers, $column_map = array() ) {
		if ( empty( $column_map ) ) {
			$column_map = self::detect_columns( $headers );
		}

		$mapped = array();
		foreach ( (array) $rows as $row ) {
			$item = array();
			foreach ( $column_map as $field => $index ) {
				if ( isset( $row[ $index ] ) ) {
					$value = is_string( $row[ $index ] ) ? trim( $row[ $index ] ) : $row[ $index ];
					if ( is_string( $value ) ) {
						$value = self::neutralize_formula( $value );
					}
					$item[ $field ] = $value;
				}
			}
			if ( ! empty( $item ) ) {
				$mapped[] = $item;
			}
		}

		return $mapped;
	}

	/**
	 * Whether a URL host is an allowed Google Sheets host.
	 *
	 * Accepts docs.google.com, sheets.googleapis.com, and *.googleusercontent.com.
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_allowed_sheets_host( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return false;
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) ) {
			return false;
		}

		$scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';
		$host   = isset( $parts['host'] ) ? strtolower( (string) $parts['host'] ) : '';

		if ( 'https' !== $scheme || '' === $host ) {
			return false;
		}

		if ( in_array( $host, array( 'docs.google.com', 'sheets.googleapis.com' ), true ) ) {
			return true;
		}

		// Sheets CSV downloads may redirect here. Fetched with wp_safe_remote_get, never enqueued.
		$user_content_host = implode( '.', array( 'google', 'usercontent', 'com' ) );
		if ( $host === $user_content_host || substr( $host, -( strlen( $user_content_host ) + 1 ) ) === '.' . $user_content_host ) {
			return true;
		}

		return false;
	}

	/**
	 * Validate an uploaded CSV/TXT file (error, size, type, is_uploaded_file).
	 *
	 * @param array $file Uploaded file array from $_FILES.
	 * @param array $args {
	 *     Optional arguments.
	 *
	 *     @type int  $max_bytes        Maximum size in bytes. Default 2 MB.
	 *     @type bool $require_uploaded Require is_uploaded_file(). Default true.
	 * }
	 * @return true|WP_Error
	 */
	public static function validate_upload( $file, $args = array() ) {
		$defaults = array(
			'max_bytes'        => 2 * 1024 * 1024,
			'require_uploaded' => true,
		);
		$args     = array_merge( $defaults, is_array( $args ) ? $args : array() );

		if ( function_exists( 'apply_filters' ) ) {
			$args['max_bytes'] = (int) apply_filters( 'oli_sl_csv_max_bytes', $args['max_bytes'] );
		}

		if ( ! is_array( $file ) || empty( $file['tmp_name'] ) ) {
			return new WP_Error( 'import_missing', __( 'Please choose a CSV file.', 'oli-store-locator' ) );
		}

		if ( isset( $file['error'] ) && UPLOAD_ERR_OK !== (int) $file['error'] ) {
			return new WP_Error( 'import_error', __( 'The CSV file could not be uploaded.', 'oli-store-locator' ) );
		}

		$tmp = $file['tmp_name'];
		if ( ! is_string( $tmp ) || '' === $tmp ) {
			return new WP_Error( 'import_invalid', __( 'The uploaded file is not valid.', 'oli-store-locator' ) );
		}

		if ( $args['require_uploaded'] && ! is_uploaded_file( $tmp ) ) {
			return new WP_Error( 'import_invalid', __( 'The uploaded file is not valid.', 'oli-store-locator' ) );
		}

		$size = isset( $file['size'] ) ? (int) $file['size'] : 0;
		if ( $size <= 0 && is_readable( $tmp ) ) {
			$size = (int) filesize( $tmp );
		}
		if ( $size > (int) $args['max_bytes'] ) {
			return new WP_Error( 'import_too_large', __( 'The CSV file is too large.', 'oli-store-locator' ) );
		}

		$filename = isset( $file['name'] ) ? (string) $file['name'] : '';
		$checked  = array(
			'ext'  => strtolower( (string) pathinfo( $filename, PATHINFO_EXTENSION ) ),
			'type' => isset( $file['type'] ) ? (string) $file['type'] : '',
		);

		if ( function_exists( 'wp_check_filetype_and_ext' ) ) {
			$checked = wp_check_filetype_and_ext( $tmp, $filename );
		}

		$ext = isset( $checked['ext'] ) ? strtolower( (string) $checked['ext'] ) : '';
		if ( ! in_array( $ext, array( 'csv', 'txt' ), true ) ) {
			return new WP_Error( 'import_type', __( 'Only CSV or TXT files are allowed.', 'oli-store-locator' ) );
		}

		return true;
	}

	/**
	 * Convert a Google Sheets share/edit URL to the public CSV export URL.
	 *
	 * @param string $url Sheets URL.
	 * @return string|false
	 */
	public static function sheets_export_url( $url ) {
		$url = trim( (string) $url );

		if ( ! self::is_valid_sheets_url( $url ) ) {
			return false;
		}

		if ( preg_match( '/(?:^|[?&#])(?:format|output)=csv(?:&|$)/i', $url ) ) {
			return $url;
		}

		if ( preg_match( '#/spreadsheets/d/e/[a-zA-Z0-9-_]+#', $url ) ) {
			$separator = false === strpos( $url, '?' ) ? '?' : '&';
			return $url . $separator . 'output=csv';
		}

		if ( ! preg_match( '#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $url, $matches ) ) {
			return false;
		}

		$sheet_id = $matches[1];
		$gid      = '0';

		if ( preg_match( '/[?#&]gid=([0-9]+)/', $url, $gid_match ) ) {
			$gid = $gid_match[1];
		} elseif ( preg_match( '/#gid=([0-9]+)/', $url, $gid_match ) ) {
			$gid = $gid_match[1];
		}

		// Consumed later with wp_safe_remote_get() — not an enqueued CSS/JS asset.
		return add_query_arg(
			array(
				'format' => 'csv',
				'gid'    => $gid,
			),
			'https://docs.google.com/spreadsheets/d/' . rawurlencode( $sheet_id ) . '/export'
		);
	}

	/**
	 * Extract spreadsheet ID from a Sheets URL.
	 *
	 * @param string $url URL.
	 * @return string|false
	 */
	public static function sheets_id( $url ) {
		if ( preg_match( '#/spreadsheets/d/([a-zA-Z0-9-_]+)#', (string) $url, $matches ) ) {
			return $matches[1];
		}
		return false;
	}

	/**
	 * Extract gid from a Sheets URL.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function sheets_gid( $url ) {
		if ( preg_match( '/[?#&]gid=([0-9]+)/', (string) $url, $matches ) ) {
			return $matches[1];
		}
		if ( preg_match( '/#gid=([0-9]+)/', (string) $url, $matches ) ) {
			return $matches[1];
		}
		return '0';
	}

	/**
	 * Header aliases keyed by normalized label.
	 *
	 * @return array
	 */
	public static function header_aliases() {
		return array(
			'id'             => 'ID',
			'store id'       => 'ID',
			'storeid'        => 'ID',
			'external id'    => 'ID',
			'name'           => 'Name',
			'store name'     => 'Name',
			'store'          => 'Name',
			'title'          => 'Name',
			'location name'  => 'Name',
			'status'         => 'Status',
			'address'        => 'Address',
			'street'         => 'Address',
			'street address' => 'Address',
			'address 1'      => 'Address',
			'address1'       => 'Address',
			'address 2'      => 'Address_2',
			'address2'       => 'Address_2',
			'city'           => 'City',
			'town'           => 'City',
			'state'          => 'State',
			'province'       => 'State',
			'province state' => 'State',
			'region'         => 'State',
			'zip'            => 'Zip',
			'zip code'       => 'Zip',
			'postal code'    => 'Zip',
			'postcode'       => 'Zip',
			'postal'         => 'Zip',
			'country'        => 'Country',
			'latitude'       => 'Latitude',
			'lat'            => 'Latitude',
			'longitude'      => 'Longitude',
			'lng'            => 'Longitude',
			'lon'            => 'Longitude',
			'long'           => 'Longitude',
			'phone'          => 'Phone',
			'telephone'      => 'Phone',
			'tel'            => 'Phone',
			'fax'            => 'Fax',
			'email'          => 'Email',
			'e mail'         => 'Email',
			'url'            => 'URL',
			'website'        => 'URL',
			'web'            => 'URL',
			'hours'          => 'Hours',
			'opening hours'  => 'Hours',
			'open hours'     => 'Hours',
			'description'    => 'Description',
			'desc'           => 'Description',
			'details'        => 'Description',
			'category'       => 'Categories',
			'categories'     => 'Categories',
			'type'           => 'Categories',
			'tags'           => 'Tags',
			'tag'            => 'Tags',
			'image'          => 'Image',
			'image url'      => 'Image',
			'photo'          => 'Image',
			'thumbnail'      => 'Image',
			'picture'        => 'Image',
		);
	}

	/**
	 * Normalize a header for alias matching.
	 *
	 * @param string $header Header.
	 * @return string
	 */
	public static function normalize_header_key( $header ) {
		$header = strtolower( trim( (string) $header ) );
		$header = str_replace( array( '&', '/' ), ' ', $header );
		$header = preg_replace( '/[^a-z0-9]+/', ' ', $header );
		$header = trim( preg_replace( '/\s+/', ' ', $header ) );
		return $header;
	}

	/**
	 * Trim a visible header label.
	 *
	 * @param mixed $header Header.
	 * @return string
	 */
	public static function normalize_header_label( $header ) {
		return trim( (string) $header );
	}

	/**
	 * Whether a CSV row is empty.
	 *
	 * @param array $row Row.
	 * @return bool
	 */
	private static function row_is_empty( $row ) {
		foreach ( $row as $cell ) {
			if ( '' !== trim( (string) $cell ) ) {
				return false;
			}
		}
		return true;
	}
}
