<?php
require_once ("../Model/User.php");
require_once("../Model/Patent.php");
header("Content-Type: application/json; charset=UTF-8");

class PatentController  extends Patent {

    private $requestMethod;
    private $patentId;
    private $ownerId;
    private $currentUser;
    public function __construct($requestMethod, $patentId=null, $ownerId=null,$currentUser=null) {
        $this->requestMethod = $requestMethod;
        $this->patentId = $patentId;
        $this->ownerId = $ownerId;
        $this->currentUser=$currentUser;
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if($this->patentId) {
                    $response = $this->getPatent($this->patentId);
                } else if($this->ownerId) {
                    $response = $this->getAllPatentsOfAUser($this->ownerId);
                } else {
                    $response = $this->getAllPatents();
                }
                break;
            case 'POST':
                $response = $this->createPatentFromRequest();
                break;
            case 'PUT':
                $response = $this->updatePatentFromRequest($this->patentId);
                break;
            case 'DELETE':
                $response = $this->deletePatent($this->patentId);
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

    private function getPatent($id) {
        $result = Patent::findPatent($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        if($this->currentUser->getType()=="Student" && $result["ownerId"]!=$this->currentUser->getUserId()){
            return $this->unprocessableEntityResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getAllPatentsOfAUser($id) {
        $result = Patent::findAllPatentsOfAUser($id);
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

    private function getAllPatents() {
        if($this->currentUser->getType()=="Student"){
            return $this->unprocessableEntityResponse();
        }
        if($this->currentUser->getType()=="Student"){
            return $this->unprocessableEntityResponse();
        }
        $result = Patent::findAllPatents();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    private function createPatentFromRequest() {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validatePatentForInsertion($input)) {
            return $this->unprocessableEntityResponse();
        }
        Patent::insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }

    private function updatePatentFromRequest($id) {
        $result = Patent::findPatent($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validatePatentForUpdate($input)) {
            return $this->unprocessableEntityResponse();
        }
        if(array_key_exists ( 'expertId' ,  $input )) {
            Patent::updateExpert($id, $input);
        } else if(array_key_exists ( 'extraResources' ,  $input )) {
            Patent::updateExtraResources($id, $input);
        } else if(array_key_exists ( 'patentStatus' ,  $input )) {
            Patent::updateStatus($id, $input);
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function deletePatent($id) {
        $result = Patent::findPatent($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        if($this->currentUser->getType()=="Student" && $result["ownerId"]!=$this->currentUser->getUserId()){
            return $this->unprocessableEntityResponse();
        }
        Patent::delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validatePatentForInsertion($input) {
        if (!isset($input['patent_name']) || !isset($input['ownerId']) || !isset($input["extraResources"])) {
            return false;
        }
        return true;
    }
    private function validatePatentForUpdate($input){
        if(isset($input["expertId"]) || isset($input["extraResources"]) || isset($input["patentStatus"])) return true;
        return false;
    }

    public static function deleteAllPatentOfUser($id){
        $statement = "
            DELETE FROM PATENTS
            WHERE ownerId = '$id';
        ";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute();
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
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