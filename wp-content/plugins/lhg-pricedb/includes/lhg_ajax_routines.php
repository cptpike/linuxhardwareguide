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
add_action('wp_ajax_nopriv_lhg_scan_create_hardware_comment_ajax', 'lhg_scan_create_hardware_comment_ajax');

# HW scan results: append comment to hardware article
add_action('wp_ajax_lhg_scan_append_hardware_comment_ajax', 'lhg_scan_append_hardware_comment_ajax');
add_action('wp_ajax_nopriv_lhg_scan_append_hardware_comment_ajax', 'lhg_scan_append_hardware_comment_ajax');

# return lspci output based on list of PCI IDs
add_action('wp_ajax_lhg_pci_extract_ajax', 'lhg_pci_extract_ajax');
add_action('wp_ajax_nopriv_lhg_pci_extract_ajax', 'lhg_pci_extract_ajax');

# translate a slug
add_action('wp_ajax_lhg_translate_slug_ajax', 'lhg_translate_slug_ajax');
add_action('wp_ajax_nopriv_lhg_translate_slug_ajax', 'lhg_translate_slug_ajax');

# change teaser image of post
add_action('wp_ajax_lhg_update_teaser_image_ajax', 'lhg_update_teaser_image_ajax');
add_action('wp_ajax_nopriv_lhg_update_teaser_image_ajax', 'lhg_update_teaser_image_ajax');

# change status of scan (new, complete, ongoing, ...)
add_action('wp_ajax_lhg_update_scan_status_ajax', 'lhg_update_scan_status_ajax');
add_action('wp_ajax_nopriv_lhg_update_scan_status_ajax', 'lhg_update_scan_status_ajax');

# create a new mainboard / laptop article
add_action('wp_ajax_lhg_create_mainboard_post_ajax', 'lhg_create_mainboard_post_ajax');
add_action('wp_ajax_nopriv_lhg_create_mainboard_post_ajax', 'lhg_create_mainboard_post_ajax');

# modify title of post in scan overview
add_action('wp_ajax_lhg_scan_update_title_ajax', 'lhg_scan_update_title_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_title_ajax', 'lhg_scan_update_title_ajax');

# modify tags of post in scan overview
add_action('wp_ajax_lhg_scan_update_tags_ajax', 'lhg_scan_update_tags_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_tags_ajax', 'lhg_scan_update_tags_ajax');

# modify tags of post in scan overview
add_action('wp_ajax_lhg_scan_update_categories_ajax', 'lhg_scan_update_categories_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_categories_ajax', 'lhg_scan_update_categories_ajax');

# modify tags of post in scan overview
add_action('wp_ajax_lhg_scan_update_asin_ajax', 'lhg_scan_update_asin_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_asin_ajax', 'lhg_scan_update_asin_ajax');

# modify tags of post in scan overview
add_action('wp_ajax_lhg_scan_update_designation_ajax', 'lhg_scan_update_designation_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_designation_ajax', 'lhg_scan_update_designation_ajax');

# publish mainboard article
add_action('wp_ajax_lhg_scan_publish_mb_article_ajax', 'lhg_scan_publish_mb_article_ajax');
add_action('wp_ajax_nopriv_lhg_scan_publish_mb_article_ajax', 'lhg_scan_publish_mb_article_ajax');

# change mainboard title
add_action('wp_ajax_lhg_scan_update_mb_title_ajax', 'lhg_scan_update_mb_title_ajax');
add_action('wp_ajax_nopriv_lhg_scan_update_mb_title_ajax', 'lhg_scan_update_mb_title_ajax');

# AJAX funcitonalities

# create a new mainboard article. Return the post id.
# used by scan page to create article if MB was wrongly identified
function lhg_create_mainboard_post_ajax() {
        global $lhg_price_db;

        require_once( WP_PLUGIN_DIR . '/lhg-hardware-profile-manager/templates/scan.php');

	$sid   	 = $_REQUEST['session'] ;
	$title   = $_REQUEST['title'] ;

        #error_log("SID: $sid - Title: $title");

        # execute standard actions for status change:
        #lhg_db_update_status( $sid, $status, $uid );



        $postid = lhg_create_mainboard_article($title, $sid, "");
        $newurl = "/wp-admin/post.php?post=".$postid."&action=edit&scansid=".$sid;
        #error_log("Created article: $postid");

        $response = new WP_Ajax_Response;
        $response->add( array(
                'data' => 'success',
                'supplemental' => array(
	        	'postid' => $postid,
                        'newurl' => $newurl
                ),
                ) );

        $response->send();

        die();
}

# update the scan status of a hardware scan
function lhg_update_scan_status_ajax() {
        global $lhg_price_db;

	$sid   	 = $_REQUEST['session'] ;
	$status	 = $_REQUEST['status'] ;
	$uid	 = $_REQUEST['uid'] ;

        #error_log("SID: $sid - Stat: $status");

        # execute standard actions for status change:
        lhg_db_update_status( $sid, $status, $uid );

        $response = new WP_Ajax_Response;
        $response->add( array(
                'data' => 'success',
                'supplemental' => array(
	        	'status' => $status,
                ),
                ) );

        $response->send();

        die();
}


