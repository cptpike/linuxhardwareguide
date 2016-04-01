<?php

function lhg_create_cpu_article ($title, $sid ) {

  global $lhg_price_db;
  global $cpus_from_library;
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



  #
  ### Clean CPU title
  #

  $title = lhg_clean_cpu_title($title);



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
  #print "POS: $pos - $pos_colon - $pos_end<br>";
  #print substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2)."<br>";
  $cpu_identifier = substr($cpu0,$pos+$pos_colon+2, $pos_end-$pos_colon-2);
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

  return $newPostID;


}

#
#
###### Mainboard article
#
#

function lhg_create_mainboard_article ($title, $sid ) {

  global $lhg_price_db;
  $otitle = $title;
  $title = lhg_clean_mainboard_name( $title );
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
  global $lspci_content_from_library;
  global $dmesg_content_from_library;
  global $lsb_content_from_library;
  global $version_content_from_library;


  if ( $lspci_content_from_library == "" ) {
	$url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=lspci.txt";
  	$lspci_content_from_library = file_get_contents($url);
  }

  if ( $dmesg_content_from_library == "" ) {
	  $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=dmesg.txt";
	  $dmesg_content_from_library = file_get_contents($url);
  }

  if ( $lsb_content_from_library == "" ) {
	  $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=lsb_release.txt";
	  $lsb_content_from_library = file_get_contents($url);
  }

  if ( $version_content_from_library == "" ) {
	  $url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=version.txt";
       	  $version_content_from_library = file_get_contents($url);
  }

$lspci = explode("\n\n",$lspci_content_from_library);
#print "<br>Dump:".var_dump($lspci)."<br>";
$lspci0 = $lspci[0];
$lspci0 = str_replace("\n\n","",$lspci0);


	# create filtered and unfiltered list of all PCI IDs as array $pci_array_all
	$lspci_array = explode("\n",$lspci_content_from_library);
        $pcilist = array();

        foreach ($lspci_array as $line) {
                #print "L $i:".$line."<br>";
                $pciid_found = preg_match("/\[....:....\]/",$line,$matches);
                $subsystem_found = preg_match("/Subsystem/",$line,$matches2);
                #print preg_match("/\[....:....\]/",$line,$matches)." - ".var_dump($matches)."<br>";

                $clean_pciid = $matches[0];
                $clean_pciid = str_replace("[","",$clean_pciid);
                $clean_pciid = str_replace("]","",$clean_pciid);
                # PCI ID found, but no Subsystem ID
                if ( ( $pciid_found == 1 ) && ( $subsystem_found == 0) ) array_push($pcilist, $clean_pciid);
        }
        $pci_array_all = $pcilist;






#print "URL: $url<br>";
#print "cont: <pre>".$cpu0."</pre>";

  # DMI entry
  $dmesg_content_array = split("\n",$dmesg_content_from_library);
  $dmi_array = preg_grep("/DMI: /",$dmesg_content_array);
  $dmi_line = implode("\n",$dmi_array);
  $dmi_line = str_replace("[    0.000000]","",$dmi_line);

  # Distribution
  $lsb_content_array = split("\n",$lsb_content_from_library);
  $lsb_array = preg_grep("/Description/",$lsb_content_array);
  $distribution = implode(" ",$lsb_array);
  $distribution = str_replace("Description:","",$distribution);
  while (preg_match("/  /",$distribution)){
        $distribution = str_replace("  "," ",$distribution);
  }


  # Kernel version
  $version_content_array = split("\n",$version_content_from_library);
  $version_array = preg_grep("/Linux version/",$version_content_array);
  $version_line = $version_array[0];
  $version_line = str_replace("Linux version ","",$version_line);
  list($version, $null) = split(" ",$version_line);

  $article =  'The '.$title." ";

  if ($laptop_probability > 0.8) $article .= 'is a laptop and ';

  $article .= 'was successfully tested in configuration
[code lang="plain" title="dmesg | grep DMI"]
'.$dmi_line.'
[/code]
under '.trim($distribution).' with Linux kernel version '.trim($version).'.

';

  $article .= '<h3>Hardware Overview</h3>
The following hardware components are part of the '.$title.' and are supported by the listed kernel drivers:
[code lang="plain" title="lspci -nnk"]
'.$lspci0.'
[/code]
';

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
  if(get_post_meta($newPostID, $key, FALSE)) { //if the custom field already has a value
  	update_post_meta($newPostID, $key, $value);
  } else { //if the custom field doesn't have a value
  	add_post_meta($newPostID, $key, $value);
  }

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
  $otitle = $title;

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

  $taglist = array( 584);

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

  # ATA drive
  if (strpos($title, "ATA ") !== false ) {
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
          foreach ($device_array as $line) $device_line = $line;
	  preg_match("/\[s..\]/i", $device_line, $device_name_array);
          $device_name = $device_name_array[0];
          $device_name_raw = substr($device_name,1,-1);
          #print "DNAme RAW:".$device_name_raw."<br>";
          #var_dump($device_array);

#          print "
#1: $ata_nr<br>
#2: $device_name<br>
#3: $device_name_raw<br>
#4: ".substr($ata_nr,0,4)."<br>
#5: $scsi_nr<br>"
#;
          # 5. extract all relevant lines
	  $dmesg_outputs = preg_grep("/(".$ata_nr."|\[".$device_name_raw."\]|".$device_name_raw."|".substr($ata_nr,0,4)."|".$scsi_nr.")/i", $dmesg);
          $clean_dmesg_outputs = array();
          foreach($dmesg_outputs as $dmesg_output){
                #$tmp = explode("] ", $dmesg_output);
                if ( strpos( $dmesg_output , "Stopping disk" ) !== false ) break;
                array_push( $clean_dmesg_outputs, substr( $dmesg_output, 15) );
	  }

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


  $title = lhg_clean_usb_title( $title );


  $article = 'The '.$title_orig.' is a USB '.$type.' with USB ID '.$usbid.'
[code lang="plain" title="lsusb"]
'.$lsusbOutput.'
[/code]
It is automatically recognized and fully supported by the Linux kernel:
[code lang="plain" title="dmesg"]
'.$dmesgOutput.'
[/code]
';

  $category_array = lhg_category_by_title ( $title  );


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


	echo "<h3>Start Auto-translate:</h3> ";

        print "PID com: $postid<br>";

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

          if ($tmp->term_id == "") print "ERROR: tag ".$tmp->name."not found <br>";
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
			'post_author' => 1,
			'post_title' =>  $result_title_translated,
			'post_category' => $category_ids,
                        'tags_input' => $tagarray_names,
		);

  	$newPostID = wp_insert_post($myPost);

        print '<a href="/wp-admin/post.php?post='.$newPostID.'&action=edit">New article '.$newPostID.'</a><br>';

        # add amazon id
	update_post_meta($newPostID, 'amazon-product-single-asin', $amazon_id );


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
	$title = str_replace("Gigabyte Technology Co., Ltd. ","Gigabyte ",$title);
	$s=explode(", BIOS",$title);
        $title=trim($s[0]);
        if ($title == "") $title = " ";

        # check if name twice given, separated by "/"
        # i.e. check if part after "/" is existing twice
        $s=explode("/",$title);

        if ( trim($s[1]) != "")
        if ( strpos(substr($title,0,strpos($title, "/") ), trim($s[1]) ) > 0) $title = trim($s[0]);


        # make title beautyful
        $title = str_replace("FUJITSU","Fujitsu", $title);
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
        #print "TITLE: $title_main + $title_prop<br>";

        # get props by taglist
        $tagarray = lhg_taglist_by_title ( $string );
        foreach ($tagarray as $tagnr)  {

		$result = get_term_by('id', $tagnr, 'post_tag');
        	$tag_name = $result->name;
                #print "NAme: $tag_name";

                #check if already part of title. Only add to properties if not
                if ( stristr($title_main, $tag_name) == false ) array_push($props,$tag_name);

	}

        # search for special word
        if ( stristr($string, "LGA 1155") != false ) array_push($props,"Socket LGA 1155");
        if ( stristr($string, "Quad-Core") != false ) array_push($props,"Quad Core");
        if ( preg_match("/[0-9] MB Cache/i", $string, $match) == 1 ) array_push($props, $match[0]);
        # Sometimes CPU title do not have the word "Cache"
        if ( ( preg_match("/Intel/i", $string, $match) == 1 ) && ( preg_match("/[0-9] MB /i", $string, $match) == 1 ) )  array_push($props, $match[0]."Cache");
        if ( preg_match("/[0-9][0-9][0-9]GB/i", $string, $match) == 1 ) array_unshift($props, $match[0]);
        if ( preg_match("/[0-9][0-9][0-9]GB/i", $string, $match) == 1 ) array_unshift($props, "Harddisk");
        if ( preg_match("/DVD.*RW/i", $string, $match) == 1 ) array_unshift($props, "DVD Writer");
        if ( preg_match("/DVD/i", $string, $match) == 1 ) array_push($props, "Optical Drive");
        if ( preg_match("/Optical Drive/i", $string, $match) == 1 ) array_push($props, "Optical Drive");
        if ( preg_match("/Dual Layer/i", $string, $match) == 1 ) array_push($props, "Dual Layer");
        if ( preg_match("/SSD/i", $string, $match) == 1 ) $props = array_diff( $props, array("Harddisk") );
        if ( preg_match("/SSD/i", $string, $match) == 1 ) array_unshift($props, "SSD");


        # Order: HDD first, size, rest
        #if ($mode = "drive")

        # clean strings in array (remove spaces)
        $props_tmp = array();
        foreach ($props as $prop)  {
                if (trim($prop) != "") array_push($props_tmp, trim($prop) );
	}

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

	$title = str_replace("Socket","Sockel",$title);
	$title = str_replace("socket","Sockel",$title);
	$title = str_replace(" Burner","-Brenner",$title);
	$title = str_replace(" Writer","-Brenner",$title);
	$title = str_replace("External","Extern",$title);
	$title = str_replace("Hard Drive","Festplatte",$title);
	$title = str_replace("Hard Disk","Festplatte",$title);
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

        return $title;
}

