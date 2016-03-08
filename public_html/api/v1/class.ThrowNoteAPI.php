<?php
require_once 'class.API.php';
require_once dirname(dirname(dirname(__FILE__))) . '/models/config.php';

class ThrowNoteAPI extends API
{
    public function __construct($request, $origin) {
        parent::__construct($request);
    }

    //------------------------ NOTES ENDPOINT ------------------------
    protected function notes(){
        //URI: /api/v1/notes
        if(!is_array($this->args) || count($this->args) == 0){
            return $this->notesCollection();
        } else if(count($this->args) == 1){ //URI: /api/v1/notes/<ID>
            if(!is_numeric($this->args[0])){
                return "error: note id not numeric";  
            } 
            return $this->singleNote();
        } else {
            return "IMPROPER API CALL";
        }
    }

    //handler for API call to the notes collection
    private function notesCollection(){
        switch($this->method){
            case 'POST':
                //NEW NOTE (or updated if ID given)
                //make sure user gave enough info for note
                if(!$this->requestFieldsSubmitted(["text","created","owner"]))
                    return "error: missing note information";
                return $this->newNote();
            default:
                return "endpoint does not recognize " . $this->method . " requests";
        }
    }

    //handler for API call to a single note
    private function singleNote(){
        switch($this->method){
            case 'GET':
                return $this->getNote();
            case 'POST':
                //make sure user gave enough info for note
                if(!$this->requestFieldsSubmitted(["text","updated","owner"]))
                    return "error: missing note information";
                return $this->updateNote();
            case 'DELETE':
                return $this->deleteNote();
            default:
                return "endpoint does not recognize " . $this->method . " requests";
        }
    }

    //------------------------ USERS ENDPOINT ------------------------
    protected function users(){
        //URI: /api/v1/users
        if(!is_array($this->args) || count($this->args) == 0){
            return "error: no active endpoint methods with 0 arguments";
        } else if(count($this->args) == 2){ //URI: /api/v1/users/<ARG>/<ARG>
            if(is_numeric($this->args[0]) && $this->args[1] == 'notes'){
                //URI: /api/v1/users/<ID>/notes
                return $this->usersNotes();
            } else return "error: unknown API call to users endpoint";
            return $this->singleNote();
        } else {
            return "IMPROPER API CALL";
        }
    }

    //---------------- NOTES ENDPOINT METHODS ----------------
    private function newNote(){
        $note = new Note();

        //required
        $note->setText($this->request['text']);
        $note->setCreated($this->request['created']);
        $note->setOwner($this->request['owner']);
        
        //optional
        if(isset($this->request['id']) && !empty($this->request['id'])) 
            $note->setID($this->request['id']);
        if(isset($this->request['updated']) && !empty($this->request['updated'])) 
            $note->setUpdated($this->request['updated']);

        $note->prepareAndSaveNote();
        return $note->toArray();
    }

    //---------------- NOTE ENDPOINT METHODS ----------------
    public function getNote(){
        if(is_numeric($this->args[0])){
            $id = $this->args[0];
            $note = new Note();
            $note->fetch($id);
            if(empty($note->getText())){
                 return "error: note lookup failed (may not exist)";   
            }
            return $note->toArray();
        } else {
            return "error: note id is not an int";
        }
    }

    public function updateNote(){
        if(is_numeric($this->args[0])){
            $id = $this->args[0];
            $note = new Note();
            $note->fetch($id);
            if(empty($note->getText())){
                 return "error: note lookup failed (may not exist)";   
            }
            if($note->getOwner() != $this->request['owner']){
                return "error: note and request owner mismatch";
            }
            $note->setText($this->request['text']);
            $note->setUpdated($this->request['updated']);
            $note->prepareAndSaveNote();

            return $note->toArray();
        } else {
            return "error: note id is not an int";
        }
    }

    public function deleteNote(){
        if(is_numeric($this->args[0])){
            $id = $this->args[0];
            $note = new Note();
            $note->fetch($id);
            if(empty($note->getText())){
                 return "error: note lookup failed (may not exist)";   
            }

            if($note->delete() == true){
                return "note deleted successfully";    
            } else {
                return "error: issue deleting note";
            }
        } else {
            return "error: note id is not an int";
        }
    }

    //------------------------ USERS ENDPOINT METHODS ------------------------

    //actions on the collection of notes of a single user
    //args[0] = userid
    public function usersNotes(){
        switch ($this->method) {
            case 'GET':
                return $this->usersNotesGet();
            default:
                return "endpoint does not recognize " . $this->method . " requests";
        }
    }

    //get an array of notes(array form) authored by a user
    public function usersNotesGet(){
        $notes = Note::fetchByUser($this->args[0]);
        $notesArray = array();
        if(is_array($notes)){
            foreach($notes as $note){
                $notesArray[] = $note->toArray();
            }    
        }
        return $notesArray;
    }


    //------------------------ HELPER METHODS ------------------------

    //checks if all necessary variables are set
    //$vars is an array
    private function requestFieldsSubmitted($vars){
        if(is_array($vars)){
            foreach($vars as $var){
                if(!isset($this->request[$var])) return false;
            }
            return true;
        }
        return false;
    }
    
 }
 ?>