<?php
session_start();
require('util.php');
$message = '';
if(isLoggedIn() && isset($_GET['logout'])){
	setcookie('usercookie', '', time()-3600);
	$id = $_SESSION['user']['id'];
	setQuery("UPDATE user_session SET token = NULL WHERE id = '$id'");
	setQuery("UPDATE user SET status ='0' WHERE id = '$id'");
	session_destroy();
}
if(isset($_COOKIE['usercookie'])){
	$cookie = $_COOKIE['usercookie'];
	$cookieResult = mysqli_fetch_array(getQuery("SELECT id FROM user_session WHERE token ='$cookie'"));
	if(!empty($cookieResult)){
		$id = $cookieResult['id'];
		$_SESSION['user'] = mysqli_fetch_array(getQuery("SELECT * FROM user WHERE id ='$id'"));
		mysqli_close($connection);
		header('Location: chat.php');
		die();
	}
}
if(isset($_POST['username'])){	
	//Preprocess username and password
	$username = strtolower($connection->real_escape_string($_POST['username']));
	$password = md5(salt($connection->real_escape_string($_POST['password']),$_POST['username']));
	
	//Look for matching users
	$user = mysqli_fetch_array(getQuery("SELECT * FROM user WHERE username = '$username' AND password = '$password'"));
	

	
	//If a matching user was found, redirect to chat
	if(!empty($user)){
		$_SESSION['user'] = $user;
		$token = $_SESSION['user']['id'].md5(strval(time()));
		$id = $_SESSION['user']['id'];
		setQuery("INSERT INTO user_session VALUES ($id, '$token')");
		//Close connection to database
		mysqli_close($connection);
		setcookie('usercookie', $token, 86400*365*100);
		header('Location: chat.php');
		die();
	}
	//Close connection to database
	mysqli_close($connection);
	
	//Store error message if login was unsuccessful
	$message = '<span class="error-message">'.getString("incorrectUserOrPassword").'</span>';
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
				  <div class="panel-heading login-header"><h3><?php echo getString("logInSvada"); ?></h3></div>
				  <div class="panel-body">
				  
					<form action="" method="post">
						<div class="form-group">
							<label for="username"><?php echo getString("username");?></label>
							<input type="text" name="username" class="form-control" id="username">
						</div>
						<div class="form-group">
							<label for="password"><?php echo getString("password"); ?></label>
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
