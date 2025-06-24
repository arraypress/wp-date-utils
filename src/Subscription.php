<?php
/**
 * Subscription Date Utility Class
 *
 * Provides subscription-specific date calculations including renewal dates,
 * trial periods, grace periods, and billing cycles.
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

class Subscription {

	/**
	 * Get subscription period types
	 *
	 * @param string $context Optional context for filtering
	 *
	 * @return array<string, string> List of period identifiers and labels
	 */
	public static function get_period_types( string $context = 'default' ): array {
		$periods = [
			'daily'     => __( 'Daily', 'arraypress' ),
			'weekly'    => __( 'Weekly', 'arraypress' ),
			'monthly'   => __( 'Monthly', 'arraypress' ),
			'quarterly' => __( 'Quarterly', 'arraypress' ),
			'biannual'  => __( 'Biannual', 'arraypress' ),
			'yearly'    => __( 'Yearly', 'arraypress' )
		];

		return apply_filters( 'arraypress_subscription_periods', $periods, $context );
	}

	/**
	 * Get available subscription periods with labels
	 *
	 * @param string $context Optional context for filtering
	 *
	 * @return array<string, string> List of period identifiers and labels
	 */
	public static function get_periods( string $context = 'default' ): array {
		return self::get_period_types( $context );
	}

	/**
	 * Get subscription period keys only
	 *
	 * @param string $context Optional context for filtering
	 *
	 * @return array<string> List of period identifiers
	 */
	public static function get_period_keys( string $context = 'default' ): array {
		return array_keys( self::get_period_types( $context ) );
	}

	/**
	 * Get subscription period options formatted for REST API/dropdowns
	 *
	 * @param string $context Optional context for filtering
	 *
	 * @return array Array of options with value/label structure
	 */
	public static function get_period_options( string $context = 'default' ): array {
		$periods = self::get_period_types( $context );
		$options = [];

		foreach ( $periods as $value => $label ) {
			$options[] = [
				'value' => $value,
				'label' => $label
			];
		}

		return $options;
	}

	/**
	 * Get renewal date based on start date and period
	 *
	 * @param string $start_utc Start date in UTC
	 * @param string $period    Period type (daily, weekly, monthly, quarterly, biannual, yearly)
	 * @param string $format    Optional datetime format
	 *
	 * @return string Renewal date in UTC
	 * @throws InvalidArgumentException If period is invalid
	 */
	public static function get_renewal_date(
		string $start_utc,
		string $period,
		string $format = 'Y-m-d H:i:s'
	): string {
		$periods = self::get_period_types();
		if ( ! isset( $periods[ $period ] ) ) {
			throw new InvalidArgumentException( "Invalid subscription period: {$period}" );
		}

		$start = Carbon::parse( $start_utc, 'UTC' );

		switch ( $period ) {
			case 'daily':
				$start->addDay();
				break;
			case 'weekly':
				$start->addWeek();
				break;
			case 'monthly':
				$start->addMonth();
				break;
			case 'quarterly':
				$start->addMonths( 3 );
				break;
			case 'biannual':
				$start->addMonths( 6 );
				break;
			case 'yearly':
				$start->addYear();
				break;
		}

		return $start->format( $format );
	}

	/**
	 * Get trial period dates
	 *
	 * @param string $start_utc Start date in UTC
	 * @param int    $days      Trial duration in days
	 * @param string $format    Optional datetime format
	 *
	 * @return array{start: string, end: string} Trial period dates
	 * @throws InvalidArgumentException If days is not positive
	 */
	public static function get_trial_dates(
		string $start_utc,
		int $days,
		string $format = 'Y-m-d H:i:s'
	): array {
		if ( $days <= 0 ) {
			throw new InvalidArgumentException( 'Trial days must be positive' );
		}

		$start = Carbon::parse( $start_utc, 'UTC' );
		$end   = $start->copy()->addDays( $days )->endOfDay();

		return [
			'start' => $start->format( $format ),
			'end'   => $end->format( $format )
		];
	}

	/**
	 * Get grace period end date
	 *
	 * @param string $expires_utc Expiration date in UTC
	 * @param int    $days        Grace period in days
	 * @param string $format      Optional datetime format
	 *
	 * @return string Grace period end date in UTC
	 * @throws InvalidArgumentException If days is not positive
	 */
	public static function get_grace_period_end(
		string $expires_utc,
		int $days,
		string $format = 'Y-m-d H:i:s'
	): string {
		if ( $days <= 0 ) {
			throw new InvalidArgumentException( 'Grace period days must be positive' );
		}

		return Carbon::parse( $expires_utc, 'UTC' )
		             ->addDays( $days )
		             ->endOfDay()
		             ->format( $format );
	}

	/**
	 * Check if date is within grace period
	 *
	 * @param string $expires_utc Expiration date in UTC
	 * @param int    $grace_days  Grace period in days
	 *
	 * @return bool True if within grace period
	 */
	public static function is_in_grace_period( string $expires_utc, int $grace_days ): bool {
		if ( $grace_days <= 0 ) {
			return false;
		}

		$expiration = Carbon::parse( $expires_utc, 'UTC' );
		$grace_end  = $expiration->copy()->addDays( $grace_days )->endOfDay();
		$now        = Carbon::now( 'UTC' );

		return $now->isAfter( $expiration ) && $now->isBefore( $grace_end );
	}

	/**
	 * Get next billing date (alias for get_renewal_date)
	 *
	 * @param string $last_payment_utc Last payment date in UTC
	 * @param string $period           Billing period
	 * @param string $format           Optional datetime format
	 *
	 * @return string Next billing date in UTC
	 */
	public static function get_next_billing_date(
		string $last_payment_utc,
		string $period,
		string $format = 'Y-m-d H:i:s'
	): string {
		return self::get_renewal_date( $last_payment_utc, $period, $format );
	}

	/**
	 * Check if subscription is expired
	 *
	 * @param string   $expires_utc Expiration date in UTC
	 * @param int|null $grace_days  Optional grace period in days
	 *
	 * @return bool True if expired (including grace period if specified)
	 */
	public static function is_expired( string $expires_utc, ?int $grace_days = null ): bool {
		if ( $grace_days !== null && $grace_days > 0 ) {
			return ! self::is_in_grace_period( $expires_utc, $grace_days );
		}

		return Carbon::parse( $expires_utc, 'UTC' )->isPast();
	}

	/**
	 * Get subscription status details
	 *
	 * @param string   $expires_utc Expiration date in UTC
	 * @param int|null $grace_days  Optional grace period in days
	 *
	 * @return array{active: bool, in_grace: bool, expired: bool, status: string}
	 */
	public static function get_status( string $expires_utc, ?int $grace_days = null ): array {
		$expiration = Carbon::parse( $expires_utc, 'UTC' );
		$now        = Carbon::now( 'UTC' );

		$expired  = $expiration->isPast();
		$in_grace = false;

		if ( $expired && $grace_days !== null && $grace_days > 0 ) {
			$in_grace = self::is_in_grace_period( $expires_utc, $grace_days );
		}

		return [
			'active'   => ! $expired || $in_grace,
			'in_grace' => $in_grace,
			'expired'  => $expired && ! $in_grace,
			'status'   => $expired ? ( $in_grace ? 'grace' : 'expired' ) : 'active'
		];
	}

	/**
	 * Get trial end date
	 *
	 * @param string $start_utc Start date in UTC
	 * @param int    $days      Trial duration in days
	 * @param string $format    Optional datetime format
	 *
	 * @return string Trial end date in UTC
	 * @throws InvalidArgumentException If days is not positive
	 */
	public static function get_trial_end_date( string $start_utc, int $days, string $format = 'Y-m-d H:i:s' ): string {
		if ( $days <= 0 ) {
			throw new InvalidArgumentException( 'Trial days must be positive' );
		}

		return Carbon::parse( $start_utc, 'UTC' )
		             ->addDays( $days )
		             ->endOfDay()
		             ->format( $format );
	}

	/**
	 * Get renewal reminder date
	 *
	 * @param string $expires_utc Expiration date in UTC
	 * @param int    $days_before Days before expiration
	 * @param string $format      Optional datetime format
	 *
	 * @return string Reminder date in UTC
	 * @throws InvalidArgumentException If days_before is not positive
	 */
	public static function get_renewal_reminder_date( string $expires_utc, int $days_before, string $format = 'Y-m-d H:i:s' ): string {
		if ( $days_before <= 0 ) {
			throw new InvalidArgumentException( 'Days before expiration must be positive' );
		}

		return Carbon::parse( $expires_utc, 'UTC' )
		             ->subDays( $days_before )
		             ->startOfDay()
		             ->format( $format );
	}

	/**
	 * Check if subscription is in trial period
	 *
	 * @param string      $start_utc   Trial start date in UTC
	 * @param int         $trial_days  Trial duration in days
	 * @param string|null $current_utc Optional current date in UTC (default: now)
	 *
	 * @return bool True if subscription is in trial period
	 */
	public static function is_in_trial( string $start_utc, int $trial_days, ?string $current_utc = null ): bool {
		if ( $trial_days <= 0 ) {
			return false;
		}

		$start = Carbon::parse( $start_utc, 'UTC' );
		$end   = $start->copy()->addDays( $trial_days )->endOfDay();
		$now   = $current_utc ? Carbon::parse( $current_utc, 'UTC' ) : Carbon::now( 'UTC' );

		return $now->between( $start, $end );
	}

	/**
	 * Check if subscription is active
	 *
	 * @param string      $expires_utc Expiration date in UTC
	 * @param int|null    $grace_days  Optional grace period in days
	 * @param string|null $trial_end   Optional trial end date in UTC
	 *
	 * @return bool True if subscription is active
	 */
	public static function is_active( string $expires_utc, ?int $grace_days = null, ?string $trial_end = null ): bool {
		$now = Carbon::now( 'UTC' );

		// Check if in trial period
		if ( $trial_end && $now->lte( Carbon::parse( $trial_end, 'UTC' ) ) ) {
			return true;
		}

		// Check if within normal subscription period
		if ( $now->lte( Carbon::parse( $expires_utc, 'UTC' ) ) ) {
			return true;
		}

		// Check if in grace period
		if ( $grace_days !== null && $grace_days > 0 ) {
			return self::is_in_grace_period( $expires_utc, $grace_days );
		}

		return false;
	}

	/**
	 * Check if subscription needs renewal
	 *
	 * @param string   $expires_utc Expiration date in UTC
	 * @param int      $remind_days Days before expiration to start reminding
	 * @param int|null $grace_days  Optional grace period in days
	 *
	 * @return bool True if subscription needs renewal
	 */
	public static function needs_renewal( string $expires_utc, int $remind_days = 7, ?int $grace_days = null ): bool {
		$now        = Carbon::now( 'UTC' );
		$expiration = Carbon::parse( $expires_utc, 'UTC' );

		// Already expired
		if ( $expiration->isPast() ) {
			// If in grace period, needs renewal
			if ( $grace_days && self::is_in_grace_period( $expires_utc, $grace_days ) ) {
				return true;
			}

			return true;
		}

		// Check if within reminder period
		$reminder_start = $expiration->copy()->subDays( $remind_days )->startOfDay();

		return $now->gte( $reminder_start );
	}

	/**
	 * Get next occurrences of subscription renewals
	 *
	 * @param string      $period   Period type (daily, weekly, monthly, yearly)
	 * @param int         $count    Number of occurrences to get
	 * @param string|null $from_utc Starting UTC date (default: now)
	 *
	 * @return array<string> Array of upcoming renewal dates
	 */
	public static function get_next_occurrences( string $period, int $count = 5, ?string $from_utc = null ): array {
		$dates   = [];
		$current = $from_utc ? Carbon::parse( $from_utc, 'UTC' ) : Carbon::now( 'UTC' );

		for ( $i = 0; $i < $count; $i ++ ) {
			$current = $current->copy();

			switch ( $period ) {
				case 'daily':
					$current->addDay();
					break;
				case 'weekly':
					$current->addWeek();
					break;
				case 'monthly':
					$current->addMonth();
					break;
				case 'quarterly':
					$current->addMonths( 3 );
					break;
				case 'biannual':
					$current->addMonths( 6 );
					break;
				case 'yearly':
					$current->addYear();
					break;
				default:
					throw new InvalidArgumentException( "Invalid period: {$period}" );
			}

			$dates[] = $current->format( 'Y-m-d H:i:s' );
		}

		return $dates;
	}

}