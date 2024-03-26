<?php
require_once dirname(__DIR__, 1) . '/db/ConnectionManager.php';
require_once dirname(__DIR__, 1) . '/db/UserPermissionAccessor.php';
require_once dirname(__DIR__, 1) . '/entity/UserPermission.php';
require_once dirname(__DIR__, 1) . '/utils/Constants.php';
require_once dirname(__DIR__, 1) . '/utils/ChromePhp.php';
require_once dirname(__DIR__) . '/api/ResponseManager.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $cm = new ConnectionManager(Constants::$MYSQL_CONNECTION_STRING, Constants::$MYSQL_USERNAME, Constants::$MYSQL_PASSWORD);
    $userPermissionAccessor = new UserPermissionAccessor($cm->getConnection());
    if ($method === "GET") {
        doGet($userPermissionAccessor);
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

function doGet($userPermissionAccessor)
{
    //individual gets - are supported in this case
    //url = example: "bullseyeService/userpermissions/employee/1" where 1 is a employee ID integer ==> get just the user permissions for the employee with the matching integer ID
    if (filter_has_var(INPUT_GET, 'employeeID')) {
        try {
            //get the employeeid
            $employeeID = $_GET['employeeID'];

            //want to send the into getOneEmployeeUserPermissions
            $results = $userPermissionAccessor->getOneEmployeeUserPermissions($employeeID);

            ChromePhp::log($results);
            if ($results instanceof UserPermission) {
                echo ResponseManager::sendResponse(200, $results, null);
            }

            //status code 404 - entities not found/not in DB
            else {
                echo ResponseManager::sendResponse(404, null, "not found, could not retrieve user permissions for that employee.");
            }
        }
        //status code 500 - critical server error
        catch (Exception $e) {
            echo ResponseManager::sendResponse(500, null, "SERVER ERROR: " . $e->getMessage() + ".");
        }
    }

    //collection - no need/support to get a collection of user permissions for all employees, however
    else {
        echo ResponseManager::sendResponse(405, null, "collection GETs not supported for user permissions.");
    }
}
