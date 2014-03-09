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
	require_once('stock_functions.php');
	$ticker_sym = "AAPL";
	$extrap = $_POST['extrap'];

	if (isset($extrap)){
		# Before extrapolating tomorrow price udpate DB
		updateDB($ticker_sym);

		# preforms linear regression on the closing 
		# prices of AAPL for the last 5 days of data
		$prediction = predictor();
		echo "<h1>$prediction</h1>";
		
	}
?>


<body>
	<FORM ACTION="sbbrg.php" method="post">
		<INPUT TYPE="submit" name=extrap id=extrap VALUE="Extrapolate Data">
	</FORM>
</body>
</html>
