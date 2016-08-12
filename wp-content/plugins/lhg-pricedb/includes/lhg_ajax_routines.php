<?php

# Ajax routines
# HW scan results: update data for new hardware (comment + url)
add_action('wp_ajax_lhg_scan_update_ajax', 'lhg_scan_update_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_ajax', 'lhg_scan_update_ajax');

# HW scan results: PCI onboard radio button "no" was pressen
add_action('wp_ajax_lhg_scan_onboardn_ajax', 'lhg_scan_onboardn_ajax');
add_action('wp_ajax_nopriv_lhg_scan_onboardn_ajax', 'lhg_scan_onboardn_ajax');

# HW scan results: PCI onboard radio button "yes" was pressen
add_action('wp_ajax_lhg_scan_onboardy_ajax', 'lhg_scan_onboardy_ajax');
add_action('wp_ajax_nopriv_lhg_scan_onboardy_ajax', 'lhg_scan_onboardy_ajax');

# HW scan results: email address was updated
add_action('wp_ajax_lhg_scan_update_email_ajax', 'lhg_scan_update_email_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_email_ajax', 'lhg_scan_update_email_ajax');

# HW scan results: comment provided for known hardware
add_action('wp_ajax_lhg_scan_update_known_hardware_comment_ajax', 'lhg_scan_update_known_hardware_comment_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_known_hardware_comment_ajax', 'lhg_scan_update_known_hardware_comment_ajax');

# HW scan results: comment on mainboard
add_action('wp_ajax_lhg_scan_update_mb_comment_ajax', 'lhg_scan_update_mb_comment_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_mb_comment_ajax', 'lhg_scan_update_mb_comment_ajax');

# HW scan results: comment on existing hardware
add_action('wp_ajax_lhg_scan_create_hardware_comment_ajax', 'lhg_scan_create_hardware_comment_ajax');
add_action('wp_ajax_nopriv_lhg_scan_created_hardware_comment_ajax', 'lhg_scan_create_hardware_comment_ajax');

# HW scan results: append comment to hardware article
add_action('wp_ajax_lhg_scan_append_hardware_comment_ajax', 'lhg_scan_append_hardware_comment_ajax');
add_action('wp_ajax_nopriv_lhg_scan_append_hardware_comment_ajax', 'lhg_scan_append_hardware_comment_ajax');

# AJAX funcitonalities

# append a user comments to the hardware article
function lhg_scan_append_hardware_comment_ajax() {
        global $lhg_price_db;

	$session   = $_REQUEST['session'] ;
	$comment   = $_REQUEST['comment'] ;
	$username  = $_REQUEST['username'] ;
	$wpuid_de  = $_REQUEST['username'] ;
	$wpuid_com = $_REQUEST['wpuid_com'] ;
	$email     = $_REQUEST['email'] ;
	$editor    = $_REQUEST['editor'] ;
	$postid    = $_REQUEST['postid'] ;

	$content_post = get_post($postid);
	$content = $content_post->post_content;
        $content = str_replace("<!--:-->","",$content);

        #error_log("RET: $content - $session - $postid - WPUIDDE $wpuid_de - WPUIDCOM $wpuid_com - em: $email_comment - editor: $editor");
        #print "RET: $comment - $session - $postid <br>";


        #add comment to DB
        if ($wpuid_de  != "") $userid = $wpuid_de;
        if ($wpuid_com != "") $userid = $wpuid_com;
        $time = current_time('mysql');

	$newpost_content = array(
		    'ID' => $postid,
		    'post_content' => $content."

".$comment.'<!--:-->',
	);

        wp_update_post ( $newpost_content );

        # ajax: return data
        $response = new WP_Ajax_Response;
        $response->add( array(
                'data' => 'success',
                'supplemental' => array(
	        	'text' => "Debug: $pid - $id - $asin - $comment - SID: $session -- END",
                ),
                ) );

        $response->send();

        die();
}


