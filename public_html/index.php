<?php
require("models/Path.php");
require(Path::models() . "config.php");

/*
*	STEPS
*	1. check for new notes
*	2. load old notes
*/ 


//------------------------ check for new notes ------------------------
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$text = null;

	//test and set input values
	if(isset($_POST['text'])){
		$text = test_input($_POST['text']);
	}

	if(!empty($text)){
		$note = new Note();
		$note->setText($text);
		$note->prepareAndSaveNote();	
		echo "Note saved! <br />ID: " . $note->getID();
	} else {
		echo "Note not saved! No input<br />";
	}
}

//------------------------ load old notes ------------------------
$notes = Note::fetchByUser(1);

?>

<!DOCTYPE html>
<html>
<head>
	<title>ThrowNote</title>
</head>
<body>

	<form role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
		<textarea rows="4" cols="60" name="text"></textarea>
		<input type="submit" name="submit" value="Submit" />
	</form>

	<hr />
	<h1>Notes</h1>

	<!-- Print out all notes -->
	<?php
		if(is_array($notes)){
			foreach($notes as $note){
				$note->linkifyFromText();
				echo "<p>" . 
				"ID: " . $note->getID() . "<br />" . 
				"TEXT: " . $note->getText() . "<br />";
				"</p>";
			}
		}
			 
	?>

</body>
</html>