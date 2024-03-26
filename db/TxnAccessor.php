<?php
require_once dirname(__DIR__, 1) . '/entity/Txn.php';

class TxnAccessor
{

    # *** Class SQL Strings ***

    # *** Select Statements ***
    private $getAllNewOnlineOrdersBySiteStatementString = "select t.txnID, s2.name as originSite, s.name as destinationSite, t.siteIDTo, t.siteIDFrom, t.status, t.shipDate, t.txnType, t.barCode, t.createdDate, IFNULL(ti.notes, '') as notes from txn t inner join site s on t.siteIDTo = s.siteID inner join site s2 on t.siteIDFrom = s2.siteID where status IN ('New', 'Assembling') and txnType IN ('Online Order') and t.siteIDTo = :siteID";

    //getting the store and emergency orders for a particular ship date
    private $getAllStoreOrdersOnShipDateStatementString = "select t.txnID, s2.name as originSite, s.name as destinationSite, t.siteIDTo, t.siteIDFrom, t.status, t.shipDate, t.txnType, t.barCode, t.createdDate, IFNULL(t.notes, '') as notes from txn t inner join site s on t.siteIDTo = s.siteID inner join site s2 on t.siteIDFrom = s2.siteID where t.shipDate = :shipDate and t.txnType IN ('Store Order', 'Emergency')";

    //getting one particular txn based on the txn ID
    private $getByIDStatementString = "select t.txnID, s2.name as originSite, s.name as destinationSite, t.siteIDTo, t.siteIDFrom, t.status, t.shipDate, t.txnType, t.barCode, t.createdDate, IFNULL(t.notes, '') as notes from txn t inner join site s on t.siteIDTo = s.siteID inner join site s2 on t.siteIDFrom = s2.siteID where t.txnID = :txnID";

    //getting one particular online order based on the txn ID
    private $getOnlineOrderByIDStatementString = "select t.txnID, s2.name as originSite, s.name as destinationSite, t.siteIDTo, t.siteIDFrom, t.status, t.shipDate, t.txnType, t.barCode, t.createdDate, IFNULL(t.notes, '') as notes from txn t inner join site s on t.siteIDTo = s.siteID inner join site s2 on t.siteIDFrom = s2.siteID where t.txnID = :txnID and t.txnType = 'Online Order'";

    //getting the last txn in the DB (the one with the highest txn ID)
    private $getLastTxnStatementString = "select t.txnID, s2.name as originSite, s.name as destinationSite, t.siteIDTo, t.siteIDFrom, t.status, t.shipDate, t.txnType, t.barCode, t.createdDate, IFNULL(t.notes, '') as notes from txn t inner join site s on t.siteIDTo = s.siteID inner join site s2 on t.siteIDFrom = s2.siteID order by t.txnID DESC LIMIT 1";

    //getting one or more txns based on the notes (ex. customer's email in the notes field)
    private $getByNotesStatementString = "select t.txnID, s2.name as originSite, s.name as destinationSite, t.siteIDTo, t.siteIDFrom, t.status, t.shipDate, t.txnType, t.barCode, t.createdDate, IFNULL(t.notes, '') as notes from txn t inner join site s on t.siteIDTo = s.siteID inner join site s2 on t.siteIDFrom = s2.siteID WHERE LOCATE(:notes, t.notes) > 0 and t.txnType = 'Online Order' order by t.createdDate desc";

    //insert statement for a single txn 
    private $insertStatementString = "insert into txn (siteIDTo, siteIDFrom, status, shipDate, txnType, barCode, createdDate, notes) VALUES (:siteIDTo, :siteIDFrom, :status, :shipDate, :txnType, :barCode, :createdDate, :notes)";

    //update statement - for status updates
    private $updateStatusStatementString = "update txn set status = :status where txnID = :txnID";

    # *** Class Statements ***
    private $getAllNewOnlineOrdersBySiteStatement = null;
    private $getAllStoreOrdersOnShipDateStatement = null;
    private $getByIDStatement = null;
    private $getOnlineOrderByIDStatement = null;
    private $getLastTxnStatement = null;
    private $getByNotesStatement = null;
    private $insertStatement = null;
    private $updateStatusStatement = null;

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

        $this->getAllNewOnlineOrdersBySiteStatement = $conn->prepare($this->getAllNewOnlineOrdersBySiteStatementString);
        if (is_null($this->getAllNewOnlineOrdersBySiteStatement)) {
            throw new Exception("bad statement: '" . $this->getAllNewOnlineOrdersBySiteStatementString . "'", 500);
        }

