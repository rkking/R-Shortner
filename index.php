<?php 

/*
author: RK king

This is main page of R Shortner

*/

require_once 'includes/config.php'; // Loading core configuration of site
require_once 'includes/functions.php'; // Loading R Shortner Functions

$rurl = new RShortner(); // Calls the constructor of class which will establish the DB Connection.
$msg = '';

// if the form has been submitted by user
if ( isset($_POST['longurl']) )
{
	// escape bad characters from the user's url
	$longurl = trim(mysql_real_escape_string($_POST['longurl']));

	// set the protocol to not ok by default
	$protocol_ok = false;
	
	// if there's a list of allowed protocols, 
	// check to make sure that the user's url uses one of them
	if ( count($allowed_protocols) )
	{
		foreach ( $allowed_protocols as $ap )
		{
			if ( strtolower(substr($longurl, 0, strlen($ap))) == strtolower($ap) )
			{
				$protocol_ok = true;
				break;
			}
		}
	}
	else // if there's no protocol list, screw all that
	{
		$protocol_ok = true;
	}
		
	// add the url to the database
	if ( $protocol_ok && $rurl->add_url($longurl) )
	{
		if ( REWRITE ) // mod_rewrite style link
		{
			$url = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/'.$rurl->get_id($longurl);
		}
		else // regular GET style link
		{
			$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?id='.$rurl->get_id($longurl);
		}

		$msg = '<p class="success">Your Shorted URL is: <a href="'.$url.'">'.$url.'</a></p>';
	}
	elseif ( !$protocol_ok )
	{
		$msg = '<p class="error">We dont Allow Shorting of that kind or Url!</p>';
	}
	else
	{
		$msg = '<p class="error">Creation of your short URL failed!</p>';
	}
}
else // if the form hasn't been submitted, look for an id to redirect to
{
	if ( isSet($_GET['id']) ) // check GET first
	{
		$id = mysql_real_escape_string($_GET['id']);
	}
	elseif ( REWRITE ) // check the URI if we're using mod_rewrite
	{
		$explodo = explode('/', $_SERVER['REQUEST_URI']);
		$id = mysql_real_escape_string($explodo[count($explodo)-1]);
	}
	else // otherwise, just make it empty
	{
		$id = '';
	}
	
	// if the id isn't empty and it's not this file, redirect to it's url
	if ( $id != '' && $id != basename($_SERVER['PHP_SELF']) )
	{
		$location = $rurl->get_url($id);
		
		if ( $location != -1 )
		{
			header('Location: '.$location);
		}
		else
		{
			$msg = '<p class="error">Sorry, but that URL isn\'t in our database.</p>';
		}
	}
}

// print the form

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>

	<head>
		<title><?php echo PAGE_TITLE; ?></title>
		
		<style type="text/css">
		body {
			font: .8em "Trebuchet MS", Verdana, Arial, Sans-Serif;
			text-align: center;
			color: #333;
			background-color: #5E5E5E;
			margin-top: 5em;
		}
		
		h1 {
			font-size: 5em;
			padding: 0;
			margin: 0;
			color:#fff;
		}

		h4 {
			font-size: 1.5em;
			font-weight: bold;
		}
		
		form {
			width: 60em;
			height: 8em;
			background-color: #eee;
			border: 1px solid #ccc;
			margin-left: auto;
			margin-right: auto;
			padding: 1em;
		}

		fieldset {
			border: 0;
			margin: 0;
			padding: 0;
		}
		
		a {
			color: #09c;
			text-decoration: none;
			font-weight: bold;
		}

		a:visited {
			color: #07a;
		}

		a:hover {
			color: #c30;
		}

		.error, .success {
			font-size: 2.0em;
			font-weight: bold;
		}
		
		.error {
			color: #ff0000;
		}
		
		.success {
			color: #000;
		}
		
		</style>

	</head>
	
	<body onload="document.getElementById('longurl').focus()">
		
		<h1><?php echo PAGE_TITLE; ?></h1>
		
		<?php echo $msg; ?>
		
		<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
		
			<fieldset>
				<label for="longurl"><h4>Enter your long URL:</h4></label>
				<input type="text" name="longurl" id="longurl" />
				<input type="submit" name="submit" id="submit" value="Short It!" />
			</fieldset>
		
		</form>

		<small>Powered by <a href="https://github.com/rkking/R-Shortner">R Shortner</a></small>
	
	</body>

</html>