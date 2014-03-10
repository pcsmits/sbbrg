<?php
function updateDB($ticker_sym){
	$dbconn = getDBConnection();

	#query db for last inserted date 
	$sql = "SELECT closing_date FROM stocks WHERE ticker_symbol='AAPL' ORDER BY closing_date DESC LIMIT 1";
	$result = mysql_query($sql, $dbconn) or die(var_dump(mysql_error()));

	if(mysql_num_rows($result)) {
		$dateArray = mysql_fetch_row($result);
		$startDate = $dateArray[0];
	} else {
		# get the last 5 results 7 days ago should have exactly 5 results
		$startDate = modifyDate(getTodaysDate(), '-1 week');
		$startDate = modifyDate($startDate, '-1 day');
	}
	
	# modify day to exclude the day that is already in the DB
	$startDate = modifyDate($startDate, '+1 day');

	# get today's date so the yahoo api knows when to stop
	$endDate = getTodaysDate();

	# API to yahoo finance using Yahoo Query Language
	$yql_query = buildAPIQuery($ticker_sym, $startDate, $endDate);

	# fetches and decodes api results
	$closePrices = curlAndDecodeAPI($yql_query);

	# insert updates Into DB
	insertStockUpdates($closePrices, $ticker_sym, $dbconn);

}

function insertStockUpdates($closePrices, $ticker_sym, $dbconn) {

	#loops over all new closing prices
	if(!is_null($closePrices->query->results)){
		$quote = $closePrices->query->results->quote;
		if(!is_object($quote)){

			# if an array of objects iterate over objects
			foreach ($quote as $vars){

				$price = $vars->Close;
				$date = $vars->Date;
				$sql = "INSERT INTO stocks VALUES ( \"$ticker_sym\", \"$price\", \"$date\")";
				if (!mysql_query($sql, $dbconn))
				{
					die('Error: ' . mysql_error());
				}
				echo "record added for $date<br>";
			}
		} else {

			#if only one result (just grab result)
			$price = $quote->Close;
			$date = $quote->Date;
			$sql = "INSERT INTO stocks VALUES ( \"$ticker_sym\", \"$price\", \"$date\")";
			if (!mysql_query($sql, $dbconn))
			{
				die('Error: ' . mysql_error());
			}
			echo "record added for $date<br>";
		}

	}
}



function curlAndDecodeAPI($yql_query) {

	$session = curl_init($yql_query);
	curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
	$json = curl_exec($session);

	$closePrices =  json_decode($json);
	return $closePrices;

}

function buildAPIQuery($ticker_sym, $startDate, $endDate) {
	# setting up yahoo api call
	$yahoo_base_api = "http://query.yahooapis.com/v1/public/yql";
	$query = "select Close, Date from yahoo.finance.historicaldata where symbol='".$ticker_sym."' and startDate='".$startDate."' and endDate='".$endDate."'";


	$yql_query = $yahoo_base_api . "?q=" . urlencode($query);
	$yql_query .= "&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
	return $yql_query;
}

function getTodaysDate(){
	# Format the current date for the query
	date_default_timezone_set('UTC');
	$currDate = date("c");
	$tmp = explode("T", $currDate);
	return $tmp[0];

}

function modifyDate($startDate, $modifier){
	$modifydate = new DateTime($startDate);
	$modifydate->modify($modifier);
	$startDate = $modifydate->format('Y-m-d');
	return $startDate;

}

function fitLine ($dayInFuture, $slope, $intercept) {
	return ($slope*(5+$dayInFuture)) + $intercept;

}

function solveIntercept($xMean, $yMean, $slope) {
	$intercept = $yMean - ($slope * $xMean);
	return $intercept;
}

function predictor() {
	$x = array(1,2,3,4,5);
	$y = (array) getClosingPrices();
	$xMean = getXMean();
	$yMean = getYMean($y);
	$slope = getSlope($xMean, $x, $yMean, $y);
	$Yintercept = solveIntercept($xMean, $yMean, $slope);
	$prediction = fitLine(1, $slope, $Yintercept);
	return $prediction;
}

function getClosingPrices() {
	$dbconn = getDBConnection();

	# Retrieve first 5 entries and reverse them to match with x-values
	$sql = "SELECT closing_price FROM (SELECT closing_price, closing_date FROM stocks ORDER BY closing_date DESC LIMIT 5) AS reverseorder order by closing_date ASC";
	$result = mysql_query($sql, $dbconn) or die(var_dump(mysql_error()));
	
	$yValues = array();
	if(mysql_num_rows($result)) {
		while($row =mysql_fetch_row($result)){
			array_push($yValues, $row[0]);
		}
	} else {
		echo "No rows returned";
		# get the last 5 results
	}
	return $yValues;
}


function getDBConnection() {
	$mysql_db = "uwwiscus_sbbrg";
	$mysql_user = "uwwiscus_sbbrg";
	$mysql_pass = "sbbrg#@!";
	$dbconn = mysql_connect("localhost", $mysql_user, $mysql_pass);
	mysql_select_db($mysql_db, $dbconn);

	return $dbconn;
}

function getSlope($xMean, $x, $yMean, $y){
	# first get x squared distance
	$xx = array();
	foreach ($x as $val) {
		array_push($xx, pow($val - $xMean, 2));
	}
	
	# x difference from mean
	$xdiff = array();
	foreach ($x as $val) {
		array_push($xdiff, $val - $xMean);
	}

	# Next get y difference
	$yy = array();
	$count = 0;
	foreach ($y as $val) {
		array_push($yy, $xdiff[$count] * ($val - $yMean));
		$count++;
	}

	return array_sum($yy)/array_sum($xx);

}

function getYMean($y) {
	$yMean = array_sum($y)/count($y);
	return $yMean;
}

function getXMean() {
	# X mean will always be 3
	# days (1-5)
	return 3;
}

?>
