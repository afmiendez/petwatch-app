<?php

// include login controller to handle session and login checks
require_once('logincontroller.php');

// include the sightings model to work with pet sightings
require_once('Models/SightingsDataSet.php');

// create a view object to pass data to the view
$view = new stdClass();

// set the page title for the view
$view->pageTitle = 'Pet Sightings';

// create instances of the models
$sightingsDataSet = new SightingsDataSet();
$userDataSet = new UserDataSet();

// check if a user is logged in
if (isset($_SESSION['login'])) {
    // fetch logged-in user info
    $view->userLoggedIn = $userDataSet->fetchLoggedInUser($_SESSION['login']);
} else {
    // no user logged in
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

// get total number of sightings for pagination
$totalRecords = $sightingsDataSet->countAllSightings($view->searchInput, $view->sortByStatus);
$totalPages = ceil($totalRecords / $recordsPerPage);
$offset = ($currentPage - 1) * $recordsPerPage;

// fetch sightings for the current page with search and sorting
$view->sightingsDataSet = $sightingsDataSet->fetchAllSightings(
    $view->searchInput,
    $view->sortByLetters,
    $view->sortByStatus,
    $recordsPerPage,
    $offset
);

// pass pagination info to the view
$view->totalPages = $totalPages;
$view->currentPage = $currentPage;

// load the sightings view
require_once('Views/sightings.phtml');
