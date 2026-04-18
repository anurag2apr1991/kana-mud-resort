<?php
/**
 * Contact section: (1) paragraph-style address & phone, (2) Google Map embed.
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

$c_eye = kmr_text( 'section_contact_eyebrow', __( 'Visit', 'kana-mud-resort' ) );
$c_ttl = kmr_text( 'section_contact_title', __( 'Contact & location', 'kana-mud-resort' ) );
$c_int = kmr_text( 'section_contact_intro', __( 'Reach us by phone or email, then use the map below for directions.', 'kana-mud-resort' ) );

$kmr_contact_defaults = kmr_default_options();
$det_h                = trim( (string) kmr_get_option( 'section_contact_details_heading', $kmr_contact_defaults['section_contact_details_heading'] ?? '' ) );
$map_h                = trim( (string) kmr_get_option( 'section_contact_map_heading', $kmr_contact_defaults['section_contact_map_heading'] ?? '' ) );

$c_adr = kmr_text( 'section_contact_address_placeholder', __( 'Address details will appear here soon.', 'kana-mud-resort' ) );
$c_map = kmr_text( 'section_contact_map_placeholder', __( 'Add a Google Maps embed under Contact & map → Map embed URL or iframe HTML.', 'kana-mud-resort' ) );
$lb_ad = kmr_text( 'section_contact_label_address', __( 'Address', 'kana-mud-resort' ) );
$lb_ph = kmr_text( 'section_contact_label_phone', __( 'Phone', 'kana-mud-resort' ) );
$lb_em = kmr_text( 'section_contact_label_email', __( 'Email', 'kana-mud-resort' ) );
$lb_hr = kmr_text( 'section_contact_label_hours', __( 'Hours', 'kana-mud-resort' ) );
?>
<section id="contact" class="scroll-mt-28 bg-stone-50 py-20 sm:py-28">
	<div class="mx-auto max-w-6xl px-4 sm:px-6">
		<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800"><?php echo esc_html( $c_eye ); ?></p>
		<h2 class="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl"><?php echo esc_html( $c_ttl ); ?></h2>
		<p class="mt-4 max-w-2xl text-stone-600">
			<?php echo esc_html( $c_int ); ?>
		</p>

		<?php /* —— Section 1: Address & phone (paragraph layout) —— */ ?>
		<div class="kmr-contact-details mt-14 border-t border-stone-200/90 pt-14">
			<?php if ( $det_h !== '' ) : ?>
				<h3 class="font-serif text-2xl text-stone-900"><?php echo esc_html( $det_h ); ?></h3>
			<?php endif; ?>
			<div class="<?php echo $det_h !== '' ? 'mt-6' : ''; ?> max-w-3xl space-y-6 text-lg leading-relaxed text-stone-700">
				<?php if ( $address !== '' ) : ?>
					<p class="whitespace-pre-line">
						<span class="font-semibold text-stone-900"><?php echo esc_html( $lb_ad ); ?></span>
						<?php echo "\n"; ?>
						<?php echo esc_html( $address ); ?>
					</p>
				<?php else : ?>
					<p class="text-stone-500"><?php echo esc_html( $c_adr ); ?></p>
				<?php endif; ?>

				<?php if ( $phone !== '' ) : ?>
					<p>
						<span class="font-semibold text-stone-900"><?php echo esc_html( $lb_ph ); ?></span>
						<?php echo ' '; ?>
						<a href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $phone ) ); ?>" class="font-medium text-emerald-800 underline decoration-emerald-800/30 underline-offset-2 hover:decoration-emerald-800"><?php echo esc_html( $phone ); ?></a>
					</p>
				<?php endif; ?>

				<?php if ( $email !== '' ) : ?>
					<p>
						<span class="font-semibold text-stone-900"><?php echo esc_html( $lb_em ); ?></span>
						<?php echo ' '; ?>
						<a href="<?php echo esc_url( 'mailto:' . $email ); ?>" class="font-medium text-emerald-800 underline decoration-emerald-800/30 underline-offset-2 hover:decoration-emerald-800"><?php echo esc_html( $email ); ?></a>
					</p>
				<?php endif; ?>

				<?php if ( $hours !== '' ) : ?>
					<p class="whitespace-pre-line">
						<span class="font-semibold text-stone-900"><?php echo esc_html( $lb_hr ); ?></span>
						<?php echo "\n"; ?>
						<?php echo esc_html( $hours ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<?php /* —— Section 2: Google Map —— */ ?>
		<div class="kmr-contact-map mt-16 border-t border-stone-200/90 pt-16 lg:mt-20 lg:pt-20">
			<?php if ( $map_h !== '' ) : ?>
				<h3 class="font-serif text-2xl text-stone-900"><?php echo esc_html( $map_h ); ?></h3>
			<?php endif; ?>
			<div class="<?php echo $map_h !== '' ? 'mt-8' : ''; ?> relative isolate w-full overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-stone-200/80">
				<div class="relative aspect-[4/3] w-full min-h-[280px] sm:min-h-[320px] lg:aspect-[21/9] lg:min-h-[360px]">
					<?php if ( $has_iframe ) : ?>
						<div class="absolute inset-0 [&_iframe]:!h-full [&_iframe]:!w-full [&_iframe]:max-h-none [&_iframe]:border-0">
							<?php echo $map_raw; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized on save. ?>
						</div>
					<?php elseif ( $map_src ) : ?>
						<iframe title="<?php echo esc_attr( $map_h !== '' ? $map_h : __( 'Google Map', 'kana-mud-resort' ) ); ?>" src="<?php echo esc_url( $map_src ); ?>" class="absolute inset-0 h-full w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
					<?php else : ?>
						<div class="flex h-full min-h-[12rem] items-center justify-center p-8 text-center text-stone-500">
							<?php echo esc_html( $c_map ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
