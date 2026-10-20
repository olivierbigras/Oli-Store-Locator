<?php
/**
 * Define internationalization functionality.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define internationalization functionality.
 */
class Oli_SL_I18n {

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'oli-store-locator',
			false,
			dirname( OLI_SL_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
