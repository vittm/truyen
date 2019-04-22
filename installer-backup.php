<?php

if (!defined('KB_IN_BYTES')) { define('KB_IN_BYTES', 1024); }
if (!defined('MB_IN_BYTES')) { define('MB_IN_BYTES', 1024 * KB_IN_BYTES); }
if (!defined('GB_IN_BYTES')) { define('GB_IN_BYTES', 1024 * MB_IN_BYTES); }
if (!defined('DUPLICATOR_PHP_MAX_MEMORY')) { define('DUPLICATOR_PHP_MAX_MEMORY', 4096 * MB_IN_BYTES); }

date_default_timezone_set('UTC'); // Some machines don’t have this set so just do it here.
@ignore_user_abort(true);
@set_time_limit(3600);
@ini_set('memory_limit', DUPLICATOR_PHP_MAX_MEMORY);
@ini_set('max_input_time', '-1');
@ini_set('pcre.backtrack_limit', PHP_INT_MAX);
@ini_set('default_socket_timeout', 3600);

class DUPX_CSRF {
	
	/** Session var name
	 * @var string
	 */
	public static $prefix = '_DUPX_CSRF';
	
	/** Generate DUPX_CSRF value for form
	 * @param	string	$form	- Form name as session key
	 * @return	string	- token
	 */
	public static function generate($form = NULL) {
		if (!empty($_COOKIE[DUPX_CSRF::$prefix . '_' . $form])) {
			$token = $_COOKIE[DUPX_CSRF::$prefix . '_' . $form];
		} else {
            $token = DUPX_CSRF::token() . DUPX_CSRF::fingerprint();
		}
		$cookieName = DUPX_CSRF::$prefix . '_' . $form;
        $ret = DUPX_CSRF::setCookie($cookieName, $token);
		return $token;
	}
	
	/** Check DUPX_CSRF value of form
	 * @param	string	$token	- Token
	 * @param	string	$form	- Form name as session key
	 * @return	boolean
	 */
	public static function check($token, $form = NULL) {
		if (!self::isCookieEnabled()) {
			return true;
		}
		if (isset($_COOKIE[DUPX_CSRF::$prefix . '_' . $form]) && $_COOKIE[DUPX_CSRF::$prefix . '_' . $form] == $token) { // token OK
			return (substr($token, -32) == DUPX_CSRF::fingerprint()); // fingerprint OK?
		}
		return FALSE;
	}
	
	/** Generate token
	 * @param	void
	 * @return  string
	 */
	protected static function token() {
		mt_srand((double) microtime() * 10000);
		$charid = strtoupper(md5(uniqid(rand(), TRUE)));
		return substr($charid, 0, 8) . substr($charid, 8, 4) . substr($charid, 12, 4) . substr($charid, 16, 4) . substr($charid, 20, 12);
	}
	
