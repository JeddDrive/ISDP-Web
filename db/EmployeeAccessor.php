<?php
require_once dirname(__DIR__, 1) . '/entity/Employee.php';
require_once dirname(__DIR__, 1) . '/utils/ChromePhp.php';

class EmployeeAccessor
{

    # *** Class SQL Strings ***

    # *** Select Statements ***
    private $getAllStatementString = "select e.employeeID, e.Password, e.FirstName, e.LastName, IFNULL(e.Email, '') as Email, e.active, e.PositionID, e.siteID, e.locked, e.username, IFNULL(e.notes, '') as notes, loginAttempts, madeFirstLogin, s.name, p.permissionLevel from employee e inner join site s on e.siteID = s.siteID inner join posn p on e.positionID = p.positionID order by employeeID";

    private $getByUsernameStatementString = "select e.employeeID, e.Password, e.FirstName, e.LastName, IFNULL(e.Email, '') as Email, e.active, e.PositionID, e.siteID, e.locked, e.username, IFNULL(e.notes, '') as notes, loginAttempts, madeFirstLogin, s.name, p.permissionLevel from employee e inner join site s on e.siteID = s.siteID inner join posn p on e.positionID = p.positionID where username = :username";

    //update statement - to update an employee to be locked
    private $updateLockedStatementString = "update employee set locked = 1 where employeeID = :employeeID";

    //update statement - updating an employee's password
    private $updatePasswordStatementString = "update employee set password = :password where employeeID = :employeeID";

    //update statement - subtracting 1 from employee's login attempts
    private $updateLoginAttemptsMinusOneStatementString = "update employee set loginAttempts = loginAttempts - 1 where employeeID = :employeeID";

    //update statement - updating login attempts back to 3 for a user/employee
    private $updateLoginAttemptsToThreeStatementString = "update employee set loginAttempts = 3 where employeeID = :employeeID";

    //update statement - updating madeFirstLogin field for employee to 1 after they make their first login
    private $updateMadeFirstLoginStatementString = "update employee set madeFirstLogin = 1 where employeeID = :employeeID";

    # *** Class Statements ***
    private $getAllStatement = null;
    private $getByUsernameStatement = null;
    private $updateLockedStatement = null;
    private $updatePasswordStatement = null;
    private $updateLoginAttemptsMinusOneStatement = null;
    private $updateLoginAttemptsToThreeStatement = null;
    private $updateMadeFirstLoginStatement = null;

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

        $this->getByUsernameStatement = $conn->prepare($this->getByUsernameStatementString);
        if (is_null($this->getByUsernameStatement)) {
            throw new Exception("bad statement: '" . $this->getByUsernameStatementString . "'", 500);
        }

        $this->updateLockedStatement = $conn->prepare($this->updateLockedStatementString);
        if (is_null($this->updateLockedStatement)) {
            throw new Exception("bad statement: '" . $this->updateLockedStatementString . "'", 500);
        }

        $this->updatePasswordStatement = $conn->prepare($this->updatePasswordStatementString);
        if (is_null($this->updatePasswordStatement)) {
            throw new Exception("bad statement: '" . $this->updatePasswordStatementString . "'", 500);
        }

        $this->updateLoginAttemptsMinusOneStatement = $conn->prepare($this->updateLoginAttemptsMinusOneStatementString);
        if (is_null($this->updateLoginAttemptsMinusOneStatement)) {
            throw new Exception("bad statement: '" . $this->updateLoginAttemptsMinusOneStatementString . "'", 500);
        }

        $this->updateLoginAttemptsToThreeStatement = $conn->prepare($this->updateLoginAttemptsToThreeStatementString);
        if (is_null($this->updateLoginAttemptsToThreeStatement)) {
            throw new Exception("bad statement: '" . $this->updateLoginAttemptsToThreeStatementString . "'", 500);
        }

