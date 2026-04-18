<?php
/**
 * Theme options (Hero, Booking, Contact, Site).
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'kmr_register_options_page' );
add_action( 'admin_init', 'kmr_register_settings' );
add_action( 'admin_enqueue_scripts', 'kmr_enqueue_resort_home_admin' );
add_action( 'admin_footer', 'kmr_resort_home_scroll_to_hash' );

/**
 * Scroll to #kmr-section-* when opening Appearance → Resort Home from sidebar with a hash.
 */
function kmr_resort_home_scroll_to_hash(): void {
	$screen = get_current_screen();
	if ( ! $screen || 'appearance_page_kmr-home-settings' !== $screen->id ) {
		return;
	}
	?>
	<script>
	(function () {
		var h = window.location.hash;
		if (!h) return;
		window.setTimeout(function () {
			var el = document.querySelector(h);
			if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}, 150);
	})();
	</script>
	<?php
}

/**
 * Scripts for Resort Home (media picker for hero IDs).
 *
 * @param string $hook_suffix Current admin page.
 */
function kmr_enqueue_resort_home_admin( string $hook_suffix ): void {
	if ( 'appearance_page_kmr-home-settings' !== $hook_suffix ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'kmr-admin-resort-home',
		KMR_URI . '/assets/js/admin-resort-home.js',
		[ 'jquery' ],
		KMR_VERSION,
		true
	);
	wp_localize_script(
		'kmr-admin-resort-home',
		'kmrResortHome',
		[
			'i18n' => [
				'heroTitle'   => __( 'Hero background images', 'kana-mud-resort' ),
				'heroButton'  => __( 'Use selected images', 'kana-mud-resort' ),
			],
		]
	);
}

/**
 * Add submenu under Appearance.
 */
function kmr_register_options_page(): void {
	add_theme_page(
		__( 'Resort Home', 'kana-mud-resort' ),
		__( 'Resort Home', 'kana-mud-resort' ),
		'manage_options',
		'kmr-home-settings',
		'kmr_render_options_page'
	);
}

/**
 * Register option.
 */
function kmr_register_settings(): void {
	register_setting(
		'kmr_options_group',
		'kmr_options',
		[
			'type'              => 'array',
			'sanitize_callback' => 'kmr_sanitize_options',
			'default'           => kmr_default_options(),
		]
	);
}

/**
 * Default option values.
 *
 * @return array<string, string>
 */
