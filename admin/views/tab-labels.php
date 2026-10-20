<?php
/**
 * Labels settings.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$labels = oli_sl_label_catalog();
?>
<p><?php esc_html_e( 'Override frontend strings. Leave blank to use the translated defaults. If WPML, or a plugin using the WPML API is active, the labels below are the source text.', 'oli-store-locator' ); ?></p>
<table class="form-table" role="presentation">
	<?php foreach ( $labels as $key => $label ) : ?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input type="text" class="regular-text" id="<?php echo esc_attr( $key ); ?>" name="oli_sl_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( oli_sl_get_option( $key ) ); ?>" /></td>
		</tr>
	<?php endforeach; ?>
</table>
