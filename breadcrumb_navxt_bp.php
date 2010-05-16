<?php
/*
Plugin Name: Breadcrumb NavXT BuddyPress extension
Plugin URI: http://mtekk.weblogs.us/code/breadcrumb-navxt/
Description: Extends Breadcrumb NavXT to support BuddyPress. For details on how to use this plugin visit <a href="http://mtekk.weblogs.us/code/breadcrumb-navxt/">Breadcrumb NavXT</a>. 
Version: 0.0.1
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
class bcn_fill_bp
{
 	protected $trail;
	protected $opt = array
		(
			//The prefix for group breadcrumbs, place on all page elements and inside of current_item prefix
			'group_prefix' => '',
			//The suffix for group breadcrumbs, place on all page elements and inside of current_item suffix
			'group_suffix' => ''
		);
	function __construct()
	{
		//Register our extending call
		add_action('bcn_before_fill', array($this, 'fill'));
	}
	function do_group()
	{
		//Insert the group breadcrumb into the trail
		$this->trail->add(new bcn_breadcrumb(bp_get_group_name(), $this->opt['group_prefix'] . $this->trail->opt['current_item_prefix'], 
			$this->trail->opt['current_item_suffix'] . $this->opt['group_suffix']));
	}
	function fill(bcn_breadcrumb_trail $trail)
	{
		$this->trail = $trail;
		//Split off based on the specific group subpage
		//If we're just doing the group home page
		if(bp_is_group_home())
		{
			$this->do_group();
		}
		else if(bp_is_group_forum())
		{
			
		}
		else if(bp_is_group_activity())
		{
			
		}
		else if(bp_is_group_members())
		{
			
		}
		return $trail;
	}
}
//Let's make an instance of our object takes care of everything
$bcn_fill_bp = new bcn_fill_bp;