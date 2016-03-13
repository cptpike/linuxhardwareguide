<?php

#echo "TEST";

$ip = $_SERVER['REMOTE_ADDR'];

#echo "IP: $ip";

if ( ($ip == "192.168.3.112") or
     ($ip == "192.168.3.113") or
     ($ip == "192.168.56.12") or
     ($ip == "192.168.13.65") or
     ($ip == "192.168.56.13") ) {

	$aid = $_GET ["aid"];
	$sid = $_GET ["sid"];
	$mode = $_GET ["mode"];
	$region = $_GET ["region"];
	$product = $_GET ["product"];

	#echo "AID: $aid SID: $sid Mode: $mode";


        if ($mode == "getprice") {

                if ($region == "") $region = "com";
		#print "<br>/home/ronny/LinuxHardwareGuide/wp-content/plugins/lhg-pricedb/backend/includes/amazon_price.pl -a $aid com <br>";
		system("/home/ronny/LinuxHardwareGuide/wp-content/plugins/lhg-pricedb/backend/includes/amazon_price.pl -a $aid $region 2> /tmp/test.txt", $output_tmp );
                #print "<br>OUT: $output";
                #$output = "<pre>".$output_tmp."</pre>";#InProgress";
        }elseif ( $mode == "productsearch"){
                print "Testsearch: $product";
		system("/home/ronny/LinuxHardwareGuide/wp-content/plugins/lhg-pricedb/backend/includes/amazon_search.pl $region \"$product\" 2> /tmp/test.txt", $output_tmp );
                # Debug
                #print "OUT: $output";
                #$output = "Debug: <pre>".$output_tmp."</pre>";#InProgress";

        }else{

		//insert into DB
		$output = system("/home/ronny/LinuxHardwareGuide/wp-content/plugins/lhg-pricedb/backend/filldb-shop-".$sid.".pl $aid");

	}
echo "$output";

} else  {
  echo "Requesting Host unknown. Ignoring";
}
?>
