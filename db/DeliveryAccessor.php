<?php
require_once dirname(__DIR__, 1) . '/entity/Delivery.php';

class DeliveryAccessor
{

    # *** Class SQL Strings ***

    # *** Select Statements ***
    private $getAllStatementString = "select deliveryID, distanceCost, vehicleType, IFNULL(notes, '') AS notes from delivery";

    private $getByIDStatementString = "select deliveryID, distanceCost, vehicleType, IFNULL(notes, '') AS notes from delivery where deliveryID = :deliveryID";

    # *** Class Statements ***
    private $getAllStatement = null;
    private $getByIDStatement = null;

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

        $this->getAllStatement = $conn->prepare($this->getAllStatementString);
        if (is_null($this->getAllStatement)) {
            throw new Exception("bad statement: '" . $this->getAllStatementString . "'", 500);
        }

        $this->getByIDStatement = $conn->prepare($this->getByIDStatementString);
        if (is_null($this->getByIDStatement)) {
            throw new Exception("bad statement: '" . $this->getByIDStatementString . "'", 500);
        }
    }
    //insert methods here

    /**
     * Retrieves all of the deliveries in the DB.
     * 
     * @return Delivery[] an array of Delivery objects
     */
    public function getAllDeliveries()
    {
        //results array to be returned
        $results = [];

        try {
            $this->getAllStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getAllStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                $deliveryID = $r['deliveryID'];
                $distanceCost = $r['distanceCost'];
                $vehicleType = $r['vehicleType'];
                $notes = $r['notes'];

                //construct delivery object
                $deliveryObj = new Delivery($deliveryID, $distanceCost, $vehicleType, $notes);

                //push object into results array
                array_push($results, $deliveryObj);
            }
        } catch (Exception $e) {
            $results = [];
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getAllStatement)) {
                $this->getAllStatement->closeCursor();
            }
        }

        return $results;
    }

    /**
     * Gets the delivery with the specified delivery ID from the DB.
     * 
     * @param Integer the delivery ID wanted to retrieve 
     * @return Delivery Delivery object with the specified delivery ID, or null if not found
     */
    public function getOneDelivery($deliveryID)
    {
        //initialize object to be returned to null
        $deliveryObj = null;

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getByIDStatement->bindParam(":deliveryID", $deliveryID);
            $this->getByIDStatement->execute();

            //using fetch, not fetchAll (want a single item)
            $r = $this->getByIDStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $deliveryID = $r['deliveryID'];
                $distanceCost = $r['distanceCost'];
                $vehicleType = $r['vehicleType'];
                $notes = $r['notes'];

                //construct delivery object
                $deliveryObj = new Delivery($deliveryID, $distanceCost, $vehicleType, $notes);
            }
        } catch (Exception $e) {
            $deliveryObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getByIDStatement)) {
                $this->getByIDStatement->closeCursor();
            }
        }

        //return the object
        return $deliveryObj;
    }
}
