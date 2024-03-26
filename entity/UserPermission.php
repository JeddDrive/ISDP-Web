<?php
//UserPermission class to represent this entity from the Bullseye DB
class UserPermission implements JsonSerializable
{
    //2 private fields
    private $employeeID;
    private $permissionIDList;

    //public constructor
    public function __construct(
        $inEmployeeID,
        $inPermissionIDList = []
    ) {
        $this->employeeID = $inEmployeeID;
        $this->permissionIDList = $inPermissionIDList;
    }

    //2 public getter methods
    public function getEmployeeID()
    {
        return $this->employeeID;
    }

    public function getPermissionIDList()
    {
        return $this->permissionIDList;
    }

    //jsonSerialize() method
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
} //end of class
