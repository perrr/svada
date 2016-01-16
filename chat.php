<?php
session_start();

//If there's no session for this visitor, redirect him out of here
if(!isset($_SESSION['user'])){
	header('Location: index.php');
	die();
}

//Connect to database
require('db.php');

//For convenience, store session in a variable with a shorter name
$user = $_SESSION['user'];

//Preload some messages. We should probably set a date cap or something?
$messages = mysql_query("SELECT * FROM message, user WHERE message.author = user.id");

while ($message = mysql_fetch_array($messages)) {
	echo $message['display_name'].': '.$message['content'].' ('.date('H:i:s', $message['timestamp']).')<br>';
}

?>
