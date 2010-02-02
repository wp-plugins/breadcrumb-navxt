<?php
/*
Plugin Name: Breadcrumb NavXT
Plugin URI: http://mtekk.weblogs.us/code/breadcrumb-navxt/
Description: Adds a breadcrumb navigation showing the visitor&#39;s path to their current location. For details on how to use this plugin visit <a href="http://mtekk.weblogs.us/code/breadcrumb-navxt/">Breadcrumb NavXT</a>. 
Version: 3.4.90
Author: John Havlik
Author URI: http://mtekk.weblogs.us/
*/
/*  Copyright 2007-2010  John Havlik  (email : mtekkmonkey@gmail.com)

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
//Include the breadcrumb class
require_once(dirname(__FILE__) . '/breadcrumb_navxt_class.php');
//Include the WP 2.8+ widget class
require_once(dirname(__FILE__) . '/breadcrumb_navxt_widget.php');
//Include admin base class
if(!class_exists('mtekk_admin'))
{
	require_once(dirname(__FILE__) . '/mtekk_admin_class.php');
}
//Include the supplemental functions
require_once(dirname(__FILE__) . '/breadcrumb_navxt_api.php');
/**
 * The administrative interface class 
 * 
 */
class bcn_admin extends mtekk_admin
{
	/**
	 * local store for breadcrumb version
	 * 
	 * @var   string
	 */
	protected $version = '3.4.90';
	protected $full_name = 'Breadcrumb NavXT Settings';
	protected $short_name = 'Breadcrumb NavXT';
	protected $access_level = 'manage_options';
	protected $identifier = 'breadcrumb_navxt';
	protected $unique_prefix = 'bcn';
	protected $plugin_basename = 'breadcrumb-navxt/breadcrumb_navxt_admin.php';
	/**
	 * wether or not this administration page has contextual help
	 * 
	 * @var bool
	 */
	protected $_has_contextual_help = false;	
	
