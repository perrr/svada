<?php

?>
<h1 class="tab-header col-sm-12"><?php echo 'Style Creator'; ?></h1>
<form method="post" action="">
  <div class="form-group">
	<label for="name" class="control-label col-sm-4"><?php echo 'Name'; ?>:</label>
	<div class="col-sm-8">
		<input type="textbox" name="Name" id="Name" class="form-control" />
	</div>
  </div>
  <div class="form-group">
	<label for="primaryColor" class="control-label col-sm-4"><?php echo 'Primary color'; ?>:</label>
	<div class="col-sm-8">
		<input type="textbox" name="primaryColor" id="primaryColor" class="form-control" />
	</div>
  </div>
  <div class="form-group">
	<label for="secondaryColor" class="control-label col-sm-4"><?php echo 'Secondary color'; ?>:</label>
	<div class="col-sm-8">
		<input type="textbox" name="secondaryColor" id="secondaryColor" class="form-control" />
	</div>
  </div>
  <div class="form-group">
	<label for="backgroundColor" class="control-label col-sm-4"><?php echo 'Background color'; ?>:</label>
	<div class="col-sm-8">
		<input type="textbox" name="backgroundColor" id="backgroundColor" class="form-control" />
	</div>
  </div>
  <div class="form-group">
	<label for="scrollbar" class="control-label col-sm-4"><?php echo 'scrollbar'; ?>:</label>
	<div class="col-sm-8">
		<input type="textbox" name="scrollbar" id="scrollbar" class="form-control" />
	</div>
  </div>
  <div class="form-group">
	<label for="markup" class="control-label col-sm-4"><?php echo 'markup'; ?>:</label>
	<div class="col-sm-8">
		<input type="textbox" name="markup" id="markup" class="form-control" />
	</div>
  </div>
  <div class="form-group">
    <div class="col-sm-12">
      <button type="submit" class="btn button" name="saveSettings"><?php echo 'save'; ?></button>
    </div>
  </div>
  </div>
</form>