<?php
/**
 * Post meta boxes for CPTs.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', 'kmr_add_meta_boxes' );
add_action( 'save_post', 'kmr_save_meta_boxes', 10, 2 );
add_action( 'admin_enqueue_scripts', 'kmr_enqueue_room_gallery_admin', 20 );

/**
 * Register meta boxes.
 */
function kmr_add_meta_boxes(): void {
	add_meta_box(
		'kmr_room_details',
		__( 'Room details', 'kana-mud-resort' ),
		'kmr_render_room_meta_box',
		'kmr_room',
		'normal',
		'high'
	);

	add_meta_box(
		'kmr_photo_caption',
		__( 'Caption', 'kana-mud-resort' ),
		'kmr_render_photo_meta_box',
		'kmr_photo',
		'normal',
		'high'
	);

	add_meta_box(
		'kmr_nearby_details',
		__( 'Place details', 'kana-mud-resort' ),
		'kmr_render_nearby_meta_box',
		'kmr_nearby',
		'normal',
		'high'
	);

	add_meta_box(
		'kmr_amenity_details',
		__( 'Amenity details', 'kana-mud-resort' ),
		'kmr_render_amenity_meta_box',
		'kmr_amenity',
		'normal',
		'high'
	);

	add_meta_box(
		'kmr_offer_details',
		__( 'Offer details', 'kana-mud-resort' ),
		'kmr_render_offer_meta_box',
		'kmr_offer',
		'normal',
		'high'
	);

	add_meta_box(
		'kmr_testimonial_details',
		__( 'Guest quote', 'kana-mud-resort' ),
		'kmr_render_testimonial_meta_box',
		'kmr_testimonial',
		'normal',
		'high'
	);
}

/**
 * @param WP_Post $post Post.
 */
function kmr_render_room_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'kmr_save_room_meta', 'kmr_room_meta_nonce' );
	$price_label      = get_post_meta( $post->ID, '_kmr_price_label', true );
	$original_price   = get_post_meta( $post->ID, '_kmr_original_price', true );
	$discounted_price = get_post_meta( $post->ID, '_kmr_discounted_price', true );
	$capacity         = get_post_meta( $post->ID, '_kmr_capacity', true );
	$gallery_ids      = get_post_meta( $post->ID, '_kmr_gallery_ids', true );
	?>
	<p>
		<label for="kmr_price_label"><?php esc_html_e( 'Price label (e.g. From ₹14,000 / night)', 'kana-mud-resort' ); ?></label><br />
		<input type="text" class="widefat" id="kmr_price_label" name="kmr_price_label" value="<?php echo esc_attr( (string) $price_label ); ?>" />
	</p>
	<p>
		<label for="kmr_original_price"><?php esc_html_e( 'Original price (integer, optional)', 'kana-mud-resort' ); ?></label><br />
		<input type="number" min="0" class="small-text" id="kmr_original_price" name="kmr_original_price" value="<?php echo esc_attr( (string) $original_price ); ?>" />
	</p>
	<p>
		<label for="kmr_discounted_price"><?php esc_html_e( 'Discounted price (integer, optional)', 'kana-mud-resort' ); ?></label><br />
		<input type="number" min="0" class="small-text" id="kmr_discounted_price" name="kmr_discounted_price" value="<?php echo esc_attr( (string) $discounted_price ); ?>" />
	</p>
	<p>
		<label for="kmr_capacity"><?php esc_html_e( 'Capacity (guests)', 'kana-mud-resort' ); ?></label><br />
		<input type="number" min="1" class="small-text" id="kmr_capacity" name="kmr_capacity" value="<?php echo esc_attr( (string) ( $capacity !== '' && $capacity !== null ? $capacity : '2' ) ); ?>" />
	</p>
	<div id="kmr-room-gallery-root">
		<p>
			<strong><?php esc_html_e( 'More room photos (carousel)', 'kana-mud-resort' ); ?></strong>
		</p>
		<input type="hidden" id="kmr_gallery_ids" name="kmr_gallery_ids" value="<?php echo esc_attr( (string) $gallery_ids ); ?>" />
		<p>
			<button type="button" class="button button-secondary" id="kmr-room-gallery-add">
				<?php esc_html_e( 'Add images to carousel', 'kana-mud-resort' ); ?>
			</button>
		</p>
		<div id="kmr-room-gallery-preview" class="kmr-room-gallery-preview" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;align-items:flex-start;"></div>
		<p class="description">
			<?php esc_html_e( 'Set the Featured Image as the first slide, then click “Add images to carousel” to choose additional photos from the Media Library (order follows your selection). Remove a thumbnail to drop it from the carousel. Do not insert images in the main editor — use excerpt for a short line and the editor for extra text only.', 'kana-mud-resort' ); ?>
		</p>
	</div>
	<?php
}

