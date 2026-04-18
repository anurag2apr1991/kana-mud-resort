<?php
/**
 * Helpers for Kana Mud Resort theme.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param string $key Option key without kmr_options array prefix.
 * @param mixed  $default Default value.
 * @return mixed
 */
function kmr_get_option( string $key, $default = '' ) {
	$opts = get_option( 'kmr_options', [] );
	if ( ! is_array( $opts ) ) {
		return $default;
	}
	return isset( $opts[ $key ] ) ? $opts[ $key ] : $default;
}

/**
 * Trimmed theme option, or default if empty (for CMS-editable section copy).
 *
 * @param string $key Option key under kmr_options.
 * @param string $default Fallback when missing or blank.
 */
function kmr_text( string $key, string $default ): string {
	$v = kmr_get_option( $key, $default );
	if ( ! is_string( $v ) ) {
		return $default;
	}
	$v = trim( $v );
	return $v !== '' ? $v : $default;
}

/**
 * @param int|null $attachment_id Attachment ID.
 * @param string   $size Image size.
 * @return string URL or empty string.
 */
function kmr_image_url( ?int $attachment_id, string $size = 'large' ): string {
	if ( ! $attachment_id ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $attachment_id, $size );
	return $url ? $url : '';
}

/**
 * @param int|null $attachment_id Attachment ID.
 * @param string   $size Image size.
 * @param string   $alt Alt text.
 * @param string   $class Optional class.
 * @return string HTML img tag.
 */
function kmr_img_tag( ?int $attachment_id, string $size, string $alt, string $class = '' ): string {
	if ( ! $attachment_id ) {
		return '';
	}
	return wp_get_attachment_image(
		$attachment_id,
		$size,
		false,
		[
			'class' => trim( $class ),
			'alt'   => $alt,
		]
	);
}

/**
 * Default anchor nav when CMS menu is empty.
 *
 * @return array<int, array{label:string,href:string}>
 */
function kmr_default_nav(): array {
	return [
		[ 'href' => '#rooms', 'label' => __( 'Rooms', 'kana-mud-resort' ) ],
		[ 'href' => '#gallery', 'label' => __( 'Gallery', 'kana-mud-resort' ) ],
		[ 'href' => '#nearby', 'label' => __( 'Nearby', 'kana-mud-resort' ) ],
		[ 'href' => '#amenities', 'label' => __( 'Amenities', 'kana-mud-resort' ) ],
		[ 'href' => '#offers', 'label' => __( 'Offers', 'kana-mud-resort' ) ],
		[ 'href' => '#testimonials', 'label' => __( 'Guests', 'kana-mud-resort' ) ],
		[ 'href' => '#contact', 'label' => __( 'Contact', 'kana-mud-resort' ) ],
	];
}

/**
 * @param mixed $raw JSON string or array from options.
 * @return array<int, array{label:string,href:string}>
 */
function kmr_normalize_menu_links( $raw ): array {
	if ( empty( $raw ) ) {
		return [];
	}
	if ( is_string( $raw ) ) {
		$decoded = json_decode( $raw, true );
		$raw     = is_array( $decoded ) ? $decoded : [];
	}
	if ( ! is_array( $raw ) ) {
		return [];
	}
	$out = [];
	foreach ( $raw as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$label = '';
		$href  = '';
		foreach ( [ 'label', 'title', 'name', 'text' ] as $k ) {
			if ( ! empty( $item[ $k ] ) && is_string( $item[ $k ] ) ) {
				$label = trim( $item[ $k ] );
				break;
			}
		}
		foreach ( [ 'href', 'url', 'link', 'hash', 'path' ] as $k ) {
			if ( ! empty( $item[ $k ] ) && is_string( $item[ $k ] ) ) {
				$href = trim( $item[ $k ] );
				break;
			}
		}
		if ( $label && $href ) {
			$out[] = compact( 'label', 'href' );
		}
	}
	return $out;
}

/**
 * @param string|null $url Booking primary URL.
 * @return string Safe href for primary CTA.
 */
function kmr_booking_primary_href( ?string $url ): string {
	$t = $url ? trim( $url ) : '';
	if ( '' === $t || str_contains( $t, 'example.com' ) ) {
		return '#contact';
	}
	return $t;
}

/**
 * Escape href for anchors or absolute URLs (esc_url strips bare #fragments).
 *
 * @param string $url URL or hash.
 * @return string
 */
function kmr_esc_href( string $url ): string {
	$u = trim( $url );
	if ( '' === $u ) {
		return '';
	}
	if ( str_starts_with( $u, '#' ) ) {
		return esc_attr( $u );
	}
	return esc_url( $u );
}

/**
 * Format integer as INR-style display (grouped with commas).
 *
 * @param int $value Value.
 * @return string
 */
