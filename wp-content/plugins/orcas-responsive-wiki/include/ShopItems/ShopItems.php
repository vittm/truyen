<?php
/**
 * Created by PhpStorm.
 * User: crea
 * Date: 15.05.18
 * Time: 13:02
 */

namespace de\orcas\extension;

if(!defined('ORCAS_DOMAIN')) {
    define('ORCAS_DOMAIN', 'https://www.orcas.de');
}

if(!defined('ShopItems')){

    define('ShopItems', '1');
    if(!defined('SHOP_API_URL')) define('SHOP_API_URL', ORCAS_DOMAIN . '/wp-json/orcas/shop/plugins?XDEBUG_SESSION_START=17577');

    class ShopItems {
        public static function getItems() {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,SHOP_API_URL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                'token' => get_option('orcas_upgrade_token'),
                'domain' => get_home_url(),
                'language' => preg_replace("/_[a-zA-Z]+/", '', get_locale())
            )));

            $response = curl_exec ($ch);

            curl_close ($ch);

            return json_decode($response,true);
        }

        public static function addButton($action, $slug, $label, $hidden = array()) {
            $hiddenFields = '';

            foreach($hidden as $name => $hide) {
                $v = is_array($hide) ? join('-', $hide): str_replace(array("\r\n"), '-', $hide);
                $hiddenFields .= "<input type='hidden' name='$name' value='$v'/>";
            }

            return "<form method='post'>
                $hiddenFields
                <input type='hidden' name='slug' value='$slug' />
                <input type='hidden' name='action' value='$action' />
                <input type='submit' name='item-action' value='$label' />
                </form>";
        }

        public static function checkIsInstalled($require) {
            $installedSingleExtensions = json_decode(get_option('orcas_installed_single_extension', '[]'), true);
            $set = explode('#', $require);
            $pluginSet = explode('|', $set[0]);

            $pluginSlug = $pluginSet[0];
            $label = false;
            $isInstalled = is_plugin_active("$pluginSlug" . DIRECTORY_SEPARATOR . "$pluginSlug.php");

            if(!$isInstalled) {
                $label = $pluginSlug;
            }

            if(count($pluginSet) > 1) {
                $extensionName = $pluginSet[1];
                $extensionInstalled = in_array($pluginSlug . '-' . $extensionName, $installedSingleExtensions);
                if(!$extensionInstalled) {
                    $label = $pluginSlug;
                }
            }

            if($label && count($set) > 1) {
                $label = __($set[1]);
            }

            return $label;

        }
    }
}