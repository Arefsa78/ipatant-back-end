<?php
//require_once ("PatentController.php");
//require_once("UserController.php");
//require_once ("loginController.php");
//require_once ("../Model/User.php");
//require_once ("IdeaController.php");
//header("Access-Control-Allow-Origin: *");
//header("Content-Type: application/json; charset=UTF-8");
//header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
//header("Access-Control-Max-Age: 3600");
//header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
//
//
//
//parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $queries);
//$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//$uri = explode( '/', $uri );
//$requestedMethod=$_SERVER["REQUEST_METHOD"];
//



require_once("PatentController.php");
require_once("UserController.php");
require_once("loginController.php");
require_once("../Model/User.php");
require_once("IdeaController.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $queries);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$requestedMethod = $_SERVER["REQUEST_METHOD"];

print_r($uri );

print_r($requestedMethod);
//if($uri[5]=="User"){
//    $controller=null;
//    if(!isset($uri[6])) $controller=new UserController($requestedMethod);
//    else $controller=new UserController($requestedMethod,$uri[6]);
//    $controller->processRequest();
//}
//if($uri[5]=="Idea"){
//    if(!isset($queries["type"])){
//        if(isset($uri[6]))
//        $controller=new IdeaController($requestedMethod,null,$uri[6]);
//        else $controller=new IdeaController($requestedMethod);
//
//    }
//    else{
//        if($queries["type"]=="owner" && isset($uri[6]) ) $controller=new IdeaController($requestedMethod,$uri[6]);
//        elseif($queries["type"]=="idea" && isset($uri[6])) $controller=new IdeaController($requestedMethod,null,$uri[6]);
//    }
//
//}
//if ($uri[5]=="Patent"){
//    if(!isset($queries["type"])){
//        if(isset($uri[6]))$controller=new PatentController($requestedMethod,$uri[6]);
//        else $controller=new PatentController($requestedMethod);
//    }
//    else{
//        if($queries["type"]=="owner" && isset($uri[6])) $controller=new PatentController($requestedMethod,null,$uri[6]);
//        elseif($queries["type"]=="patent" && isset($uri[6])) $controller=new PatentController($requestedMethod,$uri[6],null);
//    }
//}
//if($uri[5]=="auth"){
//    $controller=new loginController($requestedMethod);
//}
//if($uri[5]=="refresh"){
//    $controller=new authHandler("GET",null,"zdf");
//}
//if($uri[5]=="authUser"){
//    $controller=new authHandler("GET","User",null);
//}
//if($uri[5]=="authAdmin"){
//    $controller=new authHandler("GET","Admin",null);
//}
//if($uri[5]=="authAssistant"){
//    $controller=new authHandler("GET","Assistant",null);
//}
//if(is_null($controller)) die("unproccessable request!");
//$controller->processRequest();





///last version
//$user = loginController::sessionBasedLogin();
//if ($user == false) {
//    if ((($uri[5] == "User" || $uri[5] == "auth") && $requestedMethod == "POST") == false) {
//        //header("Location:");///// url to login page!!!
//        echo "hoghe!";
//        die();
//    }
//}
//
//
//if ($uri[5] == "User") {
//    $userController = null;
//    if ($requestedMethod == "POST") {
//        $userController = new UserController("POST");
//    } elseif ($requestedMethod == "GET") {
//        if (isset($uri[6])) {
//            $userController = new UserController("GET", $uri[6], $user);
//        } else {
//            $userController = new UserController("GET", null, $user);
//        }
//
//    } elseif ($requestedMethod == "PUT") {
//        $userController = new UserController("PUT", $user->getUserId());
//    } elseif ($requestedMethod == "DELETE" && isset($uri[6])) {
//        $userController = new UserController("DELETE", $uri[6], $user);
//    } else {
//        die();////
//    }
//    $userController->processRequest();
//} elseif ($uri[5] == "Idea" && $user->getEnabled() == 1) {
//    $ideaController = null;
//    if ($requestedMethod == "POST") {
//
//        $ideaController = new IdeaController("POST");
//
//    } elseif ($requestedMethod == "GET") {
//        if (isset($queries["type"]) && isset($uri[6]) && $queries["type"] == "owner") {
//            $ideaController = new IdeaController("GET", $uri[6], null, $user);
//
//        } elseif (isset($queries["type"]) && isset($uri[6]) && $queries["type"] == "idea") {
//            $ideaController = new IdeaController("GET", null, $uri[6], $user);
//        } else {
//            $ideaController = new IdeaController("GET", null, null, $user);
//        }
//
//    } elseif ($requestedMethod == "PUT" && isset($uri[6])) {
//        $ideaController = new IdeaController("PUT", null, $uri[6]);
//    } elseif ($requestedMethod == "DELETE" && isset($uri[6])) {
//        $ideaController = new IdeaController("DELETE", null, $uri[6], $user);
//    } else {
//        die();
//    }
//    $ideaController->processRequest();
//
//} elseif ($uri[5] == "Patent" && $user->getEnabled() == 1) {
//    $patentController = null;
//    if ($requestedMethod == "POST") {
//        $patentController = new PatentController("POST");
//
//    } elseif ($requestedMethod == "GET") {
//
//        if (isset($queries["type"]) && isset($uri[6]) && $queries["type"] == "owner") {
//            $patentController = new PatentController("GET", null, $uri[6], $user);
//
//        } elseif (isset($queries["type"]) && isset($uri[6]) && $queries["type"] == "patent") {
//            $patentController = new PatentController("GET", $uri[6], null, $user);
//        } else {
//            $patentController = new PatentController("GET", null, null, $user);
//        }
//
//    } elseif ($requestedMethod == "PUT" && isset($uri[6])) {
//        $patentController = new PatentController("PUT", $uri[6], null);
//
//    } elseif ($requestedMethod == "DELETE" && isset($uri[6])) {
//        $patentController = new PatentController("DELETE", $uri[6], null, $user);
//    } else {
//        die();
//    }
//    $patentController->processRequest();
//
//} elseif ($uri[5] == "auth") {
//    $loginController = null;
//    if ($requestedMethod == "POST") {
//        $loginController = new loginController("POST");
//    } elseif ($requestedMethod = "DELETE") {
//        $loginController = new loginController("DELETE");
//    } else {
//        die();
//    }
//
//    $loginController->processRequest();
//
//} else {
//    die();
//}