/**
 * @param WP_Post $post Post.
 */
function kmr_render_photo_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'kmr_save_photo_meta', 'kmr_photo_meta_nonce' );
	$caption = get_post_meta( $post->ID, '_kmr_caption', true );
	?>
	<p>
		<label for="kmr_caption"><?php esc_html_e( 'Caption (optional)', 'kana-mud-resort' ); ?></label><br />
		<input type="text" class="widefat" id="kmr_caption" name="kmr_caption" value="<?php echo esc_attr( (string) $caption ); ?>" />
	</p>
	<p class="description">
		<?php esc_html_e( 'Set the Featured Image for this card. The homepage shows up to six photos per view; if you add more, visitors can use arrows or dots to see additional pages. Clicking a photo opens a full-size zoom view.', 'kana-mud-resort' ); ?>
	</p>
	<?php
}

/**
 * @param WP_Post $post Post.
 */
function kmr_render_nearby_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'kmr_save_nearby_meta', 'kmr_nearby_meta_nonce' );
	$distance    = get_post_meta( $post->ID, '_kmr_distance_label', true );
	$description = get_post_meta( $post->ID, '_kmr_description', true );
	?>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Homepage card: Featured image = card photo only. Title = place name. Main editor = text (paragraphs, lists, links)—do not insert images in the editor. Optional short description below if you leave the editor empty.', 'kana-mud-resort' ); ?>
	</p>
	<p>
		<label for="kmr_distance_label"><strong><?php esc_html_e( 'Distance label', 'kana-mud-resort' ); ?></strong></label><br />
		<input type="text" class="widefat" id="kmr_distance_label" name="kmr_distance_label" value="<?php echo esc_attr( (string) $distance ); ?>" placeholder="<?php esc_attr_e( 'e.g. 12 km', 'kana-mud-resort' ); ?>" />
	</p>
	<p>
		<label for="kmr_nearby_description"><strong><?php esc_html_e( 'Short description (optional)', 'kana-mud-resort' ); ?></strong></label><br />
		<textarea class="widefat" rows="4" id="kmr_nearby_description" name="kmr_nearby_description" placeholder="<?php esc_attr_e( 'Used only if the main content is empty', 'kana-mud-resort' ); ?>"><?php echo esc_textarea( (string) $description ); ?></textarea>
	</p>
	<?php
}

/**
 * @param WP_Post $post Post.
 */
function kmr_render_amenity_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'kmr_save_amenity_meta', 'kmr_amenity_meta_nonce' );
	$description = get_post_meta( $post->ID, '_kmr_description', true );
	$icon_key    = get_post_meta( $post->ID, '_kmr_icon_key', true );
	?>
	<p>
		<label for="kmr_icon_key"><?php esc_html_e( 'Icon key', 'kana-mud-resort' ); ?></label><br />
		<input type="text" class="widefat" id="kmr_icon_key" name="kmr_icon_key" value="<?php echo esc_attr( (string) $icon_key ); ?>" placeholder="leaf, mountain, wifi, food, ..." />
	</p>
	<p>
		<label for="kmr_amenity_description"><strong><?php esc_html_e( 'Short description (optional)', 'kana-mud-resort' ); ?></strong></label><br />
		<textarea class="widefat" rows="3" id="kmr_amenity_description" name="kmr_amenity_description" placeholder="<?php esc_attr_e( 'Plain text only; used only if the main content above is empty', 'kana-mud-resort' ); ?>"><?php echo esc_textarea( (string) $description ); ?></textarea>
	</p>
	<p class="description">
		<?php esc_html_e( 'Use the icon key for the symbol (no photos). Main editor = text only—do not insert images. Short description is a plain-text fallback when the editor is empty.', 'kana-mud-resort' ); ?>
	</p>
	<?php
}

/**
 * @param WP_Post $post Post.
 */
