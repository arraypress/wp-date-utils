<?php
/**
 * Date Validation Utility Class
 *
 * Provides comprehensive date validation methods for checking dates, formats, and components.
 * Enhanced with WordPress integration and real-world validation scenarios.
 *
 * @package     ArrayPress\DateUtils
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\DateUtils;

use Carbon\Carbon;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

class Validate {

	/**
	 * Check if date matches specific format
	 *
	 * @param string $date   Date string to check
	 * @param string $format Expected date format
	 *
	 * @return bool True if matches format
	 */
	public static function format( string $date, string $format ): bool {
		try {
			$parsed = Carbon::createFromFormat( $format, $date );

			return $parsed && $parsed->format( $format ) === $date;
		} catch ( Exception $ex ) {
			return false;
		}
	}

	/**
	 * Check if a date range is valid
	 *
	 * @param string $start_date Start date
	 * @param string $end_date   End date
	 *
	 * @return bool True if valid range
	 */
	public static function is_valid_range( string $start_date, string $end_date ): bool {
		try {
			$start = Carbon::parse( $start_date );
			$end   = Carbon::parse( $end_date );

			return $start->lte( $end );
		} catch ( Exception $ex ) {
			return false;
		}
	}

	/**
	 * Check if date is within range
	 *
	 * @param string $date_utc  UTC datetime to check
	 * @param string $start_utc UTC start datetime
	 * @param string $end_utc   UTC end datetime
	 * @param bool   $inclusive Whether to include boundary dates
	 *
	 * @return bool True if within range
	 */
	public static function range( string $date_utc, string $start_utc, string $end_utc, bool $inclusive = true ): bool {
		try {
			$check     = Carbon::parse( $date_utc, 'UTC' );
			$startDate = Carbon::parse( $start_utc, 'UTC' );
			$endDate   = Carbon::parse( $end_utc, 'UTC' );

			return $check->between( $startDate, $endDate, $inclusive );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if date is today (in site timezone)
	 *
	 * @param string $utc_date UTC datetime
	 *
	 * @return bool True if date is today
	 */
	public static function is_today( string $utc_date ): bool {
		try {
			$date = Carbon::parse( $utc_date, 'UTC' )->setTimezone( wp_timezone() );
			$now  = Carbon::now( wp_timezone() );

			return $date->isSameDay( $now );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if date is a business day
	 *
	 * @param string $utc_date      UTC datetime
	 * @param array  $business_days Business days (1=Mon, 7=Sun)
	 *
	 * @return bool True if business day
	 */
	public static function business_day( string $utc_date, array $business_days = [ 1, 2, 3, 4, 5 ] ): bool {
		try {
			$date      = Carbon::parse( $utc_date, 'UTC' )->setTimezone( wp_timezone() );
			$dayOfWeek = $date->dayOfWeek === 0 ? 7 : $date->dayOfWeek; // Convert Sunday

			return in_array( $dayOfWeek, $business_days, true );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if time falls within business hours
	 *
	 * @param string $utc_date   UTC datetime to check
	 * @param string $start_time Start time in H:i format (e.g., '09:00')
	 * @param string $end_time   End time in H:i format (e.g., '17:00')
	 *
	 * @return bool True if within business hours
	 */
	public static function is_business_hours( string $utc_date, string $start_time = '09:00', string $end_time = '17:00' ): bool {
		try {
			$date = Carbon::parse( $utc_date, 'UTC' )->setTimezone( wp_timezone() );
			$time = $date->format( 'H:i' );

			return $time >= $start_time && $time <= $end_time;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Validate month number
	 *
	 * @param int $month Month number
	 *
	 * @return bool True if valid month
	 */
	public static function is_valid_month( int $month ): bool {
		return $month >= 1 && $month <= 12;
	}

	/**
	 * Validate day of month
	 *
	 * @param int      $day   Day of month
	 * @param int      $month Month (1-12)
	 * @param int|null $year  Year (for leap year checking)
	 *
	 * @return bool True if valid day
	 */
	public static function is_valid_day( int $day, int $month, ?int $year = null ): bool {
		if ( ! self::is_valid_month( $month ) ) {
			return false;
		}

		$year = $year ?? (int) date( 'Y' );

		return checkdate( $month, $day, $year );
	}

	/**
	 * Validate hour in 24-hour format
	 *
	 * @param int $hour Hour to validate (0-23)
	 *
	 * @return bool True if valid hour
	 */
	public static function is_valid_hour( int $hour ): bool {
		return $hour >= 0 && $hour <= 23;
	}

	/**
	 * Validate minute
	 *
	 * @param int $minute Minute to validate (0-59)
	 *
	 * @return bool True if valid minute
	 */
	public static function is_valid_minute( int $minute ): bool {
		return $minute >= 0 && $minute <= 59;
	}

	/**
	 * Validate second
	 *
	 * @param int $second Second to validate (0-59)
	 *
	 * @return bool True if valid second
	 */
	public static function is_valid_second( int $second ): bool {
		return $second >= 0 && $second <= 59;
	}

	/**
	 * Check if timezone identifier is valid
	 *
	 * @param string $timezone Timezone identifier
	 *
	 * @return bool True if valid timezone
	 */
	public static function is_valid_timezone( string $timezone ): bool {
		try {
			new DateTimeZone( $timezone );

			return true;
		} catch ( Exception $ex ) {
			return false;
		}
	}

	/**
	 * Check if two dates are in the same period
	 *
	 * @param string $date1_utc First UTC datetime
	 * @param string $date2_utc Second UTC datetime
	 * @param string $period    Period type (day, week, month, year, quarter)
	 *
	 * @return bool True if dates are in same period
	 * @throws InvalidArgumentException If period is invalid
	 */
	public static function is_same_period( string $date1_utc, string $date2_utc, string $period ): bool {
		try {
			$date1 = Carbon::parse( $date1_utc, 'UTC' );
			$date2 = Carbon::parse( $date2_utc, 'UTC' );

			switch ( $period ) {
				case 'day':
					return $date1->isSameDay( $date2 );
				case 'week':
					return $date1->isSameWeek( $date2 );
				case 'month':
					return $date1->isSameMonth( $date2 );
				case 'year':
					return $date1->isSameYear( $date2 );
				case 'quarter':
					return $date1->isSameQuarter( $date2 );
				default:
					throw new InvalidArgumentException( "Invalid period type: {$period}" );
			}
		} catch ( Exception $e ) {
			throw new InvalidArgumentException( "Invalid date provided: " . $e->getMessage() );
		}
	}

	/**
	 * Check if a date is between two others (alias for range method)
	 *
	 * @param string $date_check UTC datetime to check
	 * @param string $start      UTC start datetime
	 * @param string $end        UTC end datetime
	 * @param bool   $inclusive  Whether to include boundary dates
	 *
	 * @return bool True if date is between start and end
	 */
	public static function is_between( string $date_check, string $start, string $end, bool $inclusive = true ): bool {
		return self::range( $date_check, $start, $end, $inclusive );
	}

	/**
	 * Validate age requirement
	 *
	 * @param string $birth_date_utc Birth date in UTC
	 * @param int    $min_age        Minimum age in years
	 *
	 * @return bool True if age requirement is met
	 */
	public static function meets_age_requirement( string $birth_date_utc, int $min_age ): bool {
		try {
			$birthDate = Carbon::parse( $birth_date_utc, 'UTC' );
			$now       = Carbon::now( 'UTC' );

			return $birthDate->diffInYears( $now ) >= $min_age;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Validate user date input against site constraints
	 *
	 * @param string $date        User input date
	 * @param array  $constraints Validation constraints
	 *
	 * @return bool True if valid
	 */
	public static function user_date( string $date, array $constraints = [] ): bool {
		if ( ! Date::is_valid( $date ) ) {
			return false;
		}

		try {
			$parsed = Carbon::parse( $date, wp_timezone() );

			// Check minimum date
			if ( isset( $constraints['min_date'] ) ) {
				$min = Carbon::parse( $constraints['min_date'], wp_timezone() );
				if ( $parsed->lt( $min ) ) {
					return false;
				}
			}

			// Check maximum date
			if ( isset( $constraints['max_date'] ) ) {
				$max = Carbon::parse( $constraints['max_date'], wp_timezone() );
				if ( $parsed->gt( $max ) ) {
					return false;
				}
			}

			// Check business days only
			if ( isset( $constraints['business_days_only'] ) && $constraints['business_days_only'] ) {
				if ( ! self::business_day( $parsed->utc()->toISOString() ) ) {
					return false;
				}
			}

			// Check against excluded dates
			if ( isset( $constraints['excluded_dates'] ) && is_array( $constraints['excluded_dates'] ) ) {
				$dateString = $parsed->format( 'Y-m-d' );
				if ( in_array( $dateString, $constraints['excluded_dates'] ) ) {
					return false;
				}
			}

			// Check minimum age requirement
			if ( isset( $constraints['min_age_years'] ) ) {
				$age = $parsed->diffInYears( Carbon::now( wp_timezone() ) );
				if ( $age < $constraints['min_age_years'] ) {
					return false;
				}
			}

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

}