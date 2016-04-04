<?php
require_once("Path.php");
require_once(Path::models() . "class.Database.php");
require_once(Path::models() . "class.Note.php");
require_once(Path::models() . "class.Attachment.php");
require_once(Path::models() . "Funcs.php");

date_default_timezone_set('America/New_York');

$upload_dir = dirname(dirname(dirname(__FILE__))) . '/uploads';

GLOBAL $errors;
GLOBAL $successes;

$errors = array();
$successes = array();

?>