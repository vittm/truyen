<?php
/**
 * Created by PhpStorm.
 * User: michael.kirchner
 * Date: 21.03.18
 * Time: 17:19
 */

/**
 * $wp_root_directory is a global variable if we link our plugin with symlink in the wordpress plugin folder. you need to set in the main plugin file this variable with ABSPATH
 */
global $orcas_plugins;

if (!isset($orcas_plugins)) {
	$orcas_plugins = array();
}

$orcas_plugins[]       = array(
	'version' => '1.1.9',  // includes version number
	'path'    => __DIR__,
);

add_action('plugins_loaded', function () {
	global $orcas_plugins, $orcas_autoload_version, $orcas_plugin_stats;
	if (isset($orcas_plugins)) {
		// Step 1: Find and load newest version
		$newestVer  = "0.0.0";
		$newestPath = false;
		foreach ($orcas_plugins as $plugin) {
			if (version_compare($newestVer, $plugin['version'], '<')) {
				$newestVer  = $plugin['version'];
				$newestPath = $plugin['path'];
			}
		}
		if (false !== $newestPath) {
			orcas_load_dir($newestPath);
		}

		// Step 2: Register update plugin paths
		foreach ($orcas_plugins as $plugin) {
			\de\orcas\extension\UpdateService::setPlugin($plugin['path'] . DIRECTORY_SEPARATOR . 'Upgrade' . DIRECTORY_SEPARATOR);
		}
		$orcas_autoload_version = $newestVer;

		if($orcas_plugin_stats) {
			foreach($orcas_plugin_stats as $pluginData) {
				\de\orcas\extension\UpdateService::analytics($pluginData['file'], $pluginData['action']);
			}
		}


		unset($GLOBALS['orcas_plugins']);
	}
}, 1);


if (!function_exists('orcas_load_dir')) {
	function orcas_load_dir($dir) {
		if (!file_exists($dir)) {
			mkdir($dir);
		}

		global $wp_root_directory;


		$pluginSlug = explode(DIRECTORY_SEPARATOR, $dir);
		if(isset($wp_root_directory[__DIR__])) {
			$symPluginPath = explode(DIRECTORY_SEPARATOR, $wp_root_directory[__DIR__] . str_replace(__DIR__, '', $dir));
		} else {
			$symPluginPath = $pluginSlug;
		}

		$useIncludeFilter = true;
		$installedSingleExtensions = json_decode(get_option('orcas_installed_single_extension', '[]'), true);
        if($pluginSlug[count($pluginSlug) - 1] == 'include' || $pluginSlug[count($pluginSlug) - 1] == 'core') {
            $useIncludeFilter = false;
        } else {
			if(file_exists($dir . DIRECTORY_SEPARATOR . 'autoload.php')) {
				$useIncludeFilter = false;
				$installedSingleExtensions = array();
			}
            $pluginSlug = $symPluginPath[count($symPluginPath) - 4];
        }

		if ($handle = @opendir($dir)) {
			/* Das ist der korrekte Weg, ein Verzeichnis zu durchlaufen. */
			while (false !== ($entry = readdir($handle))) {
				if ($entry != '.' && $entry != '..') {
					if (is_dir($dir . DIRECTORY_SEPARATOR .$entry) && is_file($dir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . "$entry.php")) {
					    if($useIncludeFilter && !in_array($pluginSlug . '-' . $entry, $installedSingleExtensions)) continue;

						require $dir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . "$entry.php";
						if (class_exists("\\de\\orcas\\wiki\\extension\\$entry")) {
							$extention = "\\de\\orcas\\wiki\\extension\\$entry";
							if (!method_exists($extention, 'instance')) {
								new $extention();
							}
						} else if (class_exists("\\de\\orcas\\extension\\$entry")) {
							$extention = "\\de\\orcas\\extension\\$entry";
							if (!method_exists($extention, 'instance')) {
								new $extention();
							}
						} else if(class_exists("\\de\\orcas\\core\\$entry")) {
                            $extention = "\\de\\orcas\\core\\$entry";
                            if (!method_exists($extention, 'instance')) {
                                new $extention();
                            }
                        }
					}
				}
			}

			closedir($handle);
		}
	}
}
orcas_load_dir(__DIR__  .DIRECTORY_SEPARATOR. '..' . DIRECTORY_SEPARATOR . 'extension');
orcas_load_dir(__DIR__  .DIRECTORY_SEPARATOR. '..' . DIRECTORY_SEPARATOR . 'core');
