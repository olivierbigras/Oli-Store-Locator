<?php
/**
 * Google Sheets sync functionality.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Sheets sync functionality.
 */
class Oli_SL_Google_Sheets_Sync {

	/**
	 * Run scheduled sync if this hook matches the configured frequency.
	 */
	public function run_scheduled_sync() {
		if ( '1' !== (string) oli_sl_get_option( 'sheets_sync_enabled', '0' ) ) {
			return;
		}

		if ( '1' === (string) get_option( 'oli_sl_sheets_paused', '0' ) ) {
			return;
		}

		$frequency      = oli_sl_get_option( 'sheets_sync_frequency', 'hourly' );
		$current_action = current_filter();
		$frequency_map  = array(
			'oli_sl_sync_google_sheets_5min'   => '5min',
			'oli_sl_sync_google_sheets_15min'  => '15min',
			'oli_sl_sync_google_sheets_hourly' => 'hourly',
			'oli_sl_sync_google_sheets_daily'  => 'daily',
		);

		if ( ! isset( $frequency_map[ $current_action ] ) || $frequency_map[ $current_action ] !== $frequency ) {
			return;
		}

		$this->sync();
	}

	/**
	 * Fetch the sheet, compute a diff, and process in batches.
	 *
	 * @return array Sync results.
	 */
	public function sync() {
		update_option( 'oli_sl_sheets_last_checked', time() );

		if ( ! Oli_SL_Sync_Lock::acquire() ) {
			$results = array(
				'success' => false,
				'message' => __( 'A sync is already running. Try again in a few minutes.', 'oli-store-locator' ),
				'created' => 0,
				'updated' => 0,
				'skipped' => 0,
				'deleted' => 0,
				'errors'  => array( __( 'A sync is already running. Try again in a few minutes.', 'oli-store-locator' ) ),
			);
			update_option( 'oli_sl_sheets_sync_results', $results );
			Oli_SL_Sync_Log::record( $results );
			return $results;
		}

		$fetched = $this->fetch_sheet();
		if ( is_wp_error( $fetched ) ) {
			$this->log_sync_error( $fetched->get_error_message() );
			$results = array(
				'success' => false,
				'message' => $fetched->get_error_message(),
				'created' => 0,
				'updated' => 0,
				'skipped' => 0,
				'deleted' => 0,
				'errors'  => array( $fetched->get_error_message() ),
			);
			update_option( 'oli_sl_sheets_sync_results', $results );
			Oli_SL_Sync_Log::record( $results );
			Oli_SL_Sync_Lock::release();
			return $results;
		}

		$column_map = oli_sl_get_option( 'sheets_column_map', array() );
		$mapped     = Oli_SL_CSV_Parser::map_rows( $fetched['rows'], $fetched['headers'], $column_map );
		$existing   = $this->get_existing_synced_stores();
		$removed    = oli_sl_get_option( 'sheets_removed_action', 'none' );
		$diff       = Oli_SL_Sync_Diff::diff( $existing, $mapped, $removed );
		$batch_size = (int) oli_sl_get_option( 'sheets_batch_size', '25' );
		$batches    = Oli_SL_Sync_Diff::batch( $diff, $batch_size );

		$state = array(
			'success' => true,
			'created' => 0,
			'updated' => 0,
			'skipped' => count( $diff['skip'] ),
			'deleted' => 0,
			'errors'  => array(),
			'message' => '',
		);

		foreach ( $diff['skip'] as $skip ) {
			if ( ! empty( $skip['reason'] ) ) {
				/* translators: 1: row number, 2: skip reason */
				$state['errors'][] = sprintf( __( 'Row %1$d skipped (%2$s).', 'oli-store-locator' ), (int) $skip['index'] + 2, $skip['reason'] );
			}
		}

		set_transient( 'oli_sl_sheets_sync_state', $state, HOUR_IN_SECONDS );
		set_transient( 'oli_sl_sheets_sync_batches', $batches, HOUR_IN_SECONDS );

		if ( empty( $batches ) ) {
			$state['message'] = __( 'All up to date', 'oli-store-locator' );
			update_option( 'oli_sl_sheets_last_sync', time() );
			update_option( 'oli_sl_sheets_sync_results', $state );
			delete_transient( 'oli_sl_sheets_sync_batches' );
			delete_transient( 'oli_sl_sheets_sync_state' );
			Oli_SL_Sync_Log::record( $state );
			Oli_SL_Search::clear_cache();
			Oli_SL_Sync_Lock::release();
			return $state;
		}

		return $this->process_batch();
	}

