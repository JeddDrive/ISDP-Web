<?php
require_once dirname(__DIR__, 1) . '/db/ConnectionManager.php';
require_once dirname(__DIR__, 1) . '/db/InventoryAccessor.php';
require_once dirname(__DIR__, 1) . '/entity/Inventory.php';
require_once dirname(__DIR__, 1) . '/utils/Constants.php';
require_once dirname(__DIR__, 1) . '/utils/ChromePhp.php';
require_once dirname(__DIR__) . '/api/ResponseManager.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $cm = new ConnectionManager(Constants::$MYSQL_CONNECTION_STRING, Constants::$MYSQL_USERNAME, Constants::$MYSQL_PASSWORD);
    $inventoryAccessor = new InventoryAccessor($cm->getConnection());
    if ($method === "GET") {
        doGet($inventoryAccessor);
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

function doGet($inventoryAccessor)
{
    //individual gets - are supported in this case
    //url = example: "bullseyeService/inventory/site/1" where 1 is a site ID integer ==> get just the inventory for the site with the matching integer ID
    if (filter_has_var(INPUT_GET, 'itemID')) {
        try {

            //get the site ID AND item ID
            $siteID = $_GET['siteID'];
            $itemID = $_GET['itemID'];

            //want to send the site ID and item ID into getInventoryItemByIDs
            $results = $inventoryAccessor->getInventoryItemByIDs($siteID, $itemID);

            ChromePhp::log($results);
            if ($results instanceof Inventory) {
                echo ResponseManager::sendResponse(200, $results, null);
            }

            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve the one inventory item for that site.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - get all inventory for a particular site
    //don't need any itemID here, but do need a siteID however
    //url = example: "bullseyeService/sites"
    elseif (filter_has_var(INPUT_GET, 'siteID')) {
        try {

            //get the siteID
            $siteID = $_GET['siteID'];

            //want to send the site ID into getAllInventoryBySiteID
            $results = $inventoryAccessor->getAllInventoryBySiteID($siteID);

            if (count($results) > 0) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not retrieve all inventory for that site.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }
}
