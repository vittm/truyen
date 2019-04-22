<?php
/**
 * Created by PhpStorm.
 * User: michael.kirchner
 * Date: 02.07.18
 * Time: 11:36
 */

namespace de\orcas\core;


class ShortCode
{
    /** @var Wiki */
    protected $wiki = null;
    protected $pluginDir = null;
    public function __construct($wiki,$pluginDir)
    {
        $this->wiki = $wiki;
        $this->pluginDir = $pluginDir;

        add_shortcode('wiki_add_create_button', array($this, 'newWikiButton'));
        add_shortcode('wiki_add_back_button', array($this, 'backButton'));
        add_shortcode('wiki_add_form_back_button', array($this, 'formBackButton'));

        add_shortcode('wiki_add_create_link', array($this, 'newWikiLink'));
        add_shortcode('wiki_add_back_link', array($this, 'backLink'));
        add_shortcode('wiki_add_form_back_link', array($this, 'formBackLink'));

        add_action("wp_ajax_wiki_load_form", array($this, "lazyLoadForm"));
        add_action("wp_ajax_nopriv_wiki_load_form", array($this, "lazyLoadForm"));
    }

    public function lazyLoadForm() {
        do_action('wiki_init', $this->wiki->getPluginVars());

        $allowCreate = $this->wiki->hasPermission('create-wiki', false);
        $list = $this->wiki->loadList(true);

        $pieceList = array();
        foreach ($list as $categoryList) {
            foreach ($categoryList['data'] as $p) {
                $pieceList[str_replace(' ', '-', strtolower($p->post_title))] = array(
                    'key' => $p->post_title,
                    'name' => $p->post_name,
                    'id' => $p->ID,
                    'isHtml' => true
                );
            }
        }

        $pages = get_pages();

        $pageList = array();

        foreach($pages as $p) {
            $pageList[str_replace(' ', '-', strtolower($p->post_title))] = array(
                'key' => $p->post_title,
                'name' => $p->post_name,
                'id' => $p->ID,
                'isHtml' => true
            );
        }

        $blogs = get_posts();
        $blogList = array();

        foreach($blogs as $p) {
            $blogList[str_replace(' ', '-', strtolower($p->post_title))] = array(
                'key' => $p->post_title,
                'name' => $p->post_name,
                'id' => $p->ID,
                'isHtml' => true
            );
        }

        $pieceList = array(
            'wiki' => array(
                'src' => $pieceList,
                'separator' => ':'
            ),
            'page' => array(
                'src' => $pageList,
                'separator' => ':'
            ),
            'blog' => array(
                'src' => $blogList,
                'separator' => ':'
            )
        );

        $pieceList = json_encode(apply_filters('wiki_auto_complete', $pieceList, $this->wiki->getPluginVars()));

        $preFill = array(
            'id' => '',
            'title' => '',
            'name' => '',
            'content' => ''
        );

        $peFileDataAttributes = '';

        if (isset($_GET['wiki-page'])) {
            $post = get_posts(array('name' => sanitize_text_field($_GET['wiki-page']), 'post_type' => $this->wiki->getPostType()));

            if (count($post) > 0) {
                $post = $post[0];
                $preFill = $this->wiki->get($post->ID);
            }

            $peFileDataAttributes = "data-wiki-page='$preFill[name]' data-page-name='$preFill[title]' data-id='$preFill[id]'";
        }

        //icons in detail Page
        $iconList = apply_filters('wiki_add_detail_page_icon', array(), array(
            'id' => $preFill['id'],
            'permission' => array(
                'create' => $allowCreate
            )
        ), $this->wiki->getPluginVars());

        $wikiJsExtensions = apply_filters('wiki_add_js_extension', array(), array(
            'id' => $preFill['id'],
            'permission' => array(
                'create' => $allowCreate
            )
        ), $this->wiki->getPluginVars());

        $wikiFieldList = $this->wiki->getFields();

        $extensionString = join(',',$wikiJsExtensions);

        ob_start();
        echo "<div id='form-create-modal' class='wiki-modal'><div id='responsive-wiki-context' class='responsive-wiki responsive-wiki-data-container' data-extensions='$extensionString' data-piece='$pieceList'>";
        include __DIR__ . "/../templates/new-wiki-form.php";
        echo "</div></div>";
        $content = ob_get_contents();
        ob_end_clean();

        echo $content;

        wp_die();
    }

