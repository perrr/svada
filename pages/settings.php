<?php

if (isset($_POST['saveSettings'])) {
	if ($user['language'] != $_POST['language']) {
		setQuery("UPDATE user set language = ".$_POST['language']." WHERE id = ".$user['id']);
	}
	if ($user['style'] != $_POST['style']) {
		setQuery("UPDATE user set style = ".$_POST['style']." WHERE id = ".$user['id']);
	}
	$muteSound = isset($_POST['sound'])==true ? 1 : 0;
	if ($user['mute_sounds'] != $muteSound) {
		setQuery("UPDATE user set mute_sounds = ".$muteSound." WHERE id = ".$user['id']);
	}
	$passwordMessage ="";
	//change password
	if (isset($_POST['oldPassword'])){
		if(password_verify($connection->real_escape_string($_POST['oldPassword']), $user['password'])){
			if (empty($_POST['newPassword'])){
				$passwordMessage= ("password can't be empty");
			}
			elseif ($_POST['newPassword']!= $_POST['repeatPassword']){
				$passwordMessage= ("the new passwords don't match");
			}
			else{
				$hashedNewPassword = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
				setQuery("UPDATE user set password = '".$hashedNewPassword."' WHERE id= ".$user['id']);
				$passwordMessage= ("password changed");
			}
		}
		else{ $passwordMessage= ("invalid password");}
		
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

$checked =($user['mute_sounds']== 1) ? ' checked' : '';
$checkbox = '<input type="checkbox" name="sound" id="muteSound" class="form-control" data-toggle="toggle" data-on="'.getString('on'). '" data-off="'.getString('off'). '" ' .$checked.'> ';

?>
<h1 class="tab-header col-sm-12"><?php echo getString('settings'); ?></h1>
<form method="post" action="">
<div class="form-horizontal settings-form">
  <div class="form-group dropdown-form-group">
    <label for="language" class="control-label col-sm-4"><?php echo getString('changeLanguage'); ?>:</label>
	<div class="col-sm-8">
		<select class="form-control" id="language" name="language">
			<?php echo $languageOptions; ?>
		</select>
	</div>
	</div>
	<div class="form-group dropdown-form-group">
    <label for="style" class="control-label col-sm-4"><?php echo getString('changeStyle'); ?>:</label>
	<div class="col-sm-8">
		<select class="form-control" id="style" name="style">
			<?php echo $styleOptions; ?>
		</select>
	</div>
  </div>
  <div class="form-group">
	<label for="muteSound" class="control-label col-sm-4"><?php echo getString('changeSound'); ?>:</label>
	<div class="col-sm-8">
		<?php echo $checkbox; ?>
	</div>
  </div>
  <div class="form-group">
	<label for="changePassword" class="control-label col-sm-4"><?php echo getString('oldPassword'); ?>:</label>
	<div class="col-sm-8">
		<input type="password" name="oldPassword" id="oldPassword" class="form-control" />
	</div>
  </div>
  <div class="form-group">
	<label for="changePassword" class="control-label col-sm-4"><?php echo getString('newPassword'); ?>:</label>
	<div class="col-sm-8">
		<input type="password" name="newPassword" id="newPassword" class="form-control" />
	</div>
  </div>
  <div class="form-group">
	<label for="changePassword" class="control-label col-sm-4"><?php echo getString('repeatPassword'); ?>:</label>
	<div class="col-sm-8">
		<input type="password" name="repeatPassword" id="repeatPassword" class="form-control" />
	</div>
  </div>
  <div class="form-group">
    <div class="col-sm-12">
      <button type="submit" class="btn button" name="saveSettings"><?php echo getString('save'); ?></button>
    </div>
  </div>
  </div>
</form>