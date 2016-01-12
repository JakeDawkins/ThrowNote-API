<?php

class Database {
	//MySQLi connection
	protected static $connection;

	/*
	*	Connect to the DB.
	*
	*	@return false on failure / true on success
	*/
	public function connect(){
		if(!isset(self::$connection)){
			//require("../db-settings.php");
			require(Path::dbSettings());
			self::$connection = new mysqli($db_host,$db_user,$db_pass,$db_name);
		}

		//handle any errors
		if(self::$connection->connect_errno > 0){
			echo "Database: [" . self::$connection->connect_error . "]<br />";
			return false;
		}

		return self::$connection;
	}//connect

	/* 
	*	returns a clean and escaped query string
	*
	*	@params SQL query string pattern, with '?' in place of values
	*			list of values to fill '?' with, separated by commas
	*
	*	@return string
	*
	*	usage: prepareQuery("SELECT * FROM `table` WHERE `field` = ?", "value");
	*/
	public function prepareQuery()
	{
		// Connect to the database
		//for mysqli escaping
        $connection = $this -> connect();


	    $Query = "";
	    $ParameterNumber = 0;

	    if (func_num_args() && $Query = func_get_arg($ParameterNumber++)) {
	        while ($ParameterNumber < func_num_args()) {
	            $NextParameter = func_get_arg($ParameterNumber++);
	            $PlaceToInsertParameter = strpos($Query, '?');
	            if ($PlaceToInsertParameter !== false) {
	                $QuerySafeString = '';
	                if (is_bool($NextParameter)) {
	                   	$QuerySafeString = $NextParameter ? 'TRUE' : 'FALSE';
	                } else if (is_float($NextParameter) || is_int($NextParameter)) {
	                    $QuerySafeString = $NextParameter;
	                } else if (is_null($NextParameter)) {
	                    $QuerySafeString = 'NULL';
	                } else {
	                    $QuerySafeString = "'" . $connection->escape_string($NextParameter) . "'";
	                }

	                $Query = substr_replace($Query, $QuerySafeString, $PlaceToInsertParameter, 1);
	            }
	        }
	    }

	    return $Query;
	}

	/*
	*	Query the DB
	*
	*	@param sql - query string
	*	@return - query results
	*/
	public function query($sql){
        // Connect to the database
        $connection = $this -> connect();

        // Query the database
        $result = $connection -> query($sql);

        return $result;
	}

	/*
	*	Fetch rows from the DB (SELECT)
	*
	*	@param $sql - query string
	*	@return bool false on failure / array Database rows on success
	*/
	public function select($sql){
        $rows = array();
        $result = $this -> query($sql);

        //no results from query
        if($result === false) {
            return false;
        }

        //parse results
        while ($row = $result -> fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
	}
}//class


?>
