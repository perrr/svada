<?php

session_start();

require('util.php');
//Check authorization here

function postMessage($content, $author) {
	$timestamp = time();
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
	printJson(sqlToJson($onlineUsers));
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

function setLanguage($userId, $language) {
	//Insert code here
}

function getAllUsers() {
	$userQuery =getQuery("SELECT id, username, display_name, status, status_message, image, is_typing FROM user");
	$users = array();
	$i=1;
	while ($row = mysqli_fetch_assoc($userQuery)) {
		$users[$i++] = $row;
	}
	$users[0] = $users[$_SESSION['user']['id']];
	printJson(json_encode($users, JSON_NUMERIC_CHECK));
}

function editMessage($messageId, $content) {
	setQuery("UPDATE message
		SET content='$content', edit=1
		WHERE id='$messageId'");
}

function setHighPriorityUserInformation($userId, $status, $isTyping) {
	setQuery("UPDATE user
		SET status = '$status', is_typing = '$isTyping'
		WHERE id='$userId'");
}

function setLowPriorityUserInformation($userId, $statusMessage, $imageId) {
	setQuery("UPDATE user
		SET status_message = '$statusMessage', image = '$imageId'
		WHERE id='$userId'");
}

function searchMessages($string, $caseSensitive, $userId) {
	if($userId==0){
		if($caseSensitive){
			$messages = getQuery("SELECT *
				FROM message
				WHERE BINARY content LIKE '%".$string."%'");
		}
		else{
			$messages = getQuery("SELECT *
				FROM message
				WHERE content LIKE '%".$string."%'");
		}
	}
	else{
		if($caseSensitive){
			$messages = getQuery("SELECT *
				FROM message
				WHERE author ='$userId' AND  BINARY content LIKE '%".$string."%'");
		}
		else{
			$messages = getQuery("SELECT *
				FROM message
				WHERE author ='$userId' AND content LIKE '%".$string."%'");
		}
	}
	printJson(sqlToJson($messages));
}

function setPassword($userId, $newPassword, $oldPassword) {
	$row = mysqli_fetch_assoc(getQuery("SELECT password 
		FROM user 
		WHERE id ='$userId'"));
	$correctPassword = $row['password'];
	if (md5($oldPassword) == $correctPassword){
		$hashedNewPassword = md5($newPassword);
		setQuery("UPDATE user
		SET password = '$hashedNewPassword'
		WHERE id='$userId'");
		return "";
	}
	else {
		return '{"error":'.getString("incorrectOldPassword").'}';
	}
}

function getAllEmoticons() {
	$emotes = getQuery("SELECT * FROM emoticon");
	printJson(sqlToJson($emotes));
}

function getAllImages() {
	$images = getQuery("SELECT * FROM image");
	printJson(sqlToJson($images));
}

function setTopic($topic, $userId) {
	setQuery("UPDATE chat
		SET topic = '$topic'");
	$content='<'. $userId.'> changed the topic of this conversation to  <span class="message-strong">' . $topic . '</span>'; 
	postMessage($content, $userId);
}

function getTopic() {
	$topic = getQuery("SELECT topic FROM chat");
	printJson(sqlToJson($topic));
}

function setChatImage($image, $userId) {
	setQuery("UPDATE chat
		SET image = '$image'");
	$content='<'. $userId.'> changed the group image of this conversation'; 
	postMessage($content, $userId);
}

function getChatImage() {
	$image = getQuery("SELECT image FROM chat");
	printJson(sqlToJson($image));

}

function getChatInformation() {
	$chatInformation = getQuery("SELECT * FROM chat");
	printJson(sqlToJson($chatInformation));
}

//Escape all input
$_GET = escapeArray($_GET);

//Handle actions
if($_GET['action'] == 'postMessage') {
	postMessage($_GET['content'], $_SESSION['user']['id']);
}
elseif($_GET['action'] == 'getMessages') {
	getMessages($_GET['lastReceivedId']);
}
elseif($_GET['action'] == 'getStatus') {
	setStatus($_SESSION['user']['id'], $_GET['status']);
}
elseif($_GET['action'] == 'getAllUsers') {
	getAllUsers();
}
elseif($_GET['action'] == 'editMessage') {
	editMessage($_GET['message'], $_GET['content']);
}
elseif($_GET['action'] == 'setPassword') {
	setPassword($_SESSION['user']['id'], $_GET['newPassword'], $_GET['oldPassword']);
}
elseif($_GET['action'] == 'getAllEmoticons') {
	getAllEmoticons();
}
elseif($_GET['action'] == 'getAllImages') {
	getAllImages();
}
elseif($_GET['action'] == 'getTopic') {
	getTopic();
}
elseif($_GET['action'] == 'getOnlineUsers') {
	getOnlineUsers();
}
elseif($_GET['action'] == 'setProfilePicture') {
	setProfilePicture($_SESSION['user']['id'], $_GET['image']);
}
elseif($_GET['action'] == 'setStatusMessage') {
	setStatusMessage($_SESSION['user']['id'], $_GET['statusMessage']);
}
elseif($_GET['action'] == 'setHighPriorityUserInformation') {
	setHighPriorityUserInformation($_SESSION['user']['id'], $_GET['status'], $_GET['isTyping']);
}
elseif($_GET['action'] == 'setLowPriorityUserInformation') {
	setLowPriorityUserInformation($_SESSION['user']['id'], $_GET['statusMessage'], $_GET['imageId']);
}
elseif($_GET['action'] == 'searchMessages') {
	searchMessages($_GET['string'], $_GET['caseSensitive'], (int) $_GET['userId']);
}
elseif($_GET['action'] == 'setTopic') {
	setTopic($_GET['topic'], $_GET['userId']);
}
elseif($_GET['action'] == 'setChatImage') {
	setChatImage($_GET['image'], $_GET['userId']);
}
elseif($_GET['action'] == 'getChatImage') {
	getChatImage();
}
elseif($_GET['action'] == 'getChatInformation') {
	getChatInformation();
}
//Close connection to database
mysqli_close($connection);

?>
