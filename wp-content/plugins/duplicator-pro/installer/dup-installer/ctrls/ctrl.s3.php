<?php
defined("DUPXABSPATH") or die("");
/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */

//-- START OF ACTION STEP 3: Update the database
require_once($GLOBALS['DUPX_INIT'].'/classes/config/class.archive.config.php');
require_once($GLOBALS['DUPX_INIT'].'/classes/config/class.wp.config.tranformer.php');
require_once($GLOBALS['DUPX_INIT'].'/classes/utilities/class.u.multisite.php');
require_once($GLOBALS['DUPX_INIT'].'/classes/utilities/class.u.search.reaplce.manager.php');


/** JSON RESPONSE: Most sites have warnings turned off by default, but if they're turned on the warnings
  cause errors in the JSON data Here we hide the status so warning level is reset at it at the end */
$ajax3_start		 = DUPX_U::getMicrotime();
// We have already removing warning from json resp
// It cause 500 internal server error so commenting out
/*
$ajax3_error_level	 = error_reporting();
error_reporting(E_ERROR);
*/

//POST PARAMS
$_POST['blogname']				 = isset($_POST['blogname']) ? htmlspecialchars($_POST['blogname'], ENT_QUOTES) : 'No Blog Title Set';
$_POST['postguid']				 = isset($_POST['postguid']) && $_POST['postguid'] == 1 ? 1 : 0;
$_POST['fullsearch']			 = isset($_POST['fullsearch']) && $_POST['fullsearch'] == 1 ? 1 : 0;

$is_mapping = (isset($_POST['replace_mode']) && $_POST['replace_mode'] === "mapping");

if (isset($_POST['path_old'])) {
	$post_path_old = DUPX_U::sanitize_text_field($_POST['path_old']);
	$_POST['path_old'] = trim($post_path_old);
} else {
	$_POST['path_old'] = null;
}

if (isset($_POST['path_new'])) {
	$post_path_new = DUPX_U::sanitize_text_field($_POST['path_new']);
	$_POST['path_new'] = trim($post_path_new);
} else {
	$_POST['path_new'] = null;
}

if ($is_mapping && isset( $_POST['mu_replace'][1])) {
    $_POST['siteurl'] = $_POST['mu_replace'][1];
}
if (isset($_POST['siteurl'])) {
	$post_site_user = DUPX_U::sanitize_text_field($_POST['siteurl']);
	$_POST['siteurl'] = rtrim(trim($post_site_user), '/');
} else {
	$_POST['siteurl'] = null;
}

$_POST['tables'] = isset($_POST['tables']) && is_array($_POST['tables']) ? array_map('DUPX_U::sanitize_text_field', $_POST['tables']) : array();

// set url old from mapping
if ($is_mapping && isset( $_POST['mu_search'][1])) {
    $_POST['url_old'] = $_POST['mu_search'][1];
}
if (isset($_POST['url_old'])) {
    $post_url_old = DUPX_U::sanitize_text_field($_POST['url_old']);
    $_POST['url_old'] = trim($post_url_old);
} else {
    $_POST['url_old'] = null;
}

// set new old from mapping
if ($is_mapping && isset( $_POST['mu_replace'][1])) {
    $_POST['url_new'] = $_POST['mu_replace'][1];
}
if (isset($_POST['url_new'])) {
    $post_url_new = DUPX_U::sanitize_text_field($_POST['url_new']);
    $_POST['url_new'] = rtrim(trim($post_url_new), '/');
} else {
    $_POST['url_new'] = null;
}

if (isset($_POST['cross_search'])) {
    $cross_search = $_POST['cross_search'] == true;
} else {
    $cross_search = false;
}

$_POST['subsite-id']			 = isset($_POST['subsite-id']) ? intval($_POST['subsite-id']) : -1;
$_POST['ssl_admin']				 = (isset($_POST['ssl_admin'])) ? true : false;
$_POST['auth_keys_and_salts']	 = (isset($_POST['auth_keys_and_salts'])) ? true : false;
$_POST['cache_wp']				 = (isset($_POST['cache_wp'])) ? true : false;
$_POST['cache_path']			 = (isset($_POST['cache_path'])) ? true : false;
$_POST['empty_schedule_storage'] = (isset($_POST['empty_schedule_storage']) && $_POST['empty_schedule_storage'] == '1') ? true : false;
$_POST['replace_mode']           = isset($_POST['replace_mode']) ? DUPX_U::sanitize_text_field($_POST['replace_mode']) : "legacy";
$_POST['remove_redundant']       = isset($_POST['remove_redundant']) ? DUPX_U::sanitize_text_field($_POST['remove_redundant']) : 0;
$_POST['wp_debug'] = (isset($_POST['wp_debug']) && 1 == $_POST['wp_debug']) ? 1 : 0;
$_POST['wp_debug_log'] = (isset($_POST['wp_debug_log']) && 1 == $_POST['wp_debug_log']) ? 1 : 0;
$_POST['exe_safe_mode']	= isset($_POST['exe_safe_mode']) ? DUPX_U::sanitize_text_field($_POST['exe_safe_mode']) : 0;
$subsite_id	 = (int)$_POST['subsite-id'];

$replace_mail = filter_input(INPUT_POST, 'search_replace_email_domain', FILTER_VALIDATE_BOOLEAN);

//MYSQL CONNECTION
$post_dbpass = trim($_POST['dbpass']);
$dbh		 = DUPX_DB::connect($_POST['dbhost'], $_POST['dbuser'], $post_dbpass, $_POST['dbname']);
$dbConnError = (mysqli_connect_error()) ? 'Error: '.mysqli_connect_error() : 'Unable to Connect';

if (!$dbh) {
	$msg = "Unable to connect with the following parameters: <br/> <b>HOST:</b> {$post_db_host}<br/> <b>DATABASE:</b> {$post_db_name}<br/>";
	$msg .= "<b>Connection Error:</b> {$dbConnError}";
	DUPX_Log::error($msg);
}

$nManager = DUPX_NOTICE_MANAGER::getInstance();

$charset_server	 = @mysqli_character_set_name($dbh);
$db_max_time = mysqli_real_escape_string($dbh, $GLOBALS['DB_MAX_TIME']);
@mysqli_query($dbh, "SET wait_timeout = ".mysqli_real_escape_string($dbh, $db_max_time));

$post_db_charset = DUPX_U::sanitize_text_field($_POST['dbcharset']);
$post_db_collate = DUPX_U::sanitize_text_field($_POST['dbcollate']);
DUPX_DB::setCharset($dbh, $post_db_charset, $post_db_collate);
$charset_client	 = @mysqli_character_set_name($dbh);

//LOGGING
$date = @date('h:i:s');
$log  = <<<LOG
\n\n
********************************************************************************
DUPLICATOR PRO INSTALL-LOG
STEP-3 START @ {$date}
NOTICE: Do NOT post to public sites or forums
********************************************************************************
CHARSET SERVER:\t{$charset_server}
CHARSET CLIENT:\t{$charset_client}\n
********************************************************************************
OPTIONS:
postguid:\t{$_POST['postguid']}
fullsearch:\t{$_POST['fullsearch']}
replace_mode:\t{$_POST['replace_mode']}
ssl_admin:\t{$_POST['ssl_admin']}
auth_keys_and_salts:\t{$_POST['auth_keys_and_salts']}
cache_wp:\t{$_POST['cache_wp']}
cache_path:\t{$_POST['cache_path']}
empty_schedule_storage:\t{$_POST['empty_schedule_storage']}
replace_mode:\t{$_POST['replace_mode']}
remove_redundant:\t{$_POST['remove_redundant']}
wp_debug:\t{$_POST['wp_debug'] }
wp_debug_log:\t{$_POST['wp_debug_log']}
exe_safe_mode:\t{$_POST['exe_safe_mode']}
cross_search:\t{$cross_search}
replace_mail:\t{$replace_mail}
********************************************************************************

LOG;
DUPX_Log::info($log);

$POST_LOG = $_POST;
unset($POST_LOG['tables']);
unset($POST_LOG['plugins']);
unset($POST_LOG['dbpass']);
ksort($POST_LOG);

