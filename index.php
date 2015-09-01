<!DOCTYPE HTML>
<html>

<head>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="js/sum.js"></script>

	<link rel="stylesheet" type="text/css" href="layout.css">
	<link rel="stylesheet" type="text/css" href="index.css" />
	<link rel="icon" type="image/png" href="/images/favicon.png" />

	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
	<meta content="utf-8" http-equiv="encoding">

	<title>Trade Assistant</title>
</head>

<body>
	<div id="navbar">
		<a href="help.html">How To / Info</a>
		<a href="#">Settings</a>
		<a href="calculator.html">Item Calculator</a>
		<a href="index.php">Home</a>
	</div>

	<a href="?"><h1>TRADE ASSISTANT</h1></a>

	<!-- This Credit is a little obnoxious I know, feel free to remove it.
		I would appreciate if you could credit me somewhere but it's not required as long as you do not claim my work is your own. -->
	<p id="credit">Created by <a href="http://shane-brown.ca/" target="_blank">Shane "SajeOne" Brown</a><br /></p>

	<form>
		<p>Enter Trade or Inventory URL:</p><br />
		<input id="textview" type="text" name="url"/>
		<br />
		<input type="submit" value="Fetch Data"/>
	</form>
</body>

</html>

<?php

function handleTrade($url, $host, $db, $user, $pass){
	$multiArray = TradeTranslator::getTrade($_GET["url"]);
	if($multiArray === FALSE){
		echo 'Failed to fetch trade.';
		return;
	}

	$total = array(0.0, 0.0, 0.0, 0.0);

	echo "<h2 id=\"trader\">Trader's Items:</h2> <br />";
	echo '<table id="left" style="width: 100%">';
	echo '<tr><td>Item Name:</td><td>Current Price:</td><td>Median Price:</td><td>Market Worth:</td><td>Volume:</td></tr>';
	for($i = 0; $i < count($multiArray[0]); $i++){
		$leftItem = fetchSqlData($multiArray[0][$i], $host, $db, $user, $pass);

		if($leftItem === FALSE){
			echo '<script>console.log("Manually Grabbing: ' . $multiArray[0][$i] . '");</script>';
			$leftItemJSON = TradeTranslator::getItemJSON($multiArray[0][$i]);
			$leftItem = new stdClass();
			if($leftItemJSON != FALSE){
				$leftItem->curPrice = TradeTranslator::getItemCurrentPrice($leftItemJSON);
				$leftItem->medPrice = TradeTranslator::getItemMedianPrice($leftItemJSON);
				$leftItem->taxPrice = $leftItem->curPrice - ($leftItem->curPrice * 0.15);
				$leftItem->volume = floatval(str_replace(",", "", TradeTranslator::getItemVolume($leftItemJSON)));
			}else{
				$leftItem->curPrice = 0.0;
				$leftItem->medPrice = 0.0;
				$leftItem->taxPrice = 0.0;
				$leftItem->volume = 0;
			}


		}else{
			echo '<script>console.log("SQL Grabbing: ' . $multiArray[0][$i] . '");</script>';
		}

		echo '<tr><td>' . $multiArray[0][$i] . '</td><td>CDN$ ' . number_format($leftItem->curPrice, 2) . '</td><td>CDN$ ' . number_format($leftItem->medPrice, 2) . '</td><td>CDN$ ' . number_format($leftItem->taxPrice, 2) . '</td><td>' . $leftItem->volume . '</td></tr>';

		$total[0] += $leftItem->curPrice;
		$total[1] += $leftItem->medPrice;
		$total[2] += $leftItem->taxPrice;
		$total[3] += $leftItem->volume;
	}
	echo '<tr><td>Total:</td><td>CDN$ ' . number_format($total[0], 2) . '</td><td>CDN$ ' . number_format($total[1], 2) . '</td><td>CDN$ ' . number_format($total[2], 2) . '</td><td>' . $total[3] . '</td></tr>';
	echo '</table>';

	$rightTotal = array(0.0, 0.0, 0.0, 0.0);

	echo '<h2 id="requested">Requested Items:</h2>';

	echo '<table id="right" style="width: 100%">';
	echo '<tr><td>Item Name:</td><td>Current Price:</td><td>Median Price:</td><td>Market Worth:</td><td>Volume:</td></tr>';
	for($i = 0; $i < count($multiArray[1]); $i++){
		$rightItem = fetchSqlData($multiArray[1][$i], $host, $db, $user, $pass);

		if($rightItem === FALSE){
			echo '<script>console.log("Manually Grabbing: ' . $multiArray[1][$i] . '");</script>';
			$rightItemJSON = TradeTranslator::getItemJSON($multiArray[1][$i]);
			$rightItem = new stdClass();
			if($rightItemJSON != FALSE){
				$rightItem->curPrice = TradeTranslator::getItemCurrentPrice($rightItemJSON);
				$rightItem->medPrice = TradeTranslator::getItemMedianPrice($rightItemJSON);
				$rightItem->taxPrice = $rightItem->curPrice - ($rightItem->curPrice * 0.15);
				$rightItem->volume = floatval(str_replace(",", "", TradeTranslator::getItemVolume($rightItemJSON)));
			}else{
				$rightItem->curPrice = 0.0;
				$rightItem->medPrice = 0.0;
				$rightItem->taxPrice = 0.0;
				$rightItem->volume = 0;
			}


		}else{
			echo '<script>console.log("SQL Grabbing: ' . $multiArray[1][$i] . '");</script>';
		}

		echo '<tr><td>' . $multiArray[1][$i] . '</td><td>CDN$ ' . number_format($rightItem->curPrice, 2) . '</td><td>CDN$ ' . number_format($rightItem->medPrice, 2) . '</td><td>CDN$ ' . number_format($rightItem->taxPrice, 2) . '</td><td>' . $rightItem->volume . '</td></tr>';
		$rightTotal[0] += $rightItem->curPrice;
		$rightTotal[1] += $rightItem->medPrice;
		$rightTotal[2] += $rightItem->taxPrice;
		$rightTotal[3] += $rightItem->volume;
	}
	echo '<tr><td>Total:</td><td>CDN$ ' . number_format($rightTotal[0], 2) . '</td><td>CDN$ ' . number_format($rightTotal[1], 2) . '</td><td>CDN$ ' . number_format($rightTotal[2], 2) . '</td><td>' . $rightTotal[3] . '</td></tr>';
	echo '</table>';

	if($rightTotal[0] < $total[0]){
		echo '<h3>Trade Status: <p style="color: green;">Good Trade</p></h3>';
	}else if($rightTotal[0] > $total[0]){
		echo '<h3>Trade Status: <p style="color: red;">Bad Trade</p></h3>';
	}else{
		echo '<h3>Trade Status: <p style="color: white;">Even Trade</p></h3>';
	}
}

