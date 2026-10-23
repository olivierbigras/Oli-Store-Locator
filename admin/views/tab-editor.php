<?php
/**
 * Store Editor settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="oli-sl-editor-country"><?php esc_html_e( 'Default country', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-editor-country" name="oli_sl_options[editor_default_country]">
				<?php foreach ( oli_sl_country_choices() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( oli_sl_get_option( 'editor_default_country' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-editor-map-type"><?php esc_html_e( 'Map type for the location preview', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-editor-map-type" name="oli_sl_options[editor_map_type]">
				<?php foreach ( array( 'roadmap', 'satellite', 'hybrid', 'terrain' ) as $map_type_choice ) : ?>
					<option value="<?php echo esc_attr( $map_type_choice ); ?>" <?php selected( oli_sl_get_option( 'editor_map_type' ), $map_type_choice ); ?>><?php echo esc_html( ucfirst( $map_type_choice ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Hide the opening hours?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'editor_hide_hours', __( 'Hide the opening hours?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-hours-format"><?php esc_html_e( 'Opening hours input type', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-hours-format" name="oli_sl_options[hours_format]">
				<option value="textarea" <?php selected( oli_sl_get_option( 'hours_format' ), 'textarea' ); ?>><?php esc_html_e( 'Textarea', 'oli-store-locator' ); ?></option>
				<option value="dropdown" <?php selected( oli_sl_get_option( 'hours_format' ), 'dropdown' ); ?>><?php esc_html_e( 'Dropdowns (recommended)', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-default-hours"><?php esc_html_e( 'The default opening hours', 'oli-store-locator' ); ?></label></th>
		<td>
			<textarea class="large-text" rows="5" id="oli-sl-default-hours" name="oli_sl_options[editor_default_hours]"><?php echo esc_textarea( oli_sl_get_option( 'editor_default_hours' ) ); ?></textarea>
			<p class="description"><?php esc_html_e( 'The default country and opening hours are only used when a new store is created. So changing the default values will have no effect on existing store locations.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Hidden editor fields', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'editor_hide_phone', __( 'Hide the phone field', 'oli-store-locator' ) ); ?><br />
			<?php oli_sl_checkbox_field( 'editor_hide_fax', __( 'Hide the fax field', 'oli-store-locator' ) ); ?><br />
			<?php oli_sl_checkbox_field( 'editor_hide_email', __( 'Hide the email field', 'oli-store-locator' ) ); ?><br />
			<?php oli_sl_checkbox_field( 'editor_hide_url', __( 'Hide the website field', 'oli-store-locator' ) ); ?>
		</td>
	</tr>
</table>
