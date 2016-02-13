<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config-uc.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Forms posted
if(!empty($_POST)){
	$cfgId = array();
	$newSettings = $_POST['settings'];
	
	//Validate new site name
	if ($newSettings[1] != $websiteName) {
		$newWebsiteName = $newSettings[1];
		if(minMaxRange(1,150,$newWebsiteName)){
			$errors[] = lang("CONFIG_NAME_CHAR_LIMIT",array(1,150));
		} else if (count($errors) == 0) {
			$cfgId[] = 1;
			$cfgValue[1] = $newWebsiteName;
			$websiteName = $newWebsiteName;
		}
	}
	
	//Validate new URL
	if ($newSettings[2] != $websiteUrl) {
		$newWebsiteUrl = $newSettings[2];
		if(minMaxRange(1,150,$newWebsiteUrl)){
			$errors[] = lang("CONFIG_URL_CHAR_LIMIT",array(1,150));
		} else if (substr($newWebsiteUrl, -1) != "/"){
			$errors[] = lang("CONFIG_INVALID_URL_END");
		} else if (count($errors) == 0) {
			$cfgId[] = 2;
			$cfgValue[2] = $newWebsiteUrl;
			$websiteUrl = $newWebsiteUrl;
		}
	}
	
	//Validate new site email address
	if ($newSettings[3] != $emailAddress) {
		$newEmail = $newSettings[3];
		if(minMaxRange(1,150,$newEmail)) {
			$errors[] = lang("CONFIG_EMAIL_CHAR_LIMIT",array(1,150));
		} elseif(!isValidEmail($newEmail)) {
			$errors[] = lang("CONFIG_EMAIL_INVALID");
		} else if (count($errors) == 0) {
			$cfgId[] = 3;
			$cfgValue[3] = $newEmail;
			$emailAddress = $newEmail;
		}
	}
	
	//Validate email activation selection
	if ($newSettings[4] != $emailActivation) {
		$newActivation = $newSettings[4];
		if($newActivation != "true" AND $newActivation != "false") {
			$errors[] = lang("CONFIG_ACTIVATION_TRUE_FALSE");
		} else if (count($errors) == 0) {
			$cfgId[] = 4;
			$cfgValue[4] = $newActivation;
			$emailActivation = $newActivation;
		}
	}
	
	//Validate new email activation resend threshold
	if ($newSettings[5] != $resend_activation_threshold) {
		$newResend_activation_threshold = $newSettings[5];
		if($newResend_activation_threshold > 72 OR $newResend_activation_threshold < 0) {
			$errors[] = lang("CONFIG_ACTIVATION_RESEND_RANGE",array(0,72));
		} else if (count($errors) == 0) {
			$cfgId[] = 5;
			$cfgValue[5] = $newResend_activation_threshold;
			$resend_activation_threshold = $newResend_activation_threshold;
		}
	}
	
	//Validate new language selection
	if ($newSettings[6] != $language) {
		$newLanguage = $newSettings[6];
		if(minMaxRange(1,150,$language)) {
			$errors[] = lang("CONFIG_LANGUAGE_CHAR_LIMIT",array(1,150));
		} elseif (!file_exists($newLanguage)) {
			$errors[] = lang("CONFIG_LANGUAGE_INVALID",array($newLanguage));				
		} else if (count($errors) == 0) {
			$cfgId[] = 6;
			$cfgValue[6] = $newLanguage;
			$language = $newLanguage;
		}
	}
	
	//Update configuration table with new settings
	if (count($errors) == 0 AND count($cfgId) > 0) {
		updateConfig($cfgId, $cfgValue);
		$successes[] = lang("CONFIG_UPDATE_SUCCESSFUL");
	}
}

$languages = getLanguageFiles(); //Retrieve list of language files
$permissionData = fetchAllPermissions(); //Retrieve list of all permission levels
?>

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<title>ThrowNote</title>
</head>
<body>
	<div id='wrapper'>
		<div id='top'><div id='logo'></div></div>
		<div id='content'>
			<h1>UserCake</h1>
			<h2>Admin Configuration</h2>
			<div id='left-nav'>

			<?php include("left-nav.php");?>

			</div>
			<div id='main'>

				<?php  echo resultBlock($errors,$successes); ?>

				<div id='regbox'>
					<form name='adminConfiguration' action=' <?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
						<p>
							<label>Website Name:</label>
							<input type='text' name='settings[<?php echo $settings['website_name']['id']; ?>]' value='<?php echo $websiteName; ?>' />
						</p>
						<p>
							<label>Website URL:</label>
							<input type='text' name='settings[<?php echo $settings['website_url']['id']; ?>]' value='<?php echo $websiteUrl; ?>' />
						</p>
						<p>
							<label>Email:</label>
							<input type='text' name='settings[<?php echo $settings['email']['id']; ?>]' value='<?php echo $emailAddress; ?>' />
						</p>
						<p>
							<label>Activation Threshold:</label>
							<input type='text' name='settings[<?php echo $settings['resend_activation_threshold']['id']; ?>]' value='<?php echo $resend_activation_threshold; ?>' />
						</p>
						<p>
							<label>Language:</label>
							<select name='settings[<?php echo $settings['language']['id']; ?>]'>";

							<?php 
							//Display language options
							foreach ($languages as $optLang){
								if ($optLang == $language){
									echo "<option value='".$optLang."' selected>$optLang</option>";
								}
								else {
									echo "<option value='".$optLang."'>$optLang</option>";
								}
							}
							?>
							</select>
						</p>
						<p>
							<label>Email Activation:</label>
							<select name='settings[<?php echo $settings['activation']['id']; ?>]'>";

							<?php
							//Display email activation options
							if ($emailActivation == "true"){
								echo "
								<option value='true' selected>True</option>
								<option value='false'>False</option>
								";
							}
							else {
								echo "
								<option value='true'>True</option>
								<option value='false' selected>False</option>
								";
							} 
							?>
							</select>
						</p>
						<input type='submit' name='Submit' value='Submit' />
					</form>
				</div> <!-- end regbox -->
			</div> <!-- end main -->
			<div id='bottom'></div>
		</div> <!-- end content -->
	</div> <!-- end wrapper -->
</body>
</html>
