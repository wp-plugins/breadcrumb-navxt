<?php
/*
Plugin Name: Breadcrumb NavXT - Adminstration Interface
Plugin URI: http://mtekk.weblogs.us/code/breadcrumb-navxt/
Description: Adds a breadcrumb navigation showing the visitor&#39;s path to their current location. This enables the administrative interface for specifying the output of the breadcrumb. For details on how to use this plugin visit <a href="http://mtekk.weblogs.us/code/breadcrumb-navxt/">Breadcrumb NavXT</a>. 
Version: 2.1.99
Author: John Havlik
Author URI: http://mtekk.weblogs.us/
*/
/*
 *
 * @author John Havlik
 * @author Tom Klingenberg
 * 
 * @todo remove static frontpage options
 * @todo put main admin panel logic into one class to better seperate from
 *       global namespace and to provide better modularization for upgrades.
 */

//Configuration 

//Globals are evil, we only use two
//This has to be redeclaired in the install function, hell if I know why
$bcn_admin_version = "2.2.0";
$bcn_admin_req = 8;

//Includes 

//Include the breadcrumb class (if needed)
if(!class_exists('bcn_breadcrumb'))
{
	require_once(dirname(__FILE__) . '/breadcrumb_navxt_class.php');
}
//Include the supplemental functions
require_once(dirname(__FILE__) . '/breadcrumb_navxt_api.php');

//Main

/**
 * Ensure the user has the proper permissions. Dies on failure.
 * 
 * @return void
 */
