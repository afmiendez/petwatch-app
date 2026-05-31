<?php

class PetsData
{
    protected $id, $name, $user_id, $species, $breed, $color, $photo_url, $status, $description, $date_reported;

    //the following constructor sets the values from the variables above with the results of the sql query executed in PetsDataSet
    public function __construct($dbRow)
    {
        $this->id = $dbRow['id'];
        $this->name = $dbRow['pet_name'];
        $this->species = $dbRow['species'];
        $this->breed = $dbRow['breed'];
        $this->color = $dbRow['color'];
        $this->photo_url = $dbRow['photo_url'];
        $this->status = $dbRow['status'];
        $this->description = $dbRow['description'];
        $this->date_reported = $dbRow['date_reported'];
        $this->user_id = $dbRow['owner_name'];
    }

    //the following accessor methods return information about a pet
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getSpecies() {
        return $this->species;
    }

    public function getBreed() {
        return $this->breed;
    }

    public function getColor() {
        return $this->color;
    }

    public function getPhotoUrl() {
        return $this->photo_url;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDateReported() {
        return $this->date_reported;
    }

    public function getUserId() {
        return $this->user_id;
    }
}