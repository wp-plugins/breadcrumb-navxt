<?php
/*
Plugin Name: Breadcrumb NavXT - Adminstration Interface
Plugin URI: http://mtekk.weblogs.us/code/breadcrumb-navxt/
Description: Adds a breadcrumb navigation showing the visitor&#39;s path to their current location. For details on how to use this plugin visit <a href="http://mtekk.weblogs.us/code/breadcrumb-navxt/">Breadcrumb NavXT</a>. 
Version: 2.0.3
Author: John Havlik
Author URI: http://mtekk.weblogs.us/
*/
/*  Copyright 2007-2008  John Havlik  (email : mtekkmonkey@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
$bcn_admin_version = "2.0.3";
$bcn_admin_req = 8;
//Include the breadcrumb class if needed
if(!class_exists('bcn_breadcrumb'))
{
	include(dirname(__FILE__)."/breadcrumb_navxt_class.php");
}
//Include the supplemental functions
include(dirname(__FILE__)."/breadcrumb_navxt_api.php");
//Security function
function bcn_security()
{
	global $userdata, $bcn_admin_req, $bcn_version, $wp_version;
	get_currentuserinfo();
	if ($userdata->user_level < $bcn_admin_req)
	{
		if($userdata->user_level == NULL)
		{
			_e('<strong>Aborting: WordPress API Malfunction</strong><br /> For some reason the 
				function get_currentuserinfo() did not behave as expected. Please report this bug
				to the plug-in author. In your report please specify your WordPress version, PHP version,
				Apache (or whatever HTTP server you are using) verion, and the version of the plug-in you 
				are using.<br />', 'breadcrumb_navxt');
			_e('WordPress version: ', 'breadcrumb_navxt');
			echo $wp_version . '<br />';
			_e('PHP version: ', 'breadcrumb_navxt');
			echo phpversion() . '<br />';
			_e('Plug-in version: ', 'breadcrumb_navxt');
			echo $bcn_version . "<br />";
		}
		else
		{
			_e('<strong>Aborting: Insufficient Privleges</strong><br /> Your User Level: ', 'breadcrumb_navxt');
			echo $userdata->user_level;
			_e('<br /> Required User Level: ', 'breadcrumb_navxt');
			echo $bcn_admin_req . '<br />';
		}
		die(); 
	}
}
//Install script
function bcn_install()
{
	global $bcn_admin_req, $bcn_version;
	bcn_security();
	if(get_option('bcn_version') != $bcn_version)
	{
		update_option('bcn_version' , $bcn_version);
		update_option('bcn_preserve', 0);
		update_option('bcn_static_frontpage', 'false');
		update_option('bcn_url_blog', '');
		update_option('bcn_home_display', 'true');
		update_option('bcn_home_link', 'true');
		update_option('bcn_title_home', 'Home');
		update_option('bcn_title_blog', 'Blog');
		update_option('bcn_separator', '&nbsp;&gt;&nbsp;');
		update_option('bcn_search_prefix', 'Search results for &#39;');
		update_option('bcn_search_suffix', '&#39;');
		update_option('bcn_author_prefix', 'Posts by ');
		update_option('bcn_author_suffix', '');
		update_option('bcn_author_display', 'display_name');
		update_option('bcn_singleblogpost_prefix', 'Blog article:&nbsp;');
		update_option('bcn_singleblogpost_suffix', '');
		update_option('bcn_page_prefix', '');
		update_option('bcn_page_suffix', '');
		update_option('bcn_urltitle_prefix', 'Browse to:&nbsp;');
		update_option('bcn_urltitle_suffix', '');
		update_option('bcn_archive_category_prefix', 'Archive by category &#39;');
		update_option('bcn_archive_category_suffix', '&#39;');
		update_option('bcn_archive_date_prefix', 'Archive: ');
		update_option('bcn_archive_date_suffix', '');
		update_option('bcn_archive_date_format', 'EU');
		update_option('bcn_attachment_prefix', 'Attachment:&nbsp;');
		update_option('bcn_attachment_suffix', '');
		update_option('bcn_archive_tag_prefix', 'Archive by tag &#39;');
		update_option('bcn_archive_tag_suffix', '&#39;');
		update_option('bcn_title_404', '404');
		update_option('bcn_link_current_item', 'false');
		update_option('bcn_current_item_urltitle', 'Link of current page (click to refresh)');
		update_option('bcn_current_item_style_prefix', '');
		update_option('bcn_current_item_style_suffix', '');
		update_option('bcn_posttitle_maxlen', 0);
		update_option('bcn_paged_display', 'false');
		update_option('bcn_paged_prefix', ', Page&nbsp;');
		update_option('bcn_paged_suffix', '');
		update_option('bcn_singleblogpost_category_display', 'true');
		update_option('bcn_singleblogpost_category_prefix', '');
		update_option('bcn_singleblogpost_category_suffix', '');
	}
}
//Display a breadcrumb, only used if admin interface is used
function breadcrumb_nav_xt_display()
{
	//Playing things really safe here
	if(class_exists('bcn_breadcrumb'))
	{
		//Make new breadcrumb object
		$breadcrumb = new bcn_breadcrumb;
		//Set the settings
		$breadcrumb->opt['static_frontpage'] = get_option('bcn_static_frontpage');
		$breadcrumb->opt['url_blog'] = get_option('bcn_url_blog');
		$breadcrumb->opt['home_display'] = get_option('bcn_home_display');
		$breadcrumb->opt['home_link'] = get_option('bcn_home_link');
		$breadcrumb->opt['title_home'] = get_option('bcn_title_home');
		$breadcrumb->opt['title_blog'] = get_option('bcn_title_blog');
		$breadcrumb->opt['separator'] = get_option('bcn_separator');
		$breadcrumb->opt['search_prefix'] = get_option('bcn_search_prefix');
		$breadcrumb->opt['search_suffix'] = get_option('bcn_search_suffix');
		$breadcrumb->opt['author_prefix'] = get_option('bcn_author_prefix');
		$breadcrumb->opt['author_suffix'] = get_option('bcn_author_suffix');
		$breadcrumb->opt['author_display'] = get_option('bcn_author_display');
		$breadcrumb->opt['attachment_prefix'] = get_option('bcn_attachment_prefix');
		$breadcrumb->opt['attachment_suffix'] = get_option('bcn_attachment_suffix');
		$breadcrumb->opt['singleblogpost_prefix'] = get_option('bcn_singleblogpost_prefix');
		$breadcrumb->opt['singleblogpost_suffix'] = get_option('bcn_singleblogpost_suffix');
		$breadcrumb->opt['page_prefix'] = get_option('bcn_page_prefix');
		$breadcrumb->opt['page_suffix'] = get_option('bcn_page_suffix');
		$breadcrumb->opt['urltitle_prefix'] = get_option('bcn_urltitle_prefix');
		$breadcrumb->opt['urltitle_suffix'] = get_option('bcn_urltitle_suffix');
		$breadcrumb->opt['archive_category_prefix'] = get_option('bcn_archive_category_prefix');
		$breadcrumb->opt['archive_category_suffix'] = get_option('bcn_archive_category_suffix');
		$breadcrumb->opt['archive_date_prefix'] = get_option('bcn_archive_date_prefix');
		$breadcrumb->opt['archive_date_suffix'] = get_option('bcn_archive_date_suffix');
		$breadcrumb->opt['archive_date_format'] = get_option('bcn_archive_date_format');
		$breadcrumb->opt['archive_tag_prefix'] = get_option('bcn_archive_tag_prefix');
		$breadcrumb->opt['archive_tag_suffix'] = get_option('bcn_archive_tag_suffix');
		$breadcrumb->opt['title_404'] = get_option('bcn_title_404');
		$breadcrumb->opt['link_current_item'] = get_option('bcn_link_current_item');
		$breadcrumb->opt['current_item_urltitle'] = get_option('bcn_current_item_urltitle');
		$breadcrumb->opt['current_item_style_prefix'] = get_option('bcn_current_item_style_prefix');
		$breadcrumb->opt['current_item_style_suffix'] = get_option('bcn_current_item_style_suffix');
		$breadcrumb->opt['posttitle_maxlen'] = get_option('bcn_posttitle_maxlen');
		$breadcrumb->opt['paged_display'] = get_option('bcn_paged_display');
		$breadcrumb->opt['paged_prefix'] = get_option('bcn_paged_prefix');
		$breadcrumb->opt['paged_suffix'] = get_option('bcn_paged_suffix');
		$breadcrumb->opt['singleblogpost_category_display'] = get_option('bcn_singleblogpost_category_display');
		$breadcrumb->opt['singleblogpost_category_prefix'] = get_option('bcn_singleblogpost_category_prefix');
		$breadcrumb->opt['singleblogpost_category_suffix'] = get_option('bcn_singleblogpost_category_suffix');
		//Display the breadcrumb
		$breadcrumb->display();
	}
}
//Sets the settings
function bcn_admin_options()
{
	global $wpdb, $bcn_admin_req;
	bcn_security();
	update_option('bcn_static_frontpage', bcn_get('static_frontpage'));
	update_option('bcn_url_blog', bcn_get('url_blog'));
	update_option('bcn_home_display', bcn_get('home_display'));
	update_option('bcn_home_link', bcn_get('home_link'));
	update_option('bcn_title_home', bcn_get('title_home'));
	update_option('bcn_title_blog', bcn_get('title_blog'));
	update_option('bcn_separator', bcn_get('separator'));
	update_option('bcn_search_prefix', bcn_get('search_prefix'));
	update_option('bcn_search_suffix', bcn_get('search_suffix'));
	update_option('bcn_author_prefix', bcn_get('author_prefix'));
	update_option('bcn_author_suffix', bcn_get('author_suffix'));
	update_option('bcn_author_display', bcn_get('author_display'));
	update_option('bcn_attachment_prefix', bcn_get('attachment_prefix'));
	update_option('bcn_attachment_suffix', bcn_get('attachment_suffix'));
	update_option('bcn_singleblogpost_prefix', bcn_get('singleblogpost_prefix'));
	update_option('bcn_singleblogpost_suffix', bcn_get('singleblogpost_suffix'));
	update_option('bcn_page_prefix', bcn_get('page_prefix'));
	update_option('bcn_page_suffix', bcn_get('page_suffix'));
	update_option('bcn_urltitle_prefix', bcn_get('urltitle_prefix'));
	update_option('bcn_urltitle_suffix',	bcn_get('urltitle_suffix'));
	update_option('bcn_archive_category_prefix', bcn_get('archive_category_prefix'));
	update_option('bcn_archive_category_suffix', bcn_get('archive_category_suffix'));
	update_option('bcn_archive_date_prefix', bcn_get('archive_date_prefix'));
	update_option('bcn_archive_date_suffix', bcn_get('archive_date_suffix'));
	update_option('bcn_archive_date_format', bcn_get('archive_date_format'));
	update_option('bcn_archive_tag_prefix', bcn_get('archive_tag_prefix'));
	update_option('bcn_archive_tag_suffix', bcn_get('archive_tag_suffix'));
	update_option('bcn_title_404', bcn_get('title_404'));
	update_option('bcn_link_current_item', bcn_get('link_current_item'));
	update_option('bcn_current_item_urltitle', bcn_get('current_item_urltitle'));
	update_option('bcn_current_item_style_prefix', bcn_get('current_item_style_prefix'));
	update_option('bcn_current_item_style_suffix', bcn_get('current_item_style_suffix'));
	update_option('bcn_posttitle_maxlen', bcn_get('posttitle_maxlen'));
	update_option('bcn_paged_display', bcn_get('paged_display'));
	update_option('bcn_paged_prefix', bcn_get('paged_prefix'));
	update_option('bcn_paged_suffix', bcn_get('paged_suffix'));
	update_option('bcn_singleblogpost_category_display', bcn_get('singleblogpost_category_display'));
	update_option('bcn_singleblogpost_category_prefix', bcn_get('singleblogpost_category_prefix'));
	update_option('bcn_singleblogpost_category_suffix', bcn_get('singleblogpost_category_suffix'));
}
//Creates link to admin interface
function bcn_add_page()
{
	global $bcn_admin_req;
    add_options_page('Breadcrumb NavXT Settings', 'Breadcrumb NavXT', $bcn_admin_req, 'breadcrumb-nav-xt', 'bcn_admin');
}
//The actual interface
function bcn_admin()
{
	global $bcn_admin_req, $bcn_admin_version, $bcn_version;
	bcn_security();
	bcn_local();
	list($breadcrumb_major, $breadcrumb_minor, $breadcrumb_bugfix) = explode('.', $bcn_version);
	list($major, $minor, $bugfix) = explode('.', $bcn_admin_version);
	if($breadcrumb_major != $major || $breadcrumb_minor != $minor)
	{ ?>
		<div id="message" class="updated fade">
			<p><?php _e('Warning, your version of Breadcrumb NavXT does not match the version supported by this administrative interface. As a result things may not work as intened.', 'breadcrumb_navxt'); ?></p>
			<p><?php _e('Your Breadcrumb NavXT Administration interface version is ', 'breadcrumb_navxt'); echo $bcn_version; ?>.</p>
			<p><?php _e('Your Breadcrumb NavXT version is ', 'breadcrumb_navxt'); echo $bcn_admin_version; ?>.</p>
		</div>
	<?php }
	?>	
	<div class="wrap"><h2>Breadcrumb NavXT Settings:</h2>
	<p><?php printf(__(	'This administration interface allows the full customization of the breadcrumb output with no loss
	of functionality when compared to manual configuration. Each setting is the same as the corresponding
	class option, please refer to the 
	%sdocumentation%s 
	for more detailed explanation of each setting.', 'breadcrumb_navxt'), '<a title="Go to the Breadcrumb NavXT online documentation" href="http://mtekk.weblogs.us/code/breadcrumb-navxt/breadcrumb-navxt-doc/">', '</a>'); ?>
	</p>

	<form action="options-general.php?page=breadcrumb-nav-xt" method="post" id="bcn_admin_options">
		<p class="submit"><input type="submit" name="bcn_admin_options" value="<?php _e('Update Options &raquo;') ?>" /></p>
		<fieldset id="general" class="options">
			<legend><?php _e('General Settings:', 'breadcrumb_navxt'); ?></legend>
			<p>
				<label for="title_blog"><?php _e('Blog Title:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="title_blog" id="title_blog" value="<?php echo bcn_get_option_inputvalue('bcn_title_blog'); ?>" size="32" />
			</p>
			<p>
				<label for="separator"><?php _e('Breadcrumb Separator:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="separator" id="separator" value="<?php echo bcn_get_option_inputvalue('bcn_separator'); ?>" size="32" />
			</p>
			<p>
				<label for="search_prefix"><?php _e('Search Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="search_prefix" id="search_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_search_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="search_suffix"><?php _e('Search Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="search_suffix" id="search_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_search_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="title_404"><?php _e('404 Title:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="title_404" id="title_404" value="<?php echo bcn_get_option_inputvalue('bcn_title_404'); ?>" size="32" />
			</p>
		</fieldset>
		<fieldset id="static_front_page" class="options">
			<legend><?php _e('Static Frontpage Settings:', 'breadcrumb_navxt'); ?></legend>
			<p><?php _e('Static Frontpage:', 'breadcrumb_navxt'); ?> 
				<select name="static_frontpage">
					<?php $bcn_opta = array("true", "false");?>
					<option><?php echo get_option('bcn_static_frontpage'); ?></option>
					<?php foreach($bcn_opta as $option)
					{
						if($option != get_option('bcn_static_frontpage'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="url_blog"><?php _e('Relative Blog URL:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="url_blog" id="url_blog" value="<?php echo bcn_get_option_inputvalue('bcn_url_blog'); ?>" size="32" />
			</p>
			<p><?php _e('Display Home:', 'breadcrumb_navxt'); ?> 
				<select name="home_display">
					<?php $bcn_opta = array("true", "false");?>
					<option><?php echo get_option('bcn_home_display'); ?></option>
					<?php foreach($bcn_opta as $option)
					{
						if($option != get_option('bcn_home_display'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p><?php _e('Display Home Link:', 'breadcrumb_navxt'); ?>
				<select name="home_link">
					<?php $bcn_opta = array("true", "false");?>
					<option><?php echo get_option('bcn_home_link'); ?></option>
					<?php foreach($bcn_opta as $option)
					{
						if($option != get_option('bcn_home_link'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="title_home"><?php _e('Home Title:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="title_home" id="title_home" value="<?php echo bcn_get_option_inputvalue('bcn_title_home'); ?>" size="32" />
			</p>
		</fieldset>
		<fieldset id="author" class="options">
			<legend><?php _e('Author Page Settings:', 'breadcrumb_navxt'); ?></legend>
			<p>
				<label for="author_prefix"><?php _e('Author Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="author_prefix" id="author_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_author_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="author_suffix"><?php _e('Author Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="author_suffix" id="author_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_author_suffix'); ?>" size="32" />
			</p>
			<p><?php _e('Author Display Format:', 'breadcrumb_navxt'); ?> 
				<select name="author_display">
					<?php $bcn_opta = array("display_name", "nickname", "first_name", "last_name");?>
					<option><?php echo get_option('bcn_author_display'); ?></option>
					<?php foreach($bcn_opta as $option)
					{
						if($option != get_option('bcn_author_display'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
		</fieldset>
		<fieldset id="category" class="options">
			<legend><?php _e('Archive Display Settings:', 'breadcrumb_navxt'); ?></legend>
			<p>
				<label for="urltitle_prefix"><?php _e('URL Title Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="urltitle_prefix" id="urltitle_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_urltitle_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="urltitle_suffix"><?php _e('URL Title Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="urltitle_suffix" id="urltitle_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_urltitle_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_category_prefix"><?php _e('Archive by Category Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="archive_category_prefix" id="archive_category_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_category_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_category_suffix"><?php _e('Archive by Category Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="archive_category_suffix" id="archive_category_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_category_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_date_prefix"><?php _e('Archive by Date Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="archive_date_prefix" id="archive_date_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_date_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_date_suffix"><?php _e('Archive by Date Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="archive_date_suffix" id="archive_date_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_date_suffix'); ?>" size="32" />
			</p>
			<p><?php _e('Archive by Date Format:', 'breadcrumb_navxt'); ?> 
				<select name="archive_date_format">
					<?php $bcn_opta = array("EU", "US", "ISO");?>
					<option><?php echo get_option('bcn_archive_date_format'); ?></option>
					<?php foreach($bcn_opta as $option)
					{
						if($option != get_option('bcn_archive_date_format'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="archive_tag_prefix"><?php _e('Archive by Tag Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="archive_tag_prefix" id="archive_tag_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_tag_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_tag_suffix"><?php _e('Archive by Tag Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="archive_tag_suffix" id="archive_tag_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_tag_suffix'); ?>" size="32" />
			</p>
		</fieldset>
		<fieldset id="current" class="options">
			<legend><?php _e('Current Item Settings:', 'breadcrumb_navxt'); ?></legend>
			<p><?php _e('Link Current Item:', 'breadcrumb_navxt'); ?> 
				<select name="link_current_item">
					<?php $bcn_opta = array("true", "false");?>
					<option><?php echo get_option('bcn_link_current_item'); ?></option>
					<?php foreach($bcn_opta as $option)
					{
						if($option != get_option('bcn_link_current_item'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="current_item_urltitle"><?php _e('Current Item URL Title:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="current_item_urltitle" id="current_item_urltitle" value="<?php echo bcn_get_option_inputvalue('bcn_current_item_urltitle'); ?>" size="32" />
			</p>
			<p>
				<label for="current_item_style_prefix"><?php _e('Current Item Style Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="current_item_style_prefix" id="current_item_style_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_current_item_style_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="current_item_style_suffix"><?php _e('Current Item Style Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="current_item_style_suffix" id="current_item_style_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_current_item_style_suffix'); ?>" size="32" />
			</p>
			<p><?php _e('Display Paged Text:', 'breadcrumb_navxt'); ?> 
				<select name="paged_display">
					<?php $bcn_opta = array("true", "false");?>
					<option><?php echo get_option('bcn_paged_display'); ?></option>
					<?php foreach($bcn_opta as $option)
					{
						if($option != get_option('bcn_paged_display'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="paged_prefix"><?php _e('Paged Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="paged_prefix" id="paged_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_paged_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="paged_suffix"><?php _e('Paged Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="paged_suffix" id="paged_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_paged_suffix'); ?>" size="32" />
			</p>
		</fieldset>
		<fieldset id="single" class="options">
			<legend><?php _e('Single Post Settings:', 'breadcrumb_navxt'); ?></legend>
			<p>
				<label for="singleblogpost_prefix"><?php _e('Single Blogpost Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="singleblogpost_prefix" id="singleblogpost_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_singleblogpost_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="singleblogpost_suffix"><?php _e('Single Blogpost Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="singleblogpost_suffix" id="singleblogpost_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_singleblogpost_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="page_prefix"><?php _e('Page Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="page_prefix" id="page_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_page_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="page_suffix"><?php _e('Page Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="page_suffix" id="page_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_page_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="attachment_prefix"><?php _e('Post Attachment Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="attachment_prefix" id="attachment_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_attachment_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="attachment_suffix"><?php _e('Post Attachment Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="attachment_suffix" id="attachment_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_attachment_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="title_home"><?php _e('Post Title Maxlen:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="posttitle_maxlen" id="posttitle_maxlen" value="<?php echo bcn_get_option_inputvalue('bcn_posttitle_maxlen'); ?>" size="10" />
			</p>
			<p>
				<label for="singleblogpost_category_display"><?php _e('Single Blog Post Category Display:', 'breadcrumb_navxt'); ?></label>
				<select name="singleblogpost_category_display">
					<?php $bcn_opta = array("true", "false");?>
					<option><?php echo get_option('bcn_singleblogpost_category_display'); ?></option>
					<?php foreach($bcn_opta as $option)
					{
						if($option != get_option('bcn_singleblogpost_category_display'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="singleblogpost_category_prefix"><?php _e('Single Blog Post Category Prefix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="singleblogpost_category_prefix" id="singleblogpost_category_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_singleblogpost_category_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="singleblogpost_category_suffix"><?php _e('Single Blog Post Category Suffix:', 'breadcrumb_navxt'); ?></label>
				<input type="text" name="singleblogpost_category_suffix" id="singleblogpost_category_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_singleblogpost_category_suffix'); ?>" size="32" />
			</p>
		</fieldset>
		<p class="submit"><input type="submit" name="bcn_admin_options" value="<?php _e('Update Options &raquo;') ?>" /></p>
	</form>
	</div>
	<?php
}
//Additional styles for admin interface
function bcn_options_style()
{
?>
<style>
	fieldset {
	margin-bottom: 5px;
	padding: 10px;
	border: #ccc solid 1px;
	}
	.halfl {
	width: 46.25%;
	float: left;
	}
	.halfr {
	width: 46.25%;
	float: right;
	}
</style>
<?php
}
//WordPress hooks
if(function_exists('add_action')){
	//Installation Script hook
	add_action('activate_breadcrumb-navxt/breadcrumb_navxt_admin.php','bcn_install');
	//WordPress Admin interface hook
	add_action('admin_menu', 'bcn_add_page');
	add_action('admin_head', 'bcn_options_style');
	//Admin Options hook
	if(isset($_POST['bcn_admin_options']))
	{
		add_action('init', 'bcn_admin_options');
	}
}
?>