        $this->getAllStoreOrdersOnShipDateStatement = $conn->prepare($this->getAllStoreOrdersOnShipDateStatementString);
        if (is_null($this->getAllStoreOrdersOnShipDateStatement)) {
            throw new Exception("bad statement: '" . $this->getAllStoreOrdersOnShipDateStatementString . "'", 500);
        }

        $this->getByIDStatement = $conn->prepare($this->getByIDStatementString);
        if (is_null($this->getByIDStatement)) {
            throw new Exception("bad statement: '" . $this->getByIDStatementString . "'", 500);
        }

        $this->getOnlineOrderByIDStatement = $conn->prepare($this->getOnlineOrderByIDStatementString);
        if (is_null($this->getOnlineOrderByIDStatement)) {
            throw new Exception("bad statement: '" . $this->getOnlineOrderByIDStatementString . "'", 500);
        }

        $this->getLastTxnStatement = $conn->prepare($this->getLastTxnStatementString);
        if (is_null($this->getLastTxnStatement)) {
            throw new Exception("bad statement: '" . $this->getLastTxnStatementString . "'", 500);
        }

        $this->getByNotesStatement = $conn->prepare($this->getByNotesStatementString);
        if (is_null($this->getByNotesStatement)) {
            throw new Exception("bad statement: '" . $this->getByNotesStatementString . "'", 500);
        }

        $this->insertStatement = $conn->prepare($this->insertStatementString);
        if (is_null($this->insertStatement)) {
            throw new Exception("bad statement: '" . $this->insertStatementString . "'", 500);
        }

