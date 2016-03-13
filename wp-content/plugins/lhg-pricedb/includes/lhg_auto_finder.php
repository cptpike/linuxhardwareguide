<?php

#
# Automatically find amazon articles corresponding to submitted hardware.
# Extract hardware properties from amazon article
#

//find Amazon data based on uploaded product
add_action('wp_ajax_lhg_amazon_finder_ajax', 'lhg_amazon_finder_ajax');

function lhg_amazon_finder_ajax() {

                #global $lhg_amazon_title; #stores the title received from AMAZON

                $search_region = "com"; #which AMAZON server to ask
                $pid = absint( $_REQUEST['pid'] );
                $id = absint( $_REQUEST['id'] );
                $session = $_REQUEST['session'] ;


                # type is identified by article category
                # i.e. "cpu", "mainboard",...

                $type = lhg_get_autocreate_mode( $pid );
                #error_log("Type: $type");
                if ($type == "") $type = "drive";

                #ToDo: set correct type
                #$type = "cpu";

		#$output = lhg_aws_get_price($asin,"com");
                #list($image_url_com, $product_url_com, $price_com , $product_title) = split(";;",$output);

                #print "Search results for TITLE HERE<br>...";
                #print "PID: $pid - ID: $id - Session: $session<br>";

                $title = get_the_title ( $pid );
                # drive titles are sometimes shortened -> repair them
                if ($type == "drive") {
                	$searchstring = lhg_search_string_from_drive_title ( $title, $session );
	                $title_mod = str_replace(" ","__",$searchstring);
		}

                if ($type == "cpu") {
        	        $title_mod = str_replace(" ","__",$title);
		}
                $tmp = explode("(",$title_mod);
                #$title_mod = $tmp[0];
                #error_log("Tit: $title_mod");
                $returnarray = lhg_amazon_search_by_title ( $title_mod , $search_region );

                #var_dump($returnarray);

                if ( isset($returnarray[0]) ) $asin1  = $returnarray[0];
                if ( isset($returnarray[1]) ) $title1 = $returnarray[1];
                if ( isset($returnarray[2]) ) $asin2  = $returnarray[2];
                if ( isset($returnarray[3]) ) $title2 = $returnarray[3];
                if ( isset($returnarray[4]) ) $asin3  = $returnarray[4];
                if ( isset($returnarray[5]) ) $title3 = $returnarray[5];

	       	$output1 = lhg_aws_get_price($asin1, $search_region);
	       	$output2 = lhg_aws_get_price($asin2, $search_region);
	       	$output3 = lhg_aws_get_price($asin3, $search_region);

                #var_dump($output1);

	        list($image_url_com1, $product_url_com1, $price_com1 , $product_title) = split(";;",$output1);
	        list($image_url_com2, $product_url_com2, $price_com2 , $product_title) = split(";;",$output2);
	        list($image_url_com3, $product_url_com3, $price_com3 , $product_title) = split(";;",$output3);

        	$image_url_com1     = str_replace("Image: ","", $image_url_com1);
        	$image_url_com2     = str_replace("Image: ","", $image_url_com2);
        	$image_url_com3     = str_replace("Image: ","", $image_url_com3);

        	$product_url_com1     = str_replace("URL: ","", $product_url_com1);
        	$product_url_com2     = str_replace("URL: ","", $product_url_com2);
        	$product_url_com3     = str_replace("URL: ","", $product_url_com3);

        	$price_com1     = str_replace("Price: ","", $price_com1);
        	$price_com2     = str_replace("Price: ","", $price_com2);
        	$price_com3     = str_replace("Price: ","", $price_com3);


                #, $title1, $asin2, $title2, $asin3, $title3) = $returnarray;

                print "
  <input type=\"hidden\" id=\"PID-$id\" value=\"$pid\">
<table id=\"amazon-finder-$id\">
";

#  <tr>
#  	<td>Image</td>
#        <td>Title</td>
#        <td>Price</td>
#        <td>ASIN</td>
#        <td>select</td>
#  </tr>


                print "<tr><td><a href=\"$product_url_com1\"><img src=\"$image_url_com1\" width=\"100\"></a></td><td>$title1
                <br>Price: $price_com1<br>ASIN: $asin1</td>
<td>
  <input type=\"hidden\" id=\"ASIN1-$id\" value=\"$asin1\">
  <input type=\"hidden\" id=\"IMGURL1-$id\" value=\"$image_url_com1\">
  <input type=\"hidden\" id=\"PRODURL1-$id\" value=\"$product_url_com1\">
  <input type=\"hidden\" id=\"TITLE1-$id\" value=\"$title1\">
  <a href=\"\" id=\"autocreate-1-$id\">select</a>
</td></tr>"; #, $title1<br>";

if ($asin2 != "") print "<tr><td><a href=\"$product_url_com2\"><img src=\"$image_url_com2\" width=\"100\"></a></td><td>$title2
		<br>Price: $price_com2<br>ASIN: $asin2</td>

<td>
  <input type=\"hidden\" id=\"ASIN2-$id\" value=\"$asin2\">
  <input type=\"hidden\" id=\"IMGURL2-$id\" value=\"$image_url_com2\">
  <input type=\"hidden\" id=\"PRODURL2-$id\" value=\"$product_url_com2\">
  <input type=\"hidden\" id=\"TITLE2-$id\" value=\"$title2\">
  <a href=\"\" id=\"autocreate-2-$id\">select</a>
</td></tr>"; #, $title1<br>";


if ($asin2 != "") print "<tr><td><a href=\"$product_url_com3\"><img src=\"$image_url_com3\" width=\"100\"></a></td><td>$title3
<br>Price: $price_com3<br>ASIN: $asin3</td>

<td>
  <input type=\"hidden\" id=\"ASIN3-$id\" value=\"$asin3\">
  <input type=\"hidden\" id=\"IMGURL3-$id\" value=\"$image_url_com3\">
  <input type=\"hidden\" id=\"PRODURL3-$id\" value=\"$product_url_com3\">
  <input type=\"hidden\" id=\"TITLE3-$id\" value=\"$title3\">
  <a href=\"\" id=\"autocreate-3-$id\">select</a>
</td></tr>"; #, $title1<br>";
                #print "ASIN2: $asin2, $title2<br>";
                #print "ASIN3: $asin3, $title3<br>";
                #print "Searchresult: $result<br>";
print "</table>";

                die();
}

