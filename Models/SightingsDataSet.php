<?php

require_once('Database.php');
require_once('SightingsData.php');

class SightingsDataSet
{
    protected $_dbHandle, $_dbInstance;

    public function __construct() {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }

    //returns the count of all sightings for the pagination system when showing results
    public function countAllSightings($searchInput, $sortByStatus)
    {
        $sqlQuery = 'SELECT COUNT(*) as total
                     FROM sightings
                     INNER JOIN pets ON sightings.pet_id = pets.id
                     INNER JOIN users ON sightings.user_id = users.id';

        $whereClauses = [];
        $params = [];

        //if there is a search filter then add the filter to the sql query
        if (!empty($searchInput)) {
            $whereClauses[] = "(pets.name LIKE ? 
            OR users.username LIKE ? 
            OR sightings.comment LIKE ? 
            OR sightings.timestamp LIKE ? 
            OR sightings.latitude LIKE ? 
            OR sightings.longitude LIKE ? 
            OR pets.species LIKE ?  
            OR pets.breed LIKE ? 
            OR pets.color LIKE ?)";
            $searchParam = "%" . $searchInput . "%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        //if there is a filter on the pet status then add it to the sql query
        if (!empty($sortByStatus)) {
            $whereClauses[] = "pets.status = ?";
            $params[] = $sortByStatus;
        }

        if (!empty($whereClauses)) {
            $sqlQuery .= " WHERE " . implode(" AND ", $whereClauses);
        }

        //execute the sql statement with the parameters provided
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute($params);
        return $statement->fetchColumn();
    }

    //returns all sightings from the database using the filters from the search form
    public function fetchAllSightings($searchInput, $sortByLetters, $sortByStatus, $limit, $offset) {

        // check if the input provided is actually valid to prevent SQL Injection
        if (!($sortByLetters == 'ASC' || $sortByLetters == 'DESC' || $sortByLetters == '')) {
            $sortByLetters = '';
        }
        if (!($sortByStatus == 'found' || $sortByStatus == 'lost' || $sortByStatus == '')) {
            $sortByStatus = '';
        }

        $sqlQuery = '
        SELECT
            sightings.id,
            sightings.pet_id,
            pets.name as pet_name,
            users.username as user_id,
            sightings.comment,
            sightings.latitude,
            sightings.longitude,
            sightings.timestamp,
            pets.status as pet_status,
            pets.species as pet_species,
            pets.breed as pet_breed,
            pets.color as pet_color
        FROM sightings
        INNER JOIN pets ON sightings.pet_id = pets.id
        INNER JOIN users ON sightings.user_id = users.id';

        $whereClauses = [];
        $params = [];

        //if there is a search filter then add the filter to the sql query
        if (!empty($searchInput)) {
            $whereClauses[] = "(pets.name LIKE ? 
            OR users.username LIKE ? 
            OR sightings.comment LIKE ? 
            OR sightings.timestamp LIKE ? 
            OR sightings.latitude LIKE ? 
            OR sightings.longitude LIKE ? 
            OR pets.species LIKE ?  
            OR pets.breed LIKE ? 
            OR pets.color LIKE ?)";
            $searchParam = "%" . $searchInput . "%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        //if there is a filter on the pet status then add it to the sql query
        if (!empty($sortByStatus)) {
            $whereClauses[] = "pets.status = ?";
            $params[] = $sortByStatus;
        }

        if (!empty($whereClauses)) {
            $sqlQuery .= " WHERE " . implode(" AND ", $whereClauses);
        }

        if ($sortByLetters === 'ASC') {
            $sqlQuery .= " ORDER BY pets.name ASC";
        } elseif ($sortByLetters === 'DESC') {
            $sqlQuery .= " ORDER BY pets.name DESC";
        } else {
            $sqlQuery .= " ORDER BY sightings.timestamp DESC";
        }

        $sqlQuery .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        //execute the sql statement with the parameters provided
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute($params);

        //create a new SightingData object for every row and insert it inside the dataSet array to return it
        $dataSet = [];
        while ($row = $statement->fetch()) {
            $dataSet[] = new SightingsData($row);
        }

        return $dataSet;
    }

    //inserts a new sighting inside the database
    public function insertNewSighting($sightingInfo) {

        $sqlQuery = 'INSERT INTO sightings (
                        pet_id,
                        user_id,
                        comment,
                        latitude,
                        longitude,
                        timestamp
                    )
                    VALUES (?, ?, ?, ?, ?, ?)';

        try {
            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->execute($sightingInfo);
            return true;
        } catch (PDOException $errorMsg) {
            return $errorMsg;
        }
    }

}