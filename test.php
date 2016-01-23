<?php

require('models/config.php');

$note = new Note();
$note->fetch(18);
echo $note->toHTMLString() . "<br />"; 

//$note->setText("hi ;)");
$note->addTag("mynewtag");
$note->addTag("cats");
echo $note->toHTMLString() . "<br />"; 
var_dump($note);

//$note->save();


?>