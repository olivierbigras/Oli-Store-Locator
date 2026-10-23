<?php
/**
 * Register taxonomies for stores.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register taxonomies for stores.
 */
class Oli_SL_Taxonomies {

	/**
	 * Register the store categories and tags taxonomies.
	 */
	public function register_taxonomies() {
		$category_labels = array(
			'name'                       => _x( 'Store Categories', 'taxonomy general name', 'oli-store-locator' ),
			'singular_name'              => _x( 'Store Category', 'taxonomy singular name', 'oli-store-locator' ),
			'search_items'               => __( 'Search Store Categories', 'oli-store-locator' ),
			'popular_items'              => __( 'Popular Store Categories', 'oli-store-locator' ),
			'all_items'                  => __( 'All Store Categories', 'oli-store-locator' ),
			'parent_item'                => __( 'Parent Store Category', 'oli-store-locator' ),
			'parent_item_colon'          => __( 'Parent Store Category:', 'oli-store-locator' ),
			'edit_item'                  => __( 'Edit Store Category', 'oli-store-locator' ),
			'update_item'                => __( 'Update Store Category', 'oli-store-locator' ),
			'add_new_item'               => __( 'Add New Store Category', 'oli-store-locator' ),
			'new_item_name'              => __( 'New Store Category Name', 'oli-store-locator' ),
			'separate_items_with_commas' => __( 'Separate store categories with commas', 'oli-store-locator' ),
			'add_or_remove_items'        => __( 'Add or remove store categories', 'oli-store-locator' ),
			'choose_from_most_used'      => __( 'Choose from the most used store categories', 'oli-store-locator' ),
			'not_found'                  => __( 'No store categories found.', 'oli-store-locator' ),
			'menu_name'                  => __( 'Categories', 'oli-store-locator' ),
		);

		register_taxonomy(
			'oli_store_category',
			array( 'oli_store' ),
			array(
				'hierarchical'      => true,
				'labels'            => $category_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'store-category' ),
				'show_in_rest'      => true,
			)
		);

		$tag_labels = array(
			'name'                       => _x( 'Store Tags', 'taxonomy general name', 'oli-store-locator' ),
			'singular_name'              => _x( 'Store Tag', 'taxonomy singular name', 'oli-store-locator' ),
			'search_items'               => __( 'Search Store Tags', 'oli-store-locator' ),
			'popular_items'              => __( 'Popular Store Tags', 'oli-store-locator' ),
			'all_items'                  => __( 'All Store Tags', 'oli-store-locator' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Store Tag', 'oli-store-locator' ),
			'update_item'                => __( 'Update Store Tag', 'oli-store-locator' ),
			'add_new_item'               => __( 'Add New Store Tag', 'oli-store-locator' ),
			'new_item_name'              => __( 'New Store Tag Name', 'oli-store-locator' ),
			'separate_items_with_commas' => __( 'Separate store tags with commas', 'oli-store-locator' ),
			'add_or_remove_items'        => __( 'Add or remove store tags', 'oli-store-locator' ),
			'choose_from_most_used'      => __( 'Choose from the most used store tags', 'oli-store-locator' ),
			'not_found'                  => __( 'No store tags found.', 'oli-store-locator' ),
			'menu_name'                  => __( 'Tags', 'oli-store-locator' ),
		);

		register_taxonomy(
			'oli_store_tag',
			array( 'oli_store' ),
			array(
				'hierarchical'      => false,
				'labels'            => $tag_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'store-tag' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Add marker icon field to category add form.
	 */
	public function add_category_marker_field() {
		wp_nonce_field( 'oli_sl_save_category_marker', 'oli_sl_category_marker_nonce' );
		?>
		<div class="form-field">
			<label for="oli_sl_marker_icon"><?php esc_html_e( 'Marker Icon URL', 'oli-store-locator' ); ?></label>
			<input type="url" name="oli_sl_marker_icon" id="oli_sl_marker_icon" value="" />
			<p class="description"><?php esc_html_e( 'Enter the URL of a custom marker icon for this category.', 'oli-store-locator' ); ?></p>
		</div>
		<div class="form-field">
			<label for="oli_sl_color"><?php esc_html_e( 'Color', 'oli-store-locator' ); ?></label>
			<input type="text" class="oli-sl-color" name="oli_sl_color" id="oli_sl_color" value="#0073aa" />
			<p class="description"><?php esc_html_e( 'Shown as a dot in the category filter and in the Color column.', 'oli-store-locator' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add marker icon field to category edit form.
	 *
	 * @param WP_Term $term The term object.
	 */
	public function edit_category_marker_field( $term ) {
		$marker_icon = get_term_meta( $term->term_id, 'oli_sl_marker_icon', true );
		$color       = get_term_meta( $term->term_id, 'oli_sl_color', true );
		wp_nonce_field( 'oli_sl_save_category_marker', 'oli_sl_category_marker_nonce' );
		?>
		<tr class="form-field">
			<th scope="row">
				<label for="oli_sl_marker_icon"><?php esc_html_e( 'Marker Icon URL', 'oli-store-locator' ); ?></label>
			</th>
			<td>
				<input type="url" name="oli_sl_marker_icon" id="oli_sl_marker_icon" value="<?php echo esc_attr( $marker_icon ); ?>" class="regular-text" />
				<p class="description"><?php esc_html_e( 'Enter the URL of a custom marker icon for this category.', 'oli-store-locator' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row">
				<label for="oli_sl_color"><?php esc_html_e( 'Color', 'oli-store-locator' ); ?></label>
			</th>
			<td>
				<input type="text" class="oli-sl-color" name="oli_sl_color" id="oli_sl_color" value="<?php echo esc_attr( $color ? $color : '#0073aa' ); ?>" />
				<p class="description"><?php esc_html_e( 'Shown as a dot in the category filter and in the Color column.', 'oli-store-locator' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save marker icon field.
	 *
	 * @param int $term_id The term ID.
	 */
	public function save_category_marker_field( $term_id ) {
		if ( ! isset( $_POST['oli_sl_category_marker_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['oli_sl_category_marker_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'oli_sl_save_category_marker' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		if ( isset( $_POST['oli_sl_marker_icon'] ) ) {
			update_term_meta( $term_id, 'oli_sl_marker_icon', esc_url_raw( wp_unslash( $_POST['oli_sl_marker_icon'] ) ) );
		}

		if ( isset( $_POST['oli_sl_color'] ) ) {
			$color = sanitize_hex_color( wp_unslash( $_POST['oli_sl_color'] ) );
			if ( $color ) {
				update_term_meta( $term_id, 'oli_sl_color', $color );
			}
		}
	}

	/**
	 * Category list columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function category_columns( $columns ) {
		$columns['oli_sl_color'] = __( 'Color', 'oli-store-locator' );
		return $columns;
	}

	/**
	 * Category list column content.
	 *
	 * @param string $content Content.
	 * @param string $column  Column.
	 * @param int    $term_id Term ID.
	 * @return string
	 */
	public function category_column_content( $content, $column, $term_id ) {
		if ( 'oli_sl_color' !== $column ) {
			return $content;
		}

		$color = get_term_meta( $term_id, 'oli_sl_color', true );
		if ( ! $color ) {
			return '—';
		}

		return '<span class="oli-sl-cat-dot" style="display:inline-block;width:12px;height:12px;border-radius:50%;background:' . esc_attr( $color ) . ';"></span> ' . esc_html( $color );
	}
}
