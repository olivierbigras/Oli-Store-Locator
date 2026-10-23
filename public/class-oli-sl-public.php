<?php
/**
 * Public-facing assets and templates.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public-facing assets and templates.
 */
class Oli_SL_Public {

	/**
	 * Register scripts and styles (enqueued when the locator is used).
	 */
	public function register_assets() {
		wp_register_style(
			'oli-sl-leaflet',
			OLI_SL_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.css',
			array(),
			'1.9.4'
		);
		wp_register_style(
			'oli-sl-leaflet-markercluster',
			OLI_SL_PLUGIN_URL . 'assets/vendor/leaflet-markercluster/MarkerCluster.css',
			array( 'oli-sl-leaflet' ),
			'1.5.3'
		);
		wp_register_style(
			'oli-sl-leaflet-markercluster-default',
			OLI_SL_PLUGIN_URL . 'assets/vendor/leaflet-markercluster/MarkerCluster.Default.css',
			array( 'oli-sl-leaflet-markercluster' ),
			'1.5.3'
		);
		wp_register_style(
			'oli-sl-public',
			OLI_SL_PLUGIN_URL . 'public/css/oli-sl-public.css',
			array(),
			OLI_SL_VERSION
		);
		wp_register_style(
			'oli-sl-public-rtl',
			OLI_SL_PLUGIN_URL . 'public/css/oli-sl-public-rtl.css',
			array( 'oli-sl-public' ),
			OLI_SL_VERSION
		);

		wp_register_script(
			'oli-sl-leaflet',
			OLI_SL_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.js',
			array(),
			'1.9.4',
			true
		);
		wp_register_script(
			'oli-sl-leaflet-markercluster',
			OLI_SL_PLUGIN_URL . 'assets/vendor/leaflet-markercluster/leaflet.markercluster.js',
			array( 'oli-sl-leaflet' ),
			'1.5.3',
			true
		);
		wp_register_script(
			'oli-sl-markerclusterer',
			OLI_SL_PLUGIN_URL . 'assets/vendor/markerclusterer/index.min.js',
			array(),
			'1.2.10',
			true
		);
		wp_register_script(
			'oli-sl-public',
			OLI_SL_PLUGIN_URL . 'public/js/oli-sl-public.js',
			array(),
			OLI_SL_VERSION,
			true
		);
	}

	/**
	 * Enqueue when a locator block/shortcode is present in the queried object.
	 */
	public function maybe_enqueue() {
		if ( ! is_singular() ) {
			return;
		}
		$post = get_post();
		if ( ! $post ) {
			return;
		}
		if ( has_shortcode( $post->post_content, 'oli_store_locator' )
			|| has_shortcode( $post->post_content, 'oli_store_map' )
			|| has_block( 'oli-store-locator/store-locator', $post )
			|| has_block( 'oli-store-locator/store-map', $post )
			|| is_singular( 'oli_store' )
		) {
			self::enqueue_locator_assets();
		}
	}

	/**
	 * Enqueue locator assets.
	 */
	public static function enqueue_locator_assets() {
		$provider = oli_sl_get_option( 'map_provider', 'leaflet' );

		wp_enqueue_style( 'oli-sl-public' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'oli-sl-public-rtl' );
		}

		if ( 'leaflet' === $provider ) {
			wp_enqueue_style( 'oli-sl-leaflet' );
			wp_enqueue_style( 'oli-sl-leaflet-markercluster' );
			wp_enqueue_style( 'oli-sl-leaflet-markercluster-default' );
			wp_enqueue_script( 'oli-sl-leaflet' );
			wp_enqueue_script( 'oli-sl-leaflet-markercluster' );
		} else {
			if ( oli_sl_is_enabled( oli_sl_get_option( 'compatibility_mode', '0' ) ) && wp_script_is( 'google-maps', 'registered' ) ) {
				wp_enqueue_script( 'google-maps' );
			}
			$key = oli_sl_get_option( 'google_api_key', '' );
			$src = add_query_arg(
				array(
					'key'       => $key,
					'libraries' => 'places',
					'callback'  => 'oliSLGoogleMapsReady',
					'loading'   => 'async',
				),
				// Declared in readme.txt == External services ==. Optional Maps JavaScript API, not a CDN copy of plugin files.
				'https://maps.googleapis.com/maps/api/js'
			);
			wp_enqueue_script( 'oli-sl-google-maps', $src, array(), OLI_SL_VERSION, true );
			wp_enqueue_script( 'oli-sl-markerclusterer' );
			wp_script_add_data( 'oli-sl-google-maps', 'strategy', 'async' );
		}

