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
                $response = $this->updateUserFromRequest();
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




    private function updateUserFromRequest() {
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        $result = User::findUser($decoded->data->user_id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        if($decoded->data->type=="Student" && $decoded->data->user_id!= $result["accountId"]){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $x=$this->validateUserForUpdate($input);
        if (is_array($x)) {
            return $x;
        }
        User::update($decoded->data->user_id, $input);
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
        PatentController::deleteAllPatentOfUser($id);
        IdeaController::deleteAllIdeasOfUser($id);
        User::delete($id);
        return $this->createMessageToClient(200,"ok","ok");
    }


    private function validateUserForUpdate($input){
        if(!isset($input['email']) || !isset($input['nationalCode']) || !isset($input['address'])
            || !isset($input['residence']) || !isset($input['schoolName']) ){
            return $this->createMessageToClient(422,"invalid input!","invalid input!");
        }
        if(User::hasUserWithEmail($input["email"])){
            return $this->createMessageToClient(401,"invalid email!","این پست الکترونیکی قبلا ثبت شده است!");
        }
        if(User::hasUserWithNationalCode($input["nationalCode"])){
            return $this->createMessageToClient(401,"invalid email!","این کد ملی قبلا ثبت شده است!");
        }
        return true;
    }


    private function validateUserForRegister($input) {
        if (! isset($input['phoneNum']) || ! isset($input['fullname']) || ! isset($input['password'])) {
            return $this->createMessageToClient(403,"invalid command!","invalid input!");
        }
        if(User::hasUserWithPhoneNumber($input["phoneNum"])){
            return $this->createMessageToClient(401,"invalid phone!","این شماره تلفن قبلا ثبت شده است!");
        }
        return true;
    }

    private function createMessageToClient($httpCode,$headerMessage,$body){
        $response["header"]="HTTP/1.1 ".$httpCode." ".$headerMessage;
        $response["body"]=$body;
        return $response;
    }


}

