<?php
//Connect to database
require('db.php');
$connection = getConnection();

// If user is not logged in, set English as default
if(isset($_SESSION['user'])){
	$language = $_SESSION['user']['language'];
}
else{
	$language = 'english';
}
$language = loadLanguage($language);

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
	global $language;
	return $language[$id];
}

function salt($password, $salt){
	$number =1;
	for ($i=0; $i < strlen($salt); $i++) { 
		$number = $number* ord($salt[$i]);
	}
	while ($number<= 1000000000) {
		$number=$number*7;
	}
	$numberString= strval($number);
	for ($i=0; $i < 10; $i++) { 
			$password=$password.$numberString[$i];
	}
	return $password;
}
?>
