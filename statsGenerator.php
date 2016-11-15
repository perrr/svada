<?php
//header("Content-Type: application/json");
require('util.php');
$stats = array();

function isEmoticon($word, $shortcuts) {
	foreach ($shortcuts as $shortcut) {
		if ($word == $shortcut)
			return true;
	}
	return false;
}

function printPercentage($number, $total) {
	$perc = ($number / $total) * 100;
	return round($perc, 2).' %';
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
		$exploded = preg_split('/\s+/', $message);
		foreach ($exploded as $word) {
			if (isEmoticon($word, $shortcuts)) {
				$numEmoticons++;
				if (array_key_exists($word, $emoticons))
					$emoticons[$word] += 1;
				else
					$emoticons[$word] = 1;
			}
			else {
				$word = str_replace('<br', '', $word);
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

function getUserActivity() {
	$usersSql = getQuery("SELECT id FROM user");
	$users = array();
	while ($user = mysqli_fetch_assoc($usersSql)) {
		$users[] = $user["id"];
	}
	$firstMessage = mysqli_fetch_assoc(getQuery("SELECT MIN(timestamp) FROM message"))["MIN(timestamp)"];
	$lastMessage = mysqli_fetch_assoc(getQuery("SELECT MAX(timestamp) FROM message"))["MAX(timestamp)"];
	$months = findAllMonths($firstMessage, $lastMessage);
	$monthNames = findMonthNames($months);
	$userMessages = array();

	foreach ($users as $user) {
		$userName = mysqli_fetch_assoc(getQuery("SELECT display_name FROM user WHERE id = ".$user))["display_name"];
		$messages = array();
		for ($i = 0; $i < count($months) - 1; $i++) {
			$messages[$monthNames[$i]] = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE author = ".$user." AND timestamp >= ".$months[$i]." AND timestamp < ".$months[$i+1]))["COUNT(*)"];
		}
		$messages[$monthNames[$i]] = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE author = ".$user." AND timestamp >= ".$months[count($months)-1]))["COUNT(*)"];
		$userMessages[$userName] = $messages;
	}

	$totalMessages = array();
	for ($i = 0; $i < count($months) - 1; $i++) {
		$totalMessages[$monthNames[$i]] = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE timestamp >= ".$months[$i]." AND timestamp < ".$months[$i+1]))["COUNT(*)"];
	}
	$totalMessages[$monthNames[$i]] = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE timestamp >= ".$months[count($months)-1]))["COUNT(*)"];
	$userMessages['Total'] = $totalMessages;

	global $stats;
	$stats["userActivity"] = $userMessages;
}

function getDailyActivity() {
	$usersSql = getQuery("SELECT id FROM user");
	$users = array();
	while ($user = mysqli_fetch_assoc($usersSql)) {
		$users[] = $user["id"];
	}

	$timestamps = array();

	foreach ($users as $user) {
		$userName = mysqli_fetch_assoc(getQuery("SELECT display_name FROM user WHERE id = ".$user))["display_name"];
		$userTimestamps = array();
		$messagesSql = getQuery("SELECT timestamp FROM message WHERE author = ".$user);
		while ($message = mysqli_fetch_assoc($messagesSql)) {
			$timestamp = getMinute($message["timestamp"]);
			if (array_key_exists($timestamp, $timestamps))
				$userTimestamps[$timestamp]++;
			else
				$userTimestamps[$timestamp] = 1;
		}
		$timestamps[$userName] = $userTimestamps;
	}

	$timestamps['Total'] = array();
	$messagesSql = getQuery("SELECT timestamp FROM message");
	while ($message = mysqli_fetch_assoc($messagesSql)) {
		$timestamp = getMinute($message["timestamp"]);
		if (array_key_exists($timestamp, $timestamps['Total']))
			$timestamps['Total'][$timestamp]++;
		else
			$timestamps['Total'][$timestamp] = 1;
	}

	global $stats;
	$dailyActivity = array();
	foreach (array_keys($timestamps) as $user) {
		$dailyActivity[$user] = array();
		for ($h = 0; $h < 24; $h++) {
			for ($m = 0; $m < 60; $m++) {
				$timestamp = ($h < 10 ? '0'.$h : $h).':'.($m < 10 ? '0'.$m : $m);
				if (array_key_exists($timestamp, $timestamps[$user]))
					$dailyActivity[$user][$timestamp] = $timestamps[$user][$timestamp];
				else
					$dailyActivity[$user][$timestamp] = 0;
			}
		}
	}
	$stats["dailyActivity"] = $dailyActivity;
}

function getNumbers() {
	global $stats;
	$stats['Key numbers'] = array();

	$emoticonSql = getQuery("SELECT shortcut FROM emoticon");
	$shortcuts = array();
	while ($row = mysqli_fetch_assoc($emoticonSql)) {
		$emoticonShortcuts = $row['shortcut'];
		$exploded = explode(' ', $emoticonShortcuts);
		foreach ($exploded as $shortcut) {
			$shortcuts[] = $shortcut;
		}
	}

	$messagesTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message"));

	$messages = $messagesTable['COUNT(*)'];
	$stats['Key numbers']['Number of messages'] = $messages;

	if ($messages > 0) {
		$usersQuery = getQuery("SELECT id, username FROM user");
		$users = array();
		while($user = mysqli_fetch_assoc($usersQuery)){
		  $users[] = $user;
		}

		$stats['Key numbers']['Messages per user'] = array();
		foreach (array_keys($users) as $user) { ////////////////////////////////////////////////////////////////////// HER VISES IKKE NAVNET I JSON-EN
			$id = $users[$user]['id'];
			$userMessagesTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE author = $id"));
			$userMessages = $userMessagesTable['COUNT(*)'];
			$stats['Key numbers']['Messages per user'][$user] = array();
			$stats['Key numbers']['Messages per user'][$user]['Total'] = $userMessages;
			$stats['Key numbers']['Messages per user'][$user]['Percentage'] = printPercentage($userMessages, $messages);
		}

		$skypeTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 1"));
		$skype = $skypeTable['COUNT(*)'];
		$stats['Key numbers']['Messages from Skype'] = array();
		$stats['Key numbers']['Messages from Skype']['Total'] = $skype;
		$stats['Key numbers']['Messages from Skype']['Percentage'] = printPercentage($skype, $messages);

		$notSkypeTable = mysqli_fetch_assoc(getQuery("SELECT COUNT(*) FROM message WHERE skype = 0"));
		$notSkype = $notSkypeTable['COUNT(*)'];
		$stats['Key numbers']['Messages not from Skype'] = array();
		$stats['Key numbers']['Messages not from Skype']['Total'] = $notSkype;
		$stats['Key numbers']['Messages not from Skype']['Percentage'] = printPercentage($notSkype, $messages);

		$lengthTable = mysqli_fetch_assoc(getQuery("SELECT AVG(LENGTH(content)) FROM message"));
		$length = $lengthTable['AVG(LENGTH(content))'];
		$stats['Key numbers']['Average message length'] = round($length, 2);

		$stats['Key numbers']['Average message length per user'] = array();
		foreach (array_keys($users) as $user) { ////////////////////////////////////////////////////////////////////// HER VISES IKKE NAVNET I JSON-EN
			$id = $users[$user]['id'];
			$userLengthTable = mysqli_fetch_assoc(getQuery("SELECT AVG(LENGTH(content)) FROM message WHERE author = $id"));
			$userLength = $userLengthTable['AVG(LENGTH(content))'];
			$stats['Key numbers']['Average message length per user'][$user] = array();
			$stats['Key numbers']['Average message length per user'][$user]['Total'] = round($userLength, 2);
		}

		
		/*
		list($mostUsedWords, $mostUsedEmoticons, $numWordsTotal, $numEmoticonsTotal) = mostUsedWordsAndEmoticons(null, $shortcuts);
		$numWordsUnique = sizeof($mostUsedWords);
		$content[] = '<br>'.getString('totNoWords').': '.$numWordsTotal.'<br>';
		$content[] = '<br>'.getString('totNoUniqueWords').': '.$numWordsUnique.'<br>';
		$content[] = '<br>'.getString('mostUsedWords').':<br>';
		printWordList($mostUsedWords, false);
		$numEmoticonsUnique = sizeof($mostUsedEmoticons);
		$content[] = '<br>'.getString('totNoEmoticons').': '.$numEmoticonsTotal.'<br>';
		$content[] = '<br>'.getString('totNoUniqueEmoticons').': '.$numEmoticonsUnique.'<br>';
		$content[] = '<br>'.getString('mostUsedEmoticons').':<br>';
		printWordList($mostUsedEmoticons, false);
		foreach ($users as $user) {
			list($userWords, $userEmoticons, $numWordsUser, $numEmoticonsUser) = mostUsedWordsAndEmoticons($user['id'], $shortcuts);
			$numWordsUserUnique = sizeof($userWords);
			$content[] = '<br>'.getString('noWordsFor').' '.$user['username'].': '.$numWordsUser.'<br>';
			$content[] = '<br>'.getString('noUniqueWordsFor').' '.$user['username'].': '.$numWordsUserUnique.'<br>';
			$content[] = '<br>'.getString('mostUsedWordsFor').' '.$user['username'].':<br>';
			printWordList($userWords, false);
			$numEmoticonsUserUnique = sizeof($userEmoticons);
			$content[] = '<br>'.getString('noEmoticonsFor').' '.$user['username'].': '.$numEmoticonsUser.'<br>';
			$content[] = '<br>'.getString('noUniqueEmoticonsFor').' '.$user['username'].': '.$numEmoticonsUserUnique.'<br>';
			$content[] = '<br>'.getString('mostUsedEmoticonsFor').' '.$user['username'].':<br>';
			printWordList($userEmoticons, false);
			$content[] = '<br>'.getString('relMostUsedWordsFor').' '.$user['username'].':<br>';
			$relWords = array();
			foreach ($userWords as $k => $v) {
				if ($mostUsedWords[$k] >= 10) // To avoid words you've used 1-9 times and no one else uses to dominate the list
					$relWords[$k] = ($v / $numWordsUser) / ($mostUsedWords[$k] / $numWordsTotal);
			}
			asort($relWords);
			$relWords = array_reverse($relWords);
			printWordList($relWords, true);
			$content[] = '<br>'.getString('relMostUsedEmoticonsFor').' '.$user['username'].':<br>';
			$relEmoticons = array();
			foreach ($userEmoticons as $k => $v) {
				if ($mostUsedWords[$k] >= 10) // To avoid emoticons you've used 1-9 times and no one else uses to dominate the list
					$relEmoticons[$k] = ($v / $numEmoticonsUser) / ($mostUsedEmoticons[$k] / $numEmoticonsTotal);
			}
			asort($relEmoticons);
			$relEmoticons = array_reverse($relEmoticons);
			printWordList($relEmoticons, true);
		}*/

	}
}

echo mysqli_fetch_assoc(getQuery("SELECT stats FROM chat"))["stats"];

// This should probably be done in a thread or similar
$lastStats = mysqli_fetch_assoc(getQuery("SELECT stats_timestamp FROM chat"))["stats_timestamp"];
//if (time() - 24 * 60 * 60 > $lastStats) {
	getNumbers();
	getUserActivity();
	getDailyActivity();
	$json = json_encode($stats, JSON_NUMERIC_CHECK);
	$time = time();
	setQuery("UPDATE chat SET stats = '$json', stats_timestamp = '$time'");
//}
?>
