<?php
require_once("config.php");

class Attachment {
	private $id;
	private $filename;
	private $filetypeID;
	private $noteID;
	private $path;

	//------------------------ GETTERS ------------------------
	public function getID(){
		return $this->id;
	}

	public function getFilename(){
		return $this->filename;
	}

	public function getFiletypeID(){
		return $this->filetypeID;
	}

	public function getNoteID(){
		return $this->noteID;
	}

	public function getPath(){
		return $this->path;
	}

	public function toArray(){
		return array(
			'id' => $this->id,
			'filename' => $this->filename,
			'filetypeID' => $this->filetypeID,
			'noteID' => $this->noteID,
			'path' => $this->path
			);
	}

	//------------------------ SETTERS ------------------------
	public function setID($id){
		$this->id = $id;
	}

	public function setFilename($filename){
		$this->filename = $filename;
	}

	public function setFiletypeID($filetypeID){
		$this->filetypeID = $filetypeID;
	}

	public function setNoteID($noteID){
		$this->noteID = $noteID;
	}

	public function setPath($path){
		$this->path = $path;
	}


	//------------------------ DB ------------------------
	public function save(){
		if(!isset($this->id)){
			$this->saveNewAttachment();
		} else {
			$this->updateAttachment();
		}
	}

	public function saveNewAttachment(){
		$db = new Database();

		//first, add
		$sql = 'INSERT INTO `attachments`(`filename`,`filetype_id`,`note_id`,`path`) VALUES(?,?,?,?)';
		$sql = $db->prepareQuery($sql, $this->filename, $this->filetypeID, $this->noteID, $this->path);
		$db->query($sql);

		//next, get id
		$sql = 'SELECT `id` FROM `attachments` WHERE `path`=? AND `note_id`=?';
		$sql = $db->prepareQuery($sql, $this->path, $this->noteID);
		$results = $db->select($sql);

		if(!is_array($results) || count($results) == 0) return false;
		$this->id = $results[0]['id'];
	}

	public function updateAttachment(){
		$db = new Database();

		$sql = 'UPDATE `attachments` SET `filename`=?, `filetype_id`=?, `note_id`=?, `path`=? WHERE `id`=?';
		$sql = $db->prepareQuery($sql, $this->filename, $this->filetypeID, $this->noteID, $this->path, $this->id);
		$db->query($sql);

		//next, get id
		$sql = 'SELECT `id` FROM `attachments` WHERE `path`=? AND `note_id`=?';
		$sql = $db->prepareQuery($sql, $this->path, $this->noteID);
		$results = $db->select($sql);

		if(!is_array($results) || count($results) == 0) return false;
		$this->id = $results[0]['id'];
	}

	//loads attachment from DB
	//if attachment doesn't exist returns bool|false
	public function fetch($id){
		$db = new Database();
		$sql = 'SELECT * FROM attachments WHERE id=?';
		$sql = $db->prepareQuery($sql, $id);
		$results = $db->select($sql);

		if(!is_array($results) || count($results) == 0) return false;

		$this->id = $results[0]['id'];
		$this->filename = $results[0]['filename'];
		$this->filetypeID = $results[0]['filetype_id'];
		$this->noteID = $results[0]['note_id'];
		$this->path = $results[0]['path'];
	}

	//look in the db for the filtype (file ext).
	//if filetype doesn't exist, return bool|false
	public function lookupFileTypeID($filetype){
		$db = new Database();
		$sql = 'SELECT id FROM filetypes WHERE filetype=?';
		$sql = $db->prepareQuery($sql, $filetype);
		$results = $db->select($sql);
		
		//not in db
		if(!is_array($results) || count($results) == 0) return false; 

		return $results[0]['id'];
	}

	//removes from DB using object's ID
	//removes file from filesystem
	public function deleteAttachment(){
		$path = $this->path;
		delete($path);

		$db = new Database();
		$sql = 'DELETE FROM `attachments` WHERE `id`=?';
		$sql = $db->prepareQuery($sql, $this->id);
		$db->query($sql);
	}

}
?>