<?php

# This script is used to receive upload requests by the lhg_hwscan client

#echo "TEST";

$version = $_GET['v'];
$ip = $_SERVER['REMOTE_ADDR'];


if ($version == "0.2") { 

        #create Session ID
	$rndstrg = substr(md5(rand()), 0, 30);
	echo $rndstrg;
        system("mkdir /var/www/uploads/".$rndstrg);

	$date = date_create();
        file_put_contents( "/var/www/uploads/".$rndstrg."/sessiontime", date_timestamp_get($date) );

} else  {

	echo "Unknown request. Ignoring";
}

?>
