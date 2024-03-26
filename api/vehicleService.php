<?php
require_once dirname(__DIR__, 1) . '/db/ConnectionManager.php';
require_once dirname(__DIR__, 1) . '/db/VehicleAccessor.php';
require_once dirname(__DIR__, 1) . '/entity/Vehicle.php';
require_once dirname(__DIR__, 1) . '/utils/Constants.php';
require_once dirname(__DIR__, 1) . '/utils/ChromePhp.php';
require_once dirname(__DIR__) . '/api/ResponseManager.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $cm = new ConnectionManager(Constants::$MYSQL_CONNECTION_STRING, Constants::$MYSQL_USERNAME, Constants::$MYSQL_PASSWORD);
    $vehicleAccessor = new VehicleAccessor($cm->getConnection());
    if ($method === "GET") {
        doGet($vehicleAccessor);
    } else {
        echo ResponseManager::sendResponse(405, null, "method not allowed.");
    }
} catch (Exception $e) {
    echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage());
} finally {
    if (!is_null($cm)) {
        $cm->closeConnection();
    }
}

function doGet($vehicleAccessor)
{
    //individual gets - are supported in this case
    //url = example: "bullseyeService/vehicles/Van" where Van is a vehicle type string ==> get just the vehicle type with the matching string
    if (isset($_GET['vehicleType'])) {
        try {
            //get the vehicle type
            $vehicleType = $_GET['vehicleType'];

            //want to send the vehicle type into getOneVehicle
            $results = $vehicleAccessor->getOneVehicle($vehicleType);

            ChromePhp::log($results);
            if ($results instanceof Vehicle) {
                echo ResponseManager::sendResponse(200, $results, null);
            }

            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve the one vehicle.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - get all vehicles
    //url = example: "bullseyeService/vehicles"
    else {
        try {
            $results = $vehicleAccessor->getAllVehicles();
            if (count($results) > 0) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not retrieve all vehicles.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }
}
