<?php
/**
 * Plugin Name: Oli Store Locator
 * Plugin URI: https://github.com/olivierbigras/Oli-Store-Locator
 * Description: A powerful and fully customizable store locator with Google Maps/Leaflet, Google Sheets sync, and extensive design options.
 * Version: 0.2.2
 * Author: Olivier Bigras
 * Author URI: https://olivierbigras.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: oli-store-locator
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 7.1
 * Requires PHP: 7.4
 *
 * @package Oli_Store_Locator
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'OLI_SL_VERSION', '0.2.2' );
define( 'OLI_SL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OLI_SL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OLI_SL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function oli_sl_activate() {
	require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-activator.php';
	Oli_SL_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function oli_sl_deactivate() {
	require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-deactivator.php';
	Oli_SL_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'oli_sl_activate' );
register_deactivation_hook( __FILE__, 'oli_sl_deactivate' );

/**
 * The core plugin class.
 */
require OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-core.php';

/**
 * Begins execution of the plugin.
 */
function oli_sl_run() {
	$plugin = new Oli_SL_Core();
	$plugin->run();
}
oli_sl_run();
