<?php

/**
 * Ckan_client class
 *
 * A PHP client for the CKAN (Comprehensive Knowledge Archive Network) API.
 *
 * For details and documentation, please see http://github.com/jeffreybarke/Ckan_client-PHP
 *
 * @author		Jeffrey Barke
 * @copyright	Copyright 2010 Jeffrey Barke
 * @license		http://github.com/jeffreybarke/Ckan_client-PHP/blob/master/LICENSE
 * @link		http://github.com/jeffreybarke/Ckan_client-PHP
 *
 */

class Ckan_client
{

	// Properties ---------------------------------------------------------

	/**
	 * Client's API key. Required for any PUT or POST methods.
	 *
	 * @link	http://knowledgeforge.net/ckan/doc/ckan/api.html#ckan-api-keys
	 * @since	Version 0.1.0
	 */
	private $api_key = FALSE;

	/**
	 * Version of the CKAN API we're using.
	 *
	 * @var		string
	 * @link	http://knowledgeforge.net/ckan/doc/ckan/api.html#api-versions
	 * @since	Version 0.1.0
	 */
	private $api_version = '2';

	/**
	 * URI to the CKAN web service.
	 *
	 * @var		string
	 * @since	Version 0.1.0
	 */
	private $base_url = 'http://ckan.net/api/%d/';

	/**
	 * Internal cURL object.
	 *
	 * @since	Version 0.1.0
	 */
	private $ch = FALSE;

	/**
	 * cURL headers.
	 *
	 * @since	Version 0.1.0
	 */
	private $ch_headers;

	/**
	 * Standard HTTP status codes.
	 *
	 * @var		array
	 * @since	Version 0.1.0
	 */
	private $http_status_codes = array(
		'200' => 'OK',
		'301' => 'Moved Permanently',
		'400' => 'Bad Request',
		'403' => 'Not Authorized',
		'404' => 'Not Found',
		'409' => 'Conflict (e.g. name already exists)',
		'500' => 'Service Error'
	);

	/**
	 * Array of CKAN resources and their URI fragment.
	 *
	 * @var		array
	 * @link	http://knowledgeforge.net/ckan/doc/ckan/api.html#ckan-model-api
	 * @since	Version 0.1.0
	 */
	private $resources = array(
		'package_register' => 'rest/package',
		'package_entity' => 'rest/package',
		'group_register' => 'rest/group',
		'group_entity' => 'rest/group',
		'tag_register' => 'rest/tag',
		'tag_entity' => 'rest/tag',
		'rating_register' => 'rest/rating',
		'rating_entity' => 'rest/rating',
		'revision_register' => 'rest/revision',
		'revision_entity' => 'rest/revision',
		'license_list' => 'rest/licenses',
		'package_search' => 'search/package'
	);

	/**
	 * Ckan_client user agent string.
	 *
	 * @var		string
	 * @since	Version 0.1.0
	 */
	private $user_agent = 'Ckan_client-PHP/%s';

	/**
	 * Ckan_client version number.
	 *
	 * @var		string
	 * @since	Version 0.1.0
	 */
	private $version = '0.1.0';

	// Magic methods ------------------------------------------------------

