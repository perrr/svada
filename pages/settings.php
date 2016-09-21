<?php

if (isset($_POST['saveSettings'])) {
	if ($user['language'] != $_POST['language']) {
		setQuery("UPDATE user set language = ".$_POST['language']." WHERE id = ".$user['id']);
	}
	
	//Update page
	updateUserSession();
	header('Location: chat.php');
	die();
}

$languages = getQuery("SELECT * FROM language");
$languageOptions = '';
while ($lang = mysqli_fetch_assoc($languages)) {
	$selected = $lang['id'] == $user['language'] ? " selected" : "";
	$languageOptions .= '<option value="'.$lang['id'].'"'.$selected.'>'.$lang['local_name'].'</option>';
}

?>
<h1 class="tab-header col-sm-12"><?php echo getString('settings'); ?></h1>
 <form class="form-horizontal" action="" method="post">
  <div class="form-group dropdown-form-group">
    <label for="language" class="control-label col-sm-4"><?php echo getString('changeLanguage'); ?>:</label>
	<div class="col-sm-8">
		<select class="form-control" id="language" name="language">
			<?php echo $languageOptions; ?>
		</select>
	</div>
  </div>
  <div class="form-group">
    <div class="col-sm-12">
      <button type="submit" class="btn button" name="saveSettings"><?php echo getString('save'); ?></button>
    </div>
  </div>
  
  </form>