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
			'name'               => __( 'Rooms', 'kana-mud-resort' ),
			'singular_name'      => __( 'Room', 'kana-mud-resort' ),
			'menu_name'          => __( 'Rooms', 'kana-mud-resort' ),
			'add_new_item'       => __( 'Add New Room', 'kana-mud-resort' ),
			'edit_item'          => __( 'Edit Room', 'kana-mud-resort' ),
			'all_items'          => __( 'Rooms', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'kmr-site-hub',
		'menu_position'       => 20,
		'capability_type'     => 'post',
		'has_archive'         => false,
		'rewrite'             => false,
		'supports'            => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_room', $rooms );

	$photos = [
		'labels'              => [
			'name'               => __( 'Gallery', 'kana-mud-resort' ),
			'singular_name'      => __( 'Gallery photo', 'kana-mud-resort' ),
			'menu_name'          => __( 'Gallery', 'kana-mud-resort' ),
			'all_items'          => __( 'Gallery', 'kana-mud-resort' ),
			'add_new_item'       => __( 'Add gallery photo', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'kmr-site-hub',
		'menu_position'       => 21,
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_photo', $photos );

	$nearby = [
		'labels'              => [
			'name'               => __( 'Nearby places', 'kana-mud-resort' ),
			'singular_name'      => __( 'Nearby place', 'kana-mud-resort' ),
			'menu_name'          => __( 'Nearby places', 'kana-mud-resort' ),
			'all_items'          => __( 'Nearby places', 'kana-mud-resort' ),
			'add_new_item'       => __( 'Add nearby place', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'kmr-site-hub',
		'menu_position'       => 22,
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_nearby', $nearby );

	$amenities = [
		'labels'              => [
			'name'               => __( 'Amenities', 'kana-mud-resort' ),
			'singular_name'      => __( 'Amenity', 'kana-mud-resort' ),
			'menu_name'          => __( 'Amenities', 'kana-mud-resort' ),
			'all_items'          => __( 'Amenities', 'kana-mud-resort' ),
			'add_new_item'       => __( 'Add amenity', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'kmr-site-hub',
		'menu_position'       => 23,
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_amenity', $amenities );

	$offers = [
		'labels'              => [
			'name'               => __( 'Offers & packages', 'kana-mud-resort' ),
			'singular_name'      => __( 'Offer', 'kana-mud-resort' ),
			'menu_name'          => __( 'Offers & packages', 'kana-mud-resort' ),
			'all_items'          => __( 'Offers & packages', 'kana-mud-resort' ),
			'add_new_item'       => __( 'Add offer', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'kmr-site-hub',
		'menu_position'       => 24,
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_offer', $offers );

	$testimonials = [
		'labels'              => [
			'name'               => __( 'Guests', 'kana-mud-resort' ),
			'singular_name'      => __( 'Guest quote', 'kana-mud-resort' ),
			'menu_name'          => __( 'Guests', 'kana-mud-resort' ),
			'all_items'          => __( 'Guests', 'kana-mud-resort' ),
			'add_new_item'       => __( 'Add guest quote', 'kana-mud-resort' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'kmr-site-hub',
		'menu_position'       => 25,
		'capability_type'     => 'post',
		'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
	];
	register_post_type( 'kmr_testimonial', $testimonials );
}
