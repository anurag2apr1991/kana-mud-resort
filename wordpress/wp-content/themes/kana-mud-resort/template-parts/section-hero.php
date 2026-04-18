<?php
/**
 * Hero section.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$eyebrow = kmr_get_option( 'hero_eyebrow', '' );
$title   = kmr_get_option( 'hero_title', __( 'A quiet place to arrive and breathe.', 'kana-mud-resort' ) );
$sub     = kmr_get_option( 'hero_subtitle', '' );
$cta     = kmr_get_option( 'hero_cta_label', __( 'Explore rooms', 'kana-mud-resort' ) );
$target  = kmr_get_option( 'hero_cta_target_section', 'rooms' );
$target  = $target ? preg_replace( '/[^a-z0-9_-]/i', '', $target ) : 'rooms';

$urls           = kmr_resolve_hero_image_urls();
$urls_json      = wp_json_encode( $urls );
$hero_has_media = count( kmr_parse_id_list( (string) kmr_get_option( 'hero_background_ids', '' ) ) ) > 0;
?>
<section id="hero" class="relative flex min-h-[100svh] flex-col justify-end pb-16 pt-28 sm:pb-24">
	<?php if ( ! $hero_has_media && count( $urls ) && current_user_can( 'manage_options' ) ) : ?>
		<div class="absolute left-4 right-4 top-24 z-20 sm:left-6 sm:right-6 lg:left-1/2 lg:right-auto lg:w-full lg:max-w-6xl lg:-translate-x-1/2 lg:px-6">
			<p class="rounded-2xl border border-amber-200/90 bg-amber-50/95 px-4 py-3 text-sm text-amber-950 shadow-sm backdrop-blur-sm">
				<?php esc_html_e( 'Demo hero images are shown until you add Media Library attachment IDs under Appearance → Resort Home. This note is visible only to administrators.', 'kana-mud-resort' ); ?>
			</p>
		</div>
	<?php endif; ?>
	<?php if ( count( $urls ) ) : ?>
		<div
			class="kmr-carousel absolute inset-0 z-0 overflow-hidden"
			data-kmr-carousel
			data-kmr-hero="1"
			data-kmr-interval="6000"
			data-kmr-lightbox="0"
			data-images="<?php echo esc_attr( $urls_json ); ?>"
		></div>
	<?php else : ?>
		<div class="absolute inset-0 z-0 bg-gradient-to-br from-emerald-950 via-stone-800 to-stone-900" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="pointer-events-none absolute inset-0 z-[5] bg-gradient-to-t from-stone-950/90 via-stone-900/40 to-stone-900/30" aria-hidden="true"></div>
	<div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6">
		<?php if ( $eyebrow ) : ?>
			<p class="mb-3 text-base font-semibold uppercase tracking-[0.25em] text-emerald-200/90"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>
		<h1 class="max-w-3xl font-serif text-4xl font-medium leading-tight text-white sm:text-5xl md:text-6xl"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $sub ) : ?>
			<p class="mt-6 max-w-xl text-lg leading-relaxed text-stone-200 sm:text-xl"><?php echo esc_html( $sub ); ?></p>
		<?php endif; ?>
		<div class="mt-10 flex flex-wrap gap-4">
			<a href="#<?php echo esc_attr( $target ); ?>" class="inline-flex rounded-full bg-white px-6 py-3 text-sm font-semibold text-stone-900 shadow-lg transition hover:bg-stone-100"><?php echo esc_html( $cta ); ?></a>
			<a href="#contact" class="inline-flex rounded-full border border-white/40 px-6 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10"><?php esc_html_e( 'Plan your visit', 'kana-mud-resort' ); ?></a>
		</div>
	</div>
</section>
