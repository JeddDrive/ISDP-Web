<?php
//PasswordSalt class to represent this entity from the Bullseye DB
class PasswordSalt implements JsonSerializable
{
    //2 private fields
    private $employeeID;
    private $passwordSalt;

    //public constructor
    public function __construct($inEmployeeID, $inPasswordSalt)
    {
        $this->employeeID = $inEmployeeID;
        $this->passwordSalt = $inPasswordSalt;
    }

    //2 public getter methods
    public function getEmployeeID()
    {
        return $this->employeeID;
    }

    public function getPasswordSalt()
    {
        return $this->passwordSalt;
    }

    //jsonSerialize() method
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
} //end of class
