<?php
//Connect to database
require('db.php');
require('lib/password.php');


if (basename($_SERVER["SCRIPT_FILENAME"]) != 'install.php') {
	$connection = getConnection();

	if(!isset($_SESSION['user']))
		renewSession();
}

// If user is not logged in, set English as default
if(isset($_SESSION['user'])){
	$language = getQuery("SELECT name FROM user AS u, language AS l WHERE l.id = u.language AND u.id = ".$_SESSION['user']['id']);
	$language = $language->fetch_assoc();
	$language = $language['name'];
}
else{
	$language = 'english';
}
$language = loadLanguage($language);

// To be used if we want to use a specified connection
function setConnection($conn) {
	$connection = $conn;
}

function isLoggedIn() {
	if (!isset($_SESSION['user'])) {
		return false;
	}

	$databaseSession = mysqli_fetch_array(getQuery("SELECT * FROM user_session WHERE id = ".$_SESSION['user']['id']));
	return !empty($databaseSession);
}

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

function getStyle($style){
	$result = getQuery("SELECT * FROM style WHERE id='$style'");
	$result = $result -> fetch_assoc();
	$html = '<link href="css/styles/'.$result['css'].'" rel="stylesheet">' . PHP_EOL . '<link href="css/markup/'.$result['markup'].'" rel="stylesheet">';
	return $html;
}

function getString($id) {
	global $language;
	return $language[$id];
}


function verifyQuotes($message) {
	$pattern = '/(<div class="quote" data-messageid=")(.*)(" contenteditable="false">)(.*)(<)/';
	preg_match_all($pattern, $message, $matches, PREG_OFFSET_CAPTURE);
	for ($i=0; $i < count($matches[0]); $i++) { 
		$messageId= $matches[2][$i][0];
		$quote= $matches[4][$i][0];
		$result = getQuery("SELECT content FROM message WHERE id = '$messageId'");
		$originalText =  $result ->fetch_assoc();
		echo $originalText['content'];
		echo $quote;
		if(strpos($originalText['content'], $quote)===false){
			return false;
		}
	}
	return true;
}

function updateUserSession() {
	$_SESSION['user'] = $user = mysqli_fetch_array(getQuery("SELECT * FROM user WHERE id = ".$_SESSION['user']['id']));
}

function renewSession() {
	if(isset($_COOKIE['usercookie'])){
		$cookie = $_COOKIE['usercookie'];
		$cookieResult = mysqli_fetch_array(getQuery("SELECT id FROM user_session WHERE token ='$cookie'"));
		if(!empty($cookieResult)){
			$id = $cookieResult['id'];
			$_SESSION['user'] = mysqli_fetch_array(getQuery("SELECT * FROM user WHERE id ='$id'"));
		}
	}
}
?>
