<?php
/**
 * Search settings tab.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Default radius', 'oli-store-locator' ); ?></th>
		<td>
			<input type="number" min="1" name="oli_sl_options[search_radius]" value="<?php echo esc_attr( oli_sl_get_option( 'search_radius' ) ); ?>" />
			<p>
				<label><?php esc_html_e( 'Radius dropdown options (comma separated)', 'oli-store-locator' ); ?><br />
					<input type="text" class="regular-text" name="oli_sl_options[radius_options]" value="<?php echo esc_attr( oli_sl_get_option( 'radius_options' ) ); ?>" />
				</label>
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Max results', 'oli-store-locator' ); ?></th>
		<td>
			<input type="number" min="1" name="oli_sl_options[max_results]" value="<?php echo esc_attr( oli_sl_get_option( 'max_results' ) ); ?>" />
			<p>
				<label><?php esc_html_e( 'Max results dropdown options (comma separated)', 'oli-store-locator' ); ?><br />
					<input type="text" class="regular-text" name="oli_sl_options[max_results_options]" value="<?php echo esc_attr( oli_sl_get_option( 'max_results_options' ) ); ?>" />
				</label>
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Category filter', 'oli-store-locator' ); ?></th>
		<td>
			<select name="oli_sl_options[category_filter_type]">
				<option value="dropdown" <?php selected( oli_sl_get_option( 'category_filter_type' ), 'dropdown' ); ?>><?php esc_html_e( 'Dropdown', 'oli-store-locator' ); ?></option>
				<option value="checkboxes" <?php selected( oli_sl_get_option( 'category_filter_type' ), 'checkboxes' ); ?>><?php esc_html_e( 'Checkboxes', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Search behaviour', 'oli-store-locator' ); ?></th>
		<td>
			<label><input type="hidden" name="oli_sl_options[auto_locate]" value="0" />
			<input type="checkbox" name="oli_sl_options[auto_locate]" value="1" <?php checked( oli_sl_get_option( 'auto_locate' ), '1' ); ?> /> <?php esc_html_e( 'Offer “Use my location” (HTML5 geolocation)', 'oli-store-locator' ); ?></label><br />
			<label><input type="hidden" name="oli_sl_options[enable_autocomplete]" value="0" />
			<input type="checkbox" name="oli_sl_options[enable_autocomplete]" value="1" <?php checked( oli_sl_get_option( 'enable_autocomplete' ), '1' ); ?> /> <?php esc_html_e( 'Address / ZIP autocomplete', 'oli-store-locator' ); ?></label><br />
			<label><input type="hidden" name="oli_sl_options[search_on_load]" value="0" />
			<input type="checkbox" name="oli_sl_options[search_on_load]" value="1" <?php checked( oli_sl_get_option( 'search_on_load' ), '1' ); ?> /> <?php esc_html_e( 'Search around start location on page load', 'oli-store-locator' ); ?></label><br />
			<label><input type="hidden" name="oli_sl_options[enable_directions]" value="0" />
			<input type="checkbox" name="oli_sl_options[enable_directions]" value="1" <?php checked( oli_sl_get_option( 'enable_directions' ), '1' ); ?> /> <?php esc_html_e( 'Directions link', 'oli-store-locator' ); ?></label><br />
			<label><input type="hidden" name="oli_sl_options[in_map_directions]" value="0" />
			<input type="checkbox" name="oli_sl_options[in_map_directions]" value="1" <?php checked( oli_sl_get_option( 'in_map_directions' ), '1' ); ?> /> <?php esc_html_e( 'In-map directions (Google Maps only)', 'oli-store-locator' ); ?></label>
		</td>
	</tr>
</table>