	/**
	 * local store for the breadcrumb object
	 * 
	 * @see   bcn_admin()
	 * @var   bcn_breadcrumb
	 */
	public $breadcrumb_trail;
	/**
	 * bcn_admin
	 * 
	 * Administrative interface class default constructor
	 */
	function bcn_admin()
	{
		//We'll let it fail fataly if the class isn't there as we depend on it
		$this->breadcrumb_trail = new bcn_breadcrumb_trail;
		$this->opt = $this->breadcrumb_trail->opt;
		//We set the plugin basename here, could manually set it, but this is for demonstration purposes
		//$this->plugin_base = plugin_basename(__FILE__);
		//We're going to make sure we load the parent's constructor
		parent::__construct();
	}
	/**
	 * admin initialisation callback function
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
		//Add javascript enqeueing callback
		add_action('wp_print_scripts', array($this, 'javascript'));
	}
	/**
	 * security
	 * 
	 * Makes sure the current user can manage options to proceed
	 */
	function security()
	{
		//If the user can not manage options we will die on them
		if(!current_user_can($this->access_level))
		{
			_e('Insufficient privileges to proceed.', 'breadcrumb_navxt');
			die();
		}
	}
	/**
	 * install
	 * 
	 * This sets up and upgrades the database settings, runs on every activation
	 */
	function install()
	{
		//Call our little security function
		$this->security();
		//Initilize the options
		$this->breadcrumb_trail = new bcn_breadcrumb_trail;
		//Reduce db queries by saving this
		$db_version = $this->get_option('bcn_version');
		//If our version is not the same as in the db, time to update
		if($db_version !== $this->version)
		{
			//Split up the db version into it's components
			list($major, $minor, $release) = explode('.', $db_version);
			//For upgrading from 2.x.x
			if($major == 2)
			{
				//Delete old options
				$delete_options = array
				(
					'bcn_preserve', 'bcn_static_frontpage', 'bcn_url_blog', 
					'bcn_home_display', 'bcn_home_link', 'bcn_title_home', 
					'bcn_title_blog', 'bcn_separator', 'bcn_search_prefix', 
					'bcn_search_suffix', 'bcn_author_prefix', 'bcn_author_suffix', 
					'bcn_author_display', 'bcn_singleblogpost_prefix', 
					'bcn_singleblogpost_suffix', 'bcn_page_prefix', 'bcn_page_suffix', 
					'bcn_urltitle_prefix', 'bcn_urltitle_suffix', 
					'bcn_archive_category_prefix', 'bcn_archive_category_suffix', 
					'bcn_archive_date_prefix', 'bcn_archive_date_suffix', 
					'bcn_archive_date_format', 'bcn_attachment_prefix', 
					'bcn_attachment_suffix', 'bcn_archive_tag_prefix', 
					'bcn_archive_tag_suffix', 'bcn_title_404', 'bcn_link_current_item', 
					'bcn_current_item_urltitle', 'bcn_current_item_style_prefix', 
					'bcn_current_item_style_suffix', 'bcn_posttitle_maxlen', 
					'bcn_paged_display', 'bcn_paged_prefix', 'bcn_paged_suffix', 
					'bcn_singleblogpost_taxonomy', 'bcn_singleblogpost_taxonomy_display', 
					'bcn_singleblogpost_category_prefix', 'bcn_singleblogpost_category_suffix', 
					'bcn_singleblogpost_tag_prefix', 'bcn_singleblogpost_tag_suffix'
				);
				foreach ($delete_options as $option)
				{
					$this->delete_option($option);	
				}
			}
			else if($major == 3 && $minor == 0)
			{
				//Update our internal settings
				$this->breadcrumb_trail->opt = $this->get_option('bcn_options');
				$this->breadcrumb_trail->opt['search_anchor'] = __('<a title="Go to the first page of search results for %title%." href="%link%">','breadcrumb_navxt');
			}
			else if($major == 3 && $minor < 3)
			{
				//Update our internal settings
				$this->breadcrumb_trail->opt = $this->get_option('bcn_options');
				$this->breadcrumb_trail->opt['blog_display'] = true;
			}
			else if($major == 3 && $minor < 4)
			{
				//Inline upgrade of the tag setting
				if($this->breadcrumb_trail->opt['post_taxonomy_type'] === 'tag')
				{
					$this->breadcrumb_trail->opt['post_taxonomy_type'] = 'post_tag';
				}
				//Fix our tag settings
				$this->breadcrumb_trail->opt['archive_post_tag_prefix'] = $this->breadcrumb_trail->opt['archive_tag_prefix'];
				$this->breadcrumb_trail->opt['archive_post_tag_suffix'] = $this->breadcrumb_trail->opt['archive_tag_suffix'];
				$this->breadcrumb_trail->opt['post_tag_prefix'] = $this->breadcrumb_trail->opt['tag_prefix'];
				$this->breadcrumb_trail->opt['post_tag_suffix'] = $this->breadcrumb_trail->opt['tag_suffix'];
				$this->breadcrumb_trail->opt['post_tag_anchor'] = $this->breadcrumb_trail->opt['tag_anchor'];
			}
			//Always have to update the version
			$this->update_option('bcn_version', $this->version);
			//Store the options
			$this->add_option('bcn_options', $this->breadcrumb_trail->opt);
		}
		//Check if we have valid anchors
		if($temp = $this->get_option('bcn_options'))
		{
			//Missing the blog anchor is a bug from 3.0.0/3.0.1 so we soft error that one
			if(strlen($temp['blog_anchor']) == 0)
			{
				$temp['blog_anchor'] = $this->breadcrumb_trail->opt['blog_anchor'];
				$this->update_option('bcn_options', $temp);
			}
			else if(strlen($temp['home_anchor']) == 0 || 
				strlen($temp['blog_anchor']) == 0 || 
				strlen($temp['page_anchor']) == 0 || 
				strlen($temp['post_anchor']) == 0 || 
				strlen($temp['tag_anchor']) == 0 ||
				strlen($temp['date_anchor']) == 0 ||
				strlen($temp['category_anchor']) == 0)
			{
				$this->delete_option('bcn_options');
				$this->add_option('bcn_options', $this->breadcrumb_trail->opt);
			}
		}
	}
	/**
	 * ops_update
	 * 
	 * Updates the database settings from the webform
	 */
	function opts_update()
	{
		global $wp_taxonomies;
		$this->security();
		//Do a nonce check, prevent malicious link/form problems
		check_admin_referer('bcn_options-options');
		
		//Grab the options from the from post
		//Home page settings
		$this->breadcrumb_trail->opt['home_display'] = bcn_get('home_display', false);
		$this->breadcrumb_trail->opt['blog_display'] = bcn_get('blog_display', false);
		$this->breadcrumb_trail->opt['home_title'] = bcn_get('home_title');
		$this->breadcrumb_trail->opt['home_anchor'] = bcn_get('home_anchor', $this->breadcrumb_trail->opt['home_anchor']);
		$this->breadcrumb_trail->opt['blog_anchor'] = bcn_get('blog_anchor', $this->breadcrumb_trail->opt['blog_anchor']);
		$this->breadcrumb_trail->opt['home_prefix'] = bcn_get('home_prefix');
		$this->breadcrumb_trail->opt['home_suffix'] = bcn_get('home_suffix');
		$this->breadcrumb_trail->opt['separator'] = bcn_get('separator');
		$this->breadcrumb_trail->opt['max_title_length'] = (int) bcn_get('max_title_length');
		//Current item settings
		$this->breadcrumb_trail->opt['current_item_linked'] = bcn_get('current_item_linked', false);
		$this->breadcrumb_trail->opt['current_item_anchor'] = bcn_get('current_item_anchor', $this->breadcrumb_trail->opt['current_item_anchor']);
		$this->breadcrumb_trail->opt['current_item_prefix'] = bcn_get('current_item_prefix');
		$this->breadcrumb_trail->opt['current_item_suffix'] = bcn_get('current_item_suffix');
		//Paged settings
		$this->breadcrumb_trail->opt['paged_prefix'] = bcn_get('paged_prefix');
		$this->breadcrumb_trail->opt['paged_suffix'] = bcn_get('paged_suffix');
		$this->breadcrumb_trail->opt['paged_display'] = bcn_get('paged_display', false);
		//Page settings
		$this->breadcrumb_trail->opt['page_prefix'] = bcn_get('page_prefix');
		$this->breadcrumb_trail->opt['page_suffix'] = bcn_get('page_suffix');
		$this->breadcrumb_trail->opt['page_anchor'] = bcn_get('page_anchor', $this->breadcrumb_trail->opt['page_anchor']);
		//Post related options
		$this->breadcrumb_trail->opt['post_prefix'] = bcn_get('post_prefix');
		$this->breadcrumb_trail->opt['post_suffix'] = bcn_get('post_suffix');
		$this->breadcrumb_trail->opt['post_anchor'] = bcn_get('post_anchor', $this->breadcrumb_trail->opt['post_anchor']);
		$this->breadcrumb_trail->opt['post_taxonomy_display'] = bcn_get('post_taxonomy_display', false);
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
		$this->breadcrumb_trail->opt['search_anchor'] = bcn_get('search_anchor', $this->breadcrumb_trail->opt['search_anchor']);
		//Tag settings
		$this->breadcrumb_trail->opt['post_tag_prefix'] = bcn_get('post_tag_prefix');
		$this->breadcrumb_trail->opt['post_tag_suffix'] = bcn_get('post_tag_suffix');
		$this->breadcrumb_trail->opt['post_tag_anchor'] = bcn_get('post_tag_anchor', $this->breadcrumb_trail->opt['post_tag_anchor']);
		//Author page settings
		$this->breadcrumb_trail->opt['author_prefix'] = bcn_get('author_prefix');
		$this->breadcrumb_trail->opt['author_suffix'] = bcn_get('author_suffix');
		$this->breadcrumb_trail->opt['author_display'] = bcn_get('author_display');
		//Category settings
		$this->breadcrumb_trail->opt['category_prefix'] = bcn_get('category_prefix');
		$this->breadcrumb_trail->opt['category_suffix'] = bcn_get('category_suffix');
		$this->breadcrumb_trail->opt['category_anchor'] = bcn_get('category_anchor', $this->breadcrumb_trail->opt['category_anchor']);
		//Archive settings
		$this->breadcrumb_trail->opt['archive_category_prefix'] = bcn_get('archive_category_prefix');
		$this->breadcrumb_trail->opt['archive_category_suffix'] = bcn_get('archive_category_suffix');
		$this->breadcrumb_trail->opt['archive_post_tag_prefix'] = bcn_get('archive_post_tag_prefix');
		$this->breadcrumb_trail->opt['archive_post_tag_suffix'] = bcn_get('archive_post_tag_suffix');
		//Archive by date settings
		$this->breadcrumb_trail->opt['date_anchor'] = bcn_get('date_anchor', $this->breadcrumb_trail->opt['date_anchor']);
		$this->breadcrumb_trail->opt['archive_date_prefix'] = bcn_get('archive_date_prefix');
		$this->breadcrumb_trail->opt['archive_date_suffix'] = bcn_get('archive_date_suffix');
		//Loop through all of the taxonomies in the array
		foreach($wp_taxonomies as $taxonomy)
		{
			//We only want custom taxonomies
			if($taxonomy->object_type == 'post' && ($taxonomy->name != 'post_tag' && $taxonomy->name != 'category'))
			{
				$this->breadcrumb_trail->opt[$taxonomy->name . '_prefix'] = bcn_get($taxonomy->name . '_prefix');
				$this->breadcrumb_trail->opt[$taxonomy->name . '_suffix'] = bcn_get($taxonomy->name . '_suffix');
				$this->breadcrumb_trail->opt[$taxonomy->name . '_anchor'] = bcn_get($taxonomy->name . '_anchor', $this->breadcrumb_trail->opt['post_tag_anchor']);
				$this->breadcrumb_trail->opt['archive_' . $taxonomy->name . '_prefix'] = bcn_get('archive_' . $taxonomy->name . '_prefix');
				$this->breadcrumb_trail->opt['archive_' . $taxonomy->name . '_suffix'] = bcn_get('archive_' . $taxonomy->name . '_suffix');
			}
		}
		//Commit the option changes
		$this->update_option('bcn_options', $this->breadcrumb_trail->opt);
		$this->message['updated fade'][] = __('Settings successfully saved.', $this->identifier);
		add_action('admin_notices', array($this, 'message'));
	}
	/**
	 * javascript
	 *
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
	 * get help text
	 * 
	 * @return string
	 */
	protected function _get_help_text()
	{
		return sprintf(__('Tips for the settings are located below select options. Please refer to the %sdocumentation%s for more information.', 'breadcrumb_navxt'), 
			'<a title="' . __('Go to the Breadcrumb NavXT online documentation', 'breadcrumb_navxt') . '" href="http://mtekk.weblogs.us/code/breadcrumb-navxt/breadcrumb-navxt-doc/">', '</a>');
	}
	/**
	 * admin_head
	 *
	 * Adds in the JavaScript and CSS for the tabs in the adminsitrative 
	 * interface
	 * 
	 */
	function admin_head()
	{	
		// print style and script element (should go into head element) 
		?>
<style type="text/css">
	/**
	 * Tabbed Admin Page (CSS)
	 * 
	 * @see Breadcrumb NavXT (Wordpress Plugin)
	 * @author Tom Klingenberg 
	 * @colordef #c6d9e9 light-blue (older tabs border color, obsolete)
	 * @colordef #dfdfdf light-grey (tabs border color)
	 * @colordef #f9f9f9 very-light-grey (admin standard background color)
	 * @colordef #fff    white (active tab background color)
	 */
#hasadmintabs ul.ui-tabs-nav {border-bottom:1px solid #dfdfdf; font-size:12px; height:29px; list-style-image:none; list-style-position:outside; list-style-type:none; margin:13px 0 0; overflow:visible; padding:0 0 0 8px;}
#hasadmintabs ul.ui-tabs-nav li {display:block; float:left; line-height:200%; list-style-image:none; list-style-position:outside; list-style-type:none; margin:0; padding:0; position:relative; text-align:center; white-space:nowrap; width:auto;}
#hasadmintabs ul.ui-tabs-nav li a {background:transparent none no-repeat scroll 0 50%; border-bottom:1px solid #dfdfdf; display:block; float:left; line-height:28px; padding:1px 13px 0; position:relative; text-decoration:none;}
#hasadmintabs ul.ui-tabs-nav li.ui-tabs-selected a{-moz-border-radius-topleft:4px; -moz-border-radius-topright:4px;border:1px solid #dfdfdf; border-bottom-color:#f9f9f9; color:#333333; font-weight:normal; padding:0 12px;}
#hasadmintabs ul.ui-tabs-nav a:focus, a:active {outline-color:-moz-use-text-color; outline-style:none; outline-width:medium;}
#screen-options-wrap p.submit {margin:0; padding:0;}
</style>
<script type="text/javascript">
/* <![CDATA[ */
	/**
	 * Breadcrumb NavXT Admin Page (javascript/jQuery)
	 *
	 * unobtrusive approach to add tabbed forms into
	 * the wordpress admin panel and various other 
	 * stuff that needs javascript with the Admin Panel.
	 *
	 * @see Breadcrumb NavXT (Wordpress Plugin)
	 * @author Tom Klingenberg
	 * @author John Havlik
	 * @uses jQuery
	 * @uses jQuery.ui.tabs
	 */		
	jQuery(function()
	{
		bcn_context_init();
		bcn_tabulator_init();		
	 });
	function bcn_confirm(type)
	{
		if(type == 'reset'){
			var answer = confirm("<?php _e('All of your current Breadcrumb NavXT settings will be overwritten with the default values. Are you sure you want to continue?', 'breadcrumb_navxt'); ?>");
		}
		else{
			var answer = confirm("<?php _e('All of your current Breadcrumb NavXT settings will be overwritten with the imported values. Are you sure you want to continue?', 'breadcrumb_navxt'); ?>");
		}
		if(answer)
			return true;
		else
			return false;
	}
	/**
	 * Tabulator Bootup
	 */
	function bcn_tabulator_init(){
		/* if this is not the breadcrumb admin page, quit */
		if (!jQuery("#hasadmintabs").length) return;		
		/* init markup for tabs */
		jQuery('#hasadmintabs').prepend("<ul><\/ul>");
		jQuery('#hasadmintabs > fieldset').each(function(i){
		    id      = jQuery(this).attr('id');
		    caption = jQuery(this).find('h3').text();
		    jQuery('#hasadmintabs > ul').append('<li><a href="#'+id+'"><span>'+caption+"<\/span><\/a><\/li>");
		    jQuery(this).find('h3').hide();					    
	    });	
		/* init the tabs plugin */
		var jquiver = undefined == jQuery.ui ? [0,0,0] : undefined == jQuery.ui.version ? [0,1,0] : jQuery.ui.version.split('.');
		switch(true){
			// tabs plugin has been fixed to work on the parent element again.
			case jquiver[0] >= 1 && jquiver[1] >= 7:
				jQuery("#hasadmintabs").tabs();
				break;
			// tabs plugin has bug and needs to work on ul directly.
			default:
				jQuery("#hasadmintabs > ul").tabs(); 
		}
		/* handler for opening the last tab after submit (compability version) */
		jQuery('#hasadmintabs ul a').click(function(i){
			var form   = jQuery('#bcn_admin_options');
			var action = form.attr("action").split('#', 1) + jQuery(this).attr('href');
			// an older bug pops up with some jQuery version(s), which makes it
			// necessary to set the form's action attribute by standard javascript 
			// node access:						
			form.get(0).setAttribute("action", action);
		});
	}
	/**
	 * context screen options for import/export
	 */
	 function bcn_context_init(){
		if (!jQuery("#bcn_import_export_relocate").length) return;
		var jqver = undefined == jQuery.fn.jquery ? [0,0,0] : jQuery.fn.jquery.split('.');
		jQuery('#screen-meta').prepend(
				'<div id="screen-options-wrap" class="hidden"></div>'
		);
		jQuery('#screen-meta-links').append(
				'<div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle">' +
				'<a class="show-settings" id="show-settings-link" href="#screen-options"><?php printf('%s/%s/%s', __('Import', 'breadcrumb_navxt'), __('Export', 'breadcrumb_navxt'), __('Reset', 'breadcrumb_navxt')); ?></a>' + 
				'</div>'
		);
		// jQuery Version below 1.3 (common for WP 2.7) needs some other style-classes
		// and jQuery events
		if (jqver[0] <= 1 && jqver[1] < 3){
			// hide-if-no-js for WP 2.8, not for WP 2.7
			jQuery('#screen-options-link-wrap').removeClass('hide-if-no-js');
			// screen settings tab (WP 2.7 legacy)
			jQuery('#show-settings-link').click(function () {
				if ( ! jQuery('#screen-options-wrap').hasClass('screen-options-open') ) {
					jQuery('#contextual-help-link-wrap').addClass('invisible');
				}
				jQuery('#screen-options-wrap').slideToggle('fast', function(){
					if ( jQuery(this).hasClass('screen-options-open') ) {
						jQuery('#show-settings-link').css({'backgroundImage':'url("images/screen-options-right.gif")'});
						jQuery('#contextual-help-link-wrap').removeClass('invisible');
						jQuery(this).removeClass('screen-options-open');
					} else {
						jQuery('#show-settings-link').css({'backgroundImage':'url("images/screen-options-right-up.gif")'});
						jQuery(this).addClass('screen-options-open');
					}
				});
				return false;
			});			
		}
		var code = jQuery('#bcn_import_export_relocate').html();
		jQuery('#bcn_import_export_relocate').html('');
		code = code.replace(/h3>/gi, 'h5>');		
		jQuery('#screen-options-wrap').prepend(code);		
	 }
/* ]]> */
</script>
<?php
	} //function admin_head()

