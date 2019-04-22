<?php
/**
 * Define function about sanitation for customizer option
 * 
 * @package Mystery Themes
 * @subpackage Editorial
 * @since 1.0.0
 */

//Check box
function editorial_sanitize_checkbox( $input ) {
    if ( $input == 1 ) {
        return 1;
    } else {
        return 0;
    }
}

// site layout
function editorial_sanitize_site_layout($input) {
    $valid_keys = array(
        'fullwidth_layout' => __( 'Fullwidth Layout', 'editorial' ),
        'boxed_layout'     => __( 'Boxed Layout', 'editorial' )
        );
    if ( array_key_exists( $input, $valid_keys ) ) {
        return $input;
    } else {
        return '';
    }
}

// Switch option (enable/disable)
function editorial_enable_switch_sanitize($input) {
    $valid_keys = array(
        'enable'    => __( 'Enable', 'editorial' ),
        'disable'   => __( 'Disable', 'editorial' )
        );
    if ( array_key_exists( $input, $valid_keys ) ) {
        return $input;
    } else {
        return '';
    }
}

//switch option (show/hide)
function editorial_show_switch_sanitize( $input ) {
    $valid_keys = array(
            'show'  => __( 'Show', 'editorial' ),
            'hide'  => __( 'Hide', 'editorial' )
        );
    if ( array_key_exists( $input, $valid_keys ) ) {
        return $input;
    } else {
        return '';
    }
}

//Archive page layout
function editorial_sanitize_archive_layout($input) {
    $valid_keys = array(
        'classic'   => __( 'Classic Layout', 'editorial' ),
        'columns'   => __( 'Columns Layout', 'editorial' )
        );
    if ( array_key_exists( $input, $valid_keys ) ) {
        return $input;
    } else {
        return '';
    }
}

//Post/Page sidebar layout
function editorial_page_layout_sanitize( $input ) {
    $valid_keys = array(
            'right_sidebar'     => get_template_directory_uri() . '/inc/admin/assets/images/right-sidebar.png',
            'left_sidebar'      => get_template_directory_uri() . '/inc/admin/assets/images/left-sidebar.png',
            'no_sidebar'        => get_template_directory_uri() . '/inc/admin/assets/images/no-sidebar.png',
            'no_sidebar_center' => get_template_directory_uri() . '/inc/admin/assets/images/no-sidebar-center.png'
        );
    if ( array_key_exists( $input, $valid_keys ) ) {
        return $input;
    } else {
        return '';
    }
}

//Footer widget columns
function editorial_footer_widget_sanitize( $input ) {
    $valid_keys = array(
            'column1'   => __( 'One Column', 'editorial' ),
            'column2'   => __( 'Two Columns', 'editorial' ),
            'column3'   => __( 'Three Columns', 'editorial' ),
            'column4'   => __( 'Four Columns', 'editorial' )
        );
    if ( array_key_exists( $input, $valid_keys ) ) {
        return $input;
    } else {
        return '';
    }
}

//Related posts type
function editorial_sanitize_related_type( $input ) {
    $valid_keys = array(
            'category'  => __( 'by Category', 'editorial' ),
            'tag'       => __( 'by Tags', 'editorial' )
        );
    if ( array_key_exists( $input, $valid_keys ) ) {
        return $input;
    } else {
        return '';
    }
}