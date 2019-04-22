<?php
/**
 * Editorial Theme Customizer.
 *
 * @package Mystery Themes
 * @subpackage Editorial
 * @since 1.0.0
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function editorial_customize_register( $wp_customize ) {

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	/**
     * Register custom section types.
     *
     * @since 1.2.8
     */
    $wp_customize->register_section_type( 'Editorial_Customize_Section_Upsell' );

    /**
     * Register theme upsell sections.
     *
     * @since 1.2.8
     */
    $wp_customize->add_section( new Editorial_Customize_Section_Upsell(
        $wp_customize,
            'theme_upsell',
            array(
                'title'    => esc_html__( 'Editorial Pro', 'editorial' ),
                'pro_text' => esc_html__( 'Buy Pro', 'editorial' ),
                'pro_url'  => 'https://mysterythemes.com/wp-themes/editorial-pro/',
                'priority'  => 1,
            )
        )
    );

    $wp_customize->add_section(
        'editorial_imp_link_section',
        array(
            'title'      => __( 'Important Theme Links', 'editorial' ),
            'priority'   => 30
        )
    );

    $wp_customize->add_setting(
        'editorial_imp_links',
        array(
            'sanitize_callback' => 'editorial_sanitize_text'
        )
    );

    $wp_customize->add_control( new Editorial_Info_Content(
        $wp_customize,
            'editorial_imp_links',
            array(
                'section'       => 'editorial_imp_link_section',
                'description'   => '<a class="mt-imp-link" href="http://docs.mysterythemes.com/editorial/" target="_blank">'.__( 'Documentation', 'editorial' ).'</a><a class="mt-imp-link" href="http://demo.mysterythemes.com/editorial/" target="_blank">'.__( 'Live Demo', 'editorial' ).'</a><a class="mt-imp-link" href="https://mysterythemes.com/support/forum/themes/free-themes/editorial/" target="_blank">'.__( 'Support Forum', 'editorial' ).'</a><a class="mt-imp-link" href="https://www.facebook.com/mysterythemes/" target="_blank">'.__( 'Like Us in Facebook', 'editorial' ).'</a><a class="mt-imp-link" href="https://mysterythemes.com/wp-themes/editorial-pro/" target="_blank">'.__( 'Upgrade to Pro', 'editorial' ).'</a>',
            )
        )
    );

    $wp_customize->add_setting(
        'editorial_rate_us',
        array(
            'sanitize_callback' => 'editorial_sanitize_text'
        )
    );

    $wp_customize->add_control( new Editorial_Info_Content( 
        $wp_customize,
            'editorial_rate_us',
            array(
                'section'       => 'editorial_imp_link_section',
                'description'   => sprintf(__( 'Please do rate our theme if you liked it %s', 'editorial' ), '<a class="mt-imp-link" href="https://wordpress.org/support/theme/editorial/reviews/?filter=5" target="_blank">Rate/Review</a>' ),
            )
        )
    );
}
add_action( 'customize_register', 'editorial_customize_register' );

/*-----------------------------------------------------------------------------------------------------------------------*/

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function editorial_customize_preview_js() {
	global $editorial_version;
	wp_enqueue_script( 'editorial_customizer', get_template_directory_uri() . '/assets/js/customizer.js', array( 'customize-preview' ), esc_attr( $editorial_version ), true );
}
add_action( 'customize_preview_init', 'editorial_customize_preview_js' );

/*-----------------------------------------------------------------------------------------------------------------------*/
/**
 * Enqueue required scripts/styles for customizer panel
 *
 * @since 1.3.7
 */
function editorial_customize_backend_scripts() {

    wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/assets/library/font-awesome/css/font-awesome.min.css', array(), '4.7.0' );

    wp_enqueue_style( 'editorial_admin_customizer_style', get_template_directory_uri() . '/assets/css/customizer-style.css' );

    wp_enqueue_script( 'editorial_admin_customizer', get_template_directory_uri() . '/assets/js/customizer-controls.js', array( 'jquery', 'customize-controls' ), '20170616', true );
}
add_action( 'customize_controls_enqueue_scripts', 'editorial_customize_backend_scripts', 10 );

/*-----------------------------------------------------------------------------------------------------------------------*/

/**
 * Customizer Callback functions
 */
function editorial_related_articles_option_callback( $control ) {
    if ( $control->manager->get_setting( 'editorial_related_articles_option' )->value() != 'disable' ) {
        return true;
    } else {
        return false;
    }
}

/*-----------------------------------------------------------------------------------------------------------------------*/

/**
 * Load customizer panels
 */
require get_template_directory() . '/inc/customizer/general-panel.php'; //General settings panel
require get_template_directory() . '/inc/customizer/header-panel.php'; //header settings panel
require get_template_directory() . '/inc/customizer/design-panel.php'; //Design Settings panel
require get_template_directory() . '/inc/customizer/additional-panel.php'; //Additional settings panel

/**
 * Load customizer custom classes
 */
require get_template_directory() . '/inc/customizer/editorial-custom-classes.php'; //custom classes
require get_template_directory() . '/inc/customizer/editorial-sanitize.php'; //sanitize