<?php

function sqlToJson($sql) {
	$json = array();
	while ($row = mysql_fetch_assoc($sql)) {
		$json[] = $row;
	}
	echo json_encode($json, JSON_NUMERIC_CHECK);
}

?>
