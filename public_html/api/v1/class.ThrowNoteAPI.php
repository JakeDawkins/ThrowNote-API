<?php

require_once 'class.API.php'; // parent
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
*           1.1.3 Note attachment   (/notes/id/file)
*               1.1.3.0 Add/update  (POST: /notes/id/file)
*               1.1.3.1 get         (GET: /notes/id/file)
*               1.1.3.2 delete file (DELETE: /notes/id/file)
*   2. Users Endpoint               (/users)
*       2.0 Users Notes             (/users/USERID/notes)
*           2.0.0 Users Notes Get   (GET:       /users/USERID/notes)
*       2.1 Users Name              (/users/USERNAME)
*           2.1.0 Users Name Auth   (POST:      /users/USERNAME)
*       2.2 New user                (POST: /users)
*   3. Helpers
*       3.0 RequestFieldsSubmitted
*       3.1 Validate Photo
*       3.2 Validate Audio
*/

class ThrowNoteAPI extends API
{
    public function __construct($request, $origin) {
        parent::__construct($request);
    }

    /*------------------------  ------------------------
    *
    *                   NOTES ENDPOINT
    *
    *------------------------  ------------------------*/

    //1
    protected function notes(){
        //URI: /api/v1/notes
        if(!is_array($this->args) || count($this->args) == 0){
            return $this->notesCollection();
        } else if(count($this->args) >= 1){ //URI: /api/v1/notes/<ID>/...
            if(!is_numeric($this->args[0])){
                return "error: note id not numeric";  
            } 
            return $this->singleNote();
        } else {
            return "IMPROPER API CALL";
        }
    }

    //1.0 handler for API call to the notes collection
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

    //1.1 handler for API call to a single note
    private function singleNote(){
        if(count($this->args) == 1){ 
            //URL: /api/v1/notes/id
            //note content
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
        } else if(count($this->args) == 2 && $this->args[1] == 'file') {
            //URI: /api/v1/notes/id/file
            //note file handler
            switch($this->method){
                case 'GET':
                    return $this->noteFileGet();
                case 'POST':
                    return $this->noteFilePost();
                case 'DELETE':
                    return $this->noteFileDelete();
                default:
                    return "endpoint does not recognize " . $this->method . " requests";
            }            
        } else {
            //$this->reponse; 
        }
    }

    private function noteFileGet(){
        $noteID = $this->args[0];
        $atts = Attachment::getAttachmentsForNote($noteID);

        if(!is_array($atts) || count($atts) == 0){
            $this->response['message'] = 'error: no attachments for this note';
            $this->response['code'] = 400;
            return;
        }

        //echo image to browser/client
        //TODO -- increase from 1 attachment
        $file = file_get_contents($atts[0]->getPath());
        if($file == false){ //make sure photo loaded
            $this->response['message'] = 'error: broken path';
            $this->response['code'] = 500;
        }
        
        //change for different types
        $filetype = $atts[0]->getFiletypeID();
        if($filetype == $atts[0]->lookupFiletypeID('png')){
            header('content-type: image/png');
            header("HTTP/1.1 200 OK");
            echo $file;            
        } else if ($filetype == $atts[0]->lookupFiletypeID('mp3')){
            header('content-type: audio/mpeg3');
            header("HTTP/1.1 200 OK");
            echo $file;
        }
    }

    //TODO
    private function noteFileDelete(){
        $noteID = $this->args[0];
        Attachment::deleteAttachmentsForNote($noteID);
    }

    /*
    *   handles uploading of a file to a note.
    */  
    private function noteFilePost(){
        if($this->validateAudio()) 
            return $this->noteAudioPost();
        else if ($this->validatePhoto()) 
            return $this->notePhotoPost();
        else {
            return 'error: file upload failed. could not validate filetype'; 
        }
    }

    /*
    *   adds an audio file (mp3) to a note
    */
    private function noteAudioPost(){
        $note = new Note();

        //fetch and check for valid note
        $note->fetch($this->args[0]);
        if($note->getID() == null){
            $this->response['message'] = 'error: failed to load note with id ' . $this->args[0];
            $this->response['code'] = 405;
        }

        //report id
        $noteID = $this->args[0];

        $audio = $this->files['audio'];
        $upload_dir = Path::uploads() . $noteID . '/';

        //make the directory if it doesn't already exist
        if(!file_exists($upload_dir)){
            mkdir($upload_dir, 0755, true);
        }

        //make sure there wasnt an error with the upload
        if($audio['error'] !== UPLOAD_ERR_OK){
            $this->response['message'] = 'error: audio upload error';
            $this->response['code'] = 400;
        }

        //make sure filename is safe
        $name = preg_replace("/[^A-Z0-9._-]/i", "_", $audio['name']);

        //different dir for each note
        $i = 0;
        $parts = pathinfo($name);
        while(file_exists($upload_dir . $name)){
            //myfile-1.png
            $name = $parts['filename'] . '-' . $i . '.' . $parts['extension'];
        }

        //move file from temp directory
        $success = move_uploaded_file($audio['tmp_name'], $upload_dir . $name);
        if(!$success){
            $this->response['message'] = 'error: unable to save file';
            $this->response['code'] = 500;
        }

        //set proper file permissions on new file
        chmod($upload_dir . $name, 0644);

        //add attachment to DB
        $att = new Attachment(); 
        $att->setNoteID($noteID);
        $att->setFilename($name);
        $att->setPath($upload_dir . $name);
        //gets filtypeID, NOT extension
        $filetypeID = $att->lookupFiletypeID($parts['extension']);
        $att->setFiletypeID($filetypeID);
        $att->save();

        return $att->toArray();
    }

