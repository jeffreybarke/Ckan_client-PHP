<!DOCTYPE html>

<html lang="en-us" dir="ltr">

<head>

	<meta charset="utf-8">

	<title>Ckan_client package search demo</title>

	<style>
		body { color: #333; font: normal 14px Arial, Helvetica, sans-serif; line-height: 1.5em; margin: 40px; }
		h1, h2, h3 { font-weight: normal; }
		h1 { font-size: 26px; }
		h2 { border-top: 1px solid #ccc; font-size: 22px; margin-top: 2em; padding-top: 0.5em; }
		h3 { font-size: 18px; }
		a { color: #666; }
		a:hover { text-decoration: none; }
	</style>

</head>

<body>

	<h1>Ckan_client package search demo</h1>

	<p>&#8226; <a href="./">Return to Ckan_client demos</a></p>

	<?php

		// Display errors for demo
		@ini_set('error_reporting', E_ALL);
		@ini_set('display_errors', 'stdout');

		// Include Ckan_client
		require_once('./../Ckan_client.php');

		// Create CKAN object
		// Takes optional API key parameter. Required for POST and PUT methods.
		$ckan = new Ckan_client();

	?>

	<p>To learn more about the CKAN Search <abbr title="Application Programming Interface">API</abbr> options, please visit <a href="http://knowledgeforge.net/ckan/doc/ckan/api.html#ckan-search-api">http://knowledgeforge.net/ckan/doc/ckan/api.html#ckan-search-api</a>.</p>

	<h2>Demo one</h2>
	<p>Search for packages containing &#8220;alcohol + beverage + licenses&#8221; using the default search options and manually output the results.</p>

	<?php
		try
		{
			// Search for packages containing "alcohol + beverage + licenses"
			$search_term = 'alcohol beverage licenses';
			$data = $ckan->search_package($search_term);
			// Display results
			if ($data):
				printf('<h3>There %s %d result%s for &#8220;%s&#8221;:</h3>', 
					(($data->count === 1) ? 'is' : 'are'), 
					$data->count, 
					(($data->count === 1) ? '' : 's'),
					$search_term);
				if ($data->count > 0):
					print '<ol>';
					foreach ($data->results as $val):
						$package = $ckan->get_package_entity($val);
						// The notes field uses the Markdown syntax, so
						// convert it to HTML using the PHP Markdown library
						printf('<li><a href="%s">%s</a>%s</li>',
							$package->ckan_url,
							$package->title,
							(($package->notes) ? 
								': ' . strip_tags(Markdown($package->notes), 
								'<a>') : '' )
						);
					endforeach;
					print '</ol>';
				endif;
			endif;
		}
		catch (Exception $e)
		{
			print '<p><strong>Caught exception: ' . $e->getMessage() . 
				'</strong></p>';
		}
	?>

	<h2>Demo two</h2>
	<p>Search for packages containing &#8220;osm&#8221; using the alias functions and search options: <code>$ckan->search($search_term, array('order_by' => 'title', 'offset' => 1, 'limit' => 2, 'openness' => 1, 'downloadable' => 1));</code> and then manually output the results.</p>

	<?php
		try
		{
			// Search for packages containing "osm" using the alias functions
			$search_term = 'osm';
			// search() is an alias for search_package()
			$data = $ckan->search($search_term, array(
				'order_by' => 'title',
				'offset' => 1,
				'limit' => 2,
				'openness' => 1,
				'downloadable' => 1
			));
			// Display results
			if ($data):
				printf('<h3>There %s %d result%s for &#8220;%s&#8221;:</h3>', 
					(($data->count === 1) ? 'is' : 'are'), 
					$data->count, 
					(($data->count === 1) ? '' : 's'),
					$search_term);
				if ($data->count > 0):
					print '<ol>';
					foreach ($data->results as $val):
						// get_package($param) is an alias for 
						// get_package_entity()
						$package = $ckan->get_package($val);
						printf('<li><a href="%s">%s</a>%s</li>',
							$package->ckan_url,
							$package->title,
							(($package->notes) ? 
								': ' . strip_tags(Markdown($package->notes), 
								'<a>') : '' )
						);
					endforeach;
					print '</ol>';
				endif;
			endif;
		}
		catch (Exception $e)
		{
			print '<p><strong>Caught exception: ' . $e->getMessage() . 
				'</strong></p>';
		}
	?>

	<h2>Demo three</h2>
	<p>Search for packages containing &#8220;Supercalifragilisticexpialidocious&#8221; using the alias functions. There should be no results.</p>

	<?php
		try
		{
			// Search for packages containing 
			 // "Supercalifragilisticexpialidocious" 
			// using the alias functions
			$search_term = 'supercalifragilisticexpialidocious';
			// search() is an alias for search_package()
			$data = $ckan->search($search_term);
			// Display results
			if ($data):
				printf('<h3>There %s %d result%s for &#8220;%s&#8221;:</h3>', 
					(($data->count === 1) ? 'is' : 'are'), 
					$data->count, 
					(($data->count === 1) ? '' : 's'),
					$search_term);
				if ($data->count > 0):
					print '<ol>';
					foreach ($data->results as $val):
						// get_package($param) is an alias for get_package_entity()
						$package = $ckan->get_package($val);
						printf('<li><a href="%s">%s</a>%s</li>',
							$package->ckan_url,
							$package->title,
							(($package->notes) ? 
								': ' . strip_tags(Markdown($package->notes), 
								'<a>') : '' )
						);
					endforeach;
					print '</ol>';
				endif;
			endif;
		}
		catch (Exception $e)
		{
			print '<p><strong>Caught exception: ' . $e->getMessage() . 
				'</strong></p>';
		}
	?>

	<h2>Demo four</h2>
	<p>Search for packages containing &#8220;gis&#8221; using the alias functions and the search output helper: <code>$ckan->search_display($data, array('search_term' => $search_term, 'title_tag' => 'h3', 'result_list_tag' => 'ol', 'show_notes' => TRUE, 'format_notes' => '&lt;a&gt;'</code>.</p>

	<?php
		try
		{
			// Search for packages containing "gis" using the alias functions
			// and search output helper
			$search_term = 'gis';
			// search() is an alias for search_package()
			$data = $ckan->search($search_term);
			// search_display takes the result of search() or seach_package() 
			// and an optional parameters array
			$ckan->search_display($data, array(
				'search_term' => $search_term,
				'title_tag' => 'h3',
				'result_list_tag' => 'ol',
				'show_notes' => TRUE,
				'format_notes' => '<a>'
			));
		}
		catch (Exception $e)
		{
			print '<p><strong>Caught exception: ' . $e->getMessage() . 
				'</strong></p>';
		}

		unset($ckan);

	?>

	<p>&#8226; <a href="./">Return to Ckan_client demos</a></p>

</body>

</html>