        $this->updateMadeFirstLoginStatement = $conn->prepare($this->updateMadeFirstLoginStatementString);
        if (is_null($this->updateMadeFirstLoginStatement)) {
            throw new Exception("bad statement: '" . $this->updateMadeFirstLoginStatementString . "'", 500);
        }
    }
    //insert methods here

    /**
     * Retrieves all of the employees in the DB.
     * 
     * @return Employee[] an array of Employee objects
     */
    public function getAllEmployees()
    {
        //results array to be returned
        $results = [];

        try {
            $this->getAllStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getAllStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                $employeeID = $r['employeeID'];
                $password = $r['Password'];
                $firstName = $r['FirstName'];
                $lastName = $r['FastName'];
                $email = $r['Email'];
                $active = $r['active'];
                $positionID = $r['PositionID'];
                $siteID = $r['siteID'];
                $locked = $r['locked'];
                $username = $r['username'];
                $notes = $r['notes'];
                $siteName = $r['name'];
                $permissionLevel = $r['permissionLevel'];
                $loginAttempts = $r['loginAttempts'];
                $madeFirstLogin = $r['madeFirstLogin'];

                //construct employee object
                $employeeObj = new Employee(
                    $employeeID,
                    $password,
                    $firstName,
                    $lastName,
                    $email,
                    $active,
                    $positionID,
                    $siteID,
                    $locked,
                    $username,
                    $notes,
                    $siteName,
                    $permissionLevel,
                    $loginAttempts,
                    $madeFirstLogin
                );

                //push object into results array
                array_push($results, $employeeObj);
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
     * Gets the employee with the specified username from the DB.
     * 
     * @param String the username wanted to retrieve 
     * @return Employee Employee object with the specified username, or null if not found
     */
    public function getEmployeeByUsername($username)
    {
        //initialize object to be returned to null
        $employeeObj = null;

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getByUsernameStatement->bindParam(":username", $username);
            $this->getByUsernameStatement->execute();

            //using fetch, not fetchAll (want a single game/item)
            $r = $this->getByUsernameStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $employeeID = $r['employeeID'];
                $password = $r['Password'];
                $firstName = $r['FirstName'];
                $lastName = $r['LastName'];
                $email = $r['Email'];
                $active = $r['active'];
                $positionID = $r['PositionID'];
                $siteID = $r['siteID'];
                $locked = $r['locked'];
                $username = $r['username'];
                $notes = $r['notes'];
                $siteName = $r['name'];
                $permissionLevel = $r['permissionLevel'];
                $loginAttempts = $r['loginAttempts'];
                $madeFirstLogin = $r['madeFirstLogin'];

                //construct employee object
                $employeeObj = new Employee(
                    $employeeID,
                    $password,
                    $firstName,
                    $lastName,
                    $email,
                    $active,
                    $positionID,
                    $siteID,
                    $locked,
                    $username,
                    $notes,
                    $siteName,
                    $permissionLevel,
                    $loginAttempts,
                    $madeFirstLogin
                );
            }
        } catch (Exception $e) {
            $employeeObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getByUsernameStatement)) {
                $this->getByUsernameStatement->closeCursor();
            }
        }

        return $employeeObj;
    }

    /**
     * Does the employee already exist in the DB (by checking it's username)? The username isn't the PK for this entity, but it SHOULD be unique.
     * 
     * @param Employee the employee to check
     * @return boolean true if the employee exists, and false if not
     */
    public function employeeExists(Employee $employee)
    {
        return $this->getEmployeeByUsername($employee->getUsername()) !== null;
    }

    /**
     * Updates an existing employee already in the DB to be locked.
     * 
     * @param Employee an object of type Employee, containing the new values to replace the DB's current values
     * @return boolean indicates if the employee was updated
     */
    public function updateEmployeeLocked(Employee $employeeObj)
    {
        //boolean var to be returned
        $success = false;

        //if employee doesn't exist, return false
        if (!$this->employeeExists($employeeObj)) {
            return false;
        }

        //get the fields of the object
        $employeeID = $employeeObj->getEmployeeID();

        try {

            //bindParam replaces the colon values with their actual variables
            $this->updateLockedStatement->bindParam(":employeeID", $employeeID);

            $success = $this->updateLockedStatement->execute();

            //success will only be true if the rowCount() ftn also returns a 1 exactly
            $success = $this->updateLockedStatement->rowCount() === 1;
        } catch (PDOException $e) {
            $success = false;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->updateLockedStatement)) {
                $this->updateLockedStatement->closeCursor();
            }
        }
        return $success;
    }

    /**
     * Updates an existing employee's password already in the DB.
     * 
     * @param Employee an object of type Employee, containing the new values to replace the DB's current values
     * @return boolean indicates if the employee was updated
     */
    public function updateEmployeePassword(Employee $employeeObj)
    {
        //boolean var to be returned
        $success = false;

        //if employee doesn't exist, return false
        if (!$this->employeeExists($employeeObj)) {
            return false;
        }

        //get the fields of the object
        $password = $employeeObj->getPassword();
        $employeeID = $employeeObj->getEmployeeID();

        try {

            //bindParam replaces the colon values with their actual variables
            $this->updatePasswordStatement->bindParam(":password", $password);
            $this->updatePasswordStatement->bindParam(":employeeID", $employeeID);

            $success = $this->updatePasswordStatement->execute();

            //success will only be true if the rowCount() ftn also returns a 1 exactly
            $success = $this->updatePasswordStatement->rowCount() === 1;
        } catch (PDOException $e) {
            $success = false;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->updatePasswordStatement)) {
                $this->updatePasswordStatement->closeCursor();
            }
        }
        return $success;
    }

    /**
     * Updates an existing employee's login attempts already in the DB.
     * 
     * @param Employee an object of type Employee, containing the new values to replace the DB's current values
     * @return boolean indicates if the employee was updated
     */
    public function updateEmployeeLoginAttemptsMinusOne(Employee $employeeObj)
    {
        //boolean var to be returned
        $success = false;

        //if employee doesn't exist, return false
        if (!$this->employeeExists($employeeObj)) {
            return false;
        }

        //get the fields of the object
        $employeeID = $employeeObj->getEmployeeID();

        try {

            //bindParam replaces the colon values with their actual variables
            $this->updateLoginAttemptsMinusOneStatement->bindParam(":employeeID", $employeeID);

            $success = $this->updateLoginAttemptsMinusOneStatement->execute();

            //success will only be true if the rowCount() ftn also returns a 1 exactly
            $success = $this->updateLoginAttemptsMinusOneStatement->rowCount() === 1;
        } catch (PDOException $e) {
            $success = false;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->updateLoginAttemptsMinusOneStatement)) {
                $this->updateLoginAttemptsMinusOneStatement->closeCursor();
            }
        }
        return $success;
    }

    /**
     * Updates an existing employee's login attempts already in the DB.
     * 
     * @param Employee an object of type Employee, containing the new values to replace the DB's current values
     * @return boolean indicates if the employee was updated
     */
    public function updateEmployeeLoginAttemptsToThree(Employee $employeeObj)
    {
        //boolean var to be returned
        $success = false;

        //if employee doesn't exist, return false
        if (!$this->employeeExists($employeeObj)) {
            return false;
        }

        //get the fields of the object
        $employeeID = $employeeObj->getEmployeeID();

        ChromePhp::log($employeeID);

        try {

            //bindParam replaces the colon values with their actual variables
            $this->updateLoginAttemptsToThreeStatement->bindParam(":employeeID", $employeeID);

            $success = $this->updateLoginAttemptsToThreeStatement->execute();

            //success will only be true if the rowCount() ftn also returns a 1 exactly
            $success = $this->updateLoginAttemptsToThreeStatement->rowCount() === 1;

            ChromePhp::log($success);
        } catch (PDOException $e) {
            $success = false;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->updateLoginAttemptsToThreeStatement)) {
                $this->updateLoginAttemptsToThreeStatement->closeCursor();
            }
        }
        return $success;
    }

    /**
     * Updates an existing employee's made first login field already in the DB.
     * 
     * @param Employee an object of type Employee, containing the new values to replace the DB's current values
     * @return boolean indicates if the employee was updated
     */
    public function updateEmployeeMadeFirstLogin(Employee $employeeObj)
    {
        //boolean var to be returned
        $success = false;

        //if employee doesn't exist, return false
        if (!$this->employeeExists($employeeObj)) {
            return false;
        }

        //get the fields of the object
        $employeeID = $employeeObj->getEmployeeID();

        try {

            //bindParam replaces the colon values with their actual variables
            $this->updateMadeFirstLoginStatement->bindParam(":employeeID", $employeeID);

            $success = $this->updateMadeFirstLoginStatement->execute();

            //success will only be true if the rowCount() ftn also returns a 1 exactly
            $success = $this->updateMadeFirstLoginStatement->rowCount() === 1;
        } catch (PDOException $e) {
            $success = false;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->updateMadeFirstLoginStatement)) {
                $this->updateMadeFirstLoginStatement->closeCursor();
            }
        }
        return $success;
    }
}
