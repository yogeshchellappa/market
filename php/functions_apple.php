<?php
	
	error_reporting(0);
	$rating_weight = 25;
	$reviews_weight = 25;
	$game_center_weight = 15;
	$size_weight = 10;
	$price_weight = 10;
	$tab_weight = 5;
	$content_weight = 5;
	
	$reviews_array = array(
		"pos" => 1,
		"neg" => -1,
		"neutral" => 0
	);

	
	/*
	//	Function to perform POST requests in PHP. 
	/ 	URL => URL to make the request to.
	// 	Data => Data to be posted, in JSON format.
	*/
	function do_post_request($url, $data, $optional_headers = null)
	{
		$params = array('http' => array(
						'method' => 'POST',
						'content' => $data
						));
		if ($optional_headers !== null) 
		{
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp)
		{
			throw new Exception("Problem with $url, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) 
		{
			throw new Exception("Problem reading data from $url, $php_errormsg");
		}
		return $response;
	}
	
	
	/*
	/	Run analytics based on rating, size, price, tablet specific, content rating,
	/	number of languages, reviews. Sentiment analysis done on reviews.
	*/
	function run_analytics($app)
	{
		global 	$rating_weight, $reviews_weight, $game_center_weight, $size_weight,	$price_weight, $tab_weight,	$content_weight, 
				$my_title, $my_developer, $my_rating, $my_number_of_ratings, $my_price, $my_content_rating, $my_tab_supported, $my_debut, 
				$my_category, $my_size, $my_is_game_centered, $my_num_lang_supported, $my_is_game, $my_game_genre_id, $my_reviews, $reviews_array;
		
		// Initially, score is 0
			// Positive score => Other app is better, negative, vice versa
		$score = 0;
		
		$results_returned = $app;
		
		// Unique ID for the app, needed for fetching reviews
		$id = $results_returned['trackId'];
		$fp = fopen("id.txt","w");
		fwrite($fp, $id);
		fclose($fp);
		
		$title = $results_returned['trackCensoredName'];
		$content_rating = $results_returned['trackContentRating'];
		
		// In MB
		$size = ($results_returned['fileSizeBytes'] / (1024*1024));
			
		$price = $results_returned['price'];
		
		$rating = $results_returned['averageUserRating'];

		// Get the number of languages supported
		if(isset($results_returned['languageCodesISO2A']))
		{
			$num_lang_supported = sizeof($results_returned['languageCodesISO2A']);
		}
		else
		{
			$num_lang_supported = 1;
		}
		
		
		if(isset($results_returned['ipad']))
		{
			$tab_supported = $results_returned['ipad'];
		}
		else
		{
			$tab_supported = 0;
		}

		// Supports game center
		$is_game_centered = $results_returned['isGameCenterEnabled'];
		
		// Fetching done 
		// Starting comparison and awarding score
		
		// Rating comparison
		if($rating > $my_rating)
		{
			$score += $rating_weight;
		}
		else if($rating < $my_rating)
		{
			$score -= $rating_weight;
		}
		else if($rating == $my_rating)
		{
			$score += 0;
		}
		
		// Size comparison
		if($size > $my_size)
		{
			$score += $size_weight;
		}
		else if($size < $my_size)
		{
			$score -= $size_weight;
		}
		else if($size == $my_size)
		{
			$score += 0;
		}	

		// Price comparison
		if($price > $my_price)
		{
			$score += $price_weight;
		}
		else if($price < $my_price)
		{
			$score -= $price_weight;
		}
		else if($price == $my_price)
		{
			$score += 0;
		}		
		
		
		// Tablet support
		if($tab_supported > $my_tab_supported)
		{
			$score += $tab_weight;
		}
		else if($tab_supported < $my_tab_supported)
		{
			$score -= $tab_weight;
		}
		else if($tab_supported == $my_tab_supported)
		{
			$score += 0;
		}		
		
		// Content rating
		if($content_rating > $my_content_rating)
		{
			$score += $content_weight;
		}
		else if($content_rating < $my_content_rating)
		{
			$score -= $content_weight;
		}
		else if($content_rating == $my_content_rating)
		{
			$score += 0;
		}		
		
		// Language support
			// For games, languages not very crucial cause it's graphics based
		if($my_is_game)
		{
			if($num_lang_supported > $my_num_lang_supported)
			{
				$score += 5;
			}
			else if($num_lang_supported < $my_num_lang_supported)
			{
				$score -= 5;
			}
			else if($num_lang_supported == $my_num_lang_supported)
			{
				$score += 0;
			}
			
			// Game center support
			if($is_game_centered > $my_is_game_centered)
			{
				$score += 15;
			}
			else if($is_game_centered < $my_is_game_centered)
			{
				$score -= 15;
			}
			else if($is_game_centered == $my_is_game_centered)
			{
				$score += 0;
			}
		}
		// Higher priority
		else
		{
			if($num_lang_supported > $my_num_lang_supported)
			{
				$score += 20;
			}
			else if($num_lang_supported < $my_num_lang_supported)
			{
				$score -= 20;
			}
			else if($num_lang_supported == $my_num_lang_supported)
			{
				$score += 0;
			}
		}

		// Reviews
		$reviews = exec("python apple.py");
		if(isset($reviews))
		{
			if($reviews_array[$reviews] > $reviews_array[$my_reviews])
			{
				$score += $reviews_weight;
			}
			else if($reviews_array[$reviews] < $reviews_array[$my_reviews])
			{
				$score -= $reviews_weight;
			}
			else
			{
				$score += 0;
			}
		}
		else
		{
			$score += 0;
		}
		
		return $score;
	}	
?>