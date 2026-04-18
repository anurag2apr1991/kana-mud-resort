<?php
/**
 * Custom post types.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'kmr_register_post_types' );

/**
 * Register resort content types.
 */
function kmr_register_post_types(): void {
	$rooms = [
		'labels'              => [
			'name'          => __( 'Rooms', 'kana-mud-resort' ),
			'singular_name' => __( 'Room', 'kana-mud-resort' ),
			'add_new_item'  => __( 'Add New Room', 'kana-mud-resort' ),
			'edit_item'     => __( 'Edit Room', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-admin-home',
		'capability_type'     => 'post',
		'has_archive'         => false,
		'rewrite'             => false,
		'supports'            => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_room', $rooms );

	$photos = [
		'labels'              => [
			'name'          => __( 'Gallery photos', 'kana-mud-resort' ),
			'singular_name' => __( 'Gallery photo', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 21,
		'menu_icon'           => 'dashicons-format-gallery',
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_photo', $photos );

	$nearby = [
		'labels'              => [
			'name'          => __( 'Nearby places', 'kana-mud-resort' ),
			'singular_name' => __( 'Nearby place', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 22,
		'menu_icon'           => 'dashicons-location',
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_nearby', $nearby );

	$amenities = [
		'labels'              => [
			'name'          => __( 'Amenities', 'kana-mud-resort' ),
			'singular_name' => __( 'Amenity', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 23,
		'menu_icon'           => 'dashicons-heart',
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'page-attributes' ],
	];
	register_post_type( 'kmr_amenity', $amenities );

	$offers = [
		'labels'              => [
			'name'          => __( 'Offers', 'kana-mud-resort' ),
			'singular_name' => __( 'Offer', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 24,
		'menu_icon'           => 'dashicons-tickets-alt',
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_offer', $offers );

	$testimonials = [
		'labels'              => [
			'name'          => __( 'Testimonials', 'kana-mud-resort' ),
			'singular_name' => __( 'Testimonial', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-testimonial',
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_testimonial', $testimonials );
}
