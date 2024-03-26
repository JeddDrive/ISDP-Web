<?php
//TxnItems class to represent this entity from the Bullseye DB
class TxnItems implements JsonSerializable
{
    //8 private fields
    private $txnID;
    private $itemID;
    private $quantity;
    private $notes;
    private $name;
    private $description;
    private $caseSize;
    private $weight;

    //public constructor
    public function __construct(
        $inTxnID,
        $inItemID,
        $inQuantity,
        $inNotes,
        $inName,
        $inDescription,
        $inCaseSize,
        $inWeight
    ) {
        $this->txnID = $inTxnID;
        $this->itemID = $inItemID;
        $this->quantity = $inQuantity;
        $this->notes = $inNotes;
        $this->name = $inName;
        $this->description = $inDescription;
        $this->caseSize = $inCaseSize;
        $this->weight = $inWeight;
    }

    //8 public getter methods
    public function getTxnID()
    {
        return $this->txnID;
    }

    public function getItemID()
    {
        return $this->itemID;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getCaseSize()
    {
        return $this->caseSize;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    //jsonSerialize() method
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
} //end of class
