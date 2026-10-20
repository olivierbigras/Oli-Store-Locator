<?php
/**
 * Google Sheets sync tab.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$enabled     = oli_sl_get_option( 'sheets_sync_enabled', '0' );
$paused      = get_option( 'oli_sl_sheets_paused', '0' );
$last_sync   = (int) get_option( 'oli_sl_sheets_last_sync', 0 );
$last_check  = (int) get_option( 'oli_sl_sheets_last_checked', 0 );
$results     = get_option( 'oli_sl_sheets_sync_results', array() );
$sync_errors = get_option( 'oli_sl_sheets_sync_errors', array() );
$next        = Oli_SL_Google_Sheets_Sync::next_sync_time();
$column_map  = oli_sl_get_option( 'sheets_column_map', array() );
$fields      = Oli_SL_CSV_Parser::fields();
$sync_status = ( '1' === (string) $enabled && '1' !== (string) $paused ) ? __( 'Active', 'oli-store-locator' ) : ( '1' === (string) $paused ? __( 'Paused', 'oli-store-locator' ) : __( 'Inactive', 'oli-store-locator' ) );
$message     = is_array( $results ) && ! empty( $results['message'] ) ? $results['message'] : '';
?>
<form action="options.php" method="post" id="oli-sl-sync-settings">
	<?php settings_fields( 'oli_sl_settings_group' ); ?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'Google Sheets link', 'oli-store-locator' ); ?></th>
			<td>
				<input type="url" class="large-text" id="oli_sl_sheets_url" name="oli_sl_options[sheets_url]" value="<?php echo esc_attr( oli_sl_get_option( 'sheets_url' ) ); ?>" placeholder="https://docs.google.com/spreadsheets/d/…/edit?usp=sharing" />
				<p class="description"><?php esc_html_e( 'Share the sheet with “Anyone with the link can view”. The plugin reads https://docs.google.com/spreadsheets/d/{ID}/export?format=csv&gid={GID}. No Google login is required.', 'oli-store-locator' ); ?></p>
				<p><button type="button" class="button" id="oli-sl-detect-columns"><?php esc_html_e( 'Detect columns', 'oli-store-locator' ); ?></button> <span id="oli-sl-detect-status"></span></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Optional Sheets API key', 'oli-store-locator' ); ?></th>
			<td>
				<input type="text" class="regular-text" name="oli_sl_options[sheets_api_key]" value="<?php echo esc_attr( oli_sl_get_option( 'sheets_api_key' ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Optional. Used for Google Sheets API v4 when the public CSV export is blocked. The sheet still needs to be accessible to the key.', 'oli-store-locator' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Frequency', 'oli-store-locator' ); ?></th>
			<td>
				<select name="oli_sl_options[sheets_sync_frequency]">
					<option value="5min" <?php selected( oli_sl_get_option( 'sheets_sync_frequency' ), '5min' ); ?>><?php esc_html_e( 'Every 5 minutes', 'oli-store-locator' ); ?></option>
					<option value="15min" <?php selected( oli_sl_get_option( 'sheets_sync_frequency' ), '15min' ); ?>><?php esc_html_e( 'Every 15 minutes', 'oli-store-locator' ); ?></option>
					<option value="hourly" <?php selected( oli_sl_get_option( 'sheets_sync_frequency' ), 'hourly' ); ?>><?php esc_html_e( 'Hourly', 'oli-store-locator' ); ?></option>
					<option value="daily" <?php selected( oli_sl_get_option( 'sheets_sync_frequency' ), 'daily' ); ?>><?php esc_html_e( 'Daily', 'oli-store-locator' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Uses WP-Cron custom schedules. If you set DISABLE_WP_CRON, trigger wp-cron.php from server cron (e.g. every 5 minutes) so these intervals can fire.', 'oli-store-locator' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Stores removed from the sheet', 'oli-store-locator' ); ?></th>
			<td>
				<select name="oli_sl_options[sheets_removed_action]">
					<option value="none" <?php selected( oli_sl_get_option( 'sheets_removed_action' ), 'none' ); ?>><?php esc_html_e( 'Leave them', 'oli-store-locator' ); ?></option>
					<option value="draft" <?php selected( oli_sl_get_option( 'sheets_removed_action' ), 'draft' ); ?>><?php esc_html_e( 'Set to draft', 'oli-store-locator' ); ?></option>
					<option value="delete" <?php selected( oli_sl_get_option( 'sheets_removed_action' ), 'delete' ); ?>><?php esc_html_e( 'Move to trash', 'oli-store-locator' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Batch size', 'oli-store-locator' ); ?></th>
			<td>
				<input type="number" min="1" max="100" name="oli_sl_options[sheets_batch_size]" value="<?php echo esc_attr( oli_sl_get_option( 'sheets_batch_size' ) ); ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Column mapping', 'oli-store-locator' ); ?></th>
			<td>
				<div id="oli-sl-column-map">
					<?php if ( empty( $column_map ) ) : ?>
						<p class="description"><?php esc_html_e( 'Paste a sheet URL and click Detect columns. Headers like name, address, city, province/state, postal code, country, phone, email, website, hours, category, lat, lng, description, and image are auto-detected.', 'oli-store-locator' ); ?></p>
					<?php else : ?>
						<table class="widefat striped">
							<thead><tr><th><?php esc_html_e( 'Store field', 'oli-store-locator' ); ?></th><th><?php esc_html_e( 'Sheet column index', 'oli-store-locator' ); ?></th></tr></thead>
							<tbody>
							<?php foreach ( $fields as $field ) : ?>
								<tr>
									<td><?php echo esc_html( $field ); ?></td>
									<td>
										<input type="number" min="0" name="oli_sl_options[sheets_column_map][<?php echo esc_attr( $field ); ?>]" value="<?php echo isset( $column_map[ $field ] ) ? esc_attr( $column_map[ $field ] ) : ''; ?>" />
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</td>
		</tr>
	</table>
	<?php submit_button( __( 'Save mapping & settings', 'oli-store-locator' ) ); ?>
</form>

<div class="oli-sl-sync-status">
	<h2><?php esc_html_e( 'Status', 'oli-store-locator' ); ?></h2>
	<p><strong><?php echo esc_html( $sync_status ); ?></strong>
		<?php if ( $message ) : ?>
			— <?php echo esc_html( $message ); ?>
		<?php endif; ?>
	</p>
	<ul>
		<li><?php esc_html_e( 'Last checked:', 'oli-store-locator' ); ?> <?php echo $last_check ? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_check ) ) : esc_html__( 'Never', 'oli-store-locator' ); ?></li>
		<li><?php esc_html_e( 'Last sync:', 'oli-store-locator' ); ?> <?php echo $last_sync ? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_sync ) ) : esc_html__( 'Never', 'oli-store-locator' ); ?></li>
		<li><?php esc_html_e( 'Next sync:', 'oli-store-locator' ); ?> <?php echo $next ? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next ) ) : '—'; ?></li>
		<?php if ( is_array( $results ) ) : ?>
			<li><?php esc_html_e( 'Created:', 'oli-store-locator' ); ?> <?php echo isset( $results['created'] ) ? esc_html( (string) $results['created'] ) : '0'; ?></li>
			<li><?php esc_html_e( 'Updated:', 'oli-store-locator' ); ?> <?php echo isset( $results['updated'] ) ? esc_html( (string) $results['updated'] ) : '0'; ?></li>
			<li><?php esc_html_e( 'Deleted:', 'oli-store-locator' ); ?> <?php echo isset( $results['deleted'] ) ? esc_html( (string) $results['deleted'] ) : '0'; ?></li>
			<li><?php esc_html_e( 'Skipped:', 'oli-store-locator' ); ?> <?php echo isset( $results['skipped'] ) ? esc_html( (string) $results['skipped'] ) : '0'; ?></li>
		<?php endif; ?>
	</ul>
	<?php if ( ! empty( $sync_errors ) && is_array( $sync_errors ) ) : ?>
		<h3><?php esc_html_e( 'Error log', 'oli-store-locator' ); ?></h3>
		<ul>
			<?php foreach ( array_reverse( $sync_errors ) as $log_entry ) : ?>
				<li><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $log_entry['time'] ) ); ?> — <?php echo esc_html( $log_entry['message'] ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>

<form method="post" style="display:inline-block;margin-right:8px;">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="start_sync" />
	<?php submit_button( __( 'Save & Start Sync', 'oli-store-locator' ), 'primary', 'submit', false ); ?>
</form>
<form method="post" style="display:inline-block;margin-right:8px;">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="sync_now" />
	<?php submit_button( __( 'Sync Now', 'oli-store-locator' ), 'secondary', 'submit', false ); ?>
</form>
<?php if ( '1' === (string) $paused ) : ?>
	<form method="post" style="display:inline-block;margin-right:8px;">
		<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
		<input type="hidden" name="oli_sl_admin_action" value="resume_sync" />
		<?php submit_button( __( 'Resume', 'oli-store-locator' ), 'secondary', 'submit', false ); ?>
	</form>
<?php else : ?>
	<form method="post" style="display:inline-block;margin-right:8px;">
		<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
		<input type="hidden" name="oli_sl_admin_action" value="pause_sync" />
		<?php submit_button( __( 'Pause', 'oli-store-locator' ), 'secondary', 'submit', false ); ?>
	</form>
<?php endif; ?>
<form method="post" style="display:inline-block;">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="remove_sync" />
	<?php submit_button( __( 'Remove Sync', 'oli-store-locator' ), 'delete', 'submit', false ); ?>
</form>
