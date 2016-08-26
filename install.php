<?php

if (isset($_POST["ip"])) {
	//Connect to database
	// THIS DOES NOT WORK BECAUSE WE HAVE NO CONNECTION YET, NEED TO FIX THIS
	$connection = new mysqli($_POST["ip"], $_POST["db_user"], $_POST["db_password"]);
	//$connection = new mysqli($connection->real_escape_string($_POST["ip"]), $connection->real_escape_string($_POST["db_user"]), $connection->real_escape_string($_POST["db_password"]));
	if ($connection->connect_error) {
	    die("Connection failed: " . $connection->connect_error);
	}
	//Create database
	mysqli_query($connection, "CREATE DATABASE svada" or die(mysqli_error($connection)));

	require('util.php');

	//Create tables
	setQuery("DROP TABLE IF EXISTS `chat`");
	setQuery("CREATE TABLE `chat` (
	  `name` varchar(20) NOT NULL,
	  `topic` text NOT NULL DEFAULT '',
	  `image` int(11) NOT NULL DEFAULT '0'
	) ENGINE=InnoDB DEFAULT CHARSET=latin1");
	setQuery('INSERT INTO `chat` (`name`) VALUES
	("'.$connection->real_escape_string($_POST["chat"]).'")');

	setQuery("DROP TABLE IF EXISTS `emoticon`");
	setQuery("CREATE TABLE `emoticon` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `path` varchar(30) NOT NULL,
	  `name` varchar(20) NOT NULL,
	  `shortcut` text NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1");

	setQuery("DROP TABLE IF EXISTS `file`");
	setQuery("CREATE TABLE `file` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `path` varchar(30) NOT NULL,
	  `uploader` int(11) NOT NULL,
	  `timestamp` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1");

	setQuery("DROP TABLE IF EXISTS `message`");
	setQuery("CREATE TABLE `message` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `content` text NOT NULL,
	  `author` int(11) NOT NULL,
	  `timestamp` int(11) NOT NULL,
	  `edit` int(11) NOT NULL DEFAULT '0',
	  `skype` int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=latin1");

	setQuery("DROP TABLE IF EXISTS `style`");
	setQuery("CREATE TABLE `style` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(30) NOT NULL,
	  `css` varchar(30) NOT NULL,
	  `markup` varchar(30) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1");
	setQuery("INSERT INTO `style` (`id`, `name`, `css`, `markup`) VALUES
	(1, 'Standard', 'standard.css', 'highlight.xcode.css')");

	setQuery("DROP TABLE IF EXISTS `user`");
	setQuery("CREATE TABLE `user` (
	  `username` varchar(20) NOT NULL,
	  `display_name` varchar(30) NOT NULL,
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `password` varchar(255) NOT NULL,
	  `status` int(11) NOT NULL DEFAULT '0',
	  `status_message` varchar(100) NOT NULL,
	  `image` int(11) DEFAULT NULL,
	  `is_typing` int(11) NOT NULL DEFAULT '0',
	  `language` varchar(20) NOT NULL DEFAULT 'english',
	  `mute_sounds` int(11) NOT NULL DEFAULT '0',
	  `last_activity` int(11) NOT NULL DEFAULT '0',
	  `style` int(11) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1");
	setQuery('INSERT INTO `user` (`username`, `display_name`, `password`) VALUES 
		("'.strtolower($connection->real_escape_string($_POST['username'])).'", "'.$connection->real_escape_string($_POST["display"]).'", "'.md5(salt($connection->real_escape_string($_POST['password']), $_POST['username'])).'")');
	
	setQuery("DROP TABLE IF EXISTS `user_session`");
	setQuery("CREATE TABLE `user_session` (
	  `id` int(11) NOT NULL,
	  `token` varchar(33) DEFAULT NULL
	) ENGINE=MyISAM DEFAULT CHARSET=latin1");

	//Delete this file upon completion
	//unlink(__FILE__);
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>Install Svada Chat Client</title>
		<meta name="generator" content="Bootply" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<!--[if lt IE 9]>
			<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<link href="css/styles.css" rel="stylesheet">
	</head>
	<body>
		<form action="install.php" method="post">
			Database host: <input type="text" name="ip"><br>
			Database username: <input type="text" name="db_user"><br>
			Database password: <input type="text" name="db_password"><br>
			Name of chat: <input type="text" name="chat"><br>
			Your username: <input type="text" name="username"><br>
			Your displayname: <input type="text" name="display"><br>
			Your password: <input type="text" name="password"><br>
			<!--Repeat password: <input type="text" name="password2"><br>-->
			<input type="submit">
		</form>
		<!-- Scripts -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/functions.js"></script>
		<script src="js/ajax.js"></script>
	</body>
</html>