# process user comments on existing posts / hw -> write to WPdB
function lhg_scan_create_hardware_comment_ajax() {
        global $lhg_price_db;

	$session   = $_REQUEST['session'] ;
	$comment   = $_REQUEST['comment'] ;
	$username  = $_REQUEST['username'] ;
	$wpuid_de  = $_REQUEST['username'] ;
	$wpuid_com = $_REQUEST['wpuid_com'] ;
	$email     = $_REQUEST['email'] ;
	$postid    = $_REQUEST['postid'] ;

        # check if a email address is available for this scan
	$myquery = $lhg_price_db->prepare("SELECT email FROM `lhgscansessions` WHERE sid = %s", $session);
	$email = $lhg_price_db->get_var($myquery);
        if ($email != "") {
                $tmp = explode("@",$email,2);
                $email_front = $tmp[0];

                $tmp = explode(".",$email);
                $email_end = end ( $tmp );

                $email_comment = $email_front."@___.".$email_end;
	}

        #global $lhg_price_db;
	#$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET usercomment = %s WHERE sid = %s ", $comment, $session);
	#$result = $lhg_price_db->query($myquery);

        #error_log("RET: $comment - $session - $postid - WPUIDDE $wpuid_de - WPUIDCOM $wpuid_com - em: $email_comment ");
        #print "RET: $comment - $session - $postid <br>";

        # what comment to show?
        $return_comment = $comment;

        #add comment to DB
        if ($wpuid_de  != "") $userid = $wpuid_de;
        if ($wpuid_com != "") $userid = $wpuid_com;
        $time = current_time('mysql');

	if ($userid != 0) {
	        $commentdata = array(
		    'comment_post_ID' => $postid,
		    'comment_content' => $comment,
		    'comment_type' => '',
		    'comment_parent' => 0,
		    'user_id' => $userid,
		    'comment_date' => $time
		);
	} else {
                if ($email == "") $email_comment = "Anonymous";

	        $commentdata = array(
		    'comment_post_ID' => $postid,
		    'comment_content' => $comment,
		    'comment_type' => '',
		    'comment_parent' => 0,
		    'comment_date' => $time,
        	    'comment_author_email' => $email,
        	    'comment_author' => $email_comment
		);
        }

        $comment_id = wp_new_comment ( $commentdata );

        # store comment_id in PriceDB allow linking comment with scan
	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET commentid = %s WHERE sid = %s AND postid = %s ", $comment_id, $session, $postid);
	$result = $lhg_price_db->query($myquery);

        # ajax: return data
        $response = new WP_Ajax_Response;
        $response->add( array(
                'data' => 'success',
                'supplemental' => array(
	        	'text' => "Debug: $pid - $id - $asin - $comment - SID: $session -- END",
        	         'return_comment' => "$return_comment",
        	         'comment_id' => "$comment_id",
                ),
                ) );

        $response->send();

        die();
}


# process user comment on known hardware
function lhg_scan_update_known_hardware_comment_ajax() {

	$session = $_REQUEST['session'] ;
	$comment = $_REQUEST['comment'] ;

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET usercomment = %s WHERE sid = %s ", $comment, $session);
	$result = $lhg_price_db->query($myquery);

        //print "RET: $comment - $session <br>";

        die();
}

# process user comment on mainboard
function lhg_scan_update_mb_comment_ajax() {

	$session = $_REQUEST['session'] ;
	$comment = $_REQUEST['comment'] ;
	$url = $_REQUEST['url'] ;

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET mb_usercomment = %s WHERE sid = %s ", $comment, $session);
	$result = $lhg_price_db->query($myquery);


	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET mb_url = %s WHERE sid = %s ", $url, $session);
	$result = $lhg_price_db->query($myquery);

        //print "RET: $comment - $session <br>";

        die();
}

# process onboard radio button "no"
function lhg_scan_onboardn_ajax() {

	$session = $_REQUEST['session'] ;
	$id = $_REQUEST['id'] ;

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET onboard = %s WHERE sid = %s and id = %s ", "no", $session, $id);
	$result = $lhg_price_db->query($myquery);

        //print "RET: $comment - $session <br>";

        die();
}

