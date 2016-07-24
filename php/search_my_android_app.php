<?php
	
	/* 
		Information about my app will be stored in session variables 
		and accessed later instead of fetching again, during comparison.
	*/
	//error_reporting(0);
	session_start();
	
	// Unset session set previously 
	session_unset();
	
	$ACCESS_TOKEN = "ed9cbc7cd3e0521ae2a2a30201989d16b9b7da7f";
	$URL = 'http://42matters.com/api/1/apps/search.json';
	
	
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
		exit("Please check your internet connection and try again.");
	}
	// Result is in JSON format.
	$results = json_decode($result, true);
	
	// No apps found
	if($results['number_results'] === 0)
	{
		exit("No app found. Verify spelling and try again.");
	}
	else
	{
		$results_returned = $results['results'][0];
		
		
		$title = $results_returned['title'];
		$debut = $results_returned['created'];
		$developer = $results_returned['developer'];
		$content_rating = $results_returned['content_rating'];
		$last_update = $results_returned['market_update'];
		$package_name = $results_returned['package_name'];
		
		// In MB
		$size = ($results_returned['size'] / (1024*1024));
		
		// Not able to obtain for some apps. 
		if($size == 0)
		{
			$size = "Varies with device.";
		}
		
		$category = $results_returned['category'];
		
		// To test is the app is a game or not
			// cat_type => 0 -> Non game, 1 -> Game
		$is_game = $results_returned['cat_type'];
		if($is_game == 1)
			$category_str = "Game: ". $category;
		else
			$category_str = $category;
			
		$price = $results_returned['price_numeric'];
		$icon_pic = $results_returned['icon_72'];
		
		// In-app purchases
		$iap = $results_returned['iap'];
		if($iap == 1)
			$iap_str = "Yes";
		else if($iap == 0)
			$iap_str = "No";
		
		$rating = $results_returned['rating'];
		$number_of_ratings = $results_returned['number_ratings'];
		$downloads = $results_returned['downloads'];
		
?>
		<center>
			<img alt="App Icon" src= <?php echo $icon_pic ?>></img></br></br>
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
				<td><center><?php echo number_format($rating, 3) ?></center></td>
			</tr>		
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Downloads</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $downloads ?></center></td>
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
				<td><center><?php echo $price . " USD" ?></center></td>
			</tr>
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">In-app Purchases</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $iap_str ?></center></td>
			</tr>		
			
			<tr>	
				<th><center><b style="color:#1299ee; font-size:20px;">Category</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo $category_str ?></center></td>
			</tr>		
			
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
				<th><center><b style="color:#1299ee; font-size:20px;">Last Updated on</b></center></th>
			</tr>
			<tr>
				<td><center><?php echo substr($last_update, 0, 10) ?></center></td>
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
		$_SESSION['content_rating'] = $content_rating;
		$_SESSION['last_update'] = substr($last_update, 0, 10);
		$_SESSION['debut'] = substr($debut, 0, 10);
		$_SESSION['category'] = $category;
		$_SESSION['iap'] = $iap;
		$_SESSION['size'] = number_format($size, 2);
		$_SESSION['downloads'] = $downloads;
		$_SESSION['package_name'] = $package_name;
		
		// Write package to a file
		$fp = fopen("package.txt","w");
		fwrite($fp,$package_name);
		fclose($fp);
		
		$reviews = exec("python android.py");
		$_SESSION['reviews'] = $reviews;
	}
?>