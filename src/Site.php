<?php
/**
 * The site's own clock and formats.
 *
 * @package   ArrayPress\Dates
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     2.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Dates;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Class Site
 *
 * Everything else in this library works in UTC, which is the only sane way to
 * store a moment. This is the layer that turns one into what a person in the
 * admin should see.
 *
 * Two WordPress facts drive it. A site has a timezone that is not the
 * server's, and it has date and time formats an administrator chose. Reading
 * either from PHP's defaults gives the wrong answer on most installs.
 *
 * @since 2.0.0
 */
final class Site {

	/**
	 * The site's timezone.
	 *
	 * @since 2.0.0
	 *
	 * @return DateTimeZone
	 */
	public static function timezone(): DateTimeZone {
		// wp_timezone() has been in core since 5.3 and handles both ways a
		// site can be configured: a named zone, or a bare UTC offset.
		return wp_timezone();
	}

	/**
	 * A stored UTC value as a DateTimeImmutable in the site's timezone.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value A UTC datetime, as stored.
	 *
	 * @return DateTimeImmutable|null Null when the value cannot be read.
	 */
	public static function local( string $value ): ?DateTimeImmutable {
		$parsed = Timestamp::parse( $value );

		return $parsed?->setTimezone( self::timezone() );
	}

	/**
	 * A stored UTC value, rendered the way this site renders dates.
	 *
	 * Uses date_i18n(), so the month and day names are translated -- which
	 * PHP's own format() will not do.
	 *
	 * @since 2.0.0
	 *
	 * @param string      $value  A UTC datetime, as stored.
	 * @param string|null $format A date format, or null for the site's.
	 *
	 * @return string Empty when the value cannot be read.
	 */
	public static function format( string $value, ?string $format = null ): string {
		$local = self::local( $value );

		if ( null === $local ) {
			return '';
		}

		$format ??= (string) get_option( 'date_format', 'F j, Y' );

		return date_i18n( $format, $local->getTimestamp() + $local->getOffset() );
	}

	/**
	 * A stored UTC value as date and time together, in the site's formats.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value A UTC datetime, as stored.
	 *
	 * @return string
	 */
	public static function format_datetime( string $value ): string {
		$date = (string) get_option( 'date_format', 'F j, Y' );
		$time = (string) get_option( 'time_format', 'g:i a' );

		return self::format( $value, $date . ' ' . $time );
	}

	/**
	 * "3 hours ago", or "in 2 days".
	 *
	 * @since 2.0.0
	 *
	 * @param string     $value A UTC datetime, as stored.
	 * @param Clock|null $clock Fixed time, for tests.
	 *
	 * @return string
	 */
	public static function relative( string $value, ?Clock $clock = null ): string {
		return Relative::format( $value, $clock );
	}

	/**
	 * A relative phrase with the exact moment as its title attribute.
	 *
	 * The pair a list table wants: "3 hours ago" to read, and the real
	 * timestamp on hover for anyone who needs it.
	 *
	 * @since 2.0.0
	 *
	 * @param string     $value A UTC datetime, as stored.
	 * @param Clock|null $clock Fixed time, for tests.
	 *
	 * @return array{text: string, title: string}
	 */
	public static function relative_with_exact( string $value, ?Clock $clock = null ): array {
		return array(
			'text'  => self::relative( $value, $clock ),
			'title' => self::format_datetime( $value ),
		);
	}
}
