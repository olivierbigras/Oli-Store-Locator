<?php
/**
 * Local Pages settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Create landing pages?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'enable_single_pages', __( 'Enable public single store pages', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'Enabling this option will create single pages for all store locations. Copy templates/single-store.php to yourtheme/oli-store-locator/ to customize the landing page.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Remove the front base from the permalink structure?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'permalink_remove_front', __( 'Remove the front base from the permalink structure?', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'If a front base is set (for example /blog/), enabling this option will remove it from the store locator permalinks.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-store-slug"><?php esc_html_e( 'Store slug', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="text" id="oli-sl-store-slug" name="oli_sl_options[store_slug]" value="<?php echo esc_attr( oli_sl_get_option( 'store_slug' ) ); ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Category in permalinks', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'permalink_category', __( 'Include the store category in the permalink when possible.', 'oli-store-locator' ) ); ?></td>
	</tr>
</table>
