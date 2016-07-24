<?php
	error_reporting(0);
	header("content-type:application/json");
	session_start();
	include_once "connection.php";
	include_once 'functions_apple.php';
	
	$url = 'https://42matters.com/api/1/ios/apps/query.json';
	$access_token = "ed9cbc7cd3e0521ae2a2a30201989d16b9b7da7f";
	
	
	// Obtaining my app details from the session variables
	$my_title = $_SESSION['title'];
	$my_developer = $_SESSION['developer'];
	$my_rating = floatval($_SESSION['rating']);
	$my_price = floatval($_SESSION['price']);
	$my_content_rating = intval($_SESSION['content_rating']);
	$my_tab_supported = intval($_SESSION['tab_supported']);
	$my_debut = $_SESSION['debut'];
	$my_category = $_SESSION['category'];
	$my_size = floatval($_SESSION['size']);
	$my_is_game_centered = $_SESSION['is_game_centered'];
	$my_num_lang_supported = intval($_SESSION['num_lang_supported']);
	$my_is_game = intval($_SESSION['is_game']);
	$my_game_genre_id = intval($_SESSION['game_genre_id']);
	$my_reviews = $_SESSION['reviews'];
		
	// Map the category to its corresponding integer
	$query = "SELECT cat_int FROM mapping_apple WHERE cat_name = '$my_category'";
	$result = mysql_query($query);
	$result = mysql_fetch_array($result);
	$cat_int = $result['cat_int'];
	
	
	// To store final results
	$results_array = array(
		"name" => $my_title
	);
	
	// Not game
	if(intval($my_is_game) == 0)
	{
		$payload = '{
				"query": {
					"name": "",
					"platform": "ios",
					"query_params": {
						"from": 0,
						"num": 6,
						"sort": "averageUserRating",
						"sort_order": "desc",
						"primaryGenreId": ['
							. strval($cat_int) . '
						]
					}
				}
		}';
	}
	else
	{
		$payload = '{
				"query": {
					"name": "",
					"platform": "ios",
					"query_params": {
						"from": 0,
						"num": 6,
						"sort": "userRatingCount",
						"sort_order": "desc",
						"primaryGenreId": [
							6014
						],
						"genreIds": ['
							. strval($my_game_genre_id) .
						']						
					}
				}
		}';
	}
		
	$complete_url = $url . '?' . 'access_token=' . $access_token;
	try
	{
		// Make the request
		$res = do_post_request($complete_url, $payload);
		$other_apps = json_decode($res, true);
	}
	catch(Exception $e)
	{
		die("Please check your internet connection and try again.");
	}
	
	// Remove the same app if present in the results
	for($i = 0; $i < sizeof($other_apps['results']); $i++)
	{
		$app_name = $other_apps['results'][$i]['trackCensoredName'];
		if(strcmp($app_name, $my_title) == 0)
		{
			// Remove that element at that index
			array_splice($other_apps['results'], $i, 1);
		}
	}
	
	for($i = 0; $i < sizeof($other_apps['results']); $i++)
	{
		$score = run_analytics($other_apps['results'][$i]);
		$name = $other_apps['results'][$i]['trackCensoredName'];
		$result = array(
			'name' => $name,
			'score' => $score
			);
		array_push($results_array, $result);
	}

	$finalScore = json_encode($results_array);
	print_r ($finalScore);
?>