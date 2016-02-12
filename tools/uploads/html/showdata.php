<?php

# access uploaded data

$sid  = $_GET['sid'];
$file = $_GET['file'];

#print "S: ".strlen($sid);

if (strlen($sid) != 30) exit(1);

if ( ($file == "cpuinfo.txt") or
     ($file == "dmesg.txt") or
     ($file == "lsusb.txt") or
     ($file == "version.txt") )  { 

	$content = file_get_contents("/var/www/uploads/".$sid."/".$file);
	print $content;
  }

?>
