<?php

function sqlToJson($sql) {
	$json = array();
	while ($row = mysql_fetch_assoc($sql)) {
		$json[] = $row;
	}
	echo json_encode($json, JSON_NUMERIC_CHECK);
}

function escapeArray($array){
	$keys = array_keys($array);
	foreach ($keys as  $key) {
	 	$array[$key] = mysql_real_escape_string($array[$key]);
	 }
	 return $array;
}

?>
