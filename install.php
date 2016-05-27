<?php

/*
** install.php - Will install the fresh database
*/
require_once("../config.php");
require_once("install_functions.php");

if (!is_dir("Backup"))
	mkdir("Backup");

$datetime = new DateTime(); 
$logFile = getcwd() . "\\Backup\\" . CH_DB_NAME . "_install_log_" . $datetime->format('Y_m_d_H_i_s') . ".txt";

$option = "interactive";
if (isset($argv[1]))
	$option = $argv[1];

$newLineChar = "\n";
$instMode = 0;
	
set_time_limit(0);

if (php_sapi_name() != "cli")	
{
	$option = "--silent";
	$instMode = 1;
	$newLineChar = "<br>";
	writeLogOutput("Can not Install database from Web Interface" . $newLineChar);
	die;
}
else
{
	writeLogOutput("Install from Command Line" . $newLineChar);
}

writeLogOutput("**************Connecting Database********************* $newLineChar");
if (connect_database() == true)
{
	if ($instMode == 1)
	{
		$sql = "SHOW TABLES FROM " . CH_DB_NAME;
		$result = mysql_query($sql);

		if ($result) 
		{
			if (mysql_num_rows($result) > 0)
			{
				writeLogOutput("Database contains data tables. Can not recreate via Web Interface $newLineChar");
				writeLogOutput("Please try upgrade $newLineChar");
				writeLogOutput("**************Disconnecting Database**************** $newLineChar");
				disconnect_database();
				die;
			}
		}
	}
	
	writeLogOutput("$newLineChar $newLineChar Database " . CH_DB_NAME . " already exist $newLineChar $newLineChar");
	$reinstall = "no";
	if ($option == "--silent")
		$reinstall = "yes";
	else
		$reinstall = getInput("Do you really want to recreate database [Yes|No]");
	if (strtolower($reinstall) == "yes")
	{		
		writeLogOutput("Current Database will be wiped out. Taking Backup... $newLineChar");
		$backupDir = false;
		if (is_dir("Backup"))
		{
			writeLogOutput("Backup Directory exist $newLineChar");
			$backupDir = true;
		}
		else
		{
			if (mkdir("Backup"))
			{
				writeLogOutput("Backup Directory created $newLineChar");
				$backupDir = true;
			}
			else
			{
				writeLogOutput("Failed to create Backup directory $newLineChar");
				$backupDir = false;
			}
		}
		if ($backupDir == true)
			$backupFolder = "Backup\\" . CH_DB_NAME . "_data_" . $datetime->format('Y_m_d_H_i_s');
		else
			$backupFolder = CH_DB_NAME . "_data_" . $datetime->format('Y_m_d_H_i_s');
		

		writeLogOutput("Taking existing database dump to " . $backupFolder . " file $newLineChar");
		writeLogOutput("This operation may take some time.... $newLineChar");

		$sql = "SHOW TABLES FROM " .CH_DB_NAME;
		$result =  mysql_query($sql);
		if(mysql_num_rows($result) > 0) 
		{
			$dir = 'Backup\\'.CH_DB_NAME.'_install_data_'.$datetime->format('Y_m_d_H_i_s');
			mkdir($dir, 0777);  
				
			while($row = mysql_fetch_array($result)){
				$tableName = $row[0];
				$fileName = $dir."\\".$row[0].".sql";
				$cmd = "mysqldump --user=" . CH_DB_USER . " --password=" . CH_DB_PASSWORD . " --host=" . CH_DB_HOST . " " . CH_DB_NAME ." ". $tableName . " > " . $fileName;
				//echo $cmd."<br>";
				exec($cmd);
			}
		}

		//$cmd = "mysqldump --user=" . CH_DB_USER . " --pass=" . CH_DB_PASSWORD . " --host=" . CH_DB_HOST . " " . CH_DB_NAME . " > " . $backupFile;
		//exec($cmd);
	}
	else
	{
		writeLogOutput("Good Choice not to wipe out the current database. Thanks $newLineChar");
		writeLogOutput("**************Disconnecting Database**************** $newLineChar");
		disconnect_database();
		die;
	}
}

writeLogOutput("Drop Database " . CH_DB_NAME . " $newLineChar");
$sql = "Drop database if exists " . CH_DB_NAME;
if (!mysql_query($sql))
{
	writeLogOutput("Failed to execute query: " . $sql . " Error: " . mysql_error());
}

writeLogOutput("Create Database " . CH_DB_NAME . " $newLineChar");
$sql = "Create database if not exists " . CH_DB_NAME;
if (!mysql_query($sql))
{
	writeLogOutput("Failed to execute query: " . $sql . " Error: " . mysql_error());
}

writeLogOutput("**************Opening Database*********************** $newLineChar");
if (open_database() == false)
{
	writeLogOutput("Database " . CH_DB_NAME . " does not exist $newLineChar");
	die;
}

$dbInfo = array (
			"basedb" => array (
						"path" => "3.0.30",
						"script" => "install_base.php",
						"desc" => "Loading base structure and data",
					),
			
		);
		
foreach($dbInfo as $db)
{
	$cwd = getcwd();
	chdir($db['path']);

	writeLogOutput("****************" . $db['desc'] . "************** $newLineChar");
	
	require_once($db['script']);
	chdir($cwd);
}

writeLogOutput("**************Disconnecting Database**************** $newLineChar");
disconnect_database();

writeLogOutput("Applying updates $newLineChar");

require_once("update.php");

?>


