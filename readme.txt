=== Working News Sitemap Generator For Google News (2015)  ===
Contributors: soliver, webmaster-net, Chris Jinks 
Donate link: http://webmaster.net/plugins/
Tags: google news, news sitemap, xml sitemap, opinion piece, op/ed, google news sitemap, sitemap generator
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 1.03
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Liteweight sitemap generator for Google News that is actually working and easier to use than any of the existing plugins. 

== Description == 

I looked for a plugin the other day to generate a proper Google News sitemap. Unfortunately, most of them were either not working, premium plugins or too bloated. 
After looking for a good while and testing a handful of plugins, I set out to make a plugin that is compatible with recent Google News additions, including the possibility to specify many parameters manually.  
 
I hope to maintain this plugin throughout 2014 and 2015 and add a few more features. If there are any bugs please report them so they can be fixed with the next release.  

= Some of the new features =

* Provide publication name manually - if you don't want it to be your Homepage name
* Provide site language using Google's language codes
* Provide related stock tickers manually
* Rather than excluding categories, this plugin allows you to include categories only


= Why You Should Include Only A Few Categories =

Google is very picky about what sites they include. If you create a sitemap that is fetching content from your entire site, you won't get accepted. 

It is a far better idea to create a "News" and "Opinion Piece" category


= What features will you add in the next version =

Next on the list are additional genres: Opinion Piece, Op/Ed and other genres accepted by Google.

  
= How do I avoid my blog from dropping out of Google News =

For more info, check out the following article:

* [Avoiding Google News Pitfalls] (https://www.webmaster.net/one-surefire-way-of-blocking-your-news-posts-from-getting-indexed-into-google-news-from-your-wp-blog/). 
* [Google News Sitemap Guidelines](https://support.google.com/news/publisher/answer/74288?hl=en).


== Installation ==

This will help you to correctly install the plugin

 1. Upload `news-sitemap-generator-2014` directory to the `/wp-content/plugins/` directory
 2. Activate the plugin through the 'Plugins' menu in WordPress
 3. Move the file "google-news-sitemap.xml" to the root directory e.g. public_html and open a SSH terminal. CD into the directory and run chown nobody:nobody google-news-sitemap.xml where nobodoy MAY have to be replaced with your Apache username on certain machines
 4. Publish a test post

== Frequently Asked Questions ==

No questions yet - send questions to contact@webmaster.net

If you need support, please go to our new product support forums at https://forums.webmaster.net/#product-support-forums.66
 
 

== Screenshots ==

1. Add Publication Name and Language Manually 

== Changelog ==
 
= 1.03 =
* Added Stock Ticker

= 1.02 =
* Added Publication Name, Publication Language

= 1.0 =
* First Re-Release: Usability Improvements

 
