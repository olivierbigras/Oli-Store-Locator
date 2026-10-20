<?php
/**
 * Fired during plugin activation.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin activation.
 */
class Oli_SL_Activator {

	/**
	 * Activation actions.
	 */
	public static function activate() {
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-post-type.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-taxonomies.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/oli-sl-functions.php';

		$post_type = new Oli_SL_Post_Type();
		$post_type->register_post_type();

		$taxonomies = new Oli_SL_Taxonomies();
		$taxonomies->register_taxonomies();

		flush_rewrite_rules();

		self::set_default_options();
		self::schedule_sync_cron();
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options() {
		$existing = get_option( 'oli_sl_options', false );
		if ( false === $existing ) {
			add_option( 'oli_sl_options', oli_sl_default_options() );
		}
	}

	/**
	 * Schedule Google Sheets sync cron events.
	 */
	private static function schedule_sync_cron() {
		if ( ! wp_next_scheduled( 'oli_sl_sync_google_sheets_5min' ) ) {
			wp_schedule_event( time(), 'oli_sl_every_5min', 'oli_sl_sync_google_sheets_5min' );
		}
		if ( ! wp_next_scheduled( 'oli_sl_sync_google_sheets_15min' ) ) {
			wp_schedule_event( time(), 'oli_sl_every_15min', 'oli_sl_sync_google_sheets_15min' );
		}
		if ( ! wp_next_scheduled( 'oli_sl_sync_google_sheets_hourly' ) ) {
			wp_schedule_event( time(), 'hourly', 'oli_sl_sync_google_sheets_hourly' );
		}
		if ( ! wp_next_scheduled( 'oli_sl_sync_google_sheets_daily' ) ) {
			wp_schedule_event( time(), 'daily', 'oli_sl_sync_google_sheets_daily' );
		}
		if ( ! wp_next_scheduled( 'oli_sl_purge_sync_log' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'oli_sl_purge_sync_log' );
		}
	}
}
