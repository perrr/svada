<?php
//Connect to database
require('db.php');
require('util.php');

//Check authorization here

function postMessage($content, $author, $timestamp) {
	mysql_query("INSERT INTO message (content, author, timestamp)
	VALUES ('$content', '$author', '$timestamp')") or die(mysql_error());
}

function getMessages($lastReceivedId) {
	$newMessages = mysql_query("SELECT * FROM message WHERE message .id > $lastReceivedId");
	echo sqlToJson($newMessages);
}

function getOnlineUsers() {
	$onlineUsers = mysql_query("SELECT id, status FROM user WHERE status != 0");
	echo sqlToJson($onlineUsers);
}



function setStatusMessage($userid, $status_message){
	mysql_query("UPDATE user 
	SET status_message='$status_message'
	WHERE id='$userid'");

function setStatus($userId, $status) {
	mysql_query("UPDATE user SET status = $status WHERE id = $userId");
}

function getAllUsers() {
	$users = mysql_query("SELECT id, username, display_name, status, image FROM user");
	echo sqlToJson($users);
}

function editMessage($messageId, $content) {
	mysql_query("UPDATE message
		SET content='$content', edit=1
		WHERE id='$messageId'");
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
