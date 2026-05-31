<?php
require_once('Models/PetsDataSet.php');
$petsDataSet = new PetsDataSet();

// get search and sorting inputs from URL or default to empty
$searchInput = $_GET['searchinput'] ?? '';
$sortByLetters = $_GET['sortByLetters'] ?? '';
$sortByStatus  = $_GET['sortByStatus'] ?? '';

// set up pagination
$currentPage = (int)($_GET['page'] ?? 1);
$recordsPerPage = 8;
$offset = ($currentPage - 1) * $recordsPerPage;

// get total number of pets for pagination
$totalRecords = $petsDataSet->countAllPets($searchInput, $sortByStatus);
$pets = $petsDataSet->fetchAllPets($searchInput, $sortByLetters, $sortByStatus, $recordsPerPage, $offset);

// loop through the dataset and organise the pet objects into an array for json encoding
$petData = [];
foreach ($pets as $pet) {
    $petData[] = [
        'id' => $pet->getId(),
        'name' => $pet->getName(),
        'status' => $pet->getStatus(),
        'species' => $pet->getSpecies(),
        'breed' => $pet->getBreed(),
        'color' => $pet->getColor(),
        'photo' => $pet->getPhotoUrl(),
        'date' => $pet->getDateReported(),
        'description' => $pet->getDescription(),
        'user_id' => $pet->getUserId()
    ];
}

// outputs the processed pet data and pagination details as a json string to be handled by the js client
echo json_encode([
    'pets' => $petData,
    'pagination' => [
        'currentPage' => $currentPage,
        'totalPages' => ceil($totalRecords / $recordsPerPage)
    ]
]);