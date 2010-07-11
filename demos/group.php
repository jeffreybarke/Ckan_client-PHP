<!DOCTYPE html>

<html lang="en-us" dir="ltr">

<head>

	<meta charset="utf-8">

	<title>Ckan_client group register and group entity demo</title>

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

	<h1>Ckan_client group register and group entity demo</h1>

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

	<h2>Demo one</h2>
	<p>Make a <code>GET</code> request to the group register resource. This will return an array of all group IDs. Manually output two.</p>

	<?php
		try
		{
			$data = $ckan->get_group_register();
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
	<p>Make a <code>GET</code> request to the group entity resource and manually output some fields.</p>

	<?php
		try
		{
			$data = $ckan->get_group_entity('mkedata');
			if ($data):
				print '<blockquote><h3>' . $data->title . '</h3><p>' . 
					$data->description . '</p>';
				if (count($data->packages) > 0):
					print '<ul>';
					foreach ($data->packages as $val):
						$package = $ckan->get_package_entity($val);
						printf('<li><a href="%s">%s</a></li>',
							$package->ckan_url,
							$package->title
						);
					endforeach;
					print '</ul>';
				endif;
				print '</blockquote>';
			endif;
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