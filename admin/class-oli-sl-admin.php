<?php
/**
 * Admin assets and actions.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin assets and actions.
 */
class Oli_SL_Admin {

	/**
	 * Enqueue admin styles.
	 *
	 * @param string $hook Current hook.
	 */
	public function enqueue_styles( $hook ) {
		if ( false === strpos( (string) $hook, 'oli_store' ) && ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$allowed = array( 'oli_store', 'oli_store_page_oli-sl-settings', 'edit-oli_store_category' );
		if ( ! in_array( $screen->id, $allowed, true ) && 'oli_store' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'oli-sl-admin',
			OLI_SL_PLUGIN_URL . 'admin/css/oli-sl-admin.css',
			array(),
			OLI_SL_VERSION
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( false === strpos( (string) $hook, 'oli_store' ) && ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$needed = ( 'oli_store' === $screen->post_type ) || ( 'oli_store_page_oli-sl-settings' === $screen->id ) || ( 'edit-oli_store_category' === $screen->id );
		if ( ! $needed ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
			'oli-sl-admin',
			OLI_SL_PLUGIN_URL . 'admin/js/oli-sl-admin.js',
			array( 'jquery', 'wp-color-picker' ),
			OLI_SL_VERSION,
			true
		);

		wp_localize_script(
			'oli-sl-admin',
			'oliSLAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'oli_sl_admin_nonce' ),
				'i18n'    => array(
					'geocoding' => __( 'Looking up coordinates…', 'oli-store-locator' ),
					'syncing'   => __( 'Syncing…', 'oli-store-locator' ),
					'detecting' => __( 'Detecting columns…', 'oli-store-locator' ),
				),
			)
		);
	}

	/**
	 * Handle admin-post style actions from settings screens.
	 */
	public function handle_admin_actions() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $_POST['oli_sl_admin_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified per action.
			return;
		}

		check_admin_referer( 'oli_sl_admin_action', 'oli_sl_admin_nonce' );

		$action = sanitize_key( wp_unslash( $_POST['oli_sl_admin_action'] ) );

		if ( 'export_csv' === $action ) {
			$csv = Oli_SL_CSV_Handler::export_stores();
			nocache_headers();
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=oli-stores-' . gmdate( 'Y-m-d' ) . '.csv' );
			echo $csv; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV download.
			exit;
		}

		if ( 'import_csv' === $action ) {
			$file  = isset( $_FILES['oli_sl_csv_file'] ) ? oli_sl_sanitize_uploaded_file( $_FILES['oli_sl_csv_file'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- oli_sl_sanitize_uploaded_file().
			$valid = Oli_SL_CSV_Parser::validate_upload( $file );
			if ( is_wp_error( $valid ) ) {
				add_settings_error( 'oli_sl_options', $valid->get_error_code(), $valid->get_error_message(), 'error' );
				return;
			}

			$tmp     = $file['tmp_name'];
			$content = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- temporary uploaded file.
			$results = Oli_SL_CSV_Handler::import_stores( $content, true );

			$message = sprintf(
				/* translators: 1: created count, 2: updated count, 3: skipped count */
				__( 'Import finished. Created: %1$d, updated: %2$d, skipped: %3$d.', 'oli-store-locator' ),
				(int) $results['created'],
				(int) $results['updated'],
				(int) $results['skipped']
			);
			add_settings_error( 'oli_sl_options', 'import_done', $message, 'updated' );
			if ( ! empty( $results['warnings'] ) && is_array( $results['warnings'] ) ) {
				foreach ( $results['warnings'] as $warning ) {
					add_settings_error( 'oli_sl_options', 'import_warning', $warning, 'warning' );
				}
			}
		}

		if ( 'start_sync' === $action ) {
			oli_sl_update_option( 'sheets_sync_enabled', '1' );
			update_option( 'oli_sl_sheets_paused', '0' );
			$sync    = new Oli_SL_Google_Sheets_Sync();
			$results = $sync->sync();
			$this->add_sync_notice( $results );
			$this->persist_settings_errors();
			wp_safe_redirect( admin_url( 'edit.php?post_type=oli_store&page=oli-sl-settings&tab=sync&oli_sl_notice=started' ) );
			exit;
		}

		if ( 'sync_now' === $action ) {
			$sync    = new Oli_SL_Google_Sheets_Sync();
			$results = $sync->sync();
			$this->add_sync_notice( $results );
			$this->persist_settings_errors();
			wp_safe_redirect( admin_url( 'edit.php?post_type=oli_store&page=oli-sl-settings&tab=sync&oli_sl_notice=synced' ) );
			exit;
		}

		if ( 'pause_sync' === $action ) {
			update_option( 'oli_sl_sheets_paused', '1' );
			add_settings_error( 'oli_sl_options', 'sync_paused', __( 'Google Sheets sync paused.', 'oli-store-locator' ), 'updated' );
			$this->persist_settings_errors();
			wp_safe_redirect( admin_url( 'edit.php?post_type=oli_store&page=oli-sl-settings&tab=sync&oli_sl_notice=paused' ) );
			exit;
		}

		if ( 'resume_sync' === $action ) {
			update_option( 'oli_sl_sheets_paused', '0' );
			add_settings_error( 'oli_sl_options', 'sync_resumed', __( 'Google Sheets sync resumed.', 'oli-store-locator' ), 'updated' );
			$this->persist_settings_errors();
			wp_safe_redirect( admin_url( 'edit.php?post_type=oli_store&page=oli-sl-settings&tab=sync&oli_sl_notice=resumed' ) );
			exit;
		}

		if ( 'clear_search_cache' === $action ) {
			Oli_SL_Search::clear_cache();
			add_settings_error( 'oli_sl_options', 'cache_cleared', __( 'Search cache cleared.', 'oli-store-locator' ), 'updated' );
			return;
		}

		if ( 'clear_geocode_cache' === $action ) {
			Oli_SL_Search::clear_geocode_cache();
			add_settings_error( 'oli_sl_options', 'geo_cache_cleared', __( 'Geocode cache cleared.', 'oli-store-locator' ), 'updated' );
			return;
		}

		if ( 'geocode_missing' === $action ) {
			$count = $this->geocode_missing_stores();
			add_settings_error(
				'oli_sl_options',
				'geocode_missing',
				sprintf(
					/* translators: %d: number of stores */
					__( 'Geocoded %d stores.', 'oli-store-locator' ),
					$count
				),
				'updated'
			);
			return;
		}

		if ( 'export_settings' === $action ) {
			$options = get_option( 'oli_sl_options', array() );
			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=oli-store-locator-settings.json' );
			echo wp_json_encode( $options );
			exit;
		}

		if ( 'import_settings' === $action ) {
			$file = isset( $_FILES['oli_sl_settings_file'] ) ? oli_sl_sanitize_uploaded_file( $_FILES['oli_sl_settings_file'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- oli_sl_sanitize_uploaded_file().
			$ext  = strtolower( (string) pathinfo( $file['name'], PATHINFO_EXTENSION ) );
			if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) || 'json' !== $ext ) {
				add_settings_error( 'oli_sl_options', 'settings_import', __( 'Please choose a JSON settings file.', 'oli-store-locator' ), 'error' );
				return;
			}
			$raw  = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- temporary uploaded file.
			$data = json_decode( $raw, true );
			if ( ! is_array( $data ) ) {
				add_settings_error( 'oli_sl_options', 'settings_import', __( 'The settings file is not valid JSON.', 'oli-store-locator' ), 'error' );
				return;
			}
			$settings = new Oli_SL_Settings();
			update_option( 'oli_sl_options', $settings->sanitize_options( $data ) );
			add_settings_error( 'oli_sl_options', 'settings_import', __( 'Settings imported.', 'oli-store-locator' ), 'updated' );
			return;
		}

		if ( 'remove_sync' === $action ) {
			oli_sl_update_option( 'sheets_sync_enabled', '0' );
			oli_sl_update_option( 'sheets_url', '' );
			oli_sl_update_option( 'sheets_column_map', array() );
			update_option( 'oli_sl_sheets_paused', '0' );
			delete_option( 'oli_sl_sheets_sync_results' );
			delete_option( 'oli_sl_sheets_sync_errors' );
			delete_transient( 'oli_sl_sheets_sync_batches' );
			add_settings_error( 'oli_sl_options', 'sync_removed', __( 'Google Sheets sync removed.', 'oli-store-locator' ), 'updated' );
			$this->persist_settings_errors();
			wp_safe_redirect( admin_url( 'edit.php?post_type=oli_store&page=oli-sl-settings&tab=sync&oli_sl_notice=removed' ) );
			exit;
		}
	}

	/**
	 * Keep settings errors across a redirect (Sync Now, pause, etc.).
	 */
	private function persist_settings_errors() {
		global $wp_settings_errors;
		if ( empty( $wp_settings_errors ) || ! function_exists( 'get_current_user_id' ) ) {
			return;
		}
		set_transient( 'oli_sl_settings_errors_' . get_current_user_id(), $wp_settings_errors, MINUTE_IN_SECONDS );
	}

	/**
	 * Replay settings errors stored before a redirect, plus Sync Now query notices.
	 */
	public function restore_settings_errors() {
		if ( ! is_admin() || ! function_exists( 'get_current_user_id' ) ) {
			return;
		}

		$key    = 'oli_sl_settings_errors_' . get_current_user_id();
		$stored = get_transient( $key );
		if ( is_array( $stored ) ) {
			foreach ( $stored as $error ) {
				if ( ! is_array( $error ) || empty( $error['code'] ) ) {
					continue;
				}
				add_settings_error(
					isset( $error['setting'] ) ? $error['setting'] : 'oli_sl_options',
					$error['code'],
					isset( $error['message'] ) ? $error['message'] : '',
					isset( $error['type'] ) ? $error['type'] : 'updated'
				);
			}
			delete_transient( $key );
		}

		if ( empty( $_GET['oli_sl_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- admin flash only.
			return;
		}

		$notice = sanitize_key( wp_unslash( $_GET['oli_sl_notice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- admin flash only.
		if ( 'synced' === $notice && ! $this->has_settings_error( 'sync_ok' ) && ! $this->has_settings_error( 'sync_error' ) ) {
			add_settings_error( 'oli_sl_options', 'sync_ok', __( 'Sync Now finished.', 'oli-store-locator' ), 'updated' );
		}
	}

	/**
	 * Whether a settings error code is already queued.
	 *
	 * @param string $code Code.
	 * @return bool
	 */
	private function has_settings_error( $code ) {
		$errors = get_settings_errors( 'oli_sl_options' );
		foreach ( $errors as $error ) {
			if ( isset( $error['code'] ) && $code === $error['code'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Admin notice when Google Maps is selected without an API key.
	 */
	public function missing_google_key_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( 'google' !== oli_sl_get_option( 'map_provider', 'leaflet' ) ) {
			return;
		}
		if ( '' !== trim( (string) oli_sl_get_option( 'google_api_key', '' ) ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		echo esc_html__( 'Oli Store Locator: Google Maps is selected but no API key is set. The public map uses Leaflet until you add a key under Stores → Settings → API.', 'oli-store-locator' );
		echo '</p></div>';
	}

	/**
	 * Show a store-editor warning when invalid coordinates were cleared.
	 */
	public function metabox_coordinate_notice() {
		if ( ! function_exists( 'get_current_user_id' ) ) {
			return;
		}

		$key     = 'oli_sl_metabox_notice_' . get_current_user_id();
		$message = get_transient( $key );
		if ( ! is_string( $message ) || '' === $message ) {
			return;
		}

		delete_transient( $key );
		echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Store list columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function store_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['oli_sl_address']  = __( 'Address', 'oli-store-locator' );
				$new['oli_sl_coords']   = __( 'Coordinates', 'oli-store-locator' );
				$new['oli_sl_featured'] = __( 'Featured', 'oli-store-locator' );
			}
		}
		return $new;
	}

	/**
	 * Store list column content.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 */
	public function store_column_content( $column, $post_id ) {
		if ( 'oli_sl_address' === $column ) {
			echo esc_html(
				oli_sl_join_address(
					array(
						oli_sl_get_store_meta( $post_id, 'address' ),
						oli_sl_get_store_meta( $post_id, 'city' ),
						oli_sl_get_store_meta( $post_id, 'state' ),
						oli_sl_get_store_meta( $post_id, 'zip' ),
					)
				)
			);
		}
		if ( 'oli_sl_featured' === $column ) {
			echo '1' === (string) oli_sl_get_store_meta( $post_id, 'featured' ) ? esc_html__( 'Yes', 'oli-store-locator' ) : '—';
		}
		if ( 'oli_sl_coords' === $column ) {
			$lat = oli_sl_get_store_meta( $post_id, 'latitude' );
			$lng = oli_sl_get_store_meta( $post_id, 'longitude' );
			if ( $lat && $lng ) {
				echo esc_html( $lat . ', ' . $lng );
			} else {
				esc_html_e( 'Not geocoded', 'oli-store-locator' );
			}
		}
	}

	/**
	 * Admin geocode AJAX.
	 */
	public function geocode_address() {
		check_ajax_referer( 'oli_sl_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'oli-store-locator' ) ) );
		}

		$address = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';
		if ( '' === $address ) {
			wp_send_json_error( array( 'message' => __( 'Address is required.', 'oli-store-locator' ) ) );
		}

		$result = Oli_SL_Geocoding::geocode(
			$address,
			array(
				'allow_sleep'      => true,
				'enforce_ip_limit' => false,
			)
		);
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Detect Google Sheets columns.
	 */
	public function detect_sheet_columns() {
		check_ajax_referer( 'oli_sl_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'oli-store-locator' ) ) );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		if ( $url ) {
			if ( ! Oli_SL_CSV_Parser::is_valid_sheets_url( $url ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'The Google Sheet URL was not saved. Use a https://docs.google.com/spreadsheets/d/… link or a published CSV export URL.', 'oli-store-locator' ),
					)
				);
			}
			oli_sl_update_option( 'sheets_url', $url );
		}

		$sync   = new Oli_SL_Google_Sheets_Sync();
		$parsed = $sync->fetch_sheet();
		if ( is_wp_error( $parsed ) ) {
			wp_send_json_error( array( 'message' => $parsed->get_error_message() ) );
		}

		$map = Oli_SL_CSV_Parser::detect_columns( $parsed['headers'] );
		oli_sl_update_option( 'sheets_column_map', $map );

		wp_send_json_success(
			array(
				'headers' => $parsed['headers'],
				'map'     => $map,
				'preview' => array_slice( $parsed['rows'], 0, 3 ),
				'fields'  => Oli_SL_CSV_Parser::fields(),
			)
		);
	}

	/**
	 * AJAX sync now.
	 */
	public function sync_now() {
		check_ajax_referer( 'oli_sl_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'oli-store-locator' ) ) );
		}

		$sync    = new Oli_SL_Google_Sheets_Sync();
		$results = $sync->sync();
		wp_send_json_success( $results );
	}

	/**
	 * Admin bar shortcut.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar.
	 */
	public function admin_bar_menu( $wp_admin_bar ) {
		if ( ! oli_sl_is_enabled( oli_sl_get_option( 'admin_bar_menu', '1' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'oli-sl-stores',
				'title' => __( 'Stores', 'oli-store-locator' ),
				'href'  => admin_url( 'edit.php?post_type=oli_store' ),
			)
		);
		if ( current_user_can( 'manage_options' ) ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'oli-sl-stores',
					'id'     => 'oli-sl-settings',
					'title'  => __( 'Settings', 'oli-store-locator' ),
					'href'   => admin_url( 'edit.php?post_type=oli_store&page=oli-sl-settings' ),
				)
			);
		}
	}

	/**
	 * Geocode published stores that have no coordinates.
	 *
	 * @return int
	 */
	private function geocode_missing_stores() {
		$ids = get_posts(
			array(
				'post_type'      => 'oli_store',
				'post_status'    => 'publish',
				'posts_per_page' => 25,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin tool.
					'relation' => 'OR',
					array(
						'key'     => '_oli_sl_latitude',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_oli_sl_latitude',
						'value'   => '',
						'compare' => '=',
					),
				),
			)
		);

		$count = 0;
		foreach ( $ids as $store_id ) {
			$address = oli_sl_join_address(
				array(
					oli_sl_get_store_meta( $store_id, 'address' ),
					oli_sl_get_store_meta( $store_id, 'city' ),
					oli_sl_get_store_meta( $store_id, 'state' ),
					oli_sl_get_store_meta( $store_id, 'zip' ),
					oli_sl_get_store_meta( $store_id, 'country' ),
				)
			);
			if ( '' === $address ) {
				continue;
			}
			$result = Oli_SL_Geocoding::geocode(
				$address,
				array(
					'allow_sleep'      => true,
					'enforce_ip_limit' => false,
				)
			);
			if ( is_wp_error( $result ) ) {
				continue;
			}
			$geo_lat = oli_sl_sanitize_latitude( $result['lat'] );
			$geo_lng = oli_sl_sanitize_longitude( $result['lng'] );
			if ( '' === $geo_lat || '' === $geo_lng ) {
				continue;
			}
			update_post_meta( $store_id, '_oli_sl_latitude', $geo_lat );
			update_post_meta( $store_id, '_oli_sl_longitude', $geo_lng );
			++$count;
		}

		Oli_SL_Search::clear_cache();
		return $count;
	}

	/**
	 * Flash a sync result notice.
	 *
	 * @param array $results Results.
	 */
	private function add_sync_notice( $results ) {
		if ( empty( $results['success'] ) ) {
			add_settings_error( 'oli_sl_options', 'sync_error', isset( $results['message'] ) ? $results['message'] : __( 'Sync failed.', 'oli-store-locator' ), 'error' );
			return;
		}

		$message = sprintf(
			/* translators: 1: created, 2: updated, 3: deleted, 4: skipped */
			__( 'Sync finished. Created: %1$d, updated: %2$d, deleted: %3$d, skipped: %4$d.', 'oli-store-locator' ),
			isset( $results['created'] ) ? (int) $results['created'] : 0,
			isset( $results['updated'] ) ? (int) $results['updated'] : 0,
			isset( $results['deleted'] ) ? (int) $results['deleted'] : 0,
			isset( $results['skipped'] ) ? (int) $results['skipped'] : 0
		);
		add_settings_error( 'oli_sl_options', 'sync_ok', $message, 'updated' );

		if ( ! empty( $results['warnings'] ) && is_array( $results['warnings'] ) ) {
			foreach ( $results['warnings'] as $warning ) {
				add_settings_error( 'oli_sl_options', 'sync_warning', $warning, 'warning' );
			}
		}
	}
}