	/** Returns "digital fingerprint" of user
	 * @param 	void
	 * @return 	string 	- MD5 hashed data
	 */
	protected static function fingerprint() {
		return strtoupper(md5(implode('|', array($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']))));
	}

	public static function setCookie($cookieName, $cookieVal) {
		$_COOKIE[$cookieName] = $cookieVal;
		return setcookie($cookieName, $cookieVal, time() + 10800, '/');
	}
	
	/**
	* @return bool
	*/
	protected static function isCookieEnabled() {
		return (count($_COOKIE) > 0);
	}

	public static function resetAllTokens() {
		foreach ($_COOKIE as $cookieName => $cookieVal) {
			if (0 === strpos($cookieName, DUPX_CSRF::$prefix) || 'archive' == $cookieName || 'bootloader' == $cookieName) {
				$baseUrl = self::getBaseUrl();
				setcookie($cookieName, '', time() - 86400, $baseUrl);	
			}
		}
		$_COOKIE = array();
	}

	private static function getBaseUrl() {
		// output: /myproject/index.php
		$currentPath = $_SERVER['PHP_SELF']; 
		
		// output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index ) 
		$pathInfo = pathinfo($currentPath); 
		
		// output: localhost
		$hostName = $_SERVER['HTTP_HOST']; 
		
		// output: http://
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
		
		// return: http://localhost/myproject/
		return $protocol.$hostName.$pathInfo['dirname']."/";
	}
}

/**
 * Bootstrap utility to exatract the core installer
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\Bootstrap
 * @link http://www.php-fig.org/psr/psr-2/
 *
 *  To force extraction mode:
 *		installer.php?unzipmode=auto
 *		installer.php?unzipmode=ziparchive
 *		installer.php?unzipmode=shellexec
 */

abstract class DUPX_Bootstrap_Zip_Mode
{
	const AutoUnzip		= 0;
	const ZipArchive	= 1;
	const ShellExec		= 2;
}

abstract class DUPX_Connectivity
{
	const OK		= 0;
	const Error		= 1;
	const Unknown	= 2;
}

class DUPX_Bootstrap
{
	//@@ Params get dynamically swapped when package is built
	const ARCHIVE_FILENAME	 = '20190422_truyenbanquyen_38b1958569fd772f2451_20190422012124_archive.daf';
	const ARCHIVE_SIZE		 = '1483805336';
	const INSTALLER_DIR_NAME = 'dup-installer';
	const PACKAGE_HASH		 = '38b1958-22012124';
	const VERSION			 = '3.8.2';

	public $hasZipArchive     = false;
	public $hasShellExecUnzip = false;
	public $mainInstallerURL;
	public $installerContentsPath;
	public $installerExtractPath;
	public $archiveExpectedSize = 0;
	public $archiveActualSize = 0;
	public $activeRatio = 0;

	/**
	 * Instantiate the Bootstrap Object
	 *
	 * @return null
	 */
	public function __construct()
	{
		//ARCHIVE_SIZE will be blank with a root filter so we can estimate
		//the default size of the package around 17.5MB (18088000)
		$archiveActualSize		        = @filesize(self::ARCHIVE_FILENAME);
		$archiveActualSize				= ($archiveActualSize !== false) ? $archiveActualSize : 0;
		$this->hasZipArchive			= class_exists('ZipArchive');
		$this->hasShellExecUnzip		= $this->getUnzipFilePath() != null ? true : false;
		$this->installerContentsPath	= str_replace("\\", '/', (dirname(__FILE__). '/' .self::INSTALLER_DIR_NAME));
		$this->installerExtractPath		= str_replace("\\", '/', (dirname(__FILE__)));
		$this->archiveExpectedSize      = strlen(self::ARCHIVE_SIZE) ?  self::ARCHIVE_SIZE : 0 ;
		$this->archiveActualSize        = $archiveActualSize;

        if($this->archiveExpectedSize > 0) {
            $this->archiveRatio			= (((1.0) * $this->archiveActualSize)  / $this->archiveExpectedSize) * 100;
        } else {
            $this->archiveRatio			= 100;
        }
	}

	/**
	 * Run the bootstrap process which includes checking for requirements and running
	 * the extraction process
	 *
	 * @return null | string	Returns null if the run was successful otherwise an error message
	 */
	public function run()
	{
		date_default_timezone_set('UTC'); // Some machines don't have this set so just do it here
		@unlink('./dup-installer-bootlog__'.self::PACKAGE_HASH.'.txt');
		self::log('==DUPLICATOR INSTALLER BOOTSTRAP v3.8.2==');
		self::log('----------------------------------------------------');
		self::log('Installer bootstrap start');

		$archive_filepath	 = $this->getArchiveFilePath();
		$archive_filename	 = self::ARCHIVE_FILENAME;

		$error					= null;
		$extract_installer		= true;
		$installer_directory	= dirname(__FILE__).'/'.self::INSTALLER_DIR_NAME;
		$extract_success		= false;
		$archiveExpectedEasy	= $this->readableByteSize($this->archiveExpectedSize);
		$archiveActualEasy		= $this->readableByteSize($this->archiveActualSize);

        //$archive_extension = strtolower(pathinfo($archive_filepath)['extension']);
        $archive_extension		= strtolower(pathinfo($archive_filepath, PATHINFO_EXTENSION));
		$manual_extract_found   = (
									file_exists($installer_directory."/main.installer.php")
									&&
									file_exists($installer_directory."/dup-archive__".self::PACKAGE_HASH.".txt")
									&&
									file_exists($installer_directory."/dup-database__".self::PACKAGE_HASH.".sql")
									);

        $isZip = ($archive_extension == 'zip');

		//MANUAL EXTRACTION NOT FOUND
		if (! $manual_extract_found) {

			//MISSING ARCHIVE FILE
			if (! file_exists($archive_filepath)) {
				self::log("ERROR: Archive file not found!");
				$archive_candidates = ($isZip) ? $this->getFilesWithExtension('zip') : $this->getFilesWithExtension('daf');
				$candidate_count = count($archive_candidates);
				$candidate_html  = "- No {$archive_extension} files found -";

				if ($candidate_count >= 1) {
					$candidate_html = "<ol>";
					foreach($archive_candidates as $archive_candidate) {
						$candidate_html .=  "<li> {$archive_candidate}</li>";
					}
				   $candidate_html .=  "</ol>";
				}

				$error  = "<b>Archive not found!</b> The <i>'Required File'</i> below should be present in the <i>'Extraction Path'</i>.  "
					. "The archive file name must be the <u>exact</u> name of the archive file placed in the extraction path character for character.<br/><br/>  "
					. "If the file does not have the correct name then rename it to the <i>'Required File'</i> below.   When downloading the package files make "
					. "sure both files are from the same package line in the packages view.  If the archive is not finished downloading please wait for it to complete.<br/><br/>"
					. "<b>Required File:</b>  <span class='file-info'>{$archive_filename}</span> <br/>"
					. "<b>Extraction Path:</b> <span class='file-info'>{$this->installerExtractPath}/</span><br/><br/>"
					. "Potential archives found at extraction path: <br/>{$candidate_html}<br/><br/>";

				return $error;
			}

			if (!filter_var(self::ARCHIVE_SIZE, FILTER_VALIDATE_INT) || self::ARCHIVE_SIZE > 2147483647) {
				
				$os_first_three_chars = substr(PHP_OS, 0, 3);
				$os_first_three_chars = strtoupper($os_first_three_chars);
				$no_of_bits = PHP_INT_SIZE * 8;

				if ($no_of_bits == 32) {
					if ($isZip) { // ZIP
						if ('WIN' === $os_first_three_chars) {
							$error = "This package is currently {$archiveExpectedEasy} and it's on a Windows OS. PHP on Windows does not support files larger than 2GB. Please use the file filters to get your package lower to support this server or try the package on a Linux server.";
							return $error;
						}
					} else { // DAF
						if ('WIN' === $os_first_three_chars) {
							$error  = 'Windows PHP limitations prevents extraction of archives larger than 2GB. Please do the following: <ol><li>Download and use the <a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-052-q">Windows DupArchive extractor</a> to extract all files from the archive.</li><li>Perform a <a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q">Manual Extract Install</a> starting at step 4.</li></ol>';
						} else 	{					
							$error  = 'This archive is too large for 32-bit PHP. Ask your host to upgrade the server to 64-bit PHP or install on another system has 64-bit PHP.';
						}
						return $error;
					}
				}
			}
			
			//SIZE CHECK ERROR
			if (($this->archiveRatio < 90) && ($this->archiveActualSize > 0) && ($this->archiveExpectedSize > 0)) {
				$this->log("ERROR: The expected archive size should be around [{$archiveExpectedEasy}].  The actual size is currently [{$archiveActualEasy}].");
				$this->log("The archive file may not have fully been downloaded to the server");
				$percent = round($this->archiveRatio);

				$autochecked = isset($_POST['auto-fresh']) ? "checked='true'" : '';
				$error  = "<b>Archive file size warning.</b><br/> The expected archive size should be around <b class='pass'>[{$archiveExpectedEasy}]</b>.  "
					. "The actual size is currently <b class='fail'>[{$archiveActualEasy}]</b>.  The archive file may not have fully been downloaded to the server.  "
					. "Please validate that the file sizes are close to the same size and that the file has been completely downloaded to the destination server.  If the archive is still "
					. "downloading then refresh this page to get an update on the download size.<br/><br/>";

				return $error;
			}

		}

		

		if ($manual_extract_found) {
			// INSTALL DIRECTORY: Check if its setup correctly AND we are not in overwrite mode
			if (isset($_GET['force-extract-installer']) && ('1' == $_GET['force-extract-installer'] || 'enable' == $_GET['force-extract-installer'] || 'false' == $_GET['force-extract-installer'])) {

				self::log("Manual extract found with force extract installer get parametr");
				$extract_installer = true;

			} else {
				$extract_installer = false;
				self::log("Manual extract found so not going to extract dup-installer dir");
			}
		} else {
			$extract_installer = true;
			self::log("Manual extract didn't found so going to extract dup-installer dir");
		}

		// if ($extract_installer && file_exists($installer_directory)) {
		if (file_exists($installer_directory)) {
			$hash_pattern = '[a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9]-[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]';
			$file_patterns_with_hash_file = array(
				// file pattern => hash file
				'dup-archive__'.$hash_pattern.'.txt' => 'dup-archive__'.self::PACKAGE_HASH.'.txt',
				'dup-database__'.$hash_pattern.'.sql' => 'dup-database__'.self::PACKAGE_HASH.'.sql',
				'dup-installer-data__'.$hash_pattern.'.sql' => 'dup-installer-data__'.self::PACKAGE_HASH.'.sql',
				'dup-installer-log__'.$hash_pattern.'.txt' => 'dup-installer-log__'.self::PACKAGE_HASH.'.txt',
				'dup-scan__'.$hash_pattern.'.json' => 'dup-scan__'.self::PACKAGE_HASH.'.json',
				'dup-scanned-dirs__'.$hash_pattern.'.txt' => 'dup-scanned-dirs__'.self::PACKAGE_HASH.'.txt',
				'dup-scanned-files__'.$hash_pattern.'.txt' => 'dup-scanned-files__'.self::PACKAGE_HASH.'.txt',
			);
			foreach ($file_patterns_with_hash_file as $file_pattern => $hash_file) {
				$globs = glob($installer_directory.'/'.$file_pattern);
				if (!empty($globs)) {
					foreach ($globs as $glob) {
						$file = basename($glob);
						if ($file != $hash_file) {
							if (unlink($glob)) {
								self::log('Successfully deleted the file '.$glob);
							} else {
								$error .= 'Error deleting the file '.$glob.'. Please manually delete it and try again.';
								self::log($error);
							}							
						}
					}
				}
			}
		}
		
		//ATTEMPT EXTRACTION:
		//ZipArchive and Shell Exec
		if ($extract_installer) {
			self::log("Ready to extract the installer");

			self::log("Checking permission of destination folder");
			$destination = dirname(__FILE__);
			if (!is_writable($destination)) {
				self::log("destination folder for extraction is not writable");
				if (@chmod($destination, 0755)) {
					self::log("Permission of destination folder changed to 0755");
				} else {
					self::log("Permission of destination folder failed to change to 0755");
				}
			}

			if (!is_writable($destination)) {
				$error	= "NOTICE: The {$destination} directory is not writable on this server please talk to your host or server admin about making ";
				$error	.= "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-055-q'>writable {$destination} directory</a> on this server. <br/>";
				return $error; 
			}

			if ($isZip) {
				$zip_mode = $this->getZipMode();

				if (($zip_mode == DUPX_Bootstrap_Zip_Mode::AutoUnzip) || ($zip_mode == DUPX_Bootstrap_Zip_Mode::ZipArchive) && class_exists('ZipArchive')) {
					if ($this->hasZipArchive) {
						self::log("ZipArchive exists so using that");
						$extract_success = $this->extractInstallerZipArchive($archive_filepath);

						if ($extract_success) {
							self::log('Successfully extracted with ZipArchive');
						} else {
							if (0 == $this->installer_files_found) {
								$error = 'This archive is not properly formatted and does not contain a dup-installer directory. Please make sure you are attempting to install the original archive and not one that has been reconstructed.';
								self::log($error);
								return $error;
							} else {
								$error = 'Error extracting with ZipArchive. ';
								self::log($error);
							}
						}
					} else {
						self::log("WARNING: ZipArchive is not enabled.");
						$error	 = "NOTICE: ZipArchive is not enabled on this server please talk to your host or server admin about enabling ";
						$error	 .= "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-060-q'>ZipArchive</a> on this server. <br/>";
					}
				}

				if (!$extract_success) {
					if (($zip_mode == DUPX_Bootstrap_Zip_Mode::AutoUnzip) || ($zip_mode == DUPX_Bootstrap_Zip_Mode::ShellExec)) {
						$unzip_filepath = $this->getUnzipFilePath();
						if ($unzip_filepath != null) {
							$extract_success = $this->extractInstallerShellexec($archive_filepath);
							if ($extract_success) {
								self::log('Successfully extracted with Shell Exec');
								$error = null;
							} else {
								$error .= 'Error extracting with Shell Exec. Please manually extract archive then choose Advanced > Manual Extract in installer.';
								self::log($error);
							}
						} else {
							self::log('WARNING: Shell Exec Zip is not available');
							$error	 .= "NOTICE: Shell Exec is not enabled on this server please talk to your host or server admin about enabling ";
							$error	 .= "<a target='_blank' href='http://php.net/manual/en/function.shell-exec.php'>Shell Exec</a> on this server or manually extract archive then choose Advanced > Manual Extract in installer.";
						}
					}
				}

				// If both ZipArchive and ShellZip are not available, Error message should be combined for both
				if (!$extract_success && $zip_mode == DUPX_Bootstrap_Zip_Mode::AutoUnzip) {
					$unzip_filepath = $this->getUnzipFilePath();
					if (!class_exists('ZipArchive') && empty($unzip_filepath)) {
						$error	 = "NOTICE: ZipArchive and Shell Exec are not enabled on this server please talk to your host or server admin about enabling ";
						$error	 .= "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-060-q'>ZipArchive</a> or <a target='_blank' href='http://php.net/manual/en/function.shell-exec.php'>Shell Exec</a> on this server or manually extract archive then choose Advanced > Manual Extract in installer.";	
					}
				}				
			} else {
				DupArchiveMiniExpander::init("DUPX_Bootstrap::log");
				try {
					DupArchiveMiniExpander::expandDirectory($archive_filepath, self::INSTALLER_DIR_NAME, dirname(__FILE__));
				} catch (Exception $ex) {
					self::log("Error expanding installer subdirectory:".$ex->getMessage());
					throw $ex;
				}
			}

			$is_apache = (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false);
			$is_nginx = (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false);

			$sapi_type = php_sapi_name();
			$php_ini_data = array(						
						'max_execution_time' => 3600,
						'max_input_time' => -1,
						'ignore_user_abort' => 'On',
						'post_max_size' => '4096M',
						'upload_max_filesize' => '4096M',
						'memory_limit' => DUPLICATOR_PHP_MAX_MEMORY,
						'default_socket_timeout' => 3600,
						'pcre.backtrack_limit' => 99999999999,
					);
			$sapi_type_first_three_chars = substr($sapi_type, 0, 3);
			if ('fpm' === $sapi_type_first_three_chars) {
				self::log("SAPI: FPM");
				if ($is_apache) {
					self::log('Server: Apache');
				} elseif ($is_nginx) {
					self::log('Server: Nginx');
				}

				if (($is_apache && function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) || $is_nginx) {
					$htaccess_data = array();
					foreach ($php_ini_data as $php_ini_key=>$php_ini_val) {
						if ($is_apache) {
							$htaccess_data[] = 'SetEnv PHP_VALUE "'.$php_ini_key.' = '.$php_ini_val.'"';
						} elseif ($is_nginx) {
							if ('On' == $php_ini_val || 'Off' == $php_ini_val) {
								$htaccess_data[] = 'php_flag '.$php_ini_key.' '.$php_ini_val;
							} else {
								$htaccess_data[] = 'php_value '.$php_ini_key.' '.$php_ini_val;
							}							
						}
					}				
				
					$htaccess_text = implode("\n", $htaccess_data);
					$htaccess_file_path = dirname(__FILE__).'/dup-installer/.htaccess';
					self::log("creating {$htaccess_file_path} with the content:");
					self::log($htaccess_text);
					@file_put_contents($htaccess_file_path, $htaccess_text);
				}
			} elseif ('cgi' === $sapi_type_first_three_chars || 'litespeed' === $sapi_type) {
				if ('cgi' === $sapi_type_first_three_chars) {
					self::log("SAPI: CGI");
				} else {
					self::log("SAPI: litespeed");
				}
				if (version_compare(phpversion(), 5.5) >= 0 && (!$is_apache || 'litespeed' === $sapi_type)) {
					$ini_data = array();
					foreach ($php_ini_data as $php_ini_key=>$php_ini_val) {
						$ini_data[] = $php_ini_key.' = '.$php_ini_val;
					}
					$ini_text = implode("\n", $ini_data);
					$ini_file_path = dirname(__FILE__).'/dup-installer/.user.ini';
					self::log("creating {$ini_file_path} with the content:");
					self::log($ini_text);
					@file_put_contents($ini_file_path, $ini_text);
				} else{
					self::log("No need to create dup-installer/.htaccess or dup-installer/.user.ini");
				}
			} else {
				self::log("No need to create dup-installer/.htaccess or dup-installer/.user.ini");
				self::log("SAPI: Unrecognized");
			}
		} else {
			self::log("Didn't need to extract the installer.");
		}

		if (empty($error)) {
			$config_files = glob('./dup-installer/dup-archive__*.txt');
			$config_file_absolute_path = array_pop($config_files);
			if (!file_exists($config_file_absolute_path)) {
				$error = 'NOTICE: Archive config file not found in dup-installer folder. Please ensure that your archive file is valid.</b>';
			}
		}
		
		$is_https = $this->isHttps();

		if($is_https) {
			$current_url = 'https://';
		} else {
			$current_url = 'http://';
		}

		if(($_SERVER['SERVER_PORT'] == 80) && ($is_https)) {
			// Fixing what appears to be a bad server setting
			$server_port = 443;
		} else {
			$server_port = $_SERVER['SERVER_PORT'];
		}


		//$current_url .= $_SERVER['HTTP_HOST'];//WAS SERVER_NAME and caused problems on some boxes
		$current_url .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];//WAS SERVER_NAME and caused problems on some boxes
		if(strpos($current_url,':') === false) {
                   $current_url = $current_url.':'.$server_port;
                }
                
        if (!isset($_SERVER['REQUEST_URI']))  {

            $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 0);

            if (isset($_SERVER['QUERY_STRING']) AND $_SERVER['QUERY_STRING'] != "") {

                $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
            }
        }

		$current_url .= $_SERVER['REQUEST_URI'];
		$uri_start    = dirname($current_url);

		if ($error === null) {
                 
                if(!file_exists($installer_directory)) {
        
                    $error = 'Can\'t extract installer directory. See <a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-022-q">this FAQ item</a> for details on how to resolve.</a>';
                }
                
                    if($error == null) {

                        $bootloader_name	 = basename(__FILE__);
                        $this->mainInstallerURL = $uri_start.'/'.self::INSTALLER_DIR_NAME.'/main.installer.php';

						$this->archive = $archive_filepath;
						$this->bootloader = $bootloader_name;

                        $this->fixInstallerPerms($this->mainInstallerURL);
                        // $this->mainInstallerURL = $this->mainInstallerURL . "?archive=$encoded_archive_path&bootloader=$bootloader_name";

                        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                            $this->mainInstallerURL .= '?'.$_SERVER['QUERY_STRING'];
                        }

                        self::log("No detected errors so redirecting to the main installer. Main Installer URI = {$this->mainInstallerURL}");
                    }
                }

		return $error;
	}
        
	/**
     * Indicates if site is running https or not
     *
     * @return bool  Returns true if https, false if not
     */
	public function isHttps()
	{
		$retVal = true;

		if (isset($_SERVER['HTTPS'])) {
			$retVal = ($_SERVER['HTTPS'] !== 'off');
		} else {
			$retVal = ($_SERVER['SERVER_PORT'] == 443);
            }

        // nginx
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			$retVal = ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
		}

        
		return $retVal;
	}

