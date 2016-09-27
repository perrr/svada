<?php

session_start();

require('util.php');

$id = $connection->real_escape_string($_GET['id']);
$fileQuery = getQuery("SELECT path FROM file WHERE id = '$id'");
$file = $fileQuery -> fetch_assoc();
$path = 'uploads/' . $file['path'];
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($path) . "\"");
readfile($path);

//Close connection to database
mysqli_close($connection);

?>