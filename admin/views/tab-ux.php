<?php
/**
 * User Experience settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Open links in a new window?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'open_links_new_window', __( 'Open links in a new window?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show a reset map button?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'show_reset_map', __( 'Show a reset map button?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show the route on an external website?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'direction_redirect', __( 'When enabled, clicking the directions link opens the route on an external website in a new window.', 'oli-store-locator' ) ); ?>
			<p><?php oli_sl_checkbox_field( 'enable_directions', __( 'Show a directions link', 'oli-store-locator' ) ); ?></p>
			<p><?php oli_sl_checkbox_field( 'in_map_directions', __( 'Draw the route on the embedded map (Google Maps only)', 'oli-store-locator' ) ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Make the contact details always clickable?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'clickable_contact', __( 'Make the contact details always clickable?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Hide days marked as closed in the opening hours?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'hide_closed_hours', __( 'Hide days marked as closed in the opening hours?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show the current open / closed status?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'show_hour_status', __( 'Show the current open / closed status?', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'When this option is enabled, the results that are loaded automatically on page load are no longer cached.', 'oli-store-locator' ); ?></p>
			<p><?php oli_sl_checkbox_field( 'expand_hours', __( 'Make the current opening status expandable in the search results?', 'oli-store-locator' ) ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Make the store name clickable if a store URL exists?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'clickable_store_name', __( 'Make the store name clickable if a store URL exists?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Make the phone number clickable on mobile devices?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'clickable_phone', __( 'Make the phone number clickable on mobile devices?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show a “Street view” link in the info window when available for the location?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'show_streetview_link', __( 'Show a “Street view” link in the info window when available for the location?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show a “Zoom here” link in the info window?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'show_zoom_here', __( 'Show a “Zoom here” link in the info window?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show the store thumbnail in the info window?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'infowindow_thumbnail', __( 'Show the store thumbnail in the info window?', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'The thumbnail is the featured image of the store, and only shows for stores that have one.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'On page load move the mouse cursor to the search field?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'mouse_focus', __( 'On page load move the mouse cursor to the search field?', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'This option is disabled on mobile devices.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Hide the country in the results?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'hide_country', __( 'Hide the country in the results?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Hide the distance in the results?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'hide_distance', __( 'Hide the distance in the results?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-marker-effect"><?php esc_html_e( 'When hovering over a search result, the matching marker', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-marker-effect" name="oli_sl_options[marker_effect]">
				<option value="bounce" <?php selected( oli_sl_get_option( 'marker_effect' ), 'bounce' ); ?>><?php esc_html_e( 'Bounces up and down', 'oli-store-locator' ); ?></option>
				<option value="info_window" <?php selected( oli_sl_get_option( 'marker_effect' ), 'info_window' ); ?>><?php esc_html_e( 'Will open the info window', 'oli-store-locator' ); ?></option>
				<option value="ignore" <?php selected( oli_sl_get_option( 'marker_effect' ), 'ignore' ); ?>><?php esc_html_e( 'Does not respond', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Clicking on the address details opens the info window on the map?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'address_opens_infowindow', __( 'Clicking on the address details opens the info window on the map?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show categories?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'show_categories_in_results', __( 'List the categories of a store below its address in the search results.', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Ask for map consent (GDPR)', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'gdpr_consent', __( 'Ask visitors for consent before the map loads', 'oli-store-locator' ) ); ?>
			<p><textarea class="large-text" rows="3" name="oli_sl_options[gdpr_text]"><?php echo esc_textarea( oli_sl_get_option( 'gdpr_text' ) ); ?></textarea></p>
		</td>
	</tr>
</table>
