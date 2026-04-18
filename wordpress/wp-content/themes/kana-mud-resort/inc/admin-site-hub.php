<?php
/**
 * Central place in wp-admin: each homepage section → where to edit (not the Page editor).
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'kmr_register_site_hub', 5 );
add_action( 'admin_menu', 'kmr_register_site_hub_submenus', 6 );
add_action( 'admin_menu', 'kmr_sort_resort_site_submenu', 1000 );

/**
 * Top-level menu so the site does not feel like a single static HTML file.
 */
function kmr_register_site_hub(): void {
	add_menu_page(
		__( 'Resort site', 'kana-mud-resort' ),
		__( 'Resort site', 'kana-mud-resort' ),
		'edit_posts',
		'kmr-site-hub',
		'kmr_render_site_hub',
		'dashicons-admin-site-alt3',
		3
	);
}

/**
 * Quick links into Appearance → Resort Home (anchors). CPTs register their own submenus under this parent.
 */
function kmr_register_site_hub_submenus(): void {
	$parent = 'kmr-site-hub';
	$base   = admin_url( 'themes.php?page=kmr-home-settings' );

	$redirect = static function ( string $hash ) use ( $base ): void {
		wp_safe_redirect( $base . $hash );
		exit;
	};

	add_submenu_page(
		$parent,
		__( 'Hero & images', 'kana-mud-resort' ),
		__( 'Hero & images', 'kana-mud-resort' ),
		'manage_options',
		'kmr-hub-hero',
		static function () use ( $redirect ): void {
			$redirect( '#kmr-section-hero' );
		}
	);

	add_submenu_page(
		$parent,
		__( 'Experience & amenities (headings)', 'kana-mud-resort' ),
		__( 'Experience & amenities', 'kana-mud-resort' ),
		'manage_options',
		'kmr-hub-amenities-headings',
		static function () use ( $redirect ): void {
			$redirect( '#kmr-section-amenities-intro' );
		}
	);

	add_submenu_page(
		$parent,
		__( 'Booking bar', 'kana-mud-resort' ),
		__( 'Booking bar', 'kana-mud-resort' ),
		'manage_options',
		'kmr-hub-booking',
		static function () use ( $redirect ): void {
			$redirect( '#kmr-section-booking' );
		}
	);

	add_submenu_page(
		$parent,
		__( 'Contact & location', 'kana-mud-resort' ),
		__( 'Contact & location', 'kana-mud-resort' ),
		'manage_options',
		'kmr-hub-contact',
		static function () use ( $redirect ): void {
			$redirect( '#kmr-section-contact' );
		}
	);

	add_submenu_page(
		$parent,
		__( 'Header & site', 'kana-mud-resort' ),
		__( 'Header & site', 'kana-mud-resort' ),
		'manage_options',
		'kmr-hub-site',
		static function () use ( $redirect ): void {
			$redirect( '#kmr-section-site' );
		}
	);

	add_submenu_page(
		$parent,
		__( 'Media library', 'kana-mud-resort' ),
		__( 'Media', 'kana-mud-resort' ),
		'upload_files',
		'kmr-hub-media',
		static function (): void {
			wp_safe_redirect( admin_url( 'upload.php' ) );
			exit;
		}
	);
}

/**
 * Order submenu to match public page: hero → bands → booking strip → contact → site → media.
 * CPT items are registered by core with slugs like edit.php?post_type=kmr_room.
 */
function kmr_sort_resort_site_submenu(): void {
	global $submenu;
	if ( empty( $submenu['kmr-site-hub'] ) || ! is_array( $submenu['kmr-site-hub'] ) ) {
		return;
	}

	$items = $submenu['kmr-site-hub'];
	$by    = [];
	foreach ( $items as $item ) {
		if ( empty( $item[2] ) ) {
			continue;
		}
		$by[ $item[2] ] = $item;
	}

	$order = [
		'kmr-site-hub',
		'kmr-hub-hero',
		'edit.php?post_type=kmr_room',
		'edit.php?post_type=kmr_photo',
		'edit.php?post_type=kmr_nearby',
		'kmr-hub-amenities-headings',
		'edit.php?post_type=kmr_amenity',
		'edit.php?post_type=kmr_offer',
		'edit.php?post_type=kmr_testimonial',
		'kmr-hub-booking',
		'kmr-hub-contact',
		'kmr-hub-site',
		'kmr-hub-media',
	];

	$new = [];
	foreach ( $order as $slug ) {
		if ( isset( $by[ $slug ] ) ) {
			$new[] = $by[ $slug ];
			unset( $by[ $slug ] );
		}
	}
	foreach ( $by as $rest ) {
		$new[] = $rest;
	}

	$submenu['kmr-site-hub'] = $new;
}

