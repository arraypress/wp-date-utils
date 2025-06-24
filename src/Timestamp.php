<?php
/**
 * Timestamp Utility Class
 *
 * Fast timestamp operations for quick calculations, caching, and simple time math.
 * Optimized for performance when you don't need full Carbon parsing.
 *
 * @package     ArrayPress\Utils
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\DateUtils;

class Timestamp {

	/**
	 * Get current timestamp
	 *
	 * @return int Current Unix timestamp
	 */
	public static function now(): int {
		return time();
	}

	/**
	 * Get timestamp X seconds from now
	 *
	 * @param int $seconds Seconds to add
	 *
	 * @return int Future timestamp
	 */
	public static function in_seconds( int $seconds ): int {
		return time() + $seconds;
	}

	/**
	 * Get timestamp X minutes from now
	 *
	 * @param int $minutes Minutes to add
	 *
	 * @return int Future timestamp
	 */
	public static function in_minutes( int $minutes ): int {
		return time() + ( $minutes * 60 );
	}

	/**
	 * Get timestamp X hours from now
	 *
	 * @param int $hours Hours to add
	 *
	 * @return int Future timestamp
	 */
	public static function in_hours( int $hours ): int {
		return time() + ( $hours * 3600 );
	}

	/**
	 * Get timestamp X days from now
	 *
	 * @param int $days Days to add
	 *
	 * @return int Future timestamp
	 */
	public static function in_days( int $days ): int {
		return time() + ( $days * 86400 );
	}

	/**
	 * Get timestamp X weeks from now
	 *
	 * @param int $weeks Weeks to add
	 *
	 * @return int Future timestamp
	 */
	public static function in_weeks( int $weeks ): int {
		return time() + ( $weeks * 604800 );
	}

	/**
	 * Convert seconds to minutes
	 *
	 * @param int $seconds Number of seconds
	 *
	 * @return float Number of minutes
	 */
	public static function to_minutes( int $seconds ): float {
		return $seconds / 60;
	}

	/**
	 * Convert seconds to hours
	 *
	 * @param int $seconds Number of seconds
	 *
	 * @return float Number of hours
	 */
	public static function to_hours( int $seconds ): float {
		return $seconds / 3600;
	}

	/**
	 * Convert seconds to days
	 *
	 * @param int $seconds Number of seconds
	 *
	 * @return float Number of days
	 */
	public static function to_days( int $seconds ): float {
		return $seconds / 86400;
	}

	/**
	 * Convert minutes to seconds
	 *
	 * @param int $minutes Number of minutes
	 *
	 * @return int Number of seconds
	 */
	public static function from_minutes( int $minutes ): int {
		return $minutes * 60;
	}

	/**
	 * Convert hours to seconds
	 *
	 * @param int $hours Number of hours
	 *
	 * @return int Number of seconds
	 */
	public static function from_hours( int $hours ): int {
		return $hours * 3600;
	}

	/**
	 * Convert days to seconds
	 *
	 * @param int $days Number of days
	 *
	 * @return int Number of seconds
	 */
	public static function from_days( int $days ): int {
		return $days * 86400;
	}

	/**
	 * Check if timestamp is in the past
	 *
	 * @param int $timestamp Unix timestamp
	 *
	 * @return bool True if in past
	 */
	public static function is_past( int $timestamp ): bool {
		return $timestamp < time();
	}

	/**
	 * Check if timestamp is in the future
	 *
	 * @param int $timestamp Unix timestamp
	 *
	 * @return bool True if in future
	 */
	public static function is_future( int $timestamp ): bool {
		return $timestamp > time();
	}

	/**
	 * Get age of timestamp in seconds
	 *
	 * @param int $timestamp Unix timestamp
	 *
	 * @return int Age in seconds (negative if future)
	 */
	public static function age( int $timestamp ): int {
		return time() - $timestamp;
	}

	/**
	 * Check if timestamp is older than X seconds
	 *
	 * @param int $timestamp Unix timestamp
	 * @param int $seconds   Seconds threshold
	 *
	 * @return bool True if older
	 */
	public static function is_older_than( int $timestamp, int $seconds ): bool {
		return self::age( $timestamp ) > $seconds;
	}

	/**
	 * Check if timestamp is newer than X seconds
	 *
	 * @param int $timestamp Unix timestamp
	 * @param int $seconds   Seconds threshold
	 *
	 * @return bool True if newer
	 */
	public static function is_newer_than( int $timestamp, int $seconds ): bool {
		return self::age( $timestamp ) < $seconds;
	}

	/**
	 * Round timestamp to nearest interval
	 *
	 * @param int    $timestamp Unix timestamp
	 * @param int    $interval  Interval in seconds
	 * @param string $direction Round direction: 'up', 'down', 'nearest'
	 *
	 * @return int Rounded timestamp
	 */
	public static function round( int $timestamp, int $interval, string $direction = 'nearest' ): int {
		switch ( $direction ) {
			case 'up':
				return (int) ( ceil( $timestamp / $interval ) * $interval );
			case 'down':
				return (int) ( floor( $timestamp / $interval ) * $interval );
			default:
				return (int) ( round( $timestamp / $interval ) * $interval );
		}
	}

}