	/**
	 * Process one batch of sync operations.
	 *
	 * @return array
	 */
	public function process_batch() {
		Oli_SL_Sync_Lock::acquire();
		$batches = get_transient( 'oli_sl_sheets_sync_batches' );
		$state   = get_transient( 'oli_sl_sheets_sync_state' );

		if ( ! is_array( $batches ) || empty( $batches ) ) {
			if ( is_array( $state ) ) {
				$state['message'] = ( 0 === (int) $state['created'] && 0 === (int) $state['updated'] && 0 === (int) $state['deleted'] )
					? __( 'All up to date', 'oli-store-locator' )
					: __( 'Sync completed.', 'oli-store-locator' );
				update_option( 'oli_sl_sheets_last_sync', time() );
				update_option( 'oli_sl_sheets_sync_results', $state );
				Oli_SL_Sync_Log::record( $state );
			}
			delete_transient( 'oli_sl_sheets_sync_batches' );
			delete_transient( 'oli_sl_sheets_sync_state' );
			Oli_SL_Search::clear_cache();
			Oli_SL_Sync_Lock::release();
			return is_array( $state ) ? $state : array( 'success' => true );
		}

		if ( ! is_array( $state ) ) {
			$state = array(
				'success' => true,
				'created' => 0,
				'updated' => 0,
				'skipped' => 0,
				'deleted' => 0,
				'errors'  => array(),
				'message' => '',
			);
		}

		$batch = array_shift( $batches );

		foreach ( $batch as $item ) {
			$op = isset( $item['op'] ) ? $item['op'] : '';

			if ( 'create' === $op ) {
				$result = $this->create_store( $item['row'], $item['key'], ! empty( $item['geocode'] ) );
				if ( is_wp_error( $result ) ) {
					$state['errors'][] = $result->get_error_message();
					++$state['skipped'];
				} else {
					++$state['created'];
				}
			} elseif ( 'update' === $op ) {
				$result = $this->update_store( $item['id'], $item['row'], $item['key'], ! empty( $item['geocode'] ) );
				if ( is_wp_error( $result ) ) {
					$state['errors'][] = $result->get_error_message();
					++$state['skipped'];
				} else {
					++$state['updated'];
				}
			} elseif ( 'remove' === $op ) {
				$this->remove_store( $item['id'], $item['action'] );
				++$state['deleted'];
			}
		}

		if ( empty( $batches ) ) {
			$state['message'] = ( 0 === (int) $state['created'] && 0 === (int) $state['updated'] && 0 === (int) $state['deleted'] )
				? __( 'All up to date', 'oli-store-locator' )
				: __( 'Sync completed.', 'oli-store-locator' );
			update_option( 'oli_sl_sheets_last_sync', time() );
			update_option( 'oli_sl_sheets_sync_results', $state );
			delete_transient( 'oli_sl_sheets_sync_batches' );
			delete_transient( 'oli_sl_sheets_sync_state' );
			Oli_SL_Sync_Log::record( $state );
			Oli_SL_Search::clear_cache();
			Oli_SL_Sync_Lock::release();
			return $state;
		}

		set_transient( 'oli_sl_sheets_sync_batches', $batches, HOUR_IN_SECONDS );
		set_transient( 'oli_sl_sheets_sync_state', $state, HOUR_IN_SECONDS );
		wp_schedule_single_event( time() + 5, 'oli_sl_sync_google_sheets_batch' );

		$state['message'] = __( 'Sync in progress…', 'oli-store-locator' );
		update_option( 'oli_sl_sheets_sync_results', $state );

		return $state;
	}

	/**
	 * Whether a URL uses an allowed Google Sheets host.
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_allowed_host( $url ) {
		return Oli_SL_CSV_Parser::is_allowed_sheets_host( $url );
	}

	/**
	 * GET a Google Sheets URL via wp_safe_remote_get after host checks.
	 *
	 * @param string $url  URL.
	 * @param array  $args Request args.
	 * @return array|WP_Error
	 */
	public static function safe_remote_get( $url, $args = array() ) {
		if ( ! self::is_allowed_host( $url ) ) {
			return new WP_Error( 'invalid_host', __( 'The URL is not an allowed Google Sheets host.', 'oli-store-locator' ) );
		}

		return wp_safe_remote_get( $url, $args );
	}

