<?php
require_once dirname(__DIR__, 1) . '/db/ConnectionManager.php';
require_once dirname(__DIR__, 1) . '/db/EmployeeAccessor.php';
require_once dirname(__DIR__, 1) . '/entity/Employee.php';
require_once dirname(__DIR__, 1) . '/utils/Constants.php';
require_once dirname(__DIR__, 1) . '/utils/ChromePhp.php';
require_once dirname(__DIR__) . '/api/ResponseManager.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $cm = new ConnectionManager(Constants::$MYSQL_CONNECTION_STRING, Constants::$MYSQL_USERNAME, Constants::$MYSQL_PASSWORD);
    $employeeAccessor = new EmployeeAccessor($cm->getConnection());
    if ($method === "GET") {
        doGet($employeeAccessor);
    } else if ($method === "PUT") {
        doPut($employeeAccessor);
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

function doGet($employeeAccessor)
{
    //individual gets - are supported in this case
    //url = example: "bullseyeService/employees/jperez" where jperez is a username string ==> get just the employee with the matching username
    if ($_GET['username']) {
        try {
            //get just the username
            $username = $_GET['username'];

            //want to send the item ID AND txn ID into getItemByIDs
            $results = $employeeAccessor->getEmployeeByUsername($username);

            //ChromePhp::log($results);
            if ($results instanceof Employee) {
                echo ResponseManager::sendResponse(200, $results, null);
            }

            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve the employee with that username.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - getting all employees
    //url = example: "bullseyeService/txns/lastTxn"
    else {
        try {

            $results = $employeeAccessor->getAllEmployees();

            if (count($results) > 0) {
                //$results = json_encode($results, JSON_NUMERIC_CHECK);
                echo ResponseManager::sendResponse(200, $results, null);
            }
            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve all employees.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }
}

//UPDATE
function doPut($employeeAccessor)
{
    //updating an employee to be locked
    if (filter_has_var(INPUT_GET, 'locked')) {

        //the details of the item to update will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            //create a new object
            //most fields here can just be dummy fields since they aren't needed
            $employeeObj = new Employee(
                $contents['employeeID'],
                'dummyPassword',
                'dummyFirstName',
                'dummyLastName',
                'dummyEmail',
                1,
                1,
                1,
                1,
                $contents['username'],
                'dummyNotes',
                'dummySiteName',
                'dummyPermissionLevel',
                3,
                1
            );

            //update the existing object in the DB
            $success = $employeeAccessor->updateEmployeeLocked($employeeObj);

            if ($success) {
                echo ResponseManager::sendResponse(200, $success, null);
            }
            //status code 404 - entity not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not update employee to be locked.");
            }
        }
        //status code 400
        catch (Exception $e) {
            echo ResponseManager::sendResponse(400, null, $e->getMessage());
        }
    }
    //updating an employee's password
    elseif (filter_has_var(INPUT_GET, 'password')) {

        //the details of the item to update will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            //create a new object
            //most fields here can just be dummy fields since they aren't needed
            $employeeObj = new Employee(
                $contents['employeeID'],
                $contents['password'],
                'dummyFirstName',
                'dummyLastName',
                'dummyEmail',
                1,
                1,
                1,
                1,
                $contents['username'],
                'dummyNotes',
                'dummySiteName',
                'dummyPermissionLevel',
                3,
                1
            );

            //update the existing object in the DB
            $success = $employeeAccessor->updateEmployeePassword($employeeObj);

            if ($success) {
                echo ResponseManager::sendResponse(200, $success, null);
            }
            //status code 404 - entity not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not update the employee's password.");
            }
        }
        //status code 400
        catch (Exception $e) {
            echo ResponseManager::sendResponse(400, null, $e->getMessage());
        }
    }
    //updating an employee's login attempts by minus 1
    elseif (filter_has_var(INPUT_GET, 'loginAttemptsMinusOne')) {

        //the details of the item to update will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            //create a new object
            //most fields here can just be dummy fields since they aren't needed
            $employeeObj = new Employee(
                $contents['employeeID'],
                'dummyPassword',
                'dummyFirstName',
                'dummyLastName',
                'dummyEmail',
                1,
                1,
                1,
                1,
                $contents['username'],
                'dummyNotes',
                'dummySiteName',
                'dummyPermissionLevel',
                3,
                1
            );

            //update the existing object in the DB
            $success = $employeeAccessor->updateEmployeeLoginAttemptsMinusOne($employeeObj);

            if ($success) {
                echo ResponseManager::sendResponse(200, $success, null);
            }
            //status code 404 - entity not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not update subtract one from employee login attempts.");
            }
        }
        //status code 400
        catch (Exception $e) {
            echo ResponseManager::sendResponse(400, null, $e->getMessage());
        }
    }
    //updating an employee's login attempts back to 3
    elseif (filter_has_var(INPUT_GET, 'loginAttemptsThree')) {

        //the details of the item to update will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            //create a new object
            //most fields here can just be dummy fields since they aren't needed
            $employeeObj = new Employee(
                $contents['employeeID'],
                'dummyPassword',
                'dummyFirstName',
                'dummyLastName',
                'dummyEmail',
                1,
                1,
                1,
                1,
                $contents['username'],
                'dummyNotes',
                'dummySiteName',
                'dummyPermissionLevel',
                3,
                1
            );

            ChromePhp::log($contents['employeeID']);

            //update the existing object in the DB
            $success = $employeeAccessor->updateEmployeeLoginAttemptsToThree($employeeObj);

            if ($success) {
                echo ResponseManager::sendResponse(200, $success, null);
            }
            //status code 404 - entity not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not update employee's login attempts back to three.");
            }
        }
        //status code 400
        catch (Exception $e) {
            echo ResponseManager::sendResponse(400, null, $e->getMessage());
        }
    }
    //updating an employee's madeFirstLogin to 1 (true)
    elseif (filter_has_var(INPUT_GET, 'madeFirstLogin')) {

        //the details of the item to update will be in the request body.
        $body = file_get_contents('php://input');
        $contents = json_decode($body, true);

        try {

            //create a new object
            //most fields here can just be dummy fields since they aren't needed
            $employeeObj = new Employee(
                $contents['employeeID'],
                'dummyPassword',
                'dummyFirstName',
                'dummyLastName',
                'dummyEmail',
                1,
                1,
                1,
                1,
                $contents['username'],
                'dummyNotes',
                'dummySiteName',
                'dummyPermissionLevel',
                3,
                1
            );

            //update the existing object in the DB
            $success = $employeeAccessor->updateEmployeeMadeFirstLogin($employeeObj);

            if ($success) {
                echo ResponseManager::sendResponse(200, $success, null);
            }
            //status code 404 - entity not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "could not update employee's made first login field to 1 (meaning true).");
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
