<?php
/**
 * Search settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="oli-sl-search-method"><?php esc_html_e( 'Search method', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-search-method" name="oli_sl_options[search_method]">
				<option value="geocode" <?php selected( oli_sl_get_option( 'search_method' ), 'geocode' ); ?>><?php esc_html_e( 'Geocode API (default)', 'oli-store-locator' ); ?></option>
				<option value="name" <?php selected( oli_sl_get_option( 'search_method' ), 'name' ); ?>><?php esc_html_e( 'Name search (post / location name)', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-input-placeholder"><?php esc_html_e( 'Placeholder for the search field', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" id="oli-sl-input-placeholder" name="oli_sl_options[input_placeholder]" value="<?php echo esc_attr( oli_sl_get_option( 'input_placeholder' ) ); ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Attempt to auto-locate the user', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'auto_locate', __( 'Offer “Use my location” (HTML5 geolocation)', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'A HTTPS connection is required before the Geolocation API can access the user’s location. This option is ignored for a store locator that sets its own start point in the shortcode or block.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Enable autocomplete?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'enable_autocomplete', __( 'Address / ZIP autocomplete', 'oli-store-locator' ) ); ?>
			<p><?php oli_sl_checkbox_field( 'autosubmit_autocomplete', __( 'Automatically start a search when an autocomplete value is selected?', 'oli-store-locator' ) ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Force zip code only search', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'force_postalcode', __( 'Treat the search field as a postal / ZIP code', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'This option works best when the API results are restricted to one or more regions.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Hide the map until the first search is made?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'hide_map_until_search', __( 'Hide the map until the first search is made?', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'Please make sure to disable “Search around start location on page load” (Map) and “Attempt to auto-locate the user”, otherwise the map will still show on page load.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Hide the search results list?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'hide_results_list', __( 'Hide the search results list?', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'Only the search bar and the map are shown. The “No results found” message is shown in an overlay on the map.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Distance unit', 'oli-store-locator' ); ?></th>
		<td>
			<label><input type="radio" name="oli_sl_options[distance_unit]" value="km" <?php checked( oli_sl_get_option( 'distance_unit' ), 'km' ); ?> /> <?php esc_html_e( 'km', 'oli-store-locator' ); ?></label>
			<label><input type="radio" name="oli_sl_options[distance_unit]" value="mi" <?php checked( oli_sl_get_option( 'distance_unit' ), 'mi' ); ?> /> <?php esc_html_e( 'mi', 'oli-store-locator' ); ?></label>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show search radius dropdown?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'show_radius_dropdown', __( 'Show search radius dropdown?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show max results dropdown?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'show_results_dropdown', __( 'Show max results dropdown?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-max-results"><?php esc_html_e( 'Max search results', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="number" min="1" id="oli-sl-max-results" name="oli_sl_options[max_results]" value="<?php echo esc_attr( oli_sl_get_option( 'max_results' ) ); ?>" />
			<p>
				<label><?php esc_html_e( 'Max results dropdown options (comma separated)', 'oli-store-locator' ); ?><br />
					<input type="text" class="regular-text" name="oli_sl_options[max_results_options]" value="<?php echo esc_attr( oli_sl_get_option( 'max_results_options' ) ); ?>" />
				</label>
			</p>
			<p class="description"><?php esc_html_e( 'The default value is set as the selected max search results number.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-search-radius"><?php esc_html_e( 'Search radius options', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="number" min="1" id="oli-sl-search-radius" name="oli_sl_options[search_radius]" value="<?php echo esc_attr( oli_sl_get_option( 'search_radius' ) ); ?>" />
			<p>
				<label><?php esc_html_e( 'Radius dropdown options (comma separated)', 'oli-store-locator' ); ?><br />
					<input type="text" class="regular-text" name="oli_sl_options[radius_options]" value="<?php echo esc_attr( oli_sl_get_option( 'radius_options' ) ); ?>" />
				</label>
			</p>
			<p class="description"><?php esc_html_e( 'The default value is set as the selected search radius number.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-sort-by"><?php esc_html_e( 'Sort by', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-sort-by" name="oli_sl_options[sort_by]">
				<option value="distance" <?php selected( oli_sl_get_option( 'sort_by' ), 'distance' ); ?>><?php esc_html_e( 'Distance', 'oli-store-locator' ); ?></option>
				<option value="title" <?php selected( oli_sl_get_option( 'sort_by' ), 'title' ); ?>><?php esc_html_e( 'Store name', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-sort-order"><?php esc_html_e( 'Sort order', 'oli-store-locator' ); ?></label></th>
		<td>
			<select id="oli-sl-sort-order" name="oli_sl_options[sort_order]">
				<option value="asc" <?php selected( oli_sl_get_option( 'sort_order' ), 'asc' ); ?>><?php esc_html_e( 'Ascending', 'oli-store-locator' ); ?></option>
				<option value="desc" <?php selected( oli_sl_get_option( 'sort_order' ), 'desc' ); ?>><?php esc_html_e( 'Descending', 'oli-store-locator' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Enable category filter?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'category_filter', __( 'Enable category filter?', 'oli-store-locator' ) ); ?>
			<p>
				<label for="oli-sl-category-filter-type"><?php esc_html_e( 'Filter type', 'oli-store-locator' ); ?></label>
				<select id="oli-sl-category-filter-type" name="oli_sl_options[category_filter_type]">
					<option value="dropdown" <?php selected( oli_sl_get_option( 'category_filter_type' ), 'dropdown' ); ?>><?php esc_html_e( 'Dropdown', 'oli-store-locator' ); ?></option>
					<option value="checkboxes" <?php selected( oli_sl_get_option( 'category_filter_type' ), 'checkboxes' ); ?>><?php esc_html_e( 'Checkboxes', 'oli-store-locator' ); ?></option>
				</select>
			</p>
			<p><?php oli_sl_checkbox_field( 'all_categories_required', __( 'Only show locations that are in all the selected categories?', 'oli-store-locator' ) ); ?></p>
			<p><?php oli_sl_checkbox_field( 'category_filter_only', __( 'Only show the category filter?', 'oli-store-locator' ) ); ?></p>
			<p class="description"><?php esc_html_e( 'Enabling “only show the category filter” will automatically hide the location input field and the max results and radius filters.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Exclude locations from nearby countries?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'enforce_borders', __( 'Returns only locations in the same country as the search.', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'E.g. a search for a Canadian border town returns only Canadian locations, not ones a few km across the US border.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Return all locations when someone searches for a whole country or state?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'full_search', __( 'Ignore the max results and search radius limits for country or state searches.', 'oli-store-locator' ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show the number of returned results?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'show_number_results', __( 'Show the number of returned results?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'If no results are found, then show the nearest location ignoring the search radius?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'find_nearest', __( 'If a search returns no locations within the chosen radius, the single closest location is shown instead.', 'oli-store-locator' ) ); ?>
		</td>
	</tr>
</table>
