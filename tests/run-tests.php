<?php
/**
 * Lightweight test runner.
 *
 * @package Oli_Store_Locator
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/unit/test-csv-parser.php';
require_once __DIR__ . '/unit/test-sync-diff.php';
require_once __DIR__ . '/unit/test-geocode-security.php';
require_once __DIR__ . '/unit/test-upload-and-sheets.php';
require_once __DIR__ . '/unit/test-sync-lock-and-log.php';

$results = array_merge(
	Oli_SL_Test_CSV_Parser::run(),
	Oli_SL_Test_Sync_Diff::run(),
	Oli_SL_Test_Geocode_Security::run(),
	Oli_SL_Test_Upload_And_Sheets::run(),
	Oli_SL_Test_Sync_Lock_And_Log::run()
);

$failed = 0;
foreach ( $results as $result ) {
	$prefix = $result['ok'] ? 'PASS' : 'FAIL';
	echo $prefix . '  ' . $result['message'] . PHP_EOL;
	if ( ! $result['ok'] ) {
		++$failed;
	}
}

echo PHP_EOL . ( count( $results ) - $failed ) . '/' . count( $results ) . ' tests passed' . PHP_EOL;
exit( $failed > 0 ? 1 : 0 );
