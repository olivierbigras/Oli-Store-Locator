<?php
/**
 * Register the Store custom post type.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Store custom post type.
 */
class Oli_SL_Post_Type {

	/**
	 * Register the store post type.
	 */
	public function register_post_type() {
		$enable_single = (bool) oli_sl_get_option( 'enable_single_pages', '1' );
		$store_slug    = oli_sl_get_option( 'store_slug', 'store' );

		$labels = array(
			'name'                  => _x( 'Stores', 'Post type general name', 'oli-store-locator' ),
			'singular_name'         => _x( 'Store', 'Post type singular name', 'oli-store-locator' ),
			'menu_name'             => _x( 'Stores', 'Admin Menu text', 'oli-store-locator' ),
			'name_admin_bar'        => _x( 'Store', 'Add New on Toolbar', 'oli-store-locator' ),
			'add_new'               => __( 'Add New', 'oli-store-locator' ),
			'add_new_item'          => __( 'Add New Store', 'oli-store-locator' ),
			'new_item'              => __( 'New Store', 'oli-store-locator' ),
			'edit_item'             => __( 'Edit Store', 'oli-store-locator' ),
			'view_item'             => __( 'View Store', 'oli-store-locator' ),
			'all_items'             => __( 'All Stores', 'oli-store-locator' ),
			'search_items'          => __( 'Search Stores', 'oli-store-locator' ),
			'parent_item_colon'     => __( 'Parent Stores:', 'oli-store-locator' ),
			'not_found'             => __( 'No stores found.', 'oli-store-locator' ),
			'not_found_in_trash'    => __( 'No stores found in Trash.', 'oli-store-locator' ),
			'featured_image'        => _x( 'Store Image', 'Overrides the "Featured Image" phrase', 'oli-store-locator' ),
			'set_featured_image'    => _x( 'Set store image', 'Overrides the "Set featured image" phrase', 'oli-store-locator' ),
			'remove_featured_image' => _x( 'Remove store image', 'Overrides the "Remove featured image" phrase', 'oli-store-locator' ),
			'use_featured_image'    => _x( 'Use as store image', 'Overrides the "Use as featured image" phrase', 'oli-store-locator' ),
			'archives'              => _x( 'Store archives', 'The post type archive label', 'oli-store-locator' ),
			'insert_into_item'      => _x( 'Insert into store', 'Overrides the "Insert into post" phrase', 'oli-store-locator' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this store', 'Overrides the "Uploaded to this post" phrase', 'oli-store-locator' ),
			'filter_items_list'     => _x( 'Filter stores list', 'Screen reader text', 'oli-store-locator' ),
			'items_list_navigation' => _x( 'Stores list navigation', 'Screen reader text', 'oli-store-locator' ),
			'items_list'            => _x( 'Stores list', 'Screen reader text', 'oli-store-locator' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => $enable_single,
			'publicly_queryable' => $enable_single,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => sanitize_title( $store_slug ) ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-location',
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest'       => true,
		);

		/**
		 * Filter oli_store post type args.
		 *
		 * @param array $args Post type args.
		 */
		$args = apply_filters( 'oli_sl_post_type_args', $args );

		register_post_type( 'oli_store', $args );
	}
}
