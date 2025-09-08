<?php
/**
 * Global Date Helper Functions
 *
 * Provides convenient global functions for common date operations.
 * These functions are wrappers around the ArrayPress\DateUtils\Dates class.
 *
 * Functions included:
 * - current_time_utc() - Get current UTC time for database storage
 * - utc_to_local() - Convert UTC to local timezone for display
 * - local_to_utc() - Convert local to UTC for database storage
 * - format_date() - Format dates using WordPress settings
 * - date_or_empty() - Format dates with empty value handling
 *
 * @package ArrayPress\DateUtils
 * @since   1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use ArrayPress\DateUtils\Dates;

/**
 * Global Date Helper Functions
 *
 * Essential shortcuts for the most common date operations.
 * These are intentionally in the global namespace for easy access.
 */

if ( ! function_exists( 'current_time_utc' ) ) {
	/**
	 * Get current UTC datetime for database storage.
	 *
	 * @param string $format PHP date format. Default MySQL format.
	 *
	 * @return string Current UTC datetime.
	 * @since 1.0.0
	 */
	function current_time_utc( string $format = 'Y-m-d H:i:s' ): string {
		return Dates::now_utc( $format );
	}
}

if ( ! function_exists( 'utc_to_local' ) ) {
	/**
	 * Convert UTC datetime to local timezone for display.
	 *
	 * @param string $utc_datetime UTC datetime from database.
	 * @param string $format       PHP date format. Empty uses WP settings.
	 *
	 * @return string Local datetime.
	 * @since 1.0.0
	 */
	function utc_to_local( string $utc_datetime, string $format = '' ): string {
		return Dates::to_local( $utc_datetime, $format );
	}
}

if ( ! function_exists( 'local_to_utc' ) ) {
	/**
	 * Convert local datetime to UTC for database storage.
	 *
	 * @param string $local_datetime Local datetime from user input.
	 * @param string $format         Output format.
	 *
	 * @return string UTC datetime.
	 * @since 1.0.0
	 */
	function local_to_utc( string $local_datetime, string $format = 'Y-m-d H:i:s' ): string {
		return Dates::to_utc( $local_datetime, $format );
	}
}

if ( ! function_exists( 'format_date' ) ) {
	/**
	 * Format UTC datetime using WordPress settings.
	 *
	 * @param string $utc_datetime UTC datetime to format.
	 * @param string $type         Format type: 'datetime', 'date', 'time'.
	 *
	 * @return string Formatted datetime.
	 * @since 1.0.0
	 */
	function format_date( string $utc_datetime, string $type = 'datetime' ): string {
		return Dates::format( $utc_datetime, $type );
	}
}

if ( ! function_exists( 'date_or_empty' ) ) {
	/**
	 * Format datetime with empty value handling.
	 *
	 * @param string|null $utc_datetime UTC datetime to format.
	 * @param string      $empty_text   Text for empty values.
	 * @param string      $format_type  Format type: 'datetime', 'date', 'time', 'human'.
	 *
	 * @return string Formatted datetime or empty text.
	 * @since 1.0.0
	 */
	function date_or_empty( ?string $utc_datetime, string $empty_text = '—', string $format_type = 'datetime' ): string {
		return Dates::format_or_empty( $utc_datetime, $empty_text, $format_type );
	}
}