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

$layout        = oli_sl_get_option( 'list_layout', 'below' );
$mobile        = oli_sl_get_option( 'mobile_layout', 'map_first' );
$filter        = oli_sl_get_option( 'category_filter_type', 'dropdown' );
$unit          = oli_sl_get_option( 'distance_unit', 'km' );
$radii         = oli_sl_parse_int_list( oli_sl_get_option( 'radius_options' ) );
$maxes         = oli_sl_parse_int_list( oli_sl_get_option( 'max_results_options' ) );
$radius        = isset( $atts['radius'] ) ? absint( $atts['radius'] ) : (int) oli_sl_get_option( 'search_radius' );
$max           = isset( $atts['max_results'] ) ? absint( $atts['max_results'] ) : (int) oli_sl_get_option( 'max_results' );
$height        = isset( $atts['height'] ) ? $atts['height'] : oli_sl_get_option( 'map_height', '500' );
$width         = isset( $atts['width'] ) ? $atts['width'] : oli_sl_get_option( 'map_width', '100%' );
$pre_cat       = isset( $atts['category'] ) ? $atts['category'] : '';
$search_method = oli_sl_get_option( 'search_method', 'geocode' );
$placeholder   = oli_sl_get_option( 'input_placeholder', '' );
if ( '' === $placeholder ) {
	$placeholder = ( 'name' === $search_method )
		? oli_sl_get_label( 'label_store_name', __( 'Store name', 'oli-store-locator' ) )
		: oli_sl_get_label( 'label_address', __( 'Address or ZIP', 'oli-store-locator' ) );
}
$prefill = isset( $_GET['oli_sl_address'] ) ? sanitize_text_field( wp_unslash( $_GET['oli_sl_address'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public deep-link.
if ( '' === $prefill && isset( $_GET['oli_sl_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public deep-link.
	$prefill = sanitize_text_field( wp_unslash( $_GET['oli_sl_name'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public deep-link.
}
$show_radius   = oli_sl_is_enabled( oli_sl_get_option( 'show_radius_dropdown', '1' ) ) && 'name' !== $search_method;
$show_results  = oli_sl_is_enabled( oli_sl_get_option( 'show_results_dropdown', '1' ) );
$show_category = oli_sl_is_enabled( oli_sl_get_option( 'category_filter', '1' ) );
$category_only = oli_sl_is_enabled( oli_sl_get_option( 'category_filter_only', '0' ) );
$hide_list     = oli_sl_is_enabled( oli_sl_get_option( 'hide_results_list', '0' ) );
$hide_map      = oli_sl_is_enabled( oli_sl_get_option( 'hide_map_until_search', '0' ) );
$gdpr          = oli_sl_is_enabled( oli_sl_get_option( 'gdpr_consent', '0' ) );
$show_reset    = oli_sl_is_enabled( oli_sl_get_option( 'show_reset_map', '1' ) );
$categories    = get_terms(
	array(
		'taxonomy'   => 'oli_store_category',
		'hide_empty' => true,
	)
);

if ( isset( $atts['results_list'] ) && in_array( (string) $atts['results_list'], array( 'false', '0' ), true ) ) {
	$hide_list = true;
}

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
$classes    = array(
	'oli-sl-wrap',
	'oli-sl-layout-' . $layout,
	'oli-sl-mobile-' . $mobile,
);
if ( $hide_list ) {
	$classes[] = 'oli-sl-hide-list';
}
if ( $hide_map ) {
	$classes[] = 'oli-sl-hide-map';
}
?>
<div id="<?php echo esc_attr( $instance ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="--oli-sl-map-height: <?php echo esc_attr( $map_height ); ?>; width: <?php echo esc_attr( $width ); ?>;"
	data-search-method="<?php echo esc_attr( $search_method ); ?>"
	<?php
	if ( ! empty( $atts['start_lat'] ) ) :
		?>
		data-start-lat="<?php echo esc_attr( $atts['start_lat'] ); ?>"<?php endif; ?>
	<?php
	if ( ! empty( $atts['start_lng'] ) ) :
		?>
		data-start-lng="<?php echo esc_attr( $atts['start_lng'] ); ?>"<?php endif; ?>
	<?php
	if ( ! empty( $atts['zoom'] ) ) :
		?>
		data-zoom="<?php echo esc_attr( $atts['zoom'] ); ?>"<?php endif; ?>
	<?php
	if ( ! empty( $atts['directions'] ) ) :
		?>
		data-directions="<?php echo esc_attr( $atts['directions'] ); ?>"<?php endif; ?>
>
	<?php if ( $gdpr ) : ?>
		<div class="oli-sl-consent">
			<p><?php echo esc_html( oli_sl_get_label( 'label_consent', '' ) ? oli_sl_get_option( 'gdpr_text' ) : ( oli_sl_get_option( 'gdpr_text' ) ? oli_sl_get_option( 'gdpr_text' ) : __( 'This map loads tiles from an external map service. Accept to display the map.', 'oli-store-locator' ) ) ); ?></p>
			<button type="button" class="oli-sl-consent-btn"><?php echo esc_html( oli_sl_get_label( 'label_consent', __( 'Load the map', 'oli-store-locator' ) ) ); ?></button>
		</div>
	<?php endif; ?>
	<form class="oli-sl-search" action="#" method="get">
		<div class="oli-sl-search-row">
			<?php if ( ! $category_only ) : ?>
			<label class="oli-sl-field oli-sl-field-address">
				<span class="screen-reader-text"><?php echo esc_html( $placeholder ); ?></span>
				<input type="text" class="oli-sl-address-input" name="oli_sl_address" autocomplete="off" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( $prefill ); ?>" />
				<ul class="oli-sl-autocomplete" hidden></ul>
			</label>
			<?php endif; ?>
			<?php if ( $show_radius && ! $category_only ) : ?>
			<label class="oli-sl-field">
				<span><?php echo esc_html( oli_sl_get_label( 'label_radius', __( 'Search radius', 'oli-store-locator' ) ) ); ?></span>
				<select name="oli_sl_radius" class="oli-sl-radius">
					<?php foreach ( $radii as $item ) : ?>
						<option value="<?php echo esc_attr( $item ); ?>" <?php selected( $radius, $item ); ?>><?php echo esc_html( $item . ' ' . $unit ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php endif; ?>
			<?php if ( $show_results && ! $category_only ) : ?>
			<label class="oli-sl-field">
				<span><?php echo esc_html( oli_sl_get_label( 'label_results', __( 'Results', 'oli-store-locator' ) ) ); ?></span>
				<select name="oli_sl_max_results" class="oli-sl-max-results">
					<?php foreach ( $maxes as $item ) : ?>
						<option value="<?php echo esc_attr( $item ); ?>" <?php selected( $max, $item ); ?>><?php echo esc_html( (string) $item ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php endif; ?>
			<?php if ( $show_category && ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
				<?php if ( 'checkboxes' === $filter ) : ?>
					<fieldset class="oli-sl-field oli-sl-categories">
						<legend><?php echo esc_html( oli_sl_get_label( 'label_category', __( 'Category filter', 'oli-store-locator' ) ) ); ?></legend>
						<?php foreach ( $categories as $category_term ) : ?>
							<?php $cat_color = get_term_meta( $category_term->term_id, 'oli_sl_color', true ); ?>
							<label>
								<input type="checkbox" name="oli_sl_category[]" value="<?php echo esc_attr( $category_term->term_id ); ?>" <?php checked( (string) $pre_cat, (string) $category_term->term_id ); ?> />
								<?php if ( $cat_color ) : ?>
									<span class="oli-sl-cat-dot" style="background:<?php echo esc_attr( $cat_color ); ?>"></span>
								<?php endif; ?>
								<?php echo esc_html( $category_term->name ); ?>
							</label>
						<?php endforeach; ?>
					</fieldset>
				<?php else : ?>
					<label class="oli-sl-field">
						<span><?php echo esc_html( oli_sl_get_label( 'label_category', __( 'Category filter', 'oli-store-locator' ) ) ); ?></span>
						<select name="oli_sl_category" class="oli-sl-category">
							<option value=""><?php echo esc_html( oli_sl_get_label( 'label_category_first', __( 'All categories', 'oli-store-locator' ) ) ); ?></option>
							<?php foreach ( $categories as $category_term ) : ?>
								<option value="<?php echo esc_attr( $category_term->term_id ); ?>" <?php selected( (string) $pre_cat, (string) $category_term->term_id ); ?>><?php echo esc_html( $category_term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
			<?php endif; ?>
			<div class="oli-sl-actions">
				<button type="submit" class="oli-sl-search-btn"><?php echo esc_html( oli_sl_get_label( 'label_search', __( 'Search', 'oli-store-locator' ) ) ); ?></button>
				<?php if ( oli_sl_get_option( 'auto_locate', '1' ) && 'name' !== $search_method && ! $category_only ) : ?>
					<button type="button" class="oli-sl-locate-btn"><?php echo esc_html( oli_sl_get_label( 'label_locate', __( 'Use my location', 'oli-store-locator' ) ) ); ?></button>
				<?php endif; ?>
				<?php if ( $show_reset ) : ?>
					<button type="reset" class="oli-sl-reset-btn"><?php echo esc_html( oli_sl_get_label( 'label_reset', __( 'Reset', 'oli-store-locator' ) ) ); ?></button>
				<?php endif; ?>
			</div>
		</div>
	</form>
	<div class="oli-sl-count" hidden></div>
	<div class="oli-sl-body">
		<div class="oli-sl-map" style="height: <?php echo esc_attr( $map_height ); ?>;"></div>
		<div class="oli-sl-results" aria-live="polite"></div>
	</div>
</div>
