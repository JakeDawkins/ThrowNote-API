<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

//Database Information
//Local Config
$db_host = "127.0.0.1"; //Host address (most likely localhost)
$db_name = "ThrowNote"; //Name of Database
$db_user = "root"; //Name of database user
$db_pass = "root"; //Password for database user

$db_table_prefix = "uc_";

GLOBAL $errors;
GLOBAL $successes;

$errors = array();
$successes = array();

/* Create a new mysqli object with database connection parameters */
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
GLOBAL $mysqli;

if(mysqli_connect_errno()) {
	echo "Connection Failed: " . mysqli_connect_errno();
	exit();
}

?>