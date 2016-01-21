<?php
include("../models/Path.php");
include(Path::models() . 'config.php');

class DatabaseTest extends PHPUnit_Framework_TestCase {

	//database object to be used by all tests
	private $db;

	public function setUp(){
		$this->db = new Database();
	}
	public function tearDown(){
		unset($this->db);
	}

	//------------------------ TESTS ------------------------

	public function testConnectSuccess(){
		$mysqli = $this->db->connect();
		$this->assertTrue($mysqli != false);
	}

	public function testPrepareQuery(){
		$sql = $this->db->prepareQuery("SELECT * FROM `table` WHERE `field1` = ? AND `field2` = ? AND ? AND `field3` = ?","lol 'WUT'", 13, true, null);
		$this->assertEquals($sql, "SELECT * FROM `table` WHERE `field1` = 'lol \'WUT\'' AND `field2` = 13 AND TRUE AND `field3` = NULL");

		$sql = $this->db->prepareQuery('UPDATE `some_table` SET `some_column` = ?, `some_other_column` = ?, `some_id` = ? WHERE `item` = ?', '20', 21, 69, 'this_val');
		$this->assertEquals($sql, "UPDATE `some_table` SET `some_column` = '20', `some_other_column` = 21, `some_id` = 69 WHERE `item` = 'this_val'");
	}

	/*
	*	@prereq: must be records in test_notes
	*/
	public function testQuery(){
		$sql = "SELECT * FROM notes";
		$result = $this->db->query($sql)->num_rows;
		$this->assertTrue($result > 0); 
	}

	/*
	*	@preq: must be records in test_notes where...
	*		record with id=1 has text value of "test"
	*		recond record in table has created timedate of "2016-01-10 17:10:10"
	*/
	public function testSelect(){
		$sql = "SELECT text FROM notes WHERE id=1";
		$result = $this->db->select($sql);
		$this->assertEquals("test",$result[0]['text']);

		$sql = "SELECT * FROM notes";
		$result = $this->db->select($sql);
		$this->assertTrue(count($result) > 0);
		$this->assertEquals("2016-01-10 17:10:10", $result[1]['created']);
	}
}
?>