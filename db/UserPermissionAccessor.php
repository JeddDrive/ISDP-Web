<?php
require_once dirname(__DIR__, 1) . '/entity/UserPermission.php';

class UserPermissionAccessor
{

    # *** Class SQL Strings ***

    # *** Select Statements ***

    //are getting the permissions that an employee has, included in the permission ID list/array. This won't include permissions that an employee doesn't have
    private $getOneEmployeePermissionsStatementString = "select * from user_permission where employeeID = :employeeID and hasPermission = 1";

    # *** Class Statements ***
    private $getOneEmployeePermissionsStatement = null;

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

        $this->getOneEmployeePermissionsStatement = $conn->prepare($this->getOneEmployeePermissionsStatementString);
        if (is_null($this->getOneEmployeePermissionsStatement)) {
            throw new Exception("bad statement: '" . $this->getOneEmployeePermissionsStatementString . "'", 500);
        }
    }
    //insert methods here

    /**
     * Gets one user permission object, based on the employeeID sent in.
     * 
     * @param Integer the employeeID wanted to retrieve 
     * @return UserPermission UserPermission object with the specified employeeID, or null if not found
     */
    public function getOneEmployeeUserPermissions($employeeID)
    {
        //initialize object to be returned to null
        $userPermissionObj = null;

        //need an array for the permission IDs
        $permissionsIDArray = [];

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getOneEmployeePermissionsStatement->bindParam(":employeeID", $employeeID);
            $this->getOneEmployeePermissionsStatement->execute();

            //using fetch, not fetchAll (want a single game/item)
            $dbresults = $this->getOneEmployeePermissionsStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                //$employeeIDQuery = $r['employeeID'];
                $permissionID = $r['permissionID'];

                //push each permission ID into the array
                array_push($permissionsIDArray, $permissionID);
            }

            //after the foreach loop
            //construct UserPermission object
            $userPermissionObj = new UserPermission($employeeID, $permissionsIDArray);
        } catch (Exception $e) {
            $userPermissionObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getOneEmployeePermissionsStatement)) {
                $this->getOneEmployeePermissionsStatement->closeCursor();
            }
        }

        //return the object
        return $userPermissionObj;
    }
}
