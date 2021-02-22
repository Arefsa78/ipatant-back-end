<?php
require_once ("PatentController.php");
require_once("UserController.php");
require_once ("loginController.php");
require_once ("../Model/User.php");
require_once ("IdeaController.php");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $queries);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
$requestedMethod=$_SERVER["REQUEST_METHOD"];

//$user=loginController::sessionBasedLogin();
//if($user==false) {
//    if((($uri[5]=="User" || $uri[5]=="auth") && $requestedMethod=="POST")==false) {
//        //header("Location:");///// url to login page!!!
//        echo "hoghe!";
//        die();
//    }
//}


$controller=null;
if($uri[5]=="User"){
    if(!isset($uri[6])) $controller=new UserController($requestedMethod);
    else $controller=new UserController($requestedMethod,$uri[6]);
}
if($uri[5]=="Idea"){
    if(!isset($queries["type"])){
        $controller=new IdeaController($requestedMethod);
    }
    else{
        if($queries["type"]=="owner") $controller=new IdeaController($requestedMethod,$queries["owner"]);
        elseif($queries["type"]=="idea") $controller=new IdeaController($requestedMethod,null,$queries["idea"]);
    }

}
if ($uri[5]=="Patent"){
    if(!isset($queries["type"])){
        $controller=new PatentController($requestedMethod);
    }
    else{
        if($queries["type"]=="owner") $controller=new PatentController($requestedMethod,$queries["owner"]);
        elseif($queries["type"]=="patent") $controller=new PatentController($requestedMethod,null,$queries["patent"]);
    }
}
if($uri[5]=="auth"){
    $controller=new loginController($requestedMethod);
}
if($uri[5]=="refresh"){
    $controller=new authHandler("GET",null,"zdf");
}
if($uri[5]=="authUser"){
    $controller=new authHandler("GET","User",null);
}
if($uri[5]=="authAdmin"){
    $controller=new authHandler("GET","Admin",null);
}
if($uri[5]=="authAssistant"){
    $controller=new authHandler("GET","Assistant",null);
}
$controller->processRequest();



