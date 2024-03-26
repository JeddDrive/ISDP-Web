<?php
//Employee class to represent this entity from the Bullseye DB
class Employee implements JsonSerializable
{
    //15 private fields
    private $employeeID;
    private $password;
    private $firstName;
    private $lastName;
    private $email;
    private $active;
    private $positionID;
    private $siteID;
    private $locked;
    private $username;
    private $notes;
    private $siteName;
    private $permissionLevel;
    private $loginAttempts;
    private $madeFirstLogin;

    //public constructor
    public function __construct(
        $inEmployeeID,
        $inPassword,
        $inFirstName,
        $inLastName,
        $inEmail,
        $inActive,
        $inPositionID,
        $inSiteID,
        $inLocked,
        $inUsername,
        $inNotes,
        $inSiteName,
        $inPermissionLevel,
        $inLoginAttempts,
        $madeFirstLogin
    ) {
        $this->employeeID = $inEmployeeID;
        $this->password = $inPassword;
        $this->firstName = $inFirstName;
        $this->lastName = $inLastName;
        $this->email = $inEmail;
        $this->active = $inActive;
        $this->positionID = $inPositionID;
        $this->siteID = $inSiteID;
        $this->locked = $inLocked;
        $this->username = $inUsername;
        $this->notes = $inNotes;
        $this->siteName = $inSiteName;
        $this->permissionLevel = $inPermissionLevel;
        $this->loginAttempts = $inLoginAttempts;
        $this->madeFirstLogin = $madeFirstLogin;
    }

    //15 public getter methods
    public function getEmployeeID()
    {
        return $this->employeeID;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function getPositionID()
    {
        return $this->positionID;
    }

    public function getSiteID()
    {
        return $this->siteID;
    }

    public function getLocked()
    {
        return $this->locked;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function getSiteName()
    {
        return $this->siteName;
    }

    public function getPermissionLevel()
    {
        return $this->permissionLevel;
    }

    public function getLoginAttempts()
    {
        return $this->loginAttempts;
    }

    public function getMadeFirstLogin()
    {
        return $this->madeFirstLogin;
    }

    //jsonSerialize() method
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
} //end of class
