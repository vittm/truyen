<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( !current_user_can('edit_others_pages') ) { //protection for admin content
	exit(0);
} ?>
<?php
//define all variables the needed alot
include 'the_globals.php';
$post_action = '';
if(isset($_POST["action"])) $post_action = sanitize_text_field($_POST["action"]);
if($post_action == 'update')
{
	//----------------------------------------------------list the options array values
	//----------------------------------------------------list the options array values
	$single_posts_protection = '';
	if(isset($_POST["single_posts_protection"])) $single_posts_protection = sanitize_text_field($_POST["single_posts_protection"]);
	$home_page_protection = '';
	if(isset($_POST["home_page_protection"])) $home_page_protection = sanitize_text_field($_POST["home_page_protection"]);
	$page_protection = '';
	if(isset($_POST["page_protection"])) $page_protection = sanitize_text_field($_POST["page_protection"]);
	
	$exclude_admin_from_protection = '';
	if(isset($_POST["exclude_admin_from_protection"])) $exclude_admin_from_protection = sanitize_text_field($_POST["exclude_admin_from_protection"]);
	
	$home_css_protection = '';
	if(isset($_POST["home_css_protection"])) $home_css_protection = sanitize_text_field($_POST["home_css_protection"]);
	$posts_css_protection = '';
	if(isset($_POST["posts_css_protection"])) $posts_css_protection = sanitize_text_field($_POST["posts_css_protection"]);
	$pages_css_protection = '';
	if(isset($_POST["pages_css_protection"])) $pages_css_protection = sanitize_text_field($_POST["pages_css_protection"]);
	
	$right_click_protection_posts = '';
	if(isset($_POST["right_click_protection_posts"])) $right_click_protection_posts = sanitize_text_field($_POST["right_click_protection_posts"]);
	$right_click_protection_homepage = '';
	if(isset($_POST["right_click_protection_homepage"])) $right_click_protection_homepage = sanitize_text_field($_POST["right_click_protection_homepage"]);
	$right_click_protection_pages = '';
	if(isset($_POST["right_click_protection_pages"])) $right_click_protection_pages = sanitize_text_field($_POST["right_click_protection_pages"]);

	$img = '';
	if(isset($_POST["img"])) $img = sanitize_text_field($_POST["img"]);
	$a = '';
	if(isset($_POST["a"])) $a = sanitize_text_field($_POST["a"]);
	$pb = '';
	if(isset($_POST["pb"])) $pb = sanitize_text_field($_POST["pb"]);
	$input = '';
	if(isset($_POST["input"])) $input = sanitize_text_field($_POST["input"]);
	$h = '';
	if(isset($_POST["h"])) $h = sanitize_text_field($_POST["h"]);
	$textarea = '';
	if(isset($_POST["textarea"])) $textarea = sanitize_text_field($_POST["textarea"]);
	$emptyspaces = '';
	if(isset($_POST["emptyspaces"])) $emptyspaces = sanitize_text_field($_POST["emptyspaces"]);

	$smessage = '';
	if(isset($_POST["smessage"])) $smessage = sanitize_text_field($_POST["smessage"]);
	$alert_msg_img = '';
	if(isset($_POST["alert_msg_img"])) $alert_msg_img = sanitize_text_field($_POST["alert_msg_img"]);
	$alert_msg_a = '';
	if(isset($_POST["alert_msg_a"])) $alert_msg_a = sanitize_text_field($_POST["alert_msg_a"]);
	$alert_msg_pb = '';
	if(isset($_POST["alert_msg_pb"])) $alert_msg_pb = sanitize_text_field($_POST["alert_msg_pb"]);
	$alert_msg_input = '';
	if(isset($_POST["alert_msg_input"])) $alert_msg_input = sanitize_text_field($_POST["alert_msg_input"]);
	$alert_msg_h = '';
	if(isset($_POST["alert_msg_h"])) $alert_msg_h = sanitize_text_field($_POST["alert_msg_h"]);
	$alert_msg_textarea = '';
	if(isset($_POST["alert_msg_textarea"])) $alert_msg_textarea = sanitize_text_field($_POST["alert_msg_textarea"]);
	$alert_msg_emptyspaces = '';
	if(isset($_POST["alert_msg_emptyspaces"])) $alert_msg_emptyspaces = sanitize_text_field($_POST["alert_msg_emptyspaces"]);
	
	//----------------------------------------------------Get the  options array values
	$wccp_settings = 
	Array (
			'single_posts_protection' => $single_posts_protection, // prevent content copy, take 2 parameters
			'home_page_protection' => $home_page_protection, // PROTECT THE HOME PAGE OR NOT
			'page_protection' => $page_protection, // protect pages by javascript
			'right_click_protection_posts' => $right_click_protection_posts, //
			'right_click_protection_homepage' => $right_click_protection_homepage, //
			'right_click_protection_pages' => $right_click_protection_pages, //
			'home_css_protection' => $home_css_protection, // premium option
			'posts_css_protection' => 'No', // premium option
			'pages_css_protection' => 'No', // premium option
			'exclude_admin_from_protection' => 'No', // premium option
			'img' => '', // premium option
			'a' => '', // premium option
			'pb' => '', // premium option
			'input' => '', // premium option
			'h' => '', // premium option
			'textarea' => '', // premium option
			'emptyspaces' => '', // premium option
			'smessage' => $smessage,
			'alert_msg_img' => $alert_msg_img,
			'alert_msg_a' => $alert_msg_a,
			'alert_msg_pb' => $alert_msg_pb,
			'alert_msg_input' => $alert_msg_input,
			'alert_msg_h' => $alert_msg_h,
			'alert_msg_textarea' => $alert_msg_textarea,
			'alert_msg_emptyspaces' => $alert_msg_emptyspaces
		);

		if ($wccp_settings != '' ) {
		    update_option( 'wccp_settings' , $wccp_settings );
		} else {
		    $deprecated = ' ';
		    $autoload = 'no';
		    add_option( 'wccp_settings', $wccp_settings, $deprecated, $autoload );
		}
}else //no update action
{
	$wccp_settings = wccp_read_options();
}

