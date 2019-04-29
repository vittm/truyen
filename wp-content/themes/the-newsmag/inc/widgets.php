<?php

/**
 * Contains all the widgets parts included in the theme
 *
 * @package The NewsMag
 */
class The_NewsMag_Random_Posts_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_random_posts_widget',
			esc_html__( 'TNM: Random Posts Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description'                 => esc_html__( 'Displays the random posts from your site.', 'the-newsmag' ),
				'classname'                   => 'widget-entry-meta the-newsmag-random-posts-widget clear',
				'customize_selective_refresh' => true,
			)
		// Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$number = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		$title  = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number of random posts to display:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo absint( $number ); ?>" size="3">
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance           = array();
		$instance['number'] = ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5;
		$instance['title']  = strip_tags( $new_instance['title'] );

		return $instance;
	}

	function widget( $args, $instance ) {
		$number = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		$title  = isset( $instance['title'] ) ? $instance['title'] : '';

		echo $args['before_widget'];
		?>
		<div class="random-posts-widget" id="random-posts">
			<?php
			global $post;
			$random_posts = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'orderby'             => 'rand',
				'no_found_rows'       => true,
			) );
			?>

			<?php
			if ( ! empty( $title ) ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}
			?>

			<?php
			while ( $random_posts->have_posts() ) :
				$random_posts->the_post();
				?>
				<div class="single-article-content clear">
					<?php if ( has_post_thumbnail() ) { ?>
						<figure class="featured-image">
							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'the-newsmag-featured-small-thumbnail' ); ?></a>
						</figure>
					<?php } ?>
					<h3 class="entry-title">
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
					</h3>
					<div class="entry-meta">
						<?php the_newsmag_widget_posts_posted_on(); ?>
					</div>
				</div>
			<?php
			endwhile;
			// Reset Post Data
			wp_reset_postdata();
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

}

class The_NewsMag_Tabbed_Widget extends WP_Widget {

	/**
	 * Register widget in WordPress
	 */
	function __construct() {
		parent::__construct(
			'the_newsmag_tabbed_widget',
			esc_html__( 'TNM: Tabbed Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description' => esc_html__( 'Displays the popular posts, recent posts and the recent comments in the tabs.', 'the-newsmag' ),
				'classname'   => 'widget-entry-meta the-newsmag-tabbed-widget clear',
			) // Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$number = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number of popular posts, recent posts and comments to display:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo absint( $number ); ?>" size="3">
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance           = array();
		$instance['number'] = ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5;

		return $instance;
	}

	function widget( $args, $instance ) {
		// enqueue the required js files
		if ( is_active_widget( false, false, $this->id_base ) || is_customize_preview() ) {
			wp_enqueue_script( 'jquery-ui-tabs' );
		}

		$number = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		echo $args['before_widget'];
		?>

		<div class="tab-content the-newsmag-tab-content">

			<ul class="the-newsmag-tabs" role="tablist">
				<li role="presentation" class="popular">
					<a href="#popular"><i class="fa fa-star"></i><?php esc_html_e( 'Popular', 'the-newsmag' ); ?></a>
				</li>
				<li role="presentation" class="recent">
					<a href="#recent"><i class="fa fa-history"></i><?php esc_html_e( 'Recent', 'the-newsmag' ); ?></a>
				</li>
				<li role="presentation" class="comment">
					<a href="#user-comments"><i class="fa fa-comment"></i><?php esc_html_e( 'Comment', 'the-newsmag' ); ?>
					</a></li>
			</ul>

			<!-- Popular Tab -->
			<div role="tabpanel" class="tabs-panel popular-tab" id="popular">
				<?php
				global $post;
				$get_popular_posts = new WP_Query( array(
					'posts_per_page'      => $number,
					'post_type'           => 'post',
					'ignore_sticky_posts' => true,
					'orderby'             => 'comment_count',
					'no_found_rows'       => true,
				) );
				?>
				<?php while ( $get_popular_posts->have_posts() ) : $get_popular_posts->the_post(); ?>
					<div class="single-article-content clear">
						<?php if ( has_post_thumbnail() ) { ?>
							<figure class="featured-image">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'the-newsmag-featured-small-thumbnail' ); ?></a>
							</figure>
						<?php } ?>
						<h3 class="entry-title">
							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
						</h3>
						<div class="entry-meta">
							<?php the_newsmag_widget_posts_posted_on(); ?>
						</div>
					</div>
				<?php
				endwhile;
				// Reset Post Data
				wp_reset_postdata();
				?>
			</div>

			<!-- Recent Tab -->
			<div role="tabpanel" class="tabs-panel recent-tab" id="recent">
				<?php
				global $post;
				$get_recent_posts = new WP_Query( array(
					'posts_per_page'      => $number,
					'post_type'           => 'post',
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				) );
				?>
				<?php
				while ( $get_recent_posts->have_posts() ) : $get_recent_posts->the_post();
					?>
					<div class="single-article-content clear">
						<?php if ( has_post_thumbnail() ) { ?>
							<figure class="featured-image">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'the-newsmag-featured-small-thumbnail' ); ?></a>
							</figure>
						<?php } ?>
						<h3 class="entry-title">
							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
						</h3>
						<div class="entry-meta">
							<?php the_newsmag_widget_posts_posted_on(); ?>
						</div>
					</div>
				<?php
				endwhile;
				// Reset Post Data
				wp_reset_postdata();
				?>
			</div>

