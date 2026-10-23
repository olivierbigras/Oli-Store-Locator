<?php
/**
 * Plugin translations from /languages.
 *
 * WordPress 6.7+ loads the plugin’s own languages folder automatically.
 * On 6.5–6.6, just-in-time loading only looks in wp-content/languages/plugins/,
 * so bundled .mo / .l10n.php files need load_plugin_textdomain() on init.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load bundled translations on WordPress versions older than 6.7.
 */
class Oli_SL_I18n {

	/**
	 * Whether bundled plugin-folder translations must be registered.
	 *
	 * @param string $wp_version WordPress version.
	 * @return bool
	 */
	public static function needs_bundled_textdomain( $wp_version ) {
		return version_compare( (string) $wp_version, '6.7', '<' );
	}

	/**
	 * Register the plugin text domain when WordPress will not.
	 */
	public function load_plugin_textdomain() {
		$version = function_exists( 'get_bloginfo' ) ? get_bloginfo( 'version' ) : '';
		if ( ! self::needs_bundled_textdomain( $version ) ) {
			return;
		}

		// WordPress 6.7+ JIT-loads /languages from the plugin folder. 6.5–6.6 only
		// look in WP_LANG_DIR/plugins/, so bundled fr_CA (e.g. “All categories”)
		// stays English without this call. Requires at least is 6.5, not 6.7.
		load_plugin_textdomain(
			'oli-store-locator',
			false,
			dirname( OLI_SL_PLUGIN_BASENAME ) . '/languages'
		);
	}
}
