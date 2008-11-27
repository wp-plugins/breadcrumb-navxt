<?php
/**
 * the one and only TABULATOR!
 * 
 * Add Tabs to the Breadcrumb NavXT Admin Page (and the rest of the admin pages in wp)
 *
 * @version   0.1.4
 * @author    Tom Klingenberg
 * @copyright by the author, some rights reserved
 * @see http://www.artnorm.de/this-morning-in-bleeding,105,2008-06.html
 * 
 * Plugin Name: Tabulator (Breadcrumb NavXT Extending) [PHP5]
 * Plugin URI: http://mtekk.weblogs.us/code/breadcrumb-navxt
 * Description: And Tabs should come over all your admin pages as WP 2.5 will let you rule the world of jQuery and beyond! Thou shall it be! 
 * Version: 3.0.2
 * Author: Tom Klingenberg/John Havlik
 * Author URI: http://mtekk.weblogs.us
 */


/**
 * Initialize the Plugin
 */

Navxt_Plugin_Tabulator::init();


/**
 * Tabulator Plugin Class
 *
 * Contains all Plugin Functionality Functions
 * and the Initialisation
 * 
 * @hints php.wp.add_action.admin_head
 * @hints php.wp.enqueue_script
 * @hints js.jQuery
 * @hints js.ui.core
 * @hints js.ui.tabs
 * 
 */
class Navxt_Plugin_Tabulator
{
	public static function init()
	{
		$plugin = new self();
		add_action('admin_head', array($plugin, 'admin_head'));
		add_action('wp_print_scripts', array($plugin, 'javascript'));
	}
	public function javascript()
	{
		//If we are in the dashboard we may need this
		if(is_admin())
		{
			wp_enqueue_script('jquery-ui-tabs');
		}
	}
	/**
	 * admin_head hook function
	 * 
	 * Adds needed javascript and stylesheets to the head
	 * 
	 * @todo create external references in the plugin directory
	 *       for this.
	 *
	 */
	public function admin_head()
	{
?>
<style type="text/css">
	/**
	 * Tabbed Admin Page (CSS)
	 *
	 * unobtrusive approach to add tabbed forms into
	 * the wordpress admin panel
	 *
	 * @see Tabulator NavXT (Wordpress Plugin)
	 * @see Breadcrumb NavXT (Wordpress Plugin)
	 * @author Tom Klingenberg
	 * @cssdoc 1.0-pre
	 * @colordef #fff    white      (tab background) 
	 * @colordef #c6d9e9 grey-blue  (tab line)
	 * @colordef #d54e21 orange     (tab text of active tab)
	 * @colordef #d54e21 orange     (tab text of inactive tab hovered) external
	 * @colordef #2583ad dark-blue  (tab text of inactive tab) external	 	 
	 */
#hasadmintabs ul.ui-tabs-nav {background:#F9F9F9 none repeat scroll 0 0;border-bottom:1px solid #C6D9E9;font-size:12px;height:29px;list-style-image:none;list-style-position:outside;list-style-type:none;margin:13px 0 0;padding:0 0 0 8px;}
#hasadmintabs ul.ui-tabs-nav li {display:inline;line-height:200%;list-style-image:none;list-style-position:outside;list-style-type:none;margin:0;padding:0;position:relative;text-align:center;top:1px;white-space:nowrap;}
#hasadmintabs ul.ui-tabs-nav li a {background:transparent none no-repeat scroll 0 50%;border-bottom:1px solid #DFDFDF;display:block;float:left;line-height:28px;padding:1px 13px 0;position:relative;text-decoration:none;}
#hasadmintabs ul.ui-tabs-nav li.ui-tabs-selected a {-moz-border-radius-topleft:4px;-moz-border-radius-topright:4px;background:#F9F9F9 none repeat scroll 0 0;border-color:#DFDFDF #DFDFDF #F9F9F9;border-style:solid;border-width:1px;color:#333333;font-weight:normal;padding:0 12px;}
#hasadmintabs ul.ui-tabs-nav a:focus, a:active {outline-color:-moz-use-text-color;outline-style:none;outline-width:medium;}
#hasadmintabs fieldset {clear:both;}
</style>
<script type="text/javascript">
/* <![CDATA[ */
	/**
	 * Tabbed Admin Page (jQuery)
	 *
	 * unobtrusive approach to add tabbed forms into
	 * the wordpress admin panel
	 *
	 * @see Tabulator NavXT (Wordpress Plugin)
	 * @see Breadcrumb NavXT (Wordpress Plugin)
	 * @see http://www.artnorm.de/this-morning-in-bleeding,105,2008-06.html	 
	 * @author Tom Klingenberg
	 * @uses jQuery
	 * @uses ui.core
	 * @uses ui.tabs
	 */
	 
	jQuery(function() 
	{
		bcn_tabulator_init();		
	 });
	 
	/**
	 * Tabulator Bootup
	 */
	function bcn_tabulator_init()
	{
		bcn_admin_init_tabs();					
	}
	
	/**
	 * inittialize tabs for breadcrumb navxt admin panel
	 */	 
	function bcn_admin_init_tabs()
	{
		jQuery('#hasadmintabs').prepend("<ul><\/ul>");
		jQuery('#hasadmintabs > fieldset').each(function(i)
		{
		    id      = jQuery(this).attr('id');
		    caption = jQuery(this).find('h3').text();
		    jQuery('#hasadmintabs > ul').append('<li><a href="#'+id+'"><span>'+caption+"<\/span><\/a><\/li>");
		    jQuery(this).find('h3').hide();
	    });    
	    jQuery("#hasadmintabs > ul").tabs({
		    select: function(e, ui) {
			jQuery('#wpbody .wrap form').attr("action", (jQuery('#wpbody .wrap form').attr("action")).split('#', 1) + '#' + ui.panel.id);
			}
		});	
	}
/* ]]> */
</script>
<?php		
	} // admin_head() 
} // class
?>