//Detailed logging
$log = "--------------------------------------\n";
$log .= "POST DATA\n";
$log .= "--------------------------------------\n";
$log .= print_r($POST_LOG, true);
$log .= "--------------------------------------\n";
$log .= "URLS DATA\n";
$log .= "--------------------------------------\n";
$log .= 'URL OLD '.$_POST['url_old']."\n";
$log .= 'URL NEW '.$_POST['url_new']."\n";
$log .= 'SITE_URL '.$_POST['siteurl']."\n";
$log .= ($is_mapping ? 'URL MAPPING' : 'NO URL MAPPING')."\n";
$log .= "--------------------------------------\n";
$log .= "TABLES TO SCAN\n";
$log .= "--------------------------------------\n";
$log .= (isset($_POST['tables']) && count($_POST['tables']) > 0) ? print_r($_POST['tables'], true) : 'No tables selected to update';
$log .= "--------------------------------------\n";
$log .= "KEEP PLUGINS ACTIVE\n";
$log .= "--------------------------------------\n";
$log .= (isset($_POST['plugins']) && count($_POST['plugins']) > 0) ? print_r($_POST['plugins'], true) : 'No plugins selected for activation';
DUPX_Log::info($log, 2);

//===============================================
//UPDATE ENGINE
//===============================================
$log = "--------------------------------------\n";
$log .= "SERIALIZER ENGINE\n";
$log .= "[*] scan every column\n";
$log .= "[~] scan only text columns\n";
$log .= "[^] no searchable columns\n";
$log .= "--------------------------------------";
DUPX_Log::info($log);

//===============================================
// INIZIALIZE WP_CONFIG TRANSFORMER
//===============================================
$root_path = $GLOBALS['DUPX_ROOT'];
$wpconfig_ark_path	= "{$root_path}/dup-wp-config-arc__{$GLOBALS['DUPX_AC']->package_hash}.txt";
$config_transformer =  null;
if (is_readable($wpconfig_ark_path)) {
    $config_transformer = new WPConfigTransformer($wpconfig_ark_path);
}

//===============================================
// SEARCH AND REPLACE STRINGS
//===============================================
$s_r_manager = DUPX_S_R_MANAGER::getInstance();


//CUSTOM REPLACE -> REPLACE LIST
if (isset($_POST['search'])) {
    $search_count = count($_POST['search']);
    if ($search_count > 0) {
        for ($search_index = 0; $search_index < $search_count; $search_index++) {
            $search_for   = DUPX_U::sanitize_text_field($_POST['search'][$search_index]);
            $replace_with = DUPX_U::sanitize_text_field($_POST['replace'][$search_index]);

            if (trim($search_for) != '') {
                $s_r_manager->addItem($search_for, $replace_with, DUPX_S_R_ITEM::TYPE_STRING, 20);
            }
        }
    }
}

//MULTISITE SEARCH AND REPLACE
if ($GLOBALS['DUPX_AC']->mu_mode == 0) {
    $action_mu_mode = DUPX_MultisiteMode::SingleSite;
} else {
    $action_mu_mode = $subsite_id > 0 ? DUPX_MultisiteMode::Standalone : $GLOBALS['DUPX_AC']->mu_mode;
}

DUPX_Log::info("SUBSITE ID :\"{$subsite_id}\"");
DUPX_Log::info("MU MODE :\"{$GLOBALS['DUPX_AC']->mu_mode}\"");
DUPX_Log::info("ACTION MU MODE :\"{$action_mu_mode}\"");
DUPX_Log::info("REPLACE MODE :\"{$_POST['replace_mode']}\"");

DUPX_Log::info("-----------------------------------------", 2);
DUPX_Log::info("ACTION MU MODE START :\"{$action_mu_mode}\"", 2);
switch ($action_mu_mode) {
    case DUPX_MultisiteMode::Subdomain:
    case DUPX_MultisiteMode::Subdirectory:
        $subsites = $GLOBALS['DUPX_AC']->subsites;

        // put the main sub site at the end
        $main_subsite = $subsites[0];
        array_shift($subsites);
        $subsites[]   = $main_subsite;

        if ($action_mu_mode !== DUPX_MultisiteMode::Subdomain) {
            $subsites = DUPX_U::urlForSubdirectoryMode($subsites, $GLOBALS['DUPX_AC']->url_old);
        }

        $main_url = $main_subsite->name;

        DUPX_Log::info("MAIN URL :\"{$main_url}\"", 2);
        DUPX_Log::info(
            '-- SUBSITES --'."\n".
            print_r($subsites, true), 3);

        foreach ($subsites as $cSub) {
            DUPX_Log::info('SUBSITE ID:'.$cSub->id.'NAME: '.$cSub->name, 3);

            if ($is_mapping && isset($_POST['mu_search'][$cSub->id])) {
                $search = $_POST['mu_search'][$cSub->id];
            } else {
                $search = $cSub->name;
            }

            if ($is_mapping && isset($_POST['mu_replace'][$cSub->id])) {
                $replace = $_POST['mu_replace'][$cSub->id];
            } else {
                $replace = DUPX_U::getDefaultURL($cSub->name, $main_url, $action_mu_mode === DUPX_MultisiteMode::Subdomain);
            }

            // get table for search and replace scope for subsites
            if ($cross_search == false && $cSub->id > 1) {
                $tables = DUPX_MU::getSubsiteTables($cSub->id, $dbh, $GLOBALS['DUPX_AC']->wp_tableprefix);
            } else {
                // global scope
                $tables = true;
            }
            $priority = ($cSub->id > 1) ? 5 : 10;
            $s_r_manager->addItem($search, $replace, DUPX_S_R_ITEM::TYPE_URL, $priority, $tables);

            // Replace email address (xyz@oldomain.com to xyz@newdomain.com).
            if ($replace_mail) {
                $at_old_domain = '@'.DUPX_U::getDomain($search);
                $at_new_domain = '@'.DUPX_U::getDomain($replace);
                $s_r_manager->addItem($at_old_domain, $at_new_domain, DUPX_S_R_ITEM::TYPE_STRING, 20, $tables);
            }

            // for domain host and path priority is on main site
            $priority = ($cSub->id > 1) ? 10 : 5;
            $sUrlInfo = parse_url ($search);
            $sHost = isset($sUrlInfo['host']) ? $sUrlInfo['host'] : '';
            $sPath = isset($sUrlInfo['path']) ? $sUrlInfo['path'] : '';
            $rUrlInfo = parse_url ($replace);
            $rHost = isset($rUrlInfo['host']) ? $rUrlInfo['host'] : '';
            $rPath = isset($rUrlInfo['path']) ? $rUrlInfo['path'] : '';

            // add path and host scope for custom columns in database
            $s_r_manager->addItem($sHost, $rHost, DUPX_S_R_ITEM::TYPE_URL, $priority, 'domain_host');
            $s_r_manager->addItem($sPath, $rPath, DUPX_S_R_ITEM::TYPE_STRING, $priority, 'domain_path');
        }
        break;
    case DUPX_MultisiteMode::Standalone:

        // REPLACE URL
        foreach ($GLOBALS['DUPX_AC']->subsites as $cSub) {
            if ($cSub->id == $subsite_id) {
                $standalone_obj = $cSub;
                break;
            }
        }
        if ($GLOBALS['DUPX_AC']->mu_mode !== DUPX_MultisiteMode::Subdomain) {
            $subsites       = DUPX_U::urlForSubdirectoryMode(array($standalone_obj), $GLOBALS['DUPX_AC']->url_old);
            $standalone_obj = $subsites[0];
        }
        $search  = $standalone_obj->name;
        $replace = DUPX_U::sanitize_text_field($_POST['url_new']);
        $s_r_manager->addItem($search, $replace, DUPX_S_R_ITEM::TYPE_URL, 5);

        // CONVERSION
        if ($subsite_id == 1) {
            // Since we are converting subsite to multisite consider this a standalone site
            $GLOBALS['DUPX_AC']->mu_mode = DUPX_MultisiteMode::Standalone;
            DUPX_Log::info("####4");
            $post_path_new               = DUPX_U::sanitize_text_field($_POST['path_new']);
            $new_content_dir             = (substr($post_path_new, -1, 1) == '/') ? "{$post_path_new}{$GLOBALS['DUPX_AC']->relative_content_dir}" : "{$post_path_new}/{$GLOBALS['DUPX_AC']->relative_content_dir}";
            try {
                DUPX_Log::info("####5");
                $post_subsite_id       = intval($_POST['subsite-id']);
                $post_remove_redundant = DUPX_U::sanitize_text_field($_POST['remove_redundant']);
                DUPX_MU::convertSubsiteToStandalone($post_subsite_id, $dbh, $GLOBALS['DUPX_AC'], $new_content_dir, $post_remove_redundant);
            } catch (Exception $ex) {
                DUPX_Log::info("####6");
                DUPX_Log::error("Problem with core logic of converting subsite into a standalone site.<br/>".$ex->getMessage().'<br/>'.$ex->getTraceAsString());
            }
        } else if ($subsite_id > 1) {

            // Need to swap the subsite prefix for the main table prefix
            $subsite_uploads_dir = "/uploads/sites/{$subsite_id}";
            $subsite_prefix      = "{$GLOBALS['DUPX_AC']->wp_tableprefix}{$subsite_id}_";

            $s_r_manager->addItem($subsite_uploads_dir, '/uploads', DUPX_S_R_ITEM::TYPE_PATH, 10);
            $s_r_manager->addItem($subsite_prefix, $GLOBALS['DUPX_AC']->wp_tableprefix, DUPX_S_R_ITEM::TYPE_STRING, 10);

            // REPLACE PATH
            DUPX_Log::info("####4");
            $post_path_new   = DUPX_U::sanitize_text_field($_POST['path_new']);
            $new_content_dir = (substr($post_path_new, -1, 1) == '/') ? "{$post_path_new}{$GLOBALS['DUPX_AC']->relative_content_dir}" : "{$post_path_new}/{$GLOBALS['DUPX_AC']->relative_content_dir}";

            try {
                DUPX_Log::info("####5");
                $post_subsite_id       = intval($_POST['subsite-id']);
                $post_remove_redundant = DUPX_U::sanitize_text_field($_POST['remove_redundant']);
                DUPX_MU::convertSubsiteToStandalone($post_subsite_id, $dbh, $GLOBALS['DUPX_AC'], $new_content_dir, $post_remove_redundant);
            } catch (Exception $ex) {
                DUPX_Log::info("####6");
                DUPX_Log::error("Problem with core logic of converting subsite into a standalone site.<br/>".$ex->getMessage().'<br/>'.$ex->getTraceAsString());
            }

            // Since we are converting subsite to multisite consider this a standalone site
            $GLOBALS['DUPX_AC']->mu_mode = DUPX_MultisiteMode::Standalone;
            DUPX_Log::info("####7");

            //Replace WP 3.4.5 subsite uploads path in DB
            if ($GLOBALS['DUPX_AC']->mu_generation === 1) {
                $post_subsite_id = intval($_POST['subsite-id']);
                $blogs_dir       = 'blogs.dir/'.$post_subsite_id.'/files';
                $uploads_dir     = 'uploads';

                $s_r_manager->addItem($blogs_dir, $uploads_dir, DUPX_S_R_ITEM::TYPE_PATH, 5);

                $post_url_new = DUPX_U::sanitize_text_field($_POST['url_new']);
                $files_dir    = "{$post_url_new}/files";
                $uploads_dir  = "{$post_url_new}/{$GLOBALS['DUPX_AC']->relative_content_dir}/uploads";

                $s_r_manager->addItem($files_dir, $uploads_dir, DUPX_S_R_ITEM::TYPE_URL, 5);
            }
        } else {
            // trace error stand alone conversion with subsite id <= 0
        }

        break;
    case DUPX_MultisiteMode::SingleSite:
    default:
        // do nothing
        break;
}

