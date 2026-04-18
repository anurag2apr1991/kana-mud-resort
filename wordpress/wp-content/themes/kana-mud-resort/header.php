<?php
/**
 * Header / primary nav.
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

$brand       = kmr_get_option( 'site_brand_name', __( 'Kana Mud Resort', 'kana-mud-resort' ) );
$menu_raw    = kmr_get_option( 'site_menu_links', '' );
$nav         = kmr_normalize_menu_links( $menu_raw );
$default_nav = kmr_default_nav();
$nav         = count( $nav ) ? $nav : $default_nav;

$primary_label   = kmr_get_option( 'booking_primary_label', __( 'Book', 'kana-mud-resort' ) );
$primary_url     = kmr_booking_primary_href( kmr_get_option( 'booking_primary_url', '#contact' ) );
$secondary_label = kmr_get_option( 'booking_secondary_label', '' );
$secondary_url   = trim( (string) kmr_get_option( 'booking_secondary_url', '' ) );

$primary_target = '';
$primary_rel    = '';
if ( str_starts_with( $primary_url, 'http' ) ) {
	$primary_target = '_blank';
	$primary_rel    = 'noopener noreferrer';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'min-h-screen bg-stone-50 font-sans text-stone-900 antialiased' ); ?>>
<?php wp_body_open(); ?>

<header class="fixed inset-x-0 top-0 z-50 border-b border-stone-200/80 bg-stone-50/90 backdrop-blur-md">
	<div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
		<a href="#hero" class="font-serif text-lg font-semibold tracking-tight text-stone-900 sm:text-xl"><?php echo esc_html( $brand ); ?></a>
		<nav class="hidden items-center gap-1 text-sm text-stone-600 lg:flex" aria-label="<?php esc_attr_e( 'Primary', 'kana-mud-resort' ); ?>">
			<?php foreach ( $nav as $i => $item ) : ?>
				<a href="<?php echo kmr_esc_href( $item['href'] ); ?>" class="rounded-full px-3 py-1.5 transition hover:bg-stone-100 hover:text-stone-900"><?php echo esc_html( $item['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="flex shrink-0 items-center gap-2">
			<?php if ( $secondary_url ) : ?>
				<a href="<?php echo kmr_esc_href( $secondary_url ); ?>" target="_blank" rel="noopener noreferrer" class="hidden rounded-full border border-stone-300 px-3 py-2 text-sm font-medium text-stone-800 transition hover:border-stone-400 sm:inline-flex"><?php echo esc_html( $secondary_label ?: __( 'WhatsApp concierge', 'kana-mud-resort' ) ); ?></a>
			<?php endif; ?>
			<a href="<?php echo kmr_esc_href( $primary_url ); ?>" <?php echo $primary_target ? ' target="' . esc_attr( $primary_target ) . '"' : ''; ?> <?php echo $primary_rel ? ' rel="' . esc_attr( $primary_rel ) . '"' : ''; ?> class="inline-flex rounded-full bg-emerald-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900"><?php echo esc_html( $primary_label ); ?></a>
		</div>
	</div>
	<nav class="flex gap-2 overflow-x-auto border-t border-stone-100 px-4 py-2 lg:hidden" aria-label="<?php esc_attr_e( 'Sections', 'kana-mud-resort' ); ?>">
		<?php foreach ( $nav as $i => $item ) : ?>
			<a href="<?php echo kmr_esc_href( $item['href'] ); ?>" class="whitespace-nowrap rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-700"><?php echo esc_html( $item['label'] ); ?></a>
		<?php endforeach; ?>
	</nav>
</header>
