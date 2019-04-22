<?php
defined("DUPXABSPATH") or die("");
/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */
/* @var $archive_config DUPX_ArchiveConfig */
/* @var $installer_state DUPX_InstallerState */

require_once($GLOBALS['DUPX_INIT'] . '/classes/config/class.archive.config.php');

//ARCHIVE FILE
$arcCheck = (file_exists($GLOBALS['FW_PACKAGE_PATH'])) ? 'Pass' : 'Fail';
$arcSize = @filesize($GLOBALS['FW_PACKAGE_PATH']);
$arcSize = is_numeric($arcSize) ? $arcSize : 0;

$root_path				= $GLOBALS['DUPX_ROOT'];
$installer_state		= DUPX_InstallerState::getInstance();
$is_wpconfarc_present	= file_exists("{$root_path}/dup-wp-config-arc__{$GLOBALS['DUPX_AC']->package_hash}.txt");
$is_overwrite_mode		= ($installer_state->mode === DUPX_InstallerMode::OverwriteInstall);
$is_wordpress			= DUPX_Server::isWordPress();
$is_dbonly				= $GLOBALS['DUPX_AC']->exportOnlyDB;

//REQUIRMENTS
$req = array();
$ret_is_dir_writable = DUPX_Server::is_dir_writable($GLOBALS['DUPX_ROOT']);
$req['10'] = $ret_is_dir_writable['ret'] ? 'Pass' : 'Fail';
$req['20'] = function_exists('mysqli_connect') ? 'Pass' : 'Fail';
$req['30'] = DUPX_Server::$php_version_safe ? 'Pass' : 'Fail';
$all_req = in_array('Fail', $req) ? 'Fail' : 'Pass';

//NOTICES
$openbase	= ini_get("open_basedir");
$datetime1	= $GLOBALS['DUPX_AC']->created;
$datetime2	= date("Y-m-d H:i:s");
$fulldays	= round(abs(strtotime($datetime1) - strtotime($datetime2))/86400);
$root_path	= DupProSnapLibIOU::safePath($GLOBALS['DUPX_ROOT'], true);
$archive_path = DupProSnapLibIOU::safePath($GLOBALS['FW_PACKAGE_PATH'], true);
$wpconf_path = "{$root_path}/wp-config.php";
$max_time_zero = ($GLOBALS['DUPX_ENFORCE_PHP_INI']) ? false : @set_time_limit(0);
$max_time_size = 314572800;  //300MB
$max_time_ini = ini_get('max_execution_time');
$max_time_warn = (is_numeric($max_time_ini) && $max_time_ini < 31 && $max_time_ini > 0) && $arcSize > $max_time_size;
$parent_has_wordfence = file_exists($GLOBALS['DUPX_ROOT'].'/../wp-content/plugins/wordfence/wordfence.php');


$notice = array();
$notice['10'] = ! $is_overwrite_mode ? 'Good' : 'Warn';
$notice['20'] = ! $is_wpconfarc_present ? 'Good' : 'Warn';
if ($is_dbonly) {
	$notice['25'] =	$is_wordpress ? 'Good' : 'Warn';
}
$notice['30'] = $fulldays <= 180 ? 'Good' : 'Warn';
$notice['40'] = DUPX_Server::$php_version_53_plus	 ? 'Good' : 'Warn';

$packagePHP = $GLOBALS['DUPX_AC']->version_php;
$currentPHP = DUPX_Server::$php_version;
$packagePHPMajor = intval($packagePHP);
$currentPHPMajor = intval($currentPHP);
$notice['45'] = ($packagePHPMajor === $currentPHPMajor || $GLOBALS['DUPX_AC']->exportOnlyDB) ? 'Good' : 'Warn';

$notice['50'] = empty($openbase) ? 'Good' : 'Warn';
$notice['60'] = !$max_time_warn ? 'Good' : 'Warn';
$notice['70'] = !$parent_has_wordfence ? 'Good' : 'Warn';
$notice['80'] = !$GLOBALS['DUPX_AC']->is_outer_root_wp_config_file	? 'Good' : 'Warn';
if ($GLOBALS['DUPX_AC']->exportOnlyDB) {
	$notice['90'] = 'Good';
} else {
	$notice['90'] = (!$GLOBALS['DUPX_AC']->is_outer_root_wp_content_dir) 
						? 'Good' 
						: 'Warn';
}

$space_free = @disk_free_space($GLOBALS['DUPX_ROOT']); 
$archive_size = filesize($GLOBALS['FW_PACKAGE_PATH']);
$notice['100'] = ($space_free && $archive_size > $space_free) 
                    ? 'Warn'
                    : 'Good';

$all_notice = in_array('Warn', $notice) ? 'Warn' : 'Good';

//SUMMATION
$req_success	= ($all_req == 'Pass');
$req_notice		= ($all_notice == 'Good');
$all_success	= ($req_success && $req_notice);
$agree_msg		= "To enable this button the checkbox above under the 'Terms & Notices' must be checked.";

$shell_exec_unzip_path  = DUPX_Server::get_unzip_filepath();
$shell_exec_unzip_enabled = ($shell_exec_unzip_path != null);
$zip_archive_enabled    = class_exists('ZipArchive');
$archive_config			= DUPX_ArchiveConfig::getInstance();


//MULTISITE
$show_multisite = ($archive_config->mu_mode !== 0) && (count($archive_config->subsites) > 0);
$multisite_disabled = ($archive_config->getLicenseType() != DUPX_LicenseType::BusinessGold);

?>

