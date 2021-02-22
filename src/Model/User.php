<?php
require_once("../Controller/databaseController.php");

class User {
    private $userId;
    private $enabled;
    private $type;
    public function __construct($userId,$type,$enabled) {
        $this->userId=$userId;
        $this->type=$type;
        $this->enabled=$enabled;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function getEnabled(){
        return $this->enabled;
    }


    protected static function findUser($id) {
        // find an specific id
        $statement = "SELECT * FROM USERS WHERE accountId=?";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function update($id, Array $input) {
        // update user's data (for completing account information)
        $statement = "UPDATE USERS SET 
                     email= :email,
                     nationalCode= :nationalCode,
                     address= :address,
                     residence= :residence,
                     schoolName= :schoolName,
                     enabled= :enabled
                     WHERE accountId = '$id';";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array(
                'email' => $input['email'],
                'nationalCode' => $input['nationalCode'],
                'address' => $input['address'],
                'residence' => $input['residence'],
                'schoolName' => $input['schoolName'],
                'enabled' => 1
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function insert(Array $input) {

        // insert a user to databaseController
        $db=new databaseController();
        $statement = "INSERT INTO USERS (phoneNum, password,fullname)
                    VALUES (:phoneNum, :password,:fullname);";
        try {
            $input["password"]=password_hash($db->makeSafe($input["password"]),PASSWORD_DEFAULT);
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array(
                'phoneNum' => $input['phoneNum'],
                'password' => $input['password'],
                'fullname' => $input['fullname']
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function findAll() {
        // find all users
        $statement = "SELECT * FROM USERS;";
        try {
            $db= new databaseController();
            $statement= $db->getConnection()->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public static function getUserByPhoneNumber($phoneNumber){
        $statement = "SELECT `accountId`,`password`,`enabled`,`type` FROM `users` WHERE `phoneNum`=$phoneNumber";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute();
            $result=$statement->fetch(PDO::FETCH_ASSOC);
            if(count($result)==0) return null;
            return ($result);
        }catch (\PDOException $e){
            exit($e->getMessage());
        }
    }

    protected static function delete($id) {
        // delete a user
        $statement = "
            DELETE FROM USERS
            WHERE accountId = '$id';
        ";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->exec($statement);
            //$statement->execute(array('accountId' => $id));
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}