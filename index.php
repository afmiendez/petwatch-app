<?php

// include the login controller to handle session and login stuff
require_once("logincontroller.php");

// include the user model to get user info from the database
require_once("Models/UserDataSet.php");

// create a view object to pass data to the view
$view = new stdClass();

//set the page title for the view
$view->pageTitle = 'Homepage';

//create an instance of the user model
$userDataSet = new UserDataSet();

// check if a user is logged in
if (isset($_SESSION['login'])) {
    // fetch info of the logged-in user
    $view->userLoggedIn = $userDataSet->fetchLoggedInUser($_SESSION['login']);
} else {
    // no user logged in
    $view->userLoggedIn = false;
}

// load the homepage view
require_once('Views/index.phtml');
