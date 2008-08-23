<?php
/**
 * Breadcrumb NavXT - API
 *
 * Functions inside the global Namespace used by Breadcrumb NavXT
 *
 * @author John Havlik
 * @author Tom Klingenberg
 *
 * 2008-03-06:
 * FIX: bcn_get						-	Reworked the conditions for when spaces
 * 										will be preserved.
 * 2008-02-07:
 * ADD: bcn_get_option_inputvalue	-	Escape Option Values to be used inside 
 *                                  	(X)HTML Element Attribute Values.
 * FIX: bcn_get                   	- 	fixed issue solved inside wordpress main 
 *                                  	codebase in 2007-09.
 *                                  	see http://trac.wordpress.org/ticket/4781
 */

/**
 * Get Option, get_option Replacement
 *
 * @param string optionname name of the wordpress option
 * @param bool foradmin wheter or not we are returning for the admin interface or for the class
 */
function bcn_get_option($optionname, $foradmin = true)
{
	//Retrieve the option value
	$bcn_value = get_option($optionname);
	if($foradmin)
	{
		//Remove &nbsp; so that it looks correct (string problem)
		return str_replace("&nbsp;", " ", $bcn_value);
		
	}
	else
	{
		//We use entity_decode as that's the inverse of what wpdb->escape() uses
		return html_entity_decode($bcn_value);
	}
}
/**
 * str2bool converts string of "true" to true and "false" to false
 * 
 * Probably could be moved to bcn_get_option, we'll see
 * 
 * @param string $input
 * @return bool
 */
function str2bool($input)
{
	if($input === "true")
	{
		return true;
	}
	else
	{
		return false;
	}
}
/**
 * Update Option, update_option Replacement
 * 
 * @param string $optionname
 * @param string $value
 * @see bcn_get_option
 */
function bcn_update_option($optionname, $value)
{
	$bcn_value = $value;
	//We want to make sure we handle html entities correctly first
	//$bcn_value = htmlspecialchars($bcn_value);
	//Preserving the front space if exists
	if(strpos($bcn_value, " ") === 0)
	{
		$bcn_value = "&nbsp;" . ltrim($bcn_value);
	}
	//Preserv the end space if exists
	$bcn_length = strlen($bcn_value) - 1;
	if($bcn_length > 0)
	{
		if(strpos($bcn_value, " ", $bcn_length - 1) === $bcn_length)
		{
			$bcn_value = rtrim($bcn_value) . "&nbsp;";
		}
	}
	return update_option($optionname, $bcn_value);
}

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
	$bcn_value = bcn_get_option($optionname);
	
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
 * Removes Faulty Adding Slashes and Preserves leading and trailing spaces
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
 * @param  (string) default deftaul value (optional)
 * @return (string) unescaped post data
 * @note   WP-Version 2.3.3, wp-settings.php #259ff
 */
function bcn_get($varname, $default = "")
{	
	//Import variable from post-request
	$bcn_value = $_POST[$varname];
	
	//If null kick out early (handle default values as well)
	if($bcn_value == "")
	{
		return $default;
	}
	
	//Only if we have a string should we check for spaces
	// >> this has been migrated to where it belongs to: bcn_update_option	
	
	//Remove by faulty-wordpress-code added slashes
	$bcn_value = stripslashes($bcn_value);
	
	//Return unslashed value
	return $bcn_value;
}
?>