			<!-- Comment Tab -->
			<div role="tabpanel" class="tabs-panel comment-tab" id="user-comments">
				<?php
				$comments_query = new WP_Comment_Query();
				$comments       = $comments_query->query( array( 'number' => $number, 'status' => 'approve' ) );
				$commented      = '';
				$commented      .= '<ul class="comments-tab">';
				if ( $comments ) : foreach ( $comments as $comment ) :
					$commented .= '<li class="comments-tab-widget clear"><a class="author" href="' . esc_url( get_permalink( $comment->comment_post_ID ) ) . '#comment-' . $comment->comment_ID . '">';
					$commented .= get_avatar( $comment->comment_author_email, '60' );
					$commented .= get_comment_author( $comment->comment_ID ) . '</a>' . ' ' . esc_html__( 'says:', 'the-newsmag' );
					$commented .= '<p class="commented-post">' . strip_tags( substr( apply_filters( 'get_comment_text', $comment->comment_content ), 0, '50' ) ) . '&hellip;</p></li>';
				endforeach;
				else :
					$commented .= '<p class="no-comments-commented-post">' . esc_html__( 'No Comments', 'the-newsmag' ) . '</p>';
				endif;
				$commented .= '</ul>';
				echo $commented;
				?>
			</div>

		</div>

