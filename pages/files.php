<?php


function listOfFiles(){
	$filesQuery = getQuery("SELECT *, file.id AS fileId FROM file, user WHERE file.uploader = user.id ORDER BY file.id DESC");
	$stringOfFiles = "";
	while($row = mysqli_fetch_assoc($filesQuery)){
		$uploader = $row['uploader'];
		$name = $row['display_name'];
		$timestamp = $row['timestamp'];
		$date = date('d.m.Y H:i', $timestamp);
		$mimeType = $row['mime_type'];
		$filename = $row['name'];
		$stringOfFiles .= getImageTags($mimeType, $filename) .  '<a href="download.php?id=' . $row['fileId'] . '" target="_blank">' . $row['name'] . ' ' .
		getString("uploadedBy") . ' ' . $name . ' ' . $date . '</a>' . ' ' .'<a href="#" onClick= "shareFile('. $row['fileId'] . ')">' . getString("shareFile") .'</a><br>';
	}
	return $stringOfFiles;

}
function getImageTags($mimeType, $filename){
	$fileType = substr($mimeType, strrpos($mimeType, '/'));
	$directoryPath = 'res/images/free-file-icons/';
	$imageTag = "";
	if(file_exists($directoryPath . strtolower ($fileType) . '.png')){
		$imageTag = ' <img src=' . $directoryPath . strtolower ($fileType) . '.png' . ' alt=' . $filename .' >';
	}
	else{
		$imageTag = ' <img src=' . $directoryPath . '_blank.png' . ' alt=' . $filename .' >';

	}
	return $imageTag;
}

echo listOfFiles();


?>