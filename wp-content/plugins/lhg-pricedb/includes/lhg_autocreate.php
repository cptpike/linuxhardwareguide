<?php

# lhg_create_cpu_article


function lhg_create_cpu_article ($title, $sid, $id ) {
  # returns the post id of created or already existing draft article

  global $lhg_price_db;
  global $cpus_from_library;
  global $lang;

  # article creation should be limited to .com server
  if ($lang == "de") {
                error_log("Article creation on .de server should not happen. ID: $id");
                return;
  }

  # check 1:
  # see if article was already created based on article ID
  #error_log("CPU ID: $id");
  $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
  $created_id = $lhg_price_db->get_var($sql);
  if ($created_id != 0) return $created_id;

  # check 2:
  # see if article was already created based on title
  # Use clean CPU title
  #
  $title = lhg_clean_cpu_title($title);
  $page = get_page_by_title( $title );
  if ( ( $page->ID != "") && is_page($page->ID) ) return $page->ID;
  #if ( is_page($page->ID) ) return $page->ID;


  $category = 874;
  $taglist = array( 874);

  # Download only once for speed improvement
  if ( $cpus_from_library == "" ) {
	$url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=cpuinfo.txt";
	$content = file_get_contents($url);
	$cpus_from_library = explode("\n\n",$content);
  }

  #print "<br>Dump:".var_dump($cpus)."<br>";
  $cpu0 = $cpus_from_library[0];

  $new_cpulines = array();
  $cpulines = explode("\n",$cpu0);
  foreach ($cpulines as $cpuline) {
        $length= strlen($cpuline);

        #print "L: $length :".$cpuline."<br>";
        $i=1;
        $posold = 0;
        while ($length > 80) {
                $pos = strpos($cpuline, " ", 80*$i );
                $delta = $pos-$posold;
                $posold=$pos;
                $length = $length - $delta;
                $i++;
                $cpuline = substr_replace($cpuline, "\n   ", $pos, 0);
                #print "CPL: $cpuline ---- <br>";
        }
        array_push($new_cpulines, $cpuline);
  }

$cpu0 = implode("\n",$new_cpulines);

#print "URL: $url<br>";
#print "cont: <pre>".$cpu0."</pre>";

  $article = '[code lang="plain" title="cat /proc/cpuinfo"]
'.$cpu0.'
[/code]
';

  $new_taglist = lhg_taglist_by_title( $title );
  $taglist = array_merge( $taglist, $new_taglist );
  $tagstring = lhg_convert_tag_array_to_string( $taglist );





  #print "Article creation started";

  #print "<br>Title: $title <br> ScanID: $sid<br>";

        $title="<!--:us-->".$title."<!--:-->";

	$myPost = array(
			'post_status' => 'draft',
                        'post_content' => "<!--:us-->".$article."<!--:-->",
			'post_type' => 'post',
			'post_author' => 1,
			'post_title' =>  $title,
			'post_category' => array($category),
                        'tags_input' => $tagstring,
		);
        global $wpdb;
	#$post_if = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title'");
        #print "PI: ".$post_if;

	$post_if2 = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");
        #print "PI2: ".$post_if2;

        $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
        $created_id = $lhg_price_db->get_var($sql);


  if ( ($post_if2 > 0) or ($created_id != 0) ) {
  	#print "Title exists";
        if ($created_id != 0) $newPostID = $created_id;
        if ($created_id == 0) $newPostID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");

	# store created_id for already existing articles
	if ($created_id == 0)  {
                $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        	$result = $lhg_price_db->query($sql);
        }

  }else{
  	//-- Create the new post
        #print "new article";
  	$newPostID = wp_insert_post($myPost);
        $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        $result = $lhg_price_db->query($sql);
  }
  #print "<br>done<br>";

  # Store scan info in DB
  #
  # get CPU identifier
  $pos = strpos($cpu0, "model name");
  $pos_end = strpos( substr($cpu0,$pos) , "\n");
  $pos_colon = strpos( substr($cpu0,$pos) , ":");
  #error_log ("POS: $pos - $pos_colon - $pos_end<br>");
  #print substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2)."<br>";
  $cpu_identifier = substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2);
  #error_log("CPU id: $cpu_identifier");
  lhg_create_new_DB_entry_post ( $newPostID, "cpu", $cpu_identifier );
  # get Amazon ID, if available
  $amzid = lhg_get_AMZ_ID_from_scan ( $sid, "cpu", "" );
  #print "AMZID CPU: $amzid";

  # set Amazon ID
  $key = "amazon-product-single-asin";
  $value = $amzid;
  if(get_post_meta($newPostID, $key, FALSE)) { //if the custom field already has a value
  	update_post_meta($newPostID, $key, $value);
  } else { //if the custom field doesn't have a value
  	add_post_meta($newPostID, $key, $value);
  }

  # store in history that article was created
  lhg_post_history_scancreate( $newPostID, $sid);

  return $newPostID;


}

#
#
###### Mainboard article
#
#

function lhg_create_mainboard_article ($title, $sid, $id ) {

  global $lhg_price_db;
  global $lang;

  # article creation should be limited to .com server
  if ($lang == "de") {
                error_log("Article creation on .de server should not happen. ID: $id");
                return;
  }

  # check 1:
  # see if article was already created based on article ID
  $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
  $created_id = $lhg_price_db->get_var($sql);
  if ($created_id != 0) return $created_id;

  # check 1b:
  #see if article was already created based on article ID
  $sql = "SELECT mb_postid FROM `lhgscansessions` WHERE sid = \"".$sid."\" ";
  $created_id = $lhg_price_db->get_var($sql);
  #error_log("Found PostID: $created_id");
  if ($created_id != 0) return $created_id;


  # check 2:
  # see if article was already created based on title
  $otitle = $title;
  $title = lhg_clean_mainboard_name( $title );
  $page = get_page_by_title( $title );
  if ( ( $page->ID != "") && is_page($page->ID) ) return $page->ID;
  #if ( is_page($page->ID) ) return $page->ID;


  $laptop_probability = lhg_scan_is_laptop( $sid );

  // if mainboard
  if ($laptop_probability < 0.8) {
	$category = 472;
	$taglist = array( 472 );
  } else { #or if laptop
	$category = 470;
	$taglist = array( 470 );
	$taglist = array( 450 );
  }

  # Download only once for speed improvement
  #global $lspci_content_from_library;
  #global $dmesg_content_from_library;
  #global $lsb_content_from_library;
  #global $version_content_from_library;


  #if ( $lspci_content_from_library == "" ) {
  #      $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=lspci.txt";
  #	$lspci_content_from_library = file_get_contents($url);
  #}
  #
  #if ( $dmesg_content_from_library == "" ) {
  #        $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=dmesg.txt";
  #        $dmesg_content_from_library = file_get_contents($url);
  #}
  #
  #if ( $lsb_content_from_library == "" ) {
  #        $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=lsb_release.txt";
  #        $lsb_content_from_library = file_get_contents($url);
  #}
  #
  #if ( $version_content_from_library == "" ) {
  #        $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=version.txt";
  #     	  $version_content_from_library = file_get_contents($url);
  #}

#$lspci = explode("\n\n",$lspci_content_from_library);
##print "<br>Dump:".var_dump($lspci)."<br>";
#$lspci0 = $lspci[0];
#$lspci0 = str_replace("\n\n","",$lspci0);


#	# create filtered and unfiltered list of all PCI IDs as array $pci_array_all
#	$lspci_array = explode("\n",$lspci_content_from_library);
#        $pcilist = array();
#
#        foreach ($lspci_array as $line) {
#                #print "L $i:".$line."<br>";
#                $pciid_found = preg_match("/\[....:....\]/",$line,$matches);
#                $subsystem_found = preg_match("/Subsystem/",$line,$matches2);
#                #print preg_match("/\[....:....\]/",$line,$matches)." - ".var_dump($matches)."<br>";
#
#                $clean_pciid = $matches[0];
#                $clean_pciid = str_replace("[","",$clean_pciid);
#                $clean_pciid = str_replace("]","",$clean_pciid);
#                # PCI ID found, but no Subsystem ID
#                if ( ( $pciid_found == 1 ) && ( $subsystem_found == 0) ) array_push($pcilist, $clean_pciid);
#        }
#        $pci_array_all = $pcilist;






#print "URL: $url<br>";
#print "cont: <pre>".$cpu0."</pre>";

#  # DMI entry
#  $dmesg_content_array = split("\n",$dmesg_content_from_library);
#  $dmi_array = preg_grep("/DMI: /",$dmesg_content_array);
#  $dmi_line = implode("\n",$dmi_array);
#  $dmi_line = str_replace("[    0.000000]","",$dmi_line);
#
#  # Distribution
#  $lsb_content_array = split("\n",$lsb_content_from_library);
#  $lsb_array = preg_grep("/Description/",$lsb_content_array);
#  $distribution = implode(" ",$lsb_array);
#  $distribution = str_replace("Description:","",$distribution);
#  while (preg_match("/  /",$distribution)){
#        $distribution = str_replace("  "," ",$distribution);
#  }
  if (trim($distribution) == "") {
        # get distribution name from scan data base
	$sql = "SELECT distribution FROM `lhgscansessions` WHERE sid = \"".$sid."\"";
    	$result = $lhg_price_db->get_results($sql);
        $result0 = $result[0];
        $distribution = $result0->distribution;
  }





#  # Kernel version
#  $version_content_array = split("\n",$version_content_from_library);
#  $version_array = preg_grep("/Linux version/",$version_content_array);
#  $version_line = $version_array[0];
#  $version_line = str_replace("Linux version ","",$version_line);
#  list($version, $null) = split(" ",$version_line);

  #$article =  'The '.$title." ";

  #if ($laptop_probability > 0.8) $article .= 'is a laptop and ';

#  $article .= 'was successfully tested in configuration
#[code lang="plain" title="dmesg | grep DMI"]
#'.$dmi_line.'
#[/code]
#under '.trim($distribution).' with Linux kernel version '.trim($version).'.
#
#';

#  $article .= '<h3>Hardware Overview</h3>
#The following hardware components are part of the '.$title.' and are supported by the listed kernel drivers:
#[code lang="plain" title="lspci -nnk"]
#'.$lspci0.'
#[/code]
#';


#$article .= "[lhg_mainboard_intro distribution=\"".trim($distribution)."\" version=\"".trim($version)."\" dmi_output=\"".trim($dmi_line)."\"]
#
#[lhg_mainboard_lspci]
#".trim($lspci0)."
#[/lhg_mainboard_lspci]
#";

#print $article;




  # ToDo: should be created based on lspci and dmesg output

  $new_taglist = lhg_taglist_by_title( $title );
  $taglist = array_merge( $taglist, $new_taglist );
  $tagstring = lhg_convert_tag_array_to_string( $taglist );



  #print "Article creation started";

  #print "<br>Title: $title <br> ScanID: $sid<br>";

        $title="<!--:us-->".$title."<!--:-->";

	$myPost = array(
			'post_status' => 'draft',
                        'post_content' => "<!--:us-->".$article."<!--:-->",
			'post_type' => 'post',
			'post_author' => 1,
			'post_title' =>  $title,
			'post_category' => array($category),
                        'tags_input' => $tagstring,
		);
        global $wpdb;
	#$post_if = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title'");
        #print "PI: ".$post_if;

	$post_if2 = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");
        #print "PI2: ".$post_if2;

        $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
        $created_id = $lhg_price_db->get_var($sql);


  if ( ($post_if2 > 0) or ($created_id != 0) ) {
  	#print "Title exists";
        if ($created_id != 0) $newPostID = $created_id;
        if ($created_id == 0) $newPostID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");

	# store created_id for already existing articles
	if ($created_id == 0)  {
                $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        	$result = $lhg_price_db->query($sql);

                $sql = "UPDATE `lhgscansessions` SET mb_postid = \"".$newPostID."\" WHERE sid = \"".$sid."\" ";
        	$result = $lhg_price_db->query($sql);

        }

  }else{
  	//-- Create the new post
        #print "new article";
  	$newPostID = wp_insert_post($myPost);
        $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        $result = $lhg_price_db->query($sql);

        $sql = "UPDATE `lhgscansessions` SET mb_postid = \"".$newPostID."\" WHERE sid = \"".$sid."\" ";
        $result = $lhg_price_db->query($sql);


  }
  #print "<br>done<br>";

  # ToDo: store MB in DB
  # ToDo: store MB in DB

  # Store scan info in DB
  #
  # get CPU identifier
  #$pos = strpos($cpu0, "model name");
  #$pos_end = strpos( substr($cpu0,$pos) , "\n");
  #$pos_colon = strpos( substr($cpu0,$pos) , ":");
  #print "POS: $pos - $pos_colon - $pos_end<br>";
  #print substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2)."<br>";
  #$cpu_identifier = substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2);


  if ($laptop_probability > 0.8) {
        # store all pci ids
        $identifier = lhg_get_mainboard_fingerprint( $sid );
        lhg_create_new_DB_entry_post ( $newPostID, "laptop", $identifier );
  } else {
        # store all pci ids
        # ToDo: filter pciids not onboard!
        $identifier = lhg_get_mainboard_fingerprint( $sid );
        #$identifier = implode(",",$pci_array_all);
	lhg_create_new_DB_entry_post ( $newPostID, "mainboard", $identifier );
  }

  # get Amazon ID, if available
  $amzid = lhg_get_AMZ_ID_from_scan ( $sid, "mainboard", "" );
  #print "AMZID CPU: $amzid";

  # set Amazon ID
  $key = "amazon-product-single-asin";
  $value = $amzid;

  if ($amzid != "")
  if(get_post_meta($newPostID, $key, FALSE)) { //if the custom field already has a value
  	update_post_meta($newPostID, $key, $value);
  } else { //if the custom field doesn't have a value
  	add_post_meta($newPostID, $key, $value);
  }

  # store in history that article was created
  lhg_post_history_scancreate( $newPostID, $sid);

  return $newPostID;

}


#
#
####### PCI article
#
#

function lhg_create_pci_article ($title, $sid, $id ) {

  global $lhg_price_db;
  global $lspci_content_from_library;
  global $lang;

  # article creation should be limited to .com server
  if ($lang == "de") {
                error_log("Article creation on .de server should not happen. ID: $id");
                return;
  }

  # check 1:
  # see if article was already created based on article ID
  $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
  $created_id = $lhg_price_db->get_var($sql);
  if ($created_id != 0) return $created_id;

  # check 2:
  # see if article was already created based on title
  $otitle = $title;
  $page = get_page_by_title( $title );
  if ( ( $page->ID != "") && is_page($page->ID) ) return $page->ID;
  #if ( is_page($page->ID) ) return $page->ID;


  $category = "";
  $taglist = array( );

  if ( $lspci_content_from_library == "" ) {
	  $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=lspci.txt";
	  $lspci_content_from_library = file_get_contents($url);
  }


  // ToDo: grab relevant PCI lines

  $lspci = explode("\n\n",$lspci_content_from_library);
  #print "<br>Dump:".var_dump($cpus)."<br>";
  $lspci0 = $lspci[0];

#print "URL: $url<br>";
#print "cont: <pre>".$cpu0."</pre>";

  $article = '[code lang="plain" title="lspci -nnk"]
'.$lspci0.'
[/code]
';


  # ToDo: should be created based on lspci and dmesg output

  $new_taglist = lhg_taglist_by_title( $title );
  $taglist = array_merge( $taglist, $new_taglist );
  $tagstring = lhg_convert_tag_array_to_string( $taglist );



  #print "Article creation started";

  #print "<br>Title: $title <br> ScanID: $sid<br>";

        $title="<!--:us-->".$title."<!--:-->";

	$myPost = array(
			'post_status' => 'draft',
                        'post_content' => "<!--:us-->".$article."<!--:-->",
			'post_type' => 'post',
			'post_author' => 1,
			'post_title' =>  $title,
			'post_category' => array($category),
                        'tags_input' => $tagstring,
		);
        global $wpdb;
	#$post_if = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title'");
        #print "PI: ".$post_if;

	$post_if2 = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");
        #print "PI2: ".$post_if2;

        $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
        $created_id = $lhg_price_db->get_var($sql);


  if ( ($post_if2 > 0) or ($created_id != 0) ) {
  	#print "Title exists";
        if ($created_id != 0) $newPostID = $created_id;
        if ($created_id == 0) $newPostID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");

	# store created_id for already existing articles
	if ($created_id == 0)  {
                $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        	$result = $lhg_price_db->query($sql);
        }

  }else{
  	//-- Create the new post
        #print "new article";
  	$newPostID = wp_insert_post($myPost);
        $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        $result = $lhg_price_db->query($sql);
  }
  #print "<br>done<br>";

  # ToDo: store MB in DB

  # Store scan info in DB
  #
  # get CPU identifier
  #$pos = strpos($cpu0, "model name");
  #$pos_end = strpos( substr($cpu0,$pos) , "\n");
  #$pos_colon = strpos( substr($cpu0,$pos) , ":");
  #print "POS: $pos - $pos_colon - $pos_end<br>";
  #print substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2)."<br>";
  #$cpu_identifier = substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2);


  lhg_create_new_DB_entry_post ( $newPostID, "pci", $otitle );
  # get Amazon ID, if available
  $amzid = lhg_get_AMZ_ID_from_scan ( $sid, "pci", "" );
  #print "AMZID CPU: $amzid";

  # set Amazon ID
  $key = "amazon-product-single-asin";
  $value = $amzid;
  if(get_post_meta($newPostID, $key, FALSE)) { //if the custom field already has a value
  	update_post_meta($newPostID, $key, $value);
  } else { //if the custom field doesn't have a value
  	add_post_meta($newPostID, $key, $value);
  }

  # store in history that article was created
  lhg_post_history_scancreate( $newPostID, $sid);

  return $newPostID;

}

