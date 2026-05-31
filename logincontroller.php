<?php
// start the session to manage login info
session_start();

// include the user model to access user-related methods
require_once('Models/UserDataSet.php');

// check if the login form was submitted
if (isset($_POST["loginbutton"])) {
    // get the username and password from the form
    $username = $_POST["username"];
    $password = $_POST["password"];

    // create an instance of the user model
    // we will use it to check login info
    $userDataSet = new UserDataSet();
    $userSelected = $userDataSet->checkLogin($username, $password);

    // if login is successful
    if ($userSelected) {
        // store the user ID in the session
        $_SESSION["login"] = $userSelected->getUserId();
    } else {
        // if the login failed then show an error message
        $_SESSION['display_error'] = "Invalid email or password.";
    }
}

// check if the logout button was pressed
if (isset($_POST["logoutbutton"])) {
    // remove login info from the session and destroy it
    unset($_SESSION["login"]);
    session_destroy();
}
