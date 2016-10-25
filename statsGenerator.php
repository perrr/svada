<?php
header("Content-Type: application/json");
require('util.php');
$stats = array();

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
	$messagesSql = getQuery("SELECT timestamp FROM message");
	$timestamps = array();
	while ($message = mysqli_fetch_assoc($messagesSql)) {
		$timestamp = getMinute($message["timestamp"]);
		if (array_key_exists($timestamp, $timestamps))
			$timestamps[$timestamp]++;
		else
			$timestamps[$timestamp] = 1;
	}
	global $stats;
	$dailyActivity = array();
	for ($h = 0; $h < 24; $h++) {
		for ($m = 0; $m < 60; $m++) {
			$timestamp = ($h < 10 ? '0'.$h : $h).':'.($m < 10 ? '0'.$m : $m);
			if (array_key_exists($timestamp, $timestamps))
				$dailyActivity[$timestamp] = $timestamps[$timestamp];
			else
				$dailyActivity[$timestamp] = 0;
		}
	}
	$stats["dailyActivity"] = $dailyActivity;
}

echo mysqli_fetch_assoc(getQuery("SELECT stats FROM chat"))["stats"];

// This should probably be done in a thread or similar
$lastStats = mysqli_fetch_assoc(getQuery("SELECT stats_timestamp FROM chat"))["stats_timestamp"];
if (time() - 24 * 60 * 60 > $lastStats) {
	getUserActivity();
	getDailyActivity();
	$json = json_encode($stats, JSON_NUMERIC_CHECK);
	$time = time();
	setQuery("UPDATE chat SET stats = '$json', stats_timestamp = '$time'");
}
?>
