<?php

require_once('Database.php');
require_once('PetsData.php');

class PetsDataSet
{
    protected $_dbHandle, $_dbInstance;

    public function __construct() {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }

    //returns the count of all pets for the pagination system when showing results
    public function countAllPets($searchInput, $sortByStatus)
    {
        $sqlQuery = 'SELECT COUNT(*) as total
                     FROM
                        pets
                            INNER JOIN
                        users ON pets.user_id = users.id';

        $whereClauses = [];
        $params = [];

        //if there is a search filter then add the filter to the sql query
        if (!empty($searchInput)) {
            $whereClauses[] = "(pets.name LIKE ? 
            OR users.username LIKE ? 
            OR pets.species LIKE ? 
            OR pets.breed LIKE ? 
            OR pets.color LIKE ? 
            OR pets.description LIKE ? 
            OR pets.date_reported LIKE ?)";
            $searchParam = "%" . $searchInput . "%";
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

    //returns the count of all pets for the pagination system when showing results for a specific user
    public function countAllPetsByUserID($searchInput, $sortByStatus, $userID)
    {
        $sqlQuery = 'SELECT COUNT(*) as total
                     FROM
                        pets
                            INNER JOIN
                        users ON pets.user_id = users.id';

        $whereClauses = [];
        $params = [];

        //set a default WHERE on the sql query to count all pets from the specific user
        $whereClauses[] = 'pets.user_id = ?';
        $params[] = $userID;

        //if there is a search filter then add the filter to the sql query
        if (!empty($searchInput)) {
            $whereClauses[] = "(pets.name LIKE ? 
            OR pets.species LIKE ? 
            OR pets.breed LIKE ? 
            OR pets.color LIKE ? 
            OR pets.description LIKE ? 
            OR pets.date_reported LIKE ?)";
            $searchParam = "%" . $searchInput . "%";
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

    //returns all pets from the database using the filters from the search form
    public function fetchAllPets($searchInput, $sortByLetters, $sortByStatus, $limit, $offset) {

        // check if the input provided is actually valid to prevent SQL Injection
        if (!($sortByLetters == 'ASC' || $sortByLetters == 'DESC' || $sortByLetters == '')) {
            $sortByLetters = '';
        }
        if (!($sortByStatus == 'found' || $sortByStatus == 'lost' || $sortByStatus == '')) {
            $sortByStatus = '';
        }

        $sqlQuery = '
        SELECT
            pets.id,
            pets.name AS pet_name,
            species,
            breed,
            color,
            photo_url,
            status,
            description,
            date_reported,
            users.username AS owner_name
         FROM
            pets
                INNER JOIN
            users ON pets.user_id = users.id';

        $whereClauses = [];
        $params = [];

        //if there is a search filter then add the filter to the sql query
        if (!empty($searchInput)) {
            $whereClauses[] = "(pets.name LIKE ? 
            OR users.username LIKE ? 
            OR pets.species LIKE ? 
            OR pets.breed LIKE ? 
            OR pets.color LIKE ? 
            OR pets.description LIKE ? 
            OR pets.date_reported LIKE ?)";
            $searchParam = "%" . $searchInput . "%";
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

        //if there is a filter on the results order then add it to the sql query
        if ($sortByLetters == '') {
            $sqlQuery .= " ORDER BY pets.id";
        } else {
            $sqlQuery .= " ORDER BY pets.name " . $sortByLetters;
        }

        //if limit and offset are provided then add it to the sql query for the pagination system
        if ($limit !== null && $offset !== null) {
            $sqlQuery .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        //execute the sql statement with the parameters provided
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute($params);

        //create a new PetsData object for every row and insert it inside the dataSet array to return it
        $dataSet = [];
        while ($row = $statement->fetch()) {
            $dataSet[] = new PetsData($row);
        }

        return $dataSet;
    }

    //returns pets that are from a specific user from the database using the filters from the search form
    public function fetchPetsFromUserID($userID, $searchInput, $sortByLetters, $sortByStatus, $limit, $offset) {

        // check if the input provided is actually valid to prevent SQL Injection
        if (!($sortByLetters == 'ASC' || $sortByLetters == 'DESC' || $sortByLetters == '')) {
            $sortByLetters = '';
        }
        if (!($sortByStatus == 'found' || $sortByStatus == 'lost' || $sortByStatus == '')) {
            $sortByStatus = '';
        }

        $sqlQuery = 'SELECT
                        pets.id,
                        pets.name AS pet_name,
                        species,
                        breed,
                        color,
                        photo_url,
                        status,
                        description,
                        date_reported,
                        users.username AS owner_name
                    FROM pets
                    INNER JOIN users ON pets.user_id = users.id';

        $whereClauses = [];
        $params = [];

        $whereClauses[] = "pets.user_id = ?";
        $params[] = $userID;

        //if there is a search filter then add the filter to the sql query
        if (!empty($searchInput)) {
            $whereClauses[] = "(pets.name LIKE ? 
            OR users.username LIKE ? 
            OR pets.species LIKE ? 
            OR pets.breed LIKE ? 
            OR pets.color LIKE ? 
            OR pets.description LIKE ? 
            OR pets.date_reported LIKE ?)";
            $searchParam = "%" . $searchInput . "%";
            $params = array_merge($params, array_fill(0, 7, $searchParam));
        }

        //if there is a filter on the pet status then add it to the sql query
        if (!empty($sortByStatus)) {
            $whereClauses[] = "pets.status = ?";
            $params[] = $sortByStatus;
        }

        if (!empty($whereClauses)) {
            $sqlQuery .= " WHERE " . implode(" AND ", $whereClauses);
        }

        //if there is a filter on the results order then add it to the sql query
        if ($sortByLetters == '') {
            $sqlQuery .= " ORDER BY pets.id";
        } else {
            $sqlQuery .= " ORDER BY pets.name " . $sortByLetters;
        }

        //add limit and offset to the sql query for the pagination system
        $sqlQuery .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        //execute the sql statement with the parameters provided
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute($params);

        //create a new PetsData object for every row and insert it inside the dataSet array to return it
        $dataSet = [];
        while ($row = $statement->fetch()) {
            $dataSet[] = new PetsData($row);
        }

        return $dataSet;
    }


    //returns pet information only using its ID
    public function fetchPetByPetId($petId) {
        $sqlQuery = 'SELECT
                        pets.id,
                        pets.name AS pet_name,
                        species,
                        breed,
                        color,
                        photo_url,
                        status,
                        description,
                        date_reported,
                        users.username AS owner_name
                     FROM
                        pets
                            INNER JOIN
                        users ON pets.user_id = users.id
                     WHERE pets.id = ?';

        try {

            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->execute([$petId]);

            $row = $statement->fetch();

            if ($row) {
                return new PetsData($row);
            } else {
                return false;
            }

        } catch (PDOException $errorMsg) {
            return $errorMsg;
        }
    }

    //updates pet information for pet owners
    public function updatePetInfo($petInfo) {
        $sqlQuery = 'UPDATE pets
                     SET
                        name = ?,
                        species = ?,
                        breed = ?,
                        color = ?,
                        photo_url = ?,
                        status = ?,
                        description = ?
                     WHERE id = ?';

        try {
            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->execute($petInfo);
            return true;
        } catch (PDOException $errorMsg) {
            return $errorMsg;
        }
    }

    //inserts a new pet inside the database for pet owners
    public function insertNewPet($petInfo) {

        $sqlQuery = 'INSERT INTO pets (
                        name,
                        species,
                        breed,
                        color,
                        photo_url,
                        status,
                        description,
                        date_reported,
                        user_id
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

        try {
            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->execute($petInfo);
            return true;
        } catch (PDOException $errorMsg) {
            return $errorMsg;
        }
    }

    //deletes pet from the database for pet owners
    public function deleteSelectedPet($rowID, $userID) {

        $sqlQuery = 'DELETE FROM pets WHERE id = ? and user_id = ?';

        try {
            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->execute([$rowID, $userID]);
            return true;
        } catch (PDOException $errorMsg) {
            return $errorMsg;
        }
    }

}