<?
/* 
Plugin Name: Working News Sitemap Generator For Google News (2014)  
Plugin URI: https://www.webmaster.net/plugins/
Version: v1.03
Author: webmaster-net, soliver, Chris Jinks
Description: Liteweight sitemap generator for Google News that is actually working and easy to use.  


Installation:
==============================================================================
* 1. Upload `news-sitemap-generator-2014` directory to the `/wp-content/plugins/` directory
* 2. Activate the plugin through the 'Plugins' menu in WordPress
* 3. Move the file "google-news-sitemap.xml" to the root directory e.g. public_html and open a SSH terminal. CD into the directory and run chown nobody:nobody google-news-sitemap.xml where nobodoy MAY have to be replaced with your Apache username on certain machines
* 4. Publish a test post
 
Release History:
==============================================================================
	2014-11-04		v1.00		First Re-Release: Usability Improvements
    2014-11-05		v1.02		Added Publication Name, Publication Language
    2014-11-05		v1.03		Added Stock Ticker
  
 
*/

/*  Modifications and Usability Improvements:
    By Oliver Krautscheid - http://www.webmaster.net
	
	Original concept: 
    David Stansbury - http://www.kb3kai.com/david_stansbury/
    Chris Jinks - http://www.southcoastwebsites.co.uk

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
// create custom plugin settings menu

/*
add_action('admin_menu', 'sitemap_create_menu');
function sitemap_create_menu()
{
    //create new top-level menu
    add_menu_page('News Sitemap Settings', 'Webmaster.Net News Sitemap', 'administrator', 'sitemap_main_menu', 'sitemap_settings_page', plugins_url('/images/icon.png', __FILE__));
    add_submenu_page('sitemap_main_menu', 'Sitemap Setting', 'Settings', 'administrator', 'sitemap-settings', 'sitemap_settings_function');
    //call register settings function 
    add_action('admin_init', 'register_mysettings'); 
}
 

function sitemap_settings_function()
{
    include("inc/functions.php"); 
}*/

