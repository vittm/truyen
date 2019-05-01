<?php
/**
 * The template for displaying category pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package The NewsMag
 */
get_header();
?>

<?php do_action('the_newsmag_before_body_content'); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<?php if (have_posts()) : ?>
			<?php
			/* Start the Loop */
			
			global $current_category;
			$current_category = get_category( get_query_var( 'cat' ), false );
			$tmp = get_term_meta($current_category->term_id, 'tmp_type_category', true );
			if($tmp === 'tmp_review'){
				require get_template_directory() . '/template-parts/content-review.php';
			}else {
				while (have_posts()) : the_post();
				/*
				 * Include the Post-Format-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
				 */
				get_template_part('template-parts/content', get_post_format());

				endwhile;
				the_posts_pagination();
			}

		else :

			get_template_part('template-parts/content', 'none');

		endif;
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php the_newsmag_sidebar_select(); ?>

<?php do_action('the_newsmag_after_body_content'); ?>

<?php get_footer(); ?>