	/**
     *  Attempts to set the 'dup-installer' directory permissions
     *
     * @return null
     */
	private function fixInstallerPerms()
	{
		$file_perms = substr(sprintf('%o', fileperms(__FILE__)), -4);
		$file_perms = octdec($file_perms);
		//$dir_perms = substr(sprintf('%o', fileperms(dirname(__FILE__))), -4);

		// No longer using existing directory permissions since that can cause problems.  Just set it to 755
		$dir_perms = '755';
		$dir_perms = octdec($dir_perms);
		$installer_dir_path = $this->installerContentsPath;

		$this->setPerms($installer_dir_path, $dir_perms, false);
		$this->setPerms($installer_dir_path, $file_perms, true);
	}

	/**
     * Set the permissions of a given directory and optionally all files
     *
     * @param string $directory		The full path to the directory where perms will be set
     * @param string $perms			The given permission sets to use such as '0755'
	 * @param string $do_files		Also set the permissions of all the files in the directory
     *
     * @return null
     */
	private function setPerms($directory, $perms, $do_files)
	{
		if (!$do_files) {
			// If setting a directory hiearchy be sure to include the base directory
			$this->setPermsOnItem($directory, $perms);
		}

		$item_names = array_diff(scandir($directory), array('.', '..'));

		foreach ($item_names as $item_name) {
			$path = "$directory/$item_name";
			if (($do_files && is_file($path)) || (!$do_files && !is_file($path))) {
				$this->setPermsOnItem($path, $perms);
			}
		}
	}

