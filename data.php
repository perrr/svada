<?php

session_start();

require('util.php');
//Check authorization here


//MESSAGES
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

function getMessage($id) {
	$message = getQuery("SELECT * FROM message WHERE id = $id");
	printJson(sqlToJson($message));
}

function getMessages($lastReceivedId) {
	$newMessages = getQuery("SELECT * FROM message WHERE message .id > $lastReceivedId");
	printJson(sqlToJsonArray($newMessages));
}

function getMessagesNewerThan($timeLimit) {
	$limitMessages = getQuery("SELECT * FROM message WHERE message .timestamp > $timeLimit");
	printJson(sqlToJsonArray($limitMessages));
}

function getRecentMessages() {
	$lastDate = mysqli_fetch_array(getQuery("SELECT timestamp FROM message ORDER BY timestamp DESC LIMIT 1"));
	$date = new DateTime();
	$date->setTimestamp($lastDate['timestamp']);
	$date->setTime(0,0,0);
	$timestamp= $date->getTimestamp();
	$recentMessages = getQuery("SELECT * FROM message WHERE message .timestamp > $timestamp");
	printJson(sqlToJsonArray($recentMessages));
}

function getNextMessages($lastTimestamp){
	$lastDate2 = mysqli_fetch_array(getQuery("SELECT timestamp FROM message WHERE message .timestamp < $lastTimestamp ORDER BY timestamp DESC LIMIT 1"));
	$date2 = new DateTime();
	$date2->setTimestamp($lastDate2['timestamp']);
	$date2->setTime(0,0,0);
	$timestamp2= $date2->getTimestamp();
	$nextMessages = getQuery("SELECT * FROM message WHERE message .timestamp > $timestamp2 AND message .timestamp < $lastTimestamp");
	printJson(sqlToJsonArray($nextMessages));
}
//USERS
function getOnlineUsers() {
	$onlineUsers = getQuery("SELECT id, status FROM user WHERE status != 0");
	printJson(sqlToJsonArray($onlineUsers));
}

