<?php
require_once ("databaseController.php");
require_once ("PatentController.php");
require_once ("IdeaController.php");
require_once ("../Model/User.php");
require_once ("authHandler.php");
header("Content-Type: application/json; charset=UTF-8");

class UserController  extends User{
    private $requestMethod;
    private $userId;
    public function __construct($requestMethod,$userId=null) {
        $this->requestMethod = $requestMethod;
        $this->userId=$userId;
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
                $response = $this->createMessageToClient(422,"invalid command!","invalid command!");
                break;
        }
        header($response['header']);
        echo json_encode($response["body"],JSON_UNESCAPED_UNICODE );
    }

    private function getAllUsers() {
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student"){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        $result = User::findAll();
        return $this->createMessageToClient(200,"ok",$result);
    }



    private function getUser($id) {
        $result = User::findUser($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $decoded->data->user_id!= $result["accountId"]){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        if($decoded->data->enable==0) return $this->createMessageToClient(403,"access denied!","access denied!");
        return $this->createMessageToClient(200,"ok",$result);
    }



    private function createUserFromRequest() {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateUserForRegister($input)) {
            return $this->createMessageToClient(422,"invalid command!","invalid command!");
        }
        $this->insert($input);
        return $this->createMessageToClient(201,"ok","created!");
    }




    private function updateUserFromRequest($id) {
        $result = User::findUser($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $decoded->data->user_id!= $result["accountId"]){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateUserForUpdate($input)) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        User::update($id, $input);
        return $this->createMessageToClient(200,"ok","ok");
    }




    private function deleteUser($id) {
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student"){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        $result = User::findUser($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        if($decoded->data->enable==0) return $this->createMessageToClient(403,"access denied!","access denied!");
        PatentController::deleteAllPatentOfUser($id);
        IdeaController::deleteAllIdeasOfUser($id);
        User::delete($id);
        return $this->createMessageToClient(200,"ok","ok");
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

    private function createMessageToClient($httpCode,$headerMessage,$body){
        $response["header"]="HTTP/1.1 ".$httpCode." ".$headerMessage;
        $response["body"]=$body;
        return $response;
    }


}

