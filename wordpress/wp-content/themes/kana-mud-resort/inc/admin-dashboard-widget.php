<?php
/**
 * Dashboard widget: where to edit this one-page theme.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_dashboard_setup', 'kmr_register_dashboard_widget' );

/**
 * Register dashboard help widget for admins.
 */
function kmr_register_dashboard_widget(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	wp_add_dashboard_widget(
		'kmr_edit_site_guide',
		__( 'Kana Mud Resort — Where to edit the site', 'kana-mud-resort' ),
		'kmr_render_dashboard_widget'
	);
}

/**
 * Output widget HTML.
 */
function kmr_render_dashboard_widget(): void {
	$hub    = admin_url( 'admin.php?page=kmr-site-hub' );
	$resort = admin_url( 'themes.php?page=kmr-home-settings' );
	$is_mgr = current_user_can( 'manage_options' );
	?>
	<p class="description">
		<?php esc_html_e( 'This theme is one scrolling page, but each section has its own editor — not a single HTML file or the block editor on Pages → Home.', 'kana-mud-resort' ); ?>
	</p>
	<p>
		<a class="button button-primary" href="<?php echo esc_url( $hub ); ?>"><?php esc_html_e( 'Open Resort site (section map)', 'kana-mud-resort' ); ?></a>
	</p>
	<ul style="list-style:disc;margin-left:1.25em;">
		<?php if ( $is_mgr ) : ?>
		<li>
			<strong><a href="<?php echo esc_url( $resort ); ?>"><?php esc_html_e( 'Appearance → Resort Home', 'kana-mud-resort' ); ?></a></strong>
			— <?php esc_html_e( 'hero text, hero images, booking buttons, contact, map, header name & menu JSON.', 'kana-mud-resort' ); ?>
		</li>
		<?php else : ?>
		<li>
			<?php esc_html_e( 'Hero, booking bar, and contact details are edited by an administrator under Appearance → Resort Home.', 'kana-mud-resort' ); ?>
		</li>
		<?php endif; ?>
		<li>
			<strong><?php esc_html_e( 'Rooms, Gallery, Nearby places, Amenities, Offers & packages, Guests', 'kana-mud-resort' ); ?></strong>
			— <?php esc_html_e( 'each has its own menu on the left. Set featured images where needed.', 'kana-mud-resort' ); ?>
		</li>
		<?php if ( $is_mgr ) : ?>
		<li>
			<strong><?php esc_html_e( 'Settings → Reading', 'kana-mud-resort' ); ?></strong>
			— <?php esc_html_e( '“Your homepage displays” can stay on latest posts; the theme still shows the full landing.', 'kana-mud-resort' ); ?>
		</li>
		<?php endif; ?>
	</ul>
	<?php if ( $is_mgr ) : ?>
	<p class="description">
		<?php esc_html_e( 'If saving fails, ask your host to allow POST to wp-admin, check PHP memory (128MB+), and temporarily disable caching/security plugins to test.', 'kana-mud-resort' ); ?>
	</p>
	<?php endif; ?>
	<?php
}
