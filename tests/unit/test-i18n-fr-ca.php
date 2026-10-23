<?php
/**
 * Compare fr_CA .po and generated .l10n.php.
 *
 * @package Oli_Store_Locator
 */

class Oli_SL_Test_I18n_Fr_CA {

	/**
	 * Run tests.
	 *
	 * @return array
	 */
	public static function run() {
		return array(
			self::test_l10n_matches_po(),
			self::test_settings_and_all_categories_translated(),
		);
	}

	/**
	 * Assert helper.
	 *
	 * @param bool   $ok      Condition.
	 * @param string $message Message.
	 * @return array
	 */
	private static function assert_true( $ok, $message ) {
		return array(
			'ok'      => (bool) $ok,
			'message' => $message,
		);
	}

	/**
	 * Parse a simple gettext .po file.
	 *
	 * @param string $contents PO contents.
	 * @return array
	 */
	public static function parse_po( $contents ) {
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
				$msgid = self::unquote( substr( $line, 6 ) );
				$field = 'msgid';
				continue;
			}
			if ( 0 === strpos( $line, 'msgstr ' ) ) {
				$msgstr = self::unquote( substr( $line, 7 ) );
				$field  = 'msgstr';
				continue;
			}
			if ( '"' === substr( ltrim( $line ), 0, 1 ) ) {
				$chunk = self::unquote( trim( $line ) );
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
	 * Unquote a PO token.
	 *
	 * @param string $token Token.
	 * @return string
	 */
	private static function unquote( $token ) {
		$token = trim( $token );
		if ( strlen( $token ) >= 2 && '"' === $token[0] && '"' === substr( $token, -1 ) ) {
			$token = substr( $token, 1, -1 );
		}
		return stripcslashes( $token );
	}

	/**
	 * Every translated msgid in the .po is present in the .l10n.php with the same msgstr.
	 *
	 * @return array
	 */
	private static function test_l10n_matches_po() {
		$base = dirname( __DIR__, 2 ) . '/languages/oli-store-locator-fr_CA';
		$po   = self::parse_po( file_get_contents( $base . '.po' ) );
		$php  = include $base . '.l10n.php';
		$php  = isset( $php['messages'] ) && is_array( $php['messages'] ) ? $php['messages'] : array();

		$missing = array();
		$mismatch = array();
		foreach ( $po as $msgid => $msgstr ) {
			if ( '' === $msgid || '' === $msgstr ) {
				continue;
			}
			if ( ! array_key_exists( $msgid, $php ) ) {
				$missing[] = $msgid;
				continue;
			}
			if ( $php[ $msgid ] !== $msgstr ) {
				$mismatch[] = $msgid;
			}
		}

		$ok = empty( $missing ) && empty( $mismatch ) && count( $po ) > 50;
		$msg = 'fr_CA .l10n.php matches the .po';
		if ( $missing ) {
			$msg .= ' (missing ' . count( $missing ) . ': ' . $missing[0] . ')';
		}
		if ( $mismatch ) {
			$msg .= ' (mismatch ' . count( $mismatch ) . ': ' . $mismatch[0] . ')';
		}
		return self::assert_true( $ok, $msg );
	}

	/**
	 * Settings labels and the front “All categories” string are translated.
	 *
	 * @return array
	 */
	private static function test_settings_and_all_categories_translated() {
		$base = dirname( __DIR__, 2 ) . '/languages/oli-store-locator-fr_CA';
		$po   = self::parse_po( file_get_contents( $base . '.po' ) );
		$php  = include $base . '.l10n.php';
		$php  = isset( $php['messages'] ) && is_array( $php['messages'] ) ? $php['messages'] : array();

		$required = array(
			'All categories'           => 'Toutes les catégories',
			'Settings'                 => 'Réglages',
			'Store Locator Settings'   => 'Réglages du localisateur',
			'API'                      => 'API',
			'Search'                   => 'Rechercher',
			'Map'                      => 'Carte',
			'User Experience'          => 'Expérience utilisateur',
			'Markers'                  => 'Marqueurs',
			'Store Editor'             => 'Éditeur de magasin',
			'Local Pages'              => 'Pages locales',
			'Labels'                   => 'Libellés',
			'Tools'                    => 'Outils',
			'Design'                   => 'Apparence',
			'Import / Export'          => 'Import / Export',
			'Google Sheets'            => 'Google Sheets',
			'Use my location'          => 'Utiliser ma position',
			'Sync Now'                 => 'Synchroniser maintenant',
			'Columns detected. The sheet CSV is valid.' => 'Colonnes détectées. Le CSV de la feuille est valide.',
			'Google Maps could not load. Check the API key. The map is using OpenStreetMap instead.' => 'Google Maps n’a pas pu se charger. Vérifiez la clé API. La carte utilise OpenStreetMap à la place.',
		);

		$ok = true;
		foreach ( $required as $msgid => $expected ) {
			$ok = $ok
				&& isset( $po[ $msgid ], $php[ $msgid ] )
				&& $expected === $po[ $msgid ]
				&& $expected === $php[ $msgid ];
		}

		return self::assert_true( $ok, 'Settings page strings and “All categories” are translated in fr_CA' );
	}
}
