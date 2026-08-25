<?php
/**
 * PHPUnit bootstrap.
 *
 * @package ArrayPress\Dates
 */

declare( strict_types=1 );

/**
 * Stubbed options.
 *
 * @var array<string, mixed>
 */
$GLOBALS['du_options'] = array(
	'date_format' => 'F j, Y',
	'time_format' => 'g:i a',
	'timezone'    => 'Europe/London',
);

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Read a stubbed option.
	 *
	 * @param string $name    Option name.
	 * @param mixed  $default Fallback.
	 *
	 * @return mixed
	 */
	function get_option( string $name, $default = false ) {
		return $GLOBALS['du_options'][ $name ] ?? $default;
	}
}

if ( ! function_exists( 'wp_timezone' ) ) {
	/**
	 * The site's timezone.
	 *
	 * @return DateTimeZone
	 */
	function wp_timezone(): DateTimeZone {
		return new DateTimeZone( $GLOBALS['du_options']['timezone'] ?? 'UTC' );
	}
}

if ( ! function_exists( 'date_i18n' ) ) {
	/**
	 * Core's translated date formatter.
	 *
	 * The stub does no translating -- there is nothing to translate to in a
	 * suite -- but it takes the same arguments, which is what matters: core
	 * expects a timestamp already shifted into the site's offset.
	 *
	 * @param string $format    Date format.
	 * @param int    $timestamp Shifted timestamp.
	 *
	 * @return string
	 */
	function date_i18n( string $format, int $timestamp ): string {
		return gmdate( $format, $timestamp );
	}
}

/**
 * Put the stubbed options back.
 *
 * @return void
 */
function du_reset_globals(): void {
	$GLOBALS['du_options'] = array(
		'date_format' => 'F j, Y',
		'time_format' => 'g:i a',
		'timezone'    => 'Europe/London',
	);
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
