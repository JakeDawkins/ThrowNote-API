<?php
require_once("config.php");

class Note {
	public $id;
	private $text;
	private $created;
	private $updated;
	private $tags;		//string array of tag NAMES, not ID
	private $owner;

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

	public function getOwner(){
		return $this->owner;
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

	function toArray(){
		return array(
			'id' => $this->id,
			'text' => $this->text,
			'created' => $this->created,
			'updated' => $this->updated,
			'owner' => $this->owner
			);
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

	public function setOwner($owner){
		$this->owner = $owner;
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
	*	fetchByUser(id) -- gets array of notes for user with id
	* 	fetch(id) -- pulls all a note's data from the DB
	*	save() -- saves a note to the DB (adds if necessary) using the following...
	*		addNewNote() -- adds a note to DB and sets local id from DB
	*		updateNote() -- changes the text and updated timestamp in DB
	*		removeAllTagAssociations() -- removes all associated tags from tags_notes DB
	*		addDBTags() -- adds back tag associations in tags_notes
	*			getTagID($name) -- look up tag id from DB using tag $name
	*	delete() -- deletes a note from the DB
	*/  


	/*
	*	gets all notes associated to a user and returns array of initialized 
	*	notes
	*
	*	@param: int|User ID
	*	@ret: Array(Note)| notes of a user
	*	@TODO: use user id instead of 1, limit num of notes
	*/
	static function fetchByUser($id){
		$db = new Database();
		$sql = "SELECT `id` FROM `notes` WHERE `owner`=?";
		$sql = $db->prepareQuery($sql, $id);

		$results = $db->select($sql);
		if(is_array($results)){
			$notes = array();

			foreach($results as $result){
				$newNote = new Note();
				$newNote->fetch($result['id']);
				$notes[] = $newNote;
			}
			return $notes;
		}
		return false;
	}

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
		$this->owner = $results[0]["owner"];

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

	/*
	*	Total preparation for saving a note including tagging
	*	@req: note text field set
	*/
	function prepareAndSaveNote(){
		$this->setTagsFromText();
		$this->save();
	}

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

		if(!isset($this->created)){
			$now = date("Y-m-d H:i:s");
			$this->setCreated($now);	
		}

		$sql = "INSERT INTO `notes`(`text`, `created`, `owner`) VALUES (?, ?, ?)"; 
		$sql = $db->prepareQuery($sql, $this->text, $this->created, $this->owner);

		$db->query($sql);

		//new note in DB. Fetch the ID
		$sql = "SELECT `id` FROM `notes` WHERE `text`=? AND `created`=? AND `owner`=?";
		$sql = $db->prepareQuery($sql, $this->text, $this->created, $this->owner);

		$result = $db->select($sql);

		$this->id = $result[0]['id'];
	}

	//update all non-tag info for the note in the DB
	function updateNote(){
		$db = new Database();

		if(!isset($this->updated)){
			$now = date("Y-m-d H:i:s");
			$this->setUpdated($now);
		}
		
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

	//adds back tag associations in tags_notes
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

	/*
	*	Deletes the current note from DB
	* 	@ret bool | true if successful, false otherwise
	*/
	function delete(){
		if(isset($this->id)){
			$db = new Database();
			$sql = "DELETE FROM notes WHERE id=?";
			$sql = $db->prepareQuery($sql, $this->id);
			$db->query($sql);
			return true;
		} else return false;	
	}

	//------------------------ HELPER METHODS ------------------------

	/*
	*	Given a note string, pulls out valid tags and returns the tags array	
	*	@param: String|string of text to extract tags from
	*	@ret: Array(String)|Array of tags (including #)
	*	@TODO: find cleaner way to trim space off tags2 items (better regex)
	*/
	function findTags($text){
		//match the hashtags
		//to make url-fragments (e.g. www.example.com#header) safe, check for beginning of string or space before tag
		preg_match_all("/(^#\w+)/", $text, $tags); 
		preg_match_all("/([ ])(#\w+)/", $text, $tags2);

		//trim spaces off tags2 items
		if(is_array($tags2) && is_array($tags2[0])){	
			foreach ($tags2[0] as $key => $value) {
				$tags2[0][$key] = trim($value);
			}
		}

		return array_merge($tags[0], $tags2[0]);
	}

	/*
	* 	uses the note's set text to find the tags. Sets the local $tags array
	*	@side-effects: sets local note->tags variable to array of tag strings
	*/
	function setTagsFromText(){
		if(!empty($this->text)){
			$this->tags = $this->findTags($this->text);	
		}	
	}

	/*
	*	adds anchor to URLs and hashtags for easy redirection/search
	*/
	function linkifyFromText(){
		$text = $this->text;

		//match links
		//matches www, http://, https://, http://www, https://www, ftp:// ftps://
		$text = preg_replace("/(^|[\n ])([\w]*?)([\w]*?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $text);  
		$text = preg_replace("/(^|[\n ])([\w]*?)((www)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", $text);
		$text = preg_replace("/(^|[\n ])([\w]*?)((ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"ftp://$3\" >$3</a>", $text);  
		$text = preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $text);  

		//match the hashtags
		//to make url-fragments (e.g. www.example.com#header) safe, check for beginning of string or space before tag
		$text = preg_replace("/([ ])(#\w+)/", " <a href=\"$2\">$2</a>", $text); //check space before
		$text = preg_replace("/(^#\w+)/", "<a href=\"$1\">$1</a>", $text); //check at beginning of string
		$this->text = $text;
	}
}


?>