add_action( 'admin_notices', 'kmr_notice_pages_not_home_sections' );

/**
 * Warn when editing “Pages” — layout/images for the landing page are not stored here.
 */
function kmr_notice_pages_not_home_sections(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	if ( ! in_array( $screen->id, [ 'page', 'edit-page' ], true ) ) {
		return;
	}
	$hub = admin_url( 'admin.php?page=kmr-site-hub' );
	?>
	<div class="notice notice-warning">
		<p>
			<strong><?php esc_html_e( 'Kana Mud Resort:', 'kana-mud-resort' ); ?></strong>
			<?php
			printf(
				/* translators: 1: opening anchor to Resort site, 2: closing anchor */
				wp_kses_post( __( 'The homepage is not built from this Page. To change hero text, images, rooms, gallery, and other sections, use %1$sResort site%2$s in the admin menu (or Appearance → Resort Home for hero & contact).', 'kana-mud-resort' ) ),
				'<a href="' . esc_url( $hub ) . '">',
				'</a>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * @return array<int, array{slug:string,title:string,desc:string,edit:string,cap?:string}>
 */
function kmr_site_hub_sections(): array {
	$base  = admin_url( 'themes.php?page=kmr-home-settings' );
	$media = admin_url( 'upload.php' );

	return [
		[
			'slug'  => 'hero',
			'title' => __( 'Hero & images', 'kana-mud-resort' ),
			'desc'  => __( 'Headline, hero slideshow, CTA. Same screen as Appearance → Resort Home (jump link).', 'kana-mud-resort' ),
			'edit'  => $base . '#kmr-section-hero',
			'cap'   => 'manage_options',
		],
		[
			'slug'  => 'rooms',
			'title' => __( 'Rooms', 'kana-mud-resort' ),
			'desc'  => __( 'Room cards: title, description, featured image, price, extra gallery IDs.', 'kana-mud-resort' ),
			'edit'  => admin_url( 'edit.php?post_type=kmr_room' ),
		],
		[
			'slug'  => 'gallery',
			'title' => __( 'Gallery', 'kana-mud-resort' ),
			'desc'  => __( 'Featured image + caption for each photo in the gallery strip.', 'kana-mud-resort' ),
			'edit'  => admin_url( 'edit.php?post_type=kmr_photo' ),
		],
		[
			'slug'  => 'nearby',
			'title' => __( 'Nearby places', 'kana-mud-resort' ),
			'desc'  => __( 'Places to visit: image, distance label, description.', 'kana-mud-resort' ),
			'edit'  => admin_url( 'edit.php?post_type=kmr_nearby' ),
		],
		[
			'slug'  => 'experience',
			'title' => __( 'Experience & amenities (headings)', 'kana-mud-resort' ),
			'desc'  => __( 'Eyebrow, title, and intro paragraph above the amenities grid.', 'kana-mud-resort' ),
			'edit'  => $base . '#kmr-section-amenities-intro',
			'cap'   => 'manage_options',
		],
		[
			'slug'  => 'amenities',
			'title' => __( 'Amenities (items)', 'kana-mud-resort' ),
			'desc'  => __( 'Each amenity card: title, icon key, description.', 'kana-mud-resort' ),
			'edit'  => admin_url( 'edit.php?post_type=kmr_amenity' ),
		],
		[
			'slug'  => 'offers',
			'title' => __( 'Offers & packages', 'kana-mud-resort' ),
			'desc'  => __( 'Promotions with optional image, badge, and valid-until date.', 'kana-mud-resort' ),
			'edit'  => admin_url( 'edit.php?post_type=kmr_offer' ),
		],
		[
			'slug'  => 'guests',
			'title' => __( 'Guests', 'kana-mud-resort' ),
			'desc'  => __( 'Quotes, author line, rating, optional photo.', 'kana-mud-resort' ),
			'edit'  => admin_url( 'edit.php?post_type=kmr_testimonial' ),
		],
		[
			'slug'  => 'booking',
			'title' => __( 'Booking bar', 'kana-mud-resort' ),
			'desc'  => __( 'Sticky booking buttons and footer line.', 'kana-mud-resort' ),
			'edit'  => $base . '#kmr-section-booking',
			'cap'   => 'manage_options',
		],
		[
			'slug'  => 'contact',
			'title' => __( 'Contact & location', 'kana-mud-resort' ),
			'desc'  => __( 'Email, phone, address, hours, map embed.', 'kana-mud-resort' ),
			'edit'  => $base . '#kmr-section-contact',
			'cap'   => 'manage_options',
		],
		[
			'slug'  => 'site',
			'title' => __( 'Header & site', 'kana-mud-resort' ),
			'desc'  => __( 'Brand name in the header, optional menu JSON, room sort order.', 'kana-mud-resort' ),
			'edit'  => $base . '#kmr-section-site',
			'cap'   => 'manage_options',
		],
		[
			'slug'  => 'media',
			'title' => __( 'Media library', 'kana-mud-resort' ),
			'desc'  => __( 'Upload images, then use them in Resort Home or as featured images.', 'kana-mud-resort' ),
			'edit'  => $media,
		],
	];
}

/**
 * Render hub screen.
 */
function kmr_render_site_hub(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'kana-mud-resort' ) );
	}

	$sections = kmr_site_hub_sections();
	?>
	<div class="wrap kmr-site-hub">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Resort site — homepage sections', 'kana-mud-resort' ); ?></h1>
		<hr class="wp-header-end" />

		<div class="notice notice-info" style="margin:12px 0 20px;">
			<p style="margin:.5em 0;">
				<strong><?php esc_html_e( 'This theme is one scrolling page, but content is not edited like a single HTML file.', 'kana-mud-resort' ); ?></strong>
			</p>
			<p style="margin:.5em 0;">
				<?php esc_html_e( 'Use the boxes below — each section of the public site has its own list or settings screen. You usually do not need the block editor on Pages for the homepage layout.', 'kana-mud-resort' ); ?>
			</p>
		</div>

		<div style="display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));max-width:1200px;">
			<?php foreach ( $sections as $row ) : ?>
				<?php
				$need_cap = isset( $row['cap'] ) ? $row['cap'] : 'edit_posts';
				$can      = current_user_can( $need_cap );
				?>
				<div class="postbox" style="margin:0;">
					<h2 class="hndle" style="padding:12px 14px;margin:0;border-bottom:1px solid #c3c4c7;">
						<?php echo esc_html( $row['title'] ); ?>
					</h2>
					<div class="inside" style="padding:14px;">
						<p style="margin-top:0;"><?php echo esc_html( $row['desc'] ); ?></p>
						<p style="margin-bottom:0;">
							<?php if ( $can ) : ?>
								<a class="button button-primary" href="<?php echo esc_url( $row['edit'] ); ?>"><?php esc_html_e( 'Edit this section', 'kana-mud-resort' ); ?></a>
							<?php else : ?>
								<span class="button button-disabled" aria-disabled="true"><?php esc_html_e( 'Administrator only', 'kana-mud-resort' ); ?></span>
								<span class="description" style="display:block;margin-top:8px;">
									<?php esc_html_e( 'Ask a site administrator to change hero text, images, booking links, and contact details.', 'kana-mud-resort' ); ?>
								</span>
							<?php endif; ?>
						</p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<p class="description" style="margin-top:20px;max-width:720px;">
			<?php esc_html_e( 'Tip: After changing content, visit the public site in a private browser window if a cache plugin or host cache still shows old images.', 'kana-mud-resort' ); ?>
		</p>
	</div>
	<?php
}

add_action( 'admin_bar_menu', 'kmr_admin_bar_site_hub', 100 );

/**
 * Logged-in users: quick link to section hub from the public homepage.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function kmr_admin_bar_site_hub( WP_Admin_Bar $wp_admin_bar ): void {
	if ( is_admin() || ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}
	$wp_admin_bar->add_node(
		[
			'id'    => 'kmr-site-hub',
			'title' => __( 'Resort site (edit sections)', 'kana-mud-resort' ),
			'href'  => admin_url( 'admin.php?page=kmr-site-hub' ),
			'meta'  => [ 'class' => 'kmr-site-hub-ab' ],
		]
	);
}
