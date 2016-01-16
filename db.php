<?php
if (!$dbh=mysql_connect('localhost','root',''))
	die("Could not connect to database.");
mysql_select_db('svada');
mysql_query('SET NAMES utf8');
?>