	/**
     * Set the permissions of a single directory or file
     *
     * @param string $path			The full path to the directory or file where perms will be set
     * @param string $perms			The given permission sets to use such as '0755'
     *
     * @return bool		Returns true if the permission was properly set
     */
	private function setPermsOnItem($path, $perms)
	{
		$result = @chmod($path, $perms);
		$perms_display = decoct($perms);
		if ($result === false) {
			self::log("Couldn't set permissions of $path to {$perms_display}<br/>");
		} else {
			self::log("Set permissions of $path to {$perms_display}<br/>");
		}
		return $result;
	}


	/**
     * Logs a string to the dup-installer-bootlog__[HASH].txt file
     *
     * @param string $s			The string to log to the log file
     *
     * @return null
     */
	public static function log($s)
	{
		$timestamp = date('M j H:i:s');
		file_put_contents('./dup-installer-bootlog__'.self::PACKAGE_HASH.'.txt', "$timestamp $s\n", FILE_APPEND);
	}

	/**
     * Extracts only the 'dup-installer' files using ZipArchive
     *
     * @param string $archive_filepath	The path to the archive file.
     *
     * @return bool		Returns true if the data was properly extracted
     */
	private function extractInstallerZipArchive($archive_filepath, $checkSubFolder = false)
	{
		$success	 = true;
		$zipArchive	 = new ZipArchive();
		$subFolderArchiveList   = array();

		if ($zipArchive->open($archive_filepath) === true) {
			self::log("Successfully opened $archive_filepath");
			$destination = dirname(__FILE__);
			$folder_prefix = self::INSTALLER_DIR_NAME.'/';
			self::log("Extracting all files from archive within ".self::INSTALLER_DIR_NAME);

			$installer_files_found = 0;

			for ($i = 0; $i < $zipArchive->numFiles; $i++) {
				$stat		 = $zipArchive->statIndex($i);
				if ($checkSubFolder == false) {
					$filenameCheck = $stat['name'];
					$filename = $stat['name'];
                    $tmpSubFolder = null;
				} else {
					$safePath = rtrim(self::setSafePath($stat['name']) , '/');
					$tmpArray = explode('/' , $safePath);
					
					if (count($tmpArray) < 2)  {
						continue;
					}

					$tmpSubFolder = $tmpArray[0];
					array_shift($tmpArray);
					$filenameCheck = implode('/' , $tmpArray);
					$filename = $stat['name'];
				}

				
				if ($this->startsWith($filenameCheck , $folder_prefix)) {
					$installer_files_found ++;

					if (!empty($tmpSubFolder) && !in_array($tmpSubFolder , $subFolderArchiveList)) {
						$subFolderArchiveList[] = $tmpSubFolder;
					}

					if ($zipArchive->extractTo($destination, $filename) === true) {
						self::log("Success: {$filename} >>> {$destination}");
					} else {
						self::log("Error extracting {$filename} from archive archive file");
						$success = false;
						break;
					}
				}
			}

			if ($checkSubFolder && count($subFolderArchiveList) !== 1) {
				self::log("Error: Multiple dup subfolder archive");
				$success = false;			
			} else {
				if ($checkSubFolder) {
					$this->moveUpfromSubFolder(dirname(__FILE__).'/'.$subFolderArchiveList[0] , true);
				}

			    $lib_directory = dirname(__FILE__).'/'.self::INSTALLER_DIR_NAME.'/lib';
			    $snaplib_directory = $lib_directory.'/snaplib';

			    // If snaplib files aren't present attempt to extract and copy those
			    if(!file_exists($snaplib_directory))
			    {
				$folder_prefix = 'snaplib/';
				$destination = $lib_directory;

				for ($i = 0; $i < $zipArchive->numFiles; $i++) {
				    $stat		 = $zipArchive->statIndex($i);
				    $filename	 = $stat['name'];

				    if ($this->startsWith($filename, $folder_prefix)) {
				        $installer_files_found++;

				        if ($zipArchive->extractTo($destination, $filename) === true) {
				            self::log("Success: {$filename} >>> {$destination}");
				        } else {
				            self::log("Error extracting {$filename} from archive archive file");
				            $success = false;
				            break;
				        }
				    }
				}
			    }
			}

			if ($zipArchive->close() === true) {
				self::log("Successfully closed archive file");
			} else {
				self::log("Problem closing archive file");
				$success = false;
			}
			
			if ($success != false && $installer_files_found < 10) {
				if ($checkSubFolder) {
					self::log("Couldn't find the installer directory in the archive!");
					$success = false;
				} else {
					self::log("Couldn't find the installer directory in archive root! Check subfolder");
					$this->extractInstallerZipArchive($archive_filepath, true);
				}
			}
		} else {
			self::log("Couldn't open archive archive file with ZipArchive");
			$success = false;
		}

		return $success;
	}

