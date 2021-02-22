<?php
require_once ("../Model/User.php");
require_once("../Model/Idea.php");
require_once ("authHandler.php");
header("Content-Type: application/json; charset=UTF-8");

class IdeaController extends Idea {

    private $requestMethod;
    private $ideaId;
    private $ownerId;
    public function __construct($requestMethod,$ownerId=null,$ideaId=null) {
        $this->requestMethod = $requestMethod;
        $this->ownerId=$ownerId;
        $this->ideaId=$ideaId;
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
                $response = $this->createMessageToClient(404,"not found!","not found!");
                break;
        }
        header($response["header"]);
        echo json_encode($response["body"],JSON_UNESCAPED_UNICODE );
    }

    private function getIdea($id) {
        $result = Idea::findIdea($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $decoded->data->user_id!= $result["ownerId"]){
           return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        return $this->createMessageToClient(200,"ok",$result);
    }

    private function getAllIdeasOfAUser($id) {
        $result = Idea::findAllIdeasOfAUser($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $id!=$decoded->data->user_id){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
         return $this->createMessageToClient(200,"ok",$result);
    }

    private function getAllIdeas() {
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student"){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        $result = Idea::findAllIdeas();
        return $this->createMessageToClient(200,"ok",$result);
    }


    private function createIdeaFromRequest() {
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateIdeaForInsertion($input)) {
            return $this->createMessageToClient(422,"invalid command!","invalid command!");
        }
        Idea::insert($input);
        return $this->createMessageToClient(201,"ok","created!");
    }

    private function updateIdeaFromRequest($id) {
        $result = Idea::findIdea($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $decoded->data->user_id!= $result["ownerId"]){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateIdeaForUpdation($input)) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        if(array_key_exists ( 'expertId' ,  $input )) {
            $this->updateExpert($id, $input);
        } else if(array_key_exists ( 'extraResources' ,  $input )) {
            $this->updateExtraResources($id, $input);
        } else if(array_key_exists ( 'ideaStatus' ,  $input )) {
            $this->updateStatus($id, $input);
        }
        return $this->createMessageToClient(200,"ok","ok");
    }

    private function deleteIdea($id) {
        $result = Idea::findIdea($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $decoded->data->user_id!= $result["ownerId"]){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        Idea::delete($id);
        return $this->createMessageToClient(200,"ok","ok");
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

    private function createMessageToClient($httpCode,$headerMessage,$body){
        $response["header"]="HTTP/1.1 ".$httpCode." ".$headerMessage;
        $response["body"]=$body;
        return $response;
    }
}