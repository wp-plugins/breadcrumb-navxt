<?php
/*
Plugin Name: Breadcrumb NavXT - Core
Plugin URI: http://mtekk.weblogs.us/code/breadcrumb-navxt/
Description: Adds a breadcrumb navigation showing the visitor&#39;s path to their current location. This plug-in provides direct access to the bcn_breadcrumb class without using the administrative interface. For details on how to use this plugin visit <a href="http://mtekk.weblogs.us/code/breadcrumb-navxt/">Breadcrumb NavXT</a>. 
Version: 2.1.99
Author: John Havlik
Author URI: http://mtekk.weblogs.us/
*/
/*  Copyright 2007-2008  John Havlik  (email : mtekkmonkey@gmail.com)

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
//Internal version number, may differ from above
$bcn_version = "2.2.0";
//The breadcrumb class
class bcn_breadcrumb
{
	//Our member variables
	//The main text that will be shown
	var $title;
	//Boolean, is this element linked
	var $linked;
	//Linked anchor contents, null if $linked == false
	var $anchor;
	//Global prefix, outside of link tags
	var $prefix;
	//Global suffix, outside of link tags
	var $suffix;
	//Default constructor
	function breadcrumb()
	{
		//Default state of unlinked
		$this->linked = false;
		//Always NULL if unlinked
		$this->anchor = NULL;
	}
	/**
	 * title_trim
	 * 
	 * This function will intelligently trim the title to the value passed in through $max_length.
	 * 
	 * @param (int) max_length of the title.
	 */
	function title_trim($max_length)
	{
		if((strlen($this->title) + 3) > $max_length)
		{
			$this->title = substr($this->title, $max_length - 1);
			//Make sure we can split at a space, but we want to limmit to cutting at max an additional 25%
			if(strpos($this->title, " ", 3 * $max_length / 4) > 0)
			{
				//Don't split mid word
				while(substr($this->title,-1) != " ")
				{
					$this->title = substr($this->title, 0, -1);
				}
			}
			//remove the whitespace at the end and add the hellip
			$this->title = rtrim($this->title) . '&hellip;';
		}
	}
}

