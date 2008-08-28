<?php
/*
Plugin Name: Breadcrumb NavXT - Adminstration Interface
Plugin URI: http://mtekk.weblogs.us/code/breadcrumb-navxt/
Description: Adds a breadcrumb navigation showing the visitor&#39;s path to their current location. This enables the administrative interface for specifying the output of the breadcrumb. For details on how to use this plugin visit <a href="http://mtekk.weblogs.us/code/breadcrumb-navxt/">Breadcrumb NavXT</a>. 
Version: 2.1.99
Author: John Havlik
Author URI: http://mtekk.weblogs.us/
*/
//Include the breadcrumb class (if needed)
if(!class_exists('bcn_breadcrumb'))
{
	require_once(dirname(__FILE__) . '/breadcrumb_navxt_class.php');
}
//Include the supplemental functions
require_once(dirname(__FILE__) . '/breadcrumb_navxt_api.php');

//The administrative interface class
class bcn_admin
{
	private $version;
	private $breadcrumb_trail;
	/**
	 * bcn_admin
	 * 
	 * Administrative interface class default constructor
	 */
	function bcn_admin()
	{
		//Setup our internal version
		$this->version = "3.0.0";
		//We'll let the fail fataly if the class isn't there as we depend on it
		$this->breadcrumb_trail = new bcn_breadcrumb_trail;
		//Installation Script hook
		add_action('activate_breadcrumb-navxt/breadcrumb_navxt_admin.php', array(&$this, 'install'));
		//WordPress Admin interface hook
		add_action('admin_menu', array(&$this, 'add_page'));
		//WordPress Hook for the widget
		add_action('plugins_loaded', array(&$this, 'register_widget'));
		//Admin Options hook
		if(isset($_POST['bcn_admin_options']))
		{
			add_action('init', array(&$this, 'update'));
		}
	}
	/**
	 * security
	 * 
	 * Makes sure the current user can manage options to proceed
	 */
	function security()
	{
		//If the user can not manage options we will die on them
		if(!current_user_can('manage_options'))
		{
			_e('Insufficient privileges to proceed.', 'breadcrumb_navxt');
			die();
		}
	}
	/**
	 * install
	 * 
	 * This setsup and upgrades the database settings, runs on every activation
	 */
	function install()
	{
		//Call our little security function
		$this->security();
		//Reduce db queries by saving this
		$db_version = get_option('bcn_version');
		//If our version is not the same as in the db, time to update
		if($db_version !== $this->version)
		{
			//Split up the db version into it's components
			list($major, $minor, $release) = explode('.', $db_version);
			//For upgrading from 2.1.x
			if($major <= 2 && $minor <= 1 && $release <= 4)
			{
				delete_option('bcn_preserve');
				delete_option('bcn_static_frontpage');
				delete_option('bcn_url_blog');
				delete_option('bcn_home_display');
				delete_option('bcn_home_link');
				delete_option('bcn_title_home');
				delete_option('bcn_title_blog');
				delete_option('bcn_separator');
				delete_option('bcn_search_prefix');
				delete_option('bcn_search_suffix');
				delete_option('bcn_author_prefix');
				delete_option('bcn_author_suffix');
				delete_option('bcn_author_display');
				delete_option('bcn_singleblogpost_prefix');
				delete_option('bcn_singleblogpost_suffix');
				delete_option('bcn_page_prefix');
				delete_option('bcn_page_suffix');
				delete_option('bcn_urltitle_prefix');
				delete_option('bcn_urltitle_suffix');
				delete_option('bcn_archive_category_prefix');
				delete_option('bcn_archive_category_suffix');
				delete_option('bcn_archive_date_prefix');
				delete_option('bcn_archive_date_suffix');
				delete_option('bcn_archive_date_format');
				delete_option('bcn_attachment_prefix');
				delete_option('bcn_attachment_suffix');
				delete_option('bcn_archive_tag_prefix');
				delete_option('bcn_archive_tag_suffix');
				delete_option('bcn_title_404');
				delete_option('bcn_link_current_item');
				delete_option('bcn_current_item_urltitle');
				delete_option('bcn_current_item_style_prefix');
				delete_option('bcn_current_item_style_suffix');
				delete_option('bcn_posttitle_maxlen');
				delete_option('bcn_paged_display');
				delete_option('bcn_paged_prefix');
				delete_option('bcn_paged_suffix');
				delete_option('bcn_singleblogpost_taxonomy');
				delete_option('bcn_singleblogpost_taxonomy_display');
				delete_option('bcn_singleblogpost_category_prefix');
				delete_option('bcn_singleblogpost_category_suffix');
				delete_option('bcn_singleblogpost_tag_prefix');
				delete_option('bcn_singleblogpost_tag_suffix');
			}
			//For upgrading from 2.2.x betas
			if($major < 3)
			{
				//Migrate over stuff
				$this->breadcrumb_trail->opt['home_display'] = str2bool(get_option('bcn_home_display'));
				$this->breadcrumb_trail->opt['home_title'] = get_option('bcn_home_title');
				$this->breadcrumb_trail->opt['home_anchor'] = get_option('bcn_home_anchor');
				$this->breadcrumb_trail->opt['blog_anchor'] = get_option('bcn_blog_anchor');
				$this->breadcrumb_trail->opt['separator'] = get_option('bcn_separator');
				$this->breadcrumb_trail->opt['max_title_length'] = get_option('bcn_max_title_length');
				$this->breadcrumb_trail->opt['current_item_linked'] = str2bool(get_option('bcn_current_item_linked'));
				$this->breadcrumb_trail->opt['current_item_anchor'] = get_option('bcn_current_item_anchor');
				$this->breadcrumb_trail->opt['current_item_prefix'] = get_option('bcn_current_item_prefix');
				$this->breadcrumb_trail->opt['current_item_suffix'] = get_option('bcn_current_item_suffix');
				$this->breadcrumb_trail->opt['paged_prefix'] = get_option('bcn_paged_prefix');
				$this->breadcrumb_trail->opt['paged_suffix'] = get_option('bcn_paged_suffix');
				$this->breadcrumb_trail->opt['paged_display'] = str2bool(get_option('bcn_paged_display'));
				$this->breadcrumb_trail->opt['page_prefix'] = get_option('bcn_page_prefix');
				$this->breadcrumb_trail->opt['page_suffix'] = get_option('bcn_page_suffix');
				$this->breadcrumb_trail->opt['page_anchor'] = get_option('bcn_page_anchor');
				$this->breadcrumb_trail->opt['post_prefix'] = get_option('bcn_post_prefix');
				$this->breadcrumb_trail->opt['post_suffix'] = get_option('bcn_post_suffix');
				$this->breadcrumb_trail->opt['post_anchor'] = get_option('bcn_post_anchor');
				$this->breadcrumb_trail->opt['post_taxonomy_display'] = str2bool(get_option('bcn_post_taxonomy_display'));
				$this->breadcrumb_trail->opt['post_taxonomy_type'] = get_option('bcn_post_taxonomy_type');
				$this->breadcrumb_trail->opt['attachment_prefix'] = get_option('bcn_attachment_prefix');
				$this->breadcrumb_trail->opt['attachment_suffix'] = get_option('bcn_attachment_suffix');
				$this->breadcrumb_trail->opt['404_prefix'] = get_option('bcn_404_prefix');
				$this->breadcrumb_trail->opt['404_suffix'] = get_option('bcn_404_suffix');
				$this->breadcrumb_trail->opt['404_title'] = get_option('bcn_404_title');
				$this->breadcrumb_trail->opt['search_prefix'] = get_option('bcn_search_prefix');
				$this->breadcrumb_trail->opt['search_suffix'] = get_option('bcn_search_suffix');
				$this->breadcrumb_trail->opt['tag_prefix'] = get_option('bcn_tag_prefix');
				$this->breadcrumb_trail->opt['tag_suffix'] = get_option('bcn_tag_suffix');
				$this->breadcrumb_trail->opt['tag_anchor'] = get_option('bcn_tag_anchor');
				$this->breadcrumb_trail->opt['author_prefix'] = get_option('bcn_author_prefix');
				$this->breadcrumb_trail->opt['author_suffix'] = get_option('bcn_author_suffix');
				$this->breadcrumb_trail->opt['author_display'] = get_option('bcn_author_display');
				$this->breadcrumb_trail->opt['category_prefix'] = get_option('bcn_category_prefix');
				$this->breadcrumb_trail->opt['category_suffix'] = get_option('bcn_category_suffix');
				$this->breadcrumb_trail->opt['category_anchor'] = get_option('bcn_category_anchor');
				$this->breadcrumb_trail->opt['archive_category_prefix'] = get_option('bcn_archive_category_prefix');
				$this->breadcrumb_trail->opt['archive_category_suffix'] = get_option('bcn_archive_category_suffix');
				$this->breadcrumb_trail->opt['archive_tag_prefix'] = get_option('bcn_archive_tag_prefix');
				$this->breadcrumb_trail->opt['archive_tag_suffix'] = get_option('bcn_archive_tag_suffix');
				$this->breadcrumb_trail->opt['date_anchor'] = get_option('bcn_date_anchor');
				$this->breadcrumb_trail->opt['archive_date_prefix'] = get_option('bcn_archive_date_prefix');
				$this->breadcrumb_trail->opt['archive_date_suffix'] = get_option('bcn_archive_date_suffix');
				//Now remove the old options
				delete_option('bcn_trail_linked', 'true');
				delete_option('bcn_home_display', 'true');
				delete_option('bcn_home_title', 'Blog');
				delete_option('bcn_home_anchor', '<a title="Go to %title%." href="%link%">');
				delete_option('bcn_blog_anchor', '<a title="Go to %title%." href="%link%">');
				delete_option('bcn_max_title_length', 0);
				delete_option('bcn_separator', '&nbsp;&gt;&nbsp;');
				delete_option('bcn_search_prefix', 'Search results for &#39;');
				delete_option('bcn_search_suffix', '&#39;');
				delete_option('bcn_author_prefix', 'Posts by ');
				delete_option('bcn_author_suffix', '');
				delete_option('bcn_author_display', 'display_name');
				delete_option('bcn_page_prefix', '');
				delete_option('bcn_page_suffix', '');
				delete_option('bcn_page_anchor', '<a title="Go to %title%." href="%link%">');
				delete_option('bcn_archive_category_prefix', 'Archive by category &#39;');
				delete_option('bcn_archive_category_suffix', '&#39;');
				delete_option('bcn_archive_tag_prefix', 'Archive by tag &#39;');
				delete_option('bcn_archive_tag_suffix', '&#39;');
				delete_option('bcn_attachment_prefix', 'Attachment:&nbsp;');
				delete_option('bcn_attachment_suffix', '');
				delete_option('bcn_404_prefix', '');
				delete_option('bcn_404_suffix', '');
				delete_option('bcn_404_title', '404');
				delete_option('bcn_current_item_linked', 'false');
				delete_option('bcn_current_item_anchor', '<a title="Reload the current page." href="%link%">');
				delete_option('bcn_current_item_prefix', '');
				delete_option('bcn_current_item_suffix', '');
				delete_option('bcn_paged_display', 'false');
				delete_option('bcn_paged_prefix', ', Page&nbsp;');
				delete_option('bcn_paged_suffix', '');
				delete_option('bcn_post_prefix', 'Blog article:&nbsp;');
				delete_option('bcn_post_suffix', '');
				delete_option('bcn_post_taxonomy_type', 'category');
				delete_option('bcn_post_taxonomy_display', 'true');
				delete_option('bcn_post_anchor', '<a title="Go to %title%." href="%link%">');
				delete_option('bcn_category_prefix', '');
				delete_option('bcn_category_suffix', '');
				delete_option('bcn_category_anchor', '<a title="Go to the %title% category archives." href="%link%">');
				delete_option('bcn_tag_prefix', '');
				delete_option('bcn_tag_suffix', '');
				delete_option('bcn_tag_anchor', '<a title="Go to the %title% tag archives." href="%link%">');
				delete_option('bcn_archive_date_prefix', '');
				delete_option('bcn_archive_date_suffix', '');
				delete_option('bcn_date_anchor', '<a title="Go to the %title% archives." href="%link%">');
			}
			//Always have to update the version
			update_option('bcn_version', $this->version);
			//Store the options
			add_option('bcn_options', $this->breadcrumb_trail->opt);
		}
	}
	/**
	 * update
	 * 
	 * Updates the database settings from the webform
	 */
	function update()
	{
		$this->security();
		//Do a nonce check, prevent malicious link/form problems
		check_admin_referer('bcn_admin_options');
		//Grab the options from the from post
		//Home page settings
		$this->breadcrumb_trail->opt['home_display'] = str2bool(bcn_get('home_display', 'false'));
		$this->breadcrumb_trail->opt['home_title'] = bcn_get('home_title');
		$this->breadcrumb_trail->opt['home_anchor'] = bcn_get('home_anchor');
		$this->breadcrumb_trail->opt['blog_anchor'] = bcn_get('blog_anchor');
		$this->breadcrumb_trail->opt['separator'] = bcn_get('separator');
		$this->breadcrumb_trail->opt['max_title_length'] = bcn_get('max_title_length');
		//Current item settings
		$this->breadcrumb_trail->opt['current_item_linked'] = str2bool(bcn_get('current_item_linked', 'false'));
		$this->breadcrumb_trail->opt['current_item_anchor'] = bcn_get('current_item_anchor');
		$this->breadcrumb_trail->opt['current_item_prefix'] = bcn_get('current_item_prefix');
		$this->breadcrumb_trail->opt['current_item_suffix'] = bcn_get('current_item_suffix');
		//Paged settings
		$this->breadcrumb_trail->opt['paged_prefix'] = bcn_get('paged_prefix');
		$this->breadcrumb_trail->opt['paged_suffix'] = bcn_get('paged_suffix');
		$this->breadcrumb_trail->opt['paged_display'] = str2bool(bcn_get('paged_display', 'false'));
		//Page settings
		$this->breadcrumb_trail->opt['page_prefix'] = bcn_get('page_prefix');
		$this->breadcrumb_trail->opt['page_suffix'] = bcn_get('page_suffix');
		$this->breadcrumb_trail->opt['page_anchor'] = bcn_get('page_anchor');
		//Post related options
		$this->breadcrumb_trail->opt['post_prefix'] = bcn_get('post_prefix');
		$this->breadcrumb_trail->opt['post_suffix'] = bcn_get('post_suffix');
		$this->breadcrumb_trail->opt['post_anchor'] = bcn_get('post_anchor');
		$this->breadcrumb_trail->opt['post_taxonomy_display'] = str2bool(bcn_get('post_taxonomy_display', 'false'));
		$this->breadcrumb_trail->opt['post_taxonomy_type'] = bcn_get('post_taxonomy_type');
		//Attachment settings
		$this->breadcrumb_trail->opt['attachment_prefix'] = bcn_get('attachment_prefix');
		$this->breadcrumb_trail->opt['attachment_suffix'] = bcn_get('attachment_suffix');
		//404 page settings
		$this->breadcrumb_trail->opt['404_prefix'] = bcn_get('404_prefix');
		$this->breadcrumb_trail->opt['404_suffix'] = bcn_get('404_suffix');
		$this->breadcrumb_trail->opt['404_title'] = bcn_get('404_title');
		//Search page settings
		$this->breadcrumb_trail->opt['search_prefix'] = bcn_get('search_prefix');
		$this->breadcrumb_trail->opt['search_suffix'] = bcn_get('search_suffix');
		//Tag settings
		$this->breadcrumb_trail->opt['tag_prefix'] = bcn_get('tag_prefix');
		$this->breadcrumb_trail->opt['tag_suffix'] = bcn_get('tag_suffix');
		$this->breadcrumb_trail->opt['tag_anchor'] = bcn_get('tag_anchor');
		//Author page settings
		$this->breadcrumb_trail->opt['author_prefix'] = bcn_get('author_prefix');
		$this->breadcrumb_trail->opt['author_suffix'] = bcn_get('author_suffix');
		$this->breadcrumb_trail->opt['author_display'] = bcn_get('author_display');
		//Category settings
		$this->breadcrumb_trail->opt['category_prefix'] = bcn_get('category_prefix');
		$this->breadcrumb_trail->opt['category_suffix'] = bcn_get('category_suffix');
		$this->breadcrumb_trail->opt['category_anchor'] = bcn_get('category_anchor');
		//Archive settings
		$this->breadcrumb_trail->opt['archive_category_prefix'] = bcn_get('archive_category_prefix');
		$this->breadcrumb_trail->opt['archive_category_suffix'] = bcn_get('archive_category_suffix');
		$this->breadcrumb_trail->opt['archive_tag_prefix'] = bcn_get('archive_tag_prefix');
		$this->breadcrumb_trail->opt['archive_tag_suffix'] = bcn_get('archive_tag_suffix');
		//Archive by date settings
		$this->breadcrumb_trail->opt['date_anchor'] = bcn_get('date_anchor');
		$this->breadcrumb_trail->opt['archive_date_prefix'] = bcn_get('archive_date_prefix');
		$this->breadcrumb_trail->opt['archive_date_suffix'] = bcn_get('archive_date_suffix');
		//bcn_update_option('bcn_trail_linked', bcn_get('trail_linked', 'false'));
		//Commit the option changes
		update_option('bcn_options', $this->breadcrumb_trail->opt);
	}
	/**
	 * display
	 * 
	 * Outputs the breadcrumb trail
	 * 
	 * @param  (bool)   $linked Whether to allow hyperlinks in the trail or not.
	 */
	function display($linked = true)
	{
		//Update our internal settings
		$this->breadcrumb_trail->opt = get_option('bcn_options');
		//Generate the breadcrumb trail
		$this->breadcrumb_trail->fill();
		//Display the breadcrumb trail
		$this->breadcrumb_trail->display(false, $linked);
	}
	/**
	 * filter_plugin_actions
	 * 
	 * Places in a link to the settings page on the plugins listing
	 * 
	 * @param  (array)   $links An array of links that are output in the listing
	 * @param  (string)   $file The file that is currently in processing
	 */
	function filter_plugin_actions($links, $file)
	{
		static $this_plugin;
		if(!$this_plugin)
		{
			$this_plugin = plugin_basename(__FILE__);
		}
		//Make sure we are adding only for Breadcrumb NavXT
		if($file == $this_plugin)
		{
			//Setup the link string
			$settings_link = '<a href="options-general.php?page=breadcrumb-navxt">' . __('Settings') . '</a>';
			//Add it to the beginning of the array
			array_unshift($links, $settings_link);
		}
		return $links;
	}
	/**
	 * add_page
	 * 
	 * Adds the adminpage the menue and the nice little settings link
	 * 
	 */
	function add_page()
	{
		global $bcn_admin_req;
		//We did away with bcn_security in favor of this nice thing
		if(current_user_can('manage_options'))
		{
			//Add the submenu page to "settings", more robust than previous method
			add_submenu_page('options-general.php', 'Breadcrumb NavXT Settings', 'Breadcrumb NavXT', 'manage_options', 'breadcrumb-navxt', array(&$this, 'admin_panel'));
			//Add in the nice "settings" link to the plugins page
			add_filter('plugin_action_links', array(&$this, 'filter_plugin_actions'), 10, 2);
		}
	}
	/**
	 * admin_panel
	 * 
	 * The administrative panel for Breadcrumb NavXT
	 * 
	 */
	function admin_panel()
	{
		$this->security();
		//Update our internal options array, use form safe function
		$this->breadcrumb_trail->opt = $this->get_option('bcn_options');
		//var_dump($this->breadcrumb_trail->opt);
		//Initilizes l10n domain	
		$this->local();
		//See if the administrative interface matches versions with the class, if not then warn the user
		list($bcn_plugin_major, $bcn_plugin_minor, $bcn_plugin_bugfix) = explode('.', $this->breadcrumb_trail->version);	
		list($bcn_admin_major,  $bcn_admin_minor,  $bcn_admin_bugfix)  = explode('.', $this->version);		
		if($bcn_plugin_major != $bcn_admin_major || $bcn_plugin_minor != $bcn_admin_minor)
		{
			?>
			<div id="message" class="updated fade">
				<p><?php _e('Warning, your version of Breadcrumb NavXT does not match the version supported by this administrative interface. As a result, settings may not work as expected.', 'breadcrumb_navxt'); ?></p>
				<p><?php _e('Your Breadcrumb NavXT Administration interface version is ', 'breadcrumb_navxt'); echo $this->version; ?>.</p>
				<p><?php _e('Your Breadcrumb NavXT version is ', 'breadcrumb_navxt'); echo $this->breadcrumb_trail->version; ?>.</p>
			</div>
			<?php 
		} ?>
		<div class="wrap"><h2><?php _e('Breadcrumb NavXT Settings', 'breadcrumb_navxt'); ?></h2>
		<p><?php 
			printf(__('Tips for the settings are located below select options. Please refer to the %sdocumentation%s for more detailed explanation of each setting.', 'breadcrumb_navxt'), 
			'<a title="Go to the Breadcrumb NavXT online documentation" href="http://mtekk.weblogs.us/code/breadcrumb-navxt/breadcrumb-navxt-doc/">', '</a>'); 
		?></p>
		<form action="options-general.php?page=breadcrumb-navxt" method="post" id="bcn_admin_options">
			<?php wp_nonce_field('bcn_admin_options');?>
			<div id="hasadmintabs">
			<fieldset id="general" class="bcn_options">
				<h3><?php _e('General', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php _e('Home Breadcrumb', 'breadcrumb_navxt'); ?>						
						</th>
						<td>
							<p>
								<label>
									<input name="home_display" type="radio" value="false" class="togx" <?php checked(false, $this->breadcrumb_trail->opt['home_display']); ?> />
									<?php _e('Leave the home breadcrumb out of the trail.', 'breadcrumb_navxt'); ?>
								</label>
							</p>
							<p>
								<label>
									<input name="home_display" type="radio" value="true" class="togx" <?php checked(true, $this->breadcrumb_trail->opt['home_display']); ?> />
									<?php _e('Place the home breadcrumb in the trail.', 'breadcrumb_navxt'); ?>	
								</label>
								<ul>
									<li>
										<label for="home_title">
											<?php _e('Home Title: ','breadcrumb_navxt');?>
											<input type="text" name="home_title" id="home_title" value="<?php echo $this->breadcrumb_trail->opt['home_title']; ?>" size="20" />			
										</label>
									</li>
								</ul>							
							</p>													
						</td>
					</tr>		
					<tr valign="top">
						<th scope="row">
							<label for="separator"><?php _e('Breadcrumb Separator', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="separator" id="separator" value="<?php echo $this->breadcrumb_trail->opt['separator']; ?>" size="32" /><br />
							<?php _e('Placed in between each breadcrumb.', 'breadcrumb_navxt'); ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="max_title_length"><?php _e('Breadcrumb Max Title Length', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="max_title_length" id="max_title_length" value="<?php echo $this->breadcrumb_trail->opt['max_title_length'];?>" size="10" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="home_anchor"><?php _e('Home Anchor', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="home_anchor" id="home_anchor" value="<?php echo $this->breadcrumb_trail->opt['home_anchor']; ?>" size="60" /><br />
							<?php _e('The anchor template for the home breadcrumb.', 'breadcrumb_navxt'); ?>
						</td>
					</tr>
					<?php 
					//We only need this if in a static front page condition
					if(get_option('show_on_front') == "page")
					{?>
					<tr valign="top">
						<th scope="row">
							<label for="blog_anchor"><?php _e('Blog Anchor', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="blog_anchor" id="blog_anchor" value="<?php echo $this->breadcrumb_trail->opt['blog_anchor']; ?>" size="60" /><br />
							<?php _e('The anchor template for the blog breadcrumb.', 'breadcrumb_navxt'); ?>
						</td>
					</tr> 
					<?php } ?>
				</table>
			</fieldset>
			<fieldset id="current" class="bcn_options">
				<h3><?php _e('Current Item', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="current_item_linked"><?php _e('Link Current Item', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<label>
								<input name="current_item_linked" type="checkbox" id="current_item_linked" value="true" <?php checked(true, $this->breadcrumb_trail->opt['current_item_linked']); ?> />
								<?php _e('Yes'); ?>							
							</label>					
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="current_item_prefix"><?php _e('Current Item Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="current_item_prefix" id="current_item_prefix" value="<?php echo $this->breadcrumb_trail->opt['current_item_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="current_item_suffix"><?php _e('Current Item Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="current_item_suffix" id="current_item_suffix" value="<?php echo $this->breadcrumb_trail->opt['current_item_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="current_item_anchor"><?php _e('Current Item Anchor', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="current_item_anchor" id="current_item_anchor" value="<?php echo $this->breadcrumb_trail->opt['current_item_anchor']; ?>" size="60" /><br />
							<?php _e('The anchor template for current item breadcrumbs.', 'breadcrumb_navxt'); ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="paged_display"><?php _e('Display Paged Text', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<label>
								<input name="paged_display" type="checkbox" id="paged_display" value="true" <?php checked(true, $this->breadcrumb_trail->opt['paged_display']); ?> />
								<?php _e('Show that the user is on a page other than the first on posts/archives with multiple pages.', 'breadcrumb_navxt'); ?>							
							</label>	
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="paged_prefix"><?php _e('Paged Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="paged_prefix" id="paged_prefix" value="<?php echo $this->breadcrumb_trail->opt['paged_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="paged_suffix"><?php _e('Paged Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="paged_suffix" id="paged_suffix" value="<?php echo $this->breadcrumb_trail->opt['paged_suffix']; ?>" size="32" />
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset id="single" class="bcn_options">
				<h3><?php _e('Posts & Pages', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="post_prefix"><?php _e('Post Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="post_prefix" id="post_prefix" value="<?php echo $this->breadcrumb_trail->opt['post_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="post_suffix"><?php _e('Post Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="post_suffix" id="post_suffix" value="<?php echo $this->breadcrumb_trail->opt['post_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="post_anchor"><?php _e('Post Anchor', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="post_anchor" id="post_anchor" value="<?php echo $this->breadcrumb_trail->opt['post_anchor']; ?>" size="60" /><br />
							<?php _e('The anchor template for post breadcrumbs.', 'breadcrumb_navxt'); ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e('Post Taxonomy Display', 'breadcrumb_navxt'); ?>
						</th>
						<td>
							<label for="post_taxonomy_display">
								<input name="post_taxonomy_display" type="checkbox" id="post_taxonomy_display" value="true" <?php checked(true, $this->breadcrumb_trail->opt['post_taxonomy_display']); ?> />
								<?php _e('Show the taxonomy leading to a post in the breadcrumb trail.', 'breadcrumb_navxt'); ?>							
							</label>							
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<p><?php _e('Post Taxonomy', 'breadcrumb_navxt'); ?></p>
						</th>
						<td>
							<p>
								<label>
									<input name="post_taxonomy_type" type="radio" value="category" class="togx" <?php checked('category', $this->breadcrumb_trail->opt['post_taxonomy_type']); ?> />
									<?php _e('Categories'); ?>
								</label>
							</p>
							<p>
								<label>
									<input name="post_taxonomy_type" type="radio" value="tag" class="togx" <?php checked('tag', $this->breadcrumb_trail->opt['post_taxonomy_type']); ?> />
									<?php _e('Tags'); ?>								
								</label>
							</p>
							<p>
								<?php _e('The taxonomy which the breadcrumb trail will show.', 'breadcrumb_navxt'); ?>
							</p>														
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="page_prefix"><?php _e('Page Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="page_prefix" id="page_prefix" value="<?php echo $this->breadcrumb_trail->opt['page_prefix']; ?>" size="32" />
						</td>
				</tr>
					<tr valign="top">
						<th scope="row">
							<label for="page_suffix"><?php _e('Page Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="page_suffix" id="page_suffix" value="<?php echo $this->breadcrumb_trail->opt['page_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="page_anchor"><?php _e('Page Anchor', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="page_anchor" id="page_anchor" value="<?php echo $this->breadcrumb_trail->opt['page_anchor']; ?>" size="60" /><br />
							<?php _e('The anchor template for page breadcrumbs.', 'breadcrumb_navxt'); ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="attachment_prefix"><?php _e('Attachment Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="attachment_prefix" id="attachment_prefix" value="<?php echo $this->breadcrumb_trail->opt['attachment_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="attachment_suffix"><?php _e('Attachment Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="attachment_suffix" id="attachment_suffix" value="<?php echo $this->breadcrumb_trail->opt['attachment_suffix']; ?>" size="32" />
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset id="category" class="bcn_options">
				<h3><?php _e('Categories', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="archive_category_prefix"><?php _e('Archive by Category Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="archive_category_prefix" id="archive_category_prefix" value="<?php echo $this->breadcrumb_trail->opt['archive_category_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="archive_category_suffix"><?php _e('Archive by Category Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="archive_category_suffix" id="archive_category_suffix" value="<?php echo $this->breadcrumb_trail->opt['archive_category_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="category_prefix"><?php _e('Category Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="category_prefix" id="category_prefix" value="<?php echo $this->breadcrumb_trail->opt['category_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="category_suffix"><?php _e('Category Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="category_suffix" id="category_suffix" value="<?php echo $this->breadcrumb_trail->opt['category_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="category_anchor"><?php _e('Category Anchor', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="category_anchor" id="category_anchor" value="<?php echo $this->breadcrumb_trail->opt['category_anchor']; ?>" size="60" /><br />
							<?php _e('The anchor template for category breadcrumbs.', 'breadcrumb_navxt'); ?>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset id="tag" class="bcn_options">
				<h3><?php _e('Tags', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="archive_tag_prefix"><?php _e('Archive by Tag Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="archive_tag_prefix" id="archive_tag_prefix" value="<?php echo $this->breadcrumb_trail->opt['archive_tag_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="archive_tag_suffix"><?php _e('Archive by Tag Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="archive_tag_suffix" id="archive_tag_suffix" value="<?php echo $this->breadcrumb_trail->opt['archive_tag_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="tag_prefix"><?php _e('Tag Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="tag_prefix" id="tag_prefix" value="<?php echo $this->breadcrumb_trail->opt['tag_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="tag_suffix"><?php _e('Tag Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="tag_suffix" id="tag_suffix" value="<?php echo $this->breadcrumb_trail->opt['tag_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="tag_anchor"><?php _e('Tag Anchor', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="tag_anchor" id="tag_anchor" value="<?php echo $this->breadcrumb_trail->opt['tag_anchor']; ?>" size="60" /><br />
							<?php _e('The anchor template for tag breadcrumbs.', 'breadcrumb_navxt'); ?>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset id="date" class="bcn_options">
				<h3><?php _e('Date Archives', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="archive_date_prefix"><?php _e('Archive by Date Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="archive_date_prefix" id="archive_date_prefix" value="<?php echo $this->breadcrumb_trail->opt['archive_date_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="archive_date_suffix"><?php _e('Archive by Date Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="archive_date_suffix" id="archive_date_suffix" value="<?php echo $this->breadcrumb_trail->opt['archive_date_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="date_anchor"><?php _e('Date Anchor', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="date_anchor" id="date_anchor" value="<?php echo $this->breadcrumb_trail->opt['date_anchor']; ?>" size="60" /><br />
							<?php _e('The anchor template for date breadcrumbs.', 'breadcrumb_navxt'); ?>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset id="miscellaneous" class="bcn_options">
				<h3><?php _e('Miscellaneous', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="author_prefix"><?php _e('Author Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="author_prefix" id="author_prefix" value="<?php echo $this->breadcrumb_trail->opt['author_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="author_suffix"><?php _e('Author Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="author_suffix" id="author_suffix" value="<?php echo $this->breadcrumb_trail->opt['author_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="author_display"><?php _e('Author Display Format', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<select name="author_display" id="author_display">
								<?php $this->select_options('author_display', array("display_name", "nickname", "first_name", "last_name")); ?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="search_prefix"><?php _e('Search Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="search_prefix" id="search_prefix" value="<?php echo $this->breadcrumb_trail->opt['search_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="search_suffix"><?php _e('Search Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="search_suffix" id="search_suffix" value="<?php echo $this->breadcrumb_trail->opt['search_suffix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="404_title"><?php _e('404 Title', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="404_title" id="404_title" value="<?php echo $this->breadcrumb_trail->opt['404_title']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="404_prefix"><?php _e('404 Prefix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="404_prefix" id="404_prefix" value="<?php echo $this->breadcrumb_trail->opt['404_prefix']; ?>" size="32" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="404_suffix"><?php _e('404 Suffix', 'breadcrumb_navxt'); ?></label>
						</th>
						<td>
							<input type="text" name="404_suffix" id="404_suffix" value="<?php echo $this->breadcrumb_trail->opt['404_suffix']; ?>" size="32" />
						</td>
					</tr>
				</table>
			</fieldset>
			</div>
			<p class="submit"><input type="submit" name="bcn_admin_options" value="<?php _e('Save Changes') ?>" /></p>
		</form>
		</div>
		<?php
	}
	/**
	 * widget
	 *
	 * The sidebar widget 
	 */
	function widget($args)
	{
		extract($args);
		//Manditory before widget junk
		echo $before_widget;
		//Display the breadcrumb trial
		bcn_display();
		//Manditory after widget junk
		echo $after_widget;
	}
	/**
	 * register_widget
	 *
	 * Registers the sidebar widget 
	 */
	function register_widget()
	{
		register_sidebar_widget('Breadcrumb NavXT', array(&$this, 'widget'));
	}
	/**
	 * local
	 *
	 * Initilizes localization domain
	 */
	function local()
	{
		//Load breadcrumb-navxt translation
		load_plugin_textdomain($domain = 'breadcrumb_navxt', $path = PLUGINDIR . '/breadcrumb-navxt');
	}
	/**
	 * select_options
	 *
	 * Displays wordpress options as <seclect> options defaults to true/false
	 *
	 * @param (string) optionname name of wordpress options store
	 * @param (array) options array of options defaults to array('true','false')
	 */
	function select_options($optionname, $options = array('true','false'))
	{
		$value = $this->breadcrumb_trail->opt[$optionname];
		//First output the current value
		if ($value)
		{
			printf('<option>%s</option>', $value);
		}
		//Now do the rest
		foreach($options as $option)
		{
			//Don't want multiple occurance of the current value
			if($option != $value)
			{
				printf('<option>%s</option>', $option);
			}
		}
	}
	/**
	 * get_option
	 *
	 * This grabs the the data from the db and places it in a form safe manner
	 *
	 * @param (string) option name of wordpress option to get
	 * @return (mixed)
	 */
	function get_option($option)
	{
		$db_data = get_option($option);
		//If we get an array, we should loop through all of its members
		if(is_array($db_data))
		{
			//Loop through all the members
			foreach($db_data as $key=>$item)
			{
				//We ignore anything but strings
				if(is_string($item))
				{
					$db_data[$key] = htmlentities($item);
				}
			}
			return $db_data;
		}
		else
		{
			return htmlentities($db_data);
		}
	}
}
//Let's make an instance of our object takes care of everything
$bcn_admin = new bcn_admin;
/**
 * Exists for legacy compatibility. Tells user to use bcn_display, function slated for removal in 3.1.
 */
function breadcrumb_nav_xt_display()
{
	echo "Please use bcn_display instead of breadcrumb_nav_xt_display";
}
/**
 * A wrapper for the internal function in the class, please directly acess the admin class instead
 * 
 * @param  (bool)   $linked Whether to allow hyperlinks in the trail or not.
 */
function bcn_display($linked = true)
{
	global $bcn_admin;
	$bcn_admin->display($linked);
}
?>