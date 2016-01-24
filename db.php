<?php
//if (!$dbh=mysql_connect('localhost','root',''))
//	die("Could not connect to database.");
//mysql_select_db('svada');
//mysql_query('SET NAMES utf8');

function getConnection() {
	if (!$connection=mysqli_connect('localhost', 'root', '', 'svada'))
		die("Could not connect to database.");
	mysqli_query($connection, 'SET NAMES utf8');
	return $connection;
}

?>