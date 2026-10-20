<?php
/**
 * Minimal WordPress stubs for unit tests.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Lightweight WP_Error stand-in.
	 */
	class WP_Error {
		/**
		 * Errors.
		 *
		 * @var array
		 */
		public $errors = array();

		/**
		 * Error data.
		 *
		 * @var array
		 */
		public $error_data = array();

		/**
		 * Constructor.
		 *
		 * @param string $code    Code.
		 * @param string $message Message.
		 * @param mixed  $data    Data.
		 */
		public function __construct( $code = '', $message = '', $data = '' ) {
			if ( $code ) {
				$this->errors[ $code ][] = $message;
				if ( '' !== $data && null !== $data ) {
					$this->error_data[ $code ] = $data;
				}
			}
		}

		/**
		 * First error code.
		 *
		 * @return string
		 */
		public function get_error_code() {
			$codes = array_keys( $this->errors );
			return $codes ? $codes[0] : '';
		}

		/**
		 * First error message.
		 *
		 * @return string
		 */
		public function get_error_message() {
			$code = $this->get_error_code();
			return ( $code && isset( $this->errors[ $code ][0] ) ) ? $this->errors[ $code ][0] : '';
		}

		/**
		 * Error data for a code.
		 *
		 * @param string $code Code.
		 * @return mixed
		 */
		public function get_error_data( $code = '' ) {
			if ( '' === $code ) {
				$code = $this->get_error_code();
			}
			return isset( $this->error_data[ $code ] ) ? $this->error_data[ $code ] : null;
		}
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	/**
	 * Whether a value is a WP_Error.
	 *
	 * @param mixed $thing Value.
	 * @return bool
	 */
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Identity gettext.
	 *
	 * @param string $text   Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * Strip tags and trim.
	 *
	 * @param string $str String.
	 * @return string
	 */
	function sanitize_text_field( $str ) {
		return trim( strip_tags( (string) $str ) );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	/**
	 * Identity unslash.
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	function wp_unslash( $value ) {
		return $value;
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	/**
	 * parse_url wrapper.
	 *
	 * @param string $url       URL.
	 * @param int    $component Component.
	 * @return mixed
	 */
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	/**
	 * Get a test transient.
	 *
	 * @param string $key Key.
	 * @return mixed
	 */
	function get_transient( $key ) {
		if ( ! isset( $GLOBALS['oli_sl_test_transients'][ $key ] ) ) {
			return false;
		}
		$entry = $GLOBALS['oli_sl_test_transients'][ $key ];
		if ( $entry['expires'] < time() ) {
			unset( $GLOBALS['oli_sl_test_transients'][ $key ] );
			return false;
		}
		return $entry['value'];
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	/**
	 * Set a test transient.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 * @param int    $ttl   TTL.
	 * @return bool
	 */
	function set_transient( $key, $value, $ttl ) {
		$GLOBALS['oli_sl_test_transients'][ $key ] = array(
			'value'   => $value,
			'expires' => time() + (int) $ttl,
		);
		return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	/**
	 * Delete a test transient.
	 *
	 * @param string $key Key.
	 * @return bool
	 */
	function delete_transient( $key ) {
		unset( $GLOBALS['oli_sl_test_transients'][ $key ] );
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Apply registered test filters.
	 *
	 * @param string $hook  Hook.
	 * @param mixed  $value Value.
	 * @return mixed
	 */
	function apply_filters( $hook, $value ) {
		$args = func_get_args();
		array_shift( $args );
		$value = array_shift( $args );

		if ( empty( $GLOBALS['oli_sl_test_filters'][ $hook ] ) ) {
			return $value;
		}

		foreach ( $GLOBALS['oli_sl_test_filters'][ $hook ] as $callback ) {
			$value = call_user_func_array( $callback, array_merge( array( $value ), $args ) );
		}

		return $value;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * Register a test filter.
	 *
	 * @param string   $hook     Hook.
	 * @param callable $callback Callback.
	 * @param int      $priority Priority.
	 * @param int      $accepted Accepted args.
	 */
	function add_filter( $hook, $callback, $priority = 10, $accepted = 1 ) {
		unset( $priority, $accepted );
		$GLOBALS['oli_sl_test_filters'][ $hook ][] = $callback;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	/**
	 * Capability check from the test harness.
	 *
	 * @param string $cap Capability.
	 * @return bool
	 */
	function current_user_can( $cap ) {
		$caps = isset( $GLOBALS['oli_sl_test_caps'] ) ? $GLOBALS['oli_sl_test_caps'] : array();
		return in_array( $cap, $caps, true );
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	/**
	 * Nonce check from the test harness.
	 *
	 * @param string $nonce  Nonce.
	 * @param string $action Action.
	 * @return bool
	 */
	function wp_verify_nonce( $nonce, $action ) {
		return ! empty( $GLOBALS['oli_sl_test_nonces'][ $action ] ) && $GLOBALS['oli_sl_test_nonces'][ $action ] === $nonce;
	}
}

if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
	/**
	 * Extension check from the filename.
	 *
	 * @param string $file     Path.
	 * @param string $filename Filename.
	 * @param array  $mimes    Mimes.
	 * @return array
	 */
	function wp_check_filetype_and_ext( $file, $filename, $mimes = null ) {
		unset( $file, $mimes );
		return array(
			'ext'             => strtolower( (string) pathinfo( $filename, PATHINFO_EXTENSION ) ),
			'type'            => '',
			'proper_filename' => false,
		);
	}
}

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Get a test option.
	 *
	 * @param string $key      Key.
	 * @param mixed  $fallback Fallback.
	 * @return mixed
	 */
	function get_option( $key, $fallback = false ) {
		$opts = isset( $GLOBALS['oli_sl_test_wp_options'] ) ? $GLOBALS['oli_sl_test_wp_options'] : array();
		return array_key_exists( $key, $opts ) ? $opts[ $key ] : $fallback;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * Update a test option.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 * @param bool   $auto  Autoload.
	 * @return bool
	 */
	function update_option( $key, $value, $auto = true ) {
		unset( $auto );
		$GLOBALS['oli_sl_test_wp_options'][ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Delete a test option.
	 *
	 * @param string $key Key.
	 * @return bool
	 */
	function delete_option( $key ) {
		unset( $GLOBALS['oli_sl_test_wp_options'][ $key ] );
		return true;
	}
}

if ( ! function_exists( 'oli_sl_get_option' ) ) {
	/**
	 * Option reader for tests.
	 *
	 * @param string $key      Key.
	 * @param mixed  $fallback Fallback.
	 * @return mixed
	 */
	function oli_sl_get_option( $key, $fallback = null ) {
		$opts = isset( $GLOBALS['oli_sl_test_options'] ) ? $GLOBALS['oli_sl_test_options'] : array();
		return array_key_exists( $key, $opts ) ? $opts[ $key ] : $fallback;
	}
}

if ( ! function_exists( 'wp_doing_cron' ) ) {
	/**
	 * Cron flag.
	 *
	 * @return bool
	 */
	function wp_doing_cron() {
		return ! empty( $GLOBALS['oli_sl_test_doing_cron'] );
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	/**
	 * Admin flag.
	 *
	 * @return bool
	 */
	function is_admin() {
		return ! empty( $GLOBALS['oli_sl_test_is_admin'] );
	}
}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
	/**
	 * Ajax flag.
	 *
	 * @return bool
	 */
	function wp_doing_ajax() {
		return ! empty( $GLOBALS['oli_sl_test_doing_ajax'] );
	}
}

if ( ! function_exists( 'rest_authorization_required_code' ) ) {
	/**
	 * REST auth status.
	 *
	 * @return int
	 */
	function rest_authorization_required_code() {
		return 401;
	}
}

/**
 * Reset mutable test state.
 */
function oli_sl_test_reset() {
	$GLOBALS['oli_sl_test_transients']  = array();
	$GLOBALS['oli_sl_test_filters']     = array();
	$GLOBALS['oli_sl_test_caps']        = array();
	$GLOBALS['oli_sl_test_nonces']      = array();
	$GLOBALS['oli_sl_test_options']     = array(
		'geocoding_provider'       => 'nominatim',
		'nominatim_rate_limit'     => '1',
		'sync_log_retention_days'  => '30',
	);
	$GLOBALS['oli_sl_test_wp_options']  = array();
	$GLOBALS['oli_sl_test_doing_cron']  = false;
	$GLOBALS['oli_sl_test_is_admin']    = false;
	$GLOBALS['oli_sl_test_doing_ajax']  = false;
	$GLOBALS['oli_sl_test_pre_geocode'] = null;
	$_SERVER['REMOTE_ADDR']             = '203.0.113.10';
}

oli_sl_test_reset();
