<?php
session_start();

//If there's no session for this visitor, redirect him out of here
if(!isset($_SESSION['user'])){
	header('Location: index.php');
	die();
}

//For convenience, store session in a variable with a shorter name
$user = $_SESSION['user'];
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
		<div class="container" id="chat-top">
		  -topic-
		</div>
		<div class="container" id="chat-bottom">
			<div class="row">
				<div class="col-sm-2" id="sidebar">
					-users-
				</div>
				<div class="col-sm-10" id="mainbar">
					<div id="messages"></div>
					<textarea id="message-text-field"></textarea> 
				</div>
			</div>
		</div>
		
		<!-- Scripts -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/global.js"></script>
		<script src="js/functions.js"></script>
		<script src="js/ajax.js"></script>
		<script src="js/listeners.js"></script>
	</body>
</html>