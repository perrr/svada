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
		<?php
		while ($message = mysql_fetch_array($messages)) {
			echo $message['display_name'].': '.$message['content'].' ('.date('H:i:s', $message['timestamp']).')<br>';
		}
		?>
		
		<!-- Scripts -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/functions.js"></script>
		<script src="js/ajax.js"></script>
	</body>
</html>