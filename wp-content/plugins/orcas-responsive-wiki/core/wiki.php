<?php
/**
 * Created by PhpStorm.
 * User: michael.kirchner
 * Date: 20.03.18
 * Time: 11:45
 */

namespace de\orcas\core;

defined( 'ABSPATH' ) || exit;

include_once "Base.php";
include_once "ShortCode.php";

class Wiki extends Base
{
    /** @var Wiki  */
    public static $self = null;
    protected $pluginDir = null;
    protected $buddypress = null;
    protected $postType = null;
    protected $fields = array();
    protected $additionalFields = array();
    protected $pluginVars = array();

    public function __construct($pluginDir, $postType)
    {
        add_action('init', array($this, 'init'));
        $this->pluginDir = $pluginDir;
        $this->postType = $postType;

        $this->additionalFields = array();
        new ShortCode($this, $this->pluginDir);
        static::$self = $this;
    }

    public function getFields() {
        return $this->fields;
    }
    
    public function getPostType() {
        return $this->postType;
    }

    public function getPluginVars() {
        return $this->pluginDir;
    }

    public function renderCategories($list, $class = 'category-block', $open = true, $topLevel = true) {
        ob_start();
        ?>
        <?php foreach($list as $categoryName => $fileList) {
            $rand = rand(0, 100000);
            $classIcon = $topLevel ? 'category-icon' : '';
            ?>
            <?php if(count($fileList['data']) == 0 && !$this->renderCheckHasChildren($fileList)) continue; ?>
            <div class="list-categories-item <?php echo $class; ?>">
                <?php echo isset($fileList['icon']) && strlen(trim($fileList['icon'])) > 0 ? $fileList['icon'] : "<i class='fa fa-folder-open-o  $classIcon'></i>"; ?>
                <?php echo $categoryName != 'none' ? "<div class='category-header'>" . esc_html($categoryName) . "</div>": ''?>
                <label class="collapse-overlay" for="collapse-chk-<?php echo $rand; ?>"></label>
                <input type="checkbox" id="collapse-chk-<?php echo $rand; ?>" style="display: none" <?php echo $open ? 'checked' : '' ?>/>
                <ul class="wiki-list-collection">
                    <?php
                    $subCategory = isset($fileList['child']) ? $this->renderCategories($fileList['child'], 'sub-category-block', false, false) : '';
                    if(strlen($subCategory) > 0) {
                        echo "<li>$subCategory</li>";
                    }
                    foreach($fileList['data'] as $item) { ?>
                        <li class="wiki-entries" data-wiki-page="<?php echo esc_attr($item->post_name); ?>" data-page-name="<?php echo esc_attr($item->post_title); ?>" data-id="<?php echo esc_attr($item->ID);?>"><i class='fa fa-file-o'></i><?php echo esc_html($item->post_title); ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function init() {
        $this->pluginVars = apply_filters('wiki_plugin_vars_init', $this->pluginVars);

        $this->setAdditionalFields();

        $defaultFields = array(
            array(
                'name' => 'title',
                'label' => __('Title:', 'orcas-responsive-wiki'),
                'required' => true,
                'order' => 5,
                'show' => false,
            ),
            array(
                'name' => 'content',
                'label' => __('Content:', 'orcas-responsive-wiki'),
                'order' => 15,
                'class' => 'rich-text',
                'type' => 'textarea',
                'show' => true,
                'location' => 'block',
                'description' => __('Use the keyword "<strong>wiki:</strong>" to create a link to an other existing Wiki page. <br />Notice: You need a space before the keyword.', 'orcas-responsive-wiki')
            ),
        );

        $this->fields = $this->order(apply_filters('wiki_change_form_fields', array_merge($defaultFields, $this->additionalFields), $this->pluginVars));
    }

    public function admin_page()
    {
	    // orcas menu is added by /include/Upgrade/Upgrade.php:140
	    \add_submenu_page(
		    'orcas',
		    __('Wiki', 'orcas-responsive-wiki'),
		    __('Wiki', 'orcas-responsive-wiki'),
		    'manage_options',
		    'responsive-wiki-settings',
		    array($this, 'responsive_wiki')
	    );
    }

    public function saveSettings()
    {
        if(isset($_POST['_wpnonce']) && !wp_verify_nonce($_POST['_wpnonce'], 'admin-settings')) {
            wp_die();
        }

        if (isset($_POST['settings-submit'])) {
            if(is_numeric($_POST['create-wiki-permission'])) {
                update_option('responsive-wiki-permission-create-wiki', intval($_POST['create-wiki-permission']));
            }
            if(isset($_POST['create-wiki-permission-roles'])) {
                update_option('responsive-wiki-permission-role-create-wiki', sanitize_text_field(json_encode($_POST['create-wiki-permission-roles'])));
            } else {
                delete_option('responsive-wiki-permission-role-create-wiki');
            }

            if(isset($_POST['create-wiki-permission-users'])) {
                update_option('responsive-wiki-permission-user-create-wiki', sanitize_text_field(json_encode($_POST['create-wiki-permission-users'])));
            } else {
                delete_option('responsive-wiki-permission-user-create-wiki');
            }

            do_action('wiki_settings_save', $_POST, $this->pluginVars);
        }
    }

    public function responsive_wiki()
    {
        $this->saveSettings();
        global $title;
        ?>
        <h2><?php echo $title; ?></h2>
        <?php
        $createPermission = get_option('responsive-wiki-permission-create-wiki');
        $users = get_users();
        $roles = get_editable_roles();

        wp_enqueue_style('orw_settings', plugin_dir_url($this->pluginDir) . 'css/settings/settings.css');

        $permissionRole = get_option('responsive-wiki-permission-role-create-wiki');
        $permissionUser = get_option('responsive-wiki-permission-user-create-wiki');

        $createPermission = $createPermission ? $createPermission : 2;
        $permissionRole = $permissionRole ? json_decode($permissionRole) : array();
        $permissionUser = $permissionUser ? json_decode($permissionUser) : array();
        ob_start();
        include __DIR__ . "/../templates/settings/settings.phtml";
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;
    }

    public static function wiki_cmp($a, $b)
    {
        if ($a['order'] == $b['order']) {
            return 0;
        }

        return ($a['order'] < $b['order']) ? -1 : 1;
    }

    public function setAdditionalFields()
    {
        $tmpList = apply_filters('wiki_add_form_fields', array(), $this->pluginVars);
        foreach ($tmpList as $field) {
            if (isset($field['name']) && isset($field['label'])) {
                $this->additionalFields[] = $field;
            }
        }
    }


    public function order($array)
    {
        if (count($array) > 0) {
            usort($array, array("de\orcas\core\Wiki", 'wiki_cmp'));
        }

        return $array;
    }

    public function delete($post) {
        if(isset($post['_wpnonce']) && !wp_verify_nonce($post['_wpnonce'], 'delete-wiki')) {
            wp_die();
        }

        $post = apply_filters('wiki_before_delete', $post, $this->pluginVars);

        if(isset($post['wiki-delete-id']) && preg_match('/[0-9]+/', $post['wiki-delete-id'])) {
            wp_delete_post($post['wiki-delete-id'], true);
        }
    }

    public function showWiki($args)
    {
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

        do_action('wiki_init', $this->pluginVars);

        if (isset($_POST) && isset($_POST['new-wiki-submit'])) {
            $this->save($_POST);
            wp_redirect($_SERVER['REQUEST_URI']);
            exit();
        } else if(isset($_POST) && isset($_POST['delete-wiki-submit'])) {
            $this->delete($_POST);
            wp_redirect($_SERVER['REQUEST_URI']);
            exit();
        }

        $allowCreate = $this->hasPermission('create-wiki', false, 'create-wiki');
        $pieceListRaw = $this->loadList(true);

        $pieceList = array();
        foreach ($pieceListRaw as $categoryList) {
            foreach ($categoryList['data'] as $p) {
                $pieceList[str_replace(' ', '-', strtolower($p->post_title))] = array(
                        'key' => $p->post_title,
                    'name' => $p->post_name,
                    'id' => $p->ID,
                    'isHtml' => true
                );
            }
        }

        $list = $this->loadList();

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

        $pieceList = json_encode(apply_filters('wiki_auto_complete', $pieceList, $this->pluginVars));

        $preFill = array(
            'id' => '',
            'title' => '',
            'name' => '',
            'content' => ''
        );

        $peFileDataAttributes = '';
        $defaultWikiPage = apply_filters('wiki_default_page_slug', get_option('wiki_default_page'));
        if(preg_match("/\/" . str_replace('/', '\/', $defaultWikiPage) . "\/([a-zA-Z0-9\-]+)/", $_SERVER['REQUEST_URI'], $resultPostName)) {
            $postName = $resultPostName[1];
        }

        $postName = isset($_GET['wiki-page']) ? $_GET['wiki-page'] : $postName;

        if ($postName) {
            $post = get_posts(array('name' => sanitize_text_field($postName), 'post_type' => $this->postType));

            if (count($post) > 0) {
                $post = $post[0];
                $preFill = $this->get($post->ID);
            }

            $peFileDataAttributes = "data-wiki-page='$preFill[name]' data-page-name='$preFill[title]' data-id='$preFill[id]'";
        }

        //icons in detail Page
        $iconList = apply_filters('wiki_add_detail_page_icon', array(), array(
                'id' => $preFill['id'],
                'permission' => array(
                        'create' => $allowCreate
                )
        ), $this->pluginVars);

        $wikiJsExtensions = apply_filters('wiki_add_js_extension', array(), array(
            'id' => $preFill['id'],
            'permission' => array(
                'create' => $allowCreate
            )
        ), $this->pluginVars);

        ob_start();
        $listPath = apply_filters('wiki_list_template', __DIR__ . '/../templates/wiki-list.php', $this->pluginVars);
        include $listPath;
        $list = ob_get_contents();
        ob_end_clean();

        $nonce = wp_create_nonce('responsive-wiki');

        $wikiFieldList = $this->fields;

        ob_start();
        echo "<div id='responsive-wiki-context' class='responsive-wiki responsive-wiki-data-container' data-piece='" . $pieceList . "' data-wiki-uri='$defaultWikiPage'>";
        include __DIR__ . '/../templates/wiki.phtml';
        echo "</div>";
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * @param $key
     * @param $post \WP_Post
     * @param string $type
     */
    public function hasPermission($key, $post, $type = 'public')
    {
        $permission = true;
        if (!current_user_can('administrator')) {
            switch ($type) {
                case 'protected':
                    $permission = $post->post_author == get_current_user_id();
                    break;
                case 'create-wiki':
                    $permission = false;
                    $createPermission = get_option('responsive-wiki-permission-create-wiki');
                    if($createPermission > 0) {
                        if($createPermission == 1) {
                            $permission = true;
                        } else if($createPermission == 3) {
                            $permissionRole = json_decode(get_option('responsive-wiki-permission-role-create-wiki', '[]'), true);
                            $permissionRole = $permissionRole ? $permissionRole : array();
                            $user = wp_get_current_user();
                            $userRoles = (array) $user->roles;
                            foreach($permissionRole as $role) {
                                if ( in_array( $role, $userRoles ) ) {
                                    $permission = true;
                                    break;
                                }
                            }

                            if(!$permission) {
                                $permissionUser = json_decode(get_option('responsive-wiki-permission-user-create-wiki', '[]'), true);
                                $permissionUser = $permissionUser ? $permissionUser : array();
                                if(in_array( $user->ID, $permissionUser )) {
                                    $permission = true;
                                }
                            }

                        }
                    }
                    break;
            }
        }

        return apply_filters('wiki_has_permission', $permission, $key, $post, $type, $this->pluginVars);
    }

    public function renderDetailView($content)
    {
        $content = apply_filters('wiki_before_render_detail_view', $content, $this->pluginVars);
        //$content = str_replace(array("\n\r", "\n"), '<br />', $content);

        //match wiki pages
        preg_match_all('/\[\s*wiki\s*:(.*?)\]/', $content, $matches);
        $posts = get_posts($this->createFilterForAllWikiPages());
        for ($ii = 0; $ii < count($matches[1]); $ii++) {
            /** @var \WP_Post $p */
            foreach ($posts as $p) {
                if ($matches[1][$ii] == $p->post_title) {
                    $replace = '<a data-wiki-page="' . $p->post_name . '" data-page-name="' . $p->post_title . '" data-id="' . $p->ID . '" class="wiki-link" href="?wiki-page=' . $p->post_name . '">' . $p->post_title . '</a>';
                    $content = str_replace($matches[0][$ii], $replace, $content);
                    break;
                }
            }
        }


        return apply_filters('wiki_after_render_detail_view', $content, $this->pluginVars);
    }

    /**
     * encode
     * Use this funktion if the contant has html values for the html DOM
     * @param string $data
     * @return string
     */
    public function esc($data) {
        $data = htmlspecialchars($data);
        return addslashes($data);
    }

    /**
     * decode
     * Use this funktion if the contant has html values for the html DOM
     *
     * @param string $data
     * @return string
     */
    public function deesc($data) {
        $data = stripslashes($data);
        return htmlspecialchars_decode($data);
    }


    public function save($post)
    {
        if (isset($post)) {
            $args = array(
                'post_title' => $this->esc($post['wiki-title']),
                'post_content' => $this->esc($post['wiki-content']),
                'post_status' => 'publish',
                'post_type' => $this->postType,
            );

            if (isset($post['wiki-id']) && strlen(trim($post['wiki-id'])) > 0) {
                if(preg_match('/[0-9]+/', $post['wiki-id'])) {
                    $args['ID'] = intval($post['wiki-id']);
                } else {
                    return;
                }
            }

            if(isset($post['_wpnonce']) && !wp_verify_nonce($post['_wpnonce'], 'new-wiki')) {
                wp_die();
            }

            $args = apply_filters('wiki_before_save', $args, $this->pluginVars);

            if(isset($post['wiki-id']) && strlen(trim($post['wiki-id'])) > 0) {
                $id = wp_update_post($args, true);
            } else {
                $id = wp_insert_post($args, true);
            }

            $post['wiki-id'] = $id;

            update_post_meta($id, 'wiki-key', $this->buildIdentifier());

            foreach ($this->additionalFields as $field) {
                $key = 'wiki-' . $field['name'];
                if (isset($post[$key])) {
                    if (in_array($post[$key]['type'], array('radio', 'checkbox'))) {
                        update_post_meta($id, $key, isset($post[$key]) ? 1 : 0);
                    } else {
                        update_post_meta($id, $key, htmlspecialchars($post[$key]));
                    }
                }
            }
            do_action('wiki_after_save', $post, $this->pluginVars);

            return $id;
        }

        return false;
    }

    public function buildIdentifier()
    {
        return apply_filters('wiki_identifier', 'wordpress', $this->pluginVars);
    }

    public function createFilterForAllWikiPages()
    {
        return array(
            'post_type' => $this->postType,
            'numberposts' => 1000000,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'wiki-key',
                    'value' => $this->buildIdentifier(),
                ),
            )
        );
    }

    public function loadList($none = false)
    {
        $filter = $this->createFilterForAllWikiPages();

        $filter = apply_filters('wiki_before_load_list', $filter, $this->pluginVars);

        $postList = $this->group(get_posts($filter), 'alphabetical', $none);

        return apply_filters('wiki_after_load_list', $postList, $this->pluginVars);
    }

    public function group($postList, $groupType, $none = false)
    {
        $group_type = $none ? $groupType : apply_filters('wiki_before_group', $groupType, $this->pluginVars);

        $result = array();
        switch ($group_type) {
            case 'alphabetical':
                /** @var \WP_Post $p */
                foreach ($postList as $p) {
                    $firstChar = strtoupper($this->specialCharacters(mb_substr($p->post_title, 0, 1)));
                    if (!isset($result[$firstChar])) $result[$firstChar] = array('data' => array());
                    $result[$firstChar]['data'][] = $p;
                }
                break;
            default:
                $result['none'] = array('data' => $postList);
        }

        return apply_filters('wiki_after_group', $result, $group_type, $this->pluginVars);
    }

    public function specialCharacters($char)
    {
        switch ($char) {
            case 'ö':
            case 'Ö':
                return 'o';
                break;
            case 'ä':
            case 'Ä':
                return 'a';
                break;
            case 'ü':
            case 'Ü':
                return 'u';
                break;
            case 'ß':
                return 's';
                break;
            default:
                return $char;
        }
    }

    public function get($id, $render = true)
    {
        $filter = apply_filters('wiki_before_load', $id, $this->pluginVars);

        $post = get_post($filter);

        $result = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $render ? $this->renderDetailView($post->post_content) : $this->deesc($post->post_content),
            'name' => $post->post_name,
            'raw_content' => $post->post_content,
            'identifier' => $this->buildIdentifier()
        );

        foreach ($this->additionalFields as $field) {
            $key = 'wiki-' . $field['name'];
            $result[$field['name']] = $render ? $this->renderDetailView(get_post_meta($post->ID, $key, true)) : get_post_meta($post->ID, $key, true);
        }

        /** @var \WP_Post $post */
        $result = apply_filters('wiki_after_load', $result, $this->pluginVars);

        if ($render) {
            ob_start();
            include __DIR__ . "/../templates/wiki-page.phtml";
            $content = ob_get_contents();
            ob_end_clean();
            $result['content'] = $content;
            return $result;
        } else {
            return $result;
        }
    }
}