<form id="s1-input-form" method="post" class="content-form">
    <input type="hidden" name="view" value="step1" />
    <input type="hidden" name="csrf_token" value="<?php echo DUPX_CSRF::generate('step1'); ?>">
    <input type="hidden" name="secure-pass" value="<?php echo DUPX_U::esc_attr($_POST['secure-pass']); ?>" />
    <input type="hidden" name="bootloader" value="<?php echo DUPX_U::esc_attr($GLOBALS['BOOTLOADER_NAME']); ?>" />
	<input type="hidden" name="archive" value="<?php echo DUPX_U::esc_attr($GLOBALS['FW_PACKAGE_PATH']); ?>" />    
	<input type="hidden" name="ctrl_action" value="ctrl-step1" />
    <input type="hidden" name="ctrl_csrf_token" value="<?php echo DUPX_CSRF::generate('ctrl-step1'); ?>"> 
    <input type="hidden" id="s1-input-form-extra-data" name="extra_data" />

    <div class="hdr-main">
        Step <span class="step">1</span> of 4: Deployment
    </div><br/>


    <!-- ====================================
    ARCHIVE
    ==================================== -->
    <div class="hdr-sub1 toggle-hdr" data-type="toggle" data-target="#s1-area-archive-file">
        <a id="s1-area-archive-file-link"><i class="fa fa-plus-square"></i>Archive</a>
        <div class="<?php echo ( $arcCheck == 'Pass') ? 'status-badge-pass' : 'status-badge-fail'; ?>">
            <?php echo ($arcCheck == 'Pass') ? 'Pass' : 'Fail'; ?>
        </div>
    </div>
    <div id="s1-area-archive-file" style="display:none">
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1">Server</a></li>
                <!--li><a href="#tabs-2">Cloud</a></li-->
            </ul>
            <div id="tabs-1">

                <table class="s1-archive-local">
                    <tr>
                        <td colspan="2"><div class="hdr-sub3">Site Details</div></td>
                    </tr>
                    <tr>
                        <td>Site:</td>
                        <td><?php echo DUPX_U::esc_html($GLOBALS['DUPX_AC']->blogname);?> </td>
                    </tr>
                    <tr>
                        <td>Notes:</td>
                        <td><?php echo strlen($GLOBALS['DUPX_AC']->package_notes) ? "{$GLOBALS['DUPX_AC']->package_notes}" : " - no notes - "; ?></td>
                    </tr>
                    <?php if ($GLOBALS['DUPX_AC']->exportOnlyDB) :?>
                        <tr>
                            <td>Mode:</td>
                            <td>Archive only database was enabled during package package creation.</td>
                        </tr>
                    <?php endif; ?>
                </table>

                <table class="s1-archive-local">
                    <tr>
                        <td colspan="2"><div class="hdr-sub3">File Details</div></td>
                    </tr>
                    <tr>
                        <td>Size:</td>
                        <td><?php echo DUPX_U::readableByteSize($arcSize);?> </td>
                    </tr>
                    <tr>
                        <td>Path:</td>
                        <td><?php echo $root_path; ?> </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top">Status:</td>
                        <td>
                            <?php if ($arcCheck != 'Fail') : ?>
                                <span class="dupx-pass">Archive file successfully detected.</span>
                            <?php else : ?>
                                <span class="dupx-fail" style="font-style:italic">
							The archive file named above must be the <u>exact</u> name of the archive file placed in the root path (character for character).
							When downloading the package files make sure both files are from the same package line.  <br/><br/>

							If the contents of the archive were manually transferred to this location without the archive file then simply create a temp file named with
							the exact name shown above and place the file in the same directory as the installer.php file.  The temp file will not need to contain any data.
							Afterward, refresh this page and continue with the install process.
						</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

            </div>
            <!--div id="tabs-2"><p>Content Here</p></div-->
        </div>
    </div><br/><br/>

    <!-- ====================================
    VALIDATION
    ==================================== -->
    <div class="hdr-sub1 toggle-hdr" data-type="toggle" data-target="#s1-area-sys-setup">
        <a id="s1-area-sys-setup-link"><i class="fa fa-plus-square"></i>Validation</a>
        <div class="<?php echo ( $req_success) ? 'status-badge-pass' : 'status-badge-fail'; ?>	">
            <?php echo ( $req_success) ? 'Pass' : 'Fail'; ?>
        </div>
    </div>
    <div id="s1-area-sys-setup" style="display:none">
        <div class='info-top'>The system validation checks help to make sure the system is ready for install.</div>

        <!-- REQUIREMENTS -->
        <div class="s1-reqs" id="s1-reqs-all">
            <div class="header">
                <table class="s1-checks-area">
                    <tr>
                        <td class="title">Requirements <small>(must pass)</small></td>
                        <td class="toggle"><a href="javascript:void(0)" onclick="DUPX.toggleAll('#s1-reqs-all')">[toggle]</a></td>
                    </tr>
                </table>
            </div>

		<!-- REQ 10 -->
		<div class="status <?php echo strtolower($req['10']); ?>"><?php echo $req['10']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-reqs10"><i class="fa fa-caret-right"></i> Permissions</div>
		<div class="info" id="s1-reqs10">
			<table>
				<tr>
					<td><b>Deployment Path:</b> </td>
					<td><i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i> </td>
				</tr>
				<tr>
					<td><b>Suhosin Extension:</b> </td>
					<td><?php echo extension_loaded('suhosin') ? "<i class='dupx-fail'>Enabled</i>" : "<i class='dupx-pass'>Disabled</i>"; ?> </td>
				</tr>
				<tr>
					<td><b>PHP Safe Mode:</b> </td>
					<td><?php echo (DUPX_Server::$php_safe_mode_on) ? "<i class='dupx-fail'>Enabled</i>" : "<i class='dupx-pass'>Disabled</i>"; ?> </td>
				</tr>
                <?php
                if (!empty($ret_is_dir_writable['failedObjects'])) {
                ?>
                    <tr>
                        <td colspan="2">
                        &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <b>Overwrite fails for these folders or files (change permissions or remove then restart):</b><br/>
                            <ul style="color:maroon; word-break: break-word; margin: 0 0 0 0; padding: 4px 0 0 15px; line-height: 1.7em;">
                                <?php
                                echo '<li>'.implode('</li><li>', $ret_is_dir_writable['failedObjects']).'</li>';
                                ?>
                            </ul>
                        </td>
                    </tr>
                <?php
                }
                ?>
			</table>                     
            
            <br/>
			The deployment path must be writable by PHP in order to extract the archive file.  Incorrect permissions and extension such as
			<a href="https://suhosin.org/stories/index.html" target="_blank">suhosin</a> can interfere with PHP's ability to write/extract files.
			Please see the <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-055-q" target="_blank">FAQ permission</a> help link for details.
			PHP with <a href='http://php.net/manual/en/features.safe-mode.php' target='_blank'>safe mode</a> should be disabled.  If Safe Mode is enabled then
			contact your hosting provider or server administrator to disable PHP safe mode.
		</div>
		<!-- REQ 20 -->
		<div class="status <?php echo strtolower($req['20']); ?>"><?php echo $req['20']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-reqs20"><i class="fa fa-caret-right"></i> PHP Mysqli</div>
		<div class="info" id="s1-reqs20">
			Support for the PHP <a href='http://us2.php.net/manual/en/mysqli.installation.php' target='_blank'>mysqli extension</a> is required.
			Please contact your hosting provider or server administrator to enable the mysqli extension.  <i>The detection for this call uses
				the function_exists('mysqli_connect') call.</i>
		</div>

		<!-- REQ 30 -->
		<div class="status <?php echo strtolower($req['30']); ?>"><?php echo $req['30']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-reqs30"><i class="fa fa-caret-right"></i> PHP Version</div>
		<div class="info" id="s1-reqs30">
			This server is running PHP: <b><?php echo DUPX_Server::$php_version ?></b>. <i>A minimum of PHP 5.2.17 is required</i>.
			Contact your hosting provider or server administrator and let them know you would like to upgrade your PHP version.
		</div>
	</div><br/>


        <!-- ====================================
        NOTICES  -->
        <div class="s1-reqs" id="s1-notice-all">
            <div class="header">
                <table class="s1-checks-area">
                    <tr>
                        <td class="title">Notices <small>(optional)</small></td>
                        <td class="toggle"><a href="javascript:void(0)" onclick="DUPX.toggleAll('#s1-notice-all')">[toggle]</a></td>
                    </tr>
                </table>
            </div>

		<!-- NOTICE 10: OVERWRITE INSTALL -->
		<?php if ($is_overwrite_mode && $is_wordpress) :?>
			<div class="status fail">Warn</div>
			<div class="title" data-type="toggle" data-target="#s1-notice10"><i class="fa fa-caret-right"></i> Overwrite Install</div>
			<div class="info" id="s1-notice10">
				<b>Deployment Path:</b> <i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i>
				<br/><br/>

				Duplicator is in "Overwrite Install" mode because it has detected an existing WordPress site at the deployment path above.  This mode allows for the installer
				to be dropped directly into an existing WordPress site and overwrite its contents.   Any content inside of the archive file
				will <u>overwrite</u> the contents from the deployment path.  To continue choose one of these options:

				<ol>
					<li>Ignore this notice and continue with the install if you want to overwrite this sites files.</li>
					<li>Move this installer and archive to another empty directory path to keep this sites files.</li>
				</ol>

				<small style="color:maroon">
					<b>Notice:</b> Existing content such as plugin/themes/images will still show-up after the install is complete if they did not already exist in
					the archive file. For example if you have an SEO plugin in the current site but that same SEO plugin <u>does not exist</u> in the archive file
					then that plugin will display as a disabled plugin after the install is completed. The same concept with themes and images applies.  This will
					not impact the sites operation, and the behavior is expected.
				</small>
				<br/><br/>


				<small style="color:#025d02">
					<b>Recommendation:</b> It is recommended you only overwrite WordPress sites that have a minimal	setup (plugins/themes).  Typically a fresh install or a
					cPanel 'one click' install is the best baseline to work from when using this mode but is not required.
				</small>
			</div>

		<!-- NOTICE 20: ARCHIVE EXTRACTED -->
		<?php elseif ($is_wpconfarc_present) :?>
			<div class="status fail">Warn</div>
			<div class="title" data-type="toggle" data-target="#s1-notice20"><i class="fa fa-caret-right"></i> Archive Extracted</div>
			<div class="info" id="s1-notice20">
				<b>Deployment Path:</b> <i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i>
				<br/><br/>

				The installer has detected that the archive file has been extracted to the deployment path above.  To continue choose one of these options:

				<ol>
					<li>Skip the extraction process by <a href="javascript:void(0)" onclick="DUPX.getManaualArchiveOpt()">[enabling manual archive extraction]</a> </li>
					<li>Ignore this message and continue with the install process to re-extract the archive file.</li>
				</ol>

				<small>Note: This test looks for a file named <i>dup-wp-config-arc__[HASH].txt</i> in the dup-installer directory.  If the file exists then this notice is shown.
				The <i>dup-wp-config-arc__[HASH].txt</i> file is created with every archive and removed once the install is complete.  For more details on this process see the
				<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q" target="_blank">manual extraction FAQ</a>.</small>
			</div>
		<?php endif; ?>

		<!-- NOTICE 25: DATABASE ONLY -->
		<?php if ($is_dbonly && ! $is_wordpress) :?>
			<div class="status fail">Warn</div>
			<div class="title" data-type="toggle" data-target="#s1-notice25"><i class="fa fa-caret-right"></i> Database Only</div>
			<div class="info" id="s1-notice25">
				<b>Deployment Path:</b> <i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i>
				<br/><br/>

				The installer has detected that a WordPress site does not exist at the deployment path above. This installer is currently in 'Database Only' mode because that is
				how the archive was created.  If core WordPress site files do not exist at the path above then they will need to be placed there in order for a WordPress site
				to properly work.  To continue choose one of these options:

				<ol>
					<li>Place this installer and archive at a path where core WordPress files already exist to hide this message. </li>
					<li>Create a new package that includes both the database and the core WordPress files.</li>
					<li>Ignore this message and install only the database (for advanced users only).</li>
				</ol>

				<small>Note: This test simply looks for the directories <?php echo DUPX_Server::$wpCoreDirsList; ?> and a wp-config.php file.  If they are not found in the
				deployment path above then this notice is shown.</small>

			</div>
		<?php endif; ?>
			

		<!-- NOTICE 30 -->
		<div class="status <?php echo ($notice['30'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['30']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice30"><i class="fa fa-caret-right"></i> Package Age</div>
		<div class="info" id="s1-notice30">
			This package is <?php echo "{$fulldays}"; ?> day(s) old. Packages older than 180 days might be considered stale.  It is recommended to build a new
			package unless your aware of the content and its data.  This is message is simply a recommendation.
		</div>

		<!-- NOTICE 40 -->
		<div class="status <?php echo ($notice['40'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['40']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice40"><i class="fa fa-caret-right"></i> PHP Version 5.2</div>
		<div class="info" id="s1-notice40">
			<?php
				$cssStyle   = DUPX_Server::$php_version_53_plus	 ? 'color:green' : 'color:red';
				echo "<b style='{$cssStyle}'>This server is currently running PHP version [{$currentPHP}]</b>.<br/>"
				. "Duplicator Pro allows PHP 5.2 to be used during install but does not officially support it.  If you're using PHP 5.2 we strongly recommend NOT using it and having your "
				. "host upgrade to a newer more stable, secure and widely supported version.  The <a href='http://php.net/eol.php' target='_blank'>end of life for PHP 5.2</a> "
				. "was in January of 2011 and is not recommended for use.<br/><br/>";

                echo "Many plugin and theme authors are no longer supporting PHP 5.2 and trying to use it can result in site wide problems and compatibility warnings and errors.  "
                    . "Please note if you continue with the install using PHP 5.2 the Duplicator Pro support team will not be able to help with issues or troubleshoot your site.  "
                    . "If your server is running <b>PHP 5.3+</b> please feel free to reach out for help if you run into issues with your migration/install.";
                ?>
            </div>

        <!-- NOTICE 45 -->
		<div class="status <?php echo ($notice['45'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['45']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice45"><i class="fa fa-caret-right"></i> PHP Version mismatch</div>
		<div class="info" id="s1-notice45">
			<?php
                $cssStyle   = $notice['45'] == 'Good' ? 'color:green' : 'color:red';
				echo "<b style='{$cssStyle}'>You are migrating site from PHP {$packagePHP} to PHP {$currentPHP}</b>.<br/>"
                    ."If the PHP version of your website is different than the PHP version of your package 
                    it MAY cause problems with the functioning of your website.<br/>";
                ?>
            </div>

		<!-- NOTICE 50 -->
		<div class="status <?php echo ($notice['50'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['50']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice50"><i class="fa fa-caret-right"></i> PHP Open Base</div>
		<div class="info" id="s1-notice50">
			<b>Open BaseDir:</b> <i><?php echo $notice['50'] == 'Good' ? "<i class='dupx-pass'>Disabled</i>" : "<i class='dupx-fail'>Enabled</i>"; ?></i>
			<br/><br/>

                If <a href="http://php.net/manual/en/ini.core.php#ini.open-basedir" target="_blank">open_basedir</a> is enabled and you're
                having issues getting your site to install properly please work with your host and follow these steps to prevent issues:
                <ol style="margin:7px; line-height:19px">
                    <li>Disable the open_basedir setting in the php.ini file</li>
                    <li>If the host will not disable, then add the path below to the open_basedir setting in the php.ini<br/>
                        <i style="color:maroon">"<?php echo str_replace('\\', '/', dirname( __FILE__ )); ?>"</i>
                    </li>
                    <li>Save the settings and restart the web server</li>
                </ol>
                Note: This warning will still show if you choose option #2 and open_basedir is enabled, but should allow the installer to run properly.  Please work with your
                hosting provider or server administrator to set this up correctly.
            </div>

		<!-- NOTICE 60 -->
		<div class="status <?php echo ($notice['60'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['60']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice60"><i class="fa fa-caret-right"></i> PHP Timeout</div>
		<div class="info" id="s1-notice60">
			<b>Archive Size:</b> <?php echo DUPX_U::readableByteSize($arcSize) ?>  <small>(detection limit is set at <?php echo DUPX_U::readableByteSize($max_time_size) ?>) </small><br/>
			<b>PHP max_execution_time:</b> <?php echo "{$max_time_ini}"; ?> <small>(zero means not limit)</small> <br/>
			<b>PHP set_time_limit:</b> <?php echo ($max_time_zero) ? '<i style="color:green">Success</i>' : '<i style="color:maroon">Failed</i>' ?>
			<br/><br/>

                The PHP <a href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank">max_execution_time</a> setting is used to
                determine how long a PHP process is allowed to run.  If the setting is too small and the archive file size is too large then PHP may not have enough
                time to finish running before the process is killed causing a timeout.
                <br/><br/>

                Duplicator Pro attempts to turn off the timeout by using the
                <a href="http://php.net/manual/en/function.set-time-limit.php" target="_blank">set_time_limit</a> setting.   If this notice shows as a warning then it is
                still safe to continue with the install.  However, if a timeout occurs then you will need to consider working with the max_execution_time setting or extracting the
                archive file using the 'Manual Archive Extraction' method.
                Please see the	<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-100-q" target="_blank">FAQ timeout</a> help link for more details.
        </div>

        <!-- NOTICE 70 -->
        <div class="status <?php echo ($notice['70'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['70']; ?></div>
        <div class="title" data-type="toggle" data-target="#s1-notice08"><i class="fa fa-caret-right"></i> Wordfence</div>
        <div class="info" id="s1-notice08">
            <?php if( $parent_has_wordfence): ?>
            You are installing in a subdirectory of another site that has Wordfence installed.
            Temporarily deactivate Wordfence on the parent site before continuing with the install.
            <?php else: ?>
            Having Wordfence in a parent site can interfere with the install, however no such condition was detected.
            <?php endif;?>
        </div>

        <!-- NOTICE 80 -->
		<div class="status <?php echo ($notice['80'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo DUPX_U::esc_html($notice['80']); ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice80"><i class="fa fa-caret-right"></i> wp-config.php file location</div>
		<div class="info" id="s1-notice80">
			When this item shows a warning, it indicates the wp-config.php file was detected in the directory above the WordPress root folder on the source site. 
			<br/><br/>
			The Duplicator Installer will place the wp-config.php file in the root folder of the WordPress installation. This will not affect operation of the site.
		</div>

		<!-- NOTICE 90 -->
		<div class="status <?php echo ($notice['90'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo DUPX_U::esc_html($notice['90']); ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice90"><i class="fa fa-caret-right"></i> wp-content directory location</div>
		<div class="info" id="s1-notice90">
			When this item shows a warning, it indicates the wp-content directory was not in the WordPress root folder on the source site.
			<br/><br/>
			The Duplicator Installer will place the wp-content directory in the WordPress root folder of the WordPress installation. This will not affect operation of the site.
		</div>

        <!-- NOTICE 100 -->
		<div class="status <?php echo ($notice['100'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo DUPX_U::esc_html($notice['100']); ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice100"><i class="fa fa-caret-right"></i> Sufficient disk space</div>
		<div class="info" id="s1-notice100">
        <?php
        echo ($notice['100'] == 'Good')
                ? 'You have sufficient disk space in your machine to extract the archive.'
                : 'You don’t have sufficient disk space in your machine to extract the archive. Ask your host to increase disk space.'
        ?>
		</div>
        </div>
    </div>
    <br/><br/>

    <!-- ====================================
    MULTISITE PANEL
    ==================================== -->
    <?php if($show_multisite) : ?>
        <div class="hdr-sub1 toggle-hdr" data-type="toggle" data-target="#s1-multisite">
            <a href="javascript:void(0)"><i class="fa fa-minus-square"></i>Multisite</a>
        </div>
        <div id="s1-multisite">
            <?php if(!$archive_config->mu_is_filtered): ?>
                <input id="full-network" onclick="DUPX.enableSubsiteList(false);" type="radio" name="multisite-install-type" value="0" checked>
                <label for="full-network">Restore entire multisite network</label><br/>
                <input <?php if($multisite_disabled) {echo 'disabled';} ?> id="multisite-install-type" onclick="DUPX.enableSubsiteList(true);" type="radio" name="multisite-install-type" value="1">
                <label for="multisite-install-type">Convert subsite
                    <select id="subsite-id" name="subsite-id" style="width:200px" disabled>
                        <?php foreach($archive_config->subsites as $subsite) :
                            if (property_exists($subsite, 'blogname')) {
                                $label = $subsite->blogname.' ('.$subsite->name.')';
                            } else {
                                $label = $subsite->name;
                            }
                            ?>
                            <option value="<?php echo intval($subsite->id); ?>"><?php echo DUPX_U::esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    into a standalone site<?php if($multisite_disabled) { echo '*';} ?>
                </label>
            <?php else: ?>
                Convert subsite
                <select id="subsite-id" name="subsite-id" style="width:200px" <?php if($multisite_disabled) { echo 'disabled';}?>>
                    <?php foreach($archive_config->subsites as $subsite) : 
                        if (property_exists ( $subsite , 'blogname')) {
                            $label = $subsite->blogname.' ('.$subsite->name.')';
                        } else {
                            $label = $subsite->name;
                        }
                        ?>
                        <option value="<?php echo $subsite->id; ?>"><?php echo DUPX_U::esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                into a standalone site<?php if($multisite_disabled) { echo '*';} ?><br>
                <p style="line-height:17px; margin-top:27px">
                    <b>Note:</b> You can't restore the entire multisite network because one or more subsites were filtered when this package was created.
                </p>
            <?php endif; ?>
            <?php
            if($multisite_disabled) {
                $license_string = ' This installer was created with ';

                switch($archive_config->getLicenseType()) {
                    case DUPX_LicenseType::Unlicensed:
                        $license_string .= "an Unlicensed copy of Duplicator Pro.";
                        break;

                    case DUPX_LicenseType::Personal:
                        $license_string .= "a Personal license of Duplicator Pro.";
                        break;

                    case DUPX_LicenseType::Freelancer:
                        $license_string .= "a Freelancer license of Duplicator Pro.";
                        break;

                    default:
                        $license_string = '';
                }
                echo "<p class='note'>*Requires Business or Gold license. $license_string</p>";
            }
            ?>
        </div>
        <br/><br/>
    <?php endif; ?>

    <!-- ====================================
    OPTIONS
    ==================================== -->
    <div class="hdr-sub1 toggle-hdr" data-type="toggle" data-target="#s1-area-adv-opts">
        <a href="javascript:void(0)"><i class="fa fa-plus-square"></i>Options</a>
    </div>
    <div id="s1-area-adv-opts" style="display:none">
        <div class="help-target">
            <a href="<?php echo DUPX_U::esc_url($GLOBALS['_HELP_URL_PATH']); ?>#help-s1" target="_blank"><i class="fas fa-question-circle fa-sm"></i></a>
        </div><br/>

	<div class="hdr-sub3">General</div>
	<table class="dupx-opts dupx-advopts">
        <tr>
            <td>Extraction:</td>
            <td>
                <?php $num_selections = ($archive_config->isZipArchive() ? 3 : 2); ?>
                <select id="archive_engine" name="archive_engine" size="<?php echo $num_selections; ?>">
					<option <?php echo ($is_wpconfarc_present ? '' : 'disabled'); ?> value="manual">Manual Archive Extraction <?php echo ($is_wpconfarc_present ? '' : '*'); ?></option>
                    <?php
                        if($archive_config->isZipArchive()){

                            //ZIP-ARCHIVE
                            if ($zip_archive_enabled){
                                echo '<option value="ziparchive">PHP ZipArchive</option>';
                                echo '<option value="ziparchivechunking" selected="true">PHP ZipArchive Chunking</option>';
                            } else {
                                echo '<option value="ziparchive" disabled="true">PHP ZipArchive (not detected on server)</option>';
                            }
                            
                            //SHELL-EXEC UNZIP
                            if ($shell_exec_unzip_enabled) {
                                 if($zip_archive_enabled) {
                                    echo '<option value="shellexec_unzip" >Shell Exec Unzip</option>';
                                 } else {
                                    echo '<option value="shellexec_unzip" selected="true">Shell Exec Unzip</option>';
                                 }
                            } else {
                                echo '<option value="shellexec_unzip" disabled="true">Shell Exec Unzip (not detected on server)</option>';
                            }
                    }
                    else {
                        echo '<option value="duparchive" selected="true">DupArchive</option>';
                    }
                    ?>
                </select><br/>
				<?php if(!$is_wpconfarc_present) :?>
					<span class="sub-notes">
						*Option enabled when archive has been pre-extracted
						<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q" target="_blank">[more info]</a>
					</span>
				<?php endif ?>
            </td>
        </tr>
		<tr>
			<td>Permissions:</td>
			<td>
				<input type="checkbox" name="set_file_perms" id="set_file_perms" value="1" onclick="jQuery('#file_perms_value').prop('disabled', !jQuery(this).is(':checked'));"/>
				<label for="set_file_perms">All Files</label><input name="file_perms_value" id="file_perms_value" style="width:30px; margin-left:7px;" value="644" disabled> &nbsp;
				<input type="checkbox" name="set_dir_perms" id="set_dir_perms" value="1" onclick="jQuery('#dir_perms_value').prop('disabled', !jQuery(this).is(':checked'));"/>
				<label for="set_dir_perms">All Directories</label><input name="dir_perms_value" id="dir_perms_value" style="width:30px; margin-left:7px;" value="755" disabled>
			</td>
		</tr>
	</table><br/><br/>

        <div class="hdr-sub3">Advanced</div>
        <table class="dupx-opts dupx-advopts">
            <tr>
                <td>Safe Mode:</td>
                <td>
                    <select name="exe_safe_mode" id="exe_safe_mode" onchange="DUPX.onSafeModeSwitch();" style="width:200px;">
                        <option value="0">Off</option>
                        <option value="1">Basic</option>
                        <option value="2">Advanced</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Config Files:</td>
                <td>
                    <input type="checkbox" name="retain_config" id="retain_config" value="1" />
                    <label for="retain_config" style="font-weight: normal">Retain original .htaccess, .user.ini and web.config</label>
                </td>
            </tr>
            <tr>
                <td>File Times:</td>
                <td>
                    <input type="radio" name="zip_filetime" id="zip_filetime_now" value="current" checked="checked" />
                    <label class="radio" for="zip_filetime_now" title='Set the files current date time to now'>Current</label> &nbsp;
                    <input type="radio" name="zip_filetime" id="zip_filetime_orginal" value="original" />
                    <label class="radio" for="zip_filetime_orginal" title="Keep the files date time the same">Original</label>
                </td>
            </tr>
            <tr>
                <td>Logging:</td>
                <td>
                    <input type="radio" name="logging" id="logging-light" value="1" checked="true"> <label for="logging-light" class="radio">Light</label> &nbsp;
                    <input type="radio" name="logging" id="logging-detailed" value="2"> <label for="logging-detailed" class="radio">Detailed</label> &nbsp;
                    <input type="radio" name="logging" id="logging-debug" value="3"> <label for="logging-debug" class="radio">Debug</label>
                </td>
            </tr>
            <?php if(!$archive_config->isZipArchive()): ?>
                <tr>
                    <td>Client-Kickoff:</td>
                    <td>
                        <input type="checkbox" name="clientside_kickoff" id="clientside_kickoff" value="1" checked/>
                        <label for="clientside_kickoff" style="font-weight: normal">Browser drives the archive engine.</label>
                    </td>
                </tr>
            <?php endif;
            $licence_type = $GLOBALS['DUPX_AC']->getLicenseType();
            if ($licence_type >= DUPX_LicenseType::Freelancer) {
            ?>
            <tr id="remove-redundant-row" <?php if ($GLOBALS['DUPX_AC']->exportOnlyDB) {?>style="display:none;"<?php } ?>>
                <td>Inactive Plugins<br> and Themes:</td>
                <td>
                    <input type="checkbox" id="remove-redundant" name="remove-redundant" value="1">
                    <label for="remove-redundant">Migrate only active themes and plugins.</label>
                    <?php if($show_multisite) { ?>
                        <br>
                        <span class="sub-notes">
                            <?php echo str_repeat('&nbsp;', 7);?>When checked for a subsite to standalone migration, only active users will be retained also.
                        </span>
                    <?php } ?>                    
                </td>
            </tr>
            <?php
            }
            ?>
        </table>
    </div><br/>

    <?php include ('view.s1.terms.php') ;?>

    <div id="s1-warning-check">
        <input id="accept-warnings" name="accpet-warnings" type="checkbox" onclick="DUPX.acceptWarning()" />
        <label for="accept-warnings">I have read and accept all <a href="javascript:void(0)" onclick="DUPX.viewTerms()">terms &amp; notices</a> <small style="font-style:italic">(required to continue)</small></label><br/>
    </div>
    <br/><br/>
    <br/><br/>


    <?php if (!$req_success || $arcCheck == 'Fail') : ?>
        <div class="s1-err-msg">
            <i>
                This installation will not be able to proceed until the archive and validation sections both pass. Please adjust your servers settings or contact your
                server administrator, hosting provider or visit the resources below for additional help.
            </i>
            <div style="padding:10px">
                &raquo; <a href="https://snapcreek.com/duplicator/docs/faqs-tech/" target="_blank">Technical FAQs</a> <br/>
                &raquo; <a href="https://snapcreek.com/support/docs/" target="_blank">Online Documentation</a> <br/>
            </div>
        </div>
    <?php else : ?>
        <div class="footer-buttons" >
            <button id="s1-deploy-btn" type="button" title="<?php echo $agree_msg; ?>" onclick="DUPX.processNext()"  class="default-btn"> Next <i class="fa fa-caret-right"></i> </button>
        </div>
    <?php endif; ?>

