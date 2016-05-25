<?php
require_once('api/dbConnect.php');
// opening db connection
$db = new dbConnect();
$conn = $db->connect();

$r3 = $conn->query("CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `passwd` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");

$r4 = $conn->query("INSERT INTO `users` (`id`, `name`, `username`, `passwd`) VALUES
(1, 'Alice', 'Alice', '202cb962ac59075b964b07152d234b70'),
(2, 'Bob', 'Bob', '202cb962ac59075b964b07152d234b70'),
(3, 'Charlie', 'Charlie', '202cb962ac59075b964b07152d234b70')");

$r5 = $conn->query("CREATE TABLE IF NOT EXISTS `usersnote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `priority` enum('1','2','3') NOT NULL DEFAULT '2',
  `added_by` int(11) NOT NULL,
  `delete_flag` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35");


$id = array("1"=>"1","2"=>"2","3"=>"3");
$name = array("1"=>"Alice","2"=>"Bob","3"=>"Charlie");
$note = "";
$title="";
$sql = "";
$count = 0;
for($i=1;$i<=30;$i++){
	for($j=1;$j<=3;$j++){
		$count++;
		$title  = "Random Title by ".$name[$j];
		$note = "A wonderful serenity has taken possession of " . $name[$j] . " entire soul, like these sweet mornings of spring which " . $name[$j] . " enjoy with his whole heart. " . $name[$j] . " is alone, and feel the charm of existence in this spot, which was created for the bliss of souls like mine. " . $name[$j] . " is so happy";
		
		$query = "INSERT INTO usersnote (id,title,notes,priority,added_by) VALUES ('".$count."','".$title."','".$note."','".mt_rand(1,3)."','".$id[$j]."')";
		$r = $conn->multi_query($query) or die($conn->error.__LINE__);
		if($r){
			echo $count ." record for ". $name[$j] ." has been insurted successfully"."\n";
		}
	}
}

?>