    /**
     * move all folder content up to parent
     *
     * @param string $subFolderName full path
     * @param boolean $deleteSubFolder if true delete subFolder after moved all
     * @return boolean
     * 
     */
    private function moveUpfromSubFolder($subFolderName, $deleteSubFolder = false)
    {
        if (!is_dir($subFolderName)) {
            return false;
        }

        $parentFolder = dirname($subFolderName);
        if (!is_writable($parentFolder)) {
            return false;
        }

        $success = true;
        if (($subList = glob(rtrim($subFolderName, '/').'/*', GLOB_NOSORT)) === false) {
            self::log("Problem glob folder ".$subFolderName);
            return false;
        } else {
            foreach ($subList as $cName) {
                $destination = $parentFolder.'/'.basename($cName);
                if (file_exists($destination)) {
                    $success = self::deletePath($destination);
                }

                if ($success) {
                    $success = rename($cName, $destination);
                } else {
                    break;
                }
            }

            if ($success && $deleteSubFolder) {
                $success = self::deleteDirectory($subFolderName, true);
            }
        }

        if (!$success) {
            self::log("Problem om moveUpfromSubFolder subFolder:".$subFolderName);
        }

        return $success;
    }

	/**
     * Extracts only the 'dup-installer' files using Shell-Exec Unzip
     *
     * @param string $archive_filepath	The path to the archive file.
     *
     * @return bool		Returns true if the data was properly extracted
     */
	private function extractInstallerShellexec($archive_filepath)
	{
		$success = false;
		self::log("Attempting to use Shell Exec");
		$unzip_filepath	 = $this->getUnzipFilePath();

		if ($unzip_filepath != null) {
			$unzip_command	 = "$unzip_filepath -q $archive_filepath ".self::INSTALLER_DIR_NAME.'/* 2>&1';
			self::log("Executing $unzip_command");
			$stderr	 = shell_exec($unzip_command);

            $lib_directory = dirname(__FILE__).'/'.self::INSTALLER_DIR_NAME.'/lib';
            $snaplib_directory = $lib_directory.'/snaplib';

            // If snaplib files aren't present attempt to extract and copy those
            if(!file_exists($snaplib_directory))
            {
                $local_lib_directory = dirname(__FILE__).'/snaplib';
                $unzip_command	 = "$unzip_filepath -q $archive_filepath snaplib/* 2>&1";
                self::log("Executing $unzip_command");
                $stderr	 .= shell_exec($unzip_command);
				mkdir($lib_directory);
                rename($local_lib_directory, $snaplib_directory);
            }

			if ($stderr == '') {
				self::log("Shell exec unzip succeeded");
				$success = true;
			} else {
				self::log("Shell exec unzip failed. Output={$stderr}");
			}
		}

		return $success;
	}

	/**
     * Attempts to get the archive file path
     *
     * @return string	The full path to the archive file
     */
	private function getArchiveFilePath()
	{
		if (isset($_GET['archive'])) {
			$archive_filepath = $_GET['archive'];
		} else {
		$archive_filename = self::ARCHIVE_FILENAME;
			$archive_filepath = str_replace("\\", '/', dirname(__FILE__) . '/' . $archive_filename);
		}

		self::log("Using archive $archive_filepath");
		return $archive_filepath;
	}

	/**
     * Gets the DUPX_Bootstrap_Zip_Mode enum type that should be used
     *
     * @return DUPX_Bootstrap_Zip_Mode	Returns the current mode of the bootstrapper
     */
	private function getZipMode()
	{
		$zip_mode = DUPX_Bootstrap_Zip_Mode::AutoUnzip;

		if (isset($_GET['zipmode'])) {
			$zipmode_string = $_GET['zipmode'];
			self::log("Unzip mode specified in querystring: $zipmode_string");

			switch ($zipmode_string) {
				case 'autounzip':
					$zip_mode = DUPX_Bootstrap_Zip_Mode::AutoUnzip;
					break;

				case 'ziparchive':
					$zip_mode = DUPX_Bootstrap_Zip_Mode::ZipArchive;
					break;

				case 'shellexec':
					$zip_mode = DUPX_Bootstrap_Zip_Mode::ShellExec;
					break;
			}
		}

		return $zip_mode;
	}

	/**
     * Checks to see if a string starts with specific characters
     *
     * @return bool		Returns true if the string starts with a specific format
     */
	private function startsWith($haystack, $needle)
	{
		return $needle === "" || strrpos($haystack, $needle, - strlen($haystack)) !== false;
	}

	/**
     * Checks to see if the server supports issuing commands to shell_exex
     *
     * @return bool		Returns true shell_exec can be ran on this server
     */
	public function hasShellExec()
	{
		$cmds = array('shell_exec', 'escapeshellarg', 'escapeshellcmd', 'extension_loaded');

		//Function disabled at server level
		if (array_intersect($cmds, array_map('trim', explode(',', @ini_get('disable_functions'))))) return false;

		//Suhosin: http://www.hardened-php.net/suhosin/
		//Will cause PHP to silently fail
		if (extension_loaded('suhosin')) {
			$suhosin_ini = @ini_get("suhosin.executor.func.blacklist");
			if (array_intersect($cmds, array_map('trim', explode(',', $suhosin_ini)))) return false;
		}
		// Can we issue a simple echo command?
		if (!@shell_exec('echo duplicator')) return false;

		return true;
	}

	/**
     * Gets the possible system commands for unzip on Linux
     *
     * @return string		Returns unzip file path that can execute the unzip command
     */
	public function getUnzipFilePath()
	{
		$filepath = null;

		if ($this->hasShellExec()) {
			if (shell_exec('hash unzip 2>&1') == NULL) {
				$filepath = 'unzip';
			} else {
				$possible_paths = array(
					'/usr/bin/unzip',
					'/opt/local/bin/unzip',
					'/bin/unzip',
					'/usr/local/bin/unzip',
					'/usr/sfw/bin/unzip',
					'/usr/xdg4/bin/unzip',
					'/opt/bin/unzip',					
					// RSR TODO put back in when we support shellexec on windows,
				);

				foreach ($possible_paths as $path) {
					if (file_exists($path)) {
						$filepath = $path;
						break;
					}
				}
			}
		}

		return $filepath;
	}

