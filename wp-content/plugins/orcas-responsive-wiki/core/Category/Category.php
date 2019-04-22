<?php
/**
 * Created by PhpStorm.
 * User: michael.kirchner
 * Date: 21.06.18
 * Time: 13:26
 */

namespace de\orcas\core;

class Category extends Base
{
    public function __construct()
    {
        add_action("wiki_settings", array($this, 'wpWikiSettings'));
        add_action("responsive_wiki_buddypress_settings", array($this, 'wpWikiSettings'));
        add_action("responsive_wiki_buddypress_settings_save", array($this, 'wpWikiSettingsSave'));
        add_action("wiki_settings_save", array($this, 'wpWikiSettingsSave'));
        add_filter("wiki_before_group", array($this, 'getGroupType'), 10, 2);
        add_filter("wiki_after_group", array($this, 'groupList'), 10, 3);
        add_filter("wiki_add_form_fields", array($this, 'fields'), 10,2);
        add_action('wiki_add_js_extension', array($this, 'addJsExtension'), 10, 2);
        add_action('wiki_list_template', array($this, 'getWikiListTemplate'), 10, 2);
        add_action("wiki_after_save", array($this, 'saveWikiPost'), 10, 2);
        add_action('wiki_before_list', array($this, 'addBreadCrumb'), 20);

        add_action('wiki_init', array($this, 'initCategory'));
        //AJAX
        add_action("wp_ajax_wiki_edit_category", array($this, "updateAjaxCategory"));
        add_action("wp_ajax_nopriv_wiki_edit_category", array($this, "updateAjaxCategory"));
    }

    public function initCategory() {
        wp_enqueue_script('wiki_category_form_init', plugin_dir_url(__FILE__) . '/js/initForm.js', array(), false, true);
        wp_enqueue_style('wiki_category_form_init', plugin_dir_url(__FILE__) . 'css/form.css');
    }

    public function addBreadCrumb($pluginVariables) {
        $type = $this->getGroupType('', $pluginVariables);

        if($type == 'category-box') {
            echo "<div id='category-bread-crumb'>
            <ul><li data-key='__home'>" . __('Top level category', 'orcas-responsive-wiki') . "</li></ul>
        </div>";
        }
    }

    public function saveWikiPost($post, $pluginVariables) {
        $type = isset($pluginVariables['groupId']) ? '-bp-' . $pluginVariables['groupId'] : '';
        update_post_meta($post['wiki-id'], 'wiki-category' . $type, $post['wiki-category']);
    }

    public function getWikiListTemplate($path, $pluginVariables) {
        $type = $this->getGroupType('', $pluginVariables);

        if($type == 'category-box') {
            wp_enqueue_style('wiki_category_box_init', plugin_dir_url(__FILE__) . 'css/categoryBox.css');
            wp_enqueue_script('wiki_category_box_init', plugin_dir_url(__FILE__) . '/js/categoryBox.js', array(), false, true);
            $path = __DIR__ . '/views/wiki-list.phtml';
        }

        return $path;
    }

    public function fields($fields, $pluginVariables) {
        $categories = array_merge(array(array('icon' => '', 'name' => __('None', 'orcas-responsive-wiki'), 'child' => array(), 'value' => '')), $this->getCategories($pluginVariables));

        $fields[] = array(
            'name' => 'category',
            'label' => __('Category:', 'orcas-responsive-wiki'),
            'order' => 10,
            'type' => 'custom',
            'container' => array(
                'class' => 'category-container'
            ),
            'show' => false,
            'location' => 'block',
            'html' => $this->categorySelection($categories),
        );

        return $fields;
    }

    public function addJsExtension($extensions, $data) {
            $extensions[] =  'WikiCategory';

        return $extensions;
    }

    public function insertCategory(&$category, $catList) {
        $catName = array_shift($catList);
        if(count($catList) > 0) {
            foreach($category as &$cat) {
                if($cat['name'] == $catName) {
                    if(!isset($cat['child'])) $cat['child'] = array();
                    $this->insertCategory($cat['child'], $catList);
                    break;
                }
            }
        } else {
            $category[] = array(
                'icon' => '',
                'name' => $catName,
                'child' => array()
            );
        }
    }