		<?php
		echo $args['after_widget'];
	}

}
class The_NewsMag_Posts_Category_Tab extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_posts_category_tab',
			esc_html__( 'TNM: Posts Category Tab', 'the-newsmag' ), // Name of the widget
			array(
				'description' => esc_html__( 'Displays the latest posts or posts from certain category chosen to be used as tabs.', 'the-newsmag' ),
				'classname'   => 'widget-entry-meta the-newsmag-posts_category_tab clear',
			) // Arguments of the widget, here it is provided with the description
		);
	}
	function form($instance) {
		$title = isset($instance[ 'title' ]) ? $instance[ 'title' ] : 'Categories';
		$instance['category'] = !empty($instance['category']) ? explode(",",$instance['category']) : array();
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title</label>
			<input type="text" class="widfat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" style="width: 100%;" value="<?php echo $title; ?>"/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Select Categories you want to show:' ); ?></label><br />
			<?php $args = array(
					'post_type' => 'post',
					'taxonomy' => 'category',
				);
				$terms = get_terms( $args );
			//print_r($terms);
			foreach( $terms as $id => $name ) { 
				$checked = "";
				if(in_array($name->name,$instance['category'])){
					$checked = "checked='checked'";
				}
			?>
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category[]'); ?>" value="<?php echo $name->name; ?>"  <?php echo $checked; ?>/>
				<label for="<?php echo $this->get_field_id('category'); ?>"><?php echo $name->name; ?></label><br />
			<?php } ?>
		</p>

		<?php
	}
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		$instance['category'] = !empty($new_instance['category']) ? implode(",",$new_instance['category']) : 0;
		return $instance;
	}
	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', $instance[ 'title' ] );
		$postCats = $instance[ 'category' ];
		$categories_list = explode(",", $postCats);

		echo $before_widget;

		$args = array('post_type' => 'post','taxonomy' => 'category',);
		$terms = get_terms( $args );
		?>
		<div class="the-newsmag-posts-slider-widget ">
				<div class="category-links">
					<a> <?php echo $title; ?></a>
				</div><!-- .entry-meta -->
				<ul class="tabs" role="tablist">
					<?php 
						foreach ($categories_list as $cat) {
							foreach($terms as $key => $term) {
								if($cat === $term->name) {
					?>
					<li>
						<input type="radio" name="tabs" id="tab<?php echo $key; ?>" checked />
						<label for="tab<?php echo $key; ?>" 
							role="tab" 
							aria-selected="true" 
							aria-controls="panel<?php echo $key; ?>" 
							tabindex="0"><?php echo $term->name; ?></label>
						<div id="tab-content<?php echo $key; ?>" 
							class="tab-content" 
							role="tabpanel" 
							aria-labelledby="description" 
							aria-hidden="false">
							<?php
								global $postReview;
								$argsStt =  array( 'ID' => 'DESC' );
								$get_recent_posts = get_posts( array(
									'meta_key' => 'the_newsmag_show_select',
									'meta_value' => 'select_home',
									'posts_per_page'      => '1',
									'post_type'           => 'post',
									'ignore_sticky_posts' => true,
									'category__in'        => $term,
									'no_found_rows'       => true,
									'orderby' => $argsStt,
								) );
								?>
								
								<?php
								foreach ( $get_recent_posts	 as $postReview ) {
									setup_postdata( $postReview );
									if(($postReview->hcf_show_review) === on ){
									?>
									<div class="hcf_box review-box">
										<style scoped>
											.hcf_box{
												display: grid;
												grid-template-columns: 40% 60%;
												grid-row-gap: 10px;
												grid-column-gap: 20px;
											}
											.hcf_field{
												display: contents;
											}
										</style>
										<div class="meta-options hcf_field">
												<div class="review-box__left">
													<div class="reivew-box__point">

													</div>
													<a href="<?php the_permalink($postReview->ID); ?>" title="<?php the_title_attribute($postReview->ID); ?>">
													<?php echo $postReview->post_title;?></a>
													<p>Tên tác giả : <strong> <?php echo esc_attr( get_post_meta( $postReview->ID, 'hcf_author', true ) );?></strong></p>
													<p> Thể Loại: <strong><?php echo $term->name; ?></strong> </p>
													<p> Năm: <strong><?php echo esc_attr( get_post_meta( $postReview->ID, 'hcf_year', true ) );?></strong> </p>
													<p> Số tập: <strong><?php echo esc_attr( get_post_meta( $postReview->ID, 'hcf_chap', true ) );?></strong><p>
													<p class="reivew-box__content">
														<?php echo esc_attr( get_post_meta( $postReview->ID, 'hcf_summary', true ) );?>
													</p>
													<a href="">xem chi tiết</a>
												</div>
												<div class="review-box__right">
													<a href="<?php the_permalink($postReview->ID); ?>" title="<?php the_title_attribute($postReview->ID); ?>">
														<img src="<?php $feat_image = wp_get_attachment_url( get_post_thumbnail_id($postReview->ID) ); echo $feat_image;?>" />
														<div class="nameCategoryPost" style="background:<?php echo the_newsmag_category_color($term->term_id) ?>">
															<p class="nameCategoryPost__title">
																<?php 
																	echo $term->name;
																?>
															</p>
														</div>
													</a>
												</div>
										</div>
									</div>
								<?php
								}
								}
								wp_reset_postdata();
								?>
						</div>
					</li>
					<?php } } } ?>
				</ul>
			</div>
		<?php
		
		echo $after_widget;
	}
}
class The_NewsMag_Posts_Slider_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_posts_slider_widget',
			esc_html__( 'TNM: Posts Slider Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description' => esc_html__( 'Displays the latest posts or posts from certain category chosen to be used as the slider.', 'the-newsmag' ),
				'classname'   => 'widget-entry-meta the-newsmag-posts-slider-widget clear',
			) // Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 4;
		$type     = ! empty( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = ! empty( $instance['category'] ) ? $instance['category'] : '';
		$nameCategory = ! empty( $instance['nameCategory'] ) ? $instance['nameCategory'] : '';
		$showItem = ! empty( $instance['showItem'] ) ? $instance['showItem'] : '';
		$typeShow   = ! empty( $instance['typeShow'] ) ? $instance['typeShow'] : 1;
		?>
		<p>
			<label>
				<input type="radio" <?php checked( $type, 'latest' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="latest"><?php esc_html_e( 'Show latest posts.', 'the-newsmag' ); ?>
			</label>
			<br />
			<label>
				<input type="radio" <?php checked( $type, 'category' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="category"><?php esc_html_e( 'Show posts from a certain category.', 'the-newsmag' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php esc_html_e( 'Select the category:', 'the-newsmag' ); ?>
				<?php
				$args = wp_dropdown_categories( array(
					'show_option_none' => ' ',
					'name'             => $this->get_field_name( 'category' ),
					'selected'         => $category,
				) );
				?>
			</label>
		</p>		
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number of posts to display:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo absint( $number ); ?>" size="3">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'nameCategory' ); ?>"><?php esc_html_e( 'Title:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'nameCategory' ); ?>" name="<?php echo $this->get_field_name( 'nameCategory' ); ?>" type="text" value="<?php echo $nameCategory; ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'showItem' ); ?>"><?php esc_html_e( 'Show Item:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'showItem' ); ?>" name="<?php echo $this->get_field_name( 'showItem' ); ?>" type="number" value="<?php echo $showItem; ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'typeShow' ); ?>"><?php esc_html_e( 'Show Item:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'typeShow' ); ?>" name="<?php echo $this->get_field_name( 'typeShow' ); ?>" type="number" value="<?php echo $typeShow; ?>">
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance             = array();
		$instance['number']   = ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 4;
		$instance['type']     = sanitize_key( $new_instance['type'] );
		$instance['category'] = absint( $new_instance['category'] );
		$instance['nameCategory']   = ! empty( $new_instance['nameCategory'] ) ? $new_instance['nameCategory'] : 'Tin Tức';
		$instance['showItem']   = ! empty( $new_instance['showItem'] ) ? $new_instance['showItem'] : 2;
		$instance['typeShow']   = ! empty( $new_instance['typeShow'] ) ? $new_instance['typeShow'] : 1;
		return $instance;
	}

	function widget( $args, $instance ) {
		// enqueue the required js files
		if ( is_active_widget( false, false, $this->id_base ) || is_customize_preview() ) {
			wp_enqueue_script( 'jquery-bxslider' );
		}
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 4;
		$type     = isset( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = isset( $instance['category'] ) ? $instance['category'] : '';
		$nameCategory   = ! empty( $instance['nameCategory'] ) ? $instance['nameCategory'] : 'Tin Tức';
		$showItem   = ! empty( $instance['showItem'] ) ? $instance['showItem'] : 2;
		$typeShow   = ! empty( $instance['typeShow'] ) ? $instance['typeShow'] : 1;
		echo $args['before_widget'];
		?>
		<?php
		global $post;
		if ( $type == 'latest' ) {
			$category_posts_slider = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			) );
		} else {
			$category_posts_slider = new WP_Query( array(
				'meta_key' => 'the_newsmag_show_select',
				'meta_value' => 'select_home',
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'category__in'        => $category,
				'no_found_rows'       => true,
			) );
		}
		?>
		<div class="category-links">
			<a> <?php echo $nameCategory;  ?></a>
		</div><!-- .entry-meta -->
		<div class="the-newsmag-category-slider">
			
		<?php
			$ac = $category_posts_slider->posts;
			$chunks = array_chunk($ac , $showItem);
			// $orchunks =  array_chunk( get_posts( $category_posts_slider->the_post() ), $cols = 3 );
			?>

			<?php
			
			foreach( $chunks as $key => $chunk ){
				setup_postdata($chunk);	
			?>
			
			<div class="single-article-content">
				<?php foreach( $chunk as $key => $post ){ ?>
					<div class="single-article-content__group" style="margin-top: 40px;flex-basis:<?php if($showItem > 3){echo 100/($showItem/2) - 1.5; }else {echo (100/$showItem) ;}?>%">
					<figure class="featured-image">
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_post_thumbnail( 'the-newsmag-featured-large-thumbnail' ); ?>
						</a>
					</figure>
					<?php if($typeShow == 1) { ?>
						<a class="single-article-content__group__title" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
					<?php } else if($typeShow == 2) { ?>
						<div class="category-title-meta-wrapper">
							<div class="entry-meta">
								<?php the_newsmag_widget_posts_posted_on(); ?>
							</div>
							<h3 class="entry-title">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</h3>
							<p class="entry-content">
								<?php the_content(); ?> 
							</p>
						</div>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
			<?php
			}
			?>
			<?php
			
			// Reset Post Data
			wp_reset_postdata();
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

}

class The_NewsMag_Posts_Grid_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_posts_grid_widget',
			esc_html__( 'TNM: Posts Grid Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description'                 => esc_html__( 'Displays the latest posts or posts from certain category chosen to be used in beside the slider area.', 'the-newsmag' ),
				'classname'                   => 'widget-entry-meta the-newsmag-posts-grid-widget clear',
				'customize_selective_refresh' => true,
			) // Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 4;
		$type     = ! empty( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = ! empty( $instance['category'] ) ? $instance['category'] : '';
		?>
		<p>
			<label>
				<input type="radio" <?php checked( $type, 'latest' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="latest"><?php esc_html_e( 'Show latest posts.', 'the-newsmag' ); ?>
			</label>
			<br />
			<label>
				<input type="radio" <?php checked( $type, 'category' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="category"><?php esc_html_e( 'Show posts from a certain category.', 'the-newsmag' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php esc_html_e( 'Select the category:', 'the-newsmag' ); ?>
				<?php
				wp_dropdown_categories( array(
					'show_option_none' => ' ',
					'name'             => $this->get_field_name( 'category' ),
					'selected'         => $category,
				) );
				?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number of posts to display:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo absint( $number ); ?>" size="3">
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance             = array();
		$instance['number']   = ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 4;
		$instance['type']     = sanitize_key( $new_instance['type'] );
		$instance['category'] = absint( $new_instance['category'] );

		return $instance;
	}

	function widget( $args, $instance ) {
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 4;
		$type     = isset( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = isset( $instance['category'] ) ? $instance['category'] : '';

		echo $args['before_widget'];
		?>
		<?php
		global $post;
		if ( $type == 'latest' ) {
			$category_posts_grid = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			) );
		} else {
			$category_posts_grid = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'category__in'        => $category,
				'no_found_rows'       => true,
			) );
		}
		?>

		<div class="the-newsmag-posts-grid">
			<?php
			while ( $category_posts_grid->have_posts() ) :
				$category_posts_grid->the_post();
				?>
				<?php if ( has_post_thumbnail() ) { ?>
				<div class="single-article-content">
					<div class="category-links">
						<?php the_newsmag_colored_category(); ?>
					</div><!-- .entry-meta -->

					<figure class="featured-image">
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'the-newsmag-featured-medium-thumbnail' ); ?></a>
					</figure>

					<div class="category-title-meta-wrapper">
						<h3 class="entry-title">
							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
						</h3>
						<div class="entry-meta">
							<?php the_newsmag_widget_posts_posted_on(); ?>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php
			endwhile;
			// Reset Post Data
			wp_reset_postdata();
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

}

class The_NewsMag_Posts_One_Column_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_posts_one_column_widget',
			esc_html__( 'TNM: Posts One Column Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description'                 => esc_html__( 'Displays the latest posts or posts from certain category chosen to display the posts in single column.', 'the-newsmag' ),
				'classname'                   => 'widget-entry-meta the-newsmag-one-column-widget clear',
				'customize_selective_refresh' => true,
			) // Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$text     = ! empty( $instance['text'] ) ? $instance['text'] : '';
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		$type     = ! empty( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = ! empty( $instance['category'] ) ? $instance['category'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php esc_html_e( 'Description', 'the-newsmag' ); ?></label>
			<textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo esc_attr( $text ); ?></textarea>
		</p>

		<p>
			<label>
				<input type="radio" <?php checked( $type, 'latest' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="latest"><?php esc_html_e( 'Show latest posts.', 'the-newsmag' ); ?>
			</label>
			<br />
			<label>
				<input type="radio" <?php checked( $type, 'category' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="category"><?php esc_html_e( 'Show posts from a certain category.', 'the-newsmag' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php esc_html_e( 'Select the category:', 'the-newsmag' ); ?>
				<?php
				wp_dropdown_categories( array(
					'show_option_none' => ' ',
					'name'             => $this->get_field_name( 'category' ),
					'selected'         => $category,
				) );
				?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number of posts to display:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo absint( $number ); ?>" size="3">
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance             = array();
		$instance['title']    = strip_tags( $new_instance['title'] );
		$instance['text']     = sanitize_text_field( $new_instance['text'] );
		$instance['number']   = ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5;
		$instance['type']     = sanitize_key( $new_instance['type'] );
		$instance['category'] = absint( $new_instance['category'] );

		return $instance;
	}

	function widget( $args, $instance ) {
		$title    = isset( $instance['title'] ) ? $instance['title'] : '';
		$text     = isset( $instance['text'] ) ? $instance['text'] : '';
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		$type     = isset( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = isset( $instance['category'] ) ? $instance['category'] : '';

		echo $args['before_widget'];
		?>
		<?php
		global $post;
		if ( $type == 'latest' ) {
			$category_column_one = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			) );
		} else {
			$category_column_one = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'category__in'        => $category,
				'no_found_rows'       => true,
			) );
		}
		?>

		<?php
		if ( $type != 'latest' ) {
			$border_color = 'style="border-bottom-color:' . the_newsmag_category_color( $category ) . ';"';
			$title_color  = 'style="background-color:' . the_newsmag_category_color( $category ) . ';"';
		} else {
			$border_color = '';
			$title_color  = '';
		}

		if ( ! empty( $title ) ) {
			echo '<h3 class="widget-title" ' . $border_color . '><span ' . $title_color . '>' . esc_html( $title ) . '</span></h3>';
		}
		if ( ! empty( $text ) ) {
			?>
			<p><?php echo esc_html( $text ); ?></p>
		<?php } ?>

		<div class="the-newsmag-one-column-posts">
			<?php
			$i = 1;
			while ( $category_column_one->have_posts() ) :
				$category_column_one->the_post();
				if ( $i == 1 ) {
					echo '<div class="first-post">';
					$featured_image_size = 'the-newsmag-featured-medium-thumbnail';
				} else if ( $i == 2 ) {
					echo '<div class="following-post">';
					$featured_image_size = 'the-newsmag-featured-small-thumbnail';
				}
				// adding the class name upon thumbnail availablility
				if ( has_post_thumbnail() ) {
					$class = 'has-featured-image';
				} else {
					$class = 'no-featured-image';
				}
				?>
				<div class="single-article-content clear">
					<div class="posts-column-wrapper <?php echo esc_attr( $class ); ?>">
						<?php if ( $i == 1 ) { ?>
							<div class="category-links">
								<?php the_newsmag_colored_category(); ?>
							</div><!-- .entry-meta -->
						<?php } ?>

						<?php if ( has_post_thumbnail() ) { ?>
							<figure class="featured-image">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( $featured_image_size ); ?></a>
							</figure>
						<?php } ?>

						<div class="category-title-meta-wrapper">
							<h3 class="entry-title">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</h3>
							<div class="entry-meta">
								<?php the_newsmag_widget_posts_posted_on(); ?>
							</div>
						</div>
					</div>

					<?php if ( $i == 1 ) { ?>
						<div class="entry-content">
							<?php the_excerpt(); ?>
						</div>
					<?php } ?>

				</div>
				<?php
				if ( $i == 1 ) {
					echo '</div>';
				}
				$i ++;
			endwhile;
			if ( $i > 2 ) {
				echo '</div>';
			}
			// Reset Post Data
			wp_reset_postdata();
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

}

class The_NewsMag_Posts_Two_Column_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_posts_two_column_widget',
			esc_html__( 'TNM: Posts Two Column Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description'                 => esc_html__( 'Displays the latest posts or posts from certain category chosen to display the posts in double column.', 'the-newsmag' ),
				'classname'                   => 'widget-entry-meta the-newsmag-two-column-widget clear',
				'customize_selective_refresh' => true,
			) // Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$text     = ! empty( $instance['text'] ) ? $instance['text'] : '';
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		$type     = ! empty( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = ! empty( $instance['category'] ) ? $instance['category'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php esc_html_e( 'Description', 'the-newsmag' ); ?></label>
			<textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo esc_attr( $text ); ?></textarea>
		</p>

		<p>
			<label>
				<input type="radio" <?php checked( $type, 'latest' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="latest"><?php esc_html_e( 'Show latest posts.', 'the-newsmag' ); ?>
			</label>
			<br />
			<label>
				<input type="radio" <?php checked( $type, 'category' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="category"><?php esc_html_e( 'Show posts from a certain category.', 'the-newsmag' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php esc_html_e( 'Select the category:', 'the-newsmag' ); ?>
				<?php
				wp_dropdown_categories( array(
					'show_option_none' => ' ',
					'name'             => $this->get_field_name( 'category' ),
					'selected'         => $category,
				) );
				?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number of posts to display:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo absint( $number ); ?>" size="3">
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance             = array();
		$instance['title']    = strip_tags( $new_instance['title'] );
		$instance['text']     = sanitize_text_field( $new_instance['text'] );
		$instance['number']   = ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5;
		$instance['type']     = sanitize_key( $new_instance['type'] );
		$instance['category'] = absint( $new_instance['category'] );

		return $instance;
	}

	function widget( $args, $instance ) {
		$title    = isset( $instance['title'] ) ? $instance['title'] : '';
		$text     = isset( $instance['text'] ) ? $instance['text'] : '';
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		$type     = isset( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = isset( $instance['category'] ) ? $instance['category'] : '';

		echo $args['before_widget'];
		?>
		<?php
		global $post;
		if ( $type == 'latest' ) {
			$category_column_two = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			) );
		} else {
			$category_column_two = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'category__in'        => $category,
				'no_found_rows'       => true,
			) );
		}
		?>

		<?php
		if ( $type != 'latest' ) {
			$border_color = 'style="border-bottom-color:' . the_newsmag_category_color( $category ) . ';"';
			$title_color  = 'style="background-color:' . the_newsmag_category_color( $category ) . ';"';
		} else {
			$border_color = '';
			$title_color  = '';
		}

		if ( ! empty( $title ) ) {
			echo '<h3 class="widget-title" ' . $border_color . '><span ' . $title_color . '>' . esc_html( $title ) . '</span></h3>';
		}
		if ( ! empty( $text ) ) {
			?>
			<p><?php echo esc_html( $text ); ?></p>
		<?php } ?>

		<div class="the-newsmag-two-column-posts">
			<?php
			$i = 1;
			while ( $category_column_two->have_posts() ) :
				$category_column_two->the_post();
				if ( $i == 1 ) {
					echo '<div class="first-post">';
					$featured_image_size = 'the-newsmag-featured-medium-thumbnail';
				} else if ( $i == 2 ) {
					echo '<div class="following-post">';
					$featured_image_size = 'the-newsmag-featured-small-thumbnail';
				}
				// adding the class name upon thumbnail availablility
				if ( has_post_thumbnail() ) {
					$class = 'has-featured-image';
				} else {
					$class = 'no-featured-image';
				}
				?>
				<div class="single-article-content clear">
					<div class="posts-column-wrapper <?php echo esc_attr( $class ); ?>">
						<?php if ( $i == 1 ) { ?>
							<div class="category-links">
								<?php the_newsmag_colored_category(); ?>
							</div><!-- .entry-meta -->
						<?php } ?>

						<?php if ( has_post_thumbnail() ) { ?>
							<figure class="featured-image">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( $featured_image_size ); ?></a>
							</figure>
						<?php } ?>

						<div class="category-title-meta-wrapper">
							<h3 class="entry-title">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</h3>
							<div class="entry-meta">
								<?php the_newsmag_widget_posts_posted_on(); ?>
							</div>
						</div>
					</div>

					<?php if ( $i == 1 ) { ?>
						<div class="entry-content">
							<?php the_excerpt(); ?>
						</div>
					<?php } ?>

				</div>
				<?php
				if ( $i == 1 ) {
					echo '</div>';
				}
				$i ++;
			endwhile;
			if ( $i > 2 ) {
				echo '</div>';
			}
			// Reset Post Data
			wp_reset_postdata();
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

}

class The_NewsMag_Posts_Extended_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_posts_extended_widget',
			esc_html__( 'TNM: Posts Extended Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description'                 => esc_html__( 'Displays the latest posts or posts from certain category chosen to display the posts and its excerpt.', 'the-newsmag' ),
				'classname'                   => 'widget-entry-meta the-newsmag-posts-extended-widget clear',
				'customize_selective_refresh' => true,
			) // Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$text     = ! empty( $instance['text'] ) ? $instance['text'] : '';
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		$type     = ! empty( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = ! empty( $instance['category'] ) ? $instance['category'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php esc_html_e( 'Description', 'the-newsmag' ); ?></label>
			<textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo esc_attr( $text ); ?></textarea>
		</p>

		<p>
			<label>
				<input type="radio" <?php checked( $type, 'latest' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="latest"><?php esc_html_e( 'Show latest posts.', 'the-newsmag' ); ?>
			</label>
			<br />
			<label>
				<input type="radio" <?php checked( $type, 'category' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="category"><?php esc_html_e( 'Show posts from a certain category.', 'the-newsmag' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php esc_html_e( 'Select the category:', 'the-newsmag' ); ?>
				<?php
				wp_dropdown_categories( array(
					'show_option_none' => ' ',
					'name'             => $this->get_field_name( 'category' ),
					'selected'         => $category,
				) );
				?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number of posts to display:', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo absint( $number ); ?>" size="3">
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance             = array();
		$instance['title']    = strip_tags( $new_instance['title'] );
		$instance['text']     = sanitize_text_field( $new_instance['text'] );
		$instance['number']   = ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5;
		$instance['type']     = sanitize_key( $new_instance['type'] );
		$instance['category'] = absint( $new_instance['category'] );

		return $instance;
	}

	function widget( $args, $instance ) {
		$title    = isset( $instance['title'] ) ? $instance['title'] : '';
		$text     = isset( $instance['text'] ) ? $instance['text'] : '';
		$number   = ! empty( $instance['number'] ) ? $instance['number'] : 5;
		$type     = isset( $instance['type'] ) ? $instance['type'] : 'latest';
		$category = isset( $instance['category'] ) ? $instance['category'] : '';

		echo $args['before_widget'];
		?>
		<?php
		global $post;
		if ( $type == 'latest' ) {
			$category_extended = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			) );
		} else {
			$category_extended = new WP_Query( array(
				'posts_per_page'      => $number,
				'post_type'           => 'post',
				'ignore_sticky_posts' => true,
				'category__in'        => $category,
				'no_found_rows'       => true,
			) );
		}
		?>

		<?php
		if ( $type != 'latest' ) {
			$border_color = 'style="border-bottom-color:' . the_newsmag_category_color( $category ) . ';"';
			$title_color  = 'style="background-color:' . the_newsmag_category_color( $category ) . ';"';
		} else {
			$border_color = '';
			$title_color  = '';
		}

		if ( ! empty( $title ) ) {
			echo '<h3 class="widget-title" ' . $border_color . '><span ' . $title_color . '>' . esc_html( $title ) . '</span></h3>';
		}
		if ( ! empty( $text ) ) {
			?>
			<p><?php echo esc_html( $text ); ?></p>
		<?php } ?>

		<div class="the-newsmag-extended-posts-widget">
			<?php
			while ( $category_extended->have_posts() ) :
				$category_extended->the_post();
				// adding the class name upon thumbnail availablility
				if ( has_post_thumbnail() ) {
					$class = 'has-featured-image';
				} else {
					$class = 'no-featured-image';
				}
				?>
				<div class="single-article-content clear">
					<div class="posts-column-wrapper <?php echo esc_attr( $class ); ?>">
						<div class="category-links">
							<?php the_newsmag_colored_category(); ?>
						</div><!-- .entry-meta -->

						<?php if ( has_post_thumbnail() ) { ?>
							<figure class="featured-image">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'the-newsmag-featured-medium-thumbnail' ); ?></a>
							</figure>
						<?php } ?>

						<div class="post-details">
							<h3 class="entry-title">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</h3>
							<div class="entry-meta">
								<?php the_newsmag_widget_posts_posted_on(); ?>
							</div>
							<div class="entry-content">
								<?php the_excerpt(); ?>
							</div>
						</div>
					</div>

				</div>
			<?php
			endwhile;
			// Reset Post Data
			wp_reset_postdata();
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

}

class The_NewsMag_728x90_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_728x90_widget',
			esc_html__( 'TNM: 728 x 90 Advertisement Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description'                 => esc_html__( 'Add the required 728 x 90 advertisement in your site with the help of the image.', 'the-newsmag' ),
				'classname'                   => 'widget-entry-meta the-newsmag-728x90-widget clear',
				'customize_selective_refresh' => true,
			) // Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$image_link = ! empty( $instance['image_link'] ) ? $instance['image_link'] : '';
		$image_url  = ! empty( $instance['image_url'] ) ? $instance['image_url'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'image_link' ); ?>"><?php esc_html_e( 'Advertisement Image Link', 'the-newsmag' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'image_link' ); ?>" name="<?php echo $this->get_field_name( 'image_link' ); ?>" type="text" value="<?php echo esc_url( $image_link ); ?>" />
		</p>

		<label for="<?php echo $this->get_field_id( 'image_url' ); ?>"><?php esc_html_e( 'Advertisement Image', 'the-newsmag' ); ?></label>
		<div class="media-uploader" id="<?php echo $this->get_field_id( 'image_url' ); ?>">
			<div class="custom_media_preview">
				<?php if ( ! empty( $image_url ) ) : ?>
					<img class="custom_media_preview_default" src="<?php echo esc_url( $image_url ); ?>" style="max-width:100%;" />
				<?php endif; ?>
			</div>
			<input type="text" class="widefat custom_media_input" id="<?php echo $this->get_field_id( 'image_url' ); ?>" name="<?php echo $this->get_field_name( 'image_url' ); ?>" value="<?php echo esc_url( $image_url ); ?>" style="margin-top:2px;" />
			<button class="custom_media_upload button button-primary" id="<?php echo $this->get_field_id( 'image_url' ); ?>" data-choose="<?php esc_attr_e( 'Choose image', 'the-newsmag' ); ?>" data-update="<?php esc_attr_e( 'Use image', 'the-newsmag' ); ?>" style="margin-top:6px;"><?php esc_html_e( 'Select Image', 'the-newsmag' ); ?></button>
		</div>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance               = array();
		$instance['title']      = strip_tags( $new_instance['title'] );
		$instance['image_link'] = esc_url_raw( $new_instance['image_link'] );
		$instance['image_url']  = esc_url_raw( $new_instance['image_url'] );

		return $instance;
	}

	function widget( $args, $instance ) {
		$title      = isset( $instance['title'] ) ? $instance['title'] : '';
		$image_link = isset( $instance['image_link'] ) ? $instance['image_link'] : '';
		$image_url  = isset( $instance['image_url'] ) ? $instance['image_url'] : '';

		echo $args['before_widget'];
		?>

		<div class="advertisement-728x90">
			<?php if ( ! empty( $title ) ) { ?>
				<div class="advertisement-title">
					<?php echo $args['before_title'] . esc_html( $title ) . $args['after_title']; ?>
				</div>
			<?php } ?>

			<?php
			$output = '';
			if ( ! empty( $image_url ) ) {
				$output .= '<div class="advertisement-image">';
				if ( ! empty( $image_link ) ) {
					$output .= '<a href="' . $image_link . '" class="advetisement-728x90" target="_blank" rel="nofollow">
									<img src="' . $image_url . '" width="728" height="90">
						   </a>';
				} else {
					$output .= '<img src="' . $image_url . '" width="728" height="90">';
				}
				$output .= '</div>';
				echo $output;
			}
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

}

class The_NewsMag_300x250_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_300x250_widget',
			esc_html__( 'TNM: 300 x 250 Advertisement Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description'                 => esc_html__( 'Add the required 300 x 250 advertisement in your site with the help of the image.', 'the-newsmag' ),
				'classname'                   => 'widget-entry-meta the-newsmag-300x250-widget clear',
				'customize_selective_refresh' => true,
			) // Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$image_link = ! empty( $instance['image_link'] ) ? $instance['image_link'] : '';
		$image_url  = ! empty( $instance['image_url'] ) ? $instance['image_url'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'image_link' ); ?>"><?php esc_html_e( 'Advertisement Image Link', 'the-newsmag' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'image_link' ); ?>" name="<?php echo $this->get_field_name( 'image_link' ); ?>" type="text" value="<?php echo esc_url( $image_link ); ?>" />
		</p>

		<label for="<?php echo $this->get_field_id( 'image_url' ); ?>"><?php esc_html_e( 'Advertisement Image', 'the-newsmag' ); ?></label>
		<div class="media-uploader" id="<?php echo $this->get_field_id( 'image_url' ); ?>">
			<div class="custom_media_preview">
				<?php if ( ! empty( $image_url ) ) : ?>
					<img class="custom_media_preview_default" src="<?php echo esc_url( $image_url ); ?>" style="max-width:100%;" />
				<?php endif; ?>
			</div>
			<input type="text" class="widefat custom_media_input" id="<?php echo $this->get_field_id( 'image_url' ); ?>" name="<?php echo $this->get_field_name( 'image_url' ); ?>" value="<?php echo esc_url( $image_url ); ?>" style="margin-top:2px;" />
			<button class="custom_media_upload button button-primary" id="<?php echo $this->get_field_id( 'image_url' ); ?>" data-choose="<?php esc_attr_e( 'Choose image', 'the-newsmag' ); ?>" data-update="<?php esc_attr_e( 'Use image', 'the-newsmag' ); ?>" style="margin-top:6px;"><?php esc_html_e( 'Select Image', 'the-newsmag' ); ?></button>
		</div>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance               = array();
		$instance['title']      = strip_tags( $new_instance['title'] );
		$instance['image_link'] = esc_url_raw( $new_instance['image_link'] );
		$instance['image_url']  = esc_url_raw( $new_instance['image_url'] );

		return $instance;
	}

	function widget( $args, $instance ) {
		$title      = isset( $instance['title'] ) ? $instance['title'] : '';
		$image_link = isset( $instance['image_link'] ) ? $instance['image_link'] : '';
		$image_url  = isset( $instance['image_url'] ) ? $instance['image_url'] : '';

		echo $args['before_widget'];
		?>

		<div class="advertisement-300x250">
			<?php if ( ! empty( $title ) ) { ?>
				<div class="advertisement-title">
					<?php echo $args['before_title'] . esc_html( $title ) . $args['after_title']; ?>
				</div>
			<?php } ?>

			<?php
			$output = '';
			if ( ! empty( $image_url ) ) {
				$output .= '<div class="advertisement-image">';
				if ( ! empty( $image_link ) ) {
					$output .= '<a href="' . $image_link . '" class="advetisement-300x250" target="_blank" rel="nofollow">
									<img src="' . $image_url . '" width="300" height="250">
						   </a>';
				} else {
					$output .= '<img src="' . $image_url . '" width="300" height="250">';
				}
				$output .= '</div>';
				echo $output;
			}
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

}

class The_NewsMag_125x125_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'the_newsmag_125x125_widget',
			esc_html__( 'TNM: 125 x 125 Advertisement Widget', 'the-newsmag' ), // Name of the widget
			array(
				'description'                 => esc_html__( 'Add the required 125 x 125 advertisement in your site with the help of the image.', 'the-newsmag' ),
				'classname'                   => 'widget-entry-meta the-newsmag-125x125-widget clear',
				'customize_selective_refresh' => true,
			) // Arguments of the widget, here it is provided with the description
		);
	}

	function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		for ( $i = 1; $i < 7; $i ++ ) {
			$instance[ 'image_link' . $i ] = ! empty( $instance[ 'image_link' . $i ] ) ? $instance[ 'image_link' . $i ] : '';
			$instance[ 'image_url' . $i ]  = ! empty( $instance[ 'image_url' . $i ] ) ? $instance[ 'image_url' . $i ] : '';
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'the-newsmag' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php for ( $i = 1; $i < 7; $i ++ ) { ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'image_link' . $i ); ?>"><?php
					esc_html_e( 'Advertisement Image Link', 'the-newsmag' );
					echo $i;
					?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'image_link' . $i ); ?>" name="<?php echo $this->get_field_name( 'image_link' . $i ); ?>" type="text" value="<?php echo esc_url( $instance[ 'image_link' . $i ] ); ?>" />
			</p>

			<label for="<?php echo $this->get_field_id( 'image_url' . $i ); ?>"><?php
				esc_html_e( 'Advertisement Image', 'the-newsmag' );
				echo $i;
				?></label>
			<div class="media-uploader" id="<?php echo $this->get_field_id( 'image_url' . $i ); ?>">
				<div class="custom_media_preview">
					<?php if ( ! empty( $instance[ 'image_url' . $i ] ) ) : ?>
						<img class="custom_media_preview_default" src="<?php echo esc_url( $instance[ 'image_url' . $i ] ); ?>" style="max-width:100%;" />
					<?php endif; ?>
				</div>
				<input type="text" class="widefat custom_media_input" id="<?php echo $this->get_field_id( 'image_url' . $i ); ?>" name="<?php echo $this->get_field_name( 'image_url' . $i ); ?>" value="<?php echo esc_url( $instance[ 'image_url' . $i ] ); ?>" style="margin-top:2px;" />
				<button class="custom_media_upload button button-primary" id="<?php echo $this->get_field_id( 'image_url' . $i ); ?>" data-choose="<?php esc_attr_e( 'Choose image', 'the-newsmag' ); ?>" data-update="<?php esc_attr_e( 'Use image', 'the-newsmag' ); ?>" style="margin-top:6px;"><?php esc_html_e( 'Select Image', 'the-newsmag' ); ?></button>
			</div>
			<?php
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		for ( $i = 1; $i < 7; $i ++ ) {
			$instance[ 'image_link' . $i ] = esc_url_raw( $new_instance[ 'image_link' . $i ] );
			$instance[ 'image_url' . $i ]  = esc_url_raw( $new_instance[ 'image_url' . $i ] );
		}

		return $instance;
	}

	function widget( $args, $instance ) {
		$title       = isset( $instance['title'] ) ? $instance['title'] : '';
		$image_array = array();
		$link_array  = array();
		for ( $i = 1; $i < 7; $i ++ ) {
			$image_link = isset( $instance[ 'image_link' . $i ] ) ? $instance[ 'image_link' . $i ] : '';
			$image_url  = isset( $instance[ 'image_url' . $i ] ) ? $instance[ 'image_url' . $i ] : '';
			if ( ! empty( $image_link ) ) {
				array_push( $link_array, $image_link );
			}
			if ( ! empty( $image_url ) ) {
				array_push( $image_array, $image_url );
			}
		}

		echo $args['before_widget'];
		?>

		<div class="advertisement-125x125">
			<?php if ( ! empty( $title ) ) { ?>
				<div class="advertisement-title">
					<?php echo $args['before_title'] . esc_html( $title ) . $args['after_title']; ?>
				</div>
			<?php } ?>

			<?php
			$output = '';
			if ( ! empty( $image_array ) ) {
				$output .= '<div class="advertisement-image">';
				for ( $i = 1; $i < 7; $i ++ ) {
					$j = $i - 1;
					if ( ! empty( $image_array[ $j ] ) ) {
						if ( ! empty( $link_array[ $j ] ) ) {
							$output .= '<a href="' . $link_array[ $j ] . '" class="advetisement-125x125" target="_blank" rel="nofollow">
								 <img src="' . $image_array[ $j ] . '" width="125" height="125">
							  </a>';
						} else {
							$output .= '<img src="' . $image_array[ $j ] . '" width="125" height="125">';
						}
					}
				}
				$output .= '</div>';
				echo $output;
			}
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

}

?>
