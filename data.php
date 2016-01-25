<?php

session_start();

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

function getMessagesNewerThan($timeLimit) {
	$limitMessages = mysql_query("SELECT * FROM message WHERE message .timestamp > $timeLimit");
	echo sqlToJson($limitMessages);
}

function getOnlineUsers() {
	$onlineUsers = mysql_query("SELECT id, status FROM user WHERE status != 0");
	echo sqlToJson($onlineUsers);
}

function setProfilePicture($userid, $imageid) {
	mysql_query("UPDATE user
		SET image='$imageid'
		WHERE id='$userid'");
}

function setStatusMessage($userid, $statusMessage){
	mysql_query("UPDATE user 
	SET status_message='$statusMessage'
	WHERE id='$userid'");
}

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

function setPassword($userId, $newPassword, $oldPassword) {
	$correctPassword = mysql_result(mysql_query("SELECT password 
		FROM user 
		WHERE id ='$userId'"), 0);
	if(md5($oldPassword) == $correctPassword){
		echo "correctPassword";
		$hashedNewPassword = md5($newPassword);
		mysql_query(("UPDATE user
		SET password = '$hashedNewPassword'
		WHERE id='$userId'"));
		return "";
	}
	else{
		return '{"error": "Incorrect old password."}';
	}
}

function getAllEmoticons() {
	$emotes = mysql_query("SELECT * FROM emoticon");
	echo sqlToJson($emotes);
}
//Escape all input
$_GET = escapeArray($_GET);

//Handle actions
if(isset($_GET['user']) && $_GET['user'] != $_SESSION['user']['id']){
	echo '{"error": "Invalid action."}';
}
elseif($_GET['action'] == 'postMessage') {
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
elseif($_GET['action'] == 'setPassword') {
	setPassword($_GET['user'], $_GET['newPassword'], $_GET['oldPassword']);
}
elseif($_GET['action'] == 'getAllEmoticons') {
	getAllEmoticons();
}
elseif($_GET['action'] == 'getOnlineUsers') {
	getOnlineUsers();
}
elseif($_GET['action'] == 'setProfilePicture') {
	setProfilePicture($_GET['user'], $_GET['image']);
}
elseif($_GET['action'] == 'setStatusMessage') {
	setStatusMessage($_GET['user'], $_GET['statusMessage']);
}
?>
