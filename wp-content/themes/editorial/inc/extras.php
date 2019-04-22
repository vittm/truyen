<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Mystery Themes
 * @subpackage Editorial
 * @since 1.0.0
 */

/*------------------------------------------------------------------------------------------------*/
/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function editorial_body_classes( $classes ) {

    global $post;
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	/**
     * option for web site layout 
     */
    $editorial_website_layout = esc_attr( get_theme_mod( 'site_layout_option', 'fullwidth_layout' ) );
    
    if( !empty( $editorial_website_layout ) ) {
        $classes[] = esc_attr( $editorial_website_layout );
    }

    /**
     * sidebar option for post/page/archive 
     */
    if( is_single() || is_page() ) {
        $sidebar_meta_option = esc_attr( get_post_meta( $post->ID, 'editorial_sidebar_location', true ) );
    }
     
    if( is_home() ) {
        $set_id = esc_attr( get_option( 'page_for_posts' ) );
		$sidebar_meta_option = esc_attr( get_post_meta( $set_id, 'editorial_sidebar_location', true ) );
    }
    
    if( empty( $sidebar_meta_option ) || is_archive() || is_search() ) {
        $sidebar_meta_option = 'default_sidebar';
    }
    $editorial_archive_sidebar = esc_attr( get_theme_mod( 'editorial_archive_sidebar', 'right_sidebar' ) );
    $editorial_post_default_sidebar = esc_attr( get_theme_mod( 'editorial_default_post_sidebar', 'right_sidebar' ) );
    $editorial_page_default_sidebar = esc_attr( get_theme_mod( 'editorial_default_page_sidebar', 'right_sidebar' ) );
    
    if( $sidebar_meta_option == 'default_sidebar' ) {
        if( is_single() ) {
            if( $editorial_post_default_sidebar == 'right_sidebar' ) {
                $classes[] = 'right-sidebar';
            } elseif( $editorial_post_default_sidebar == 'left_sidebar' ) {
                $classes[] = 'left-sidebar';
            } elseif( $editorial_post_default_sidebar == 'no_sidebar' ) {
                $classes[] = 'no-sidebar';
            } elseif( $editorial_post_default_sidebar == 'no_sidebar_center' ) {
                $classes[] = 'no-sidebar-center';
            }
        } elseif( is_page() ) {
            if( $editorial_page_default_sidebar == 'right_sidebar' ) {
                $classes[] = 'right-sidebar';
            } elseif( $editorial_page_default_sidebar == 'left_sidebar' ) {
                $classes[] = 'left-sidebar';
            } elseif( $editorial_page_default_sidebar == 'no_sidebar' ) {
                $classes[] = 'no-sidebar';
            } elseif( $editorial_page_default_sidebar == 'no_sidebar_center' ) {
                $classes[] = 'no-sidebar-center';
            }
        } elseif( $editorial_archive_sidebar == 'right_sidebar' ) {
            $classes[] = 'right-sidebar';
        } elseif( $editorial_archive_sidebar == 'left_sidebar' ) {
            $classes[] = 'left-sidebar';
        } elseif( $editorial_archive_sidebar == 'no_sidebar' ) {
            $classes[] = 'no-sidebar';
        } elseif( $editorial_archive_sidebar == 'no_sidebar_center' ) {
            $classes[] = 'no-sidebar-center';
        }
    } elseif( $sidebar_meta_option == 'right_sidebar' ) {
        $classes[] = 'right-sidebar';
    } elseif( $sidebar_meta_option == 'left_sidebar' ) {
        $classes[] = 'left-sidebar';
    } elseif( $sidebar_meta_option == 'no_sidebar' ) {
        $classes[] = 'no-sidebar';
    } elseif( $sidebar_meta_option == 'no_sidebar_center' ) {
        $classes[] = 'no-sidebar-center';
    }

    if( is_archive() ) {
        $editorial_archive_layout = get_theme_mod( 'editorial_archive_layout', 'classic' );
        if( !empty( $editorial_archive_layout ) ) {
            $classes[] = 'archive-'. esc_attr( $editorial_archive_layout );
        }
    }

	return $classes;
}
add_filter( 'body_class', 'editorial_body_classes' );
