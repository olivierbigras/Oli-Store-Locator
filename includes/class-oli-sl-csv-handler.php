<?php
/**
 * CSV import/export handler.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSV import/export handler.
 */
class Oli_SL_CSV_Handler {

	/**
	 * Export stores to CSV.
	 *
	 * @return string CSV content.
	 */
	public static function export_stores() {
		$stores = get_posts(
			array(
				'post_type'      => 'oli_store',
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);

		$csv_data   = array();
		$csv_data[] = array(
			'ID',
			'Name',
			'Status',
			'Address',
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

		foreach ( $stores as $store ) {
			$categories = wp_get_post_terms( $store->ID, 'oli_store_category', array( 'fields' => 'names' ) );
			$tags       = wp_get_post_terms( $store->ID, 'oli_store_tag', array( 'fields' => 'names' ) );

			$csv_data[] = array(
				$store->ID,
				$store->post_title,
				$store->post_status,
				oli_sl_get_store_meta( $store->ID, 'address' ),
				oli_sl_get_store_meta( $store->ID, 'city' ),
				oli_sl_get_store_meta( $store->ID, 'state' ),
				oli_sl_get_store_meta( $store->ID, 'zip' ),
				oli_sl_get_store_meta( $store->ID, 'country' ),
				oli_sl_get_store_meta( $store->ID, 'latitude' ),
				oli_sl_get_store_meta( $store->ID, 'longitude' ),
				oli_sl_get_store_meta( $store->ID, 'phone' ),
				oli_sl_get_store_meta( $store->ID, 'fax' ),
				oli_sl_get_store_meta( $store->ID, 'email' ),
				oli_sl_get_store_meta( $store->ID, 'url' ),
				oli_sl_get_store_meta( $store->ID, 'hours' ),
				oli_sl_get_store_meta( $store->ID, 'description' ),
				is_array( $categories ) ? implode( '|', $categories ) : '',
				is_array( $tags ) ? implode( '|', $tags ) : '',
				oli_sl_get_store_meta( $store->ID, 'image_url' ),
			);
		}

		return Oli_SL_CSV_Parser::array_to_csv( $csv_data );
	}

	/**
	 * Import stores from CSV.
	 *
	 * @param string $csv_content CSV content.
	 * @param bool   $update      Whether to update existing stores.
	 * @return array Import results.
	 */
	public static function import_stores( $csv_content, $update = true ) {
		$parsed = Oli_SL_CSV_Parser::parse( $csv_content );

		if ( empty( $parsed['headers'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'No data found in CSV.', 'oli-store-locator' ),
			);
		}

		$map  = Oli_SL_CSV_Parser::detect_columns( $parsed['headers'] );
		$rows = Oli_SL_CSV_Parser::map_rows( $parsed['rows'], $parsed['headers'], $map );

		$results = array(
			'created'  => 0,
			'updated'  => 0,
			'skipped'  => 0,
			'errors'   => array(),
			'warnings' => array(),
			'success'  => true,
		);

		foreach ( $rows as $index => $data ) {
			$result = self::import_store_row( $data, $update );

			if ( is_wp_error( $result ) ) {
				/* translators: 1: row number, 2: error message */
				$results['errors'][] = sprintf( __( 'Row %1$d: %2$s', 'oli-store-locator' ), $index + 2, $result->get_error_message() );
				++$results['skipped'];
				continue;
			}

			$warning = '';
			if ( is_array( $result ) ) {
				$warning = isset( $result['warning'] ) ? $result['warning'] : '';
				$result  = isset( $result['action'] ) ? $result['action'] : 'skipped';
			}

			if ( '' !== $warning ) {
				/* translators: 1: row number, 2: warning */
				$results['warnings'][] = sprintf( __( 'Row %1$d: %2$s', 'oli-store-locator' ), $index + 2, $warning );
			}

			if ( 'created' === $result ) {
				++$results['created'];
			} elseif ( 'updated' === $result ) {
				++$results['updated'];
			} else {
				++$results['skipped'];
			}
		}

		Oli_SL_Search::clear_cache();

		return $results;
	}

	/**
	 * Import a single store row.
	 *
	 * @param array $data   Store data.
	 * @param bool  $update Whether to update existing stores.
	 * @return string|WP_Error 'created', 'updated', 'skipped', or WP_Error.
	 */
	private static function import_store_row( $data, $update ) {
		if ( empty( $data['Name'] ) ) {
			return new WP_Error( 'missing_name', __( 'Store name is required.', 'oli-store-locator' ) );
		}

		$store_id = 0;

		if ( ! empty( $data['ID'] ) && $update ) {
			$existing = get_post( absint( $data['ID'] ) );
			if ( $existing && 'oli_store' === $existing->post_type ) {
				$store_id = $existing->ID;
			}
		}

		$status = self::sanitize_import_status( isset( $data['Status'] ) ? $data['Status'] : '' );

		$post_data = array(
			'post_type'    => 'oli_store',
			'post_title'   => sanitize_text_field( $data['Name'] ),
			'post_status'  => $status['status'],
			'post_content' => ! empty( $data['Description'] ) ? wp_kses_post( $data['Description'] ) : '',
		);

		if ( $store_id ) {
			$post_data['ID'] = $store_id;
			$result          = wp_update_post( $post_data, true );
			$action          = 'updated';
		} else {
			$result = wp_insert_post( $post_data, true );
			$action = 'created';
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$store_id = $result;
		self::save_row_meta( $store_id, $data, true );

		if ( '' !== $status['warning'] ) {
			return array(
				'action'  => $action,
				'warning' => $status['warning'],
			);
		}

		return $action;
	}

	/**
	 * Allowed CSV Status values.
	 *
	 * @return array
	 */
	public static function allowed_statuses() {
		return array( 'publish', 'draft', 'pending', 'private' );
	}

	/**
	 * Accept only publish, draft, pending, or private. Other values become draft.
	 *
	 * @param mixed $status Raw status.
	 * @return array{status: string, warning: string}
	 */
	public static function sanitize_import_status( $status ) {
		$status = sanitize_key( (string) Oli_SL_CSV_Parser::strip_formula_prefix( $status ) );
		if ( '' === $status ) {
			return array(
				'status'  => 'publish',
				'warning' => '',
			);
		}

		if ( in_array( $status, self::allowed_statuses(), true ) ) {
			return array(
				'status'  => $status,
				'warning' => '',
			);
		}

		return array(
			'status'  => 'draft',
			'warning' => sprintf(
				/* translators: %s: rejected status value */
				__( 'Invalid status “%s” was reset to draft.', 'oli-store-locator' ),
				$status
			),
		);
	}

	/**
	 * Save mapped row fields onto a store.
	 *
	 * @param int   $store_id Store ID.
	 * @param array $data     Mapped row.
	 * @param bool  $geocode  Geocode if coordinates missing.
	 */
	public static function save_row_meta( $store_id, $data, $geocode = false ) {
		$meta_fields = array(
			'_oli_sl_address'     => 'Address',
			'_oli_sl_address_2'   => 'Address_2',
			'_oli_sl_city'        => 'City',
			'_oli_sl_state'       => 'State',
			'_oli_sl_zip'         => 'Zip',
			'_oli_sl_country'     => 'Country',
			'_oli_sl_phone'       => 'Phone',
			'_oli_sl_fax'         => 'Fax',
			'_oli_sl_email'       => 'Email',
			'_oli_sl_url'         => 'URL',
			'_oli_sl_hours'       => 'Hours',
			'_oli_sl_description' => 'Description',
			'_oli_sl_image_url'   => 'Image',
		);

		foreach ( $meta_fields as $meta_key => $csv_key ) {
			if ( isset( $data[ $csv_key ] ) ) {
				if ( 'Email' === $csv_key ) {
					update_post_meta( $store_id, $meta_key, sanitize_email( $data[ $csv_key ] ) );
				} elseif ( in_array( $csv_key, array( 'URL', 'Image' ), true ) ) {
					update_post_meta( $store_id, $meta_key, esc_url_raw( $data[ $csv_key ] ) );
				} else {
					update_post_meta( $store_id, $meta_key, sanitize_text_field( $data[ $csv_key ] ) );
				}
			}
		}

		if ( ! empty( $data['Latitude'] ) && ! empty( $data['Longitude'] ) ) {
			update_post_meta( $store_id, '_oli_sl_latitude', sanitize_text_field( Oli_SL_CSV_Parser::strip_formula_prefix( $data['Latitude'] ) ) );
			update_post_meta( $store_id, '_oli_sl_longitude', sanitize_text_field( Oli_SL_CSV_Parser::strip_formula_prefix( $data['Longitude'] ) ) );
		} elseif ( $geocode ) {
			$address = oli_sl_join_address(
				array(
					isset( $data['Address'] ) ? $data['Address'] : '',
					isset( $data['City'] ) ? $data['City'] : '',
					isset( $data['State'] ) ? $data['State'] : '',
					isset( $data['Zip'] ) ? $data['Zip'] : '',
					isset( $data['Country'] ) ? $data['Country'] : '',
				)
			);
			if ( '' !== $address ) {
				$geocoded = Oli_SL_Geocoding::geocode( $address );
				if ( ! is_wp_error( $geocoded ) ) {
					update_post_meta( $store_id, '_oli_sl_latitude', $geocoded['lat'] );
					update_post_meta( $store_id, '_oli_sl_longitude', $geocoded['lng'] );
				}
			}
		}

		if ( ! empty( $data['Categories'] ) ) {
			$categories = array_map( 'trim', explode( '|', $data['Categories'] ) );
			wp_set_object_terms( $store_id, $categories, 'oli_store_category' );
		}

		if ( ! empty( $data['Tags'] ) ) {
			$tags = array_map( 'trim', explode( '|', $data['Tags'] ) );
			wp_set_object_terms( $store_id, $tags, 'oli_store_tag' );
		}

		if ( ! empty( $data['ID'] ) ) {
			update_post_meta( $store_id, '_oli_sl_external_id', sanitize_text_field( $data['ID'] ) );
		}
	}
}
