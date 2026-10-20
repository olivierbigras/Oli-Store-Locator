<?php
/**
 * Google Sheets sync history log.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Sheets sync history log.
 */
class Oli_SL_Sync_Log {

	const OPTION = 'oli_sl_sheets_sync_log';

	/**
	 * Default number of days to keep entries.
	 *
	 * @var int
	 */
	const DEFAULT_RETENTION_DAYS = 30;

	/**
	 * Maximum number of entries kept after purge.
	 *
	 * @var int
	 */
	const MAX_ENTRIES = 100;

	/**
	 * Append a sync result to the history.
	 *
	 * @param array $results Sync results.
	 * @return array Stored entry.
	 */
	public static function record( $results ) {
		$entry = array(
			'time'    => time(),
			'created' => isset( $results['created'] ) ? (int) $results['created'] : 0,
			'updated' => isset( $results['updated'] ) ? (int) $results['updated'] : 0,
			'deleted' => isset( $results['deleted'] ) ? (int) $results['deleted'] : 0,
			'skipped' => isset( $results['skipped'] ) ? (int) $results['skipped'] : 0,
			'success' => ! empty( $results['success'] ),
			'message' => isset( $results['message'] ) ? (string) $results['message'] : '',
			'errors'  => array(),
		);

		if ( ! empty( $results['errors'] ) && is_array( $results['errors'] ) ) {
			$entry['errors'] = array_values( array_map( 'strval', $results['errors'] ) );
		}

		$log   = self::all();
		$log[] = $entry;
		$log   = self::purge_list( $log );
		update_option( self::OPTION, $log, false );

		return $entry;
	}

	/**
	 * All stored entries, newest last.
	 *
	 * @return array
	 */
	public static function all() {
		$log = get_option( self::OPTION, array() );
		return is_array( $log ) ? $log : array();
	}

	/**
	 * Entries newest first.
	 *
	 * @return array
	 */
	public static function newest_first() {
		$log = self::all();
		return array_reverse( $log );
	}

	/**
	 * Purge old entries using the configured retention.
	 *
	 * @return int Number of remaining entries.
	 */
	public static function purge() {
		$log = self::purge_list( self::all() );
		update_option( self::OPTION, $log, false );
		return count( $log );
	}

	/**
	 * Filter a log list by age and max size.
	 *
	 * @param array $log Entries.
	 * @param int   $now Timestamp.
	 * @return array
	 */
	public static function purge_list( $log, $now = 0 ) {
		$now      = $now ? (int) $now : time();
		$days     = (int) oli_sl_get_option( 'sync_log_retention_days', (string) self::DEFAULT_RETENTION_DAYS );
		$days     = $days > 0 ? $days : self::DEFAULT_RETENTION_DAYS;
		$cutoff   = $now - ( $days * DAY_IN_SECONDS );
		$filtered = array();

		foreach ( (array) $log as $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}
			$time = isset( $entry['time'] ) ? (int) $entry['time'] : 0;
			if ( $time >= $cutoff ) {
				$filtered[] = $entry;
			}
		}

		if ( count( $filtered ) > self::MAX_ENTRIES ) {
			$filtered = array_slice( $filtered, -1 * self::MAX_ENTRIES );
		}

		return array_values( $filtered );
	}

	/**
	 * Delete the log.
	 */
	public static function clear() {
		delete_option( self::OPTION );
	}
}