</form>


<!-- =========================================
VIEW: STEP 1 - AJAX RESULT
Auto Posts to view.step2.php
========================================= -->
<form id='s1-result-form' method="post" class="content-form" style="display:none">

    <div class="dupx-logfile-link"><a href="<?php echo './'.DUPX_U::esc_attr($GLOBALS["LOG_FILE_NAME"]);?>" target="dup-installer">installer-log.txt</a></div>
    <div class="hdr-main">
        Step <span class="step">1</span> of 4: Extraction
    </div>

    <!--  POST PARAMS -->
    <div class="dupx-debug">
        <i>Step 1 - AJAX Response</i>
        <input type="hidden" name="view" value="step2" />
        <input type="hidden" name="csrf_token" value="<?php echo DUPX_U::esc_attr(DUPX_CSRF::generate('step2')); ?>">
		<input type="hidden" name="secure-pass" value="<?php echo DUPX_U::esc_attr($_POST['secure-pass']); ?>" />
        <input type="hidden" name="bootloader" value="<?php echo DUPX_U::esc_attr($GLOBALS['BOOTLOADER_NAME']); ?>" />
	    <input type="hidden" name="archive" value="<?php echo DUPX_U::esc_attr($GLOBALS['FW_PACKAGE_PATH']); ?>" />
        <input type="hidden" name="logging" id="ajax-logging"  />
        <input type="hidden" name="archive_name" value="<?php echo DUPX_U::esc_attr($GLOBALS['FW_PACKAGE_NAME']); ?>" />
        <input type="hidden" name="retain_config" id="ajax-retain-config" />
        <input type="hidden" name="exe_safe_mode" id="exe-safe-mode"  value="0" />
        <input type="hidden" name="subsite-id" id="ajax-subsite-id" value="-1" />
        <input type="hidden" name="remove_redundant" id="ajax-remove-redundant" value="0" />
        <input type="hidden" name="json" id="ajax-json" />
        <textarea id='ajax-json-debug' name='json_debug_view'></textarea>
        <input type='submit' value='manual submit'>
    </div>

    <!--  PROGRESS BAR -->
    <div id="progress-area">
        <div style="width:500px; margin:auto">
            <div class="progress-text"><i class="fas fa-circle-notch fa-spin"></i> Extracting Archive Files<span id="progress-pct"></span></div>
            <div id="secondary-progress-text"></div>
            <div id="progress-notice"></div>
            <div id="progress-bar"></div>
            <h3> Please Wait...</h3><br/><br/>
            <i>Keep this window open during the extraction process.</i><br/>
            <i>This can take several minutes.</i>
        </div>
    </div>

    <!--  AJAX SYSTEM ERROR -->
    <div id="ajaxerr-area" style="display:none">
        <p>Please try again an issue has occurred.</p>
        <div style="padding: 0px 10px 10px 0px;">
            <div id="ajaxerr-data">An unknown issue has occurred with the file and database setup process.  Please see the installer-log.txt file for more details.</div>
            <div style="text-align:center; margin:10px auto 0px auto">
                <input type="button" class="default-btn" onclick="DUPX.hideErrorResult()" value="&laquo; Try Again" /><br/><br/>
                <i style='font-size:11px'>See online help for more details at <a href='https://snapcreek.com/ticket' target='_blank'>snapcreek.com</a></i>
            </div>
        </div>
    </div>
