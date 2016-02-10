<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Forms posted
if(!empty($_POST) && $emailActivation){
	$email = $_POST["email"];
	$username = $_POST["username"];
	
	//Perform some validation
	//Feel free to edit / change as required
	if(trim($email) == ""){
		$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
	}
	//Check to ensure email is in the correct format / in the db
	else if(!isValidEmail($email) || !emailExists($email)){
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	
	if(trim($username) == ""){
		$errors[] =  lang("ACCOUNT_SPECIFY_USERNAME");
	} else if(!usernameExists($username)) {
		$errors[] = lang("ACCOUNT_INVALID_USERNAME");
	}
	
	if(count($errors) == 0) {
		//Check that the username / email are associated to the same account
		if(!emailUsernameLinked($email,$username)) {
			$errors[] = lang("ACCOUNT_USER_OR_EMAIL_INVALID");
		} else {
			$userdetails = fetchUserDetails($username);
			
			//See if the user's account is activation
			if($userdetails["active"]==1) {
				$errors[] = lang("ACCOUNT_ALREADY_ACTIVE");
			} else {
				if ($resend_activation_threshold == 0) {
					$hours_diff = 0;
				} else {
					$last_request = $userdetails["last_activation_request"];
					$hours_diff = round((time()-$last_request) / (3600*$resend_activation_threshold),0);
				}
				
				if($resend_activation_threshold!=0 && $hours_diff <= $resend_activation_threshold) {
					$errors[] = lang("ACCOUNT_LINK_ALREADY_SENT",array($resend_activation_threshold));
				} else {
					//For security create a new activation url;
					$new_activation_token = generateActivationToken();
					
					if(!updateLastActivationRequest($new_activation_token,$username,$email)) {
						$errors[] = lang("SQL_ERROR");
					} else {
						$mail = new userCakeMail();
						
						$activation_url = $websiteUrl."activate-account.php?token=".$new_activation_token;
						
						//Setup our custom hooks
						$hooks = array(
							"searchStrs" => array("#ACTIVATION-URL","#USERNAME#"),
							"subjectStrs" => array($activation_url,$userdetails["display_name"])
							);
						
						if(!$mail->newTemplateMsg("resend-activation.txt",$hooks)) {
							$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
						} else {
							if(!$mail->sendMail($userdetails["email"],"Activate your ".$websiteName." Account")) {
								$errors[] = lang("MAIL_ERROR");
							} else {
								//Success, user details have been updated in the db now mail this information out.
								$successes[] = lang("ACCOUNT_NEW_ACTIVATION_SENT");
							}
						}
					}
				}
			}
		}
	}
}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: account.php"); die(); }
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
			<h2>Resend Activation</h2>
			<div id='left-nav'>";

			<?php include("left-nav.php"); ?>

			</div>
			<div id='main'>
				<?php echo resultBlock($errors,$successes); ?>

				<div id='regbox'>
					<?php 
					//Show disabled if email activation not required
					if(!$emailActivation){ 
					        echo lang("FEATURE_DISABLED");
					} else { ?>
						<form name='resendActivation' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
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
					<?php } ?>
				</div><!-- end regbox -->
			</div><!-- end main -->
			<div id='bottom'></div>
		</div><!-- end content -->
	</div><!-- end wrapper --> 
</body>
</html>