<?php
/**
 * Template Name: Magazine
 *
 * @package The NewsMag
 */
get_header();
?>

<?php do_action('the_newsmag_before_body_content'); ?>

<?php if (is_active_sidebar('the-newsmag-magazine-slider-sidebar') || is_active_sidebar('the-newsmag-magazine-beside-slider-sidebar')) : ?>
	<div class="magazine-page-top-area clear">
		<div class="slider-area">
			<?php
			if (is_active_sidebar('the-newsmag-magazine-slider-sidebar')) {
				dynamic_sidebar('the-newsmag-magazine-slider-sidebar');
			}
			?>
		</div>

		<div class="beside-slider-area">
			<?php
			if (is_active_sidebar('the-newsmag-magazine-beside-slider-sidebar')) {
				dynamic_sidebar('the-newsmag-magazine-beside-slider-sidebar');
			}
			?>
		</div>
	</div>
<?php endif; ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<div class="magazine-page-content-top-sidebar clear">
			<?php
			if (is_active_sidebar('the-newsmag-magazine-top-content-sidebar')) {
				dynamic_sidebar('the-newsmag-magazine-top-content-sidebar');
			}
			?>
		</div>

		

	</main><!-- #main -->
</div><!-- #primary -->

<?php the_newsmag_sidebar_select(); ?>

<?php do_action('the_newsmag_after_body_content'); ?>

<?php get_footer(); ?>
