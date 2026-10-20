<?php
/**
 * Test bootstrap (no WordPress required).
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/includes/class-oli-sl-csv-parser.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-sl-sync-diff.php';
