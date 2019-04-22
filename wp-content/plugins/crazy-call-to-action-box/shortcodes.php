<?php
/*
Plugin Name Call To action Box
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define ( 'CCTAB_JS_PATH' , plugins_url( '/js/cctab.js', __FILE__ ));

add_action('admin_head', 'add_ctab_mce_button');
function add_ctab_mce_button() {
	if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
		return;
	}
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'cctab_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'cctab_register_mce_button' );
	}
}

// Declare script for new button
function cctab_add_tinymce_plugin( $plugin_array ) {
	$plugin_array['cctab_mce_button'] = CCTAB_JS_PATH;
	return $plugin_array;
}

// Register new button in the editor
function cctab_register_mce_button( $buttons ) {
	array_push( $buttons, 'cctab_mce_button' );
	return $buttons;
}

function cctab_shortcodes_mce_css() {
	wp_enqueue_style('ctab_shortcodes_mce_admin-css',  plugins_url('/css/admin.css', __FILE__  ));
}
add_action( 'admin_enqueue_scripts', 'cctab_shortcodes_mce_css' );

/*Dont edite anything avobe this line/###############################
######################################################################
 * ################ All Short Codes Start From Here     ############## */

 
## Affiliate Button Link -------------------------------------------------- #
function ccta_affiliate_text_link( $atts, $content = null ) {
	$link = '';
    $color= '';
    if (is_array($atts)) {extract($atts);}
    $html = '<div class="col-md-12 text-center cta-btn-cont ">
	<a rel="nofollow" class="cta-link ctab-text-'.$color.'" href="'.$link.'" >&gt;&gt;&gt; '  . do_shortcode($content) . ' &lt;&lt;&lt;</a>';	$html.= '</div>';
	return $html;}
add_shortcode("cta_link", "ccta_affiliate_text_link"); 
 
 
 
 
## Affiliate Button Link -------------------------------------------------- #
function ccta_affiliate_button_link( $atts, $content = null ) {
	$link = '';
        $color= '';
        $size = '';

        if (is_array($atts)) {
        extract($atts);
    }

    $html = '<div class="col-md-12 text-center cta-btn-cont">
	
	<span class=" ctabtn ctabtn-'.$color.'  ctabtn-'.$size.' cta-link" onclick="window.open(\''.$link.'\');"  >' . do_shortcode($content) . '</span>
	
	';
	$html.= '</div>';
	return $html;
    
}
add_shortcode("cta_btn", "ccta_affiliate_button_link");  



## Affiliate Image BOX call to action Button Link type 01 -------------------------------------------------- #
function ccta_affiliate_image_box_button_link( $atts, $content = null ) {
    $imagesrc = '';
    $alt ='';
    $align = '';
    $boxsize = '';
    $link = '';
    $color= '';
    $btnsize = '';    
    $btntext ='';
    

    if (is_array($atts)) {
        extract($atts);
    }

    $html = '
<div class="cta-img-con text-center col-md-'.$boxsize.'  '.$align.' " >
    <div class="ctabthumbnail">
        <img class="ctab-image" src="'.$imagesrc.'" alt="'.$alt.'" onclick="window.open(\''.$link.'\');" data-qazy="true" >
    </div>
    <span class=" ctabtn ctabtn-'.$color.'  ctabtn-'.$btnsize.' cta-link " onclick="window.open(\''.$link.'\');"  >'.$btntext.'</span>

</div>';
return $html;

} 
add_shortcode("cta_image_box1", "ccta_affiliate_image_box_button_link");  




## Affiliate Image BOX with call to action Button Link type 02-------------------------------------------------- #
function ccta_image_box_button_link2( $atts, $content = null ) {
    $imagesrc = '';
    $alt ='';
    $link = '';
    $color= '';
    $btnsize = '';    
    $btntext ='';
    $headline ='';
	$titlecolor = '';
    
    
 if (is_array($atts)) {
        extract($atts);
    }

    $html = '
	<div class="cta-img-con-2 text-center col-md-12 " >
		<div class="col-md-5 ctab ctabthumbnail">
			<img class="ctab-image " src="'.$imagesrc.'" alt="'.$alt.'" onclick="window.open(\''.$link.'\');" data-qazy="true" >
		</div>		
		<div class=" col-md-7">
			<h4 class="ctab-text-'.$titlecolor.'">'.$headline.'</h4>
			<p class="BoxTextContent ">' . do_shortcode($content) . '</p>
			<span class="cta-link ctabtn ctabtn-'.$color.'  ctabtn-'.$btnsize.'" onclick="window.open(\''.$link.'\');"  >'.$btntext.'</span>
		</div>

</div>';
return $html;

}
add_shortcode("cta_image_box2", "ccta_image_box_button_link2");  


## Affiliate Image BOX with call to action Button Link type 03-------------------------------------------------- #
function cctab_image_ext_width( $atts, $content = null ) {
    $imagesrc = '';
    $alt ='';
    $link = '';
    
    
 if (is_array($atts)) {  extract($atts);   }

    $html = '
	<div class="extended-width row" >
			<img class="extended-width" src="'.$imagesrc.'" alt="'.$alt.'" onclick="window.open(\''.$link.'\');" data-qazy="true" >
	</div>';
	return $html;

}
add_shortcode("image_ext_width", "cctab_image_ext_width");  



