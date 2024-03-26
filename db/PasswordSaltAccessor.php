<?php
require_once dirname(__DIR__, 1) . '/entity/PasswordSalt.php';

class PasswordSaltAccessor
{

    # *** Class SQL Strings ***

    # *** Select Statements ***
    private $getByIDStatementString = "select * from passwordsalt where employeeID = :employeeID";

    //insert statement - need to insert both an employeeID and passwordSalt
    private $insertStatementString = "insert into passwordsalt (employeeID, passwordSalt) values (:employeeID, :passwordSalt)";

    //update statement - only update a password salt's salt, not the employeeID, but need the ID to update it
    private $updateStatementString = "update passwordsalt set passwordSalt = :passwordSalt where employeeID = :employeeID";

    # *** Class statements ***
    private $getByIDStatement = null;
    private $insertStatement = null;
    private $updateStatement = null;

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

        $this->getByIDStatement = $conn->prepare($this->getByIDStatementString);
        if (is_null($this->getByIDStatement)) {
            throw new Exception("bad statement: '" . $this->getByIDStatementString . "'", 500);
        }

        $this->insertStatement = $conn->prepare($this->insertStatementString);
        if (is_null($this->insertStatement)) {
            throw new Exception("bad statement: '" . $this->insertStatementString . "'", 500);
        }

        $this->updateStatement = $conn->prepare($this->updateStatementString);
        if (is_null($this->updateStatement)) {
            throw new Exception("bad statement: '" . $this->updateStatementString . "'", 500);
        }
    }

    /**
     * Get the password salt with the specified employee ID.
     * 
     * @param Integer $id the ID of the password salt to retrieve 
     * @return password salt password salt object with the specified ID, or NULL if not found
     */
    public function getPasswordSaltByID($id)
    {
        $result = null;

        try {
            $this->getByIDStatement->bindParam(":employeeID", $id);
            $this->getByIDStatement->execute();

            //using fetch, not fetchAll
            $dbresults = $this->getByIDStatement->fetch(PDO::FETCH_ASSOC);

            if ($dbresults) {
                $employeeID = $dbresults['employeeID'];
                $passwordSalt = $dbresults['passwordSalt'];
                $result = new PasswordSalt($employeeID, $passwordSalt);
            }
        } catch (Exception $e) {
            $result = null;
        }
        //regardless - close the cursor and connection
        finally {
            if (!is_null($this->getByIDStatement)) {
                $this->getByIDStatement->closeCursor();
            }
        }

        //return an object or null
        return $result;
    }

    /**
     * Does a password salt exist (with the same ID)?
     * 
     * @param PasswordSalt $passwordSalt the password salt to check
     * @return boolean true if the item exists; false if not
     */
    public function passwordSaltExists($passwordSalt)
    {
        return $this->getPasswordSaltByID($passwordSalt->getEmployeeID()) !== null;
    }

    /**
     * Inserts a new password salt into the database.
     * 
     * @param PasswordSalt $passwordSalt an object of type PasswordSalt
     * @return boolean indicates if the passwordsalt was inserted
     */
    public function insertPasswordSalt(PasswordSalt $passwordSalt)
    {
        $success = false;

        //if password salt already exists, return false
        if ($this->passwordSaltExists($passwordSalt)) {
            return $success;
        }

        $employeeID = $passwordSalt->getEmployeeID();
        $passwordSaltField = $passwordSalt->getPasswordSalt();

        try {
            $this->insertStatement->bindParam(":employeeID", $employeeID);
            $this->insertStatement->bindParam(":passwordSalt", $passwordSaltField);

            //execute the statement and check that the row count is 1
            $success = $this->insertStatement->execute();
            $success = $this->insertStatement->rowCount() === 1;
        } catch (PDOException $e) {
            $success = false;
        }
        //regardless - close the cursor and connection
        finally {
            if (!is_null($this->insertStatement)) {
                $this->insertStatement->closeCursor();
            }
        }

        //return a boolean
        return $success;
    }

    /**
     * Updates a password salt in the database.
     * 
     * @param PasswordSalt $item an object of type PasswordSalt, the new values to replace the database's current values
     * @return boolean indicates if the password salt was updated
     */
    public function updatePasswordSalt(PasswordSalt $passwordSalt)
    {
        $success = false;

        //if password salt does NOT exist, return false
        if (!$this->passwordSaltExists($passwordSalt)) {
            return $success;
        }

        $employeeID = $passwordSalt->getEmployeeID();
        $passwordSaltField = $passwordSalt->getPasswordSalt();

        try {
            $this->updateStatement->bindParam(":passwordSalt", $passwordSaltField);
            $this->updateStatement->bindParam(":employeeID", $employeeID);

            //execute the statement and check that the row count is 1
            $success = $this->updateStatement->execute();
            $success = $this->updateStatement->rowCount() === 1;
        } catch (PDOException $e) {
            $success = false;
        }
        //regardless - close the cursor and connection
        finally {
            if (!is_null($this->updateStatement)) {
                $this->updateStatement->closeCursor();
            }
        }

        //return a boolean
        return $success;
    }
}
// end class password saltAccessor