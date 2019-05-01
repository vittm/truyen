<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package The NewsMag
 */
?>
<?php
	$postReview = get_post(the_ID());
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="article-container clear">
		<?php do_action('the_newsmag_before_post_content'); 
		if(($postReview->hcf_show_review) !== on ){
		?>
			<header class="page-header">
				<?php
				do_action('the_newsmag_category_title');
				single_cat_title();
				echo '<div class="taxonomy-description">' . category_description() . '</div>';
				?>
			</header><!-- .page-header -->
			<div class="post-header-wrapper clear">
			<?php if (has_post_thumbnail()) : ?>

				<?php
				$image_popup_id = get_post_thumbnail_id();
				$image_popup_url = wp_get_attachment_url($image_popup_id);
				?>

				<?php if (!is_single()) : ?>
					<figure class="featured-image">
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail('the-newsmag-featured-large-thumbnail'); ?></a>
					</figure>
				<?php else : ?>
					<figure class="featured-image">
						<?php if (get_theme_mod('the_newsmag_featured_image_popup', 0) == 1) { ?>
							<a href="<?php echo $image_popup_url; ?>" class="featured-image-popup" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail('the-newsmag-featured-large-thumbnail'); ?></a>
						<?php } else { ?>
							<?php the_post_thumbnail('the-newsmag-featured-large-thumbnail'); ?>
						<?php } ?>
					</figure>
				<?php endif; ?>

			<?php endif; ?>

			<?php if ('post' === get_post_type()) : ?>
				<div class="category-links">
					<?php the_newsmag_colored_category(); ?>
				</div><!-- .entry-meta -->
			<?php endif; ?>

			<?php
			if (('post' === get_post_type() && !post_password_required()) && (comments_open() || get_comments_number())) :
				if ((has_post_thumbnail()) || (!has_post_thumbnail() && !is_single())) :
					?>
					<a href="<?php esc_url(comments_link()); ?>" class="entry-meta-comments">
						<?php
						printf(_nx('<i class="fa fa-comment"></i> 1', '<i class="fa fa-comment"></i> %1$s', get_comments_number(), 'comments title', 'the-newsmag'), number_format_i18n(get_comments_number()));
						?>
					</a>
					<?php
				endif;
			endif;
			?>

			<header class="entry-header clear">
				<?php
				if (is_single()) {
					the_title('<h1 class="entry-title">', '</h1>');
				} else {
					the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
				}
				?>
			</header><!-- .entry-header -->
		</div>
		<?php
		}
		?>

		<div class="entry-header-meta">
			<?php
			if ('post' === get_post_type()) :
				?>
				<div class="entry-meta">
					<?php the_newsmag_posted_on(); ?>
				</div><!-- .entry-meta -->
			<?php endif;
			?>
		</div><!-- .entry-header-meta -->
		<?php 
			
		?>
		<div class="entry-header-review">
			<div class="entry-header-review__content">
				<div class="entry-header-review__content__left">
					<h3>ĐÁNH GIÁ TÁC PHẨM</h3>
						<p><strong>Tên tác phẩm</strong>: <?php the_title() ?></p>
						<p><strong>Tác giả</strong>: <?php echo esc_attr( get_post_meta( $postReview->ID, 'hcf_author', true ) );?></p>
						<p><strong>Thể loại</strong>: Relef</p>
						<p><strong>Nhà xuất bản</strong>: Relef</p>
						<p><strong>Tình trạng</strong>: Relef</p>
						<p><strong>Số tập</strong>: Relef</p>
				</div>
				<div class="entry-header-review__content__right">
					<?php the_post_thumbnail( 'the-newsmag-featured-large-thumbnail' ); ?>
				</div>
			</div>
			<div class="entry-header-review__point">
				<?php
					$core = sc_attr( get_post_meta( $postReview->ID, 'hcf_author', true ) );
					$
				?>
					<p>Cốt Truyện </p>
					<div class="entry-header-review__point__detail">
						<div class="review_pollbar">
						</div>
						<p>9</p>
					</div>
					<p>Nhân vật</p>
					<div class="entry-header-review__point__detail">
						<div class="review_pollbar">
						</div>
						<p>9</p>
					</div>
					<p>Nét vẽ</p>
					<div class="entry-header-review__point__detail">
						<div class="review_pollbar">
						</div>
						<p>9</p>
					</div>
					<p>Chất lượng</p>
					<div class="entry-header-review__point__detail">
						<div class="review_pollbar">
						</div>
						<p>9</p>
					</div>
			</div>
			<div class="entry-header-review__total">
				<div class="entry-header-review__total__left">
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. 
Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p>
				</div>
				<div class="entry-header-review__total__right">
					<div class="reivew-box__point">
						<p>
							Khá ổn
						</p>
						<p>
							8.5
						</p>
						<fieldset class="rating">
							<input type="radio" id="star5" name="rating" value="5" /><label class = "full" for="star5" title="Awesome - 5 stars"></label>
							<input type="radio" id="star4half" name="rating" value="4 and a half" /><label class="half" for="star4half" title="Pretty good - 4.5 stars"></label>
							<input type="radio" id="star4" name="rating" value="4" /><label class = "full" for="star4" title="Pretty good - 4 stars"></label>
							<input type="radio" id="star3half" name="rating" value="3 and a half" /><label class="half" for="star3half" title="Meh - 3.5 stars"></label>
							<input type="radio" id="star3" name="rating" value="3" /><label class = "full" for="star3" title="Meh - 3 stars"></label>
							<input type="radio" id="star2half" name="rating" value="2 and a half" /><label class="half" for="star2half" title="Kinda bad - 2.5 stars"></label>
							<input type="radio" id="star2" name="rating" value="2" /><label class = "full" for="star2" title="Kinda bad - 2 stars"></label>
							<input type="radio" id="star1half" name="rating" value="1 and a half" /><label class="half" for="star1half" title="Meh - 1.5 stars"></label>
							<input type="radio" id="star1" name="rating" value="1" /><label class = "full" for="star1" title="Sucks big time - 1 star"></label>
							<input type="radio" id="starhalf" name="rating" value="half" /><label class="half" for="starhalf" title="Sucks big time - 0.5 stars"></label>
						</fieldset>
					</div>
				</div>
			</div>
			<div class="entry-header-review__feel">
				<div class="entry-header-review__feel__right">
						<h3>ĐIỂM CỘNG</h3>
				</div>
				<div class="entry-header-review__feel__left">
						<h3>ĐIỂM TRỪ</h3>
				</div>
			</div>
		</div>
		<div class="entry-content">
			<?php
			if (is_single()) :
				the_content();
			else :
				if (is_sticky()) :
					// displaying full content for the sticky post
					the_content(sprintf(
									/* translators: %s: Name of current post. */
									wp_kses('<button type="button" class="btn continue-more-link">' . __('Read More <i class="fa fa-arrow-circle-o-right"></i>', 'the-newsmag') . '</button> %s', array('i' => array('class' => array()), 'button' => array('class' => array(), 'type' => array()))), the_title('<span class="screen-reader-text">"', '"</span>', false)
					));
				else :
					the_excerpt(); // displaying excerpt for the archive pages
				endif;
			endif;

			wp_link_pages(array(
				'before' => '<div class="page-links">' . esc_html__('Pages:', 'the-newsmag'),
				'after' => '</div>',
			));
			?>

			<?php if (!is_single() && !is_sticky()) : ?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					<?php
					printf(
							/* translators: %s: Name of current post. */
							wp_kses('<button type="button" class="btn continue-more-link">' . __('Read More <i class="fa fa-arrow-circle-o-right"></i>', 'the-newsmag') . '</button> %s', array('i' => array('class' => array()), 'button' => array('class' => array(), 'type' => array()))), the_title('<span class="screen-reader-text">"', '"</span>', false)
					);
					?>
				</a>
			<?php endif; ?>
		</div><!-- .entry-content -->

		<?php if (is_single()) : ?>
			<footer class="entry-footer">
				<?php the_newsmag_entry_footer(); ?>
			</footer><!-- .entry-footer -->
		<?php endif; ?>
		<?php do_action('the_newsmag_after_post_content'); ?>
	</div>
</article><!-- #post-## -->
