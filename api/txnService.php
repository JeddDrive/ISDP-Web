<?php
require_once dirname(__DIR__, 1) . '/db/ConnectionManager.php';
require_once dirname(__DIR__, 1) . '/db/TxnAccessor.php';
require_once dirname(__DIR__, 1) . '/entity/Txn.php';
require_once dirname(__DIR__, 1) . '/utils/Constants.php';
require_once dirname(__DIR__, 1) . '/utils/ChromePhp.php';
require_once dirname(__DIR__) . '/api/ResponseManager.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $cm = new ConnectionManager(Constants::$MYSQL_CONNECTION_STRING, Constants::$MYSQL_USERNAME, Constants::$MYSQL_PASSWORD);
    $txnAccessor = new TxnAccessor($cm->getConnection());
    if ($method === "GET") {
        doGet($txnAccessor);
    } else if ($method === "POST") {
        doPost($txnAccessor);
    } else if ($method === "PUT") {
        doPut($txnAccessor);
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

function doGet($txnAccessor)
{
    //individual gets - are supported in this case
    //url = example: "bullseyeService/txns/100" where 100 is a txn ID integer ==> get just the txn with the matching integer ID
    if (filter_has_var(INPUT_GET, 'txnID')) {
        try {
            //get just the txn ID
            $txnID = $_GET['txnID'];

            //want to send the item ID AND txn ID into getItemByIDs
            $results = $txnAccessor->getTxnByID($txnID);

            ChromePhp::log($results);
            if ($results instanceof Txn) {
                echo ResponseManager::sendResponse(200, $results, null);
            }

            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve the txn.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - get all txns with these notes
    //url = example: "bullseyeService/txns/notes/email" where email is a notes string ==> get just the txns matching the notes string
    elseif (filter_has_var(INPUT_GET, 'notes')) {
        try {
            //get the notes
            $notes = $_GET['notes'];

            $results = $txnAccessor->getTxnsByNotes($notes);

            ChromePhp::log($results);

            if (count($results) > 0) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not retrieve any txn(s) by notes that match the e-mail.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - get all new or assembling online orders for this site
    //url = example: "bullseyeService/txns/onlineorders/4" where 4 is a site ID integer ==> get just the online order txns matching the site ID integer
    elseif (filter_has_var(INPUT_GET, 'siteID')) {
        try {
            //get the site ID
            $siteID = $_GET['siteID'];

            $results = $txnAccessor->getAllNewOnlineOrdersBySite($siteID);

            if (count($results) > 0) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not retrieve all new and assembling online orders by site.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    if (filter_has_var(INPUT_GET, 'onlineOrderID')) {
        try {
            //get just the txn ID
            $txnID = $_GET['onlineOrderID'];

            //want to send the item ID AND txn ID into getItemByIDs
            $results = $txnAccessor->getOnlineOrderByID($txnID);

            ChromePhp::log($results);

            if ($results instanceof Txn) {
                echo ResponseManager::sendResponse(200, $results, null);
            }

            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve an online order with that order ID.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //individual - getting the last txn
    //url = example: "bullseyeService/txns/lastTxn"
    else {
        try {

            $results = $txnAccessor->getLastTxn();

            if ($results instanceof Txn) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not retrieve last/most recent txn.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }
}

//CREATE
function doPost($txnAccessor)
{
    //notes must be not null, since it should contain the customer's info like email address
    if (filter_has_var(INPUT_GET, 'txnID')) {

        //the details of the item to insert will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            //create a new object
            //passing in a txn ID, origin site, and destination site since they aren't required for a txn insert
            $txnObj = new Txn($contents['txnID'], $contents['siteIDTo'], $contents['siteIDFrom'], $contents['status'], $contents['shipDate'], $contents['txnType'], $contents['barCode'], $contents['createdDate'], $contents['notes'], 'dummyOriginSite', 'dummyDestinationSite');

            //add the new object to DB
            $success = $txnAccessor->insertTxn($txnObj);
            if ($success) {
                echo ResponseManager::sendResponse(201, $success, null);
            }
            //status code 409 - item was already added to DB
            else {
                echo ResponseManager::sendResponse(409, null, "could not insert txn.");
            }
        }
        //status code 400
        catch (Exception $e) {
            echo ResponseManager::sendResponse(400, null, $e->getMessage());
        }
    }

    //collection - getting all the store orders for a particular ship date
    //url = example: "bullseyeService/txns/storeorders/2024-01-01" where 2024-01-01 is a ship date string
    elseif (filter_has_var(INPUT_GET, 'shipDate')) {

        //the details of the item to insert will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {
            //get the ship date
            $shipDate = $contents['shipDate'];

            ChromePhp::log($contents['shipDate'], $contents);

            $results = $txnAccessor->getAllStoreOrdersOnShipDate($contents['shipDate']);

            if (count($results) > 0) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "no store orders exist for the selected ship date, meaning that no delivery by Acadia will be required.");
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

//UPDATE
function doPut($txnAccessor)
{
    //txnID must be not null
    if (filter_has_var(INPUT_GET, 'txnID')) {

        //get the txn ID
        //$txnID = $_GET['txnID'];

        //get the status
        //$status = $_GET['status'];

        //the details of the item to update will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            //create a new object
            //most fields here can just be dummy fields since they aren't needed
            $txnObj = new Txn($contents['txnID'], 1, 1, $contents['status'], '2024-01-01', 'dummyType', 1234567890, '2024-01-01', 'dummyNotes', 'dummySite', 'dummySite');

            //update the existing object in the DB
            $success = $txnAccessor->updateTxnStatus($txnObj);

            if ($success) {
                echo ResponseManager::sendResponse(200, $success, null);
            }
            //status code 404 - entity not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not update txn - the txn does not exist.");
            }
        }
        //status code 400
        catch (Exception $e) {
            echo ResponseManager::sendResponse(400, null, $e->getMessage());
        }
    } else {
        //bulk updates not implemented.
        echo ResponseManager::sendResponse(405, null, "bulk UPDATEs not supported.");
    }
}
