<?php


class Idea
{
    protected static function findAllIdeas() {
        // find all ideas of all students
        $statement = "SELECT * FROM `ideas`;";
        try {
            $db=new databaseController();
            $statement= $db->getConnection()->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function findAllIdeasOfAUser($id) {
        // find all ideas of a user with ID $id
        $statement = "SELECT * FROM `ideas` WHERE `ownerId`='$id';";
        try {
            $db=new databaseController();
            $statement= $db->getConnection()->query($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function findIdea($id) {
        // find an specific idea of a user
        $statement = "SELECT * FROM `ideas` WHERE `idea_id`=?;";
        try {
            $db=new databaseController();
            $statement= $db->getConnection()->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function insert(Array $input,$ownerId) {

        // insert an idea to databaseController
        $statement = "INSERT INTO `ideas` (`idea_name`,`ownerId`,`ideaStatus`,`description`, `extraResources`)
                    VALUES (:idea_name, :ownerId,:ideaStatus ,:description, :extraResources);";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array(
                'idea_name'=>$input['idea_name'],
                'ownerId' => $ownerId,
                'ideaStatus' => 'START',
                'description' => $input['description'],
                'extraResources' => $input['extraResources'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function updateExpert($id, Array $input) {
        // update idea's data (EXPERT)
        $statement = "UPDATE `ideas` SET 
                      `expertId`= :expertId 
                      WHERE idea_id = $id;";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array(
                'expertId' => $input['expertId'],
            ));
            // return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function updateExtraResources($id, Array $input) {
        // update idea's data (EXTRA_RESOURCES)
        $statement = "UPDATE `ideas` SET 
                     `extraResources`= :extraResources
                     WHERE idea_id = '$id';";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array(
                'extraResources' => $input['extraResources'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function updateStatus($id, Array $input) {
        // update idea's data (IDEA_STATUS)
        $statement = "UPDATE `ideas` SET 
                     `ideaStatus`= :ideaStatus
                      WHERE idea_id = '$id';";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array(
                'ideaStatus' => $input['ideaStatus'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function  delete($id) {
        // delete a idea
        $statement = "
            DELETE FROM `ideas`
            WHERE `idea_id` = '$id';
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


}