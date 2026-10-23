<?php
/**
 * Uninstall Oli Store Locator.
 *
 * Store posts and taxonomies are left in place so content is not destroyed.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'oli_sl_options' );
delete_option( 'oli_sl_sheets_last_sync' );
delete_option( 'oli_sl_sheets_last_checked' );
delete_option( 'oli_sl_sheets_sync_results' );
delete_option( 'oli_sl_sheets_sync_errors' );
delete_option( 'oli_sl_sheets_paused' );
delete_option( 'oli_sl_sheets_sync_log' );

wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_5min' );
wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_15min' );
wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_hourly' );
wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_daily' );
wp_clear_scheduled_hook( 'oli_sl_sync_google_sheets_batch' );

$admin_role = function_exists( 'get_role' ) ? get_role( 'administrator' ) : null;
if ( $admin_role && $admin_role->has_cap( 'oli_sl_geocode' ) ) {
	$admin_role->remove_cap( 'oli_sl_geocode' );
}

global $wpdb;
$like = $wpdb->esc_like( '_transient_oli_sl_' ) . '%';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
$like_timeout = $wpdb->esc_like( '_transient_timeout_oli_sl_' ) . '%';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like_timeout ) );