</form>

<script>
    DUPX.toggleSetupType = function ()
    {
        var val = $("input:radio[name='setup_type']:checked").val();
        $('div.s1-setup-type-sub').hide();
        $('#s1-setup-type-sub-' + val).show(200);
    };

DUPX.getManaualArchiveOpt = function ()
{
	$("html, body").animate({scrollTop: $(document).height()}, 1500);
	$("div[data-target='#s1-area-adv-opts']").find('i.fa').removeClass('fa-plus-square').addClass('fa-minus-square');
	$('#s1-area-adv-opts').show(1000);
	$('select#archive_engine').val('manual').focus();
};

    DUPX.enableSubsiteList = function (enable)
    {
        if (enable) {
            <?php if ($GLOBALS['DUPX_AC']->exportOnlyDB) :?>
                if ($('#remove-redundant-row').length) {
                    $('#remove-redundant-row').show();
                }
            <?php endif; ?>
            $("#subsite-id").prop('disabled', false);
        } else {
            <?php if ($GLOBALS['DUPX_AC']->exportOnlyDB) :?>
                if ($('#remove-redundant-row').length) {
                    $('#remove-redundant-row').hide();
                }
            <?php endif; ?>
            $("#subsite-id").prop('disabled', 'disabled');
        }
    };

    DUPX.startExtraction = function()
    {
        var isManualExtraction = ($("#archive_engine").val() == "manual");
        var zipEnabled = <?php echo DupProSnapLibStringU::boolToString($archive_config->isZipArchive()); ?>;
        var chunkingEnabled  = ($("#archive_engine").val() == "ziparchivechunking");

        $("#operation-text").text("Extracting Archive Files");

        if (zipEnabled || isManualExtraction) {
            if(chunkingEnabled){
                DUPX.runChunkedExtraction(undefined);
            } else {
                DUPX.runStandardExtraction();
            }
        } else {
            DUPX.kickOffDupArchiveExtract();
        }
    }

    DUPX.processNext = function ()
    {
        DUPX.startExtraction();
    };

    DUPX.updateProgressPercent = function (percent)
    {
        var percentString = '';
        if (percent > 0) {
            percentString = ' ' + percent + '%';
        }
        $("#progress-pct").text(percentString);
    };

    DUPX.updateDupArchiveProgress = function(itemIndex, totalItems)
    {
        itemIndex++;
        var itemIndexString		= DUPX.Util.formatBytes(itemIndex);  //itemIndex.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var totalItemsString	= DUPX.Util.formatBytes(totalItems); //totalItems.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var s = "Bytes processed: " + itemIndexString + " of " + totalItemsString;
        $("#secondary-progress-text").text(s);
    }

    DUPX.updateZipArchiveProgress = function(itemIndex, totalItems)
    {
        itemIndex++;
        var itemIndexString		= itemIndex.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var totalItemsString	= totalItems.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var s =  "Files processed: " + itemIndexString + " of " + totalItemsString;
        $("#secondary-progress-text").text(s);
    }

    DUPX.clearDupArchiveStatusTimer = function ()
    {
        if (DUPX.dupArchiveStatusIntervalID != -1) {
            clearInterval(DUPX.dupArchiveStatusIntervalID);
            DUPX.dupArchiveStatusIntervalID = -1;
        }
    };

   DUPX.getCriticalFailureText = function(failures)
{
	var retVal = null;

	if((failures !== null) && (typeof failures !== 'undefined')) {
		var len = failures.length;

		for(var j = 0; j < len; j++) {
			failure = failures[j];
			if(failure.isCritical) {
				retVal = failure.description;
				break;
			}
		}
	}

	return retVal;
};