//The trail class
class bcn_breadcrumb_trail
{
	//Our member variables
	//An array of breadcrumbs
	var $trail;
	//The options
	var $opt;
	//Default constructor
	function bcn_breadcrumb_trail()
	{
		//Initilize the trail as an array
		$this->trail = array();
		//Initilize with default option values
		$this->opt = array
		(
			'home_display' => 'true',
			//Separator that is placed between each item in the breadcrumb navigation, but not placed before
			//the first and not after the last element. You also can use images here,
			//e.g. '<img src="separator.gif" title="separator" width="10" height="8" />'
			'separator' => ' &gt; ',
			//The maximum title lenght
			'max_title_length' => 0,
			//Current item options, really only applies to static pages and posts unless other current items are linked
			'current_item_linked' => false,
			//The anchor template for current items, this is global, two keywords are available %link% and %title%
			'current_item_anchor' => '<a title="Reload the current page." href="%link%">',
			//The prefix for current items allows separate styling of the current location breadcrumb
			'current_item_prefix' => '',
			//The suffix for current items allows separate styling of the current location breadcrumb
			'current_item_suffix' => '',
			//Static page options
			//The prefix for page breadcrumbs, place on all page elements and outside of current_item prefix
			'page_prefix' => '',
			//The suffix for page breadcrumbs, place on all page elements and outside of current_item suffix
			'page_suffix' => '',
			//The anchor template for page breadcrumbs, two keywords are available %link% and %title%
			'page_anchor' => '<a title="Go to %title%." href="%link%">',
			//The post options previously singleblogpost
			//Should the trail include the taxonomy of the post
			'post_taxonomy_display' => true,
			//What taxonomy should be shown leading to the post, tag or category
			'post_taxonomy_type' => 'category'
		);
	}
	//The do filling functions
	/**
	 * do_search
	 * 
	 * A Breadcrumb Trail Filling Function
	 * 
	 * This functions fills a breadcrumb for a search page.
	 */
	function do_search()
	{
		global $s;
		//Add new breadcrumb to the trail
		$this->trail[] = new bcn_breadcrumb();
		//Figure out where we placed the crumb, make a nice pointer to it
		$bcn_breadcrumb = &$this->trail[count($this->trail)--];
		//Assign the prefix
		$bcn_breadcrumb->prefix = $this->opt['search_prefix'];
		//Assign the suffix
		$bcn_breadcrumb->suffix = $this->opt['search_suffix'];
		//Assign the title
		$bcn_breadcrumb->title = wp_specialchars($s, 1);
	}
	/**
	 * do_attachment
	 * 
	 * A Breadcrumb Trail Filling Function
	 * 
	 * This functions fills a breadcrumb for an attachment page.
	 */
	function do_attachment()
	{
		global $post;
		//Add new breadcrumb to the trail
		$this->trail[] = new bcn_breadcrumb();
		//Figure out where we placed the crumb, make a nice pointer to it
		$bcn_breadcrumb = &$this->trail[count($this->trail)--];
		//Assign the prefix
		$bcn_breadcrumb->prefix = $this->opt['attachment_prefix'];
		//Assign the suffix
		$bcn_breadcrumb->suffix = $this->opt['attachment_suffix'];
		//Addign the title, still using old method
		$bcn_breadcrumb->title = trim(wp_title('', false));
		//Get the parent page/post of the attachment
		$bcn_parent_id = $post->post_parent;
		//Get the parent's information
		$bcn_parent = get_post($bcn_parent_id);
		//We need to treat post and page attachment hierachy differently
		if($bcn_parent->post_type == "page")
		{
			//Place the rest of the page hierachy
			$this->page_parents($bcn_parent_id);
		}
		else
		{
			//Add new breadcrumb to the trail
			$this->trail[] = new bcn_breadcrumb();
			//Figure out where we placed the crumb, make a nice pointer to it
			$bcn_breadcrumb = &$this->trail[count($this->trail)--];
			//Assign the prefix
			$bcn_breadcrumb->prefix = $this->opt['attachment_prefix'];
			//Assign the suffix
			$bcn_breadcrumb->suffix = $this->opt['attachment_suffix'];
			//Get the parent's information
			$bcn_parent = get_post($bcn_parent_id);
			//Adding the title, still using old method
			$bcn_breadcrumb->title = $bcn_parent->post_title;
			//Assign the anchor properties
			$bcn_breadcrumb->anchor = str_replace("%title%", $bcn_parent->post_title, str_replace("%link%", get_permalink($id), $this->opt['post_anchor']));
			//We want this to be linked
			$bcn_breadcrumb->linked = true;
		}
	}
	/**
	 * do_author
	 * 
	 * A Breadcrumb Trail Filling Function
	 * 
	 * This functions fills a breadcrumb for an author page.
	 */
	function do_author()
	{
		//Add new breadcrumb to the trail
		$this->trail[] = new bcn_breadcrumb();
		//Figure out where we placed the crumb, make a nice pointer to it
		$bcn_breadcrumb = &$this->trail[count($this->trail)--];
		//Assign the prefix
		$bcn_breadcrumb->prefix = $this->opt['author_prefix'];
		//Assign the suffix
		$bcn_breadcrumb->suffix = $this->opt['author_suffix'];
		//Get the Author name, note it is an array
		$bcn_curauth = (get_query_var('author_name')) ? get_userdatabylogin(get_query_var('author_name')) : get_userdata(get_query_var('author'));
		//Get the Author display type
		$bcn_authdisp = $this->opt['author_display'];
		//Make sure user picks only safe values
		if($bcn_authdisp == 'nickname' || $bcn_authdisp == 'first_name' || $bcn_authdisp == 'last_name' || $bcn_authdisp == 'display_name')
		{
			//Assign the title
			$bcn_breadcrumb->title = $bcn_curauth->$bcn_authdisp;
		}
	}
	/**
	 * page_parents
	 * 
	 * A Breadcrumb Trail Filling Function
	 * 
	 * This recursive functions fills the trail with breadcrumbs for parent pages.
	 * @param  (int)   $id The id of the parent page.
	 */
	function page_parents($id)
	{
		//Add new breadcrumb to the trail
		$this->trail[] = new bcn_breadcrumb();
		//Figure out where we placed the crumb, make a nice pointer to it
		$bcn_breadcrumb = &$this->trail[count($this->trail)--];
		//Assign the prefix
		$bcn_breadcrumb->prefix = $this->opt['page_prefix'];
		//Assign the suffix
		$bcn_breadcrumb->suffix = $this->opt['page_suffix'];
		//Use WordPress API, though a bit heavier than the old method, this will ensure compatibility with other plug-ins
		$bcn_parent = get_post($id);
		//Assign the title
		$bcn_breadcrumb->title = $bcn_parent->post_title;
		//Assign the anchor properties
		$bcn_breadcrumb->anchor = str_replace("%title%", $bcn_parent->post_title, str_replace("%link%", get_permalink($id), $this->opt['page_anchor']));
		//We want this to be linked
		$bcn_breadcrumb->linked = true;
		//Figure out the next parent id
		$bcn_parent_id  = $bcn_parent->post_parent;
		//Make sure the id is valid
		if(is_numeric($bcn_parent_id) && $bcn_parent_id != 0)
		{
			//If valid, recursivly call this function
			page_parents($bcn_parent_id);
		}
	}
	/**
	 * do_page
	 * 
	 * A Breadcrumb Trail Filling Function
	 * 
	 * This functions fills a breadcrumb for a atatic page.
	 */
	function do_page()
	{
		global $post;
		//Add new breadcrumb to the trail
		$this->trail[] = new bcn_breadcrumb();
		//Figure out where we placed the crumb, make a nice pointer to it
		$bcn_breadcrumb = &$this->trail[count($this->trail)--];
		//Assign the prefix
		$bcn_breadcrumb->prefix = $this->opt['page_prefix'] . $this->opt['current_item_prefix'];
		//Assign the suffix
		$bcn_breadcrumb->suffix = $this->opt['current_item_suffix'] . $this->opt['page_suffix'];
		//Assign the title, using our older method to replace in the future
		$bcn_breadcrumb->title = trim(wp_title('', false));
		//Done with the current item, now on to the parents
		$bcn_parent_id = $post->post_parent;
		//If there is a parent page let's find it
		if(is_numeric($bcn_parent_id) && $bcn_parent_id != 0)
		{
			page_parents($bcn_parent_id);
		}
	}
	/**
	 * post_tag
	 * 
	 * A Breadcrumb Trail Filling Function
	 * 
	 * This functions fills a breadcrumb for the tags of a post
	 * 
	 * @TODO	Need to implement this cleaner, possibly a recursive object
	 */
	function post_tags()
	{
		global $post;
		//Add new breadcrumb to the trail
		$this->trail[] = new bcn_breadcrumb();
		//Figure out where we placed the crumb, make a nice pointer to it
		$bcn_breadcrumb = &$this->trail[count($this->trail)--];
		//Fills a temporary object with the tags for the post
		$bcn_object = get_the_tags($post->ID);
		//Only process if we have tags
		if(is_array($bcn_object))
		{
			$i = true;
			foreach($bcn_object as $tag)
			{
				//On the first run we don't need a separator
				if($i)
				{
					$bcn_breadcrumb->title = $this->opt['singleblogpost_tag_prefix'] . '<a href="' . get_tag_link($tag->term_id) . '" title="' . $this->opt['urltitle_prefix'] . $tag->name . $this->opt['urltitle_suffix'] . '">' . $tag->name . '</a>'. $this->opt['singleblogpost_tag_suffix'];
					$i = false;
				}
				else
				{
					$bcn_breadcrumb->title .= ', ' .$this->opt['singleblogpost_tag_prefix'] . '<a href="' . get_tag_link($tag->term_id) . '" title="' . $this->opt['urltitle_prefix'] . $tag->name . $this->opt['urltitle_suffix'] . '">' . $tag->name . '</a>'. $this->opt['singleblogpost_tag_suffix'];
				}
			}
		}
		else
		{
			$bcn_breadcrumb->title = "Untaged";	
		}
	}
	/**
	 * category_parents
	 * 
	 * A Breadcrumb Trail Filling Function
	 * 
	 * This recursive functions fills the trail with breadcrumbs for parent categories.
	 * @param  (int)   $id The id of the parent category.
	 */
	function category_parents($id)
	{
		global $post;
		//We kick out of the recursive loop when the id is not valid
		if($id)
		{
			//Add new breadcrumb to the trail
			$this->trail[] = new bcn_breadcrumb();
			//Figure out where we placed the crumb, make a nice pointer to it
			$bcn_breadcrumb = &$this->trail[count($this->trail)--];
			//Assign the prefix
			$bcn_breadcrumb->prefix = $this->opt['category_prefix'];
			//Assign the suffix
			$bcn_breadcrumb->suffix = $this->opt['category_suffix'];
			//Get the current category object
			$bcn_category = get_category($id);
			//Figure out the anchor for the first category
			$bcn_breadcrumb->anchor = str_replace("%title%", $bcn_category->cat_name, str_replace("%link%", get_category_link($bcn_category->cat_ID), $this->opt['category_anchor']));
			//We want this to be linked
			$bcn_breadcrumb->linked = true;
			//Figure out the rest of the category hiearchy via recursion
			$this->category_parents($bcn_category->category_parent);
		}
	}
	/**
	 * do_post
	 * 
	 * A Breadcrumb Trail Filling Function
	 * 
	 * This functions fills a breadcrumb for a posts.
	 */
	function do_post()
	{
		global $post;
		//Add new breadcrumb to the trail
		$this->trail[] = new bcn_breadcrumb();
		//Figure out where we placed the crumb, make a nice pointer to it
		$bcn_breadcrumb = &$this->trail[count($this->trail)--];
		//Assign the prefix
		$bcn_breadcrumb->prefix = $this->opt['page_prefix'] . $this->opt['current_item_prefix'];
		//Assign the suffix
		$bcn_breadcrumb->suffix = $this->opt['current_item_suffix'] . $this->opt['page_suffix'];
		//Assign the title, using our older method to replace in the future
		$bcn_breadcrumb->title = trim(wp_title('', false));
		//Check to see if breadcrumbs for the taxonomy of the post needs to be generated
		if($this->opt['post_taxonomy_display'])
		{
			//Figure out which taxonomy is desired
			if($this->opt['post_taxonomy'] == 'tag')
			{
				$this->post_tags();
			}
			else
			{
				//Fills the temp object to get the categories 
				$bcn_object = get_the_category();
				//Now find which one has a parent, pick the first one that does
				$i = 0;
				$bcn_use_category = 0;
				foreach($bcn_object as $object)
				{
					//We want the first category hiearchy
					if($object->category_parent > 0 && $bcn_use_category == 0)
					{
						$bcn_use_category = $i;
					}
					$i++;
				}
				//Fill out the category hiearchy
				$this->category_parents($bcn_use_category);
			}
		}
		//If our max title length is greater than 0 we should do something
		if($this->opt['max_title_length'] > 0)
		{
			$bcn_breadcrumb->title_trim($this->opt['max_title_length']);
		}
	}
	/**
	 * fill
	 * 
	 * Breadcrumb Trail Filling Function
	 * 
	 * This functions fills the breadcrumb trail.
	 */
	function fill()
	{
		global $wpdb, $post, $wp_query, $bcn_version, $paged;
		////////////////////////////////////
		//Do specific opperations for the various page types
		////////////////////////////////////
		//Check if this isn't the first of a multi paged item
		if(is_paged() && $this->opt['paged_display'] === 'true')
		{
			$this->do_paged();
		}
		//For the home/front page
		if(is_front_page())
		{
			$this->do_home();
		}
		//For searches
		else if(is_search())
		{
			$this->do_search();
		}
		////////////////////////////////////
		//For pages
		else if(is_page())
		{
			$this->do_page();
		}
		////////////////////////////////////
		//For post/page attachments
		else if(is_attachment())
		{
			$this->do_attachment();
		}
		////////////////////////////////////
		//For blog posts
		else if(is_single())
		{
			$this->do_post();
		}
		////////////////////////////////////
		//For author pages
		else if(is_author())
		{
			$this->do_author();
		}
		////////////////////////////////////
		//For category based archives
		else if(is_archive() && is_category())
		{
			$this->do_archive_by_category();
		}
		////////////////////////////////////
		//For date based archives
		else if(is_archive() && is_date())
		{
			$this->do_archive_by_date();
		}
		////////////////////////////////////
		//For tag based archives
		else if(is_archive() && is_tag())
		{
			$this->do_archive_by_tag();
		}
		////////////////////////////////////
		//For 404 pages
		else if(is_404())
		{
			$this->breadcrumb['last']['item'] = $this->opt['title_404'];
		}
		//We always do the home link last
		$this->do_home();
		//We build the trail backwards the last thing to do is to get it back to normal order
		krsort($this->trail);
	}
	/**
	 * display
	 * 
	 * Breadcrumb Creation Function
	 * 
	 * This functions outputs or returns the breadcrumb trail.
	 *
	 * @param  (bool)   $bcn_return Whether to return data or to echo it.
	 * @param  (bool)   $bcn_linked Whether to allow hyperlinks in the trail or not.
	 * 
	 * @return (void)   Void if Option to print out breadcrumb trail was chosen.
	 * @return (string) String-Data of breadcrumb trail. 
	 */
	function display($bcn_return = false, $bcn_linked = true)
	{
		global $bcn_version;
		//Initilize the string which will hold the compiled trail
		$bcn_trail_str = "";
		//The main compiling loop
		foreach($this->trail as $key=>$breadcrumb)
		{
			//We only use a separator if there is more than one element
			if($key > 0)
			{
				$bcn_trail_str .= $this->opt['separator'];
			}
			//Place in the breadcrumb's elements
			$bcn_trail_str .= $breadcrumb->prefix;
			//If we are linked we'll need to do up the link
			if($breadcrumb->linked && $bcn_linked)
			{
				$bcn_trail_str .= $breadcrumb->anchor . $breadcrumb->title . "</a>";
			}
			//Otherwise we just slip in the title
			else
			{
				$bcn_trail_str .= $breadcrumb->title;
			}
			$bcn_trail_str .= $breadcrumb->suffix;
		}
		//Should we return or echo the compiled trail?
		if($bcn_return)
		{
			return $bcn_trail_str;
		}
		else
		{
			//Giving credit where credit is due, please don't remove it
			$bcn_tag = "<!-- \nBreadcrumb, generated by Breadcrumb NavXT " . $bcn_version . " - http://mtekk.weblogs.us/code \n-->";
			echo $bcn_tag . $bcn_trail_str;
		}
	}
}








