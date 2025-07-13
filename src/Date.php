<?php
/**
 * Core Date Utility Class - CLEANED UP
 *
 * Essential date/time handling with UTC awareness and WordPress integration.
 * Focused on the most commonly needed operations.
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

class Date {

	/**
	 * Parse date string safely
	 *
	 * @param string      $date     Date string to parse
	 * @param string|null $timezone Timezone (defaults to WordPress timezone)
	 *
	 * @return Carbon|null Carbon instance or null on failure
	 */
	public static function parse( string $date, ?string $timezone = null ): ?Carbon {
		try {
			$tz = $timezone ?: wp_timezone();

			return Carbon::parse( $date, $tz );
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Get current UTC time
	 *
	 * @param string $format Optional datetime format
	 *
	 * @return string Formatted UTC datetime
	 */
	public static function now_utc( string $format = 'Y-m-d H:i:s' ): string {
		return Carbon::now( 'UTC' )->format( $format );
	}

	/**
	 * Get current local time using WordPress timezone
	 *
	 * @param string $format Optional datetime format
	 *
	 * @return string Formatted local datetime
	 */
	public static function now_local( string $format = 'Y-m-d H:i:s' ): string {
		return Carbon::now( wp_timezone() )->format( $format );
	}

	/**
	 * Convert local time to UTC (for database storage)
	 *
	 * @param string $local_time Local datetime in site's timezone
	 * @param string $format     Optional datetime format
	 *
	 * @return string Formatted UTC datetime
	 */
	public static function to_utc( string $local_time, string $format = 'Y-m-d H:i:s' ): string {
		return Carbon::parse( $local_time, wp_timezone() )
		             ->setTimezone( 'UTC' )
		             ->format( $format );
	}

	/**
	 * Convert UTC to local time (for display)
	 *
	 * @param string $utc_time UTC datetime
	 * @param string $format   Optional datetime format
	 *
	 * @return string Formatted local datetime
	 */
	public static function to_local( string $utc_time, string $format = 'Y-m-d H:i:s' ): string {
		return Carbon::parse( $utc_time, 'UTC' )
		             ->setTimezone( wp_timezone() )
		             ->format( $format );
	}

	/**
	 * Add time to a UTC date
	 *
	 * @param string $utc_date UTC datetime
	 * @param int    $amount   Amount to add
	 * @param string $unit     Unit (years, months, weeks, days, hours, minutes, seconds)
	 * @param string $format   Optional datetime format
	 *
	 * @return string New UTC datetime
	 */
	public static function add( string $utc_date, int $amount, string $unit, string $format = 'Y-m-d H:i:s' ): string {
		return Carbon::parse( $utc_date, 'UTC' )
		             ->add( $unit, $amount )
		             ->format( $format );
	}

	/**
	 * Subtract time from a UTC date
	 *
	 * @param string $utc_date UTC datetime
	 * @param int    $amount   Amount to subtract
	 * @param string $unit     Unit (years, months, weeks, days, hours, minutes, seconds)
	 * @param string $format   Optional datetime format
	 *
	 * @return string New UTC datetime
	 */
	public static function subtract( string $utc_date, int $amount, string $unit, string $format = 'Y-m-d H:i:s' ): string {
		return Carbon::parse( $utc_date, 'UTC' )
		             ->sub( $unit, $amount )
		             ->format( $format );
	}

	/**
	 * Get difference between two dates
	 *
	 * @param string $date1 First UTC datetime
	 * @param string $date2 Second UTC datetime
	 * @param string $unit  Unit (days, hours, minutes, etc.)
	 *
	 * @return float Difference in specified unit
	 */
	public static function diff( string $date1, string $date2, string $unit = 'days' ): float {
		$first  = Carbon::parse( $date1, 'UTC' );
		$second = Carbon::parse( $date2, 'UTC' );

		$method = 'diffIn' . ucfirst( $unit );

		return method_exists( $first, $method ) ? $first->$method( $second ) : 0;
	}

	/**
	 * Get age in years from birthdate
	 *
	 * @param string $birth_date_utc UTC birth datetime
	 *
	 * @return int Age in years
	 */
	public static function get_age( string $birth_date_utc ): int {
		return Carbon::parse( $birth_date_utc, 'UTC' )->age;
	}

	/**
	 * Get age in days from birthdate
	 *
	 * @param string $birth_date_utc UTC birth datetime
	 *
	 * @return int Age in days
	 */
	public static function get_age_in_days( string $birth_date_utc ): int {
		return (int) Carbon::parse( $birth_date_utc, 'UTC' )->diffInDays( Carbon::now( 'UTC' ) );
	}

	/**
	 * Get next business day from given date
	 *
	 * @param string $utc_date      UTC datetime to start from
	 * @param array  $business_days Business days (1=Mon, 7=Sun)
	 * @param string $format        Optional datetime format
	 *
	 * @return string Next business day in UTC
	 */
	public static function next_business_day(
		string $utc_date, array $business_days = [
		1,
		2,
		3,
		4,
		5
	], string $format = 'Y-m-d H:i:s'
	): string {
		$date = Carbon::parse( $utc_date, 'UTC' )->setTimezone( wp_timezone() );

		do {
			$date->addDay();
			$dayOfWeek = $date->dayOfWeek === 0 ? 7 : $date->dayOfWeek; // Convert Sunday
		} while ( ! in_array( $dayOfWeek, $business_days, true ) );

		return $date->setTimezone( 'UTC' )->format( $format );
	}

	/**
	 * Check if date is valid
	 *
	 * @param string $date Date string to validate
	 *
	 * @return bool True if valid
	 */
	public static function is_valid( string $date ): bool {
		return self::parse( $date ) !== null;
	}

	/**
	 * Check if date is empty or zero
	 *
	 * @param string|null $date Date string
	 *
	 * @return bool True if empty
	 */
	public static function is_empty( ?string $date ): bool {
		return empty( $date ) || $date === '0000-00-00 00:00:00' || ! self::is_valid( $date );
	}

	/**
	 * Check if date is in future
	 *
	 * @param string $utc_date UTC datetime
	 *
	 * @return bool True if date is in future
	 */
	public static function is_future( string $utc_date ): bool {
		return Carbon::parse( $utc_date, 'UTC' )->isFuture();
	}

	/**
	 * Check if date is in past
	 *
	 * @param string $utc_date UTC datetime
	 *
	 * @return bool True if date is in past
	 */
	public static function is_past( string $utc_date ): bool {
		return Carbon::parse( $utc_date, 'UTC' )->isPast();
	}

	/**
	 * Check if date is a weekend
	 *
	 * @param string|null $utc_date UTC datetime (default: now)
	 *
	 * @return bool True if weekend
	 */
	public static function is_weekend( ?string $utc_date = null ): bool {
		$date = $utc_date ? Carbon::parse( $utc_date, 'UTC' ) : Carbon::now( 'UTC' );

		return $date->setTimezone( wp_timezone() )->isWeekend();
	}

	/**
	 * Check if date is a weekday
	 *
	 * @param string|null $utc_date UTC datetime (default: now)
	 *
	 * @return bool True if weekday
	 */
	public static function is_weekday( ?string $utc_date = null ): bool {
		return ! self::is_weekend( $utc_date );
	}

	/**
	 * Get the weekday name
	 *
	 * @param string|null $utc_date UTC datetime (default: now)
	 *
	 * @return string Full weekday name
	 */
	public static function get_weekday( ?string $utc_date = null ): string {
		$date = $utc_date ? Carbon::parse( $utc_date, 'UTC' ) : Carbon::now( 'UTC' );

		return $date->setTimezone( wp_timezone() )->format( 'l' );
	}

	/**
	 * Get the month name
	 *
	 * @param string|null $utc_date UTC datetime (default: now)
	 *
	 * @return string Full month name
	 */
	public static function get_month( ?string $utc_date = null ): string {
		$date = $utc_date ? Carbon::parse( $utc_date, 'UTC' ) : Carbon::now( 'UTC' );

		return $date->setTimezone( wp_timezone() )->format( 'F' );
	}

	/**
	 * Get the quarter
	 *
	 * @param string|null $utc_date UTC datetime (default: now)
	 *
	 * @return string Quarter (Q1, Q2, Q3, or Q4)
	 */
	public static function get_quarter( ?string $utc_date = null ): string {
		$date = $utc_date ? Carbon::parse( $utc_date, 'UTC' ) : Carbon::now( 'UTC' );

		return 'Q' . $date->setTimezone( wp_timezone() )->quarter;
	}

	/**
	 * Convert various date formats to timestamp
	 *
	 * @param mixed $date Date to convert (string, Carbon, timestamp)
	 *
	 * @return int|null Unix timestamp or null on failure
	 */
	public static function to_timestamp( $date ): ?int {
		try {
			if ( is_numeric( $date ) ) {
				return (int) $date;
			}

			if ( $date instanceof Carbon ) {
				return $date->timestamp;
			}

			if ( is_string( $date ) ) {
				$parsed = self::parse( $date );

				return $parsed ? $parsed->timestamp : null;
			}

			return null;
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Convert UTC date to local timezone timestamp
	 *
	 * @param string $utc_date UTC datetime
	 *
	 * @return int|null Local timezone timestamp or null on failure
	 */
	public static function to_local_timestamp( string $utc_date ): ?int {
		try {
			return Carbon::parse( $utc_date, 'UTC' )
			             ->setTimezone( wp_timezone() )
				->timestamp;
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Simple expiration check with optional grace period.
	 *
	 * @param string   $expiry_utc  UTC expiry datetime.
	 * @param int|null $grace_hours Optional grace period in hours.
	 *
	 * @return bool True if expired beyond grace period.
	 */
	public static function is_expired( string $expiry_utc, ?int $grace_hours = null ): bool {
		$expiry_timestamp = strtotime( $expiry_utc );
		if ( false === $expiry_timestamp ) {
			return true;
		}

		if ( $grace_hours && $grace_hours > 0 ) {
			$expiry_timestamp += ( $grace_hours * 3600 ); // Convert hours to seconds
		}

		return time() > $expiry_timestamp;
	}

	/**
	 * Calculate expiration date from duration.
	 *
	 * @param int         $duration_seconds Duration in seconds.
	 * @param string|null $from_date        Optional. UTC date to calculate from.
	 *
	 * @return string|null UTC datetime string or null if invalid.
	 */
	public static function calculate_expiration( int $duration_seconds, ?string $from_date = null ): ?string {
		if ( $duration_seconds <= 0 ) {
			return null;
		}

		$from_timestamp = empty( $from_date ) ? time() : strtotime( $from_date );
		if ( false === $from_timestamp ) {
			return null;
		}

		return gmdate( 'Y-m-d H:i:s', $from_timestamp + $duration_seconds );
	}

}