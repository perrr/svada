<?php

session_start();

require('util.php');
//Check authorization here

function postMessage($content, $author) {
	$timestamp = time();
	if(notSpam($author, $timestamp-5) && verifyQuotes($content)){
	setQuery("INSERT INTO message (content, author, timestamp)
	VALUES ('$content', '$author', '$timestamp')");
}
}
function notSpam($author, $timestamp) {
	$messages = (getQuery("SELECT count(*) as count
		FROM message 
		WHERE author = '$author' AND timestamp>'$timestamp'"));
	$result = $messages->fetch_assoc();
	return ($result['count']<5);
}

function getMessages($lastReceivedId) {
	$newMessages = getQuery("SELECT * FROM message WHERE message .id > $lastReceivedId");
	printJson(sqlToJson($newMessages));
}

function getMessagesNewerThan($timeLimit) {
	$limitMessages = getQuery("SELECT * FROM message WHERE message .timestamp > $timeLimit");
	printJson(sqlToJson($limitMessages));
}

function getRecentMessages() {
	$lastDate = mysqli_fetch_array(getQuery("SELECT timestamp FROM message ORDER BY timestamp DESC LIMIT 1"));
	$date = new DateTime();
	$date->setTimestamp($lastDate['timestamp']);
	$date->setTime(0,0,0);
	$timestamp= $date->getTimestamp();
	$recentMessages = getQuery("SELECT * FROM message WHERE message .timestamp > $timestamp");
	printJson(sqlToJson($recentMessages));
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
	setQuery("UPDATE user SET language = '$language' WHERE id = '$userId'");
}

function setIsTyping($userId, $isTyping) {
	setQuery("UPDATE user SET is_typing = '$isTyping' WHERE id = '$userId'");
}

function getAllUsers() {
	$userQuery =getQuery("SELECT id, display_name, status, status_message, image, is_typing FROM user");
	$users = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($userQuery)) {
		$users[$i++] = $row;
	}
	printJson(json_encode($users, JSON_NUMERIC_CHECK));
}

function getUser() {
	$user = $_SESSION['user']['id'];
	$userData = getQuery("SELECT id, username, display_name, status, status_message, image, is_typing, language, mute_sounds FROM user WHERE id = '$user'");
	$userData = $userData->fetch_assoc();
	printJson(json_encode($userData, JSON_NUMERIC_CHECK));

}

function editMessage($user, $messageId, $content) {
	//5*60=300 is the maximum amount of time ou can wait before editing a message
	$timestamp = time() -300;
	setQuery("UPDATE message
		SET content='$content', edit=1
		WHERE id='$messageId' AND author = '$user' AND timestamp>'$timestamp'");
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
	if (md5(salt($oldPassword, $_SESSION['user']['username'])) == $correctPassword){
		$hashedNewPassword = md5(salt($newPassword, $_SESSION['user']['username']));
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
	$images = getQuery("SELECT * FROM file");
	printJson(sqlToJson($images));
}

function setChatName($chatName, $userId) {
	if ($chatName != null && $chatName != ""){
		setQuery("UPDATE chat
			SET name = '$chatName'");
		$content='<username|'.$userId.'> <lang|'."changedchatname".'>  <span class="message-strong">' . $chatName .'. </span>'; 
		postMessage($content, 1);	
	}
}

function setTopic($topic, $userId) {
	setQuery("UPDATE chat
		SET topic = '$topic'");
	$content='<username|'.$userId.'> <lang|'."changedtopic".'>  <span class="message-strong">' . $topic .'. </span>'; 
	postMessage($content, 1);
}

function getTopic() {
	$topic = getQuery("SELECT topic FROM chat");
	printJson(sqlToJson($topic));
}

function setChatImage($image, $userId) {
	setQuery("UPDATE chat
		SET image = '$image'");
	$content='<username|'.$userId.'> <lang|'."changedGroupImage".'>'; 
	postMessage($content, 0);
}

function getChatImage() {
	$image = getQuery("SELECT image FROM chat");
	printJson(sqlToJson($image));

}

function getChatInformation() {
	$chatInformation = getQuery("SELECT * FROM chat");
	printJson(sqlToJson($chatInformation));
}

function uploadFile($file, $uploader, $share){
	$save_path = "uploads/";
	$chatResult = getQuery("SELECT * FROM chat");
	$chatAssoc = $chatResult -> fetch_assoc();
	$maxSize = $chatAssoc["maximum_file_size"] * 1024 * 1024;
	for ($i=0; $i < count($file["name"]) ; $i++) { 
	 	$originalFileName = $file["name"][$i];
  		$uploadTime = time();
  		$fileSize = $file["size"][$i];
  		$v = print_r($file, true);
  		//Create unique id for file
		$fileIdresult = getQuery("SELECT * FROM file WHERE id=(SELECT MAX(id) FROM file)");
		$newFileIdAssoc = $fileIdresult -> fetch_assoc();
		$newFileId = $newFileIdAssoc["id"] + 1;

		//Format for filename 'id.fileExtension'
  		$newFileName = $newFileId.substr($originalFileName, strrpos($originalFileName, '.'));
  		
  		if($fileSize > $maxSize){
  			printJson('{"status": "failure", "message": " '. $originalFileName . ' ' . getString('fileIsTooLarge') . '"}');
  			return;
  		}
  		//Add to database 
  		setQuery("INSERT INTO file (path, uploader, name, timestamp) VALUES ('$newFileName', '$uploader', '$originalFileName', '$uploadTime')");
  		$success = move_uploaded_file($file['tmp_name'][$i], $save_path.$newFileName);
  		if($share == 1){
  			$content = '<username|'.$uploader.'> <lang|'."userUploadedFile".'> <span class="message-strong"> <file|' . $newFileId .'>. </span>';
  			postMessage($content, 0);
  		}

	 } 

	printJson('{"status": "success", "message": "' . getString('uploadSuccessful') . '"}');
}

function checkUserActivity($user) {
	$currentTimestamp = time();
	setQuery("UPDATE user
		SET last_activity = '$currentTimestamp'
		WHERE id ='$user'");
	$fiveMinutesAgo = time()-300;
	setQuery("UPDATE user
		SET status = '0' 
		WHERE last_activity<'$fiveMinutesAgo'");
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
elseif($_GET['action'] == 'getRecentMessages') {
	getRecentMessages();
}
elseif($_GET['action'] == 'setStatus') {
	setStatus($_SESSION['user']['id'], $_GET['status']);
}
elseif($_GET['action'] == 'getAllUsers') {
	getAllUsers();
}
elseif($_GET['action'] == 'editMessage') {
	editMessage($_SESSION['user']['id'],$_GET['message'], $_GET['content']);
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
	setTopic($_GET['topic'], $_SESSION['user']['id']);
}
elseif($_GET['action'] == 'setChatName') {
	setChatName($_GET['chatName'], $_SESSION['user']['id']);
}
elseif($_GET['action'] == 'setChatImage') {
	setChatImage($_GET['image'], $_SESSION['user']['id']);
}
elseif($_GET['action'] == 'getChatImage') {
	getChatImage();
}
elseif($_GET['action'] == 'getChatInformation') {
	getChatInformation();
}
elseif($_GET['action'] == 'setLanguage') {
	setLanguage($_SESSION['user']['id'], $_GET['language']);
}
elseif($_GET['action'] == 'setIsTyping') {
	setIsTyping($_SESSION['user']['id'], $_GET['isTyping']);
}
elseif($_GET['action'] == 'checkUserActivity') {
	checkUserActivity($_SESSION['user']['id']);
}
elseif($_GET['action'] == 'getUser') {
	getUser();
}
elseif($_GET['action'] == 'upload') {
	uploadFile($_FILES['files'], $_SESSION['user']['id'], $_POST['share']);
}

//Close connection to database
mysqli_close($connection);

?>
