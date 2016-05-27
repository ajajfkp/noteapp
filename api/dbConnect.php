<?php

class dbConnect {
    private $conn;
    private $dbcon;
    function __construct() {
		
    }
	    /**
     * Establishing database connection
     * @return database connection handler
     */
    function connect() {
        // Connecting to mysql database
        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        // Check for database connection error
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        // returing connection resource
        return $this->conn;
    }
	
	function connectDb(){
		return $this->dbcon = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
	}
	
	function selectDb($dbcon){
		return mysqli_select_db($dbcon, DB_NAME);
	}
	

}

?>