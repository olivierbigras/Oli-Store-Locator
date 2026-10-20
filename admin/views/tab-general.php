<?php
/**
 * General settings tab.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Map provider', 'oli-store-locator' ); ?></th>
		<td>
			<select name="oli_sl_options[map_provider]">
				<option value="leaflet" <?php selected( oli_sl_get_option( 'map_provider' ), 'leaflet' ); ?>><?php esc_html_e( 'OpenStreetMap / Leaflet (free)', 'oli-store-locator' ); ?></option>
				<option value="google" <?php selected( oli_sl_get_option( 'map_provider' ), 'google' ); ?>><?php esc_html_e( 'Google Maps', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Geocoding provider', 'oli-store-locator' ); ?></th>
		<td>
			<select name="oli_sl_options[geocoding_provider]">
				<option value="nominatim" <?php selected( oli_sl_get_option( 'geocoding_provider' ), 'nominatim' ); ?>><?php esc_html_e( 'Nominatim (OpenStreetMap, rate limited)', 'oli-store-locator' ); ?></option>
				<option value="google" <?php selected( oli_sl_get_option( 'geocoding_provider' ), 'google' ); ?>><?php esc_html_e( 'Google Geocoding API', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Google Maps JavaScript API key', 'oli-store-locator' ); ?></th>
		<td>
			<input type="text" class="regular-text" name="oli_sl_options[google_api_key]" value="<?php echo esc_attr( oli_sl_get_option( 'google_api_key' ) ); ?>" />
			<p class="description"><?php esc_html_e( 'Used on the front end for Google Maps, Places autocomplete, and Directions.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Google Geocoding (server) API key', 'oli-store-locator' ); ?></th>
		<td>
			<input type="text" class="regular-text" name="oli_sl_options[google_server_key]" value="<?php echo esc_attr( oli_sl_get_option( 'google_server_key' ) ); ?>" />
			<p class="description"><?php esc_html_e( 'Optional admin/server key. Falls back to the JavaScript key if empty.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Distance unit', 'oli-store-locator' ); ?></th>
		<td>
			<select name="oli_sl_options[distance_unit]">
				<option value="km" <?php selected( oli_sl_get_option( 'distance_unit' ), 'km' ); ?>><?php esc_html_e( 'Kilometers', 'oli-store-locator' ); ?></option>
				<option value="mi" <?php selected( oli_sl_get_option( 'distance_unit' ), 'mi' ); ?>><?php esc_html_e( 'Miles', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Single store pages', 'oli-store-locator' ); ?></th>
		<td>
			<label><input type="hidden" name="oli_sl_options[enable_single_pages]" value="0" />
			<input type="checkbox" name="oli_sl_options[enable_single_pages]" value="1" <?php checked( oli_sl_get_option( 'enable_single_pages' ), '1' ); ?> /> <?php esc_html_e( 'Enable public single store pages', 'oli-store-locator' ); ?></label>
			<p>
				<label><?php esc_html_e( 'Slug', 'oli-store-locator' ); ?>
					<input type="text" name="oli_sl_options[store_slug]" value="<?php echo esc_attr( oli_sl_get_option( 'store_slug' ) ); ?>" />
				</label>
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Opening hours input', 'oli-store-locator' ); ?></th>
		<td>
			<select name="oli_sl_options[hours_format]">
				<option value="textarea" <?php selected( oli_sl_get_option( 'hours_format' ), 'textarea' ); ?>><?php esc_html_e( 'Textarea', 'oli-store-locator' ); ?></option>
				<option value="dropdown" <?php selected( oli_sl_get_option( 'hours_format' ), 'dropdown' ); ?>><?php esc_html_e( 'Day dropdowns', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Search cache', 'oli-store-locator' ); ?></th>
		<td>
			<label><input type="hidden" name="oli_sl_options[enable_caching]" value="0" />
			<input type="checkbox" name="oli_sl_options[enable_caching]" value="1" <?php checked( oli_sl_get_option( 'enable_caching' ), '1' ); ?> /> <?php esc_html_e( 'Cache search results', 'oli-store-locator' ); ?></label>
			<p>
				<label><?php esc_html_e( 'Duration (seconds)', 'oli-store-locator' ); ?>
					<input type="number" min="60" name="oli_sl_options[cache_duration]" value="<?php echo esc_attr( oli_sl_get_option( 'cache_duration' ) ); ?>" />
				</label>
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Nominatim rate limit', 'oli-store-locator' ); ?></th>
		<td>
			<label><input type="hidden" name="oli_sl_options[nominatim_rate_limit]" value="0" />
			<input type="checkbox" name="oli_sl_options[nominatim_rate_limit]" value="1" <?php checked( oli_sl_get_option( 'nominatim_rate_limit' ), '1' ); ?> /> <?php esc_html_e( 'Limit Nominatim to 1 request per second (required by OpenStreetMap usage policy)', 'oli-store-locator' ); ?></label>
		</td>
	</tr>
</table>
