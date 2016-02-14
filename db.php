<?php

function getConnection() {
	if (!$connection=mysqli_connect('localhost', 'root', '', 'svada'))
		die("Could not connect to database.");
	mysqli_query($connection, 'SET NAMES utf8');
	return $connection;
}

?>