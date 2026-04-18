<?php
/**
 * Theme options (Hero, Booking, Contact, Site).
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'kmr_register_options_page' );
add_action( 'admin_init', 'kmr_register_settings' );

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
		<h1><?php esc_html_e( 'Resort Home — Hero, booking & contact', 'kana-mud-resort' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'kmr_options_group' ); ?>

			<h2 class="title"><?php esc_html_e( 'Hero', 'kana-mud-resort' ); ?></h2>
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
					<th scope="row"><label for="hero_background_ids"><?php esc_html_e( 'Hero background image IDs', 'kana-mud-resort' ); ?></label></th>
					<td>
						<input name="kmr_options[hero_background_ids]" id="hero_background_ids" type="text" class="large-text" value="<?php echo esc_attr( $opts['hero_background_ids'] ); ?>" placeholder="12, 34, 56" />
						<p class="description"><?php esc_html_e( 'Comma-separated Media Library attachment IDs. Slideshow rotates like the Next.js site.', 'kana-mud-resort' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Booking CTAs', 'kana-mud-resort' ); ?></h2>
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

			<h2 class="title"><?php esc_html_e( 'Contact & map', 'kana-mud-resort' ); ?></h2>
			<table class="form-table" role="presentation">
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

			<h2 class="title"><?php esc_html_e( 'Site', 'kana-mud-resort' ); ?></h2>
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