	/**
	 * Display human readable byte sizes such as 150MB
	 *
	 * @param int $size		The size in bytes
	 *
	 * @return string A readable byte size format such as 100MB
	 */
	public function readableByteSize($size)
	{
		try {
			$units = array('B', 'KB', 'MB', 'GB', 'TB');
			for ($i = 0; $size >= 1024 && $i < 4; $i++)
				$size /= 1024;
			return round($size, 2).$units[$i];
		} catch (Exception $e) {
			return "n/a";
		}
	}

	/**
     *  Returns an array of zip files found in the current executing directory
     *
     *  @return array of zip files
     */
    public static function getFilesWithExtension($extension)
    {
        $files = array();
        foreach (glob("*.{$extension}") as $name) {
            if (file_exists($name)) {
                $files[] = $name;
            }
        }

        if (count($files) > 0) {
            return $files;
        }

        //FALL BACK: Windows XP has bug with glob,
        //add secondary check for PHP lameness
        if ($dh = opendir('.')) {
            while (false !== ($name = readdir($dh))) {
                $ext = substr($name, strrpos($name, '.') + 1);
                if (in_array($ext, array($extension))) {
                    $files[] = $name;
                }
            }
            closedir($dh);
        }

        return $files;
    }
    
	/**
     * Safely remove a directory and recursively if needed
     *
     * @param string $directory The full path to the directory to remove
     * @param string $recursive recursively remove all items
     *
     * @return bool Returns true if all content was removed
     */
    public static function deleteDirectory($directory, $recursive)
    {
        $success = true;

        $filenames = array_diff(scandir($directory), array('.', '..'));

        foreach ($filenames as $filename) {
            $fullPath = $directory.'/'.$filename;

            if (is_dir($fullPath)) {
                if ($recursive) {
                    $success = self::deleteDirectory($fullPath, true);
                }
            } else {
                $success = @unlink($fullPath);
                if ($success === false) {
                    self::log( __FUNCTION__.": Problem deleting file:".$fullPath);
                }
            }

            if ($success === false) {
                self::log("Problem deleting dir:".$directory);
                break;
            }
        }

        return $success && rmdir($directory);
    }

    /**
     * Safely remove a file or directory and recursively if needed
     *
     * @param string $directory The full path to the directory to remove
     *
     * @return bool Returns true if all content was removed
     */
    public static function deletePath($path)
    {
        $success = true;

        if (is_dir($path)) {
            $success = self::deleteDirectory($path, true);
        } else {
            $success = @unlink($path);

            if ($success === false) {
                self::log( __FUNCTION__.": Problem deleting file:".$path);
            }
        }

        return $success;
    }
    
    /**
	 *  Makes path safe for any OS for PHP
	 *
	 *  Paths should ALWAYS READ be "/"
	 * 		uni:  /home/path/file.txt
	 * 		win:  D:/home/path/file.txt
	 *
	 *  @param string $path		The path to make safe
	 *
	 *  @return string The original $path with a with all slashes facing '/'.
	 */
	public static function setSafePath($path)
	{
		return str_replace("\\", "/", $path);
	}
}

try {
    $boot  = new DUPX_Bootstrap();
    $boot_error = $boot->run();
    $auto_refresh = isset($_POST['auto-fresh']) ? true : false;
    DUPX_CSRF::resetAllTokens();
} catch (Exception $e) {
   $boot_error = $e->getMessage();
}

if ($boot_error == null) {
	$secure_csrf_token = DUPX_CSRF::generate('secure');
	DUPX_CSRF::setCookie('archive', $boot->archive);
	DUPX_CSRF::setCookie('bootloader', $boot->bootloader);
}
?>

<html>
<?php if ($boot_error == null) :?>

	<head>
		<meta name="robots" content="noindex,nofollow">
		<title>Duplicator Pro Installer</title>
	</head>
	<body>
		<?php
		$id = uniqid();
		$html = "<form id='{$id}' method='post' action='{$boot->mainInstallerURL}' />\n";
		$data = array(
			'archive' => $boot->archive,
			'bootloader' => $boot->bootloader,
			'csrf_token' => $secure_csrf_token,
		);
		foreach ($data as $name => $value) {
			if ('csrf_token' != $name)  $_SESSION[$name] = $value;
			$html .= "<input type='hidden' name='{$name}' value='{$value}' />\n";
		}
		$html .= "</form>\n";
		$html .= "<script>window.onload = function() { document.getElementById('{$id}').submit(); }</script>";
		echo $html;
		?>
	</body>
