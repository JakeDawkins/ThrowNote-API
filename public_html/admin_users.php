<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config-uc.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Forms posted
if(!empty($_POST)) {
	$deletions = $_POST['delete'];
	if ($deletion_count = deleteUsers($deletions)){
		$successes[] = lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count));
	} else {
		$errors[] = lang("SQL_ERROR");
	}
}

$userData = fetchAllUsers(); //Fetch information for all users
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
			<h2>Admin Users</h2>
			<div id='left-nav'>
			<?php include("left-nav.php"); ?>
			</div>
			<div id='main'>
				<?php echo resultBlock($errors,$successes); ?>

				<form name='adminUsers' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
					<table>
						<tr>
							<th>Delete</th><th>Username</th><th>Display Name</th><th>Title</th><th>Last Sign In</th>
						</tr>
						<?php
						//Cycle through users
						foreach ($userData as $v1) {
							echo "
							<tr>
								<td><input type='checkbox' name='delete[".$v1['id']."]' id='delete[".$v1['id']."]' value='".$v1['id']."'></td>
								<td><a href='admin_user.php?id=".$v1['id']."'>".$v1['user_name']."</a></td>
								<td>".$v1['display_name']."</td>
								<td>".$v1['title']."</td>
								<td>
								";
								
								//Interprety last login
								if ($v1['last_sign_in_stamp'] == '0'){
									echo "Never";	
								}
								else {
									echo date("j M, Y", $v1['last_sign_in_stamp']);
								}
								echo "
								</td>
							</tr>";
						}
						?>
					</table>
					<input type='submit' name='Submit' value='Delete' />
				</form>
			</div><!-- end main -->
		</div><!-- end content -->
		<div id='bottom'></div>
	</div><!-- end wrapper -->
</body>
</html>