#
#
##### Drive article
#
#

function lhg_create_drive_article ($title, $sid, $id ) {

  global $lhg_price_db;
  global $dmesg_content_from_library;
  global $lang;

  # article creation should be limited to .com server
  if ($lang == "de") {
                error_log("Article creation on .de server should not happen. ID: $id");
                return;
  }

  # check 1:
  # see if article was already created based on article ID
  $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
  $created_id = $lhg_price_db->get_var($sql);
  if ($created_id != 0) return $created_id;



  $taglist = array( 584 );

  if ( $dmesg_content_from_library == "" ) {
	  $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=dmesg.txt";
	  #extract relevant dmesg output
          $dmesg_content_from_library = file_get_contents($url);
	  #$pos = strpos($content, $title);
	  #$line = substr($content,$pos,300);
	  #print "dmesgline: ".$line."<br>";
  }


  $dmesg = explode("\n",$dmesg_content_from_library);

  #get full dmesg line
  #error_log("Title: $title");
  $title_regexp= trim($title);
  $title_regexp = str_replace("/",".",$title_regexp);
  $title_regexp = str_replace("-",".",$title_regexp);
  $dmesg_lines = preg_grep("/".$title_regexp."/i",$dmesg);
  if (count($dmesg_lines) > 0)
  foreach ($dmesg_lines as $dmesg_line) {
        $full_title = $dmesg_line;
  }
  #error_log("Full Title: $full_title");

  # ATA drive 
  if ( (strpos($title, "ATA ") !== false )  ) {
	  # 1. get HDD name
	  $dmesg_lines = preg_grep("/".$title."/i",$dmesg);
	  foreach ($dmesg_lines as $dmesg_line) {
        	$strpos = strpos($dmesg_line,"ATA ");
	        if ($strpos > 0) {
        		$searchstring0 = substr($dmesg_line, $strpos+4);
	                $tmp = explode( " ", trim( $searchstring0 ) );
	                $searchstring = $tmp[0];
                        break;
		}

              /*  # In case of USB storage we have no ATA string
        	$strpos_usbstorage = strpos($dmesg_line,"ANSI: 6");
        	$strpos_usb_start = strpos($dmesg_line,"Direct-Access");
	        if ($strpos_usbstorage > 0) {
        		$searchstring0 = substr($dmesg_line, $strpos_usb_start+14);
	                $tmp = explode( " ", trim( $searchstring0 ) );
	                $searchstring = $tmp[0];
                        break;
		}
                */

	  }

	  # 2. get scsi ID
	  $dmesg_lines_array = preg_grep("/scsi(.*)".$searchstring."/i",$dmesg );
          foreach ($dmesg_lines_array as $line) $dmesg_line = $line;
          #$dmesg_line = $dmesg_lines_array[0];
          #print "dmla:<br>";
          #var_dump($dmesg_lines_array);
          #print "dml:<br>";
          #var_dump($dmesg_line);
	  preg_match("/scsi(.*)[0-9]\:[0-9]\:[0-9]\:[0-9]/i", $dmesg_line, $scsi_match_array);
	  preg_match("/[0-9]\:[0-9]\:[0-9]\:[0-9]/i", $dmesg_line, $scsi_nr_array);
          $scsi_nr = $scsi_nr_array[0];
          #error_log("dmesg_line: $dmesg_line");
          #print "scsiNR: $scsi_nr --- $dmesg_line<br>";
          #var_dump($scsi_nr);
          #print "dmesg_line: $dmesg_line<br>";
          #var_dump($dmesg_lines);

          # 3. get ata ID
          $ata_lines_array = preg_grep("/ata[0-9].[0-9][0-9](.*)".$searchstring."/i", $dmesg);
          foreach ($ata_lines_array as $line) $ata_line = $line;
	  preg_match("/ata[0-9].[0-9][0-9]/i", $ata_line, $ata_nr_array);
          $ata_nr = $ata_nr_array[0];
          #print "ATANR: $ata_nr<br>";

          # 4. get device file name
	  $device_array = preg_grep("/sd ".$scsi_nr."/i",$dmesg );
          foreach ($device_array as $line) {
                #error_log("DL: $line");
                # found line with [s..] definition. Remember and leave loop
                if ( preg_match("/\[s..\]/i", $line ) == 1 ) {
        	        $device_line = $line;
                        #error_log("Found: $line");
                        break;
		}
	  }
	  preg_match("/\[s..\]/i", $device_line, $device_name_array);
          $device_name = $device_name_array[0];
          $device_name_raw = substr($device_name,1,-1);
          #print "DNAme RAW:".$device_name_raw."<br>";
          #var_dump($device_array);

#          error_log ("
#1: $ata_nr<br>
#2: $device_name<br>
#3: $device_name_raw<br>
#4: ".substr($ata_nr,0,4)."<br>
#5: $scsi_nr<br>")
#;
          # 5. extract all relevant lines
	  $dmesg_outputs = preg_grep("/(".$ata_nr."|\[".$device_name_raw."\]|".$device_name_raw."|".substr($ata_nr,0,4)."|".$scsi_nr.")/i", $dmesg);
          $clean_dmesg_outputs = array();
          foreach($dmesg_outputs as $dmesg_output){
                #$tmp = explode("] ", $dmesg_output);
                if ( strpos( $dmesg_output , "Stopping disk" ) !== false ) break;
                array_push( $clean_dmesg_outputs, substr( $dmesg_output, 15) );
	  }

          # if more than 100 lines were found something probably went wrong. If not, no use in adding so many lines of log output to post
          if ( count($clean_dmesg_outputs) > 100 ) $clean_dmesg_output = "Error: too many dmesg files found";
          $dmesgOutput = join("\n",$clean_dmesg_outputs);
  }

  # CD-ROM drive
  if (strpos($full_title, "CD-ROM ") !== false ) {

	  # 1. get scsi ID
	  $dmesg_lines_array = preg_grep("/".$title."/i",$dmesg );
          foreach ($dmesg_lines_array as $line) $dmesg_line = $line;
          #$dmesg_line = $dmesg_lines_array[0];
          #print "dmla:<br>";
          #var_dump($dmesg_lines_array);
          #print "dml:<br>";
          #var_dump($dmesg_line);
	  preg_match("/scsi(.*)[0-9]\:[0-9]\:[0-9]\:[0-9]/i", $dmesg_line, $scsi_match_array);
	  preg_match("/[0-9]\:[0-9]\:[0-9]\:[0-9]/i", $dmesg_line, $scsi_nr_array);
          $scsi_nr = $scsi_nr_array[0];
          #error_log("SCSI NR: $scsi_nr");

          # get ata ID based on last identifier of drive (should be drive id)
          #error_log("TRG: $title_regexp");
          $tmp = explode(" ",$title_regexp);
          $tmp2 = array_reverse($tmp);
          $ata_lines_array = preg_grep("/ata[0-9].[0-9][0-9](.*)".$tmp2[0]."/i", $dmesg);
          foreach ($ata_lines_array as $line) $ata_line = $line;
	  preg_match("/ata[0-9].[0-9][0-9]/i", $ata_line, $ata_nr_array);
          $ata_nr = $ata_nr_array[0];
          if ($ata_nr == "") $ata_nr = "ERROR_NO_ATA_NR_FOUND";
          #error_log("ATANR: $ata_line -> $ata_nr");


          # extract all relevant lines
	  $dmesg_outputs = preg_grep("/(".$ata_nr."|cdrom\: |".$scsi_nr.")/i", $dmesg);
          $oldline = "";
          $clean_dmesg_outputs = array();
          foreach($dmesg_outputs as $dmesg_output){
                #$tmp = explode("] ", $dmesg_output);

                # check if this line is a repetition
                if ( substr( $dmesg_output, 15) != $oldline )
                array_push( $clean_dmesg_outputs, substr( $dmesg_output, 15) );
                $oldline = substr( $dmesg_output, 15);
	  }

          $dmesgOutput = join("\n",$clean_dmesg_outputs);
          #error_log("OUT: $dmesgOutput");

	  # Get Identifier String from Dmesg output
	  $drive_id_array = preg_grep("/ ANSI: 5/i",$clean_dmesg_outputs );
	  foreach ($drive_id_array as $line) $drive_id = $line;

	  $pos = strpos($drive_id, "0: ");
	  $drive_identifier = substr($drive_id,$pos+3);
	  #error_log("DriveID: $drive_identifier");
	  #lhg_create_new_DB_entry_post ( $newPostID, "drive", $drive_identifier );

  }




  $find = array_keys( $dmesg, $title);

  $keyword = $title;
  foreach($dmesg as $key => $arrayItem){
        if( stristr( $arrayItem, $keyword ) ){
            #error_log( "Key: $key - ".$dmesg[$key] );
            $foundkey = $key;
            break;
        }
  }
  list($null, $drive_identifier) = explode("0:0: ",$dmesg[$foundkey]);

  #print "ID: $drive_identifier";

  #var_dump($find);
  #print "F0: ".$dmesg[$find[0]]."<br>";
  #print "F1: ".$dmesg[$find[1]]."<br>";


#print "<br>Dump:".var_dump($dmesg)."<br>";
		#$cpu0 = $cpus[0];

		#print "URL: $url<br>";
		#print "cont: <pre>".$cpu0."</pre>";

  $article = '[lhg_drive_intro]
[code lang="plain" title="dmesg"]
'.$dmesgOutput.'
[/code]
';

  # The scsi line shows a truncated title, if the device name is too long.
  # For the title we will need the full title
  $title = lhg_search_long_title( $title , $dmesg );

  #if (preg_match('/Athlon(tm) II/',$title)) array_push ( $taglist , 880);

  $title = wp_strip_all_tags($title);
  $title = lhg_clean_drive_title($title);
  #print "Article creation started";

  $new_taglist = lhg_taglist_by_title($title);
  $taglist = array_merge( $taglist, $new_taglist );
  $tagstring = lhg_convert_tag_array_to_string( $taglist );

  # set category
  #$category = 478;
  $category_array = lhg_category_by_title ( $title  );
  array_push( $category_array, 478);

  #print "<br>Title: $title <br> ScanID: $sid<br>";

	#$title = $title;

  # Do nothing, if article already exists
  $page = get_page_by_title( "<!--:us-->".$title."<!--:-->" );
  if ( ( $page->ID != "") && is_page($page->ID) ) return $page->ID;

  $page = get_page_by_title( $title );
  if ( ( $page->ID != "") && is_page($page->ID) ) return $page->ID;


	$myPost = array(
			'post_status' => 'draft',
                        'post_content' => "<!--:us-->".$article."<!--:-->",
			'post_type' => 'post',
			'post_author' => 1,
			'post_title' =>  "<!--:us-->".$title."<!--:-->",
			'post_category' => $category_array,
                        'tags_input' => $tagstring,
		);
        global $wpdb;
	#$post_if = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title'");
        #print "PI: ".$post_if;

	#$post_if2 = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '%".$title."%' ");
	$post_if2 = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '%".$title."%' AND post_status = 'draft' ");
        #print "PI2: ".$post_if2;

        $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
        $created_id = $lhg_price_db->get_var($sql);

        #print "ID: $id<br>
        #found: $post_if2<br>
        #CreatedID: $created_id<br>";

  # if article was created or title already exists
  if ( ($post_if2 > 0) or ($created_id != 0) ) {
  	#print "Title exists".$title."<br>";
        #$newPostID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title like '%".$title."%' AND post_status = 'draft' ");

        if ($created_id != 0) $newPostID = $created_id;
        if ($created_id == 0) $newPostID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title like '%".$title."%' ");

	# store created_id for already existing articles
	if ($created_id == 0)  {
                $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        	$result = $lhg_price_db->query($sql);
        }
  }else{
  	//-- Create the new post
        #print "new article";
  	$newPostID = wp_insert_post($myPost);
        #store id

        $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        $result = $lhg_price_db->query($sql);

  }
  #print "<br>done<br>";

  #error_log("PID: $newPostID -> $drive_identifier");

  if ($drive_identifier != "")
  lhg_create_new_DB_entry_post ( $newPostID, "drive", $drive_identifier );
  # get Amazon ID, if available
  $amzid = lhg_get_AMZ_ID_from_scan ( $sid, "drive", $id );

  # set Amazon ID
  $key = "amazon-product-single-asin";
  $value = $amzid;
  if(get_post_meta($newPostID, $key, FALSE)) { //if the custom field already has a value
  	update_post_meta($newPostID, $key, $value);
  } else { //if the custom field doesn't have a value
  	add_post_meta($newPostID, $key, $value);
  }

  # store in history that article was created
  lhg_post_history_scancreate( $newPostID, $sid);

  return $newPostID;
}


#
#
#### USB article
#
#