//The main class
class bcn_breadcrumb_old
{
	var $opt;
	var $breadcrumb;
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
				'url_blog' => '',
			//Display HOME? If set to false, HOME is not being displayed. 
				'home_display' => 'true',
			//URL for the home link
				'url_home' => get_option('home') . "/",
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
				'separator' => ' &gt; ',
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
			//Display category or tag when displaying single blog post (e.g., tag or category)
				'singleblogpost_taxonomy' => 'category',
			//Display category/tag when displaying single blog post
				'singleblogpost_taxonomy_display' => 'true',
			//Prefix for single blog post category, only being used if 'singleblogpost_taxonomy_display' => true
				'singleblogpost_category_prefix' => '',
			//Suffix for single blog post category, only being used if 'singleblogpost_taxonomy_display' => true
				'singleblogpost_category_suffix' => '',
			//Prefix for single blog post category, only being used if 'singleblogpost_taxonomy_display' => true
				'singleblogpost_tag_prefix' => '',
			//Suffix for single blog post tag, only being used if 'singleblogpost_taxonomy_display' => true
				'singleblogpost_tag_suffix' => '',
		);
		//Initilize breadcrumb stream
		$this->breadcrumb = array
		(
			//Used for the blog title
			'title' => NULL,
			//Used for the category/page hierarchy
			'middle' => NULL,
				//Used for the current tiem
			'last' => array
			(
				'prefix' => NULL,
				'item' => NULL,
				'suffix' => NULL
			)
		);
	}
	//Handle the home page or the first link part
	function do_home()
	{
		//Static front page
		if(get_option('show_on_front') == 'page')
		{
			//If we're displaying the home
			if($this->opt['home_display'] === 'true')
			{
				//Should we display the home link or not
				if($this->opt['home_link'] === 'true')
				{
					//If so, let's set it up
					$this->breadcrumb['title'] = '<a title="' . $this->opt['urltitle_prefix'] . $this->opt['title_home'] . $this->opt['urltitle_suffix'] . '" href="' . $this->opt['url_home'] . '">' . $this->opt['title_home'] . '</a>';
				}
				else
				{
					//Otherwise just the specified 'title_home' will do
					$this->breadcrumb['title'] = $this->opt['title_home'];
				}
			}
		}
		//If it's paged, we'll want to link it to the first page
		else if(is_paged() && $this->opt['paged_display'] === 'true')
		{
			$this->breadcrumb['title'] = '<a title="' . $this->opt['urltitle_prefix'] . $this->opt['title_blog'] . $this->opt['urltitle_suffix'] . '" href="' . get_option('home') . '">' . $this->opt['title_blog'] . '</a>';
		}
		//Non-static front page, if link current item is off
		else if($this->opt['link_current_item'] === 'false') 
		{
			$this->breadcrumb['title'] = $this->opt['title_blog'];
		}
		//Default to linking this is kinda hackish as we usually don't build links for the current item outside of the assembler
		else
		{
			$this->breadcrumb['title'] = '<a title="' . $this->opt['current_item_urltitle'] . '" href="' . get_option('home') . '">' . $this->opt['title_blog'] . '</a>';
		}
	}
	function do_title()
	{
		//If there are static front pages we need to make sure that link shows up as well as the blog title.	
		if(get_option('show_on_front') == 'page')
		{
			//Single posts, archives of all types, and the author pages are descendents of "blog"
			if(is_page() || is_single() || is_archive() || is_author() || (is_home() && $this->opt['link_current_item'] === 'true'))
			{
				$this->breadcrumb['title'] = array();
				$this->breadcrumb['title'][] = '<a title="' . $this->opt['urltitle_prefix'] . $this->opt['title_blog'] . $this->opt['urltitle_suffix'] . '" href="' . $this->opt['url_home'] . '">' . $this->opt['title_home'] . '</a>';
				$this->breadcrumb['title'][] = '<a title="' . $this->opt['urltitle_prefix'] . $this->opt['title_blog'] . $this->opt['urltitle_suffix'] . '" href="' . $this->opt['url_home'] . $this->opt['url_blog'] . '">' . $this->opt['title_blog'] . '</a>';
			}
			//If it's on the blog page but we don't link current
			else if(is_home())
			{
				$this->breadcrumb['title'] = array();
				$this->breadcrumb['title'][] = '<a title="' . $this->opt['urltitle_prefix'] . $this->opt['title_blog'] . $this->opt['urltitle_suffix'] . '" href="' . $this->opt['url_home'] . '">' . $this->opt['title_home'] . '</a>';
				$this->breadcrumb['title'][] = $this->opt['title_blog'];
			}
		}
		else
		{
			$this->breadcrumb['title'] = '<a title="' . $this->opt['urltitle_prefix'] . $this->opt['title_blog'] . $this->opt['urltitle_suffix'] . '" href="' . get_option('home') . '">' . $this->opt['title_blog'] . '</a>';
		}
	}
	//Handle category based archives
	function do_archive_by_category()
	{
		global $wp_query;
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
		$this->breadcrumb['last']['prefix'] = $cat_breadcrumbs;
		$this->breadcrumb['last']['prefix'] .= $this->opt['archive_category_prefix'];
		//Current Category, uses WP API to get the title of the page, hopefully itis more robust than the old method
		$this->breadcrumb['last']['item'] = trim(wp_title('', false));
		$this->breadcrumb['last']['suffix'] = $this->opt['archive_category_suffix'];		
	}
	//Handle date based archives
	function do_archive_by_date()
	{
		//If it's archives by day
		if(is_day())
		{
			//If the date format is US style
			if($this->opt['archive_date_format'] == 'US')
			{
				$this->breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'] . 
					'<a title="Browse to the ' . get_the_time('F') . ' ' . get_the_time('Y') . 
					' archive" href="' . get_year_link(get_the_time('Y')) . get_the_time('m') . 
					'">' . get_the_time('F') . '</a>' . ' ';
				$this->breadcrumb['last']['item'] = get_the_time('jS');
				$this->breadcrumb['last']['suffix'] = ', ' . ' <a title="Browse to the ' . 
					get_the_time('Y') . ' archive" href="' . get_year_link(get_the_time('Y')) . 
					'">' . get_the_time('Y') . '</a>' . $this->opt['archive_date_suffix'];
			}
			//If the date format is ISO style
			else if($this->opt['archive_date_format'] == 'ISO')
			{
				$this->breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'] .
					' <a title="Browse to the ' . get_the_time('Y') . ' archive" href="' . 
					get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . 
					'</a> <a title="Browse to the ' . get_the_time('F') . ' ' . get_the_time('Y') . 
					' archive" href="' . get_year_link(get_the_time('Y')) . get_the_time('m') . 
					'">' . get_the_time('F') . '</a>' . ' ';
				$this->breadcrumb['last']['item'] = get_the_time('d');
				$this->breadcrumb['last']['suffix'] = $this->opt['archive_date_suffix'];
			}
			//If the date format is European style
			else
			{
				$this->breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'];
				$this->breadcrumb['last']['item'] = get_the_time('d');
				$this->breadcrumb['last']['suffix'] = ' ' .'<a title="Browse to the ' . 
					get_the_time('F') . ' ' . get_the_time('Y') . ' archive" href="' . 
					get_year_link(get_the_time('Y')) . get_the_time('m') . '">' . 
					get_the_time('F') . '</a>' . ' <a title="Browse to the ' . get_the_time('Y') . 
					' archive" href="' . get_year_link(get_the_time('Y')) . '">' . 
					get_the_time('Y') . '</a>' . $this->opt['archive_date_suffix'];
			}
		}
		//If it's archives by month
		else if(is_month())
		{
			$this->breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'];
			$this->breadcrumb['last']['item'] = get_the_time('F');
			$this->breadcrumb['last']['suffix'] = ' ' . '<a title="Browse to the ' . 
				get_the_time('Y') . ' archive" href="' . get_year_link(get_the_time('Y')) . '">' . 
				get_the_time('Y') . '</a>' . $this->opt['archive_date_suffix'];
		}
		//If it's archives by year
		else if(is_year())
		{
			$this->breadcrumb['last']['prefix'] = $this->opt['archive_date_prefix'];
			$this->breadcrumb['last']['item'] = get_the_time('Y');
			$this->breadcrumb['last']['suffix'] = $this->opt['archive_date_suffix'];
		}
	}
	//Handle tag based archives
	function do_archive_by_tag()
	{
		$this->breadcrumb['last']['prefix'] = $this->opt['archive_tag_prefix'];
		//Use the WordPress API for the page title, should hook better than the other method
		$this->breadcrumb['last']['item'] = trim(wp_title('', false));
		$this->breadcrumb['last']['suffix'] = $this->opt['archive_tag_suffix'];
	}
	//Handled paged items
	function do_paged()
	{
		global $paged;
		//For home pages
		if(is_home())
		{
			$this->breadcrumb['title'] .= $this->opt['paged_prefix'] . $paged . $this->opt['paged_suffix'];
		}
		//For archive/search pages
		else
		{
			$this->breadcrumb['last']['suffix'] .= $this->opt['paged_prefix'] . $paged . $this->opt['paged_suffix'];
		}
	}
}
?>