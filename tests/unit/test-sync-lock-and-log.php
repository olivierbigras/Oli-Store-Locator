<?php
/**
 * Sync lock and history log tests.
 *
 * @package Oli_Store_Locator
 */

class Oli_SL_Test_Sync_Lock_And_Log {

	/**
	 * Run tests.
	 *
	 * @return array
	 */
	public static function run() {
		return array(
			self::test_lock_blocks_second_acquire(),
			self::test_log_records_counts(),
			self::test_log_purge_old_entries(),
			self::test_hours_minutes(),
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
	 * Lock cannot be acquired twice.
	 *
	 * @return array
	 */
	private static function test_lock_blocks_second_acquire() {
		oli_sl_test_reset();
		$first  = Oli_SL_Sync_Lock::acquire( 60 );
		$second = Oli_SL_Sync_Lock::acquire( 60 );
		Oli_SL_Sync_Lock::release();
		$third = Oli_SL_Sync_Lock::acquire( 60 );
		return self::assert_true( $first && ! $second && $third, 'Sync lock prevents overlapping runs' );
	}

	/**
	 * History stores added/modified/deleted counts.
	 *
	 * @return array
	 */
	private static function test_log_records_counts() {
		oli_sl_test_reset();
		Oli_SL_Sync_Log::record(
			array(
				'success' => true,
				'created' => 2,
				'updated' => 3,
				'deleted' => 1,
				'skipped' => 0,
				'message' => 'Sync completed.',
				'errors'  => array(),
			)
		);
		$all = Oli_SL_Sync_Log::all();
		$ok  = 1 === count( $all ) && 2 === $all[0]['created'] && 3 === $all[0]['updated'] && 1 === $all[0]['deleted'];
		return self::assert_true( $ok, 'Sync log stores added, modified and deleted counts' );
	}

	/**
	 * Old entries are purged.
	 *
	 * @return array
	 */
	private static function test_log_purge_old_entries() {
		oli_sl_test_reset();
		$now = time();
		$log = array(
			array(
				'time'    => $now - ( 40 * DAY_IN_SECONDS ),
				'created' => 1,
				'updated' => 0,
				'deleted' => 0,
				'errors'  => array(),
			),
			array(
				'time'    => $now - 60,
				'created' => 4,
				'updated' => 0,
				'deleted' => 0,
				'errors'  => array(),
			),
		);
		$kept = Oli_SL_Sync_Log::purge_list( $log, $now );
		$ok   = 1 === count( $kept ) && 4 === $kept[0]['created'];
		return self::assert_true( $ok, 'Sync log purges entries older than the retention window' );
	}

	/**
	 * Hours minutes parser.
	 *
	 * @return array
	 */
	private static function test_hours_minutes() {
		$ok = 540 === Oli_SL_Hours::minutes( '09:00' ) && 1020 === Oli_SL_Hours::minutes( '17:00' ) && null === Oli_SL_Hours::minutes( 'nope' );
		return self::assert_true( $ok, 'Opening hours parser converts HH:MM to minutes' );
	}

}
