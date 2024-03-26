<?php
//Txn class to represent this entity from the Bullseye DB
class Txn implements JsonSerializable
{
    //11 private fields
    private $txnID;
    private $siteIDTo;
    private $siteIDFrom;
    private $status;
    private $shipDate;
    private $txnType;
    private $barCode;
    private $createdDate;
    private $notes;
    private $originSite;
    private $destinationSite;

    //public constructor
    public function __construct(
        $inTxnID,
        $inSiteIDTo,
        $inSiteIDFrom,
        $inStatus,
        $inShipDate,
        $inTxnType,
        $inBarCode,
        $inCreatedDate,
        $inNotes,
        $inOriginSite,
        $inDestinationSite
    ) {
        $this->txnID = $inTxnID;
        $this->siteIDTo = $inSiteIDTo;
        $this->siteIDFrom = $inSiteIDFrom;
        $this->status = $inStatus;
        $this->shipDate = $inShipDate;
        $this->txnType = $inTxnType;
        $this->barCode = $inBarCode;
        $this->createdDate = $inCreatedDate;
        $this->notes = $inNotes;
        $this->originSite = $inOriginSite;
        $this->destinationSite = $inDestinationSite;
    }

    //11 public getter methods
    public function getTxnID()
    {
        return $this->txnID;
    }

    public function getSiteIDTo()
    {
        return $this->siteIDTo;
    }

    public function getSiteIDFrom()
    {
        return $this->siteIDFrom;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getShipDate()
    {
        return $this->shipDate;
    }

    public function getTxnType()
    {
        return $this->txnType;
    }

    public function getBarcode()
    {
        return $this->barCode;
    }

    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function getOriginSite()
    {
        return $this->originSite;
    }

    public function getDestinationSite()
    {
        return $this->destinationSite;
    }

    //jsonSerialize() method
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
} //end of class
