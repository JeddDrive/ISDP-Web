<?php
require_once dirname(__DIR__, 1) . '/db/ConnectionManager.php';
require_once dirname(__DIR__, 1) . '/db/TxnItemsAccessor.php';
require_once dirname(__DIR__, 1) . '/entity/TxnItems.php';
require_once dirname(__DIR__, 1) . '/utils/Constants.php';
require_once dirname(__DIR__, 1) . '/utils/ChromePhp.php';
require_once dirname(__DIR__) . '/api/ResponseManager.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $cm = new ConnectionManager(Constants::$MYSQL_CONNECTION_STRING, Constants::$MYSQL_USERNAME, Constants::$MYSQL_PASSWORD);
    $txnItemsAccessor = new TxnItemsAccessor($cm->getConnection());
    if ($method === "GET") {
        doGet($txnItemsAccessor);
    } else if ($method === "POST") {
        doPost($txnItemsAccessor);
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

function doGet($TxnItemsAccessor)
{
    //individual gets - are supported in this case
    //url = example: "bullseyeService/txnitems/10000" where 10000 is a item ID integer ==> get just the txn item with the matching integer ID
    if (filter_has_var(INPUT_GET, 'itemID')) {
        try {

            //get the item ID and txn ID
            $txnID = $_GET['txnID'];
            $itemID = $_GET['itemID'];

            //want to send the item ID AND txn ID into getItemByIDs
            $results = $TxnItemsAccessor->getItemByIDs($txnID, $itemID);

            ChromePhp::log($results);
            if ($results instanceof TxnItems) {
                echo ResponseManager::sendResponse(200, $results, null);
            }

            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve the txn item.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - get all items in a particular txn
    //url = example: "bullseyeService/txnitems/100" where 100 is a txn ID integer ==> get just the txn items for the txn matching integer ID
    elseif (filter_has_var(INPUT_GET, 'txnID')) {
        try {
            //get the txn ID
            $txnID = $_GET['txnID'];

            $results = $TxnItemsAccessor->getAllItemsByTxnID($txnID);
            if (count($results) > 0) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not retrieve all items in the txn.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - getting the delivery weight for store orders on a particular ship date
    //url = example: "bullseyeService/txnitems/'2024-01-01"
    else {
        try {
            //get the shipDate
            $shipDate = $_GET['shipDate'];

            $results = $TxnItemsAccessor->getDeliveryWeightOnShipDate($shipDate);

            if (count($results) > 0) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not retrieve the delivery weight for the ship date.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }
}

//CREATE
function doPost($txnItemsAccessor)
{
    //txnID and itemID must be not null
    if (filter_has_var(INPUT_GET, 'txnID')) {

        //the details of the item to insert will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            //create a new object
            //passing in a dummy name, description, case size, and weight since they are not required for an insert for this table
            $txnItemObj = new TxnItems($contents['txnID'], $contents['itemID'], $contents['quantity'], $contents['notes'], 'dummyName', 'dummyDescription', 0, 0);

            //add the new object to DB
            $success = $txnItemsAccessor->insertTxnItem($txnItemObj);
            if ($success) {
                echo ResponseManager::sendResponse(201, $success, null);
            }
            //status code 409 - item was already added to DB
            else {
                echo ResponseManager::sendResponse(409, null, "could not insert txn item.");
            }
        }
        //status code 400
        catch (Exception $e) {
            echo ResponseManager::sendResponse(400, null, $e->getMessage());
        }
    }

    //collection - getting the delivery weight for store orders on a particular ship date
    //url = example: "bullseyeService/txnitems/'2024-01-01"
    elseif (filter_has_var(INPUT_GET, 'shipDate')) {

        //the details of the item to insert will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {
            //get the ship date
            $shipDate = $contents['shipDate'];

            ChromePhp::log($contents['shipDate'], $contents);

            $results = $txnItemsAccessor->getDeliveryWeightOnShipDate($contents['shipDate']);

            if ($results instanceof TxnItems) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not retrieve the delivery weight for the ship date.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    } else {
        //bulk inserts not supported/allowed
        echo ResponseManager::sendResponse(405, null, "bulk INSERTs not supported.");
    }
}
