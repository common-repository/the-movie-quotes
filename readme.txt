=== The Movie Quotes ===
Contributors: TMQ
Tags: memorable, movies, quotes, movie, quote, sidebar, widget
Requires at least: 2.5.0
Tested up to: 2.7
Stable tag: 1.4

Movie Quotes widget will fetch the latest, random or top rated quotes from themoviequotes.com website, and display them on your blog.

== Description ==

Movie Quotes widget will fetch the latest, random or top rated quotes from [themoviequotes.com](http://www.themoviequotes.com) website, and display them on your blog.

You can select between:<br />
- Latest Quotes<br />
- Random Quotes<br />
- Top Rated Quotes (as rated by TheMovieQuotes.com users)

You can also set how many quotes you would like to show.

Movie Quotes widget uses in-built MagPie for reading data and HTTP Snoopy Client for fetching data from [themoviequotes.com](http://www.themoviequotes.com).

For less stress on the system, Movie Quotes widget does not use database, but it caches data to a file.

* [Widget Homepage:](http://www.themoviequotes.com/tools/wordpress "Widget Homepage")
* [Live Demo](http://blog.themoviequotes.com "Live Demo")

== Installation ==

1. Upload `the-movie-quotes` directory to the `/wp-content/plugins/` directory
2. Create `/wp-content/cache` folder if it doesn't exist and chmod it to 777
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to 'Design' -> 'Widgets' to add, and configure the widget

== Frequently Asked Questions ==
The [FAQ](http://www.themoviequotes.com/tools/wordpress) is available at the [themoviequotes.com](http://www.themoviequotes.com) Wordpress widget page, [click here](http://www.themoviequotes.com/tools/wordpress) to view it.

== Screenshots ==

1. Configuration interface of the widget
2. Quotes shown on the front page

== Changelog ==
* Version 1.4 (2008/12/11)
	* Fix: Works in Wordpress 2.7 now, Snoopy is getting old problems ;)
* Version 1.3 (2008/10/11)
	* Fix: If TheMovieQuotes.com website is down, it reads from cache instead of trying to get new quote
	* Added option to put movie title below the quote
	* Removed "vid=" from query string. No more version checks
* Version 1.2 (2008/08/04)
	* Added option to show only quotes with N lines
	* Added "vid=" to query string. "vid" = Version ID. If you are using non-compatible version of TMQ widget, it will show a notification
	* Fix: Removed "die();" when cache folder cannot be found. Error message is still shown, but it doesn't kill the whole blog. :)
	* Changed: Default setting changed from "latest" to "random" quotes
* Version 1.0 (2008/07/22)
	* Initial version