<?php
require_once ("../Model/User.php");
require_once("../Model/Patent.php");
require_once ("authHandler.php");
header("Content-Type: application/json; charset=UTF-8");

class PatentController  extends Patent {

    private $requestMethod;
    private $patentId;
    private $ownerId;
    public function __construct($requestMethod, $patentId=null, $ownerId=null) {
        $this->requestMethod = $requestMethod;
        $this->patentId = $patentId;
        $this->ownerId = $ownerId;
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
                $response = $this->createMessageToClient(404,"not found!","not found!");
                break;
        }
        header($response['header']);
        echo json_encode($response["body"],JSON_UNESCAPED_UNICODE );
    }

    private function getPatent($id) {
        $result = Patent::findPatent($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }

        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!"|| $decoded=="access denied!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $result["ownerId"]!=$decoded->data->user_id){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        if(!User::isEnabled($decoded->data->user_id)) return $this->createMessageToClient(403,"access denied!","access denied!");
        return $this->createMessageToClient(200,"ok",$result);
    }

    private function getAllPatentsOfAUser($id) {
        $result = Patent::findAllPatentsOfAUser($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!"|| $decoded=="access denied!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $id!=$decoded->data->user_id){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        if(!User::isEnabled($decoded->data->user_id)) return $this->createMessageToClient(403,"access denied!","access denied!");
        return $this->createMessageToClient(200,"ok",$result);
    }

    private function getAllPatents() {
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!"|| $decoded=="access denied!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student"){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        $result = Patent::findAllPatents();
        return $this->createMessageToClient(200,"ok",$result);
    }


    private function createPatentFromRequest() {
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!" || $decoded=="access denied!") return $this->createMessageToClient("403","access denied!",$decoded);
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $x= $this->validatePatentForInsertion($input);
        if (is_array($x)) {
            return $x;
        }
        if(!User::isEnabled($decoded->data->user_id)) return $this->createMessageToClient(403,"access denied!","access denied!");
        Patent::insert($input,$decoded->data->user_id);
        return $this->createMessageToClient(200,"ok","ok");
    }

    private function updatePatentFromRequest($id) {
        $result = Patent::findPatent($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!"|| $decoded=="access denied!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $decoded->data->user_id!= $result["ownerId"]){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        if(!User::isEnabled($decoded->data->user_id)) return $this->createMessageToClient(403,"access denied!","access denied!");
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $x=$this->validatePatentForUpdate($input);
        if (is_array($x)) {
            return $x;
        }
        if(array_key_exists ( 'expertId' ,  $input )) {
            Patent::updateExpert($id, $input);
        } else if(array_key_exists ( 'extraResources' ,  $input )) {
            Patent::updateExtraResources($id, $input);
        } else if(array_key_exists ( 'patentStatus' ,  $input )) {
            Patent::updateStatus($id, $input);
        }
        return $this->createMessageToClient(200,"ok","ok");
    }

    private function deletePatent($id) {
        $result = Patent::findPatent($id);
        if (! $result) {
            return $this->createMessageToClient(404,"not found!","not found!");
        }
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!"|| $decoded=="access denied!") return $this->createMessageToClient("403","access denied!",$decoded);
        if($decoded->data->type=="Student" && $decoded->data->user_id!= $result["ownerId"]){
            return $this->createMessageToClient(403,"access denied!","access denied!");
        }
        if(!User::isEnabled($decoded->data->user_id)) return $this->createMessageToClient(403,"access denied!","access denied!");
        Patent::delete($id);
        return $this->createMessageToClient(200,"ok","ok");
    }

    private function validatePatentForInsertion($input) {
        if (!isset($input['patent_name'])  || !isset($input["extraResources"]) || !isset($input["description"])) {
            return $this->createMessageToClient(422,"invalid command!","invalid command!");
        }
        return true;
    }
    private function validatePatentForUpdate($input){
        if(isset($input["expertId"]) || isset($input["extraResources"]) || isset($input["patentStatus"])) return true;
        return $this->createMessageToClient(422,"invalid command!","invalid command!");
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

    private function createMessageToClient($httpCode,$headerMessage,$body){
        $response["header"]="HTTP/1.1 ".$httpCode." ".$headerMessage;
        $response["body"]=$body;
        return $response;
    }

}