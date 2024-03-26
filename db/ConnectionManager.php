<?php

class ConnectionManager
{
    private $connectionString;
    private $username;
    private $password;
    private $conn;

    public function __construct($connectionString, $username, $password)
    {
        $this->connectionString = $connectionString;
        $this->username = $username;
        $this->password = $password;
        $this->conn = null;
    }

    public function getConnection()
    {
        $this->conn = new PDO($this->connectionString, $this->username, $this->password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $this->conn;
    }

    public function closeConnection()
    {
        $this->conn = null;
    }
}
