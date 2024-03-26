<?php
require_once dirname(__DIR__, 1) . '/db/ConnectionManager.php';
require_once dirname(__DIR__, 1) . '/db/DeliveryAccessor.php';
require_once dirname(__DIR__, 1) . '/entity/Delivery.php';
require_once dirname(__DIR__, 1) . '/utils/Constants.php';
require_once dirname(__DIR__, 1) . '/utils/ChromePhp.php';
require_once dirname(__DIR__) . '/api/ResponseManager.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $cm = new ConnectionManager(Constants::$MYSQL_CONNECTION_STRING, Constants::$MYSQL_USERNAME, Constants::$MYSQL_PASSWORD);
    $deliveryAccessor = new DeliveryAccessor($cm->getConnection());
    if ($method === "GET") {
        doGet($deliveryAccessor);
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

function doGet($deliveryAccessor)
{
    //individual gets - are supported in this case
    //url = example: "bullseyeService/deliveries/1" where 1 is a delivery ID integer ==> get just the delivery with the matching integer ID
    if (isset($_GET['deliveryID'])) {
        try {
            //get the delivery ID
            $deliveryID = $_GET['deliveryID'];

            //want to send just the delivery ID into getOneDelivery
            $results = $deliveryAccessor->getOneDelivery($deliveryID);

            ChromePhp::log($results);
            if ($results instanceof Delivery) {
                echo ResponseManager::sendResponse(200, $results, null);
            }

            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve the one delivery.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - get all deliveries
    //url = example: "bullseyeService/deliveries"
    else {
        try {
            $results = $deliveryAccessor->getAllDeliveries();
            if (count($results) > 0) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not retrieve all deliveries.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }
}