# update a teaser image based on url and postid
function lhg_update_teaser_image_ajax() {
        global $lhg_price_db;

	$url   	 = $_REQUEST['url'] ;
	$id   	 = $_REQUEST['id'] ;
	$postid  = $_REQUEST['postid'] ;
        $title   = get_the_title( $postid );
        $product_title = sanitize_title( $title );

        #error_log("PID $postid - url: $url - id: $id - title: $product_title");

        if ($url == "") {
        	$scaled_image_url = "/wp-uploads/2013/03/noimage130.jpg";
        }else{

        	$scaled_image_url = lhg_create_article_image( $url, $product_title );
                $si_filename = str_replace("/wp-uploads/","",$scaled_image_url);

                //if ( !has_post_thumbnail( $postid ) ) {
                // always replace teaser image with selected one

                	$file = "/var/www/wordpress".$scaled_image_url;
                        #print "PID: $pid";
                        #print "<br>Store Thumbnail!";
                        #print "<br>SIURL: $scaled_image_url";

                        $wp_filetype = wp_check_filetype($file, null );

                        $attachment = array(
                        	'post_mime_type' => $wp_filetype['type'],
                                'post_title' => sanitize_title($product_title),
                                'post_content' => '',
                                'post_status' => 'inherit'
                        );

                        #  var_dump($attachment);

                        $attach_id = wp_insert_attachment( $attachment, $si_filename, $pid );
                        #print "AID: ".$attach_id;
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $si_filename );
                        wp_update_attachment_metadata( $attach_id, $attach_data );
                        set_post_thumbnail( $postid, $attach_id );
                //}
        }

        $response = new WP_Ajax_Response;
        $response->add( array(
                'data' => 'success',
                'supplemental' => array(
	        	'file' => $si_filename,
                ),
                ) );

        $response->send();

        die();
}

# slug was translated. Add to DBs
function lhg_translate_slug_ajax() {
        global $lhg_price_db;

	$slug   	 = $_REQUEST['slug'] ;
	$translated_tag = $_REQUEST['translated_tag'] ;
	$postid 	 = $_REQUEST['postid'] ;
        $translated_slug = sanitize_title($translated_tag);

        error_log("PID $postid - s: $slug - ts: $translated_slug");

        # add slug and tag name to WPDB
        wp_insert_term( $translated_tag , 'post_tag' , array( 'slug' => $translated_slug ) );

        # Link slug in TransverseDB
	$myquery = $lhg_price_db->prepare("SELECT slug_de, id FROM `lhgtransverse_tags` WHERE slug_com = %s ", $slug);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->get_results($myquery);


        #error_log("ID: ".$result[0]->id." - Slug: ".$result[0]->slug_de);

        if ( ( $result[0]->slug_de == "") && ( $result[0]->id > 0 ) ) {
		$myquery = $lhg_price_db->prepare("UPDATE `lhgtransverse_tags` SET slug_de = %s WHERE id = %s ", $translated_slug, $result[0]->id);
		#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
		$result = $lhg_price_db->query($myquery);
	}


        /*
        # ajax: return data
        $response = new WP_Ajax_Response;
        $response->add( array(
                'data' => 'success',
                'supplemental' => array(
	        	'error_txt' => "",
                ),
                ) );

        $response->send();
        */
        die();
}


# append a user comments to the hardware article
function lhg_pci_extract_ajax() {
        global $lhg_price_db;

	$session   = $_REQUEST['session'] ;
	$pcilist   = $_REQUEST['pcilist'] ;

        $pciarray = explode(",",$pcilist);

        $pci_output = "";
        foreach ($pciarray as $pciid) {
                # store comment_id in PriceDB allow linking comment with scan
		$myquery = $lhg_price_db->prepare("SELECT * FROM `lhghwscans` WHERE sid = %s AND pciid = %s ", $session, $pciid );
		$result  = $lhg_price_db->get_results($myquery);

                #error_log("PCIID: $session/$pciid ->".$result[0]->idstring);
                $pci_output .= $result[0]->idstring;
                if ($result[0]->idstring_subsystem != "") $pci_output .= $result[0]->idstring_subsystem;
	}


        #error_log("PCI list: $pci_output");

        # ajax: return data
        $response = new WP_Ajax_Response;
        $response->add( array(
                'data' => 'success',
                'supplemental' => array(
	        	'pcilist_txt' => $pci_output,
                ),
                ) );

        $response->send();

        die();
}


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

        #error_log("Create comment!");

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
        	         'return_comment' => $return_comment,
        	         'comment_id' => $comment_id,
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

        // store user submitted title
        if ($title != "")




        // ToDo
        // ASIN processing disabled -> separate routine needed
        $asinURL = "";

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

# update post title on scan page
function lhg_scan_update_title_ajax() {

	$pid       = $_REQUEST['postid'];
	$id        = $_REQUEST['id'] ;
	$session   = $_REQUEST['session'] ;
        $title     = $_REQUEST['title'] ;
        //$wpuid_de  = $_REQUEST['wpuid_de'] ;
        //$wpuid_com = $_REQUEST['wpuid_com'] ;

        global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET usertitle = %s WHERE id = %s ", $title, $id);
	$result = $lhg_price_db->query($myquery);

        exit();
}

