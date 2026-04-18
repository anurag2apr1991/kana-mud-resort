<?php
/**
 * Kana Mud Resort theme bootstrap.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

define( 'KMR_VERSION', '1.0.11' );
define( 'KMR_DIR', get_template_directory() );
define( 'KMR_URI', get_template_directory_uri() );

require KMR_DIR . '/inc/php-compat.php';
require KMR_DIR . '/inc/helpers.php';
require KMR_DIR . '/inc/demo-assets.php';
require KMR_DIR . '/inc/cpt.php';
require KMR_DIR . '/inc/meta-boxes.php';
require KMR_DIR . '/inc/admin-site-hub.php';
require KMR_DIR . '/inc/admin-options.php';
require KMR_DIR . '/inc/admin-dashboard-widget.php';
require KMR_DIR . '/inc/admin-hosting-notice.php';

add_action( 'after_setup_theme', 'kmr_setup' );

/**
 * Theme setup.
 */
function kmr_setup(): void {
	load_theme_textdomain( 'kana-mud-resort', KMR_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		[
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		]
	);
}

add_action( 'wp_enqueue_scripts', 'kmr_enqueue_assets' );

/**
 * Styles and scripts.
 */
function kmr_enqueue_assets(): void {
	wp_enqueue_style(
		'kmr-google-fonts',
		'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&display=swap',
		[],
		null
	);
	wp_enqueue_style( 'kmr-main', KMR_URI . '/assets/css/main.css', [], KMR_VERSION );
	wp_enqueue_script( 'kmr-main', KMR_URI . '/assets/js/main.js', [], KMR_VERSION, true );
}

add_action( 'wp_head', 'kmr_output_json_ld', 5 );

/**
 * LodgingBusiness JSON-LD for SEO.
 */
function kmr_output_json_ld(): void {
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}
	$hero_title = kmr_get_option( 'hero_title', 'Kana Mud Resort' );
	$hero_sub   = kmr_get_option( 'hero_subtitle', '' );
	$addr       = kmr_get_option( 'contact_address', '' );
	$email      = kmr_get_option( 'contact_email', '' );
	$phone      = kmr_get_option( 'contact_phone', '' );

	$data = [
		'@context' => 'https://schema.org',
		'@type'    => 'LodgingBusiness',
		'name'     => $hero_title,
	];
	if ( $hero_sub ) {
		$data['description'] = $hero_sub;
	}
	if ( $addr ) {
		$data['address'] = [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $addr,
		];
	}
	if ( $email ) {
		$data['email'] = $email;
	}
	if ( $phone ) {
		$data['telephone'] = $phone;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

add_filter( 'document_title_separator', static fn() => '|' );

add_filter(
	'body_class',
	static function ( array $classes ): array {
		if ( ! is_front_page() && ! is_home() ) {
			return $classes;
		}
		if ( ! kmr_use_demo_assets() ) {
			return $classes;
		}
		$hero = kmr_parse_id_list( (string) kmr_get_option( 'hero_background_ids', '' ) );
		if ( ! count( $hero ) ) {
			$classes[] = 'kmr-demo-hero';
		}
		if ( ! count( get_posts( [ 'post_type' => 'kmr_photo', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ] ) ) ) {
			$classes[] = 'kmr-demo-gallery';
		}
		if ( ! count( get_posts( [ 'post_type' => 'kmr_room', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ] ) ) ) {
			$classes[] = 'kmr-demo-rooms';
		}
		return $classes;
	}
);

add_action(
	'wp_head',
	static function () {
		echo '<link rel="icon" href="' . esc_url( KMR_URI . '/assets/images/icon.svg' ) . '" type="image/svg+xml" />' . "\n";
	},
	2
);