    public function updateAjaxCategory() {
        $groupId = function_exists('bp_get_current_group_id') ? bp_get_current_group_id() : false;
        $categories = $this->getCategories(array());

        $this->insertCategory($categories, $_POST['route']);

        if($groupId) {
            $everyOneCanCreateCategories = \WikiUtility::GetGroupMeta('wiki_category_create_everyone', bp_get_current_group_id(), 0);
            if($everyOneCanCreateCategories) \WikiUtility::updateGroupMeta('wiki_category_list', json_encode($categories), $groupId);
        } else {
            update_option('wiki_category_list', json_encode($categories));
        }

        wp_die();
    }

    public function categorySelection($categories) {
        if(class_exists('WikiUtility')) {
            $everyOneCanCreateCategories = \WikiUtility::GetGroupMeta('wiki_category_create_everyone', bp_get_current_group_id(), 0);
        } else {
            $everyOneCanCreateCategories = get_option('wiki_category_create_everyone', 0);
        }

        ob_start();
        include "views/category.phtml";
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function getGroupType($group_type, $pluginVariables) {
        if(isset($pluginVariables['buddyPress']) && $pluginVariables['buddyPress']) {
            $group_type = \WikiUtility::GetGroupMeta('wiki_category_list_sorting', $pluginVariables['groupId'], 'alphabetical');
        } else {
            $group_type = get_option('wiki_category_list_sorting', 'alphabetical');
        }

        return $group_type;
    }

    public function getCategories($pluginVariables) {
        if(isset($pluginVariables['buddyPress']) && $pluginVariables['buddyPress']) {
            return json_decode(\WikiUtility::GetGroupMeta('wiki_category_list', $pluginVariables['groupId'], '[]'), true);
        } else {
            return json_decode(get_option('wiki_category_list', '[]'), true);
        }
    }

    public function createIcon($field) {
        $icon = '';

        if(preg_match('/fa\-/', $field)) {
            $icon = "<i class='category-icon $field'></i>";
        } else if(strlen($icon) > 0) {
            $icon = "<img src='$icon' class='category-icon'/>";
        }

        return $icon;
    }

    public function preIniCategory($rawCategories) {
        $newList = array();
        foreach($rawCategories as $c) {
            $childs = isset($c['child']) && count($c['child']) > 0 ? $this->preIniCategory($c['child']) : array();
            $newList[$c['name']] = array('icon' => $this->createIcon($c['icon']), 'data' => array(), 'child' => $childs);
        }


        return $newList;
    }

    public function countSubChildren(&$catList) {
        $count = 0;
        foreach($catList as $catKey => &$catData) {
                $value = isset($catData['child']) ? $this->countSubChildren($catData['child']) : 0;
            $count = $value + count($catData['data']);
            if(!isset($catData['sum'])) $catData['sum'] = 0;
                $catData['sum'] += $count;
        }

        return $count;
    }

    public function addToSubcategory(&$catList, $cat, $data) {
        $found = false;

        foreach($catList as $catKey => &$catData) {
            if(isset($catData['child']) && $this->addToSubcategory($catData['child'], $cat, $data)) {
                $found = true;
                break;
            } else if($catKey == $cat) {
                $found = true;
                $catData['data'][] = $data;
                break;
            }
        }

        return $found;
    }

    public function groupList($list, $group_type, $pluginVariables) {
        $type = '';
        if(isset($pluginVariables['buddyPress']) && $pluginVariables['buddyPress']) {
            $type = '-bp-' . $pluginVariables['groupId'];
            if($group_type == 'category' || $group_type == 'category-box') {
                $categories = json_decode(\WikiUtility::GetGroupMeta('wiki_category_list', $pluginVariables['groupId'], '[]'), true);
            }
        } else {
            $categories = json_decode(get_option('wiki_category_list', '[]'), true);
        }

        switch($group_type) {
            case 'category-box':
            case 'category':
                $categories[] = array('icon' => '', 'name' => 'uncategorized');
                $newList = $this->preIniCategory($categories);

                /** @var \WP_Post $post */
                foreach($list['none']['data'] as $post) {
                    $cat = get_post_meta($post->ID, 'wiki-category' . $type, true);
                    if($cat) {
                        if(!$this->addToSubcategory($newList, $cat, $post)) {
                            $newList[$cat]['data'][] = $post;
                        }
                    } else {
                        $newList['uncategorized']['data'][] = $post;
                    }
                }

                $this->countSubChildren($newList);

                $list = array();
                foreach($newList as $key => $value) {
                    if(count($value) > 0) {
                        $list[$key] =  $value;
                    }
                }

                break;
        }

        return $list;
    }

    public function wpWikiSettings($args) {
        wp_enqueue_style('wiki_category_fontawesome', plugin_dir_url(__FILE__) . 'css/font-awesome.min.css');
        wp_enqueue_style('wiki_category_settings', plugin_dir_url(__FILE__) . 'css/style.css');
        wp_enqueue_script('wiki_category_nestable', plugin_dir_url(__FILE__) . '/js/jquery.nestable.js', array(), false, true);
        wp_enqueue_script('wiki_category_sortable', plugin_dir_url(__FILE__) . '/js/Sortable.min.js', array(), false, true);
        wp_enqueue_script('wiki_category_init', plugin_dir_url(__FILE__) . '/js/init.js', array(), false, true);

        $categories = array();
        $sorting = 'alphabetical';
        if(isset($args['groupId'])) {
            $everyOneCanCreateCategories = \WikiUtility::GetGroupMeta('wiki_category_create_everyone', bp_get_group_id(), 0);
            $sorting = \WikiUtility::GetGroupMeta('wiki_category_list_sorting', bp_get_group_id(), 'alphabetical');
            $categories = json_decode(\WikiUtility::GetGroupMeta('wiki_category_list', bp_get_group_id(), '[]'), true);
        } else {
            $everyOneCanCreateCategories = get_option('wiki_category_create_everyone', 0);
            $sorting = get_option('wiki_category_list_sorting', 'alphabetical');
            $categories = json_decode(get_option('wiki_category_list', '[]'), true);
        }

        $iconList = json_decode(file_get_contents(__DIR__ . '/src/icon.json'));

        include "views/settings.phtml";
    }

    public function collectCategories($data, $type) {
        $list = array();
        foreach($data as $d) {
            $list[] = array(
                'name' => $d['new'],
                'icon' => $d['icon'] == '{{ICON}}' ? '' : $d['icon'],
                'child' => isset($d['children']) ? $this->collectCategories($d['children'], $type) : array()
            );
            $this->updateCategoryInUse($d['new'], $d['old'], $type);
        }

        return $list;
    }

    public function wpWikiSettingsSave($post) {
        $type = isset($post['groupId']) ? '-bp-' . $post['groupId'] : '';
        $data = json_decode(stripslashes($post['categories']), true);
        $everyOneCanCreateCategories = isset($post['frontend-creation']) ? 1 : 0;
        $list = $this->collectCategories($data, $type);

        if(isset($post['groupId'])) {
            \WikiUtility::updateGroupMeta('wiki_category_create_everyone', $everyOneCanCreateCategories, $post['groupId']);
            \WikiUtility::updateGroupMeta('wiki_category_list_sorting', $post['listing-type'], $post['groupId']);
            \WikiUtility::updateGroupMeta('wiki_category_list', json_encode($list), $post['groupId']);
        } else {
            update_option('wiki_category_create_everyone', $everyOneCanCreateCategories);
            update_option('wiki_category_list_sorting', $post['listing-type']);
            update_option('wiki_category_list', json_encode($list));
        }
    }


    public function updateCategoryInUse($new, $old, $type = '') {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $query = "Update {$prefix}postmeta SET meta_value=%s WHERE meta_key=%s AND meta_value=%s";

        $query = $wpdb->prepare($query, array($new, 'wiki-category' . $type, $old));
        $wpdb->query($query);
    }
}