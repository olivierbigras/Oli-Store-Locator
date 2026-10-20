<?php
/**
 * Store search functionality.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store search functionality.
 */
class Oli_SL_Search {

	/**
	 * Search for stores near a location.
	 *
	 * @param array $args Search arguments.
	 * @return array Array of store results.
	 */
	public static function search_stores( $args = array() ) {
		$defaults = array(
			'lat'         => '',
			'lng'         => '',
			'radius'      => oli_sl_get_option( 'search_radius', '50' ),
			'max_results' => oli_sl_get_option( 'max_results', '25' ),
			'category'    => '',
			'unit'        => oli_sl_get_option( 'distance_unit', 'km' ),
		);

		$args = wp_parse_args( $args, $defaults );

		/**
		 * Filter search arguments before querying.
		 *
		 * @param array $args Search args.
		 */
		$args = apply_filters( 'oli_sl_search_args', $args );

		$cache_key = '';
		if ( oli_sl_get_option( 'enable_caching', '1' ) ) {
			$cache_key = 'oli_sl_search_' . md5( wp_json_encode( $args ) );
			$cached    = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$query_args = array(
			'post_type'      => 'oli_store',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- coordinate lookup is required for radius search.
				array(
					'key'     => '_oli_sl_latitude',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_oli_sl_longitude',
					'compare' => 'EXISTS',
				),
			),
		);

		if ( ! empty( $args['category'] ) ) {
			$terms = is_array( $args['category'] ) ? array_map( 'absint', $args['category'] ) : array( absint( $args['category'] ) );
			$terms = array_filter( $terms );
			if ( ! empty( $terms ) ) {
				$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- optional category filter.
					array(
						'taxonomy' => 'oli_store_category',
						'field'    => 'term_id',
						'terms'    => $terms,
					),
				);
			}
		}

		/**
		 * Filter the WP_Query args used for store search.
		 *
		 * @param array $query_args WP_Query args.
		 * @param array $args       Search args.
		 */
		$query_args = apply_filters( 'oli_sl_search_query_args', $query_args, $args );

		$query  = new WP_Query( $query_args );
		$stores = array();
		$ids    = $query->posts;

		$earth_radius = 'mi' === $args['unit'] ? 3959 : 6371;

		foreach ( $ids as $store_id ) {
			$store_lat = (float) get_post_meta( $store_id, '_oli_sl_latitude', true );
			$store_lng = (float) get_post_meta( $store_id, '_oli_sl_longitude', true );

			if ( '' !== $args['lat'] && '' !== $args['lng'] ) {
				$distance = self::calculate_distance(
					(float) $args['lat'],
					(float) $args['lng'],
					$store_lat,
					$store_lng,
					$earth_radius
				);

				if ( $distance > (float) $args['radius'] ) {
					continue;
				}
			} else {
				$distance = 0;
			}

			$stores[] = self::format_store_data( $store_id, $distance, $args['unit'] );
		}

		if ( '' !== $args['lat'] && '' !== $args['lng'] ) {
			usort(
				$stores,
				function ( $a, $b ) {
					return $a['distance'] <=> $b['distance'];
				}
			);
		}

		$stores = array_slice( $stores, 0, max( 1, (int) $args['max_results'] ) );

		/**
		 * Filter store search results.
		 *
		 * @param array $stores Results.
		 * @param array $args   Search args.
		 */
		$stores = apply_filters( 'oli_sl_search_results', $stores, $args );

		if ( oli_sl_get_option( 'enable_caching', '1' ) && $cache_key ) {
			$cache_duration = (int) oli_sl_get_option( 'cache_duration', '3600' );
			set_transient( $cache_key, $stores, $cache_duration );
		}

