<?php
/**
 * Date Range Utility Class
 *
 * Handles date ranges, periods, and intervals with WordPress integration.
 * Provides functionality for predefined ranges and custom periods.
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
use InvalidArgumentException;

class Range {

	/**
	 * Get predefined date range labels
	 *
	 * @param string $context        Optional context for filtering
	 * @param bool   $include_future Whether to include future ranges
	 *
	 * @return array<string, string> List of range identifiers and labels
	 */
	private static function get_range_labels( string $context = 'default', bool $include_future = true ): array {
		$ranges = [
			'today'         => __( 'Today', 'arraypress' ),
			'yesterday'     => __( 'Yesterday', 'arraypress' ),
			'this_week'     => __( 'This Week', 'arraypress' ),
			'last_week'     => __( 'Last Week', 'arraypress' ),
			'this_month'    => __( 'This Month', 'arraypress' ),
			'last_month'    => __( 'Last Month', 'arraypress' ),
			'this_quarter'  => __( 'This Quarter', 'arraypress' ),
			'last_quarter'  => __( 'Last Quarter', 'arraypress' ),
			'this_year'     => __( 'This Year', 'arraypress' ),
			'last_year'     => __( 'Last Year', 'arraypress' ),
			'last_7_days'   => __( 'Last 7 Days', 'arraypress' ),
			'last_30_days'  => __( 'Last 30 Days', 'arraypress' ),
			'last_90_days'  => __( 'Last 90 Days', 'arraypress' ),
			'last_365_days' => __( 'Last 365 Days', 'arraypress' ),
			'year_to_date'  => __( 'Year to Date', 'arraypress' ),
			'month_to_date' => __( 'Month to Date', 'arraypress' ),
			'week_to_date'  => __( 'Week to Date', 'arraypress' ),
		];

		// Add future ranges if requested
		if ( $include_future ) {
			$future_ranges = [
				'next_7_days'  => __( 'Next 7 Days', 'arraypress' ),
				'next_30_days' => __( 'Next 30 Days', 'arraypress' ),
				'next_90_days' => __( 'Next 90 Days', 'arraypress' ),
			];
			$ranges        = array_merge( $ranges, $future_ranges );
		}

		/**
		 * Filter the available date ranges
		 *
		 * @param array  $ranges         Array of range identifiers and labels
		 * @param string $context        The context in which the ranges are being used
		 * @param bool   $include_future Whether future ranges are included
		 */
		return apply_filters( 'arraypress_date_ranges', $ranges, $context, $include_future );
	}

	/**
	 * Get list of available predefined ranges
	 *
	 * @param string $context        Optional context for filtering
	 * @param bool   $include_future Whether to include future ranges
	 *
	 * @return array<string, string> List of range identifiers and labels
	 */
	public static function get_ranges( string $context = 'default', bool $include_future = true ): array {
		return self::get_range_labels( $context, $include_future );
	}

	/**
	 * Get available range options formatted for REST API/dropdowns
	 *
	 * @param string $context        Optional context for filtering
	 * @param bool   $include_future Whether to include future ranges
	 *
	 * @return array Array of options with value/label structure
	 */
	public static function options( string $context = 'default', bool $include_future = true ): array {
		$ranges  = self::get_range_labels( $context, $include_future );
		$options = [];

		foreach ( $ranges as $value => $label ) {
			$options[] = [
				'value' => $value,
				'label' => $label
			];
		}

		return $options;
	}

	/**
	 * Get a predefined date range
	 *
	 * @param string $range   Range identifier
	 * @param string $format  Date format for returned dates
	 * @param string $context Optional context for filtering
	 *
	 * @return array{start: string, end: string} Start and end dates in UTC
	 * @throws InvalidArgumentException If range is invalid
	 */
	public static function get( string $range, string $format = 'Y-m-d H:i:s', string $context = 'default' ): array {
		$ranges = self::get_range_labels( $context, true ); // Include all ranges for validation

		if ( ! isset( $ranges[ $range ] ) ) {
			throw new InvalidArgumentException( "Invalid range: {$range}" );
		}

		$now = Carbon::now( wp_timezone() );

		switch ( $range ) {
			case 'today':
				$start = $now->copy()->startOfDay();
				$end   = $now->copy()->endOfDay();
				break;

			case 'yesterday':
				$start = $now->copy()->subDay()->startOfDay();
				$end   = $now->copy()->subDay()->endOfDay();
				break;

			case 'this_week':
				$start = $now->copy()->startOfWeek();
				$end   = $now->copy()->endOfWeek();
				break;

			case 'last_week':
				$start = $now->copy()->subWeek()->startOfWeek();
				$end   = $now->copy()->subWeek()->endOfWeek();
				break;

			case 'this_month':
				$start = $now->copy()->startOfMonth();
				$end   = $now->copy()->endOfMonth();
				break;

			case 'last_month':
				$start = $now->copy()->subMonth()->startOfMonth();
				$end   = $now->copy()->subMonth()->endOfMonth();
				break;

			case 'this_quarter':
				$start = $now->copy()->startOfQuarter();
				$end   = $now->copy()->endOfQuarter();
				break;

			case 'last_quarter':
				$start = $now->copy()->subQuarter()->startOfQuarter();
				$end   = $now->copy()->subQuarter()->endOfQuarter();
				break;

			case 'this_year':
				$start = $now->copy()->startOfYear();
				$end   = $now->copy()->endOfYear();
				break;

			case 'last_year':
				$start = $now->copy()->subYear()->startOfYear();
				$end   = $now->copy()->subYear()->endOfYear();
				break;

			case 'last_7_days':
				$start = $now->copy()->subDays( 7 )->startOfDay();
				$end   = $now->copy()->endOfDay();
				break;

			case 'last_30_days':
				$start = $now->copy()->subDays( 30 )->startOfDay();
				$end   = $now->copy()->endOfDay();
				break;

			case 'last_90_days':
				$start = $now->copy()->subDays( 90 )->startOfDay();
				$end   = $now->copy()->endOfDay();
				break;

			case 'last_365_days':
				$start = $now->copy()->subDays( 365 )->startOfDay();
				$end   = $now->copy()->endOfDay();
				break;

			case 'next_7_days':
				$start = $now->copy()->startOfDay();
				$end   = $now->copy()->addDays( 7 )->endOfDay();
				break;

			case 'next_30_days':
				$start = $now->copy()->startOfDay();
				$end   = $now->copy()->addDays( 30 )->endOfDay();
				break;

			case 'next_90_days':
				$start = $now->copy()->startOfDay();
				$end   = $now->copy()->addDays( 90 )->endOfDay();
				break;

			case 'year_to_date':
				$start = $now->copy()->startOfYear();
				$end   = $now->copy()->endOfDay();
				break;

			case 'month_to_date':
				$start = $now->copy()->startOfMonth();
				$end   = $now->copy()->endOfDay();
				break;

			case 'week_to_date':
				$start = $now->copy()->startOfWeek();
				$end   = $now->copy()->endOfDay();
				break;

			default:
				throw new InvalidArgumentException( "Unsupported range: {$range}" );
		}

		return [
			'start' => $start->setTimezone( 'UTC' )->format( $format ),
			'end'   => $end->setTimezone( 'UTC' )->format( $format )
		];
	}

	/**
	 * Get dates between two dates
	 *
	 * @param string $start_utc Start UTC datetime
	 * @param string $end_utc   End UTC datetime
	 * @param string $interval  Interval (day, week, month)
	 * @param string $format    Output format
	 *
	 * @return array<string> Array of formatted dates
	 * @throws InvalidArgumentException If invalid interval or dates
	 */
	public static function between( string $start_utc, string $end_utc, string $interval = 'day', string $format = 'Y-m-d' ): array {
		$start = Carbon::parse( $start_utc, 'UTC' );
		$end   = Carbon::parse( $end_utc, 'UTC' );

		if ( $start->gt( $end ) ) {
			throw new InvalidArgumentException( 'Start date must be before end date' );
		}

		$dates   = [];
		$current = $start->copy();

		while ( $current->lte( $end ) ) {
			$dates[] = $current->format( $format );

			switch ( $interval ) {
				case 'day':
					$current->addDay();
					break;
				case 'week':
					$current->addWeek();
					break;
				case 'month':
					$current->addMonth();
					break;
				case 'year':
					$current->addYear();
					break;
				default:
					throw new InvalidArgumentException( "Invalid interval: {$interval}" );
			}
		}

		return $dates;
	}

	/**
	 * Get today's boundaries in LOCAL timezone
	 *
	 * @param string $format Output format
	 *
	 * @return array{start: string, end: string} Local start/end of today
	 */
	public static function today_local( string $format = 'Y-m-d H:i:s' ): array {
		$now = Carbon::now( wp_timezone() );

		return [
			'start' => $now->copy()->startOfDay()->format( $format ),
			'end'   => $now->copy()->endOfDay()->format( $format )
		];
	}

	/**
	 * Get today's boundaries in UTC
	 *
	 * @param string $format Output format
	 *
	 * @return array{start: string, end: string} UTC start/end of today
	 */
	public static function today_utc( string $format = 'Y-m-d H:i:s' ): array {
		$local = self::today_local();

		return [
			'start' => Date::to_utc( $local['start'], $format ),
			'end'   => Date::to_utc( $local['end'], $format )
		];
	}

	/**
	 * Convert local date range to UTC range
	 *
	 * @param string $start_local Local start date
	 * @param string $end_local   Local end date
	 * @param string $format      Output format
	 *
	 * @return array{start: string, end: string} UTC range
	 */
	public static function local_to_utc( string $start_local, string $end_local, string $format = 'Y-m-d H:i:s' ): array {
		return [
			'start' => Date::to_utc( $start_local, $format ),
			'end'   => Date::to_utc( $end_local, $format )
		];
	}

	/**
	 * Get period boundaries
	 *
	 * @param string      $period        Period identifier (day, week, month, quarter, year)
	 * @param string|null $reference_utc Reference UTC datetime
	 * @param string      $format        Optional datetime format
	 *
	 * @return array{start: string, end: string}
	 * @throws InvalidArgumentException If period is invalid
	 */
	public static function period_boundaries(
		string $period,
		?string $reference_utc = null,
		string $format = 'Y-m-d H:i:s'
	): array {
		$date = $reference_utc ? Carbon::parse( $reference_utc, 'UTC' ) : Carbon::now( 'UTC' );

		switch ( $period ) {
			case 'day':
				$start = $date->copy()->startOfDay();
				$end   = $date->copy()->endOfDay();
				break;
			case 'week':
				$start = $date->copy()->startOfWeek();
				$end   = $date->copy()->endOfWeek();
				break;
			case 'month':
				$start = $date->copy()->startOfMonth();
				$end   = $date->copy()->endOfMonth();
				break;
			case 'quarter':
				$start = $date->copy()->startOfQuarter();
				$end   = $date->copy()->endOfQuarter();
				break;
			case 'year':
				$start = $date->copy()->startOfYear();
				$end   = $date->copy()->endOfYear();
				break;
			default:
				throw new InvalidArgumentException( "Invalid period: {$period}" );
		}

		return [
			'start' => $start->format( $format ),
			'end'   => $end->format( $format )
		];
	}

	/**
	 * Set date to start of period
	 *
	 * @param string $utc_time UTC datetime
	 * @param string $period   Period type (minute, hour, day, week, month, quarter, year)
	 * @param string $format   Optional datetime format
	 *
	 * @return string Modified UTC datetime
	 * @throws InvalidArgumentException If period is invalid
	 */
	public static function start_of( string $utc_time, string $period, string $format = 'Y-m-d H:i:s' ): string {
		$date = Carbon::parse( $utc_time, 'UTC' );

		switch ( $period ) {
			case 'minute':
				$date->startOfMinute();
				break;
			case 'hour':
				$date->startOfHour();
				break;
			case 'day':
				$date->startOfDay();
				break;
			case 'week':
				$date->startOfWeek();
				break;
			case 'month':
				$date->startOfMonth();
				break;
			case 'quarter':
				$date->startOfQuarter();
				break;
			case 'year':
				$date->startOfYear();
				break;
			default:
				throw new InvalidArgumentException( "Invalid period: {$period}" );
		}

		return $date->format( $format );
	}

	/**
	 * Set date to end of period
	 *
	 * @param string $utc_time UTC datetime
	 * @param string $period   Period type (minute, hour, day, week, month, quarter, year)
	 * @param string $format   Optional datetime format
	 *
	 * @return string Modified UTC datetime
	 * @throws InvalidArgumentException If period is invalid
	 */
	public static function end_of( string $utc_time, string $period, string $format = 'Y-m-d H:i:s' ): string {
		$date = Carbon::parse( $utc_time, 'UTC' );

		switch ( $period ) {
			case 'minute':
				$date->endOfMinute();
				break;
			case 'hour':
				$date->endOfHour();
				break;
			case 'day':
				$date->endOfDay();
				break;
			case 'week':
				$date->endOfWeek();
				break;
			case 'month':
				$date->endOfMonth();
				break;
			case 'quarter':
				$date->endOfQuarter();
				break;
			case 'year':
				$date->endOfYear();
				break;
			default:
				throw new InvalidArgumentException( "Invalid period: {$period}" );
		}

		return $date->format( $format );
	}

}