		wp_enqueue_script( 'oli-sl-public' );
		wp_localize_script( 'oli-sl-public', 'oliSLSettings', oli_sl_frontend_config() );

		self::add_locator_inline_css();
	}

	/**
	 * Theme variables and optional custom CSS, attached to the public stylesheet.
	 */
	public static function add_locator_inline_css() {
		wp_add_inline_style( 'oli-sl-public', self::locator_inline_css() );
	}

	/**
	 * Sanitized CSS for locator colors, layout, and the Custom CSS setting.
	 *
	 * @return string
	 */
	public static function locator_inline_css() {
		$defaults = function_exists( 'oli_sl_default_options' ) ? oli_sl_default_options() : array();
		$shadow   = oli_sl_is_enabled( oli_sl_get_option( 'infowindow_shadow', '1' ) ) ? '0 4px 16px rgba(0,0,0,.15)' : 'none';

		$css = sprintf(
			':root{--oli-sl-primary:%1$s;--oli-sl-button:%2$s;--oli-sl-button-text:%3$s;--oli-sl-text:%4$s;--oli-sl-hover:%5$s;--oli-sl-cluster:%6$s;--oli-sl-cluster-text:%7$s;--oli-sl-map-height:%8$spx;--oli-sl-featured:%9$s;--oli-sl-infowindow-bg:%10$s;--oli-sl-infowindow-text:%11$s;--oli-sl-infowindow-radius:%12$spx;--oli-sl-infowindow-shadow:%13$s;}',
			self::hex_color( 'primary_color', $defaults ),
			self::hex_color( 'button_color', $defaults ),
			self::hex_color( 'button_text_color', $defaults ),
			self::hex_color( 'text_color', $defaults ),
			self::hex_color( 'hover_color', $defaults ),
			self::hex_color( 'cluster_color', $defaults ),
			self::hex_color( 'cluster_text_color', $defaults ),
			absint( oli_sl_get_option( 'map_height', '500' ) ),
			self::hex_color( 'featured_color', $defaults ),
			self::hex_color( 'infowindow_bg', $defaults ),
			self::hex_color( 'infowindow_text_color', $defaults ),
			absint( oli_sl_get_option( 'infowindow_radius', '8' ) ),
			$shadow
		);

		$custom = oli_sl_get_option( 'custom_css', '' );
		if ( is_string( $custom ) && '' !== trim( $custom ) ) {
			$css .= wp_strip_all_tags( $custom );
		}

		return $css;
	}

	/**
	 * Sanitized hex color option with a safe fallback.
	 *
	 * @param string $key      Option key.
	 * @param array  $defaults Default options.
	 * @return string
	 */
	private static function hex_color( $key, $defaults ) {
		$color = sanitize_hex_color( oli_sl_get_option( $key ) );
		if ( $color ) {
			return $color;
		}
		$fallback = isset( $defaults[ $key ] ) ? sanitize_hex_color( $defaults[ $key ] ) : '';
		return $fallback ? $fallback : '#000000';
	}

	/**
	 * Single store template override.
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function single_template( $template ) {
		if ( ! is_singular( 'oli_store' ) ) {
			return $template;
		}
		if ( ! oli_sl_get_option( 'enable_single_pages', '1' ) ) {
			return $template;
		}
		$plugin_template = oli_sl_locate_template( 'single-store.php' );
		return $plugin_template ? $plugin_template : $template;
	}
}