function setUserImage($userid, $imageid) {
	setQuery("UPDATE user
		SET image='$imageid'
		WHERE id='$userid'");
}

function setStatusMessage($userid, $statusMessage) {
	$statusMessage = htmlentities($statusMessage, null, null, false);
	setQuery("UPDATE user 
	SET status_message='$statusMessage'
	WHERE id='$userid'");
}

function setDisplayName($userid, $displayName) {
	$displayName = htmlentities($displayName, null, null, false);
	if (!empty($displayName)){		
		setQuery("UPDATE user 
		SET display_name='$displayName'
		WHERE id='$userid'");
	}
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
	$userData = getQuery("SELECT u.id, username, display_name, status, status_message, image, is_typing, l.name AS language, mute_sounds FROM user AS u, language AS l WHERE u.id = '$user' AND u.language = l.id");
	$userData = $userData->fetch_assoc();
	printJson(json_encode($userData, JSON_NUMERIC_CHECK));

}

function editMessage($user, $messageId, $content) {
	//5*60=300 is the maximum amount of time ou can wait before editing a message
	global $connection;
	$timestamp = time() -300;
	$currentTimestamp = time();
	setQuery("UPDATE message
		SET content='$content', edit=1
		WHERE id='$messageId' AND author = '$user' AND timestamp>'$timestamp'");
	if(($connection->affected_rows)>0){
		setQuery("INSERT INTO edited_message (message,  timestamp) VALUES ('$messageId', '$currentTimestamp')");
		postMessage("Melding er redigert", 1);
	}
}

function getRecentlyEditedMessages(){
	$twoMinutesAgo = time() - 120;
	$editedMessagesQuery = getQuery("SELECT message FROM edited_message WHERE timestamp > '$twoMinutesAgo'");
	$editedMessages = $editedMessagesQuery -> fetch_assoc();
	printJson(json_encode($editedMessages, JSON_NUMERIC_CHECK));
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
	printJson(sqlToJsonArray($messages));
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

function getAllEmoticons() {
	$emotes = getQuery("SELECT * FROM emoticon");
	printJson(sqlToJsonArray($emotes));
}

function getAllImages() {
	$images = getQuery("SELECT * FROM file");
	printJson(sqlToJsonArray($images));
}


//CHAT
function setChatName($chatName, $userId) {
	$chatName = htmlentities($chatName, null, null, false);
	if ($chatName != null && $chatName != ""){
		setQuery("UPDATE chat
			SET name = '$chatName'");
		$content='{username|'.$userId.'} {lang|'."changedchatname".'} <span class="message-strong">' . $chatName .'.</span>'; 
		postMessage($content, 0);	
	}
}

function setTopic($topic, $userId) {
	$topic = htmlentities($topic, null, null, false);
	setQuery("UPDATE chat
		SET topic = '$topic'");
	$content='{username|'.$userId.'} {lang|'."changedtopic".'} <span class="message-strong">' . $topic .'.</span>'; 
	postMessage($content, 0);
}

function getTopic() {
	$topic = getQuery("SELECT topic FROM chat");
	printJson(sqlToJsonArray($topic));
}

function setChatImage($image, $userId) {
	setQuery("UPDATE chat
		SET image = '$image'");
	$content='{username|'.$userId.'} {lang|'."changedGroupImage".'}.'; 
	postMessage($content, 0);
}

function getChatImage() {
	$image = getQuery("SELECT image FROM chat");
	printJson(sqlToJsonArray($image));

}

function getChatInformation() {
	$chatInformation = getQuery("SELECT * FROM chat");
	printJson(sqlToJsonArray($chatInformation));
}


//FILE UPLOADING
function uploadFile($file, $uploader, $share, $uploadType){
	$savePath = "uploads/";
	$chatResult = getQuery("SELECT * FROM chat");
	$chatAssoc = $chatResult -> fetch_assoc();
	$maxSize = $chatAssoc["maximum_file_size"] * 1024 * 1024;
	if ($uploadType == "file") {
		shareFiles($file, $uploader, $share, $maxSize);
	}
	else {
		uploadUserOrChatImage($file, $uploader, $savePath, $maxSize, $uploadType);
	}
}

function shareFiles($file, $uploader, $share, $maxSize){
		$savePath = "uploads/";
		global $connection;
		for ($i=0; $i < count($file["name"]) ; $i++) { 
	 		$originalFileName = $connection->real_escape_string($file["name"][$i]);
	 		$fileExtension = substr($originalFileName, strrpos($originalFileName, '.'));
  			$uploadTime = time();
  			$fileSize = $file["size"][$i];
  			//Create unique id for file
			$fileIdresult = getQuery("SELECT * FROM file WHERE id=(SELECT MAX(id) FROM file)");
			$newFileIdAssoc = $fileIdresult -> fetch_assoc();
			$newFileId = $newFileIdAssoc["id"] + 1;
			//Format for filename 'id.fileExtension'
  			$newFileName = $newFileId.$fileExtension;
  			
  			if(strlen($originalFileName)>255){
  				$originalFileName = substr($originalFileName, 0, 255-strlen($fileExtension));
  				$originalFileName = $originalFileName.$fileExtension;
  			}

  			if($fileSize > $maxSize){
  				printJson('{"status": "failure", "message": " '. getString('theFile') .' '. $originalFileName .' '. getString('fileIsTooLarge') .' ('.getString('maxFileSize').' '.$chatAssoc["maximum_file_size"].'MB)."}');
  				return;
  			}
  			//Add to database 
  			$mime = mime_content_type($file['tmp_name'][$i]);
  			setQuery("INSERT INTO file (path, uploader, name, mime_type, timestamp) VALUES ('$newFileName', '$uploader', '$originalFileName', '$mime', '$uploadTime')");
  			$success = move_uploaded_file($file['tmp_name'][$i], $savePath.$newFileName);
  			if(!$success){
  				printJson('{"status": "failure", "message": "' . getString('uploadFailed') . '"}');
  				return;
  			}
  			if($share == 1){
  				shareAlreadyUploadedFile($newFileId, $uploader);
  			}
	} 
	printJson('{"status": "success", "message": " '.getString('theFile'). ' ' . $originalFileName . ' ' . getString('wasUploaded') . '"}');
}

function uploadUserOrChatImage($file, $uploader, $savePath, $maxSize, $type){
	$originalFileName = $file["name"][0];
  	$uploadTime = time();
  	$fileSize = $file["size"][0];
  	//Create unique id for file
	$fileIdresult = getQuery("SELECT * FROM file WHERE id=(SELECT MAX(id) FROM file)");
	$newFileIdAssoc = $fileIdresult -> fetch_assoc();
	$newFileId = $newFileIdAssoc["id"] + 1;
	//check if file is an image:
	$mime = mime_content_type($file['tmp_name'][0]);
	if(!(strstr($mime, "image/"))) {
    	printJson('{"status": "failure", "message": " '. $originalFileName . ' ' . getString('notAnImage'). '."}');
		return;
	}
	//Format for filename 'id.fileExtension'
  	$newFileName = $newFileId.substr($originalFileName, strrpos($originalFileName, '.'));
  		
  	if($fileSize > $maxSize){
  		printJson('{"status": "failure", "message": " '. $originalFileName . ' ' . getString('fileIsTooLarge') . '."}');
  		return;
  	}
  	//Add to database 
  	setQuery("INSERT INTO file (path, uploader, name, mime_type, timestamp) VALUES ('$newFileName', '$uploader', '$originalFileName','$mime', '$uploadTime')");
  	$success = move_uploaded_file($file['tmp_name'][0], $savePath.$newFileName);
  	if($success && $type == "userImage"){
  		setUserImage($uploader, $newFileId);
  		printJson('{"status": "success", "message": " '.getString('theFile'). ' ' . $originalFileName . ' ' . getString('wasUploaded') . '."}');
  	}
  	elseif(($success && $type == "chatImage")){
  		setChatImage($newFileId, $uploader);
  		printJson('{"status": "success", "message": " '.getString('theFile'). ' ' . $originalFileName . ' ' . getString('wasUploaded') . '."}');
  	}
  	else{
  		printJson('{"status": "success", "message": "' . getString('uploadFailed') . '."}');
  	}
}

function shareAlreadyUploadedFile($id, $user){
	$content = '{username|'.$user.'} {lang|'."userUploadedFile".'} {file|' . $id .'}.';
	postMessage($content, 0);
}


//Escape all input
$_GET = escapeArray($_GET);

//Handle actions
if($_GET['action'] == 'postMessage') {
	postMessage($_GET['content'], $_SESSION['user']['id']);
}
elseif($_GET['action'] == 'getMessage') {
	getMessage($_GET['id']);
}
elseif($_GET['action'] == 'getMessages') {
	getMessages($_GET['lastReceivedId']);
}
elseif($_GET['action'] == 'getRecentMessages') {
	getRecentMessages();
}
elseif($_GET['action'] == 'getNextMessages') {
	getNextMessages($_GET['lastTimestamp']);
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
	setUserImage($_SESSION['user']['id'], $_GET['image']);
}
elseif($_GET['action'] == 'setStatusMessage') {
	setStatusMessage($_SESSION['user']['id'], $_GET['statusMessage']);
}
elseif($_GET['action'] == 'setDisplayName') {
	setDisplayName($_SESSION['user']['id'], $_GET['displayName']);
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
	uploadFile($_FILES['files'], $_SESSION['user']['id'], $_POST['share'], $_POST['uploadType']);
}
elseif($_GET['action'] == 'pingServer') {
	printJson('{"running": true}');
}
elseif($_GET['action'] == 'shareUploadedFile'){
	shareAlreadyUploadedFile($_GET['fileId'], $_SESSION['user']['id']);
}
elseif($_GET['action'] == 'getRecentlyEditedMessages'){
	getRecentlyEditedMessages();
}
//Close connection to database
mysqli_close($connection);

?>
