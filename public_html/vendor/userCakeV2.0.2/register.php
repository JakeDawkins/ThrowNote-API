<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: account.php"); die(); }

//Forms posted
if(!empty($_POST)){
	$errors = array();
	$email = trim($_POST["email"]);
	$username = trim($_POST["username"]);
	$displayname = trim($_POST["displayname"]);
	$password = trim($_POST["password"]);
	$confirm_pass = trim($_POST["passwordc"]);
	$captcha = md5($_POST["captcha"]);
	
	
	if ($captcha != $_SESSION['captcha']){
		$errors[] = lang("CAPTCHA_FAIL");
	}
	if(minMaxRange(5,25,$username)){
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,25));
	}
	if(!ctype_alnum($username)){
		$errors[] = lang("ACCOUNT_USER_INVALID_CHARACTERS");
	}
	if(minMaxRange(5,25,$displayname)){
		$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(5,25));
	}
	if(!ctype_alnum($displayname)){
		$errors[] = lang("ACCOUNT_DISPLAY_INVALID_CHARACTERS");
	}
	if(minMaxRange(8,50,$password) && minMaxRange(8,50,$confirm_pass)){
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	}
	else if($password != $confirm_pass){
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}
	if(!isValidEmail($email)){
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	//End data validation
	if(count($errors) == 0){	
		//Construct a user object
		$user = new User($username,$displayname,$password,$email);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if(!$user->status){
			if($user->username_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
			if($user->displayname_taken) $errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));		
		} else {
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			if(!$user->userCakeAddUser()){
				if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
				if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
		}
	}
	if(count($errors) == 0) {
		$successes[] = $user->success;
	}
}
?>


<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<title>ThrowNote</title>
	<script src='models/funcs.js' type='text/javascript'>
	</script>
</head>

<body>
	<div id='wrapper'>
		<div id='top'><div id='logo'></div></div>
		<div id='content'>
			<h1>UserCake</h1>
			<h2>Register</h2>

			<div id='left-nav'>
				<?php include("left-nav.php"); ?>
			</div>

			<div id='main'>
				<?php echo resultBlock($errors,$successes); ?>

				<div id='regbox'>
					<form name='newUser' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
						<p>
							<label>User Name:</label>
							<input type='text' name='username' />
						</p>
						<p>
							<label>Display Name:</label>
							<input type='text' name='displayname' />
						</p>
						<p>
							<label>Password:</label>
							<input type='password' name='password' />
						</p>
						<p>
							<label>Confirm:</label>
							<input type='password' name='passwordc' />
						</p>
						<p>
							<label>Email:</label>
							<input type='text' name='email' />
						</p>
						<p>
							<label>Security Code:</label>
							<img src='models/captcha.php'>
						</p>
							<label>Enter Security Code:</label>
							<input name='captcha' type='text'>
						</p>
							<label>&nbsp;</label><br>
							<input type='submit' value='Register'/>
						</p>
					</form>
				</div><!-- end regbox -->
			</div><!-- end main -->
			<div id='bottom'></div>
		</div><!-- end content --> 
	</div><!-- end wrapper -->
</body>
</html>
