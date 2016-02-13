<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config-uc.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Forms posted
if(!empty($_POST)) {
	//Delete permission levels
	if(!empty($_POST['delete'])){
		$deletions = $_POST['delete'];
		if ($deletion_count = deletePermission($deletions)){
			$successes[] = lang("PERMISSION_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
	}
	
	//Create new permission level
	if(!empty($_POST['newPermission'])) {
		$permission = trim($_POST['newPermission']);
		
		//Validate request
		if (permissionNameExists($permission)){
			$errors[] = lang("PERMISSION_NAME_IN_USE", array($permission));
		} elseif (minMaxRange(1, 50, $permission)){
			$errors[] = lang("PERMISSION_CHAR_LIMIT", array(1, 50));	
		} else {
			if (createPermission($permission)) {
				$successes[] = lang("PERMISSION_CREATION_SUCCESSFUL", array($permission));
			} else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
}

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
			<h2>Admin Permissions</h2>
			<div id='left-nav'>

			<?php include("left-nav.php"); ?>

			</div>
			<div id='main'>
				<?php echo resultBlock($errors,$successes); ?>

				<form name='adminPermissions' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
					<table>
					<tr>
						<th>Delete</th>
						<th>Permission Name</th>
					</tr>

					<?php
					//List each permission level
					foreach ($permissionData as $v1) {
						echo "
						<tr>
						<td><input type='checkbox' name='delete[".$v1['id']."]' id='delete[".$v1['id']."]' value='".$v1['id']."'></td>
						<td><a href='admin_permission.php?id=".$v1['id']."'>".$v1['name']."</a></td>
						</tr>";
					}
					?>

					</table>
					<p>
					<label>Permission Name:</label>
					<input type='text' name='newPermission' />
					</p>                                
					<input type='submit' name='Submit' value='Submit' />
				</form>
			</div><!-- end main -->
		</div> <!-- end content -->
		<div id='bottom'></div>
	</div><!-- end wrapper -->
</body>
</html>
