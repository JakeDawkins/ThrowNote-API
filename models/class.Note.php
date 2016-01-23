<?php
require_once("config.php");

class Note {
	private $id;
	private $text;
	private $created;
	private $updated;
	private $tags;		//string array of tag NAMES, not ID

	function __construct (){
		$tags = array();
	}

	//------------------------ GETTERS ------------------------
	public function getID(){
		return $this->id;
	}

	public function getText(){
		return $this->text;
	}

	public function getCreated(){
		return $this->created;
	}

	public function getUpdated(){
		return $this->updated;
	}

	// @ret: array of strings.
	public function getTags(){
		return $this->tags;
	}

	function toHTMLString(){
		$ret = 
			"ID: " . $this->id . "<br />" .
			"TEXT: " . $this->text . "<br />" .
			"CREATED: " . $this->created . "<br />" .
			"UPDATED: " . $this->updated . "<br />" . 
			"TAGS: [ ";
		if(is_array($this->tags)){
			foreach($this->tags as $tag){
				$ret .= $tag . " ";
			}
		}
		$ret .= "]<br />";
		return $ret;
	}

	//------------------------ SETTERS ------------------------
	public function setID($id){
		$this->id = $id;
	}

	public function setText($text){
		$this->text = $text;
	}

	public function setCreated($created){
		$this->created = $created;
	}

	public function setUpdated($updated){
		$this->updated = $updated;
	}

	// @param: array of strings. just sets, doesn't add
	public function setTags($tags){
		$this->tags = $tags;
	}

	// @param: String $tag to add to $tags array
	public function addTag($tag){
		if(!is_array($this->tags)){
			$this->tags = array();
		} 
		if(!in_array($tag, $this->tags)){
			$this->tags[] = $tag;	
		}
	}

	// @req: $this->tags must be array
	// @param: String $tag to remove
	public function removeTag($tag){
		if(in_array($tag, $this->tags)){
			$index = array_search($tag, $this->tags);
			unset($this->tags[$index]);
		}
	}

	//------------------------ DB METHODS ------------------------
	/*	
	* 	fetch(id) -- pulls all a note's data from the DB
	*	save() -- saves a note to the DB (adds if necessary) using the following...
	*		addNewNote() -- adds a note to DB and sets local id from DB
	*		updateNote() -- changes the text and updated timestamp in DB
	*		removeAllTagAssociations() -- removes all associated tags from tags_notes DB
	*		addDBTags() -- adds back tag associations in tags_notes
	*			getTagID($name) -- look up tag id from DB using tag $name
	*	delete() -- deletes a note from the DB
	*/  

	// fetches a note from the DB and sets up the local variables
	function fetch($id){
		$db = new Database();
		$sql = "SELECT * FROM `notes` WHERE `id`= ?";
		$sql = $db->prepareQuery($sql, $id);
		
		$results = $db->select($sql);

		$this->id = $id;
		$this->text = $results[0]["text"];
		$this->created = $results[0]["created"];
		$this->updated = $results[0]["updated"];

		//get all associated tags
		$sql = "SELECT `name` FROM `tags_notes` INNER JOIN `tags` ON tags_notes.tag = tags.id WHERE `note` = ?"; 
		$sql = $db->prepareQuery($sql, $id);

		$results = $db->select($sql);

		if(is_array($results)){
			foreach($results as $result){
				$this->addTag($result['name']);
			}
		}	
	}//fetch

	function save(){
		$db = new Database();

		if(!isset($this->id)){ //this is a new note. no previous ID
			$this->addNewNote();
		} else { //old note. remove old tag associations
			$this->updateNote();
			$this->removeAllTagAssociations();
		}

		$this->addDBTags();
	}//save

	//handles adding note to the DB and setting local id
	// TODO -- fix default user
	function addNewNote(){
		$db = new Database();

		$now = date("Y-m-d H:i:s");
		$this->setCreated($now);

		$sql = "INSERT INTO `notes`(`text`, `created`, `owner`) VALUES (?, ?, ?)"; 
		$sql = $db->prepareQuery($sql, $this->text, $this->created, 1);

		$db->query($sql);

		//new note in DB. Fetch the ID
		$sql = "SELECT `id` FROM `notes` WHERE `text`=? AND `created`=? AND `owner`=?";
		$sql = $db->prepareQuery($sql, $this->text, $this->created, 1);

		$result = $db->select($sql);

		$this->id = $result[0]['id'];
	}

	//update all non-tag info for the note in the DB
	function updateNote(){
		$db = new Database();

		$now = date("Y-m-d H:i:s");
		$this->setUpdated($now);

		$sql = "UPDATE `notes` SET `text`=?, `updated`=? WHERE `id`=?";
		$sql = $db->prepareQuery($sql, $this->text, $this->updated, $this->id);

		$db->query($sql);
	}

	//remove all tags from the DB associated with this note
	function removeAllTagAssociations(){
		$db = new Database();

		$sql = "DELETE FROM `tags_notes` WHERE `note`=?";
		$sql = $db->prepareQuery($sql, $this->id);	

		$db->query($sql);
	}

	/*
	*	use to look up tag ids using name.
	*	@param: String| tag name to search db for
	*	@ret: int| success: tag id, fail: -1
	*/
	function getTagID($name){
		$db = new Database();

		$sql = "SELECT `id` FROM `tags` WHERE `name`=?";
		$sql = $db->prepareQuery($sql, $name);
		
		$result = $db->select($sql);
		
		if(!empty($result)){
			return $result[0]['id'];	
		} else {
			return -1;
		}
	}

	function addDBTags(){
		$db = new Database();

		if(is_array($this->tags)){
			foreach($this->tags as $tag){
				$tag_id = $this->getTagID($tag);

				if($tag_id == -1) {
				    //id not found. need to add to DB
					$sql = "INSERT INTO `tags`(`name`) VALUES(?)";
					$sql = $db->prepareQuery($sql, $tag);
					
					$db->query($sql);

					//new tag should be added to DB. can get an id now
					$tag_id = $this->getTagID($tag);
				}

				$sql = "INSERT INTO `tags_notes`(`note`,`tag`) VALUES(?,?)";
			    $sql = $db->prepareQuery($sql, $this->id, $tag_id);
			    $db->query($sql);
			}//foreach			
		}//if
	}//addDBTags
}


?>