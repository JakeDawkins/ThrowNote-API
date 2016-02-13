<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config-uc.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

$pages = getPageFiles(); //Retrieve list of pages in root usercake folder
$dbpages = fetchAllPages(); //Retrieve list of pages in pages table
$creations = array();
$deletions = array();

//Check if any pages exist which are not in DB
foreach ($pages as $page){
	if(!isset($dbpages[$page])){
		$creations[] = $page;	
	}
}

//Enter new pages in DB if found
if (count($creations) > 0) {
	createPages($creations)	;
}

if (count($dbpages) > 0){
	//Check if DB contains pages that don't exist
	foreach ($dbpages as $page){
		if(!isset($pages[$page['page']])){
			$deletions[] = $page['id'];	
		}
	}
}

//Delete pages from DB if not found
if (count($deletions) > 0) {
	deletePages($deletions);
}

//Update DB pages
$dbpages = fetchAllPages();
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
			<h2>Admin Pages</h2>
			<div id='left-nav'>
				<?php include("left-nav.php"); ?>
			</div>
			<div id='main'>
				<table>
					<tr>
						<th>Id</th>
						<th>Page</th>
						<th>Access</th>
					</tr>

					<!-- display list of pages -->
					<?php foreach ($dbpages as $page){ ?>
						<tr>
							<td>
								<?php echo $page['id']; ?>
							</td>
							<td>
								<a href ='admin_page.php?id=<?php echo $page['id']; ?>'><?php echo $page['page']; ?></a>
							</td>
							<td>
								<?php 
								//Show public/private setting of page
								if($page['private'] == 0){
									echo "Public";
								} else {
									echo "Private";	
								}
								?>
							</td>
						</tr>
					<?php } ?>
				</table>
			</div> <!-- end main -->
			<div id='bottom'></div>
		</div> <!-- end content -->
	</div><!-- end wrapper -->
</body>
</html>