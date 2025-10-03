<?php
/**
 * WordPress Simple Date Utils
 *
 * Lightweight date utilities for WordPress without external dependencies.
 * Focused on UTC storage and local display conversion.
 *
 * @package     ArrayPress\DateUtils
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

namespace ArrayPress\DateUtils;

// Exit if accessed directly
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Simple Date utilities for WordPress
 *
 * @since 1.0.0
 */
class Dates {

	/* ========================================================================
	 * CORE DATE OPERATIONS
	 * ======================================================================== */

	/**
	 * Get current UTC datetime
	 *
	 * @param string $format Format string (default MySQL format)
	 *
	 * @return string UTC datetime
	 * @since 1.0.0
	 *
	 */
	public static function now_utc( string $format = 'Y-m-d H:i:s' ): string {
		return gmdate( $format );
	}

	/**
	 * Get current local datetime
	 *
	 * @param string $format Format string (default MySQL format)
	 *
	 * @return string Local datetime
	 * @since 1.0.0
	 *
	 */
	public static function now_local( string $format = 'Y-m-d H:i:s' ): string {
		return current_time( $format );
	}

	/**
	 * Convert local datetime to UTC for database storage
	 *
	 * @param string $local_datetime Local datetime string
	 * @param string $format         Output format
	 *
	 * @return string UTC datetime
	 * @since 1.0.0
	 *
	 */
	public static function to_utc( string $local_datetime, string $format = 'Y-m-d H:i:s' ): string {
		return get_gmt_from_date( $local_datetime, $format );
	}

