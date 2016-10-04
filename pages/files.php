<?php


function listOfFiles(){
	$filesQuery = getQuery("SELECT * FROM file");
	$stringOfFiles = "";
	while($row = mysqli_fetch_assoc($filesQuery)){
		$stringOfFiles .=  '<a href="download.php?id=' . $row['id'] . '">' . $row['name'] . getString("uploadedBy") . $row['uploader'] . '</a><br>';
	}
	return $stringOfFiles;

}
echo listOfFiles();


?>