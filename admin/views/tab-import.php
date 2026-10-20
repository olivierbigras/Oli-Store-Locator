<?php
/**
 * CSV import/export tab.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2><?php esc_html_e( 'Export stores', 'oli-store-locator' ); ?></h2>
<form method="post">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="export_csv" />
	<?php submit_button( __( 'Download CSV', 'oli-store-locator' ), 'secondary', 'submit', false ); ?>
</form>

<h2><?php esc_html_e( 'Import stores', 'oli-store-locator' ); ?></h2>
<form method="post" enctype="multipart/form-data">
	<?php wp_nonce_field( 'oli_sl_admin_action', 'oli_sl_admin_nonce' ); ?>
	<input type="hidden" name="oli_sl_admin_action" value="import_csv" />
	<p>
		<input type="file" name="oli_sl_csv_file" accept=".csv,text/csv" required />
	</p>
	<p class="description"><?php esc_html_e( 'Columns are auto-detected (Name, Address, City, Province/State, Postal code, Country, Phone, Email, Website, Hours, Category, Lat, Lng, Description, Image, ID). Existing stores are matched by ID when present.', 'oli-store-locator' ); ?></p>
	<?php submit_button( __( 'Import CSV', 'oli-store-locator' ), 'primary', 'submit', false ); ?>
</form>
