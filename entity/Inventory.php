<?php
//Inventory class to represent this entity from the Bullseye DB
class Inventory implements JsonSerializable
{
    //12 private fields
    private $itemID;
    private $siteID;
    private $quantity;
    private $itemLocation;
    private $imageFileLocation;
    private $reorderThreshold;
    private $optimumThreshold;
    private $notes;
    private $name;
    private $description;
    private $siteName;
    private $price;

    //public constructor
    public function __construct(
        $inItemID,
        $inSiteID,
        $inQuantity,
        $inItemLocation,
        $inImageFileLocation,
        $inReorderThreshold,
        $inOptimumThreshold,
        $inNotes,
        $inName,
        $inDescription,
        $inSiteName,
        $inPrice
    ) {
        $this->itemID = $inItemID;
        $this->siteID = $inSiteID;
        $this->quantity = $inQuantity;
        $this->itemLocation = $inItemLocation;
        $this->imageFileLocation = $inImageFileLocation;
        $this->reorderThreshold = $inReorderThreshold;
        $this->optimumThreshold = $inOptimumThreshold;
        $this->notes = $inNotes;
        $this->name = $inName;
        $this->description = $inDescription;
        $this->siteName = $inSiteName;
        $this->price = $inPrice;
    }

    //12 public getter methods
    public function getItemID()
    {
        return $this->itemID;
    }

    public function getSiteID()
    {
        return $this->siteID;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getItemLocation()
    {
        return $this->itemLocation;
    }

    public function getImageFileLocation()
    {
        return $this->imageFileLocation;
    }

    public function getReorderThreshold()
    {
        return $this->reorderThreshold;
    }

    public function getOptimumThreshold()
    {
        return $this->optimumThreshold;
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

    public function getSiteName()
    {
        return $this->siteName;
    }

    public function getPrice()
    {
        return $this->price;
    }

    //jsonSerialize() method
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
} //end of class
