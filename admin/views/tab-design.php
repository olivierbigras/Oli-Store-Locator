<?php
/**
 * Design settings tab.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'List layout', 'oli-store-locator' ); ?></th>
		<td>
			<select name="oli_sl_options[list_layout]">
				<option value="below" <?php selected( oli_sl_get_option( 'list_layout' ), 'below' ); ?>><?php esc_html_e( 'Below map', 'oli-store-locator' ); ?></option>
				<option value="left" <?php selected( oli_sl_get_option( 'list_layout' ), 'left' ); ?>><?php esc_html_e( 'Beside map (list left)', 'oli-store-locator' ); ?></option>
				<option value="right" <?php selected( oli_sl_get_option( 'list_layout' ), 'right' ); ?>><?php esc_html_e( 'Beside map (list right)', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Mobile layout', 'oli-store-locator' ); ?></th>
		<td>
			<select name="oli_sl_options[mobile_layout]">
				<option value="map_first" <?php selected( oli_sl_get_option( 'mobile_layout' ), 'map_first' ); ?>><?php esc_html_e( 'Map then list', 'oli-store-locator' ); ?></option>
				<option value="list_first" <?php selected( oli_sl_get_option( 'mobile_layout' ), 'list_first' ); ?>><?php esc_html_e( 'List then map', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Map size', 'oli-store-locator' ); ?></th>
		<td>
			<label><?php esc_html_e( 'Height (px)', 'oli-store-locator' ); ?>
				<input type="text" name="oli_sl_options[map_height]" value="<?php echo esc_attr( oli_sl_get_option( 'map_height' ) ); ?>" />
			</label>
			<label><?php esc_html_e( 'Width', 'oli-store-locator' ); ?>
				<input type="text" name="oli_sl_options[map_width]" value="<?php echo esc_attr( oli_sl_get_option( 'map_width' ) ); ?>" />
			</label>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Colors', 'oli-store-locator' ); ?></th>
		<td>
			<p><label><?php esc_html_e( 'Primary', 'oli-store-locator' ); ?> <input type="text" class="oli-sl-color" name="oli_sl_options[primary_color]" value="<?php echo esc_attr( oli_sl_get_option( 'primary_color' ) ); ?>" /></label></p>
			<p><label><?php esc_html_e( 'Buttons', 'oli-store-locator' ); ?> <input type="text" class="oli-sl-color" name="oli_sl_options[button_color]" value="<?php echo esc_attr( oli_sl_get_option( 'button_color' ) ); ?>" /></label></p>
			<p><label><?php esc_html_e( 'Button text', 'oli-store-locator' ); ?> <input type="text" class="oli-sl-color" name="oli_sl_options[button_text_color]" value="<?php echo esc_attr( oli_sl_get_option( 'button_text_color' ) ); ?>" /></label></p>
			<p><label><?php esc_html_e( 'Text', 'oli-store-locator' ); ?> <input type="text" class="oli-sl-color" name="oli_sl_options[text_color]" value="<?php echo esc_attr( oli_sl_get_option( 'text_color' ) ); ?>" /></label></p>
			<p><label><?php esc_html_e( 'List hover', 'oli-store-locator' ); ?> <input type="text" class="oli-sl-color" name="oli_sl_options[hover_color]" value="<?php echo esc_attr( oli_sl_get_option( 'hover_color' ) ); ?>" /></label></p>
			<p><label><?php esc_html_e( 'Cluster', 'oli-store-locator' ); ?> <input type="text" class="oli-sl-color" name="oli_sl_options[cluster_color]" value="<?php echo esc_attr( oli_sl_get_option( 'cluster_color' ) ); ?>" /></label></p>
			<p><label><?php esc_html_e( 'Cluster text', 'oli-store-locator' ); ?> <input type="text" class="oli-sl-color" name="oli_sl_options[cluster_text_color]" value="<?php echo esc_attr( oli_sl_get_option( 'cluster_text_color' ) ); ?>" /></label></p>
			<p class="description"><?php esc_html_e( 'Fonts inherit from the active theme.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Default marker icon', 'oli-store-locator' ); ?></th>
		<td>
			<input type="url" class="regular-text" id="oli_sl_default_marker" name="oli_sl_options[default_marker]" value="<?php echo esc_attr( oli_sl_get_option( 'default_marker' ) ); ?>" />
			<button type="button" class="button oli-sl-media-upload" data-target="oli_sl_default_marker"><?php esc_html_e( 'Upload', 'oli-store-locator' ); ?></button>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Custom CSS', 'oli-store-locator' ); ?></th>
		<td>
			<textarea class="large-text code" rows="8" name="oli_sl_options[custom_css]"><?php echo esc_textarea( oli_sl_get_option( 'custom_css' ) ); ?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Live preview', 'oli-store-locator' ); ?></th>
		<td>
			<div class="oli-sl-admin-preview" id="oli-sl-admin-preview">
				<div class="oli-sl-preview-map"><?php esc_html_e( 'Map preview', 'oli-store-locator' ); ?></div>
				<button type="button" class="oli-sl-preview-btn"><?php esc_html_e( 'Search', 'oli-store-locator' ); ?></button>
				<ul class="oli-sl-preview-list">
					<li><?php esc_html_e( 'Sample store result', 'oli-store-locator' ); ?></li>
					<li><?php esc_html_e( 'Another store', 'oli-store-locator' ); ?></li>
				</ul>
			</div>
		</td>
	</tr>
</table>