function lhg_create_usb_article ($title, $sid, $usbid ) {

  # Library download timeout settings
  ini_set('default_socket_timeout', 5);

  global $lhg_price_db;
  global $lsusb_content_from_library;
  global $dmesg_content_from_library;
  global $lang;

  # article creation should be limited to .com server
  if ($lang == "de") {
                error_log("Article creation on .de server should not happen. ID: $id");
                return;
  }

  # check if article was already created
  #error_log("USB article ID: $id");
  $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
  $created_id = $lhg_price_db->get_var($sql);
  if ($created_id != 0) return $created_id;





  #$category = 478;
  $taglist = array( 156 );

  if ( $lsusb_content_from_library == "" ) {
	  $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=lsusb.txt";
	  $lsusb_content_from_library = file_get_contents($url);
  	if ($lsusb_content_from_library === false) {
	        throw new Exception('Failed to open ' . $url);
	  }
  }

#extract lsusb line

foreach(preg_split("/((\r?\n)|(\r\n?))/", $lsusb_content_from_library) as $line){
    if ( strpos( $line, $usbid) > 0 ) $lsusbOutput = $line;
} 
if ($lsusbOutput == "") $lsusbOutput = $lsusb_content_from_library;
#extract relevant dmesg output


  if ( $dmesg_content_from_library == "" ) {
	  $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=dmesg.txt";
	  $dmesg_content_from_library = file_get_contents($url);
  	if ($dmesg_content_from_library === false) {
        	throw new Exception('Failed to open ' . $url);
	  }
  }

#1 get usb port number: search line where USB ID is present:
foreach(preg_split("/((\r?\n)|(\r\n?))/", $dmesg_content_from_library) as $line){
    if ( ( strpos( $line, substr($usbid,0,4) ) > 0 ) &&
         ( strpos( $line, substr($usbid,-4) ) > 0 ) ) {
                $pos_start = strpos($line,"]");
                $pos_end = strpos($line,":");

                $usbline = substr($line,$pos_start, ($pos_end - $pos_start) );
        	break;
	}
} 

#2 get all relevant lines
foreach(preg_split("/((\r?\n)|(\r\n?))/", $dmesg_content_from_library) as $line){
    if ( strpos( $line, $usbline) > 0 ) $dmesgOutput .= substr($line,$pos_start+2)."\r\n";
} 


  #print "Tags: "; var_dump( $taglist ); print "<br>";

  $new_taglist = lhg_taglist_by_title( $title );
  $taglist = array_merge( $taglist, $new_taglist );

  $tagstring = lhg_convert_tag_array_to_string( $taglist );
  #print "newTags: "; var_dump( $taglist ); print "<br>";


  $title = wp_strip_all_tags($title);
  $title_orig = $title;

  $title_orig_filtered = str_replace(", Inc.","",$title_orig);

  $title = lhg_clean_usb_title( $title );


  $article = 'The '.$title_orig_filtered.' is a USB '.$type.' with USB ID '.$usbid.'
[code lang="plain" title="lsusb"]
'.$lsusbOutput.'
[/code]
It is automatically recognized and fully supported by the Linux kernel:
[code lang="plain" title="dmesg"]
'.$dmesgOutput.'
[/code]
';

  $category_array = lhg_category_by_title ( $title );


  #print "Article creation started";

  #print "<br>Title: $title <br> ScanID: $sid<br>";

	$title = "<!--:us-->".$title."<!--:-->";

	$myPost = array(
			'post_status' => 'draft',
                        'post_content' => "<!--:us-->".$article."<!--:-->",
			'post_type' => 'post',
			'post_author' => 1,
			'post_title' =>  $title,
			'post_category' => $category_array,
                        'tags_input' => $tagstring,
		);
        global $wpdb;
	#$post_if = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title'");
        #print "PI: ".$post_if;

	$post_if2 = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");
        #print "PI2: ".$post_if2;

        $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
        $created_id = $lhg_price_db->get_var($sql);


  if ( ($post_if2 > 0) or ($created_id != 0) ) {
  	#print "Title exists";

        if ($created_id != 0) $newPostID = $created_id;
        if ($created_id == 0) $newPostID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");

	# store created_id for already existing articles
	if ($created_id == 0)  {
                $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        	$result = $lhg_price_db->query($sql);
        }

  }else{
  	//-- Create the new post
        #print "new article";
  	$newPostID = wp_insert_post($myPost);

        $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        $result = $lhg_price_db->query($sql);


  }
  #print "<br>done<br>";


  # Store post in DB
  lhg_create_new_DB_entry_post ( $newPostID, "usb", $usbid );
  #lhg_link_new_DB_entry_in_scan ( $newPostID, "usb", $usbid, $sid );
  # get Amazon ID, if available
  $amzid = lhg_get_AMZ_ID_from_scan ( $sid, "usb", $usbid );
  #print "AMZID: $amzid";
  # set Amazon ID
  $key = "amazon-product-single-asin";
  $value = $amzid;
  if(get_post_meta($newPostID, $key, FALSE)) { //if the custom field already has a value
  	update_post_meta($newPostID, $key, $value);
  } else { //if the custom field doesn't have a value
  	add_post_meta($newPostID, $key, $value);
  }

  # store in history that article was created
  lhg_post_history_scancreate( $newPostID, $sid);

  return $newPostID;
}

#
#### store tags to central DB for automatic translation tool
#
global $lang;

# store only for com server
if ($lang != "de") {
	#add_action( 'save_post', 'lhg_store_tags_in_db');
	#add_action( 'save_post', 'lhg_store_content_in_db');
	#add_action( 'save_post', 'lhg_store_title_in_db');
	#add_action( 'save_post', 'lhg_store_categories_in_db');
	#add_action( 'save_post', 'lhg_store_thumbnail_in_db');

	add_action( 'transition_post_status', 'lhg_store_tags_in_db', 10, 3);
	add_action( 'transition_post_status', 'lhg_store_content_in_db', 10, 3);
	add_action( 'transition_post_status', 'lhg_store_title_in_db', 10, 3);
	add_action( 'transition_post_status', 'lhg_store_categories_in_db', 10, 3);
	add_action( 'transition_post_status', 'lhg_store_thumbnail_in_db', 10, 3);
	add_action( 'transition_post_status', 'lhg_store_product_in_db', 10, 3);
	add_action( 'transition_post_status', 'lhg_store_permalink_in_db', 10, 3);

}

function lhg_store_product_in_db(){

    global $lhg_price_db;

    $postid = $_POST['post_ID'];
    $title=translate_title(get_the_title($postid));
    $s=explode("(",$title);
    $short_title=trim($s[0]);


    if ($short_title != "") {
	    # write taglist to lhgtransverse_posts
	    $sql = "UPDATE lhgtransverse_posts SET `product` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $short_title, $postid);
	    $result = $lhg_price_db->query($safe_sql);
	}
}

function lhg_store_permalink_in_db(){

    global $lhg_price_db;

    $postid = $_POST['post_ID'];
    $permalink = get_permalink($postid);


    if ($permalink != "") {
	    # write taglist to lhgtransverse_posts
	    $sql = "UPDATE lhgtransverse_posts SET `permalink_com` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $permalink, $postid);
	    $result = $lhg_price_db->query($safe_sql);
	}
}

function lhg_store_thumbnail_in_db(){

    global $lhg_price_db;

    $postid = $_POST['post_ID'];
    $url = wp_get_attachment_url( get_post_thumbnail_id($postid) );

    if ($url != "") {
	    # write taglist to lhgtransverse_posts
	    $sql = "UPDATE lhgtransverse_posts SET `icon_com` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $url, $postid);
	    $result = $lhg_price_db->query($safe_sql);
	}
}




function lhg_store_tags_in_db(){

    global $lhg_price_db;

    $postid = $_POST['post_ID'];
    $posttags = get_the_tags($postid);

    if ($posttags) {
  	foreach($posttags as $tag) {
    	    $taglist .= $tag->slug . ',';
	}

    $taglist = substr($taglist,0,-1);

    }

    #"PID: $postid A:".
    #$taglist = implode(",",$taglist_o);


    if ($taglist != "") {
	    # write taglist to lhgtransverse_posts
	    $sql = "UPDATE lhgtransverse_posts SET `tagids_com` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $taglist, $postid);
	    $result = $lhg_price_db->query($safe_sql);
	}


}

function lhg_store_content_in_db(){

    global $lhg_price_db;

    $postid = $_POST['post_ID'];

    $content = get_post_field('post_content', $postid);

    #"PID: $postid A:".
    #$taglist = implode(",",$taglist_o);


    if ($content != "") {
	    # write taglist to lhgtransverse_posts
	    $sql = "UPDATE lhgtransverse_posts SET `postcontent_com` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $content, $postid);
	    $result = $lhg_price_db->query($safe_sql);
    }
}

function lhg_store_title_in_db(){

    global $lhg_price_db;

    $postid = $_POST['post_ID'];

    $title = get_the_title($postid);

    #"PID: $postid A:".
    #$taglist = implode(",",$taglist_o);


    if ($title != "") {
	    # write taglist to lhgtransverse_posts
	    $sql = "UPDATE lhgtransverse_posts SET `title_com` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $title, $postid);
	    $result = $lhg_price_db->query($safe_sql);
    }
}

function lhg_store_categories_in_db(){

    global $lhg_price_db;

    $postid = $_POST['post_ID'];

    $categories = get_the_category($postid);
    $output = '';

    if ( ! empty( $categories ) ) {
    foreach( $categories as $category ) {
        $output .=  $category->slug .",";
    }

    	if (is_string($categories)) $categories = substr($categories,0,-1);

    }


    #"PID: $postid A:".
    #$taglist = implode(",",$taglist_o);


    if ($output != "") {
	    # write taglist to lhgtransverse_posts
	    $sql = "UPDATE lhgtransverse_posts SET `categories_com` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $output, $postid);
	    $result = $lhg_price_db->query($safe_sql);
    }
}

function lhg_show_translate_process($postid) {
        global $lang;
        global $lhg_price_db;

	$urlprefix = "";
	if ($lang == "de") $urlprefix = "http://www.linux-hardware-guide.com";

        wp_enqueue_style('admin-styles', '/wp-content/plugins/lhg-pricedb/css/backend.css');




	echo "<h3>Start Auto-translate:</h3> ";

        #print "PID com: $postid<br>";

        #
        # get tags
        #

	$sql = "SELECT `tagids_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $result_tags = $lhg_price_db->get_var($safe_sql);


        $tagarray_slug = explode(",",$result_tags);


        $tagarray_ids  = array();
        $tagarray_names= array();
        foreach($tagarray_slug as $tagarray_s){

	  # get "de" slug from DB
  	  $sql = "SELECT `slug_de` FROM `lhgtransverse_tags` WHERE slug_com = %s";
          $safe_sql = $lhg_price_db->prepare($sql, $tagarray_s);
          $de_tag = $lhg_price_db->get_var($safe_sql);

          #fallback
          #print "ERROR: tag ($tagarray_s) not found -> fallback used<br>";
          if ($de_tag == "") $de_tag = $tagarray_s;

          $tmp = get_term_by('slug', $de_tag , 'post_tag');
          #var_dump($tmp); print "<br>";
	  array_push($tagarray_ids, $tmp->term_id );
	  array_push($tagarray_names, $tmp->name );

          if ($tmp->term_id == "") {
          	# unknown tag. Ask for translation now
                #print "ERROR: tag ".$de_tag." not found <br>";
                print '<form id="translate-form-'.$de_tag.'">'."
                        Translate &quot;$de_tag&quot; to German: ";
                print ' <input id="tagtranslate-'.$de_tag.'" name="tagtranslate-'.$de_tag.'" type="text" value="" size="20" maxlenght="290">
			<input type="submit" id="submit-tagtranslate-'.$de_tag.'" name="submit-tagtranslate" value="Submit translation" class="submit-tagtranslate-button" />
                      </form>';
	  }
	}

        #
        # get title
        #

	$sql = "SELECT `title_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $result_title = $lhg_price_db->get_var($safe_sql);

        if ($lang == "de") $result_title_de = lhg_translate_title_en_to_de( $result_title );

        #
        # get icon
        #

	$sql = "SELECT `icon_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $result_icon = $lhg_price_db->get_var($safe_sql);

        #
        # get categories
        #

	$sql = "SELECT `categories_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $result_categories = $lhg_price_db->get_var($safe_sql);

        $category_slugs = explode(",",$result_categories);
        $category_ids   = array();

        foreach($category_slugs as $cat_slug){

          $catid = get_category_by_slug($cat_slug);
          #var_dump ($catid);

          # 1. auto recognition by slug
          if ( ($catid->cat_ID) != "") array_push($category_ids, $catid->cat_ID );

          # 2. not all categories are found by english slugs.
          # No use for automatic detection. We simply identify them manually here:
          if ($cat_slug == "cctv" ) array_push($category_ids, 663);
          if ($cat_slug == "internal" ) array_push($category_ids, 335);
          if ($cat_slug == "ultrabook" ) array_push($category_ids, 589);
          if ($cat_slug == "all-in-one-printer" ) array_push($category_ids, 368);
          if ($cat_slug == "external" ) array_push($category_ids, 333);
          if ($cat_slug == "ssd" ) array_push($category_ids, 601);
          if ($cat_slug == "printer" ) array_push($category_ids, 323);
          if ($cat_slug == "laser-printer" ) array_push($category_ids, 488);
          if ($cat_slug == "graphiccards" ) array_push($category_ids, 507);
          if ($cat_slug == "network" ) array_push($category_ids, 5);

	}


        #
        # get content
        #

	$sql = "SELECT `postcontent_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $result_content = $lhg_price_db->get_var($safe_sql);

        $result_content = str_replace("<!--:us-->","",$result_content);
        $result_content = str_replace("<!--:-->","",$result_content);
        # for safety reasons we had to replace chars when storing them in PriceDB. Now we need to
        # revert this (normally only for the [code] block but we do not distinguish text and code yet - ToDo!)
        $result_content = str_replace("&lt;","<",$result_content);
        $result_content = str_replace("&gt;",">",$result_content);
        $result_content = str_replace("&quot;","\"",$result_content);
        $result_content = str_replace("&amp;","&",$result_content);

        #
        # guess AMAZON ID
        #

	$sql = "SELECT `shop_article_id` FROM `lhgprices` WHERE lhg_article_id = %s AND shop_id = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid, 7);
        $amazon_id = $lhg_price_db->get_var($safe_sql);

	#var_dump($result_title);
        print "<br>Title: $result_title";
        print "<br>New title: $result_title_de<br>";
        print "<br>Icon: $result_icon<br>";
        print "Tags: $result_tags  -> ".implode(",",$tagarray_ids)."<br>";
        print "<br>Categories: $result_categories -> ".implode(",",$category_ids)."<br>";
        print "Amazon ID: $amazon_id<br>";

        print "Content:<pre> $result_content</pre><br>";

        print "<h3>Article created</h3>";

        # use the translated title for new article
        # translation depends on de->en or en->de
        $result_title_translated = $result_title;
        if ($lang == "de") $result_title_translated = $result_title_de;


	$myPost = array(
			'post_status' => 'draft',
                        'post_content' => $result_content,
			'post_type' => 'post',
			'post_author' => get_current_user_id(),
			'post_title' =>  $result_title_translated,
			'post_category' => $category_ids,
                        'tags_input' => $tagarray_names,
                        'comment_status' => 'open'
		);

  	$newPostID = wp_insert_post($myPost);

        print '<a href="/wp-admin/post.php?post='.$newPostID.'&action=edit">New article '.$newPostID.'</a><br>';

        # add amazon id
	update_post_meta($newPostID, 'amazon-product-single-asin', $amazon_id );


        print '
	<script src="https://cdn.rawgit.com/vast-engineering/jquery-popup-overlay/1.7.11/jquery.popupoverlay.js"></script>

                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

                                $("[id^=submit-tagtranslate-]").click(function() {

                                	var button = this;
                                	var slug = $(this).attr(\'id\').substring(20);
                                        var translated_tag = $("#tagtranslate-"+slug).val();

                                        //alert("SLUG: "+slug);

        	                        var indicator_html = \'<img class="small-ajax-indicator" id="ajax-indicator-tagtranslate-\'+slug+\'" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                	                $(button).after(indicator_html);


                        	        //prepare Ajax data:
                                	var data ={
                                        	action: \'lhg_translate_slug_ajax\',
	                                        slug: slug,
                                                translated_tag:  translated_tag,
        	                                postid: "'.$newPostID.'"
                	                };


	                                //load & show server output
        	                        $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                	                        //$(button).append("Response");
                        	                //$(button).after(response);
                                	        //$(box).append("Response: <br>IMG: "+imageurl+" <br>text: "+responsetext);

        	                                //return to normal state
                                                $("#translate-form-"+slug).replaceWith(\'<div class="translated-tag-replaced">Translated: \'+translated_tag+\'</div>\');
                                                $(button).remove();
                                                //$(button).attr("class", "hwscan-comment-button-light");
                                	        //var indicatorid = "#button-load-known-hardware-comment";
                                        	$("#ajax-indicator-tagtranslate-"+slug).remove();

	                                });


                                //prevent default behavior
        	                return false;

                                });



                });



                /*]]> */

                </script>';





        #
        # auto-create link with com article
        #
        # set new post id
        $sql = "UPDATE lhgtransverse_posts SET `postid_de` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $newPostID, $postid);
	$result = $lhg_price_db->query($safe_sql);

        # set permalink
        # not possible since title has not been translated yet
        #$permalink = get_permalink($newPostID, true);
        #print "PL: $permalink";
        #$sql = "UPDATE lhgtransverse_posts SET `permalink_de` = \"%s\" WHERE postid_com = %s";
	#$safe_sql = $lhg_price_db->prepare($sql, $permalink, $postid);
	#$result = $lhg_price_db->query($safe_sql);

        # Todo: post icon to be transferred

        if ($result_icon != "")  {
		$upload_dir = wp_upload_dir();
                $image_url="http://www.linux-hardware-guide.com".$result_icon;
		$image_data = file_get_contents($image_url);
		$filename = basename($image_url);

                print "FN: $filename <br>";
                print "IU: $image_url <br>";

                if(wp_mkdir_p($upload_dir['path']))
    			$file = $upload_dir['path'] . '/' . $filename;
		else
    			$file = $upload_dir['basedir'] . '/' . $filename;

		file_put_contents($file, $image_data);

		$wp_filetype = wp_check_filetype($filename, null );

                $attachment = array(
		    'post_mime_type' => $wp_filetype['type'],
		    'post_title' => sanitize_file_name($filename),
		    'post_content' => '',
		    'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file, $newPostID );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		set_post_thumbnail( $newPostID, $attach_id );

	}

}

