<?php
session_start();

require('util.php');

//If there's no session for this visitor, redirect him out of here
if(!isLoggedIn()){
	header('Location: index.php');
	die();
}

//For convenience, store session in a variable with a shorter name
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>Svada Chat Client</title>
		<meta name="generator" content="Bootply" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link href="css/plugins/bootstrap.min.css" rel="stylesheet">
		<link href="css/plugins/jquery.mCustomScrollbar.css" rel="stylesheet">
		<link href="css/plugins/bootstrap-toggle.min.css" rel="stylesheet">
		<link href="css/plugins/font-awesome.min.css" rel="stylesheet">
		<!--[if lt IE 9]>
			<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<link href="css/styles.css" rel="stylesheet">
		<?php echo getStyle($user['style']); ?>
	</head>
	<body>
		<?php include('pages/splashscreen.php'); ?>
		<div class="container" id="chat-top"></div>
		<div id="chat-menu"></div>
		<div class="container" id="chat-bottom">
			<div class="row">
				<div class="col-sm-2" id="sidebar">
					<div id="users"></div>
				</div>
				<div class="col-sm-10" id="mainbar">
					<div id="notifications"></div>
					<div id="tabs">
						<div class="tab active-tab" id="tab-chat">
							<form id="uploadform" method="post" action="" name="file" enctype="multipart/form-data">
								<div id="search">
									<div id="search-wrapper">
										<div id="search-group" class="input-group">
											<input type="text" class="form-control" placeholder="<?php echo getString('search'); ?>" name="srch-term" id="search-field">
											<div class="input-group-btn">
												<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-content">
									<div id="messages">
										<div id="message-container"></div>
									</div>
									<div id="toolbar">
										<div id="whoistyping"></div>
										<div id="right-toolbar">
											<span class="glyphicon glyphicon-list-alt toolbar-item" onclick="changeTab('files', true)" title="<?php echo getString('files'); ?>"></span>
											<span class="glyphicon glyphicon-upload toolbar-item" onclick="manualUpload('file')" title="<?php echo getString('upload'); ?>"></span>
										</div>
										<br class="clear">
									</div>
									<div id="write-message">
										<div id="message-text-field" contenteditable="true" onkeyup="arrangeQuotes()"></div>
									</div>	
								</div>
								<input id="fileupload" class="hidden" onchange="submitUpload()" type="file" name="files[]" multiple />
							</form>
						</div>
						<div class="tab" id="tab-settings">
							<div class="tab-content">
								<?php include('pages/settings.php'); ?>
							</div>
						</div>
						<div class="tab" id="tab-stats">
							<div class="tab-content">
								<?php include('pages/stats.php'); ?>
								<div id="activity_graph"></div>
							</div>
						</div>
						<div class="tab" id="tab-files">
							<div class="tab-back" onclick="changeTab('chat', false)"><span class="glyphicon glyphicon-arrow-left"></span> <?php echo getString('back'); ?></div>
							<div class="tab-content">
								<?php include('pages/files.php'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Scripts -->
		<script src="js/plugins/jquery.min.js"></script>
		<script src="js/plugins/bootstrap.min.js"></script>
		<script src="js/plugins/highlight.pack.js"></script>
		<script src="js/plugins/jquery.mCustomScrollbar.concat.min.js"></script>
		<script src="js/plugins/bootstrap-toggle.min.js"></script>
		<script src="js/plugins/googlecharts.js"></script>
		<script src="js/plugins/is.min.js"></script>
		<script src="js/global.js"></script>
		<script src="js/functions.js"></script>
		<script src="js/ajax.js"></script>
		<script src="js/listeners.js"></script>
		<script src="js/upload.js"></script>
		<script src="js/tabs.js"></script>
		<script src="js/initialize.js"></script>
		<script src="js/charts.js"></script>
	</body>
</html>
