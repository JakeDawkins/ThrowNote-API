<?php

require('models/config.php');
$db = new Database();

//var_dump($db->connect()); echo "<br /><br />";
echo $db->connect()->stat() . "<br />";

/*print_r($db->select('SELECT * FROM test_notes'));
	echo "<br />";
print_r($db->select('SELECT * FROM test_tags'));
	echo "<br />";
print_r($db->select('SELECT * FROM test_tags_notes'));
	echo "<br />";
print_r($db->select('SELECT * FROM test_users'));
	echo "<br />";*/
?>