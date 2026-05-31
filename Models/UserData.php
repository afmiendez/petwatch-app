<?php

class UserData
{
    protected $id, $username, $email, $password, $role;

    //the following constructor sets the values from the variables above with the results of the sql query executed in UserDataSet
    public function __construct($dbRow)
    {
        $this->id = $dbRow['id'];
        $this->username = $dbRow['username'];
        $this->email = $dbRow['email'];
        $this->password = $dbRow['password_hash'];
        $this->role = $dbRow['role'];
    }

    //the following accessor methods return information about a user
    public function getUserId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getRole() {
        return $this->role;
    }
}