<?php else :?>

	<head>
		<style>
			body {font-family:Verdana,Arial,sans-serif; line-height:18px; font-size: 12px}
			h2 {font-size:20px; margin:5px 0 5px 0; border-bottom:1px solid #dfdfdf; padding:3px}
			div#content {border:1px solid #CDCDCD; width:750px; min-height:550px; margin:auto; margin-top:18px; border-radius:5px; box-shadow:0 8px 6px -6px #333; font-size:13px}
			div#content-inner {padding:10px 30px; min-height:550px}

			/* Header */
			table.header-wizard {border-top-left-radius:5px; border-top-right-radius:5px; width:100%; box-shadow:0 5px 3px -3px #999; background-color:#F1F1F1; font-weight:bold}
			table.header-wizard td.header {font-size:24px; padding:7px 0 7px 0; width:100%;}
			div.dupx-logfile-link {float:right; font-weight:normal; font-size:12px}
			.dupx-version {white-space:nowrap; color:#999; font-size:11px; font-style:italic; text-align:right;  padding:0 15px 5px 0; line-height:14px; font-weight:normal}
			.dupx-version a { color:#999; }

			div.errror-notice {text-align:center; font-style:italic; font-size:11px}
			div.errror-msg { color:maroon; padding: 10px 0 5px 0}
			.pass {color:green}
			.fail {color:red}
			span.file-info {font-size: 11px; font-style: italic}
			div.skip-not-found {padding:10px 0 5px 0;}
			div.skip-not-found label {cursor: pointer}
			table.settings {width:100%; font-size:12px}
			table.settings td {padding: 4px}
			table.settings td:first-child {font-weight: bold}
			.w3-light-grey,.w3-hover-light-grey:hover,.w3-light-gray,.w3-hover-light-gray:hover{color:#000!important;background-color:#f1f1f1!important}
			.w3-container:after,.w3-container:before,.w3-panel:after,.w3-panel:before,.w3-row:after,.w3-row:before,.w3-row-padding:after,.w3-row-padding:before,
			.w3-cell-row:before,.w3-cell-row:after,.w3-clear:after,.w3-clear:before,.w3-bar:before,.w3-bar:after
			{content:"";display:table;clear:both}
			.w3-green,.w3-hover-green:hover{color:#fff!important;background-color:#4CAF50!important}
			.w3-container{padding:0.01em 16px}
			.w3-center{display:inline-block;width:auto; text-align: center !important}
		</style>
	</head>
	<body>
	<div id="content">

		<table cellspacing="0" class="header-wizard">
			<tr>
				<td class="header"> &nbsp; Duplicator Pro - Bootloader</div</td>
				<td class="dupx-version">
					version: <?php echo htmlentities(DUPX_Bootstrap::VERSION); ?> <br/>
					&raquo; <a target='_blank' href='dup-installer-bootlog__<?php echo DUPX_Bootstrap::PACKAGE_HASH; ?>.txt'>dup-installer-bootlog__[HASH].txt</a>
				</td>
			</tr>
		</table>

		<form id="error-form" method="post">
		<div id="content-inner">
			<h2 style="color:maroon">Setup Notice:</h2>
			<div class="errror-notice">An error has occurred. In order to load the full installer please resolve the issue below.</div>
			<div class="errror-msg">
				<?php echo $boot_error ?>
			</div>
			<br/><br/>

			<h2>Server Settings:</h2>
			<table class='settings'>
				<tr>
					<td>ZipArchive:</td>
					<td><?php echo $boot->hasZipArchive  ? '<i class="pass">Enabled</i>' : '<i class="fail">Disabled</i>'; ?> </td>
				</tr>
				<tr>
					<td>ShellExec&nbsp;Unzip:</td>
					<td><?php echo $boot->hasShellExecUnzip	? '<i class="pass">Enabled</i>' : '<i class="fail">Disabled</i>'; ?> </td>
				</tr>
				<tr>
					<td>Extraction&nbsp;Path:</td>
					<td><?php echo $boot->installerExtractPath; ?></td>
				</tr>
				<tr>
					<td>Installer Path:</td>
					<td><?php echo $boot->installerContentsPath; ?></td>
				</tr>
				<tr>
					<td>Archive Name:</td>
					<td><?php echo DUPX_Bootstrap::ARCHIVE_FILENAME  ?></td>
				</tr>
				<tr>
					<td>Archive Size:</td>
					<td>
						<b>Expected Size:</b> <?php echo $boot->readableByteSize($boot->archiveExpectedSize); ?>  &nbsp;
						<b>Actual Size:</b>   <?php echo $boot->readableByteSize($boot->archiveActualSize); ?>
					</td>
				</tr>
				<tr>
					<td>Boot Log</td>
					<td><a target='_blank' href='dup-installer-bootlog__<?php echo DUPX_Bootstrap::PACKAGE_HASH; ?>.txt'>dup-installer-bootlog__[HASH].txt</a></td>
				</tr>
			</table>
			<br/><br/>

			<div style="font-size:11px">
				Please Note: Either ZipArchive or Shell Exec will need to be enabled for the installer to run automatically otherwise a manual extraction
				will need to be performed.  In order to run the installer manually follow the instructions to
				<a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q' target='_blank'>manually extract</a> before running the installer.
			</div>
			<br/><br/>

		</div>
		</form>

	</div>
	</body>

	<script>
		function AutoFresh() {
			document.getElementById('error-form').submit();
		}
		<?php if ($auto_refresh) :?>
			var duration = 10000; //10 seconds
			var counter  = 10;
			var countElement = document.getElementById('count-down');

			setTimeout(function(){window.location.reload(1);}, duration);
			setInterval(function() {
				counter--;
				countElement.innerHTML = (counter > 0) ? counter.toString() : "0";
			}, 1000);

		<?php endif; ?>
	</script>


<?php endif; ?>


<?php
if (!defined("ABSPATH") && !defined("DUPXABSPATH"))
    die("");
//---------- DUPARCHIVE MINI EXPANDER: The contents of this file will be injected into the installer bootlog at build time ------------------------

class DupArchiveHeaderMiniU
{
    const MaxStandardHeaderFieldLength = 128;

    public static function readStandardHeaderField($archiveHandle, $ename)
    {
        $expectedStart = "<{$ename}>";
        $expectedEnd = "</{$ename}>";

        $startingElement = fread($archiveHandle, strlen($expectedStart));

        if($startingElement !== $expectedStart) {
            throw new Exception("Invalid starting element. Was expecting {$expectedStart} but got {$startingElement}");
        }

        return stream_get_line($archiveHandle, self::MaxStandardHeaderFieldLength, $expectedEnd);
    }
}

class DupArchiveMiniItemHeaderType
{
    const None      = 0;
    const File      = 1;
    const Directory = 2;
    const Glob      = 3;
}

class DupArchiveMiniFileHeader
{
    public $fileSize;
    public $mtime;
    public $permissions;
    public $hash;
    public $relativePathLength;
    public $relativePath;

    static function readFromArchive($archiveHandle)
    {
        $instance = new DupArchiveMiniFileHeader();

        $instance->fileSize           = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'FS');
        $instance->mtime              = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'MT');
        $instance->permissions        = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'P');
        $instance->hash                = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'HA');
        $instance->relativePathLength = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'RPL');

        // Skip <RP>
        fread($archiveHandle, 4);

        $instance->relativePath       = fread($archiveHandle, $instance->relativePathLength);

        // Skip </RP>
        fread($archiveHandle, 5);

        // Skip the #F!
        //fread($archiveHandle, 3);
        // Skip the </F>
        fread($archiveHandle, 4);

        return $instance;
    }
}

class DupArchiveMiniDirectoryHeader
{
    public $mtime;
    public $permissions;
    public $relativePathLength;
    public $relativePath;

   // const MaxHeaderSize                = 8192;
   // const MaxStandardHeaderFieldLength = 128;

    static function readFromArchive($archiveHandle)
    {
        $instance = new DupArchiveMiniDirectoryHeader();

        $instance->mtime              = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'MT');
        $instance->permissions        = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'P');
        $instance->relativePathLength = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'RPL');

        // Skip the <RP>
        fread($archiveHandle, 4);

        $instance->relativePath       = fread($archiveHandle, $instance->relativePathLength);

        // Skip the </RP>
        fread($archiveHandle, 5);

        // Skip the </D>
        fread($archiveHandle, 4);

        return $instance;
    }
}

class DupArchiveMiniGlobHeader //extends HeaderBase
{
    public $originalSize;
    public $storedSize;
    public $hash;

 //   const MaxHeaderSize = 255;

   public static function readFromArchive($archiveHandle, $skipGlob)
    {
        $instance = new DupArchiveMiniGlobHeader();

      //  DupArchiveUtil::log('Reading glob starting at ' . ftell($archiveHandle));

        $startElement = fread($archiveHandle, 3);

        //if ($marker != '?G#') {
        if ($startElement != '<G>') {
            throw new Exception("Invalid glob header marker found {$startElement}. location:" . ftell($archiveHandle));
        }

        $instance->originalSize           = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'OS');
        $instance->storedSize             = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'SS');
        $instance->hash                    = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'HA');

        // Skip the </G>
        fread($archiveHandle, 4);

        if ($skipGlob) {
          //  DupProSnapLibIOU::fseek($archiveHandle, $instance->storedSize, SEEK_CUR);
		    if(fseek($archiveHandle, $instance->storedSize, SEEK_CUR) === -1)
			{
                throw new Exception("Can't fseek when skipping glob at location:".ftell($archiveHandle));
            }
        }

        return $instance;
    }
}

class DupArchiveMiniHeader
{
    public $version;
    public $isCompressed;

//    const MaxHeaderSize = 50;

    private function __construct()
    {
        // Prevent instantiation
    }

    public static function readFromArchive($archiveHandle)
    {
        $instance = new DupArchiveMiniHeader();

        $startElement = fgets($archiveHandle, 4);

        if ($startElement != '<A>') {
            throw new Exception("Invalid archive header marker found {$startElement}");
        }

        $instance->version           = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'V');
        $instance->isCompressed      = DupArchiveHeaderMiniU::readStandardHeaderField($archiveHandle, 'C') == 'true' ? true : false;

        // Skip the </A>
        fgets($archiveHandle, 5);

        return $instance;
    }
}

class DupArchiveMiniWriteInfo
{
    public $archiveHandle       = null;
    public $currentFileHeader   = null;
    public $destDirectory       = null;
    public $directoryWriteCount = 0;
    public $fileWriteCount      = 0;
    public $isCompressed        = false;
    public $enableWrite         = false;

