<?php
require_once ("../Model/User.php");
require_once("../Model/Idea.php");
header("Content-Type: application/json; charset=UTF-8");

class IdeaController {

    private $requestMethod;
    private $ideaId;
    private $ownerId;
    private $currentUser;
    public function __construct($requestMethod,$ownerId=null,$ideaId=null,$currentUser=null) {
        $this->requestMethod = $requestMethod;
        $this->ownerId=$ownerId;
        $this->ideaId=$ideaId;
        $this->currentUser=$currentUser;
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if($this->ideaId) {
                    $response = $this->getIdea($this->ideaId);
                } else if($this->ownerId) {
                    $response = $this->getAllIdeasOfAUser($this->ownerId);
                } else {
                    $response = $this->getAllIdeas();
                }
                break;
            case 'POST':
                $response = $this->createIdeaFromRequest();
                break;
            case 'PUT':
                $response = $this->updateIdeaFromRequest($this->ideaId);
                break;
            case 'DELETE':

                $response = $this->deleteIdea($this->ideaId);
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

    private function getIdea($id) {
        $result = $this->findIdea($id);
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

    private function getAllIdeasOfAUser($id) {
        $result = $this->findAllIdeasOfAUser($id);
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

    private function getAllIdeas() {
        if($this->currentUser->getType()=="Student"){
            return $this->unprocessableEntityResponse();
        }
        if($this->currentUser->getType()=="Student"){
            return $this->unprocessableEntityResponse();
        }
        $result = $this->findAllIdeas();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    private function createIdeaFromRequest() {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateIdeaForInsertion($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }

    private function updateIdeaFromRequest($id) {
        $result = $this->findIdea($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateIdeaForUpdation($input)) {
            return $this->unprocessableEntityResponse();
        }

        if(array_key_exists ( 'expertId' ,  $input )) {
            $this->updateExpert($id, $input);
        } else if(array_key_exists ( 'extraResources' ,  $input )) {
            $this->updateExtraResources($id, $input);
        } else if(array_key_exists ( 'ideaStatus' ,  $input )) {
            $this->updateStatus($id, $input);
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function deleteIdea($id) {
        $result = $this->findIdea($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        if($this->currentUser->getType()=="Student" && $result["ownerId"]!=$this->currentUser->getUserId()){
            return $this->unprocessableEntityResponse();
        }
        $this->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    public static function deleteAllIdeasOfUser($id){
        $statement = "
            DELETE FROM IDEAS
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

    private function validateIdeaForInsertion($input) {
        if (!isset($input['idea_name']) || !isset($input["ownerId"]) || !isset($input["description"])
        || !isset($input["extraResources"]) ) {
            return false;
        }
        return true;
    }
    private function validateIdeaForUpdation($input){
        if(isset($input["expertId"])|| isset($input["extraResources"])|| isset($input["ideaStatus"])) return true;
        return false;
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