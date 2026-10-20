<?php
/**
 * Labels settings tab.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$labels = array(
	'label_search'     => __( 'Search button', 'oli-store-locator' ),
	'label_address'    => __( 'Address field', 'oli-store-locator' ),
	'label_radius'     => __( 'Radius', 'oli-store-locator' ),
	'label_results'    => __( 'Max results', 'oli-store-locator' ),
	'label_category'   => __( 'Category', 'oli-store-locator' ),
	'label_locate'     => __( 'Use my location', 'oli-store-locator' ),
	'label_reset'      => __( 'Reset', 'oli-store-locator' ),
	'label_directions' => __( 'Directions', 'oli-store-locator' ),
	'label_no_results' => __( 'No results', 'oli-store-locator' ),
	'label_phone'      => __( 'Phone', 'oli-store-locator' ),
	'label_hours'      => __( 'Hours', 'oli-store-locator' ),
	'label_website'    => __( 'Website', 'oli-store-locator' ),
);
?>
<p><?php esc_html_e( 'Override frontend strings. Leave blank to use the translated defaults (WPML / TranslatePress friendly).', 'oli-store-locator' ); ?></p>
<table class="form-table" role="presentation">
	<?php foreach ( $labels as $key => $label ) : ?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input type="text" class="regular-text" id="<?php echo esc_attr( $key ); ?>" name="oli_sl_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( oli_sl_get_option( $key ) ); ?>" /></td>
		</tr>
	<?php endforeach; ?>
</table>
