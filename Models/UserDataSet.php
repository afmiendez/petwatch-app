<?php

require_once('Database.php');
require_once('UserData.php');

class UserDataSet
{

    protected $dbHandle;

    public function __construct() {
        $dbInstance = Database::getInstance();
        $this->dbHandle = $dbInstance->getdbConnection();
    }

    //checks if the username and password provided match with the username and hashed password inside the database
    public function checkLogin($userName, $userPass) {

        $sqlQuery = "SELECT * FROM users WHERE username = ?";
        $statement = $this->dbHandle->prepare($sqlQuery);
        $statement->execute([$userName]); //execute the sql statement with the parameters provided

        $userInfo = $statement->fetch(PDO::FETCH_ASSOC); // get the user returned from the sql query

        if ($userInfo) { //check if its valid

            $userDataItem = new UserData($userInfo); //create new UserData object with the user's information

            //verifies if the password provided matches with the hashed password inside the database
            //if so, then return the user information
            if (password_verify($userPass, $userDataItem->getPassword())) {

                return $userDataItem;

            } else {

                return false;

            }
        } else {
            return false;
        }
    }


    //returns information about the current logged-in user
    public function fetchLoggedInUser($userID) {

        $sqlQuery = "SELECT * FROM users WHERE id = ?";
        $statement = $this->dbHandle->prepare($sqlQuery);
        $statement->execute([$userID]);

        $row = $statement->fetch();
        if ($row) {
            return new UserData($row);
        } else {
            return null;
        }
    }
}