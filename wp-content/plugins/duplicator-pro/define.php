<?php
defined("ABSPATH") or die("");

//Prevent directly browsing to the file
if (function_exists('plugin_dir_url')) {

    // For compatibility to an older WP
    if (!defined('KB_IN_BYTES'))  define('KB_IN_BYTES', 1024);
    if (!defined('MB_IN_BYTES'))  define('MB_IN_BYTES', 1024 * KB_IN_BYTES);
    if (!defined('GB_IN_BYTES'))  define('GB_IN_BYTES', 1024 * MB_IN_BYTES);

    define('DUPLICATOR_PRO_VERSION', '3.8.2');
    define('DUPLICATOR_PRO_LIMIT_UPLOAD_VERSION', '3.3.0.0'); // Limit Drag & Drop`
    define('DUPLICATOR_PRO_GIFT_THIS_RELEASE', false); // Display Gift - should be true for new features OR if we want them to fill out survey
    define('DUPLICATOR_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
    define('DUPLICATOR_PRO_SITE_URL', get_site_url());
    define('DUPLICATOR_PRO_IMG_URL', DUPLICATOR_PRO_PLUGIN_URL . '/assets/img');

    /* Paths should ALWAYS read "/"
      uni: /home/path/file.txt
      win:  D:/home/path/file.txt
      SSDIR = SnapShot Directory */
    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname(__FILE__));
    }

    //PATH CONSTANTS
    if (!defined('DUPLICATOR_PRO_WPROOTPATH')) {
        define('DUPLICATOR_PRO_WPROOTPATH', str_replace('\\', '/', ABSPATH));
    }
    if (!defined("DUPLICATOR_PRO_SSDIR_NAME")) define("DUPLICATOR_PRO_SSDIR_NAME", 'backups-dup-pro');
    define("DUPLICATOR_PRO_IMPORTS_DIR_NAME", 'imports');
    define('DUPLICATOR_PRO_PLUGIN_PATH', str_replace("\\", "/", plugin_dir_path(__FILE__)));
    define("DUPLICATOR_PRO_SSDIR_PATH", str_replace("\\", "/", WP_CONTENT_DIR . '/' . DUPLICATOR_PRO_SSDIR_NAME));
    define("DUPLICATOR_PRO_SSDIR_PATH_TMP", DUPLICATOR_PRO_SSDIR_PATH . '/tmp');
    define("DUPLICATOR_PRO_SSDIR_PATH_IMPORTS", DUPLICATOR_PRO_SSDIR_PATH . '/' . DUPLICATOR_PRO_IMPORTS_DIR_NAME);
    define("DUPLICATOR_PRO_SSDIR_PATH_INSTALLER", DUPLICATOR_PRO_SSDIR_PATH . '/installer');
    define("DUPLICATOR_PRO_SSDIR_URL", content_url() . "/" . DUPLICATOR_PRO_SSDIR_NAME);

    define("DUPLICATOR_PRO_INSTALL_PHP", 'installer.php');
    define("DUPLICATOR_PRO_IMPORT_INSTALLER_NAME", 'dpro-importinstaller.php');
    define("DUPLICATOR_PRO_IMPORT_INSTALLER_FILEPATH", DUPLICATOR_PRO_WPROOTPATH . DUPLICATOR_PRO_IMPORT_INSTALLER_NAME);
    define('DUPLICATOR_PRO_INSTALLER_HASH_PATTERN', '[a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9]-[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]');
    define("DUPLICATOR_PRO_IMPORT_INSTALLER_URL", DUPLICATOR_PRO_SITE_URL . '/' . DUPLICATOR_PRO_IMPORT_INSTALLER_NAME);
	define("DUPLICATOR_PRO_DUMP_PATH", DUPLICATOR_PRO_SSDIR_PATH.'/dump');
	define('DUPLICATOR_PRO_HTACCESS_ORIG_FILENAME', 'htaccess.orig');
	define('DUPLICATOR_PRO_WPCONFIG_ARK_FILENAME', 'wp-config-arc.txt');
	define("DUPLICATOR_PRO_ENHANCED_INSTALLER_DIRECTORY", DUPLICATOR_PRO_WPROOTPATH.'dup-installer');
    define('DUPLICATOR_PRO_LIB_PATH', DUPLICATOR_PRO_PLUGIN_PATH.'/lib');
    define('DUPLICATOR_PRO_CERT_PATH', apply_filters('duplicator_pro_certificate_path', DUPLICATOR_PRO_LIB_PATH.'/certificates/cacert.pem'));

    //RESTRAINT CONSTANTS
    define('DUPLICATOR_PRO_PHP_MAX_MEMORY', 4096 * MB_IN_BYTES);
    define("DUPLICATOR_PRO_DB_MAX_TIME", 5000);
    define("DUPLICATOR_PRO_DB_EOF_MARKER", 'DUPLICATOR_PRO_MYSQLDUMP_EOF');
    define("DUPLICATOR_PRO_SCAN_SITE_ZIP_ARCHIVE_WARNING_SIZE", 367001600); //350MB
    define("DUPLICATOR_PRO_SCAN_SITE_WARNING_SIZE", 1610612736); //1.5 GB

    define("DUPLICATOR_PRO_SCAN_WARNFILESIZE", 4194304); //4MB
    define("DUPLICATOR_PRO_SCAN_CACHESIZE", 1048576); //1MB
    define("DUPLICATOR_PRO_SCAN_DB_ALL_SIZE", 104857600); //100MB
    define("DUPLICATOR_PRO_SCAN_DB_ALL_ROWS", 1000000); //1 million rows
    define('DUPLICATOR_PRO_SCAN_DB_TBL_ROWS', 100000); //100K rows per table
    define('DUPLICATOR_PRO_SCAN_DB_TBL_SIZE', 10485760);  //10MB Table
    define("DUPLICATOR_PRO_SCAN_TIMEOUT", 25); //Seconds
    define("DUPLICATOR_PRO_BUFFER_READ_WRITE_SIZE", 4377);
    define('DUPLICATOR_PRO_PHP_BULK_SIZE', 524288);
    define('DUPLICATOR_PRO_SQL_SCRIPT_PHP_CODE_MULTI_THREADED_MAX_RETRIES', 6);
    define('DUPLICATOR_PRO_TEST_SQL_LOCK_NAME', 'duplicator_pro_test_lock');

    define("DUPLICATOR_PRO_SCAN_MIN_WP", "4.6.0");





    $GLOBALS['DUPLICATOR_PRO_SERVER_LIST'] = array('Apache', 'LiteSpeed', 'Nginx', 'Lighttpd', 'IIS', 'WebServerX', 'uWSGI');
    $GLOBALS['DUPLICATOR_PRO_OPTS_DELETE'] = array('duplicator_pro_ui_view_state', 'duplicator_pro_package_active', 'duplicator_pro_settings');

    //GLOBAL FILTERS: Prevent backups of non essential data
    // - To include a specific path just comment out the path
    // - Future plans to build UI around these settings
    $_dup_pro_upload_dir = wp_upload_dir();
    $_dup_pro_upload_dir = isset($_duplicator_pro_upload_dir['basedir']) ? basename($_duplicator_pro_upload_dir['basedir']) : 'uploads';
    $_dup_pro_wp_root = rtrim(DUPLICATOR_PRO_WPROOTPATH, '/');
    $_dup_pro_wp_content = str_replace("\\", "/", WP_CONTENT_DIR);
    $_dup_pro_wp_content_upload = "{$_dup_pro_wp_content}/{$_dup_pro_upload_dir}";
    $GLOBALS['DUPLICATOR_PRO_GLOBAL_FILE_FILTERS_ON'] = true;
    $GLOBALS['DUPLICATOR_PRO_GLOBAL_FILE_FILTERS'] = array(
        'error_log',
        'error.log',
        'debug_log',
        'ws_ftp.log',
        'dbcache',
        'pgcache',
        'objectcache',
		'.DS_Store'
    );

    $GLOBALS['DUPLICATOR_PRO_GLOBAL_DIR_FILTERS_ON'] = true;
    $GLOBALS['DUPLICATOR_PRO_GLOBAL_DIR_FILTERS'] = array(
        //WP-ROOT
        $_dup_pro_wp_root . '/wp-snapshots',
        //WP-CONTENT
        $_dup_pro_wp_content . '/ai1wm-backups',
        $_dup_pro_wp_content . '/backupwordpress',
        $_dup_pro_wp_content . '/content/cache',
        $_dup_pro_wp_content . '/contents/cache',
        $_dup_pro_wp_content . '/infinitewp/backups',
        $_dup_pro_wp_content . '/managewp/backups',
        $_dup_pro_wp_content . '/old-cache',
        $_dup_pro_wp_content . '/plugins/all-in-one-wp-migration/storage',
        $_dup_pro_wp_content . '/updraft',
        $_dup_pro_wp_content . '/wishlist-backup',
        $_dup_pro_wp_content . '/wfcache',
        $_dup_pro_wp_content . '/plugins/really-simple-captcha/tmp',
        $_dup_pro_wp_content . '/plugins/wordfence/tmp',
        $_dup_pro_wp_content . '/cache',
        //WP-CONTENT-UPLOADS
        $_dup_pro_wp_content_upload . '/aiowps_backups',
        $_dup_pro_wp_content_upload . '/backupbuddy_temp',
        $_dup_pro_wp_content_upload . '/backupbuddy_backups',
        $_dup_pro_wp_content_upload . '/ithemes-security/backups',
        $_dup_pro_wp_content_upload . '/mainwp/backup',
        $_dup_pro_wp_content_upload . '/pb_backupbuddy',
        $_dup_pro_wp_content_upload . '/snapshots',
        $_dup_pro_wp_content_upload . '/sucuri',
        $_dup_pro_wp_content_upload . '/wp-clone',
        $_dup_pro_wp_content_upload . '/wp_all_backup',
        $_dup_pro_wp_content_upload . '/wpbackitup_backups',
        $_dup_pro_wp_content_upload . '/backup-guard'
    );
} else {
    error_reporting(0);
    $port = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off") ? "https://" : "http://";
    $url = $port . $_SERVER["HTTP_HOST"];
    header("HTTP/1.1 404 Not Found", true, 404);
    header("Status: 404 Not Found");
    exit();
}
