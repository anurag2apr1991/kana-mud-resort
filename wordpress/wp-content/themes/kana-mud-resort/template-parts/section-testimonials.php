<?php
/**
 * Testimonials section.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$items = get_posts(
	[
		'post_type'      => 'kmr_testimonial',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	]
);
$ts_eye = kmr_text( 'section_testimonials_eyebrow', __( 'Guests', 'kana-mud-resort' ) );
$ts_ttl = kmr_text( 'section_testimonials_title', __( 'What visitors say', 'kana-mud-resort' ) );
$ts_emp = kmr_text( 'section_testimonials_empty_message', __( 'Guest stories will appear here soon.', 'kana-mud-resort' ) );
?>
<section id="testimonials" class="scroll-mt-28 bg-stone-100 py-20 sm:py-28">
	<div class="mx-auto max-w-6xl px-4 sm:px-6">
		<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800"><?php echo esc_html( $ts_eye ); ?></p>
		<h2 class="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl"><?php echo esc_html( $ts_ttl ); ?></h2>
		<?php if ( ! count( $items ) ) : ?>
			<p class="mt-6 max-w-2xl text-stone-600"><?php echo esc_html( $ts_emp ); ?></p>
		<?php else : ?>
			<div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
				<?php foreach ( $items as $p ) : ?>
					<?php
					$pid    = (int) $p->ID;
					$quote  = (string) get_post_meta( $pid, '_kmr_quote', true );
					$title  = (string) get_post_meta( $pid, '_kmr_author_title', true );
					$rating = (int) get_post_meta( $pid, '_kmr_rating', true );
					$av_id  = (int) get_post_thumbnail_id( $pid );
					$av_src = kmr_image_url( $av_id, 'thumbnail' );
					$name   = get_the_title( $p );
					$initial = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );
					?>
					<blockquote class="flex h-full flex-col rounded-3xl bg-white p-6 shadow-sm ring-1 ring-stone-200/80">
						<?php if ( $rating > 0 ) : ?>
							<div class="mb-4 text-sm"><?php echo kmr_stars_html( $rating ); ?></div>
						<?php endif; ?>
						<p class="flex-1 text-lg leading-relaxed text-stone-700">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
						<footer class="mt-6 flex items-center gap-3 border-t border-stone-100 pt-6">
							<?php if ( $av_src ) : ?>
								<div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-full bg-stone-200">
									<img src="<?php echo esc_url( $av_src ); ?>" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async" />
								</div>
							<?php else : ?>
								<div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-900" aria-hidden="true"><?php echo esc_html( $initial ); ?></div>
							<?php endif; ?>
							<div>
								<cite class="not-italic font-semibold text-stone-900"><?php echo esc_html( $name ); ?></cite>
								<?php if ( $title ) : ?>
									<p class="text-sm text-stone-500"><?php echo esc_html( $title ); ?></p>
								<?php endif; ?>
							</div>
						</footer>
					</blockquote>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