    public function newWikiButton($tag = 'button', $class='btn') {
        if($this->wiki->hasPermission('create-wiki', false)) {
            if (isset($_POST) && isset($_POST['new-wiki-submit'])) {
                $this->wiki->save($_POST);
                wp_redirect($_SERVER['REQUEST_URI']);
                exit();
            }

            $scripts = WP_Scripts();
            if(strpos(join(',', $scripts->done), 'bootstrap') === false) {
                wp_enqueue_script('orw_bootstrap', plugin_dir_url($this->pluginDir) . 'js/summernote/bootstrap.js', array(), '3', true);
            }
            wp_enqueue_script('orw_summernote', plugin_dir_url($this->pluginDir) . 'js/summernote/summernote.js', array(), '3', true);

            wp_enqueue_style('orw_bootstrap_css', plugin_dir_url($this->pluginDir) . 'js/summernote/bootstrap.css');
            wp_enqueue_style('orw_codemirror_css', plugin_dir_url($this->pluginDir) . 'js/summernote/codemirror.css');
            wp_enqueue_style('orw_summernote_css', plugin_dir_url($this->pluginDir) . 'js/summernote/summernote.css');
            wp_enqueue_style( 'font_awesome', plugin_dir_url($this->pluginDir) . 'css/font-awesome.css');

            wp_enqueue_script('orc_js_calendar', plugin_dir_url($this->pluginDir) . '/js/script.js', array(), false, true);
            wp_enqueue_style('orc_css_loader', plugin_dir_url($this->pluginDir) . '/css/loader.css');
            wp_enqueue_style('orc_css_calendar', plugin_dir_url($this->pluginDir) . '/css/style.css');


            wp_enqueue_script('wiki_short_code_new_wiki', plugin_dir_url($this->pluginDir) . 'js/btn/externalNewWikiBtn.js', array(), '3', true);
            wp_enqueue_style('wiki_short_code_new_wiki', plugin_dir_url($this->pluginDir) . 'css/btn/externalNewWikiBtn.css');

            do_action('wiki_init', $this->wiki->getPluginVars());

            if(strlen($tag) == 0) $tag = 'button';
            if(strlen($class) == 0) $class = 'btn';

            $translation = __('Add wiki', 'orcas-responsive-wiki');

            echo "<$tag title='$translation' class='$class external-new-wiki-action'>$translation</$tag>";
        }
    }

    public function newWikiLink() {
        $this->newWikiButton('a', '');
    }

    public function backButton($tag, $class='btn') {
        wp_enqueue_script('wiki_short_code_back', plugin_dir_url($this->pluginDir) . 'js/btn/externalBackBtn.js', array(), '3', true);
        wp_enqueue_style('wiki_short_code_back', plugin_dir_url($this->pluginDir) . 'css/btn/externalBackBtn.css');

        if(strlen($tag) == 0) $tag = 'button';
        if(strlen($class) == 0) $class = 'btn';

        $translation = __('Back', 'orcas-responsive-wiki');

        $display = isset($_GET['wiki-page']) ? 'inline-block' : 'none';

        echo "<$tag title='$translation' class='$class external-back-action' style='display:$display;'>$translation</$tag>";
    }

    public function backLink() {
        $this->backButton('a', '');
    }

    public function formBackButton($tag, $class='btn') {
        wp_enqueue_script('wiki_short_code_form_back', plugin_dir_url($this->pluginDir) . 'js/btn/externalFormBackBtn.js', array(), '3', true);
        wp_enqueue_style('wiki_short_code_form_back', plugin_dir_url($this->pluginDir) . 'css/btn/externalFormBackBtn.css');

        if(strlen($tag) == 0) $tag = 'button';
        if(strlen($class) == 0) $class = 'btn';

        $translation = __('Back', 'orcas-responsive-wiki');

        echo "<$tag title='$translation' class='$class external-form-back-action' style='display:none;'>$translation</$tag>";
    }

    public function formBackLink() {
        $this->formBackButton('a', '');
    }
}