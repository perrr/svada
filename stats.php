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

$content = array(); // An array containing all we wish to print

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
	global $content;
	$content[] = ' ('.round($perc, 2).' %)';
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
	global $content;
	$count = 0;
	foreach ($words as $k => $v) {
		if ($percent)
			$content[] = mb_convert_case($k, MB_CASE_TITLE).' '.round($v * 100, 2).' %';
		else
			$content[] = mb_convert_case($k, MB_CASE_TITLE).' '.$v;
		$content[] = '<br>';
		$count++;
		if ($count == 10) {
			break;
		}
	}
}

$content[] = 'STATS<br><br>';

//Database queries
$messagesTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message"));
$messages = $messagesTable['COUNT(*)'];
$content[] = 'Number of messages: ';
$content[] = $messages;
$users = getQuery("SELECT id, username FROM user");

$content[] = '<br><br>';
foreach ($users as $user) {
	$id = $user['id'];
	$userMessagesTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE author = $id"));
	$userMessages = $userMessagesTable['COUNT(*)'];
	$content[] = $user['username'].': ';
	$content[] = $userMessages;
	printPercentage($userMessages, $messages);
	$content[] = '<br>';
}

$content[] = '<br>';
$skypeTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 1"));
$skype = $skypeTable['COUNT(*)'];
$content[] = 'Messages from Skype: ';
$content[] = $skype;
printPercentage($skype, $messages);

$content[] = '<br>';
$notSkypeTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 0"));
$notSkype = $notSkypeTable['COUNT(*)'];
$content[] = 'Messages not from Skype: ';
$content[] = $notSkype;
printPercentage($notSkype, $messages);

$content[] = '<br><br>';
$lengthTable = mysqli_fetch_assoc(getQuery("SELECT AVG(LENGTH(content)) FROM message"));
$length = $lengthTable['AVG(LENGTH(content))'];
$content[] = 'Average message length: ';
$content[] = round($length, 2);

$content[] = '<br><br>';
foreach ($users as $user) {
	$id = $user['id'];
	$userLengthTable = mysqli_fetch_assoc(getQuery("SELECT AVG(LENGTH(content)) FROM message WHERE author = $id"));
	$userLength = $userLengthTable['AVG(LENGTH(content))'];
	$content[] = $user['username'].': ';
	$content[] = round($userLength, 2);
	$content[] = '<br>';
}

list($mostUsedWords, $mostUsedEmoticons, $numWordsTotal, $numEmoticonsTotal) = mostUsedWordsAndEmoticons(null, $shortcuts);
$numWordsUnique = sizeof($mostUsedWords);
$content[] = '<br>Total number of words: '.$numWordsTotal.'<br>';
$content[] = '<br>Total number of unique words: '.$numWordsUnique.'<br>';
$content[] = '<br>Most used words:<br>';
printWordList($mostUsedWords, false);
$numEmoticonsUnique = sizeof($mostUsedEmoticons);
$content[] = '<br>Total number of emoticons: '.$numEmoticonsTotal.'<br>';
$content[] = '<br>Total number of unique emoticons: '.$numEmoticonsUnique.'<br>';
$content[] = '<br>Most used emoticons:<br>';
printWordList($mostUsedEmoticons, false);
foreach ($users as $user) {
	list($userWords, $userEmoticons, $numWordsUser, $numEmoticonsUser) = mostUsedWordsAndEmoticons($user['id'], $shortcuts);
	$numWordsUserUnique = sizeof($userWords);
	$content[] = '<br>Number of words for '.$user['username'].': '.$numWordsUser.'<br>';
	$content[] = '<br>Number of unique words for '.$user['username'].': '.$numWordsUserUnique.'<br>';
	$content[] = '<br>Most used words for '.$user['username'].':<br>';
	printWordList($userWords, false);
	$numEmoticonsUserUnique = sizeof($userEmoticons);
	$content[] = '<br>Number of emoticons for '.$user['username'].': '.$numEmoticonsUser.'<br>';
	$content[] = '<br>Number of unique emoticons for '.$user['username'].': '.$numEmoticonsUserUnique.'<br>';
	$content[] = '<br>Most used emoticons for '.$user['username'].':<br>';
	printWordList($userEmoticons, false);
	
	$content[] = '<br>Relatively most used words for '.$user['username'].':<br>';
	$relWords = array();
	foreach ($userWords as $k => $v) {
		$relWords[$k] = ($v / $numWordsUser) / ($mostUsedWords[$k] / $numWordsTotal);
	}
	asort($relWords);
	$relWords = array_reverse($relWords);
	printWordList($relWords, true);
	$content[] = '<br>Relatively most used emoticons for '.$user['username'].':<br>';
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
		<?php
		foreach ($content as $text) {
			echo $text;
		}
		?>
		<!-- Scripts -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/functions.js"></script>
		<script src="js/ajax.js"></script>
	</body>
</html>