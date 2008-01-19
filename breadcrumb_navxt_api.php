<?php
//Administration input complex, replaces the broken WordPress one
//Based off of the suggestions and code of Tom Klingenberg
function bcn_get($varname)
{
	$val = $_POST[$varname];
	$val = stripslashes($val);
	//Keep out spaces please ;)
	if(isset($_POST['bcn_preserve_space']))
	{
		update_option('bcn_preserve', '1');
		$val = str_replace(" ", "&nbsp;", $val);
	}
	else
	{
		$val = htmlspecialchars($val);
	}
	return $val;
}
//WordPress localization stuff
function bcn_local()
{
	load_plugin_textdomain('breadcrumb-navxt', 'wp-content/plugins/breadcrumb-navxt');
}
?>