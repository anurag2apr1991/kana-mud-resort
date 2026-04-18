<?php
/**
 * Polyfills for older PHP on some shared hosts (e.g. if core compat did not load yet).
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'str_contains' ) ) {
	/**
	 * @param string $haystack Haystack.
	 * @param string $needle Needle.
	 */
	function str_contains( $haystack, $needle ) {
		return $needle !== '' && strpos( $haystack, $needle ) !== false;
	}
}

if ( ! function_exists( 'str_starts_with' ) ) {
	/**
	 * @param string $haystack Haystack.
	 * @param string $needle Needle.
	 */
	function str_starts_with( $haystack, $needle ) {
		return $needle === '' || strncmp( $haystack, $needle, strlen( $needle ) ) === 0;
	}
}
