<?php
/**
 * Store locator template (theme-overridable).
 *
 * Copy this file to yourtheme/oli-store-locator/store-locator.php
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$layout     = oli_sl_get_option( 'list_layout', 'below' );
$mobile     = oli_sl_get_option( 'mobile_layout', 'map_first' );
$filter     = oli_sl_get_option( 'category_filter_type', 'dropdown' );
$unit       = oli_sl_get_option( 'distance_unit', 'km' );
$radii      = oli_sl_parse_int_list( oli_sl_get_option( 'radius_options' ) );
$maxes      = oli_sl_parse_int_list( oli_sl_get_option( 'max_results_options' ) );
$radius     = isset( $atts['radius'] ) ? absint( $atts['radius'] ) : (int) oli_sl_get_option( 'search_radius' );
$max        = isset( $atts['max_results'] ) ? absint( $atts['max_results'] ) : (int) oli_sl_get_option( 'max_results' );
$height     = isset( $atts['height'] ) ? $atts['height'] : oli_sl_get_option( 'map_height', '500' );
$width      = isset( $atts['width'] ) ? $atts['width'] : oli_sl_get_option( 'map_width', '100%' );
$pre_cat    = isset( $atts['category'] ) ? $atts['category'] : '';
$categories = get_terms(
	array(
		'taxonomy'   => 'oli_store_category',
		'hide_empty' => true,
	)
);

if ( ! in_array( $radius, $radii, true ) ) {
	$radii[] = $radius;
	sort( $radii );
}
if ( ! in_array( $max, $maxes, true ) ) {
	$maxes[] = $max;
	sort( $maxes );
}

$map_height = is_numeric( $height ) ? $height . 'px' : $height;
$instance   = 'oli-sl-' . wp_unique_id();
?>
<div id="<?php echo esc_attr( $instance ); ?>" class="oli-sl-wrap oli-sl-layout-<?php echo esc_attr( $layout ); ?> oli-sl-mobile-<?php echo esc_attr( $mobile ); ?>" style="--oli-sl-map-height: <?php echo esc_attr( $map_height ); ?>; width: <?php echo esc_attr( $width ); ?>;">
	<form class="oli-sl-search" action="#" method="get">
		<div class="oli-sl-search-row">
			<label class="oli-sl-field oli-sl-field-address">
				<span class="screen-reader-text"><?php echo esc_html( oli_sl_get_label( 'label_address', __( 'Address or ZIP', 'oli-store-locator' ) ) ); ?></span>
				<input type="text" class="oli-sl-address-input" name="oli_sl_address" autocomplete="off" placeholder="<?php echo esc_attr( oli_sl_get_label( 'label_address', __( 'Address or ZIP', 'oli-store-locator' ) ) ); ?>" />
				<ul class="oli-sl-autocomplete" hidden></ul>
			</label>
			<label class="oli-sl-field">
				<span><?php echo esc_html( oli_sl_get_label( 'label_radius', __( 'Radius', 'oli-store-locator' ) ) ); ?></span>
				<select name="oli_sl_radius" class="oli-sl-radius">
					<?php foreach ( $radii as $item ) : ?>
						<option value="<?php echo esc_attr( $item ); ?>" <?php selected( $radius, $item ); ?>><?php echo esc_html( $item . ' ' . $unit ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="oli-sl-field">
				<span><?php echo esc_html( oli_sl_get_label( 'label_results', __( 'Results', 'oli-store-locator' ) ) ); ?></span>
				<select name="oli_sl_max_results" class="oli-sl-max-results">
					<?php foreach ( $maxes as $item ) : ?>
						<option value="<?php echo esc_attr( $item ); ?>" <?php selected( $max, $item ); ?>><?php echo esc_html( (string) $item ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
				<?php if ( 'checkboxes' === $filter ) : ?>
					<fieldset class="oli-sl-field oli-sl-categories">
						<legend><?php echo esc_html( oli_sl_get_label( 'label_category', __( 'Category', 'oli-store-locator' ) ) ); ?></legend>
						<?php foreach ( $categories as $category_term ) : ?>
							<label>
								<input type="checkbox" name="oli_sl_category[]" value="<?php echo esc_attr( $category_term->term_id ); ?>" <?php checked( (string) $pre_cat, (string) $category_term->term_id ); ?> />
								<?php echo esc_html( $category_term->name ); ?>
							</label>
						<?php endforeach; ?>
					</fieldset>
				<?php else : ?>
					<label class="oli-sl-field">
						<span><?php echo esc_html( oli_sl_get_label( 'label_category', __( 'Category', 'oli-store-locator' ) ) ); ?></span>
						<select name="oli_sl_category" class="oli-sl-category">
							<option value=""><?php esc_html_e( 'All categories', 'oli-store-locator' ); ?></option>
							<?php foreach ( $categories as $category_term ) : ?>
								<option value="<?php echo esc_attr( $category_term->term_id ); ?>" <?php selected( (string) $pre_cat, (string) $category_term->term_id ); ?>><?php echo esc_html( $category_term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
			<?php endif; ?>
			<div class="oli-sl-actions">
				<button type="submit" class="oli-sl-search-btn"><?php echo esc_html( oli_sl_get_label( 'label_search', __( 'Search', 'oli-store-locator' ) ) ); ?></button>
				<?php if ( oli_sl_get_option( 'auto_locate', '1' ) ) : ?>
					<button type="button" class="oli-sl-locate-btn"><?php echo esc_html( oli_sl_get_label( 'label_locate', __( 'Use my location', 'oli-store-locator' ) ) ); ?></button>
				<?php endif; ?>
				<button type="reset" class="oli-sl-reset-btn"><?php echo esc_html( oli_sl_get_label( 'label_reset', __( 'Reset', 'oli-store-locator' ) ) ); ?></button>
			</div>
		</div>
	</form>
	<div class="oli-sl-body">
		<div class="oli-sl-map" style="height: <?php echo esc_attr( $map_height ); ?>;"></div>
		<div class="oli-sl-results" aria-live="polite"></div>
	</div>
</div>
