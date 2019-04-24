<?php 
function cctab_required_file() {
    wp_enqueue_style( 'cctab-css', plugins_url( '/css/style.css', __FILE__ ));
	wp_enqueue_script( 'cctab-lazyload-js', plugins_url( '/js/qazy.min.js', __FILE__ ), '', '1.0', true );
	}
add_action('init','cctab_required_file');
/*-----------------------------------------------------------------------------------*/
# Get Absolute path for loading image to use in js file
/*-----------------------------------------------------------------------------------*/
add_action('wp_head','cctab_loaderimageUrl');
function cctab_loaderimageUrl() {
	$output="<script> 	var cctab_loaderimageUrl = '".plugins_url('/css/loader.gif', __FILE__)."';</script>";
	echo $output;
/*-----------------------------------------------------------------------------------*/
# Fix Shortcodes
/*-----------------------------------------------------------------------------------*/
function cctab_fix_shortcodes($content){   
    $array = array (
        '[raw]' => '', 
        '[/raw]' => '', 
        '<p>[raw]' => '', 
        '[/raw]</p>' => '', 
        '[/raw]<br />' => '', 
        '<p>[' => '[', 
        ']</p>' => ']', 
        ']<br />' => ']'
    );
    return $content;
}
add_filter('the_content', 'cctab_fix_shortcodes');


require_once('shortcodes.php');


?>