function lhg_link_new_DB_entry_in_scan ( $postid, $mode, $mode_data, $sid ){

  global $lhg_price_db;

  #
  ### Link hwscan DB entry with the corresponding auto-created postid
  #

  if ($mode == "usb") {
        $usbids = $mode_data;

	$sql = "SELECT id FROM `lhghwscans` WHERE usbid = %s AND sid = %s ";
	$safe_sql = $lhg_price_db->prepare($sql, $usbids, $sid);
  	$id = $lhg_price_db->get_var($safe_sql);
        $error = $lhg_price_db->last_error;
	if ($error != "") { var_dump($error); die(); }



        $sql = "UPDATE lhghwscans SET `postid` = \"%s\" WHERE id = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $postid, $id);
	$result = $lhg_price_db->query($safe_sql);
        $error = $lhg_price_db->last_error;
	if ($error != "") { var_dump($error); die(); }

  }

  if ( ($mode == "cpu") or ($mode == "drive") ){
        $idstring = $mode_data;

	$sql = "SELECT id FROM `lhghwscans` WHERE idstring = %s AND sid = %s ";
	$safe_sql = $lhg_price_db->prepare($sql, $idstring, $sid);
  	$id = $lhg_price_db->get_var($safe_sql);
        $error = $lhg_price_db->last_error;
	if ($error != "") { var_dump($error); die(); }



        $sql = "UPDATE lhghwscans SET `postid` = \"%s\" WHERE id = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $postid, $id);
	$result = $lhg_price_db->query($safe_sql);
        $error = $lhg_price_db->last_error;
	if ($error != "") { var_dump($error); die(); }

  }

}

function lhg_create_new_DB_entry_post ( $postid, $mode , $mode_data ) {

  # $postid    ... Post ID of article that was automatically created
  # $mode      ... "usb", "cpu", "drive"
  # $mode_data ... Additional data for mode (i.e. USB IDs, CPU identifier, drive identifier


  #print "PID: $postid<br>MODE: $mode -> $mode_data<br>";


  #
  ### Create entry in LHG-PriceDB
  #
  global $lhg_price_db;

  #  1. check if already existing
  $sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE postid_com = ".$postid;
  $result = $lhg_price_db->get_var($sql);

  if ($result != "") {
  	//already in DB
        #echo "found<br>";

  }elseif ($result == "0") {
  	#echo "found 2<br>";

  }else{
  	#echo "Update needed<br>";
        #echo "Inser pid: $postid_com";
        //write permalink to DB

        $sqlinsert = "INSERT INTO lhgtransverse_posts (postid_com, origin, status_com) VALUES ('$postid', 'autocreate', 'draft')";
	$resultB = $lhg_price_db->query($sqlinsert);
  }

  #print "A: $result<br>";
  #print "B: $resultB<br>";

  #
  ### Set additional data (USB ID, PCI ID, ...)
  #

  if ($mode == "usb") {
        $usbids = $mode_data;

        $sql = "UPDATE lhgtransverse_posts SET `usbids` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $usbids, $postid);
	$result = $lhg_price_db->query($safe_sql);

  }

  if ($mode == "cpu") {
        $idstring = $mode_data;

        $sql = "UPDATE lhgtransverse_posts SET `idstring` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $idstring, $postid);
	$result = $lhg_price_db->query($safe_sql);

  }

  if ($mode == "drive") {
        $idstring = $mode_data;
        #error_log("update idstring to $mode_data");
        $sql = "UPDATE lhgtransverse_posts SET `idstring` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $idstring, $postid);
	$result = $lhg_price_db->query($safe_sql);

  }

  if ($mode == "laptop") {
        $idstring = $mode_data;

        $sql = "UPDATE lhgtransverse_posts SET `pciids` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $idstring, $postid);
	$result = $lhg_price_db->query($safe_sql);

  }

  #print "C: $result<br>";

}

function lhg_get_AMZ_ID_from_scan ( $sid, $mode , $mode_data ) {

  #
  ### Get the Amazon ID provided by user for a scan
  #

  # $sid       ... Scan ID 
  # $mode      ... "usb", "cpu", "drive"
  # $mode_data ... Additional data for mode (i.e. USB IDs, CPU identifier, drive identifier

  global $lhg_price_db;

  # get URL
  if ($mode == "usb") {
        $usbids = $mode_data;
	$sql = "SELECT url FROM `lhghwscans` WHERE sid = %s and usbid = %s";
  	$safe_sql = $lhg_price_db->prepare($sql, $sid, $usbids);
  	$url = $lhg_price_db->get_var($safe_sql);
  }

  if ($mode == "cpu") {
        $usbids = $mode_data;
	$sql = "SELECT url FROM `lhghwscans` WHERE sid = %s and scantype = %s";
  	$safe_sql = $lhg_price_db->prepare($sql, $sid, "cpu");
  	$url = $lhg_price_db->get_var($safe_sql);
  }

  if ($mode == "drive") {
        $id = $mode_data;
	$sql = "SELECT url FROM `lhghwscans` WHERE sid = %s and id = %s";
  	$safe_sql = $lhg_price_db->prepare($sql, $sid, $id);
  	$url = $lhg_price_db->get_var($safe_sql);
  }

  if ($mode == "mainboard") {
        $id = $mode_data;
	$sql = "SELECT mb_url FROM `lhgscansessions` WHERE sid = %s";
  	$safe_sql = $lhg_price_db->prepare($sql, $sid);
  	$url = $lhg_price_db->get_var($safe_sql);
  }

  #print "URL: $sid - $usbids - $url<br>";

  # get Amamzon ID from URL
  $pos   = strpos($url,"/B0");
  $amzid = substr($url, $pos+1,10);

  return $amzid;
}

#
### change status of post if post is published
#
add_action( 'transition_post_status', 'lhg_change_db_status', 10, 3);
function lhg_change_db_status ( $new_status, $old_status, $post ) {
        $PID = $post->ID;

/*
print "

PID: $PID <br>
NStat: $new_status<br>
Otat: $old_status<br>";
var_dump($post);

#die;
*/
        if ($new_status == "publish") {
        	global $lhg_price_db;
	        $sql = "UPDATE lhgtransverse_posts SET `status_com` = \"published\" WHERE postid_com = %s";
	        $safe_sql = $lhg_price_db->prepare($sql,$PID);
	        $result  = $lhg_price_db->query($safe_sql);

                $error = $lhg_price_db->last_error;
		if ($error != "") var_dump($error);
		if ($error != "") var_dump($safe_sql);

		if ($error != "") die;
	}


        # set title, contents, categories, tags


}

function lhg_clean_cpu_title ( $title ) {

  # get GHz
  $pos_ghz_start = strpos($title," @");
  $pos_ghz_end   = strpos($title,"GHz");
  $ghz = substr($title,$pos_ghz_start+2,$pos_ghz_end-$pos_ghz_start+1);
  #print "GHZ: $ghz, $pos_ghz_start, $pos_ghz_end";
  if ($pos_ghz_start != "") $title = substr($title,0,$pos_ghz_start).substr($title,$pos_ghz_end+3);
  $title = wp_strip_all_tags($title);
  $title = str_replace("(tm)","",$title);
  $title = str_replace("CPU @","",$title);
  $title = str_replace("CPU ","",$title);
  $title = str_replace("(TM)","",$title);
  $title = str_replace("Processor","",$title);

  $title = str_replace("(R)","",$title);
  $title = str_replace("i3-","i3 ",$title);
  $title = str_replace("i4-","i4 ",$title);
  $title = str_replace("i5-","i5 ",$title);
  $title = str_replace("i6-","i6 ",$title);
  $title = $title." (CPU, ".$ghz;

  if (preg_match('/Dual Core/',$title)) {
  	$title = str_replace("Dual Core","",$title);
        $title = $title.", Dual Core";
  }

  $title = $title.")";

  while (preg_match('/  /',$title)) {
    $title = str_replace("  "," ",$title);
  }
  return $title;
}

function lhg_clean_drive_title ( $title ) {
  # strip white spaces
  $title = preg_replace('/\s+/', ' ',$title);

  if (substr($title,0,5) == " ATA ") $title = substr($title,5);
  if (substr($title,0,4) == "ATA ") $title = substr($title,4);

  $title = str_replace("WDC ","Western Digital ",$title);
  $title = str_replace("ATAPI "," ",$title);
  $title = str_replace(" BD-RW "," ",$title);
  $title = str_replace("HGST ","Hitachi Travelstar ",$title);
  $title = str_replace("Generic- "," ",$title);
  $title = str_replace("TOSHIBA ","Toshiba ",$title);
  $title = str_replace("MATSHITA ","Matshita ",$title);
  $title = str_replace("ASUS ","Asus ",$title);
  $title = str_replace("SAMSUNG ","Samsung ",$title);
  $title = str_replace("TSSTcorp ","Toshiba / Samsung ",$title);
  $title = str_replace("INTENSO ","Intenso ",$title);
  $title = str_replace("HITACHI ","Hitachi ",$title);
  $title = str_replace("SEAGATE ","Seagate ",$title);
  $title = str_replace("MAXTOR ","Maxtor ",$title);

  # HTS Hitachi Drive but no Hitachi name
  if ( preg_match('/HTS[0-9][0-9]/',$title) && (!preg_match('/Hitachi/i',$title)) ) $title = "Hitachi ".$title;
  if ( preg_match('/HDS[0-9][0-9]/',$title) && (!preg_match('/Hitachi/i',$title)) && (!preg_match('/Deskstar /i',$title)) ) $title = "Hitachi Deskstar ".$title;
  if ( preg_match('/HDS[0-9][0-9]/',$title) && (!preg_match('/Hitachi/i',$title)) ) $title = "Hitachi ".$title;
  if ( (substr($title,0,2) == "CT") && preg_match('/CT[0-9][0-9][0-9]/',$title) && (!preg_match('/Crucial/i',$title)) ) $title = "Curcial ".$title;

  if (substr($title,0,2) == "ST") $title = "Seagate ".$title;


  return $title;
}

function lhg_clean_pci_title ( $title ) {
  $otitle=$title;
  # strip white spaces
  $title = preg_replace('/\[....:....\]/', '',$title);
  $title = preg_replace('/ \[....\]:/', ':',$title);
  $title = preg_replace('/\(rev ..\)/', '',$title);
  $title = str_replace("Advanced Micro Devices, Inc. [AMD]","AMD",$title);
  $title = str_replace("Advanced Micro Devices, Inc. [AMD/ATI]","AMD/ATI",$title);
  $title = str_replace("(PHY/Link)","",$title);
  $title = str_replace("Realtek Semiconductor Co., Ltd. ","Realtek ",$title);

  if ($title == "") $title = $otitle."ERROR1";

  return $title;
}

function lhg_clean_usb_title ( $title ) {

  $title = wp_strip_all_tags($title);
  $title_orig = $title;

  $title = str_replace("ASMedia Technology Inc. ASMedia","ASMedia",$title);

  $title = str_replace("(tm)","",$title);
  $title = str_replace(", Inc.","",$title);
  $title = str_replace("Corp. ","",$title);
                      
  $title = str_replace("Samsung Electronics Co., Ltd","Samsung",$title);
  $title = str_replace(" Ltd"," ",$title);

  $title = str_replace("Techology Inc. ","",$title);
  $title = str_replace("Inc.","",$title);

  $title = str_replace("America Info. Systems","",$title);
  $title = str_replace("Belkin Components","Belkin",$title);
  $title = str_replace("ASUSTek Comupter","Asus",$title);
  $title = str_replace("Pte.","",$title);

  $title = str_replace(",","",$title);

  $title = $title." (";

  $type = "???";
  if (preg_match('/Mouse/',$title)) {
     $title = str_replace("Mouse","",$title);
     $title = $title."Mouse";
  }

  $title = $title.", USB";
  $title = $title.")";

  while (preg_match('/  /',$title)) {
    $title = str_replace("  "," ",$title);
  }


  return $title;
}

function lhg_clean_mainboard_name ( $title  ) {
        $otitle = $name;

        # Remove or rename some long names or placeholders
	$title = str_replace("Gigabyte Technology Co., Ltd. ","Gigabyte ",$title);
	$title = str_replace("System manufacturer System Product Name","",$title);
	$title = str_replace("To be filled by O.E.M./","",$title);
	$title = str_replace("To Be Filled By O.E.M./","",$title);
	$title = str_replace("To be filled by O.E.M.","",$title);
	$title = str_replace("To Be Filled By O.E.M.","",$title);
        $title = str_replace("\\xffffffff","",$title);

	$s=explode(", BIOS",$title);
        $title=trim($s[0]);
        if ($title == "") $title = " ";


        # check if name starts with "/"
        if ( substr($title,0,1 ) == "/") $title=substr($title,1);


        # check if name twice given, separated by "/"
        # i.e. check if part after "/" is existing twice
        $s=explode("/",$title);

        if ( trim($s[1]) != "")
        if ( strpos(substr($title,0,strpos($title, "/") ), trim($s[1]) ) > 0) $title = trim($s[0]);


        # make title beautiful
        $title = str_replace("PIONEER","Pioneer", $title);
        $title = str_replace("ZOTAC","Zotac", $title);
        $title = str_replace("FUJITSU","Fujitsu", $title);
        $title = str_replace("SAMSUNG ELECTRONICS CO., LTD.","Samsung", $title);
        $title = str_replace("SAMSUNG","Samsung", $title);
        $title = str_replace("TOSHIBA","Toshiba", $title);
        $title = str_replace("SATELLITE","Satellite", $title);
        $title = str_replace("LENOVO ","Lenovo ", $title);
        $title = str_replace("LIFEBOOK","Lifebook", $title);
        $title = str_replace("COMPUTER INC.","", $title);
        $title = str_replace("ASUSTeK ","Asus ", $title);
        $title = str_replace("ASUS ","Asus ", $title);
        $title = str_replace("Dell Inc. ","Dell ", $title);
        $title = str_replace("CO., LTD "," ", $title);
        $title = str_replace("MICRO-STAR INTERNATIONAL ","MSI ", $title);
        $title = str_replace("Fujitsu // Phoenix Technologies Ltd.","Fujitsu ", $title);

        # check if ID before and after / is identical
	list($p1, $p2) =explode("/",$title);
        list($id2, $null) = explode(" ",$p2);
        $idlength = strlen($id2);

        $cutid1 = substr($p1,-1*$idlength);
        $cutid2 = substr($p2,0,$idlength);
        #print "IDL: $idlength - $cutid1 - $cutid2 <br>";

        if ($cutid1 == $cutid2) $title = $p1." ".substr($p2,$idlength);

        $title = str_replace("("," ", $title);
        $title = str_replace(")"," ", $title);


        return $title;
}


