<?php

require('models/config.php');

$att = new Attachment();
//var_dump($att->lookupFiletypeID('jpg'));

// $att->fetch(1);
// var_dump($att);

$filename = 'my_new.png';
$dir = '/my/test';

$att->setFilename($filename);

$info = new SplFileInfo($filename);
$ext = $info->getExtension();
$filetypeID = $att->lookupFiletypeID($ext);

$att->setID(3);
$att->setFiletypeID($filetypeID);
$att->setNoteID(2);
$att->setPath($dir . '/' . $filename);
$att->save();

echo json_encode($att->toArray());
?>