?>
<style>
#aio_admin_main {
text-align:left;
direction:ltr;
padding:10px;
margin: 10px;
background-color: #ffffff;
border:1px solid #EBDDE2;
display: relative;
overflow: auto;
}
.inner_block{
height: 370px;
display: inline;
min-width:770px;
}
#donate{
    background-color: #EEFFEE;
    border: 1px solid #66DD66;
    border-radius: 10px 10px 10px 10px;
    height: 58px;
    padding: 10px;
    margin: 15px;
    }
.text-font {
    color: #1ABC9C;
    font-size: 14px;
    line-height: 1.5;
    padding-left: 3px;
    transition: color 0.25s linear 0s;
}
.text-font:hover {
    opacity: 1;
    transition: color 0.25s linear 0s;
}
.simpleTabsContent{
	border: 1px solid #E9E9E9;
	padding: 4px;
}
div.simpleTabsContent{
	margin-top:0;
	border: 1px solid #E0E0E0;
    display: none;
    height: 100%;
    min-height: 400px;
    padding: 5px 15px 15px;
}
html {
background: #FFFFFF;
}
.size-full {
    height: auto;
    max-width: 100%;
}
</style>
<div id="aio_admin_main">
<form method="POST">
<input type="hidden" value="update" name="action">
<div class="simpleTabs">
<ul class="simpleTabsNavigation">
    <li><a href="#">Main Settings</a></li>
	<li><a href="#">Premium RightClick Protection</a></li>
	<li><a href="#">Premium Protection by CSS</a></li>
    <li><a href="#">More with pro</a></li>