DUPX.DAWSProcessingFailed = function(errorText)
{
	DUPX.clearDupArchiveStatusTimer();
	$('#ajaxerr-data').html(errorText);
	DUPX.hideProgressBar();
}

DUPX.handleDAWSProcessingProblem = function(errorText, pingDAWS)
{
	DUPX.DAWS.FailureCount++;

	if(DUPX.DAWS.FailureCount <= DUPX.DAWS.MaxRetries) {
		var callback = DUPX.pingDAWS;

		if(pingDAWS) {
			console.log('!!!PING FAILURE #' + DUPX.DAWS.FailureCount);
		} else {
			console.log('!!!KICKOFF FAILURE #' + DUPX.DAWS.FailureCount);
			callback = DUPX.kickOffDupArchiveExtract;
		}

		DUPX.throttleDelay = 9;	// Equivalent of 'low' server throttling
		console.log('Relaunching in ' + DUPX.DAWS.RetryDelayInMs);
		setTimeout(callback, DUPX.DAWS.RetryDelayInMs);
	}
	else {
		console.log('Too many failures.');
		DUPX.DAWSProcessingFailed(errorText);
	}
};


DUPX.handleDAWSCommunicationProblem = function(xHr, pingDAWS, textStatus, page)
{
	DUPX.DAWS.FailureCount++;

	if(DUPX.DAWS.FailureCount <= DUPX.DAWS.MaxRetries) {

		var callback = DUPX.pingDAWS;

		if(pingDAWS) {
			console.log('!!!PING FAILURE #' + DUPX.DAWS.FailureCount);
		} else {
			console.log('!!!KICKOFF FAILURE #' + DUPX.DAWS.FailureCount);
			callback = DUPX.kickOffDupArchiveExtract;
		}
		console.log(xHr);
		DUPX.throttleDelay = 9;	// Equivalent of 'low' server throttling
		console.log('Relaunching in ' + DUPX.DAWS.RetryDelayInMs);
		setTimeout(callback, DUPX.DAWS.RetryDelayInMs);
	}
	else {
		console.log('Too many failures.');
		DUPX.ajaxCommunicationFailed(xHr, textStatus, page);
	}
};