#
# Update an article based on the ASIN returned by the search tool
# 
#

//find Amazon data based on uploaded product
add_action('wp_ajax_lhg_update_article_by_amazon_search', 'lhg_update_article_by_amazon_search');

function lhg_update_article_by_amazon_search() {


                $search_region = "com"; #which AMAZON server to ask
                $pid = absint( $_REQUEST['pid'] );
                $id = absint( $_REQUEST['id'] );
                $session = $_REQUEST['session'] ;
                $mode = lhg_get_autocreate_mode($pid); # first implementation for CPU only

                $asin[1] = $_REQUEST['asin1'] ;
                $asin[2] = $_REQUEST['asin2'] ;
                $asin[3] = $_REQUEST['asin3'] ;

                $title[1] = $_REQUEST['title1'] ;
                $title[2] = $_REQUEST['title2'] ;
                $title[3] = $_REQUEST['title3'] ;

                $produrl[1] = $_REQUEST['produrl1'] ;
                $produrl[2] = $_REQUEST['produrl2'] ;
                $produrl[3] = $_REQUEST['produrl3'] ;

                $imgurl[1] = $_REQUEST['imgurl1'] ;
                $imgurl[2] = $_REQUEST['imgurl2'] ;
                $imgurl[3] = $_REQUEST['imgurl3'] ;

                #$asin1 = $_REQUEST['asin1'] ;
                #$asin2 = $_REQUEST['asin2'] ;
                #$asin3 = $_REQUEST['asin3'] ;
                $option = $_REQUEST['option'] ;
                $type = $_REQUEST['type'] ; # i.e. "cpu", "mainboard",...

                #print "Title: ".$title[$option]."<br>IURL: ".$imgurl[$option]."
                #<br>PURL: ".$produrl[$option]."<br> ASIN: ".$asin[$option]."<br>";

                #error_log("DEB: ".$title[$option]);

                # Update tags by Amazon title
                lhg_update_tags_by_string($pid, $title[$option], $mode);
                lhg_update_categories_by_string($pid, $title[$option], $mode);
                lhg_update_title_by_string($pid, $title[$option], $mode);

                if ($mode == "drive") lhg_correct_drive_name($pid, $session);

                die();

}