</ul>
<div class="simpleTabsContent">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td width="77%">
	<h4>Copy Protection using JavaScript (<font color="#008000">Basic Layer</font>):</h4>
	<p><font face="Tahoma" size="2">This is the basic protection layer that uses
	<u>JavaScript</u> to protect the posts, home page content from being copied by any other 
	web site author.</font></p>
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td width="60%">
	<div style="float: left;padding: 4px" id="layer3">
		<table border="0" width="100%" height="320" cellspacing="0" cellpadding="0">
			<tr>
				<td width="221" height="33"><font face="Tahoma" size="2">Posts protection 
				by 
	<u>JavaScript</u></font></td>
				<td>
				<select size="1" name="single_posts_protection">
				<?php 
				if ($wccp_settings['single_posts_protection'] == 'Enabled')
					{
						echo '<option selected>Enabled</option>';
						echo '<option>Disabled</option>';
					}
					else
					{
						echo '<option>Enabled</option>';
						echo '<option selected>Disabled</option>';
					}
				?>
				</select>
				</td>
				<td align="left">
				<p><font face="Tahoma" size="2">&nbsp;For single posts content</font></p></td>
			</tr>
			<tr>
				<td width="221" height="33"><font face="Tahoma" size="2">Homepage 
				protection 
				by 
	<u>JavaScript</u></font></td>
				<td>
				<select size="1" name="home_page_protection">
				<?php 
				if ($wccp_settings['home_page_protection'] == 'Enabled')
					{
						echo '<option selected>Enabled</option>';
						echo '<option>Disabled</option>';
					}
					else
					{
						echo '<option>Enabled</option>';
						echo '<option selected>Disabled</option>';
					}
				?>
				</select>
				</td>
				<td align="left">
				<p><font face="Tahoma" size="2">&nbsp;Don't copy any thing! 
				even from my homepage</font></td>
			</tr>
			<tr>
				<td width="221" height="33"><font face="Tahoma" size="2">Static page's protection</font></td>
				<td>
				<select size="1" name="page_protection">
				<?php 
				if ($wccp_settings['page_protection'] == 'Enabled')
					{
						echo '<option selected>Enabled</option>';
						echo '<option>Disabled</option>';
					}
					else
					{
						echo '<option selected>Disabled</option>';
						echo '<option>Enabled</option>';
					}
				?>
				</select></td>
				<td align="left">
				<p><font face="Tahoma" size="2">&nbsp;Use Premium Settings tab to customize more options</font></td>
			</tr>
			<tr>
				<td width="221" height="33"><font face="Tahoma" size="2">Exclude 
				<u>Admin</u> from protection</font></td>
				<td>
				<p align="center"><font color="#FF0000">Premium</font></td>
				<td align="left">
				<font face="Tahoma" size="2">&nbsp;If <u>Yes</u>, The protection 
				functions will be inactive for the admin when he is logged in</font></td>
			</tr>
			<tr>
				<td width="221" height="33"><font face="Tahoma" size="2">Selection disabled message</font></td>
				<td colspan="2">
				<table border="0" width="49%" cellspacing="0" cellpadding="0">
					<tr>
						<td>
						<input type="text" placeholder="Enter something" class="form-control" name="smessage"  value="<?php echo $wccp_settings['smessage']; ?>"></td>
					</tr>
				</table>
				</td>
			</tr>
			</table></div>
			</td>
			</tr>
	</table>
</td>
	</tr>
</table></div>
<div class="simpleTabsContent">
	<h4>Copy Protection on RightClick (<font color="#008000">Premium Layer 2</font>):</h4>
	<p><font face="Tahoma" size="2">In this protection layer your visitors will 
	be able to <u>right click</u> on a specific page elements only (such as 
	Links as an example)</font></p>
	<div id="layer4">
		<table border="0" width="100%" height="361" cellspacing="0" cellpadding="0">
			<tr>
				<td height="53" width="21%"><font face="Tahoma" size="2">Disable <u>RightClick</u> on</font></td>
				<td height="53">
				<table border="0" width="521" height="100%" cellspacing="1" cellpadding="0">
					<tr>
						<td width="161" height="46">
				<label class="checkbox" for="checkbox1">
					<font face="Tahoma">
					<input data-toggle="checkbox" type="checkbox" name="right_click_protection_posts" value="checked" <?php echo $wccp_settings['right_click_protection_posts']; ?>><font size="2">Posts
						</font></font>
						</label>
						</td>
						<td width="161" height="46">
				<label class="checkbox" for="checkbox1">
					<font face="Tahoma">
					<input data-toggle="checkbox" type="checkbox" name="right_click_protection_homepage" value="checked" <?php echo $wccp_settings['right_click_protection_homepage']; ?>><font size="2">HomePage
						</font></font>
						</label>
						</td>
						<td width="185" height="46">
				<label class="checkbox" for="checkbox1">
					<font face="Tahoma">
					<input data-toggle="checkbox" type="checkbox" name="right_click_protection_pages" value="checked" <?php echo $wccp_settings['right_click_protection_pages']; ?>><font size="2">Static 
				pages 
				</font></font> 
				</label>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td height="44" colspan="2">
				<p align="left"><font color="#FF0000" face="Tahoma" size="2">
				Remaining premium options preview image </font>
				<img src="<?php echo $pluginsurl ?>/images/click-here-arrow.png" id="irc_mi">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<b><font color="#0909FF"><u>
				<a href="http://www.wp-buy.com/product/wp-content-copy-protection-pro/">
				<font color="#0909FF">Preview &amp; Pricing</font></a></u></font></b>
				</td>
			</tr>
			<tr>
				<td height="264" colspan="2">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="http://www.wp-buy.com/product/wp-content-copy-protection-pro/">
				<img class="size-full" border="1" src="<?php echo $pluginsurl ?>/images/right-click-protection.jpg" style="border: 1px dotted #C0C0C0"></a><p>&nbsp;</td>
			</tr>
			</table></div>