# update post tags on scan page
function lhg_scan_update_tags_ajax() {

	$pid       = $_REQUEST['postid'];
	$id        = $_REQUEST['id'] ;
	$session   = $_REQUEST['session'] ;
        $tags      = $_REQUEST['tags'] ;
        //$wpuid_de  = $_REQUEST['wpuid_de'] ;
        //$wpuid_com = $_REQUEST['wpuid_com'] ;


        # create string of tag ids
        $tagstring = "";
        foreach ($tags as $tag)  {
                if ( $tagstring == "" ) $tagstring = $tag;
                if ( $tagstring != "" ) $tagstring = $tagstring.",$tag";
        }

	global $lhg_price_db;

        #error_log("tags ajax: $id $pid $session $tagstring");

        if ($id == "mb") {
                # store mainboard data
		$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET mb_usertags_ids = %s WHERE sid = %s ", $tagstring, $session );
		$result = $lhg_price_db->query($myquery);
	}else{
		$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET usertags_ids = %s WHERE id = %s ", $tagstring, $id);
		$result = $lhg_price_db->query($myquery);
        }
        exit();
}

# update post categories on scan page
function lhg_scan_update_categories_ajax() {

	$pid        = $_REQUEST['postid'];
	$id         = $_REQUEST['id'] ;
	$session    = $_REQUEST['session'] ;
        $categories = $_REQUEST['categories'] ;
        //$wpuid_de  = $_REQUEST['wpuid_de'] ;
        //$wpuid_com = $_REQUEST['wpuid_com'] ;


        # create string of tag ids
        $catstring = "";
        if ($categories != "")
        foreach ($categories as $cat)  {
                if ( $catstring == "" ) $catstring = $cat;
                if ( $catstring != "" ) $catstring = $catstring.",$cat";
        }

	global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET usercategories = %s WHERE id = %s ", $catstring, $id);
	$result = $lhg_price_db->query($myquery);

        exit();
}


# update asin ID 
function lhg_scan_update_asin_ajax() {

	$pid        = $_REQUEST['postid'];
	$id         = $_REQUEST['id'] ;
	$session    = $_REQUEST['session'] ;
        $asin 	    = $_REQUEST['asin'] ;
	$key = "amazon-product-single-asin";

        #error_log("Update ASIN -> PID $pid, ID $id, Session: $session, ASIN: $asin");
        #error_log("Before stored value($pid) = ".get_post_meta($pid, $key, TRUE));

        if ($asin == "") exit();

	# And Store asin in WPDB
  	$value = $asin;
	if( metadata_exists('post', $pid, $key) ) { //if the custom field already has a value
  		update_post_meta($pid, $key, $value);
                #error_log( "Updated ASIN (PID $pid) to $value");
	} else { //if the custom field doesn't have a value
  		add_post_meta($pid, $key, $value);
                #error_log("Added ASIN: (PID $pid) to $value");
	}

        #error_log("Stored value($pid) = ".get_post_meta($pid, $key, TRUE)." == $asin ?");

        exit();
}

# update scan designation
function lhg_scan_update_designation_ajax() {

	$sid         = $_REQUEST['sid'] ;
	$designation = $_REQUEST['input'] ;

        if ($designation == "") exit();

	global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET designation = %s WHERE sid = %s ", $designation, $sid);
	$result = $lhg_price_db->query($myquery);

        exit();
}

function lhg_scan_publish_mb_article_ajax() {

	$sid         = $_REQUEST['sid'] ;
	$id	     = $_REQUEST['id'] ;
	$idarray_pci = $_REQUEST['idarray_pci'] ;
	$idarray_usb = $_REQUEST['idarray_usb'] ;

        $idstring_pci = "";
        if ($idarray_pci != "")
        foreach ($idarray_pci as $idx)  {
                if ( $idstring_pci != "" ) $idstring_pci .= $idstring.",$idx";
                if ( $idstring_pci == "" ) $idstring_pci = $idx;
        }

        $idstring_usb = "";
        if ($idarray_usb != "")
        foreach ($idarray_usb as $idx)  {
                if ( $idstring_usb != "" ) $idstring_usb .= $idstring.",$idx";
                if ( $idstring_usb == "" ) $idstring_usb = $idx;
        }


        #error_log("AJAX: SID: $sid -- ID: $id -- ALL_PCI: $idstring_pci -- ALL_USB: $idstring_usb");

        exit();
}

# update mainboard designation
function lhg_scan_update_mb_title_ajax() {

	$sid         = $_REQUEST['session'] ;
	$designation = $_REQUEST['title'] ;

        #error_log("Update MB title: $designation SID: $sid");

        if ($designation == "") exit();

	global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET mb_usertitle = %s WHERE sid = %s ", $designation, $sid);
	$result = $lhg_price_db->query($myquery);

        exit();
}


?>