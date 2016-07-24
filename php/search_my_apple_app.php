<?php
	
	/* 
		Information about my app will be stored in session variables 
		and accessed later instead of fetching again, during comparison.
	*/
	error_reporting(0);
	session_start();
	
	// Unset previously set session
	session_unset();
	
	$ACCESS_TOKEN = "ed9cbc7cd3e0521ae2a2a30201989d16b9b7da7f";
	$URL = 'http://42matters.com/api/1/ios/apps/search.json';
	
	
	// Prevent SQL Injection.
	function clean($str) 
    {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) 
        {
			$str = stripslashes($str);
        }
		return mysql_real_escape_string($str);
	}
		
	$search_app = clean($_POST['search_app']);
	
	// Complete URL
	$request_url = $URL . '?access_token=' . $ACCESS_TOKEN . "&q=" . $search_app . "&limit=1";
	
	try
	{
		// Make the request
		$result = file_get_contents($request_url);
	}
	catch(Exception $e)
	{
		die("Please check your internet connection and try again.");
	}
	// Result is in JSON format.
	$results = json_decode($result, true);
	
	// No apps found
	if($results['number_results'] === 0)
	{
		die("No app found. Verify spelling and try again.");
	}
	else
	{
		$results_returned = $results['results'][0];
		
		
		$title = $results_returned['trackCensoredName'];
		$debut = $results_returned['releaseDate'];
		$developer = $results_returned['artistName'];
		$content_rating = $results_returned['trackContentRating'];
		$content_rating_int = substr($content_rating, 0, -1);
		
		// In MB
		$size = ($results_returned['fileSizeBytes'] / (1024*1024));
		
		// Not able to obtain for some apps. 
		if($size == 0)
		{
			$size = "Varies with device.";
		}
		
		$category = $results_returned['primaryGenreName'];
		$category_str = $category;
		// To test is the app is a game or not
			// Game and its sub category
		$is_game = 0;
		$genreId = $results_returned['primaryGenreId'];
		$genres = $results_returned['genres'];
		if($genres[0] == "Games")
		{
			$category = $genres[1];
			$is_game = 1;
			$genreId = $results_returned['genreIds'][1];
			$category_str = "Games: " . $category;
		}
			
		$price_str = $results_returned['formattedPrice'];
		$price = $results_returned['price'];
		$icon_pic = $results_returned['artworkUrl60'];
		
		$rating = $results_returned['averageUserRating'];
		$number_of_ratings = $results_returned['userRatingCount'];	

		// Unique ID for the app
		$id = $results_returned['trackId'];
		
		// Get the number of languages supported
		$num_lang_supported = sizeof($results_returned['languageCodesISO2A']);
		
		$tab_supported = $results_returned['ipad'];
		if($tab_supported)
		{
			$tab_supported_str = "Yes";
		}
		else
		{
			$tab_supported_str = "No";
		}
		
		// Supports game center
		$is_game_centered = $results_returned['isGameCenterEnabled'];
		if($is_game_centered)
		{
			$is_game_centered_str = "Yes";
		}
		else
		{
			$is_game_centered_str = "No";
		}
		
?>
		<center>
			<img alt="App Icon" height="60" width="60" src= <?php echo $icon_pic ?>></img></br></br>
			<b style="font-size: 25px; color:grey;"><?php echo $title ?></b>
		</center>
		</br>
		<table class="table table-striped table-bordered table-condensed">	
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Developer</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $developer ?></center></td>
			</tr>		
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Rating</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo number_format($rating, 2) ?></center></td>
			</tr>		
					
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Number of ratings</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $number_of_ratings ?></center></td>
			</tr>
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Size</b></center></th>
			</tr>
			<tr>
				<td><center><?php if(is_numeric($size))
						  {
							    echo number_format($size, 2) . " MB";
						  }
						  else
								echo($size); 
					?></center>
				</td>
			</tr>		
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Price</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $price_str ?></center></td>
			</tr>
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Tablet specific app</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $tab_supported_str ?></center></td>
			</tr>		
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Category</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $category_str ?></center></td>
			</tr>	
				
			<?php
				// Check if game center is enabled for the game. Should display only if app is game
				// App is game
				if($is_game[0] == "Games")
				{
			?>	
				<tr>	
					<th><center><b style="color:#1299ee; font-size:20px;">Game Center enabled</b></center></th>
				</tr>
				<tr>
					<td><center><?php echo $is_game_centered_str ?></center></td>
				</tr>	
					
			<?php	
				}
			?>
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Content Rating</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $content_rating ?></center></td>
			</tr>		
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Debut</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo substr($debut, 0, 10) ?></center></td>
			</tr>		
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Number of languages supported</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $num_lang_supported ?></center></td>
			</tr>		
					
		</table>
		<center>
			<button class = "btn btn-info btn-large" onclick="run_analytics();">Run Analytics</button>
		</center>
<?php
		
		// Setting up sessions
		$_SESSION['title'] = $title;
		$_SESSION['developer'] = $developer;
		$_SESSION['rating'] = number_format($rating, 3);
		$_SESSION['number_of_ratings'] = $number_of_ratings;
		$_SESSION['price'] = $price;
		$_SESSION['content_rating'] = $content_rating_int;
		$_SESSION['tab_supported'] = $tab_supported;
		$_SESSION['debut'] = substr($debut, 0, 10);
		$_SESSION['category'] = $category;
		$_SESSION['id'] = $id;
		$_SESSION['is_game'] = $is_game;
		$_SESSION['game_genre_id'] = $genreId;
		
		// Consider if only the app is a game
		$_SESSION['is_game_centered'] = $is_game_centered;
		
		$_SESSION['size'] = number_format($size, 2);
		$_SESSION['num_lang_supported'] = $num_lang_supported;
		
		// Write id to a file
		$fp = fopen("id.txt","w");
		fwrite($fp,$id);
		fclose($fp);
		
		$reviews = exec("python apple.py");
		$_SESSION['reviews'] = $reviews;
	}
?>