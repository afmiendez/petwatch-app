<?php

require_once('logincontroller.php');

// include models to work with pets and users
require_once('Models/PetsDataSet.php');
require_once('Models/UserDataSet.php');

// create a view object to pass data to the view
$view = new stdClass();

// set the page title
$view->pageTitle = 'Missing pets';

// create instances of the models
$petsDataSet = new PetsDataSet();
$userDataSet = new UserDataSet();

// check if a user is logged in
if (isset($_SESSION['login'])) {
    $view->userLoggedIn = $userDataSet->fetchLoggedInUser($_SESSION['login']);
} else {
    $view->userLoggedIn = false;
}

// get search and sorting inputs from URL or default to empty
$view->searchInput = isset($_GET['searchinput']) ? $_GET['searchinput'] : '';
$view->sortByLetters = isset($_GET['sortByLetters']) ? $_GET['sortByLetters'] : '';
$view->sortByStatus  = isset($_GET['sortByStatus']) ? $_GET['sortByStatus'] : '';

// set up pagination
$recordsPerPage = 8;
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}

// get total number of pets for pagination
$totalRecords = $petsDataSet->countAllPets($view->searchInput, $view->sortByStatus);
$totalPages = ceil($totalRecords / $recordsPerPage);
$offset = ($currentPage - 1) * $recordsPerPage;

// fetch the pets for the current page with search and sorting
$view->petsDataSet = $petsDataSet->fetchAllPets(
    $view->searchInput,
    $view->sortByLetters,
    $view->sortByStatus,
    $recordsPerPage,
    $offset
);

// pass pagination info to the view
$view->totalPages = $totalPages;
$view->currentPage = $currentPage;

// load the missing pets view
require_once('Views/missingpets.phtml');
