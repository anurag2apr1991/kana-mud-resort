<?php
/**
 * Amenities section — image + title + optional short text (no post editor).
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$amenities = get_posts(
	[
		'post_type'      => 'kmr_amenity',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	]
);
?>
<section id="amenities" class="scroll-mt-28 bg-emerald-950 py-20 text-stone-100 sm:py-28">
	<div class="mx-auto max-w-6xl px-4 sm:px-6">
		<?php
		$am_eye = kmr_text( 'amenities_eyebrow', __( 'Experience', 'kana-mud-resort' ) );
		$am_ttl = kmr_text( 'amenities_title', __( 'Amenities', 'kana-mud-resort' ) );
		$am_int = kmr_text( 'amenities_intro', __( 'A Himalayan-style retreat in spirit—wholesome meals, forest trails, crisp air, and space to do very little.', 'kana-mud-resort' ) );
		?>
		<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-300/90"><?php echo esc_html( $am_eye ); ?></p>
		<h2 class="mt-2 font-serif text-3xl sm:text-4xl"><?php echo esc_html( $am_ttl ); ?></h2>
		<p class="mt-4 max-w-2xl text-lg text-emerald-100/90">
			<?php echo esc_html( $am_int ); ?>
		</p>
		<?php if ( ! count( $amenities ) ) : ?>
			<p class="mt-8 text-emerald-200/80"><?php echo esc_html( kmr_text( 'amenities_empty_message', __( 'We are updating this list. Check back soon for the full amenity guide.', 'kana-mud-resort' ) ) ); ?></p>
		<?php else : ?>
			<ul class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php foreach ( $amenities as $p ) : ?>
					<?php
					$pid      = (int) $p->ID;
					$desc     = (string) get_post_meta( $pid, '_kmr_description', true );
					$icon     = (string) get_post_meta( $pid, '_kmr_icon_key', true );
					$thumb_id = (int) get_post_thumbnail_id( $pid );
					$img_src  = $thumb_id ? kmr_image_url( $thumb_id, 'medium' ) : '';
					if ( ! $img_src && $thumb_id ) {
						$img_src = kmr_image_url( $thumb_id, 'thumbnail' );
					}
					$glyph        = kmr_amenity_icon_glyph( $icon );
					$k            = strtolower( $icon ?: 'default' );
					$is_mountain  = in_array( $k, [ 'mountain', 'pool' ], true );
					$show_emoji   = ! $img_src && $glyph;
					?>
					<li class="flex gap-4 rounded-2xl bg-emerald-900/40 p-5 ring-1 ring-emerald-700/50">
						<span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-emerald-50 ring-1 ring-emerald-900/10" aria-hidden="true">
							<?php if ( $img_src ) : ?>
								<img src="<?php echo esc_url( $img_src ); ?>" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async" />
							<?php elseif ( $show_emoji ) : ?>
								<span class="inline-flex size-10 items-center justify-center leading-none text-stone-700 [font-feature-settings:normal] <?php echo $is_mountain ? 'text-[28px]' : 'text-[24px]'; ?>"><?php echo esc_html( $glyph ); ?></span>
							<?php else : ?>
								<span class="px-1 text-center text-[10px] font-medium uppercase leading-tight text-stone-500"><?php esc_html_e( 'Photo', 'kana-mud-resort' ); ?></span>
							<?php endif; ?>
						</span>
						<div class="min-w-0 flex-1">
							<h3 class="font-serif text-xl text-white"><?php echo esc_html( get_the_title( $p ) ); ?></h3>
							<?php if ( $desc !== '' ) : ?>
								<p class="mt-2 text-sm leading-relaxed text-emerald-100/85"><?php echo esc_html( $desc ); ?></p>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
