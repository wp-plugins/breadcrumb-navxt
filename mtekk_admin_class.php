<?php
/*  
	Copyright 2007-2009  John Havlik  (email : mtekkmonkey@gmail.com)

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
class mtekk_admin
{
	protected $version;
	protected $full_name;
	protected $short_name;
	protected $plugin_basename;
	protected $access_level = 'manage_options';
	protected $identifier;
	protected $unique_prefix;
	protected $opt = array();
	protected $message;
	function __construct()
	{
		//Admin Init Hook
		add_action('admin_init', array($this, 'init'));
		//WordPress Admin interface hook
		add_action('admin_menu', array($this, 'add_page'));
		//Installation Script hook
		add_action('activate_' . $this->plugin_base, array($this, 'install'));
		//Initilizes l10n domain
		$this->local();
		//Register the WordPress 2.8 Widget
		add_action('widgets_init', create_function('', 'return register_widget("'. $this->unique_prefix . '_widget");'));
	}
	function admin_url()
	{
		return admin_url('options-general.php?page=' .$this->identifier);
	}
	function init()
	{
		//Admin Options update hook
		if(isset($_POST[$this->unique_prefix . '_admin_options']))
		{
			//Temporarily add update function on init if form has been submitted
			$this->opts_update;
		}	
		//Admin Options reset hook
		if(isset($_POST[$this->unique_prefix . '_admin_reset']))
		{
			//Run the reset function on init if reset form has been submitted
			$this->opts_reset;
		}
		//Admin Options export hook
		else if(isset($_POST[$this->unique_prefix . '_admin_export']))
		{
			//Run the export function on init if export form has been submitted
			$this->opts_export;
		}
		//Admin Options import hook
		else if(isset($_FILES[$this->unique_prefix . '_admin_import_file']) && !empty($_FILES[$this->unique_prefix . '_admin_import_file']['name']))
		{
			//Run the import function on init if import form has been submitted
			$this->opts_import;
		}
		//Add in the nice "settings" link to the plugins page
		add_filter('plugin_action_links', array($this, 'filter_plugin_actions'), 10, 2);
	}
	/**
	 * add_page
	 * 
	 * Adds the adminpage the menue and the nice little settings link
	 *
	 */
	function add_page()
	{
		// check capability of user to manage options (access control)
		if(current_user_can('manage_options'))
		{
			//Add the submenu page to "settings" menu
			$hookname = add_submenu_page('options-general.php', __($this->full_name, $this->identifier), $this->short_name, $this->access_level, $this->identifier, array($this, 'admin_page'));		
			//Register admin_head-$hookname callback
			add_action('admin_head-' . $hookname, array($this, 'admin_head'));			
			//Register Help Output
			add_action('contextual_help', array($this, 'contextual_help'), 10, 2);
		}
	}
	/**
	 * local
	 *
	 * Initilizes localization textdomain for translations (if applicable)
	 * 
	 * Will conditionally load the textdomain for translations. This is here for
	 * plugins that span multiple files and have localization in more than one file
	 * 
	 * @return void
	 */
	function local()
	{
		global $l10n;
		// the global and the check might become obsolete in
		// further wordpress versions
		// @see https://core.trac.wordpress.org/ticket/10527		
		if(!isset($l10n[$this->identifier]))
		{
			load_plugin_textdomain($this->identifier, false, $this->identifier . '/languages');
		}
	}
	/**
	 * filter_plugin_actions
	 * 
	 * Places in a link to the settings page in the plugins listing entry
	 * 
	 * @param  array  $links An array of links that are output in the listing
	 * @param  string $file The file that is currently in processing
	 * @return array  Array of links that are output in the listing.
	 */
	function filter_plugin_actions($links, $file)
	{
		//Make sure we are adding only for the current plugin
		if($file == $this->plugin_basename)
		{ 
			//Add our link to the end of the array to better integrate into the WP 2.8 plugins page
			$links[] = '<a href="' . $this->admin_url() . '">' . __('Settings') . '</a>';
		}
		return $links;
	}
	/**
	 * opts_update
	 * 
	 * Function prototype to prevent errors
	 */
	function opts_update()
	{
		
	}
	/**
	 * opts_export
	 * 
	 * Exports a XML options document
	 */
	function opts_export()
	{
		//Do a nonce check, prevent malicious link/form problems 
		check_admin_referer($this->unique_prefix . 'admin_import_export');
		//Update our internal settings
		$this->opt = get_option($this->unique_prefix . '_options');
		//Create a DOM document
		$dom = new DOMDocument('1.0', 'UTF-8');
		//Adds in newlines and tabs to the output
		$dom->formatOutput = true;
		//We're not using a DTD therefore we need to specify it as a standalone document
		$dom->xmlStandalone = true;
		//Add an element called options
		$node = $dom->createElement('options');
		$parnode = $dom->appendChild($node);
		//Add a child element named plugin
		$node = $dom->createElement('plugin');
		$plugnode = $parnode->appendChild($node);
		//Add some attributes that identify the plugin and version for the options export
		$plugnode->setAttribute('name', $this->short_name);
		$plugnode->setAttribute('version', $this->version);
		//Change our headder to text/xml for direct save
		header('Cache-Control: public');
		//The next two will cause good browsers to download instead of displaying the file
		header('Content-Description: File Transfer');
		header('Content-disposition: attachemnt; filename=' . $this->unique_prefix . '_settings.xml');
		header('Content-Type: text/xml');
		//Loop through the options array
		foreach($this->opt as $key=>$option)
		{
			//Add a option tag under the options tag, store the option value
			$node = $dom->createElement('option', htmlentities($option, ENT_COMPAT, 'UTF-8'));
			$newnode = $plugnode->appendChild($node);
			//Change the tag's name to that of the stored option
			$newnode->setAttribute('name', $key);
		}
		//Prepair the XML for output
		$output = $dom->saveXML();
		//Let the browser know how long the file is
		header('Content-Length: ' . strlen($output)); // binary length
		//Output the file
		echo $output;
		//Prevent WordPress from continuing on
		die();
	}
	/**
	 * opts_import
	 * 
	 * Imports a XML options document
	 */
	function opts_import()
	{
		//Our quick and dirty error supressor
		function error($errno, $errstr, $eerfile, $errline)
		{
			return true;
		}
		//Do a nonce check, prevent malicious link/form problems
		check_admin_referer($this->unique_prefix . 'admin_import_export');
		//Create a DOM document
		$dom = new DOMDocument('1.0', 'UTF-8');
		//We want to catch errors ourselves
		set_error_handler('error');
		//Load the user uploaded file, handle failure gracefully
		if($dom->load($_FILES[$this->unique_prefix . 'admin_import_file']['tmp_name']))
		{
			//Have to use an xpath query otherwise we run into problems
			$xpath = new DOMXPath($dom);  
			$option_sets = $xpath->query('plugin');
			//Loop through all of the xpath query results
			foreach($option_sets as $options)
			{
				//We only want to import options for only this plugin
				if($options->getAttribute('name') === $this->short_name)
				{
					//Do a quick version check
					list($plug_major, $plug_minor, $plug_release) = explode('.', $this->version);
					list($major, $minor, $release) = explode('.', $options->getAttribute('version'));
					//We don't support using newer versioned option files in older releases
					if($plug_major == $major && $plug_minor >= $minor)
					{
						//Loop around all of the options
						foreach($options->getelementsByTagName('option') as $child)
						{
							//Place the option into the option array, DOMDocument decodes html entities for us
							$this->opt[$child->getAttribute('name')] = $child->nodeValue;
						}
					}
				}
			}
			//Commit the loaded options to the database
			$this->update_option($this->unique_prefix . '_options', $this->opt);
			//Everything was successful, let the user know
			$this->message['updated fade'][] = __('Settings successfully imported from the uploaded file.', $this->identifier);
		}
		else
		{
			//Throw an error since we could not load the file for various reasons
			$this->message['error'][] = __('Importing settings from file failed.', $this->identifier);
		}
		//Reset to the default error handler after we're done
		restore_error_handler();
		//Output any messages that there may be
		add_action('admin_notices', array($this, 'message'));
	}
	/**
	 * opts_reset
	 * 
	 * Resets the database settings array to the default set in opt
	 */
	function opts_reset()
	{
		//Do a nonce check, prevent malicious link/form problems
		check_admin_referer($this->unique_prefix . 'admin_import_export');
		//Only needs this one line, will load in the hard coded default option values
		$this->update_option($this->unique_prefix . '_options', $this->opt);
		//Reset successful, let the user know
		$this->message['updated fade'][] = __('Settings successfully reset to the default values.', $this->identifier);
		add_action('admin_notices', array($this, 'message'));
	}
	/**
	 * contextual_help action hook function
	 * 
	 * @param  string $contextual_help
	 * @param  string $screen
	 * @return string
	 */
	function contextual_help($contextual_help, $screen)
	{
		// add contextual help on current screen		
		if ($screen == 'settings_page_' . $this->identifier)
		{
			$contextual_help = $this->_get_contextual_help();
			$this->_has_contextual_help = true;
		}
		return $contextual_help;
	}
	/**
	 * get contextual help
	 * 
	 * @return string
	 */
	private function _get_contextual_help()
	{
		$t = $this->_get_help_text();	
		$t = sprintf('<div class="metabox-prefs">%s</div>', $t);	
		$title = __($this->full_name, $this->identifier);	
		$t = sprintf('<h5>%s</h5>%s', sprintf(__('Get help with "%s"'), $title), $t);
		return $t;
	}
	/**
	 * message
	 * 
	 * Prints to screen all of the messages stored in the message member variable
	 */
	function message()
	{
		//Loop through our message classes
		foreach($this->message as $class)
		{
			//Loop through the messages in the current class
			foreach($class as $message)
			{
				printf('<div class="%s"><p>%s</p></div>', $class, $message);	
			}
		}	
	}
	/**
	 * install
	 * 
	 * Function prototype to prevent errors
	 */
	function install()
	{
		
	}
	function admin_head()
	{
		
	}
	function admin_page()
	{
		
	}
	function settings_form()
	{
		
	}
	function import_form()
	{
		printf('<div id="%s_import_export_relocate">', $this->unique_prefix);
		printf('<form action="options-general.php?page=%s" method="post" enctype="multipart/form-data" id="%s_admin_upload">', $this->identifier, $this->unique_prefix);
		wp_nonce_field($this->unique_prefix . '_admin_upload');
	}
	/**
	 * input_text
	 * 
	 * This will output a well formed table row for a text input
	 * 
	 * @param string $label
	 * @param string $option
	 * @param string $width [optional]
	 * @param bool $disable [optional]
	 * @param string $description [optional]
	 * @return 
	 */
	function input_text($label, $option, $width = '32', $disable = false, $description = "")
	{?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo $option;?>"><?php echo $label;?></label>
			</th>
			<td>
				<input type="text" name="<?php echo $option;?>" id="<?php echo $option;?>" <?php if($disable){echo 'disabled="disabled" class="disabled"';}?> value="<?php echo $this->opt[$option];?>" size="<?php echo $width;?>" /><br />
					<?php if($description !== ""){?><span class="setting-description"><?php echo $description;?></span><?php } ?>
			</td>
		</tr><?php
	}
	/**
	 * input_check
	 * 
	 * This will output a well formed table row for a checkbox input
	 * 
	 * @param string $label
	 * @param string $option
	 * @param string $instruction
	 * @param bool $disable [optional]
	 * @param string $description [optional]
	 * @return 
	 */
	function input_check($label, $option, $instruction, $disable = false, $description = "")
	{?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo $option;?>"><?php echo $label;?></label>
			</th>
			<td>	
				<label>
					<input type="checkbox" name="<?php echo $option;?>" id="<?php echo $option;?>" <?php if($disable){echo 'disabled="disabled" class="disabled"';}?> value="true" <?php checked(true, $this->opt[$option]);?> />
						<?php echo $instruction;?>				
				</label><br />
				<?php if($description !== ""){?><span class="setting-description"><?php echo $description;?></span><?php } ?>
			</td>
		</tr><?php
	}
	/**
	 * input_select
	 * 
	 * This will output a well formed table row for a select input
	 * 
	 * @param string $label
	 * @param string $option
	 * @param array $values
	 * @param bool $disable [optional]
	 * @param string $description [optional]
	 * @return 
	 */
	function input_select($label, $option, $values, $disable = false, $description = "")
	{?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo $option;?>"><?php echo $label;?></label>
			</th>
			<td>
				<select name="<?php echo $option;?>" id="<?php echo $option;?>" <?php if($disable){echo 'disabled="disabled" class="disabled"';}?>>
					<?php $this->select_options($option, $values); ?>
				</select><br />
				<?php if($description !== ""){?><span class="setting-description"><?php echo $description;?></span><?php } ?>
			</td>
		</tr><?php
	}
	/**
	 * select_options
	 *
	 * Displays wordpress options as <seclect> options defaults to true/false
	 *
	 * @param string $optionname name of wordpress options store
	 * @param array $options array of names of options that can be selected
	 * @param array $exclude[optional] array of names in $options array to be excluded
	 */
	function select_options($optionname, $options, $exclude = array())
	{
		$value = $this->opt[$optionname];
		//First output the current value
		if($value)
		{
			printf('<option>%s</option>', $value);
		}
		//Now do the rest
		foreach($options as $option)
		{
			//Don't want multiple occurance of the current value
			if($option != $value && !in_array($option, $exclude))
			{
				printf('<option>%s</option>', $option);
			}
		}
	}
}