<?php
/**
 * Sync diff tests.
 *
 * @package Oli_Store_Locator
 */

class Oli_SL_Test_Sync_Diff {

	/**
	 * Run tests.
	 *
	 * @return array
	 */
	public static function run() {
		return array(
			self::test_key_prefers_id(),
			self::test_key_falls_back_to_hash(),
			self::test_create_update_skip(),
			self::test_geocode_only_when_address_changes(),
			self::test_removed_draft_or_delete(),
			self::test_removed_none(),
			self::test_duplicate_sheet_rows_skipped(),
			self::test_batching(),
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
	 * ID column wins.
	 *
	 * @return array
	 */
	private static function test_key_prefers_id() {
		$key = Oli_SL_Sync_Diff::make_key(
			array(
				'ID'      => 'store-mtl',
				'Name'    => 'Montreal Flagship',
				'Address' => '123 Rue Saint-Denis',
			)
		);
		return self::assert_true( 'id_store-mtl' === $key, 'Stable key prefers ID column' );
	}

	/**
	 * Hash fallback.
	 *
	 * @return array
	 */
	private static function test_key_falls_back_to_hash() {
		$row = array(
			'Name'    => 'Montreal Flagship',
			'Address' => '123 Rue Saint-Denis',
		);
		$key = Oli_SL_Sync_Diff::make_key( $row );
		$ok  = 0 === strpos( $key, 'hash_' ) && $key === Oli_SL_Sync_Diff::make_key( $row );
		return self::assert_true( $ok, 'Key falls back to hash of name+address' );
	}

	/**
	 * Create / update / skip.
	 *
	 * @return array
	 */
	private static function test_create_update_skip() {
		$existing = array(
			'id_store-mtl' => array(
				'id'          => 10,
				'address_sig' => Oli_SL_Sync_Diff::address_signature(
					array(
						'Address' => '123 Rue Saint-Denis',
						'City'    => 'Montreal',
						'State'   => 'QC',
						'Zip'     => 'H2X 3K9',
						'Country' => 'Canada',
					)
				),
			),
		);
		$incoming = array(
			array(
				'ID'        => 'store-mtl',
				'Name'      => 'Montreal Flagship',
				'Address'   => '123 Rue Saint-Denis',
				'City'      => 'Montreal',
				'State'     => 'QC',
				'Zip'       => 'H2X 3K9',
				'Country'   => 'Canada',
				'Latitude'  => '45.5',
				'Longitude' => '-73.5',
			),
			array(
				'ID'      => 'store-to',
				'Name'    => 'Toronto Store',
				'Address' => '250 Queen Street West',
			),
			array(
				'Name' => '',
			),
		);
		$diff = Oli_SL_Sync_Diff::diff( $existing, $incoming, 'none' );
		$ok   = 1 === count( $diff['update'] ) && 1 === count( $diff['create'] ) && 1 === count( $diff['skip'] ) && 'missing_name' === $diff['skip'][0]['reason'];
		return self::assert_true( $ok, 'Diff creates, updates, and skips missing names' );
	}

	/**
	 * Geocode only when address changes and coords missing.
	 *
	 * @return array
	 */
	private static function test_geocode_only_when_address_changes() {
		$sig = Oli_SL_Sync_Diff::address_signature(
			array(
				'Address' => '123 Rue Saint-Denis',
				'City'    => 'Montreal',
			)
		);
		$existing = array(
			'id_a' => array(
				'id'          => 1,
				'address_sig' => $sig,
			),
			'id_b' => array(
				'id'          => 2,
				'address_sig' => $sig,
			),
		);
		$incoming = array(
			array(
				'ID'      => 'a',
				'Name'    => 'Same',
				'Address' => '123 Rue Saint-Denis',
				'City'    => 'Montreal',
			),
			array(
				'ID'      => 'b',
				'Name'    => 'Moved',
				'Address' => '999 New Street',
				'City'    => 'Montreal',
			),
		);
		$diff = Oli_SL_Sync_Diff::diff( $existing, $incoming, 'none' );
		$ok   = 1 === count( $diff['geocode'] ) && 'id_b' === $diff['geocode'][0];
		return self::assert_true( $ok, 'Geocode only when address changed and lat/lng missing' );
	}

	/**
	 * Removed stores.
	 *
	 * @return array
	 */
	private static function test_removed_draft_or_delete() {
		$existing = array(
			'id_keep'   => array(
				'id'          => 1,
				'address_sig' => '',
			),
			'id_remove' => array(
				'id'          => 2,
				'address_sig' => '',
			),
		);
		$incoming = array(
			array(
				'ID'   => 'keep',
				'Name' => 'Keep me',
			),
		);
		$diff = Oli_SL_Sync_Diff::diff( $existing, $incoming, 'draft' );
		$ok   = 1 === count( $diff['remove'] ) && 'draft' === $diff['remove'][0]['action'] && 'id_remove' === $diff['remove'][0]['key'];
		return self::assert_true( $ok, 'Removed sheet rows can be drafted' );
	}

	/**
	 * Removed action none.
	 *
	 * @return array
	 */
	private static function test_removed_none() {
		$existing = array(
			'id_keep' => array(
				'id'          => 1,
				'address_sig' => '',
			),
			'id_gone' => array(
				'id'          => 2,
				'address_sig' => '',
			),
		);
		$incoming = array(
			array(
				'ID'   => 'keep',
				'Name' => 'Keep me',
			),
		);
		$diff = Oli_SL_Sync_Diff::diff( $existing, $incoming, 'none' );
		return self::assert_true( empty( $diff['remove'] ), 'Removed action none leaves extra stores' );
	}

	/**
	 * Duplicate keys in the sheet.
	 *
	 * @return array
	 */
	private static function test_duplicate_sheet_rows_skipped() {
		$incoming = array(
			array(
				'ID'   => 'dup',
				'Name' => 'One',
			),
			array(
				'ID'   => 'dup',
				'Name' => 'Two',
			),
		);
		$diff = Oli_SL_Sync_Diff::diff( array(), $incoming, 'none' );
		$ok   = 1 === count( $diff['create'] ) && 1 === count( $diff['skip'] ) && 'duplicate_in_sheet' === $diff['skip'][0]['reason'];
		return self::assert_true( $ok, 'Duplicate sheet keys are skipped' );
	}

	/**
	 * Batching.
	 *
	 * @return array
	 */
	private static function test_batching() {
		$incoming = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			$incoming[] = array(
				'ID'   => 's' . $i,
				'Name' => 'Store ' . $i,
			);
		}
		$diff    = Oli_SL_Sync_Diff::diff( array(), $incoming, 'none' );
		$batches = Oli_SL_Sync_Diff::batch( $diff, 2 );
		$ok      = 3 === count( $batches ) && 2 === count( $batches[0] ) && 1 === count( $batches[2] );
		return self::assert_true( $ok, 'Diff operations split into batches' );
	}
}
