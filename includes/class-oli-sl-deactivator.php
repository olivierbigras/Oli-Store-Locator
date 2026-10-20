<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin deactivation.
 */
class Oli_SL_Deactivator {

	/**
	 * Deactivation actions.
	 */
	public static function deactivate() {
		// Clear scheduled cron events.
		wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_5min' );
		wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_15min' );
		wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_hourly' );
		wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_daily' );
		wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_batch' );
		wp_clear_scheduled_hook( 'oli_sl_purge_sync_log' );

		flush_rewrite_rules();
	}
}
