<?php
/**
 *
 * Created by PhpStorm.
 * User: michael.kirchner
 * Date: 19.04.18
 * Time: 17:27
 */
namespace de\orcas\extension;

include "UpdateService.php";

if(!defined('ORCAS_DOMAIN')) {
    define('ORCAS_DOMAIN', 'https://www.orcas.de');
}

if (!defined('Orcas_Upgrade')) {
    define('Orcas_Upgrade', '1');

    class Upgrade
    {
        protected static $self = null;
        protected $tokenValidateUrl = ORCAS_DOMAIN . '/wp-json/orcas/validate';

        private function __construct()
        {
            add_action('admin_menu', array($this, 'addMenu'), 0);
            add_action('upgrader_process_complete', array($this, 'update'), 100, 2);
            add_filter( 'http_request_args', array($this, 'dm_prevent_update_check'), 10, 2 );
            add_action('init', array($this, 'init'));
        }

        public function init() {
            ob_start();
        }

        public function dm_prevent_update_check( $r, $url ) {
            if ( 0 === strpos( $url, 'https://api.wordpress.org/plugins/update-check/1.1/' ) /*&& strlen(get_option('orcas_upgrade_token')) > 0*/) {
                $proPlugins = \de\orcas\extension\UpdateService::getProPlugins();

                $plugins = json_decode( $r['body']['plugins'], true);
                foreach($proPlugins as $pluginSlug) {
                    unset( $plugins['plugins']["$pluginSlug/$pluginSlug.php"] );
                    unset( $plugins['plugins'][array_search( "$pluginSlug/$pluginSlug.php", $plugins['active'] )] );
                }
                $r['body']['plugins'] = json_encode( $plugins );
            }
            return $r;
        }

        /**
         * @param $upgrader_object
         * @param $options
         */
        public function update($upgrader_object, $options) {
            if ($options['action'] == 'update' && $options['type'] == 'plugin' ){
                $doUpdatePlugins = array();
                foreach($options['plugins'] as $each_plugin){
                    $data = explode('/', $each_plugin);
                    if (count($data)> 0 && in_array($data[0], \de\orcas\extension\UpdateService::getProPlugins())) {
                        $doUpdatePlugins[] = $data[0];
                    }
                }

                if(count($doUpdatePlugins) > 0) {
                    $updateService = new \de\orcas\extension\UpdateService();
                    $updateService->install($doUpdatePlugins, true);
                }
            }
        }

        /**
         * @return Upgrade
         */
        public static function instance() {
            if(static::$self == null) { static::$self = new Upgrade();

            }
            return static::$self;
        }

        public function redirect() {
            wp_redirect(home_url() . '/wp-admin/admin.php?page=orcas-upgrade');
            exit();
        }

        public function optionPage()
        {
            $success = false;
            $error = false;
            if (isset($_POST['orcas_upgrade_token'])) {
                $ch = curl_init($this->tokenValidateUrl . '?XDEBUG_SESSION_START=15096');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                    'domain' => get_home_url(),
                    'token' => $_POST['orcas_upgrade_token'],
                    'password' => $_POST['orcas_upgrade_token_password']
                )));

                $success = curl_exec($ch);
                curl_close($ch);

                if($success == 1) {
                    $success = true;
                    update_option('orcas_upgrade_token', $_POST['orcas_upgrade_token']);
                } else {
                    $error = true;
                    delete_option('orcas_upgrade_token');
                }
            } else if(isset($_POST['delete-token']) && $_POST['delete-token']) {
                delete_option('orcas_upgrade_token');
                $this->redirect();
            }

            if(isset($_POST['item-action'])) {
                $updateService = new \de\orcas\extension\UpdateService();
                $extensions = isset($_POST['extensions']) ? explode('-', $_POST['extensions']) : array();

                if(isset($_POST['slug'])) {
                    foreach($extensions as &$ext) {
                        $ext = $_POST['slug'] . '-' . $ext;
                    }
                }

                switch($_POST['action']) {
                    case 'download':
                        $updateService->install(array($_POST['slug']));
                        break;
                    case 'pro-download':
                        $updateService->install(array($_POST['slug']), true);
                        break;
                    case 'install':
                        $updateService->install(array($_POST['slug']), false, false);
                        $this->redirect();
                        break;
                    case 'pro-install':
                        $updateService->install(array($_POST['slug']), true, false);
                        $this->redirect();
                        break;
                    case 'activate-extension':
                        $updateService->installSingleExtension(array($_POST['slug']));
                        break;
                    case 'deactivate-extension':
                        $updateService->uninstallSingleExtension($extensions);
                        break;
                    case 'upgrade':
                        $updateService->install(array($_POST['slug']), true);
                    break;
                    case 'downgrade':
                        $updateService->downgrade(array($_POST['slug']));
                        $updateService->uninstallSingleExtension($extensions);
                        break;
                    case 'deactivate':
                        $updateService->deactivate(array($_POST['slug']));
                        $updateService->uninstallSingleExtension($extensions);
                        break;
                    case 'uninstall':
                        $updateService->uninstall(array($_POST['slug']));
                        break;
                }

                wp_redirect($_SERVER['REQUEST_URI']);
                exit();
            }

            if(!is_writable(\de\orcas\extension\UpdateService::getPluginDir())) {
                echo __('Plugins cannot installed! Permission denied.', 'orcas-upgrade');
            }

            include plugin_dir_path(__FILE__) . 'views' . DIRECTORY_SEPARATOR . 'view.php';
            wp_enqueue_style("License Server", plugin_dir_url(__FILE__) . "css/style.css", array(), "1.0");
        }

        public function addmenu()
        {
	        add_menu_page(
		        __('orcas', 'orcas-upgrade'),
		        __('orcas', 'orcas-upgrade'),
		        'manage_options',
		        'orcas',
		        array($this, 'optionPage'),
		        'data:image/svg+xml;base64,' . base64_encode(file_get_contents(plugin_dir_path(__FILE__) . '/images/menu-icon.svg')),
		        10
	        );

	        add_submenu_page(
		        'orcas',
		        __('License & Shop', 'orcas-upgrade'),
		        __('License & Shop', 'orcas-upgrade'),
		        'manage_options',
		        'orcas-upgrade',
		        array($this, 'optionPage')
	        );

	        remove_submenu_page('orcas', 'orcas');
        }
    }

    Upgrade::instance();
}