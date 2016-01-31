<?php

session_start();

require('util.php');
//Check authorization here

function postMessage($content, $author, $timestamp) {
	setQuery("INSERT INTO message (content, author, timestamp)
	VALUES ('$content', '$author', '$timestamp')");
}

function getMessages($lastReceivedId) {
	$newMessages = getQuery("SELECT * FROM message WHERE message .id > $lastReceivedId");
	printJson(sqlToJson($newMessages));
}

function getMessagesNewerThan($timeLimit) {
	$limitMessages = getQuery("SELECT * FROM message WHERE message .timestamp > $timeLimit");
	printJson(sqlToJson($limitMessages));
}

function getOnlineUsers() {
	$onlineUsers = getQuery("SELECT id, status FROM user WHERE status != 0");
	printJson(sqlToJson($newMessages));
}

function setProfilePicture($userid, $imageid) {
	setQuery("UPDATE user
		SET image='$imageid'
		WHERE id='$userid'");
}

function setStatusMessage($userid, $statusMessage) {
	setQuery("UPDATE user 
	SET status_message='$statusMessage'
	WHERE id='$userid'");
}

function setStatus($userId, $status) {
	setQuery("UPDATE user SET status = $status WHERE id = $userId");
}

function getAllUsers() {
	$users = getQuery("SELECT id, username, display_name, status, image FROM user");
	printJson(sqlToJson($users));
}

function editMessage($messageId, $content) {
	setQuery("UPDATE message
		SET content='$content', edit=1
		WHERE id='$messageId'");
}

function setHighPriorityUserInformation($userId, $status, $isTyping) {
	//Insert code here
}

function setLowPriorityUserInformation($userId, $statusMessage, $imageId) {
	//Insert code here
}

function searchMessages($string, $caseSensitive, $userId) {
	//Insert code here
}

function setPassword($userId, $newPassword, $oldPassword) {
	$row = mysqli_fetch_assoc(getQuery("SELECT password 
		FROM user 
		WHERE id ='$userId'"));
	$correctPassword = $row['password'];
	if (md5($oldPassword) == $correctPassword) {
		echo "correctPassword";
	}
	if (md5($oldPassword) == $correctPassword){
		$hashedNewPassword = md5($newPassword);
		setQuery("UPDATE user
		SET password = '$hashedNewPassword'
		WHERE id='$userId'");
		return "";
	}
	else {
		return '{"error": "Incorrect old password."}';
	}
}

function getAllEmoticons() {
	$emotes = getQuery("SELECT * FROM emoticon");
	printJson(sqlToJson($emotes));
}


function setTopic($topic, $userId) {
	//Insert code here
}

//Escape all input
$_GET = escapeArray($_GET);

//Handle actions
if(isset($_GET['user']) && $_GET['user'] != $_SESSION['user']['id']){
	printJson('{"error": "Invalid action."}');
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
