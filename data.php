<?php
//Connect to database
require('db.php');

//Check authorization here

function postMessage($content, $author, $timestamp) {
	//Insert code here
	//mysql_query("INSERT INTO message (content, author)
	//VALUES ('Hei!', 1)") or die(mysql_error());
}

function getMessages($lastReceivedId) {
	//Insert code here
}

function getOnlineUsers(){
	//Insert code here
}

function setStatus($user, $status) {
	//Insert code here
}

function getAllUsers() {
	//Insert code here
}

function editMessage($message, $content) {
	//Insert code here
}

//Handle actions
if($_GET['action'] == 'postMessage') {
	postMessage($_GET['content'], $_GET['user'], $_GET['timestamp']);
}
elseif($_GET['action'] == 'getMessages') {
	getMessages($_GET['lastReceivedId']);
}
elseif($_GET['action'] == 'getStatus') {
	setStatus($_GET['user'], $_GET['status']);
}
elseif($_GET['action'] == 'getAllUsers') {
	getAllUsers();
}
elseif($_GET['action'] == 'editMessage') {
	editMessage($_GET['message'], $_GET['content']);
}

?>