function kmr_default_options(): array {
	return [
		'hero_eyebrow'            => __( 'Welcome', 'kana-mud-resort' ),
		'hero_title'              => __( 'A quiet place to arrive and breathe.', 'kana-mud-resort' ),
		'hero_subtitle'           => __( 'Unwind in thoughtfully designed spaces surrounded by forest and calm.', 'kana-mud-resort' ),
		'hero_cta_label'          => __( 'Explore rooms', 'kana-mud-resort' ),
		'hero_cta_target_section' => 'rooms',
		'hero_background_ids'     => '',
		'booking_primary_label'   => __( 'Book', 'kana-mud-resort' ),
		'booking_primary_url'     => '#contact',
		'booking_secondary_label' => __( 'WhatsApp concierge', 'kana-mud-resort' ),
		'booking_secondary_url'   => '',
		'booking_footer_note'     => __( 'Himalayan-style calm, earth-built simplicity, and warm hospitality near Mussoorie.', 'kana-mud-resort' ),
		'contact_email'           => '',
		'contact_phone'           => '',
		'contact_address'         => '',
		'contact_map_embed_url'   => '',
		'contact_hours'           => '',
		'site_brand_name'         => __( 'Kana Mud Resort', 'kana-mud-resort' ),
		'site_menu_links'         => '',
		'site_room_sort_order'    => 'default',
		'amenities_eyebrow'        => __( 'Experience', 'kana-mud-resort' ),
		'amenities_title'          => __( 'Amenities', 'kana-mud-resort' ),
		'amenities_intro'          => __( 'A Himalayan-style retreat in spirit—wholesome meals, forest trails, crisp air, and space to do very little.', 'kana-mud-resort' ),
		'amenities_empty_message'  => __( 'We are updating this list. Check back soon for the full amenity guide.', 'kana-mud-resort' ),

		'hero_secondary_cta_label' => __( 'Plan your visit', 'kana-mud-resort' ),
		'hero_secondary_cta_href'  => '#contact',

		'section_rooms_eyebrow'         => __( 'Stay', 'kana-mud-resort' ),
		'section_rooms_title'           => __( 'Rooms & cottages', 'kana-mud-resort' ),
		'section_rooms_intro'           => __( 'Each space is curated for rest—earthy textures, soft light, and views you will want to wake up to.', 'kana-mud-resort' ),
		'section_rooms_empty_title'     => __( 'Rooms', 'kana-mud-resort' ),
		'section_rooms_empty_message'   => __( 'Room descriptions and rates will appear here soon. Contact us to check availability.', 'kana-mud-resort' ),

		'section_gallery_eyebrow'       => __( 'Moments', 'kana-mud-resort' ),
		'section_gallery_title'         => __( 'Around the property', 'kana-mud-resort' ),
		'section_gallery_intro'         => __( 'Mud walls, forest light, courtyards, and paths you will want to remember — a quiet look at the retreat before you arrive.', 'kana-mud-resort' ),
		'section_gallery_empty_title'   => __( 'Gallery', 'kana-mud-resort' ),
		'section_gallery_empty_message' => __( 'New photos of the property will appear here soon.', 'kana-mud-resort' ),

		'section_nearby_eyebrow'       => __( 'Explore', 'kana-mud-resort' ),
		'section_nearby_title'         => __( 'Nearby places', 'kana-mud-resort' ),
		'section_nearby_intro'         => __( 'Mussoorie, ridge walks, and the villages along the slopes are within easy reach—sunset viewpoints, craft corners, and day trips you can pair with slow days at the retreat.', 'kana-mud-resort' ),
		'section_nearby_empty_title'   => __( 'Nearby places', 'kana-mud-resort' ),
		'section_nearby_empty_message' => __( 'Nearby walks, villages, and viewpoints will be listed here soon.', 'kana-mud-resort' ),

		'section_offers_eyebrow'        => __( 'Value', 'kana-mud-resort' ),
		'section_offers_title'          => __( 'Offers & packages', 'kana-mud-resort' ),
		'section_offers_intro'          => '',
		'section_offers_empty_message'  => __( 'Seasonal packages and special rates will be listed here when available. Ask us about current offers.', 'kana-mud-resort' ),
		'section_offers_cta_label'      => __( 'Enquire now', 'kana-mud-resort' ),

		'section_testimonials_eyebrow'       => __( 'Guests', 'kana-mud-resort' ),
		'section_testimonials_title'         => __( 'What visitors say', 'kana-mud-resort' ),
		'section_testimonials_empty_message' => __( 'Guest stories will appear here soon.', 'kana-mud-resort' ),

		'section_contact_eyebrow'            => __( 'Visit', 'kana-mud-resort' ),
		'section_contact_title'              => __( 'Contact & location', 'kana-mud-resort' ),
		'section_contact_intro'              => __( 'Reach us by phone or email, find directions below, and plan your arrival with confidence.', 'kana-mud-resort' ),
		'section_contact_address_placeholder' => __( 'Address details will appear here soon.', 'kana-mud-resort' ),
		'section_contact_map_placeholder'    => __( 'Map preview is not available yet. Use the address on the left for directions.', 'kana-mud-resort' ),
		'section_contact_label_address'      => __( 'Address', 'kana-mud-resort' ),
		'section_contact_label_phone'        => __( 'Phone', 'kana-mud-resort' ),
		'section_contact_label_email'        => __( 'Email', 'kana-mud-resort' ),
		'section_contact_label_hours'        => __( 'Hours', 'kana-mud-resort' ),

		'footer_brand_name' => '',
		'footer_legal_text' => __( 'Kana Mud Resort. All rights reserved.', 'kana-mud-resort' ),
	];
}

/**
 * @param array<string, mixed> $input Raw input.
 * @return array<string, string>
 */
