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

$styles = getQuery("SELECT * FROM style");
$styleOptions = '';
while ($style = mysqli_fetch_assoc($styles)) {
	$selected = $style['id'] == $user['style'] ? " selected" : "";
	$styleOptions .= '<option value="'.$style['id'].'"'.$selected.'>'.$style['name'].'</option>';
}

?>
<h1 class="tab-header col-sm-12"><?php echo getString('settings'); ?></h1>
  <div class="form-group dropdown-form-group">
    <label for="language" class="control-label col-sm-4"><?php echo getString('changeLanguage'); ?>:</label>
	<div class="col-sm-8">
		<select class="form-control" id="language" name="language">
			<?php echo $languageOptions; ?>
		</select>
	</div>
    <label for="style" class="control-label col-sm-4"><?php echo getString('changeStyle'); ?>:</label>
	<div class="col-sm-8">
		<select class="form-control" id="style" name="style">
			<?php echo $styleOptions; ?>
		</select>
	</div>
  </div>
  <div class="form-group">
    <div class="col-sm-12">
      <button type="submit" class="btn button" name="saveSettings"><?php echo getString('save'); ?></button>
    </div>
  </div>