// GLOBAL -> REPLACE LIST
// DIRS PATHS
$post_path_old = $_POST['path_old'];
$post_path_new = $_POST['path_new'];
$s_r_manager->addItem($post_path_old, $post_path_new, DUPX_S_R_ITEM::TYPE_PATH, 10);

// URLS
// url from _POST
$old_urls_list = array($_POST['url_old']);
$post_url_new  = $_POST['url_new'];
$at_new_domain = '@'.DUPX_U::getDomain($post_url_new);

try {
    // urls from wp-config
    if (!is_null($config_transformer)) {
        if ($config_transformer->exists('constant', 'WP_HOME')) {
            $old_urls_list[] = $config_transformer->get_value('constant', 'WP_HOME');
        }

        if ($config_transformer->exists('constant', 'WP_SITEURL')) {
            $old_urls_list[] = $config_transformer->get_value('constant', 'WP_SITEURL');
        }
    }

    // urls from db
    $dbUrls = mysqli_query($dbh, 'SELECT * FROM `'.mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix).'options` where option_name IN (\'siteurl\',\'home\')');
    if ($dbUrls instanceof mysqli_result) {
        while ($row = $dbUrls->fetch_object()) {
            $old_urls_list[] = $row->option_value;
        }
    } else {
        DUPX_Log::info('DB ERROR: '.mysqli_error($dbh));
    }
} catch (Exception $e) {
    DUPX_Log::info('CONTINUE EXCEPTION: '.$exceptionError->getMessage());
    DUPX_Log::info('TRACE:');
    DUPX_Log::info($exceptionError->getTraceAsString());
}

$old_urls_list = array_unique($old_urls_list);
foreach ($old_urls_list as $old_url) {
    $s_r_manager->addItem($old_url, $post_url_new, DUPX_S_R_ITEM::TYPE_URL, 10);

    // Replace email address (xyz@oldomain.com to xyz@newdomain.com).
    if ($replace_mail) {
        $at_old_domain = '@'.DUPX_U::getDomain($old_url);
        $s_r_manager->addItem($at_old_domain, $at_new_domain, DUPX_S_R_ITEM::TYPE_STRING, 20);
    }
}

/*
DUPX_Log::info("Final replace list: \n". print_r($GLOBALS['REPLACE_LIST'], true),3);*/
$report = DUPX_UpdateEngine::load($dbh, $_POST['tables'], $_POST['fullsearch']);

//===============================================
//REMOVE MAINTENANCE MODE
//===============================================
if (isset($_POST['remove_redundant']) && $_POST['remove_redundant']) {
    if ($GLOBALS['DUPX_STATE']->mode == DUPX_InstallerMode::OverwriteInstall) {
        DUPX_U::maintenanceMode(false, $GLOBALS['DUPX_ROOT']);
    }
}

//BUILD JSON RESPONSE
$JSON						 = array();
$JSON['step1']				 = json_decode(urldecode($_POST['json']));
$JSON['step3']				 = $report;
$JSON['step3']['warn_all']	 = 0;
$JSON['step3']['warnlist']	 = array();

DUPX_UpdateEngine::logStats($report);
DUPX_UpdateEngine::logErrors($report);

