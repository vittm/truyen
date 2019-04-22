<?php
/**
 * Created by PhpStorm.
 * User: michael.kirchner
 * Date: 23.08.18
 * Time: 15:03
 */

namespace de\orcas\extension;

if(!defined('ORCAS_DOMAIN')) {
    define('ORCAS_DOMAIN', 'https://www.orcas.de');
}

if (!class_exists('\de\orcas\extension\Analytics')) {
	class Analytics {
        public function __construct($pluginFile, $action) {
            $data = array_merge(array(
				'action'    => $action,
				'plugin'    => basename($pluginFile, '.php'),
				'agent'     => $_SERVER['HTTP_USER_AGENT'],
                'signature' => $_SERVER['SERVER_SIGNATURE'],
				'domain'    => get_home_url()
            ), get_file_data($pluginFile, array('version' => 'Version', 'plugin_name' => 'Plugin Name')));

			$ch = curl_init(ORCAS_DOMAIN . '/wp-json/orcas/analytics');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                'data' => $data
            )));

            $response = curl_exec($ch);
            curl_close($ch);
        }
    }
}