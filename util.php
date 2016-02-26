<?php
//Connect to database
require('db.php');

$connection = getConnection();

function sqlToJson($sql) {
	$json = array();
	while ($row = mysqli_fetch_assoc($sql)) {
		$json[] = $row;
	}
	return json_encode($json, JSON_NUMERIC_CHECK);
}

function escapeArray($array){
	global $connection;
	$keys = array_keys($array);
	foreach ($keys as  $key) {
	 	$array[$key] = $connection->real_escape_string($array[$key]);
	}
	return $array;
}

function getQuery($query) {
	global $connection;
	$sql = mysqli_query($connection, $query) or die(mysqli_error($connection));
	return $sql;
}

function setQuery($query) {
	global $connection;
	mysqli_query($connection, $query) or die(mysqli_error($connection));
}

function printJson($json) {
	 header("Content-Type: application/json");
	 echo $json;
}

function loadLanguage($language) {
	$path ="lang/".$language.".json";
	$languageFile = file_get_contents($path);
	$languageFile = mb_convert_encoding($languageFile,'HTML-ENTITIES', "UTF-8");
	$languageArray = json_decode($languageFile, true);
	
	return $languageArray;
}

function getString($id) {
	//Insert code here
}

?>
