<?php
include("../models/Path.php");
include(Path::models() . 'config.php');
include(Path::tests() . 'install.php');

class NoteTest extends PHPUnit_Framework_TestCase {

	//database object to be used by all tests
	private $db;

	//pre-built Note object for testing
	private $testNote;

	public function setUp(){
		$this->db = new Database();
		
		$this->testNote = new Note();
		$this->testNote->setID(99);
		$this->testNote->setText("This is a test note...");
		$this->testNote->setCreated("2016-01-10 17:10:07");
		$this->testNote->setUpdated("2016-02-11 18:11:08");
		$this->testNote->addTag("web");
		$this->testNote->addTag("clip");
	}

	public function tearDown(){
		unset($this->db);
		unset($this->testNote);
	}

	//------------------------ TESTS ------------------------

	//Test all getters.
	//NOTE: IF ANY FAIL, TRY TESTING THE SETTERS WITH A VAR_DUMP
	public function testGetters(){
		$this->assertEquals(99,$this->testNote->getID());
		$this->assertEquals("This is a test note...",$this->testNote->getText());
		$this->assertEquals("2016-01-10 17:10:07",$this->testNote->getCreated());
		$this->assertEquals("2016-02-11 18:11:08",$this->testNote->getUpdated());
		$this->assertEquals("clip",$this->testNote->getTags()[1]);
	}

	public function testToHTMLString(){
		$this->assertEquals("ID: 99<br />TEXT: This is a test note...<br />CREATED: 2016-01-10 17:10:07<br />UPDATED: 2016-02-11 18:11:08<br />TAGS: [ web clip ]<br />", $this->testNote->toHTMLString());
	}

	public function testSetters(){
		$this->testNote->setID(100);
		$this->testNote->setText("wow");
		$this->testNote->setCreated("2016-01-01 00:00:01");
		$this->testNote->setUpdated("2016-01-02 00:00:02");
		
		$newTags = array();
		$newTags[] = "new1";
		$newTags[] = "new2";
		
		$this->testNote->setTags($newTags);
		$this->testNote->addTag("new3");
		$this->testNote->removeTag("new1");

		$this->assertEquals("ID: 100<br />TEXT: wow<br />CREATED: 2016-01-01 00:00:01<br />UPDATED: 2016-01-02 00:00:02<br />TAGS: [ new2 new3 ]<br />", $this->testNote->toHTMLString());
	}

	/*
	*	@req: notes table with 
	*		record id=1 have text: test, created: 2016-01-10 17:10:07, updated=null
	*/
	public function testFetch(){
		$note = new Note();
		$note->fetch(1);

		$this->assertEquals("ID: 1<br />TEXT: test<br />CREATED: 2016-01-10 17:10:07<br />UPDATED: <br />TAGS: [ tag1 tag2 ]<br />", $note->toHTMLString());
	}

	/*
	*	@req notes table with record id=1 have text: test
	*/
	public function testFetchByUser(){
		$notes = Note::fetchByUser(1);

		$this->assertTrue(count($notes) > 0);
		$this->assertEquals(1, $notes[0]->getID());
		$this->assertEquals("test", $notes[0]->getText());
	}


	public function testSave(){
		//unset id to force new note
		unset($this->testNote->id);
		$uniqueText = "testsave: " . time();

		//a copy of original testNote to check correct save
		$oldNote = clone $this->testNote;

		$this->testNote->setText($uniqueText); 
		$this->testNote->save(); 

		//fetch the test note
		unset($this->testNote);
		$sql = "SELECT `id` FROM `notes` WHERE `text`= '" . $uniqueText . "'";
		$id = $this->db->select($sql)[0]['id'];

		$this->testNote = new Note();
		$this->testNote->fetch($id);

		$this->assertEquals($this->testNote->getText(),$uniqueText);
		$this->assertEquals($this->testNote->getTags()[1],$oldNote->getTags()[1]);
	}

	public function testAddNewNote(){
		$note = new Note();
		$note->setText("new note test");
		$note->addNewNote();

		$this->assertTrue(!empty($note->getID()));
		$id = $note->getID();

		$note = new Note();
		$note->fetch($id);

		$this->assertEquals("new note test", $note->getText());
	}

	public function testUpdateNote(){
		$note = new Note();
		$note->fetch(1);

		$note->setText("updated!");
		$note->updateNote();

		unset($note);
		$note = new Note();
		$note->fetch(1);

		$this->assertEquals("updated!", $note->getText());
	}

	public function testRemoveAllTagAssociations(){
		$note = new Note();
		$note->fetch(1);

		$note->removeAllTagAssociations();

		$sql = "DELETE FROM `tags_notes` WHERE tag=1";
		$this->db->query($sql);

		$sql = "SELECT * FROM `tags_notes` WHERE tag=1";
		$result = $this->db->select($sql);

		$this->assertEquals(0,count($result));
	}

	public function testGetTagID(){
		$this->assertEquals(1,$this->testNote->getTagID("tag1"));
		$this->assertEquals(-1,$this->testNote->getTagID("doesntExistTag"));
	}

	public function testAddDBTags(){
		$note = New Note();
		$note->fetch(1);
		$note->addTag("tag1");
		$note->addTag("tag3");
		$note->addDBTags();

		$sql = "SELECT * FROM `tags_notes` WHERE `note` = 1";
		$results = $this->db->select($sql);
		$this->assertTrue(count($results) > 0);
	}

	public function testFindTags(){
		$text = "this has a #hashtag a  #badhash-tag and a #goodhash_tag #bad'tag #good0tag #";
		$tags = $this->testNote->findTags($text);

		$this->assertEquals(5,count($tags));
		$this->assertEquals("#hashtag",$tags[0]);
		$this->assertEquals("#badhash",$tags[1]);
		$this->assertEquals("#goodhash_tag",$tags[2]);
		$this->assertEquals("#bad",$tags[3]);
		$this->assertEquals("#good0tag",$tags[4]);
	}

	public function testSetTagsFromText(){
		$text = "this has a #hashtag a  #badhash-tag and a #goodhash_tag #bad'tag #good0tag #";
		$this->testNote->setText($text);
		$this->testNote->setTagsFromText();
		$tags = $this->testNote->getTags();

		$this->assertEquals(5,count($tags));
		$this->assertEquals("#hashtag",$tags[0]);
		$this->assertEquals("#badhash",$tags[1]);
		$this->assertEquals("#goodhash_tag",$tags[2]);
		$this->assertEquals("#bad",$tags[3]);
		$this->assertEquals("#good0tag",$tags[4]);
	}

	
}
?>