function kmr_render_offer_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'kmr_save_offer_meta', 'kmr_offer_meta_nonce' );
	$description = get_post_meta( $post->ID, '_kmr_description', true );
	$badge       = get_post_meta( $post->ID, '_kmr_badge', true );
	$valid_until = get_post_meta( $post->ID, '_kmr_valid_until', true );
	?>
	<p class="description">
		<?php esc_html_e( 'Featured Image = offer photo only. Main editor = text (paragraphs, lists, links)—do not insert images in the editor. Short description below is optional plain text if the editor is empty.', 'kana-mud-resort' ); ?>
	</p>
	<p>
		<label for="kmr_badge"><?php esc_html_e( 'Badge (optional)', 'kana-mud-resort' ); ?></label><br />
		<input type="text" class="widefat" id="kmr_badge" name="kmr_badge" value="<?php echo esc_attr( (string) $badge ); ?>" placeholder="<?php esc_attr_e( 'e.g. Monsoon special', 'kana-mud-resort' ); ?>" />
	</p>
	<p>
		<label for="kmr_valid_until"><?php esc_html_e( 'Valid until (YYYY-MM-DD, optional)', 'kana-mud-resort' ); ?></label><br />
		<input type="date" class="widefat" id="kmr_valid_until" name="kmr_valid_until" value="<?php echo esc_attr( (string) $valid_until ); ?>" />
	</p>
	<p>
		<label for="kmr_offer_description"><strong><?php esc_html_e( 'Short description (optional)', 'kana-mud-resort' ); ?></strong></label><br />
		<textarea class="widefat" rows="3" id="kmr_offer_description" name="kmr_offer_description" placeholder="<?php esc_attr_e( 'Plain text; used only if the main content is empty', 'kana-mud-resort' ); ?>"><?php echo esc_textarea( (string) $description ); ?></textarea>
	</p>
	<?php
}

/**
 * @param WP_Post $post Post.
 */
function kmr_render_testimonial_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'kmr_save_testimonial_meta', 'kmr_testimonial_meta_nonce' );
	$quote        = get_post_meta( $post->ID, '_kmr_quote', true );
	$author_title = get_post_meta( $post->ID, '_kmr_author_title', true );
	$rating       = get_post_meta( $post->ID, '_kmr_rating', true );
	?>
	<p>
		<label for="kmr_quote"><?php esc_html_e( 'Quote', 'kana-mud-resort' ); ?></label><br />
		<textarea class="widefat" rows="4" id="kmr_quote" name="kmr_quote" required><?php echo esc_textarea( (string) $quote ); ?></textarea>
	</p>
	<p>
		<label for="kmr_author_title"><?php esc_html_e( 'Author title / location (optional)', 'kana-mud-resort' ); ?></label><br />
		<input type="text" class="widefat" id="kmr_author_title" name="kmr_author_title" value="<?php echo esc_attr( (string) $author_title ); ?>" />
	</p>
	<p>
		<label for="kmr_rating"><?php esc_html_e( 'Rating (0–5)', 'kana-mud-resort' ); ?></label><br />
		<input type="number" min="0" max="5" step="1" class="small-text" id="kmr_rating" name="kmr_rating" value="<?php echo esc_attr( (string) ( $rating !== '' && $rating !== null ? $rating : '' ) ); ?>" />
	</p>
	<p class="description"><?php esc_html_e( 'The post title is the guest name. Optional avatar: set Featured Image.', 'kana-mud-resort' ); ?></p>
	<?php
}

