<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config-uc.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$pageId = $_GET['id'];

//Check if selected pages exist
if(!pageIdExists($pageId)){
	header("Location: admin_pages.php"); die();	
}

$pageDetails = fetchPageDetails($pageId); //Fetch information specific to page

//Forms posted
if(!empty($_POST)){
	$update = 0;
	
	if(!empty($_POST['private'])){ $private = $_POST['private']; }
	
	//Toggle private page setting
	if (isset($private) AND $private == 'Yes'){
		if ($pageDetails['private'] == 0){
			if (updatePrivate($pageId, 1)){
				$successes[] = lang("PAGE_PRIVATE_TOGGLED", array("private"));
			} else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	} elseif ($pageDetails['private'] == 1){
		if (updatePrivate($pageId, 0)){
			$successes[] = lang("PAGE_PRIVATE_TOGGLED", array("public"));
		} else {
			$errors[] = lang("SQL_ERROR");	
		}
	}
	
	//Remove permission level(s) access to page
	if(!empty($_POST['removePermission'])){
		$remove = $_POST['removePermission'];
		if ($deletion_count = removePage($pageId, $remove)){
			$successes[] = lang("PAGE_ACCESS_REMOVED", array($deletion_count));
		} else {
			$errors[] = lang("SQL_ERROR");	
		}
	}
	
	//Add permission level(s) access to page
	if(!empty($_POST['addPermission'])){
		$add = $_POST['addPermission'];
		if ($addition_count = addPage($pageId, $add)){
			$successes[] = lang("PAGE_ACCESS_ADDED", array($addition_count));
		} else {
			$errors[] = lang("SQL_ERROR");	
		}
	}
	
	$pageDetails = fetchPageDetails($pageId);
}

$pagePermissions = fetchPagePermissions($pageId);
$permissionData = fetchAllPermissions();

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
			<h2>Admin Page</h2>
			<div id='left-nav'>

			<?php include("left-nav.php");?> 

			</div>
			<div id='main'>
				<?php echo resultBlock($errors,$successes); ?> 

				<form name='adminPage' action='".$_SERVER['PHP_SELF']."?id=".$pageId."' method='post'>
					<input type='hidden' name='process' value='1'>
					<table>
						<tr>
							<td>
								<h3>Page Information</h3>
								<div id='regbox'>
									<p>
										<label>ID:</label>
										<?php echo $pageDetails['id']; ?>
									</p>
									<p>
										<label>Name:</label>
										<?php echo $pageDetails['page']; ?>
									</p>
									<p>
										<label>Private:</label>
										<?php 
											//Display private checkbox
											if ($pageDetails['private'] == 1){
												echo "<input type='checkbox' name='private' id='private' value='Yes' checked>";
											}
											else {
												echo "<input type='checkbox' name='private' id='private' value='Yes'>";	
											}
										?>
									</p>
								</div>
							</td>
							<td>
								<h3>Page Access</h3>
								<div id='regbox'>
									<p>
										Remove Access:
										<?php
										//Display list of permission levels with access
										foreach ($permissionData as $v1) {
											if(isset($pagePermissions[$v1['id']])){
												echo "<br><input type='checkbox' name='removePermission[".$v1['id']."]' id='removePermission[".$v1['id']."]' value='".$v1['id']."'> ".$v1['name'];
											}
										}
										?>
									</p>
									<p>
										Add Access:
										<?php
										//Display list of permission levels without access
										foreach ($permissionData as $v1) {
											if(!isset($pagePermissions[$v1['id']])){
												echo "<br><input type='checkbox' name='addPermission[".$v1['id']."]' id='addPermission[".$v1['id']."]' value='".$v1['id']."'> ".$v1['name'];
											}
										}
										?>
									</p>
								</div>
							</td>
						</tr>
					</table>
					<p>
					<label>&nbsp;</label>
					<input type='submit' value='Update' />
					</p>
				</form>
			</div> <!-- end main -->
		</div><!-- end content -->
		<div id='bottom'></div>
	</div>
</body>
</html>
