<?php
/**
 * Idempotent Google Sheets / CSV sync diff.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compute create/update/delete/skip actions without WordPress.
 */
class Oli_SL_Sync_Diff {

	/**
	 * Stable identity for a mapped row.
	 *
	 * Prefers an ID column; otherwise hashes name + address.
	 *
	 * @param array $row Mapped row.
	 * @return string
	 */
	public static function make_key( $row ) {
		if ( ! empty( $row['ID'] ) ) {
			return 'id_' . trim( (string) $row['ID'] );
		}

		$name    = isset( $row['Name'] ) ? trim( (string) $row['Name'] ) : '';
		$address = isset( $row['Address'] ) ? trim( (string) $row['Address'] ) : '';

		return 'hash_' . md5( $name . '|' . $address );
	}

	/**
	 * Address signature used to decide whether geocoding is needed.
	 *
	 * @param array $row Mapped row or existing store snapshot.
	 * @return string
	 */
	public static function address_signature( $row ) {
		$parts = array(
			isset( $row['Address'] ) ? trim( (string) $row['Address'] ) : '',
			isset( $row['Address_2'] ) ? trim( (string) $row['Address_2'] ) : '',
			isset( $row['City'] ) ? trim( (string) $row['City'] ) : '',
			isset( $row['State'] ) ? trim( (string) $row['State'] ) : '',
			isset( $row['Zip'] ) ? trim( (string) $row['Zip'] ) : '',
			isset( $row['Country'] ) ? trim( (string) $row['Country'] ) : '',
		);

		return strtolower( implode( '|', $parts ) );
	}

	/**
	 * Whether a mapped row already includes coordinates.
	 *
	 * @param array $row Mapped row.
	 * @return bool
	 */
	public static function has_coordinates( $row ) {
		if ( ! isset( $row['Latitude'], $row['Longitude'] ) ) {
			return false;
		}

		$lat = trim( (string) $row['Latitude'] );
		$lng = trim( (string) $row['Longitude'] );

		return '' !== $lat && '' !== $lng;
	}

	/**
	 * Diff incoming sheet rows against existing synced stores.
	 *
	 * @param array  $existing        Keyed by sync key: id, address_sig.
	 * @param array  $incoming        Mapped rows.
	 * @param string $removed_action  none, draft, or delete.
	 * @return array
	 */
	public static function diff( $existing, $incoming, $removed_action = 'none' ) {
		$create  = array();
		$update  = array();
		$skip    = array();
		$remove  = array();
		$geocode = array();
		$seen    = array();

		foreach ( (array) $incoming as $index => $row ) {
			$name = isset( $row['Name'] ) ? trim( (string) $row['Name'] ) : '';
			if ( '' === $name ) {
				$skip[] = array(
					'index'  => $index,
					'reason' => 'missing_name',
				);
				continue;
			}

			$key = self::make_key( $row );
			if ( isset( $seen[ $key ] ) ) {
				$skip[] = array(
					'index'  => $index,
					'key'    => $key,
					'reason' => 'duplicate_in_sheet',
				);
				continue;
			}
			$seen[ $key ] = true;

			$needs_geocode = ! self::has_coordinates( $row );

			if ( ! isset( $existing[ $key ] ) ) {
				$create[] = array(
					'key'     => $key,
					'row'     => $row,
					'geocode' => $needs_geocode,
				);
				if ( $needs_geocode ) {
					$geocode[] = $key;
				}
				continue;
			}

			$address_changed = self::address_signature( $row ) !== $existing[ $key ]['address_sig'];
			$needs_geocode   = $needs_geocode && $address_changed;

			$update[] = array(
				'key'     => $key,
				'id'      => $existing[ $key ]['id'],
				'row'     => $row,
				'geocode' => $needs_geocode,
			);

			if ( $needs_geocode ) {
				$geocode[] = $key;
			}
		}

		if ( in_array( $removed_action, array( 'draft', 'delete' ), true ) ) {
			foreach ( (array) $existing as $key => $store ) {
				if ( ! isset( $seen[ $key ] ) ) {
					$remove[] = array(
						'key'    => $key,
						'id'     => $store['id'],
						'action' => $removed_action,
					);
				}
			}
		}

		return array(
			'create'  => $create,
			'update'  => $update,
			'skip'    => $skip,
			'remove'  => $remove,
			'geocode' => $geocode,
		);
	}

	/**
	 * Split a diff into batches.
	 *
	 * @param array $diff       Diff array.
	 * @param int   $batch_size Batch size.
	 * @return array
	 */
	public static function batch( $diff, $batch_size = 25 ) {
		$batch_size = max( 1, (int) $batch_size );
		$ops        = array();

		foreach ( array( 'create', 'update', 'remove' ) as $type ) {
			foreach ( $diff[ $type ] as $item ) {
				$item['op'] = $type;
				$ops[]      = $item;
			}
		}

		return array_chunk( $ops, $batch_size );
	}
}
