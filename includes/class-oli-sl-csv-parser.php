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
			fputcsv( $output, (array) $row, ',', '"', '\\' );
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
					$item[ $field ] = is_string( $row[ $index ] ) ? trim( $row[ $index ] ) : $row[ $index ];
				}
			}
			if ( ! empty( $item ) ) {
				$mapped[] = $item;
			}
		}

		return $mapped;
	}

	/**
	 * Convert a Google Sheets share/edit URL to the public CSV export URL.
	 *
	 * @param string $url Sheets URL.
	 * @return string|false
	 */
	public static function sheets_export_url( $url ) {
		$url = trim( (string) $url );

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

		return sprintf(
			'https://docs.google.com/spreadsheets/d/%1$s/export?format=csv&gid=%2$s',
			$sheet_id,
			$gid
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
