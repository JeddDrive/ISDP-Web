<?php
//Site class to represent this entity from the Bullseye DB
class Site implements JsonSerializable
{
    //13 private fields
    private $siteID;
    private $name;
    private $provinceID;
    private $address;
    private $address2;
    private $city;
    private $country;
    private $postalCode;
    private $phone;
    private $dayOfWeek;
    private $distanceFromWH;
    private $notes;
    private $active;

    //public constructor
    public function __construct(
        $inSiteID,
        $inName,
        $inProvinceID,
        $inAddress,
        $inAddress2,
        $inCity,
        $inCountry,
        $inPostalCode,
        $inPhone,
        $inDayOfWeek,
        $inDistanceFromWH,
        $inNotes,
        $inActive
    ) {
        $this->siteID = $inSiteID;
        $this->name = $inName;
        $this->provinceID = $inProvinceID;
        $this->address = $inAddress;
        $this->address2 = $inAddress2;
        $this->city = $inCity;
        $this->country = $inCountry;
        $this->postalCode = $inPostalCode;
        $this->phone = $inPhone;
        $this->dayOfWeek = $inDayOfWeek;
        $this->distanceFromWH = $inDistanceFromWH;
        $this->notes = $inNotes;
        $this->active = $inActive;
    }

    //13 public getter methods
    public function getSiteID()
    {
        return $this->siteID;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getProvinceID()
    {
        return $this->provinceID;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    public function getDistanceFromWH()
    {
        return $this->distanceFromWH;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function getActive()
    {
        return $this->active;
    }

    //jsonSerialize() method
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
} //end of class