function bcn_security()
{
	global $userdata, $bcn_admin_req, $bcn_version, $wp_version;
	//Make sure userdata is filled
	get_currentuserinfo();
	//If the user_levels aren't proper and the user is not an administrator via capabilities
	if($userdata->user_level < $bcn_admin_req && $userdata->wp_capabilities['administrator'] != true && !current_user_can('manage_options'))
	{
		//If user_level is null which tends to cause problems for everyone
		if($userdata->user_level == NULL)
		{
			_e('<strong>Aborting: WordPress API Malfunction</strong><br /> For some reason the 
				function get_currentuserinfo() did not behave as expected. Your user_level seems to be null.
				This can be resolved by navigationg to the Users section of the WordPress administrative interface.
				In this section check the user that you use for administrative purposes. Then under the drop down
				labled "change role to..." select administrator. Now click the change button. Should you still 
				recieve this error please report this bug to the plug-in author. In your report please specify 
				your WordPress version, PHP version, Apache (or whatever HTTP server you are using) verion, and 
				the version of the plug-in you are using.<br />', 'breadcrumb_navxt');
			_e('WordPress version: ', 'breadcrumb_navxt');
			echo $wp_version . '<br />';
			_e('PHP version: ', 'breadcrumb_navxt');
			echo phpversion() . '<br />';
			_e('Plug-in version: ', 'breadcrumb_navxt');
			echo $bcn_version . "<br />";
		}
		//Otherwise we have an anauthorized acess attempt
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
/**
 * Initilizes the administrative interface options if it is a new install, or an upgrade from an incompatible version
 *
 * @return void 
 */
function bcn_install()
{
	global $bcn_admin_req, $bcn_admin_version;
	bcn_security();
	//Globals seem not to work with WordPress' odd way of doing these calls
	$bcn_admin_version = "2.1.99";
	//Check if the database settings are for an old version
	if(get_option('bcn_version') !== $bcn_admin_version)
	{
		//First we should clean up old options we don't use anymore
		list($major, $minor, $release) = explode('.', get_option('bcn_version'));
		//If the old DB version was prior to 2.1.3
		if($major <= 2 && $minor <= 1 && $release <= 3)
		{
			//Remove old crap
			delete_option('bcn_preserve');
			delete_option('bcn_static_frontpage');
		}
		//Fix up depreciated in 2.2, migrate any old settings if possible
		else if($major <= 2 && $minor < 2)
		{
			delete_option('bcn_url_blog');
			delete_option('bcn_home_link');
			//Upgrade to a current option
			add_option('bcn_home_title', get_option('bcn_title_blog'));
			//Remove the old stuff
			delete_option('bcn_title_blog');
			delete_option('bcn_title_home');
			update_option('bcn_home_display', get_option('bcn_home_display'));
			delete_option('bcn_urltitle_prefix');
			delete_option('bcn_urltitle_suffix');
			delete_option('bcn_archive_date_format');
			add_option('bcn_404_title', get_option('bcn_title_404'));
			delete_option('bcn_title_404');
			add_option('bcn_post_taxonomy_type', get_option('bcn_singleblogpost_taxonomy'));
			add_option('bcn_post_taxonomy_display', get_option('bcn_singleblogpost_taxonomy_display'));
			delete_option('bcn_singleblogpost_taxonomy');
			delete_option('bcn_singleblogpost_taxonomy_display');
			//Migrate the next set, then clean up
			add_option('bcn_category_prefix', get_option('bcn_singleblogpost_category_prefix'));
			add_option('bcn_category_suffix', get_option('bcn_singleblogpost_category_suffix'));
			add_option('bcn_tag_prefix', get_option('bcn_singleblogpost_tag_prefix'));
			add_option('bcn_tag_suffix', get_option('bcn_singleblogpost_tag_suffix'));
			delete_option('bcn_singleblogpost_category_prefix');
			delete_option('bcn_singleblogpost_category_suffix');
			delete_option('bcn_singleblogpost_tag_prefix');
			delete_option('bcn_singleblogpost_tag_suffix');
			add_option('bcn_current_item_linked', get_option('bcn_link_current_item'));
			delete_option('bcn_link_current_item');
			delete_option('bcn_current_item_urltitle');
			add_option('bcn_post_prefix', get_option('bcn_singleblogpost_prefix'));
			add_option('bcn_post_suffix', get_option('bcn_singleblogpost_suffix'));
			dalete_option('bcn_singleblogpost_prefix');
			delete_option('bcn_singleblogpost_suffix');
			add_option('bcn_current_item_prefix', get_option('bcn_singleblogpost_style_prefix'));
			add_option('bcn_current_item_suffix', get_option('bcn_singleblogpost_style_suffix'));
			dalete_option('bcn_singleblogpost_style_prefix');
			delete_option('bcn_singleblogpost_style_suffix');
			//Migrate title_maxlen
			add_option('bcn_max_title_length', get_option('bcn_posttitle_maxlen'));
			delete_option('bcn_posttitle_maxlen');
		}
		//We always want to update to our current version
		update_option('bcn_version', $bcn_admin_version);
		//Add in options if they didn't exist before, load defaults into them
		add_option('bcn_trail_linked', 'true');
		//Home page settings
		add_option('bcn_home_display', 'true');
		add_option('bcn_home_title', 'Blog');
		add_option('bcn_home_anchor', '<a title="Go to %title%." href="%link%">');
		add_option('bcn_blog_anchor', '<a title="Go to %title%." href="%link%">');
		add_option('bcn_max_title_length', 0);
		add_option('bcn_separator', '&nbsp;&gt;&nbsp;');
		//Search page settings
		add_option('bcn_search_prefix', 'Search results for &#39;');
		add_option('bcn_search_suffix', '&#39;');
		//Author page settings
		add_option('bcn_author_prefix', 'Posts by ');
		add_option('bcn_author_suffix', '');
		add_option('bcn_author_display', 'display_name');
		//Page settings
		add_option('bcn_page_prefix', '');
		add_option('bcn_page_suffix', '');
		add_option('bcn_page_anchor', '<a title="Go to %title%." href="%link%">');
		//Archive settings
		add_option('bcn_archive_category_prefix', 'Archive by category &#39;');
		add_option('bcn_archive_category_suffix', '&#39;');
		add_option('bcn_archive_tag_prefix', 'Archive by tag &#39;');
		add_option('bcn_archive_tag_suffix', '&#39;');
		//Attachment settings
		add_option('bcn_attachment_prefix', 'Attachment:&nbsp;');
		add_option('bcn_attachment_suffix', '');
		//404 page settings
		add_option('bcn_404_prefix', '');
		add_option('bcn_404_suffix', '');
		add_option('bcn_404_title', '404');
		//Current item settings
		add_option('bcn_current_item_linked', 'false');
		add_option('bcn_current_item_anchor', '<a title="Reload the current page." href="%link%">');
		add_option('bcn_current_item_prefix', '');
		add_option('bcn_current_item_suffix', '');
		//Paged settings
		add_option('bcn_paged_display', 'false');
		add_option('bcn_paged_prefix', ', Page&nbsp;');
		add_option('bcn_paged_suffix', '');
		//Post related options
		add_option('bcn_post_prefix', 'Blog article:&nbsp;');
		add_option('bcn_post_suffix', '');
		add_option('bcn_post_taxonomy_type', 'category');
		add_option('bcn_post_taxonomy_display', 'true');
		add_option('bcn_post_anchor', '<a title="Go to %title%." href="%link%">');
		//Category settings
		add_option('bcn_category_prefix', '');
		add_option('bcn_category_suffix', '');
		add_option('bcn_category_anchor', '<a title="Go to the %title% category archives." href="%link%">');
		//Tag settings
		add_option('bcn_tag_prefix', '');
		add_option('bcn_tag_suffix', '');
		add_option('bcn_tag_anchor', '<a title="Go to the %title% tag archives." href="%link%">');
		//Archive by date settings
		add_option('bcn_archive_date_prefix', '');
		add_option('bcn_archive_date_suffix', '');
		add_option('bcn_date_anchor', '<a title="Go to the %title% archives." href="%link%">');
	}
}
/**
 * Exists for legacy compatibility. Tells user to use bcn_display, function slated for removal in 3.1.
 */
function breadcrumb_nav_xt_display()
{
	echo "Please use bcn_display instead of breadcrumb_nav_xt_display";
}
/**
 * Creates a bcn_breadcrumb object, sets the options per user specification in the 
 * administration interface and outputs the breadcrumb
 * 
 * @param  (bool)   $linked Whether to allow hyperlinks in the trail or not.
 */
function bcn_display($linked = true)
{
	//Playing things really safe here
	if(class_exists('bcn_breadcrumb_trail'))
	{
		//Make new breadcrumb object
		$breadcrumb_trail = new bcn_breadcrumb_trail;
		//Set the settings
		$breadcrumb_trail->opt['home_display'] = str2bool(get_option('bcn_home_display'));
		$breadcrumb_trail->opt['home_title'] = get_option('bcn_home_title');
		$breadcrumb_trail->opt['home_anchor'] = bcn_get_option('bcn_home_anchor');
		$breadcrumb_trail->opt['blog_anchor'] = bcn_get_option('bcn_blog_anchor');
		$breadcrumb_trail->opt['separator'] = get_option('bcn_separator');
		$breadcrumb_trail->opt['max_title_length'] = get_option('bcn_max_title_length');
		$breadcrumb_trail->opt['current_item_linked'] = str2bool(get_option('bcn_current_item_linked'));
		$breadcrumb_trail->opt['current_item_anchor'] = get_option('bcn_current_item_anchor');
		$breadcrumb_trail->opt['current_item_prefix'] = get_option('bcn_current_item_prefix');
		$breadcrumb_trail->opt['current_item_suffix'] = get_option('bcn_current_item_suffix');
		$breadcrumb_trail->opt['paged_prefix'] = get_option('bcn_paged_prefix');
		$breadcrumb_trail->opt['paged_suffix'] = get_option('bcn_paged_suffix');
		$breadcrumb_trail->opt['paged_display'] = str2bool(get_option('bcn_paged_display'));
		$breadcrumb_trail->opt['page_prefix'] = get_option('bcn_page_prefix');
		$breadcrumb_trail->opt['page_suffix'] = get_option('bcn_page_suffix');
		$breadcrumb_trail->opt['page_anchor'] = get_option('bcn_page_anchor');
		$breadcrumb_trail->opt['post_prefix'] = get_option('bcn_post_prefix');
		$breadcrumb_trail->opt['post_suffix'] = get_option('bcn_post_suffix');
		$breadcrumb_trail->opt['post_anchor'] = get_option('bcn_post_anchor');
		$breadcrumb_trail->opt['post_taxonomy_display'] = str2bool(get_option('bcn_post_taxonomy_display'));
		$breadcrumb_trail->opt['post_taxonomy_type'] = get_option('bcn_post_taxonomy_type');
		$breadcrumb_trail->opt['attachment_prefix'] = get_option('bcn_attachment_prefix');
		$breadcrumb_trail->opt['attachment_suffix'] = get_option('bcn_attachment_suffix');
		$breadcrumb_trail->opt['404_prefix'] = get_option('bcn_404_prefix');
		$breadcrumb_trail->opt['404_suffix'] = get_option('bcn_404_suffix');
		$breadcrumb_trail->opt['404_title'] = get_option('bcn_404_title');
		$breadcrumb_trail->opt['search_prefix'] = get_option('bcn_search_prefix');
		$breadcrumb_trail->opt['search_suffix'] = get_option('bcn_search_suffix');
		$breadcrumb_trail->opt['tag_prefix'] = get_option('bcn_tag_prefix');
		$breadcrumb_trail->opt['tag_suffix'] = get_option('bcn_tag_suffix');
		$breadcrumb_trail->opt['tag_anchor'] = get_option('bcn_tag_anchor');
		$breadcrumb_trail->opt['author_prefix'] = get_option('bcn_author_prefix');
		$breadcrumb_trail->opt['author_suffix'] = get_option('bcn_author_suffix');
		$breadcrumb_trail->opt['author_display'] = get_option('bcn_author_display');
		$breadcrumb_trail->opt['category_prefix'] = get_option('bcn_category_prefix');
		$breadcrumb_trail->opt['category_suffix'] = get_option('bcn_category_suffix');
		$breadcrumb_trail->opt['category_anchor'] = get_option('bcn_category_anchor');
		$breadcrumb_trail->opt['archive_category_prefix'] = get_option('bcn_archive_category_prefix');
		$breadcrumb_trail->opt['archive_category_suffix'] = get_option('bcn_archive_category_suffix');
		$breadcrumb_trail->opt['archive_tag_prefix'] = get_option('bcn_archive_tag_prefix');
		$breadcrumb_trail->opt['archive_tag_suffix'] = get_option('bcn_archive_tag_suffix');
		$breadcrumb_trail->opt['date_anchor'] = get_option('bcn_date_anchor');
		$breadcrumb_trail->opt['archive_date_prefix'] = get_option('bcn_archive_date_prefix');
		$breadcrumb_trail->opt['archive_date_suffix'] = get_option('bcn_archive_date_suffix');
		//Generate the breadcrumb trail
		$breadcrumb_trail->fill();
		//Display the breadcrumb trail
		$breadcrumb_trail->display(false, str2bool(get_option('bcn_trail_linked')));
	}
}
/**
 * bcn_admin_options
 *
 * Grabs and cleans updates to the settings from the administrative interface
 */
function bcn_admin_options()
{
	global $wpdb, $bcn_admin_req;
	
	bcn_security();
	
	//Do a nonce check, prevent malicious link/form problems
	check_admin_referer('bcn_admin_options');
	//Update the options
	bcn_update_option('bcn_trail_linked', bcn_get('trail_linked', 'false'));
	//Home page settings
	bcn_update_option('bcn_home_display', bcn_get('home_display', 'false'));
	bcn_update_option('bcn_home_title', bcn_get('home_title'));
	bcn_update_option('bcn_home_anchor', bcn_get('home_anchor'));
	bcn_update_option('bcn_blog_anchor', bcn_get('blog_anchor'));
	bcn_update_option('bcn_max_title_length', bcn_get('max_title_length'));
	bcn_update_option('bcn_separator', bcn_get('separator'));
	//Search page settings
	bcn_update_option('bcn_search_prefix', bcn_get('search_prefix'));
	bcn_update_option('bcn_search_suffix', bcn_get('search_suffix'));
	//Author page settings
	bcn_update_option('bcn_author_prefix', bcn_get('author_prefix'));
	bcn_update_option('bcn_author_suffix', bcn_get('author_suffix'));
	bcn_update_option('bcn_author_display', bcn_get('author_display'));
	//Page settings
	bcn_update_option('bcn_page_prefix', bcn_get('page_prefix'));
	bcn_update_option('bcn_page_suffix', bcn_get('page_suffix'));
	bcn_update_option('bcn_page_anchor', bcn_get('page_anchor'));
	//Archive settings
	bcn_update_option('bcn_archive_category_prefix', bcn_get('archive_category_prefix'));
	bcn_update_option('bcn_archive_category_suffix', bcn_get('archive_category_suffix'));
	bcn_update_option('bcn_archive_tag_prefix', bcn_get('archive_tag_prefix'));
	bcn_update_option('bcn_archive_tag_suffix', bcn_get('archive_tag_suffix'));
	//Attachment settings
	bcn_update_option('bcn_attachment_prefix', bcn_get('attachment_prefix'));
	bcn_update_option('bcn_attachment_suffix', bcn_get('attachment_suffix'));
	//404 page settings
	bcn_update_option('bcn_404_prefix', bcn_get('404_prefix'));
	bcn_update_option('bcn_404_suffix', bcn_get('404_suffix'));
	bcn_update_option('bcn_404_title', bcn_get('404_title'));
	//Current item settings
	bcn_update_option('bcn_current_item_linked', bcn_get('current_item_linked', 'false'));
	bcn_update_option('bcn_current_item_anchor', bcn_get('current_item_anchor'));
	bcn_update_option('bcn_current_item_prefix', bcn_get('current_item_prefix'));
	bcn_update_option('bcn_current_item_suffix', bcn_get('current_item_suffix'));
	//Paged settings
	bcn_update_option('bcn_paged_display', bcn_get('paged_display', 'false'));
	bcn_update_option('bcn_paged_prefix', bcn_get('paged_prefix'));
	bcn_update_option('bcn_paged_suffix', bcn_get('paged_suffix'));
	//Post related options
	bcn_update_option('bcn_post_prefix', bcn_get('post_prefix'));
	bcn_update_option('bcn_post_suffix', bcn_get('post_suffix'));
	bcn_update_option('bcn_post_taxonomy_type', bcn_get('post_taxonomy_type'));
	bcn_update_option('bcn_post_taxonomy_display', bcn_get('post_taxonomy_display', 'false'));
	bcn_update_option('bcn_post_anchor', bcn_get('post_anchor'));
	//Category settings
	bcn_update_option('bcn_category_prefix', bcn_get('category_prefix'));
	bcn_update_option('bcn_category_suffix', bcn_get('category_suffix'));
	bcn_update_option('bcn_category_anchor', bcn_get('category_anchor'));
	//Tag settings
	bcn_update_option('bcn_tag_prefix', bcn_get('tag_prefix'));
	bcn_update_option('bcn_tag_suffix', bcn_get('tag_suffix'));
	bcn_update_option('bcn_tag_anchor', bcn_get('tag_anchor'));
	//Archive by date settings
	bcn_update_option('bcn_archive_date_prefix', bcn_get('archive_date_prefix'));
	bcn_update_option('bcn_archive_date_suffix', bcn_get('archive_date_suffix'));
	bcn_update_option('bcn_date_anchor', bcn_get('date_anchor'));
}
//Deals with the "action link" in the plugin page
function bcn_filter_plugin_actions($links, $file)
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
 * bcn_add_page
 *
 * Creates link to admin interface
 */
function bcn_add_page()
{
	global $bcn_admin_req;
	if(current_user_can('manage_options'))
	{
		add_submenu_page('options-general.php', 'Breadcrumb NavXT Settings', 'Breadcrumb NavXT', 'manage_options', 'breadcrumb-navxt', 'bcn_admin');
		add_filter( 'plugin_action_links', 'bcn_filter_plugin_actions', 10, 2 );
	}
}
/**
 * bcn_admin
 *
 * The actual administration interface
 */
function bcn_admin()
{
	global $bcn_admin_req, $bcn_admin_version, $bcn_version;
	//Makes sure the user has the proper permissions. Dies on failure.
	bcn_security();
	//Initilizes l10n domain	
	bcn_local();
	//See if the administrative interface matches versions with the class, if not then warn the user
	list($bcn_plugin_major, $bcn_plugin_minor, $bcn_plugin_bugfix) = explode('.', $bcn_version);	
	list($bcn_admin_major,  $bcn_admin_minor,  $bcn_admin_bugfix)  = explode('.', $bcn_admin_version);		
	if($bcn_plugin_major != $bcn_admin_major || $bcn_plugin_minor != $bcn_admin_minor)
	{
		?>
		<div id="message" class="updated fade">
			<p><?php _e('Warning, your version of Breadcrumb NavXT does not match the version supported by this administrative interface. As a result, settings may not work as expected.', 'breadcrumb_navxt'); ?></p>
			<p><?php _e('Your Breadcrumb NavXT Administration interface version is ', 'breadcrumb_navxt'); echo $bcn_admin_version; ?>.</p>
			<p><?php _e('Your Breadcrumb NavXT version is ', 'breadcrumb_navxt'); echo $bcn_version; ?>.</p>
		</div>
		<?php 
	}
	//Output the administration panel (until end of function)
	?>
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
						<label for="trail_linked"><?php _e('Breadcrumbs Linked', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<label>
							<input name="trail_linked" type="checkbox" id="trail_linked" value="true" <?php checked('true', bcn_get_option('bcn_trail_linked')); ?> />
							<?php _e('Yes'); ?>
						</label><br />
						<?php _e('Allow breadcrumbs in the trail to be linked.', 'breadcrumb_navxt');?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e('Home Breadcrumb', 'breadcrumb_navxt'); ?>						
					</th>
					<td>
						<p>
							<label>
								<input name="home_display" type="radio" value="false" class="togx" <?php checked('false', bcn_get_option('bcn_home_display')); ?> />
								<?php _e('Leave the home breadcrumb out of the trail.', 'breadcrumb_navxt'); ?>
							</label>
						</p>
						<p>
							<label>
								<input name="home_display" type="radio" value="true" class="togx" <?php checked('true', bcn_get_option('bcn_home_display')); ?> />
								<?php _e('Place the home breadcrumb in the trail.', 'breadcrumb_navxt'); ?>	
							</label>
							<ul>
								<li>
									<label for="home_title">
										<?php _e('Home Title: ','breadcrumb_navxt');?>
										<input type="text" name="home_title" id="home_title" value="<?php echo bcn_get_option_inputvalue('bcn_home_title'); ?>" size="20" />			
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
						<input type="text" name="separator" id="separator" value="<?php echo bcn_get_option_inputvalue('bcn_separator'); ?>" size="32" /><br />
						<?php _e('Placed in between each breadcrumb.', 'breadcrumb_navxt'); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="max_title_length"><?php _e('Breadcrumb Max Title Length', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="max_title_length" id="max_title_length" value="<?php echo bcn_get_option_inputvalue('bcn_max_title_length'); ?>" size="10" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="home_anchor"><?php _e('Home Anchor', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="home_anchor" id="home_anchor" value="<?php echo bcn_get_option_inputvalue('bcn_home_anchor'); ?>" size="60" /><br />
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
						<input type="text" name="blog_anchor" id="blog_anchor" value="<?php echo bcn_get_option_inputvalue('bcn_blog_anchor'); ?>" size="60" /><br />
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
							<input name="current_item_linked" type="checkbox" id="current_item_linked" value="true" <?php checked('true', bcn_get_option('bcn_current_item_linked')); ?> />
							<?php _e('Yes'); ?>							
						</label>					
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="current_item_prefix"><?php _e('Current Item Prefix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="current_item_prefix" id="current_item_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_current_item_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="current_item_suffix"><?php _e('Current Item Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="current_item_suffix" id="current_item_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_current_item_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="current_item_anchor"><?php _e('Current Item Anchor', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="current_item_anchor" id="current_item_anchor" value="<?php echo bcn_get_option_inputvalue('bcn_current_item_anchor'); ?>" size="60" /><br />
						<?php _e('The anchor template for current item breadcrumbs.', 'breadcrumb_navxt'); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="paged_display"><?php _e('Display Paged Text', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<label>
							<input name="paged_display" type="checkbox" id="paged_display" value="true" <?php checked('true', bcn_get_option('bcn_paged_display')); ?> />
							<?php _e('Show that the user is on a page other than the first on posts/archives with multiple pages.', 'breadcrumb_navxt'); ?>							
						</label>	
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="paged_prefix"><?php _e('Paged Prefix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="paged_prefix" id="paged_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_paged_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="paged_suffix"><?php _e('Paged Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="paged_suffix" id="paged_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_paged_suffix'); ?>" size="32" />
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
						<input type="text" name="post_prefix" id="post_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_post_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="post_suffix"><?php _e('Post Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="post_suffix" id="post_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_post_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="post_anchor"><?php _e('Post Anchor', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="post_anchor" id="post_anchor" value="<?php echo bcn_get_option_inputvalue('bcn_post_anchor'); ?>" size="60" /><br />
						<?php _e('The anchor template for post breadcrumbs.', 'breadcrumb_navxt'); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e('Post Taxonomy Display', 'breadcrumb_navxt'); ?>
					</th>
					<td>
						<label for="post_taxonomy_display">
							<input name="post_taxonomy_display" type="checkbox" id="post_taxonomy_display" value="true" <?php checked('true', bcn_get_option('bcn_post_taxonomy_display')); ?> />
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
								<input name="post_taxonomy_type" type="radio" value="category" class="togx" <?php checked('category', bcn_get_option('bcn_post_taxonomy_type')); ?> />
								<?php _e('Categories'); ?>
							</label>
						</p>
						<p>
							<label>
								<input name="post_taxonomy_type" type="radio" value="tag" class="togx" <?php checked('tag', bcn_get_option('bcn_post_taxonomy_type')); ?> />
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
						<input type="text" name="page_prefix" id="page_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_page_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="page_suffix"><?php _e('Page Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="page_suffix" id="page_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_page_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="page_anchor"><?php _e('Page Anchor', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="page_anchor" id="page_anchor" value="<?php echo bcn_get_option_inputvalue('bcn_page_anchor'); ?>" size="60" /><br />
						<?php _e('The anchor template for page breadcrumbs.', 'breadcrumb_navxt'); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="attachment_prefix"><?php _e('Attachment Prefix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="attachment_prefix" id="attachment_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_attachment_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="attachment_suffix"><?php _e('Attachment Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="attachment_suffix" id="attachment_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_attachment_suffix'); ?>" size="32" />
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
						<input type="text" name="archive_category_prefix" id="archive_category_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_category_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="archive_category_suffix"><?php _e('Archive by Category Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="archive_category_suffix" id="archive_category_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_category_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="category_prefix"><?php _e('Category Prefix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="category_prefix" id="category_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_category_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="category_suffix"><?php _e('Category Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="category_suffix" id="category_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_category_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="category_anchor"><?php _e('Category Anchor', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="category_anchor" id="category_anchor" value="<?php echo bcn_get_option_inputvalue('bcn_category_anchor'); ?>" size="60" /><br />
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
						<input type="text" name="archive_tag_prefix" id="archive_tag_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_tag_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="archive_tag_suffix"><?php _e('Archive by Tag Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="archive_tag_suffix" id="archive_tag_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_tag_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="tag_prefix"><?php _e('Tag Prefix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="tag_prefix" id="tag_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_tag_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="tag_suffix"><?php _e('Tag Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="tag_suffix" id="tag_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_tag_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="tag_anchor"><?php _e('Tag Anchor', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="tag_anchor" id="tag_anchor" value="<?php echo bcn_get_option_inputvalue('bcn_tag_anchor'); ?>" size="60" /><br />
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
						<input type="text" name="archive_date_prefix" id="archive_date_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_date_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="archive_date_suffix"><?php _e('Archive by Date Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="archive_date_suffix" id="archive_date_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_archive_date_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="date_anchor"><?php _e('Date Anchor', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="date_anchor" id="date_anchor" value="<?php echo bcn_get_option_inputvalue('bcn_date_anchor'); ?>" size="60" /><br />
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
						<input type="text" name="author_prefix" id="author_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_author_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="author_suffix"><?php _e('Author Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="author_suffix" id="author_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_author_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="author_display"><?php _e('Author Display Format', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<select name="author_display" id="author_display">
							<?php bcn_select_options('bcn_author_display', array("display_name", "nickname", "first_name", "last_name")); ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="search_prefix"><?php _e('Search Prefix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="search_prefix" id="search_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_search_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="search_suffix"><?php _e('Search Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="search_suffix" id="search_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_search_suffix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="404_title"><?php _e('404 Title', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="404_title" id="404_title" value="<?php echo bcn_get_option_inputvalue('bcn_404_title'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="404_prefix"><?php _e('404 Prefix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="404_prefix" id="404_prefix" value="<?php echo bcn_get_option_inputvalue('bcn_404_prefix'); ?>" size="32" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="404_suffix"><?php _e('404 Suffix', 'breadcrumb_navxt'); ?></label>
					</th>
					<td>
						<input type="text" name="404_suffix" id="404_suffix" value="<?php echo bcn_get_option_inputvalue('bcn_404_suffix'); ?>" size="32" />
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
 * bcn_select_options
 *
 * displays wordpress options as <seclect> options defaults to true/false
 *
 * @param (string) optionname name of wordpress options store
 * @param (array) options array of options defaults to array('true','false')
 */
function bcn_select_options($optionname, $options = array('true','false'))
{
	$value = get_option($optionname);
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
 * bcn_widget
 *
 * The sidebar widget 
 */
function bcn_widget($args)
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
 * bcn_register_widget
 *
 * Registers the sidebar widget 
 */
function bcn_register_widget()
{
	register_sidebar_widget('Breadcrumb NavXT', 'bcn_widget');
}
/**
 * bcn_local
 *
 * Initilizes localization domain
 */
function bcn_local()
{
	//Load breadcrumb-navxt translation
	load_plugin_textdomain($domain = 'breadcrumb_navxt', $path = PLUGINDIR . '/breadcrumb-navxt');
}
//WordPress hooks
if(function_exists('add_action')){
	//Installation Script hook
	add_action('activate_breadcrumb-navxt/breadcrumb_navxt_admin.php','bcn_install');
	//WordPress Admin interface hook
	add_action('admin_menu', 'bcn_add_page');
	//WordPress Hook for the widget
	add_action('plugins_loaded','bcn_register_widget');
	//Admin Options hook
	if(isset($_POST['bcn_admin_options']))
	{
		add_action('init', 'bcn_admin_options');
	}
}

?>