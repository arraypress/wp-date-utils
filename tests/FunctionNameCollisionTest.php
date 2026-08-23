<?php
/**
 * Global function names must not collide with common data literals.
 *
 * @package ArrayPress\DateUtils
 */

declare( strict_types=1 );

namespace ArrayPress\DateUtils\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Strauss prefixes global function names by rewriting the token wherever it
 * appears — including inside string literals, because it cannot tell a
 * function reference from data. A generic global function name therefore
 * corrupts any package prefixed alongside this one that uses the same word as
 * an array key.
 *
 * This is not theoretical: `date_range()` rewrote wp-register-post-fields'
 * `'date_range'` field type to `'apfd_date_range'`, and the metabox stopped
 * registering with "Invalid field type". Renamed to `get_date_range()`.
 */
final class FunctionNameCollisionTest extends TestCase {

	/**
	 * Words this library must never declare as a bare global function,
	 * because consumers use them as field types or config keys.
	 *
	 * @var string[]
	 */
	private const RESERVED = [
		'date_range', 'time_range', 'date', 'time', 'datetime', 'range',
		'number', 'text', 'select', 'checkbox', 'toggle', 'color', 'email',
		'url', 'file', 'image', 'gallery', 'link', 'group', 'repeater',
	];

	/**
	 * Every global function this library declares.
	 *
	 * @return string[]
	 */
	private function declared_functions(): array {
		$source = (string) file_get_contents( dirname( __DIR__ ) . '/src/Functions.php' );

		preg_match_all( '/^\t*function ([a-z_]+)\s*\(/m', $source, $matches );

		$this->assertNotEmpty( $matches[1], 'No global functions found to check.' );

		return $matches[1];
	}

	/**
	 * No declared function may use a reserved word.
	 */
	public function test_no_function_collides_with_a_reserved_literal(): void {
		$collisions = array_intersect( $this->declared_functions(), self::RESERVED );

		$this->assertSame(
			[],
			array_values( $collisions ),
			sprintf(
				"These global function names are also used as array keys by consuming libraries.\n"
				. "Strauss rewrites the literal as well as the call, silently corrupting their config: %s",
				implode( ', ', $collisions )
			)
		);
	}

	/**
	 * The guard name must match the declaration, or it answers false forever
	 * and the function is declared twice on a double include.
	 */
	public function test_every_guard_matches_its_declaration(): void {
		$source = (string) file_get_contents( dirname( __DIR__ ) . '/src/Functions.php' );

		preg_match_all(
			"/if \( ! function_exists\( '([a-z_]+)' \) \) \{\s*\n(?:.*?\n)*?\t*function ([a-z_]+)\s*\(/",
			$source,
			$matches,
			PREG_SET_ORDER
		);

		$this->assertNotEmpty( $matches, 'No guarded functions found.' );

		foreach ( $matches as $match ) {
			$this->assertSame(
				$match[2],
				$match[1],
				sprintf( 'Guard checks "%s" but declares "%s".', $match[1], $match[2] )
			);
		}
	}

}
