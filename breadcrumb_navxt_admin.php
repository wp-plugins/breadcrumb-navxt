<?php
/*
Plugin Name: Breadcrumb NavXT
Plugin URI: http://mtekk.us/code/breadcrumb-navxt/
Description: Adds a breadcrumb navigation showing the visitor&#39;s path to their current location. For details on how to use this plugin visit <a href="http://mtekk.us/code/breadcrumb-navxt/">Breadcrumb NavXT</a>. 
Version: 3.9.20
Author: John Havlik
Author URI: http://mtekk.us/
License: GPL2
TextDomain: breadcrumb_navxt
DomainPath: /languages/

*/
/*  Copyright 2007-2011  John Havlik  (email : mtekkmonkey@gmail.com)

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
//Do a PHP version check, require 5.2 or newer
if(version_compare(phpversion(), '5.2.0', '<'))
{
	//Only purpose of this function is to echo out the PHP version error
	function bcn_phpold()
	{
		printf('<div class="error"><p>' . __('Your PHP version is too old, please upgrade to a newer version. Your version is %s, Breadcrumb NavXT requires %s', 'breadcrumb_navxt') . '</p></div>', phpversion(), '5.2.0');
	}
	//If we are in the admin, let's print a warning then return
	if(is_admin())
	{
		add_action('admin_notices', 'bcn_phpold');
	}
	return;
}
//Include the breadcrumb class
require_once(dirname(__FILE__) . '/breadcrumb_navxt_class.php');
//Include the WP 2.8+ widget class
require_once(dirname(__FILE__) . '/breadcrumb_navxt_widget.php');
//Include admin base class
if(!class_exists('mtekk_adminKit'))
{
	require_once(dirname(__FILE__) . '/includes/mtekk_adminkit.php');
}
/**
 * The administrative interface class 
 * 
 */
