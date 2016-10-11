<?php


function listOfFiles(){
	$filesQuery = getQuery("SELECT * FROM file, user WHERE file.uploader = user.id ORDER BY file.id DESC");
	$stringOfFiles = "";
	while($row = mysqli_fetch_assoc($filesQuery)){
		$uploader = $row['uploader'];
		$name = $row['display_name'];
		$timestamp = $row['timestamp'];
		$date = date('d.m.Y H:i');
		$stringOfFiles .=  '<a href="download.php?id=' . $row['id'] . '" target="_blank">' . $row['name'] . ' ' . getString("uploadedBy") . ' ' . $name . ' ' . $date . '</a><br>';
	}
	return $stringOfFiles;

}
echo listOfFiles();


?>