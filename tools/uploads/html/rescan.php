<?php

# access uploaded data

$sid  = $_GET['sid'];

#print "S: ".strlen($sid);

$sid = str_replace("/","",$sid);

if (strlen($sid) != 30) exit(1);

	if  ($_SERVER['SERVER_ADDR'] == "192.168.56.15")
	$scan_url = "www.linux-hardware-guide.com";

	if ($_SERVER['SERVER_ADDR'] == "192.168.3.115") 
	$scan_url = "192.168.3.113";

	$ref=@$_SERVER[HTTP_REFERER];
	echo "Referrer of this page  = $ref <br>";
        echo "(short: ".substr($ref,0,21)."<br>";

if ( (substr($ref,0,21) == "http://192.168.3.113/") or
     (substr($ref,0,21) == "http://192.168.56.13/") or
     (substr($ref,0,21) == "http://www.linux-hard") )
{

        print "executing rescan<br>";
        $out = shell_exec('cd /var/www/uploads/ && ./scan_hw_upload.pl '.$sid.' -r');
        print "OUT:<pre> $out </pre>";
	if ( strpos( $ref, "/scan-") > 0 ) {
        	print '<a href="http://'.$scan_url.'/hardware-profile/scan-'.$sid.'">back to HW overview</a><br>';
        } else {
        	print '<a href="http://'.$scan_url.'/hardware-profile/editscan-'.$sid.'">back to HW overview</a><br>';
	}
}


?>
