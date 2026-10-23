<?php
/**
 * Test bootstrap (no WordPress required).
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'OLI_SL_VERSION' ) ) {
	define( 'OLI_SL_VERSION', '0.2.2' );
}

require_once __DIR__ . '/wp-stubs.php';
require_once dirname( __DIR__ ) . '/includes/oli-sl-functions.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-search.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-csv-parser.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-sync-diff.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-sync-lock.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-sync-log.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-hours.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-geocoding.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-geocoding-nominatim.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-rest-api.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-google-sheets-sync.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-csv-handler.php';
