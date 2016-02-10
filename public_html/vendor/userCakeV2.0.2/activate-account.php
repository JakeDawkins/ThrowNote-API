<?php 
/*
UserCake Version: 2.0.1
http://usercake.com
*/
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Get token param
if(isset($_GET["token"])){	
	$token = $_GET["token"];	
	if(!isset($token)){
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
	} else if(!validateActivationToken($token)) {//Check for a valid token. Must exist and active must be = 0
		$errors[] = lang("ACCOUNT_TOKEN_NOT_FOUND");
	} else {
		//Activate the users account
		if(!setUserActive($token)) {
			$errors[] = lang("SQL_ERROR");
		}
	}
} else {
	$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
}

if(count($errors) == 0) {
	$successes[] = lang("ACCOUNT_ACTIVATION_COMPLETE");
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
		<h2>Activate Account</h2>

		<div id='left-nav'>";
			<?php include("left-nav.php"); ?>
		</div>
		<div id='main'>";
			<?php echo resultBlock($errors,$successes); ?>
		</div>
		<div id='bottom'></div>
	</div>
</body>
</html>