function lhg_amazon_search_by_title( $title, $region ) {

        #get  price from LHG-PriceDB via URL
        #      http://192.168.3.114/lhgupdatedb.php?mode=getprice&aid=B000EHIA06
        #returns URL, Image, Price

	if ( ($_SERVER['SERVER_ADDR'] == "192.168.56.12") or ($_SERVER['SERVER_ADDR'] == "192.168.56.13") )
	$lhg_price_db_ip = "192.168.56.14";

	if ( ($_SERVER['SERVER_ADDR'] == "192.168.3.112") or ($_SERVER['SERVER_ADDR'] == "192.168.3.113") )
	$lhg_price_db_ip = "192.168.3.114";

        $product=sanitize_text_field($title);
        $searchresult = file_get_contents('http://'.$lhg_price_db_ip.'/lhgupdatedb.php?mode=productsearch&region='.$region.'&product='.$product);
        #error_log("Prod: $product");

        # parse result
        $resultarray = explode("\n",$searchresult,100);

        foreach ($resultarray as $result) {
                #print "Res: $result<br>";
                #print "POS: ".strpos($result,"Title1:")."<br>";

                if (strpos($result,"itle1:") == 1 ) $title1=substr($result,7);
                if (strpos($result,"itle2:") == 1 ) $title2=substr($result,7);
                if (strpos($result,"itle3:") == 1 ) $title3=substr($result,7);

                if (strpos($result,"SIN1:") == 1 ) $asin1=substr($result,7);
                if (strpos($result,"SIN2:") == 1 ) $asin2=substr($result,7);
                if (strpos($result,"SIN3:") == 1 ) $asin3=substr($result,7);

	}

        $searchresult = array( $asin1, $title1, $asin2, $title2, $asin3, $title3);
        return $searchresult;
}

function lhg_get_correct_drive_name ( $title, $sid ) {

	global $dmesg_content_from_library;

        if ( $dmesg_content_from_library == "" ) {
          #error_log( "Download: $sid" );
	  $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=dmesg.txt";
	  $dmesg_content_from_library = file_get_contents($url);
	}

	$dmesg_content_array = split("\n",$dmesg_content_from_library);
        #error_log( "Count: ".count($dmesg_content_array) );

        # Seagate drives - remove
        if ( strpos($title,"Seagate") !== false )  {

                # extract main ID of HDD
                $part1 = "Seagate";
                $searchfor = str_replace("Seagate ","", $title);
                #get HD name
                if (strpos($searchfor,"-") !== false) {
                	$tmp = explode("-",$searchfor);
                        $searchfor = $tmp[0];
		}

                # check for full name
                $length = strlen($searchfor);
		$searchresult_array = preg_grep("/".$searchfor."/i",$dmesg_content_array);
                foreach($searchresult_array as $line) {
                        $words = explode(" ",$line);
			$words_filtered = preg_grep("/".$searchfor."/i",$words);
                        foreach ($words_filtered as $word) {
                                if (substr($word,-1) == ",") $word = substr($word,0,-1);
                                # check if we have more details
                                if ( strlen($word) > $length ) {
                                        $length = strlen($word);
                                        $searchfor = $word;
				}
			}
                	#error_log("found $line");
		}
                $part2 = $searchfor;
	        $title = $part1." ".$part2;
        }else {
                #error_log("OT: $title");
                # Misc corrections
                $title = str_replace(" A "," ",$title);
                $title = str_replace("hp ","HP ",$title);
                $title = str_replace(" DVD "," ",$title);
                $title = str_replace("  "," ",$title);
	}

  	//$dmi_line = implode("\n",$dmi_array);
  	//$dmi_line = str_replace("[    0.000000]","",$dmi_line);


        #error_log("Real name: $title");

        return $title;
}

function lhg_correct_drive_name ( $pid, $sid ) {
        # updated drive title
        $title = get_the_title( $pid );
        $tmp = explode("(",$title);
        $title_main = $tmp[0];
        $title_props = " (".$tmp[1];

        $corr = lhg_get_correct_drive_name ( $title_main , $sid ).$title_props;
	$corr = str_replace("  "," ",$corr);
        #error_log("Corr: $title_main -> $corr");

        $my_post = array(
      		'ID'           => $pid,
      		'post_title'   => $corr
 	 );
         wp_update_post ( $my_post );

}


function lhg_search_string_from_drive_title ( $title, $sid ) {

        # could return multiple possible search strings
        # currently only one implemented.

        # use drive name correction
        $title = lhg_get_correct_drive_name ( $title, $sid);
        return $title;

}

# Which type of article are we working on?
# cpu, drive, ...
function lhg_get_autocreate_mode ( $pid ) {

                $categories = get_the_category( $pid );
                foreach ($categories as $category) {
                        if ( $category->term_id == 874 ) $type = "cpu";
                        if ( $category->term_id == 478 ) $type = "drive";
		}

        return $type;
}

?>
