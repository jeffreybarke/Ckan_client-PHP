<!DOCTYPE html>

<html lang="en-us" dir="ltr">

<head>

	<meta charset="utf-8">

	<title>Ckan_client package register and package entity demo</title>

	<style>
		body { color: #333; font: normal 14px Arial, Helvetica, sans-serif; line-height: 1.5em; margin: 40px; }
		h1, h2, h3 { font-weight: normal; }
		h1 { font-size: 26px; }
		h2 { border-top: 1px solid #ccc; font-size: 22px; margin-top: 2em; padding-top: 0.5em; }
		h3 { font-size: 18px; }
		a { color: #666; }
		a:hover { text-decoration: none; }
		blockquote { border: 1px dashed #ccc; padding: 20px; }
		blockquote h3 { margin-top: 0; }
		blockquote p { margin: 0; }
	</style>

</head>

<body>

	<h1>Ckan_client package register and package entity demo</h1>

	<p>&#8226; <a href="./">Return to Ckan_client demos</a></p>

	<?php

		// Display errors for demo
		@ini_set('error_reporting', E_ALL);
		@ini_set('display_errors', 'stdout');

		// Include Ckan_client
		require_once('./../Ckan_client.php');

		// Create CKAN object
		// Takes optional API key parameter. Required for POST and PUT methods.
		$ckan = new Ckan_client('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');

	?>

	<h2>Demo one</h2>
	<p>Make a <code>GET</code> request to the package register resource. This will return an array of all package IDs. Manually output two.</p>

	<?php
		try
		{
			$data = $ckan->get_package_register();
			if ($data):
				print '<ul>';
				for ($i = 0; $i < 2; $i++):
					print '<li>' . $data[$i] . '</li>';
				endfor;
				print '</ul>';
			endif;
		}
		catch (Exception $e)
		{
			print '<p><strong>Caught exception: ' . $e->getMessage() . 
				'</strong></p>';
		}
	?>

	<h2>Demo two</h2>
	<p>Make a <code>GET</code> request to the package entity resource and manually output some fields.</p>

	<?php
		try
		{
			$data = $ckan->get_package_entity('mke-liquor-licenses');
			if ($data):
				print '<blockquote><h3>' . $data->title . '</h3><p><strong>' . 
					$data->maintainer . '</strong></p>' . 
					Markdown($data->notes) . '<p><strong>License:</strong> ' . 
					$data->license . '<br />&#8226; <a href="' . 
					$data->ckan_url . 
					'">View this package at CKAN</a></p></blockquote>';
			endif;
		}
		catch (Exception $e)
		{
			print '<p><strong>Caught exception: ' . $e->getMessage() . 
				'</strong></p>';
		}
	?>

	<h3>Demo three</h3>
	<p>Make a <code>POST</code> request to the package register resource to create a new package. <em>Note:</em> This demo requires an <abbr title="Application Programming Interface">API</abbr> key.</p>

	<?php

		try
		{
			$data = '{
				"name": "Name-String", 
				"title": "String",
				"url": "String",
				"notes": "String",
				"tags": [Tag-List],
				"maintainer": "String",
				"maintainer_email": "String",
				"license_id": "String",
				"resources": [ Resource ]
			}';
			$ckan->post_package_register($data);
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