# process onboard radio button "yes"
function lhg_scan_onboardy_ajax() {

	$session = $_REQUEST['session'] ;
	$id = $_REQUEST['id'] ;

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET onboard = %s WHERE sid = %s and id = %s ", "yes", $session, $id);
	$result = $lhg_price_db->query($myquery);

        //print "RET: $comment - $session <br>";

        die();
}

# process user email address
function lhg_scan_update_email_ajax() {

	$session = $_REQUEST['session'] ;
	$email = $_REQUEST['email'] ;


        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET email = %s WHERE sid = %s ", $email, $session);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->query($myquery);

        $to = "webmaster@linux-hardware-guide.com";
        $subject = "LHG Hardware Scan";
        $message = 'A hardware scan was performed and the user left a contact address.
ScanID: '.$sid.'
email: '.$email.'

Please visit: http://www.linux-hardware-guide.com/hardware-profile/scan-'.$session.'

';
        wp_mail( $to, $subject, $message );
        die();
}



# process user input for scan result
function lhg_scan_update_ajax() {

	$pid     = $_REQUEST['postid'];
	$id      = $_REQUEST['id'] ;
	$session = $_REQUEST['session'] ;
        $asinURL = $_REQUEST['asinURL'] ;
        $comment = $_REQUEST['comment'] ;

        // get image URL
        $pos = strpos($asinURL,"/B0");
        $asin = substr($asinURL, $pos+1,10);


        if (strpos($asinURL,".com/") > 1) $amz_region = "com";
        if (strpos($asinURL,".fr/") > 1)  $amz_region = "fr";
        if (strpos($asinURL,".de/") > 1) $amz_region = "de";
        if (strpos($asinURL,".co.uk/") > 1) $amz_region = "co.uk";
       	$output = lhg_aws_get_price($asin,$amz_region);

        list($image_url_com, $product_url_com, $price_com , $product_title) = split(";;",$output);
        $image_url_com     = str_replace("Image: ","", $image_url_com);


        # update article data by ASIN data
        $mode = lhg_get_autocreate_mode($pid); 
        lhg_update_tags_by_string($pid, $product_title, $mode);
        lhg_update_categories_by_string($pid, $product_title, $mode);
        lhg_update_title_by_string($pid, $product_title, $mode);
        if ($mode == "drive") lhg_correct_drive_name($pid, $session);

        # extract new Properties and new title coming from ASIN data
        $newtitle = get_the_title( $pid );
        $posttags = get_the_tags( $pid );
        $properties_array = array();
        if ($posttags) {
  		foreach($posttags as $tag) {
		   array_push( $properties_array, $tag->name);
                   #error_log("TAG: ".$tag->name);
		}
	}
        $properties = join( ", " , $properties_array );
        #error_log("PID: $pid - Title: $newtitle - Prop: $properties");


        // Write extracted data to DB
        global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET usercomment = %s WHERE id = %s ", $comment, $id);
	$result = $lhg_price_db->query($myquery);

	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET url = %s WHERE id = %s ", $asinURL, $id);
	$result = $lhg_price_db->query($myquery);

	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET imgurl = %s WHERE id = %s ", $image_url_com, $id);
	$result = $lhg_price_db->query($myquery);


	# And Store asin in WPDB
	$key = "amazon-product-single-asin";
  	$value = $asin;
	if(get_post_meta($pid, $key, FALSE)) { //if the custom field already has a value
  		update_post_meta($pid, $key, $value);
	} else { //if the custom field doesn't have a value
  		add_post_meta($pid, $key, $value);
	}



        $response = new WP_Ajax_Response;

        $response->add( array(
                'data' => 'success',
                'supplemental' => array(
	        	'text' => "Debug: $pid - $id - $asin - $comment - SID: $session -- END",
        	         'imgurl' => "$image_url_com",
        	         'properties' => "$properties",
        	         'newtitle' => "$newtitle"
                         //"http://www.linux-hardware-guide.com/wp-uploads/2014/11/LHG_Logo_circle-300x290.png"
        	        //'imgurl' => ""
                ),
                ) );


	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET usercomment = %s WHERE id = %s ", $comment, $id);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->query($myquery);


        $response->send();

        //var_dump($response);
        exit();
        //die();

}



?>