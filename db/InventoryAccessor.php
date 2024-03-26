<?php
require_once dirname(__DIR__, 1) . '/entity/Inventory.php';

class InventoryAccessor
{

    # *** Class SQL Strings ***

    # *** Select Statements ***
    private $getAllBySiteStatementString = "select iv.itemID, it.name, it.description, iv.siteID, s.name AS siteName, iv.quantity, iv.itemLocation, it.imageFileLocation, IFNULL(iv.reorderThreshold, '') as reorderThreshold, iv.optimumThreshold, IFNULL(iv.notes, '') as notes, it.retailPrice from inventory iv inner join item it on iv.itemID = it.itemID inner join site s on iv.siteID = s.siteID where iv.siteID = :siteID";

    //getting one inventory item for a particular site
    //the PK for the inventory table is the itemID AND siteID (Composite Key)
    private $getByIDsStatementString = "select iv.itemID, it.name, it.description, iv.siteID, s.name AS siteName, iv.quantity, iv.itemLocation, it.imageFileLocation, IFNULL(iv.reorderThreshold, '') as reorderThreshold, iv.optimumThreshold, IFNULL(iv.notes, '') as notes, it.retailPrice from inventory iv inner join item it on iv.itemID = it.itemID inner join site s on iv.siteID = s.siteID where iv.siteID = :siteID and iv.itemID = :itemID";

    # *** Class Statements ***
    private $getAllBySiteStatement = null;
    private $getByIDsStatement = null;

    /**
     * Creates a new instance of the accessor with the supplied database connection.
     * 
     * @param PDO $conn - a database connection
     */
    public function __construct($conn)
    {

        if (is_null($conn)) {
            throw new Exception("no connection", 500);
        }

        $this->getAllBySiteStatement = $conn->prepare($this->getAllBySiteStatementString);
        if (is_null($this->getAllBySiteStatement)) {
            throw new Exception("bad statement: '" . $this->getAllBySiteStatementString . "'", 500);
        }

        $this->getByIDsStatement = $conn->prepare($this->getByIDsStatementString);
        if (is_null($this->getByIDsStatement)) {
            throw new Exception("bad statement: '" . $this->getByIDsStatementString . "'", 500);
        }
    }
    //insert methods here

    /**
     * Retrieves all of the inventory items for 1 site in the DB (not all sites).
     * 
     * @param Integer the site ID wanted to retrieve 
     * @return Site[] an array of Site objects
     */
    public function getAllInventoryBySiteID($siteID)
    {
        //results array to be returned
        $results = [];

        try {
            //bindParam replaces the colon value with it's actual variable
            $this->getAllBySiteStatement->bindParam(":siteID", $siteID);
            $this->getAllBySiteStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getAllBySiteStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                $itemID = $r['itemID'];
                $name = $r['name'];
                $description = $r['description'];
                $siteID2 = $r['siteID'];
                $quantity = $r['quantity'];
                $itemLocation = $r['itemLocation'];
                $imageFileLocation = $r['imageFileLocation'];
                $reorderThreshold = $r['reorderThreshold'];
                $optimumThreshold = $r['optimumThreshold'];
                $notes = $r['notes'];
                $siteName = $r['siteName'];
                $price = $r['retailPrice'];

                //construct inventory object
                $inventoryObj = new Inventory($itemID, $siteID2, $quantity, $itemLocation, $imageFileLocation, $reorderThreshold, $optimumThreshold, $notes, $name, $description, $siteName, $price);

                //push object into results array
                array_push($results, $inventoryObj);
            }
        } catch (Exception $e) {
            $results = [];
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getAllBySiteStatement)) {
                $this->getAllBySiteStatement->closeCursor();
            }
        }

        //return the array
        return $results;
    }

    /**
     * Gets the inventory item for a particular site.
     * 
     * @param Integer the site ID wanted to retrieve 
     * @param Integer the item ID wanted to retrieve 
     * @return Site Site object with the specified site ID, or null if not found
     */
    public function getInventoryItemByIDs($siteID, $itemID)
    {
        //initialize object to be returned to null
        $inventoryObj = null;

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getByIDsStatement->bindParam(":siteID", $siteID);
            $this->getByIDsStatement->bindParam(":itemID", $itemID);
            $this->getByIDsStatement->execute();

            //using fetch, not fetchAll (want a single item)
            $r = $this->getByIDsStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $itemID2 = $r['itemID'];
                $name = $r['name'];
                $description = $r['description'];
                $siteID2 = $r['siteID'];
                $quantity = $r['quantity'];
                $itemLocation = $r['itemLocation'];
                $imageFileLocation = $r['imageFileLocation'];
                $reorderThreshold = $r['reorderThreshold'];
                $optimumThreshold = $r['optimumThreshold'];
                $notes = $r['notes'];
                $siteName = $r['siteName'];
                $price = $r['retailPrice'];

                //construct inventory object
                $inventoryObj = new Inventory($itemID2, $siteID2, $quantity, $itemLocation, $imageFileLocation, $reorderThreshold, $optimumThreshold, $notes, $name, $description, $siteName, $price);
            }
        } catch (Exception $e) {
            $inventoryObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getByIDsStatement)) {
                $this->getByIDsStatement->closeCursor();
            }
        }

        //return the object
        return $inventoryObj;
    }
}
