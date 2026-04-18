<?php
/**
 * Gallery section ("Around the property").
 *
 * Up to six images per card grid; more than six use a paged slider. Images: Gallery (kmr_photo) + featured image.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$photos = get_posts(
	[
		'post_type'      => 'kmr_photo',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	]
);

$items = [];
foreach ( $photos as $p ) {
	$pid = (int) $p->ID;
	$aid = (int) get_post_thumbnail_id( $pid );
	$url_card = kmr_image_url( $aid, 'medium_large' );
	if ( ! $url_card ) {
		$url_card = kmr_image_url( $aid, 'large' );
	}
	if ( ! $url_card ) {
		continue;
	}
	$url_full = kmr_image_url( $aid, 'full' );
	if ( ! $url_full ) {
		$url_full = $url_card;
	}
	$caption = (string) get_post_meta( $pid, '_kmr_caption', true );
	if ( ! $caption ) {
		$caption = get_the_title( $pid );
	}
	$items[] = [
		'id'      => $pid,
		'src'     => $url_card,
		'full'    => $url_full,
		'alt'     => $caption,
		'caption' => $caption,
	];
}

$gallery_demo = false;
if ( ! count( $items ) && kmr_use_demo_assets() ) {
	$items        = kmr_demo_gallery_items();
	$gallery_demo = true;
}

$per_page     = 6;
$chunks       = count( $items ) ? array_chunk( $items, $per_page ) : [];
$page_count   = count( $chunks );
$slider_mode  = $page_count > 1;
$gallery_json = wp_json_encode( $items );
?>
<section id="gallery" class="scroll-mt-28 bg-white py-20 sm:py-28<?php echo $gallery_demo ? ' kmr-section--demo' : ''; ?>">
	<div class="mx-auto max-w-6xl px-4 sm:px-6">
		<?php if ( ! count( $items ) ) : ?>
			<h2 class="font-serif text-3xl text-stone-900 sm:text-4xl"><?php echo esc_html( kmr_text( 'section_gallery_empty_title', __( 'Gallery', 'kana-mud-resort' ) ) ); ?></h2>
			<p class="mt-4 text-stone-600"><?php echo esc_html( kmr_text( 'section_gallery_empty_message', __( 'New photos of the property will appear here soon.', 'kana-mud-resort' ) ) ); ?></p>
		<?php else : ?>
			<?php if ( $gallery_demo && current_user_can( 'manage_options' ) ) : ?>
				<p class="mb-6 rounded-2xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-950">
					<?php esc_html_e( 'Demo photos are shown until you add entries under Gallery and set featured images. This note is visible only to administrators.', 'kana-mud-resort' ); ?>
				</p>
			<?php endif; ?>
			<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800"><?php echo esc_html( kmr_text( 'section_gallery_eyebrow', __( 'Moments', 'kana-mud-resort' ) ) ); ?></p>
			<h2 class="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl"><?php echo esc_html( kmr_text( 'section_gallery_title', __( 'Around the property', 'kana-mud-resort' ) ) ); ?></h2>
			<p class="mt-4 max-w-2xl text-lg leading-relaxed text-stone-600">
				<?php echo esc_html( kmr_text( 'section_gallery_intro', __( 'Mud walls, forest light, courtyards, and paths you will want to remember — a quiet look at the retreat before you arrive.', 'kana-mud-resort' ) ) ); ?>
			</p>
			<?php if ( $slider_mode ) : ?>
				<p class="mt-3 text-sm text-stone-500">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: max photos per slide */
							__( 'Showing up to %d photos per slide. Use the arrows or dots to see more.', 'kana-mud-resort' ),
							$per_page
						)
					);
					?>
				</p>
			<?php endif; ?>

			<div
				class="mt-10 <?php echo $slider_mode ? 'kmr-gallery-slider relative' : ''; ?>"
				data-kmr-gallery="<?php echo esc_attr( $gallery_json ); ?>"
				<?php if ( $slider_mode ) : ?>
					data-kmr-gallery-slider="1"
					data-kmr-gallery-pages="<?php echo esc_attr( (string) $page_count ); ?>"
				<?php endif; ?>
			>
				<?php if ( $slider_mode ) : ?>
					<button
						type="button"
						class="kmr-gallery-slider-prev absolute left-0 top-[calc(50%-1.5rem)] z-10 flex h-11 w-11 -translate-x-1 items-center justify-center rounded-full border border-stone-200 bg-white text-2xl text-stone-800 shadow-sm transition hover:bg-stone-50 disabled:pointer-events-none disabled:opacity-30 max-sm:top-[calc(50%-2.75rem)]"
						aria-label="<?php esc_attr_e( 'Previous photos', 'kana-mud-resort' ); ?>"
					>
						‹
					</button>
					<button
						type="button"
						class="kmr-gallery-slider-next absolute right-0 top-[calc(50%-1.5rem)] z-10 flex h-11 w-11 translate-x-1 items-center justify-center rounded-full border border-stone-200 bg-white text-2xl text-stone-800 shadow-sm transition hover:bg-stone-50 disabled:pointer-events-none disabled:opacity-30 max-sm:top-[calc(50%-2.75rem)]"
						aria-label="<?php esc_attr_e( 'Next photos', 'kana-mud-resort' ); ?>"
					>
						›
					</button>
				<?php endif; ?>

				<div class="<?php echo $slider_mode ? 'overflow-hidden sm:mx-14' : ''; ?>">
					<div
						class="kmr-gallery-slider-track flex transition-transform duration-300 ease-out"
						style="width: <?php echo esc_attr( (string) ( $page_count * 100 ) ); ?>%;"
					>
						<?php
						$global_idx = 0;
						foreach ( $chunks as $chunk ) :
							$page_w = $page_count > 0 ? 100 / $page_count : 100;
							?>
							<div class="shrink-0 px-0.5" style="width: <?php echo esc_attr( (string) $page_w ); ?>%;">
								<div class="kmr-gallery-grid grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
									<?php foreach ( $chunk as $it ) : ?>
										<figure class="overflow-hidden rounded-2xl bg-stone-100 shadow-sm ring-1 ring-stone-200/80">
											<div class="relative aspect-[4/3] w-full">
												<button type="button" class="group relative block h-full w-full cursor-zoom-in" data-kmr-gallery-open="<?php echo esc_attr( (string) $global_idx ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s caption */ __( 'View larger: %s', 'kana-mud-resort' ), $it['caption'] ) ); ?>">
													<img src="<?php echo esc_url( $it['src'] ); ?>" alt="<?php echo esc_attr( $it['alt'] ); ?>" class="h-full w-full object-cover transition duration-300 group-hover:brightness-95" loading="lazy" decoding="async" />
												</button>
											</div>
											<?php if ( $it['caption'] ) : ?>
												<figcaption class="px-4 py-3 text-sm text-stone-600"><?php echo esc_html( $it['caption'] ); ?></figcaption>
											<?php endif; ?>
										</figure>
										<?php
										++$global_idx;
									endforeach;
									?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<?php if ( $slider_mode ) : ?>
					<div class="kmr-gallery-slider-dots mt-8 flex justify-center gap-2" role="tablist" aria-label="<?php esc_attr_e( 'Gallery pages', 'kana-mud-resort' ); ?>">
						<?php
						for ( $pi = 0; $pi < $page_count; $pi++ ) :
							?>
							<button
								type="button"
								role="tab"
								class="kmr-gallery-slider-dot h-2.5 w-2.5 rounded-full transition <?php echo 0 === $pi ? 'bg-emerald-700' : 'bg-stone-300'; ?>"
								aria-selected="<?php echo 0 === $pi ? 'true' : 'false'; ?>"
								aria-label="<?php echo esc_attr( sprintf( /* translators: %d page number */ __( 'Page %d', 'kana-mud-resort' ), $pi + 1 ) ); ?>"
								data-kmr-gallery-page="<?php echo esc_attr( (string) $pi ); ?>"
							></button>
						<?php endfor; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
