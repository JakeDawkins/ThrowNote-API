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
                if(!$this->requestFieldsSubmitted(["text","created"]))
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
                return "SINGLE NOTE (POST)";
            case 'DELETE':
                return "SINGLE NOTE (DELETE)";
            default:
                return "endpoint does not recognize " . $this->method . " requests";
        }
    }

    //---------------- NOTES ENDPOINT METHODS ----------------
    private function newNote(){
        $note = new Note();

        //required
        $note->setText($this->request['text']);
        $note->setCreated($this->request['created']);
        
        //optional
        if(isset($this->request['id']) && !empty($this->request['id'])) 
            $note->setID($this->request['id']);
        if(isset($this->request['updated']) && !empty($this->request['updated'])) 
            $note->setUpdated($this->request['updated']);

        $note->save();
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

    //------------------------ USER ENDPOINT ------------------------


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