<?php /* Plugin Name: Crazy Call To action Box Plugin URI: https://wordpress.org/plugins/crazy-call-to-action-box/ Description: This plugin will create awesome affiliate Call To action link for your niche site. Version: 1.05 Author: Hafiz Uddin Ahmed Author URI: http://facebook.com/huahmed License:     GPL2 License URI: https://www.gnu.org/licenses/gpl-2.0.html */if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
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
	echo $output;}
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
    );    $content = strtr($content, $array);
    return $content;
}
add_filter('the_content', 'cctab_fix_shortcodes');


require_once('shortcodes.php');


?>