<?php
/*
 * Plugin Name:  orcas Responsive Wiki
 * Plugin URI:   https://www.orcas.de/wordpress-plugin/wiki/
 * Description:  Buddypress wiki where registered users in the frontend can edit the same document after each other.
 * Version:      1.2.0
 * Author:       orcas
 * Author URI:   https://www.orcas.de/
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  orcas-responsive-wiki
 * Domain Path:  languages/
 */

namespace de\orcas;
defined( 'ABSPATH' ) || exit;

include_once "core/wiki.php";

use de\orcas\core\Wiki;

class OrcasResponsiveWiki
{
    protected $wiki = null;
    protected $pluginDir = __FILE__;
    protected $postType = 'wiki';

    public function __construct() {
        $this->wiki = new Wiki($this->pluginDir, $this->postType);

        add_action('plugins_loaded', array($this, 'loadTextdomain'));
        $this->addActions();
        $this->addShortCodes();
        ob_start();
    }

    public function loadTextdomain() {
        load_plugin_textdomain('orcas-responsive-wiki', false, basename(dirname(__FILE__)) . '/languages');
	    load_plugin_textdomain('orcas-upgrades', false, basename(dirname(__FILE__)) . '/include/languages');
    }

    function wiki_register_elementor_widgets() {
        if (defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')) {
            if (class_exists('Elementor\Plugin')) {
                if (is_callable('Elementor\Plugin', 'instance')) {
                    $elementor = \Elementor\Plugin::instance();
                    if (isset($elementor->widgets_manager)) {
                        if (method_exists($elementor->widgets_manager, 'register_widget_type')) {
                            $template_file = dirname(__FILE__) . '/widgets/elementor-widget.php';
                            if ($template_file && is_readable($template_file)) {
                                require_once $template_file;
                                \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Elementor\orcas_responsive_wiki());
                            }
                        }
                    }
                }
            }
        }
    }

    public function addShortCodes() {
        add_shortcode('view_wiki', array($this->wiki, 'showWiki'));
    }

    public function init() {
        register_post_type($this->postType, array(
            'public' => true,
            'supports' => apply_filters('wiki_post_type_support', array('title','editor')),
            'labels' => array(
                'name' => __('Wiki', 'orcas-responsive-wiki'),
                'singular_name' => __('Wiki', 'orcas-responsive-wiki'),
                'add_new_item' => __('Add new Wiki Page', 'orcas-responsive-wiki'),
                'edit_item' => __('Edit Wiki Page', 'orcas-responsive-wiki'),
                'new_item' => __('New Wiki', 'orcas-responsive-wiki'),
                'view_item' => __('View Wiki', 'orcas-responsive-wiki'),
                'view_items' => __('View Wikis', 'orcas-responsive-wiki'),
                'search_items' => __('Search Wikis', 'orcas-responsive-wiki'),
                'not_found' => __('No Wiki Pages found', 'orcas-responsive-wiki'),
                'not_found_in_trash'  => __('No Wiki Pages found in Trash', 'orcas-responsive-wiki')
            ),
        ));
    }

    public function adminMenu() {
        $this->wiki->admin_page();
    }

    public function executeWiki() {
        do_shortcode("[view_wiki]");
    }

    public function shutdown() {
        try {
            $content = ob_get_contents();
            //ob_end_clean();
            echo $content;
            echo $content;
        } catch(\Exception $e) {}
    }

    public function addActions() {
        add_action('shutdown', array($this, 'shutdown'));
        //add_action('wp_die_handler', array($this, 'shutdown'));
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'adminMenu'));

        add_action("wp_ajax_wiki_page_edit", array($this, 'ajaxWikiPage'));
        add_action("wp_ajax_nopriv_wiki_page_edit", array($this, 'ajaxWikiPage'));

        add_action("wp_ajax_wiki_page", array($this, 'ajaxWikiPageRendered'));
        add_action("wp_ajax_nopriv_wiki_page", array($this, 'ajaxWikiPageRendered'));

        add_action("wiki_display", array($this, 'executeWiki'));

        add_action('elementor/init', array($this, 'wiki_register_elementor_widgets'));
    }

    public function ajaxWikiPageRendered() {
        if(isset($_POST['security']) && check_ajax_referer('responsive-wiki', 'security') && preg_match('/[0-9]+/', $_POST['id'])) {
            echo json_encode($this->wiki->get(intval($_POST['id']), true));
        }
        wp_die();
    }

    public function ajaxWikiPage() {
        if(isset($_POST['security']) && check_ajax_referer('responsive-wiki', 'security') && preg_match('/[0-9]+/', $_POST['id'])) {
            echo json_encode($this->wiki->get(intval($_POST['id']), false));
        }
        wp_die();
    }
}

include_once "include/autoload.php";

new OrcasResponsiveWiki();