<?php
require_once ("databaseController.php");
require_once ("PatentController.php");
require_once ("IdeaController.php");
require_once ("../Model/User.php");
header("Content-Type: application/json; charset=UTF-8");

class UserController  extends User{
    private $requestMethod;
    private $userId;
    private $currentUser;
    public function __construct($requestMethod,$userId=null,$currentUser=null) {
        $this->requestMethod = $requestMethod;
        $this->userId=$userId;
        $this->currentUser=$currentUser;
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if($this->userId) {
                    $response = $this->getUser($this->userId);
                } else {
                    $response = $this->getAllUsers();
                }
                break;
            case 'POST':
                $response = $this->createUserFromRequest();
                break;
            case 'PUT':
                $response = $this->updateUserFromRequest($this->userId);
                break;
            case 'DELETE':
                $response = $this->deleteUser($this->userId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllUsers() {
        if($this->currentUser->getType()=="Student"){
            return $this->unprocessableEntityResponse();
        }
        if($this->currentUser->getType()=="Student"){
            return $this->unprocessableEntityResponse();
        }
        $result = User::findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }



    private function getUser($id) {
        $result = User::findUser($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        if($this->currentUser->getType()=="Student" && $id!=$this->currentUser->getUserId()){
            return $this->unprocessableEntityResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }



    private function createUserFromRequest() {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateUserForRegister($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }




    private function updateUserFromRequest($id) {
        $result = User::findUser($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateUserForUpdate($input)) {
            return $this->unprocessableEntityResponse();
        }
        User::update($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        $user=$this->loadUserFromSession();
        $user->setEnabled(true);
        $this->saveUserObjectInSession($user);
        return $response;
    }




    private function deleteUser($id) {
        if($this->currentUser->getType()=="Student"){
            return $this->unprocessableEntityResponse();
        }
        $result = User::findUser($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        PatentController::deleteAllPatentOfUser($id);
        IdeaController::deleteAllIdeasOfUser($id);
        User::delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }


    private function validateUserForUpdate($input){
        if(!isset($input['email']) || !isset($input['nationalCode']) || !isset($input['address'])
        || !isset($input['residence']) || !isset($input['schoolName']) ){
            return false;
        }
        return true;
    }


    private function validateUserForRegister($input) {
        if (! isset($input['phoneNum'])) {
            return false;
        }
        if (! isset($input['password'])) {
            return false;
        }
        if (! isset($input['fullname'])) {
            return false;
        }

        return true;
    }

    public function saveUserObjectInSession($userObj){ /// at the end of each page!!!!
        if(session_status()==PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["userObj"]=serialize($userObj);
        return session_id();
    }

    public function loadUserFromSession(){
        if(session_status()==PHP_SESSION_NONE) {
            session_start();
        }
        $userObj=unserialize($_SESSION["userObj"]);
        return $userObj;
    }


    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse() {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}

