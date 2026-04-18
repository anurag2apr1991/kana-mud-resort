<?php
/**
 * Footer.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$primary_label   = kmr_get_option( 'booking_primary_label', __( 'Book', 'kana-mud-resort' ) );
$primary_url     = kmr_booking_primary_href( kmr_get_option( 'booking_primary_url', '#contact' ) );
$secondary_label = kmr_get_option( 'booking_secondary_label', '' );
$secondary_url   = trim( (string) kmr_get_option( 'booking_secondary_url', '' ) );
$footer_note = kmr_get_option( 'booking_footer_note', __( 'Himalayan-style calm, earth-built simplicity, and warm hospitality near Mussoorie.', 'kana-mud-resort' ) );

$footer_brand = trim( (string) kmr_get_option( 'footer_brand_name', '' ) );
if ( $footer_brand === '' ) {
	$footer_brand = kmr_get_option( 'site_brand_name', __( 'Kana Mud Resort', 'kana-mud-resort' ) );
}
$footer_legal = kmr_text( 'footer_legal_text', __( 'Kana Mud Resort. All rights reserved.', 'kana-mud-resort' ) );

$primary_target = '';
$primary_rel    = '';
if ( str_starts_with( $primary_url, 'http' ) ) {
	$primary_target = '_blank';
	$primary_rel    = 'noopener noreferrer';
}
?>
	<footer class="border-t border-stone-200 bg-stone-900 py-12 text-stone-300">
		<div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
			<div>
				<p class="font-serif text-lg text-white"><?php echo esc_html( $footer_brand ); ?></p>
				<p class="mt-2 max-w-md text-sm leading-relaxed text-stone-400"><?php echo esc_html( $footer_note ); ?></p>
			</div>
			<div class="flex flex-wrap gap-4">
				<a href="<?php echo kmr_esc_href( $primary_url ); ?>" <?php echo $primary_target ? ' target="' . esc_attr( $primary_target ) . '"' : ''; ?> <?php echo $primary_rel ? ' rel="' . esc_attr( $primary_rel ) . '"' : ''; ?> class="rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-stone-900 transition hover:bg-stone-100"><?php echo esc_html( $primary_label ); ?></a>
				<?php if ( $secondary_url ) : ?>
					<a href="<?php echo kmr_esc_href( $secondary_url ); ?>" target="_blank" rel="noopener noreferrer" class="rounded-full border border-stone-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:border-stone-400"><?php echo esc_html( $secondary_label ?: __( 'WhatsApp concierge', 'kana-mud-resort' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<p class="mx-auto mt-10 max-w-6xl px-4 text-center text-xs text-stone-500 sm:px-6">
			© <?php echo esc_html( (string) gmdate( 'Y' ) ); ?> <?php echo esc_html( $footer_legal ); ?>
		</p>
	</footer>
	<?php wp_footer(); ?>
</body>
</html>
