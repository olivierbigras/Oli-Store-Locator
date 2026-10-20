<?php
/**
 * Markers settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php esc_html_e( 'Pick a default marker, or upload your own image. Category markers override the default when a store belongs to that category.', 'oli-store-locator' ); ?></p>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Default marker icon', 'oli-store-locator' ); ?></th>
		<td>
			<input type="url" class="regular-text" id="oli_sl_default_marker" name="oli_sl_options[default_marker]" value="<?php echo esc_attr( oli_sl_get_option( 'default_marker' ) ); ?>" />
			<button type="button" class="button oli-sl-media-upload" data-target="oli_sl_default_marker"><?php esc_html_e( 'Upload', 'oli-store-locator' ); ?></button>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Hide the start marker?', 'oli-store-locator' ); ?></th>
		<td><?php oli_sl_checkbox_field( 'hide_start_marker', __( 'Hide the start marker?', 'oli-store-locator' ) ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Show start marker on top of store markers?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'start_marker_on_top', __( 'Show start marker on top of store markers?', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'When enabled, the start marker will always appear above store markers on the map, even when they overlap.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Enable marker clusters?', 'oli-store-locator' ); ?></th>
		<td>
			<?php oli_sl_checkbox_field( 'marker_clustering', __( 'Enable marker clusters?', 'oli-store-locator' ) ); ?>
			<p class="description"><?php esc_html_e( 'Recommended for maps with a large amount of markers.', 'oli-store-locator' ); ?></p>
			<p><?php oli_sl_checkbox_field( 'cluster_exclude_start', __( 'Exclude the start marker from the marker cluster?', 'oli-store-locator' ) ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-cluster-max-zoom"><?php esc_html_e( 'Max zoom level', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="number" min="1" max="21" id="oli-sl-cluster-max-zoom" name="oli_sl_options[cluster_max_zoom]" value="<?php echo esc_attr( oli_sl_get_option( 'cluster_max_zoom' ) ); ?>" />
			<p class="description"><?php esc_html_e( 'If this zoom level is reached or exceeded, all markers are moved out of the marker cluster and shown as individual markers.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="oli-sl-cluster-size"><?php esc_html_e( 'Cluster size', 'oli-store-locator' ); ?></label></th>
		<td>
			<input type="number" min="20" max="200" id="oli-sl-cluster-size" name="oli_sl_options[cluster_size]" value="<?php echo esc_attr( oli_sl_get_option( 'cluster_size' ) ); ?>" />
			<p class="description"><?php esc_html_e( 'This pixel value determines how close markers need to be to each other to be grouped into a cluster. A larger number will result in a lower amount of clusters.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Cluster colors', 'oli-store-locator' ); ?></th>
		<td>
			<p><label><?php esc_html_e( 'Low density color', 'oli-store-locator' ); ?> <input type="text" class="oli-sl-color" name="oli_sl_options[cluster_color]" value="<?php echo esc_attr( oli_sl_get_option( 'cluster_color' ) ); ?>" /></label></p>
			<p><label><?php esc_html_e( 'Label color', 'oli-store-locator' ); ?> <input type="text" class="oli-sl-color" name="oli_sl_options[cluster_text_color]" value="<?php echo esc_attr( oli_sl_get_option( 'cluster_text_color' ) ); ?>" /></label></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Featured store color', 'oli-store-locator' ); ?></th>
		<td>
			<input type="text" class="oli-sl-color" name="oli_sl_options[featured_color]" value="<?php echo esc_attr( oli_sl_get_option( 'featured_color' ) ); ?>" />
			<p class="description"><?php esc_html_e( 'Used for stores marked “Show first in results?” in the store editor.', 'oli-store-locator' ); ?></p>
		</td>
	</tr>
</table>
