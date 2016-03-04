<?php
require_once 'class.API.php';

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
                return "NOTES COLLECTION (POST)";
            default:
                return "endpoint does not recognize " . $this->method . " requests";
        }
    }

    //handler for API call to a single note
    private function singleNote(){
        switch($this->method){
            case 'GET':
                return "SINGLE NOTE (GET)";
            case 'POST':
                return "SINGLE NOTE (POST)";
            case 'DELETE':
                return "SINGLE NOTE (DELETE)";
            default:
                return "endpoint does not recognize " . $this->method . " requests";
        }
    }

    //---------------- NOTES ENDPOINT METHODS ----------------


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