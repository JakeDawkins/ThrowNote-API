<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config-uc.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//User has confirmed they want their password changed 
if(!empty($_GET["confirm"])) {
	$token = trim($_GET["confirm"]);
	
	if($token == "" || !validateActivationToken($token,TRUE)) {
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
	} else {
		$rand_pass = getUniqueCode(15); //Get unique code
		$secure_pass = generateHash($rand_pass); //Generate random hash
		$userdetails = fetchUserDetails(NULL,$token); //Fetchs user details
		$mail = new userCakeMail();		
		
		//Setup our custom hooks
		$hooks = array(
			"searchStrs" => array("#GENERATED-PASS#","#USERNAME#"),
			"subjectStrs" => array($rand_pass,$userdetails["display_name"])
			);
		
		if(!$mail->newTemplateMsg("your-lost-password.txt",$hooks)) {
			$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
		} else {	
			if(!$mail->sendMail($userdetails["email"],"Your new password")) {
				$errors[] = lang("MAIL_ERROR");
			} else {
				if(!updatePasswordFromToken($secure_pass,$token)) {
					$errors[] = lang("SQL_ERROR");
				} else {	
					if(!flagLostPasswordRequest($userdetails["user_name"],0)) {
						$errors[] = lang("SQL_ERROR");
					} else {
						$successes[]  = lang("FORGOTPASS_NEW_PASS_EMAIL");
					}
				}
			}
		}
	}
}

//User has denied this request
if(!empty($_GET["deny"])) {
	$token = trim($_GET["deny"]);
	
	if($token == "" || !validateActivationToken($token,TRUE)) {
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
	} else {
		$userdetails = fetchUserDetails(NULL,$token);
		
		if(!flagLostPasswordRequest($userdetails["user_name"],0)) {
			$errors[] = lang("SQL_ERROR");
		} else {
			$successes[] = lang("FORGOTPASS_REQUEST_CANNED");
		}
	}
}

//Forms posted
if(!empty($_POST)) {
	$email = $_POST["email"];
	$username = sanitize($_POST["username"]);
	
	//Perform some validation
	//Feel free to edit / change as required
	
	if(trim($email) == "") {
		$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
	}
	//Check to ensure email is in the correct format / in the db
	else if(!isValidEmail($email) || !emailExists($email)) {
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	
	if(trim($username) == "") {
		$errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
	} else if(!usernameExists($username)) {
		$errors[] = lang("ACCOUNT_INVALID_USERNAME");
	}
	
	if(count($errors) == 0) {
		//Check that the username / email are associated to the same account
		if(!emailUsernameLinked($email,$username)) {
			$errors[] =  lang("ACCOUNT_USER_OR_EMAIL_INVALID");
		} else {
			//Check if the user has any outstanding lost password requests
			$userdetails = fetchUserDetails($username);
			if($userdetails["lost_password_request"] == 1) {
				$errors[] = lang("FORGOTPASS_REQUEST_EXISTS");
			} else {
				//Email the user asking to confirm this change password request
				//We can use the template builder here
				
				//We use the activation token again for the url key it gets regenerated everytime it's used.
				
				$mail = new userCakeMail();
				$confirm_url = lang("CONFIRM")."\n".$websiteUrl."forgot-password.php?confirm=".$userdetails["activation_token"];
				$deny_url = lang("DENY")."\n".$websiteUrl."forgot-password.php?deny=".$userdetails["activation_token"];
				
				//Setup our custom hooks
				$hooks = array(
					"searchStrs" => array("#CONFIRM-URL#","#DENY-URL#","#USERNAME#"),
					"subjectStrs" => array($confirm_url,$deny_url,$userdetails["user_name"])
					);
				
				if(!$mail->newTemplateMsg("lost-password-request.txt",$hooks)) {
					$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
				} else {
					if(!$mail->sendMail($userdetails["email"],"Lost password request")) {
						$errors[] = lang("MAIL_ERROR");
					} else {
						//Update the DB to show this account has an outstanding request
						if(!flagLostPasswordRequest($userdetails["user_name"],1)) {
							$errors[] = lang("SQL_ERROR");
						} else {	
							$successes[] = lang("FORGOTPASS_REQUEST_SUCCESS");
						}
					}
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
			<h2>Forgot Password</h2>
			<div id='left-nav'>";

			<?php include("left-nav.php"); ?>

			</div>
			<div id='main'>";
				<?php echo resultBlock($errors,$successes); ?>

				<div id='regbox'>
					<form name='newLostPass' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
						<p>
							<label>Username:</label>
							<input type='text' name='username' />
						</p>
						<p>    
							<label>Email:</label>
							<input type='text' name='email' />
						</p>
						<p>
							<label>&nbsp;</label>
							<input type='submit' value='Submit' />
						</p>
					</form>
				</div><!-- end regbox -->
			</div><!-- end main -->
			<div id='bottom'></div>
		</div><!-- end content -->
	</div><!-- end wrapper -->
</body>
</html>