function handleInventory($url, $host, $db, $user, $pass){
	$itemArray = InventoryTranslator::getInventory($url, $host, $db, $user, $pass);
	$name = InventoryTranslator::getSteamName($url);
	if($itemArray === FALSE){
		echo 'Failed to fetch inventory';
		return;
	}

	if($name === FALSE){
		$name = "User";
	}

	$total = array(0.0, 0.0, 0.0, 0.0);

	echo "<h2 id=\"trader\">" . $name . "'s Inventory:</h2> <br />";
	echo '<table id="left" style="width: 100%">';
	echo '<tr><td>Item Name:</td><td>Current Price:</td><td>Median Price:</td><td>Market Worth:</td><td>Volume:</td></tr>';
	for($i = 0; $i < count($itemArray); $i++){
		$item = fetchSqlData($itemArray[$i]->name, $host, $db, $user, $pass);

		if($item === FALSE){
			echo '<script>console.log("Manually Grabbing: ' . $itemArray[$i]->name . '");</script>';
			$item = new stdClass();
			if(($itemJSON = TradeTranslator::getItemJSON($itemArray[$i]->name)) != FALSE){
				$item->curPrice = TradeTranslator::getItemCurrentPrice($itemJSON);
				$item->medPrice = TradeTranslator::getItemMedianPrice($itemJSON);
				$item->taxPrice = $item->curPrice - ($item->curPrice * 0.15);
				$item->volume = floatval(str_replace(",", "", TradeTranslator::getItemVolume($itemJSON)));
			}else{
				$item->curPrice = 0.0;
				$item->medPrice = 0.0;
				$item->taxPrice = 0.0;
				$item->volume = 0.0;
			}


		}else{
			echo '<script>console.log("SQL Grabbing: ' . $itemArray[$i]->name . '");</script>';
		}

		for($k = 0; $k < $itemArray[$i]->count; $k++){
			echo '<tr><td>' . $itemArray[$i]->name . '</td><td>CDN$ ' . number_format($item->curPrice, 2) . '</td><td>CDN$ ' . number_format($item->medPrice, 2)  . '</td><td>CDN$ ' . number_format($item->taxPrice, 2) . '</td><td>' . $item->volume . '</td></tr>';
			$total[0] += $item->curPrice;
			$total[1] += $item->medPrice;
			$total[2] += $item->taxPrice;
			$total[3] += $item->volume;
		}
	}
	echo '<tr><td>Total:</td><td>CDN$ ' . number_format($total[0], 2) . '</td><td>CDN$ ' . number_format($total[1], 2) . '</td><td>CDN$ ' . number_format($total[2], 2) . '</td><td>' . $total[3] . '</td></tr>';
	echo '</table>';
}

