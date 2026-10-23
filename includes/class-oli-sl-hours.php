<?php
/**
 * Opening hours helpers.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Opening hours helpers.
 */
class Oli_SL_Hours {

	/**
	 * Structured hours for a store.
	 *
	 * @param int $store_id Store ID.
	 * @return array
	 */
	public static function structured( $store_id ) {
		$struct = get_post_meta( $store_id, '_oli_sl_hours_structured', true );
		return is_array( $struct ) ? $struct : array();
	}

	/**
	 * Whether the store is open at a given timestamp.
	 *
	 * @param int $store_id Store ID.
	 * @param int $when     Unix timestamp. Default now.
	 * @return string|null 'open', 'closed', or null when unknown.
	 */
	public static function status( $store_id, $when = 0 ) {
		$struct = self::structured( $store_id );
		if ( empty( $struct ) ) {
			return null;
		}

		$when = $when ? (int) $when : time();
		$day  = strtolower( wp_date( 'l', $when ) );
		$map  = array(
			'monday'    => 'monday',
			'tuesday'   => 'tuesday',
			'wednesday' => 'wednesday',
			'thursday'  => 'thursday',
			'friday'    => 'friday',
			'saturday'  => 'saturday',
			'sunday'    => 'sunday',
		);

		$key = isset( $map[ $day ] ) ? $map[ $day ] : '';
		if ( '' === $key || empty( $struct[ $key ] ) ) {
			return null;
		}

		$row = $struct[ $key ];
		if ( ! empty( $row['closed'] ) && '0' !== (string) $row['closed'] ) {
			return 'closed';
		}

		$open  = isset( $row['open'] ) ? self::minutes( $row['open'] ) : null;
		$close = isset( $row['close'] ) ? self::minutes( $row['close'] ) : null;
		if ( null === $open || null === $close ) {
			return null;
		}

		$now = ( (int) wp_date( 'G', $when ) * 60 ) + (int) wp_date( 'i', $when );

		if ( $close < $open ) {
			return ( $now >= $open || $now <= $close ) ? 'open' : 'closed';
		}

		return ( $now >= $open && $now <= $close ) ? 'open' : 'closed';
	}

	/**
	 * Convert HH:MM to minutes past midnight.
	 *
	 * @param string $value Time.
	 * @return int|null
	 */
	public static function minutes( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return null;
		}

		if ( ! preg_match( '/^(\d{1,2}):(\d{2})/', $value, $matches ) ) {
			return null;
		}

		$hour = (int) $matches[1];
		$min  = (int) $matches[2];
		if ( $hour > 23 || $min > 59 ) {
			return null;
		}

		return ( $hour * 60 ) + $min;
	}

	/**
	 * Format structured hours for display.
	 *
	 * @param int  $store_id      Store ID.
	 * @param bool $hide_closed   Hide closed days.
	 * @return string
	 */
	public static function format_display( $store_id, $hide_closed = false ) {
		$struct = self::structured( $store_id );
		if ( empty( $struct ) ) {
			return (string) oli_sl_get_store_meta( $store_id, 'hours' );
		}

		$lines = array();
		foreach ( oli_sl_weekdays() as $day => $label ) {
			if ( empty( $struct[ $day ] ) ) {
				continue;
			}
			$row    = $struct[ $day ];
			$closed = ! empty( $row['closed'] ) && '0' !== (string) $row['closed'];
			if ( $closed ) {
				if ( $hide_closed ) {
					continue;
				}
				/* translators: %s: weekday name */
				$lines[] = sprintf( __( '%s: Closed', 'oli-store-locator' ), $label );
				continue;
			}
			/* translators: 1: weekday, 2: open time, 3: close time */
			$lines[] = sprintf( __( '%1$s: %2$s – %3$s', 'oli-store-locator' ), $label, $row['open'], $row['close'] );
		}

		return implode( "\n", $lines );
	}
}