function kmr_sanitize_options( $input ): array {
	$defaults = kmr_default_options();
	$out      = [];
	if ( ! is_array( $input ) ) {
		return $defaults;
	}

	$out['hero_eyebrow']            = sanitize_text_field( $input['hero_eyebrow'] ?? $defaults['hero_eyebrow'] );
	$out['hero_title']              = sanitize_text_field( $input['hero_title'] ?? $defaults['hero_title'] );
	$out['hero_subtitle']           = sanitize_textarea_field( $input['hero_subtitle'] ?? $defaults['hero_subtitle'] );
	$out['hero_cta_label']          = sanitize_text_field( $input['hero_cta_label'] ?? $defaults['hero_cta_label'] );
	$out['hero_cta_target_section'] = preg_replace( '/[^a-z0-9_-]/i', '', (string) ( $input['hero_cta_target_section'] ?? $defaults['hero_cta_target_section'] ) );
	$out['hero_background_ids']     = sanitize_text_field( $input['hero_background_ids'] ?? '' );

	$out['booking_primary_label']   = sanitize_text_field( $input['booking_primary_label'] ?? $defaults['booking_primary_label'] );
	$out['booking_primary_url']     = esc_url_raw( (string) ( $input['booking_primary_url'] ?? '' ) );
	if ( $out['booking_primary_url'] === '' ) {
		$out['booking_primary_url'] = '#contact';
	}
	$out['booking_secondary_label'] = sanitize_text_field( $input['booking_secondary_label'] ?? '' );
	$out['booking_secondary_url']   = esc_url_raw( (string) ( $input['booking_secondary_url'] ?? '' ) );
	$out['booking_footer_note']     = sanitize_textarea_field( $input['booking_footer_note'] ?? $defaults['booking_footer_note'] );

	$out['contact_email']   = sanitize_email( (string) ( $input['contact_email'] ?? '' ) );
	$out['contact_phone']   = sanitize_text_field( $input['contact_phone'] ?? '' );
	$out['contact_address'] = sanitize_textarea_field( $input['contact_address'] ?? '' );
	$map_raw                = (string) ( $input['contact_map_embed_url'] ?? '' );
	if ( str_contains( $map_raw, '<' ) ) {
		$allowed = wp_kses_allowed_html( 'post' );
		$allowed['iframe'] = [
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'style'           => true,
			'loading'         => true,
			'referrerpolicy'  => true,
			'allowfullscreen' => true,
			'frameborder'     => true,
			'title'           => true,
		];
		$out['contact_map_embed_url'] = wp_kses( $map_raw, $allowed );
	} else {
		$out['contact_map_embed_url'] = esc_url_raw( trim( $map_raw ) );
	}
	$out['contact_hours'] = sanitize_textarea_field( $input['contact_hours'] ?? '' );

	$out['site_brand_name'] = sanitize_text_field( $input['site_brand_name'] ?? $defaults['site_brand_name'] );
	$raw_menu               = $input['site_menu_links'] ?? '';
	if ( is_string( $raw_menu ) ) {
		$raw_menu = trim( $raw_menu );
		if ( $raw_menu !== '' ) {
			$decoded = json_decode( $raw_menu, true );
			if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
				$out['site_menu_links'] = wp_json_encode( $decoded );
			} else {
				$out['site_menu_links'] = sanitize_textarea_field( $raw_menu );
			}
		} else {
			$out['site_menu_links'] = '';
		}
	} else {
		$out['site_menu_links'] = '';
	}

	$sort = $input['site_room_sort_order'] ?? 'default';
	$sort = is_string( $sort ) ? $sort : 'default';
	$out['site_room_sort_order'] = in_array( $sort, [ 'default', 'price-low-high', 'price-high-low' ], true ) ? $sort : 'default';

	$out['amenities_eyebrow'] = sanitize_text_field( $input['amenities_eyebrow'] ?? $defaults['amenities_eyebrow'] );
	$out['amenities_title']   = sanitize_text_field( $input['amenities_title'] ?? $defaults['amenities_title'] );
	$out['amenities_intro']   = sanitize_textarea_field( $input['amenities_intro'] ?? $defaults['amenities_intro'] );
	$out['amenities_empty_message'] = sanitize_textarea_field( $input['amenities_empty_message'] ?? $defaults['amenities_empty_message'] );

	$out['hero_secondary_cta_label'] = sanitize_text_field( $input['hero_secondary_cta_label'] ?? $defaults['hero_secondary_cta_label'] );
	$hsec                           = isset( $input['hero_secondary_cta_href'] ) ? trim( (string) $input['hero_secondary_cta_href'] ) : $defaults['hero_secondary_cta_href'];
	$out['hero_secondary_cta_href'] = $hsec !== '' ? $hsec : '#contact';

	$text_keys = [
		'section_rooms_eyebrow', 'section_rooms_title', 'section_rooms_empty_title',
		'section_gallery_eyebrow', 'section_gallery_title', 'section_gallery_empty_title',
		'section_nearby_eyebrow', 'section_nearby_title', 'section_nearby_empty_title',
		'section_offers_eyebrow', 'section_offers_title',
		'section_offers_cta_label',
		'section_testimonials_eyebrow', 'section_testimonials_title',
		'section_contact_eyebrow', 'section_contact_title',
		'section_contact_label_address', 'section_contact_label_phone', 'section_contact_label_email', 'section_contact_label_hours',
		'footer_brand_name',
	];
	foreach ( $text_keys as $tk ) {
		$out[ $tk ] = sanitize_text_field( $input[ $tk ] ?? $defaults[ $tk ] );
	}
	$ta_keys = [
		'section_rooms_intro', 'section_rooms_empty_message',
		'section_gallery_intro', 'section_gallery_empty_message',
		'section_nearby_intro', 'section_nearby_empty_message',
		'section_offers_intro', 'section_offers_empty_message',
		'section_testimonials_empty_message',
		'section_contact_intro', 'section_contact_address_placeholder', 'section_contact_map_placeholder',
		'footer_legal_text',
	];
	foreach ( $ta_keys as $tk ) {
		$out[ $tk ] = sanitize_textarea_field( $input[ $tk ] ?? $defaults[ $tk ] );
	}

	return array_merge( $defaults, $out );
}

