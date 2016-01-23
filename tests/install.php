<?php
/*
*	THIS IS TO BE INCLUDED IN THE test.class FILES,
*	NOT TO BE USED BY ITSELF.
*	INCLUDES ALL DB SETUP CODE FOR THE TEST CASES.
*/

$db = new Database();

$clear = array();
$clear[] = "DELETE FROM `notes` WHERE `id` = 1 OR `id` = 2";
$clear[] = "DELETE FROM `tags_notes` WHERE `id` = 1 OR `id` = 2";
$clear[] = "DELETE FROM `tags` WHERE `id` = 1 OR `id` = 2";
$clear[] = "DELETE FROM `users` WHERE `id` = 1 OR `id` = 2";

$populate = array();
$populate[] = "INSERT INTO `users` (`id`, `username`, `password`, `email`)VALUES
				(1, 'jake', '123456', 'dawkinsjh@gmail.com'),
				(2, 'tester', 'password', 'jake.dawkins@newspring.cc')";
$populate[] = "INSERT INTO `notes` (`id`, `text`, `created`, `updated`, `owner`)VALUES
				(1, 'test', '2016-01-10 17:10:07', NULL, 1),
				(2, 'supppppp bro', '2016-01-10 17:10:10', '2016-01-20 16:45:42', 2)";
$populate[] = "INSERT INTO `tags` (`id`, `name`)VALUES
				(1, 'tag1'),
				(2, 'tag2')";
$populate[] = "INSERT INTO `tags_notes` (`id`, `note`, `tag`)VALUES
				(1, 1, 1),
				(2, 1, 2)";

//Run queries
foreach($clear as $clearSQL){
	$db->query($clearSQL);
}

foreach($populate as $sql){
	$db->query($sql);
}

?>