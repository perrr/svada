<?php
header("Content-Type: application/json");
require('util.php');
$stats = array();
function getActivity() {
	$usersSql = getQuery("SELECT id FROM user");
	$users = array();
	while ($user = mysqli_fetch_assoc($usersSql)["id"]) {
		$users[] = $user;
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
	global $stats;
	$stats["activity"] = $userMessages;
}
getActivity();
echo json_encode($stats, JSON_NUMERIC_CHECK);
?>
