<?php
/**
 * Build WordPress 6.5+ .l10n.php files from .po (same job as `wp i18n make-php`).
 *
 * Usage: php bin/make-php.php [locale]
 *
 * @package Oli_Store_Locator
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

$locale = isset( $argv[1] ) ? $argv[1] : '';
if ( '' === $locale ) {
	fwrite( STDERR, "Usage: php bin/make-php.php fr_CA\n" );
	exit( 1 );
}

$root     = dirname( __DIR__ );
$po_path  = $root . '/languages/oli-store-locator-' . $locale . '.po';
$php_path = $root . '/languages/oli-store-locator-' . $locale . '.l10n.php';

if ( ! is_readable( $po_path ) ) {
	fwrite( STDERR, "Missing $po_path\n" );
	exit( 1 );
}

/**
 * Parse a simple gettext .po file into msgid => msgstr.
 *
 * @param string $contents PO contents.
 * @return array
 */
function oli_sl_parse_po( $contents ) {
	$messages = array();
	$msgid    = null;
	$msgstr   = null;
	$field    = null;

	foreach ( preg_split( "/\r\n|\n|\r/", $contents ) as $line ) {
		if ( '' === trim( $line ) ) {
			if ( null !== $msgid ) {
				$messages[ $msgid ] = (string) $msgstr;
			}
			$msgid  = null;
			$msgstr = null;
			$field  = null;
			continue;
		}
		if ( 0 === strpos( $line, 'msgid ' ) ) {
			$msgid = oli_sl_po_unquote( substr( $line, 6 ) );
			$field = 'msgid';
			continue;
		}
		if ( 0 === strpos( $line, 'msgstr ' ) ) {
			$msgstr = oli_sl_po_unquote( substr( $line, 7 ) );
			$field  = 'msgstr';
			continue;
		}
		if ( '"' === substr( ltrim( $line ), 0, 1 ) ) {
			$chunk = oli_sl_po_unquote( trim( $line ) );
			if ( 'msgid' === $field ) {
				$msgid .= $chunk;
			} elseif ( 'msgstr' === $field ) {
				$msgstr .= $chunk;
			}
		}
	}

	if ( null !== $msgid ) {
		$messages[ $msgid ] = (string) $msgstr;
	}

	return $messages;
}

/**
 * Unquote a PO string token.
 *
 * @param string $token Token.
 * @return string
 */
function oli_sl_po_unquote( $token ) {
	$token = trim( $token );
	if ( strlen( $token ) >= 2 && '"' === $token[0] && '"' === substr( $token, -1 ) ) {
		$token = substr( $token, 1, -1 );
	}
	return stripcslashes( $token );
}

/**
 * Export a PHP string.
 *
 * @param string $value Value.
 * @return string
 */
function oli_sl_php_string( $value ) {
	return "'" . str_replace( array( '\\', "'" ), array( '\\\\', "\\'" ), $value ) . "'";
}

$parsed   = oli_sl_parse_po( file_get_contents( $po_path ) );
$header   = isset( $parsed[''] ) ? $parsed[''] : '';
$plural   = 'nplurals=2; plural=(n > 1);';
if ( preg_match( '/Plural-Forms:\s*(.+)/', $header, $match ) ) {
	$plural = trim( $match[1] );
}

$lines   = array();
$lines[] = '<?php';
$lines[] = '/**';
$lines[] = ' * ' . $locale . ' translations generated from the .po (wp i18n make-php).';
$lines[] = ' *';
$lines[] = ' * @package Oli_Store_Locator';
$lines[] = ' */';
$lines[] = '';
$lines[] = 'return array(';
$lines[] = "\t'messages' => array(";
$lines[] = "\t\t'' => array(";
$lines[] = "\t\t\t'plural-forms' => " . oli_sl_php_string( $plural ) . ',';
$lines[] = "\t\t),";

foreach ( $parsed as $msgid => $msgstr ) {
	if ( '' === $msgid || '' === $msgstr ) {
		continue;
	}
	$lines[] = "\t\t" . oli_sl_php_string( $msgid ) . ' => ' . oli_sl_php_string( $msgstr ) . ',';
}

$lines[] = "\t),";
$lines[] = ');';
$lines[] = '';

// This CLI helper is excluded from the distribution ZIP (.gitattributes export-ignore).
// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.FilePutContents -- local .l10n.php compile, not a runtime write.
$written = file_put_contents( $php_path, implode( "\n", $lines ) );
if ( false === $written ) {
	fwrite( STDERR, "Failed\n" );
	exit( 1 );
}
fwrite( STDOUT, 'Wrote ' . $php_path . "\n" );
exit( 0 );
