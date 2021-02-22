<?php
require_once ("databaseController.php");
require_once ("UserController.php");
require_once ("../Model/User.php");
require_once ("authHandler.php");
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
        header($response['header']);
        echo json_encode($response["body"],JSON_UNESCAPED_UNICODE );

    }

//    private function login(){ //// login normal az tarighe safhe adi!
//        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
//        if($result=$this->validateData($input)!=true || isset($_COOKIE[session_name()])) die();
//        $phone=$input["phoneNum"];
//        $password=$input["password"];
//        $userController=new UserController(null,null);
//        $result=$userController->getUserByPhoneNumber($phone);
//        if(count($result)==0) {
//          return  $this->notFoundResponse();
//        }
//        if(password_verify($password,$result["password"])==false){
//          return $this->unprocessableEntityResponse();
//        }
//        $user=new User($result["accountId"],$result["type"],$result["enabled"],1);
//        $accountId=$result["accountId"];
//        $current_time=time();
//        $lifetime=604800;
//        session_set_cookie_params($lifetime);
//        $sessionId=$userController->saveUserObjectInSession($user);
//        $db=new databaseController();
//        $statement="DELETE FROM `users_sessions` WHERE `accountId`='$accountId'";
//        $db->getConnection()->exec($statement);
//        $statement="INSERT INTO `users_sessions` (`sessionId`,`accountId`,`login_time`) VALUES (:sessionId,:accountId,:login_time)";
//        $statement=$db->getConnection()->prepare($statement);
//        $statement->execute(array(
//            'sessionId' => $sessionId,
//            'accountId' => $accountId,
//            'login_time'=> $current_time
//        ));
//        $response['status_code_header'] = 'HTTP/1.1 200 ok';
//        $response['body'] = null;
//        return $response;
//    }

    private  function login(){
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if($this->validateLoginInput($input)==false){
            return $this->createMessageToClient(403,"Forbidden","شماره تلفن یا پسورد اشتباه است!");
        }
        $username=$input["phoneNum"];
        $password=$input["password"];
        $result=User::getUserByPhoneNumber($username);
        if(is_array($result)==false){
            return $this->createMessageToClient(404,"Not Found","نام کاربری یا رمز عبور اشتباه است");
        }
        if(password_verify($password,$result["password"])==false){
            return  $this->createMessageToClient(403,"Forbidden","نام کاربری یا رمز عبور اشتباه است");
        }
        $user=new User($result["accountId"],$result["type"],$result["enabled"]);
        $token=authHandler::generateJwtAccessTokenForUser($user);
        $refresh=authHandler::generateJwtRefreshTokenForUser($user);
        setcookie("refreshToken",$refresh,time()+604800,null,null,false,true);/// needs to be changed!
        $arr["accessToken"]=$token;
        $arr["type"]=$user->getType();
        return  $this->createMessageToClient(201,"created",$arr);
    }


//    public static function sessionBasedLogin(){ /// inja bayad bad az ye hafte user ru part kone biron va redirect kone safhe login!
//        if(isset($_COOKIE[session_name()])==false){
//            return false;
//        }
//        $sessionId=$_COOKIE[session_name()];
//        $db=new databaseController();
//        $statement=$db->getConnection()->prepare("SELECT `login_time` FROM `users_sessions` WHERE `sessionId`=:sessionId");
//        $statement->execute(array(':sessionId' => "$sessionId"));
//        $result = $statement->fetch(PDO::FETCH_ASSOC);
//        if(is_array($result)&&(time()-$result["login_time"])>604800){
//            unset($_COOKIE[session_name()]);
//            $statement="DELETE FROM `users_sessions` WHERE `sessionId`='$sessionId'";
//            $db->getConnection()->exec($statement);
//            return false;
//        }
//        $userController=new UserController(null,null);
//        $user=$userController->loadUserFromSession();
//        $user->setAuthenticated(1);
//        $userController->saveUserObjectInSession($user);
//        return $user;
//    }

//    public static function validateUser($expectedClientType){
//        $userController=new UserController(null,null);
//        $client=$userController->loadUserFromSession();
//        if($expectedClientType!=$client->type) {
//            die();
//        }
//        $result=loginController::sessionBasedLogin();
//        if($result!=true){
//            die();
//        }
//    }

    private function validateLoginInput($input){
        if(isset($input["phoneNum"])==false || empty($input["phoneNum"])){
            return  $this->createMessageToClient(403,"access denied!","access denied!");
        }
        if(isset($input["password"])==false || empty($input["password"])){
            return  $this->createMessageToClient(403,"access denied!","access denied!");
        }
        return true;
    }

    private function logout(){
        $token=authHandler::getBearerToken();
        $decoded=authHandler::validateToken();
        if($decoded=="invalid token!" || $decoded=="expired token!") return $this->createMessageToClient("403","access denied!",$decoded);
        $refreshToken=$_COOKIE["refreshToken"];
        $db=new authDB();
        $sql="INSERT INTO `black_list` (`access_id`,`expires_at`) VALUES ('$token','$decoded->expire')";
        $db->getConnection()->query($sql);
        $sql="DELETE FROM `refreshtokens` WHERE `refresh_id`= '$refreshToken'";
        $db->getConnection()->query($sql);
        unset($_COOKIE["refreshToken"]);
        return $this->createMessageToClient("200","ok","ok");
    }


//    private function logout(){
//        if(session_status()==PHP_SESSION_NONE) {
//            session_start();
//        }
//        $sessionId=session_id();
//        $db=new databaseController();
//        $statement="DELETE FROM `users_sessions` WHERE `sessionId`=$sessionId";
//        $db->getConnection()->exec($statement);
//        session_destroy();
//        unset($_COOKIE[session_name()]);
//        $response['status_code_header'] = 'HTTP/1.1 200 ok';
//        $response['body'] = null;
//        return $response;
//    }

    private function createMessageToClient($httpCode,$headerMessage,$body){
        $response["header"]="HTTP/1.1 ".$httpCode." ".$headerMessage;
        $response["body"]=$body;
        return $response;
    }


}