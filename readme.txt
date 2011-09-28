=== WP Nasa/ADS Query Importer ===
Contributors: giodimilia
Plugin URI: http://wordpress.org/extend/plugins/wp-nasaads-query-importer/
Author URI: http://adsabs.harvard.edu
Tags: astronomy, adsabs, ads, nasa, article, paper, bibcode, publication, wordpress, plugin
Requires at least: 3.2
Tested up to: 3.2.1
Stable tag: trunk

Import any ADS query you can perform on an ADS serve in your Wordpress website.


== Description ==

The SAO/NASA Astrophysics Data System (ADS) (http://adsabs.harvard.edu) is a Digital Library portal 
for researchers in Astronomy and Physics, operated by the Smithsonian Astrophysical Observatory (SAO) 
under a NASA grant. 
The ADS maintains three bibliographic databases containing more than 9 million records: 
Astronomy and Astrophysics, Physics, and arXiv e-prints.

"WP NASA/ADS Query Importer" is intended to import the list of papers generated from an ADS query into a Wordpress website.

With "WP NASA/ADS Query Importer" you can:

* Show any kind of ADS Query, including public "private libraries" (http://doc.adsabs.harvard.edu/abs_doc/help_pages/privlib.html#Private_Library).
* Customize the order and the visibility of the printed elemets.
* Customize where to show the link back to the full abstract of the article.
* Add a title to the query showed in the Wordpress page.
* Highlight an author in any single paper

Supported shortcodes:

* wp_nasaads_query_importer_full :only shortcode currently supported; it works together with some mandatory and optional attributes.

Mandatory attributes for shortcode "wp_nasaads_query_importer_full":

* ads_query_url: This is the attribute where the user specifies the url for the query to ADS

Optional attributes for shortcode "wp_nasaads_query_importer_full":

* title: The title the user wants to show above the results. There is no default value for this attribute
* highlight_author: String that contains the lastname of the author to highlight. There is no default value for this attribute
* max_num_authors: Max number of authors before printing "and other N coauthors". The default is 1
* max_records_to_print: Max number of papers to show. By default ALL abstracts will be printed
* omit_bibcode: boolean to omit the bibcode in the list of abstracts; the default is FALSE
* omit_authors: boolean to omit the authors in the list of abstracts; the default is FALSE
* omit_title: boolean to omit the title in the list of abstracts; the default is FALSE
* omit_journal: boolean to omit the journal in the list of abstracts; the default is FALSE
* omit_date: boolean to omit the date in the list of abstracts; the default is FALSE
* omit_link_to_ads: boolean to omit the link back to ADS in the list of abstracts; the default is FALSE
* link_on_field: field where to create a link back to ADS; the default is bibcode. Other values can be authors, title, journal, date
* print_order: string that indicates the order of the content of each abstract separated by a "|"; the default is bibcode|title|authors|journal|date; omitting a field here is equivalent to the relative omit_FIELD boolean set to TRUE	

Any attribute different from the previous ones will be ignored.
Any attribute set to a not allowed value will be re-set to the the default.

Settings:

* SAO/NASA ADS Server: Is the default server where to run the queries. 
  It can be overwritten by specifying a complete URL (containing the host) in the ads_query_url parameter, but it is necessary to create links back to the ADS website.
* Acknowledgement to ADS after list of papers: You can disable this option if you don't want the Acknowledgement string 
  containing also the link to the original query executed on ADS.

Note: Please ensure that your server is running PHP 5 or higher before installing. ( Wordpress 3.2 requires PHP5)

Example of usage #1:
[wp_nasaads_query_importer_full ads_query_url="http://adsabs.harvard.edu/cgi-bin/basic_connect?qsearch=galaxy"]
This query will print 200 records inside your page/post. All the default value for the other parameters will be used.
Since the ads_query_url contains a complete url, the value of the ADS Server specified in the Settings will be overwritten.

Example of usage #2:
[wp_nasaads_query_importer_full ads_query_url="/cgi-bin/basic_connect?qsearch=galaxy"]
This query will produce the same result of the Example #1, with the only difference being that the ADS server specified in the Settings will be used.

Example of usage #3:
[wp_nasaads_query_importer_full ads_query_url="/cgi-bin/basic_connect?qsearch=galaxy" title="Top publications about galaxies"]
This query will produce the same result of the previous example, but it will add a title before the list of papers.

Example of usage #4:
[wp_nasaads_query_importer_full ads_query_url="/cgi-bin/basic_connect?qsearch=galaxy" max_num_authors="20" highlight_author="Huchra" max_records_to_print="100"]
This query will produce a list of 100 papers (instead of the default 200 coming from the query), will show max 20 authors per paper (instead of 1) and will highlight the Author Huchra if it appears in the first 20 authors shown.

Example of usage #5:
[wp_nasaads_query_importer_full ads_query_url="/cgi-bin/basic_connect?qsearch=galaxy" omit_bibcode="true" link_on_field="title"]
This query will produce a list of 200 papers like the Example #2, but the bibcode will not be shown and the link to the abstract on the ADS website will be placed on the title.

Example of usage #6:
[wp_nasaads_query_importer_full ads_query_url="/cgi-bin/basic_connect?qsearch=galaxy" print_order="title|authors|journal|date|bibcode"]
This query will produce the same result of the Example #2, but with the bibcode at the end of the paper in the list.

Note #2: A valid query can be any query to a "private library" or to more complex queries executed from the ADS advanced query form (http://adsabs.harvard.edu/abstract_service.html) .
In general, __ANY__ URL that in ADS produces a list of results can be used with this plugin.

== Installation ==

1. FTP the entire "WP NASA/ADS Query Importer" directory to your Wordpress blog's plugins folder (/wp-content/plugins/).

2. Activate the plugin on the "Plugins" tab of the administration panel.

3. Choose the options inside Settings -> "WP NASA/ADS Query Importer"

4. Insert all the shortcode you want in your pages/posts

5. Done


== Upgrade Notice ==
Generally, all you should have to do is click the Update button on the Plugins page when a new update becomes available.
If you customized the css stylesheet, be sure to have a backup before upgrading.


== Frequently Asked Questions ==

= An option I absolutely want is not present: how can I have it? =
If an option is not present in the current version of this plugin, please contact the developers: they will be happy to analyze your request.

== Screenshots ==

1. Settings and customization of ADS server and acknowledgements.
2. Use of the shortcode
3. Example of result with default parameters
4. Example of result with default parameters and title.
5. Example of result with customized parameters (hidden bibcode and link on title)
6. Example of result with customized parameters (order changed)

== Changelog ==

= 0.1 =
* First public release

