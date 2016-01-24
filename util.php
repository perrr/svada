<?php
//Connect to database
require('db.php');

$connection = getConnection();

function sqlToJson($sql) {
	$json = array();
	while ($row = mysqli_fetch_assoc($sql)) {
		$json[] = $row;
	}
	echo json_encode($json, JSON_NUMERIC_CHECK);
}

function escapeArray($array){
	$keys = array_keys($array);
	foreach ($keys as  $key) {
	 	$array[$key] = mysql_real_escape_string($array[$key]);
	 }
	 return $array;
}

function getQuery($query) {
	global $connection;
	$sql = mysqli_query($connection, $query) or die(mysql_error());
	return $sql;
}

function setQuery($query) {
	global $connection;
	mysqli_query($connection, $query) or die(mysql_error());
}

?>
