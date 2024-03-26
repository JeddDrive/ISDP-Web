<?php
require_once dirname(__DIR__, 1) . '/entity/Vehicle.php';

class VehicleAccessor
{

    # *** Class SQL Strings ***

    # *** Select Statements ***
    private $getAllStatementString = "select * from vehicle";

    private $getByIDStatementString = "select * from vehicle where vehicleType = :vehicleType";

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
     * Retrieves all of the vehicles in the DB.
     * 
     * @return Vehicle[] an array of Vehicle objects
     */
    public function getAllVehicles()
    {
        //results array to be returned
        $results = [];

        try {
            $this->getAllStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getAllStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                $vehicleType = $r['vehicleType'];
                $maxWeight = $r['maxWeight'];
                $hourlyTruckCost = $r['HourlyTruckCost'];
                $costPerKm = $r['costPerKm'];
                $notes = $r['notes'];

                //construct vehicle object
                $vehicleObj = new Vehicle($vehicleType, $maxWeight, $hourlyTruckCost, $costPerKm, $notes);

                //push object into results array
                array_push($results, $vehicleObj);
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
     * Gets the vehicle with the specified vehicle type from the DB.
     * 
     * @param String the vehicle type wanted to retrieve 
     * @return Vehicle Vehicle object with the specified vehicle type, or null if not found
     */
    public function getOneVehicle($vehicleType)
    {
        //initialize object to be returned to null
        $vehicleObj = null;

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getByIDStatement->bindParam(":vehicleType", $vehicleType);
            $this->getByIDStatement->execute();

            //using fetch, not fetchAll (want a single item)
            $r = $this->getByIDStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $vehicleType = $r['vehicleType'];
                $maxWeight = $r['maxWeight'];
                $hourlyTruckCost = $r['HourlyTruckCost'];
                $costPerKm = $r['costPerKm'];
                $notes = $r['notes'];

                //construct vehicle object
                $vehicleObj = new Vehicle($vehicleType, $maxWeight, $hourlyTruckCost, $costPerKm, $notes);
            }
        } catch (Exception $e) {
            $vehicleObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getByIDStatement)) {
                $this->getByIDStatement->closeCursor();
            }
        }

        //return the object
        return $vehicleObj;
    }
}
