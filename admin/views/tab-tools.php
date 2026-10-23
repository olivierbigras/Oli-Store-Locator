<?php
/**
 * Tools settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$store_count = wp_count_posts( 'oli_store' );
$published   = $store_count && isset( $store_count->publish ) ? (int) $store_count->publish : 0;
$missing     = 0;
$missing_q   = new WP_Query(
	array(
		'post_type'      => 'oli_store',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- status report only.
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
$missing     = (int) $missing_q->found_posts;
?>
<form action="options.php" method="post">
	<?php settings_fields( 'oli_sl_settings_group' ); ?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'Enable store locator debug?', 'oli-store-locator' ); ?></th>
			<td><?php oli_sl_checkbox_field( 'debug_mode', __( 'Enable store locator debug?', 'oli-store-locator' ) ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Admin bar menu', 'oli-store-locator' ); ?></th>
			<td><?php oli_sl_checkbox_field( 'admin_bar_menu', __( 'Show a Stores shortcut in the admin bar', 'oli-store-locator' ) ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Enable compatibility mode?', 'oli-store-locator' ); ?></th>
			<td><?php oli_sl_checkbox_field( 'compatibility_mode', __( 'Avoid loading a second Google Maps script when another plugin already enqueued one.', 'oli-store-locator' ) ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Search cache', 'oli-store-locator' ); ?></th>
			<td>
				<?php oli_sl_checkbox_field( 'enable_caching', __( 'Cache search results', 'oli-store-locator' ) ); ?>
				<p>
					<label><?php esc_html_e( 'Duration (seconds)', 'oli-store-locator' ); ?>
						<input type="number" min="60" name="oli_sl_options[cache_duration]" value="<?php echo esc_attr( oli_sl_get_option( 'cache_duration' ) ); ?>" />
					</label>
				</p>
			</td>
		</tr>
	</table>
	<?php submit_button(); ?>
</form>

<h2><?php esc_html_e( 'Caches', 'oli-store-locator' ); ?></h2>
<form method="post" style="display:inline-block;margin-right:8px;">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="clear_search_cache" />
	<?php submit_button( __( 'Clear search cache', 'oli-store-locator' ), 'secondary', 'submit', false ); ?>
</form>
<form method="post" style="display:inline-block;">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="clear_geocode_cache" />
	<?php submit_button( __( 'Clear geocode cache', 'oli-store-locator' ), 'secondary', 'submit', false ); ?>
</form>

<h2><?php esc_html_e( 'Geocode missing coordinates', 'oli-store-locator' ); ?></h2>
<p><?php echo esc_html( sprintf( /* translators: 1: published stores, 2: missing coords */ __( '%1$d published stores, %2$d without coordinates.', 'oli-store-locator' ), $published, $missing ) ); ?></p>
<form method="post">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="geocode_missing" />
	<?php submit_button( __( 'Geocode stores without coordinates', 'oli-store-locator' ), 'secondary', 'submit', false ); ?>
</form>

<h2><?php esc_html_e( 'Settings transfer', 'oli-store-locator' ); ?></h2>
<form method="post" style="display:inline-block;margin-right:8px;">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="export_settings" />
	<?php submit_button( __( 'Export settings', 'oli-store-locator' ), 'secondary', 'submit', false ); ?>
</form>
<form method="post" enctype="multipart/form-data" style="display:inline-block;">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="import_settings" />
	<input type="file" name="oli_sl_settings_file" accept="application/json,.json" required />
	<?php submit_button( __( 'Import settings', 'oli-store-locator' ), 'secondary', 'submit', false ); ?>
</form>
