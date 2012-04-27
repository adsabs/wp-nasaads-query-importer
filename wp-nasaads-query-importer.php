<?php
/*
Plugin Name: WP NASA/ADS Query Importer
Version: 0.3
Description: Import any ADS query in your Wordpress website.
Author: Giovanni Di Milia for the ADS
Author URI: http://adsabs.harvard.edu
Plugin URI: http://wordpress.org/extend/plugins/wp-nasaads-query-importer/
 */

/*
 WP NASA/ADS Query Importer Wordpress Plugin
 Copyright (C) 2011, The SAO/NASA Astrophysics Data System

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//Check minimum Wordpress version
//I need at least the 3.2 because it was the first version that requires PHP 5.2.4 or greater
global $wp_version;
$exit_msg='WP NASA/ADS Query Importer requires Wordpress 3.2 or newer.
<a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';
if (version_compare($wp_version, "3.2", "<"))
{
	exit($exit_msg);
}

//Actual plugin
define(wp_nasaads_query_importer_path, WP_PLUGIN_URL . "/" . str_replace(basename( __FILE__), "", plugin_basename(__FILE__)));
define("wp_nasaads_query_importer_version", "0.3");
$plugin_dir = basename(dirname(__FILE__));

define("wp_nasaads_query_importer_admin_options_name", "wp_nasaads_query_importer_admin_options");
add_shortcode("wp_nasaads_query_importer_full", "wp_nasaads_query_importer_full");

//I add a filter for the "settings" link in the plugin list
add_filter('plugin_action_links', 'wp_nasaads_query_importer_add_plugin_links', 10, 2);

//function that extract the option saved
function wp_nasaads_query_importer_get_admin_options()
{
	return get_option(wp_nasaads_query_importer_admin_options_name);
}

//function that formats errors
function wp_nasaads_query_importer_format_error($str_to_print)
{
	return '<span class="wp-nasaads-query-importer-error">WP Nasa/ADS Query Importer: ERROR - '.$str_to_print.'</span>';
}

//function where I define the possible parameters I can accept inside the custom tags
function wp_nasaads_query_importer_tag_parameters()
{
	return array(
		'ads_query_url' => NULL, //this is the parameter where the user has to put the query URL to ADS; mandatory field
		'title' => NULL, //the title the user wants to show above the results; the default is NULL
		'highlight_author' => NULL, //string that contains the lastnames of the authors to highlight; the default is NULL
		'max_num_authors' => 1, //maximum number of authors before printing "et al"; the default is 1
		'max_records_to_print' => NULL, //maximum number of papers in the page: a link to the complete list in ADS will be added at the bottom; the default is NULL (ALL abstracts will be printed)
		'omit_bibcode' => FALSE, //boolean to omit the bibcode in the list of abstracts; the default is FALSE
		'omit_authors' => FALSE, //boolean to omit the authors in the list of abstracts; the default is FALSE
		'omit_title' => FALSE, //boolean to omit the title in the list of abstracts; the default is FALSE
		'omit_journal' => FALSE, //boolean to omit the journal in the list of abstracts; the default is FALSE
		'omit_date' => FALSE, //boolean to omit the date in the list of abstracts; the default is FALSE
		'omit_link_to_ads' => FALSE, //boolean to omit the link back to ADS in the list of abstracts; the default is FALSE
		'link_on_field' => 'bibcode', //field where to create a link back to ADS; the default is bibcode. Other values can be authors, title, journal, date
		'print_order' => 'bibcode|title|authors|journal|date', //string that indicates the order of the content of each abstract separated by a "|"; the default is bibcode|title|authors|journal|date; omitting a field here is equivalent to the relative omit_FIELD boolean set to TRUE	
		//at the end of the list there will be always an aknoledgement to ADS
		//any parameter different from the previous ones will be ignored
		//any parameter set to a not allowed value will be re-set to the the default
	);
}

//function that validates the value of a parameter
function wp_nasaads_query_importer_validate_param($param_name, $param_value)
{
	//I get the standard parameters to have the default values
	$default_parameters = wp_nasaads_query_importer_tag_parameters();
	//I define the valid value for each parameter
	$valid_params = array(
		'ads_query_url' => NULL, //NULL means that I skip the validation: in this case is important because the URL needs a complex check
		'title' => 'string',	//check if it is a simple string
		'highlight_author' => 'string',
		'max_num_authors' => 'int',	//check if the converted value to int is actually int
		'max_records_to_print' => 'int',
		'omit_bibcode' => 'bool', //check if the converted value to bool is actually bool
		'omit_authors' => 'bool',
		'omit_title' => 'bool',
		'omit_journal' => 'bool',
		'omit_date' => 'bool',
		'omit_link_to_ads' => 'bool',
		'link_on_field' => 'sigle_in_list_fields', //check in the value is in the list of possible values
		'print_order' => 'multiple_in_list_fields', //check if any of the values is in the list of possible values
	);
	$sigle_in_list_fields = array('bibcode', 'authors', 'title', 'journal', 'date');
	
	//then I validate the single values
	if ($valid_params[$param_name] == NULL)
		return $param_value;
	elseif ($valid_params[$param_name] == 'string')
	{
		if (is_string($param_value))
			return $param_value;
		else
			return $default_parameters[$param_name];
	}
	elseif ($valid_params[$param_name] == 'int')
	{
		$int_param_value = intval($param_value);
		if ($int_param_value > 0)
			return $int_param_value;
		else
			return $default_parameters[$param_name];
	}
	elseif ($valid_params[$param_name] == 'bool')
	{
		if (strtolower($param_value) == 'true')
			return TRUE;
		elseif (strtolower($param_value) == 'false')
			return FALSE;
		else 
			return $default_parameters[$param_name];
	}
	elseif ($valid_params[$param_name] == 'sigle_in_list_fields')
	{
		if (in_array($param_value, $sigle_in_list_fields))
			return $param_value;
		else 
			return $default_parameters[$param_name];
	}
	elseif ($valid_params[$param_name] == 'multiple_in_list_fields')
	{
		//in this case I have to see if each of the values is in the default value list
		$single_values = explode('|', $param_value);
		foreach ($single_values as $value)
		{
			//if any of the parameters in the list is not in the one, I return the default
			if (!in_array($value, $sigle_in_list_fields))
				return $default_parameters[$param_name];
		}
		return $param_value;
	}
}

//function that parses the parameters in input and create a complete list to return (so I don't have to make checks inline)
function wp_nasaads_query_importer_parse_parameters($params)
{
	if (is_array($params))
	{
		//I get a standard structure for the parameters I want to manage
		$all_params = wp_nasaads_query_importer_tag_parameters();
		//I extract all the keys that I can have
		$all_keys = array_keys($all_params);
		//for each parameter I check if there is a custom value and I validate it
		//if the value doesn't pass the validation, the default value is kept
		foreach ($all_keys as $param_name)
		{
			if (array_key_exists($param_name, $params))
				$all_params[$param_name] = wp_nasaads_query_importer_validate_param($param_name, $params[$param_name]);
		}
		return $all_params;
	}
	else 
		return FALSE;
}

//function that builds a valid URL to query ADS
function wp_nasaads_query_importer_buld_ads_url($query_url)
{
	//I re-extract the parameters
	$options = wp_nasaads_query_importer_get_admin_options();
	//and from the options the server
	$adsserver_base_url = $options["wp_nasaads_query_importer_url"];
	
	//I try to recognize if there is a complete URL or not
	//I split the url
	$parsed_query_url = parse_url($query_url);
	//if there is a scheme in the parsed_query_url, then I assume it is a complete url
	if($parsed_query_url && array_key_exists('scheme', $parsed_query_url))
	{
	}
	//otherwise I assume that I have to prepend the default URL
	else 
	{
		if (substr($query_url, 0, 1) == '/')
			$query_url = substr($query_url, 1);
		$query_url = $adsserver_base_url . $query_url;
	}
	return $query_url;
}

//function that extracts the XML for the ADS query
function wp_nasaads_query_importer_get_xml($query_url)
{
	//I format the query to ADS
	$query_url = wp_nasaads_query_importer_buld_ads_url($query_url);
	
	//then I append "&data_type=XML" if it is not in the URL
	if (strpos($query_url, '?'))
		$query_url = $query_url . '&data_type=XML&type=XML';
	else
		$query_url = $query_url . '?data_type=XML&type=XML';
	//then I fetch the XML
	$adsxml = wp_remote_get(html_entity_decode($query_url), 
							array("user-agent"=>"Mozilla/5.0 (WP NASA/ADS Query Importer - WordPress Plugin)", "timeout"=>60));
	//and I check that the request was successful (if I get XML I was)
	if (is_array($adsxml) && strpos($adsxml['headers']['content-type'], 'text/xml') === 0 )
		return $adsxml['body'];
	//otherwise I return null
	else
		return FALSE;
}

//function that extracts the info from the XML and put them in an array
function wp_nasaads_query_importer_data_from_xml($dom_doc) 
{
	//create a dom object to manage the XML
	$doc = new DOMDocument();
	if (!$doc->loadXML($dom_doc))
		return FALSE;
	//I create the array to return the results with the default values already set
	$ret_content = array(
		'records_selected' => 0,
		'records_retrieved' => 0,
		'records' => array()
	);
	
	//I create an XPATH object and register a default namespace
	$xpath = new DOMXPath($doc);
	$xpath->registerNamespace('pre', "http://ads.harvard.edu/schema/abs/1.1/abstracts");
	//I extract the basic info (selected and retrieved)
	$retrieved_node = $xpath->evaluate('/pre:records/@retrieved');
	$selected_node = $xpath->evaluate('/pre:records/@selected');
	$ret_content['records_selected'] = intval($selected_node->item(0)->nodeValue);
	$ret_content['records_retrieved'] = intval($retrieved_node->item(0)->nodeValue);
	
	//then I extract the single records
	$records = $xpath->evaluate('/pre:records/pre:record');
	for ($i=0;$i<$records->length;$i++)
	{
		//I set a new variable to the basic structure
		$cur_record = array(
			'bibcode' => '',
			'title' => '',
			'journal' => '',
			'date' => '',
			'authors' => array(),
		); 
		
		//I extract the bibcode
		$bibcode_node = $xpath->evaluate('pre:bibcode', $records->item($i));
		$cur_record['bibcode'] = $bibcode_node->item(0)->nodeValue;
		//Title
		$title_node = $xpath->evaluate('pre:title', $records->item($i));
		if($title_node->length > 0)
			$cur_record['title'] = $title_node->item(0)->nodeValue;
		//Journal
		$journal_node = $xpath->evaluate('pre:journal', $records->item($i));
		if($journal_node->length > 0)
			$cur_record['journal'] = $journal_node->item(0)->nodeValue;
		//Publication date
		$pubdate_node = $xpath->evaluate('pre:pubdate', $records->item($i));
		if($pubdate_node->length > 0)
		{
			$cur_pubdate = $pubdate_node->item(0)->nodeValue;
			if(substr($cur_pubdate, 0, 4) == 'n/a ')
				$cur_pubdate = substr($cur_pubdate, 4);
			$cur_record['date'] = $cur_pubdate;
		}
		//Authors
		$authors_node = $xpath->evaluate('pre:author', $records->item($i));
		for ($j=0;$j<$authors_node->length;$j++)
		{
			$cur_author = $authors_node->item($j)->nodeValue;
			array_push($cur_record['authors'], $cur_author);
		}
		
		//finally I copy the current record inside the global array
		array_push($ret_content['records'], $cur_record);
	}
	
	unset($doc);
	
	return $ret_content;
}

//function that extracts a full list from an adsquery
function wp_nasaads_query_importer_full($params_from_tag) 
{
	wp_register_style("wp_nasaads_query_importer", wp_nasaads_query_importer_path . "style.css", false, wp_nasaads_query_importer_version, "all");
	wp_print_styles("wp_nasaads_query_importer");
	
	//I parse the parameters
	$params = wp_nasaads_query_importer_parse_parameters($params_from_tag);
	
	//First of all I check if there is the query parameter
	if ($params['ads_query_url'] !== NULL)
	{
		//I extract the XML for the given query
		$adsxml = wp_nasaads_query_importer_get_xml($params['ads_query_url']);
		//I check I got null from the XML extraction
		//In that case I return an error
		if ($adsxml === FALSE)
		{
			$error_message = 'The query specified is not valid in ADS.<br/>
			Query: &quot;'.$params['ads_query_url'].'&quot;<br/>
			Please correct it.
			';
			return wp_nasaads_query_importer_format_error($error_message);
		}
		//otherwise I parse the results
		else
		{
			//I re-extract the parameters
			$options = wp_nasaads_query_importer_get_admin_options();
			//and from the options the server
			$adsserver_base_url = $options["wp_nasaads_query_importer_url"];
			//I convert the XML in an useful array
			$xml_content = wp_nasaads_query_importer_data_from_xml($adsxml);
			//then I extract the list of stuff to print
			$params_list = explode("|", $params['print_order']);
			//for each element if there is not an omit set, I call the related function to format the content
			
			$html_content = '';
			//first I insert the title if there is one
			if ($params['title'] !== NULL)
				$html_content .= '<span class="wp-nasaads-query-importer-result-list-title">'.$params['title'].'</span>'."\n";
			
			//If i retrieved 0 records I don't have to do anything
			if ($xml_content['records_retrieved'] == 0)
				$html_content .= '<h3>No record selected with the specified ADS query.</h3>'."\n";
			else 
			{
				//otherwise I start to process all the records
								
				$records = $xml_content['records'];
				if ($params['max_records_to_print'] !== NULL)
				{
					if ($xml_content['records_retrieved'] > $params['max_records_to_print'])
						$records = array_slice($records, 0, $params['max_records_to_print']);
				}
				//then I assemble the HTML according with the order I have
				$html_content .= '<ul class="wp-nasaads-query-importer-result-list">'."\n";
				foreach ($records as $record)
				{
					
					//bibcode
					$bibcode_formatted_html = '';
					if (in_array('bibcode', $params_list))
					{
						if ($params['omit_bibcode'] !== TRUE)
							$bibcode_formatted_html = wp_nasaads_query_importer_format_bibcode($record['bibcode']);
					}
					//title
					$title_formatted_html = '';
					if (in_array('title', $params_list))
					{
						if ($params['omit_title'] !== TRUE)
							$title_formatted_html = wp_nasaads_query_importer_format_title($record['title']);
					}
					//journal
					$journal_formatted_html = '';
					if (in_array('journal', $params_list))
					{
						if ($params['omit_journal'] !== TRUE)
							$journal_formatted_html = wp_nasaads_query_importer_format_journal($record['journal']);
					}
					//date
					$date_formatted_html = '';
					if (in_array('date', $params_list))
					{
						if ($params['omit_date'] !== TRUE)
							$date_formatted_html = wp_nasaads_query_importer_format_date($record['date']);
					}
					//authors
					$authors_formatted_html = '';
					if (in_array('authors', $params_list))
					{
						if ($params['omit_authors'] !== TRUE)
							$authors_formatted_html = wp_nasaads_query_importer_format_authors($record['authors'], $params['max_num_authors'], $params['highlight_author']);
					}
					//Link back to ADS
					$link_to_ADS = $adsserver_base_url.'abs/'.urlencode($record['bibcode']);
									
					$html_content .= '<li>';
					foreach ($params_list as $param_string)
					{
						if ($param_string == 'bibcode')
						{
							if (($params['link_on_field'] == 'bibcode') && !$params['omit_link_to_ads'])
								$html_content .= wp_nasaads_query_importer_format_link_to_ads($bibcode_formatted_html, $link_to_ADS);
							else
								$html_content .= $bibcode_formatted_html;
						}
						elseif ($param_string == 'title')
						{
							if (($params['link_on_field'] == 'title') && !$params['omit_link_to_ads'])
								$html_content .= wp_nasaads_query_importer_format_link_to_ads($title_formatted_html, $link_to_ADS);
							else
								$html_content .= $title_formatted_html;
						}
						elseif ($param_string == 'journal')
						{
							if (($params['link_on_field'] == 'journal') && !$params['omit_link_to_ads'])
								$html_content .= wp_nasaads_query_importer_format_link_to_ads($journal_formatted_html, $link_to_ADS);
							else
								$html_content .= $journal_formatted_html;
						}
						elseif ($param_string == 'date')
						{
							if (($params['link_on_field'] == 'date') && !$params['omit_link_to_ads'])
								$html_content .= wp_nasaads_query_importer_format_link_to_ads($date_formatted_html, $link_to_ADS);
							else
								$html_content .= $date_formatted_html;
						}
						elseif ($param_string == 'authors')
						{
							if (($params['link_on_field'] == 'authors') && !$params['omit_link_to_ads'])
								$html_content .= wp_nasaads_query_importer_format_link_to_ads($authors_formatted_html, $link_to_ADS);
							else
								$html_content .= $authors_formatted_html;
						}
					}	
					$html_content .= '</li>'."\n";
				}
				$html_content .= '</ul>'."\n";
			}
			
			//Other not printed records, if present.
			if ($params['max_records_to_print'] !== NULL)
			{
				if ($xml_content['records_retrieved'] > $params['max_records_to_print'])
					$html_content .= '<p class="wp-nasaads-query-importer-result-other-records-not-printed">Other '. 
						($xml_content['records_retrieved'] - $params['max_records_to_print']) .' records retrieved but not showed.</p>'."\n";
			}
				
			
			//Acknowledgement to ADS
			if($options["wp_nasaads_query_importer_acknowledgement"] == 'enabled')
				$html_content .= '<p class="wp-nasaads-query-importer-acknowledgement">
								The original query on ADS is available 
								<a href="'.wp_nasaads_query_importer_buld_ads_url($params['ads_query_url']).'" target="_blank">HERE</a>.<br/>
								Service offered by 
								<a href="http://adsabs.harvard.edu/" target="_blank">The SAO/NASA Astrophysics Data System</a>. 
								</p>';
			
			//I return the final string
			return $html_content;	
		}	
	}
	//if there is no query parameter I don't have to print anything an I return an error
	else
		return wp_nasaads_query_importer_format_error('No query parameter specified!');
}


/////////////
//Formatter functions

//Bibcode formatter
function wp_nasaads_query_importer_format_bibcode($bibcode_str)
{
	return '<span class="wp-nasaads-query-importer-result-list-bibcode">'.$bibcode_str.'</span>'."\n";
}
//Title formatter
function wp_nasaads_query_importer_format_title($title_str)
{
	return '<span class="wp-nasaads-query-importer-result-list-article-title">'.$title_str.'</span>'."\n";
}
//Journal formatter
function wp_nasaads_query_importer_format_journal($journal_str)
{
	return '<span class="wp-nasaads-query-importer-result-list-journal">'.$journal_str.'</span>'."\n";
}
//Date formatter
function wp_nasaads_query_importer_format_date($date_str)
{
	return '<span class="wp-nasaads-query-importer-result-list-date">'.$date_str.'</span>'."\n";
}
//Authors formatter
function wp_nasaads_query_importer_format_authors($authors_list, $max_num_authors, $highlight_author_str)
{
	$auth_html_str = '<span class="wp-nasaads-query-importer-result-list-authors">';
	//check if it's bigger the number of authors or the $max_num_authors
	if (count($authors_list) >= $max_num_authors)
		$num_auth_print = $max_num_authors;
	else 
		$num_auth_print = count($authors_list);
	
	for ($i=0; $i<$num_auth_print; $i++)
	{
		//I highlith the author if necessary
		if (($highlight_author_str !== NULL) && (strtolower(substr($authors_list[$i], 0, strlen($highlight_author_str))) == strtolower($highlight_author_str)))
		{
			$auth_html_str .= '<span class="wp-nasaads-query-importer-result-list-highlithed-author">'.$authors_list[$i].'</span>; ';
		}
		else
			$auth_html_str .= $authors_list[$i].'; ';
		
	}
	
	if (count($authors_list) > $max_num_authors)
		$auth_html_str .= '<span class="wp-nasaads-query-importer-result-list-other-authors">and '.(count($authors_list) - $max_num_authors).' coauthors</span>';
	
	$auth_html_str .= '</span>'."\n";
	return  $auth_html_str;
}
//Back link to ADS formatter
function wp_nasaads_query_importer_format_link_to_ads($html_string, $url)
{
	return '<a target="_blank" href="'.$url.'" class="wp-nasaads-query-importer-result-link-to-ads">'.$html_string.'</a>';
}

/////////////
//Admin part

/*** Plugins Menu Additions ***/
function wp_nasaads_query_importer_add_plugin_links($links, $file) {
	static $this_plugin;
	(!$this_plugin) ? $this_plugin = plugin_basename(__FILE__) : $this_plugin = $this_plugin;
	if ($file == $this_plugin) {
		$settings_link = '<a href="options-general.php?page=wp-nasaads-query-importer">Settings</a>';
		array_push($links, $settings_link);
	}
	return $links;
}


