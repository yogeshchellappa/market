<?php
	error_reporting(0);
	session_start();
	include_once "connection.php";
	include_once 'functions_android.php';
	
	$url = 'https://42matters.com/api/1/apps/query.json';
	// 42matters access token
	$access_token = "ed9cbc7cd3e0521ae2a2a30201989d16b9b7da7f";
	
	// playstoreapi access token
	$recom_key = 'c9c9ef3e738e5f56ca2b2ebd6c1992c3'; 
		
	
	// Obtaining my app details from the session variables
	$my_title = $_SESSION['title'];
	$my_rating = $_SESSION['rating'];
	$my_price = floatval($_SESSION['price']);
	$my_content_rating = $_SESSION['content_rating'];
	$my_last_update = $_SESSION['last_update'];
	$my_debut = $_SESSION['debut'];
	$my_category = $_SESSION['category'];
	$my_iap = intval($_SESSION['iap']);
	$my_size = floatval($_SESSION['size']);
	$my_downloads = $_SESSION['downloads'];
	$my_package_name = $_SESSION['package_name'];
	$my_reviews = $_SESSION['reviews'];
	
	$recom_url = 'http://api.playstoreapi.com/v1.1/apps/' . $my_package_name .  '?key=' . $recom_key;
	
	$final_results = array();
	
	try
	{
		// Make the request
		$result = file_get_contents($recom_url);
	}
	catch(Exception $e)
	{
		exit("Please check your internet connection and try again.");
	}
	// Result is in JSON format.
	$results = json_decode($result, true);
	for($i = 0; $i < 5; $i++)
	{
		$results_array = run_android_analytics($results['recommendedApps'][$i]);
		array_push($final_results, $results_array);
	}
	
	$base_app = array(
		"name" => $my_title,
		"score" => 0
	);
	array_push($final_results, $base_app);
	$final_results = json_encode($final_results, true);
	echo $final_results;
?>