class bcn_admin extends mtekk_adminKit
{
	/**
	 * local store for breadcrumb version
	 * 
	 * @var   string
	 */
	protected $version = '3.99.58';
	protected $full_name = 'Breadcrumb NavXT Settings';
	protected $short_name = 'Breadcrumb NavXT';
	protected $access_level = 'manage_options';
	protected $identifier = 'breadcrumb_navxt';
	protected $unique_prefix = 'bcn';
	protected $plugin_basename = 'breadcrumb-navxt/breadcrumb_navxt_admin.php';
	/**
	 * local store for the breadcrumb object
	 * 
	 * @see   bcn_admin()
	 * @var   bcn_breadcrumb
	 */
	public $breadcrumb_trail;
	/**
	 * Administrative interface class default constructor
	 */
	function bcn_admin()
	{
		//We'll let it fail fataly if the class isn't there as we depend on it
		$this->breadcrumb_trail = new bcn_breadcrumb_trail;
		//First make sure our defaults are safe
		$this->find_posttypes($this->breadcrumb_trail->opt);
		$this->find_taxonomies($this->breadcrumb_trail->opt);
		//Grab defaults from the breadcrumb_trail object
		$this->opt = $this->breadcrumb_trail->opt;
		//We set the plugin basename here, could manually set it, but this is for demonstration purposes
		//$this->plugin_basename = plugin_basename(__FILE__);
		//Register the WordPress 2.8 Widget
		add_action('widgets_init', create_function('', 'return register_widget("'. $this->unique_prefix . '_widget");'));
		//We're going to make sure we load the parent's constructor
		parent::__construct();
	}
	/**
	 * admin initialization callback function
	 * 
	 * is bound to wpordpress action 'admin_init' on instantiation
	 * 
	 * @since  3.2.0
	 * @return void
	 */
	function init()
	{
		//We're going to make sure we run the parent's version of this function as well
		parent::init();	
		//Grab the current settings from the DB
		//$this->opt = get_option('bcn_options');
		//We can not synchronize our database options untill after the parent init runs (the reset routine must run first if it needs to)
		$this->opt = $this->parse_args(get_option($this->unique_prefix . '_options'), $this->opt);
		//Add javascript enqeueing callback
		add_action('wp_print_scripts', array($this, 'javascript'));
	}
	/**
	 * Makes sure the current user can manage options to proceed
	 */
	function security()
	{
		//If the user can not manage options we will die on them
		if(!current_user_can($this->access_level))
		{
			wp_die(__('Insufficient privileges to proceed.', 'breadcrumb_navxt'));
		}
	}
	/**
	 * Upgrades input options array, sets to $this->opt
	 * 
	 * @param array $opts
	 * @param string $version the version of the passed in options
	 */
	function opts_upgrade($opts, $version)
	{
		global $wp_post_types;
		//If our version is not the same as in the db, time to update
		if($version !== $this->version)
		{
			//Upgrading to 3.8.1
			if(version_compare($version, '3.8.1', '<'))
			{
				$opts['post_page_root'] = get_option('page_on_front');
				$opts['post_post_root'] = get_option('page_for_posts');
			}
			//Upgrading to 4.0
			if(version_compare($version, '4.0.0', '<'))
			{
				
			}
			//Save the passed in opts to the object's option array
			$this->opt = $opts;
		}
	}
	/**
	 * Updates the database settings from the webform
	 */
	/*function opts_update()
	{
		//Do some security related thigns as we are not using the normal WP settings API
		$this->security();
		//Do a nonce check, prevent malicious link/form problems
		check_admin_referer('bcn_options-options');
		//Update local options from database
		$this->opt = get_option('bcn_options');
		//If we did not get an array, might as well just quit here
		if(!is_array($this->opt))
		{
			return;
		}
		//Add custom post types
		$this->find_posttypes($this->opt);
		//Add custom taxonomy types
		$this->find_taxonomies($this->opt);
		//Update our backup options
		update_option('bcn_options_bk', $this->opt);
		//Grab our incomming array (the data is dirty)
		$input = $_POST['bcn_options'];
		//We have two "permi" variables
		$input['post_page_root'] = get_option('page_on_front');
		$input['post_post_root'] = get_option('page_for_posts');
		//Loop through all of the existing options (avoids random setting injection)
		foreach($this->opt as $option => $value)
		{
			//Handle all of our boolean options first
			if(strpos($option, 'display') > 0 || $option == 'current_item_linked')
			{
				$this->opt[$option] = isset($input[$option]);
			}
			//Now handle anything that can't be blank
			else if(strpos($option, 'anchor') > 0)
			{
				//Only save a new anchor if not blank
				if(isset($input[$option]))
				{
					//Do excess slash removal sanitation
					$this->opt[$option] = stripslashes($input[$option]);
				}
			}
			//Now everything else
			else
			{
				$this->opt[$option] = stripslashes($input[$option]);
			}
		}
		//Commit the option changes
		update_option('bcn_options', $this->opt);
		//Check if known settings match attempted save
		if(count(array_diff_key($input, $this->opt)) == 0)
		{
			//Let the user know everything went ok
			$this->message['updated fade'][] = __('Settings successfully saved.', $this->identifier) . $this->undo_anchor(__('Undo the options save.', $this->identifier));
		}
		else
		{
			//Let the user know the following were not saved
			$this->message['updated fade'][] = __('Some settings were not saved.', $this->identifier) . $this->undo_anchor(__('Undo the options save.', $this->identifier));
			$temp = __('The following settings were not saved:', $this->identifier);
			foreach(array_diff_key($input, $this->opt) as $setting => $value)
			{
				$temp .= '<br />' . $setting;
			}
			$this->message['updated fade'][] = $temp . '<br />' . sprintf(__('Please include this message in your %sbug report%s.', $this->identifier),'<a title="' . __('Go to the Breadcrumb NavXT support post for your version.', $this->identifier) . '" href="http://mtekk.us/archives/wordpress/plugins-wordpress/breadcrumb-navxt-' . $this->version . '/#respond">', '</a>');
		}
		add_action('admin_notices', array($this, 'message'));
	}*/
	/**
	 * Enqueues JS dependencies (jquery) for the tabs
	 * 
	 * @see admin_init()
	 * @return void
	 */
	function javascript()
	{
		//Enqueue ui-tabs
		wp_enqueue_script('jquery-ui-tabs');
	}
	/**
	 * help action hook function
	 * 
	 * @param  string $screen
	 * @return string
	 * 
	 */
	function help($screen)
	{
		//Add contextual help on current screen
		if($screen->id == 'settings_page_' . $this->identifier)
		{
			$screen->add_help_tab(
				array(
					'id' => 'general',
					'tile' => __('General', $this->identifier),
					'content' => sprintf(__('Tips for the settings are located below select options. Please refer to the %sdocumentation%s for more information.', $this->identifier), 
			'<a title="' . __('Go to the Breadcrumb NavXT online documentation', $this->identifier) . '" href="http://mtekk.us/code/breadcrumb-navxt/breadcrumb-navxt-doc/">', '</a>')
				));
		}
	}
	/**
	 * get help text
	 * 
	 * @return string
	 * 
	 * TODO: move this over to the new help function, need WP to be fixed on its side first
	 */
	protected function _get_help_text()
	{
		return '<p>' . sprintf(__('Tips for the settings are located below select options. Please refer to the %sdocumentation%s for more information.', 'breadcrumb_navxt'), 
			'<a title="' . __('Go to the Breadcrumb NavXT online documentation', 'breadcrumb_navxt') . '" href="http://mtekk.us/code/breadcrumb-navxt/breadcrumb-navxt-doc/">', '</a>') . ' ' .
			sprintf(__('If you think you have found a bug, please include your WordPress version and details on how to reproduce the bug when you %sreport the issue%s.', $this->identifier),'<a title="' . __('Go to the Breadcrumb NavXT support post for your version.', 'breadcrumb_navxt') . '" href="http://mtekk.us/archives/wordpress/plugins-wordpress/breadcrumb-navxt-' . $this->version . '/#respond">', '</a>') . '</p><h5>' .
		__('Quick Start Information', 'breadcrumb_navxt') . '</h5><p>' . __('For the settings on this page to take effect, you must either use the included Breadcrumb NavXT widget, or place either of the code sections below into your theme.', 'breadcrumb_navxt') .
		'</p><h5>' . __('Breadcrumb trail with separators', 'breadcrumb_navxt').'</h5><code>&lt;div class="breadcrumbs"&gt;'."&lt;?php if(function_exists('bcn_display')){ bcn_display();}?&gt;&lt;/div&gt;</code>" .
		'<h5>' . __('Breadcrumb trail in list form', 'breadcrumb_navxt').'</h5><code>&lt;ol class="breadcrumbs"&gt;'."&lt;?php if(function_exists('bcn_display_list')){ bcn_display_list();}?&gt;&lt;/ol&gt;</code>";
	}
	/**
	 * enqueue's the tab style sheet on the settings page
	 */
	function admin_styles()
	{
		wp_enqueue_style('mtekk_adminkit_tabs');
	}
	/**
	 * enqueue's the tab js and translation js on the settings page
	 */
	function admin_scripts()
	{
		//Enqueue the admin tabs javascript
		wp_enqueue_script('mtekk_adminkit_tabs');
		//Load the translations for the tabs
		wp_localize_script('mtekk_adminkit_tabs', 'objectL10n', array(
			'mtad_uid' => 'bcn_admin',
			'mtad_import' => __('Import', $this->identifier),
			'mtad_export' => __('Export', $this->identifier),
			'mtad_reset' => __('Reset', $this->identifier),
		));
	}
	/**
	 * Adds in the JavaScript and CSS for the tabs in the adminsitrative 
	 * interface
	 */
	function admin_head()
	{
		
	}
	/**
	 * The administrative page for Breadcrumb NavXT
	 */
	function admin_page()
	{
		global $wp_taxonomies, $wp_post_types;
		$this->security();?>
		<div class="wrap"><h2><?php _e('Breadcrumb NavXT Settings', 'breadcrumb_navxt'); ?></h2>
		<?php
		//We exit after the version check if there is an action the user needs to take before saving settings
		if(!$this->version_check(get_option($this->unique_prefix . '_version')))
		{
			return;
		}
		?>
		<form action="options-general.php?page=breadcrumb_navxt" method="post" id="bcn_admin-options">
			<?php settings_fields('bcn_options');?>
			<div id="hasadmintabs">
			<fieldset id="general" class="bcn_options">
				<h3><?php _e('General', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Breadcrumb Separator', 'breadcrumb_navxt'), 'hseparator', '32', false, __('Placed in between each breadcrumb.', 'breadcrumb_navxt'));
						$this->input_text(__('Breadcrumb Max Title Length', 'breadcrumb_navxt'), 'amax_title_length', '10');
					?>
					<tr valign="top">
						<th scope="row">
							<?php _e('Home Breadcrumb', 'breadcrumb_navxt'); ?>						
						</th>
						<td>
							<label>
								<input name="bcn_options[bhome_display]" type="checkbox" id="bhome_display" value="true" <?php checked(true, $this->opt['bhome_display']); ?> />
								<?php _e('Place the home breadcrumb in the trail.', 'breadcrumb_navxt'); ?>				
							</label><br />
							<ul>
								<li>
									<label for="Shome_title">
										<?php _e('Home Title: ','breadcrumb_navxt');?>
										<input type="text" name="bcn_options[Shome_title]" id="Shome_title" value="<?php echo esc_html($this->opt['Shome_title'], ENT_COMPAT, 'UTF-8'); ?>" size="20" />
									</label>
								</li>
							</ul>							
						</td>
					</tr>
					<?php
						$this->input_text(__('Home Template', 'breadcrumb_navxt'), 'Hhome_template', '64', false, __('The template for the home breadcrumb.', 'breadcrumb_navxt'));
						$this->input_text(__('Home Template (Unlinked)', 'breadcrumb_navxt'), 'Hhome_template_no_anchor', '64', false, __('The template for the home breadcrumb, used when the breadcrumb is not linked.', 'breadcrumb_navxt'));
						$this->input_check(__('Blog Breadcrumb', 'breadcrumb_navxt'), 'bblog_display', __('Place the blog breadcrumb in the trail.', 'breadcrumb_navxt'), (get_option('show_on_front') !== "page"));
						$this->input_text(__('Blog Template', 'breadcrumb_navxt'), 'Hblog_template', '64', (get_option('show_on_front') !== "page"), __('The template for the blog breadcrumb, used only in static front page environments.', 'breadcrumb_navxt'));
						$this->input_text(__('Blog Template (Unlinked)', 'breadcrumb_navxt'), 'Hblog_template_no_anchor', '64', (get_option('show_on_front') !== "page"), __('The template for the blog breadcrumb, used only in static front page environments and when the breadcrumb is not linked.', 'breadcrumb_navxt'));
					?>
					<tr valign="top">
						<th scope="row">
							<?php _e('Main Site Breadcrumb', 'breadcrumb_navxt'); ?>						
						</th>
						<td>
							<label>
								<input name="bcn_options[bmainsite_display]" type="checkbox" id="bmainsite_display" <?php if(!is_multisite()){echo 'disabled="disabled" class="disabled"';}?> value="true" <?php checked(true, $this->opt['bmainsite_display']); ?> />
								<?php _e('Place the main site home breadcrumb in the trail in an multisite setup.', 'breadcrumb_navxt'); ?>				
							</label><br />
							<ul>
								<li>
									<label for="Smainsite_title">
										<?php _e('Main Site Home Title: ', 'breadcrumb_navxt');?>
										<input type="text" name="bcn_options[Smainsite_title]" id="Smainsite_title" <?php if(!is_multisite()){echo 'disabled="disabled" class="disabled"';}?> value="<?php echo htmlentities($this->opt['Smainsite_title'], ENT_COMPAT, 'UTF-8'); ?>" size="20" />
										<?php if(!is_multisite()){?><input type="hidden" name="bcn_options[Smainsite_title]" value="<?php echo htmlentities($this->opt['Smainsite_title'], ENT_COMPAT, 'UTF-8');?>" /><?php } ?>
									</label>
								</li>
							</ul>							
						</td>
					</tr>
					<?php
						$this->input_text(__('Main Site Home Template', 'breadcrumb_navxt'), 'Hmainsite_template', '64', !is_multisite(), __('The template for the main site home breadcrumb, used only in multisite environments.', 'breadcrumb_navxt'));
						$this->input_text(__('Main Site Home Template (Unlinked)', 'breadcrumb_navxt'), 'Hmainsite_template_no_anchor', '64', !is_multisite(), __('The template for the main site home breadcrumb, used only in multisite environments and when the breadcrumb is not linked.', 'breadcrumb_navxt'));
					?>
				</table>
			</fieldset>
			<fieldset id="current" class="bcn_options">
				<h3><?php _e('Current Item', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_check(__('Link Current Item', 'breadcrumb_navxt'), 'bcurrent_item_linked', __('Yes'));
						$this->input_text(__('Current Item Template', 'breadcrumb_navxt'), 'Hcurrent_item_template', '64', false, __('The template for current item breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Current Item Template (Unlinked)', 'breadcrumb_navxt'), 'Hcurrent_item_template_no_anchor', '64', false, __('The template for current item breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'));
						$this->input_check(__('Paged Breadcrumb', 'breadcrumb_navxt'), 'bpaged_display', __('Include the paged breadcrumb in the breadcrumb trail.', 'breadcrumb_navxt'), false, __('Indicates that the user is on a page other than the first on paginated posts/pages.', 'breadcrumb_navxt'));
						$this->input_text(__('Paged Template', 'breadcrumb_navxt'), 'Hpaged_template', '64', false, __('The template for paged breadcrumbs.', 'breadcrumb_navxt'));
					?>
				</table>
			</fieldset>
			<fieldset id="single" class="bcn_options">
				<h3><?php _e('Posts &amp; Pages', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Post Template', 'breadcrumb_navxt'), 'Hpost_post_template', '64', false, __('The template for post breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Post Template (Unlinked)', 'breadcrumb_navxt'), 'Hpost_post_template_no_anchor', '64', false, __('The template for post breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'));
						$this->input_check(__('Post Taxonomy Display', 'breadcrumb_navxt'), 'bpost_post_taxonomy_display', __('Show the taxonomy leading to a post in the breadcrumb trail.', 'breadcrumb_navxt'));
					?>
					<tr valign="top">
						<th scope="row">
							<?php _e('Post Taxonomy', 'breadcrumb_navxt'); ?>
						</th>
						<td>
							<?php
								$this->input_radio('Spost_post_taxonomy_type', 'category', __('Categories'));
								$this->input_radio('Spost_post_taxonomy_type', 'date', __('Dates'));
								$this->input_radio('Spost_post_taxonomy_type', 'post_tag', __('Tags'));
								$this->input_radio('Spost_post_taxonomy_type', 'page', __('Pages'));
								//Loop through all of the taxonomies in the array
								foreach($wp_taxonomies as $taxonomy)
								{
									//We only want custom taxonomies
									if(($taxonomy->object_type == 'post' || is_array($taxonomy->object_type) && in_array('post', $taxonomy->object_type)) && !$taxonomy->_builtin)
									{
										$this->input_radio('Spost_post_taxonomy_type', $taxonomy->name, mb_convert_case(__($taxonomy->label), MB_CASE_TITLE, 'UTF-8'));
									}
								}
							?>
							<span class="setting-description"><?php _e('The taxonomy which the breadcrumb trail will show.', 'breadcrumb_navxt'); ?></span>
						</td>
					</tr>
					<?php
						$this->input_text(__('Page Template', 'breadcrumb_navxt'), 'Hpost_page_template', '64', false, __('The template for page breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Page Template (Unlinked)', 'breadcrumb_navxt'), 'Hpost_page_template_no_anchor', '64', false, __('The template for page breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'));
						$this->input_text(__('Attachment Template', 'breadcrumb_navxt'), 'Hpost_attachment_template', '64', false, __('The template for attachment breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Attachment Template (Unlinked)', 'breadcrumb_navxt'), 'Hpost_attachment_template_no_anchor', '64', false, __('The template for attachment breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'));
					?>
				</table>
			</fieldset>
			<?php
			//Loop through all of the post types in the array
			foreach($wp_post_types as $post_type)
			{
				//We only want custom post types
				if(!$post_type->_builtin)
				{
					//If the post type does not have settings in the options array yet, we need to load some defaults
					if(!array_key_exists('Hpost_' . $post_type->name . '_template', $this->opt) || !$post_type->hierarchical && !array_key_exists('Spost_' . $post_type->name . '_taxonomy_type', $this->opt))
					{
						//Add the necessary option array members
						$this->opt['Hpost_' . $post_type->name . '_template'] = __('<a title="Go to %title%." href="%link%">%htitle%</a>', 'breadcrumb_navxt');
						$this->opt['Hpost_' . $post_type->name . '_template_no_anchor'] = __('%htitle%', 'breadcrumb_navxt');
						//Do type dependent tasks
						if($post_type->hierarchical)
						{
							//Set post_root for hierarchical types
							$this->opt['apost_' . $post_type->name . '_root'] = get_option('page_on_front');
						}
						//If it is flat, we need a taxonomy selection
						else
						{
							//Set post_root for flat types
							$this->opt['apost_' . $post_type->name . '_root'] = get_option('page_for_posts');
							//Default to not displaying a taxonomy
							$this->opt['bpost_' . $post_type->name . '_taxonomy_display'] = false;
							//Loop through all of the possible taxonomies
							foreach($wp_taxonomies as $taxonomy)
							{
								//Activate the first taxonomy valid for this post type and exit the loop
								if($taxonomy->object_type == $post_type->name || in_array($post_type->name, $taxonomy->object_type))
								{
									$this->opt['bpost_' . $post_type->name . '_taxonomy_display'] = true;
									$this->opt['Spost_' . $post_type->name . '_taxonomy_type'] = $taxonomy->name;
									break;
								}
							}
							//If there are no valid taxonomies for this type, we default to not displaying taxonomies for this post type
							if(!isset($this->opt['Spost_' . $post_type->name . '_taxonomy_type']))
							{
								$this->opt['Spost_' . $post_type->name . '_taxonomy_type'] = 'date';
							}
						}
					}?>
			<fieldset id="post_<?php echo $post_type->name ?>" class="bcn_options">
				<h3><?php echo $post_type->labels->singular_name; ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(sprintf(__('%s Template', 'breadcrumb_navxt'), $post_type->labels->singular_name), 'Hpost_' . $post_type->name . '_template', '64', false, sprintf(__('The template for %s breadcrumbs.', 'breadcrumb_navxt'), strtolower(__($post_type->labels->singular_name))));
						$this->input_text(sprintf(__('%s Template (Unlinked)', 'breadcrumb_navxt'), $post_type->labels->singular_name), 'Hpost_' . $post_type->name . '_template_no_anchor', '64', false, sprintf(__('The template for %s breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'), strtolower(__($post_type->labels->singular_name))));
						$optid = $this->get_valid_id('apost_' . $post_type->name . '_root');
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo $optid;?>"><?php printf(__('%s Root Page', 'breadcrumb_navxt'), $post_type->labels->singular_name);?></label>
						</th>
						<td>
							<?php wp_dropdown_pages(array('name' => $this->unique_prefix . '_options[apost_' . $post_type->name . '_root]', 'id' => $optid, 'echo' => 1, 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => $this->opt['apost_' . $post_type->name . '_root']));?>
						</td>
					</tr>
					<?php
						//If it is flat, we need a taxonomy selection
						if(!$post_type->hierarchical)
						{
							$this->input_check(sprintf(__('%s Taxonomy Display', 'breadcrumb_navxt'), $post_type->labels->singular_name), 'bpost_' . $post_type->name . '_taxonomy_display', sprintf(__('Show the taxonomy leading to a %s in the breadcrumb trail.', 'breadcrumb_navxt'), strtolower(__($post_type->labels->singular_name))));
					?>
					<tr valign="top">
						<th scope="row">
							<?php printf(__('%s Taxonomy', 'breadcrumb_navxt'), $post_type->labels->singular_name); ?>
						</th>
						<td>
							<?php
								$this->input_radio('Spost_' . $post_type->name . '_taxonomy_type', 'date', __('Dates'));
								$this->input_radio('Spost_' . $post_type->name . '_taxonomy_type', 'page', __('Pages'));
								//Loop through all of the taxonomies in the array
								foreach($wp_taxonomies as $taxonomy)
								{
									//We only want custom taxonomies
									if($taxonomy->object_type == $post_type->name || in_array($post_type->name, $taxonomy->object_type))
									{
										$this->input_radio('Spost_' . $post_type->name . '_taxonomy_type', $taxonomy->name, $taxonomy->labels->singular_name);
									}
								}
							?>
							<span class="setting-description"><?php _e('The taxonomy which the breadcrumb trail will show.', 'breadcrumb_navxt'); ?></span>
						</td>
					</tr>
					<?php } ?>
				</table>
			</fieldset>
					<?php
				}
			}?>
			<fieldset id="tax" class="bcn_options alttab">
				<h3><?php _e('Categories &amp; Tags', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Category Template', 'breadcrumb_navxt'), 'Hcategory_template', '64', false, __('The template for category breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Category Template (Unlinked)', 'breadcrumb_navxt'), 'Hcategory_template_no_anchor', '64', false, __('The template for category breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'));
						$this->input_text(__('Tag Template', 'breadcrumb_navxt'), 'Hpost_tag_template', '64', false, __('The template for tag breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Tag Template (Unlinked)', 'breadcrumb_navxt'), 'Hpost_tag_template_no_anchor', '64', false, __('The template for tag breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'));
					?>
				</table>
			</fieldset>
			<?php
			//Loop through all of the taxonomies in the array
			foreach($wp_taxonomies as $taxonomy)
			{
				//We only want custom taxonomies
				if(!$taxonomy->_builtin)
				{
					//If the taxonomy does not have settings in the options array yet, we need to load some defaults
					if(!array_key_exists('H' . $taxonomy->name . '_template', $this->opt))
					{
						//Add the necessary option array members
						$this->opt['H' . $taxonomy->name . '_template'] = __(sprintf('<a title="Go to the %%title%% %s archives." href="%%link%%">%%htitle%%</a>', $taxonomy->labels->singular_name), 'breadcrumb_navxt');
						$this->opt['H' . $taxonomy->name . '_template_no_anchor'] = __(sprintf('%%htitle%%', $taxonomy->labels->singular_name), 'breadcrumb_navxt');
					}
				?>
			<fieldset id="<?php echo $taxonomy->name; ?>" class="bcn_options alttab">
				<h3><?php echo mb_convert_case(__($taxonomy->label), MB_CASE_TITLE, 'UTF-8'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(sprintf(__('%s Template', 'breadcrumb_navxt'), $taxonomy->labels->singular_name), 'H' . $taxonomy->name . '_template', '64', false, sprintf(__('The template for %s breadcrumbs.', 'breadcrumb_navxt'), strtolower(__($taxonomy->label))));
						$this->input_text(sprintf(__('%s Template (Unlinked)', 'breadcrumb_navxt'), $taxonomy->labels->singular_name), 'H' . $taxonomy->name . '_template_no_anchor', '64', false, sprintf(__('The template for %s breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'), strtolower(__($taxonomy->label))));
					?>
				</table>
			</fieldset>
				<?php
				}
			}
			?>
			<fieldset id="miscellaneous" class="bcn_options">
				<h3><?php _e('Miscellaneous', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Author Template', 'breadcrumb_navxt'), 'Hauthor_template', '64', false, __('The template for author breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Author Template (Unlinked)', 'breadcrumb_navxt'), 'Hauthor_template_no_anchor', '64', false, __('The template for author breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'));
						$this->input_select(__('Author Display Format', 'breadcrumb_navxt'), 'Sauthor_name', array("display_name", "nickname", "first_name", "last_name"), false, __('display_name uses the name specified in "Display name publicly as" under the user profile the others correspond to options in the user profile.', 'breadcrumb_navxt'));
						$this->input_text(__('Date Template', 'breadcrumb_navxt'), 'Hdate_template', '64', false, __('The template for date breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Date Template (Unlinked)', 'breadcrumb_navxt'), 'Hdate_template_no_anchor', '64', false, __('The template for date breadcrumbs, used only when the breadcrumb is not linked.', 'breadcrumb_navxt'));
						$this->input_text(__('Search Template', 'breadcrumb_navxt'), 'Hsearch_template', '64', false, __('The anchor template for search breadcrumbs, used only when the search results span several pages.', 'breadcrumb_navxt'));
						$this->input_text(__('Search Template (Unlinked)', 'breadcrumb_navxt'), 'Hsearch_template_no_anchor', '64', false, __('The anchor template for search breadcrumbs, used only when the search results span several pages and the breadcrumb is not linked.', 'breadcrumb_navxt'));
						$this->input_text(__('404 Title', 'breadcrumb_navxt'), 'S404_title', '32');
						$this->input_text(__('404 Template', 'breadcrumb_navxt'), 'H404_template', '64', false, __('The template for 404 breadcrumbs.', 'breadcrumb_navxt'));
					?>
				</table>
			</fieldset>
			</div>
			<p class="submit"><input type="submit" class="button-primary" name="bcn_admin_options" value="<?php esc_attr_e('Save Changes') ?>" /></p>
		</form>
		<?php 
		//Need to add a separate menu thing for this
		$this->import_form(); ?>
		</div>
		<?php
	}
	function opts_update_prebk(&$opts)
	{
		//Add custom post types
		$this->find_posttypes($this->opt);
		//Add custom taxonomy types
		$this->find_taxonomies($this->opt);
	}
	/**
	 * Places settings into $opts array, if missing, for the registered post types
	 * 
	 * @param array $opts
	 */
	function find_posttypes(&$opts)
	{
		global $wp_post_types, $wp_taxonomies;
		//Loop through all of the post types in the array
		foreach($wp_post_types as $post_type)
		{
			//We only want custom post types
			if(!$post_type->_builtin)
			{
				//If the post type does not have settings in the options array yet, we need to load some defaults
				if(!array_key_exists('Hpost_' . $post_type->name . '_template', $opts) || !$post_type->hierarchical && !array_key_exists('Spost_' . $post_type->name . '_taxonomy_type', $opts))
				{
					//Add the necessary option array members
					$opts['Hpost_' . $post_type->name . '_template'] = __('<a title="Go to %title%." href="%link%">%htitle%</a>', 'breadcrumb_navxt');
					$opts['Hpost_' . $post_type->name . '_template_no_anchor'] = __('%htitle%', 'breadcrumb_navxt');
					//Do type dependent tasks
					if($post_type->hierarchical)
					{
						//Set post_root for hierarchical types
						$opts['apost_' . $post_type->name . '_root'] = get_option('page_on_front');
					}
					//If it is flat, we need a taxonomy selection
					else
					{
						//Set post_root for flat types
						$opts['apost_' . $post_type->name . '_root'] = get_option('page_for_posts');
						//Default to not displaying a taxonomy
						$opts['bpost_' . $post_type->name . '_taxonomy_display'] = false;
						//Loop through all of the possible taxonomies
						foreach($wp_taxonomies as $taxonomy)
						{
							//Activate the first taxonomy valid for this post type and exit the loop
							if($taxonomy->object_type == $post_type->name || in_array($post_type->name, $taxonomy->object_type))
							{
								$opts['bpost_' . $post_type->name . '_taxonomy_display'] = true;
								$opts['Spost_' . $post_type->name . '_taxonomy_type'] = $taxonomy->name;
								break;
							}
						}
						//If there are no valid taxonomies for this type, we default to not displaying taxonomies for this post type
						if(!isset($opts['Spost_' . $post_type->name . '_taxonomy_type']))
						{
							$opts['Spost_' . $post_type->name . '_taxonomy_type'] = 'date';
						}
					}
				}
			}
		}
	}
	/**
	 * Places settings into $opts array, if missing, for the registered taxonomies
	 * 
	 * @param $opts
	 */
	function find_taxonomies(&$opts)
	{
		global $wp_taxonomies;
		//We'll add our custom taxonomy stuff at this time
		foreach($wp_taxonomies as $taxonomy)
		{
			//We only want custom taxonomies
			if(!$taxonomy->_builtin)
			{
				//If the taxonomy does not have settings in the options array yet, we need to load some defaults
				if(!array_key_exists('H' . $taxonomy->name . '_template', $opts))
				{
					//Add the necessary option array members
					$opts['H' . $taxonomy->name . '_template'] = __(sprintf('<a title="Go to the %%title%% %s archives." href="%%link%%">%%htitle%%</a>', $taxonomy->labels->singular_name), 'breadcrumb_navxt');
					$opts['H' . $taxonomy->name . '_template_no_anchor'] = __(sprintf('%%htitle%%', $taxonomy->labels->singular_name), 'breadcrumb_navxt');
				}
			}
		}
	}
	/**
	 * Outputs the breadcrumb trail
	 * 
	 * @param  (bool)   $return Whether to return or echo the trail.
	 * @param  (bool)   $linked Whether to allow hyperlinks in the trail or not.
	 * @param  (bool)	$reverse Whether to reverse the output or not.
	 */
	function display($return = false, $linked = true, $reverse = false)
	{
		//Grab the current settings from the db
		$this->breadcrumb_trail->opt = wp_parse_args(get_option('bcn_options'), $this->breadcrumb_trail->opt);
		//Generate the breadcrumb trail
		$this->breadcrumb_trail->fill();
		return $this->breadcrumb_trail->display($return, $linked, $reverse);
	}
	/**
	 * Outputs the breadcrumb trail
	 * 
	 * @since  3.2.0
	 * @param  (bool)   $return Whether to return or echo the trail.
	 * @param  (bool)   $linked Whether to allow hyperlinks in the trail or not.
	 * @param  (bool)	$reverse Whether to reverse the output or not.
	 */
	function display_list($return = false, $linked = true, $reverse = false)
	{
		//Grab the current settings from the db
		$this->breadcrumb_trail->opt = wp_parse_args(get_option('bcn_options'), $this->breadcrumb_trail->opt);
		//Generate the breadcrumb trail
		$this->breadcrumb_trail->fill();
		return $this->breadcrumb_trail->display_list($return, $linked, $reverse);
	}
	/**
	 * Outputs the breadcrumb trail
	 * 
	 * @since  3.8.0
	 * @param bool $return Whether to return data or to echo it.
	 * @param bool $linked[optional] Whether to allow hyperlinks in the trail or not.
	 * @param string $tag[optional] The tag to use for the nesting
	 * @param string $mode[optional] Whether to follow the rdfa or Microdata format
	 */
	function display_nested($return = false, $linked = true, $tag = 'span', $mode = 'rdfa')
	{
		//Grab the current settings from the db
		$this->breadcrumb_trail->opt = wp_parse_args(get_option('bcn_options'), $this->breadcrumb_trail->opt);
		//Generate the breadcrumb trail
		$this->breadcrumb_trail->fill();
		return $this->breadcrumb_trail->display_nested($return, $linked, $tag, $mode);
	}
}
//Let's make an instance of our object takes care of everything
$bcn_admin = new bcn_admin;
/**
 * A wrapper for the internal function in the class
 * 
 * @param bool $return Whether to return or echo the trail. (optional)
 * @param bool $linked Whether to allow hyperlinks in the trail or not. (optional)
 * @param bool $reverse Whether to reverse the output or not. (optional)
 */
function bcn_display($return = false, $linked = true, $reverse = false)
{
	global $bcn_admin;
	if($bcn_admin !== null)
	{
		return $bcn_admin->display($return, $linked, $reverse);
	}
}
/**
 * A wrapper for the internal function in the class
 * 
 * @param  bool $return  Whether to return or echo the trail. (optional)
 * @param  bool $linked  Whether to allow hyperlinks in the trail or not. (optional)
 * @param  bool $reverse Whether to reverse the output or not. (optional)
 */
function bcn_display_list($return = false, $linked = true, $reverse = false)
{
	global $bcn_admin;
	if($bcn_admin !== null)
	{
		return $bcn_admin->display_list($return, $linked, $reverse);
	}
}