<?php
/**
 * the one and only TABULATOR!
 * 
 * Add Tabs to the Breadcrumb NavXT Admin Page (and the rest of the admin pages in wp)
 *
 * @version   0.1.2
 * @author    Tom Klingenberg
 * @copyright by the author, some rights reserved
 * @see http://www.artnorm.de/this-morning-in-bleeding,105,2008-06.html
 * 
 * Plugin Name: Tabulator (Breadcrumb NavXT Extending) [PHP5]
 * Plugin URI: http://mtekk.weblogs.us/code/breadcrumb-navxt
 * Description: And Tabs should come over all your admin pages as WP 2.5 will let you rule the world of jQuery and beyond! Thou shall it be! 
 * Version: 3.0.0
 * Author: Tom Klingenberg
 * Author URI: http://www.artnorm.de/
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
	ul.ui-tabs-nav {background:#fff; border-bottom:1px solid #c6d9e9; font-size:12px; height:29px; margin:13px 0 0; padding:0; padding-left:8px; list-style:none;}	
	ul.ui-tabs-nav li {display:inline; line-height: 200%; list-style:none; margin: 0; padding:0; position:relative; top:1px; text-align:center; white-space:nowrap;}
	ul.ui-tabs-nav li a {background:transparent none no-repeat scroll 0%; border:1px transparent #fff; border-bottom:1px solid #c6d9e9; display:block; float:left; line-height:28px; padding:1px 13px 0; position:relative; text-decoration:none;}
	ul.ui-tabs-nav li.ui-tabs-selected a {-moz-border-radius-topleft:4px; -moz-border-radius-topright:4px; background:#fff; border:1px solid #c6d9e9; border-bottom-color:#fff; color:#d54e21; font-weight:normal; padding:0 12px;}
	ul.ui-tabs-nav a:focus, a:active {outline: none;}
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
		bcn_admin_gobal_tabs(); // comment out this like to disable tabs in admin					
	}
	
	/**
	 * inittialize tabs for admin panel pages (wordpress core)
	 *
	 * @todo add uniqueid somehow
	 */	
	function bcn_admin_gobal_tabs()
	{	
		/* if has already a special id quit the global try here */
		if (jQuery('#hasadmintabs').length > 0) return;
		
		jQuery('#wpbody .wrap form').each(function(f)
		{						
			var $formEle = jQuery(this).children();
		
			var $eleSets      = new Array();	
			var $eleSet       = new Array();
			var $eleSetIgnore = new Array();
								
			for (var i = 0; i < $formEle.size(); i++)
			{
				var curr = $formEle.get(i);
				var $curr = jQuery(curr);	
				// cut condition: h3 or stop				
				// stop: p.submit
				if ($curr.is('p.submit') || $curr.is('h3'))
				{
					if ($eleSet.length)
					{
						if ($eleSets.length == 0 && $eleSet.length == 1 && jQuery($eleSet).is('p'))	{
							$eleSetIgnore = $eleSetIgnore.concat($eleSet);
						} else {
							$eleSets.push($eleSet);
						}						
						$eleSet  = new Array();
					}
					if ($curr.is('p.submit')) break;
					$eleSet.push(curr);					
				} else {
					// handle ingnore bag - works only before the first set is created
					var pushto = $eleSet; 
					if ($eleSets.length == 0 && $curr.is("input[type='hidden']"))
					{
						pushto = $eleSetIgnore;					
					}															
					pushto.push(curr);
				}
			}		
			
			// if the page has only one set, quit
			if ($eleSets.length < 2) return;			
			
			// tabify
			formid = 'tabulator-tabs-form-' + f;
			jQuery($eleSetIgnore).filter(':last').after('<div id="' + formid + '"></div>');
			jQuery('#'+formid).prepend("<ul><\/ul>");
			var tabcounter = 0;			
			jQuery.each($eleSets, function() {
				tabcounter++;
				id      = formid + '-tab-' + tabcounter;
				hash3   = true;
				h3probe = jQuery(this).filter('h3').eq(0);			
				if (h3probe.is('h3')) {
					caption = h3probe.text();					
				} else {
					hash3   = false;
					caption = jQuery('#wpbody .wrap h2').eq(0).text();
				}
				if (caption == ''){
					caption = 'FALLBACK';
				} 	
				tabdiv = jQuery(this).wrapAll('<span id="'+id+'"></span>');
				jQuery('#'+formid+' > ul').append('<li><a href="#'+id+'"><span>'+caption+"<\/span><\/a><\/li>");
				if (hash3) h3probe.hide();
			});
			jQuery('#'+formid+' > ul').tabs({
				select: function(e, ui) {
				jQuery('#wpbody .wrap form').attr("action", (jQuery('#wpbody .wrap form').attr("action")).split('#', 1) + '#' + ui.panel.id);
				}
			});				
		});	
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