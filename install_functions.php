<?php

function execute_sql_script($file_name)
{
	global $newLineChar;
	writeLogOutput("Executing SQL Script from " . $file_name. $newLineChar);
	$ret = true;
	if (!file_exists($file_name))
	{
		writeLogOutput("File <" . $file_name . "> not found $newLineChar");
		return false;
	}
	
	$sql_query = @file_get_contents($file_name);

	//$sql_query = explode(";", $sql_query);
	$sql_query = preg_split("/;\s*\n/", $sql_query); 
	foreach ($sql_query as $sql)
	{
		$sql = trim($sql);
		if (!empty($sql))
		{
			if (!mysql_query($sql))
			{
				writeLogOutput("\nERROR: Failed to execute query from " . $file_name . " $newLineChar");
				writeLogOutput("ERROR query: " . $sql . " $newLineChar");
				writeLogOutput("MySql Error: " . mysql_error() . " $newLineChar");
				$ret = false;
			}
		}
	}
	return $ret;
}

function connect_database()
{
	global $newLineChar;
	$db = mysql_connect(CH_DB_HOST, CH_DB_USER, CH_DB_PASSWORD);
	if (!$db){
	  die('ERROR: Failed to login database: ' . mysql_error());
	}
	
	if (!mysql_select_db(CH_DB_NAME))
	{
		writeLogOutput("Database could not be selected $newLineChar");
		return false;
	}
	
	return true;
}

function open_database()
{
	global $newLineChar;
	if (!mysql_select_db(CH_DB_NAME))
	{
		writeLogOutput("Database could not be selected $newLineChar");
		return false;
	}
	
	return true;
}

function disconnect_database()
{
	global $newLineChar;
	mysql_close();
	
	return true;
}

function getInput($msg){
	global $newLineChar;
	fwrite(STDOUT, "$msg: ");
	$varin = trim(fgets(STDIN));
	return $varin;
}

function writeLogOutput($message)
{
	global $logFile;
	echo $message;
	$str = str_replace("<br>", "\n", $message);
	file_put_contents($logFile, $str, FILE_APPEND);
}
?>

