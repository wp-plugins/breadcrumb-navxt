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
function bcn_files($dir)
{
	//Make sure a directory was passed
	if(is_dir($dir))
	{
		$contents = array();
		if($dirh = opendir($dir))
		{
			//Makesure the filename isread
			while(false !== ($file = readdir($dirh)))
			{
				//We don't want . or ..'
				if($file != '..' && $file != '.')
				{
					list($name, $type1, $type2) = explode('.', $file);
					if(in_array($type1, array("php","PHP")))
					{
						//Push into array
						$contents[] = $file;
					}
				}
			}
			//Be good and release some memory
			closedir($dirh);
		}
	}
	else
	{
		//Passed variable was not a directory
		$contents = array(0);
	}
	return $contents;
}
?>