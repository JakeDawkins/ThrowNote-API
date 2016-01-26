<?php

require('models/config.php');

//$note = new Note();
//$note->fetch(23);

//$note->linkifyFromText();
//$note->linkifyTagsFromText();

//echo $note->getText();

$text = "#this is a #test of the www.google.com#hi tagging #system";


preg_match_all("/(^#\w+)/", $text, $tags);
preg_match_all("/([ ])(#\w+)/", $text, $tags2);

$finalTags = array_merge($tags[0],$tags2[0]);

print_r($tags[0]); 
echo "<br />";
print_r($tags2[0]);
echo "<br />";
print_r($finalTags);




?>