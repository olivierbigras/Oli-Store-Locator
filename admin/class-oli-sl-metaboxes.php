<?php
/**
 * Store metaboxes.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store metaboxes.
 */
class Oli_SL_Metaboxes {

	/**
	 * Register metaboxes.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'oli_sl_store_details',
			__( 'Store Details', 'oli-store-locator' ),
			array( $this, 'render_details' ),
			'oli_store',
			'normal',
			'high'
		);
	}

	/**
	 * Render store details metabox.
	 *
	 * @param WP_Post $post Post.
	 */
	public function render_details( $post ) {
		wp_nonce_field( 'oli_sl_save_store', 'oli_sl_store_nonce' );

		$hours_format = oli_sl_get_option( 'hours_format', 'textarea' );
		$hours_struct = get_post_meta( $post->ID, '_oli_sl_hours_structured', true );
		if ( ! is_array( $hours_struct ) ) {
			$hours_struct = array();
		}

		$hide = array(
			'phone' => oli_sl_is_enabled( oli_sl_get_option( 'editor_hide_phone', '0' ) ),
			'fax'   => oli_sl_is_enabled( oli_sl_get_option( 'editor_hide_fax', '0' ) ),
			'email' => oli_sl_is_enabled( oli_sl_get_option( 'editor_hide_email', '0' ) ),
			'url'   => oli_sl_is_enabled( oli_sl_get_option( 'editor_hide_url', '0' ) ),
		);

		$fields = array(
			'address'     => array(
				'label' => __( 'Address', 'oli-store-locator' ),
				'type'  => 'text',
			),
			'address_2'   => array(
				'label' => __( 'Address line 2', 'oli-store-locator' ),
				'type'  => 'text',
			),
			'city'        => array(
				'label' => __( 'City', 'oli-store-locator' ),
				'type'  => 'text',
			),
			'state'       => array(
				'label' => __( 'Province / State', 'oli-store-locator' ),
				'type'  => 'text',
			),
			'zip'         => array(
				'label' => __( 'Postal / ZIP code', 'oli-store-locator' ),
				'type'  => 'text',
			),
			'country'     => array(
				'label' => __( 'Country', 'oli-store-locator' ),
				'type'  => 'text',
			),
			'phone'       => array(
				'label' => __( 'Phone', 'oli-store-locator' ),
				'type'  => 'text',
			),
			'fax'         => array(
				'label' => __( 'Fax', 'oli-store-locator' ),
				'type'  => 'text',
			),
			'email'       => array(
				'label' => __( 'Email', 'oli-store-locator' ),
				'type'  => 'email',
			),
			'url'         => array(
				'label' => __( 'Website URL', 'oli-store-locator' ),
				'type'  => 'url',
			),
			'image_url'   => array(
				'label' => __( 'Image URL', 'oli-store-locator' ),
				'type'  => 'url',
			),
			'description' => array(
				'label' => __( 'Short description', 'oli-store-locator' ),
				'type'  => 'textarea',
			),
		);

		echo '<div class="oli-sl-metabox">';
		$featured = oli_sl_get_store_meta( $post->ID, 'featured' );
		echo '<p><label><input type="checkbox" name="oli_sl_featured" value="1" ' . checked( $featured, '1', false ) . ' /> ' . esc_html__( 'Show first in results?', 'oli-store-locator' ) . '</label></p>';

		foreach ( $fields as $key => $field ) {
			if ( ! empty( $hide[ $key ] ) ) {
				continue;
			}
			$value = oli_sl_get_store_meta( $post->ID, $key );
			if ( 'country' === $key && '' === $value && 'auto-draft' === $post->post_status ) {
				$value = oli_sl_get_option( 'editor_default_country', '' );
			}
			echo '<p><label for="oli_sl_' . esc_attr( $key ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label><br />';
			if ( 'textarea' === $field['type'] ) {
				echo '<textarea class="widefat" rows="3" id="oli_sl_' . esc_attr( $key ) . '" name="oli_sl_' . esc_attr( $key ) . '">' . esc_textarea( $value ) . '</textarea>';
			} else {
				echo '<input class="widefat" type="' . esc_attr( $field['type'] ) . '" id="oli_sl_' . esc_attr( $key ) . '" name="oli_sl_' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
			}
			echo '</p>';
		}

		$hours = oli_sl_get_store_meta( $post->ID, 'hours' );
		if ( '' === $hours && 'auto-draft' === $post->post_status ) {
			$hours = oli_sl_get_option( 'editor_default_hours', '' );
		}
		if ( ! oli_sl_is_enabled( oli_sl_get_option( 'editor_hide_hours', '0' ) ) ) {
			echo '<p><label for="oli_sl_hours"><strong>' . esc_html__( 'Opening hours', 'oli-store-locator' ) . '</strong></label><br />';
			if ( 'dropdown' === $hours_format ) {
				echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Day', 'oli-store-locator' ) . '</th><th>' . esc_html__( 'Open', 'oli-store-locator' ) . '</th><th>' . esc_html__( 'Close', 'oli-store-locator' ) . '</th><th>' . esc_html__( 'Closed', 'oli-store-locator' ) . '</th></tr></thead><tbody>';
				foreach ( oli_sl_weekdays() as $day => $label ) {
					$open   = isset( $hours_struct[ $day ]['open'] ) ? $hours_struct[ $day ]['open'] : '09:00';
					$close  = isset( $hours_struct[ $day ]['close'] ) ? $hours_struct[ $day ]['close'] : '17:00';
					$closed = ! empty( $hours_struct[ $day ]['closed'] );
					echo '<tr><td>' . esc_html( $label ) . '</td>';
					echo '<td><input type="time" name="oli_sl_hours_struct[' . esc_attr( $day ) . '][open]" value="' . esc_attr( $open ) . '" /></td>';
					echo '<td><input type="time" name="oli_sl_hours_struct[' . esc_attr( $day ) . '][close]" value="' . esc_attr( $close ) . '" /></td>';
					echo '<td><input type="checkbox" name="oli_sl_hours_struct[' . esc_attr( $day ) . '][closed]" value="1" ' . checked( $closed, true, false ) . ' /></td></tr>';
				}
				echo '</tbody></table>';
				echo '<p class="description">' . esc_html__( 'A text version is also stored for templates and CSV export.', 'oli-store-locator' ) . '</p>';
			} else {
				echo '<textarea class="widefat" rows="5" id="oli_sl_hours" name="oli_sl_hours">' . esc_textarea( $hours ) . '</textarea>';
			}
			echo '</p>';
		}

		$lat    = oli_sl_get_store_meta( $post->ID, 'latitude' );
		$lng    = oli_sl_get_store_meta( $post->ID, 'longitude' );
		$manual = oli_sl_get_store_meta( $post->ID, 'manual_coords' );

		echo '<p><label for="oli_sl_latitude"><strong>' . esc_html__( 'Latitude', 'oli-store-locator' ) . '</strong></label><br />';
		echo '<input class="regular-text" type="text" id="oli_sl_latitude" name="oli_sl_latitude" value="' . esc_attr( $lat ) . '" /></p>';
		echo '<p><label for="oli_sl_longitude"><strong>' . esc_html__( 'Longitude', 'oli-store-locator' ) . '</strong></label><br />';
		echo '<input class="regular-text" type="text" id="oli_sl_longitude" name="oli_sl_longitude" value="' . esc_attr( $lng ) . '" /></p>';
		echo '<p><label><input type="checkbox" name="oli_sl_manual_coords" value="1" ' . checked( $manual, '1', false ) . ' /> ' . esc_html__( 'Manual lat/lng override (do not auto-geocode this store)', 'oli-store-locator' ) . '</label></p>';
		echo '<p><button type="button" class="button" id="oli-sl-geocode-btn">' . esc_html__( 'Lookup coordinates from address', 'oli-store-locator' ) . '</button> <span id="oli-sl-geocode-status"></span></p>';
		echo '</div>';
	}

	/**
	 * Save metabox fields.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public function save_meta_boxes( $post_id, $post = null ) {
		if ( ! isset( $_POST['oli_sl_store_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['oli_sl_store_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'oli_sl_save_store' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( $post && 'oli_store' !== $post->post_type ) {
			return;
		}

		$text_keys = array( 'address', 'address_2', 'city', 'state', 'zip', 'country', 'phone', 'fax' );
		foreach ( $text_keys as $key ) {
			$field = 'oli_sl_' . $key;
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, '_oli_sl_' . $key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
			}
		}

		foreach ( array( 'latitude', 'longitude' ) as $key ) {
			$field = 'oli_sl_' . $key;
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, '_oli_sl_' . $key, oli_sl_sanitize_coordinate( sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) ) );
			}
		}

		if ( isset( $_POST['oli_sl_email'] ) ) {
			update_post_meta( $post_id, '_oli_sl_email', sanitize_email( wp_unslash( $_POST['oli_sl_email'] ) ) );
		}
		if ( isset( $_POST['oli_sl_url'] ) ) {
			update_post_meta( $post_id, '_oli_sl_url', esc_url_raw( wp_unslash( $_POST['oli_sl_url'] ) ) );
		}
		if ( isset( $_POST['oli_sl_image_url'] ) ) {
			update_post_meta( $post_id, '_oli_sl_image_url', esc_url_raw( wp_unslash( $_POST['oli_sl_image_url'] ) ) );
		}
		if ( isset( $_POST['oli_sl_description'] ) ) {
			update_post_meta( $post_id, '_oli_sl_description', sanitize_textarea_field( wp_unslash( $_POST['oli_sl_description'] ) ) );
		}

		$manual = isset( $_POST['oli_sl_manual_coords'] ) ? '1' : '0';
		update_post_meta( $post_id, '_oli_sl_manual_coords', $manual );
		update_post_meta( $post_id, '_oli_sl_featured', isset( $_POST['oli_sl_featured'] ) ? '1' : '0' );

		if ( isset( $_POST['oli_sl_hours_struct'] ) && is_array( $_POST['oli_sl_hours_struct'] ) ) {
			$raw    = wp_unslash( $_POST['oli_sl_hours_struct'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
			$struct = array();
			$lines  = array();
			foreach ( oli_sl_weekdays() as $day => $label ) {
				$open           = isset( $raw[ $day ]['open'] ) ? sanitize_text_field( $raw[ $day ]['open'] ) : '';
				$close          = isset( $raw[ $day ]['close'] ) ? sanitize_text_field( $raw[ $day ]['close'] ) : '';
				$closed         = ! empty( $raw[ $day ]['closed'] ) ? '1' : '0';
				$struct[ $day ] = array(
					'open'   => $open,
					'close'  => $close,
					'closed' => $closed,
				);
				if ( '1' === $closed ) {
					/* translators: %s: weekday name */
					$lines[] = sprintf( __( '%s: Closed', 'oli-store-locator' ), $label );
				} else {
					/* translators: 1: weekday, 2: open time, 3: close time */
					$lines[] = sprintf( __( '%1$s: %2$s – %3$s', 'oli-store-locator' ), $label, $open, $close );
				}
			}
			update_post_meta( $post_id, '_oli_sl_hours_structured', $struct );
			update_post_meta( $post_id, '_oli_sl_hours', implode( "\n", $lines ) );
		} elseif ( isset( $_POST['oli_sl_hours'] ) ) {
			update_post_meta( $post_id, '_oli_sl_hours', sanitize_textarea_field( wp_unslash( $_POST['oli_sl_hours'] ) ) );
		}

		$lat = get_post_meta( $post_id, '_oli_sl_latitude', true );
		$lng = get_post_meta( $post_id, '_oli_sl_longitude', true );

		if ( '1' !== $manual && ( '' === $lat || '' === $lng ) ) {
			$address = oli_sl_join_address(
				array(
					get_post_meta( $post_id, '_oli_sl_address', true ),
					get_post_meta( $post_id, '_oli_sl_city', true ),
					get_post_meta( $post_id, '_oli_sl_state', true ),
					get_post_meta( $post_id, '_oli_sl_zip', true ),
					get_post_meta( $post_id, '_oli_sl_country', true ),
				)
			);
			if ( '' !== $address ) {
				$geocoded = Oli_SL_Geocoding::geocode( $address );
				if ( ! is_wp_error( $geocoded ) ) {
					update_post_meta( $post_id, '_oli_sl_latitude', $geocoded['lat'] );
					update_post_meta( $post_id, '_oli_sl_longitude', $geocoded['lng'] );
				}
			}
		}
	}
}
