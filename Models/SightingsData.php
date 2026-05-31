<?php

class SightingsData implements JsonSerializable
{
    private $id, $petId, $petName, $userId, $comment, $lat, $long, $sightingDate, $petStatus, $petSpecies, $petBreed, $petColor;

    //the following constructor sets the values from the variables above with the results of the sql query executed in SightingsDataSet
    public function __construct($dbRow)
    {
        $this->id = $dbRow['id'];
        $this->petId = $dbRow['pet_id'];
        $this->petName = $dbRow['pet_name'];
        $this->userId = $dbRow['user_id'];
        $this->comment = $dbRow['comment'];
        $this->lat = $dbRow['latitude'];
        $this->long = $dbRow['longitude'];
        $this->sightingDate = $dbRow['timestamp'];
        $this->petStatus = $dbRow['pet_status'];
        $this->petSpecies = $dbRow['pet_species'];
        $this->petBreed = $dbRow['pet_breed'];
        $this->petColor = $dbRow['pet_color'];
    }

    //the following accessor methods return information about a sighting
    public function getId() {
        return $this->id;
    }

    public function getPetId() {
        return $this->petId;
    }

    public function getPetName() {
        return $this->petName;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getComment() {
        return $this->comment;
    }

    public function getLatitude() {
        return $this->lat;
    }

    public function getLongitude() {
        return $this->long;
    }

    public function getDate() {
        return $this->sightingDate;
    }

    public function getPetStatus() {
        return $this->petStatus;
    }

    public function getPetSpecies() {
        return $this->petSpecies;
    }

    public function getPetBreed() {
        return $this->petBreed;
    }

    public function getPetColor() {
        return $this->petColor;
    }

    public function jsonSerialize() {
        return [
            'id'        => $this->id,
            'pet_id'    => $this->petId,
            'pet_name'  => $this->petName,
            'user_id'   => $this->userId,
            'comment'   => $this->comment,
            'latitude'  => $this->lat,
            'longitude' => $this->long,
            'timestamp' => $this->sightingDate,
            'pet_status' => $this->petStatus,
            'pet_species' => $this->petSpecies,
            'pet_breed' => $this->petBreed,
            'pet_color' => $this->petColor
        ];
    }

}