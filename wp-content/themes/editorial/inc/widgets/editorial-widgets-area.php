<?php
/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 *
 * @package Mystery Themes
 * @subpackage Editorial
 * @since 1.0.0
 */
function editorial_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'editorial' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Left Sidebar', 'editorial' ),
		'id'            => 'editorial_left_sidebar',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Header Ads', 'editorial' ),
		'id'            => 'editorial_header_ads_area',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'HomePage Slider Area', 'editorial' ),
		'id'            => 'editorial_home_slider_area',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'HomePage Content Area', 'editorial' ),
		'id'            => 'editorial_home_content_area',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'HomePage Sidebar', 'editorial' ),
		'id'            => 'editorial_home_sidebar',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 1st Column', 'editorial' ),
		'id'            => 'editorial_footer_one',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 2nd Column', 'editorial' ),
		'id'            => 'editorial_footer_two',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 3rd Column', 'editorial' ),
		'id'            => 'editorial_footer_three',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 4th Column', 'editorial' ),
		'id'            => 'editorial_footer_four',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

}
add_action( 'widgets_init', 'editorial_widgets_init' );

/**
 * register widgets
 */

add_action( 'widgets_init', 'editorial_register_widgets' );

function editorial_register_widgets() {
	
	// Ads Banner
	register_widget( 'Editorial_Ads_Banner' );

	// Block Column
	register_widget( 'Editorial_Block_Column' );

	// Block Grid
	register_widget( 'Editorial_Block_Grid' );

	// Block Layout
	register_widget( 'Editorial_Block_Layout' );

	// Block List
	register_widget( 'Editorial_Block_List' );

	// Featured Slider
	register_widget( 'Editorial_Featured_Slider' );

	// Posts List
	register_widget( 'Editorial_Posts_List' );

}


/**
 * Load widgets files
 */
require get_template_directory() . '/inc/widgets/editorial-widget-fields.php';
require get_template_directory() . '/inc/widgets/editorial-featured-slider.php';
require get_template_directory() . '/inc/widgets/editorial-block-grid.php';
require get_template_directory() . '/inc/widgets/editorial-block-column.php';
require get_template_directory() . '/inc/widgets/editorial-ads-banner.php';
require get_template_directory() . '/inc/widgets/editorial-block-layout.php';
require get_template_directory() . '/inc/widgets/editorial-posts-list.php';
require get_template_directory() . '/inc/widgets/editorial-block-list.php';