// Will either query for status or push it to continue the extraction
DUPX.pingDAWS = function ()
{
	console.log('pingDAWS:start');
	var request = new Object();
	var isClientSideKickoff = DUPX.isClientSideKickoff();

	if (isClientSideKickoff) {
		console.log('pingDAWS:client side kickoff');
		request.action = "expand";
		request.client_driven = 1;
		request.throttle_delay = DUPX.throttleDelay;
		request.worker_time = DUPX.DAWS.PingWorkerTimeInSec;
	} else {
		console.log('pingDAWS:not client side kickoff');
		request.action = "get_status";
	}

	console.log("pingDAWS:action=" + request.action);
	console.log("daws url=" + DUPX.DAWS.Url);

	$.ajax({
		type: "POST",
		timeout: DUPX.DAWS.PingWorkerTimeInSec * 2000, // Double worker time and convert to ms
		url: DUPX.DAWS.Url,
		data: JSON.stringify(request),
		success: function (respData, textStatus, xHr) {
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                console.log('AJAX error. textStatus=');
                console.log(textStatus);
                DUPX.handleDAWSCommunicationProblem(xHr, true, textStatus, 'ping');
                return false;
            }

			DUPX.DAWS.FailureCount = 0;
			console.log("pingDAWS:AJAX success. Resetting failure count");

			// DATA FIELDS
			// archive_offset, archive_size, failures, file_index, is_done, timestamp
			if (typeof (data) != 'undefined' && data.pass == 1) {

				console.log("pingDAWS:Passed");

				var status = data.status;
				var percent = Math.round((status.archive_offset * 100.0) / status.archive_size);

				console.log("pingDAWS:updating progress percent");
				DUPX.updateProgressPercent(percent);
                DUPX.updateDupArchiveProgress(status.archive_offset, status.archive_size);

				var criticalFailureText = DUPX.getCriticalFailureText(status.failures);

				if(status.failures.length > 0) {
					console.log("pingDAWS:There are failures present. (" + status.failures.length) + ")";
				}

				if (criticalFailureText === null) {
					console.log("pingDAWS:No critical failures");
					if (status.is_done) {

						console.log("pingDAWS:archive has completed");
						if(status.failures.length > 0) {

							console.log(status.failures);
							var errorMessage = "pingDAWS:Problems during extract. These may be non-critical so continue with install.\n------\n";
							var len = status.failures.length;

							for(var j = 0; j < len; j++) {
								failure = status.failures[j];
								errorMessage += failure.subject + ":" + failure.description + "\n";
							}

							alert(errorMessage);
						}

						DUPX.clearDupArchiveStatusTimer();
						console.log("pingDAWS:calling finalizeDupArchiveExtraction");
						DUPX.finalizeDupArchiveExtraction(status);
						console.log("pingDAWS:after finalizeDupArchiveExtraction");

						var dataJSON = JSON.stringify(data);

						// Don't stop for non-critical failures - just display those at the end
						$("#ajax-logging").val($("input:radio[name=logging]:checked").val());
						$("#ajax-retain-config").val($("#retain_config").is(":checked") ? 1 : 0);
						$("#ajax-json").val(escape(dataJSON));

                        if ($("#remove-redundant").is(":checked")) {
                            $("#ajax-remove-redundant").val(1);
                        } else{
                            $("#ajax-remove-redundant").val(0);
                        }

                        <?php if($show_multisite) : ?>
                        if ($("#full-network").is(":checked")) {
                            $("#ajax-subsite-id").val(-1);
                        } else {
                            $("#ajax-subsite-id").val($('#subsite-id').val());                            
                        }
                        <?php endif; ?>

						<?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
						setTimeout(function () {
							$('#s1-result-form').submit();
						}, 500);
						<?php endif; ?>
						$('#progress-area').fadeOut(1000);
						//Failures aren't necessarily fatal - just record them for later display

						$("#ajax-json-debug").val(dataJSON);
					} else if (isClientSideKickoff) {
						console.log('pingDAWS:Archive not completed so continue ping DAWS in 500');
						setTimeout(DUPX.pingDAWS, 500);
					}
				}
				else {
					console.log("pingDAWS:critical failures present");
					// If we get a critical failure it means it's something we can't recover from so no purpose in retrying, just fail immediately.
					var errorString = 'Error Processing Step 1<br/>';
					errorString += criticalFailureText;
					DUPX.DAWSProcessingFailed(errorString);
				}
			} else {
				var errorString = 'Error Processing Step 1<br/>';
				errorString += data.error;
				DUPX.handleDAWSProcessingProblem(errorString, true);
			}
		},
		error: function (xHr, textStatus) {
			console.log('AJAX error. textStatus=');
			console.log(textStatus);
			DUPX.handleDAWSCommunicationProblem(xHr, true, textStatus, 'ping');
		}
	});
};


