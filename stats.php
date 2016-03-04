<?php

session_start();

//If there's no session for this visitor, redirect him out of here
if(!isset($_SESSION['user'])){
	header('Location: index.php');
	die();
}

require('util.php');

mb_internal_encoding('UTF-8');

//For convenience, store session in a variable with a shorter name
$user = $_SESSION['user'];

$emoticonSql = getQuery("SELECT shortcut FROM emoticon");
$shortcuts = [];
while ($row = mysqli_fetch_assoc($emoticonSql)) {
	$emoticonShortcuts = $row['shortcut'];
	$exploded = explode(' ', $emoticonShortcuts);
	foreach ($exploded as $shortcut) {
		$shortcuts[] = $shortcut;
	}
}

function isEmoticon($word, $shortcuts) {
	foreach ($shortcuts as $shortcut) {
		if ($word == $shortcut)
			return true;
	}
	return false;
}

function printPercentage($number, $total) {
	$perc = ($number / $total) * 100;
	echo ' ('.round($perc, 2).' %)';
}

function mostUsedWordsAndEmoticons($user, $shortcuts) {
	if ($user == null)
		$content = getQuery("SELECT content FROM message");
	else
		$content = getQuery("SELECT content FROM message WHERE author = $user");
	$words = array();
	$emoticons = array();
	$numWords = 0;
	$numEmoticons = 0;
	while ($row = mysqli_fetch_assoc($content)) {
		$message = $row['content'];
		$exploded = explode(' ', $message);
		foreach ($exploded as $word) {
			if (isEmoticon($word, $shortcuts)) {
				$numEmoticons++;
				if (array_key_exists($word, $emoticons))
					$emoticons[$word] += 1;
				else
					$emoticons[$word] = 1;
			}
			else {
				$stripped = preg_replace('/[^[:alnum:][:space:]]/u', '', strtolower($word));
				if ($stripped != '') {
					$numWords++;
					if (array_key_exists($stripped, $words))
						$words[$stripped] += 1;
					else
						$words[$stripped] = 1;
				}
			}
		}
	}
	asort($words);
	$words = array_reverse($words);
	asort($emoticons);
	$emoticons = array_reverse($emoticons);
	return array($words, $emoticons, $numWords, $numEmoticons);
}

function printWordList($words, $percent) {
	$count = 0;
	foreach ($words as $k => $v) {
		if ($percent)
			echo mb_convert_case($k, MB_CASE_TITLE).' '.round($v * 100, 2).' %';
		else
			echo mb_convert_case($k, MB_CASE_TITLE).' '.$v;
		echo '<br>';
		$count++;
		if ($count == 10) {
			break;
		}
	}
}

echo 'STATS<br><br>';

//Database queries
$messagesTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message"));
$messages = $messagesTable['COUNT(*)'];
echo 'Number of messages: ';
echo $messages;
$users = getQuery("SELECT id, username FROM user");

echo '<br><br>';
foreach ($users as $user) {
	$id = $user['id'];
	$userMessagesTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE author = $id"));
	$userMessages = $userMessagesTable['COUNT(*)'];
	echo $user['username'].': ';
	echo $userMessages;
	printPercentage($userMessages, $messages);
	echo '<br>';
}

echo '<br>';
$skypeTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 1"));
$skype = $skypeTable['COUNT(*)'];
echo 'Messages from Skype: ';
echo $skype;
printPercentage($skype, $messages);

echo '<br>';
$notSkypeTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 0"));
$notSkype = $notSkypeTable['COUNT(*)'];
echo 'Messages not from Skype: ';
echo $notSkype;
printPercentage($notSkype, $messages);

echo '<br><br>';
$lengthTable = mysqli_fetch_assoc(getQuery("SELECT AVG(LENGTH(content)) FROM message"));
$length = $lengthTable['AVG(LENGTH(content))'];
echo 'Average message length: ';
echo round($length, 2);

echo '<br><br>';
foreach ($users as $user) {
	$id = $user['id'];
	$userLengthTable = mysqli_fetch_assoc(getQuery("SELECT AVG(LENGTH(content)) FROM message WHERE author = $id"));
	$userLength = $userLengthTable['AVG(LENGTH(content))'];
	echo $user['username'].': ';
	echo round($userLength, 2);
	echo '<br>';
}

list($mostUsedWords, $mostUsedEmoticons, $numWordsTotal, $numEmoticonsTotal) = mostUsedWordsAndEmoticons(null, $shortcuts);
$numWordsUnique = sizeof($mostUsedWords);
echo '<br>Total number of words: '.$numWordsTotal.'<br>';
echo '<br>Total number of unique words: '.$numWordsUnique.'<br>';
echo '<br>Most used words:<br>';
printWordList($mostUsedWords, false);
$numEmoticonsUnique = sizeof($mostUsedEmoticons);
echo '<br>Total number of emoticons: '.$numEmoticonsTotal.'<br>';
echo '<br>Total number of unique emoticons: '.$numEmoticonsUnique.'<br>';
echo '<br>Most used emoticons:<br>';
printWordList($mostUsedEmoticons, false);
foreach ($users as $user) {
	list($userWords, $userEmoticons, $numWordsUser, $numEmoticonsUser) = mostUsedWordsAndEmoticons($user['id'], $shortcuts);
	$numWordsUserUnique = sizeof($userWords);
	echo '<br>Number of words for '.$user['username'].': '.$numWordsUser.'<br>';
	echo '<br>Number of unique words for '.$user['username'].': '.$numWordsUserUnique.'<br>';
	echo '<br>Most used words for '.$user['username'].':<br>';
	printWordList($userWords, false);
	$numEmoticonsUserUnique = sizeof($userEmoticons);
	echo '<br>Number of emoticons for '.$user['username'].': '.$numEmoticonsUser.'<br>';
	echo '<br>Number of unique emoticons for '.$user['username'].': '.$numEmoticonsUserUnique.'<br>';
	echo '<br>Most used emoticons for '.$user['username'].':<br>';
	printWordList($userEmoticons, false);
	
	echo '<br>Relatively most used words for '.$user['username'].':<br>';
	$relWords = array();
	foreach ($userWords as $k => $v) {
		$relWords[$k] = ($v / $numWordsUser) / ($mostUsedWords[$k] / $numWordsTotal);
	}
	asort($relWords);
	$relWords = array_reverse($relWords);
	printWordList($relWords, true);
	echo '<br>Relatively most used emoticons for '.$user['username'].':<br>';
	$relEmoticons = array();
	foreach ($userEmoticons as $k => $v) {
		$relEmoticons[$k] = ($v / $numEmoticonsUser) / ($mostUsedEmoticons[$k] / $numEmoticonsTotal);
	}
	asort($relEmoticons);
	$relEmoticons = array_reverse($relEmoticons);
	printWordList($relEmoticons, true);
}

//Close connection to database
mysqli_close($connection);

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>Svada Chat Client</title>
		<meta name="generator" content="Bootply" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<!--[if lt IE 9]>
			<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<link href="css/styles.css" rel="stylesheet">
	</head>
	<body>
		
		<!-- Scripts -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/functions.js"></script>
		<script src="js/ajax.js"></script>
	</body>
</html>