/**
 * Render settings page.
 */
function kmr_render_options_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$opts = get_option( 'kmr_options', kmr_default_options() );
	if ( ! is_array( $opts ) ) {
		$opts = kmr_default_options();
	}
	$opts = array_merge( kmr_default_options(), $opts );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Resort Home — full homepage (all sections)', 'kana-mud-resort' ); ?></h1>

		<div class="notice notice-info" style="margin:12px 0 16px;">
			<p style="margin:.35em 0;">
				<strong><?php esc_html_e( 'You are editing live homepage content.', 'kana-mud-resort' ); ?></strong>
				<?php esc_html_e( 'Fields below control all homepage section headings, intros, hero, booking bar, contact column labels, footer, and more — not a Page in the block editor.', 'kana-mud-resort' ); ?>
			</p>
			<p style="margin:.35em 0;">
				<?php
				printf(
					/* translators: %s: URL to section hub */
					wp_kses_post( __( 'Rooms, gallery, and other bands are edited under <a href="%s">Resort site</a> or their left-hand menus.', 'kana-mud-resort' ) ),
					esc_url( admin_url( 'admin.php?page=kmr-site-hub' ) )
				);
				?>
			</p>
		</div>

		<form method="post" action="options.php" id="kmr-resort-home-form">
			<?php settings_fields( 'kmr_options_group' ); ?>

			<ul class="ul-disc" style="list-style:disc;margin:0 0 1.25em 1.25em;max-width:52rem;">
				<li><a href="#kmr-section-hero"><?php esc_html_e( 'Hero', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-booking"><?php esc_html_e( 'Booking bar', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-copy-rooms"><?php esc_html_e( 'Rooms (headings)', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-copy-gallery"><?php esc_html_e( 'Gallery (headings)', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-copy-nearby"><?php esc_html_e( 'Nearby (headings)', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-amenities-intro"><?php esc_html_e( 'Experience & amenities (headings)', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-copy-offers"><?php esc_html_e( 'Offers (headings)', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-copy-guests"><?php esc_html_e( 'Guests (headings)', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-contact"><?php esc_html_e( 'Contact & location', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-site"><?php esc_html_e( 'Header & site', 'kana-mud-resort' ); ?></a></li>
				<li><a href="#kmr-section-footer"><?php esc_html_e( 'Footer', 'kana-mud-resort' ); ?></a></li>
			</ul>

			<h2 class="title" id="kmr-section-hero"><?php esc_html_e( 'Hero', 'kana-mud-resort' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="hero_eyebrow"><?php esc_html_e( 'Eyebrow', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[hero_eyebrow]" id="hero_eyebrow" type="text" class="regular-text" value="<?php echo esc_attr( $opts['hero_eyebrow'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_title"><?php esc_html_e( 'Title', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[hero_title]" id="hero_title" type="text" class="large-text" value="<?php echo esc_attr( $opts['hero_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_subtitle"><?php esc_html_e( 'Subtitle', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[hero_subtitle]" id="hero_subtitle" class="large-text" rows="3"><?php echo esc_textarea( $opts['hero_subtitle'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_cta_label"><?php esc_html_e( 'Primary CTA label', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[hero_cta_label]" id="hero_cta_label" type="text" class="regular-text" value="<?php echo esc_attr( $opts['hero_cta_label'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_cta_target_section"><?php esc_html_e( 'Primary CTA target section id', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[hero_cta_target_section]" id="hero_cta_target_section" type="text" class="regular-text" value="<?php echo esc_attr( $opts['hero_cta_target_section'] ); ?>" placeholder="rooms" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_background_ids"><?php esc_html_e( 'Hero background images', 'kana-mud-resort' ); ?></label></th>
					<td>
						<p>
							<button type="button" class="button" id="kmr-hero-select-images"><?php esc_html_e( 'Select images', 'kana-mud-resort' ); ?></button>
						</p>
						<input name="kmr_options[hero_background_ids]" id="hero_background_ids" type="text" class="large-text" value="<?php echo esc_attr( $opts['hero_background_ids'] ); ?>" placeholder="12, 34, 56" />
						<p class="description"><?php esc_html_e( 'Use “Select images” (or upload under Media → Add New), then save. Order follows your selection. Leave empty only if you want the bundled demo photos.', 'kana-mud-resort' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_secondary_cta_label"><?php esc_html_e( 'Second hero button label', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[hero_secondary_cta_label]" id="hero_secondary_cta_label" type="text" class="regular-text" value="<?php echo esc_attr( $opts['hero_secondary_cta_label'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_secondary_cta_href"><?php esc_html_e( 'Second hero button URL', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[hero_secondary_cta_href]" id="hero_secondary_cta_href" type="text" class="regular-text" value="<?php echo esc_attr( $opts['hero_secondary_cta_href'] ); ?>" placeholder="#contact" /></td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-booking"><?php esc_html_e( 'Booking CTAs', 'kana-mud-resort' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="booking_primary_label"><?php esc_html_e( 'Primary button label', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[booking_primary_label]" id="booking_primary_label" type="text" class="regular-text" value="<?php echo esc_attr( $opts['booking_primary_label'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="booking_primary_url"><?php esc_html_e( 'Primary URL', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[booking_primary_url]" id="booking_primary_url" type="url" class="large-text" value="<?php echo esc_attr( $opts['booking_primary_url'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="booking_secondary_label"><?php esc_html_e( 'Secondary label', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[booking_secondary_label]" id="booking_secondary_label" type="text" class="regular-text" value="<?php echo esc_attr( $opts['booking_secondary_label'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="booking_secondary_url"><?php esc_html_e( 'Secondary URL (e.g. WhatsApp)', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[booking_secondary_url]" id="booking_secondary_url" type="url" class="large-text" value="<?php echo esc_attr( $opts['booking_secondary_url'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="booking_footer_note"><?php esc_html_e( 'Footer note', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[booking_footer_note]" id="booking_footer_note" class="large-text" rows="3"><?php echo esc_textarea( $opts['booking_footer_note'] ); ?></textarea></td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-copy-rooms"><?php esc_html_e( 'Rooms section (headings & empty state)', 'kana-mud-resort' ); ?></h2>
			<p class="description" style="max-width:52rem;"><?php esc_html_e( 'Shown above the room cards. Individual rooms are edited under Resort site → Rooms.', 'kana-mud-resort' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="section_rooms_eyebrow"><?php esc_html_e( 'Eyebrow', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_rooms_eyebrow]" id="section_rooms_eyebrow" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_rooms_eyebrow'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_rooms_title"><?php esc_html_e( 'Heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_rooms_title]" id="section_rooms_title" type="text" class="large-text" value="<?php echo esc_attr( $opts['section_rooms_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_rooms_intro"><?php esc_html_e( 'Intro paragraph', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_rooms_intro]" id="section_rooms_intro" class="large-text" rows="3"><?php echo esc_textarea( $opts['section_rooms_intro'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_rooms_empty_title"><?php esc_html_e( 'When no rooms published: heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_rooms_empty_title]" id="section_rooms_empty_title" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_rooms_empty_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_rooms_empty_message"><?php esc_html_e( 'When no rooms: message', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_rooms_empty_message]" id="section_rooms_empty_message" class="large-text" rows="2"><?php echo esc_textarea( $opts['section_rooms_empty_message'] ); ?></textarea></td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-copy-gallery"><?php esc_html_e( 'Gallery section (headings & empty state)', 'kana-mud-resort' ); ?></h2>
			<p class="description" style="max-width:52rem;"><?php esc_html_e( 'Photos are edited under Resort site → Gallery.', 'kana-mud-resort' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="section_gallery_eyebrow"><?php esc_html_e( 'Eyebrow', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_gallery_eyebrow]" id="section_gallery_eyebrow" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_gallery_eyebrow'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_gallery_title"><?php esc_html_e( 'Heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_gallery_title]" id="section_gallery_title" type="text" class="large-text" value="<?php echo esc_attr( $opts['section_gallery_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_gallery_intro"><?php esc_html_e( 'Intro paragraph', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_gallery_intro]" id="section_gallery_intro" class="large-text" rows="3"><?php echo esc_textarea( $opts['section_gallery_intro'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_gallery_empty_title"><?php esc_html_e( 'When no photos: heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_gallery_empty_title]" id="section_gallery_empty_title" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_gallery_empty_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_gallery_empty_message"><?php esc_html_e( 'When no photos: message', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_gallery_empty_message]" id="section_gallery_empty_message" class="large-text" rows="2"><?php echo esc_textarea( $opts['section_gallery_empty_message'] ); ?></textarea></td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-copy-nearby"><?php esc_html_e( 'Nearby places (headings & empty state)', 'kana-mud-resort' ); ?></h2>
			<p class="description" style="max-width:52rem;"><?php esc_html_e( 'Places are edited under Resort site → Nearby places.', 'kana-mud-resort' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="section_nearby_eyebrow"><?php esc_html_e( 'Eyebrow', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_nearby_eyebrow]" id="section_nearby_eyebrow" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_nearby_eyebrow'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_nearby_title"><?php esc_html_e( 'Heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_nearby_title]" id="section_nearby_title" type="text" class="large-text" value="<?php echo esc_attr( $opts['section_nearby_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_nearby_intro"><?php esc_html_e( 'Intro paragraph', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_nearby_intro]" id="section_nearby_intro" class="large-text" rows="3"><?php echo esc_textarea( $opts['section_nearby_intro'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_nearby_empty_title"><?php esc_html_e( 'When no places: heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_nearby_empty_title]" id="section_nearby_empty_title" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_nearby_empty_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_nearby_empty_message"><?php esc_html_e( 'When no places: message', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_nearby_empty_message]" id="section_nearby_empty_message" class="large-text" rows="2"><?php echo esc_textarea( $opts['section_nearby_empty_message'] ); ?></textarea></td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-amenities-intro"><?php esc_html_e( 'Experience & amenities (section headings)', 'kana-mud-resort' ); ?></h2>
			<p class="description" style="max-width:52rem;">
				<?php esc_html_e( 'These lines appear above the amenities grid. To edit each amenity card, use Resort site → Amenities.', 'kana-mud-resort' ); ?>
			</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="amenities_eyebrow"><?php esc_html_e( 'Eyebrow (e.g. Experience)', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[amenities_eyebrow]" id="amenities_eyebrow" type="text" class="regular-text" value="<?php echo esc_attr( $opts['amenities_eyebrow'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="amenities_title"><?php esc_html_e( 'Heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[amenities_title]" id="amenities_title" type="text" class="regular-text" value="<?php echo esc_attr( $opts['amenities_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="amenities_intro"><?php esc_html_e( 'Intro paragraph', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[amenities_intro]" id="amenities_intro" class="large-text" rows="3"><?php echo esc_textarea( $opts['amenities_intro'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="amenities_empty_message"><?php esc_html_e( 'When no amenities published', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[amenities_empty_message]" id="amenities_empty_message" class="large-text" rows="2"><?php echo esc_textarea( $opts['amenities_empty_message'] ); ?></textarea></td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-copy-offers"><?php esc_html_e( 'Offers & packages (headings)', 'kana-mud-resort' ); ?></h2>
			<p class="description" style="max-width:52rem;"><?php esc_html_e( 'Each offer is edited under Resort site → Offers & packages.', 'kana-mud-resort' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="section_offers_eyebrow"><?php esc_html_e( 'Eyebrow', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_offers_eyebrow]" id="section_offers_eyebrow" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_offers_eyebrow'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_offers_title"><?php esc_html_e( 'Heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_offers_title]" id="section_offers_title" type="text" class="large-text" value="<?php echo esc_attr( $opts['section_offers_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_offers_intro"><?php esc_html_e( 'Intro paragraph (optional, when offers exist)', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_offers_intro]" id="section_offers_intro" class="large-text" rows="2"><?php echo esc_textarea( $opts['section_offers_intro'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_offers_empty_message"><?php esc_html_e( 'When no offers: message', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_offers_empty_message]" id="section_offers_empty_message" class="large-text" rows="2"><?php echo esc_textarea( $opts['section_offers_empty_message'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_offers_cta_label"><?php esc_html_e( 'Button on each offer card', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_offers_cta_label]" id="section_offers_cta_label" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_offers_cta_label'] ); ?>" /></td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-copy-guests"><?php esc_html_e( 'Guests (headings)', 'kana-mud-resort' ); ?></h2>
			<p class="description" style="max-width:52rem;"><?php esc_html_e( 'Quotes are edited under Resort site → Guests.', 'kana-mud-resort' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="section_testimonials_eyebrow"><?php esc_html_e( 'Eyebrow', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_testimonials_eyebrow]" id="section_testimonials_eyebrow" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_testimonials_eyebrow'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_testimonials_title"><?php esc_html_e( 'Heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_testimonials_title]" id="section_testimonials_title" type="text" class="large-text" value="<?php echo esc_attr( $opts['section_testimonials_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_testimonials_empty_message"><?php esc_html_e( 'When no quotes: message', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_testimonials_empty_message]" id="section_testimonials_empty_message" class="large-text" rows="2"><?php echo esc_textarea( $opts['section_testimonials_empty_message'] ); ?></textarea></td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-contact"><?php esc_html_e( 'Contact & map', 'kana-mud-resort' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="section_contact_eyebrow"><?php esc_html_e( 'Section eyebrow', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_contact_eyebrow]" id="section_contact_eyebrow" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_contact_eyebrow'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_contact_title"><?php esc_html_e( 'Section heading', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_contact_title]" id="section_contact_title" type="text" class="large-text" value="<?php echo esc_attr( $opts['section_contact_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_contact_intro"><?php esc_html_e( 'Intro paragraph', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_contact_intro]" id="section_contact_intro" class="large-text" rows="2"><?php echo esc_textarea( $opts['section_contact_intro'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_contact_address_placeholder"><?php esc_html_e( 'Placeholder when address is empty', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_contact_address_placeholder]" id="section_contact_address_placeholder" class="large-text" rows="2"><?php echo esc_textarea( $opts['section_contact_address_placeholder'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_contact_map_placeholder"><?php esc_html_e( 'Placeholder when map is empty', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[section_contact_map_placeholder]" id="section_contact_map_placeholder" class="large-text" rows="2"><?php echo esc_textarea( $opts['section_contact_map_placeholder'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_contact_label_address"><?php esc_html_e( 'Label: Address block', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_contact_label_address]" id="section_contact_label_address" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_contact_label_address'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_contact_label_phone"><?php esc_html_e( 'Label: Phone', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_contact_label_phone]" id="section_contact_label_phone" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_contact_label_phone'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_contact_label_email"><?php esc_html_e( 'Label: Email', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_contact_label_email]" id="section_contact_label_email" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_contact_label_email'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="section_contact_label_hours"><?php esc_html_e( 'Label: Hours', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[section_contact_label_hours]" id="section_contact_label_hours" type="text" class="regular-text" value="<?php echo esc_attr( $opts['section_contact_label_hours'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="contact_email"><?php esc_html_e( 'Email', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[contact_email]" id="contact_email" type="email" class="large-text" value="<?php echo esc_attr( $opts['contact_email'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="contact_phone"><?php esc_html_e( 'Phone', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[contact_phone]" id="contact_phone" type="text" class="large-text" value="<?php echo esc_attr( $opts['contact_phone'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="contact_address"><?php esc_html_e( 'Address', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[contact_address]" id="contact_address" class="large-text" rows="4"><?php echo esc_textarea( $opts['contact_address'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="contact_hours"><?php esc_html_e( 'Hours', 'kana-mud-resort' ); ?></label></th>
					<td><textarea name="kmr_options[contact_hours]" id="contact_hours" class="large-text" rows="3"><?php echo esc_textarea( $opts['contact_hours'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="contact_map_embed_url"><?php esc_html_e( 'Map embed URL or iframe HTML', 'kana-mud-resort' ); ?></label></th>
					<td>
						<textarea name="kmr_options[contact_map_embed_url]" id="contact_map_embed_url" class="large-text code" rows="5"><?php echo esc_textarea( is_string( $opts['contact_map_embed_url'] ) ? $opts['contact_map_embed_url'] : '' ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Paste a Google Maps embed URL, or full iframe HTML from “Share → Embed”.', 'kana-mud-resort' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-site"><?php esc_html_e( 'Site', 'kana-mud-resort' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="site_brand_name"><?php esc_html_e( 'Brand name (header)', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[site_brand_name]" id="site_brand_name" type="text" class="regular-text" value="<?php echo esc_attr( $opts['site_brand_name'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="site_menu_links"><?php esc_html_e( 'Menu links (JSON)', 'kana-mud-resort' ); ?></label></th>
					<td>
						<textarea name="kmr_options[site_menu_links]" id="site_menu_links" class="large-text code" rows="8" placeholder='[{"label":"Rooms","href":"#rooms"}]'><?php echo esc_textarea( is_string( $opts['site_menu_links'] ) ? $opts['site_menu_links'] : '' ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Optional. Leave empty to use the default section anchors.', 'kana-mud-resort' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="site_room_sort_order"><?php esc_html_e( 'Room sort order', 'kana-mud-resort' ); ?></label></th>
					<td>
						<select name="kmr_options[site_room_sort_order]" id="site_room_sort_order">
							<option value="default" <?php selected( $opts['site_room_sort_order'], 'default' ); ?>><?php esc_html_e( 'Default (menu order)', 'kana-mud-resort' ); ?></option>
							<option value="price-low-high" <?php selected( $opts['site_room_sort_order'], 'price-low-high' ); ?>><?php esc_html_e( 'Price: low to high', 'kana-mud-resort' ); ?></option>
							<option value="price-high-low" <?php selected( $opts['site_room_sort_order'], 'price-high-low' ); ?>><?php esc_html_e( 'Price: high to low', 'kana-mud-resort' ); ?></option>
						</select>
					</td>
				</tr>
			</table>

			<h2 class="title" id="kmr-section-footer"><?php esc_html_e( 'Footer', 'kana-mud-resort' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="footer_brand_name"><?php esc_html_e( 'Footer brand line', 'kana-mud-resort' ); ?></label></th>
					<td>
						<input name="kmr_options[footer_brand_name]" id="footer_brand_name" type="text" class="regular-text" value="<?php echo esc_attr( $opts['footer_brand_name'] ); ?>" placeholder="<?php echo esc_attr( $opts['site_brand_name'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Leave empty to use the header brand name.', 'kana-mud-resort' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="footer_legal_text"><?php esc_html_e( 'Copyright line (after the year)', 'kana-mud-resort' ); ?></label></th>
					<td><input name="kmr_options[footer_legal_text]" id="footer_legal_text" type="text" class="large-text" value="<?php echo esc_attr( $opts['footer_legal_text'] ); ?>" /></td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Merge defaults when reading options.
 *
 * @param mixed $v Value.
 * @return array<string, string>
 */
function kmr_get_options_merged( $v ): array {
	$defaults = kmr_default_options();
	if ( ! is_array( $v ) ) {
		return $defaults;
	}
	return array_merge( $defaults, $v );
}

add_filter( 'option_kmr_options', 'kmr_get_options_merged' );
