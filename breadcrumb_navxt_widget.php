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
class bcn_widget extends WP_Widget
{
	//Default constructor
	function __construct()
	{
		$ops = array('classname' => 'widget_breadcrumb_navxt', __('This widget adds a breadcrumb trail to your widget sidebar.'));
		//We're going to be a bit evil here and do things the PHP5 way
		parent::__construct('bcn_widget', 'Breadcrumb NavXT', $ops);
	}
	function widget($args, $instance)
	{
		extract($args);
		//Manditory before widget junk
		echo $before_widget;
		//We'll want to switch between the two breadcrumb output types
		if($instance['list'] == true)
		{
			//Display the list output breadcrumb
			echo '<ul class="breadcrumb_trail">';
			bcn_display_list(false, $instance['linked'], $instance['reverse']);
			echo '</ul>';
		}
		else
		{
			//Display the regular output breadcrumb
			echo '<div class="breadcrumb_trail">';
			bcn_display(false, $instance['linked'], $instance['reverse']);
			echo '</div>';
		}
		//Manditory after widget junk
		echo $after_widget;
	}
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		//Filter out anything that could be invalid
		$instance['list'] = esc_attr($new_instance['list']);
		$instance['linked'] = esc_attr($new_instance['linked']);
		$instance['reverse'] = esc_attr($new_instance['reverse']);
		return $instance;
	}
	function form($instance)
	{
		//Filter out anything that could be invalid
		$list = esc_attr($instance['list']);
		$linked = esc_attr($instance['linked']);
		$reverse = esc_attr($instance['reverse']);?>
		<p>
			<input class="widefat" type="checkbox" name="<?php $this->get_field_name('list'); ?>" id="<?php $this->get_field_id('list'); ?>" value="true" <?php checked(true, $list);?> />
			<label for="<?php echo $this->get_field_id('list'); ?>"> <?php _e('Output trail as a list.'); ?></label>
		</p>
		<p>
			<input class="widefat" type="checkbox" name="<?php $this->get_field_name('linked'); ?>" id="<?php $this->get_field_id('linked'); ?>" value="true" <?php checked(true, $linked);?> />
			<label for="<?php echo $this->get_field_id('linked'); ?>"> <?php _e('Allow the breadcrumbs to be linked.'); ?></label>
		</p>
		<p>
			<input class="widefat" type="checkbox" name="<?php $this->get_field_name('reverse'); ?>" id="<?php $this->get_field_id('reverse'); ?>" value="true" <?php checked(true, $reverse);?> />
			<label for="<?php echo $this->get_field_id('reverse'); ?>"> <?php _e('Reverse the order of the trail.'); ?></label>
		</p>
		<?php
	}
}