    /*
    *   adds a photo file (png) to a note
    */
    private function notePhotoPost(){
        $note = new Note();

        //fetch and check for valid note
        $note->fetch($this->args[0]);
        if($note->getID() == null){
            $this->response['message'] = 'error: failed to load note with id ' . $this->args[0];
            $this->response['code'] = 405;
        }

        //report id
        $noteID = $this->args[0];

        $photo = $this->files['photo'];
        $upload_dir = Path::uploads() . $noteID . '/';

        //make the directory if it doesn't already exist
        if(!file_exists($upload_dir)){
            mkdir($upload_dir, 0755, true);
        }

        //make sure there wasnt an error with the upload
        if($photo['error'] !== UPLOAD_ERR_OK){
            $this->response['message'] = 'error: photo upload error';
            $this->response['code'] = 400;
        }

        //make sure filename is safe
        $name = preg_replace("/[^A-Z0-9._-]/i", "_", $photo['name']);

        //different dir for each note
        $i = 0;
        $parts = pathinfo($name);
        while(file_exists($upload_dir . $name)){
            //myfile-1.png
            $name = $parts['filename'] . '-' . $i . '.' . $parts['extension'];
        }

        //move file from temp directory
        $success = move_uploaded_file($photo['tmp_name'], $upload_dir . $name);
        if(!$success){
            $this->response['message'] = 'error: unable to save file';
            $this->response['code'] = 500;
        }

        //set proper file permissions on new file
        chmod($upload_dir . $name, 0644);

        //add attachment to DB
        $att = new Attachment();
        $att->setNoteID($noteID);
        $att->setFilename($name);
        $att->setPath($upload_dir . $name);
        //gets filtypeID, NOT extension
        $filetypeID = $att->lookupFiletypeID($parts['extension']);
        $att->setFiletypeID($filetypeID);
        $att->save();

        return $att->toArray();
    }

    //---------------- NOTES ENDPOINT METHODS ----------------
    //1.0.0
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
    //1.1.0
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

    //1.1.1
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

    //1.1.2
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

    //2.
    protected function users(){
        //URI: /api/v1/users
        if(!is_array($this->args) || count($this->args) == 0){
            if($this->method == 'POST'){
                if($this->requestFieldsSubmitted(["username","password"])){ 
                    return $this->newUser();
                } else {
                    $this->response['code'] = 400;
                    $this->response['message'] = "error: no username or password submitted";
                }
            } else {
                $this->response['code'] = 405;
                $this->response['message'] = "endpoint does not recognize " . $this->method . " requests";
            }            
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

    //2.0 actions on the collection of notes of a single user
    //args[0] = userid
    public function usersNotes(){
        switch ($this->method) {
            case 'GET':
                return $this->usersNotesGet();
            default:
                return "endpoint does not recognize " . $this->method . " requests";
        }
    }

    //2.0.0 get an array of notes(array form) authored by a user
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

    //2.1 handles a single user by username
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

    //2.1.0
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

    /*
    *   2.2
    *   creates a new user if doesn't exist already.
    */
    private function newUser(){
        $username = $this->request['username'];
        $password = $this->request['password'];
        $displayname = $username;
        $email = $username;

        $user = new User($username,$displayname,$password,$email);

        //Checking this flag tells us whether there were any errors such as possible data duplication occured
        if(!$user->status){
            if($user->username_taken){
                $this->response['message'] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
                $this->response['code'] = 400;
            } 
        } else {
            //Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
            if(!$user->userCakeAddUser()){
                if($user->sql_failure){
                   $this->response['code'] = 400; 
                   $this->response['message'] = lang("SQL_ERROR");
                }  
            }
        }

        //log the user in now
        return $this->usersNameAuth();
    }

    /*------------------------  ------------------------
    *
    *               3. HELPER METHODS
    *
    ------------------------  ------------------------*/

    //3.0 checks if all necessary variables are set
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

    /*
    *   3.1
    *   validates an image to make sure it is valid
    *   helps prevent incorrect uploads/malicious files
    */
    private function validatePhoto(){
        if(!empty($this->files['photo'])){
            $photo = $this->files['photo'];
            
            //verify file is correct type (gif, jpeg, png)
            $filetype = exif_imagetype($photo['tmp_name']);
            $allowed = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
            if(in_array($filetype, $allowed)){
                return true;
            }
        }
        return false;
    }

    /*
    *   3.2
    *   validates an audio file to make sure it is valid
    *   helps prevent incorrect uploads/malicious files
    */
    private function validateAudio(){
        if(!empty($this->files['audio'])){
            $audio = $this->files['audio'];

            //verify file is correct type and filesize (~20MB)
            if($audio['type'] == 'audio/mp3' && $audio['size'] < 20000000 && $audio['error'] == 0){
                return true;
            }
            //name, type, tmp_name, error, size
        }
        return false;
    }
 }
 ?>