function kmr_format_inr_amount( int $value ): string {
	return number_format_i18n( $value, 0 );
}

/**
 * Parse rupee amount from a label like "From ₹14,000 / night".
 *
 * @param string|null $label Label.
 * @return int|null
 */
function kmr_parse_rupee_from_price_label( ?string $label ): ?int {
	if ( ! $label ) {
		return null;
	}
	if ( preg_match( '/₹\s*([\d,]+)/u', $label, $m ) ) {
		$n = (int) str_replace( ',', '', $m[1] );
		return $n > 0 ? $n : null;
	}
	return null;
}

/**
 * @param mixed $v Raw meta value.
 * @return int|null
 */
function kmr_to_price( $v ): ?int {
	if ( $v === null || $v === '' ) {
		return null;
	}
	if ( is_numeric( $v ) ) {
		$n = (int) $v;
		return $n >= 0 ? $n : null;
	}
	if ( is_string( $v ) ) {
		$n = (int) str_replace( ',', '', $v );
		return $n >= 0 ? $n : null;
	}
	return null;
}

/**
 * Effective nightly price for sorting / display.
 *
 * @param int|null    $orig Original price.
 * @param int|null    $disc Discounted.
 * @param string|null $price_label Fallback label with ₹.
 * @return int|null
 */
function kmr_effective_price( ?int $orig, ?int $disc, ?string $price_label ): ?int {
	$o = $orig ?? kmr_parse_rupee_from_price_label( $price_label );
	$d = $disc;
	if ( $d !== null && $o !== null && $d < $o ) {
		return $d;
	}
	if ( $o !== null ) {
		return $o;
	}
	if ( $d !== null ) {
		return $d;
	}
	return null;
}

/**
 * Normalize a Google Maps URL for iframe src (embed-friendly).
 *
 * @param string|null $input Raw URL.
 * @return string|null Safe URL for iframe or null.
 */
function kmr_normalize_map_url( ?string $input ): ?string {
	if ( ! $input ) {
		return null;
	}
	$value = trim( $input );
	if ( '' === $value || str_contains( $value, '<' ) ) {
		return null;
	}
	if ( ! str_starts_with( $value, 'http' ) ) {
		return $value;
	}
	$parts = wp_parse_url( $value );
	if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
		return $value;
	}
	$host = $parts['host'];
	if ( str_contains( $host, 'google.' ) && str_contains( $parts['path'] ?? '', '/maps' ) ) {
		$query = [];
		if ( ! empty( $parts['query'] ) ) {
			wp_parse_str( $parts['query'], $query );
		}
		if ( ! empty( $query['q'] ) ) {
			return 'https://www.google.com/maps?q=' . rawurlencode( (string) $query['q'] ) . '&output=embed';
		}
		$sep = str_contains( $value, '?' ) ? '&' : '?';
		return $value . $sep . 'output=embed';
	}
	return $value;
}

/**
 * Attachment IDs from comma-separated string.
 *
 * @param string $csv Comma-separated IDs.
 * @return int[]
 */
function kmr_parse_id_list( string $csv ): array {
	$csv = trim( $csv );
	if ( '' === $csv ) {
		return [];
	}
	$parts = preg_split( '/\s*,\s*/', $csv );
	if ( ! is_array( $parts ) ) {
		return [];
	}
	$ids = array_map( 'intval', $parts );
	return array_values( array_filter( $ids, static fn( int $id ) => $id > 0 ) );
}

/**
 * Emoji icon for amenity icon key.
 *
 * @param string|null $icon_key Key.
 * @return string
 */
/**
 * Star row HTML for testimonials.
 *
 * @param int $n Rating 0-5.
 * @return string
 */
function kmr_stars_html( int $n ): string {
	$n    = min( 5, max( 0, $n ) );
	$on   = str_repeat( '★', $n );
	$off  = str_repeat( '★', 5 - $n );
	$aria = sprintf(
		/* translators: %d: star count */
		__( '%d out of 5 stars', 'kana-mud-resort' ),
		$n
	);
	return '<span class="text-amber-400" aria-label="' . esc_attr( $aria ) . '">' . esc_html( $on ) . '<span class="text-stone-300">' . esc_html( $off ) . '</span></span>';
}

function kmr_amenity_icon_glyph( ?string $icon_key ): string {
	$k = strtolower( (string) ( $icon_key ?: 'default' ) );
	$map = [
		'leaf'     => '🌿',
		'pool'     => '🏔️',
		'mountain' => '🏔️',
		'wifi'     => '📶',
		'food'     => '🍽️',
		'spa'      => '🧘',
		'car'      => '🚗',
		'coffee'   => '☕',
		'bed'      => '🛏️',
		'tree'     => '🌳',
		'star'     => '✨',
		'default'  => '✦',
	];
	return $map[ $k ] ?? $map['default'];
}
