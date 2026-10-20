<?php
/**
 * Map settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$presets = array(
	'default'   => __( 'Default', 'oli-store-locator' ),
	'silver'    => __( 'Silver', 'oli-store-locator' ),
	'retro'     => __( 'Retro', 'oli-store-locator' ),
	'dark'      => __( 'Dark', 'oli-store-locator' ),
	'night'     => __( 'Night', 'oli-store-locator' ),
	'aubergine' => __( 'Aubergine', 'oli-store-locator' ),
	'custom'    => __( 'Custom JSON', 'oli-store-locator' ),
);
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Start location', 'oli-store-locator' ); ?></th>
		<td>
			<label><?php esc_html_e( 'Latitude', 'oli-store-locator' ); ?> <input type="text" name="oli_sl_options[start_lat]" value="<?php echo esc_attr( oli_sl_get_option( 'start_lat' ) ); ?>" /></label>
			<label><?php esc_html_e( 'Longitude', 'oli-store-locator' ); ?> <input type="text" name="oli_sl_options[start_lng]" value="<?php echo esc_attr( oli_sl_get_option( 'start_lng' ) ); ?>" /></label>
			<p class="description"><?php esc_html_e( 'Where the map opens before a search. Featured stores still appear above other results.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-start-zoom"><?php esc_html_e( 'Initial zoom level', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="number" min="1" max="21" id="oli-sl-start-zoom" name="oli_sl_options[start_zoom]" value="<?php echo esc_attr( oli_sl_get_option( 'start_zoom' ) ); ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-max-auto-zoom"><?php esc_html_e( 'Max auto zoom level', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="number" min="1" max="21" id="oli-sl-max-auto-zoom" name="oli_sl_options[max_auto_zoom]" value="<?php echo esc_attr( oli_sl_get_option( 'max_auto_zoom' ) ); ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-map-type"><?php esc_html_e( 'Map type', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-map-type" name="oli_sl_options[map_type]">
				<?php foreach ( array( 'roadmap', 'satellite', 'hybrid', 'terrain' ) as $map_type_choice ) : ?>
					<option value="<?php echo esc_attr( $map_type_choice ); ?>" <?php selected( oli_sl_get_option( 'map_type' ), $map_type_choice ); ?>><?php echo esc_html( ucfirst( $map_type_choice ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Load locations on page load', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'search_on_load', __( 'Search around start location on page load', 'oli-store-locator' ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Controls', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'scroll_wheel', __( 'Scroll wheel zoom', 'oli-store-locator' ) ); ?><br />
			<?php oli_sl_checkbox_field( 'map_controls', __( 'Map controls (zoom, pan)', 'oli-store-locator' ) ); ?><br />
			<?php oli_sl_checkbox_field( 'street_view', __( 'Street View control (Google)', 'oli-store-locator' ) ); ?><br />
			<?php oli_sl_checkbox_field( 'fit_bounds', __( 'Fit map to results', 'oli-store-locator' ) ); ?><br />
			<?php oli_sl_checkbox_field( 'draw_radius', __( 'Draw search radius circle', 'oli-store-locator' ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Google map style', 'oli-store-locator' ); ?></th>
		<td>
			<select name="oli_sl_options[map_style_preset]">
				<?php foreach ( $presets as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( oli_sl_get_option( 'map_style_preset' ), $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<p><textarea class="large-text code" rows="6" name="oli_sl_options[map_style_json]" placeholder='[{"featureType":"all","stylers":[{"saturation":-80}]}]'><?php echo esc_textarea( oli_sl_get_option( 'map_style_json' ) ); ?></textarea></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Leaflet tiles', 'oli-store-locator' ); ?></th>
		<td>
			<select name="oli_sl_options[leaflet_tiles]">
				<?php foreach ( Oli_SL_Map_Styles::leaflet_providers() as $key => $provider ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( oli_sl_get_option( 'leaflet_tiles' ), $key ); ?>><?php echo esc_html( $provider['label'] ); ?></option>
				<?php endforeach; ?>
				<option value="custom" <?php selected( oli_sl_get_option( 'leaflet_tiles' ), 'custom' ); ?>><?php esc_html_e( 'Custom URL template', 'oli-store-locator' ); ?></option>
			</select>
			<p>
				<input type="url" class="large-text" name="oli_sl_options[leaflet_custom_url]" value="<?php echo esc_attr( oli_sl_get_option( 'leaflet_custom_url' ) ); ?>" placeholder="https://{s}.example.com/{z}/{x}/{y}.png" />
			</p>
			<p class="description"><?php esc_html_e( 'The stored tile key for OpenStreetMap remains “osm”.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
</table>