function fetchSqlData($item, $host, $db, $user, $pass){
	$sqlConn = @mysqli_connect($host, $user, $pass);

	if($sqlConn !== FALSE){
		mysqli_select_db($sqlConn, $db);

		$obj = new stdClass();
		$result = mysqli_query($sqlConn, "SELECT * FROM `items` WHERE `name`='" . $item . "'");
		if(mysqli_num_rows($result) > 0){
			$row = mysqli_fetch_assoc($result);
			$obj->curPrice = $row["current"];
			$obj->medPrice = $row["median"];
			$obj->taxPrice = $row["market"];
			$obj->volume = $row["volume"];
		}
		else
			return FALSE;

		return $obj;
	}else{
		return FALSE;
	}

}

/* START OF PROGRAM FLOW */

require 'server/InventoryTranslator.php';
require 'server/TradeTranslator.php';
require 'server/AssistantUtility.php';

$sqlData = AssistantUtility::readSettingsFile("settings.ini");
if($sqlData === FALSE){
	$sqlData = new stdClass();

	// Default values, manual fetching will still work
	$sqlData->host = "localhost";
	$sqlData->db = "csgo";
	$sqlData->user = "user";
	$sqlData->pass = "pass";
}

if(isset($_GET['url'])){
	$url = $_GET['url'];
	if(strpos($url, "csgolounge.com/trade") !== FALSE){
		handleTrade($url, $sqlData->host, $sqlData->db, $sqlData->user, $sqlData->pass);
	}else if(strpos($url, "steamcommunity.com")){
		handleInventory($url, $sqlData->host, $sqlData->db, $sqlData->user, $sqlData->pass);
	}else if(strpos($url, "csgolounge.com/profile")){
		if(strlen($url) >= 50){
			$index = strpos($url, "profile?id=") + 11;
			handleInventory("http://steamcommunity.com/profiles/" . substr($url, $index), $sqlData->host, $sqlData->db, $sqlData->user, $sqlData->pass);
		}else{
			echo 'Malformed URL';
		}

	}else{
		echo 'Malformed URL';
	}
}

?>
