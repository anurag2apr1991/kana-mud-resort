<?php
/**
 * Gallery section.
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
	$url = kmr_image_url( $aid, 'large' );
	if ( ! $url ) {
		continue;
	}
	$caption = (string) get_post_meta( $pid, '_kmr_caption', true );
	if ( ! $caption ) {
		$caption = get_the_title( $pid );
	}
	$items[] = [
		'id'      => $pid,
		'src'     => $url,
		'alt'     => $caption,
		'caption' => $caption,
	];
}

$gallery_demo = false;
if ( ! count( $items ) && kmr_use_demo_assets() ) {
	$items        = kmr_demo_gallery_items();
	$gallery_demo = true;
}
?>
<section id="gallery" class="scroll-mt-28 bg-white py-20 sm:py-28<?php echo $gallery_demo ? ' kmr-section--demo' : ''; ?>">
	<div class="mx-auto max-w-6xl px-4 sm:px-6">
		<?php if ( ! count( $items ) ) : ?>
			<h2 class="font-serif text-3xl text-stone-900 sm:text-4xl"><?php echo esc_html( kmr_text( 'section_gallery_empty_title', __( 'Gallery', 'kana-mud-resort' ) ) ); ?></h2>
			<p class="mt-4 text-stone-600"><?php echo esc_html( kmr_text( 'section_gallery_empty_message', __( 'New photos of the property will appear here soon.', 'kana-mud-resort' ) ) ); ?></p>
		<?php else : ?>
			<?php if ( $gallery_demo && current_user_can( 'manage_options' ) ) : ?>
				<p class="mb-6 rounded-2xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-950">
					<?php esc_html_e( 'Demo photos are shown until you add entries under Gallery photos and set featured images. This note is visible only to administrators.', 'kana-mud-resort' ); ?>
				</p>
			<?php endif; ?>
			<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800"><?php echo esc_html( kmr_text( 'section_gallery_eyebrow', __( 'Moments', 'kana-mud-resort' ) ) ); ?></p>
			<h2 class="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl"><?php echo esc_html( kmr_text( 'section_gallery_title', __( 'Around the property', 'kana-mud-resort' ) ) ); ?></h2>
			<p class="mt-4 max-w-2xl text-lg leading-relaxed text-stone-600">
				<?php echo esc_html( kmr_text( 'section_gallery_intro', __( 'Mud walls, forest light, courtyards, and paths you will want to remember — a quiet look at the retreat before you arrive.', 'kana-mud-resort' ) ) ); ?>
			</p>
			<div class="kmr-gallery-grid mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-kmr-gallery="<?php echo esc_attr( wp_json_encode( $items ) ); ?>">
				<?php foreach ( $items as $idx => $it ) : ?>
					<figure class="overflow-hidden rounded-2xl bg-stone-100 shadow-sm ring-1 ring-stone-200/80">
						<div class="relative aspect-[4/3] w-full">
							<button type="button" class="group relative block h-full w-full cursor-zoom-in" data-kmr-gallery-open="<?php echo esc_attr( (string) $idx ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s caption */ __( 'View larger: %s', 'kana-mud-resort' ), $it['caption'] ) ); ?>">
								<img src="<?php echo esc_url( $it['src'] ); ?>" alt="<?php echo esc_attr( $it['alt'] ); ?>" class="h-full w-full object-cover transition duration-300 group-hover:brightness-95" loading="lazy" decoding="async" />
							</button>
						</div>
						<?php if ( $it['caption'] ) : ?>
							<figcaption class="px-4 py-3 text-sm text-stone-600"><?php echo esc_html( $it['caption'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
