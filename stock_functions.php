<?php
function fitLine () {

}

function solveIntercept($xMean, $yMean, $slope) {
	$intercept = $yMean - ($slope * $xMean);

	#var_dump($intercept);
	
	return $intercept;
}

function predictor() {
	$x = array(1,2,3,4,5);
	$y = (array) getClosingPrices();
	$xMean = getXMean();
	$yMean = getYMean($y);
	$slope = getSlope($xMean, $x, $yMean, $y);
	var_dump($slope);
	$Yintercept = solveIntercept($xMean, $yMean, $slope);

	
	return 100;
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
