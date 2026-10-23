<?php
/**
 * Overlap lock for Google Sheets sync.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Overlap lock for Google Sheets sync.
 */
class Oli_SL_Sync_Lock {

	const TRANSIENT = 'oli_sl_sheets_sync_lock';

	/**
	 * Default lock lifetime in seconds.
	 *
	 * @var int
	 */
	const TTL = 300;

	/**
	 * Attempt to acquire the lock.
	 *
	 * @param int $ttl Seconds.
	 * @return bool
	 */
	public static function acquire( $ttl = 0 ) {
		$ttl = $ttl > 0 ? (int) $ttl : (int) apply_filters( 'oli_sl_sheets_lock_ttl', self::TTL );
		if ( get_transient( self::TRANSIENT ) ) {
			return false;
		}

		set_transient( self::TRANSIENT, time(), $ttl );
		return true;
	}

	/**
	 * Release the lock.
	 */
	public static function release() {
		delete_transient( self::TRANSIENT );
	}

	/**
	 * Whether a sync is currently locked.
	 *
	 * @return bool
	 */
	public static function is_locked() {
		return (bool) get_transient( self::TRANSIENT );
	}
}
