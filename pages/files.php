<?php

function listOfFiles(){
	$filesQuery = getQuery("SELECT *, file.id AS fileId FROM file, user WHERE file.uploader = user.id ORDER BY file.id DESC");
	$stringOfFiles = "";
	while($row = mysqli_fetch_assoc($filesQuery)){
		$uploader = $row['uploader'];
		$name = $row['display_name'];
		$timestamp = $row['timestamp'];
		$date = date('d.m.Y H:i', $timestamp);
		$filename = $row['name'];
		$stringOfFiles .= getImageTags($filename) .  '<a href="download.php?id=' . $row['fileId'] . '" target="_blank">' . $row['name'] . ' ' .
		getString("uploadedBy") . ' ' . $name . ' ' . $date . '</a>' . ' ' .'<a href="#" onClick= "shareFile('. $row['fileId'] . ')">' . getString("shareFile") .'</a><br>';
	}
	return $stringOfFiles;

}
function getImageTags($filename){
	$fileType = strrchr($filename, '.');
	$fileType = substr($fileType, 1);
	$directoryPath = 'res/images/free-file-icons/';
	$imageTag = "";
	if(file_exists($directoryPath . strtolower ($fileType) . '.png')){
		$imageTag = ' <img src=' . $directoryPath . strtolower ($fileType) . '.png' . ' alt=' . $filename .' >';
	}
	else{
		$imageTag = ' <img src=' . $directoryPath . '_blank.png' . ' alt=' . $filename  .' >';

	}
	return $imageTag;
}

function fileSearch(){
	$wheres = array();

	//input
	if($_POST['filename'] != "") {
		$filenameValue = $_POST['filename'];
		$filenameQuery = "name LIKE '%" . $filenameValue . "%'";
		array_push($wheres, $filenameQuery);
	}
	if($_POST['startDate'] != ""){
		$startDateValue = strtotime ($_POST['startDate']);
		$startDateQuery = "timestamp > '" . $startDateValue . "'";
		array_push($wheres, $startDateQuery);
	}
	if($_POST['endDate'] != ""){
		$endDateValue = strtotime($_POST['endDate']);
		$endDateQuery = "timestamp < '" . $endDateValue . "'";
		array_push($wheres, $endDateQuery);
	}

	if(isset($_POST['uploader'])){
		$uploaders = $_POST['uploader'];
		$uploaderArray = "(";
		for ($i=0; $i < count($uploaders) ; $i++) { 
			$uploaderArray .= $uploaders[$i] . ',';
		}
		$uploaderArray = substr($uploaderArray, 0, -1) . ')';
		$uploaderQuery = "uploader IN " . $uploaderArray ;
		array_push($wheres, $uploaderQuery);
	}
	//create query
	$searchQuery = "SELECT *, file.id AS fileId FROM file, user WHERE file.uploader = user.id";
	for ($i=0; $i < count($wheres) ; $i++) { 
		$searchQuery .= " AND " . $wheres[$i];
	}
	$searchQuery .= " ORDER BY file.id DESC";
	$filesQuery = getQuery($searchQuery);
	$stringOfFiles = "";
	while($row = mysqli_fetch_assoc($filesQuery)){
		$uploader = $row['uploader'];
		$name = $row['display_name'];
		$timestamp = $row['timestamp'];
		$date = date('d.m.Y H:i', $timestamp);
		$filename = $row['name'];
		$stringOfFiles .= getImageTags($filename) .  '<a href="download.php?id=' . $row['fileId'] . '" target="_blank">' . $row['name'] . ' ' .
		getString("uploadedBy") . ' ' . $name . ' ' . $date . '</a>' . ' ' .'<a href="#" onClick= "shareFile('. $row['fileId'] . ')">' . getString("shareFile") .'</a><br>';
	}
	return $stringOfFiles;

}

$users = getQuery("SELECT * FROM user");
$userOptions = '';
while ($user = mysqli_fetch_assoc($users)) {
	$userOptions .= '<option value="'.$user['id'].'"'."".'>'. $user['display_name'].'</option>';
}

if (isset($_POST['search'])) {
	echo fileSearch();
}
?>

<form method="post" action="">
<div class="form-group">
	<label for="filename" class="control-label col-sm-4"><?php echo getString('filename'); ?>:</label>
	<div class="col-sm-8">
		<input type="filename" name="filename" id="filename" class="form-control" />
	</div>
	<label for="startDate" class="control-label col-sm-4"><?php echo getString('startDate'); ?>:</label>
	<div class="col-sm-8">
		<input type="datetime-local"/  name="startDate" id="startDate" class="form-control" />
	</div>
	<label for="endDate" class="control-label col-sm-4"><?php echo getString('endDate'); ?>:</label>
	<div class="col-sm-8">
		<input type="datetime-local"/ name="endDate" id="endDate" class="form-control" />
	</div>

  </div>
<div class="form-horizontal settings-form">
  <div class="form-group dropdown-form-group">
    <label for="uploader" class="control-label col-sm-4"><?php echo getString('selectUploaders'); ?>:</label>
	<div class="col-sm-8">
		<select class="form-control" id="uploader" name="uploader[]" multiple>
			<?php echo $userOptions; ?>
		</select>
	</div>
	</div>
	<div class="form-group">
    <div class="col-sm-12">
      <button type="search" class="btn button" name="search"><?php echo getString('search'); ?></button>
    </div>
    </div>
	</div>
</form>
<h1 class="tab-header col-sm-12"><?php echo getString('listOfFiles'); ?></h1>
<?php echo listOfFiles(); ?>