function lhg_get_short_title ( $title ) {
	$s=explode("(",$title);
	$short_title=trim($s[0]);
	$title_part2=str_replace(")","",trim($s[1]));
	return $short_title;
}

function lhg_get_title_metas ( $title ) {
	$s=explode("(",$title);
	$short_title=trim($s[0]);
	$title_part2=str_replace(")","",trim($s[1]));

        #remove leading comma
        if ( substr( $title_part2, 0,1) == ",") $title_part2 = substr( $title_part2 , 1);

	return $title_part2;
}

function lhg_get_mainboard_fingerprint ( $sid  ) {
        # list of pci ids whch are onboard
	$pci_obl = array();


        # search for scanned items
        global $lhg_price_db;
        $myquery = $lhg_price_db->prepare("SELECT id, postid, usbid, pciid, idstring , usercomment , url , scantype FROM `lhghwscans` WHERE sid = %s AND postid = 0 AND pciid <> ''", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$unidentified_hw_pci = $lhg_price_db->get_results($myquery);


        foreach($unidentified_hw_pci as $a_identified_hw){

  		$pciid = ($a_identified_hw->pciid);
  		$title = ($a_identified_hw->idstring);
                $id=( $a_identified_hw->id );

  		$is_onboard = lhg_pci_component_is_onboard( $title, $sid, $id, $pciid);

                #print "ID: $id > $pciid - OB: $is_onboard<br>";
		if ($is_onboard == "yes")  { array_push( $pci_obl , $pciid); }

	}
        #use , as separator!
        $pcistring = implode(",",$pci_obl);
        return $pcistring;
        #print "PCI-Onboard: "; var_dump( $pci_obl );

}

function lhg_update_title_by_string($pid, $string, $mode)  {

        $title = get_the_title( $pid );
        $titlearray = explode("(",$title);
        $title_main = $titlearray[0];
        $title_prop = $titlearray[1];
        $title_prop = str_replace(")", "", $title_prop);
        $props = explode(",",$title_prop);

        # Clean broken properties. Provide array of strings to be omitted
        $del_vals=array("tel", "D");
        foreach ($del_vals as $del_val) {
                #error_log("Search: $del_val");
	        if(($key = array_search(" ".$del_val, $props)) !== false) {
                    #error_log("FOUND - cleaning");
		    unset($props[$key]);
		}
	}

        #print "TITLE: $title_main + $title_prop<br>";

        # 1. Harddisk
        if ( preg_match("/[0-9][0-9][0-9]GB/i", $string, $match) == 1 ) array_push($props, "Harddisk");
        if ( preg_match("/[0-9][0-9][0-9] GB/i", $string, $match) == 1 ) array_push($props, "Harddisk");
        if ( preg_match("/ [0-9].[0-9] TB/i", $string, $match) == 1 ) array_push($props, "Harddisk");
        if ( preg_match("/ [0-9].[0-9]TB/i", $string, $match) == 1 ) array_push($props, "Harddisk");
        if ( preg_match("/ [0-9] TB/i", $string, $match) == 1 ) array_push($props, "Harddisk");
        if ( preg_match("/ [0-9]TB/i", $string, $match) == 1 ) array_push($props, "Harddisk");

        # 2. Storage capacity
        if ( preg_match("/[0-9][0-9][0-9]GB/i", $string, $match) == 1 ) array_push($props, substr($match[0],0,-2)." GB" );
        if ( preg_match("/[0-9][0-9][0-9] GB/i", $string, $match) == 1 ) array_push($props, $match[0]);
        if ( preg_match("/ [0-9].[0-9] TB/i", $string, $match) == 1 ) array_push($props, $match[0]);
        if ( preg_match("/ [0-9].[0-9]TB/i", $string, $match) == 1 ) array_push($props, substr($match[0],0,-2)." TB" );
        if ( preg_match("/ [0-9] TB/i", $string, $match) == 1 ) array_push($props, $match[0]);
        if ( preg_match("/ [0-9]TB/i", $string, $match) == 1 ) array_push($props, $match[0]);


        # search for special word
        if ( stristr($string, "LGA 1155") != false ) array_push($props,"Socket 1155");
        if ( stristr($string, "LGA1366") != false ) array_push($props,"Socket 1366");
        if ( stristr($string, "LGA 1366") != false ) array_push($props,"Socket 1366");
        if ( stristr($string, "Socket AM2") != false ) array_push($props,"Socket AM2");
        if ( stristr($string, "Quad-Core") != false ) array_push($props,"Quad Core");
        if ( stristr($string, "Dual-Core") != false ) array_push($props,"Dual Core");
        if ( stristr($string, "Socket G2") != false ) array_push($props,"Socket G2");

        #repair broken cache string
        if ( preg_match("/ [0-9]{3}K Cache/i", $string, $match) == 1 ) array_push($props, str_replace("K Cache","KB Cache",$match[0]));
        if ( preg_match("/ [0-9]{1} MB Cache/i", $string, $match) == 1 ) array_push($props, $match[0]);
        if ( preg_match("/ [0-9]{2} MB Cache/i", $string, $match) == 1 ) array_push($props, $match[0]);
        if ( preg_match("/ [0-9]{1}MB Cache/i", $string, $match) == 1 ) array_push($props, substr($match[0],0,-8)." MB Cache" );
        if ( preg_match("/ [0-9]{2}MB Cache/i", $string, $match) == 1 ) array_push($props, substr($match[0],0,-8)." MB Cache" );
        if ( preg_match("/ [0-9]{1}M Cache/i", $string, $match) == 1 ) array_push($props, substr($match[0],0,-7)." MB Cache" );
        if ( preg_match("/ [0-9]{2}M Cache/i", $string, $match) == 1 ) array_push($props, substr($match[0],0,-7)." MB Cache" );

        # Sometimes CPU title do not have the word "Cache"
        if ( ( preg_match("/Cache/i", $string, $match) === false ) && ( preg_match("/Intel/i", $string, $match) == 1 ) && ( preg_match("/ [0-9] MB /i", $string, $match) == 1 ) )  array_push($props, $match[0]."Cache");
        if ( ( preg_match("/Cache/i", $string, $match) === false ) && ( preg_match("/Intel/i", $string, $match) == 1 ) && ( preg_match("/ [0-9][0-9] MB /i", $string, $match) == 1 ) )  array_push($props, $match[0]."Cache");

        if ( preg_match("/ [0-9].[0-9] GHz/i", $string, $match) == 1 ) array_push($props, $match[0]);


        if ( preg_match("/ [0-9][0-9][0-9][0-9]RPM/i", $string, $match) == 1 ) array_push($props, substr($match[0],0,-3)." RPM");
        if ( preg_match("/ [0-9][0-9][0-9][0-9] RPM/i", $string, $match) == 1 ) array_push($props, substr($match[0],0,-3)." RPM");

        if ( preg_match("/DVD.*RW/i", $string, $match) == 1 ) array_unshift($props, "DVD Writer");
        if ( preg_match("/DVD/i", $string, $match) == 1 ) array_push($props, "Optical Drive");
        if ( preg_match("/ DVD /i", $string, $match) == 1 ) array_push($props, "DVD");
        if ( preg_match("/DVD-Rom/i", $string, $match) == 1 ) array_push($props, "DVD");
        if ( preg_match("/ CD /i", $string, $match) == 1 ) array_push($props, "CD");
        if ( preg_match("/Optical Drive/i", $string, $match) == 1 ) array_push($props, "Optical Drive");
        if ( preg_match("/Dual Layer/i", $string, $match) == 1 ) array_push($props, "Dual Layer");
        if ( preg_match("/SSD/i", $string, $match) == 1 ) $props = array_diff( $props, array("Harddisk") );
        if ( preg_match("/SSD/i", $string, $match) == 1 ) array_unshift($props, "SSD");
        if ( preg_match("/Solid State Disk/i", $string, $match) == 1 ) array_unshift($props, "SSD");
        if ( preg_match("/2.5\"/i", $string, $match) == 1 ) array_push($props, "2.5 Inch");
        if ( preg_match("/3.5\"/i", $string, $match) == 1 ) array_push($props, "3.5 Inch");
        if ( preg_match("/2.5-Inch/i", $string, $match) == 1 ) array_push($props, "2.5 Inch");
        if ( preg_match("/3.5-Inch/i", $string, $match) == 1 ) array_push($props, "3.5 Inch");
        if ( preg_match("/ IDE /i", $string, $match) == 1 ) array_push($props, "IDE");



        # get props by taglist
        $tagarray = lhg_taglist_by_title ( $string );
        foreach ($tagarray as $tagnr)  {

		$result = get_term_by('id', $tagnr, 'post_tag');
        	$tag_name = $result->name;
                #print "NAme: $tag_name";

                #check if already part of title. Only add to properties if not
                if ( stristr($title_main, $tag_name) == false ) array_push($props,$tag_name);

	}


        # Order: HDD first, size, rest
        #if ($mode = "drive")

        # clean strings in array (remove spaces)
        $props_tmp = array();
        foreach ($props as $prop)  {
                if (trim($prop) != "") array_push($props_tmp, trim($prop) );
	}
        # Remove duplicates
        $props = array_unique($props_tmp);

        #error_log("Props: ".join("< ",$props) );

        # Clean main title
        $title_main = str_replace(" CPU", "", $title_main);

        $newtitle = $title_main." (".join(", ",$props).")";
        $newtitle = sanitize_text_field( $newtitle );


	$args['ID'] = $pid;
        $args['post_title' ] =  "<!--:us-->".$newtitle."<!--:-->";
        wp_update_post( $args );

        return $newtitle;
        #print "New: $newtitle<br>";
}

function lhg_update_categories_by_string($pid, $string, $mode)  {

        # get existing categories
        $categories = get_the_category( $pid );

        # use category by title to extract additional categories
        $categories_array = lhg_category_by_title( $string );

        # merge both lists
        foreach ($categories as $category) {
                array_push($categories_array, $category->term_id);
	}

        # update article
        wp_set_post_categories ( $pid, $categories_array, true );

}

function lhg_update_tags_by_string($pid, $string, $mode)  {
        # Extend the list of article tags by matching tags associated with the string, e.g. title
        #print "<br>Update tags PID: $pid -> $string<br>";

        #$tags = wp_get_post_tags($pid);
        $tags = get_tags( $pid );

        #print "Old Tags:";
        #print_r($tags);

        # investigate string
        $words = explode(" ", $string);
        $foundtags = array();
        $foundtags_slugs = array();

        $oldword = $word;
        foreach ($words as $word) {
                if ($word != " ") {

	                $result = get_term_by('slug', $word, 'post_tag');
        	        #print "Word: $word<br>";#$result<br>";

                	#check if we have a corresponding tag
	                foreach ($tags as $tag) {
        	                $tag_name = $tag->name;
                                $tag_slug = $tag->slug;
                	        #var_dump( $tag );

                                #1. check if word exists as tag
                                #
                                #
                        	if ( strcasecmp( $tag_name , $word ) == 0 ) {
                                        array_push( $foundtags, $tag_name );
                                        array_push( $foundtags_slugs, $tag_slug );
                                        #print "&nbsp;&nbsp; $tag_name == $word";
	                        	#print "1W FOUND<br>";

                                        break;
				}else {
                	                #print "<br>";
                        	}

        			#2. check combination of two words
	                        #
                                #
                                #print "2W: $oldword $word<br>";
                                #print "&nbsp; --> $tag_name<br>";
                        	if ( strcasecmp( $tag_name , $oldword." ".$word ) == 0 ) {
                                        array_push( $foundtags, $tag_name );
                                        array_push( $foundtags_slugs, $tag_slug );
                                        #print "&nbsp;&nbsp; $tag_name == $oldword $word";
	                        	#print "2W FOUND<br>";
                                        break;
				}else {
                	                #print "<br>";
                        	}



	                }

                        $oldword = $word;

                }
	}

        #
        # Scan for miscellaneous word
        #
        # Use the tag recognition
        $tagids = lhg_taglist_by_title ( $string );
        #error_log("Tagids: ".join(",",$tagids) );
        foreach ($tagids as $tagid)  {
                $tagslug = get_term_by('id',$tagid,'post_tag');
                $result = $tagslug->slug;
                array_push ( $foundtags_slugs, $result );
        }


        $foundtags_slugs = array_unique($foundtags_slugs);

	# SSD is no harddisk
  	if (preg_match('/SSD/',$string)) $foundtags_slugs = array_diff( $foundtags_slugs , array("harddisk") );

        # Debug
        $out = join(", ",$foundtags_slugs);
        #error_log( "Res: $out" );
        #var_dump($foundtags_slugs);


        #
        # Add tags to post
        #

        foreach ($foundtags_slugs as $foundslug) {
                #print "<br>adding: $foundslug";
                wp_set_post_tags( $pid, $foundslug, true);
        }

        $tags = wp_get_post_tags($pid);
        #print "<br>New Tags:";
        #print_r($tags);
        return $tags;

}


function lhg_convert_tag_array_to_string ( $taglist  ) {
  # convert tag id array to string of tag names
  $tagstring = "";
  foreach ($taglist as $a_tag) {
          $tag = get_tag ($a_tag);
          $tag_as_string = $tag->name;
          $tagstring .= $tag_as_string.",";
  }
  $tagstring = substr($tagstring,0,-1);
  #print "Tagstring: $tagstring<br>";
  return $tagstring;
}


function lhg_category_by_title ( $title  ) {
  $catlist = array();

  if (preg_match('/DVD writer/i',$title)) array_push ( $catlist , 478);
  if (preg_match('/ External/i',$title)) array_push ( $catlist , 333);
  if (preg_match('/ SSD/i',$title)) array_push ( $catlist , 583);
  if (preg_match('/Ethernet/i',$title)) array_push ( $catlist , 5);
  if (preg_match('/Ethernet/i',$title)) array_push ( $catlist , 322);

  return $catlist;
}

function lhg_taglist_by_title ( $title  ) {
        $taglist = array();

  #error_log("Title: $title");

  # CPU
  if (preg_match('/Processor/i',$title)) array_push ( $taglist , 874);
  if (preg_match('/Core I7/i',$title)) array_push ( $taglist , 878);
  if (preg_match('/Core I7/i',$title)) array_push ( $taglist , 372);
  if (preg_match('/Core I7/i',$title)) array_push ( $taglist , 372);
  if (preg_match('/LGA 1155/i',$title)) array_push ( $taglist , 532);
  if (preg_match('/LGA1155/i',$title)) array_push ( $taglist , 532);
  if (preg_match('/LGA1156/i',$title)) array_push ( $taglist , 988);
  if (preg_match('/LGA 1156/i',$title)) array_push ( $taglist , 988);
  if (preg_match('/LGA775/i',$title)) array_push ( $taglist , 537);
  if (preg_match('/LGA 775/i',$title)) array_push ( $taglist , 537);
  if (preg_match('/LGA1366/i',$title)) array_push ( $taglist , 819);
  if (preg_match('/LGA 1366/i',$title)) array_push ( $taglist , 819);

  if (preg_match('/AMD/',$title)) array_push ( $taglist , 520);
  if (preg_match('/Sempron/',$title)) array_push ( $taglist , 875);
  if (preg_match('/Pentium/',$title)) array_push ( $taglist , 881);
  if (preg_match('/Pentium/',$title)) array_push ( $taglist , 372);

  if (preg_match('/Intel/',$title)) array_push ( $taglist , 372);
  if (preg_match('/ i3-/',$title)) array_push ( $taglist , 887);
  if (preg_match('/ i5-/',$title)) array_push ( $taglist , 486);
  if (preg_match('/ i7-/',$title)) array_push ( $taglist , 878);
  if (preg_match('/Athlon(tm) II/',$title)) {
  	array_push ( $taglist , 880);
  } elseif (preg_match('/Athlon/',$title)) {
     	array_push ( $taglist , 882);
  }

  # Mainboard / Laptop
  if (preg_match('/Fujitsu/i',$title)) array_push ( $taglist , 755);
  if (preg_match('/Fujitsu/i',$title)) array_push ( $taglist , 756);
  if (preg_match('/Dell/i',$title)) array_push ( $taglist , 712);
  if (preg_match('/Thinkpad/i',$title)) array_push ( $taglist , 757);
  if (preg_match('/Lenovo/i',$title)) array_push ( $taglist , 367);
  if (preg_match('/MSI /i',$title)) array_push ( $taglist , 539);
  if (preg_match('/Asus/i',$title)) array_push ( $taglist , 497);
  if (preg_match('/Lifebook/i',$title)) array_push ( $taglist , 756);
  if (preg_match('/Hewlett-Packard/i',$title)) array_push ( $taglist , 439);
  if (preg_match('/Compaq/i',$title)) array_push ( $taglist , 952);
  if (preg_match('/Compaq Presario/i',$title)) array_push ( $taglist , 197);
  if (preg_match('/Notebook/i',$title)) array_push ( $taglist , 197);
  if (preg_match('/Optiplex/i',$title)) array_push ( $taglist , 997);
  if (preg_match('/Optiplex/i',$title)) array_push ( $taglist , 830); #Desktop PC

  # USB
  if (preg_match('/Laser/',$title)) array_push ( $taglist , 681);
  if (preg_match('/Hitachi/',$title)) array_push ( $taglist , 905);
  if (preg_match('/Belkin/',$title)) array_push ( $taglist , 698);
  if (preg_match('/Trust/',$title)) array_push ( $taglist , 644);
  if (preg_match('/Seagate/',$title)) array_push ( $taglist , 904);
  if (preg_match('/Logitech/',$title)) array_push ( $taglist , 361);
  if (preg_match('/Logilink/',$title)) array_push ( $taglist , 523);
  if (preg_match('/Microsoft/',$title)) array_push ( $taglist , 612);
  if (preg_match('/Keyboard/',$title)) array_push ( $taglist , 630);
  if (preg_match('/Wired/',$title)) array_push ( $taglist , 1009);

  if (preg_match('/Mouse/',$title)) {
     array_push ( $taglist , 634);
     $category = 475; // IO
     $type = "mouse";
  }

  # Drive
  if (preg_match('/Hitachi/',$title)) array_push ( $taglist , 905);
  if (preg_match('/Seagate/',$title)) array_push ( $taglist , 904);
  if (preg_match('/Western Digital/',$title)) array_push ( $taglist , 854);
  if (preg_match('/SSD/',$title)) array_push ( $taglist , 583);

  if (preg_match('/Intel/',$title)) array_push ( $taglist , 372);
  if (preg_match('/TSSTcorp/',$title)) array_push ( $taglist , 465);
  if (preg_match('/CDDVD/',$title)) array_push ( $taglist , 146);
  if (preg_match('/CDDVD/',$title)) array_push ( $taglist , 326);
  if (preg_match('/DVD RW/',$title)) array_push ( $taglist , 146);
  if (preg_match('/DVD RW/',$title)) array_push ( $taglist , 326);
  if (preg_match('/DVD+-RW/',$title)) array_push ( $taglist , 331);
  if (preg_match('/DVD+-RW/',$title)) array_push ( $taglist , 582);
  if (preg_match('/MATSHITA/i',$title)) array_push ( $taglist , 985);
  if (preg_match('/Toshiba/i',$title)) array_push ( $taglist , 557);
  if (preg_match('/Samsung/i',$title)) array_push ( $taglist , 465);
  if (preg_match('/ DRW-/i',$title)) array_push ( $taglist , 582);
  if (preg_match('/ DRW-/i',$title)) array_push ( $taglist , 331);
  if (preg_match('/DVD writer/i',$title)) array_push ( $taglist , 582);
  if (preg_match('/ External/i',$title)) array_push ( $taglist , 333);
  if (preg_match('/Optiarc/',$title)) array_push ( $taglist , 754);
  if (preg_match('/SATA /i',$title)) array_push ( $taglist , 346);
  if (preg_match('/3.5 /i',$title)) array_push ( $taglist , 903);
  if (preg_match('/3.5-Inch/i',$title)) array_push ( $taglist , 903);

  #Hitachi
  if (preg_match('/HTS[0-9][0-9]/',$title)) array_push ( $taglist , 905);
  if (preg_match('/HDS[0-9][0-9]/',$title)) array_push ( $taglist , 905);

  # SSD is no harddisk
  if (preg_match('/SSD/',$title)) $taglist = array_diff( $taglist , array(584) );

        return $taglist;
}


# Automatic translation of article titles from English to German
function lhg_translate_title_en_to_de( $title )  {
        # ToDo: Translate only properties, not product ID

	$title = str_replace("Optical Drive","Optisches Laufwerk",$title);
	$title = str_replace("Socket","Sockel",$title);
	$title = str_replace("socket","Sockel",$title);
	$title = str_replace(" Burner","-Brenner",$title);
	$title = str_replace(" Writer","-Brenner",$title);
	$title = str_replace("External","Extern",$title);
	$title = str_replace("Hard Drive","Festplatte",$title);
	$title = str_replace("Hard Disk","Festplatte",$title);
	$title = str_replace("Harddisk","Festplatte",$title);
	$title = str_replace("Inch","Zoll",$title);
	$title = str_replace("DVD Drive","DVD-Laufwerk",$title);
	$title = str_replace("DVD Player","DVD-Laufwerk",$title);
	$title = str_replace("Internal","Intern",$title);
	$title = str_replace("internal","Intern",$title);
	$title = str_replace("Flash Drive","Memory-Stick",$title);
	$title = str_replace("Low-Profile","Kleine Abmessungen",$title);
	$title = str_replace("Motherboard","Mainboard",$title);
	$title = str_replace("Laser Printer","Laser-Drucker",$title);
	$title = str_replace("Inkjet Printer","Tintenstrahl-Drucker",$title);
	$title = str_replace("All-In-One","Multifunktionsgerät",$title);
	$title = str_replace("Copier","Kopierer",$title);
	$title = str_replace("Color","Farbe",$title);
	$title = str_replace("WiFi","WLAN",$title);
	$title = str_replace("Graphics Card","Grafikkarte",$title);
	$title = str_replace("Mouse","Maus",$title);
	$title = str_replace("Keyboard","Tastatur",$title);
	$title = str_replace("Pen Tablet","Stift-Tablett",$title);
	$title = str_replace("PCI Express","PCI-Express",$title);
	$title = str_replace("Sound Card","Soundkarte",$title);
	$title = str_replace("Smart Card Reader","Smart-Card Leseger&auml;t",$title);
	$title = str_replace("Card Reader","Karten-Leseger&auml;t",$title);
	$title = str_replace("Chipset","Chipsatz",$title);

        return $title;
}

# automatic created articles should get the publisher as author
add_filter( 'wp_dropdown_users', lhg_correct_authors );

function lhg_correct_authors( $input ) {

        if ( isset($_GET['action'])  && $_GET['action'] === 'edit' ){
	        # only run checks on edit screens
	}else{
                return $input;
        }

        global $post;
	$uid = get_current_user_id();
        $pid = $_GET['post'];
        $status=get_post_status ( $pid );
        #error_log("Status: ".$status);

        # Only change author if not published yet
        if ($status != "draft") return $input;
        # admin can still delegate authorship, others get automatically assigned to themselves
        if ($uid == 1) return $input;


        # Which author is currently set?
        $author_id = isset( $post->post_author ) ? $post->post_author : null;
        # Author was set before. We show the full selector in such cases
        if ($author_id == $uid) return $input;


        $user_info = get_userdata( $uid );
        $output = "Author corrected to ".$user_info->display_name." (UID: $uid)
        <select name='post_author_override' id='post_author_override' style='visibility: hidden;' class=''>
        <option value='".$uid."' selected='selected'></option>
        </select>
        ";
    	#error_log("Correct author: $pid -> $uid (was: $author_id)");

	$myPost = array(
                        'ID' => $pid,
			'post_author' => $uid,
		);
        wp_update_post( $myPost );
        $post->post_author = $uid;
        return $output;

}

function lhg_search_long_title( $title , $dmesg ) {

        # look if the title was truncated and if a longer version exists in dmesg output
        $otitle = $title;
        $longtitle = ""; # longer version of title

        $title = trim(str_replace(" ATA ", "", $title));
        #error_log('Search for "'.$title.'"');

        # search for all appearances of "title" in dmesg output and search for longer version:
        $keys = array_keys( $dmesg, $title);
        $keyword = $title;

        foreach($dmesg as $key => $arrayItem){
        	if( stristr( $arrayItem, $keyword ) ){
	            #error_log( "Key: $key - ".$dmesg[$key] );
        	    $foundkey = $key;
                    $startpos = strpos( $arrayItem, $keyword);
                    $endpos = strpos( $arrayItem, ",");

                    # candidate found - compare lengths
                    # see if longer than former finding
		    $newname = substr( $arrayItem, $startpos, $endpos-$startpos ) ;
                    if ( ( strlen($newname) > strlen($title) ) && ( strlen($newname) > strlen($longtitle) ) ) $longtitle = $newname;
        	}
  	}

        # Did we find something, then return longer title, otherwise original title is returned
        #error_log("Old tite: $otitle -> new title: $longtitle");
        if (strlen($longtitle) < strlen($title)) return $otitle;
        return $longtitle;
}

# autocreated articles have comments deactivated (for non-admins)
# we have to correct this
add_action( 'save_post', 'lhg_correct_post_comment_status', 10, 2 );
add_action( 'transition_post_status', 'lhg_correct_post_comment_status', 10, 2);
add_action( 'edit_post', 'lhg_correct_post_comment_status', 10, 2 );
function lhg_correct_post_comment_status( $post_ID, $post_after ) {

        # check if this is draft or pending
        if (!is_int( $post_ID ) ) return;

        global $wpdb;
        if ( $post_after->comment_status != "open" ) {
                #error_log("Comments closed for post $post_ID -> reopen");

                # open the comment section
                $sql = "UPDATE wp_posts set comment_status = 'open' WHERE ID  = $post_ID ";
        	$result = $wpdb->query($sql);
                #error_log("SQL: ".$wpdb->last_error);

	}
}


# code to be executed
function lhg_url_request_autotranslate(  ) {

}

# move comment to transverse server
function lhg_url_request_move_comment(  ) {



        # ToDo: no support for attachments

	global $lhg_price_db;
        global $lang;

        if (!current_user_can("moderate_comments")){
                print "You are not allowed to moderate comments";
                exit;
	}

        $cid = $_GET['cid'];

        #$parsed = parse_url( $_SERVER['REQUEST_URI'] );
        #print "Parsed: ".$parsed['query']."<br>";
        print "Move comment ".$_GET['cid']."<br>";

        # set json conterpart and admin GUID
	if ($_SERVER['SERVER_ADDR'] == "192.168.56.12") {
		$url = "http://192.168.56.13/json";
        	$guid = 9;
	}

	if ($_SERVER['SERVER_ADDR'] == "192.168.56.13") {
		$url = "http://192.168.56.12/json";
        	$guid = 9;
	}

	if ($_SERVER['SERVER_ADDR'] == "192.168.3.112") {
		$url = "http://192.168.3.113/json";
        	$guid = 22;
	}

	if ($_SERVER['SERVER_ADDR'] == "192.168.3.113") {
		$url = "http://192.168.3.112/json";
        	$guid = 22;
	}

        # get comment
        $comment = get_comment($cid);

        # get JSON password for transverse requests
        $sql = "SELECT json_password FROM `lhgtransverse_users` WHERE id = \"%s\" ";
	$safe_sql = $lhg_price_db->prepare( $sql, $guid );
	$password = $lhg_price_db->get_var($safe_sql);

        # get GUID of comment owner
        if ($lang == "de") $sql = "SELECT id FROM `lhgtransverse_users` WHERE wpuid_de = \"%s\" ";
        if ($lang != "de") $sql = "SELECT id FROM `lhgtransverse_users` WHERE wpuid = \"%s\" ";
	$safe_sql = $lhg_price_db->prepare( $sql, $comment->user_id );
	$comment_guid = $lhg_price_db->get_var($safe_sql);

        if ($lang == "de") $server="de";
        if ($lang != "de") $server="com";

        $data = array (
                'comment_guid' => $comment_guid,
                'comment_postid' => $comment->comment_post_ID,
                'password' => $password,
                'request' => 'move_comment',
                'comment_id' => $cid,
                'comment_content' => $comment->comment_content,
                'comment_date' => $comment->comment_date,
                'comment_date_gmt' => $comment->comment_date_gmt,
                'comment_author' => $comment->comment_author,
                'comment_author_email' => $comment->comment_author_email,
                'comment_author_url' => $comment->comment_author_url,
                'comment_author_IP' => $comment->comment_author_IP,
                'comment_agent' => $comment->comment_agent,
                'commentid_server' => $server
        );

        // request the action
        $response = wp_remote_post( $url,
        		array( 'body' => $data, 'timeout' => 20 )
	            );

        wp_redirect( "/wp-admin/edit-comments.php?comment_status=approved" );

        # Todo: delete comments (implement only after functioning transfer was tested

        exit;


}

# starting automatic translation
function lhg_create_article_translation( $postid, $postid_server, $data ) {



        #error_log("PID: $postid - $postid_server");

	global $lhg_price_db;
        global $lang;

        if ($postid_server != com) {
                print "Translation only tested from com -> de";
                print "Stopping for safety reasons";
                exit;
	}

        # tag translation
        list($tagarray_ids, $tagarray_names) = lhg_create_article_translation_tags( $postid, $postid_server );

        # title translation
        $translated_title = lhg_create_article_translation_title( $postid, $postid_server );

        #error_log("TT: $translated_title");

        # icon
        $icon = lhg_create_article_translation_icon( $postid, $postid_server, $data );

        # category translation
        $category_ids = lhg_create_article_translation_categories( $postid, $postid_server );

        # content translation
        $result_content = lhg_create_article_translation_content( $postid, $postid_server );


        # create new article
	$myPost = array(
			'post_status' => 'publish',
                        'post_content' => $result_content,
			'post_type' => 'post',
			'post_author' => 1,
			'post_title' =>  $translated_title,
			'post_category' => $category_ids,
                        'tags_input' => $tagarray_names,
                        'comment_status' => 'open'
		);

  	$newPostID = wp_insert_post($myPost);

        #error_log("Created article: $newPostID");


        # add amazon id
        # local Amazon ID
        lhg_create_article_translation_amazonid( $newPostID, $data["ASIN"] );


	#update_post_meta($newPostID, 'amazon-product-single-asin', $amazon_id );
        #if ($amazon_id == "") $amazon_id = $data["ASIN"];
        if ($lang == "de") lhg_amazon_create_db_entry( "de", $newPostID, $amazon_id );

        #
        # auto-create link with com article
        #
        # set new post id
        $sql = "UPDATE lhgtransverse_posts SET `postid_de` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $newPostID, $postid);
	$result = $lhg_price_db->query($safe_sql);

        # link icon with article
	lhg_create_article_translation_iconcreation( $icon, $newPostID, $postid_server, "create" );


        # create history entry
        $lang_from = "en";
        $lang_to = "de";
        $postid_from = $postid;
        $postid_to = $newPostID;
  	lhg_post_history_translation( $lang_from, $lang_to, $postid_from, $postid_to, $guid);



}

# starting automatic translation update
function lhg_update_article_translation( $postid, $postid_server, $data ) {



        #error_log("PID: $postid - $postid_server");

	global $lhg_price_db;
        global $lang;

        if ($postid_server != com) {
                print "Translation only tested from com -> de";
                print "Stopping for safety reasons";
                exit;
	}

        # tag translation
        list($tagarray_ids, $tagarray_names) = lhg_create_article_translation_tags( $postid, $postid_server );

        # title translation
        $translated_title = lhg_create_article_translation_title( $postid, $postid_server );

        #error_log("TT: $translated_title");

        # icon - do not update the icon
        $icon = lhg_create_article_translation_icon( $postid, $postid_server, $data);

        # category translation
        $category_ids = lhg_create_article_translation_categories( $postid, $postid_server );

        # content translation
        $result_content = lhg_create_article_translation_content( $postid, $postid_server );


        # get translated postid
	if ($lang == "de") $sql = "SELECT `postid_de`  FROM `lhgtransverse_posts` WHERE postid_com = %s";
	if ($lang != "de") $sql = "SELECT `postid_com` FROM `lhgtransverse_posts` WHERE postid_de = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $translated_postid = $lhg_price_db->get_var($safe_sql);

        #error_log("Updating pid: $translated_postid");

        # create new article
	$myPost = array(
                        'ID' => $translated_postid,
                        'post_status' => 'publish',
                        'post_content' => $result_content,
			'post_type' => 'post',
			'post_author' => 1,
			'post_title' =>  $translated_title,
			'post_category' => $category_ids,
                        'tags_input' => $tagarray_names,
                        'comment_status' => 'open'
		);

  	wp_update_post($myPost);

        #error_log("Created article: $newPostID");


        # add amazon id
        # local Amazon ID
        lhg_create_article_translation_amazonid( $translated_postid, $data["ASIN"] );


        #if ($amazon_id == "") $amazon_id = $data["ASIN"];
        #error_log("AID: ".$amazon_id);
        #error_log("data: ".json_encode($data) );
	#update_post_meta($translated_postid, 'amazon-product-single-asin', $amazon_id );
        #if ($lang == "de") lhg_amazon_create_db_entry( "de", $translated_postid, $amazon_id );

        #
        # auto-create link with com article
        #
        # set new post id
        #$sql = "UPDATE lhgtransverse_posts SET `postid_de` = \"%s\" WHERE postid_com = %s";
	#$safe_sql = $lhg_price_db->prepare($sql, $newPostID, $postid);
	#$result = $lhg_price_db->query($safe_sql);

        # link icon with article
	lhg_create_article_translation_iconcreation( $icon, $translated_postid, $postid_server, "update"  );


        # create history entry
        $lang_from = "en";
        $lang_to = "de";
        $postid_from = $postid;
        $postid_to = $translated_postid;
  	lhg_post_history_translation_update( $lang_from, $lang_to, $postid_from, $postid_to, $guid);


}


#
# if an article is updated an automatic translation can be checked
# if article is already translated and modified, translation will not be done
#
add_action ('edit_post', 'lhg_initiate_autotranslate' );

function lhg_initiate_autotranslate( $postid ) {
	global $lhg_price_db;

        #error_log("starting auto-translation after article update or publishing");

        #first check if article was already translated
        global $lang;

        # check if article was already published
        if ($lang == "de")  $sql = "SELECT postid_com FROM lhgtransverse_posts WHERE postid_de  = '%s' ";
        if ($lang != "de")  $sql = "SELECT postid_de  FROM lhgtransverse_posts WHERE postid_com = '%s' ";
	$safe_sql = $lhg_price_db->prepare( $sql,  $postid );
	$translated_postid = $lhg_price_db->get_var($safe_sql);

        if ($translated_postid > 0) {
                # translation already exists
                $article_translated = 1;
	}else{
                # initiate translation
                lhg_initiate_autotranslate_by_json_request( $postid );
                return;
        }

        # check if article was already edited
        if ($lang == "de")  $sql = "SELECT timestamp FROM lhgtransverse_post_history WHERE postid_com  = '%s' AND chage_type = '%s' ";
        if ($lang != "de")  $sql = "SELECT timestamp  FROM lhgtransverse_post_history WHERE postid_de   = '%s' AND chage_type = '%s' ";
	$safe_sql = $lhg_price_db->prepare( $sql,  $translated_postid, "acticle_edited" );
	$edited_timestamp = $lhg_price_db->get_var($safe_sql);


        if ( ($article_translated == 1) && !($edited_timestamp > 0 ) ) {
                # article was auto translated but never modified.
                # Consequently, we can overwrite the article
                lhg_initiate_autotranslate_update_by_json_request( $postid );
                return;
        }
}

function lhg_initiate_autotranslate_by_json_request( $postid ) {

        #error_log("Translation request started");
	global $lhg_price_db;
        global $lang;

        # set json conterpart and admin GUID

	if ($_SERVER['SERVER_ADDR'] == "192.168.56.12") {
		$url = "http://192.168.56.13/json";
        	$guid = 9;
	}

	if ($_SERVER['SERVER_ADDR'] == "192.168.56.13") {
		$url = "http://192.168.56.12/json";
        	$guid = 9;
	}

	if ($_SERVER['SERVER_ADDR'] == "192.168.3.112") {
		$url = "http://192.168.3.113/json";
        	$guid = 22;
	}

	if ($_SERVER['SERVER_ADDR'] == "192.168.3.113") {
		$url = "http://192.168.3.112/json";
        	$guid = 22;
	}


        $sql = "SELECT json_password FROM `lhgtransverse_users` WHERE id = \"%s\" ";
	$safe_sql = $lhg_price_db->prepare( $sql, $guid );
	$password = $lhg_price_db->get_var($safe_sql);

        if ($lang == "de") $server = "de";
        if ($lang != "de") $server = "com";

        $data = array (
                'guid' => $guid,
                'password' => $password,
                'request' => 'create_article_translation',
                'postid' => $postid,
                'postid_server' => $server
        );

        # add ASIN to request if available
	$key = "amazon-product-single-asin";
  	if($val = get_post_meta($postid, $key, TRUE)) {
	  	$data['ASIN'] = $val;
  	}

        # post thumbnail to be transferred
        if ( has_post_thumbnail( $postid ) ) { // check if the post has a Post Thumbnail assigned to it.
		#$imgurl = get_the_post_thumbnail_url( $postid );

                $post = get_post( $postid );
		$post_thumbnail_id = get_post_thumbnail_id( $post );

		$imgurl = wp_get_attachment_url( get_post_thumbnail_id( $postid ) );

		if ( $imgurl != "" ) {
                        #$imgurl = wp_get_attachment_image_src( $post_thumbnail_id, 'thumbnail-size', true );
        	        $data['thumbnail'] = $imgurl;
		}
	}


        // request the action
        $response = wp_remote_post( $url,
        		array( 'body' => $data, 'timeout' => 20 )
	            );

        #error_log("Json request posted");

}


# update translation if it never was modified in between
function lhg_initiate_autotranslate_update_by_json_request( $postid ) {

	global $lhg_price_db;
        global $lang;

        # set json conterpart and admin GUID

	if ($_SERVER['SERVER_ADDR'] == "192.168.56.12") {
		$url = "http://192.168.56.13/json";
        	$guid = 9;
	}

	if ($_SERVER['SERVER_ADDR'] == "192.168.56.13") {
		$url = "http://192.168.56.12/json";
        	$guid = 9;
	}

	if ($_SERVER['SERVER_ADDR'] == "192.168.3.112") {
		$url = "http://192.168.3.113/json";
        	$guid = 22;
	}

	if ($_SERVER['SERVER_ADDR'] == "192.168.3.113") {
		$url = "http://192.168.3.112/json";
        	$guid = 22;
	}


        $sql = "SELECT json_password FROM `lhgtransverse_users` WHERE id = \"%s\" ";
	$safe_sql = $lhg_price_db->prepare( $sql, $guid );
	$password = $lhg_price_db->get_var($safe_sql);

        if ($lang == "de") $server = "de";
        if ($lang != "de") $server = "com";

        $data = array (
                'guid' => $guid,
                'password' => $password,
                'request' => 'article_translation_update',
                'postid' => $postid,
                'postid_server' => $server
        );

        # add ASIN to request if available
	$key = "amazon-product-single-asin";
  	if($val = get_post_meta($postid, $key, TRUE)) {
	  	$data['ASIN'] = $val;
  	}

        # post thumbnail to be transferred
        if ( has_post_thumbnail( $postid ) ) { // check if the post has a Post Thumbnail assigned to it.
		#$imgurl = get_the_post_thumbnail_url( $postid );

                $post = get_post( $postid );
		$post_thumbnail_id = get_post_thumbnail_id( $post );

		$imgurl = wp_get_attachment_url( get_post_thumbnail_id( $postid ) );

		if ( $imgurl != "" ) {
                        #$imgurl = wp_get_attachment_image_src( $post_thumbnail_id, 'thumbnail-size', true );
        	        $data['thumbnail'] = $imgurl;
		}
	}

        // request the action
        $response = wp_remote_post( $url,
        		array( 'body' => $data, 'timeout' => 20 )
	            );

}

# translate the tags to new article
function lhg_create_article_translation_tags( $postid, $postid_server ) {

        #
        # get tags
        #
        global $lhg_price_db;

	$sql = "SELECT `tagids_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $result_tags = $lhg_price_db->get_var($safe_sql);

        $tagarray_slug = explode(",",$result_tags);

        $tagarray_ids  = array();
        $tagarray_names= array();
        foreach($tagarray_slug as $tagarray_s){

	  # get "de" slug from DB
  	  $sql = "SELECT `slug_de` FROM `lhgtransverse_tags` WHERE slug_com = %s";
          $safe_sql = $lhg_price_db->prepare($sql, $tagarray_s);
          $de_tag = $lhg_price_db->get_var($safe_sql);

          #fallback
          #print "ERROR: tag ($tagarray_s) not found -> fallback used<br>";
          if ($de_tag == "") $de_tag = $tagarray_s;

          $tmp = get_term_by('slug', $de_tag , 'post_tag');
          #var_dump($tmp); print "<br>";
	  array_push($tagarray_ids, $tmp->term_id );
	  array_push($tagarray_names, $tmp->name );

	}

        return array($tagarray_ids, $tagarray_names);
}

# translate title
function lhg_create_article_translation_title( $postid, $postid_server ) {

        #
        # get title
        #
        global $lhg_price_db;

	$sql = "SELECT `title_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $result_title = $lhg_price_db->get_var($safe_sql);

	$result_title_de = lhg_translate_title_en_to_de( $result_title );

        return $result_title_de;
}

# transfer icon
# $data ... full JSON object
function lhg_create_article_translation_icon( $postid, $postid_server, $data ) {

        #
        # get icon
        #
        global $lhg_price_db;

        $result_icon = $data['thumbnail'];

        if ($result_icon == "") {
        	$sql = "SELECT `icon_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        	$safe_sql = $lhg_price_db->prepare($sql, $postid);
	        $result_icon = $lhg_price_db->get_var($safe_sql);
	}

        return $result_icon;
}

# translate categories
function lhg_create_article_translation_categories( $postid, $postid_server ) {

        #
        # get categories
        #
        global $lhg_price_db;

	$sql = "SELECT `categories_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $result_categories = $lhg_price_db->get_var($safe_sql);

        $category_slugs = explode(",",$result_categories);
        $category_ids   = array();

        foreach($category_slugs as $cat_slug){

          $catid = get_category_by_slug($cat_slug);
          #var_dump ($catid);

          # 1. auto recognition by slug
          if ( ($catid->cat_ID) != "") array_push($category_ids, $catid->cat_ID );

          # 2. not all categories are found by english slugs.
          # No use for automatic detection. We simply identify them manually here:
          if ($cat_slug == "cctv" ) array_push($category_ids, 663);
          if ($cat_slug == "internal" ) array_push($category_ids, 335);
          if ($cat_slug == "ultrabook" ) array_push($category_ids, 589);
          if ($cat_slug == "all-in-one-printer" ) array_push($category_ids, 368);
          if ($cat_slug == "external" ) array_push($category_ids, 333);
          if ($cat_slug == "ssd" ) array_push($category_ids, 601);
          if ($cat_slug == "printer" ) array_push($category_ids, 323);
          if ($cat_slug == "laser-printer" ) array_push($category_ids, 488);
          if ($cat_slug == "graphiccards" ) array_push($category_ids, 507);
          if ($cat_slug == "network" ) array_push($category_ids, 5);

	}
        return $category_ids;
}

# translate content
function lhg_create_article_translation_content( $postid, $postid_server ) {

        #
        # get content
        #
        global $lhg_price_db;

	$sql = "SELECT `postcontent_com` FROM `lhgtransverse_posts` WHERE postid_com = %s";
        $safe_sql = $lhg_price_db->prepare($sql, $postid);
        $result_content = $lhg_price_db->get_var($safe_sql);

        $result_content = str_replace("<!--:us-->","",$result_content);
        $result_content = str_replace("<!--:-->","",$result_content);
        # for safety reasons we had to replace chars when storing them in PriceDB. Now we need to
        # revert this (normally only for the [code] block but we do not distinguish text and code yet - ToDo!)
        $result_content = str_replace("&lt;","<",$result_content);
        $result_content = str_replace("&gt;",">",$result_content);
        $result_content = str_replace("&quot;","\"",$result_content);
        $result_content = str_replace("&amp;","&",$result_content);

        return $result_content;
}

# translate content
function lhg_create_article_translation_amazonid( $postid, $asin ) {

        #error_log("ASIN defined: $asin for $postid ");


        # use AMAZON ID that was transmitted by JSON request
        #if ($asin != "") return;


        # create post meta data
        update_post_meta($postid, 'amazon-product-single-asin', $asin );
        update_post_meta($postid, 'amazon-product-content-hook-override', 2 );
        update_post_meta($postid, 'amazon-product-content-location', 1 );
        update_post_meta($postid, 'amazon-product-excerpt-hook-override', 2 );
        update_post_meta($postid, 'amazon-product-isactive', 1 );
        update_post_meta($postid, 'amazon-product-newwindow', 3 );

        if ($lang == "de") lhg_amazon_create_db_entry( "de", $translated_postid, $amazon_id );

        return;
               $output = lhg_aws_get_price($asin,"com");
                 list($image_url_com, $product_url_com, $price_com , $product_title, $label, $brand, $image_url_com2, $image_url_com3 , $image_url_com4, $image_url_com5 ) = split(";;",$output);

                 $product_title = str_replace("Title: ","", $product_title);

               $output = lhg_aws_get_price($asin,"fr");
                 list($image_url_fr, $product_url_fr, $price_fr) = split(";;",$output);

               $output = lhg_aws_get_price($asin,"de");
                 list($image_url_de, $product_url_de, $price_de) = split(";;",$output);

                 $image_url_com   = str_replace("Image: ","", $image_url_com);
                 $image_url_com2   = str_replace("Image2: ","", $image_url_com2);
                 $image_url_com3   = str_replace("Image3: ","", $image_url_com3);


        return;
}


# created post image
function lhg_create_article_translation_iconcreation( $result_icon, $newPostID, $postid_server, $type ) {


        #error_log("NPID: $newPostID -> ".has_post_thumbnail( $newPostID ) );

        if ( has_post_thumbnail( $newPostID ) ) { // check if the post has a Post Thumbnail assigned to it.
		#$imgurl = get_the_post_thumbnail_url( $postid );

                #$post = get_post( $newPostID );
		#$post_thumbnail_id = get_post_thumbnail_id( $post );

		$imgurl = wp_get_attachment_url( get_post_thumbnail_id( $newPostID ) );

	}

        # compare the image file names. The paths can be different between .de and .com server
        if ( end(split("/",$result_icon)) == end(split("/",$imgurl)) ) {
                # nothing to be done
                #error_log("Already correct image used");
                return;
	}

        #error_log("Update image $result_icon");
        #error_log("Old image $imgurl");

        if ($result_icon != "")  {

        	if ($_SERVER['SERVER_ADDR'] == "192.168.56.12"){
			$murl = "http://192.168.56.13";
		}

		if ($_SERVER['SERVER_ADDR'] == "192.168.56.13") {
			$murl = "http://192.168.56.12";
		}

		if ($_SERVER['SERVER_ADDR'] == "192.168.3.112") {
			$murl = "http://192.168.3.113";
		}

		if ($_SERVER['SERVER_ADDR'] == "192.168.3.113") {
			$murl = "http://192.168.3.112";
		}


  		$upload_dir = wp_upload_dir();
                $image_url= $murl.$result_icon;
		$image_data = file_get_contents($image_url);
		$filename = basename($image_url);

                #print "FN: $filename <br>";
                #print "IU: $image_url <br>";

                if(wp_mkdir_p($upload_dir['path']))
    			$file = $upload_dir['path'] . '/' . $filename;
		else
    			$file = $upload_dir['basedir'] . '/' . $filename;

		file_put_contents($file, $image_data);

		$wp_filetype = wp_check_filetype($filename, null );

                $attachment = array(
		    'post_mime_type' => $wp_filetype['type'],
		    'post_title' => sanitize_file_name($filename),
		    'post_content' => '',
		    'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file, $newPostID );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		set_post_thumbnail( $newPostID, $attach_id );

	}
}

# created comment based on transfer request
function lhg_create_comment_by_json_request( $data ) {

        global $lhg_price_db;
        global $lang;

        # get postid comment needs to be associated with
        $o_postid = $data["comment_postid"];
        if ( $data["commentid_server"] == "com" ) $sql = "SELECT postid_de  FROM `lhgtransverse_posts` WHERE postid_com = \"%s\" ";
        if ( $data["commentid_server"] == "de" )  $sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE postid_de  = \"%s\" ";
	$safe_sql = $lhg_price_db->prepare( $sql, $o_postid );
	$new_postid = $lhg_price_db->get_var($safe_sql);

        # get user id comment needs to be associated with
        $comment_guid = $data["comment_guid"];
        if ($lang == "de") $sql = "SELECT wpuid_de FROM `lhgtransverse_users` WHERE id = \"%s\" ";
        if ($lang != "de") $sql = "SELECT wpuid    FROM `lhgtransverse_users` WHERE id = \"%s\" ";
	$safe_sql = $lhg_price_db->prepare( $sql, $comment_guid );
	$new_uid = $lhg_price_db->get_var($safe_sql);

        $data = array(
                'comment_post_ID' => $new_postid,
                'user_id' => $new_uid,
                'comment_date' => $data["comment_date"],
                'comment_date_gmt' => $data["comment_date_gmt"],
                'comment_content' => $data["comment_content"],
                'comment_agent' => $data["comment_agent"],
                'comment_author' => $data["comment_author"],
                'comment_author_email' => $data["comment_author_email"],
                'comment_author_url' => $data["comment_author_url"],
                'comment_author_IP' => $data["comment_author_IP"],
        );

        wp_insert_comment( $data );
        exit;
}

# publish article based on scan overview selections (AJAX initiated)
function lhg_scan_publish_mainboard_article( $_sid, $_postid, $_asin_mb, $_title_mb, $_idarray_pci, $_idarray_usb, $_idarray_tags, $_type ) {

  global $lhg_price_db;
  global $lang;

  # article creation should be limited to .com server
  if ($lang == "de") {
                error_log("Article creation on .de server should not happen. ID: $id");
                return;
  }

 # $laptop_probability = lhg_scan_is_laptop( $sid );
 #
 # // if mainboard
 # if ($laptop_probability < 0.8) {
 #       $category = 472;
 #       $taglist = array( 472 );
 # } else { #or if laptop
 #       $category = 470;
 #       $taglist = array( 470 );
 #       $taglist = array( 450 );
 # }

 # # Download only once for speed improvement
 # global $lspci_content_from_library;
 # global $dmesg_content_from_library;
 # global $lsb_content_from_library;
 # global $version_content_from_library;
 #
 #
 # if ( $lspci_content_from_library == "" ) {
 #       $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=lspci.txt";
 # 	$lspci_content_from_library = file_get_contents($url);
 # }
 #
 # if ( $dmesg_content_from_library == "" ) {
 #         $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=dmesg.txt";
 #         $dmesg_content_from_library = file_get_contents($url);
 # }
 #
 # if ( $lsb_content_from_library == "" ) {
 #         $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=lsb_release.txt";
 #         $lsb_content_from_library = file_get_contents($url);
 # }
 #
 # if ( $version_content_from_library == "" ) {
 #         $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=version.txt";
 #      	  $version_content_from_library = file_get_contents($url);
 # }

#$lspci = explode("\n\n",$lspci_content_from_library);
##print "<br>Dump:".var_dump($lspci)."<br>";
#$lspci0 = $lspci[0];
#$lspci0 = str_replace("\n\n","",$lspci0);
#
#
#	# create filtered and unfiltered list of all PCI IDs as array $pci_array_all
#	$lspci_array = explode("\n",$lspci_content_from_library);
#        $pcilist = array();
#
#        foreach ($lspci_array as $line) {
#                #print "L $i:".$line."<br>";
#                $pciid_found = preg_match("/\[....:....\]/",$line,$matches);
#                $subsystem_found = preg_match("/Subsystem/",$line,$matches2);
#                #print preg_match("/\[....:....\]/",$line,$matches)." - ".var_dump($matches)."<br>";
#
#                $clean_pciid = $matches[0];
#                $clean_pciid = str_replace("[","",$clean_pciid);
#                $clean_pciid = str_replace("]","",$clean_pciid);
#                # PCI ID found, but no Subsystem ID
#                if ( ( $pciid_found == 1 ) && ( $subsystem_found == 0) ) array_push($pcilist, $clean_pciid);
#        }
#        $pci_array_all = $pcilist;





	# get distribution name from scan data base
	$sql = "SELECT distribution FROM `lhgscansessions` WHERE sid = \"".$_sid."\"";
    	$result = $lhg_price_db->get_results($sql);
        $result0 = $result[0];
        $distribution = $result0->distribution;

	# get kernel version of scan 
	$sql = "SELECT kversion FROM `lhgscansessions` WHERE sid = \"".$_sid."\"";
    	$result = $lhg_price_db->get_results($sql);
        $result0 = $result[0];
        $version = $result0->kversion;

	# get DMI output of scan
	$sql = "SELECT dmi FROM `lhgscansessions` WHERE sid = \"".$_sid."\"";
    	$result = $lhg_price_db->get_results($sql);
        $result0 = $result[0];
        $dmi_line = $result0->dmi;
        # Clean DMI string
        $dmi_line=str_replace("[    0.000000] ","",$dmi_line);

        # set categories
        if ($_type == "laptop") $categories = array( 470 );
        if ($_type == "mainbord") $categories = array( 472 );
        if ($_type == "ultrabook") $categories = array( 470, 562 );
        if ($_type == "pc-system") $categories = array( 469 );
        if ($_type == "low-power-pc") $categories = array( 469, 471 );
        if ($_type == "other") $categories = "";

	#create PCI list
        $lspci_list = "";
        $pci_array = array();
        $subid_array = array();
	foreach ($_idarray_pci as $pciid) {

                $sql = "SELECT pciid, idstring, pciid_subsystem, idstring_subsystem FROM `lhghwscans` WHERE sid = \"".$_sid."\" AND id = \"".$pciid."\"";
    		$result = $lhg_price_db->get_results($sql);
                #error_log( "SID: $_sid ID: $pciid -> ".print_r($result, true) );
	        $result0 = $result[0];
        	$pciid_result = $result0->idstring;
                $lspci_list .= $pciid_result;
                #if ($result0->pciid_subsystem != "")    $lspci_list .= "\n".$result0->pciid_subsystem;
                if ($result0->idstring_subsystem != "") $lspci_list .= $result0->idstring_subsystem;
                #$lspci_list .= "\n";
                #error_log("PCIID: $pciid -> $result0->pciid"." ".$pciid_result ."\n".$result0->pciid_subsystem." ".$result0->idstring_subsystem);
                array_push($pci_array, $result0->pciid);
                array_push($subid_array, $result0->pciid_subsystem);
	}


	#create USB list
        $usb_list = "";
        $usb_array = array();
        if ($_idarray_usb != "")
	foreach ($_idarray_usb as $usbid) {

                $sql = "SELECT idstring, usbid FROM `lhghwscans` WHERE sid = \"".$_sid."\" AND id = \"".$usbid."\"";
    		$result = $lhg_price_db->get_results($sql);
	        $result0 = $result[0];
        	$usbid_result = $result0->idstring;
                $lsusb_list .= $result0->usbid." ".$usbid_result ."\n" ;
		array_push($usb_array, $result0->usbid);


	}

        #create tag array
        $tagstring = lhg_convert_tag_array_to_string( $_idarray_tags );

        #error_log("ToDo: Tags to be transferred to taxonomies");



$article = "[lhg_mainboard_intro distribution=\"".trim($distribution)."\" version=\"".trim($version)."\" dmi_output=\"".trim($dmi_line)."\"]

[lhg_mainboard_lspci]
".trim($lspci_list)."
[/lhg_mainboard_lspci]
";

if ($lsusb_list != "")
$article .= "
[lhg_mainboard_usb]
".trim($lsusb_list)."
[/lhg_mainboard_usb]
";

$title="<!--:us-->".$_title_mb."<!--:-->";

	$myPost = array(
			'ID' => $_postid,
			'post_status' => 'publish',
                        'post_content' => "<!--:us-->".$article."<!--:-->",
			'post_type' => 'post',
			'post_author' => 1,
			'post_title' =>  $title,
			'post_category' => $categories,
                        'tags_input' => $tagstring
		);

  	wp_update_post( $myPost );

        # create entry in lhgtransverse_posts
        $identifier = join(",",$pci_array);
	if ($_type == "laptop") {
	        $identifier = lhg_get_mainboard_fingerprint( $sid );
	        lhg_create_new_DB_entry_post ( $_postid, "laptop", $identifier );

                $sql = "UPDATE lhgtransverse_posts SET `categories_com` = \"%s\" WHERE postid_com = %s";
		$safe_sql = $lhg_price_db->prepare($sql, "notebook", $_postid);
		$result = $lhg_price_db->query($safe_sql);

	} else {
	        $identifier = lhg_get_mainboard_fingerprint( $sid );
		lhg_create_new_DB_entry_post ( $_postid, "mainboard", $identifier );

                $sql = "UPDATE lhgtransverse_posts SET `categories_com` = \"%s\" WHERE postid_com = %s";
		$safe_sql = $lhg_price_db->prepare($sql, "mainboards", $_postid);
		$result = $lhg_price_db->query($safe_sql);
	}


	# set Amazon ID
  	$key = "amazon-product-single-asin";
	$value = $_asin_mb;
  	if(get_post_meta($_postid, $key, FALSE)) { //if the custom field already has a value
  		update_post_meta($_postid, $key, $value);
	} else { //if the custom field doesn't have a value
  		add_post_meta($_postid, $key, $value);
	}

        # store PCI IDs
        lhg_set_pciids( $_postid, $pci_array );

        # store PCI IDs
        lhg_set_subids( $_postid, $subid_array );

        # store USB IDs
        lhg_set_usbids( $_postid, $usb_array );




exit;



#############
#############
# OLD STUFF


  # ToDo: should be created based on lspci and dmesg output

  $new_taglist = lhg_taglist_by_title( $title );
  $taglist = array_merge( $taglist, $new_taglist );
  $tagstring = lhg_convert_tag_array_to_string( $taglist );



  #print "Article creation started";

  #print "<br>Title: $title <br> ScanID: $sid<br>";

        $title="<!--:us-->".$title."<!--:-->";

	$myPost = array(
			'post_status' => 'draft',
                        'post_content' => "<!--:us-->".$article."<!--:-->",
			'post_type' => 'post',
			'post_author' => 1,
			'post_title' =>  $title,
			'post_category' => array($category),
                        'tags_input' => $tagstring,
		);
        global $wpdb;
	#$post_if = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title'");
        #print "PI: ".$post_if;

	$post_if2 = $wpdb->get_var("SELECT count(post_title) FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");
        #print "PI2: ".$post_if2;

        $sql = "SELECT created_postid FROM `lhghwscans` WHERE id = \"".$id."\" ";
        $created_id = $lhg_price_db->get_var($sql);


  if ( ($post_if2 > 0) or ($created_id != 0) ) {
  	#print "Title exists";
        if ($created_id != 0) $newPostID = $created_id;
        if ($created_id == 0) $newPostID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title like '$title' AND post_status = 'draft' ");

	# store created_id for already existing articles
	if ($created_id == 0)  {
                $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        	$result = $lhg_price_db->query($sql);
        }

  }else{
  	//-- Create the new post
        #print "new article";
  	$newPostID = wp_insert_post($myPost);
        $sql = "UPDATE `lhghwscans` SET created_postid = \"".$newPostID."\" WHERE id = \"".$id."\" ";
        $result = $lhg_price_db->query($sql);
  }
  #print "<br>done<br>";

  # ToDo: store MB in DB
  # ToDo: store MB in DB

  # Store scan info in DB
  #
  # get CPU identifier
  #$pos = strpos($cpu0, "model name");
  #$pos_end = strpos( substr($cpu0,$pos) , "\n");
  #$pos_colon = strpos( substr($cpu0,$pos) , ":");
  #print "POS: $pos - $pos_colon - $pos_end<br>";
  #print substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2)."<br>";
  #$cpu_identifier = substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2);


  if ($laptop_probability > 0.8) {
        # store all pci ids
        $identifier = lhg_get_mainboard_fingerprint( $sid );
        lhg_create_new_DB_entry_post ( $newPostID, "laptop", $identifier );

  } else {
        # store all pci ids
        # ToDo: filter pciids not onboard!
        $identifier = lhg_get_mainboard_fingerprint( $sid );
        #$identifier = implode(",",$pci_array_all);
	lhg_create_new_DB_entry_post ( $newPostID, "mainboard", $identifier );
  }

  # get Amazon ID, if available
  $amzid = lhg_get_AMZ_ID_from_scan ( $sid, "mainboard", "" );
  #print "AMZID CPU: $amzid";

  # set Amazon ID
  $key = "amazon-product-single-asin";
  $value = $amzid;

  if ($amzid != "")
  if(get_post_meta($newPostID, $key, FALSE)) { //if the custom field already has a value
  	update_post_meta($newPostID, $key, $value);
  } else { //if the custom field doesn't have a value
  	add_post_meta($newPostID, $key, $value);
  }

  # store in history that article was created
  lhg_post_history_scancreate( $newPostID, $sid);

  return $newPostID;


}

function lhg_set_usbids( $_postid, $_usbids ){
        # set USB IDs for existing article
        # expects array of USB IDs

        global $lhg_price_db;
        global $lang;

        if ($lang == "de") {
                error_log("Routine lhg_set_usbids only valid for .com server");
                exit;
        }

        $usbstring = join(",", $_usbids);
        $sql = "UPDATE lhgtransverse_posts SET `usbids` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $usbstring, $_postid);
	$result = $lhg_price_db->query($safe_sql);

        #error_log("set USB IDs for $_postid to $usbstring");

}

function lhg_set_pciids( $_postid, $_pciids ){
        # set PCI IDs for existing article
        # expects array of PCI IDs

        global $lhg_price_db;
        global $lang;

        if ($lang == "de") {
                error_log("Routine lhg_set_pciids only valid for .com server");
                exit;
        }

        $pcistring = join(",", $_pciids);
        $sql = "UPDATE lhgtransverse_posts SET `pciids` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $pcistring, $_postid);
	$result = $lhg_price_db->query($safe_sql);

        #error_log("set PCI IDs for $_postid to $pcistring");

}

function lhg_set_subids( $_postid, $_subids ){
        # set PCI IDs for existing article
        # expects array of PCI IDs

        global $lhg_price_db;
        global $lang;

        if ($lang == "de") {
                error_log("Routine lhg_set_subids only valid for .com server");
                exit;
        }

        $substring = join(",", $_subids);
        $sql = "UPDATE lhgtransverse_posts SET `subids` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $substring, $_postid);
	$result = $lhg_price_db->query($safe_sql);

        #error_log("set PCI IDs for $_postid to $pcistring");

}


?>