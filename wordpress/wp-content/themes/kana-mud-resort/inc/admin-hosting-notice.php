<?php
/**
 * Post-activation help for shared hosting (GoDaddy, etc.).
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'kmr_on_theme_activation' );

/**
 * Flush rewrites and show setup notice once.
 */
function kmr_on_theme_activation(): void {
	flush_rewrite_rules( false );
	set_transient( 'kmr_show_activation_notice', 1, WEEK_IN_SECONDS );
	update_option( 'kmr_activation_notice_sent', KMR_VERSION, false );
}

add_action( 'admin_init', 'kmr_maybe_dismiss_hosting_notice' );

/**
 * Dismiss notice via query arg.
 */
function kmr_maybe_dismiss_hosting_notice(): void {
	if ( ! isset( $_GET['kmr_dismiss_notice'], $_GET['_wpnonce'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'kmr_dismiss_notice' ) ) {
		return;
	}
	delete_transient( 'kmr_show_activation_notice' );
	wp_safe_redirect( admin_url() );
	exit;
}

add_action(
	'admin_init',
	static function (): void {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$sent = get_option( 'kmr_activation_notice_sent', '' );
		if ( $sent === KMR_VERSION ) {
			return;
		}
		set_transient( 'kmr_show_activation_notice', 1, WEEK_IN_SECONDS );
		update_option( 'kmr_activation_notice_sent', KMR_VERSION, false );
	}
);

add_action( 'admin_notices', 'kmr_render_hosting_notice' );

/**
 * Admin notice with troubleshooting steps.
 */
function kmr_render_hosting_notice(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! get_transient( 'kmr_show_activation_notice' ) ) {
		return;
	}
	$dismiss = wp_nonce_url(
		admin_url( 'index.php?kmr_dismiss_notice=1' ),
		'kmr_dismiss_notice'
	);
	$resort  = admin_url( 'themes.php?page=kmr-home-settings' );
	$home    = home_url( '/' );
	?>
	<div class="notice notice-info is-dismissible" data-kmr-notice>
		<p><strong><?php esc_html_e( 'Kana Mud Resort theme is active.', 'kana-mud-resort' ); ?></strong></p>
		<ol style="margin-left:1.25em;list-style:decimal;">
			<li>
				<?php
				printf(
					/* translators: %s: URL to public site */
					wp_kses_post( __( 'Open your <a href="%s">public site</a> in a private/incognito window (avoids old cache).', 'kana-mud-resort' ) ),
					esc_url( $home )
				);
				?>
			</li>
			<li>
				<?php
				printf(
					/* translators: %s: Resort Home URL */
					wp_kses_post( __( 'Edit content under <a href="%s"><strong>Appearance → Resort Home</strong></a> and the left menu (Rooms, Gallery, …). The home page is <strong>not</strong> edited under Pages.', 'kana-mud-resort' ) ),
					esc_url( $resort )
				);
				?>
			</li>
			<li><?php esc_html_e( 'Go to Settings → Permalinks and click Save once (fixes odd URLs on some hosts).', 'kana-mud-resort' ); ?></li>
			<li><?php esc_html_e( 'In your hosting panel, set PHP to 8.0 or newer if you see a blank page or critical error.', 'kana-mud-resort' ); ?></li>
			<li><?php esc_html_e( 'Clear any host or CDN cache (GoDaddy often caches the old theme).', 'kana-mud-resort' ); ?></li>
		</ol>
		<p>
			<a href="<?php echo esc_url( $dismiss ); ?>" class="button"><?php esc_html_e( 'Dismiss this notice', 'kana-mud-resort' ); ?></a>
		</p>
	</div>
	<?php
}
