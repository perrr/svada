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
	//Insert code here
}

?>
