<?php
/**
 * API settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="oli-sl-map-provider"><?php esc_html_e( 'Map service', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-map-provider" name="oli_sl_options[map_provider]">
				<option value="leaflet" <?php selected( oli_sl_get_option( 'map_provider' ), 'leaflet' ); ?>><?php esc_html_e( 'OpenStreetMap (free, no API key)', 'oli-store-locator' ); ?></option>
				<option value="google" <?php selected( oli_sl_get_option( 'map_provider' ), 'google' ); ?>><?php esc_html_e( 'Google Maps', 'oli-store-locator' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'OpenStreetMap uses Leaflet tiles. Google Maps needs a browser key for the JavaScript API.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-geocoding-provider"><?php esc_html_e( 'Server geocoder', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-geocoding-provider" name="oli_sl_options[geocoding_provider]">
				<option value="nominatim" <?php selected( oli_sl_get_option( 'geocoding_provider' ), 'nominatim' ); ?>><?php esc_html_e( 'Nominatim (OpenStreetMap, rate limited)', 'oli-store-locator' ); ?></option>
				<option value="google" <?php selected( oli_sl_get_option( 'geocoding_provider' ), 'google' ); ?>><?php esc_html_e( 'Google Geocoding API', 'oli-store-locator' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'A request to the selected geocoding service is made when a new location is created and no coordinates are provided.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-google-api-key"><?php esc_html_e( 'Browser key', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" id="oli-sl-google-api-key" name="oli_sl_options[google_api_key]" value="<?php echo esc_attr( oli_sl_get_option( 'google_api_key' ) ); ?>" autocomplete="off" />
			<p class="description"><?php esc_html_e( 'A browser key allows you to monitor the usage of the Google Maps JavaScript API. Required for Google Maps.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-google-server-key"><?php esc_html_e( 'Server key', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" id="oli-sl-google-server-key" name="oli_sl_options[google_server_key]" value="<?php echo esc_attr( oli_sl_get_option( 'google_server_key' ) ); ?>" autocomplete="off" />
			<p class="description"><?php esc_html_e( 'A server key allows you to monitor the usage of the Google Maps Geocoding API. Required for Google Maps geocoding. Falls back to the browser key if empty.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-api-language"><?php esc_html_e( 'API response language', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="text" class="small-text" id="oli-sl-api-language" name="oli_sl_options[api_language]" value="<?php echo esc_attr( oli_sl_get_option( 'api_language' ) ); ?>" placeholder="fr-CA" />
			<p class="description"><?php esc_html_e( 'By default the API will attempt to load the most appropriate language based on the users location or browser settings.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-region-restriction-type"><?php esc_html_e( 'Country filtering', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-region-restriction-type" name="oli_sl_options[region_restriction_type]">
				<option value="bias" <?php selected( oli_sl_get_option( 'region_restriction_type' ), 'bias' ); ?>><?php esc_html_e( 'Bias results (soft)', 'oli-store-locator' ); ?></option>
				<option value="restrict" <?php selected( oli_sl_get_option( 'region_restriction_type' ), 'restrict' ); ?>><?php esc_html_e( 'Restrict results (hard)', 'oli-store-locator' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Bias (soft) favors a single country but still returns matches everywhere. Restrict (hard) limits the results to the selected countries.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-bias-country"><?php esc_html_e( 'Bias country', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-bias-country" name="oli_sl_options[bias_country]">
				<?php foreach ( oli_sl_country_choices() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( oli_sl_get_option( 'bias_country' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Prefers results from the selected country when a search is ambiguous, but never excludes results elsewhere.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-restrict-countries"><?php esc_html_e( 'Restrict countries', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" id="oli-sl-restrict-countries" name="oli_sl_options[restrict_countries]" value="<?php echo esc_attr( oli_sl_get_option( 'restrict_countries' ) ); ?>" />
			<p class="description"><?php esc_html_e( 'Comma-separated country names used when country filtering is set to restrict.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Nominatim rate limit', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'nominatim_rate_limit', __( 'Limit Nominatim to 1 request per second (required by OpenStreetMap usage policy)', 'oli-store-locator' ) ); ?>
		</td>
	</tr>
</table>
