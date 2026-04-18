<?php
/**
 * Bundled demo images + fallbacks when Media Library / CPTs are empty.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

/**
 * Public URL for a file under the theme assets/images directory.
 *
 * @param string $relative_path e.g. "demo/hero-01.jpg".
 * @return string
 */
function kmr_theme_img_url( string $relative_path ): string {
	$relative_path = ltrim( str_replace( '\\', '/', $relative_path ), '/' );
	return trailingslashit( KMR_URI ) . 'assets/images/' . $relative_path;
}

/**
 * Whether to show bundled demos when content is missing (filterable).
 */
function kmr_use_demo_assets(): bool {
	/**
	 * Set to false to hide demo hero/gallery/rooms until real content exists.
	 *
	 * @param bool $use Whether demos are allowed.
	 */
	return (bool) apply_filters( 'kmr_use_demo_assets', true );
}

/**
 * Hero background URLs from theme demo folder.
 *
 * @return string[]
 */
function kmr_demo_hero_image_urls(): array {
	$files = [ 'demo/hero-01.jpg', 'demo/hero-02.jpg', 'demo/hero-03.jpg' ];
	$out   = [];
	foreach ( $files as $f ) {
		$out[] = kmr_theme_img_url( $f );
	}
	return $out;
}

/**
 * Resolve hero slideshow: Resort Home attachment IDs, else bundled demos.
 *
 * @return string[] Image URLs.
 */
function kmr_resolve_hero_image_urls(): array {
	$ids  = kmr_parse_id_list( (string) kmr_get_option( 'hero_background_ids', '' ) );
	$urls = [];
	foreach ( $ids as $aid ) {
		$u = kmr_image_url( $aid, 'full' );
		if ( $u ) {
			$urls[] = $u;
		}
	}
	if ( count( $urls ) || ! kmr_use_demo_assets() ) {
		return $urls;
	}
	return kmr_demo_hero_image_urls();
}

/**
 * Gallery items for lightbox + grid (demo).
 *
 * @return array<int, array{id:int,src:string,alt:string,caption:string}>
 */
function kmr_demo_gallery_items(): array {
	$data = [
		[ 'file' => 'gallery-01.jpg', 'caption' => __( 'Courtyard light', 'kana-mud-resort' ) ],
		[ 'file' => 'gallery-02.jpg', 'caption' => __( 'Forest trail', 'kana-mud-resort' ) ],
		[ 'file' => 'gallery-03.jpg', 'caption' => __( 'Evening calm', 'kana-mud-resort' ) ],
		[ 'file' => 'hero-01.jpg', 'caption' => __( 'Hillside view', 'kana-mud-resort' ) ],
		[ 'file' => 'card-01.jpg', 'caption' => __( 'Room detail', 'kana-mud-resort' ) ],
		[ 'file' => 'card-02.jpg', 'caption' => __( 'Quiet corner', 'kana-mud-resort' ) ],
	];
	$out = [];
	$i   = 0;
	foreach ( $data as $row ) {
		++$i;
		$src   = kmr_theme_img_url( 'demo/' . $row['file'] );
		$cap   = $row['caption'];
		$out[] = [
			'id'      => -$i,
			'src'     => $src,
			'alt'     => $cap,
			'caption' => $cap,
		];
	}
	return $out;
}

/**
 * Demo room rows matching structure built in section-rooms.php.
 *
 * @return array<int, array<string, mixed>>
 */
function kmr_demo_room_rows(): array {
	$c1 = kmr_theme_img_url( 'demo/card-01.jpg' );
	$c2 = kmr_theme_img_url( 'demo/card-02.jpg' );
	$h1 = kmr_theme_img_url( 'demo/hero-01.jpg' );

	return [
		[
			'demo'           => true,
			'post'           => null,
			'title'          => __( 'Mud cottage — forest view', 'kana-mud-resort' ),
			'excerpt'        => __( 'Earthy walls, soft daylight, and a private sit-out facing the ridge.', 'kana-mud-resort' ),
			'content'        => '',
			'price_label'    => __( 'From ₹12,000 / night', 'kana-mud-resort' ),
			'orig'           => null,
			'disc'           => null,
			'original_num'   => null,
			'discounted_n'   => null,
			'has_discount'   => false,
			'discount_pct'   => 0,
			'capacity'       => 3,
			'image_urls'     => [ $c1, $c2 ],
			'eff_price'      => null,
		],
		[
			'demo'           => true,
			'post'           => null,
			'title'          => __( 'Garden room', 'kana-mud-resort' ),
			'excerpt'        => __( 'A slower rhythm—reading nooks, linen textures, and birdsong through the window.', 'kana-mud-resort' ),
			'content'        => '',
			'price_label'    => __( 'From ₹9,500 / night', 'kana-mud-resort' ),
			'orig'           => null,
			'disc'           => null,
			'original_num'   => null,
			'discounted_n'   => null,
			'has_discount'   => false,
			'discount_pct'   => 0,
			'capacity'       => 2,
			'image_urls'     => [ $c2, $h1 ],
			'eff_price'      => null,
		],
	];
}
