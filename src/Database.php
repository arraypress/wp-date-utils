<?php
/**
 * Database Utility Class
 *
 * Handles database-specific date operations including MySQL zero dates
 * and safe database value checking.
 *
 * @package     ArrayPress\DateUtils
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\DateUtils;

class Database {

	/**
	 * Get MySQL zero date
	 *
	 * @return string MySQL zero date
	 */
	public static function zero_date(): string {
		return '0000-00-00 00:00:00';
	}

	/**
	 * Check if database date is zero/empty
	 *
	 * @param string|null $db_date Database date value
	 *
	 * @return bool True if zero/empty date
	 */
	public static function is_zero_date( ?string $db_date ): bool {
		return empty( $db_date ) || $db_date === self::zero_date();
	}

	/**
	 * Safely format database date for display
	 *
	 * @param string|null $utc_datetime Database UTC datetime
	 *
	 * @return string Formatted date or empty indicator
	 */
	public static function safe_display( ?string $utc_datetime ): string {
		if ( self::is_zero_date( $utc_datetime ) ) {
			return '—';
		}

		return Format::wp( $utc_datetime, 'datetime' );
	}

}