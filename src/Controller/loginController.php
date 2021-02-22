<?php
require_once ("databaseController.php");
require_once ("UserController.php");
require_once ("../Model/User.php");
header("Content-Type: application/json; charset=UTF-8");

class loginController
{


    private $requestMethod;
    public function __construct($requestMethod)
    {
        $this->requestMethod=$requestMethod;
    }

    public function processRequest(){
        switch ($this->requestMethod) {

            case "POST":
                $response=$this->login();
                break;
            case "DELETE":
                $response=$this->logout();
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

    private function login(){ //// login normal az tarighe safhe adi!
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if($result=$this->validateData($input)!=true || isset($_COOKIE[session_name()])) die();
        $phone=$input["phoneNum"];
        $password=$input["password"];
        $userController=new UserController(null,null);
        $result=$userController->getUserByPhoneNumber($phone);
        if(count($result)==0) {
          return  $this->notFoundResponse();
        }
        if(password_verify($password,$result["password"])==false){
          return $this->unprocessableEntityResponse();
        }
        $user=new User($result["accountId"],$result["type"],$result["enabled"],1);
        $accountId=$result["accountId"];
        $current_time=time();
        $lifetime=604800;
        session_set_cookie_params($lifetime);
        $sessionId=$userController->saveUserObjectInSession($user);
        $db=new databaseController();
        $statement="DELETE FROM `users_sessions` WHERE `accountId`='$accountId'";
        $db->getConnection()->exec($statement);
        $statement="INSERT INTO `users_sessions` (`sessionId`,`accountId`,`login_time`) VALUES (:sessionId,:accountId,:login_time)";
        $statement=$db->getConnection()->prepare($statement);
        $statement->execute(array(
            'sessionId' => $sessionId,
            'accountId' => $accountId,
            'login_time'=> $current_time
        ));
        $response['status_code_header'] = 'HTTP/1.1 200 ok';
        $response['body'] = null;
        return $response;
    }

    public static function sessionBasedLogin(){ /// inja bayad bad az ye hafte user ru part kone biron va redirect kone safhe login!
        if(isset($_COOKIE[session_name()])==false){
            return false;
        }
        $sessionId=$_COOKIE[session_name()];
        $db=new databaseController();
        $statement=$db->getConnection()->prepare("SELECT `login_time` FROM `users_sessions` WHERE `sessionId`=:sessionId");
        $statement->execute(array(':sessionId' => "$sessionId"));
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if(is_array($result)&&(time()-$result["login_time"])>604800){
            unset($_COOKIE[session_name()]);
            $statement="DELETE FROM `users_sessions` WHERE `sessionId`='$sessionId'";
            $db->getConnection()->exec($statement);
            return false;
        }
        $userController=new UserController(null,null);
        $user=$userController->loadUserFromSession();
        $user->setAuthenticated(1);
        $userController->saveUserObjectInSession($user);
        return $user;
    }

    public static function validateUser($expectedClientType){
        $userController=new UserController(null,null);
        $client=$userController->loadUserFromSession();
        if($expectedClientType!=$client->type) {
            die();
        }
        $result=loginController::sessionBasedLogin();
        if($result!=true){
            die();
        }
    }

    private function validateData($input){
        if(isset($input["phoneNum"])==false){
            return $this->unprocessableEntityResponse();
        }
        if(isset($input["password"])==false){
            return  $this->unprocessableEntityResponse();
        }
        return true;
    }




    private function logout(){
        if(session_status()==PHP_SESSION_NONE) {
            session_start();
        }
        $sessionId=session_id();
        $db=new databaseController();
        $statement="DELETE FROM `users_sessions` WHERE `sessionId`=$sessionId";
        $db->getConnection()->exec($statement);
        session_destroy();
        unset($_COOKIE[session_name()]);
        $response['status_code_header'] = 'HTTP/1.1 200 ok';
        $response['body'] = null;
        return $response;
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