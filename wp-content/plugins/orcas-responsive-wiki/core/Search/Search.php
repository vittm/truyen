<?php
/**
 * Created by PhpStorm.
 * User: michael.kirchner
 * Date: 02.07.18
 * Time: 16:12
 */

namespace de\orcas\core;

class Search extends Base
{
    protected $fieldAlias = array(
        'title' => 'post_title',
        'content' => 'post_content'
    );
    public function __construct()
    {
        add_filter('pre_get_posts', array($this, 'addPostTypeToSearch'));
        add_action('wiki_before_list', array($this, 'addSearchFields'));
        add_action('wiki_after_list', array($this, 'addSearchResult'));
        add_filter('wiki_before_group', array($this, 'searchType'), 20);

        //AJAX
        add_action("wp_ajax_wiki_search", array($this, "search"));
        add_action("wp_ajax_nopriv_wiki_search", array($this, "search"));
    }

    public function addPostTypeToSearch($query) {
        if ( is_search() && $query->is_main_query() && $query->get( 's' ) ){

            // Include our product CPT.
            $query->set( 'post_type', array(
                'post',
                'page',
                'wiki'
            ) );
        }
    }

    public function addSearchFields() {
        wp_enqueue_script('wiki_search_init', plugin_dir_url(__FILE__) . '/js/search.js', array(), false, true);
        wp_enqueue_style('wiki_search_init', plugin_dir_url(__FILE__) . '/css/style.css');

        $placeholder = __('Search ...', 'orcas-responsive-wiki');
        include "views/searchInput.phtml";
    }

    public function addSearchResult() {
        echo "<div id='wiki-search-result-box'><div id='wiki-search-result'></div><figure class='loader'>
                    <div></div><div></div>
                    <div></div><div></div>
                    <div></div><div></div>
                    <div></div><div></div>
                </figure></div>";
    }

    public function searchType($groupType) {
        if(isset($_POST['search']) && strlen($_POST['search']) > 3) {
            $groupType = 'none';
        }

        return $groupType;
    }

    public function validate($item, $searchList, $searchValue) {
        foreach($searchList as $search) {
            $key = $this->fieldAlias[$search];
            $pos = strpos(strtolower($item->$key), strtolower($searchValue));
            if($pos != false) {
                return true;
            }
        }

        return false;
    }

    public function search() {
        if(isset($_POST['search']) && strlen($_POST['search']) > 3) {
            $search = $_POST['search'];

            $finalResult = array();
            $result = Wiki::$self->loadList();

            $html = '';

            ob_start();
            if(count($result['none']['data']) > 0) {
                echo '<h3 class="search-result-header">' . __('Results', 'orcas-responsive-wiki') . '</h3>';
                echo '<ul>';
                foreach($result['none']['data'] as $item) {
                    if($this->validate($item, $_POST['filter'], $search)) {
                        $finalResult[] = $item;
                        include "views/searchResult.phtml";
                    }
                }
                echo '</ul>';
            } else {
                echo "<div class='search-no-result'>" . __('No result', 'orcas-responsive-wiki') . "</div>";
            }
            $content = ob_get_contents();
            ob_end_clean();

            echo $content;
        }

        wp_die();
    }
}