//===============================================
//REMOVE LICENSE KEY
//===============================================
if(isset($GLOBALS['DUPX_AC']->brand) && isset($GLOBALS['DUPX_AC']->brand->enabled) && $GLOBALS['DUPX_AC']->brand->enabled)
{
    $license_check	 = mysqli_query($dbh, "SELECT COUNT(1) AS count FROM `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options` WHERE `option_name` LIKE 'duplicator_pro_license_key' ");
	$license_row	 = mysqli_fetch_row($license_check);
	$license_count	 = is_null($license_row) ? 0 : $license_row[0];
    if ($license_count > 0) {
        mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options` SET `option_value` = '' WHERE `option_name` LIKE 'duplicator_pro_license_key'");
    }
}

//===============================================
//CREATE NEW ADMIN USER
//===============================================
if (strlen($_POST['wp_username']) >= 4 && strlen($_POST['wp_password']) >= 6) {
	$wp_username = mysqli_real_escape_string($dbh, $_POST['wp_username']);
	$newuser_check	 = mysqli_query($dbh, "SELECT COUNT(*) AS count FROM `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."users` WHERE user_login = '{$wp_username}' ");
	$newuser_row	 = mysqli_fetch_row($newuser_check);
	$newuser_count	 = is_null($newuser_row) ? 0 : $newuser_row[0];

	if ($newuser_count == 0) {

		$newuser_datetime = @date("Y-m-d H:i:s");
		$newuser_datetime = mysqli_real_escape_string($dbh, $newuser_datetime);
		$newuser_security = mysqli_real_escape_string($dbh, 'a:1:{s:13:"administrator";b:1;}');
		
		$post_wp_username = DUPX_U::sanitize_text_field($_POST['wp_username']);
		$post_wp_password = DUPX_U::sanitize_text_field($_POST['wp_password']);

        $post_wp_mail = DUPX_U::sanitize_text_field($_POST['wp_mail']);
		$post_wp_nickname = DUPX_U::sanitize_text_field($_POST['wp_nickname']);
        if (empty($post_wp_nickname)) {
            $post_wp_nickname = $post_wp_username;
        }
        $post_wp_first_name = DUPX_U::sanitize_text_field($_POST['wp_first_name']);
		$post_wp_last_name = DUPX_U::sanitize_text_field($_POST['wp_last_name']);

		$wp_username = mysqli_real_escape_string($dbh, $post_wp_username);
		$wp_password = mysqli_real_escape_string($dbh, $post_wp_password);
        $wp_mail = mysqli_real_escape_string($dbh, $post_wp_mail);
		$wp_nickname = mysqli_real_escape_string($dbh, $post_wp_nickname);
        $wp_first_name = mysqli_real_escape_string($dbh, $post_wp_first_name);
		$wp_last_name = mysqli_real_escape_string($dbh, $post_wp_last_name);
		
		$newuser1 = @mysqli_query($dbh,
				"INSERT INTO `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."users`
				(`user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `user_activation_key`, `user_status`, `display_name`)
				VALUES ('{$wp_username}', MD5('{$wp_password}'), '{$wp_username}', '{$wp_mail}', '{$newuser_datetime}', '', '0', '{$wp_username}')");

		$newuser1_insert_id = mysqli_insert_id($dbh);
		$newuser1_insert_id = intval($newuser1_insert_id);

		$newuser2 = @mysqli_query($dbh,
				"INSERT INTO `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."usermeta`
				(`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', '".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."capabilities', '{$newuser_security}')");

		$newuser3 = @mysqli_query($dbh,
				"INSERT INTO `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."usermeta`
				(`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', '".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."user_level', '10')");

		//Misc Meta-Data Settings:
		@mysqli_query($dbh, "INSERT INTO `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'rich_editing', 'true')");
		@mysqli_query($dbh, "INSERT INTO `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'admin_color',  'fresh')");
		@mysqli_query($dbh, "INSERT INTO `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'nickname', '{$wp_nickname}')");
        @mysqli_query($dbh, "INSERT INTO `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'first_name', '{$wp_first_name}')");
        @mysqli_query($dbh, "INSERT INTO `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'last_name', '{$wp_last_name}')");



		//Add super admin permissions
		if ($GLOBALS['DUPX_AC']->mu_mode > 0 && $subsite_id == -1){
			$site_admins_query	 = mysqli_query($dbh,"SELECT meta_value FROM `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."sitemeta` WHERE meta_key = 'site_admins'");
			$site_admins		 = mysqli_fetch_row($site_admins_query);
			$site_admins[0] = stripslashes($site_admins[0]);
			$site_admins_array	 = unserialize($site_admins[0]);
			
			array_push($site_admins_array,$_POST['wp_username']);
			
			$site_admins_serialized	 = serialize($site_admins_array);
			
			@mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."sitemeta` SET meta_value = '{$site_admins_serialized}' WHERE meta_key = 'site_admins'");
			// Adding permission for each sub-site to the newly created user
			$admin_user_level = 10; // For wp_2_user_level
			$sql_values_array = array();
			$sql_values_array[] = "('{$newuser1_insert_id}', 'primary_blog', '{$GLOBALS['DUPX_AC']->main_site_id}')";
			foreach ($GLOBALS['DUPX_AC']->subsites as $subsite_info) {
				// No need to add permission for main site
				if ($subsite_info->id == $GLOBALS['DUPX_AC']->main_site_id) {
					continue;
				}

				$cap_meta_key = $subsite_info->blog_prefix.'capabilities';
				$sql_values_array[] = "('{$newuser1_insert_id}', '{$cap_meta_key}', '{$newuser_security}')";
				
				$user_level_meta_key = $subsite_info->blog_prefix.'user_level';
				$sql_values_array[] = "('{$newuser1_insert_id}', '{$user_level_meta_key}', '{$admin_user_level}')";
			}
			$sql = "INSERT INTO ".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."usermeta (user_id, meta_key, meta_value) VALUES ".implode(', ', $sql_values_array);
			@mysqli_query($dbh, $sql);
		}
		
		DUPX_Log::info("\nNEW WP-ADMIN USER:");
		if ($newuser1 && $newuser_test2 && $newuser3) {
			DUPX_Log::info("- New username '{$_POST['wp_username']}' was created successfully allong with MU usermeta.");
		} elseif ($newuser1) {
			DUPX_Log::info("- New username '{$_POST['wp_username']}' was created successfully.");
		} else {
			$newuser_warnmsg = "- Failed to create the user '{$_POST['wp_username']}' \n ";
			$JSON['step3']['warnlist'][] = $newuser_warnmsg;

            $nManager->addFinalReportNotice(array(
                        'shortMsg' => 'New admin user create error',
                        'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                        'longMsg' => $newuser_warnmsg,
                        'sections' => 'general'
                    ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_UPDATE , 'new-user-create-error');

			DUPX_Log::info($newuser_warnmsg);
		}
	} else {
		$newuser_warnmsg = "\nNEW WP-ADMIN USER:\n - Username '{$_POST['wp_username']}' already exists in the database.  Unable to create new account.\n";
		$JSON['step3']['warnlist'][] = $newuser_warnmsg;

        $nManager->addFinalReportNotice(array(
                'shortMsg' => 'New admin user create error',
                'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg' => $newuser_warnmsg,
                'sections' => 'general'
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_UPDATE , 'new-user-create-error');

		DUPX_Log::info($newuser_warnmsg);
	}
}

//===============================================
//CONFIGURATION FILE UPDATES
//===============================================
DUPX_Log::info("\n====================================");
DUPX_Log::info('CONFIGURATION FILE UPDATES:');
DUPX_Log::info("====================================\n");

if (file_exists($wpconfig_ark_path)) {

    //@todo: integrate all logic into DUPX_WPConfig::updateVars
    if (!is_writable($wpconfig_ark_path)) {
        $err_log = "\nWARNING: Unable to update file permissions and write to dup-wp-config-arc__[HASH].txt.  ";
        $err_log .= "Check that the wp-config.php is in the archive.zip and check with your host or administrator to enable PHP to write to the wp-config.php file.  ";
        $err_log .= "If performing a 'Manual Extraction' please be sure to select the 'Manual Archive Extraction' option on step 1 under options.";
        chmod($wpconfig_ark_path, 0644) ? DUPX_Log::info("File Permission Update: dup-wp-config-arc__[HASH].txt set to 0644") : DUPX_Log::error("{$err_log}");
    }

    $mu_newDomain		 = parse_url($_POST['url_new']);
    $mu_oldDomain		 = parse_url($_POST['url_old']);
    $mu_newDomainHost	 = $mu_newDomain['host'];
    $mu_oldDomainHost	 = $mu_oldDomain['host'];
    $mu_newUrlPath		 = parse_url($_POST['url_new'], PHP_URL_PATH);
    $mu_oldUrlPath		 = parse_url($_POST['url_old'], PHP_URL_PATH);

    if (empty($mu_newUrlPath) || ($mu_newUrlPath == '/')) {
        $mu_newUrlPath = '/';
    } else {
        $mu_newUrlPath = rtrim($mu_newUrlPath, '/').'/';
    }

    if (empty($mu_oldUrlPath) || ($mu_oldUrlPath == '/')) {
        $mu_oldUrlPath = '/';
    } else {
        $mu_oldUrlPath = rtrim($mu_oldUrlPath, '/').'/';
    }

    $config_transformer->update('constant', 'WP_HOME', $_POST['url_new'], array('normalize' => true, 'add' => false));
    $config_transformer->update('constant', 'WP_SITEURL', $_POST['url_new'], array('normalize' => true, 'add' => false));

    $config_transformer->update('constant', 'DOMAIN_CURRENT_SITE', $mu_newDomainHost, array('normalize' => true, 'add' => false));
    $config_transformer->update('constant', 'PATH_CURRENT_SITE', $mu_newUrlPath, array('normalize' => true, 'add' => false));

if ($GLOBALS['DUPX_AC']->mu_mode !== DUPX_MultisiteMode::Standalone) {
    $config_transformer->update('constant', 'NOBLOGREDIRECT', $_POST['url_new'], array( 'add'=> false, 'normalize' => true));
}
    if ($subsite_id != -1) {
        DUPX_Log::info("####10");
        $config_transformer->remove('constant', 'WP_ALLOW_MULTISITE');
        $config_transformer->update('constant', 'ALLOW_MULTISITE', 'false', array('add' => false , 'raw' => true, 'normalize' => true));
        $config_transformer->update('constant', 'MULTISITE', 'false', array('add' => false , 'raw' => true, 'normalize' => true));

        DUPX_Log::info('#### WP_ALLOW_MULTISITE constant removed from WP config file');
        DUPX_Log::info('#### ALLOW_MULTISITE constant value set to false in WP config file');
        DUPX_Log::info('#### MULTISITE constant value set to false in WP config file');
    }

    if ($GLOBALS['DUPX_AC']->mu_mode !== DUPX_MultisiteMode::Standalone) {
        $config_transformer->update('constant', 'NOBLOGREDIRECT', $_POST['url_new'], array( 'add'=> false, 'normalize' => true));
    }

    $db_pass = isset($_POST['dbpass']) ? DupProSnapLibUtil::wp_json_encode(trim($_POST['dbpass'])) : "''";
    $db_pass = str_replace(array('\x00','\/'), array('','/'), $db_pass);

    $config_transformer->update('constant', 'DB_NAME', trim(DUPX_U::wp_unslash($_POST['dbname'])));
    $config_transformer->update('constant', 'DB_USER', trim(DUPX_U::wp_unslash($_POST['dbuser'])));
    $config_transformer->update('constant', 'DB_PASSWORD', $db_pass, array('raw' => true));
    $config_transformer->update('constant', 'DB_HOST', trim(DUPX_U::wp_unslash($_POST['dbhost'])));

    //SSL CHECKS
    if ($_POST['ssl_admin']) {
        $config_transformer->update('constant', 'FORCE_SSL_ADMIN', 'true', array('raw' => true, 'normalize' => true));
    } else {
        $config_transformer->update('constant', 'FORCE_SSL_ADMIN', 'false', array('raw' => true, 'add' => false, 'normalize' => true));
    }

    if ($_POST['cache_wp']) {
        $config_transformer->update('constant', 'WP_CACHE', 'true', array('raw' => true, 'normalize' => true));
    } else {
        $config_transformer->update('constant', 'WP_CACHE', 'false', array('raw' => true, 'add' => false, 'normalize' => true));
    }

    // Cache: [ ] Keep Home Path
    if ($_POST['cache_path']) {
        if ($config_transformer->exists('constant', 'WPCACHEHOME')) {
            $wpcachehome_const_val = $config_transformer->get_value('constant', 'WPCACHEHOME');
            $wpcachehome_const_val = DUPX_U::wp_normalize_path($wpcachehome_const_val);
            $wpcachehome_new_const_val = str_replace($_POST['path_old'], $_POST['path_new'], $wpcachehome_const_val, $count);
            if ($count > 0) {
                $config_transformer->update('constant', 'WPCACHEHOME', $wpcachehome_new_const_val, array('normalize' => true));
            }
        }
    } else {
        $config_transformer->remove('constant', 'WPCACHEHOME');
    }

    if ($GLOBALS['DUPX_AC']->is_outer_root_wp_content_dir) {
        if (empty($GLOBALS['DUPX_AC']->wp_content_dir_base_name)) {
            $ret = $config_transformer->remove('constant', 'WP_CONTENT_DIR');
            // sometimes WP_CONTENT_DIR const removal failed, so we need to update them
            if (false === $ret) {
                $config_transformer->update('constant', 'WP_CONTENT_DIR', "dirname(__FILE__).'/wp-content'", array('raw' => true, 'normalize' => true));
            }
        } else {
            $config_transformer->update('constant', 'WP_CONTENT_DIR', "dirname(__FILE__).'/".$GLOBALS['DUPX_AC']->wp_content_dir_base_name."'", array('raw' => true, 'normalize' => true));
        }

    } elseif ($config_transformer->exists('constant', 'WP_CONTENT_DIR')) {
        $wp_content_dir_const_val = $config_transformer->get_value('constant', 'WP_CONTENT_DIR');
        $wp_content_dir_const_val = DUPX_U::wp_normalize_path($wp_content_dir_const_val);
        $new_path = str_replace($_POST['path_old'], $_POST['path_new'], $wp_content_dir_const_val, $count);
        if ($count > 0) {
            $config_transformer->update('constant', 'WP_CONTENT_DIR', $new_path, array('normalize' => true));
        }
    }

    //WP_CONTENT_URL
    // '/' added to prevent word boundary with domains that have the same root path
    if ($GLOBALS['DUPX_AC']->is_outer_root_wp_content_dir) {
        if (empty($GLOBALS['DUPX_AC']->wp_content_dir_base_name)) {
            $ret = $config_transformer->remove('constant', 'WP_CONTENT_URL');
            // sometimes WP_CONTENT_DIR const removal failed, so we need to update them
            if (false === $ret) {
                $new_url = rtrim($_POST['url_new'], '/').'/wp-content';
                $config_transformer->update('constant', 'WP_CONTENT_URL', $new_url, array('raw' => true, 'normalize' => true));
            }
        } else {
            $new_url = rtrim($_POST['url_new'], '/').'/'.$GLOBALS['DUPX_AC']->wp_content_dir_base_name;
            $config_transformer->update('constant', 'WP_CONTENT_URL', $new_url, array('normalize' => true));
        }
    } elseif ($config_transformer->exists('constant', 'WP_CONTENT_URL')) {
        $wp_content_url_const_val = $config_transformer->get_value('constant', 'WP_CONTENT_URL');
        $new_path = str_replace($_POST['url_old'] . '/', $_POST['url_new'] . '/', $wp_content_url_const_val, $count);
        if ($count > 0) {
            $config_transformer->update('constant', 'WP_CONTENT_URL', $new_path, array('normalize' => true));
        }
    }

    //WP_TEMP_DIR
    if ($config_transformer->exists('constant', 'WP_TEMP_DIR')) {
        $wp_temp_dir_const_val = $config_transformer->get_value('constant', 'WP_TEMP_DIR');
        $wp_temp_dir_const_val = DUPX_U::wp_normalize_path($wp_temp_dir_const_val);
        $new_path = str_replace($_POST['path_old'], $_POST['path_new'], $wp_temp_dir_const_val, $count);
        if ($count > 0) {
            $config_transformer->update('constant', 'WP_TEMP_DIR', $new_path, array('normalize' => true));
        }
    }

    // WP_PLUGIN_DIR
    if ($config_transformer->exists('constant', 'WP_PLUGIN_DIR')) {
        $wp_plugin_dir_const_val = $config_transformer->get_value('constant', 'WP_PLUGIN_DIR');
        $wp_plugin_dir_const_val = DUPX_U::wp_normalize_path($wp_plugin_dir_const_val);
        $new_path = str_replace($_POST['path_old'], $_POST['path_new'], $wp_plugin_dir_const_val, $count);
        if ($count > 0) {
            $config_transformer->update('constant', 'WP_PLUGIN_DIR', $new_path, array('normalize' => true));
        }
    }

    // WP_PLUGIN_URL
    if ($config_transformer->exists('constant', 'WP_PLUGIN_URL')) {
        $wp_plugin_url_const_val = $config_transformer->get_value('constant', 'WP_PLUGIN_URL');
        $new_path = str_replace($_POST['url_old'] . '/', $_POST['url_new'] . '/', $wp_plugin_url_const_val, $count);
        if ($count > 0) {
            $config_transformer->update('constant', 'WP_PLUGIN_URL', $new_path, array('normalize' => true));
        }
    }

    // WPMU_PLUGIN_DIR
    if ($config_transformer->exists('constant', 'WPMU_PLUGIN_DIR')) {
        $wpmu_plugin_dir_const_val = $config_transformer->get_value('constant', 'WPMU_PLUGIN_DIR');
        $wpmu_plugin_dir_const_val = DUPX_U::wp_normalize_path($wpmu_plugin_dir_const_val);
        $new_path = str_replace($_POST['path_old'], $_POST['path_new'], $wpmu_plugin_dir_const_val, $count);
        if ($count > 0) {
            $config_transformer->update('constant', 'WPMU_PLUGIN_DIR', $new_path, array('normalize' => true));
        }
    }

    // WPMU_PLUGIN_URL
    if ($config_transformer->exists('constant', 'WPMU_PLUGIN_URL')) {
        $wpmu_plugin_url_const_val = $config_transformer->get_value('constant', 'WPMU_PLUGIN_URL');
        $new_path = str_replace($_POST['url_old'] . '/', $_POST['url_new'] . '/', $wpmu_plugin_url_const_val, $count);
        if ($count > 0) {
            $config_transformer->update('constant', 'WPMU_PLUGIN_URL', $new_path, array('normalize' => true));
        }
    }

    $licence_type = $GLOBALS['DUPX_AC']->getLicenseType();
    if ($licence_type >= DUPX_LicenseType::Freelancer) {
        if ($_POST['auth_keys_and_salts']) {
            $need_to_change_const_keys = array(
                'AUTH_KEY',
                'SECURE_AUTH_KEY',
                'LOGGED_IN_KEY',
                'NONCE_KEY',
                'AUTH_SALT',
                'SECURE_AUTH_SALT',
                'LOGGED_IN_SALT',
                'NONCE_SALT',
            );
            foreach ($need_to_change_const_keys as $const_key) {
                $is_const_key_exists = $config_transformer->exists('constant', $const_key);
                $key = DUPX_WPConfig::generatePassword(64, true, true);

                if ($is_const_key_exists) {
                    $config_transformer->update('constant', $const_key, $key);
                } else {
                    $config_transformer->add('constant', $const_key, $key);
                }
            }
        }
    }

    // COOKIE_DOMAIN
    if (isset($_POST['cookie_domain'])& !empty($_POST['cookie_domain'])) {
        $cookie_domain_val = DUPX_U::sanitize_text_field($_POST['cookie_domain']);
        $config_transformer->update('constant', 'COOKIE_DOMAIN', $cookie_domain_val, array('normalize' => true));
    } else {
        $config_transformer->remove('constant', 'COOKIE_DOMAIN');
    }

    // AutoSave Interval
    if (isset($_POST['autosave_interval']) && !empty($_POST['autosave_interval'])) {
        $autosave_interval_int_val = intval($_POST['autosave_interval']);
        if ($autosave_interval_int_val > 0) {
            $autosave_interval_val = DUPX_U::sanitize_text_field($_POST['autosave_interval']);
            $config_transformer->update('constant', 'AUTOSAVE_INTERVAL', $autosave_interval_val, array('raw' => true, 'normalize' => true));
        }
    } else {
        $config_transformer->remove('constant', 'AUTOSAVE_INTERVAL');
    }

    if (isset($_POST['wp_post_revisions']) && !empty($_POST['wp_post_revisions'])) {
        $wp_post_revisions_val = DUPX_U::sanitize_text_field($_POST['wp_post_revisions']);
        if ('true' == $wp_post_revisions_val) {
            if (isset($_POST['wp_post_revisions_no']) && intval($_POST['wp_post_revisions_no']) > 0) {
                $post_revision_no_val = DUPX_U::sanitize_text_field($_POST['wp_post_revisions_no']);
                $config_transformer->update('constant', 'WP_POST_REVISIONS', $post_revision_no_val, array('raw' => true, 'normalize' => true));
            } else {
                $config_transformer->update('constant', 'WP_POST_REVISIONS', 'true', array('raw' => true, 'normalize' => true));
            }
        } elseif ('false' == $wp_post_revisions_val) {
            $config_transformer->update('constant', 'WP_POST_REVISIONS', 'false', array('raw' => true, 'normalize' => true));
        }
    } else {
        $config_transformer->remove('constant', 'WP_POST_REVISIONS');
    }




    $is_wp_debug_exists = $config_transformer->exists('constant', 'WP_DEBUG');
    $wp_debug_as_str = (isset($_POST['wp_debug']) && 1 == $_POST['wp_debug']) ? 'true' : 'false';
    if ($is_wp_debug_exists) {
        $config_transformer->update('constant', 'WP_DEBUG', $wp_debug_as_str, array('raw' => true));
    } else {
        if (1 == $_POST['wp_debug']) {
            $config_transformer->add('constant', 'WP_DEBUG', $wp_debug_as_str, array('raw' => true));
        }
    }

    $is_wp_debug_log_exists = $config_transformer->exists('constant', 'WP_DEBUG_LOG');
    $wp_debug_log_as_str = (isset($_POST['wp_debug_log']) && 1 == $_POST['wp_debug_log']) ? 'true' : 'false';
    if ($is_wp_debug_log_exists) {
        $config_transformer->update('constant', 'WP_DEBUG_LOG', $wp_debug_log_as_str, array('raw' => true));
    } else {
        if (1 == $_POST['wp_debug_log']) {
            $config_transformer->add('constant', 'WP_DEBUG_LOG', $wp_debug_log_as_str, array('raw' => true));
        }
    }

    // WP_DEBUG_DISPLAY
    $is_wp_debug_display_exists = $config_transformer->exists('constant', 'WP_DEBUG_DISPLAY');
    $wp_debug_display_as_str = (isset($_POST['wp_debug_display']) && 1 == $_POST['wp_debug_display']) ? 'true' : 'false';
    if ($is_wp_debug_display_exists) {
        $config_transformer->update('constant', 'WP_DEBUG_DISPLAY', $wp_debug_log_as_str, array('raw' => true));
    } else {
        if (isset($_POST['wp_debug_display']) && 1 == $_POST['wp_debug_display']) {
            $config_transformer->add('constant', 'WP_DEBUG_DISPLAY', $wp_debug_log_as_str, array('raw' => true));
        }
    }

    // SCRIPT_DEBUG
    $is_script_debug_exists = $config_transformer->exists('constant', 'SCRIPT_DEBUG');
    $script_debug_as_str = (isset($_POST['script_debug']) && 1 == $_POST['script_debug']) ? 'true' : 'false';
    if ($is_script_debug_exists) {
        $config_transformer->update('constant', 'SCRIPT_DEBUG', $script_debug_as_str, array('raw' => true));
    } else {
        if (isset($_POST['script_debug']) && 1 == $_POST['script_debug']) {
            $config_transformer->add('constant', 'SCRIPT_DEBUG', $script_debug_as_str, array('raw' => true));
        }
    }

    // SAVEQUERIES
    $is_savequeries_exists = $config_transformer->exists('constant', 'SAVEQUERIES');
    $savequeries_as_str = (isset($_POST['savequeries']) && 1 == $_POST['savequeries']) ? 'true' : 'false';
    if ($is_savequeries_exists) {
        $config_transformer->update('constant', 'SAVEQUERIES', $savequeries_as_str, array('raw' => true));
    } else {
        if (isset($_POST['savequeries']) && 1 == $_POST['savequeries']) {
            $config_transformer->add('constant', 'SAVEQUERIES', $savequeries_as_str, array('raw' => true));
        }
    }

    // WP_MEMORY_LIMIT
    if (isset($_POST['wp_memory_limit']) && !empty($_POST['wp_memory_limit'])) {
        $wp_memory_limit_val = DUPX_U::sanitize_text_field($_POST['wp_memory_limit']);
        $config_transformer->update('constant', 'WP_MEMORY_LIMIT', $wp_memory_limit_val, array('normalize' => true));
    } else {
        $config_transformer->remove('constant', 'WP_MEMORY_LIMIT');
    }

    // WP_MAX_MEMORY_LIMIT
    if (isset($_POST['wp_max_memory_limit']) && !empty($_POST['wp_max_memory_limit'])) {
        $wp_max_memory_limit_val = DUPX_U::sanitize_text_field($_POST['wp_max_memory_limit']);
        $config_transformer->update('constant', 'WP_MAX_MEMORY_LIMIT', $wp_max_memory_limit_val, array('normalize' => true));
    } else {
        $config_transformer->remove('constant', 'WP_MAX_MEMORY_LIMIT');
    }

    // Disable File modification DISALLOW_FILE_EDIT
    if (isset($_POST['disallow_file_edit'])) {
        $config_transformer->update('constant', 'DISALLOW_FILE_EDIT', 'true', array('raw' => true, 'normalize' => true));
    } else {
        $config_transformer->remove('constant', 'DISALLOW_FILE_EDIT');
    }

    // WP_AUTO_UPDATE_CORE
    if (isset($_POST['wp_auto_update_core']) && !empty($_POST['wp_auto_update_core'])) {
        $wp_auto_update_core_val = DUPX_U::sanitize_text_field($_POST['wp_auto_update_core']);
        $pass_arr = array('normalize' => true);
        if ('true' == $wp_auto_update_core_val || 'false' == $wp_auto_update_core_val) {
            $pass_arr['raw'] = true;
        }
        $config_transformer->update('constant', 'WP_AUTO_UPDATE_CORE', $wp_auto_update_core_val, $pass_arr);
    } else {
        $config_transformer->remove('constant', 'WP_AUTO_UPDATE_CORE');
    }
    
    DUPX_Log::info("UPDATED WP-CONFIG ARK FILE:\n - 'dup-wp-config-arc__[HASH].txt'");
    DUPX_Log::info("SETTING WP DEBUG CONFIG constants");
} else {
    DUPX_Log::info("WP-CONFIG ARK FILE NOT FOUND");
    DUPX_Log::info("WP-CONFIG ARK FILE:\n - 'dup-wp-config-arc__[HASH].txt'");
    DUPX_Log::info("SKIP FILE UPDATES\n");
}

if($_POST['retain_config']) {
	$new_htaccess_name = '.htaccess';
} else {
	$new_htaccess_name = 'htaccess.orig' . rand();
}

if(DUPX_ServerConfig::renameHtaccess($GLOBALS['DUPX_ROOT'], $new_htaccess_name)){
	DUPX_Log::info("\nReseted original .htaccess content from htaccess.orig");
}

//Web Server Config Updates
if (!isset($_POST['url_new']) || $_POST['retain_config']) {
	DUPX_Log::info("\nNOTICE: Retaining the original .htaccess, .user.ini and web.config files may cause");
	DUPX_Log::info("issues with the initial setup of your site.  If you run into issues with your site or");
	DUPX_Log::info("during the install process please uncheck the 'Config Files' checkbox labeled:");
	DUPX_Log::info("'Retain original .htaccess, .user.ini and web.config' and re-run the installer.");    
} else {
	DUPX_ServerConfig::setup($GLOBALS['DUPX_AC']->mu_mode, $GLOBALS['DUPX_AC']->mu_generation, $dbh, $root_path);
}

//===============================================
//GENERAL UPDATES & CLEANUP
//===============================================
DUPX_Log::info("\n====================================");
DUPX_Log::info('GENERAL UPDATES & CLEANUP:');
DUPX_Log::info("====================================\n");

$blog_name   = mysqli_real_escape_string($dbh, $_POST['blogname']);
$plugin_list = (isset($_POST['plugins'])) ? $_POST['plugins'] : array();
// Force Duplicator Pro active so we the security cleanup will be available
if(($GLOBALS['DUPX_AC']->mu_mode > 0) && ($subsite_id == -1))
{
	$multisite_plugin_list=array();
	foreach($plugin_list as $get_plugin)
	{
		$multisite_plugin_list[$get_plugin] = time();
	}

	if (!array_key_exists('duplicator-pro/duplicator-pro.php', $multisite_plugin_list)) {
		$multisite_plugin_list['duplicator-pro/duplicator-pro.php'] = time();
	}

	$serial_plugin_list	 = @serialize($multisite_plugin_list);
}
else
{
	if (!in_array('duplicator-pro/duplicator-pro.php', $plugin_list)) {
		$plugin_list[] = 'duplicator-pro/duplicator-pro.php';
	}
	$serial_plugin_list	 = @serialize($plugin_list);
}

/** FINAL UPDATES: Must happen after the global replace to prevent double pathing
  http://xyz.com/abc01 will become http://xyz.com/abc0101  with trailing data */
mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options` SET option_value = '".mysqli_real_escape_string($dbh, $blog_name)."' WHERE option_name = 'blogname' ");
if(($GLOBALS['DUPX_AC']->mu_mode > 0) && ($subsite_id == -1))
{
	mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."sitemeta` SET meta_value = '".mysqli_real_escape_string($dbh, $serial_plugin_list)."'  WHERE meta_key = 'active_sitewide_plugins' ");
}
else
{
	mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options` SET option_value = '".mysqli_real_escape_string($dbh, $serial_plugin_list)."'  WHERE option_name = 'active_plugins' ");
}
mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options` SET option_value = '".mysqli_real_escape_string($dbh, $_POST['url_new'])."'  WHERE option_name = 'home' ");
mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options` SET option_value = '".mysqli_real_escape_string($dbh, $_POST['siteurl'])."'  WHERE option_name = 'siteurl' ");
mysqli_query($dbh, "INSERT INTO `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options` (option_value, option_name) VALUES('".mysqli_real_escape_string($dbh, $_POST['exe_safe_mode'])."','duplicator_pro_exe_safe_mode')");
//Reset the postguid data
if ($_POST['postguid']) {
	mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."posts` SET guid = REPLACE(guid, '".mysqli_real_escape_string($dbh, $_POST['url_new'])."', '".mysqli_real_escape_string($dbh, $_POST['url_old'])."')");
	$update_guid = @mysqli_affected_rows($dbh) or 0;
	DUPX_Log::info("Reverted '{$update_guid}' post guid columns back to '{$_POST['url_old']}'");
}


$mu_updates = @mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."blogs` SET domain = '".mysqli_real_escape_string($dbh, $mu_newDomainHost)."' WHERE domain = '".mysqli_real_escape_string($dbh, $mu_oldDomainHost)."'");
if ($mu_updates) {
	DUPX_Log::info("- Update MU table blogs: domain {$mu_newDomainHost} ");
}

if ($GLOBALS['DUPX_AC']->mu_mode == 2) {
	// _blogs update path column to replace /oldpath/ with /newpath/ */
	$result = @mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."blogs` SET path = CONCAT('".mysqli_real_escape_string($dbh, $mu_newUrlPath)."', SUBSTRING(path, LENGTH('".mysqli_real_escape_string($dbh, $mu_oldUrlPath)."') + 1))");
	if ($result === false) {
		DUPX_Log::error("Update to blogs table failed\n".mysqli_error($dbh));
	}
}


if (($GLOBALS['DUPX_AC']->mu_mode == 1) || ($GLOBALS['DUPX_AC']->mu_mode == 2)) {
	// _site update path column to replace /oldpath/ with /newpath/ */
	$result = @mysqli_query($dbh, "UPDATE `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."site` SET path = CONCAT('".mysqli_real_escape_string($dbh, $mu_newUrlPath)."', SUBSTRING(path, LENGTH('".mysqli_real_escape_string($dbh, $mu_oldUrlPath)."') + 1)), domain = '".mysqli_real_escape_string($dbh, $mu_newDomainHost)."'");
	if ($result === false) {
		DUPX_Log::error("Update to site table failed\n".mysqli_error($dbh));
	}
}

//SCHEDULE STORAGE CLEANUP
if (($_POST['empty_schedule_storage']) == true || (DUPX_U::$on_php_53_plus == false)) {

	$dbdelete_count	 = 0;
	$dbdelete_count1 = 0;
	$dbdelete_count2 = 0;

	@mysqli_query($dbh, "DELETE FROM `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."duplicator_pro_entities` WHERE `type` = 'DUP_PRO_Storage_Entity'");
	$dbdelete_count1 = @mysqli_affected_rows($dbh);

	@mysqli_query($dbh, "DELETE FROM `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."duplicator_pro_entities` WHERE `type` = 'DUP_PRO_Schedule_Entity'");
	$dbdelete_count2 = @mysqli_affected_rows($dbh);

	$dbdelete_count = (abs($dbdelete_count1) + abs($dbdelete_count2));
	DUPX_Log::info("- Removed '{$dbdelete_count}' schedule storage items");
}

//===============================================
//NOTICES TESTS
//===============================================
DUPX_Log::info("\n====================================");
DUPX_Log::info("NOTICES");
DUPX_Log::info("====================================\n");

if (file_exists($wpconfig_ark_path)) {
    $wpconfig_ark_contents = file_get_contents($wpconfig_ark_path);
    $config_vars	= array('WPCACHEHOME', 'COOKIE_DOMAIN', 'WP_SITEURL', 'WP_HOME', 'WP_TEMP_DIR');
    $config_found	= DUPX_U::getListValues($config_vars, $wpconfig_ark_contents);

    //Files
    if (! empty($config_found)) {
        $msg   = "WP-CONFIG NOTICE: The wp-config.php has following values set [".implode(", ", $config_found)."].  \n";
        $msg  .= "Please validate these values are correct by opening the file and checking the values.\n";
        $msg  .= "See the codex link for more details: https://codex.wordpress.org/Editing_wp-config.php";
        // old system
        $JSON['step3']['warnlist'][] = $msg;
        DUPX_Log::info($msg);

        $nManager->addFinalReportNotice(array(
                'shortMsg' => 'wp-config notice',
                'level' => DUPX_NOTICE_ITEM::NOTICE,
                'longMsg' => $msg,
                'sections' => 'general'
            ));
    }

    //-- Finally, back up the old wp-config and rename the new one
    $wpconfig_path = "{$GLOBALS['DUPX_ROOT']}/wp-config.php";
    if (copy($wpconfig_ark_path, $wpconfig_path) === false) {
        DUPX_Log::error("ERROR: Unable to copy 'dup-wp-config-arc__[HASH].txt' to 'wp-config.php'.\n".
            "Check server permissions for more details see FAQ: https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-055-q");
    }

} else {
    $msg   = "WP-CONFIG NOTICE: <b>wp-config.php not found.</b><br><br>" ;
    $msg  .= "No action on the wp-config was possible.<br>";
    $msg  .= "Be sure to insert a properly modified wp-config for correct wordpress operation.";
    $JSON['step3']['warnlist'][] = $msg;

    $nManager->addFinalReportNotice(array(
            'shortMsg' => 'wp-config not found',
            'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
            'longMsg' => $msg,
            'sections' => 'general'
        ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_UPDATE , 'wp-config-not-found');

    DUPX_Log::info($msg);
}

//Database
$result = @mysqli_query($dbh, "SELECT option_value FROM `".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options` WHERE option_name IN ('upload_url_path','upload_path')");
if ($result) {
	while ($row = mysqli_fetch_row($result)) {
		if (strlen($row[0])) {
			$msg  = "MEDIA SETTINGS NOTICE: The table '".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options' has at least one the following values ['upload_url_path','upload_path'] \n";
			$msg .=	"set please validate settings. These settings can be changed in the wp-admin by going to /wp-admin/options.php'";
			$JSON['step3']['warnlist'][] = $msg;
			DUPX_Log::info($msg);

            $nManager->addFinalReportNotice(array(
                'shortMsg' => 'Media settings notice',
                'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg' => $msg,
                'sections' => 'general'
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_UPDATE , 'media-settings-notice');

			break;
		}
	}
}

$sql = "INSERT into ".mysqli_real_escape_string($dbh, $GLOBALS['DUPX_AC']->wp_tableprefix)."options (option_name, option_value) VALUES ('duplicator_pro_migration', '1');";
@mysqli_query($dbh, $sql);

if (empty($JSON['step3']['warnlist'])) {
	DUPX_Log::info("No General Notices Found\n");
}

$JSON['step3']['warn_all'] = empty($JSON['step3']['warnlist']) ? 0 : count($JSON['step3']['warnlist']);

mysqli_close($dbh);

//Cleanup any tmp files a developer may have forgotten about
//Lets be proactive for the developer just in case
$wpconfig_path_bak	= "{$GLOBALS['DUPX_ROOT']}/wp-config.bak";
$wpconfig_path_old	= "{$GLOBALS['DUPX_ROOT']}/wp-config.old";
$wpconfig_path_org	= "{$GLOBALS['DUPX_ROOT']}/wp-config.org";
$wpconfig_path_orig	= "{$GLOBALS['DUPX_ROOT']}/wp-config.orig";
$wpconfig_safe_check = array($wpconfig_path_bak, $wpconfig_path_old, $wpconfig_path_org, $wpconfig_path_orig);
foreach ($wpconfig_safe_check as $file) {
	if(file_exists($file)) {
		$tmp_newfile = $file . uniqid('_');
		if(rename($file, $tmp_newfile) === false) {
			DUPX_Log::info("WARNING: Unable to rename '{$file}' to '{$tmp_newfile}'");
		}
	}
}

if (isset($_POST['remove_redundant']) && $_POST['remove_redundant']) {		
	$licence_type = $GLOBALS['DUPX_AC']->getLicenseType();		
	if ($licence_type >= DUPX_LicenseType::Freelancer) {
		// Need to load if user selected redundant-data checkbox
		require_once($GLOBALS['DUPX_INIT'].'/classes/utilities/class.u.remove.redundant.data.php');

		$new_content_dir = (substr($_POST['path_new'], -1, 1) == '/') ? "{$_POST['path_new']}{$GLOBALS['DUPX_AC']->relative_content_dir}"
		: "{$_POST['path_new']}/{$GLOBALS['DUPX_AC']->relative_content_dir}";
		
		try {
			DUPX_Log::info("#### Recursively deleting redundant plugins");
			DUPX_RemoveRedundantData::deleteRedundantPlugins($new_content_dir, $GLOBALS['DUPX_AC'], $subsite_id);
		} catch (Exception $ex) {
			// Technically it can complete but this should be brought to their attention
			DUPX_Log::error("Problem deleting redundant plugins");
		}

		try {
			DUPX_Log::info("#### Recursively deleting redundant themes");
			DUPX_RemoveRedundantData::deleteRedundantThemes($new_content_dir, $GLOBALS['DUPX_AC'], $subsite_id);
		} catch (Exception $ex) {
			// Technically it can complete but this should be brought to their attention
			DUPX_Log::error("Problem deleting redundant themes");
		}
    }
    if ($GLOBALS['DUPX_STATE']->mode == DUPX_InstallerMode::OverwriteInstall) {
        DUPX_U::maintenanceMode(true, $GLOBALS['DUPX_ROOT']);
    }
}

$nManager->saveNotices();

$ajax3_sum = DUPX_U::elapsedTime(DUPX_U::getMicrotime(), $ajax3_start);
DUPX_Log::info("\nSTEP-3 COMPLETE @ ".@date('h:i:s')." - RUNTIME: {$ajax3_sum} \n\n");

$JSON['step3']['pass'] = 1;
// error_reporting($ajax3_error_level);
die(json_encode($JSON));
