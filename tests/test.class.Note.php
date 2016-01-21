<?php
include("../models/Path.php");
include(Path::models() . 'config.php');

class DatabaseTest extends PHPUnit_Framework_TestCase {

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
	}

	public function testToHTMLString(){
		$this->assertEquals("ID: 99<br />TEXT: This is a test note...<br />CREATED: 2016-01-10 17:10:07<br />UPDATED: 2016-02-11 18:11:08<br />TAGS: [ web clip ]<br />", $this->testNote->toHTMLString());
	}

	/*
		@req: notes table with 
			record id=1 have text: test, created: 2016-01-10 17:10:07, updated=null
	*/
	public function testFetch(){
		$note = new Note();
		$note->fetch(1);

		$this->assertEquals("ID: 1<br />TEXT: test<br />CREATED: 2016-01-10 17:10:07<br />UPDATED: <br />TAGS: [ tag1 tag2 ]<br />", $note->toHTMLString());
	}



}


?>