function wp_nasaads_query_importer_display_admin_page()
{
	//Part where I update the value
	$updated_server = false;
	if (isset($_REQUEST['wp_nasaads_query_importer_url']) || isset($_REQUEST['wp_nasaads_query_importer_acknowledgement'])) 
	{
		$options = array();
		$options["wp_nasaads_query_importer_url"] = $_REQUEST["wp_nasaads_query_importer_url"];
		$options["wp_nasaads_query_importer_acknowledgement"] = $_REQUEST["wp_nasaads_query_importer_acknowledgement"];
		update_option(wp_nasaads_query_importer_admin_options_name, $options);
		$updated_server = true;
	}
	//I re-extract the parameters
	$options = wp_nasaads_query_importer_get_admin_options();
	//I set default values if there is nothing in the database and I save them
	if(!$options)
	{
		$options = array();
		$options["wp_nasaads_query_importer_url"] = 'http://adsabs.harvard.edu/';
		$options["wp_nasaads_query_importer_acknowledgement"] = 'enabled';
		update_option(wp_nasaads_query_importer_admin_options_name, $options);
		$updated_server = true;
	}
	
	//I put the value in a shorter variable
	$adsserver = $options["wp_nasaads_query_importer_url"];
	$ackno = $options["wp_nasaads_query_importer_acknowledgement"];
		
	//Variable that contains the html code when there is an update
	$code_for_updated_server = '
		<div class="updated">
			<p>Settings <span style="font-weight:bold;">successfully updated!<span></p>
		</div>';
	
	echo ($updated_server?$code_for_updated_server:"").'
<div class="wrap">
	<h2>WP NASA/ADS Query Importer</h2>
	<br />
	<form method="post" action="'.$_SERVER["REQUEST_URI"].'">
		<h3>SAO/NASA ADS Server</h3>
		The server where you want to execute your queries.<br />
		Please note that you can override this value specifying a complete URL in the the &quot;ads_query_url&quot; parameter in the single tags,<br/>
		but this value will be still used to create links back to ADS.
		<br /><br />
		<select name="wp_nasaads_query_importer_url">
			<option value="http://adsabs.harvard.edu/" '.(($adsserver=="http://adsabs.harvard.edu/")?"selected=\"selected\"":"") .'>http://adsabs.harvard.edu/ - Harvard-Smithsonian Center for Astrophysics, Cambridge, USA</option>
			<option value="http://cdsads.u-strasbg.fr/" '.(($adsserver=="http://cdsads.u-strasbg.fr/")?"selected=\"selected\"":"") .'>http://cdsads.u-strasbg.fr/ - Centre de Donn&eacute;es astronomiques de Strasbourg, France</option>
			<option value="http://ukads.nottingham.ac.uk/" '.(($adsserver=="http://ukads.nottingham.ac.uk/")?"selected=\"selected\"":"") .'>http://ukads.nottingham.ac.uk/ - University of Nottingham, United Kingdom</option>
			<option value="http://esoads.eso.org/" '.(($adsserver=="http://esoads.eso.org/")?"selected=\"selected\"":"") .'>http://esoads.eso.org/ - European Southern Observatory, Garching, Germany</option>
			<option value="http://ads.ari.uni-heidelberg.de/" '.(($adsserver=="http://ads.ari.uni-heidelberg.de/")?"selected=\"selected\"":"") .'>http://ads.ari.uni-heidelberg.de/ - Astronomisches Rechen-Institut, Heidelberg, Germany</option>
			<option value="http://ads.inasan.ru/" '.(($adsserver=="http://ads.inasan.ru/")?"selected=\"selected\"":"") .'>http://ads.inasan.ru/ - Institute of Astronomy of the Russian Academy of Sciences, Moscow, Russia</option>
			<option value="http://ads.mao.kiev.ua/" '.(($adsserver=="http://ads.mao.kiev.ua/")?"selected=\"selected\"":"") .'>http://ads.mao.kiev.ua/ - Main Astronomical Observatory, Kiev, Ukraine</option>
			<option value="http://ads.astro.puc.cl/" '.(($adsserver=="http://ads.astro.puc.cl/")?"selected=\"selected\"":"") .'>http://ads.astro.puc.cl/ - Pontificia Universidad Cat&oacute;lica, Santiago, Chile</option>
			<option value="http://ads.nao.ac.jp/" '.(($adsserver=="http://ads.nao.ac.jp/")?"selected=\"selected\"":"") .'>http://ads.nao.ac.jp/ - National Astronomical Observatory, Tokyo, Japan</option>
			<option value="http://ads.bao.ac.cn/" '.(($adsserver=="http://ads.bao.ac.cn/")?"selected=\"selected\"":"") .'>http://ads.bao.ac.cn/ - National Astronomical Observatory, Chinese Academy of Science, Beijing, China</option>
			<option value="http://ads.iucaa.ernet.in/" '.(($adsserver=="http://ads.iucaa.ernet.in/")?"selected=\"selected\"":"") .'>http://ads.iucaa.ernet.in/ - Inter-University Centre for Astronomy and Astrophysics, Pune, India</option>
			<option value="http://ads.arsip.lipi.go.id/" '.(($adsserver=="http://ads.arsip.lipi.go.id/")?"selected=\"selected\"":"") .'>http://ads.arsip.lipi.go.id/ - Indonesian Institute of Sciences, Jakarta, Indonesia</option>
			<option value="http://saaoads.chpc.ac.za/" '.(($adsserver=="http://saaoads.chpc.ac.za/")?"selected=\"selected\"":"") .'>http://saaoads.chpc.ac.za/ - South African Astronomical Observatory</option>
			<option value="http://ads.on.br/" '.(($adsserver=="http://ads.on.br/")?"selected=\"selected\"":"") .'>http://ads.on.br/ - Observat&oacute;rio Nacional, Rio de Janeiro, Brazil</option>
		</select>
		<br /><br />
		<h3>Acknowledgement to ADS after list of papers</h3>
		Enable or disable the acknowledgement to ADS and the link to the original query performed on the ADS server.<br/><br/>
		<input type="radio" name="wp_nasaads_query_importer_acknowledgement" value="enabled" '.(($ackno=="enabled")?"checked=\"checked\"":"").'/> Enabled 
		<input type="radio" name="wp_nasaads_query_importer_acknowledgement" value="disabled" '.(($ackno=="disabled")?"checked=\"checked\"":"").'/> Disabled
		<br /><br /><br /><br />
		<input type="submit" value="Save" />
	</form>
</div>';

}

function wp_nasaads_query_importer_admin()
{
	add_options_page("WP NASA/ADS Query Importer", "WP NASA/ADS Query Importer", "manage_options", "wp-nasaads-query-importer", "wp_nasaads_query_importer_display_admin_page");
}

add_action("admin_menu", "wp_nasaads_query_importer_admin");


?>