	/**
	 * Convert UTC datetime to local for display
	 *
	 * @param string $utc_datetime UTC datetime from database
	 * @param string $format       Output format (empty = WP settings)
	 *
	 * @return string Local datetime
	 * @since 1.0.0
	 *
	 */
	public static function to_local( string $utc_datetime, string $format = '' ): string {
		if ( self::is_zero( $utc_datetime ) ) {
			return '—';
		}

		if ( empty( $format ) ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		return get_date_from_gmt( $utc_datetime, $format );
	}

	/**
	 * Convert Unix timestamp to MySQL datetime
	 *
	 * @param int $timestamp Unix timestamp
	 *
	 * @return string MySQL datetime in UTC
	 * @since 1.0.0
	 *
	 */
	public static function timestamp_to_mysql( int $timestamp ): string {
		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}

	/**
	 * Convert MySQL datetime to Unix timestamp
	 *
	 * @param string $datetime MySQL datetime
	 *
	 * @return int Unix timestamp
	 * @since 1.0.0
	 *
	 */
	public static function to_timestamp( string $datetime ): int {
		return strtotime( $datetime . ' UTC' );
	}

	/* ========================================================================
	 * DATE ARITHMETIC
	 * ======================================================================== */

	/**
	 * Add time to a UTC date
	 *
	 * @param string $utc_datetime UTC datetime
	 * @param int    $amount       Amount to add
	 * @param string $unit         Unit: days, hours, minutes, seconds, weeks, months, years
	 *
	 * @return string Modified UTC datetime
	 * @throws Exception
	 * @since 1.0.0
	 *
	 */
	public static function add( string $utc_datetime, int $amount, string $unit = 'days' ): string {
		$timestamp = strtotime( $utc_datetime . ' UTC' );

		switch ( $unit ) {
			case 'seconds':
				$timestamp += $amount;
				break;
			case 'minutes':
				$timestamp += $amount * MINUTE_IN_SECONDS;
				break;
			case 'hours':
				$timestamp += $amount * HOUR_IN_SECONDS;
				break;
			case 'days':
				$timestamp += $amount * DAY_IN_SECONDS;
				break;
			case 'weeks':
				$timestamp += $amount * WEEK_IN_SECONDS;
				break;
			case 'months':
				return self::add_months( $utc_datetime, $amount );
			case 'years':
				return self::add_years( $utc_datetime, $amount );
		}

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}

	/**
	 * Subtract time from a UTC date
	 *
	 * @param string $utc_datetime UTC datetime
	 * @param int    $amount       Amount to subtract
	 * @param string $unit         Unit: days, hours, minutes, seconds, weeks, months, years
	 *
	 * @return string Modified UTC datetime
	 * @throws Exception
	 * @since 1.0.0
	 *
	 */
	public static function subtract( string $utc_datetime, int $amount, string $unit = 'days' ): string {
		return self::add( $utc_datetime, - $amount, $unit );
	}

	/**
	 * Add months to a UTC date (precise calculation)
	 *
	 * @param string $utc_datetime UTC datetime
	 * @param int    $months       Number of months to add
	 *
	 * @return string Modified UTC datetime
	 * @throws Exception
	 * @since 1.0.0
	 *
	 */
	public static function add_months( string $utc_datetime, int $months ): string {
		$date = new DateTime( $utc_datetime, new DateTimeZone( 'UTC' ) );
		$date->add( new DateInterval( "P{$months}M" ) );

		return $date->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Add years to a UTC date (precise calculation)
	 *
	 * @param string $utc_datetime UTC datetime
	 * @param int    $years        Number of years to add
	 *
	 * @return string Modified UTC datetime
	 * @throws Exception
	 * @since 1.0.0
	 *
	 */
	public static function add_years( string $utc_datetime, int $years ): string {
		$date = new DateTime( $utc_datetime, new DateTimeZone( 'UTC' ) );
		$date->add( new DateInterval( "P{$years}Y" ) );

		return $date->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Get difference between two dates
	 *
	 * @param string $date1_utc First UTC datetime
	 * @param string $date2_utc Second UTC datetime
	 * @param string $unit      Unit to return: days, hours, minutes, seconds
	 *
	 * @return int Difference in specified unit
	 * @since 1.0.0
	 *
	 */
	public static function diff( string $date1_utc, string $date2_utc, string $unit = 'days' ): int {
		$timestamp1   = strtotime( $date1_utc . ' UTC' );
		$timestamp2   = strtotime( $date2_utc . ' UTC' );
		$diff_seconds = abs( $timestamp1 - $timestamp2 );

		switch ( $unit ) {
			case 'seconds':
				return $diff_seconds;
			case 'minutes':
				return intval( $diff_seconds / MINUTE_IN_SECONDS );
			case 'hours':
				return intval( $diff_seconds / HOUR_IN_SECONDS );
			case 'weeks':
				return intval( $diff_seconds / WEEK_IN_SECONDS );
			case 'months':
				return intval( $diff_seconds / MONTH_IN_SECONDS );
			case 'years':
				return intval( $diff_seconds / YEAR_IN_SECONDS );
			default:
				return intval( $diff_seconds / DAY_IN_SECONDS );
		}
	}

	/**
	 * Get elapsed time since a datetime
	 *
	 * @param string $utc_datetime UTC datetime
	 * @param string $unit         Unit to return: days, hours, minutes, seconds
	 *
	 * @return int|null Elapsed time in specified unit, or null if invalid date
	 * @since 1.0.0
	 */
	public static function elapsed( string $utc_datetime, string $unit = 'hours' ): ?int {
		if ( ! $utc_datetime || self::is_zero( $utc_datetime ) ) {
			return null;
		}

		return self::diff( $utc_datetime, self::now_utc(), $unit );
	}

	/* ========================================================================
	 * FORMATTING & DISPLAY
	 * ======================================================================== */

	/**
	 * Format UTC date using WordPress settings with i18n
	 *
	 * @param string $utc_datetime UTC datetime
	 * @param string $type         Type: 'date', 'time', or 'datetime'
	 *
	 * @return string Formatted datetime
	 * @since 1.0.0
	 *
	 */
	public static function format( string $utc_datetime, string $type = 'datetime' ): string {
		if ( self::is_zero( $utc_datetime ) ) {
			return '—';
		}

		$timestamp = strtotime( $utc_datetime . ' UTC' );

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

		return wp_date( $format, $timestamp );
	}

	/**
	 * Get human-readable time difference
	 *
	 * @param string $utc_datetime UTC datetime
	 *
	 * @return string Human-readable difference
	 * @since 1.0.0
	 *
	 */
	public static function human_diff( string $utc_datetime ): string {
		if ( self::is_zero( $utc_datetime ) ) {
			return '—';
		}

		$timestamp = strtotime( $utc_datetime . ' UTC' );

		return sprintf(
		/* translators: %s: human time difference */
			__( '%s ago', 'arraypress' ),
			human_time_diff( $timestamp )
		);
	}

	/**
	 * Format for admin display with relative time
	 *
	 * @param string $utc_datetime UTC datetime
	 *
	 * @return string HTML formatted date with relative time
	 * @since 1.0.0
	 *
	 */
	public static function format_admin( string $utc_datetime ): string {
		if ( self::is_zero( $utc_datetime ) ) {
			return '—';
		}

		$formatted = self::format( $utc_datetime );
		$relative  = self::human_diff( $utc_datetime );

		return sprintf(
			'%s<br><small class="description">%s</small>',
			esc_html( $formatted ),
			esc_html( $relative )
		);
	}

	/**
	 * Format datetime with empty value handling
	 *
	 * @param string|null $utc_datetime UTC datetime
	 * @param string      $empty_text   Text to show for empty/zero dates
	 * @param string      $format_type  Format type: 'datetime', 'date', 'time', 'human', 'admin'
	 *
	 * @return string Formatted datetime or empty text
	 * @since 1.0.0
	 */
	public static function format_or_empty( ?string $utc_datetime, string $empty_text = '—', string $format_type = 'datetime' ): string {
		if ( ! $utc_datetime || self::is_zero( $utc_datetime ) ) {
			return $empty_text;
		}

		switch ( $format_type ) {
			case 'human':
				return self::human_diff( $utc_datetime );
			case 'admin':
				return self::format_admin( $utc_datetime );
			default:
				return self::format( $utc_datetime, $format_type );
		}
	}

	/* ========================================================================
	 * VALIDATION & CHECKS
	 * ======================================================================== */

	/**
	 * Check if date is zero/empty
	 *
	 * @param string|null $date Date value
	 *
	 * @return bool True if zero/empty
	 * @since 1.0.0
	 *
	 */
	public static function is_zero( ?string $date ): bool {
		return empty( $date )
		       || $date === '0000-00-00 00:00:00'
		       || $date === '0000-00-00';
	}

	/**
	 * Validate date string
	 *
	 * @param string $date Date string to validate
	 *
	 * @return bool True if valid date
	 * @since 1.0.0
	 *
	 */
	public static function is_valid( string $date ): bool {
		return strtotime( $date ) !== false;
	}

	/**
	 * Check if date matches specific format
	 *
	 * @param string $date   Date string to check
	 * @param string $format Expected format (e.g., 'Y-m-d')
	 *
	 * @return bool True if matches format
	 * @since 1.0.0
	 *
	 */
	public static function is_format( string $date, string $format ): bool {
		$d = DateTime::createFromFormat( $format, $date );

		return $d && $d->format( $format ) === $date;
	}

	/**
	 * Check if date has expired
	 *
	 * @param string $utc_datetime UTC expiration datetime
	 * @param int    $grace_hours  Optional grace period in hours
	 *
	 * @return bool True if expired
	 * @since 1.0.0
	 *
	 */
	public static function is_expired( string $utc_datetime, int $grace_hours = 0 ): bool {
		$expiry = strtotime( $utc_datetime . ' UTC' );

		if ( $grace_hours > 0 ) {
			$expiry += $grace_hours * HOUR_IN_SECONDS;
		}

		return time() > $expiry;
	}

	/**
	 * Check if date is in the past
	 *
	 * @param string $utc_datetime UTC datetime
	 *
	 * @return bool True if past
	 * @since 1.0.0
	 *
	 */
	public static function is_past( string $utc_datetime ): bool {
		return strtotime( $utc_datetime . ' UTC' ) < time();
	}

	/**
	 * Check if date is in the future
	 *
	 * @param string $utc_datetime UTC datetime
	 *
	 * @return bool True if future
	 * @since 1.0.0
	 *
	 */
	public static function is_future( string $utc_datetime ): bool {
		return strtotime( $utc_datetime . ' UTC' ) > time();
	}

	/**
	 * Check if date is within range
	 *
	 * @param string $date_utc  UTC datetime to check
	 * @param string $start_utc UTC start datetime
	 * @param string $end_utc   UTC end datetime
	 *
	 * @return bool True if within range
	 * @since 1.0.0
	 *
	 */
	public static function in_range( string $date_utc, string $start_utc, string $end_utc ): bool {
		$date  = strtotime( $date_utc . ' UTC' );
		$start = strtotime( $start_utc . ' UTC' );
		$end   = strtotime( $end_utc . ' UTC' );

		return $date >= $start && $date <= $end;
	}

	/**
	 * Check if a datetime is older than a specified threshold (is stale)
	 *
	 * @param string|null $utc_datetime UTC datetime to check
	 * @param int         $threshold    Threshold value
	 * @param string      $unit         Unit: hours, days, minutes, seconds, weeks
	 *
	 * @return bool True if datetime is older than threshold or empty
	 * @since 1.0.0
	 */
	public static function is_stale( ?string $utc_datetime, int $threshold, string $unit = 'hours' ): bool {
		if ( ! $utc_datetime || self::is_zero( $utc_datetime ) ) {
			return true;
		}

		$elapsed = self::diff( $utc_datetime, self::now_utc(), $unit );

		return $elapsed >= $threshold;
	}

	/**
	 * Check if a datetime is within a specified threshold (is fresh)
	 *
	 * @param string|null $utc_datetime UTC datetime to check
	 * @param int         $threshold    Threshold value
	 * @param string      $unit         Unit: hours, days, minutes, seconds, weeks
	 *
	 * @return bool True if datetime is within threshold and not empty
	 * @since 1.0.0
	 */
	public static function is_fresh( ?string $utc_datetime, int $threshold, string $unit = 'hours' ): bool {
		if ( ! $utc_datetime || self::is_zero( $utc_datetime ) ) {
			return false;
		}

		$elapsed = self::diff( $utc_datetime, self::now_utc(), $unit );

		return $elapsed < $threshold;
	}

	/* ========================================================================
	 * BUSINESS LOGIC
	 * ======================================================================== */

	/**
	 * Check if date is a weekend
	 *
	 * @param string $utc_datetime UTC datetime
	 *
	 * @return bool True if weekend (Saturday or Sunday)
	 * @since 1.0.0
	 *
	 */
	public static function is_weekend( string $utc_datetime ): bool {
		$day = gmdate( 'N', strtotime( $utc_datetime . ' UTC' ) );

		return in_array( $day, [ '6', '7' ] ); // Saturday, Sunday
	}

	/**
	 * Check if date is a business day
	 *
	 * @param string $utc_datetime  UTC datetime
	 * @param array  $business_days Business days (1=Mon, 7=Sun)
	 *
	 * @return bool True if business day
	 * @since 1.0.0
	 *
	 */
	public static function is_business_day( string $utc_datetime, array $business_days = [ 1, 2, 3, 4, 5 ] ): bool {
		$day = (int) gmdate( 'N', strtotime( $utc_datetime . ' UTC' ) );

		return in_array( $day, $business_days );
	}

	/**
	 * Get next business day
	 *
	 * @param string $utc_datetime  UTC datetime
	 * @param array  $business_days Business days (1=Mon, 7=Sun)
	 *
	 * @return string Next business day in UTC
	 * @since 1.0.0
	 *
	 */
	public static function next_business_day( string $utc_datetime, array $business_days = [ 1, 2, 3, 4, 5 ] ): string {
		$timestamp = strtotime( $utc_datetime . ' UTC' );

		do {
			$timestamp += DAY_IN_SECONDS;
			$day       = (int) gmdate( 'N', $timestamp );
		} while ( ! in_array( $day, $business_days ) );

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}

	/**
	 * Calculate age in years from birth date
	 *
	 * @param string $birth_date_utc Birth date in UTC
	 *
	 * @return int Age in years
	 * @throws Exception
	 * @since 1.0.0
	 *
	 */
	public static function get_age( string $birth_date_utc ): int {
		$birth = new DateTime( $birth_date_utc, new DateTimeZone( 'UTC' ) );
		$now   = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

		return $birth->diff( $now )->y;
	}

	/**
	 * Check if age requirement is met
	 *
	 * @param string $birth_date_utc Birth date in UTC
	 * @param int    $min_age        Minimum required age
	 *
	 * @return bool True if age requirement is met
	 * @since 1.0.0
	 *
	 */
	public static function meets_age_requirement( string $birth_date_utc, int $min_age ): bool {
		return self::get_age( $birth_date_utc ) >= $min_age;
	}

	/* ========================================================================
	 * DATE RANGES
	 * ======================================================================== */

	/**
	 * Get date ranges (predefined periods)
	 *
	 * @param string $range          Range identifier
	 * @param bool   $local_timezone If true, calculate in local timezone first then convert to UTC
	 *
	 * @return array{start: string, end: string} Start/end times in UTC
	 * @since 1.0.0
	 *
	 */
	public static function get_range( string $range, bool $local_timezone = true ): array {
		if ( ! $local_timezone ) {
			return self::get_range_utc( $range );
		}

		// Calculate in local timezone first (more intuitive for users)
		$local_timestamp = current_time( 'timestamp' );

		switch ( $range ) {
			case 'today':
				$start = current_time( 'Y-m-d 00:00:00' );
				$end   = current_time( 'Y-m-d 23:59:59' );
				break;

			case 'yesterday':
				$yesterday = date( 'Y-m-d', $local_timestamp - DAY_IN_SECONDS );
				$start     = $yesterday . ' 00:00:00';
				$end       = $yesterday . ' 23:59:59';
				break;

			case 'tomorrow':
				$tomorrow = date( 'Y-m-d', $local_timestamp + DAY_IN_SECONDS );
				$start    = $tomorrow . ' 00:00:00';
				$end      = $tomorrow . ' 23:59:59';
				break;

			case 'this_week':
				$week_start = strtotime( 'monday this week', $local_timestamp );
				$week_end   = strtotime( 'sunday this week', $local_timestamp ) + 86399;
				$start      = date( 'Y-m-d 00:00:00', $week_start );
				$end        = date( 'Y-m-d 23:59:59', $week_end );
				break;

			case 'last_week':
				$week_start = strtotime( 'monday last week', $local_timestamp );
				$week_end   = strtotime( 'sunday last week', $local_timestamp ) + 86399;
				$start      = date( 'Y-m-d 00:00:00', $week_start );
				$end        = date( 'Y-m-d 23:59:59', $week_end );
				break;

			case 'next_week':
				$week_start = strtotime( 'monday next week', $local_timestamp );
				$week_end   = strtotime( 'sunday next week', $local_timestamp ) + 86399;
				$start      = date( 'Y-m-d 00:00:00', $week_start );
				$end        = date( 'Y-m-d 23:59:59', $week_end );
				break;

			case 'this_month':
				$start = date( 'Y-m-01 00:00:00', $local_timestamp );
				$end   = date( 'Y-m-t 23:59:59', $local_timestamp );
				break;

			case 'last_month':
				$first_day = strtotime( 'first day of last month', $local_timestamp );
				$last_day  = strtotime( 'last day of last month', $local_timestamp );
				$start     = date( 'Y-m-d 00:00:00', $first_day );
				$end       = date( 'Y-m-d 23:59:59', $last_day );
				break;

			case 'next_month':
				$first_day = strtotime( 'first day of next month', $local_timestamp );
				$last_day  = strtotime( 'last day of next month', $local_timestamp );
				$start     = date( 'Y-m-d 00:00:00', $first_day );
				$end       = date( 'Y-m-d 23:59:59', $last_day );
				break;

			case 'this_quarter':
				$month               = date( 'n', $local_timestamp );
				$year                = date( 'Y', $local_timestamp );
				$quarter_start_month = ceil( $month / 3 ) * 3 - 2;
				$quarter_end_month   = $quarter_start_month + 2;
				$start               = date( 'Y-m-d 00:00:00', mktime( 0, 0, 0, $quarter_start_month, 1, $year ) );
				$end                 = date( 'Y-m-d 23:59:59', mktime( 23, 59, 59, $quarter_end_month + 1, 0, $year ) );
				break;

			case 'last_quarter':
				$month               = date( 'n', $local_timestamp );
				$year                = date( 'Y', $local_timestamp );
				$quarter_start_month = ceil( $month / 3 ) * 3 - 5;
				if ( $quarter_start_month < 1 ) {
					$quarter_start_month += 12;
					$year --;
				}
				$quarter_end_month = $quarter_start_month + 2;
				$start             = date( 'Y-m-d 00:00:00', mktime( 0, 0, 0, $quarter_start_month, 1, $year ) );
				$end               = date( 'Y-m-d 23:59:59', mktime( 23, 59, 59, $quarter_end_month + 1, 0, $year ) );
				break;

			case 'this_year':
				$start = date( 'Y-01-01 00:00:00', $local_timestamp );
				$end   = date( 'Y-12-31 23:59:59', $local_timestamp );
				break;

			case 'last_year':
				$year  = date( 'Y', $local_timestamp ) - 1;
				$start = $year . '-01-01 00:00:00';
				$end   = $year . '-12-31 23:59:59';
				break;

			case 'next_year':
				$year  = date( 'Y', $local_timestamp ) + 1;
				$start = $year . '-01-01 00:00:00';
				$end   = $year . '-12-31 23:59:59';
				break;

			case 'last_7_days':
			case 'last_week_rolling':
				$start = date( 'Y-m-d 00:00:00', $local_timestamp - ( 6 * DAY_IN_SECONDS ) );
				$end   = date( 'Y-m-d 23:59:59', $local_timestamp );
				break;

			case 'last_30_days':
			case 'last_month_rolling':
				$start = date( 'Y-m-d 00:00:00', $local_timestamp - ( 29 * DAY_IN_SECONDS ) );
				$end   = date( 'Y-m-d 23:59:59', $local_timestamp );
				break;

			case 'last_60_days':
				$start = date( 'Y-m-d 00:00:00', $local_timestamp - ( 59 * DAY_IN_SECONDS ) );
				$end   = date( 'Y-m-d 23:59:59', $local_timestamp );
				break;

			case 'last_90_days':
			case 'last_3_months':
				$start = date( 'Y-m-d 00:00:00', $local_timestamp - ( 89 * DAY_IN_SECONDS ) );
				$end   = date( 'Y-m-d 23:59:59', $local_timestamp );
				break;

			case 'last_180_days':
			case 'last_6_months':
				$start = date( 'Y-m-d 00:00:00', $local_timestamp - ( 179 * DAY_IN_SECONDS ) );
				$end   = date( 'Y-m-d 23:59:59', $local_timestamp );
				break;

			case 'last_365_days':
			case 'last_year_rolling':
				$start = date( 'Y-m-d 00:00:00', $local_timestamp - ( 364 * DAY_IN_SECONDS ) );
				$end   = date( 'Y-m-d 23:59:59', $local_timestamp );
				break;

			case 'year_to_date':
				$start = date( 'Y-01-01 00:00:00', $local_timestamp );
				$end   = date( 'Y-m-d 23:59:59', $local_timestamp );
				break;

			case 'month_to_date':
				$start = date( 'Y-m-01 00:00:00', $local_timestamp );
				$end   = date( 'Y-m-d 23:59:59', $local_timestamp );
				break;

			case 'week_to_date':
				$week_start = strtotime( 'monday this week', $local_timestamp );
				$start      = date( 'Y-m-d 00:00:00', $week_start );
				$end        = date( 'Y-m-d 23:59:59', $local_timestamp );
				break;

			default:
				// Default to today
				$start = current_time( 'Y-m-d 00:00:00' );
				$end   = current_time( 'Y-m-d 23:59:59' );
				break;
		}

		// Convert local times to UTC
		return [
			'start' => self::to_utc( $start ),
			'end'   => self::to_utc( $end )
		];
	}

	/**
	 * Get date range in pure UTC (for rolling windows)
	 *
	 * @param string $range Range identifier
	 *
	 * @return array{start: string, end: string} UTC range
	 * @since  1.0.0
	 * @access private
	 *
	 */
	private static function get_range_utc( string $range ): array {
		$now = time();

		switch ( $range ) {
			case 'last_7_days':
				$start = gmdate( 'Y-m-d 00:00:00', $now - ( 6 * DAY_IN_SECONDS ) );
				$end   = gmdate( 'Y-m-d 23:59:59', $now );
				break;

			case 'last_30_days':
				$start = gmdate( 'Y-m-d 00:00:00', $now - ( 29 * DAY_IN_SECONDS ) );
				$end   = gmdate( 'Y-m-d 23:59:59', $now );
				break;

			case 'last_90_days':
				$start = gmdate( 'Y-m-d 00:00:00', $now - ( 89 * DAY_IN_SECONDS ) );
				$end   = gmdate( 'Y-m-d 23:59:59', $now );
				break;

			default:
				$start = gmdate( 'Y-m-d 00:00:00', $now );
				$end   = gmdate( 'Y-m-d 23:59:59', $now );
				break;
		}

		return [
			'start' => $start,
			'end'   => $end
		];
	}

	/**
	 * Convert a local date range to UTC
	 *
	 * @param string $start_local Local start datetime
	 * @param string $end_local   Local end datetime
	 *
	 * @return array{start: string, end: string} UTC range
	 * @since 1.0.0
	 *
	 */
	public static function range_to_utc( string $start_local, string $end_local ): array {
		return [
			'start' => self::to_utc( $start_local ),
			'end'   => self::to_utc( $end_local )
		];
	}

	/**
	 * Get start of day for UTC date
	 *
	 * @param string $utc_datetime UTC datetime
	 *
	 * @return string Start of day in UTC
	 * @since 1.0.0
	 *
	 */
	public static function start_of_day( string $utc_datetime ): string {
		return gmdate( 'Y-m-d 00:00:00', strtotime( $utc_datetime . ' UTC' ) );
	}

	/**
	 * Get end of day for UTC date
	 *
	 * @param string $utc_datetime UTC datetime
	 *
	 * @return string End of day in UTC
	 * @since 1.0.0
	 *
	 */
	public static function end_of_day( string $utc_datetime ): string {
		return gmdate( 'Y-m-d 23:59:59', strtotime( $utc_datetime . ' UTC' ) );
	}

	/* ========================================================================
	 * SUBSCRIPTION & BILLING
	 * ======================================================================== */

	/**
	 * Get subscription next billing date
	 *
	 * @param string $last_payment Last payment UTC datetime
	 * @param string $period       Period: daily, weekly, monthly, yearly, every_3_months, every_6_months
	 *
	 * @return string Next billing UTC datetime
	 * @throws Exception
	 * @since 1.0.0
	 */
	public static function next_billing( string $last_payment, string $period ): string {
		$timestamp = strtotime( $last_payment . ' UTC' );

		switch ( $period ) {
			case 'daily':
				$timestamp += DAY_IN_SECONDS;
				break;
			case 'weekly':
				$timestamp += WEEK_IN_SECONDS;
				break;
			case 'every_3_months':
				return self::add_months( $last_payment, 3 );
			case 'every_6_months':
				return self::add_months( $last_payment, 6 );
			case 'yearly':
				return self::add_years( $last_payment, 1 );
			default:
				return self::add_months( $last_payment, 1 );
		}

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}

	/**
	 * Calculate expiration date from now
	 *
	 * @param int    $duration Duration value
	 * @param string $unit     Unit: days, hours, minutes, months, years
	 *
	 * @return string UTC expiration datetime
	 * @throws Exception
	 * @since 1.0.0
	 *
	 */
	public static function calculate_expiration( int $duration, string $unit = 'days' ): string {
		return self::add( self::now_utc(), $duration, $unit );
	}

	/* ========================================================================
	 * DATABASE HELPERS
	 * ======================================================================== */

	/**
	 * Build database query for date range
	 *
	 * @param string $column      Database column name
	 * @param string $start_local Local start datetime
	 * @param string $end_local   Local end datetime
	 *
	 * @return array{sql: string, values: array} Query parts
	 * @since 1.0.0
	 *
	 */
	public static function build_date_query( string $column, string $start_local, string $end_local ): array {
		return [
			'sql'    => "{$column} BETWEEN %s AND %s",
			'values' => [ self::to_utc( $start_local ), self::to_utc( $end_local ) ]
		];
	}

	/* ========================================================================
	 * OPTIONS & CONSTANTS
	 * ======================================================================== */

	/**
	 * Get available range options for dropdowns
	 *
	 * @return array Array of value => label pairs
	 * @since 1.0.0
	 */
	public static function get_range_options(): array {
		return [
			'today'        => __( 'Today', 'arraypress' ),
			'yesterday'    => __( 'Yesterday', 'arraypress' ),
			'this_week'    => __( 'This Week', 'arraypress' ),
			'last_week'    => __( 'Last Week', 'arraypress' ),
			'this_month'   => __( 'This Month', 'arraypress' ),
			'last_month'   => __( 'Last Month', 'arraypress' ),
			'this_year'    => __( 'This Year', 'arraypress' ),
			'last_year'    => __( 'Last Year', 'arraypress' ),
			'last_7_days'  => __( 'Last 7 Days', 'arraypress' ),
			'last_30_days' => __( 'Last 30 Days', 'arraypress' ),
			'last_90_days' => __( 'Last 90 Days', 'arraypress' ),
			'year_to_date' => __( 'Year to Date', 'arraypress' ),
		];
	}

	/**
	 * Get subscription period options (Stripe-compatible)
	 *
	 * @param bool $include_custom Include custom option
	 *
	 * @return array Array of value => label pairs
	 * @since 1.0.0
	 */
	public static function get_period_options( bool $include_custom = false ): array {
		$options = [
			'daily'          => __( 'Daily', 'arraypress' ),
			'weekly'         => __( 'Weekly', 'arraypress' ),
			'monthly'        => __( 'Monthly', 'arraypress' ),
			'every_3_months' => __( 'Every 3 months', 'arraypress' ),
			'every_6_months' => __( 'Every 6 months', 'arraypress' ),
			'yearly'         => __( 'Yearly', 'arraypress' ),
		];

		if ( $include_custom ) {
			$options['custom'] = __( 'Custom', 'arraypress' );
		}

		return $options;
	}

	/**
	 * Get Stripe-compatible interval format
	 *
	 * @param string $period Our period name
	 *
	 * @return array{interval: string, interval_count: int} Stripe interval format
	 * @since 1.0.0
	 */
	public static function get_stripe_interval( string $period ): array {
		switch ( $period ) {
			case 'daily':
				return [ 'interval' => 'day', 'interval_count' => 1 ];
			case 'weekly':
				return [ 'interval' => 'week', 'interval_count' => 1 ];
			case 'every_3_months':
				return [ 'interval' => 'month', 'interval_count' => 3 ];
			case 'every_6_months':
				return [ 'interval' => 'month', 'interval_count' => 6 ];
			case 'yearly':
				return [ 'interval' => 'year', 'interval_count' => 1 ];
			default:
				return [ 'interval' => 'month', 'interval_count' => 1 ];
		}
	}

	/* ========================================================================
	 * TIMEZONE
	 * ======================================================================== */

	/**
	 * Convert datetime between specific timezones
	 *
	 * @param string $datetime Source datetime
	 * @param string $from_tz  Source timezone (e.g., 'UTC', 'America/New_York')
	 * @param string $to_tz    Target timezone (e.g., 'Europe/London', 'Asia/Tokyo')
	 *
	 * @return string Converted datetime in target timezone
	 * @throws Exception If invalid timezone or datetime
	 * @since 1.0.0
	 */
	public static function convert_timezone( string $datetime, string $from_tz, string $to_tz ): string {
		try {
			$date = new DateTime( $datetime, new DateTimeZone( $from_tz ) );
			$date->setTimezone( new DateTimeZone( $to_tz ) );

			return $date->format( 'Y-m-d H:i:s' );
		} catch ( Exception $e ) {
			throw new Exception( 'Invalid timezone or datetime: ' . $e->getMessage() );
		}
	}

	/* ========================================================================
	 * WORKING DAYS
	 * ======================================================================== */

	/**
	 * Add working/business days to a date (excluding weekends)
	 *
	 * @param string $utc_datetime  Starting UTC datetime
	 * @param int    $days          Number of working days to add (can be negative)
	 * @param array  $business_days Days considered working days (1=Mon, 7=Sun)
	 * @param array  $holidays      Optional array of holiday dates in 'Y-m-d' format
	 *
	 * @return string Resulting UTC datetime
	 * @since 1.0.0
	 */
	public static function add_working_days(
		string $utc_datetime, int $days, array $business_days = [
		1,
		2,
		3,
		4,
		5
	], array $holidays = []
	): string {
		$timestamp = strtotime( $utc_datetime . ' UTC' );
		$direction = $days < 0 ? - 1 : 1;
		$days      = abs( $days );
		$added     = 0;

		while ( $added < $days ) {
			$timestamp   += $direction * DAY_IN_SECONDS;
			$day_of_week = (int) gmdate( 'N', $timestamp );
			$date_string = gmdate( 'Y-m-d', $timestamp );

			// Check if it's a business day and not a holiday
			if ( in_array( $day_of_week, $business_days ) && ! in_array( $date_string, $holidays ) ) {
				$added ++;
			}
		}

		// Preserve the original time
		$time_part = gmdate( 'H:i:s', strtotime( $utc_datetime . ' UTC' ) );

		return gmdate( 'Y-m-d', $timestamp ) . ' ' . $time_part;
	}

	/**
	 * Calculate number of working days between two dates
	 *
	 * @param string $start_utc     Start UTC datetime
	 * @param string $end_utc       End UTC datetime
	 * @param array  $business_days Days considered working days (1=Mon, 7=Sun)
	 * @param array  $holidays      Optional array of holiday dates in 'Y-m-d' format
	 *
	 * @return int Number of working days
	 * @since 1.0.0
	 */
	public static function working_days_between(
		string $start_utc, string $end_utc, array $business_days = [
		1,
		2,
		3,
		4,
		5
	], array $holidays = []
	): int {
		$start = strtotime( $start_utc . ' UTC' );
		$end   = strtotime( $end_utc . ' UTC' );

		// Ensure start is before end
		if ( $start > $end ) {
			$temp  = $start;
			$start = $end;
			$end   = $temp;
		}

		$working_days = 0;
		$current      = $start;

		while ( $current <= $end ) {
			$day_of_week = (int) gmdate( 'N', $current );
			$date_string = gmdate( 'Y-m-d', $current );

			if ( in_array( $day_of_week, $business_days ) && ! in_array( $date_string, $holidays ) ) {
				$working_days ++;
			}

			$current += DAY_IN_SECONDS;
		}

		return $working_days;
	}

}