	/**
	 * Fetch CSV rows from the configured Google Sheet.
	 *
	 * @return array|WP_Error
	 */
	public function fetch_sheet() {
		$sheet_url = oli_sl_get_option( 'sheets_url', '' );
		if ( '' === $sheet_url ) {
			return new WP_Error( 'no_url', __( 'No Google Sheets URL configured.', 'oli-store-locator' ) );
		}

		if ( ! Oli_SL_CSV_Parser::is_valid_sheets_url( $sheet_url ) ) {
			return new WP_Error( 'invalid_url', __( 'The Google Sheet URL was not saved. Use a https://docs.google.com/spreadsheets/d/… link or a published CSV export URL.', 'oli-store-locator' ) );
		}

		$api_key = oli_sl_get_option( 'sheets_api_key', '' );
		$csv     = '';

		if ( is_string( $api_key ) && '' !== $api_key ) {
			$csv = $this->fetch_via_api( $sheet_url, $api_key );
			if ( is_wp_error( $csv ) ) {
				return $csv;
			}
		}

		if ( '' === $csv ) {
			$csv_url = Oli_SL_CSV_Parser::sheets_export_url( $sheet_url );
			if ( ! $csv_url ) {
				return new WP_Error( 'invalid_url', __( 'Invalid Google Sheets URL.', 'oli-store-locator' ) );
			}

			$response = self::safe_remote_get(
				$csv_url,
				array(
					'timeout' => 30,
					'headers' => array(
						'user-agent' => 'Oli Store Locator/' . OLI_SL_VERSION,
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = wp_remote_retrieve_response_code( $response );
			$csv  = wp_remote_retrieve_body( $response );

			if ( $code >= 400 || '' === $csv ) {
				return new WP_Error( 'empty_sheet', __( 'Empty response from Google Sheets. Make sure the sheet is shared with “anyone with the link can view”.', 'oli-store-locator' ) );
			}
		}

		$parsed = Oli_SL_CSV_Parser::parse( $csv );
		if ( empty( $parsed['headers'] ) ) {
			return new WP_Error( 'no_data', __( 'No data found in Google Sheets.', 'oli-store-locator' ) );
		}

		return $parsed;
	}

	/**
	 * Fetch via Google Sheets API v4 (optional key mode).
	 *
	 * @param string $sheet_url Sheet URL.
	 * @param string $api_key   API key.
	 * @return string|WP_Error CSV string or error.
	 */
	private function fetch_via_api( $sheet_url, $api_key ) {
		if ( ! self::is_allowed_host( $sheet_url ) ) {
			return new WP_Error( 'invalid_host', __( 'The URL is not an allowed Google Sheets host.', 'oli-store-locator' ) );
		}

		$sheet_id = Oli_SL_CSV_Parser::sheets_id( $sheet_url );
		if ( ! $sheet_id ) {
			return new WP_Error( 'invalid_url', __( 'Invalid Google Sheets URL.', 'oli-store-locator' ) );
		}

		$gid      = Oli_SL_CSV_Parser::sheets_gid( $sheet_url );
		$meta_url = add_query_arg(
			array(
				'key'    => $api_key,
				'fields' => 'sheets.properties',
			),
			'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode( $sheet_id )
		);

		$meta = self::safe_remote_get( $meta_url, array( 'timeout' => 30 ) );
		if ( is_wp_error( $meta ) ) {
			return $meta;
		}

		$body  = json_decode( wp_remote_retrieve_body( $meta ), true );
		$title = 'Sheet1';

		if ( ! empty( $body['sheets'] ) && is_array( $body['sheets'] ) ) {
			foreach ( $body['sheets'] as $sheet ) {
				if ( isset( $sheet['properties']['sheetId'] ) && (string) $sheet['properties']['sheetId'] === (string) $gid ) {
					$title = $sheet['properties']['title'];
					break;
				}
			}
			if ( 'Sheet1' === $title && isset( $body['sheets'][0]['properties']['title'] ) ) {
				$title = $body['sheets'][0]['properties']['title'];
			}
		}

		$values_url = add_query_arg(
			array(
				'key' => $api_key,
			),
			sprintf(
				'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
				rawurlencode( $sheet_id ),
				rawurlencode( $title )
			)
		);

		$values = self::safe_remote_get( $values_url, array( 'timeout' => 30 ) );
		if ( is_wp_error( $values ) ) {
			return $values;
		}

		$data = json_decode( wp_remote_retrieve_body( $values ), true );
		if ( empty( $data['values'] ) || ! is_array( $data['values'] ) ) {
			return new WP_Error( 'empty_sheet', __( 'No data found in Google Sheets.', 'oli-store-locator' ) );
		}

		return Oli_SL_CSV_Parser::array_to_csv( $data['values'] );
	}

	/**
	 * Existing stores keyed by sync key.
	 *
	 * @return array
	 */
	private function get_existing_synced_stores() {
		$posts = get_posts(
			array(
				'post_type'      => 'oli_store',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- sync key lookup.
					array(
						'key'     => '_oli_sl_sync_key',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$existing = array();
		foreach ( $posts as $post ) {
			$key = get_post_meta( $post->ID, '_oli_sl_sync_key', true );
			if ( '' === $key ) {
				continue;
			}
			$existing[ $key ] = array(
				'id'          => $post->ID,
				'address_sig' => Oli_SL_Sync_Diff::address_signature(
					array(
						'Address'   => oli_sl_get_store_meta( $post->ID, 'address' ),
						'Address_2' => oli_sl_get_store_meta( $post->ID, 'address_2' ),
						'City'      => oli_sl_get_store_meta( $post->ID, 'city' ),
						'State'     => oli_sl_get_store_meta( $post->ID, 'state' ),
						'Zip'       => oli_sl_get_store_meta( $post->ID, 'zip' ),
						'Country'   => oli_sl_get_store_meta( $post->ID, 'country' ),
					)
				),
			);
		}

		return $existing;
	}

	/**
	 * Create a store from a mapped row.
	 *
	 * @param array  $row     Row.
	 * @param string $key     Sync key.
	 * @param bool   $geocode Geocode.
	 * @return int|WP_Error
	 */
	private function create_store( $row, $key, $geocode ) {
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'oli_store',
				'post_title'   => sanitize_text_field( $row['Name'] ),
				'post_status'  => 'publish',
				'post_content' => ! empty( $row['Description'] ) ? wp_kses_post( $row['Description'] ) : '',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, '_oli_sl_sync_key', sanitize_text_field( $key ) );
		Oli_SL_CSV_Handler::save_row_meta( $post_id, $row, $geocode );

		return $post_id;
	}

	/**
	 * Update a store from a mapped row.
	 *
	 * @param int    $store_id Store ID.
	 * @param array  $row      Row.
	 * @param string $key      Sync key.
	 * @param bool   $geocode  Geocode.
	 * @return int|WP_Error
	 */
	private function update_store( $store_id, $row, $key, $geocode ) {
		$result = wp_update_post(
			array(
				'ID'           => $store_id,
				'post_title'   => sanitize_text_field( $row['Name'] ),
				'post_content' => ! empty( $row['Description'] ) ? wp_kses_post( $row['Description'] ) : get_post_field( 'post_content', $store_id ),
				'post_status'  => 'publish',
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		update_post_meta( $store_id, '_oli_sl_sync_key', sanitize_text_field( $key ) );
		Oli_SL_CSV_Handler::save_row_meta( $store_id, $row, $geocode );

		return $store_id;
	}

	/**
	 * Remove a store no longer in the sheet.
	 *
	 * @param int    $store_id Store ID.
	 * @param string $action   draft or delete.
	 */
	private function remove_store( $store_id, $action ) {
		if ( 'draft' === $action ) {
			wp_update_post(
				array(
					'ID'          => $store_id,
					'post_status' => 'draft',
				)
			);
			return;
		}

		wp_trash_post( $store_id );
	}

	/**
	 * Next scheduled sync timestamp.
	 *
	 * @return int
	 */
	public static function next_sync_time() {
		$frequency = oli_sl_get_option( 'sheets_sync_frequency', 'hourly' );
		$map       = array(
			'5min'   => 'oli_sl_sync_google_sheets_5min',
			'15min'  => 'oli_sl_sync_google_sheets_15min',
			'hourly' => 'oli_sl_sync_google_sheets_hourly',
			'daily'  => 'oli_sl_sync_google_sheets_daily',
		);
		$hook      = isset( $map[ $frequency ] ) ? $map[ $frequency ] : 'oli_sl_sync_google_sheets_hourly';
		$next      = wp_next_scheduled( $hook );
		return $next ? (int) $next : 0;
	}

	/**
	 * Log sync error.
	 *
	 * @param string $message Error message.
	 */
	private function log_sync_error( $message ) {
		$errors   = get_option( 'oli_sl_sheets_sync_errors', array() );
		$errors   = is_array( $errors ) ? $errors : array();
		$errors[] = array(
			'time'    => time(),
			'message' => $message,
		);
		$errors   = array_slice( $errors, -20 );
		update_option( 'oli_sl_sheets_sync_errors', $errors );
	}
}