DUPX.isClientSideKickoff = function()
{
	return $('#clientside_kickoff').is(':checked');
}

DUPX.areConfigFilesPreserved = function()
{
	return $('#retain_config').is(':checked');
}

DUPX.kickOffDupArchiveExtract = function ()
{
	console.log('kickOffDupArchiveExtract:start');
	var $form = $('#s1-input-form');
	var request = new Object();
	var isClientSideKickoff = DUPX.isClientSideKickoff();

	request.action = "start_expand";
	request.archive_filepath = '<?php echo $archive_path; ?>';
	request.restore_directory = '<?php echo $root_path; ?>';
	request.worker_time = DUPX.DAWS.KickoffWorkerTimeInSec;
	request.client_driven = isClientSideKickoff ? 1 : 0;
	request.throttle_delay = DUPX.throttleDelay;
	request.filtered_directories = ['dup-installer'];

    if(!DUPX.areConfigFilesPreserved()) {
        request.file_renames = {".htaccess":"htaccess.orig"};
    }

	var requestString = JSON.stringify(request);

	if (!isClientSideKickoff) {
		console.log('kickOffDupArchiveExtract:Setting timer');
		// If server is driving things we need to poll the status
		DUPX.dupArchiveStatusIntervalID = setInterval(DUPX.pingDAWS, DUPX.DAWS.StatusPeriodInMS);
	}
	else {
		console.log('kickOffDupArchiveExtract:client side kickoff');
	}

	console.log("daws url=" + DUPX.DAWS.Url);
	console.log("requeststring=" + requestString);

	$.ajax({
		type: "POST",
		timeout: DUPX.DAWS.KickoffWorkerTimeInSec * 2000,  // Double worker time and convert to ms
		url: DUPX.DAWS.Url,
		data: requestString,
		beforeSend: function () {
			DUPX.showProgressBar();
			$form.hide();
			$('#s1-result-form').show();
			DUPX.updateProgressPercent(0);
		},
		success: function (respData, textStatus, xHr) {
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                console.log('kickOffDupArchiveExtract:AJAX error. textStatus=', textStatus);
			    DUPX.handleDAWSCommunicationProblem(xHr, false, textStatus);
                return false;
            }

			console.log('kickOffDupArchiveExtract:success');
			if (typeof (data) != 'undefined' && data.pass == 1) {

				var criticalFailureText = DUPX.getCriticalFailureText(status.failures);

				if (criticalFailureText === null) {

					var dataJSON = JSON.stringify(data);

					//RSR TODO:Need to check only for FATAL errors right now - have similar failure check as in pingdaws
					DUPX.DAWS.FailureCount = 0;
					console.log("kickOffDupArchiveExtract:Resetting failure count");

					$("#ajax-json-debug").val(dataJSON);
					if (typeof (data) != 'undefined' && data.pass == 1) {

						if (isClientSideKickoff) {
							console.log('kickOffDupArchiveExtract:Initial ping DAWS in 500');
							setTimeout(DUPX.pingDAWS, 500);
						}

					} else {
						$('#ajaxerr-data').html('Error Processing Step 1');
						DUPX.hideProgressBar();
					}
				} else {
					// If we get a critical failure it means it's something we can't recover from so no purpose in retrying, just fail immediately.
					var errorString = 'kickOffDupArchiveExtract:Error Processing Step 1<br/>';
					errorString += criticalFailureText;
					DUPX.DAWSProcessingFailed(errorString);
				}
			} else {
				var errorString = 'kickOffDupArchiveExtract:Error Processing Step 1<br/>';
				errorString += data.error;
				DUPX.handleDAWSProcessingProblem(errorString, false);
			}
		},
		error: function (xHr, textStatus) {

			console.log('kickOffDupArchiveExtract:AJAX error. textStatus=', textStatus);
			DUPX.handleDAWSCommunicationProblem(xHr, false, textStatus);
		}
	});
};

DUPX.finalizeDupArchiveExtraction = function(dawsStatus)
{
	console.log("finalizeDupArchiveExtraction:start");
	var $form = $('#s1-input-form');
	$("#s1-input-form-extra-data").val(JSON.stringify(dawsStatus));
	console.log("finalizeDupArchiveExtraction:after stringify dawsstatus");
	var formData = $form.serialize();

	$.ajax({
		type: "POST",
		timeout: 30000,
		url: window.location.href,
		data: formData,
		beforeSend: function () {

		},
		success: function (respData, textStatus, xHr) {
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                console.log("finalizeDupArchiveExtraction:error");
                console.log(xHr.statusText);
                console.log(xHr.getAllResponseHeaders());
                console.log(xHr.responseText);
                return false;
            }
			console.log("finalizeDupArchiveExtraction:success");
		},
		error: function (xHr) {
			console.log("finalizeDupArchiveExtraction:error");
			console.log(xHr.statusText);
			console.log(xHr.getAllResponseHeaders());
			console.log(xHr.responseText);
		}
	});
};

/**
 * Performs Ajax post to either do a zip or manual extract and then create db
 */
DUPX.runStandardExtraction = function ()
{
	var $form = $('#s1-input-form');

	//1800000 = 30 minutes
	//If the extraction takes longer than 30 minutes then user
	//will probably want to do a manual extraction or even FTP
	$.ajax({
		type: "POST",
		timeout: 1800000,
		url: window.location.href,
		data: $form.serialize(),
		beforeSend: function () {
			DUPX.showProgressBar();
			$form.hide();
			$('#s1-result-form').show();
		},
		success: function (respData, textStatus, xHr) {
            $("#ajax-json-debug").val(respData);
            var dataJSON = respData;
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
                return false;
            }
			if (typeof (data) != 'undefined' && data.pass == 1) {
				$("#ajax-logging").val($("input:radio[name=logging]:checked").val());
				$("#ajax-retain-config").val($("#retain_config").is(":checked") ? 1 : 0);
                $("#ajax-json").val(escape(dataJSON));
                
                if ($("#remove-redundant").is(":checked")) {
                    $("#ajax-remove-redundant").val(1);
                }else{
                    $("#ajax-remove-redundant").val(0);
                }

                <?php if($show_multisite) : ?>
					if ($("#full-network").is(":checked")) {
						$("#ajax-subsite-id").val(-1);
					} else {
						$("#ajax-subsite-id").val($('#subsite-id').val());                        
					}
                <?php endif; ?>

				<?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
					setTimeout(function () {$('#s1-result-form').submit();}, 500);
				<?php endif; ?>
				$('#progress-area').fadeOut(1000);
			} else {
				$('#ajaxerr-data').html('Error Processing Step 1');
				DUPX.hideProgressBar();
			}
		},
		error: function (xHr, textStatus) {
			DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
		}
	});
};

