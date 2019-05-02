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
	$postReview = get_post();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="article-container clear <?php if (!is_single() && !is_sticky() ) { echo "listing-post";}?>">
		<?php do_action('the_newsmag_before_post_content'); 
		if(($postReview->hcf_show_review) !== on ){
		?>
		
			<div class="post-header-wrapper clear">
			
			<?php if (is_single() && has_post_thumbnail()) : ?>

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
		</div>
		<?php
		}
		?>

		<div class="entry-header-meta">
		<?php if (!is_single() && !is_sticky() && has_post_thumbnail()) : ?>
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
		</div><!-- .entry-header-meta -->
		<?php 
			
		?>
		
		<?php 
		$checkReview = esc_attr( get_post_meta( $postReview->ID, 'hcf_show_review', true ));
		if (is_single() && $checkReview === 'on' ){ ?>
		<div class="entry-header-review">
			<h3 class="entry-header-review__title">ĐÁNH GIÁ TÁC PHẨM</h3>
			<div class="border-entry entry-header-review__content">
				<div class="entry-header-review__content__left">
						<p><strong>Tên tác phẩm</strong>: <?php the_title() ?></p>
						<p><strong>Tác giả</strong>: <?php echo esc_attr( get_post_meta( $postReview->ID, 'hcf_author', true ) );?></p>
						<p><strong>Thể loại</strong>: <?php $category_detail=get_the_category($postReview->ID);//$post->ID
															foreach($category_detail as $key => $cd){
															echo $cd->cat_name;
															if($key+1 < count($category_detail)){
																echo " , ";
															} 
															} ?> 
														</p>
						<p><strong>Nhà xuất bản</strong>: <?php echo esc_attr( get_post_meta( $postReview->ID, 'hcf_publishing', true ) );?></p>
						<p><strong>Tình trạng</strong>: <?php echo esc_attr( get_post_meta( $postReview->ID, 'hcf_status', true ) );?></p>
						<p><strong>Số tập</strong>: <?php echo esc_attr( get_post_meta( $postReview->ID, 'hcf_chap', true ) );?></p>
				</div>
				<div class="entry-header-review__content__right">
					<?php the_post_thumbnail( 'the-newsmag-featured-large-thumbnail' ); ?>
				</div>
			</div>
			<div class="border-entry entry-header-review__point">
				<?php
					$core = esc_attr( get_post_meta( $postReview->ID, 'hcf_core', true ) );
					$figure = esc_attr( get_post_meta( $postReview->ID, 'hcf_figure', true ) );
					$paint = esc_attr( get_post_meta( $postReview->ID, 'hcf_paint', true ) );
					$quality = esc_attr( get_post_meta( $postReview->ID, 'hcf_quality', true ) );
					$vote = esc_attr( get_post_meta( $postReview->ID, 'hcf_vote', true ) );
				?>
					<p>Cốt Truyện </p>
					<div class="entry-header-review__point__detail">
						<div class="review_pollbar">
							<div class="review_pollbar__line" style="width: <?php echo $core * 10; ?>%"></div>
						</div>
						<p><?php echo $core; ?></p>
					</div>
					<p>Nhân vật</p>
					<div class="entry-header-review__point__detail">
						<div class="review_pollbar">
							<div class="review_pollbar__line" style="width: <?php echo $figure * 10; ?>%"></div>
						</div>
						<p><?php echo $figure; ?></p>
					</div>
					<p>Nét vẽ</p>
					<div class="entry-header-review__point__detail">
						<div class="review_pollbar">
						<div class="review_pollbar__line" style="width: <?php echo $paint * 10; ?>%"></div>
						</div>
						<p><?php echo $paint; ?></p>
					</div>
					<p>Chất lượng</p>
					<div class="entry-header-review__point__detail">
						<div class="review_pollbar">
						<div class="review_pollbar__line" style="width: <?php echo $quality * 10; ?>%"></div>
						</div>
						<p><?php echo $quality; ?></p>
					</div>
			</div>
			<h3 class="entry-header-review__title entry-header-review__total--title">TỔNG KẾT</h3>
			<div class="border-entry entry-header-review__total">
				<div class="entry-header-review__total__left">
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. 
Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p>
				</div>
				<div class="entry-header-review__total__right">
					<div class="reivew-box__point">
						<?php  
							$total = totalReview($postReview->ID);
						?>
						<div class="reivew-box__point--line"></div>
						<svg viewbox="0 0 36 36" class="circular-chart organ">
							<path class="circle-bg"
								d="M18 2.0845
								a 15.9155 15.9155 0 0 1 0 31.831
								a 15.9155 15.9155 0 0 1 0 -31.831"
							/>
							<path class="circle"
								stroke-dasharray="<?php echo $total * 10; ?>, 100"
								d="M18 2.0845
								a 15.9155 15.9155 0 0 1 0 31.831
								a 15.9155 15.9155 0 0 1 0 -31.831"
							/>
							</svg>
						<p class="reivew-box__point__text">
							<?php 
								textReview($postReview->ID);
							?>
						</p>
						<p class="reivew-box__point__number">
							<?php
								echo $total;
							?>
						</p>
						<fieldset class="rating">
							<?php 
								$totalConvert4 =  round(($total/2)*2) /2;
								for ($i=5; $i > 0; $i--) {?>
									<input <?php if( $totalConvert4 % 2 === 0 &&  $totalConvert4 == $i ){ echo "checked ";} ?>type="radio" id="star<?php echo $i; ?>" name="rating"/>
									<label class = "full" for="star<?php echo $i; ?>" title="Awesome - <?php echo $i; ?> stars"></label>
									<?php if ($i < 5){ 
										?>
										<input 
											<?php if($totalConvert4 > $i &&  $totalConvert4 < ($i + 0.5)) echo "checked "; else ""; ?> 
											type="radio" id="star<?php echo $i; ?>half" name="rating" value="<?php echo $i; ?> and a half" />
										<label class="half" for="star<?php echo $i; ?>half" title="Meh - 1.5 stars"></label>
									<?php
									}
									?>
								<?php
								}
							?>
						</fieldset>
					</div>
				</div>
			</div>
			<div class="border-entry entry-header-review__feel">
				<div class="entry-header-review__feel__right">
						<h3 class="entry-header-review__feel__right__title"> <i class="far fa-thumbs-up"></i>ĐIỂM CỘNG</h3>
						<?php 
							echo $postReview->hcf_add_point;
						?>
				</div>
				<div class="entry-header-review__feel__left">
						<h3 class="entry-header-review__feel__left__title"><i class="far fa-thumbs-down"></i>ĐIỂM TRỪ</h3>
						<?php 
							echo $postReview->hcf_minus_point;
						?>
				</div>
			</div>
		</div>
		<?php 
			}
		?>
		<div class="entry-content">
			<?php if (is_single()) : ?>
				<div class="entry_content__title--single">
					<?php the_title(); ?>
				</div>
				<div class="entry-date">
					<div style="padding-right: 15px;display:inline-block;">
					<i class="far fa-clock"></i> <?php echo get_the_time('d.m.Y', $postReview->ID);?></div>
					<i class="fas fa-pencil-alt"></i> 
						<?php 
							$name = get_the_author_meta('display_name',$postReview->post_author); 
							echo $name;
						?>				
				</div>
			<?php
				endif;
			?>
			<?php if (!is_single() && !is_sticky()) : ?>
			<?php
				$category_detail=get_the_category($postReview->ID);//$post->ID
				$i=0;
				foreach($category_detail as $key => $cd){
					if($i == 0){
						?>
						<div class="entry-content__listing nameCategoryPost" style="background:<?php echo the_newsmag_category_color($cd->term_id); ?>">
						<div id="triangle-up" style="border-bottom-color:<?php echo the_newsmag_category_color($cd->term_id); ?>" class="triangle-up__right">
							
						</div>
							<p class="nameCategoryPost__title">
								<?php echo $cd->name; ?>
							</p>
						</div>
						<?php
						$i++; //added here after edit.
						continue;
					}else if($i > 0) {
						break;
					}
					$i++;
				}
			?>
				<a href="<?php the_permalink(); ?>" class="entry_content__title">
					<?php the_title(); ?>
				</a>
				<div class="entry-date">
					<div style="padding-right: 15px;display:inline-block;">
					<i class="far fa-clock"></i> <?php echo get_the_time('d.m.Y', $postReview->ID);?></div>
					<i class="fas fa-pencil-alt"></i> 
						<?php 
							$name = get_the_author_meta('display_name',$postReview->post_author); 
							echo $name;
						?>				
				</div>
				<?php
			endif;
			?>
			<?php
			if (is_single()) :
				the_content();
			else :
				if (is_sticky()) :
					?>
						<a class="entry_content__link" href="<?php the_permalink(); ?>">Xem chi tiết <i class="fas fa-angle-double-right"></i></a> 
					<?php
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
				<a class="entry_content__link" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					Xem chi tiết <i class="fas fa-angle-double-right"></i>
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
