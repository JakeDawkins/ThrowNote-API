<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he is not logged in
if(!isUserLoggedIn()) { header("Location: login.php"); die(); }

if(!empty($_POST)) {
	$errors = array();
	$successes = array();
	$password = $_POST["password"];
	$password_new = $_POST["passwordc"];
	$password_confirm = $_POST["passwordcheck"];
	
	$errors = array();
	$email = $_POST["email"];
	
	//Perform some validation
	//Feel free to edit / change as required
	
	//Confirm the hashes match before updating a users password
	$entered_pass = generateHash($password,$loggedInUser->hash_pw);
	
	if (trim($password) == ""){
		$errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
	} else if($entered_pass != $loggedInUser->hash_pw) {
		//No match
		$errors[] = lang("ACCOUNT_PASSWORD_INVALID");
	} 
	if($email != $loggedInUser->email) {
		if(trim($email) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
		} else if(!isValidEmail($email)) {
			$errors[] = lang("ACCOUNT_INVALID_EMAIL");
		} else if(emailExists($email)) {
			$errors[] = lang("ACCOUNT_EMAIL_IN_USE", array($email));	
		}
		
		//End data validation
		if(count($errors) == 0) {
			$loggedInUser->updateEmail($email);
			$successes[] = lang("ACCOUNT_EMAIL_UPDATED");
		}
	}
	
	if ($password_new != "" OR $password_confirm != "") {
		if(trim($password_new) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_NEW_PASSWORD");
		} else if(trim($password_confirm) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_CONFIRM_PASSWORD");
		} else if(minMaxRange(8,50,$password_new)) {	
			$errors[] = lang("ACCOUNT_NEW_PASSWORD_LENGTH",array(8,50));
		} else if($password_new != $password_confirm) {
			$errors[] = lang("ACCOUNT_PASS_MISMATCH");
		}
		
		//End data validation
		if(count($errors) == 0) {
			//Also prevent updating if someone attempts to update with the same password
			$entered_pass_new = generateHash($password_new,$loggedInUser->hash_pw);
			
			if($entered_pass_new == $loggedInUser->hash_pw) {
				//Don't update, this fool is trying to update with the same password Â¬Â¬
				$errors[] = lang("ACCOUNT_PASSWORD_NOTHING_TO_UPDATE");
			} else {
				//This function will create the new hash and update the hash_pw property.
				$loggedInUser->updatePassword($password_new);
				$successes[] = lang("ACCOUNT_PASSWORD_UPDATED");
			}
		}
	}
	if(count($errors) == 0 AND count($successes) == 0){
		$errors[] = lang("NOTHING_TO_UPDATE");
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
			<h2>User Settings</h2>
			<div id='left-nav'>
				<?php include("left-nav.php"); ?>
			</div>
			<div id='main'>
				<?php echo resultBlock($errors,$successes); ?>

				<div id='regbox'>
					<form name='updateAccount' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
						<p>
							<label>Password:</label>
							<input type='password' name='password' />
						</p>
						<p>
							<label>Email:</label>
							<input type='text' name='email' value='<?php echo $loggedInUser->email; ?>' />
						</p>
						<p>
							<label>New Pass:</label>
							<input type='password' name='passwordc' />
						</p>
						<p>
							<label>Confirm Pass:</label>
							<input type='password' name='passwordcheck' />
						</p>
						<p>
							<label>&nbsp;</label>
							<input type='submit' value='Update' />
						</p>
					</form>
				</div><!-- end regbox -->
			</div><!-- end main -->
			<div id='bottom'></div>
		</div><!-- end content -->
	</div><!-- end wrapper -->
</body>
</html>