	/**
	 * admin_page
	 * 
	 * The administrative page for Breadcrumb NavXT
	 * 
	 */
	function admin_page()
	{
		global $wp_taxonomies;
		$this->security();
		//Update our internal options array, use form safe function
		$this->breadcrumb_trail->opt = $this->get_option('bcn_options', true);
		?>
		<div class="wrap"><h2><?php _e('Breadcrumb NavXT Settings', 'breadcrumb_navxt'); ?></h2>		
		<p<?php if ($this->_has_contextual_help): ?> class="hide-if-js"<?php endif; ?>><?php 
			print $this->_get_help_text();			 
		?></p>
		<form action="options-general.php?page=breadcrumb_navxt" method="post" id="bcn_admin-options">
			<?php
				settings_fields('bcn_options'); 
			?>
			<div id="hasadmintabs">
			<fieldset id="general" class="bcn_options">
				<h3><?php _e('General', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Breadcrumb Separator', 'breadcrumb_navxt'), 'separator', '32', false, __('Placed in between each breadcrumb.', 'breadcrumb_navxt'));
						$this->input_text(__('Breadcrumb Max Title Length', 'breadcrumb_navxt'), 'max_title_length', '10');
					?>
					<tr valign="top">
						<th scope="row">
							<?php _e('Home Breadcrumb', 'breadcrumb_navxt'); ?>						
						</th>
						<td>
							<label>
								<input name="home_display" type="checkbox" id="home_display" value="true" <?php checked(true, $this->breadcrumb_trail->opt['home_display']); ?> />
								<?php _e('Place the home breadcrumb in the trail.', 'breadcrumb_navxt'); ?>				
							</label><br />
							<ul>
								<li>
									<label for="home_title">
										<?php _e('Home Title: ','breadcrumb_navxt');?>
										<input type="text" name="home_title" id="home_title" value="<?php echo $this->breadcrumb_trail->opt['home_title']; ?>" size="20" />
									</label>
								</li>
							</ul>							
						</td>
					</tr>
					<?php
						$this->input_check(__('Blog Breadcrumb', 'breadcrumb_navxt'), 'blog_display', __('Place the blog breadcrumb in the trail.', 'breadcrumb_navxt'), ($this->get_option('show_on_front') !== "page"));
						$this->input_text(__('Home Prefix', 'breadcrumb_navxt'), 'home_prefix', '32');
						$this->input_text(__('Home Suffix', 'breadcrumb_navxt'), 'home_suffix', '32');
						$this->input_text(__('Home Anchor', 'breadcrumb_navxt'), 'home_anchor', '60', false, __('The anchor template for the home breadcrumb.', 'breadcrumb_navxt'));
						$this->input_text(__('Blog Anchor', 'breadcrumb_navxt'), 'blog_anchor', '60', ($this->get_option('show_on_front') !== "page"), __('The anchor template for the blog breadcrumb, used only in static front page environments.', 'breadcrumb_navxt'));
					?>
				</table>
			</fieldset>
			<fieldset id="current" class="bcn_options">
				<h3><?php _e('Current Item', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_check(__('Link Current Item', 'breadcrumb_navxt'), 'current_item_linked', __('Yes'));
						$this->input_text(__('Current Item Prefix', 'breadcrumb_navxt'), 'current_item_prefix', '32', false, __('This is always placed in front of the last breadcrumb in the trail, before any other prefixes for that breadcrumb.', 'breadcrumb_navxt'));
						$this->input_text(__('Current Item Suffix', 'breadcrumb_navxt'), 'current_item_suffix', '32', false, __('This is always placed after the last breadcrumb in the trail, and after any other prefixes for that breadcrumb.', 'breadcrumb_navxt'));
						$this->input_text(__('Current Item Anchor', 'breadcrumb_navxt'), 'current_item_anchor', '60', false, __('The anchor template for current item breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_check(__('Paged Breadcrumb', 'breadcrumb_navxt'), 'paged_display', __('Include the paged breadcrumb in the breadcrumb trail.', 'breadcrumb_navxt'), false, __('Indicates that the user is on a page other than the first on paginated posts/pages.', 'breadcrumb_navxt'));
						$this->input_text(__('Paged Prefix', 'breadcrumb_navxt'), 'paged_prefix', '32');
						$this->input_text(__('Paged Suffix', 'breadcrumb_navxt'), 'paged_suffix', '32');
					?>
				</table>
			</fieldset>
			<fieldset id="single" class="bcn_options">
				<h3><?php _e('Posts &amp; Pages', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Post Prefix', 'breadcrumb_navxt'), 'post_prefix', '32');
						$this->input_text(__('Post Suffix', 'breadcrumb_navxt'), 'post_suffix', '32');
						$this->input_text(__('Post Anchor', 'breadcrumb_navxt'), 'post_anchor', '60', false, __('The anchor template for post breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_check(__('Post Taxonomy Display', 'breadcrumb_navxt'), 'post_taxonomy_display', __('Show the taxonomy leading to a post in the breadcrumb trail.', 'breadcrumb_navxt'));
					?>
					<tr valign="top">
						<th scope="row">
							<?php _e('Post Taxonomy', 'breadcrumb_navxt'); ?>
						</th>
						<td>
							<?php
								$this->input_radio('post_taxonomy_type', 'category', __('Categories'));
								$this->input_radio('post_taxonomy_type', 'date', __('Dates'));
								$this->input_radio('post_taxonomy_type', 'post_tag', __('Tags'));
								$this->input_radio('post_taxonomy_type', 'page', __('Pages'));
								//Loop through all of the taxonomies in the array
								foreach($wp_taxonomies as $taxonomy)
								{
									//We only want custom taxonomies
									if($taxonomy->object_type == 'post' && ($taxonomy->name != 'post_tag' && $taxonomy->name != 'category'))
									{
										$this->input_radio('post_taxonomy_type', $taxonomy->name, ucwords(__($taxonomy->label)));
									}
								}
							?>
							<span class="setting-description"><?php _e('The taxonomy which the breadcrumb trail will show.', 'breadcrumb_navxt'); ?></span>
						</td>
					</tr>
					<?php
						$this->input_text(__('Page Prefix', 'breadcrumb_navxt'), 'page_prefix', '32');
						$this->input_text(__('Page Suffix', 'breadcrumb_navxt'), 'page_suffix', '32');
						$this->input_text(__('Page Anchor', 'breadcrumb_navxt'), 'page_anchor', '60', false, __('The anchor template for page breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Attachment Prefix', 'breadcrumb_navxt'), 'attachment_prefix', '32');
						$this->input_text(__('Attachment Suffix', 'breadcrumb_navxt'), 'attachment_suffix', '32');
					?>
				</table>
			</fieldset>
			<fieldset id="category" class="bcn_options">
				<h3><?php _e('Categories', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Category Prefix', 'breadcrumb_navxt'), 'category_prefix', '32', false, __('Applied before the anchor on all category breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Category Suffix', 'breadcrumb_navxt'), 'category_suffix', '32', false, __('Applied after the anchor on all category breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Category Anchor', 'breadcrumb_navxt'), 'category_anchor', '60', false, __('The anchor template for category breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Archive by Category Prefix', 'breadcrumb_navxt'), 'archive_category_prefix', '32', false, __('Applied before the title of the current item breadcrumb on an archive by cateogry page.', 'breadcrumb_navxt'));
						$this->input_text(__('Archive by Category Suffix', 'breadcrumb_navxt'), 'archive_category_suffix', '32', false, __('Applied after the title of the current item breadcrumb on an archive by cateogry page.', 'breadcrumb_navxt'));
					?>
				</table>
			</fieldset>
			<fieldset id="post_tag" class="bcn_options">
				<h3><?php _e('Tags', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Tag Prefix', 'breadcrumb_navxt'), 'post_tag_prefix', '32', false, __('Applied before the anchor on all tag breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Tag Suffix', 'breadcrumb_navxt'), 'post_tag_suffix', '32', false, __('Applied after the anchor on all tag breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Tag Anchor', 'breadcrumb_navxt'), 'post_tag_anchor', '60', false, __('The anchor template for tag breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Archive by Tag Prefix', 'breadcrumb_navxt'), 'archive_post_tag_prefix', '32', false, __('Applied before the title of the current item breadcrumb on an archive by tag page.', 'breadcrumb_navxt'));
						$this->input_text(__('Archive by Tag Suffix', 'breadcrumb_navxt'), 'archive_post_tag_suffix', '32', false, __('Applied after the title of the current item breadcrumb on an archive by tag page.', 'breadcrumb_navxt'));
					?>
				</table>
			</fieldset>
			<?php 
			//Loop through all of the taxonomies in the array
			foreach($wp_taxonomies as $taxonomy)
			{
				//We only want custom taxonomies
				if($taxonomy->object_type == 'post' && ($taxonomy->name != 'post_tag' && $taxonomy->name != 'category'))
				{
				?>
			<fieldset id="<?php echo $taxonomy->name; ?>" class="bcn_options">
				<h3><?php echo ucwords(__($taxonomy->label)); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(sprintf(__('%s Prefix', 'breadcrumb_navxt'), ucwords(__($taxonomy->label))), $taxonomy->name . '_prefix', '32', false, sprintf(__('Applied before the anchor on all %s breadcrumbs.', 'breadcrumb_navxt'), strtolower(__($taxonomy->label))));
						$this->input_text(sprintf(__('%s Suffix', 'breadcrumb_navxt'), ucwords(__($taxonomy->label))), $taxonomy->name . '_suffix', '32', false, sprintf(__('Applied after the anchor on all %s breadcrumbs.', 'breadcrumb_navxt'), strtolower(__($taxonomy->label))));
						$this->input_text(sprintf(__('%s Anchor', 'breadcrumb_navxt'), ucwords(__($taxonomy->label))), $taxonomy->name . '_anchor', '60', false, sprintf(__('The anchor template for %s breadcrumbs.', 'breadcrumb_navxt'), strtolower(__($taxonomy->label))));
						$this->input_text(sprintf(__('Archive by %s Prefix', 'breadcrumb_navxt'), ucwords(__($taxonomy->label))), 'archive_' . $taxonomy->name . '_prefix', '32', false, sprintf(__('Applied before the title of the current item breadcrumb on an archive by %s page.', 'breadcrumb_navxt'), strtolower(__($taxonomy->label))));
						$this->input_text(sprintf(__('Archive by %s Suffix', 'breadcrumb_navxt'), ucwords(__($taxonomy->label))), 'archive_' . $taxonomy->name . '_suffix', '32', false, sprintf(__('Applied after the title of the current item breadcrumb on an archive by %s page.', 'breadcrumb_navxt'), strtolower(__($taxonomy->label))));
					?>
				</table>
			</fieldset>
				<?php
				}
			}
			?>
			<fieldset id="date" class="bcn_options">
				<h3><?php _e('Date Archives', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Date Anchor', 'breadcrumb_navxt'), 'date_anchor', '60', false, __('The anchor template for date breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Archive by Date Prefix', 'breadcrumb_navxt'), 'archive_date_prefix', '32', false, __('Applied before the anchor on all date breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_text(__('Archive by Date Suffix', 'breadcrumb_navxt'), 'archive_date_suffix', '32', false, __('Applied after the anchor on all date breadcrumbs.', 'breadcrumb_navxt'));
					?>
				</table>
			</fieldset>
			<fieldset id="miscellaneous" class="bcn_options">
				<h3><?php _e('Miscellaneous', 'breadcrumb_navxt'); ?></h3>
				<table class="form-table">
					<?php
						$this->input_text(__('Author Prefix', 'breadcrumb_navxt'), 'author_prefix', '32');
						$this->input_text(__('Author Suffix', 'breadcrumb_navxt'), 'author_suffix', '32');
						$this->input_text(__('Archive by Date Suffix', 'breadcrumb_navxt'), 'archive_date_suffix', '32', false, __('Applied after the anchor on all date breadcrumbs.', 'breadcrumb_navxt'));
						$this->input_select(__('Author Display Format', 'breadcrumb_navxt'), 'author_display', array("display_name", "nickname", "first_name", "last_name"), false, __('display_name uses the name specified in "Display name publicly as" under the user profile the others correspond to options in the user profile.', 'breadcrumb_navxt'));
						$this->input_text(__('Search Prefix', 'breadcrumb_navxt'), 'search_prefix', '32');
						$this->input_text(__('Search Suffix', 'breadcrumb_navxt'), 'search_suffix', '32');
						$this->input_text(__('Search Anchor', 'breadcrumb_navxt'), 'search_anchor', '60', false, __('The anchor template for search breadcrumbs, used only when the search results span several pages.', 'breadcrumb_navxt'));
						$this->input_text(__('404 Title', 'breadcrumb_navxt'), '404_title', '32');
						$this->input_text(__('404 Prefix', 'breadcrumb_navxt'), '404_prefix', '32');
						$this->input_text(__('404 Suffix', 'breadcrumb_navxt'), '404_suffix', '32');
					?>
				</table>
			</fieldset>
			</div>
			<p class="submit"><input type="submit" class="button-primary" name="bcn_admin_options" value="<?php esc_attr_e('Save Changes') ?>" /></p>
		</form>
		<?php $this->import_form(); ?>
		</div>
		<?php
	}
	/**
	 * add_option
	 *
	 * This inserts the value into the option name, WPMU safe
	 *
	 * @param (string) key name where to save the value in $value
	 * @param (mixed) value to insert into the options db
	 * @return (bool)
	 */
	function add_option($key, $value)
	{
		return add_option($key, $value);
	}
	/**
	 * delete_option
	 *
	 * This removes the option name, WPMU safe
	 *
	 * @param (string) key name of the option to remove
	 * @return (bool)
	 */
	function delete_option($key)
	{
		return delete_option($key);
	}
	/**
	 * update_option
	 *
	 * This updates the value into the option name, WPMU safe
	 *
	 * @param (string) key name where to save the value in $value
	 * @param (mixed) value to insert into the options db
	 * @return (bool)
	 */
	function update_option($key, $value)
	{
		return update_option($key, $value);
	}
	/**
	 * get_option
	 *
	 * This grabs the the data from the db it is WPMU safe and can place the data 
	 * in a HTML form safe manner.
	 *
	 * @param  (string) key name of the wordpress option to get
	 * @param  (bool)   safe output for HTML forms (default: false)
	 * @return (mixed)  value of option
	 */
	function get_option($key, $safe = false)
	{
		$db_data = get_option($key);
		if($safe)
		{
			//If we get an array, we should loop through all of its members
			if(is_array($db_data))
			{
				//Loop through all the members
				foreach($db_data as $key=>$item)
				{
					//We ignore anything but strings
					if(is_string($item))
					{
						$db_data[$key] = htmlentities($item, ENT_COMPAT, 'UTF-8');
					}
				}
			}
			else
			{
				$db_data = htmlentities($db_data, ENT_COMPAT, 'UTF-8');
			}
		}
		return $db_data;
	}
	/**
	 * display
	 * 
	 * Outputs the breadcrumb trail
	 * 
	 * @param  (bool)   $return Whether to return or echo the trail.
	 * @param  (bool)   $linked Whether to allow hyperlinks in the trail or not.
	 * @param  (bool)	$reverse Whether to reverse the output or not.
	 */
	function display($return = false, $linked = true, $reverse = false)
	{
		//Update our internal settings
		$this->breadcrumb_trail->opt = $this->get_option('bcn_options');
		//Generate the breadcrumb trail
		$this->breadcrumb_trail->fill();
		return $this->breadcrumb_trail->display($return, $linked, $reverse);
	}
	/**
	 * display_list
	 * 
	 * Outputs the breadcrumb trail
	 * 
	 * @since  3.2.0
	 * @param  (bool)   $return Whether to return or echo the trail.
	 * @param  (bool)   $linked Whether to allow hyperlinks in the trail or not.
	 * @param  (bool)	$reverse Whether to reverse the output or not.
	 */
	function display_list($return = false, $linked = true, $reverse = false)
	{
		//Update our internal settings
		$this->breadcrumb_trail->opt = $this->get_option('bcn_options');
		//Generate the breadcrumb trail
		$this->breadcrumb_trail->fill();
		return $this->breadcrumb_trail->display_list($return, $linked, $reverse);
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
	return $bcn_admin->display($return, $linked, $reverse);
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
	return $bcn_admin->display_list($return, $linked, $reverse);
}
