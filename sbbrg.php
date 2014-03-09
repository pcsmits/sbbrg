<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Least Squares Linear Regression</title>
	<!--
	   <link rel="icon" 
	      type="image.png" 
	      href="../images/icon.jpg" />
	<link href="../templates/default.css" rel="stylesheet" type="text/css" media="all" />
	-->
</head>

<?php
	$mysql_db = "uwwiscus_sbbrg";
	$mysql_user = "uwwiscus_sbbrg";
	$mysql_pass = "sbbrg#@!";
	$dbconn = mysql_connect("localhost", $mysql_user, $mysql_pass);
	mysql_select_db($mysql_db, $dbconn);

	$update = $_POST['updatedb'];
	if (isset($update)){
		#query db for last inserted date 
		$sql = "SELECT closing_date FROM stocks WHERE ticker_symbol='AAPL' ORDER BY closing_date DESC LIMIT 1";
		$result = mysql_query($sql, $dbconn) or die(var_dump(mysql_error()));
		if(mysql_num_rows($result)) {
			$startDate = mysql_fetch_row($result);
		} else {
			echo "No rows returned";
		}

		#query closing price up to the date for 'x' stock
		# To-Do make yahoo api function	 
		$ticker_sym = "AAPL";

		# Format the current date for the query
		date_default_timezone_set('UTC');
		$currDate = date("c");
		$endDate = explode("T", $currDate);

		# setting up yahoo api call
		$yahoo_base_api = "http://query.yahooapis.com/v1/public/yql";
		$query = "select Close from yahoo.finance.historicaldata where symbol='".$ticker_sym."' and startDate='".$startDate[0]."' and endDate='".$endDate[0]."'";

		var_dump($query);

		$yql_query = $yahoo_base_api . "?q=" . urlencode($query);
		$yql_query .= "&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

		var_dump($yql_query);


		# TO-DO make curl Function	
		$session = curl_init($yql_query);  
		curl_setopt($session, CURLOPT_RETURNTRANSFER,true);      
		$json = curl_exec($session);
		
		$phpObj =  json_decode($json); 
		var_dump($phpObj);
		


	}
?>


<body>
	<FORM ACTION="sbbrg.php" method="post">
		<INPUT TYPE="submit" name=updatedb id=updatedb VALUE="Update Database">
	</FORM>
</body>
</html>
