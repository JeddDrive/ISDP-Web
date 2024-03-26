<?php
require_once dirname(__DIR__, 1) . '/entity/Site.php';

class SiteAccessor
{

    # *** Class SQL Strings ***

    # *** Select Statements ***

    //get all sites
    private $getAllStatementString = "select siteID, name, provinceID, address, IFNULL(address2, '') as address2, city, country, postalCode, phone, IFNULL(dayOfWeek, '') as dayOfWeek, distanceFromWH, IFNULL(notes, '') as notes, active from site";

    //get only the stores/retail sites (ex. only sites that do and fulfill online orders)
    private $getAllStoresStatementString = "select siteID, name, provinceID, address, IFNULL(address2, '') as address2, city, country, postalCode, phone, IFNULL(dayOfWeek, '') as dayOfWeek, distanceFromWH, IFNULL(notes, '') as notes, active from site where name LIKE '%Retail%'";

    private $getByIDStatementString = "select siteID, name, provinceID, address, IFNULL(address2, '') as address2, city, country, postalCode, phone, IFNULL(dayOfWeek, '') as dayOfWeek, distanceFromWH, IFNULL(notes, '') as notes, active from site where siteID = :siteID";

    # *** Class Statements ***
    private $getAllStatement = null;
    private $getAllStoresStatement = null;
    private $getByIDStatement = null;

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

        $this->getAllStoresStatement = $conn->prepare($this->getAllStoresStatementString);
        if (is_null($this->getAllStoresStatement)) {
            throw new Exception("bad statement: '" . $this->getAllStoresStatementString . "'", 500);
        }

        $this->getByIDStatement = $conn->prepare($this->getByIDStatementString);
        if (is_null($this->getByIDStatement)) {
            throw new Exception("bad statement: '" . $this->getByIDStatementString . "'", 500);
        }
    }
    //insert methods here

    /**
     * Retrieves all of the sites in the DB. Including the warehouse, warehouse bay, etc.
     * 
     * @return Site[] an array of Site objects
     */
    public function getAllSites()
    {
        //results array to be returned
        $results = [];

        try {
            $this->getAllStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getAllStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                $siteID = $r['siteID'];
                $name = $r['name'];
                $provinceID = $r['provinceID'];
                $address = $r['address'];
                $address2 = $r['address2'];
                $city = $r['city'];
                $country = $r['country'];
                $postalCode = $r['postalCode'];
                $phone = $r['phone'];
                $dayOfWeek = $r['dayOfWeek'];
                $distanceFromWH = $r['distanceFromWH'];
                $notes = $r['notes'];
                $active = $r['active'];

                //construct site object
                $siteObj = new Site($siteID, $name, $provinceID, $address, $address2, $city, $country, $postalCode, $phone, $dayOfWeek, $distanceFromWH, $notes, $active);

                //push object into results array
                array_push($results, $siteObj);
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
     * Retrieves all of the stores in the DB. This EXCLUDES the warehouse, warehouse bay, etc.
     * 
     * @return Site[] an array of Site objects
     */
    public function getAllStores()
    {
        //results array to be returned
        $results = [];

        try {
            $this->getAllStoresStatement->execute();

            //using fetchAll, not fetch
            $dbresults = $this->getAllStoresStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbresults as $r) {
                $siteID = $r['siteID'];
                $name = $r['name'];
                $provinceID = $r['provinceID'];
                $address = $r['address'];
                $address2 = $r['address2'];
                $city = $r['city'];
                $country = $r['country'];
                $postalCode = $r['postalCode'];
                $phone = $r['phone'];
                $dayOfWeek = $r['dayOfWeek'];
                $distanceFromWH = $r['distanceFromWH'];
                $notes = $r['notes'];
                $active = $r['active'];

                //construct site object
                $siteObj = new Site(
                    $siteID,
                    $name,
                    $provinceID,
                    $address,
                    $address2,
                    $city,
                    $country,
                    $postalCode,
                    $phone,
                    $dayOfWeek,
                    $distanceFromWH,
                    $notes,
                    $active
                );

                //push object into results array
                array_push($results, $siteObj);
            }
        } catch (Exception $e) {
            $results = [];
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getAllStoresStatement)) {
                $this->getAllStoresStatement->closeCursor();
            }
        }

        return $results;
    }

    /**
     * Gets the site with the specified site ID from the DB.
     * 
     * @param Integer the site ID wanted to retrieve 
     * @return Site Site object with the specified site ID, or null if not found
     */
    public function getSiteByID($siteID)
    {
        //initialize object to be returned to null
        $siteObj = null;

        try {

            //bindParam replaces the colon value with it's actual variable
            $this->getByIDStatement->bindParam(":siteID", $siteID);
            $this->getByIDStatement->execute();

            //using fetch, not fetchAll (want a single item)
            $r = $this->getByIDStatement->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $siteID = $r['siteID'];
                $name = $r['name'];
                $provinceID = $r['provinceID'];
                $address = $r['address'];
                $address2 = $r['address2'];
                $city = $r['city'];
                $country = $r['country'];
                $postalCode = $r['postalCode'];
                $phone = $r['phone'];
                $dayOfWeek = $r['dayOfWeek'];
                $distanceFromWH = $r['distanceFromWH'];
                $notes = $r['notes'];
                $active = $r['active'];

                //construct site object
                $siteObj = new Site($siteID, $name, $provinceID, $address, $address2, $city, $country, $postalCode, $phone, $dayOfWeek, $distanceFromWH, $notes, $active);
            }
        } catch (Exception $e) {
            $siteObj = null;
        }
        //finally - regardless of success, close the cursor (and connection)
        finally {
            if (!is_null($this->getByIDStatement)) {
                $this->getByIDStatement->closeCursor();
            }
        }

        //return the object
        return $siteObj;
    }
}
