<?php
require_once dirname(__DIR__, 1) . '/entity/TxnItems.php';

class TxnItemsAccessor
{

    # *** Class SQL Strings ***

    # *** Select Statements ***
    private $getAllByTxnStatementString = "select ti.txnID, ti.itemID, i.name, IFNULL(i.description, '') as description, ti.quantity, i.caseSize, i.weight, IFNULL(ti.notes, '') as notes from txnitems ti inner join item i on ti.itemID = i.itemID where ti.txnID = :txnID";

    //getting one txn item for a particular txn
    //the PK for the txnitems table is the itemID AND txnID (Composite Key)
    private $getByIDsStatementString = "select ti.txnID, ti.itemID, i.name, IFNULL(i.description, '') as description, ti.quantity, i.caseSize, i.weight, IFNULL(ti.notes, '') as notes from txnitems ti inner join item i on ti.itemID = i.itemID where ti.txnID = :txnID and ti.itemID = :itemID";

    //getting the delivery weight for store AND emergency orders on a particular ship date
    private $getDeliveryWeightOnShipDateStatementString = "select t.shipDate, sum(i.weight * ti.quantity) as txnWeight from txnitems ti inner join item i on ti.itemID = i.itemID inner join txn t on ti.txnID = t.txnID where t.txnType IN ('Store Order', 'Emergency') and t.shipDate = :shipDate group by t.shipDate";

    //insert statement for a single txn item
    private $insertStatementString = "insert into txnitems (txnID, itemID, quantity, notes) values (:txnID, :itemID, :quantity, :notes)";

    # *** Class Statements ***
    private $getAllByTxnStatement = null;
    private $getByIDsStatement = null;
    private $getDeliveryWeightOnShipDateStatement = null;
    private $insertStatement = null;

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

        $this->getAllByTxnStatement = $conn->prepare($this->getAllByTxnStatementString);
        if (is_null($this->getAllByTxnStatement)) {
            throw new Exception("bad statement: '" . $this->getAllByTxnStatementString . "'", 500);
        }

        $this->getByIDsStatement = $conn->prepare($this->getByIDsStatementString);
        if (is_null($this->getByIDsStatement)) {
            throw new Exception("bad statement: '" . $this->getByIDsStatementString . "'", 500);
        }

        $this->getDeliveryWeightOnShipDateStatement = $conn->prepare($this->getDeliveryWeightOnShipDateStatementString);
        if (is_null($this->getDeliveryWeightOnShipDateStatement)) {
            throw new Exception("bad statement: '" . $this->getDeliveryWeightOnShipDateStatementString . "'", 500);
        }

        $this->insertStatement = $conn->prepare($this->insertStatementString);
        if (is_null($this->insertStatement)) {
            throw new Exception("bad statement: '" . $this->insertStatementString . "'", 500);
        }
    }
    //insert methods here
    /**
     * Retrieves all of the items in a txn.
     * 
     * @param Integer the txn ID wanted to retrieve 
     * @return TxnItems[] an array of TxnItems objects
     */
    public function getAllItemsByTxnID($txnID)
    {
        //results array to be returned
        $results = [];

        try {
            //bindParam replaces the colon value with it's actual variable
            $this->getAllByTxnStatement->bindParam(":txnID", $txnID);
            $this->getAllByTxnStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getAllByTxnStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                $txnID = $r['txnID'];
                $itemID = $r['itemID'];
                $quantity = $r['quantity'];
                $notes = $r['notes'];
                $name = $r['name'];
                $description = $r['description'];
                $caseSize = $r['caseSize'];
                $weight = $r['weight'];

                //construct txnitems object
                $txnItemObj = new TxnItems($txnID, $itemID, $quantity, $notes, $name, $description, $caseSize, $weight);

                //push object into results array
                array_push($results, $txnItemObj);
            }
        } catch (Exception $e) {
            $results = [];
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getAllByTxnStatement)) {
                $this->getAllByTxnStatement->closeCursor();
            }
        }

        //return the array
        return $results;
    }

    /**
     * Gets a specific item in a txn.
     * 
     * @param Integer the txn ID wanted to retrieve 
     * @param Integer the item ID wanted to retrieve 
     * @return TxnItems TxnItems object with the specified ID, or null if not found
     */
    public function getItemByIDs($txnID, $itemID)
    {
        //initialize object to be returned to null
        $txnItemObj = null;

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getByIDsStatement->bindParam(":txnID", $txnID);
            $this->getByIDsStatement->bindParam(":itemID", $itemID);
            $this->getByIDsStatement->execute();

            //using fetch, not fetchAll (want a single item)
            $r = $this->getByIDsStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $txnID = $r['txnID'];
                $itemID = $r['itemID'];
                $quantity = $r['quantity'];
                $notes = $r['notes'];
                $name = $r['name'];
                $description = $r['description'];
                $caseSize = $r['caseSize'];
                $weight = $r['weight'];

                //construct txnitems object
                $txnItemObj = new TxnItems($txnID, $itemID, $quantity, $notes, $name, $description, $caseSize, $weight);
            }
        } catch (Exception $e) {
            $txnItemObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getByIDsStatement)) {
                $this->getByIDsStatement->closeCursor();
            }
        }

        //return the object
        return $txnItemObj;
    }


    /**
     * Gets the delivery weight for all store AND emergency orders on the specified ship date.
     * 
     * @param String the ship date wanted to retrieve
     * @return TxnItems TxnItems object (dummy object), or null if not found
     */
    public function getDeliveryWeightOnShipDate($shipDate)
    {
        //initialize object to be returned to null
        $txnItemObj = null;

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getDeliveryWeightOnShipDateStatement->bindParam(":shipDate", $shipDate);
            $this->getDeliveryWeightOnShipDateStatement->execute();

            //using fetch, not fetchAll (want a single item)
            $r = $this->getDeliveryWeightOnShipDateStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {

                //only field we really need is the weight (named txnWeight in the SQL statement)
                $weight = $r['txnWeight'];

                //construct txnitems object - this is essentially a dummy object. The only info going back to the front end that inside this object that should be needed is the weight
                $txnItemObj = new TxnItems(1, 1, 0, 'dummyNotes', 'dummyName', 'dummyDescription', 0, $weight);
            }
        } catch (Exception $e) {
            $txnItemObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getDeliveryWeightOnShipDateStatement)) {
                $this->getDeliveryWeightOnShipDateStatement->closeCursor();
            }
        }

        //return the object
        return $txnItemObj;
    }

    /**
     * Inserts a new txn item for a particular txn into the database.
     * 
     * @param TxnItems $txnItem an object of type TxnItems
     * @return boolean indicates if the txnItem was inserted
     */
    public function insertTxnItem(TxnItems $txnItem)
    {
        $success = false;

        $txnID = $txnItem->getTxnID();
        $itemID = $txnItem->getItemID();
        $quantity = $txnItem->getQuantity();
        $notes = $txnItem->getNotes();

        try {
            $this->insertStatement->bindParam(":txnID", $txnID);
            $this->insertStatement->bindParam(":itemID", $itemID);
            $this->insertStatement->bindParam(":quantity", $quantity);
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
}
