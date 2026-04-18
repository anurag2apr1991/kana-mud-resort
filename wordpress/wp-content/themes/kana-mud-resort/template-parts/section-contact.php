<?php
/**
 * Contact section.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$email   = kmr_get_option( 'contact_email', '' );
$phone   = kmr_get_option( 'contact_phone', '' );
$address = kmr_get_option( 'contact_address', '' );
$hours   = kmr_get_option( 'contact_hours', '' );
$map_raw = (string) kmr_get_option( 'contact_map_embed_url', '' );

$has_iframe = str_contains( $map_raw, '<' );
$map_src    = $has_iframe ? null : kmr_normalize_map_url( $map_raw );
?>
<section id="contact" class="scroll-mt-28 bg-stone-50 py-20 sm:py-28">
	<div class="mx-auto max-w-6xl px-4 sm:px-6">
		<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800"><?php esc_html_e( 'Visit', 'kana-mud-resort' ); ?></p>
		<h2 class="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl"><?php esc_html_e( 'Contact & location', 'kana-mud-resort' ); ?></h2>
		<p class="mt-4 max-w-2xl text-stone-600">
			<?php esc_html_e( 'Reach us by phone or email, find directions below, and plan your arrival with confidence.', 'kana-mud-resort' ); ?>
		</p>
		<div class="mt-12 grid gap-10 lg:grid-cols-2 lg:items-stretch">
			<div class="space-y-6 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-stone-200/80">
				<?php if ( $address ) : ?>
					<div>
						<h3 class="text-sm font-semibold uppercase tracking-wide text-stone-500"><?php esc_html_e( 'Address', 'kana-mud-resort' ); ?></h3>
						<p class="mt-2 whitespace-pre-line text-stone-800"><?php echo esc_html( $address ); ?></p>
					</div>
				<?php else : ?>
					<p class="text-stone-500"><?php esc_html_e( 'Address details will appear here soon.', 'kana-mud-resort' ); ?></p>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<div>
						<h3 class="text-sm font-semibold uppercase tracking-wide text-stone-500"><?php esc_html_e( 'Phone', 'kana-mud-resort' ); ?></h3>
						<a href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $phone ) ); ?>" class="mt-2 inline-block text-lg font-medium text-emerald-800 hover:underline"><?php echo esc_html( $phone ); ?></a>
					</div>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<div>
						<h3 class="text-sm font-semibold uppercase tracking-wide text-stone-500"><?php esc_html_e( 'Email', 'kana-mud-resort' ); ?></h3>
						<a href="<?php echo esc_url( 'mailto:' . $email ); ?>" class="mt-2 inline-block font-medium text-emerald-800 hover:underline"><?php echo esc_html( $email ); ?></a>
					</div>
				<?php endif; ?>
				<?php if ( $hours ) : ?>
					<div>
						<h3 class="text-sm font-semibold uppercase tracking-wide text-stone-500"><?php esc_html_e( 'Hours', 'kana-mud-resort' ); ?></h3>
						<p class="mt-2 whitespace-pre-line text-stone-700"><?php echo esc_html( $hours ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<div class="relative isolate min-h-[280px] w-full overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-stone-200/80 max-lg:aspect-[4/3] lg:h-full lg:min-h-[22rem]">
				<?php if ( $has_iframe ) : ?>
					<div class="absolute inset-0 [&_iframe]:!h-full [&_iframe]:!w-full [&_iframe]:max-h-none [&_iframe]:border-0">
						<?php echo $map_raw; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized on save. ?>
					</div>
				<?php elseif ( $map_src ) : ?>
					<iframe title="<?php esc_attr_e( 'Map', 'kana-mud-resort' ); ?>" src="<?php echo esc_url( $map_src ); ?>" class="absolute inset-0 h-full w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				<?php else : ?>
					<div class="flex h-full min-h-[12rem] items-center justify-center p-8 text-center text-stone-500">
						<?php esc_html_e( 'Map preview is not available yet. Use the address on the left for directions.', 'kana-mud-resort' ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
