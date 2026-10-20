<?php
/**
 * Google map style presets and Leaflet tile providers.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map design presets.
 */
class Oli_SL_Map_Styles {

	/**
	 * Google Maps JSON style presets.
	 *
	 * @return array
	 */
	public static function google_presets() {
		return array(
			'default'   => array(),
			'silver'    => array(
				array(
					'elementType' => 'geometry',
					'stylers'     => array( array( 'color' => '#f5f5f5' ) ),
				),
				array(
					'elementType' => 'labels.icon',
					'stylers'     => array( array( 'visibility' => 'off' ) ),
				),
				array(
					'elementType' => 'labels.text.fill',
					'stylers'     => array( array( 'color' => '#616161' ) ),
				),
				array(
					'featureType' => 'water',
					'elementType' => 'geometry',
					'stylers'     => array( array( 'color' => '#c9c9c9' ) ),
				),
			),
			'retro'     => array(
				array(
					'elementType' => 'geometry',
					'stylers'     => array( array( 'color' => '#ebe3cd' ) ),
				),
				array(
					'elementType' => 'labels.text.fill',
					'stylers'     => array( array( 'color' => '#523735' ) ),
				),
				array(
					'featureType' => 'water',
					'elementType' => 'geometry.fill',
					'stylers'     => array( array( 'color' => '#b9d3c2' ) ),
				),
			),
			'dark'      => array(
				array(
					'elementType' => 'geometry',
					'stylers'     => array( array( 'color' => '#212121' ) ),
				),
				array(
					'elementType' => 'labels.text.fill',
					'stylers'     => array( array( 'color' => '#757575' ) ),
				),
				array(
					'featureType' => 'water',
					'elementType' => 'geometry',
					'stylers'     => array( array( 'color' => '#000000' ) ),
				),
			),
			'night'     => array(
				array(
					'elementType' => 'geometry',
					'stylers'     => array( array( 'color' => '#242f3e' ) ),
				),
				array(
					'elementType' => 'labels.text.fill',
					'stylers'     => array( array( 'color' => '#746855' ) ),
				),
				array(
					'featureType' => 'water',
					'elementType' => 'geometry',
					'stylers'     => array( array( 'color' => '#17263c' ) ),
				),
			),
			'aubergine' => array(
				array(
					'elementType' => 'geometry',
					'stylers'     => array( array( 'color' => '#1d2c4d' ) ),
				),
				array(
					'elementType' => 'labels.text.fill',
					'stylers'     => array( array( 'color' => '#8ec3b9' ) ),
				),
				array(
					'featureType' => 'water',
					'elementType' => 'geometry.fill',
					'stylers'     => array( array( 'color' => '#021019' ) ),
				),
			),
		);
	}

	/**
	 * Leaflet tile providers (bundled URLs, loaded at runtime).
	 *
	 * @return array
	 */
	public static function leaflet_providers() {
		return array(
			'osm'         => array(
				'label'       => __( 'OpenStreetMap', 'oli-store-locator' ),
				'url'         => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
				'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
			),
			'carto_light' => array(
				'label'       => __( 'CartoDB Positron', 'oli-store-locator' ),
				'url'         => 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
				'attribution' => '&copy; OpenStreetMap &copy; <a href="https://carto.com/attributions">CARTO</a>',
			),
			'carto_dark'  => array(
				'label'       => __( 'CartoDB Dark Matter', 'oli-store-locator' ),
				'url'         => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
				'attribution' => '&copy; OpenStreetMap &copy; <a href="https://carto.com/attributions">CARTO</a>',
			),
			'topo'        => array(
				'label'       => __( 'OpenTopoMap', 'oli-store-locator' ),
				'url'         => 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
				'attribution' => '&copy; OpenStreetMap, SRTM | OpenTopoMap',
			),
		);
	}

	/**
	 * Resolve Google styles from preset or custom JSON.
	 *
	 * @return array
	 */
	public static function resolve_google_styles() {
		$preset = oli_sl_get_option( 'map_style_preset', 'default' );
		$custom = oli_sl_get_option( 'map_style_json', '' );

		if ( 'custom' === $preset && is_string( $custom ) && '' !== $custom ) {
			$decoded = json_decode( $custom, true );
			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}

		$presets = self::google_presets();
		return isset( $presets[ $preset ] ) ? $presets[ $preset ] : array();
	}
}
