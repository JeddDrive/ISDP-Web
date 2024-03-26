<?php
//Vehicle class to represent this entity from the Bullseye DB
class Vehicle implements JsonSerializable
{
    //5 private fields
    private $vehicleType;
    private $maxWeight;
    private $hourlyTruckCost;
    private $costPerKm;
    private $notes;

    //public constructor
    public function __construct($inVehicleType, $inMaxWeight, $inHourlyTruckCost, $inCostPerKm, $inNotes)
    {
        $this->vehicleType = $inVehicleType;
        $this->maxWeight = $inMaxWeight;
        $this->hourlyTruckCost = $inHourlyTruckCost;
        $this->costPerKm = $inCostPerKm;
        $this->notes = $inNotes;
    }

    //5 public getter methods
    public function getVehicleType()
    {
        return $this->vehicleType;
    }

    public function getMaxWeight()
    {
        return $this->maxWeight;
    }

    public function getHourlyTruckCost()
    {
        return $this->hourlyTruckCost;
    }

    public function getCostPerKm()
    {
        return $this->costPerKm;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    //jsonSerialize() method
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
} //end of class
