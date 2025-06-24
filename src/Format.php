<?php
/**
 * Date Formatting Utility Class
 *
 * Provides essential date formatting methods for human-readable outputs.
 * Handles WordPress formats, human differences, and display formatting.
 *
 * @package     ArrayPress\Utils
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\DateUtils;

use Carbon\Carbon;
use Exception;

class Format {

	/**
	 * Format a UTC datetime according to WordPress settings
	 *
	 * @param string|null $utc_time UTC datetime
	 * @param string      $type     Format type (date, time, datetime)
	 * @param bool        $with_tz  Include timezone in output
	 *
	 * @return string Formatted datetime string
	 */
	public static function wp( ?string $utc_time, string $type = 'datetime', bool $with_tz = false ): string {
		if ( Date::is_empty( $utc_time ) ) {
			return '—';
		}

		try {
			$date = Carbon::parse( $utc_time, 'UTC' )->setTimezone( wp_timezone() );

			switch ( $type ) {
				case 'date':
					$format = get_option( 'date_format' );
					break;
				case 'time':
					$format = get_option( 'time_format' );
					break;
				default:
					$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			}

			$formatted = $date->format( $format );

			return $with_tz ? $formatted . ' ' . $date->format( 'T' ) : $formatted;
		} catch ( Exception $e ) {
			return '—';
		}
	}

	/**
	 * Get human-readable time difference
	 *
	 * @param string      $utc_time UTC datetime to compare
	 * @param string|null $from     UTC datetime to compare from (default: now)
	 *
	 * @return string Human-readable difference
	 */
	public static function human( string $utc_time, ?string $from = null ): string {
		try {
			$date    = Carbon::parse( $utc_time, 'UTC' );
			$compare = $from ? Carbon::parse( $from, 'UTC' ) : Carbon::now( 'UTC' );

			return $date->diffForHumans( $compare );
		} catch ( Exception $e ) {
			return '';
		}
	}

	/**
	 * Format duration in human-readable format
	 *
	 * @param int  $seconds Duration in seconds
	 * @param bool $short   Whether to use short format (default: false)
	 *
	 * @return string Formatted duration string
	 */
	public static function duration( int $seconds, bool $short = false ): string {
		if ( $seconds <= 0 ) {
			return '0' . ( $short ? 's' : ' ' . __( 'seconds', 'arraypress' ) );
		}

		$units = [
			'year'   => YEAR_IN_SECONDS,      // 31536000
			'month'  => MONTH_IN_SECONDS,     // 2592000
			'week'   => WEEK_IN_SECONDS,      // 604800
			'day'    => DAY_IN_SECONDS,       // 86400
			'hour'   => HOUR_IN_SECONDS,      // 3600
			'minute' => MINUTE_IN_SECONDS,    // 60
			'second' => 1
		];

		foreach ( $units as $unit => $value ) {
			if ( $seconds >= $value ) {
				$count = floor( $seconds / $value );

				return $short
					? $count . substr( $unit, 0, 1 )
					: sprintf( _n( '%d %s', '%d %ss', $count, 'arraypress' ), $count, $unit );
			}
		}

		return '0' . ( $short ? 's' : ' ' . __( 'seconds', 'arraypress' ) );
	}

	/**
	 * Format duration in simple format (e.g., "1h 30m", "2d 3h")
	 *
	 * @param int $seconds   Duration in seconds
	 * @param int $max_units Maximum number of units to display (default: 2)
	 *
	 * @return string Simple formatted duration string
	 */
	public static function duration_simple( int $seconds, int $max_units = 2 ): string {
		if ( $seconds <= 0 ) {
			return '0s';
		}

		$units = [
			'y' => YEAR_IN_SECONDS,
			'd' => DAY_IN_SECONDS,
			'h' => HOUR_IN_SECONDS,
			'm' => MINUTE_IN_SECONDS,
			's' => 1
		];

		$result    = [];
		$remaining = $seconds;

		foreach ( $units as $suffix => $value ) {
			if ( $remaining >= $value && count( $result ) < $max_units ) {
				$count     = floor( $remaining / $value );
				$result[]  = $count . $suffix;
				$remaining %= $value;
			}
		}

		return empty( $result ) ? '0s' : implode( ' ', $result );
	}

	/**
	 * Get relative time format
	 *
	 * Returns simple relative time strings for common periods.
	 * Example: "today", "tomorrow", "yesterday"
	 *
	 * @param string $utc_date UTC datetime to format
	 *
	 * @return string Human-readable relative time
	 */
	public static function relative( string $utc_date ): string {
		try {
			$date = Carbon::parse( $utc_date, 'UTC' )->setTimezone( wp_timezone() );

			if ( $date->isToday() ) {
				return __( 'today', 'arraypress' );
			}
			if ( $date->isTomorrow() ) {
				return __( 'tomorrow', 'arraypress' );
			}
			if ( $date->isYesterday() ) {
				return __( 'yesterday', 'arraypress' );
			}

			return self::human( $utc_date );
		} catch ( Exception $e ) {
			return '';
		}
	}

	/**
	 * Get expiration status or time until expiration
	 *
	 * Returns a human-readable string indicating if/when a date expires.
	 * Examples: "expired 2 days ago", "expires in 4 days", "expired", "never expires"
	 *
	 * @param string $utc_time UTC datetime of expiration
	 * @param bool   $detailed Whether to include detailed time difference
	 *
	 * @return string Expiration status
	 */
	public static function expiration_status( string $utc_time, bool $detailed = true ): string {
		try {
			$date = Carbon::parse( $utc_time, 'UTC' );

			if ( $date->isPast() ) {
				return $detailed
					? sprintf( __( 'expired %s', 'arraypress' ), self::human( $utc_time ) )
					: __( 'expired', 'arraypress' );
			}

			return $detailed
				? sprintf( __( 'expires %s', 'arraypress' ), self::human( $utc_time ) )
				: __( 'active', 'arraypress' );
		} catch ( Exception $e ) {
			return __( 'unknown', 'arraypress' );
		}
	}

	/**
	 * Format for WordPress admin display
	 *
	 * @param string $utc_date UTC datetime
	 * @param string $format   Optional format (default: WordPress datetime format)
	 *
	 * @return string Formatted date
	 */
	public static function admin( string $utc_date, string $format = '' ): string {
		if ( empty( $format ) ) {
			return self::wp( $utc_date, 'datetime' );
		}

		// Use custom format
		try {
			$date = Carbon::parse( $utc_date, 'UTC' )->setTimezone( wp_timezone() );

			return $date->format( $format );
		} catch ( Exception $e ) {
			return '—';
		}
	}

	/**
	 * Get relative time with "ago" suffix for admin display
	 *
	 * @param string $date Any date format
	 *
	 * @return string Relative time with "ago" or empty string
	 */
	public static function admin_relative( string $date ): string {
		if ( Date::is_empty( $date ) ) {
			return '';
		}

		$parsed = Date::parse( $date );
		if ( ! $parsed ) {
			return '';
		}

		$utc_date = $parsed->utc()->format( 'Y-m-d H:i:s' );

		return $utc_date ? self::human( $utc_date ) . ' ago' : '';
	}

	/**
	 * Format date using WordPress i18n functions
	 *
	 * @param mixed  $date   Date to format
	 * @param string $format WordPress date format string
	 *
	 * @return string Formatted date string
	 */
	public static function i18n( $date, string $format ): string {
		$timestamp = Date::to_timestamp( $date );

		return $timestamp ? date_i18n( $format, $timestamp ) : '';
	}

}