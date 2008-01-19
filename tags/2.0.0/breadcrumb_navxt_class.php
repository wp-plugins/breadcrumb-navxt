<?php
/*
Plugin Name: Breadcrumb NavXT - Core
Plugin URI: http://mtekk.weblogs.us/code/breadcrumb-navxt/
Description: Adds a breadcrumb navigation showing the visitor&#39;s path to their current location. For details on how to use this plugin visit <a href="http://mtekk.weblogs.us/code/breadcrumb-navxt/">Breadcrumb NavXT</a>. 
Version: 2.0.0
Author: John Havlik
Author URI: http://mtekk.weblogs.us/
*/
/*
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
 */
$bcn_version = "2.0.0";
//The main class
class bcn_breadcrumb
{
	var $opt;
	//Class Constructor
	function bcn_breadcrumb()
	{
		//Setting array
		$this->opt = array(
				'static_frontpage' => 'false',
			//*** only used if 'static_frontpage' => true
			//Relative URL for your blog's address that is used for the Weblog link. 
			//Use it if your blog is available at http://www.site.com/myweblog/, 
			//and at http://www.site.com/ a Wordpress page is being displayed:
			//In this case apply '/myweblog/'.
				'url_blog' => '',//
			//Display HOME? If set to false, HOME is not being displayed. 
				'home_display' => 'true',
			//URL for the home link
				'url_home' => get_option('home'),
			//Apply a link to HOME? If set to false, only plain text is being displayed.
				'home_link' => 'true',
			//Text displayed for the home link, if you don't want to call it home then just change this.
			//Also, it is being checked if the current page title = this variable. If yes, only the Home link is being displayed,
			//but not a weird "Home / Home" breadcrumb.	
				'title_home' => 'Home',
			//Text displayed for the weblog. If "'static_frontpage' => false", you
			//might want to change this value to "Home" 
				'title_blog' => 'Blog',
			//Separator that is placed between each item in the breadcrumb navigation, but not placed before
			//the first and not after the last element. You also can use images here,
			//e.g. '<img src="separator.gif" title="separator" width="10" height="8" />'
				'separator' => ' > ',
			//Prefix for a search page
				'search_prefix' => 'Search results for &#39;',
			//Suffix for a search page
				'search_suffix' => '&#39;',
			//Prefix for a author page
				'author_prefix' => 'Posts by ',
			//Suffix for a author page
				'author_suffix' => '',
			//Prefix for an attachment post
				'attachment_prefix' => 'Attachment: ',
			//Suffix for an attachment post
				'attachment_suffix' => '',
			//Name format to display for author (e.g., nickname, first_name, last_name, display_name)
				'author_display' => 'display_name',
			//Prefix for a single blog article.
				'singleblogpost_prefix' => 'Blog article: ',
			//Suffix for a single blog article.
				'singleblogpost_suffix' => '',
			//Prefix for a page.
				'page_prefix' => '',
			//Suffix for a page.
				'page_suffix' => '',
			//The prefix that is used for mouseover link (e.g.: "Browse to: Archive")
				'urltitle_prefix' => 'Browse to: ',
			//The suffix that is used for mouseover link
				'urltitle_suffix' => '',
			//Prefix for categories.
				'archive_category_prefix' => 'Archive by category &#39;',
			//Suffix for categories.
				'archive_category_suffix' => '&#39;',
			//Prefix for archive by year/month/day
				'archive_date_prefix' => 'Archive for ',
			//Suffix for archive by year/month/day
				'archive_date_suffix' => '',
			//Archive date format (e.g., ISO (yy/mm/dd), US (mm/dd/yy), EU (dd/mm/yy))
				'archive_date_format' => 'EU',
			//Prefix for tags.
				'archive_tag_prefix' => 'Archive by tag &#39;',
			//Suffix for tags.
				'archive_tag_suffix' => '&#39;',
			//Text displayed for a 404 error page, , only being used if 'use404' => true
				'title_404' => '404',
			//Display the paged information on pages that are paged
				'paged_display' => 'false',
			//Prefix to be displayed before the page number
				'paged_prefix' => ', Page ',
			//Suffix to be displayed after the page number
				'paged_suffix' => '',
			//Display current item as link?
				'link_current_item' => 'false',
			//URL title of current item, only being used if 'link_current_item' => true
				'current_item_urltitle' => 'Link of current page (click to refresh)', //
			//Style or prefix being applied as prefix to current item. E.g. <span class="bc_current">
				'current_item_style_prefix' => '',
			//Style or prefix being applied as suffix to current item. E.g. </span>
				'current_item_style_suffix' => '',
			//Maximum number of characters of post title to be displayed? 0 means no limit.
				'posttitle_maxlen' => 0,
			//Display category when displaying single blog post
				'singleblogpost_category_display' => 'true',
			//Prefix for single blog post category, only being used if 'singleblogpost_category_display' => true
				'singleblogpost_category_prefix' => '',
			//Suffix for single blog post category, only being used if 'singleblogpost_category_display' => true
				'singleblogpost_category_suffix' => '',
		);
	}
	//Breadcrumb Creation Function
	function display($bcn_return = false)
	{
		global $wpdb, $post, $wp_query, $bcn_version, $paged;
		//Initilize running length variable
		$length = 0;
		//Initilize breadcrumb stream
		$breadcrumb = array(
					//Used for the blog title
					'title' => NULL,
					//Used for the category/page hierarchy
					'middle' => NULL,
					//Used for the current tiem
					'last' => array(
									'prefix' => NULL,
									'item' => NULL,
									'suffix' => NULL
									)
					);
					
		//////////////
		//Note: everything still needs to be changed for localization
		//////////////
		
		
		//Figure out the title link
		//For home page
		if(is_home())
		{
			//Static front page
			if(($this->opt['static_frontpage'] === 'true' || get_option('page_on_front')) && $this->opt['home_display'] === 'true')
			{
				echo "moo";
				//Should we display the home link or not
				if($this->opt['home_link'])
				{
					//If so, let's set it up
					$breadcrumb['title'] = '<a title="' . $this->opt['urltitle_prefix'] . $this->opt['title_blog'] . $this->opt['urltitle_suffix'] . '" href="' . $this->opt['url_home'] . '">' . $this->opt['title_home'] . '</a>';
				}
				else
				{
					//Otherwise just the specified 'title_home' will do
					$breadcrumb['title'] = $this->opt['title_home'];
				}
			}
			//If it's paged, we'll want to link it to the first page
			else if(is_paged() && $this->opt['paged_display'] === 'true')
			{
				$breadcrumb['title'] = '<a title="' . $this->opt['urltitle_prefix'] . $this->opt['title_blog'] . $this->opt['urltitle_suffix'] . '" href="' . get_option('home') . '" >' . $this->opt['title_blog'] . '</a>';
			}
			//Non-static front page, if link current item is off
			else if($this->opt['link_current_item'] === 'false') 
			{
				$breadcrumb['title'] = $this->opt['title_blog'];
			}
			else
			{
				$breadcrumb['title'] = '<a title="' . $this->opt['current_item_urltitle'] . '" href="' . get_option('home') . '" >' . $this->opt['title_blog'] . '</a>';
			}
		}
		//For everyone else
		else
		{
			$breadcrumb['title'] = '<a title="' . $this->opt['urltitle_prefix'] . $this->opt['title_blog'] . $this->opt['urltitle_suffix'] . '" href="' . get_option('home') . '" >' . $this->opt['title_blog'] . '</a>';
		}
		////////////////////////////////////
		//Do specific opperations for the various page types
		////////////////////////////////////
		//For searches
		if(is_search())
		{
			Global $s;
			//Get the search prefix
			$breadcrumb['last']['prefix'] = $this->opt['search_prefix'];
			//Get the searched text
			$breadcrumb['last']['item'] = wp_specialchars($s, 1);
			//Get the search suffix
			$breadcrumb['last']['suffix'] = $this->opt['search_suffix'];
		}
		////////////////////////////////////
		//For post/page attachments
		else if(is_attachment())
		{
			//Blog link and parent page
			$bcn_parent_id = $post->post_parent;
			//Get the parent title
			$bcn_parent_title = get_post($bcn_parent_id);
			//Setup the attachment's parent link
			$bcn_parent = '<a title="' . $this->opt['urltitle_prefix'] .
			$bcn_parent_title->post_title . $this->opt['urltitle_suffix'] . '" href="' . get_permalink($bcn_parent_id) . '">' . $bcn_parent_title->post_title . '</a>';
			$breadcrumb['middle'] = $bcn_parent;
			//Attachment prefix text
			$breadcrumb['last']['prefix'] = $this->opt['attachment_prefix'];
			//Get attachment name
			$breadcrumb['last']['item'] = trim(wp_title('', false));
			//Attachment suffix text
			$breadcrumb['last']['suffix'] = $this->opt['attachment_suffix'];
		}
		////////////////////////////////////
		//For pages
		else if(is_page())
		{
			//Get the post title, this is a more robust method than using $post
			$bcn_page_title = trim(wp_title('', false));
			$bcn_parent_id = $post->post_parent;
			$bcn_middle = array();
			if($bcn_parent_id != 0)
			{
				//Fill the initial page
				//Use WordPress API, though a bit heavier than the old method, this will ensure compatibility with other plug-ins
				$bcn_parent = get_post($bcn_parent_id);
				$bcn_middle[] = '<a href="' . get_permalink($bcn_parent_id) . '" title="' . $this->opt['urltitle_prefix'] . $bcn_parent->post_title . $this->opt['urltitle_suffix'] . '">' . $bcn_parent->post_title . '</a>';
				$bcn_parent_id  = $bcn_parent->post_parent;
				while(is_numeric($bcn_parent_id) && $bcn_parent_id != 0)
				{
					$bcn_parent = get_post($bcn_parent_id);
					//Pushback a page into the array
					$bcn_middle[] = '<a href="' . get_permalink($bcn_parent_id) . '" title="' . $this->opt['urltitle_prefix'] . $bcn_parent->post_title . $this->opt['urltitle_suffix'] . '">' . $bcn_parent->post_title . '</a>';
					$bcn_parent_id = $bcn_parent->post_parent;
				}
				krsort($bcn_middle);
			}
			//Check to advoid Home > Home condition, has quick fallout for non-static conditions
			if(get_option('page_on_front') == 0 || !$this->opt['static_frontpage'] || (strtolower($bcn_page_title) != strtolower($this->opt['title_home'])))
			{
				$breadcrumb['middle'] = $bcn_middle;
				$breadcrumb['last']['prefix'] = $this->opt['page_prefix'];
				$breadcrumb['last']['item'] = $bcn_page_title;
				$breadcrumb['last']['suffix'] = $this->opt['page_suffix'];
			}
		}
		////////////////////////////////////
		//For blog posts
		else if(is_single())
		{
			//Get the post title, this is a more robust method than using $post
			$bcn_post_title = trim(wp_title('', false));
			//Add categories if told to
			if($this->opt['singleblogpost_category_display'] === 'true') {
				//Figure out the categories leading up to the post
				$bcn_middle = array();
				//Fills the object to get 
				$bcn_object = get_the_category();
				//Now find which one has a parrent, pick the first one that does
				$i = 0;
				$bcn_use_category = 0;
				foreach($bcn_object as $object)
				{
					if(is_numeric($object->category_parent) && $bcn_use_category == 0)
					{
						$bcn_use_category = $i;
					}
					$i++;
				}
				//Get parents of current category
				$bcn_category = $bcn_object[$bcn_use_category];
				//Fill the initial category
				$bcn_middle[] = $this->opt['singleblogpost_category_prefix'] . '<a href="' . get_category_link($bcn_category->cat_ID) . '" title="' . $this->opt['urltitle_prefix'] . $bcn_category->cat_name . $this->opt['urltitle_suffix'] . '">' . $bcn_category->cat_name . '</a>'. $this->opt['singleblogpost_category_suffix'];
				$bcn_parent_id  = $bcn_category->category_parent;
				while($bcn_parent_id)
				{
					$bcn_category = get_category($bcn_parent_id);
					//Pushback a category into the array
					$bcn_middle[] = $this->opt['singleblogpost_category_prefix'] . '<a href="' . get_category_link($bcn_category->cat_ID) . '" title="' . $this->opt['urltitle_prefix'] . $bcn_category->cat_name . $this->opt['urltitle_suffix'] . '">' . $bcn_category->cat_name . '</a>' . $this->opt['singleblogpost_category_suffix'];
					$bcn_parent_id = $bcn_category->category_parent;
				}
				//We need to reverse the order (by key) to get the proper output
				krsort($bcn_middle);
			}
			//Trim post title if needed
			if($this->opt['posttitle_maxlen'] > 0 && (strlen($bcn_post_title) + 3) > $this->opt['posttitle_maxlen'])
			{
				$bcn_post_title = substr($bcn_post_title, 0, $this->opt['posttitle_maxlen']-1) . '&hellip;';
			}
			//Place it all in the array
			$breadcrumb['middle'] = $bcn_middle;
			$breadcrumb['last']['prefix'] = $this->opt['singleblogpost_prefix'];
			$breadcrumb['last']['item'] = $bcn_post_title;
			$breadcrumb['last']['suffix'] = $this->opt['singleblogpost_suffix'];
		}
		////////////////////////////////////
		//For author pages
		else if(is_author())
		{
			//Author prefix text
			$breadcrumb['last']['prefix'] = $this->opt['author_prefix'];
			//Get the Author name, note it is an array
			$bcn_curauth = (get_query_var('author_name')) ? get_userdatabylogin(get_query_var('author_name')) : get_userdata(get_query_var('author'));
			//Get the Author display type
			$bcn_authdisp = $this->opt['author_display'];
			//Make sure user picks only safe values
			if($bcn_authdisp == 'nickname' || $bcn_authdisp == 'nickname' || $bcn_authdisp == 'first_name' || $bcn_authdisp == 'last_name' || $bcn_authdisp == 'display_name')
			{
				$breadcrumb['last']['item'] = $bcn_curauth->$bcn_authdisp;
			}
			$breadcrumb['last']['suffix'] = $this->opt['author_suffix'];
		}
		////////////////////////////////////
		//For category based archives
		else if(is_archive() && is_category())
		{
			//Simmilar to using $post, but for things $post doesn't cover
			$bcn_object = $wp_query->get_queried_object();
			//Get parents of current category
			$bcn_parent_id  = $bcn_object->category_parent;
			$cat_breadcrumbs = '';
			while($bcn_parent_id)
			{
				$bcn_category = get_category($bcn_parent_id);
				$cat_breadcrumbs = '<a href="' . get_category_link($bcn_category->cat_ID) . '" title="' . $this->opt['urltitle_prefix'] . $bcn_category->cat_name . $this->opt['urltitle_suffix'] . '">' . $bcn_category->cat_name . '</a>' . $this->opt['separator'] . $cat_breadcrumbs;
				$bcn_parent_id = $bcn_category->category_parent;
			}
			//New hiearchy dictates that cateories look like parent pages, and thus
			$breadcrumb['last']['prefix'] = $cat_breadcrumbs;
			$breadcrumb['last']['prefix'] .= $this->opt['archive_category_prefix'];
			//Current Category, uses WP API to get the title of the page, hopefully itis more robust than the old method
			$breadcrumb['last']['item'] = trim(wp_title('', false));
			$breadcrumb['last']['suffix'] = $this->opt['archive_category_suffix'];
		}
		////////////////////////////////////
		//For date based archives
		else if(is_archive() && is_date())
		{
			//If it's archives by day
			if(is_day())
			{
				//If the date format is US style
				if($this->opt['archive_date_format'] == 'US')
				{
					$breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'];
					$breadcrumb['last']['item'] = '<a title="Browse to the ' .
						get_the_time('F') . ' ' . get_the_time('Y') . ' archive" href="' .
						get_year_link(get_the_time('Y')) . get_the_time('m') . '">' .
						get_the_time('F') . '</a>' . ' ' . get_the_time('jS') . ', ' .
						' <a title="Browse to the ' . get_the_time('Y') . ' archive" href="' .
						get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>';
					$breadcrumb['last']['suffix'] = $this->opt['archive_date_suffix'];
				}
				//If the date format is ISO style
				else if($this->opt['archive_date_format'] == 'ISO')
				{
					$breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'];
					$breadcrumb['last']['item'] = ' <a title="Browse to the ' .
						get_the_time('Y') . ' archive" href="' . get_year_link(get_the_time('Y')) .
						'">' . get_the_time('Y') . '</a> <a title="Browse to the ' .
						get_the_time('F') . ' ' . get_the_time('Y') . ' archive" href="' .
						get_year_link(get_the_time('Y')) . get_the_time('m') . '">' .
						get_the_time('F') . '</a>' . ' ' . get_the_time('d');
					$breadcrumb['last']['suffix'] = $this->opt['archive_date_suffix'];
				}
				//If the date format is European style
				else
				{
					$breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'];
					$breadcrumb['last']['item'] = get_the_time('d') . ' ' .
						'<a title="Browse to the ' . get_the_time('F') . ' ' . get_the_time('Y') .
						' archive" href="' . get_year_link(get_the_time('Y')) . get_the_time('m') .
						'">' . get_the_time('F') . '</a>' . ' <a title="Browse to the ' . 
						get_the_time('Y') . ' archive" href="' . get_year_link(get_the_time('Y')) .
						'">' . get_the_time('Y') . '</a>';
					$breadcrumb['last']['suffix'] = $this->opt['archive_date_suffix'];
				}
			}
			//If it's archives by month
			else if(is_month())
			{
				$breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'];
				$breadcrumb['last']['item'] = get_the_time('F') . ' ' . '<a title="Browse to the ' . 
					get_the_time('Y') . ' archive" href="' . get_year_link(get_the_time('Y')) . '">' . 
					get_the_time('Y') . '</a>';
				$breadcrumb['last']['suffix'] = $this->opt['archive_date_suffix'];
			}
			//If it's archives by year
			else if(is_year())
			{
				$breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'];
				$breadcrumb['last']['item'] = get_the_time('Y');
				$breadcrumb['last']['suffix'] = $this->opt['archive_date_suffix'];
			}
		}
		////////////////////////////////////
		//For tag based archives
		else if(is_archive() && is_tag())
		{
			//Simmilar to using $post, but for things $post doesn't cover
			//$bcn_object = $wp_query->get_query_name();
			$breadcrumb['last']['prefix'] = $this->opt['archive_tag_prefix'];
			//Use the WordPress API for the page title, should hook better than the other method
			$breadcrumb['last']['item'] = trim(wp_title('', false));
			$breadcrumb['last']['suffix'] = $this->opt['archive_tag_suffix'];
		}
		////////////////////////////////////
		//For 404 pages
		else if(is_404())
		{
			$breadcrumb['last']['item'] = $this->opt['title_404'];
		}
		////////////////////////////////////
		//For paged items
		if(is_paged() && $this->opt['paged_display'] === 'true')
		{
			//For home pages
			if(is_home())
			{
				$breadcrumb['title'] .= $this->opt['paged_prefix'] . $paged . $this->opt['paged_suffix'];
			}
			//For archive/search pages
			else
			{
				$breadcrumb['last']['suffix'] .= $this->opt['paged_prefix'] . $paged . $this->opt['paged_suffix'];
			}
		}
		////////////////////////////////////
		//Assemble the breadcrumb
		$bcn_output = '';
		if($breadcrumb['title'])
		{
			$bcn_output .= $breadcrumb['title'];
			if(is_array($breadcrumb['middle']))
			{
				foreach($breadcrumb['middle'] as $bcn_mitem)
				{
					$bcn_output .= $this->opt['separator'] . $bcn_mitem;
				}
			}
			else if($breadcrumb['middle'])
			{
				$bcn_output .= $this->opt['separator'] . $breadcrumb['middle'];
			}
			if($breadcrumb['last']['item'] != NULL)
			{
				if($this->opt['link_current_item'] === 'true')
				{
					$breadcrumb['last']['item'] = '<a title="' . $this->opt['current_item_urltitle'] . '" href="' . $_SERVER['REQUEST_URI'] . '">' . $breadcrumb['last']['item'] . '</a>';
				}
				$bcn_output .= $this->opt['separator'] . $this->opt['current_item_style_prefix'] . $breadcrumb['last']['prefix'] . $breadcrumb['last']['item'] . $breadcrumb['last']['suffix'] . $this->opt['current_item_style_suffix'];
			}
		}
		//Polyglot compatibility filter
		if (function_exists('polyglot_filter'))
		{
			$bcn_output = polyglot_filter($bcn_output);
		}
		//Return it or echo it?
		if($bcn_return)
		{
			return $bcn_output;
		}
		else
		{
			//Giving credit where credit is due, please don't remove it
			$bcn_tag = "\n" . "<!-- Breadcrumb, generated by Breadcrumb NavXT " . $bcn_version . " - http://mtekk.weblogs.us/code -->" . "\n";
			echo $bcn_tag . $bcn_output;
		}
	}
}
?>
