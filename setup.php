<?php
set_time_limit(0);
require_once('config.php');
require_once('api/dbConnect.php');
// opening db connection
$db = new dbConnect();
$dbcon = $db->connectDb();

//create backup directeru if not createed
$newLineChar = "\n";
if (!is_dir("backup")){
	mkdir("backup");
}

$datetime = new DateTime(); 
$logFile = getcwd() . "\\Backup\\" . DB_NAME . "_install_log_" . $datetime->format('Y_m_d_H_i_s') . ".txt";

if (php_sapi_name() != "cli") {
	$newLineChar = "<br>";
	writeLogOutput("<h1>Can not Install database from Web Interface</h1>" . $newLineChar);
	die;
}

writeLogOutput("**************Connecting Database********************* $newLineChar");

//check existing database connection
$Dbsel = $db->selectDb($dbcon);
if ($Dbsel == true) {
	writeLogOutput("$newLineChar $newLineChar Database " . DB_NAME . " already exist $newLineChar $newLineChar");
	$reinstall = getInput("Current Database will be drop..... Do you really want to recreate database [Yes|No]");
	if (strtolower($reinstall) == "yes") {
		writeLogOutput("Current Database will be wiped out. $newLineChar");
		writeLogOutput("Drop Database " . DB_NAME . " $newLineChar");
		$sql1 = "Drop database if exists " . DB_NAME;
		$res1 = mysqli_query($dbcon, $sql1);
		if (!$res1) {
			writeLogOutput("Failed to execute query: " . $sql1 . " Error: " . mysqli_error($dbcon));
		}
	}else{
		writeLogOutput("Good Choice not to wipe out the current database. Thanks $newLineChar");
		die;
	}
}

//create database if not exist
writeLogOutput("Create Database " . DB_NAME . " $newLineChar");
$sql2 = "Create database if not exists " . DB_NAME;
$res2 = mysqli_query($dbcon, $sql2);
if (!$res2) {
	writeLogOutput("Failed to execute query: " . $sql2 . " Error: " . mysqli_error($dbcon));
}

//open connection of curront database
$Dbsel = $db->selectDb($dbcon);


//create users table if not exist
$sql3 ="CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `passwd` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4";
$res3 = mysqli_query($dbcon, $sql3);
if (!$res3) {
	writeLogOutput("Failed to execute query: " . $sql3 . " Error: " . mysqli_error($dbcon));
}

//inserting record in users table
$sql4 = "INSERT INTO `users` (`id`, `name`, `username`, `passwd`) VALUES
(1, 'Alice', 'Alice', '202cb962ac59075b964b07152d234b70'),
(2, 'Bob', 'Bob', '202cb962ac59075b964b07152d234b70'),
(3, 'Charlie', 'Charlie', '202cb962ac59075b964b07152d234b70')";

$res4 = mysqli_query($dbcon, $sql4);
if (!$res4) {
	writeLogOutput("Failed to execute query: " . $sql4 . " Error: " . mysqli_error($dbcon));
}


//create usersnote table if not exist
$sql5 = "CREATE TABLE IF NOT EXISTS `usersnote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `priority` enum('1','2','3') NOT NULL DEFAULT '2',
  `added_by` int(11) NOT NULL,
  `delete_flag` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35";

$res5 = mysqli_query($dbcon, $sql5);
if (!$res5) {
	writeLogOutput("Failed to execute query: " . $sql5 . " Error: " . mysqli_error($dbcon));
}


$id = array("1"=>"1","2"=>"2","3"=>"3");
$name = array("1"=>"Alice","2"=>"Bob","3"=>"Charlie");
$note = "";
$title="";
$sql = "";
$count = 0;
for($i=1;$i<=1000;$i++){
	for($j=1;$j<=3;$j++){
		$count++;
		$title  = "Random Title by ".$name[$j];
		$note = "A wonderful serenity has taken possession of " . $name[$j] . " entire soul, like these sweet mornings of spring which " . $name[$j] . " enjoy with his whole heart. " . $name[$j] . " is alone, and feel the charm of existence in this spot, which was created for the bliss of souls like mine. " . $name[$j] . " is so happy";
		
		$query = "INSERT INTO usersnote (id,title,notes,priority,added_by) VALUES ('".$count."','".$title."','".$note."','".mt_rand(1,3)."','".$id[$j]."')";
		$r = mysqli_query($dbcon, $query) or die($conn->error.__LINE__);
		if($r){
			echo $count ." record for ". $name[$j] ." has been insurted successfully"."\n";
		}
	}
}


function getInput($msg) {
	global $newLineChar;
	fwrite(STDOUT, "$msg: ");
	$varin = trim(fgets(STDIN));
	return $varin;
}

function writeLogOutput($message) {
	global $logFile;
	echo $message;
	$str = str_replace("<br>", "\n", $message);
	file_put_contents($logFile, $str, FILE_APPEND);
}


?>
