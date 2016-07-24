<?php	
	error_reporting(0);
	$rating_weight = 25;
	$reviews_weight = 25;
	$download_debut_weight = 15;	
	$iap_weight = 10;	
	$size_weight = 10;
	$price_weight = 10;
	$last_update_weight = 7;	
	$content_weight = 5;
	
	
	$content_array = array(
		"Everyone" => 5,
		"Low Maturity" => 4,
		"Medium Maturity" => 3,
		"High Maturity" => 2,
		"Not rated" => 1
	);
	
	$reviews_array = array(
		"pos" => 1,
		"neg" => -1,
		"neutral" => 0
	);

	/*
	/ Takes a date in format like 2013-10-02 and returns number of days till date
	*/
	function convert_date_to_days_till_date($date)
	{
		$now = time(); 
		$my_date = strtotime($date);
		$datediff = $now - $my_date;
		return floor($datediff / (60 * 60 * 24));
	}
	
	
	/*
	/ 	Compares the apps on the basis on number of days on the store and its performance
	*/
	function downloads_debut_compare($my_app_downloads, $my_app_debut, $other_app_downloads, $other_app_debut)
	{
		$my_number_of_days = convert_date_to_days_till_date($my_app_debut);
		$other_number_of_days = convert_date_to_days_till_date($other_app_debut);
		
		// Converting to int the number of downloads
			// Downloads are in 1,000 - 5,000 format
		$my_app_downloads = explode(" - ", $my_app_downloads);
		$my_app_downloads = $my_app_downloads[1];
		$my_app_downloads = intval(str_replace(",", "" , $my_app_downloads));
		
		$other_app_downloads = explode(" - ", $other_app_downloads);
		$other_app_downloads = $other_app_downloads[1];
		$other_app_downloads = intval(str_replace(",", "" , $other_app_downloads));
		
		$estimated_amount = floor(($other_number_of_days * $my_app_downloads) / $my_number_of_days);
		
		if($my_app_downloads < $estimated_amount)
		{
			return 1;
		}
		else if($my_app_downloads > $estimated_amount)
		{
			return -1;
		}
		else
		{
			return 0;
		}
	}


	/*
	/ 	Function to run android analytics: rating, size, price, in app purchases, content rating,
	/	last update, downloads and début date ,reviews. Sentiment analysis done on reviews.
	*/
	function run_android_analytics($package)
	{
		$score = 0;	
		$ACCESS_TOKEN = "ed9cbc7cd3e0521ae2a2a30201989d16b9b7da7f";
		$URL = 'http://42matters.com/api/1/apps/lookup.json';
		
		
	
		global 	$rating_weight, $reviews_weight, $last_update_weight, $size_weight,	$price_weight, $iap_weight,	$content_weight,$my_title, $reviews_array,
				$my_rating, $my_price, $my_content_rating, $my_last_update, $my_debut, $my_category, $my_size, $my_iap, $my_downloads, $content_array, $my_reviews;
				
		
		// Complete URL
		$request_url = $URL . '?access_token=' . $ACCESS_TOKEN . "&p=" . $package;
	
		try
		{
			// Make the request
			$result = file_get_contents($request_url);
		}
		catch(Exception $e)
		{
			exit("Please check your internet connection and try again.");
		}
		
		// Result is in JSON format.
		$results = json_decode($result, true);	
		
		$title = $results['title'];
		$debut = $results['created'];
		$debut = substr($debut, 0, 10);
		$content_rating = $results['content_rating'];
		$last_update = $results['market_update'];
		$last_update = substr($last_update, 0, 10);
		
		// In MB
		$size = ($results['size'] / (1024*1024));
		$price = $results['price_numeric'];
		
		// In-app purchases
		$iap = $results['iap'];
		
		$rating = $results['rating'];
		$downloads = $results['downloads'];
		
		$package_name = $results['package_name'];
		$fp = fopen("package.txt","w");
		fwrite($fp,$package_name);
		fclose($fp);

		// Starting comparison
		
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

		// In app purchase comparison
		if($iap > $my_iap)
		{
			$score += $iap_weight;
		}
		else if($iap < $my_iap)
		{
			$score -= $iap_weight;
		}
		else if($iap == $my_iap)
		{
			$score += 0;
		}		
		
		// Downloads and début
		$score += 15 * (downloads_debut_compare($my_downloads, $my_debut, $downloads, $debut));
		
		// Updates
		$my_last_update = convert_date_to_days_till_date($my_last_update);
		$last_update = convert_date_to_days_till_date($last_update);
		
		if($last_update < $my_last_update)
		{
			$score += 5;
		}
		else if($last_update > $my_last_update)
		{
			$score -= 5;
		}
		else
		{
			$score += 0;
		}

		// Content rating
		if($content_array[$content_rating] > $content_array[$my_content_rating])
		{
			$score += 5;
		}
		else if($content_array[$content_rating] < $content_array[$my_content_rating])
		{
			$score -= 5;
		}
		else
		{
			$score += 0;
		}
		
		// Reviews
		$reviews = exec("python android.py");
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
		
		$results_obtained = array(
			"name" => $title,
			"score" => $score
		);
		
		return $results_obtained;
	}
	
?>