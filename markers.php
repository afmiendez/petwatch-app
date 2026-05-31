<?php
require_once('logincontroller.php');
require_once('Models/SightingsDataSet.php');

$sightingsDataSet = new SightingsDataSet();

// get search and sorting inputs from URL, or default to empty
$searchInput   = isset($_GET['searchinput']) ? $_GET['searchinput'] : '';
$sortByLetters = isset($_GET['sortByLetters']) ? $_GET['sortByLetters'] : '';
$sortByStatus  = isset($_GET['sortByStatus']) ? $_GET['sortByStatus'] : '';

// set up pagination
$recordsPerPage = 8;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// gets every sighting data to add to the map markers
$allMapMarkers = $sightingsDataSet->fetchAllSightings($searchInput, $sortByLetters, $sortByStatus, 2500, 0);

// gets a limited amount of sighting data used to generate the sighting cards for the current page view
$paginatedCards = $sightingsDataSet->fetchAllSightings($searchInput, $sortByLetters, $sortByStatus, $recordsPerPage, $offset);

// get total number of sightings for pagination
$totalRecords = $sightingsDataSet->countAllSightings($searchInput, $sortByStatus);

// outputs the markers and paginated data to be processed by the js client
echo json_encode([
    'mapMarkers' => $allMapMarkers,
    'cardData'   => $paginatedCards,
    'pagination' => [
        'currentPage' => $currentPage,
        'totalPages'  => ceil($totalRecords / $recordsPerPage)
    ]
]);