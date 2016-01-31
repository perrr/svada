<?php
session_start();
$message = '';

if(isset($_POST['username'])){
	require('util.php');
	
	//Preprocess username and password
	$username = strtolower($connection->real_escape_string($_POST['username']));
	$password = md5($connection->real_escape_string($_POST['password']));
	
	//Look for matching users
	$user = mysqli_fetch_array(getQuery("SELECT * FROM user WHERE username = '$username' AND password = '$password'"));
	
	//If a matching user was found, redirect to chat
	if(!empty($user)){
		$_SESSION['user'] = $user;
		header('Location: chat.php');
		die();
	}
	
	//Store error message if login was unsuccessful
	$message = '<span class="error-message">Incorrect username or password.</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>Svada Chat Client</title>
		<meta name="generator" content="Bootply" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<!--[if lt IE 9]>
			<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<link href="css/styles.css" rel="stylesheet">
	</head>
	<body>
		
		<div class="container login-wrapper">
			<div class="panel panel-primary">
				  <div class="panel-heading login-header"><h3>Log in to Svada</h3></div>
				  <div class="panel-body">
				  
					<form action="" method="post">
						<div class="form-group">
							<label for="username">Username</label>
							<input type="text" name="username" class="form-control" id="username">
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" name="password" class="form-control" id="password">
						</div>
						<div class="form-group">
						<?php echo $message; ?></span>
						</div>
						<input type="submit" class="btn btn-primary btn-block" value="Log in">
					</form>
				  </div>
			</div>
		</div>
		
		<!-- Scripts -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>
