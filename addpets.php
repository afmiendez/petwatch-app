<?php

// include login controller for session and login handling
require_once('logincontroller.php');

// include pets model to work with pet data in the database
require_once('Models/PetsDataSet.php');

// include user model to work with user data
require_once('Models/UserDataSet.php');

// create an object to hold data for the view
$view = new stdClass();

// set the page title
$view->pageTitle = 'My Pets';

// create instances of the models
$petsDataSet = new PetsDataSet();
$userDataSet = new UserDataSet();

// pass the pets dataset to the view
$view->petsDataSet = $petsDataSet;

// check if the user is logged in
if (isset($_SESSION['login'])) {
    // get the logged-in user info
    $view->userLoggedIn = $userDataSet->fetchLoggedInUser($_SESSION['login']);
} else {
    // no user logged in
    $view->userLoggedIn = false;
}

$view->isCorrectUser = false;

//only allow pet owner users to add or delete pets
if ($view->userLoggedIn && $view->userLoggedIn->getRole() == "Owner") {

    // check if the logged-in user is a pet owner
    $view->isCorrectUser = $view->userLoggedIn->getRole() == "Owner";

    //check if the add new pet button was pressed
    if (isset($_POST["addpetsbutton"]) && $_SERVER["REQUEST_METHOD"] === "POST") {

        //get pet info from the form
        $petName = $_POST["pAddName"];
        $petSpecies = $_POST["pAddSpecies"];
        $petBreed = $_POST["pAddBreed"];
        $petColour = $_POST["pAddColour"];
        $petPhotoUrl = $_POST["pAddImg"];
        $petStatus = "lost";
        $petDesc = $_POST["pAddDesc"];
        $petDateReported = date("Y-m-d"); //get the current date
        $petUserId = $view->userLoggedIn->getUserId(); //get the logged-in user id

        // create an array with all the pet info
        $petInfo = [$petName, $petSpecies, $petBreed, $petColour, $petPhotoUrl, $petStatus, $petDesc, $petDateReported, $petUserId];

        // try to add the pet to the database
        $isAdded = $view->petsDataSet->insertNewPet($petInfo);
        if ($isAdded) {
            $_SESSION['display_success'] = "The missing pet has been added.";
        } else {
            $_SESSION['display_error'] = "Error: " . $isAdded;
        }
    }

    // handle deleting a pet
    if (isset($_POST["deletepetbutton"]) && $_SERVER["REQUEST_METHOD"] === "POST") {

        //get the id of the pet to delete
        $rowID = $_POST["rowID"];

        //try to delete the pet and check if the user able to perform this action
        $isDeleted = $view->petsDataSet->deleteSelectedPet($rowID, $view->userLoggedIn->getUserId());
        if ($isDeleted) {
            $_SESSION['display_success'] = "The pet has been deleted.";
        } else {
            $_SESSION['display_error'] = "Error: " . $isDeleted;
        }
    }

    //get search and sorting options from url
    $view->searchInput = isset($_GET['searchinput']) ? $_GET['searchinput'] : '';
    $view->sortByLetters = isset($_GET['sortByLetters']) ? $_GET['sortByLetters'] : '';
    $view->sortByStatus  = isset($_GET['sortByStatus']) ? $_GET['sortByStatus'] : '';

    //pagination setup
    $recordsPerPage = 8;
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
    if ($currentPage < 1) $currentPage = 1;

    //get total number of pets for the user
    $totalRecords = $petsDataSet->countAllPetsByUserID($view->searchInput, $view->sortByStatus, $view->userLoggedIn->getUserId());
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $offset = ($currentPage - 1) * $recordsPerPage;

    //fetch pets for the current page
    $view->petsDataRow = $petsDataSet->fetchPetsFromUserID(
        $view->userLoggedIn->getUserId(),
        $view->searchInput,
        $view->sortByLetters,
        $view->sortByStatus,
        $recordsPerPage,
        $offset
    );

    //pass pagination info to the view
    $view->totalPages = $totalPages;
    $view->currentPage = $currentPage;

} else {
    $_SESSION['display_error'] = "You are not authorized to be on this page.";
}

//load the view file
require_once('Views/addpets.phtml');
