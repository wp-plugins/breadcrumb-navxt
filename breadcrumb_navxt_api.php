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
 * str2bool converts string of "true" to true and "false" to false
 * 
 * Probably could be moved to bcn_get_option, we'll see
 * 
 * @param string $input
 * @return bool
 */
//Safely add this in just incase someone else makes one with the same name
if(!function_exists('str2bool'))
{
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
	
	//Remove by faulty-wordpress-code added slashes
	$bcn_value = stripslashes($bcn_value);
	
	//Return unslashed value
	return $bcn_value;
}
?>