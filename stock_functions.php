<?php
function fitLine () {

}

function solveSlope() {

}

function solveIntercept() {

}

function predictor() {


	return 100;
}

function getClosingPrices() {
	$dbconn = getDBConnection();

	# Retrieve first 5 entries and reverse them to match with x-values
	$sql = "SELECT closing_price FROM (SELECT closing_price, closing_date FROM stocks ORDER BY closing_date DESC LIMIT 5) AS reverseorder order by closing_date ASC";
	$result = mysql_query($sql, $dbconn) or die(var_dump(mysql_error()));

	if(mysql_num_rows($result)) {
		$y = (array) mysql_fetch_row($result);
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

?>
