=== Breadcrumb NavXT ===
Contributors: mtekk, hakre
Tags: breadcrumb, navigation
Requires at least: 2.7
Tested up to: 2.8
Stable tag: 3.2.0
Adds breadcrumb navigation showing the visitor's path to their current location.

== Description ==

Breadcrumb NavXT, the successor to the popular WordPress plugin Breadcrumb Navigation XT, was written from the ground up to be better than its ancestor. This plugin generates locational breadcrumb trails for your WordPress blog. These breadcrumb trails are highly customizable to suit the needs of just about any blog. The Administrative interface makes setting options easy, while a direct class access is available for theme developers and more adventurous users. Do note that Breadcrumb NavXT requires PHP5.

== Installation ==

Please visit [Breadcrumb NavXT's](http://mtekk.weblogs.us/code/breadcrumb-navxt/#installation "Go to Breadcrumb NavXT's project page's installation section.") project page for intallation and usage instructions.

== Change Log ==
3.3.0 [7-??-2009]: 
* Behavior change: The core plugin removed, and administrative plugin renamed, direct class still possible.
* New feature: Ability to trim the title length for all breadcrumbs in the trail.
3.2.0 [5-4-2009]: 
* New feature: Now can output breadcrumbs in trail as list elements.
* New feature: Translations for Dutch now included thanks to Stan Lenssen.
* New feature: Now breadcrumb trails can be output in reverse order.
* New feature: Ability to reset to default option values in administrative interface.
* New feature: Ability to export settings to a XML file.
* New feature: Ability to import settings from a XML file.
* Bug fix: Anchor templates now protected against complete clearing.
* Bug fix: Administrative interface related styling and JavaScript no longer leaks to other admin pages. * Bug fix: Calling `bcn_display()` works with the same inputs as `bcn_breadcrumb_trail::display()`.
* Bug fix: Calling `bcn_display()` multiple times will not place duplicate breadcrumbs into the trail.
3.1.0 [1-26-2009]:
* New feature: Tabular plugin integrated into the administrative interface/settings page plugin.
* New feature: Default options now are localized.
* New feature: Plugin uninstaller following the WordPress plugin uninstaller API.
* Bug fix: Administrative interface tweaked, hopefully more usable.
* Bug fix: Tabs work with WordPress 2.8-bleeding-edge.
* Bug fix: Translations for German, French, and Spanish are all updated.
* Bug fix: Paged archives, searches, and frontpage fixed.
3.0.2 [11-26-2008]:
* Bug fix: Default options are installed correctly now for most users.
* Bug fix: Now `bcn_breadcrumb_trail::fill()` is safe to call within the loop.
* Bug fix: In WPMU options now are properly separate/independent for each blog.
* Bug fix: WPMU settings page loads correctly after saving settings.
* Bug fix: Blog_anchor setting not lost on non-static frontpage blogs.
* Bug fix: Tabular add on no longer causes issues with WordPress 2.7.
* New feature: Spanish and French localization files are now included thanks to Karin Sequen and Laurent Grabielle.
3.0.1 [10-22-2008]:
* Bug fix: UTF-8 characters in the administrative interface now save/display correctly.
* Bug fix: Breadcrumb trails for attachments of pages no longer generate PHP errors.
* Bug fix: Administrative interface tweaks for installing default options.
* Bug fix: Changed handling of situation when Posts Page is not set and Front Page is set.
3.0.0 [9-22-2008]:
* New feature: Completely rewritten core and administrative interface.
* New feature: WordPress sidebar widget built in.
* New feature: Breadcrumb trail can output without links.
* New feature: Customizable anchor templates, allows things such as rel="nofollow".
* New feature: The home breadcrumb may now be excluded from the breadcrumb trail.
* Bug fix: 404 page breadcrumbs show up in static frontpage situations where the posts page is a child of the home page.
* Bug fix: Static frontpage situations involving the posts page being more than one level off of the home behave as expected.
* Bug fix: Compatible with all polyglot like plugins.
* Bug fix: Compatible with Viper007bond's Breadcrumb Titles for Pages plugin (but 3.0.0 can replace it as well)
* Bug fix: Author page support should be fixed on some setups where it did not work before.
2.1.4 [7-15-2008]:
* Bug fix: Post title max length option regression fixed.
* Bug fix: Double Home breadcrumb in static front page setups fixed.
* Bug fix: Home Breadcrumb in static front pages can be removed globally as it should have been able to before.
2.1.3 [7-1-2008]:
* New feature: Support for the qTranslate plugin.
* Bug fix: Removed some options that already have WordPress equivalents, general Administrative interface cleanup.
* Bug fix: I18n support improved, added some strings that should have been there before.
* Bug fix: Improved security for the administrative form, using the standard WP nonce tools.
* Bug fix: Single posts under the category taxonomy now properly finds the first hiearchy all the time, the "Double Dipping the Hierarchy" problem is fixed.
2.1.2 [5-21-2008]:
* Bug fix: Problems with /blog/ and // showing up in current item links should be resolved. =
* Bug fix: Static front page options should work as expected now.
* Bug fix: Paged items now work as they did in 2.0.x.
* Bug fix: Auto detection of static front pages has been fixed to be consistent across the board.
2.1.1 [4-25-2008]:
* Bug fix: Removed the array qualifier from the function header of `bcn_select_options()` this should fix compatibility issues with PHP4.
* Bug fix: Problems with linking the current item setting causing invalid XHTML output resolved.
* Bug fix: Behavior of linking current item works better in general.
2.1.0 [4-2-2008]:
* New feature: Administrative interface has been reworked to match the WordPress 2.5 administration panel.
* New feature: Breadcrumbs leading to posts can now be delimited by categories or tags (See new options: singleblogpost_taxonomy, singleblogpost_taxonomy_display).
* New feature: Attachments to pages will now show the full path to the attachment.
* New feature: When using the posttitle_maxlen option, the title will be trimmed to the nearest word, not exceeding the maximum length.
* Bug fix: Static front page support is back, supports the official WordPress static frontpage method.
2.0.4 [3-24-2008]:
* Bug fix: The warning generated by PHP on the use of strpos has been resolved.
2.0.3 [3-6-2008]:
* Bug fix: Administrative interface no longer interferes with WordPress global CSS styles.
* Bug fix: Usermeta errors will now also generate a usermeta variable dump (will aide in permanently solving the problem).
* Bug fix: The warning generated by some versions of PHP on the use of strpos has been resolved.
2.0.2 [2-15-2008]:
* Bug fix: Administrative interface default settings are now loaded when installing.
* Bug fix: Localization should work correctly now.
* Bug fix: Administrative interface now should handle html payloads in the form fields correctly.
* New feature: German localization files are now included thanks to Tom Klingenberg.
2.0.1 [1-25-2008]:
* Bug fix: Administrative interface errors now have more obvious meaning.
2.0.0 [1-19-2008]:
* New feature: Support for the standard WordPress localization methods.
* New feature: Support for the Polyglot plugin.
2.0.0 Beta 2 [11-30-2007]:
* Bug fix: Page hierarchy should work correctly now in hierarchies deeper than 3 pages.
* New feature: SVN repository at wp-plugins.org.
* New feature: Integration with the WordPress method of notification of new versions of the plugin.
2.0.0 Beta 1 [11-22-2007]:
* Initial Beta Release.
* New feature: Completely rewritten substructure
* New feature: Hierarchical categories.
* New feature: 100% WordPress API support, no more custom queries.