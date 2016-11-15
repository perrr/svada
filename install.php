<?php

function createIniFile($host, $username, $password, $databaseName) {
	$content = "host = ".$host;
	$content.= "\nusername = ".$username;
	$content.= "\npassword = ".$password;
	$content.= "\ndb_name = ".$databaseName;

	mkdir("./conf");
	mkdir("./uploads");
	$file = fopen("./conf/settings.ini", "w");
	fwrite($file, $content);
	fclose($file);
}

if (isset($_POST["ip"])) {

	if ($_POST["chat"] == "")
		echo "Chat name cannot be empty";

	elseif ($_POST["username"] == "")
		echo "Username cannot be empty";

	elseif ($_POST["display"] == "")
		echo "Display name cannot be empty";

	elseif ($_POST["password"] == "")
		echo "Password cannot be empty";

	elseif ($_POST["password2"] != $_POST["password"])
		echo "Passwords do not match!";

	else {
		//Connect to database
		$connection = @new mysqli($_POST["ip"], $_POST["db_user"], $_POST["db_password"]);

		if ($connection->connect_error) {
		    echo("Connection failed: " . $connection->connect_error);
		}

		else {
			foreach($_POST as $key => &$value) {
	    		$value = $connection->real_escape_string($value);
			}

			//Create database
			mysqli_query($connection, "CREATE DATABASE IF NOT EXISTS ".$_POST["db_name"]) or die(mysqli_error($connection));
			require('util.php');
			setConnection($connection);
			setQuery("USE ".$_POST["db_name"]);
			//Create tables
			
			setQuery("DROP TABLE IF EXISTS `chat`");
			setQuery("CREATE TABLE `chat` (
			  `name` varchar(20) NOT NULL,
			  `topic` text NOT NULL,
			  `image` int(11) DEFAULT NULL,
			  `maximum_file_size` int(11) DEFAULT 200,
			  `stats` longtext NOT NULL,
  			  `stats_timestamp` int(11) NOT NULL DEFAULT '0'
			) ENGINE=InnoDB DEFAULT CHARSET=latin1");
			setQuery('INSERT INTO `chat` (`name`, `topic`, `stats`) VALUES
			("'.$connection->real_escape_string($_POST["chat"]).'", "", "")');

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
			  `name` varchar(255) NOT NULL,
			  `mime_type` varchar(255) NOT NULL,
			  `uploader` int(11) NOT NULL,
			  `timestamp` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1");

			setQuery("DROP TABLE IF EXISTS `message`");
			setQuery("CREATE TABLE `message` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `content` text NOT NULL,
			  `author` int(11) NOT NULL,
			  `timestamp` int(11) NOT NULL,
			  `edit` int(11) NOT NULL DEFAULT '0',
			  `skype` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1");

			setQuery("DROP TABLE IF EXISTS `style`");
			setQuery("CREATE TABLE `style` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(30) NOT NULL,
			  `css` varchar(30) NOT NULL,
			  `primarycolor` varchar(30) NOT NULL,
			  `secondarycolor` varchar(30) NOT NULL,
			  `backgroundcolor` varchar(30) NOT NULL,
			  `scrollbar` varchar(30) NOT NULL,
			  `markup` varchar(30) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1");
			setQuery("INSERT INTO `style` (`id`, `name`, `css`, `primarycolor`, `secondarycolor`, `backgroundcolor`, `scrollbar`, `markup`) VALUES
			(1, 'Standard', 'standard.css', '#428BCA', '#EDF9FC', '#FFFFFF', 'dark-thick', 'highlight.xcode.css')");

			setQuery("DROP TABLE IF EXISTS `user`");
			setQuery("CREATE TABLE `user` (
			  `username` varchar(20) NOT NULL,
			  `display_name` varchar(30) NOT NULL,
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `password` varchar(255) NOT NULL,
			  `online` int(11) NOT NULL DEFAULT '0',
			  `status` int(11) NOT NULL DEFAULT '1',
			  `status_message` varchar(100) NOT NULL,
			  `image` int(11) DEFAULT NULL,
			  `is_typing` int(11) NOT NULL DEFAULT '0',
			  `language` int(11) NOT NULL DEFAULT '1',
			  `mute_sounds` int(11) NOT NULL DEFAULT '0',
			  `last_activity` int(11) NOT NULL DEFAULT '0',
			  `style` int(11) NOT NULL DEFAULT '1',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1");
			setQuery('INSERT INTO `user` (`username`, `display_name`, `password`, `status_message`) VALUES 
				("'.strtolower($connection->real_escape_string($_POST['username'])).'", "'.$connection->real_escape_string($_POST["display"]).'", "'.password_hash($connection->real_escape_string($_POST['password']), PASSWORD_DEFAULT).'", "")');
			
			setQuery("DROP TABLE IF EXISTS `user_session`");
			setQuery("CREATE TABLE `user_session` (
			  `id` int(11) NOT NULL,
			  `token` varchar(255) DEFAULT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=latin1");
			
			setQuery("DROP TABLE IF EXISTS `edited_message`");
			setQuery("CREATE TABLE `edited_message` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `message` int(11) NOT NULL,
			  `timestamp` int(11) NOT NULL,
			  PRIMARY KEY(`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1");

			setQuery("DROP TABLE IF EXISTS `language`");
			setQuery("CREATE TABLE `language` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(30) DEFAULT NULL,
			  `local_name` varchar(30) DEFAULT NULL,
			  PRIMARY KEY(`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1");
			setQuery('INSERT INTO `language` (`name`, `local_name`) VALUES ("english", "English")');
			setQuery('INSERT INTO `language` (`name`, `local_name`) VALUES ("norwegian", "Norsk")');
			
			//Write to .ini file
			createIniFile($_POST["ip"], $_POST["db_user"], $_POST["db_password"], $_POST["db_name"]);

			// Redirect browser
			header("Location: ./index.php");

			//Delete this file upon completion
			//unlink(__FILE__);
		}
	}
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
			Database password: <input type="password" name="db_password"><br>
			Name of database: <input type="text" name="db_name"><br>
			Name of chat: <input type="text" name="chat"><br>
			Your username: <input type="text" name="username"><br>
			Your displayname: <input type="text" name="display"><br>
			Your password: <input type="password" name="password"><br>
			Repeat password: <input type="password" name="password2"><br>
			<input type="submit">
		</form>
		<!-- Scripts -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/functions.js"></script>
		<script src="js/ajax.js"></script>
	</body>
</html>