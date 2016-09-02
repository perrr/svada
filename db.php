<?php

function getConnection() {
		$dbDetails = parse_ini_file('./conf/settings.ini');
		if (!$dbDetails) {
			header("Location: ./install.php"); // Redirect browser
			exit();
		}
		
		if (!$connection=mysqli_connect($dbDetails['host'], $dbDetails['username'], $dbDetails['password'], 'svada'))
			die("Could not connect to database.");
		mysqli_query($connection, 'SET NAMES utf8');
		return $connection;
}

?>