function get_category_keywords($newsID)
{
	global $wpdb;
	
	//Check for new >2.3 Wordpress taxonomy	
	if (function_exists("get_taxonomy") && function_exists("get_terms"))
		{
			//Get categoy names
			$categories = $wpdb->get_results("
					SELECT $wpdb->terms.name FROM $wpdb->term_relationships,  $wpdb->term_taxonomy,  $wpdb->terms
					WHERE $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
					AND $wpdb->term_taxonomy.term_id =  $wpdb->terms.term_id
					AND $wpdb->term_relationships.object_id = $newsID
					AND $wpdb->term_taxonomy.taxonomy = 'category'");
				$i = 0;
				$categoryKeywords = "";
				foreach ($categories as $category)
				{
					if ($i>0){$categoryKeywords.= ", ";} //Comma seperator
					$categoryKeywords.= $category->name; //ammed string
					$i++;
				}
				
			//Get tags				
			$tags = $wpdb->get_results("
					SELECT $wpdb->terms.name FROM $wpdb->term_relationships,  $wpdb->term_taxonomy,  $wpdb->terms
					WHERE $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
					AND $wpdb->term_taxonomy.term_id =  $wpdb->terms.term_id
					AND $wpdb->term_relationships.object_id = $newsID
					AND $wpdb->term_taxonomy.taxonomy = 'post_tag'");
				$i = 0;
				$tagKeywords = "";
				foreach ($tags as $tag)
				{
					if ($i>0){$tagKeywords.= ", ";} //Comma seperator
					$tagKeywords.= $tag->name; //ammed string
					$i++;
				}
				

		}
		
	 
	
	if (get_option('googlenewssitemap_tagkeywords') == 'on')
	{
		if($tagKeywords!=NULL)
		{
			$categoryKeywords = $categoryKeywords.', '.$tagKeywords; //IF tags are included 
		}
	} 
	
	 return $categoryKeywords; //Return post category names as keywords
}

function write_google_news_sitemap() 
{

	global $wpdb;
	// Fetch options from database
	$permalink_structure = $wpdb->get_var("SELECT option_value FROM $wpdb->options 
					WHERE option_name='permalink_structure'");
	$siteurl = $wpdb->get_var("SELECT option_value FROM $wpdb->options
				WHERE option_name='siteurl'");

	// Output XML header
	
	
	// Begin urlset			
	$xmlOutput.= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:n=\"http://www.google.com/schemas/sitemap-news/0.9\">\n";
	
	
	//Credit
	$xmlOutput.= "<!-- Generated by Working News Sitemap Generator -->\n";
	$xmlOutput.= "<!-- Maintained by Oliver Krautscheid - wwww.webmaster.net -->\n";
	$xmlOutput.= "<!-- http://wordpress.org/extend/plugins/google-news-sitemap-generator/ -->\n";
	
	//Show either Posts or Pages or Both
	if (get_option('googlenewssitemap_includePages') == 'on' && get_option('googlenewssitemap_includePosts') == 'on')
		$includeMe = 'AND (post_type="page" OR post_type = "post")';
	
	elseif (get_option('googlenewssitemap_includePages') == 'on')
		$includeMe = 'AND post_type="page"';
	
	elseif (get_option('googlenewssitemap_includePosts') == 'on')
		$includeMe = 'AND post_type="post"';
	
	//Specify Publication Name
	if (get_option('googlenewssitemap_pubName')<>NULL)
	{
		$pubName = get_option('googlenewssitemap_pubName'); 
	}
    
    //Specify Publication Language
	if (get_option('googlenewssitemap_pubLang')<>NULL)
	{
		$pubLang = get_option('googlenewssitemap_pubLang'); 
	}
    
    //Specify Tickers
	if (get_option('googlenewssitemap_stock')<>NULL)
	{
		$stock = get_option('googlenewssitemap_stock'); 
	}
    
	//Include only certain categories	
	if (get_option('googlenewssitemap_includeCat')<>NULL)
	{
		$incPosts = get_objects_in_term(get_option('googlenewssitemap_includeCat'),"category");
		$includeMe.= ' AND ID IN ('.implode(",",$incPosts).')';
	}
	
	//Limit to last 2 days, 50,000 items					
	$rows = $wpdb->get_results("SELECT ID, post_date_gmt, post_title
						FROM $wpdb->posts 
						WHERE post_status='publish' 
						AND (DATEDIFF(CURDATE(), post_date_gmt)<=2)
						$includeMe
						ORDER BY post_date_gmt DESC
						LIMIT 0, 50000");	
										
	
	// Output sitemap data
	foreach($rows as $row){
		$xmlOutput.= "\t<url>\n";
		$xmlOutput.= "\t\t<loc>";
		$xmlOutput.= get_permalink($row->ID);
		$xmlOutput.= "</loc>\n";
		$xmlOutput.= "\t\t<n:news>\n"; 
		$xmlOutput.= "\t\t\t<n:publication>\n";
		$xmlOutput.= "\t\t\t\t<n:name>";
		$xmlOutput.= $pubName;
		$xmlOutput.= "</n:name>\n"; 
        $xmlOutput.= "\t\t\t\t<n:language>";
        $xmlOutput.= $pubLang;
        $xmlOutput .= "</n:language>\n"; 
		$xmlOutput.= "\t\t\t</n:publication>\n"; 
		$xmlOutput.= "\t\t\t<n:publication_date>";
		$thedate = substr($row->post_date_gmt, 0, 10);
		$xmlOutput.= $thedate;
		$xmlOutput.= "</n:publication_date>\n";
		$xmlOutput.= "\t\t\t<n:title>";
		$xmlOutput.= htmlspecialchars($row->post_title);
		$xmlOutput.= "</n:title>\n";
		$xmlOutput.= "\t\t\t<n:keywords>"; 
		//Use the categories for keywords
		$xmlOutput.= get_category_keywords($row->ID); 
		$xmlOutput.= "</n:keywords>\n";
        
        $xmlOutput.= "\t\t\t<n:stock_tickers>";
        $xmlOutput.= $stock;
		$xmlOutput.= "</n:stock_tickers>\n"; 
        $xmlOutput.= "\t\t\t<n:genres>"; 
		$xmlOutput.= htmlspecialchars('Blog');
        $xmlOutput.= "</n:genres>\n";
        
		$xmlOutput.= "\t\t</n:news>\n";
		$xmlOutput.= "\t</url>\n";
	}
	
	// End urlset
	$xmlOutput.= "</urlset>\n";
	$xmlOutput.= "<!-- Last build time: ".date("F j, Y, g:i a")."-->";
	
	$xmlFile = "../google-news-sitemap.xml";
	$fp = fopen($xmlFile, "w+"); // open the cache file "google-news-sitemap.xml" for writing
	fwrite($fp, $xmlOutput); // save the contents of output buffer to the file
	fclose($fp); // close the file
}


if(function_exists('add_action')) //Stop error when directly accessing the PHP file
{
	add_action('publish_post', 'write_google_news_sitemap');
	add_action('save_post', 'write_google_news_sitemap');
	add_action('delete_post', 'write_google_news_sitemap');
	add_action('transition_post_status', 'write_google_news_sitemap',10, 3); //Future scheduled post action fix
	
	//Any changes to the settings are executed on change
	add_action('update_option_googlenewssitemap_includePosts', 'write_google_news_sitemap', 10, 2);
	add_action('update_option_googlenewssitemap_includePages', 'write_google_news_sitemap', 10, 2);
	add_action('update_option_googlenewssitemap_tagkeywords', 'write_google_news_sitemap', 10, 2);
	add_action('update_option_googlenewssitemap_excludeCat', 'write_google_news_sitemap', 10, 2);
}
else  //Friendly error message :)
{
	?>
	<p style="color:#FF0000"><em>Accessing this file directly will not generate the sitemap.</em></p>
	<p>The sitemap will be generated automatically when you save/pubish/delete a post from the standard Wordpress interface.</p>
	<p><strong>Instructions</strong></p>
	<p>1. Upload `news-sitemap-generator-2014` directory to the `/wp-content/plugins/` directory<br />
	2. Activate the plugin through the 'Plugins' menu in WordPress<br />
	3. Move the file "google-news-sitemap.xml" to the root directory e.g. public_html and open a SSH terminal. CD into the directory and run chown nobody:nobody google-news-sitemap.xml where nobodoy MAY have to be replaced with your Apache username on certain machines<br />
	4. Save/publish/delete a post to generate the sitemap</p>
	<?
}
//
// Admin panel options.... //
//

add_action('admin_menu', 'show_googlenewssitemap_options');

function show_googlenewssitemap_options() {
    // Add a new submenu under Options:
    add_options_page('Google News Sitemap Generator Plugin Options', 'Google News Sitemap', 8, 'googlenewssitemap', 'googlenewssitemap_options');
	
	
	//Add options for plugin
	add_option('googlenewssitemap_includePosts', 'on');
	add_option('googlenewssitemap_includePages', 'off');
	add_option('googlenewssitemap_tagkeywords', 'off');
	add_option('googlenewssitemap_includeCat', '');
	
}
//
// Admin page HTML //
//
function googlenewssitemap_options() { ?>
<style type="text/css">
div.headerWrap { background-color:#e4f2fds; width:200px}
#options h3 { padding:7px; padding-top:10px; margin:0px; cursor:auto }
#options label { width: 300px; float: left; margin-left: 10px; }
#options input { float: left; margin-left:10px}
#options p { clear: both; padding-bottom:10px; }
#options .postbox { margin:0px 0px 10px 0px; padding:0px; }
</style>
<div class="wrap">
<form method="post" action="options.php" id="options">
<?php wp_nonce_field('update-options') ?>
<h2>New Sitemaps</h2>


<div class="postbox">
<h3 class="hndle">About Webmaster.Net</h3>
 
<p>
Webmaster .Net is a leading resource for webmaster tutorials, industry news, whois tools and includes a small community forum looking for members just like you!
</p><p>
<strong>If you like this plugin <a href="https://www.webmaster.net">Visit Us Today</a> - a bookmark is much appreciated!</strong>
</p>
 
</div>



<div class="postbox">
<h3 class="hndle">Sitemap contents</h3>

		<p>
			<?php
				if (get_option('googlenewssitemap_includePosts') == 'on') {echo '<input type="checkbox" name="googlenewssitemap_includePosts" checked="yes" />';}
				else {echo '<input type="checkbox" name="googlenewssitemap_includePosts" />';}
			?>
			<label>Include posts in Google News sitemap <small>(Default)</small></label>
		</p>
		<p>
			<?php
				if (get_option('googlenewssitemap_includePages') == 'on') {echo '<input type="checkbox" name="googlenewssitemap_includePages" checked="yes" />';}
				else {echo '<input type="checkbox" name="googlenewssitemap_includePages" />';}
			?>
			<label>Include pages in Google News sitemap</label>
		</p>
        
        
        <p>
			<?php $pubName = get_option('googlenewssitemap_pubName'); 
				if (get_option('googlenewssitemap_pubName')<>NULL) {echo '<input type="text" name="googlenewssitemap_pubName" value="'.$pubName.'" />';}
				else {echo '<input type="text" name="googlenewssitemap_pubName" value=""/>';}
			?> 
			<label>Publication Name</label>
		</p>
        
        
        <p>
			<?php $pubLang = get_option('googlenewssitemap_pubLang'); 
				if (get_option('googlenewssitemap_pubLang')<>NULL) {echo '<input type="text" name="googlenewssitemap_pubLang" value="'.$pubLang.'" />';}
				else {echo '<input type="text" name="googlenewssitemap_pubLang" value=""/>';}
			?> 
			<label>Publication Language (Enter Shortcode e.g. en,hi,is,it,de - <a href="https://sites.google.com/site/tomihasa/google-language-codes">Full Code List Here</a> </label>
		</p>
        
        
          <p>
			<?php $stock = get_option('googlenewssitemap_stock'); 
				if (get_option('googlenewssitemap_stock')<>NULL) {echo '<input type="text" name="googlenewssitemap_stock" value="'.$stock.'" />';}
				else {echo '<input type="text" name="googlenewssitemap_stock" value=""/>';}
			?> 
			<label>Related Stock Ticker (YHOO,GOO,APPL) - Use the format NASDAQ:A including the index</label>
		</p>
        
        
<br style="clear:both"/>			
</div>
		
<div class="postbox">
<h3 class="hndle">Sitemap keywords</h3>
		<p>
			<?php
				if (get_option('googlenewssitemap_tagkeywords') == 'on') {echo '<input type="checkbox" name="googlenewssitemap_tagkeywords" checked="yes" />';}
				else {echo '<input type="checkbox" name="googlenewssitemap_tagkeywords" />';}
			?>
			<label>Use post tags as sitemap keywords <small><a target="_blank" href="http://www.google.com/support/news_pub/bin/answer.py?answer=74288&topic=11666" style="text-decoration:none" target="_blank">More Info</a></small></label>
		</p>
<br style="clear:both"/>		
</div>


<div class="postbox">
<h3 class="hndle">Include categories</h3>

<div style="padding:10px">Select the categories you would like to <em><strong>include</strong></em> from the Google News Sitemap:</div>

<div style="padding:10px">
<?php
  //Categories to include from sitemap
  $includedCats = get_option('googlenewssitemap_includeCat');
  if (!is_array($includedCats)) 
  $includedCats= array(); 
  $categories = get_categories('hide_empty=1');
  foreach ($categories as $cat) {
  	if (in_array($cat->cat_ID,$includedCats))
	{
  		echo '<label class="selectit"><input type="checkbox" name="googlenewssitemap_includeCat[\''.$cat->cat_ID.'\']" value="'.$cat->cat_ID.'" checked="yes" /><span style="padding-left:5px">'.$cat->cat_name.'</span></label>';
  	}
	else
	{
		echo '<label class="selectit"><input type="checkbox" name="googlenewssitemap_includeCat[\''.$cat->cat_ID.'\']" value="'.$cat->cat_ID.'" /><span style="padding-left:5px">'.$cat->cat_name.'</span></label>';
	}
  }
?>
<br style="clear:both"/>
</div>

</div>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="googlenewssitemap_includePosts,googlenewssitemap_includePages,googlenewssitemap_tagkeywords,googlenewssitemap_includeCat,googlenewssitemap_pubName,googlenewssitemap_pubLang,googlenewssitemap_stock" />
		<div style="clear:both;padding-top:0px;"></div>
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /></p>
		<div style="clear:both;padding-top:20px;"></div>
		</form>
			
</div>

<?php } ?>