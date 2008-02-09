<?php
/**
 * Breadcrumb NavXT - API
 *
 * Functions inside the global Namespace used by Breadcrumb NavXT
 *
 * @author John Havlik
 * @author Tom Klingenberg
 *
 *
 * 2008-02-07:
 * ADD: bcn_get_option_inputvalue - Escape Option Values to be used inside 
 *                                  (X)HTML Element Attribute Values.
 * FIX: bcn_get                   - fixed issue solved inside wordpress main 
 *                                  codebase in 2007-09.
 *                                  see http://trac.wordpress.org/ticket/4781
 */

/**
 * bcn_get_option_inputvalue
 *
 * Administration input complex, Escapes Option Values for the 
 * Output inside the XHTML Forms. The returned value is safe
 * for usage inside value="".
 *
 * @param  (string) optionname name of the wordpress option
 * @return (string) escaped option-value
 * @since  2008-02-07
 */
function bcn_get_option_inputvalue($optionname)
{
	//Retrieve the option value
	$bcn_value = get_option($optionname);
	//Remove &nbsp; so that it looks correct
	$bcn_value = str_replace("&nbsp;", " ", $bcn_value);
	//Convert any (x)HTML special charactors into a form that won't mess up the web form
	$bcn_value_secaped = htmlspecialchars($bcn_value);
	//Return the escaped value
	return $bcn_value_secaped;
}
/**
 * bcn_get
 *
 * Administration input complex, replaces the broken WordPress one
 * Based off of the suggestions and code of Tom Klingenberg
 *
 * Removes Faulty Adding Slashes
 *
 * Wordpress adds slashes to Request Variables by Default (before
 * removing those added by PHP) - This re-invents the wheel
 * and mimicks all the problems with magic_quotes_gpc.
 * The faulty adding slashes is done in wp-settings.php.
 * 
 * Therefore the plugin needs to unslash the slashed potential 
 * unslahsed-phpslashed data again. This is done in this function.
 *
 * @param  (string) varname name of the post variable
 * @return (string) unescaped post data
 * @note   WP-Version 2.3.3, wp-settings.php #259ff
 */
function bcn_get($varname)
{	
	//Import variable from post-request
	$bcn_value = $_POST[$varname];
	//If null kick out early (nothing to work with)
	if($bcn_value == "")
	{
		return "";
	}
	//Preserving the front space if exists
	if(strpos($bcn_value, " ") === 0)
	{
		$bcn_value = "&nbsp;" . ltrim($bcn_value);
	}
	//Preserv the end space if exists
	$bcn_length = strlen($bcn_value) - 1;
	if(strpos($bcn_value, " ", $bcn_length - 1) === $bcn_length)
	{
		$bcn_value = rtrim($bcn_value) . "&nbsp;";
	}
	//Remove by faulty-wordpress-code added slashes
	$bcn_value = stripslashes($bcn_value);
	//Return unslashed value
	return $bcn_value;
}
//WordPress localization stuff
function bcn_local()
{
	//Load breadcrumb-navxt translation
	load_plugin_textdomain($domain = 'breadcrumb_navxt', $path = PLUGINDIR . '/breadcrumb-navxt');
}
?>