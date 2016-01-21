<?php

require('models/config.php');

$note = new Note();
$note->fetch(17);
//echo $note->toHTMLString(); 

//$note->setText("hi ;)");
//$note->addTag("dudeeee");
//$note->addTag("catsr");
//$note->save();


?>