    public function getCurrentDestFilePath()
    {
        if($this->destDirectory != null)
        {
            return "{$this->destDirectory}/{$this->currentFileHeader->relativePath}";
        }
        else
        {
            return null;
        }
    }

}

class DupArchiveMiniExpander
{

    public static $loggingFunction     = null;

    public static function init($loggingFunction)
    {
        self::$loggingFunction = $loggingFunction;
    }

    public static function log($s, $flush=false)
    {
        if(self::$loggingFunction != null) {
            call_user_func(self::$loggingFunction, "MINI EXPAND:$s", $flush);
        }
    }

    public static function expandDirectory($archivePath, $relativePath, $destPath)
    {
        self::expandItems($archivePath, $relativePath, $destPath);
    }

    private static function expandItems($archivePath, $inclusionFilter, $destDirectory, $ignoreErrors = false)
    {
        $archiveHandle = fopen($archivePath, 'rb');

        if ($archiveHandle === false) {
            throw new Exception("Can’t open archive at $archivePath!");
        }

        $archiveHeader = DupArchiveMiniHeader::readFromArchive($archiveHandle);

        $writeInfo = new DupArchiveMiniWriteInfo();

        $writeInfo->destDirectory = $destDirectory;
        $writeInfo->isCompressed  = $archiveHeader->isCompressed;

        $moreToRead = true;

        while ($moreToRead) {

            if ($writeInfo->currentFileHeader != null) {

                try {
                    if (self::passesInclusionFilter($inclusionFilter, $writeInfo->currentFileHeader->relativePath)) {

                        self::writeToFile($archiveHandle, $writeInfo);

                        $writeInfo->fileWriteCount++;
                    }
                    else if($writeInfo->currentFileHeader->fileSize > 0) {
                      //  self::log("skipping {$writeInfo->currentFileHeader->relativePath} since it doesn’t match the filter");

                        // Skip the contents since the it isn't a match
                        $dataSize = 0;

                        do {
                            $globHeader = DupArchiveMiniGlobHeader::readFromArchive($archiveHandle, true);

                            $dataSize += $globHeader->originalSize;

                            $moreGlobs = ($dataSize < $writeInfo->currentFileHeader->fileSize);
                        } while ($moreGlobs);
                    }

                    $writeInfo->currentFileHeader = null;

                    // Expand state taken care of within the write to file to ensure consistency
                } catch (Exception $ex) {

                    if (!$ignoreErrors) {
                        throw $ex;
                    }
                }
            } else {

                $headerType = self::getNextHeaderType($archiveHandle);

                switch ($headerType) {
                    case DupArchiveMiniItemHeaderType::File:

                        //$writeInfo->currentFileHeader = DupArchiveMiniFileHeader::readFromArchive($archiveHandle, $inclusionFilter);
						$writeInfo->currentFileHeader = DupArchiveMiniFileHeader::readFromArchive($archiveHandle);

                        break;

                    case DupArchiveMiniItemHeaderType::Directory:

                        $directoryHeader = DupArchiveMiniDirectoryHeader::readFromArchive($archiveHandle);

                     //   self::log("considering $inclusionFilter and {$directoryHeader->relativePath}");
                        if (self::passesInclusionFilter($inclusionFilter, $directoryHeader->relativePath)) {

                        //    self::log("passed");
                            $directory = "{$writeInfo->destDirectory}/{$directoryHeader->relativePath}";

                          //  $mode = $directoryHeader->permissions;

                            // rodo handle this more elegantly @mkdir($directory, $directoryHeader->permissions, true);
                            @mkdir($directory, 0755, true);


                            $writeInfo->directoryWriteCount++;
                        }
                        else {
                     //       self::log("didnt pass");
                        }


                        break;

                    case DupArchiveMiniItemHeaderType::None:
                        $moreToRead = false;
                }
            }
        }

        fclose($archiveHandle);
    }

    private static function getNextHeaderType($archiveHandle)
    {
        $retVal = DupArchiveMiniItemHeaderType::None;
        $marker = fgets($archiveHandle, 4);

        if (feof($archiveHandle) === false) {
            switch ($marker) {
                case '<D>':
                    $retVal = DupArchiveMiniItemHeaderType::Directory;
                    break;

                case '<F>':
                    $retVal = DupArchiveMiniItemHeaderType::File;
                    break;

                case '<G>':
                    $retVal = DupArchiveMiniItemHeaderType::Glob;
                    break;

                default:
                    throw new Exception("Invalid header marker {$marker}. Location:".ftell($archiveHandle));
            }
        }

        return $retVal;
    }

    private static function writeToFile($archiveHandle, $writeInfo)
    {
		$destFilePath = $writeInfo->getCurrentDestFilePath();

		if($writeInfo->currentFileHeader->fileSize > 0)
		{
			/* @var $writeInfo DupArchiveMiniWriteInfo */
			$parentDir = dirname($destFilePath);

			if (!file_exists($parentDir)) {

				$r = @mkdir($parentDir, 0755, true);

				if(!$r)
				{
					throw new Exception("Couldn't create {$parentDir}");
				}
			}

			$destFileHandle = fopen($destFilePath, 'wb+');

			if ($destFileHandle === false) {
				throw new Exception("Couldn't open {$destFilePath} for writing.");
			}

			do {

				self::appendGlobToFile($archiveHandle, $destFileHandle, $writeInfo);

				$currentFileOffset = ftell($destFileHandle);

				$moreGlobstoProcess = $currentFileOffset < $writeInfo->currentFileHeader->fileSize;
			} while ($moreGlobstoProcess);

			fclose($destFileHandle);

            @chmod($destFilePath, 0644);

			self::validateExpandedFile($writeInfo);
		} else {
			if(touch($destFilePath) === false) {
				throw new Exception("Couldn't create $destFilePath");
			}
            @chmod($destFilePath, 0644);
		}
    }

    private static function validateExpandedFile($writeInfo)
    {
        /* @var $writeInfo DupArchiveMiniWriteInfo */

        if ($writeInfo->currentFileHeader->hash !== '00000000000000000000000000000000') {
            
            $hash = hash_file('crc32b', $writeInfo->getCurrentDestFilePath());

            if ($hash !== $writeInfo->currentFileHeader->hash) {

                throw new Exception("MD5 validation fails for {$writeInfo->getCurrentDestFilePath()}");
            }
        }
    }

    // Assumption is that archive handle points to a glob header on this call
    private static function appendGlobToFile($archiveHandle, $destFileHandle, $writeInfo)
    {
        /* @var $writeInfo DupArchiveMiniWriteInfo */
        $globHeader = DupArchiveMiniGlobHeader::readFromArchive($archiveHandle, false);

        $globContents = fread($archiveHandle, $globHeader->storedSize);

        if ($globContents === false) {

            throw new Exception("Error reading glob from {$writeInfo->getDestFilePath()}");
        }

        if ($writeInfo->isCompressed) {
            $globContents = gzinflate($globContents);
        }

        if (fwrite($destFileHandle, $globContents) === false) {
            throw new Exception("Error writing data glob to {$destFileHandle}");
        }
    }

    private static function passesInclusionFilter($filter, $candidate)
    {
        return (substr($candidate, 0, strlen($filter)) == $filter);
    }
}
?>
<!--
Used for integrity check do not remove:
DUPLICATOR_PRO_INSTALLER_EOF  -->
</html>
