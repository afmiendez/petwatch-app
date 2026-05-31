<?php

// include login controller to handle session and login logic
require_once("logincontroller.php");

// include user model to get user info from the database
require_once("Models/UserDataSet.php");

// create a view object to pass data to the view
$view = new stdClass();

// set the page title for the view
$view->pageTitle = 'Login';

// create an instance of the user model
$userDataSet = new UserDataSet();

// check if the user is already logged in
if (isset($_SESSION['login'])) {
    // get the logged-in user's info
    $view->userLoggedIn = $userDataSet->fetchLoggedInUser($_SESSION['login']);
} else {
    // no user logged in
    $view->userLoggedIn = false;
}

// load the login page view
require_once('Views/loginpage.phtml');
