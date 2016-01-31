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
$messages = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message"))['COUNT(*)'];
echo 'Number of messages: ';
echo $messages;
$users = getQuery("SELECT id, username FROM user");
echo '<br><br>';
foreach ($users as $user) {
	$id = $user['id'];
	$userMessages = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE author = $id"))['COUNT(*)'];
	echo $user['username'].': ';
	echo $userMessages;
	printPercentage($userMessages, $messages);
	echo '<br>';
}
echo '<br>';
echo 'Messages from Skype: ';
$skype = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 1"))['COUNT(*)'];
echo $skype;
printPercentage($skype, $messages);
echo '<br>';
echo 'Messages not from Skype: ';
$notSkype = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 0"))['COUNT(*)'];
echo $notSkype;
printPercentage($notSkype, $messages);

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