</div>
<div class="simpleTabsContent">
<h4>Protection by CSS Techniques (<font color="#008000">Premium Layer 3</font>):</h4>
	<p><font face="Tahoma" size="2">In this protection layer your website will 
	be protected by some <u>CSS</u> tricks that will word even if <u>JavaScript</u> 
	is disabled from the browser settings</font></p>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td width="60%">
	<div style="float: left;padding: 4px" id="layer5">
		<table border="0" width="100%" height="232" cellspacing="0" cellpadding="0">
			<tr>
				<td width="221" height="74"><font face="Tahoma" size="2"><b>Home Page</b> Protection by CSS</font></td>
				<td height="74">
				<select size="1" name="home_css_protection">
				<?php 
				if ($wccp_settings['home_css_protection'] == 'Enabled')
					{
						echo '<option selected>Enabled</option>';
						echo '<option>Disabled</option>';
					}
					else
					{
						echo '<option>Enabled</option>';
						echo '<option selected>Disabled</option>';
					}
				?>
				</select>
				</td>
				<td align="left" height="74">
				<font face="Tahoma" size="2">Protect your Homepage by CSS tricks</font></td>
			</tr>
			<tr>
				<td width="221" height="77"><font face="Tahoma" size="2"><b>Posts</b> Protection by CSS</font></td>
				<td align="center">
				<font color="#FF0000">Premium </font>
				</td>
				<td align="left">
				<font face="Tahoma" size="2">Protect your single posts by CSS tricks</font></td>
			</tr>
			<tr>
				<td width="221"><font face="Tahoma" size="2"><b>Pages</b> Protection by CSS</font></td>
				<td align="center">
				<font color="#FF0000">Premium&nbsp;
				<script>$("select").selectpicker({style: 'btn-hg btn-primary', menuStyle: 'dropdown-inverse'});</script>
				</font>
				</td>
				<td align="left"><font face="Tahoma" size="2">Protect your static pages by CSS tricks</font></td>
			</tr>
			</table></div>
			</td>
			</tr>
	</table>

</div>
<div class="simpleTabsContent" id="layer1">
		<a href="http://www.wp-buy.com/product/wp-content-copy-protection-pro/">
		<img class="size-full" border="1" src="<?php echo $pluginsurl ?>/images/smart-phones-protection.png" style="border: 1px dotted #C0C0C0">
		</a>
		<p></p>
		<a href="http://www.wp-buy.com/product/wp-content-copy-protection-pro/">
		<img class="size-full" border="1" src="<?php echo $pluginsurl ?>/images/watermark-adv.jpg" style="border: 1px dotted #C0C0C0">
		</a>
		<p></p>
		<a href="http://www.wp-buy.com/product/wp-content-copy-protection-pro/">
		<img class="size-full" border="1" src="<?php echo $pluginsurl ?>/images/watermarking-adv-examples.png" style="border: 1px dotted #C0C0C0">
		</a>
		<p></p>
		<p><b><font face="Tahoma" size="2" color="#FFFFFF">
		<span style="background-color: #008000">Basic features:</span></font></b></p>
		<ul>
			<li><font style="font-size: 10pt" face="Tahoma">Protect your content 
			from selection and copy. this plugin makes protecting your posts 
			extremely simple without yelling at your readers</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">No one can save 
			images from your site.</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">No right click or 
			context menu.</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Show alert message, 
			Image Ad or HTML Ad on save images or right click.</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Disable the 
			following keys&nbsp; CTRL+A, CTRL+C, CTRL+X,CTRL+S or CTRL+V.</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Advanced and easy to 
			use control panel.</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">No one can right 
			click images on your site if you want</font></li>
		</ul>
		<p><b><font face="Tahoma" size="2" color="#FFFFFF">
		<span style="background-color: #5B2473">Premium features:</span></font></b></p>
		<ul>
			<li><font style="font-size: 10pt" face="Tahoma">Get full Control on 
			Right click or context menu</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Full watermarking</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Show alert messages, 
			when user made right click on images, text boxes, links, plain 
			text.. etc</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Admin can exclude 
			Home page Or Single posts from being copy protected </font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Admin can disable 
			copy protection for admin users.</font></li>
			<li><font face="Tahoma" size="2">3 protection layers (JavaScript 
			protection, RightClick protection, CSS protection)</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Aggressive image 
			protection (its near impossible for expert users to steal 
			your images !!)</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">compatible with all 
			major theme frameworks</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">compatible with all major browsers</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Tested in IE9, IE10, 
			Firefox, Google Chrome, Opera</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Disables image drag 
			and drop function</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Works on smart 
			phones.</font></li>
			<li><font style="font-size: 10pt" face="Tahoma">Ability to set 
			varying levels of protection per page or post.</font></li>
		</ul>
		</div>
</div><!-- simple tabs div end -->
<p align="right"><input class="btn btn-success" type="submit" value="   Save Settings   " name="B4" style="width: 193; height: 29;">&nbsp;&nbsp;</p>
</form>
</div>
<iframe height="100" frameborder=0 width="470" src="https://wp-buy.com/ads/stats.php"></iframe>