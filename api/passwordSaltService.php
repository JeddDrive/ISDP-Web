<?php
require_once dirname(__DIR__, 1) . '/db/ConnectionManager.php';
require_once dirname(__DIR__, 1) . '/db/PasswordSaltAccessor.php';
require_once dirname(__DIR__, 1) . '/entity/PasswordSalt.php';
require_once dirname(__DIR__, 1) . '/utils/Constants.php';
require_once dirname(__DIR__, 1) . '/utils/ChromePhp.php';
require_once dirname(__DIR__) . '/api/ResponseManager.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $cm = new ConnectionManager(Constants::$MYSQL_CONNECTION_STRING, Constants::$MYSQL_USERNAME, Constants::$MYSQL_PASSWORD);
    $passwordSaltAccessor = new PasswordSaltAccessor($cm->getConnection());
    if ($method === "GET") {
        doGet($passwordSaltAccessor);
    } else if ($method === "POST") {
        doPost($passwordSaltAccessor);
    } else if ($method === "PUT") {
        doPut($passwordSaltAccessor);
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

function doGet($passwordSaltAccessor)
{
    //individual gets - are supported in this case
    //url = example: "bullseyeService/passwordSalt/employee/1" where 1 is a employee ID integer ==> get just the password salt with the matching integer ID
    if (filter_has_var(INPUT_GET, 'employeeID')) {
        try {
            //get the employeeid
            $employeeID = $_GET['employeeID'];

            //want to send the into getPasswordSaltByID
            $results = $passwordSaltAccessor->getPasswordSaltByID($employeeID);

            //ChromePhp::log($results);
            if ($results instanceof PasswordSalt) {
                echo ResponseManager::sendResponse(200, $results, null);
            }

            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve password salt for that employee.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - no need/support to get a collection of password salts, however
    else {
        echo ResponseManager::sendResponse(405, null, "collection GETs not supported for password salts.");
    }
}

//CREATE
function doPost($passwordSaltAccessor)
{
    //employeeID must be not null
    if (filter_has_var(INPUT_GET, 'employeeID')) {

        //the details of the item to insert will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            //create a new object
            $passwordSaltObj = new PasswordSalt($contents['employeeID'], $contents['passwordSalt']);

            //add the new object to DB
            $success = $passwordSaltAccessor->insertPasswordSalt($passwordSaltObj);
            if ($success) {
                echo ResponseManager::sendResponse(201, $success, null);
            }
            //status code 409 - item was already added to DB
            else {
                echo ResponseManager::sendResponse(409, null, "could not insert password salt.");
            }
        }
        //status code 400
        catch (Exception $e) {
            echo ResponseManager::sendResponse(400, null, $e->getMessage());
        }
    } else {
        //bulk inserts not supported/allowed
        echo ResponseManager::sendResponse(405, null, "bulk INSERTs not supported.");
    }
}

//UPDATE
function doPut($passwordSaltAccessor)
{
    //employeeID must be not null
    if (filter_has_var(INPUT_GET, 'employeeID')) {

        //the details of the item to update will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            ChromePhp::log($contents);

            //create a new object
            $passwordSaltObj = new PasswordSalt($contents['employeeID'], $contents['passwordSalt']);

            //update the existing object in the DB
            $success = $passwordSaltAccessor->updatePasswordSalt($passwordSaltObj);

            if ($success) {
                echo ResponseManager::sendResponse(200, $success, null);
            }
            //status code 404 - entity not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not update password salt - they (employee) do not exist.");
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