	/**
	 * Constructor
	 *
	 * Calls the API key, base URI and user agent setters.
	 * Initializes the internal cURL object.
	 *
	 * @param	string	CKAN API key.
	 */
	public function __construct($api_key = FALSE)
	{
		// If provided, set the API key.
		if ($api_key)
		{
			$this->set_api_key($api_key);
		}
		// Set base URI and Ckan_client user agent string.
		$this->set_base_url();
		$this->set_user_agent();
		// Create cURL object.
		$this->ch = curl_init();
		// Follow any Location: headers that the server sends.
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
		// However, don't follow more than five Location: headers.
		curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
		// Automatically set the Referer: field in requests 
		// following a Location: redirect.
		curl_setopt($this->ch, CURLOPT_AUTOREFERER, TRUE);
		// Return the transfer as a string instead of dumping to screen. 
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
		// If it takes more than 45 seconds, fail
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 45);
		// We don't want the header (use curl_getinfo())
		curl_setopt($this->ch, CURLOPT_HEADER, FALSE);
		// Set user agent to Ckan_client
		curl_setopt($this->ch, CURLOPT_USERAGENT, $this->user_agent);
		// Track the handle's request string
		curl_setopt($this->ch, CURLINFO_HEADER_OUT, TRUE);
		// Attempt to retrieve the modification date of the remote document.
		curl_setopt($this->ch, CURLOPT_FILETIME, TRUE);
		// Initialize cURL headers
		$this->set_headers();
		// Include PHP Markdown library
		require_once('lib/php_markdown/markdown.php');
	}

	/**
	 * Destructor
	 *
	 * Since it's possible to leave cURL open, this is the last chance to
	 * close it.
	 */
	public function __destruct()
	{
		// Cleanup
		if ($this->ch)
		{
			curl_close($this->ch);
			unset($this->ch);
		}
	}

	// Setters ------------------------------------------------------------

	/**
	 * Sets the CKAN API key.
	 *
	 * @access	public
	 * @param	string	CKAN API key.
	 * @return	void
	 * @since	Version 0.1.0
	 */
	public function set_api_key($api_key)
	{
		$this->api_key = $api_key;
	}

	/**
	 * Sets the CKAN API base URI.
	 *
	 * @access	private
	 * @return	void
	 * @since	Version 0.1.0
	 */
	private function set_base_url()
	{
		// Append the CKAN API version to the base URI.
		$this->base_url = sprintf($this->base_url, $this->api_version);
	}

	/**
	 * Sets the custom cURL headers.
	 *
	 * @access	private
	 * @return	void
	 * @since	Version 0.1.0
	 */
	private function set_headers()
	{
		$date = new DateTime(NULL, new DateTimeZone('UTC'));
		$this->ch_headers = array(
			'Date: ' . $date->format('D, d M Y H:i:s') . ' GMT', // RFC 1123
			'Accept: application/json;q=1.0, application/xml;q=0.5, */*;q=0.0',
			'Accept-Charset: utf-8',
			'Accept-Encoding: gzip'
		);
	}

	/**
	 * Sets the Ckan_client user agent string.
	 *
	 * @access	private
	 * @return	void
	 * @since	Version 0.1.0
	 */
	private function set_user_agent()
	{
		if ('80' === @$_SERVER['SERVER_PORT'])
		{
			$server_name = 'http://' . $_SERVER['SERVER_NAME'];
		}
		else
		{
			$server_name = '';
		}
		$this->user_agent = sprintf($this->user_agent, $this->version) . 
			' (' . $server_name . $_SERVER['PHP_SELF'] . ')';
	}

	// Public (API) methods -----------------------------------------------

	// Package register resource

	/**
	 * @access	public
	 * @return	array	An array of all package IDs.
	 * @since	Version 0.1.0
	 */
	public function get_package_register()
	{
		return $this->make_request('GET', $this->resources['package_register']);
	}

	/**
	 * @access	public
	 * @param	string	Package
	 * @return	bool
	 * @link	http://knowledgeforge.net/ckan/doc/ckan/api.html#model-api-data-formats
	 * @since	Version 0.1.0
	 */
	public function post_package_register($data)
	{
		return $this->make_request('POST', 
			$this->resources['package_register'], 
			$data);
	}

	// Package entity resouce

	/**
	 * @access	public
	 * @param	string	Package ID
	 * @return	object	Package
	 * @link	http://knowledgeforge.net/ckan/doc/ckan/api.html#model-api-data-formats
	 * @since	Version 0.1.0
	 */
	public function get_package_entity($package)
	{
		return $this->make_request('GET', 
			$this->resources['package_entity'] . '/' . urlencode($package));
	}

	/**
	 * @access	public
	 * @param	string	Package ID
	 * @param	string	Packing
	 * @return	bool
	 * @link	http://knowledgeforge.net/ckan/doc/ckan/api.html#model-api-data-formats
	 * @since	Version 0.1.0
	 */
	public function put_package_entity($package, $data)
	{
		return $this->make_request('PUT', 
			$this->resources['package_entity'] . '/' . urlencode($package), 
			$data);
	}

	// Package utility alias

	/**
	 * CKAN package GET utility alias.
	 *
	 * @see		get_package_register(), get_package_entity()
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function get_package($package = FALSE)
	{
		if ($package)
		{
			return $this->get_package_entity($package);
		}
		else
		{
			return $this->get_package_register();
		}
	}

	// Group register resource

	/**
	 * @access	public
	 * @return	array	An array of all group IDs.
	 * @since	Version 0.1.0
	 */
	public function get_group_register()
	{
		return $this->make_request('GET', $this->resources['group_register']);
	}

	// Group entity resource

	/**
	 * @access	public
	 * @param	string	Group ID
	 * @return	object	Group
	 * @link	http://knowledgeforge.net/ckan/doc/ckan/api.html#model-api-data-formats
	 * @since	Version 0.1.0
	 */
	public function get_group_entity($group)
	{
		return $this->make_request('GET', 
			$this->resources['group_entity'] . '/' . urlencode($group));
	}

	// Group utility alias

	/**
	 * CKAN group GET utility alias.
	 *
	 * @see		get_group_register(), get_group_entity()
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function get_group($group = FALSE)
	{
		if ($group)
		{
			return $this->get_group_entity($group);
		}
		else
		{
			return $this->get_group_register();
		}
	}

	// Tag register resource

	/**
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function get_tag_register()
	{
		return $this->make_request('GET', $this->resources['tag_register']);
	}

	// Tag entity resource

	/**
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function get_tag_entity($tag)
	{
		return $this->make_request('GET', $this->resources['tag_entity'] . 
			'/' . urlencode($tag));
	}

	// Tag utility alias

	/**
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function get_tag($tag = FALSE)
	{
		if ($tag)
		{
			return $this->get_tag_entity($tag);
		}
		else
		{
			return $this->get_tag_register();
		}
	}

	// Revision register resource

	/**
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function get_revision_register()
	{
		return $this->make_request('GET', 
			$this->resources['revision_register']);
	}

	// Revision entity resource

	/**
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function get_revision_entity($revision)
	{
		return $this->make_request('GET', 
			$this->resources['revision_entity'] . '/' . urlencode($revision));
	}

	// Revision utility alias

	/**
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function get_revision($revision = FALSE)
	{
		if ($revision)
		{
			return $this->get_revision_entity($revision);
		}
		else
		{
			return $this->get_revision_register();
		}
	}

	// License list resource

	/**
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function get_license_list()
	{
		return $this->make_request('GET', $this->resources['license_list']);
	}

	// License utility alias

	public function get_license()
	{
		return $this->get_license_list();
	}

	// Search API

	/**
	 * Searches CKAN packages.
	 *
	 * @access public
	 * @param	string	Keywords to search for
	 * @param	array	Optional. Search options.
	 * @return	mixed	If success, search object. On fail, false.
	 * @since	Version 0.1.0
	 */
	public function search_package($keywords, $opts = array())
	{
		// Gots to have keywords or there's nothing to search for.
		// Also, $opts better be an array
		if (0 === strlen(trim($keywords)) || FALSE === is_array($opts))
		{
			throw new Exception('We need keywords, yo!');
		}
		$q = '';
		// Set querystring based on $opts param.
		$q .= '&order_by=' . ((isset($opts['order_by'])) 
			? urlencode($opts['order_by']) : 'rank');
		$q .= '&offset=' . ((isset($opts['offset'])) 
			? urlencode($opts['offset']) : '0');
		$q .= '&limit=' . ((isset($opts['limit'])) 
			? urlencode($opts['limit']) : '20');
		$q .= '&filter_by_openness=' . ((isset($opts['openness'])) 
			? urlencode($opts['openness']) : '0');
		$q .= '&filter_by_downloadable=' . ((isset($opts['downloadable'])) 
			? urlencode($opts['downloadable']) : '0');
		return $data = $this->make_request('GET', 
			$this->resources['package_search'] . '?q=' . 
			urlencode($keywords) . $q);
	}

	/**
	 * CKAN package search utility alias, since it's most likely ppl just
	 * want to search the packages.
	 *
	 * @see		search_package()
	 * @access	public
	 * @since	Version 0.1.0
	 */
	public function search($keywords, $opts = array())
	{
		return $this->search_package($keywords, $opts);
	}

	// Public methods -----------------------------------------------------

	/**
	 * Helper function to ease the display of search results.
	 * Outputs directly to screen.
	 *
	 * @access	public
	 * @param	object	Result from search() or search_package()
	 * @param	array	Optional. An array of formatting options.
	 * @return	void
	 * @since	Version 0.1.0
	 */
	public function search_display($data, $opts = array())
	{
		if ($data)
		{
			// Set vars based on $opts param.
			$search_term = (isset($opts['search_term'])) ? 
				$opts['search_term'] : '';
			$title_tag = '<' . 
				((isset($opts['title_tag'])) ? $opts['title_tag'] : 'h2') . '>';
			$title_close_tag = str_replace('<', '</', $title_tag);
			$result_list_tag = (isset($opts['result_list_tag'])) 
				? $opts['result_list_tag'] : 'ul';
			if (strlen(trim($result_list_tag)))
			{
				$result_list_close_tag = '</' . $result_list_tag . '>';
				$result_list_tag = '<' . $result_list_tag . '>';
			}
			else
			{
				$result_list_close_tag = '';
			}
			$show_notes = (isset($opts['show_notes'])) 
				? $opts['show_notes'] : FALSE;
			$format_notes = (isset($opts['format_notes'])) 
				? $opts['format_notes'] : FALSE;
			// Set search title string
			// is|are, count, ''|s, ''|search_term, .|:
			printf($title_tag . 'There %s %d result%s%s%s' . $title_close_tag, 
				(($data->count === 1) ? 'is' : 'are'), 
				$data->count, 
				(($data->count === 1) ? '' : 's'),
				(strlen(trim($search_term)) 
					? ' for &#8220;' . $search_term . '&#8221;' : ''),
				(($data->count === 0) ? '.' : ':'));
			if ($data->count > 0)
			{
				print $result_list_tag;
				foreach ($data->results as $val)
				{
					$package = $this->get_package_entity($val);
					printf('<li><a href="%s">%s</a>',
						$package->ckan_url,
						$package->title);
					if (isset($package->notes) && $package->notes && 
						$show_notes)
					{
						print ': ';
						if (TRUE === $format_notes)
						{
							print Markdown($package->notes);
						}
						elseif (FALSE === $format_notes)
						{
							print $package->notes;
						}
						else
						{
							print strip_tags(Markdown($package->notes), 
								$format_notes);
						}
					}
					print '</li>';
				}
				print $result_list_close_tag;
			}
		}
	}

	// Private methods ----------------------------------------------------

	/**
	 * Make a request to the CKAN API.
	 *
	 * @access	private
	 * @param	string	HTTP method (GET, PUT, POST).
	 * @param	string	URI fragment to CKAN resource.
	 * @param	string	Optional. String in JSON-format that will be in request body.
	 * @return	mixed	If success, either an array or object. Otherwise FALSE.
	 * @since	Version 0.1.0
	 */
	private function make_request($method, $url, $data = FALSE)
	{
		// Set cURL method.
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		// Set cURL URI.
		curl_setopt($this->ch, CURLOPT_URL, $this->base_url . $url);
		// If POST or PUT, add Authorization: header and request body
		if ($method === 'POST' || $method === 'PUT')
		{
			// We needs a key and some data, yo!
			if ( ! ($this->api_key && $data))
			{
				// throw exception
				throw new Exception('Missing either an API key or POST data.');
			}
			else
			{
				// Add Authorization: header.
				$this->ch_headers[] = 'Authorization: ' . $this->api_key;
				// Add data to request body.
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
			}
		}
		else
		{
			// Since we can't use HTTPS,
			 // if it's in there, remove Authorization: header
			$key = array_search('Authorization: ' . $this->api_key, 
				$this->ch_headers);
			if ($key !== FALSE)
			{
				unset($this->ch_headers[$key]);
			}
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, NULL);
		}
		// Set headers.
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->ch_headers);
		// Execute request and get response headers.
		$response = curl_exec($this->ch);
		$info = curl_getinfo($this->ch);
		// Check HTTP response code
		if ($info['http_code'] !== 200)
		{
			throw new Exception($info['http_code'] . ': ' . 
				$this->http_status_codes[$info['http_code']]);
		}
		// Determine how to parse
		if (isset($info['content_type']) && $info['content_type'])
		{
			$content_type = str_replace('application/', '', 
				substr($info['content_type'], 0, 
				strpos($info['content_type'], ';')));
			return $this->parse_response($response, $content_type);
		}
		else
		{
			throw new Exception('Unknown content type.');
		}
	}

	/**
	 * Parse the response from the CKAN API.
	 *
	 * @access	private
	 * @param	string	Data returned from the CKAN API.
	 * @param	string	Format of data returned from the CKAN API.
	 * @return	mixed	If success, either an array or object. Otherwise FALSE.
	 * @since	Version 0.1.0
	 */
	private function parse_response($data = FALSE, $format = FALSE)
	{
		if ($data)
		{
			if ('json' === $format)
			{
				return json_decode($data);
			}
			else
			{
				throw new Exception('Unable to parse this data format.');
			}
		}
		return FALSE;
	}

}
