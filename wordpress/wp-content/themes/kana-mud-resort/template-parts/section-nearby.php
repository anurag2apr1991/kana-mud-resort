<?php
/**
 * Nearby places (paginated in JS, 3 per page).
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$places = get_posts(
	[
		'post_type'      => 'kmr_nearby',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	]
);
?>
<section id="nearby" class="scroll-mt-28 bg-stone-50 py-20 sm:py-28">
	<div class="mx-auto max-w-6xl px-4 sm:px-6">
		<?php if ( ! count( $places ) ) : ?>
			<h2 class="font-serif text-3xl text-stone-900 sm:text-4xl"><?php echo esc_html( kmr_text( 'section_nearby_empty_title', __( 'Nearby places', 'kana-mud-resort' ) ) ); ?></h2>
			<p class="mt-4 text-stone-600"><?php echo esc_html( kmr_text( 'section_nearby_empty_message', __( 'Nearby walks, villages, and viewpoints will be listed here soon.', 'kana-mud-resort' ) ) ); ?></p>
		<?php else : ?>
			<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800"><?php echo esc_html( kmr_text( 'section_nearby_eyebrow', __( 'Explore', 'kana-mud-resort' ) ) ); ?></p>
			<h2 class="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl"><?php echo esc_html( kmr_text( 'section_nearby_title', __( 'Nearby places', 'kana-mud-resort' ) ) ); ?></h2>
			<p class="mt-4 max-w-2xl text-lg leading-relaxed text-stone-600">
				<?php echo esc_html( kmr_text( 'section_nearby_intro', __( 'Mussoorie, ridge walks, and the villages along the slopes are within easy reach—sunset viewpoints, craft corners, and day trips you can pair with slow days at the retreat.', 'kana-mud-resort' ) ) ); ?>
			</p>
			<?php
			$total_pages = (int) ceil( count( $places ) / 3 );
			?>
			<div class="kmr-nearby" data-kmr-per-page="3">
				<?php if ( $total_pages > 1 ) : ?>
					<div class="mt-8 flex items-center justify-between kmr-nearby-toolbar">
						<p class="text-sm font-medium text-stone-600">
							<?php esc_html_e( 'Showing', 'kana-mud-resort' ); ?>
							<span class="kmr-nearby-current">1</span> <?php esc_html_e( 'of', 'kana-mud-resort' ); ?>
							<span class="kmr-nearby-total"><?php echo esc_html( (string) $total_pages ); ?></span>
						</p>
						<div class="flex items-center gap-2">
							<button type="button" class="kmr-nearby-prev h-9 w-9 rounded-full border border-stone-300 bg-white text-stone-700 transition hover:border-stone-400 disabled:cursor-not-allowed disabled:opacity-40" aria-label="<?php esc_attr_e( 'Previous nearby places', 'kana-mud-resort' ); ?>">&lt;</button>
							<button type="button" class="kmr-nearby-next h-9 w-9 rounded-full border border-stone-300 bg-white text-stone-700 transition hover:border-stone-400 disabled:cursor-not-allowed disabled:opacity-40" aria-label="<?php esc_attr_e( 'Next nearby places', 'kana-mud-resort' ); ?>">&gt;</button>
						</div>
					</div>
				<?php endif; ?>
				<div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 kmr-nearby-grid">
					<?php foreach ( $places as $i => $p ) : ?>
						<?php
						$pid      = (int) $p->ID;
						$dist     = (string) get_post_meta( $pid, '_kmr_distance_label', true );
						$desc     = (string) get_post_meta( $pid, '_kmr_description', true );
						$body     = $p->post_content ? apply_filters( 'the_content', $p->post_content ) : '';
						$thumb_id = (int) get_post_thumbnail_id( $pid );
						$src      = kmr_image_url( $thumb_id, 'medium_large' );
						if ( ! $src ) {
							$src = kmr_image_url( $thumb_id, 'medium' );
						}
						?>
						<article class="kmr-nearby-card flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200/80 <?php echo $i >= 3 ? 'hidden' : ''; ?>" data-kmr-nearby-index="<?php echo esc_attr( (string) $i ); ?>">
							<div class="relative aspect-[16/10] w-full shrink-0 bg-stone-100">
								<?php if ( $src ) : ?>
									<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( get_the_title( $p ) ); ?>" class="h-full w-full object-cover" loading="lazy" decoding="async" />
								<?php else : ?>
									<div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-stone-200 via-stone-100 to-stone-200 text-sm font-medium text-stone-500"><?php esc_html_e( 'Set Featured image in admin', 'kana-mud-resort' ); ?></div>
								<?php endif; ?>
							</div>
							<div class="flex flex-1 flex-col p-5">
								<?php if ( $dist !== '' ) : ?>
									<p class="text-xs font-semibold uppercase tracking-wide text-emerald-800"><?php echo esc_html( $dist ); ?></p>
								<?php endif; ?>
								<h3 class="mt-1 font-serif text-xl text-stone-900"><?php echo esc_html( get_the_title( $p ) ); ?></h3>
								<?php if ( $body ) : ?>
									<div class="kmr-nearby-card-text mt-3 flex-1 text-sm leading-relaxed text-stone-600 [&_a]:text-emerald-700 [&_a]:underline [&_p]:mb-2 [&_p:last-child]:mb-0 [&_ul]:ml-4 [&_ul]:list-disc">
										<?php echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core the_content filters. ?>
									</div>
								<?php elseif ( $desc !== '' ) : ?>
									<p class="mt-3 flex-1 text-sm leading-relaxed text-stone-600"><?php echo esc_html( $desc ); ?></p>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
