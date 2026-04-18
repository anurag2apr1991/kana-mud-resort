<?php
/**
 * Amenities section.
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
		<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-300/90"><?php esc_html_e( 'Experience', 'kana-mud-resort' ); ?></p>
		<h2 class="mt-2 font-serif text-3xl sm:text-4xl"><?php esc_html_e( 'Amenities', 'kana-mud-resort' ); ?></h2>
		<p class="mt-4 max-w-2xl text-lg text-emerald-100/90">
			<?php esc_html_e( 'A Himalayan-style retreat in spirit—wholesome meals, forest trails, crisp air, and space to do very little.', 'kana-mud-resort' ); ?>
		</p>
		<?php if ( ! count( $amenities ) ) : ?>
			<p class="mt-8 text-emerald-200/80"><?php esc_html_e( 'We are updating this list. Check back soon for the full amenity guide.', 'kana-mud-resort' ); ?></p>
		<?php else : ?>
			<ul class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php foreach ( $amenities as $p ) : ?>
					<?php
					$pid  = (int) $p->ID;
					$desc = (string) get_post_meta( $pid, '_kmr_description', true );
					$icon = (string) get_post_meta( $pid, '_kmr_icon_key', true );
					$glyph = kmr_amenity_icon_glyph( $icon );
					$k     = strtolower( $icon ?: 'default' );
					$is_mountain = in_array( $k, [ 'mountain', 'pool' ], true );
					?>
					<li class="flex gap-4 rounded-2xl bg-emerald-900/40 p-5 ring-1 ring-emerald-700/50">
						<span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 ring-1 ring-emerald-900/10" aria-hidden="true">
							<span class="inline-flex size-8 items-center justify-center leading-none [font-feature-settings:normal] <?php echo $is_mountain ? 'text-[26px]' : 'text-[22px]'; ?>"><?php echo esc_html( $glyph ); ?></span>
						</span>
						<div>
							<h3 class="font-serif text-xl text-white"><?php echo esc_html( get_the_title( $p ) ); ?></h3>
							<?php if ( $desc ) : ?>
								<p class="mt-2 text-sm leading-relaxed text-emerald-100/85"><?php echo esc_html( $desc ); ?></p>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