DUPX.runChunkedExtraction = function (data)
{
    var $form = $('#s1-input-form');
    var dataToSend;
    var chunkData;

    console.log('runChunkedExtraction called.');

    if(typeof (data) == 'undefined'){
        $("#progress-pct").text("");
        $("#secondary-progress-text").text("");
        chunkData = {
            archive_offset: 0,
            pass: -1
        };
    }else{
        chunkData = data;
    }

    dataToSend = $form.serialize()+'&'+$.param(chunkData);

    $.ajax({
        type: "POST",
        timeout: 1800000,
        url: window.location.href,
        data: dataToSend,
        beforeSend: function () {
            if(typeof (data) == 'undefined'){
                DUPX.showProgressBar();
                $form.hide();
                $('#s1-result-form').show();
                DUPX.updateProgressPercent(0);
            }
        },
        success: function (respData, textStatus, xHr) {
            if(typeof (respData) != 'undefined'){
                var dataJSON = respData;
                $("#ajax-json-debug").val(respData);
                try {
                    var data = DUPX.parseJSON(respData);
                } catch(err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
                    return false;
                }
                if (data.pass == 1) {
                    $("#ajax-logging").val($("input:radio[name=logging]:checked").val());
                    $("#ajax-retain-config").val($("#retain_config").is(":checked") ? 1 : 0);
                    $("#ajax-json").val(escape(dataJSON));

                    if ($("#remove-redundant").is(":checked")) {
                        $("#ajax-remove-redundant").val(1);
                    }else{
                        $("#ajax-remove-redundant").val(0);
                    }

                    <?php if($show_multisite) : ?>
                    if ($("#full-network").is(":checked")) {
                        $("#ajax-subsite-id").val(-1);
                    } else {
                        $("#ajax-subsite-id").val($('#subsite-id').val());                        
                    }
                    <?php endif; ?>

                    <?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
                    setTimeout(function () {
                        $('#s1-result-form').submit();
                    }, 500);
                    <?php endif; ?>
                    $('#progress-area').fadeOut(1000);
                } else if(data.pass == -1){
                    var percent = Math.round((data.archive_offset * 100.0) / data.num_files);
                    $("#progress-notice").html(data.zip_arc_chunk_notice);
                    
                    DUPX.updateProgressPercent(percent);
                    DUPX.updateZipArchiveProgress(data.archive_offset, data.num_files);
                    DUPX.runChunkedExtraction(data);
                } else {
                    $('#ajaxerr-data').html('Error Processing Step 1');
                    DUPX.hideProgressBar();
                }
            }
        },
        error: function (xHr, textStatus) {
            DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
        }
    });
};


DUPX.ajaxCommunicationFailed = function (xhr, textStatus, page)
{
	var status = "<b>Server Code:</b> " + xhr.status + "<br/>";
	status += "<b>Status:</b> " + xhr.statusText + "<br/>";
	status += "<b>Response:</b> " + xhr.responseText + "<hr/>";

	if(textStatus && textStatus.toLowerCase() == "timeout" || textStatus.toLowerCase() == "service unavailable") {

		var default_timeout_message = "<b>Recommendation:</b><br/>";
			default_timeout_message += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116102141#faq-trouble-100-q'>this FAQ item</a> for possible resolutions.";
			default_timeout_message += "<hr>";
			default_timeout_message += "<b>Additional Resources...</b><br/>";
			default_timeout_message += "With thousands of different permutations it's difficult to try and debug/diagnose a server. If you're running into timeout issues and need help we suggest you follow these steps:<br/><br/>";
			default_timeout_message += "<ol>";
				default_timeout_message += "<li><strong>Contact Host:</strong> Tell your host that you're running into PHP/Web Server timeout issues and ask them if they have any recommendations</li>";
				default_timeout_message += "<li><strong>Dedicated Help:</strong> If you're in a time-crunch we suggest that you contact <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116150030#faq-resource-030-q'>professional server administrator</a>. A dedicated resource like this will be able to work with you around the clock to the solve the issue much faster than we can in most cases.</li>";
				default_timeout_message += "<li><strong>Consider Upgrading:</strong> If you're on a budget host then you may run into constraints. If you're running a larger or more complex site it might be worth upgrading to a <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116150030#faq-resource-040-q'>managed VPS server</a>. These systems will pretty much give you full control to use the software without constraints and come with excellent support from the hosting company.</li>";
				default_timeout_message += "<li><strong>Contact SnapCreek:</strong> We will try our best to help configure and point users in the right direction, however these types of issues can be time-consuming and can take time from our support staff.</li>";
			default_timeout_message += "</ol>";

		if(page)
		{
			switch(page)
			{
				default:
					status += default_timeout_message;
					break;
				case 'extract':
					status += "<b>Recommendation:</b><br/>";
					status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q'>this FAQ item</a> for possible resolutions.<br/><br/>";
					break;
				case 'ping':
					status += "<b>Recommendation:</b><br/>";
					status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116152758#faq-trouble-030-q'>this FAQ item</a> for possible resolutions.<br/><br/>";
					break;
                case 'delete-site':
                    status += "<b>Recommendation:</b><br/>";
					status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116153643#faq-installer-120-q'>this FAQ item</a> for possible resolutions.<br/><br/>";
					break;
			}
		}
		else
		{
			status += default_timeout_message;
		}

	}
	else if ((xhr.status == 403) || (xhr.status == 500)) {
		status += "<b>Recommendation:</b><br/>";
		status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-120-q'>this FAQ item</a> for possible resolutions.<br/><br/>"
	} else if ((xhr.status == 0) || (xhr.status == 200)) {
		status += "<b>Recommendation:</b><br/>";
		status += "Possible server timeout! Performing a 'Manual Extraction' can avoid timeouts.";
		status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q'>this FAQ item</a> for a complete overview.<br/><br/>"
	} else {
		status += "<b>Additional Resources:</b><br/> ";
		status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/'>Help Resources</a><br/>";
		status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/'>Technical FAQ</a>";
	}

	$('#ajaxerr-data').html(status);
	DUPX.hideProgressBar();
};

/** Go back on AJAX result view */
DUPX.hideErrorResult = function ()
{
	$('#s1-result-form').hide();
	$('#s1-input-form').show(200);
}

/**
 * Accetps Usage Warning */
DUPX.acceptWarning = function ()
{
	if ($("#accept-warnings").is(':checked')) {
		$("#s1-deploy-btn").removeAttr("disabled");
		$("#s1-deploy-btn").removeAttr("title");
	} else {
		$("#s1-deploy-btn").attr("disabled", "true");
		$("#s1-deploy-btn").attr("title", "<?php echo $agree_msg; ?>");
	}
};

DUPX.onSafeModeSwitch = function ()
{
    var mode = $('#exe_safe_mode').val();
    if(mode == 0){
        $("#retain_config").removeAttr("disabled");
    }else if(mode == 1 || mode ==2){
        if($("#retain_config").is(':checked'))
                    $("#retain_config").removeAttr("checked");
        $("#retain_config").attr("disabled", true);
    }

    $('#exe-safe-mode').val(mode);
    console.log("mode set to"+mode);
};
//DOCUMENT LOAD
$(document).ready(function ()
{
	DUPX.DAWS = new Object();
	DUPX.DAWS.Url = window.location.href + '?is_daws=1&daws_csrf_token=<?php echo DUPX_CSRF::generate('daws');?>';
	DUPX.DAWS.StatusPeriodInMS = 10000;
	DUPX.DAWS.PingWorkerTimeInSec = 9;
	DUPX.DAWS.KickoffWorkerTimeInSec = 6; // Want the initial progress % to come back quicker

    DUPX.DAWS.MaxRetries = 10;
	DUPX.DAWS.RetryDelayInMs = 8000;

	DUPX.dupArchiveStatusIntervalID = -1;
	DUPX.DAWS.FailureCount = 0;
	DUPX.throttleDelay = 0;

	//INIT Routines
	$("*[data-type='toggle']").click(DUPX.toggleClick);
	$("#tabs").tabs();
	DUPX.acceptWarning();

    <?php
    $isWindows = DUPX_U::isWindows();
    if (!$isWindows) {
    ?>
        $('#set_file_perms').trigger("click");
        $('#set_dir_perms').trigger("click");
    <?php
    }
    ?>

	DUPX.toggleSetupType();

	<?php echo ($arcCheck == 'Fail') ? "$('#s1-area-archive-file-link').trigger('click');" : ""; ?>
	<?php echo (!$all_success) ? "$('#s1-area-sys-setup-link').trigger('click');" : ""; ?>
});
</script>
