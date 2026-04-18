<?php
/**
 * Rooms section.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$sort = kmr_get_option( 'site_room_sort_order', 'default' );
if ( ! in_array( $sort, [ 'default', 'price-low-high', 'price-high-low' ], true ) ) {
	$sort = 'default';
}

$rooms = get_posts(
	[
		'post_type'      => 'kmr_room',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	]
);

$room_data = [];
foreach ( $rooms as $p ) {
	$pid         = (int) $p->ID;
	$price_label = (string) get_post_meta( $pid, '_kmr_price_label', true );
	$orig        = kmr_to_price( get_post_meta( $pid, '_kmr_original_price', true ) );
	$disc        = kmr_to_price( get_post_meta( $pid, '_kmr_discounted_price', true ) );
	$cap_raw     = get_post_meta( $pid, '_kmr_capacity', true );
	$capacity    = $cap_raw !== '' && $cap_raw !== null ? (int) $cap_raw : null;

	$gallery_csv = (string) get_post_meta( $pid, '_kmr_gallery_ids', true );
	$gids        = kmr_parse_id_list( $gallery_csv );
	$thumb_id    = (int) get_post_thumbnail_id( $pid );
	$image_ids   = [];
	if ( $thumb_id ) {
		$image_ids[] = $thumb_id;
	}
	foreach ( $gids as $gid ) {
		if ( $gid && $gid !== $thumb_id ) {
			$image_ids[] = $gid;
		}
	}
	$image_urls = [];
	foreach ( $image_ids as $aid ) {
		$u = kmr_image_url( $aid, 'large' );
		if ( $u ) {
			$image_urls[] = $u;
		}
	}

	$original_num = $orig ?? kmr_parse_rupee_from_price_label( $price_label );
	$discounted_n = $disc;
	$has_discount = $original_num !== null && $discounted_n !== null && $discounted_n < $original_num;
	$discount_pct = 0;
	if ( $has_discount && $original_num > 0 ) {
		$discount_pct = (int) min( 100, max( 0, round( ( ( $original_num - $discounted_n ) / $original_num ) * 100 ) ) );
	}

	$room_data[] = [
		'post'         => $p,
		'price_label'  => $price_label,
		'orig'         => $orig,
		'disc'         => $disc,
		'original_num' => $original_num,
		'discounted_n' => $discounted_n,
		'has_discount' => $has_discount,
		'discount_pct' => $discount_pct,
		'capacity'     => $capacity,
		'image_urls'   => $image_urls,
		'eff_price'    => kmr_effective_price( $orig, $disc, $price_label ),
	];
}

if ( 'price-low-high' === $sort ) {
	usort(
		$room_data,
		static function ( $a, $b ) {
			$pa = $a['eff_price'];
			$pb = $b['eff_price'];
			if ( $pa === null && $pb === null ) {
				return 0;
			}
			if ( $pa === null ) {
				return 1;
			}
			if ( $pb === null ) {
				return -1;
			}
			return $pa <=> $pb;
		}
	);
} elseif ( 'price-high-low' === $sort ) {
	usort(
		$room_data,
		static function ( $a, $b ) {
			$pa = $a['eff_price'];
			$pb = $b['eff_price'];
			if ( $pa === null && $pb === null ) {
				return 0;
			}
			if ( $pa === null ) {
				return 1;
			}
			if ( $pb === null ) {
				return -1;
			}
			return $pb <=> $pa;
		}
	);
}

$rooms_demo = false;
if ( ! count( $room_data ) && kmr_use_demo_assets() ) {
	$room_data  = kmr_demo_room_rows();
	$rooms_demo = true;
}
?>
<section id="rooms" class="scroll-mt-28 bg-stone-50 py-20 sm:py-28<?php echo $rooms_demo ? ' kmr-section--demo' : ''; ?>">
	<div class="mx-auto max-w-6xl px-4 sm:px-6">
		<?php if ( ! count( $room_data ) ) : ?>
			<h2 class="font-serif text-3xl text-stone-900 sm:text-4xl"><?php esc_html_e( 'Rooms', 'kana-mud-resort' ); ?></h2>
			<p class="mt-4 max-w-2xl text-stone-600">
				<?php esc_html_e( 'Room descriptions and rates will appear here soon. Contact us to check availability.', 'kana-mud-resort' ); ?>
			</p>
		<?php else : ?>
			<?php if ( $rooms_demo && current_user_can( 'manage_options' ) ) : ?>
				<p class="mb-6 rounded-2xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-950">
					<?php esc_html_e( 'Demo rooms are shown until you publish Rooms with featured images. This note is visible only to administrators.', 'kana-mud-resort' ); ?>
				</p>
			<?php endif; ?>
			<p class="text-base font-semibold uppercase tracking-[0.2em] text-emerald-800"><?php esc_html_e( 'Stay', 'kana-mud-resort' ); ?></p>
			<h2 class="mt-2 font-serif text-3xl text-stone-900 sm:text-4xl"><?php esc_html_e( 'Rooms & cottages', 'kana-mud-resort' ); ?></h2>
			<p class="mt-4 max-w-2xl text-lg text-stone-600">
				<?php esc_html_e( 'Each space is curated for rest—earthy textures, soft light, and views you will want to wake up to.', 'kana-mud-resort' ); ?>
			</p>
			<div class="mt-14 grid items-stretch gap-8 lg:grid-cols-2">
				<?php foreach ( $room_data as $row ) : ?>
					<?php
					$is_demo = ! empty( $row['demo'] );
					$p       = $is_demo ? null : $row['post'];
					$excerpt = $is_demo ? (string) $row['excerpt'] : ( $p ? $p->post_excerpt : '' );
					$content = $is_demo ? (string) $row['content'] : ( $p ? $p->post_content : '' );
					$title   = $is_demo ? (string) $row['title'] : ( $p ? get_the_title( $p ) : '' );
					?>
					<article class="flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-stone-200/80">
						<div class="relative aspect-[4/3] w-full bg-stone-200">
							<?php if ( count( $row['image_urls'] ) ) : ?>
								<div
									class="kmr-carousel absolute inset-0 overflow-hidden"
									data-kmr-carousel
									data-kmr-interval="6000"
									data-kmr-lightbox="1"
									data-images="<?php echo esc_attr( wp_json_encode( $row['image_urls'] ) ); ?>"
								></div>
							<?php else : ?>
								<div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-stone-200 via-stone-100 to-stone-200 text-center text-sm font-medium text-stone-500" aria-label="<?php esc_attr_e( 'Room image coming soon', 'kana-mud-resort' ); ?>"><?php esc_html_e( 'Room image coming soon', 'kana-mud-resort' ); ?></div>
							<?php endif; ?>
						</div>
						<div class="flex flex-1 flex-col p-6 sm:p-8">
							<div class="flex flex-wrap items-baseline justify-between gap-2">
								<h3 class="font-serif text-2xl text-stone-900"><?php echo esc_html( $title ); ?></h3>
								<?php if ( $row['has_discount'] ) : ?>
									<span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-900">
										<span class="mr-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700"><?php echo esc_html( (string) $row['discount_pct'] ); ?>% OFF</span>
										<span class="mr-2 text-red-700 line-through">
											<?php
											if ( null !== $row['orig'] ) {
												echo esc_html( 'INR ' . kmr_format_inr_amount( (int) $row['original_num'] ) );
											} else {
												echo esc_html( $row['price_label'] ? $row['price_label'] : 'INR ' . kmr_format_inr_amount( (int) $row['original_num'] ) );
											}
											?>
										</span>
										<span><?php echo esc_html( 'INR ' . kmr_format_inr_amount( (int) $row['discounted_n'] ) . ' / night' ); ?></span>
									</span>
								<?php elseif ( null !== $row['orig'] ) : ?>
									<span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-900"><?php echo esc_html( 'INR ' . kmr_format_inr_amount( (int) $row['orig'] ) . ' / night' ); ?></span>
								<?php elseif ( $row['price_label'] ) : ?>
									<span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-900"><?php echo esc_html( $row['price_label'] ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( $row['capacity'] ) : ?>
								<p class="mt-2 text-sm text-stone-500">
									<?php
									printf(
										/* translators: %d: guest count */
										esc_html__( 'Sleeps up to %d guests', 'kana-mud-resort' ),
										(int) $row['capacity']
									);
									?>
								</p>
							<?php endif; ?>
							<?php if ( $excerpt ) : ?>
								<p class="mt-4 min-h-12 text-stone-600"><?php echo esc_html( $excerpt ); ?></p>
							<?php endif; ?>
							<?php if ( $content ) : ?>
								<div class="kmr-entry-content mt-4 max-w-none text-stone-700 [&_a]:text-emerald-700 [&_a]:underline [&_h2]:mb-2 [&_h2]:mt-6 [&_h2]:font-serif [&_h2]:text-2xl [&_h2]:text-stone-900 [&_h3]:mb-2 [&_h3]:mt-4 [&_h3]:font-serif [&_h3]:text-xl [&_h3]:text-stone-900 [&_li]:my-1 [&_ol]:mb-4 [&_ol]:ml-6 [&_ol]:list-decimal [&_p]:mb-4 [&_ul]:mb-4 [&_ul]:ml-6 [&_ul]:list-disc">
									<?php echo apply_filters( 'the_content', $content ); ?>
								</div>
							<?php endif; ?>
							<?php if ( count( $row['image_urls'] ) > 1 ) : ?>
								<p class="mt-auto pt-6 text-sm text-stone-500">
									<?php
									$n = count( $row['image_urls'] );
									echo esc_html( sprintf( _n( '%d image', '%d images', $n, 'kana-mud-resort' ), $n ) );
									?>
								</p>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
