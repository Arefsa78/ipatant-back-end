<?php


class Patent
{
    protected static function findAllPatents() {
        // find all patents of all students
        $statement = "SELECT * FROM PATENTS;";
        try {
            $db=new databaseController();
            $statement= $db->getConnection()->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function findAllPatentsOfAUser($id) {
        // find all patents of a user with ID $id
        $statement = "SELECT * FROM PATENTS WHERE ownerId='$id';";
        try {
            $db=new databaseController();
            $statement= $db->getConnection()->query($statement);
            $statement->execute(array($id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function findPatent($id) {
        // find an specific patent of a user
        $statement = "SELECT * FROM PATENTS WHERE patent_Id='$id';";
        try {
            $db=new databaseController();
            $statement= $db->getConnection()->query($statement);
            $statement->execute();
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function insert(Array $input,$ownerId) {

        // insert a patent to databaseController
        $statement = "INSERT INTO PATENTS (patent_name, ownerId, patentStatus, description, extraResources)
                    VALUES (:patent_name ,:ownerId, :patentStatus, :description, :extraResources);";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array(
                'patent_name'=>$input['patent_name'],
                'ownerId' => $ownerId,
                'patentStatus' => 'START',
                'description' => $input['description'],
                'extraResources' => $input['extraResources'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function updateExpert($id, Array $input) {
        // update patent's data (EXPERT)
        $statement = "UPDATE PATENTS SET 
                     expertId= :expertId
                     WHERE patent_id = '$id';";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array(
                'expertId' => $input['expertId']
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function updateExtraResources($id, Array $input) {
        // update patent's data (EXTRA_RESOURCES)
        $statement = "UPDATE PATENTS SET 
                     extraResources= :extraResources
                     WHERE patent_id = '$id';";
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
        // update patent's data (PATENT_STATUS)
        $statement = "UPDATE PATENTS SET 
                     patentStatus= :patentStatus
                     WHERE patent_id = '$id';";
        try {
            $db=new databaseController();
            $statement = $db->getConnection()->prepare($statement);
            $statement->execute(array(
                'patentStatus' => $input['patentStatus'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function delete($id) {
        // delete a patent
        $statement = "
            DELETE FROM PATENTS
            WHERE patent_id = '$id';
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