/**
 * Save handlers.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function kmr_save_meta_boxes( int $post_id, WP_Post $post ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	switch ( $post->post_type ) {
		case 'kmr_room':
			if ( ! isset( $_POST['kmr_room_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kmr_room_meta_nonce'] ) ), 'kmr_save_room_meta' ) ) {
				return;
			}
			update_post_meta( $post_id, '_kmr_price_label', sanitize_text_field( wp_unslash( $_POST['kmr_price_label'] ?? '' ) ) );
			update_post_meta( $post_id, '_kmr_original_price', kmr_sanitize_int_meta( $_POST['kmr_original_price'] ?? '' ) );
			update_post_meta( $post_id, '_kmr_discounted_price', kmr_sanitize_int_meta( $_POST['kmr_discounted_price'] ?? '' ) );
			update_post_meta( $post_id, '_kmr_capacity', kmr_sanitize_int_meta( $_POST['kmr_capacity'] ?? '2' ) );
			update_post_meta( $post_id, '_kmr_gallery_ids', sanitize_text_field( wp_unslash( $_POST['kmr_gallery_ids'] ?? '' ) ) );
			break;
		case 'kmr_photo':
			if ( ! isset( $_POST['kmr_photo_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kmr_photo_meta_nonce'] ) ), 'kmr_save_photo_meta' ) ) {
				return;
			}
			update_post_meta( $post_id, '_kmr_caption', sanitize_text_field( wp_unslash( $_POST['kmr_caption'] ?? '' ) ) );
			break;
		case 'kmr_nearby':
			if ( ! isset( $_POST['kmr_nearby_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kmr_nearby_meta_nonce'] ) ), 'kmr_save_nearby_meta' ) ) {
				return;
			}
			update_post_meta( $post_id, '_kmr_distance_label', sanitize_text_field( wp_unslash( $_POST['kmr_distance_label'] ?? '' ) ) );
			update_post_meta( $post_id, '_kmr_description', sanitize_textarea_field( wp_unslash( $_POST['kmr_nearby_description'] ?? '' ) ) );
			break;
		case 'kmr_amenity':
			if ( ! isset( $_POST['kmr_amenity_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kmr_amenity_meta_nonce'] ) ), 'kmr_save_amenity_meta' ) ) {
				return;
			}
			update_post_meta( $post_id, '_kmr_icon_key', sanitize_text_field( wp_unslash( $_POST['kmr_icon_key'] ?? '' ) ) );
			update_post_meta( $post_id, '_kmr_description', sanitize_textarea_field( wp_unslash( $_POST['kmr_amenity_description'] ?? '' ) ) );
			break;
		case 'kmr_offer':
			if ( ! isset( $_POST['kmr_offer_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kmr_offer_meta_nonce'] ) ), 'kmr_save_offer_meta' ) ) {
				return;
			}
			update_post_meta( $post_id, '_kmr_description', sanitize_textarea_field( wp_unslash( $_POST['kmr_offer_description'] ?? '' ) ) );
			update_post_meta( $post_id, '_kmr_badge', sanitize_text_field( wp_unslash( $_POST['kmr_badge'] ?? '' ) ) );
			update_post_meta( $post_id, '_kmr_valid_until', sanitize_text_field( wp_unslash( $_POST['kmr_valid_until'] ?? '' ) ) );
			break;
		case 'kmr_testimonial':
			if ( ! isset( $_POST['kmr_testimonial_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kmr_testimonial_meta_nonce'] ) ), 'kmr_save_testimonial_meta' ) ) {
				return;
			}
			update_post_meta( $post_id, '_kmr_quote', sanitize_textarea_field( wp_unslash( $_POST['kmr_quote'] ?? '' ) ) );
			update_post_meta( $post_id, '_kmr_author_title', sanitize_text_field( wp_unslash( $_POST['kmr_author_title'] ?? '' ) ) );
			$r = isset( $_POST['kmr_rating'] ) ? (int) $_POST['kmr_rating'] : 0;
			$r = max( 0, min( 5, $r ) );
			update_post_meta( $post_id, '_kmr_rating', $r );
			break;
		default:
			break;
	}
}

/**
 * @param mixed $v Raw value.
 * @return string Stored empty string or numeric string.
 */
function kmr_sanitize_int_meta( $v ): string {
	if ( $v === null || $v === '' ) {
		return '';
	}
	$n = (int) $v;
	return (string) max( 0, $n );
}

add_filter( 'manage_kmr_nearby_posts_columns', 'kmr_nearby_admin_columns' );
add_action( 'manage_kmr_nearby_posts_custom_column', 'kmr_nearby_admin_column', 10, 2 );

/**
 * Show card thumbnail in Nearby places list table.
 *
 * @param string[] $columns Columns.
 * @return string[]
 */
function kmr_nearby_admin_columns( array $columns ): array {
	if ( ! isset( $columns['cb'] ) ) {
		return $columns;
	}
	$cb = $columns['cb'];
	unset( $columns['cb'] );
	return array_merge(
		[
			'cb'                => $cb,
			'kmr_nearby_thumb' => __( 'Card image', 'kana-mud-resort' ),
		],
		$columns
	);
}

/**
 * @param string $column Column id.
 * @param int    $post_id Post ID.
 */
