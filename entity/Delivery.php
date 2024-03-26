<?php
//Delivery class to represent this entity from the Bullseye DB
class Delivery implements JsonSerializable
{
    //4 private fields
    private $deliveryID;
    private $distanceCost;
    private $vehicleType;
    private $notes;

    //public constructor
    public function __construct($inDeliveryID, $inDistanceCost, $inVehicleType, $inNotes)
    {
        $this->deliveryID = $inDeliveryID;
        $this->distanceCost = $inDistanceCost;
        $this->vehicleType = $inVehicleType;
        $this->notes = $inNotes;
    }

    //4 public getter methods
    public function getDeliveryID()
    {
        return $this->deliveryID;
    }

    public function getDistanceCost()
    {
        return $this->distanceCost;
    }

    public function getVehicleType()
    {
        return $this->vehicleType;
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
