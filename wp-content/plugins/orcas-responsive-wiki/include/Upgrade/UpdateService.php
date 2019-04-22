<?php
/**
 * Created by PhpStorm.
 * User: michael.kirchner
 * Date: 08.05.18
 * Time: 17:33
 */

namespace de\orcas\extension;

if(!defined('ORCAS_DOMAIN')) {
    define('ORCAS_DOMAIN', 'https://www.orcas.de');
}

if (!defined('Orcas_Update_Service')) {
    define('Orcas_Update_Service', '1');

    class UpdateService
    {
        protected static $plugins = array();
        protected static $pluginDir = null;
        protected $updateUrl = ORCAS_DOMAIN . '/wp-json/orcas/update';

        public function __construct()
        {
            $path = explode('/', __DIR__);
            $index = array_search('plugins', $path) + 1;
            array_splice($path, $index);
            static::$pluginDir = implode('/', $path);

            if (defined('ORCAS_LICENSE_DEBUG') && ORCAS_LICENSE_DEBUG) {
            	$this->updateUrl .= '?XDEBUG_SESSION_START=14430';
            }
        }

        public static function comparePluginVersion($slug, $version) {
            if(preg_match("/[0-9]+\.[0-9]+\.[0-9]+/", $version)) {
	            $data = get_file_data(static::$pluginDir . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . $slug . '.php', array('version' => 'Version'));

                return version_compare($version, $data['version'], '>');
            }

            return false;
        }

        public static function getPluginDir() {
            if(static::$pluginDir == null) {
                $path = explode(DIRECTORY_SEPARATOR, __DIR__);
                $index = array_search('plugins', $path) + 1;
                array_splice($path, $index);
                static::$pluginDir = implode(DIRECTORY_SEPARATOR, $path);
            }

            return static::$pluginDir;
        }

        public static function extensionInstalled($slug, $extension) {
            $installedSingleExtensions = json_decode(get_option('orcas_installed_single_extension', '[]'), true);

            if(is_array($extension)) {
                $extension = join('-', $extension);
            } else {
                $extension = str_replace(array("\r\n"), '-', $extension);
            }

            return in_array($slug . '-' . $extension, $installedSingleExtensions);
        }

        public static function isProInstalled($slug) {
            return in_array($slug, static::getProPlugins());
        }

        public static function isInstalled($slug) {
            return is_plugin_active("$slug" . DIRECTORY_SEPARATOR . "$slug.php"); //array_key_exists($slug, static::getPlugins());
        }

        public static function getProPlugins() {
            return json_decode(get_option('orcas_pro_extension', '[]'), true);
        }

        public static function getPlugins() {
            return static::$plugins;
        }

        public static function setPlugin($path)
        {
            $path = explode(DIRECTORY_SEPARATOR, $path);

            $index = array_search('plugins', $path) + 2;

            array_splice($path, $index);
            static::$plugins[$path[count($path) - 1]] = implode(DIRECTORY_SEPARATOR, $path);
        }

        public function upgrade($plugins = null)
        {
            $this->doOrcasAction($plugins, 'upgrade');
        }

        public function install($plugins = null, $proInstall = false, $download = true) {
            if($download) {
                $this->doOrcasAction($plugins, $proInstall ? 'pro-install' : 'install');
            } else if($plugins) {
                $path = static::$pluginDir;
                foreach($plugins as $plugin) {
                    activate_plugin($path . DIRECTORY_SEPARATOR . "$plugin" . DIRECTORY_SEPARATOR . "$plugin.php");
                }
            }
        }

        public function installSingleExtension($plugins) {
            $this->doOrcasAction($plugins, 'extension-install');
        }

        public function uninstallSingleExtension($pluginSlug) {
            $installedSingleExtensions = json_decode(get_option('orcas_installed_single_extension', '[]'), true);
            $installedSingleExtensions = array_diff($installedSingleExtensions, $pluginSlug);
            update_option('orcas_installed_single_extension', json_encode($installedSingleExtensions));
        }

        protected function doOrcasAction($plugins = null, $type = null) {
            $token = get_option('orcas_upgrade_token');

                $data = $this->send(array(
                    'token' => $token,
                    'state' => $type, //update|install|pro-install|uninstall
                    'plugins' => $plugins ? $plugins : array_keys(static::$plugins)
                ));
                if ($data) {
                    require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'php-zip' . DIRECTORY_SEPARATOR . 'Zip.php';
                    //do upgrade plugins
                    $this->installPlugin($data);
                    $this->installExtensions($data);
                }
        }

        /**
         * @param $plugins
         */
        public function downgrade($plugins) {
            foreach($plugins as $plugin) {
                //TODO workaround implementing a function that conect to orcas.de and ask for foldert that must deleted
                $this->rrmdir(\de\orcas\extension\UpdateService::$pluginDir . DIRECTORY_SEPARATOR . "$plugin" . DIRECTORY_SEPARATOR . "extension");
            }
            $proExtensions = json_decode(get_option('orcas_pro_extension', '[]'), true);
            $proExtensions = array_diff($proExtensions, $plugins);
            update_option('orcas_pro_extension', json_encode($proExtensions));
            //$this->install($plugins);
            $this->send(array(
                'token' => get_option('orcas_upgrade_token'),
                'state' => 'uninstall', //update|install|pro-install|uninstall
                'plugins' => $plugins ? $plugins : array_keys(static::$plugins)
            ));
        }

        public function deactivate($plugins) {
            foreach($plugins as $plugin) {
                deactivate_plugins(array(\de\orcas\extension\UpdateService::$pluginDir . DIRECTORY_SEPARATOR . "$plugin" . DIRECTORY_SEPARATOR . "$plugin.php"));
            }
        }

        public function uninstall($plugins) {
            $proExtensions = json_decode(get_option('orcas_pro_extension', '[]'), true);
            $proExtensions = array_diff($proExtensions, $plugins);
            update_option('orcas_pro_extension', json_encode($proExtensions));
            $this->send(array(
                'token' => get_option('orcas_upgrade_token'),
                'state' => 'uninstall', //update|install|pro-install|uninstall
                'plugins' => $plugins ? $plugins : array_keys(static::$plugins)
            ));
            foreach($plugins as $plugin) {
                //deactivate_plugins(array(\de\orcas\extension\UpdateService::$pluginDir . "/$plugin/$plugin.php"));
                $this->rrmdir(\de\orcas\extension\UpdateService::$pluginDir . DIRECTORY_SEPARATOR . "$plugin");
                //uninstall_plugin("$plugin/$plugin.php");
            }
        }

        public function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir . DIRECTORY_SEPARATOR . $object))
                            $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                        else
                            unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
                rmdir($dir);
            }
        }


        protected function installPlugin($data) {
            if (isset($data['plugins'])) {
                $path = static::$pluginDir;
                foreach ($data['plugins'] as $slugName => $plugin) {
                    if (isset($plugin['plugin'])) {
                        try {
                            $fileName = plugin_dir_path(__FILE__) . "plugin.zip";
                            $zip = new \Zip();
                            file_put_contents($fileName, base64_decode($plugin['plugin']));

                            mkdir("$path" . DIRECTORY_SEPARATOR . "cache");
                            $zip->unzip_file($fileName, "$path" . DIRECTORY_SEPARATOR . "cache");
                            exec("cp -rf $path" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "$slugName $path", $out);
                            UpdateService::setPlugin("$path" . DIRECTORY_SEPARATOR . "$slugName");
                            unlink($fileName);
                            exec("rm -rf $path" . DIRECTORY_SEPARATOR . "cache");
                        } catch (\Exception $e) {}
                    }
                }
            }
        }

        protected function installExtensions($data) {
            if (isset($data['plugins'])) {
                $proExtensions = json_decode(get_option('orcas_pro_extension', '[]'), true);
                $installedSingleExtensions = json_decode(get_option('orcas_installed_single_extension', '[]'), true);
                foreach ($data['plugins'] as $slugName => $plugin) {
                    if (isset($plugin['extension'])) {
                        if(!in_array($slugName, $proExtensions) && !isset($plugin['single_extension'])) $proExtensions[] = $slugName;

                        //set all extensions as single with plugin slug as installed
                        if(isset($plugin['single_extension']) && is_array($plugin['single_extension'])) {
                            foreach($plugin['single_extension'] as $extname) {
                                if(!in_array($slugName . '-' . $extname, $installedSingleExtensions)) {
                                    $installedSingleExtensions[] = $slugName . '-' . $extname;
                                }
                            }
                        }

                        //set all extension plugin slug join with extensions as installed
//                        if(isset($plugin['single_extension']) && is_array($plugin['single_extension']) && !in_array($slugName . '-' . join('-', $plugin['single_extension']), $installedSingleExtensions)) {
//                            $installedSingleExtensions[] = $slugName . '-' . join('-', $plugin['single_extension']);
//                        }

                        $path = static::$plugins[$slugName];

                        try {
                            $fileName = plugin_dir_path(__FILE__) . "extension.zip";
                            $zip = new \Zip();
                            file_put_contents($fileName, base64_decode($plugin['extension']));

                            $zip->unzip_file($fileName, static::$pluginDir . DIRECTORY_SEPARATOR . "$slugName");

                            unlink($fileName);
                        } catch (\Exception $e) {}
                    }
                }

                update_option('orcas_installed_single_extension', json_encode($installedSingleExtensions));
                update_option('orcas_pro_extension', json_encode($proExtensions));
            }
        }

        public static function registerProExtension($pluginSlug) {
            $proExtensions = json_decode(get_option('orcas_pro_extension', '[]'), true);
            if(!in_array($pluginSlug, $proExtensions)) {
                $proExtensions[] = $pluginSlug;
                update_option('orcas_pro_extension', json_encode($proExtensions));
            }
        }

        public static function unregister($pluginSlug) {
            $proExtensions = json_decode(get_option('orcas_pro_extension', '[]'), true);
            $proExtensions = array_diff($proExtensions, array($pluginSlug));
            update_option('orcas_pro_extension', json_encode($proExtensions));
        }

        protected function send($data)
        {
            $data['domain'] = get_home_url();
            $data['language'] = preg_replace("/_[a-zA-Z]+/", '', get_locale());
            $ch = curl_init($this->updateUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            $raw_file_data = curl_exec($ch);
            curl_close($ch);

            return json_decode($raw_file_data, true);
        }
    }
}

//UpdateService::setPlugin(__DIR__);