        $this->updateStatusStatement = $conn->prepare($this->updateStatusStatementString);
        if (is_null($this->updateStatusStatement)) {
            throw new Exception("bad statement: '" . $this->updateStatusStatementString . "'", 500);
        }
    }
    //insert methods here

    /**
     * Retrieves all of the New and Assembling online orders for a particular site, based on the site ID.
     * 
     * @param Integer the site ID wanted to retrieve 
     * @return Txn[] an array of Txn objects
     */
    public function getAllNewOnlineOrdersBySite($siteID)
    {
        //results array to be returned
        $results = [];

        try {
            //bindParam replaces the colon value with it's actual variable
            $this->getAllNewOnlineOrdersBySiteStatement->bindParam(":siteID", $siteID);
            $this->getAllNewOnlineOrdersBySiteStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getAllNewOnlineOrdersBySiteStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                $txnID = $r['txnID'];
                $siteIDTo = $r['siteIDTo'];
                $siteIDFrom = $r['siteIDFrom'];
                $status = $r['status'];
                $shipDate = $r['shipDate'];
                $txnType = $r['txnType'];
                $barCode = $r['barCode'];
                $createdDate = $r['createdDate'];
                $notes = $r['notes'];
                $originSite = $r['originSite'];
                $destinationSite = $r['destinationSite'];

                //construct txn object
                $txnObj = new Txn($txnID, $siteIDTo, $siteIDFrom, $status, $shipDate, $txnType, $barCode, $createdDate, $notes, $originSite, $destinationSite);

                //push object into results array
                array_push($results, $txnObj);
            }
        } catch (Exception $e) {
            $results = [];
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getAllNewOnlineOrdersBySiteStatement)) {
                $this->getAllNewOnlineOrdersBySiteStatement->closeCursor();
            }
        }

        //return the array
        return $results;
    }

    /**
     * Retrieves all of the store and emergency orders scheduled for delivery on the specified ship date.
     * 
     * @param String the ship date wanted to retrieve 
     * @return Txn[] an array of Txn objects, possibly empty.
     */
    public function getAllStoreOrdersOnShipDate($shipDate)
    {
        //results array to be returned
        $results = [];

        try {
            //bindParam replaces the colon value with it's actual variable
            $this->getAllStoreOrdersOnShipDateStatement->bindParam(":shipDate", $shipDate);
            $this->getAllStoreOrdersOnShipDateStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getAllStoreOrdersOnShipDateStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                $txnID = $r['txnID'];
                $siteIDTo = $r['siteIDTo'];
                $siteIDFrom = $r['siteIDFrom'];
                $status = $r['status'];
                $shipDate = $r['shipDate'];
                $txnType = $r['txnType'];
                $barCode = $r['barCode'];
                $createdDate = $r['createdDate'];
                $notes = $r['notes'];
                $originSite = $r['originSite'];
                $destinationSite = $r['destinationSite'];

                //construct txn object
                $txnObj = new Txn(
                    $txnID,
                    $siteIDTo,
                    $siteIDFrom,
                    $status,
                    $shipDate,
                    $txnType,
                    $barCode,
                    $createdDate,
                    $notes,
                    $originSite,
                    $destinationSite
                );

                //push object into results array
                array_push($results, $txnObj);
            }
        } catch (Exception $e) {
            $results = [];
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getAllStoreOrdersOnShipDateStatement)) {
                $this->getAllStoreOrdersOnShipDateStatement->closeCursor();
            }
        }

        //return the array
        return $results;
    }

    /**
     * Gets one txn, based on the txn ID.
     * 
     * @param Integer the txn ID wanted to retrieve 
     * @return Txn Txn object with the specified ID, or null if not found
     */
    public function getTxnByID($txnID)
    {
        //initialize object to be returned to null
        $txnObj = null;

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getByIDStatement->bindParam(":txnID", $txnID);
            $this->getByIDStatement->execute();

            //using fetch, not fetchAll (want a single item)
            $r = $this->getByIDStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $txnID = $r['txnID'];
                $siteIDTo = $r['siteIDTo'];
                $siteIDFrom = $r['siteIDFrom'];
                $status = $r['status'];
                $shipDate = $r['shipDate'];
                $txnType = $r['txnType'];
                $barCode = $r['barCode'];
                $createdDate = $r['createdDate'];
                $notes = $r['notes'];
                $originSite = $r['originSite'];
                $destinationSite = $r['destinationSite'];

                //construct txn object
                $txnObj = new Txn($txnID, $siteIDTo, $siteIDFrom, $status, $shipDate, $txnType, $barCode, $createdDate, $notes, $originSite, $destinationSite);
            }
        } catch (Exception $e) {
            $txnObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getByIDStatement)) {
                $this->getByIDStatement->closeCursor();
            }
        }

        //return the object
        return $txnObj;
    }

    /**
     * Gets one txn (online orders only), based on the txn ID.
     * 
     * @param Integer the txn ID wanted to retrieve 
     * @return Txn Txn object with the specified ID, or null if not found
     */
    public function getOnlineOrderByID($txnID)
    {
        //initialize object to be returned to null
        $txnObj = null;

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getOnlineOrderByIDStatement->bindParam(":txnID", $txnID);
            $this->getOnlineOrderByIDStatement->execute();

            //using fetch, not fetchAll (want a single item)
            $r = $this->getOnlineOrderByIDStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $txnID = $r['txnID'];
                $siteIDTo = $r['siteIDTo'];
                $siteIDFrom = $r['siteIDFrom'];
                $status = $r['status'];
                $shipDate = $r['shipDate'];
                $txnType = $r['txnType'];
                $barCode = $r['barCode'];
                $createdDate = $r['createdDate'];
                $notes = $r['notes'];
                $originSite = $r['originSite'];
                $destinationSite = $r['destinationSite'];

                //construct txn object
                $txnObj = new Txn(
                    $txnID,
                    $siteIDTo,
                    $siteIDFrom,
                    $status,
                    $shipDate,
                    $txnType,
                    $barCode,
                    $createdDate,
                    $notes,
                    $originSite,
                    $destinationSite
                );
            }
        } catch (Exception $e) {
            $txnObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getOnlineOrderByIDStatement)) {
                $this->getOnlineOrderByIDStatement->closeCursor();
            }
        }

        //return the object
        return $txnObj;
    }

    /**
     * Gets the last txn in the DB (ex. the one with the highest txn ID and barcode).
     * 
     * @return Txn Txn object, or null if not found
     */
    public function getLastTxn()
    {
        //initialize object to be returned to null
        $txnObj = null;

        try {

            $this->getLastTxnStatement->execute();

            //using fetch, not fetchAll (want a single item)
            $r = $this->getLastTxnStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $txnID = $r['txnID'];
                $siteIDTo = $r['siteIDTo'];
                $siteIDFrom = $r['siteIDFrom'];
                $status = $r['status'];
                $shipDate = $r['shipDate'];
                $txnType = $r['txnType'];
                $barCode = $r['barCode'];
                $createdDate = $r['createdDate'];
                $notes = $r['notes'];
                $originSite = $r['originSite'];
                $destinationSite = $r['destinationSite'];

                //construct txn object
                $txnObj = new Txn(
                    $txnID,
                    $siteIDTo,
                    $siteIDFrom,
                    $status,
                    $shipDate,
                    $txnType,
                    $barCode,
                    $createdDate,
                    $notes,
                    $originSite,
                    $destinationSite
                );
            }
        } catch (Exception $e) {
            $txnObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getLastTxnStatement)) {
                $this->getLastTxnStatement->closeCursor();
            }
        }

        //return the object
        return $txnObj;
    }

    /**
     * Retrieves one or more txns based on the notes/user's email sent in.
     * 
     * @param String the notes/email wanted to retrieve 
     * @return Txn[] an array of Txn objects
     */
    public function getTxnsByNotes($notes)
    {
        //results array to be returned
        $results = [];

        //ChromePhp::log($notes);

        try {
            //bindParam replaces the colon value with it's actual variable
            $this->getByNotesStatement->bindParam(":notes", $notes);
            $this->getByNotesStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getByNotesStatement->fetchAll(PDO::FETCH_ASSOC);

            ChromePhp::Log($dbresults);

            foreach ($dbresults as $r) {
                $txnID = $r['txnID'];
                $siteIDTo = $r['siteIDTo'];
                $siteIDFrom = $r['siteIDFrom'];
                $status = $r['status'];
                $shipDate = $r['shipDate'];
                $txnType = $r['txnType'];
                $barCode = $r['barCode'];
                $createdDate = $r['createdDate'];
                $notes2 = $r['notes'];
                $originSite = $r['originSite'];
                $destinationSite = $r['destinationSite'];

                //construct txn object
                $txnObj = new Txn(
                    $txnID,
                    $siteIDTo,
                    $siteIDFrom,
                    $status,
                    $shipDate,
                    $txnType,
                    $barCode,
                    $createdDate,
                    $notes2,
                    $originSite,
                    $destinationSite
                );

                //push object into results array
                array_push($results, $txnObj);
            }
        } catch (Exception $e) {
            $results = [];
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getByNotesStatement)) {
                $this->getByNotesStatement->closeCursor();
            }
        }

        //return the array
        return $results;
    }

    /**
     * Does the txn already exist in the DB (by checking it's ID)?
     * 
     * @param Txn the txn to check
     * @return boolean true if the txn exists, and false if not
     */
    public function txnExists(Txn $txn)
    {
        return $this->getTxnByID($txn->getTxnID()) !== null;
    }

    /**
     * Inserts a new txn into the database.
     * 
     * @param Txn $txn an object of type Txn
     * @return boolean indicates if the txnItem was inserted
     */
    public function insertTxn(Txn $txn)
    {
        $success = false;

        //shouldn't need a txnID, since that field is auto-incremented
        //$txnID = $txnItem->getTxnID();
        $siteIDTo = $txn->getSiteIDTo();
        $siteIDFrom = $txn->getSiteIDFrom();
        $status = $txn->getStatus();
        $shipDate = $txn->getShipDate();
        $txnType = $txn->getTxnType();
        $barCode = $txn->getBarCode();
        $createdDate = $txn->getCreatedDate();
        $notes = $txn->getNotes();

        try {
            //$this->insertStatement->bindParam(":txnID", $txnID);
            $this->insertStatement->bindParam(":siteIDTo", $siteIDTo);
            $this->insertStatement->bindParam(":siteIDFrom", $siteIDFrom);
            $this->insertStatement->bindParam(":status", $status);
            $this->insertStatement->bindParam(":shipDate", $shipDate);
            $this->insertStatement->bindParam(":txnType", $txnType);
            $this->insertStatement->bindParam(":barCode", $barCode);
            $this->insertStatement->bindParam(":createdDate", $createdDate);
            $this->insertStatement->bindParam(":notes", $notes);

            //execute the statement and check that the row count is 1
            $success = $this->insertStatement->execute();
            $success = $this->insertStatement->rowCount() === 1;
        } catch (PDOException $e) {
            $success = false;
        }
        //regardless - close the cursor and connection
        finally {
            if (!is_null($this->insertStatement)) {
                $this->insertStatement->closeCursor();
            }
        }

        //return a boolean
        return $success;
    }

    /**
     * Updates an existing txn's status.
     * 
     * @param Employee an object of type Employee, containing the new values to replace the DB's current values
     * @return boolean indicates if the employee was updated
     */
    public function updateTxnStatus(Txn $txnObj)
    {
        //boolean var to be returned
        $success = false;

        //if txn doesn't exist, return false
        if (!$this->txnExists($txnObj)) {
            return false;
        }

        //get the fields of the object
        $status = $txnObj->getStatus();
        $txnID = $txnObj->getTxnID();

        try {

            //bindParam replaces the colon values with their actual variables
            $this->updateStatusStatement->bindParam(":status", $status);
            $this->updateStatusStatement->bindParam(":txnID", $txnID);

            $success = $this->updateStatusStatement->execute();

            //success will only be true if the rowCount() ftn also returns a 1 exactly
            $success = $this->updateStatusStatement->rowCount() === 1;
        } catch (PDOException $e) {
            $success = false;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->updateStatusStatement)) {
                $this->updateStatusStatement->closeCursor();
            }
        }
        return $success;
    }
}