		return $stores;
	}

	/**
	 * Calculate distance between two coordinates using Haversine formula.
	 *
	 * @param float $lat1         Origin latitude.
	 * @param float $lng1         Origin longitude.
	 * @param float $lat2         Destination latitude.
	 * @param float $lng2         Destination longitude.
	 * @param float $earth_radius Earth radius (3959 for miles, 6371 for km).
	 * @return float Distance.
	 */
	public static function calculate_distance( $lat1, $lng1, $lat2, $lng2, $earth_radius = 6371 ) {
		$lat1 = deg2rad( $lat1 );
		$lng1 = deg2rad( $lng1 );
		$lat2 = deg2rad( $lat2 );
		$lng2 = deg2rad( $lng2 );

		$dlat = $lat2 - $lat1;
		$dlng = $lng2 - $lng1;

		$a = sin( $dlat / 2 ) * sin( $dlat / 2 ) +
			cos( $lat1 ) * cos( $lat2 ) *
			sin( $dlng / 2 ) * sin( $dlng / 2 );

		$c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );

		return $earth_radius * $c;
	}

	/**
	 * Format store data for output.
	 *
	 * @param int    $store_id Store post ID.
	 * @param float  $distance Distance from search location.
	 * @param string $unit     Distance unit.
	 * @return array Formatted store data.
	 */
	public static function format_store_data( $store_id, $distance = 0, $unit = 'km' ) {
		$categories     = wp_get_post_terms( $store_id, 'oli_store_category' );
		$category_names = array();
		$category_ids   = array();
		$marker_icon    = '';

		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				$category_names[] = $category->name;
				$category_ids[]   = (int) $category->term_id;
				if ( '' === $marker_icon ) {
					$marker_icon = get_term_meta( $category->term_id, 'oli_sl_marker_icon', true );
				}
			}
		}

		if ( '' === $marker_icon ) {
			$marker_icon = oli_sl_get_option( 'default_marker', '' );
		}

		$thumbnail_id  = get_post_thumbnail_id( $store_id );
		$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' ) : '';
		$image_url     = oli_sl_get_store_meta( $store_id, 'image_url', '' );
		if ( ! $thumbnail_url && $image_url ) {
			$thumbnail_url = $image_url;
		}

		$data = array(
			'id'           => $store_id,
			'title'        => get_the_title( $store_id ),
			'permalink'    => get_permalink( $store_id ),
			'address'      => oli_sl_get_store_meta( $store_id, 'address' ),
			'address_2'    => oli_sl_get_store_meta( $store_id, 'address_2' ),
			'city'         => oli_sl_get_store_meta( $store_id, 'city' ),
			'state'        => oli_sl_get_store_meta( $store_id, 'state' ),
			'zip'          => oli_sl_get_store_meta( $store_id, 'zip' ),
			'country'      => oli_sl_get_store_meta( $store_id, 'country' ),
			'latitude'     => oli_sl_get_store_meta( $store_id, 'latitude' ),
			'longitude'    => oli_sl_get_store_meta( $store_id, 'longitude' ),
			'phone'        => oli_sl_get_store_meta( $store_id, 'phone' ),
			'fax'          => oli_sl_get_store_meta( $store_id, 'fax' ),
			'email'        => oli_sl_get_store_meta( $store_id, 'email' ),
			'url'          => oli_sl_get_store_meta( $store_id, 'url' ),
			'hours'        => oli_sl_get_store_meta( $store_id, 'hours' ),
			'description'  => oli_sl_get_store_meta( $store_id, 'description' ),
			'thumbnail'    => $thumbnail_url,
			'categories'   => $category_names,
			'category_ids' => $category_ids,
			'marker_icon'  => $marker_icon,
			'distance'     => round( (float) $distance, 2 ),
			'unit'         => $unit,
		);

		/**
		 * Filter a single store payload.
		 *
		 * @param array $data     Store data.
		 * @param int   $store_id Store ID.
		 */
		return apply_filters( 'oli_sl_store_data', $data, $store_id );
	}

	/**
	 * Clear search cache.
	 */
	public static function clear_cache() {
		global $wpdb;

		$like = $wpdb->esc_like( '_transient_oli_sl_search_' ) . '%';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk transient cleanup.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );

		$like_timeout = $wpdb->esc_like( '_transient_timeout_oli_sl_search_' ) . '%';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk transient cleanup.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like_timeout ) );
	}
}
