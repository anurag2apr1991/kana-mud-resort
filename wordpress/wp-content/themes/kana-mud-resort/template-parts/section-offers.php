<?php
/**
 * Offers section.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$offers = get_posts(
	[
		'post_type'      => 'kmr_offer',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	]
);
?>
<section id="offers" class="scroll-mt-28 bg-white py-20 sm:py-28">
	<div class="mx-auto max-w-6xl px-4 sm:px-6">
		<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800"><?php esc_html_e( 'Value', 'kana-mud-resort' ); ?></p>
		<h2 class="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl"><?php esc_html_e( 'Offers & packages', 'kana-mud-resort' ); ?></h2>
		<?php if ( ! count( $offers ) ) : ?>
			<p class="mt-6 max-w-2xl text-stone-600">
				<?php esc_html_e( 'Seasonal packages and special rates will be listed here when available. Ask us about current offers.', 'kana-mud-resort' ); ?>
			</p>
		<?php else : ?>
			<div class="mt-12 grid gap-8 lg:grid-cols-2">
				<?php foreach ( $offers as $p ) : ?>
					<?php
					$pid      = (int) $p->ID;
					$desc     = (string) get_post_meta( $pid, '_kmr_description', true );
					$badge    = (string) get_post_meta( $pid, '_kmr_badge', true );
					$until    = (string) get_post_meta( $pid, '_kmr_valid_until', true );
					$thumb_id = (int) get_post_thumbnail_id( $pid );
					$src      = kmr_image_url( $thumb_id, 'medium' );

					$until_fmt = '';
					if ( $until ) {
						$t = strtotime( $until . 'T00:00:00' );
						if ( $t ) {
							$until_fmt = date_i18n( 'M j, Y', $t );
						}
					}
					?>
					<article class="flex flex-col overflow-hidden rounded-3xl ring-1 ring-stone-200/80 sm:flex-row">
						<div class="relative aspect-[16/10] w-full bg-stone-100 sm:aspect-auto sm:w-2/5">
							<?php if ( $src ) : ?>
								<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( get_the_title( $p ) ); ?>" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" />
							<?php else : ?>
								<div class="flex min-h-[200px] h-full w-full items-center justify-center bg-gradient-to-br from-stone-200 via-stone-100 to-stone-200 text-sm font-medium text-stone-500"><?php esc_html_e( 'Offer image coming soon', 'kana-mud-resort' ); ?></div>
							<?php endif; ?>
						</div>
						<div class="flex flex-1 flex-col p-6 sm:p-8">
							<?php if ( $badge ) : ?>
								<span class="w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-900"><?php echo esc_html( $badge ); ?></span>
							<?php endif; ?>
							<h3 class="mt-3 font-serif text-2xl text-stone-900"><?php echo esc_html( get_the_title( $p ) ); ?></h3>
							<?php if ( $desc ) : ?>
								<p class="mt-3 text-stone-600"><?php echo esc_html( $desc ); ?></p>
							<?php endif; ?>
							<?php if ( $until_fmt ) : ?>
								<p class="mt-4 text-sm text-stone-500">
									<?php
									printf(
										/* translators: %s: formatted date */
										esc_html__( 'Valid through %s', 'kana-mud-resort' ),
										esc_html( $until_fmt )
									);
									?>
								</p>
							<?php endif; ?>
							<div class="mt-6">
								<a href="#contact" class="inline-flex rounded-full bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900"><?php esc_html_e( 'Enquire now', 'kana-mud-resort' ); ?></a>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
