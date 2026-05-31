<?php

date_default_timezone_set('Europe/London');

// include login controller to handle sessions and login checks
require_once('logincontroller.php');

// include the models to work with pets, users, and sightings useful methods
require_once('Models/PetsDataSet.php');
require_once('Models/UserDataSet.php');
require_once('Models/SightingsDataSet.php');


// create a view object to pass data to the view files
$view = new stdClass();

// set the page title
$view->pageTitle = 'Create Sightings';

// create instances of the models
$petsDataSet = new PetsDataSet();
$userDataSet = new UserDataSet();
$sightingsDataSet = new sightingsDataSet();

// check if the user is logged in
if (isset($_SESSION['login'])) {
    $view->userLoggedIn = $userDataSet->fetchLoggedInUser($_SESSION['login']);
} else {
    $view->userLoggedIn = false;
}

// only proceed if a user is logged in
if ($view->userLoggedIn) {

    $view->petsDataSet = $petsDataSet->fetchAllPets('', '', '', null, null);

    // handle adding a new sighting
    if (isset($_POST["addsightingsbutton"]) && $_SERVER["REQUEST_METHOD"] === "POST") {

        // get all sighting info from the form
        $sightingPetId = $_POST["sightingPet"];
        $sightingPetOwner = $view->userLoggedIn->getUserId();
        $sightingComment = $_POST["sightingComment"];
        $sightingLat = $_POST["sightingLat"];
        $sightingLong = $_POST["sightingLong"];
        $petDateReported = date("Y-m-d H:i:s");

        $sightingInfo = [$sightingPetId, $sightingPetOwner, $sightingComment, $sightingLat, $sightingLong, $petDateReported];

        // try to insert the new sighting into the database
        $isAdded = $sightingsDataSet->insertNewSighting($sightingInfo);

        if ($isAdded === true) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo "success";
                exit;
            }
            $_SESSION['display_success'] = "The sighting has been successfully added.";
        } else {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo "Database Error: " . $isAdded->getMessage();
                exit;
            }
            $_SESSION['display_error'] = "Error: " . $isAdded;
        }

    }
} else {
    $_SESSION['display_error'] = "You are not authorized to be on this page.";
}

// load the view file
require_once('Views/createsightings.phtml');