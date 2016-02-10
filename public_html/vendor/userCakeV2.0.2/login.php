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
if(!empty($_POST)) {
	$errors = array();
	$username = sanitize(trim($_POST["username"]));
	$password = trim($_POST["password"]);
	
	//Perform some validation
	//Feel free to edit / change as required
	if($username == "") {
		$errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
	}
	if($password == "") {
		$errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
	}

	if(count($errors) == 0) {
		//A security note here, never tell the user which credential was incorrect
		if(!usernameExists($username)) {
			$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
		} else {
			$userdetails = fetchUserDetails($username);
			//See if the user's account is activated
			if($userdetails["active"]==0) {
				$errors[] = lang("ACCOUNT_INACTIVE");
			} else {
				//Hash the password and use the salt from the database to compare the password.
				$entered_pass = generateHash($password,$userdetails["password"]);
				
				if($entered_pass != $userdetails["password"]) {
					//Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
					$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
				} else {
					//Passwords match! we're good to go'
					
					//Construct a new logged in user object
					//Transfer some db data to the session object
					$loggedInUser = new loggedInUser();
					$loggedInUser->email = $userdetails["email"];
					$loggedInUser->user_id = $userdetails["id"];
					$loggedInUser->hash_pw = $userdetails["password"];
					$loggedInUser->title = $userdetails["title"];
					$loggedInUser->displayname = $userdetails["display_name"];
					$loggedInUser->username = $userdetails["user_name"];
					
					//Update last sign in
					$loggedInUser->updateLastSignIn();
					$_SESSION["userCakeUser"] = $loggedInUser;
					
					//Redirect to user account page
					header("Location: account.php");
					die();
				}
			}
		}
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
			<h2>Login</h2>
			<div id='left-nav'>

			<?php include("left-nav.php"); ?> 

			</div>
			<div id='main'>
				<?php echo resultBlock($errors,$successes); ?>

				<div id='regbox'>
					<form name='login' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
						<p>
						<label>Username:</label>
						<input type='text' name='username' />
						</p>
						<p>
						<label>Password:</label>
						<input type='password' name='password' />
						</p>
						<p>
						<label>&nbsp;</label>
						<input type='submit' value='Login' />
						</p>
					</form>
				</div><!-- end regbox -->
			</div><!-- end main -->
			<div id='bottom'></div>
		</div><!-- end content -->
	</div><!-- end wrapper -->
</body>
</html>