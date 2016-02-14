<?php
session_start();

//If there's no session for this visitor, redirect him out of here
if(!isset($_SESSION['user'])){
	header('Location: index.php');
	die();
}

require('util.php');

//For convenience, store session in a variable with a shorter name
$user = $_SESSION['user'];

function printPercentage($number, $total) {
	$perc =($number / $total) * 100;
	echo ' ('.round($perc, 2).' %)';
}

//Database queries
$messagesTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message"));
$messages = $messagesTable['COUNT(*)'];
echo 'Number of messages: ';
echo $messages;
$users = getQuery("SELECT id, username FROM user");
echo '<br><br>';
foreach ($users as $user) {
	$id = $user['id'];
	$userMessagesTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE author = $id"));
	$userMessages = $userMessagesTable['COUNT(*)'];
	echo $user['username'].': ';
	echo $userMessages;
	printPercentage($userMessages, $messages);
	echo '<br>';
}
echo '<br>';
$skypeTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 1"));
$skype = $skypeTable['COUNT(*)'];
echo 'Messages from Skype: ';
echo $skype;
printPercentage($skype, $messages);
echo '<br>';
$notSkypeTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 0"));
$notSkype = $notSkypeTable['COUNT(*)'];
echo 'Messages not from Skype: ';
echo $notSkype;
printPercentage($notSkype, $messages);
echo '<br><br>';
$lengthTable = mysqli_fetch_assoc(getQuery("SELECT AVG(LENGTH(content)) FROM message"));
$length = $lengthTable['AVG(LENGTH(content))'];
echo 'Average message length: ';
echo round($length, 2);
echo '<br><br>';
foreach ($users as $user) {
	$id = $user['id'];
	$userLengthTable = mysqli_fetch_assoc(getQuery("SELECT AVG(LENGTH(content)) FROM message WHERE author = $id"));
	$userLength = $userLengthTable['AVG(LENGTH(content))'];
	echo $user['username'].': ';
	echo round($userLength, 2);
	echo '<br>';
}

close();

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>Svada Chat Client</title>
		<meta name="generator" content="Bootply" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<!--[if lt IE 9]>
			<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<link href="css/styles.css" rel="stylesheet">
	</head>
	<body>
		
		<!-- Scripts -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/functions.js"></script>
		<script src="js/ajax.js"></script>
	</body>
</html>