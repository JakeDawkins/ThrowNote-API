<?php
require_once 'class.API.php';
require_once dirname(dirname(dirname(__FILE__))) . '/models/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/models/config-uc.php';

/*
*   ---- INDEX ----
*   1. Notes Endpoint
*       1.0 Notes Collection        (/notes)
*           1.0.0 New Note          (POST:      /notes)
*       1.1 Single Note             (/notes/id)
*           1.1.0 Get Note          (GET:       /notes/id)
*           1.1.1 Update Note       (POST:      /notes/id)
*           1.1.2 Delete Note       (DELETE:    /notes/id)
*    2. Users Endpoint              (/users)
*       2.0 Users Name              (/users/USERNAME)
            2.0.0 Users Name Auth   (POST:      /users/USERNAME)
*       2.1 Users Notes             (/users/USERID/notes)
*           2.1.0 Users Notes Get   (GET:       /users/USERID/notes)
*/

class ThrowNoteAPI extends API
{
    public function __construct($request, $origin) {
        parent::__construct($request);
    }

    /*------------------------  ------------------------
    *
    *               NOTES ENDPOINT
    *
    ------------------------  ------------------------*/

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

    /*------------------------  ------------------------
    *
    *               USERS ENDPOINT
    *
    ------------------------  ------------------------*/

    protected function users(){
        //URI: /api/v1/users
        if(!is_array($this->args) || count($this->args) == 0){
            return "error: no active endpoint methods with 0 arguments";
        } else if(count($this->args) == 1){ //URI: /api/v1/users/<ARG>
            if(!is_numeric($this->args[0])){ //username, not ID
                return $this->usersName();
            }
        } else if(count($this->args) == 2){ //URI: /api/v1/users/<ARG>/<ARG>
            if(is_numeric($this->args[0]) && $this->args[1] == 'notes'){
                //URI: /api/v1/users/<ID>/notes
                return $this->usersNotes();
            } else return "error: unknown API call to users endpoint";
        } else {
            return "IMPROPER API CALL";
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

    //handles a single user by username
    public function usersName(){
        switch($this->method){
            case 'POST':
                //make sure user gave enough info for user
                if(!$this->requestFieldsSubmitted(["username","password"]))
                    return "error: missing user information";
                return $this->usersNameAuth();
            default:
                return "endpoint does not recognize " . $this->method . " requests";
        }
    }

    public function usersNameAuth(){
        $username = $this->request['username'];
        $password = $this->request['password'];

        if(!usernameExists($username)){
            return array(
                'login' => 'false',
                'message' => 'error: username/password invalid'
                );
        } else {
            $userdetails = fetchUserDetails($username);

            //See if the user's account is activated
            if($userdetails["active"]==0) {
                return array(
                    'login' => 'false',
                    'message' => 'error: account is inactive'
                );
            } else {
                //Hash the password and use the salt from the database to compare the password.
                $entered_pass = generateHash($password,$userdetails["password"]);
                
                if($entered_pass != $userdetails["password"]) {
                    //Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
                    return array(
                        'login' => 'false',
                        'message' => 'error: username/password invalid'
                    );
                } else {
                    //Passwords match! we're good to go'
                    
                    //info to pass back to api
                    $user = array(
                        'login' => 'true',
                        'message' => 'Hello, ' . $userdetails["display_name"] . '!',
                        'id' => $userdetails["id"],
                        'username' => $userdetails["user_name"],
                        'displayname' => $userdetails["display_name"]
                        );

                    //set up logged in user var for updating sign in time
                    $loggedInUser = new loggedInUser();
                    $loggedInUser->user_id = $userdetails["id"];
                    
                    //Update last sign in
                    $loggedInUser->updateLastSignIn();
                    return $user;
                }
            }
        }//end else
    }

    /*------------------------  ------------------------
    *
    *               HELPER METHODS
    *
    ------------------------  ------------------------*/

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