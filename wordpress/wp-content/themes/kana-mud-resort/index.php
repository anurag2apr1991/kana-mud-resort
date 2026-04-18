<?php
/**
 * Main fallback — same landing as the React app (single-page resort site).
 *
 * @package Kana_Mud_Resort
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main>
<?php get_template_part( 'template-parts/content', 'home' ); ?>
</main>
<?php
get_footer();
