<?php
header("Content-Type: application/json; charset=UTF-8");

class databaseController
{
    private $connection;

    function __construct()
    {
       $servername= "localHost";
       $username="alirezaeiji151379";
       $password="alirezaeiji";
       $dbname="energy_Project";
       $this->connection = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
       $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->connection=null;
    }

    public function getConnection(){
        return $this->connection;
    }

    public function makeSafe($input){
        return stripcslashes(htmlspecialchars(trim($input)));
    }


}