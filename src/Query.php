<?php
/**
 * Query Builder Utility Class
 *
 * Builds WordPress database queries with automatic UTC conversion
 * and support for raw SQL, WP_Query, and meta queries.
 *
 * @package     ArrayPress\DateUtils
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\DateUtils;

class Query {

	/**
	 * Create date range WHERE clause for raw SQL queries
	 *
	 * @param string $column      Database column name
	 * @param string $start_local Local start datetime
	 * @param string $end_local   Local end datetime
	 *
	 * @return array{start_utc: string, end_utc: string, sql: string} Query parts
	 */
	public static function date_range( string $column, string $start_local, string $end_local ): array {
		return [
			'start_utc' => Date::to_utc( $start_local ),
			'end_utc'   => Date::to_utc( $end_local ),
			'sql'       => "{$column} BETWEEN %s AND %s"
		];
	}

	/**
	 * Create date range WHERE clause with UTC dates (no conversion)
	 *
	 * @param string $column    Database column name
	 * @param string $start_utc UTC start datetime
	 * @param string $end_utc   UTC end datetime
	 *
	 * @return array{start_utc: string, end_utc: string, sql: string} Query parts
	 */
	public static function date_range_utc( string $column, string $start_utc, string $end_utc ): array {
		return [
			'start_utc' => $start_utc,
			'end_utc'   => $end_utc,
			'sql'       => "{$column} BETWEEN %s AND %s"
		];
	}

	/**
	 * Create WP_Query meta query for date ranges
	 *
	 * @param string $meta_key    Meta key name
	 * @param string $start_local Local start datetime
	 * @param string $end_local   Local end datetime
	 *
	 * @return array WP_Query meta query array
	 */
	public static function meta_date_range( string $meta_key, string $start_local, string $end_local ): array {
		return [
			'key'     => $meta_key,
			'value'   => [
				Date::to_utc( $start_local ),
				Date::to_utc( $end_local )
			],
			'compare' => 'BETWEEN',
			'type'    => 'DATETIME'
		];
	}

	/**
	 * Create WP_Query meta query with UTC dates (no conversion)
	 *
	 * @param string $meta_key  Meta key name
	 * @param string $start_utc UTC start datetime
	 * @param string $end_utc   UTC end datetime
	 *
	 * @return array WP_Query meta query array
	 */
	public static function meta_date_range_utc( string $meta_key, string $start_utc, string $end_utc ): array {
		return [
			'key'     => $meta_key,
			'value'   => [ $start_utc, $end_utc ],
			'compare' => 'BETWEEN',
			'type'    => 'DATETIME'
		];
	}

	/**
	 * Create WP_Query date query for post dates
	 *
	 * @param string $start_local Local start datetime
	 * @param string $end_local   Local end datetime
	 *
	 * @return array WP_Query date query array
	 */
	public static function post_date_range( string $start_local, string $end_local ): array {
		return [
			'date_query' => [
				[
					'after'     => $start_local,
					'before'    => $end_local,
					'inclusive' => true
				]
			]
		];
	}

	/**
	 * Create meta query for single date comparison
	 *
	 * @param string $meta_key Meta key name
	 * @param string $date     Local datetime
	 * @param string $compare  Comparison operator (=, >, <, >=, <=)
	 *
	 * @return array WP_Query meta query array
	 */
	public static function meta_date_compare( string $meta_key, string $date, string $compare = '=' ): array {
		return [
			'key'     => $meta_key,
			'value'   => Date::to_utc( $date ),
			'compare' => $compare,
			'type'    => 'DATETIME'
		];
	}

	/**
	 * Create WHERE clause for single date comparison
	 *
	 * @param string $column  Database column name
	 * @param string $date    Local datetime
	 * @param string $compare Comparison operator (=, >, <, >=, <=)
	 *
	 * @return array{date_utc: string, sql: string} Query parts
	 */
	public static function date_compare( string $column, string $date, string $compare = '=' ): array {
		return [
			'date_utc' => Date::to_utc( $date ),
			'sql'      => "{$column} {$compare} %s"
		];
	}

	/**
	 * Build complete WP_Query args for date filtering
	 *
	 * @param array $args         Base WP_Query args
	 * @param array $date_filters Date filter configuration
	 *
	 * @return array Complete WP_Query args
	 */
	public static function build_wp_query( array $args, array $date_filters ): array {
		// Add meta queries
		if ( isset( $date_filters['meta'] ) ) {
			$meta_query = $args['meta_query'] ?? [];
			foreach ( $date_filters['meta'] as $meta_filter ) {
				$meta_query[] = self::meta_date_range(
					$meta_filter['key'],
					$meta_filter['start'],
					$meta_filter['end']
				);
			}
			$args['meta_query'] = $meta_query;
		}

		// Add date query for post dates
		if ( isset( $date_filters['post_date'] ) ) {
			$args = array_merge( $args, self::post_date_range(
				$date_filters['post_date']['start'],
				$date_filters['post_date']['end']
			) );
		}

		return $args;
	}

	/**
	 * Create date query for records within last X minutes.
	 *
	 * @param int    $minutes Number of minutes back to look.
	 * @param string $column  Optional. Column name.
	 *
	 * @return array Generic date query array.
	 */
	public static function since_minutes( int $minutes, string $column = 'date_created' ): array {
		if ( $minutes <= 0 ) {
			return [];
		}

		return [
			[
				'column' => $column,
				'after'  => gmdate( 'Y-m-d H:i:s', strtotime( "-{$minutes} minutes" ) )
			]
		];
	}

	/**
	 * Create date query for records since a time period ago.
	 *
	 * @param string $time_string Time string (e.g., '1 hour', '30 minutes', '2 days').
	 * @param string $column      Optional. Column name.
	 *
	 * @return array Generic date query array.
	 */
	public static function since( string $time_string, string $column = 'date_created' ): array {
		$timestamp = strtotime( "-{$time_string}" );
		if ( false === $timestamp ) {
			return [];
		}

		return [
			[
				'column' => $column,
				'after'  => gmdate( 'Y-m-d H:i:s', $timestamp )
			]
		];
	}

	/**
	 * Create date range query.
	 *
	 * @param string $start_utc Start UTC datetime.
	 * @param string $end_utc   End UTC datetime.
	 * @param string $column    Optional. Column name.
	 *
	 * @return array Generic date query array.
	 */
	public static function between( string $start_utc, string $end_utc, string $column = 'date_created' ): array {
		return [
			'relation' => 'AND',
			'column'   => $column,
			[
				'after'     => $start_utc,
				'inclusive' => true,
			],
			[
				'before'    => $end_utc,
				'inclusive' => true,
			]
		];
	}

}