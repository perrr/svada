<?php
session_start();
$message = '';

if(isset($_POST['username'])){
	require('db.php');
	
	//Preprocess username and password
	$username = strtolower(mysql_real_escape_string($_POST['username']));
	$password = md5(mysql_real_escape_string($_POST['password']));
	
	//Look for matching users
	$user = mysql_fetch_array(mysql_query("SELECT * FROM user WHERE username = '$username' AND password = '$password'"));
	
	//If a matching user was found, redirect to chat
	if(!empty($user)){
		$_SESSION['user'] = $user;
		header('Location: chat.php');
		die();
	}
	
	//Store error message if login was unsuccessful
	$message = 'Incorrect username or password.';
}
?>

<form action="" method="post">
	Username <input type="text" name="username"><br>
	Password <input type="password" name="password"><br>
	<input type="submit" value="Log in">
</form>
<?php echo $message; ?>
