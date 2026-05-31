<?php

// include login controller to handle sessions and login
require_once('logincontroller.php');

// include models to work with pets and users
require_once('Models/PetsDataSet.php');
require_once('Models/UserDataSet.php');

// create a view object to pass data to the view
$view = new stdClass();

// set the page title
$view->pageTitle = 'Edit Pets';

// create instances of the models
$petsDataSet = new PetsDataSet();
$userDataSet = new UserDataSet();

// check if the user is logged in
if (isset($_SESSION['login'])) {
    // get logged-in user info
    $view->userLoggedIn = $userDataSet->fetchLoggedInUser($_SESSION['login']);
} else {
    // no user logged in
    $view->userLoggedIn = false;
}

// pass the pets dataset to the view
$view->petsDataSet = $petsDataSet;

// fetch the pet info we want to edit
$view->petsDataRow = $petsDataSet->fetchPetByPetId($_GET['petId']);

// assume the user is not allowed by default
$view->isCorrectUser = false;

// check if the pet exists
if ($view->petsDataRow) {

    // check if the logged-in user owns this pet
    $view->isCorrectUser = $view->petsDataRow->getUserId() == $view->userLoggedIn->getUsername();

    // if the user owns the pet then allow editing
    if ($view->isCorrectUser) {

        // handle the edit form submission
        if (isset($_POST["editpetbutton"])) {

            // get new pet info from the form
            $petName = $_POST["pName"];
            $petSpecies = $_POST["pSpecies"];
            $petBreed = $_POST["pBreed"];
            $petColour = $_POST["pColour"];
            $petPhotoUrl = $_POST["pImg"];
            $petDesc = $_POST["pDesc"];
            $petId = $_GET["petId"];

            // set pet status based on form input
            if ($_POST["pStatus"] == "Found") {
                $petStatus = "found";
            } else {
                $petStatus = "lost";
            }

            // if no new photo provided then keep the existing one
            if (empty($petPhotoUrl)) {
                $petPhotoUrl = $_POST["existingImg"];
            }

            // insert all pet info into an array
            $petInfo = [$petName, $petSpecies, $petBreed, $petColour, $petPhotoUrl, $petStatus, $petDesc, $petId];

            // update the pet info in the database
            $isUpdated = $view->petsDataSet->updatePetInfo($petInfo);
            if ($isUpdated) {
                $_SESSION['display_success'] = "The pet information has been successfully updated.";
            } else {
                $_SESSION['display_error'] = "Error: " . $isUpdated;
            }

            // refresh the pet data after update
            $view->petsDataRow = $petsDataSet->fetchPetByPetId($petId);
        }
    }

} else {
    $_SESSION['display_error'] = "You are not allowed to edit this pet.";
}

// load the edit pets view
require_once('Views/editpets.phtml');