function kmr_nearby_admin_column( string $column, int $post_id ): void {
	if ( 'kmr_nearby_thumb' !== $column ) {
		return;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, [ 80, 54 ], [ 'style' => 'border-radius:4px;object-fit:cover;' ] );
	} else {
		echo '<span class="dashicons dashicons-format-image" style="color:#c3c4c7;" aria-hidden="true"></span>';
	}
}

add_filter( 'manage_kmr_photo_posts_columns', 'kmr_photo_admin_columns' );
add_action( 'manage_kmr_photo_posts_custom_column', 'kmr_photo_admin_column', 10, 2 );

/**
 * Show featured image in Gallery list table.
 *
 * @param string[] $columns Columns.
 * @return string[]
 */
function kmr_photo_admin_columns( array $columns ): array {
	if ( ! isset( $columns['cb'] ) ) {
		return $columns;
	}
	$cb = $columns['cb'];
	unset( $columns['cb'] );
	return array_merge(
		[
			'cb'               => $cb,
			'kmr_photo_thumb' => __( 'Photo', 'kana-mud-resort' ),
		],
		$columns
	);
}

/**
 * @param string $column Column id.
 * @param int    $post_id Post ID.
 */
function kmr_photo_admin_column( string $column, int $post_id ): void {
	if ( 'kmr_photo_thumb' !== $column ) {
		return;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, [ 80, 54 ], [ 'style' => 'border-radius:4px;object-fit:cover;' ] );
	} else {
		echo '<span class="dashicons dashicons-format-image" style="color:#c3c4c7;" aria-hidden="true"></span>';
	}
}

add_filter( 'manage_kmr_offer_posts_columns', 'kmr_offer_admin_columns' );
add_action( 'manage_kmr_offer_posts_custom_column', 'kmr_offer_admin_column', 10, 2 );

/**
 * Show featured image in Offers list table.
 *
 * @param string[] $columns Columns.
 * @return string[]
 */
function kmr_offer_admin_columns( array $columns ): array {
	if ( ! isset( $columns['cb'] ) ) {
		return $columns;
	}
	$cb = $columns['cb'];
	unset( $columns['cb'] );
	return array_merge(
		[
			'cb'              => $cb,
			'kmr_offer_thumb' => __( 'Image', 'kana-mud-resort' ),
		],
		$columns
	);
}

/**
 * @param string $column Column id.
 * @param int    $post_id Post ID.
 */
function kmr_offer_admin_column( string $column, int $post_id ): void {
	if ( 'kmr_offer_thumb' !== $column ) {
		return;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, [ 80, 54 ], [ 'style' => 'border-radius:4px;object-fit:cover;' ] );
	} else {
		echo '<span class="dashicons dashicons-format-image" style="color:#c3c4c7;" aria-hidden="true"></span>';
	}
}

/**
 * Media picker for room carousel image IDs (edit screen only).
 *
 * @param string $hook_suffix Current admin page.
 */
function kmr_enqueue_room_gallery_admin( string $hook_suffix ): void {
	if ( 'post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'kmr_room' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'kmr-admin-room-gallery',
		KMR_URI . '/assets/js/admin-room-gallery.js',
		[ 'jquery' ],
		KMR_VERSION,
		true
	);
	$ids  = [];
	$pget = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
	if ( $pget > 0 ) {
		$ids = kmr_parse_id_list( (string) get_post_meta( $pget, '_kmr_gallery_ids', true ) );
	}
	$urls = [];
	foreach ( $ids as $id ) {
		$u = wp_get_attachment_image_url( (int) $id, 'thumbnail' );
		if ( $u ) {
			$urls[ (string) $id ] = $u;
		}
	}
	wp_localize_script(
		'kmr-admin-room-gallery',
		'kmrRoomGallery',
		[
			'urls' => $urls,
			'i18n' => [
				'title'        => __( 'Select images for the room carousel', 'kana-mud-resort' ),
				'button'       => __( 'Use selected images', 'kana-mud-resort' ),
				'add'          => __( 'Add images to carousel', 'kana-mud-resort' ),
				'remove'       => __( 'Remove image from carousel', 'kana-mud-resort' ),
				'mediaMissing' => __( 'Media library is not ready yet. Wait a moment and try again, or refresh the page.', 'kana-mud-resort' ),
			],
		]
	);
}
