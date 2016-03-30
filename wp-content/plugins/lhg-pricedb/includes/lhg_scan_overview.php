<?php

function lhg_article_scans_overview () {


        global $lang;
	global $lhg_price_db;
        global $txt_scan_distribution;
        global $txt_scan_kernel;
        global $txt_scan_scandate;
        global $txt_scan_result;
        global $txt_scan_results;
        global $txt_scan_title;
        global $txt_scan_text;
        global $txt_Rating;

	$pid = get_the_ID();
        if ($lang == "de") $pid = lhg_get_com_post_URL( $pid );


        $sql = "SELECT sid FROM `lhghwscans` WHERE postid = \"".$pid."\"";
    	$ids = $lhg_price_db->get_results($sql);

        #print "ID List:";
        #var_dump($ids);
        #print "<br>";

        $findings=0;
        $output_tmp = '<div class="article-scantable-outer">
        <table class="article-scantable">
        <tr>
                <td></td>
        	<td><div class="article-scantable-header">'.$txt_scan_distribution.'</div></td>
        	<td><div class="article-scantable-header">'.$txt_scan_kernel.'</div></td>
        	<td><div class="article-scantable-header">'.$txt_Rating.'</div></td>
        	<td><div class="article-scantable-header">'.$txt_scan_scandate.'</div></td>
        </tr>
                ';


        $idarray=array();
        $counter = 0;

        foreach ($ids as $id) {
                $sid = $id->sid;
                #var_dump($sid);
	        $sql = "SELECT id, distribution, kversion, pub_id, scandate FROM `lhgscansessions` WHERE sid = \"".$sid."\"";
    		$result = $lhg_price_db->get_results($sql);

                $rating = lhg_get_rating_by_scan($sid , $pid);

                $ratingimage = lhg_create_rating_img( $rating );

		#var_dump($result);
                #print "--<br>";
                $result0 = $result[0];
		#var_dump($result0);

                #print "-> Distr: ".$result0->distribution."<br>"; //]." - ".$results0["kversion"]." - $sid<br>";
                #print "Res:  -> Distr: ".$result0["distribution"]." - ".$results0["kversion"]." - $sid<br>";
                #print "Res: $result -> Distr: ".$result["distribution"]." - ".$results["kversion"]." - $sid<br>";

                # test if distri+kernel combination was already shown
                $uniquestring=$result0->distribution."-".$result0->kversion;
                $known = array_search( $uniquestring, $idarray );

                #if ($pid == 21006) print "USTR: $uniquestring - ";
                #if ($pid == 21006) print "kn: ". $known ."<br>";

                # ToDo: Scan overview currently only available on .com servers:
                $url_prefix = "./";
                if ($lang == "de") $url_prefix = "http://www.linux-hardware-guide.com";

                if ( !is_int($known) ) {
	          #if ($pid == 21006) print "AA<br>";
                   # Unique combination
                   array_push( $idarray, $uniquestring );
                   if ( ($result0->distribution != "") && ($result0->kversion != "") && ($findings < 11) ) {
                        $date = date_i18n( get_option( 'date_format' ), $result0->scandate );
                        $date_array[$counter]= $result0->scandate;
                        $logo = get_distri_logo($result0->distribution);

                        $tooltiptext = $txt_scan_distribution.": ".preg_replace( "/\r|\n/", "", $result0->distribution)."\n".$txt_scan_kernel.": ".preg_replace( "/\r|\n/", "",$result0->kversion);

                        $output_tmp_array[$counter] .= '<tr>
                        <td width="30">'."
                        <div class=\"scan-overview-distri-logo\"><img src=\"".$logo.'" width="30" alt="'.$tooltiptext.'" title="'.$tooltiptext.'" ></div>
                        </td><td>'.$result0->distribution."</td>
                        <td>".$result0->kversion."</td>
                        <td>".$ratingimage."</td>
                        <td><a href=\"".$url_prefix."/hardware-profile/system-".$result0->pub_id."\">".$date."</a></td>
                        </tr>";
                        $findings++;
                        $counter++;
                   }
                }else{
                        # had this result before. Skipping!
	                #if ($pid == 21006) print "skipping: $uniquestring <br>";

                }
	}

        if ($counter == 1) $txt_results = "1 ".$txt_scan_result;
        if ($counter > 1) $txt_results = $counter." ".$txt_scan_results;

        $output = "";
        $output .= "<h2>$txt_scan_title ($txt_results) </h2>";
	$output .= $txt_scan_text;
        #'This hardware component was used by Linux users under the following system configurations. These results were collected by our <a href="./add-hardware">Scan Tool</a>:';


        # Sort scan overview table by date
        if ($counter > 0) {
        	array_multisort($date_array, SORT_DESC, $output_tmp_array);
        	$output_tmp_string = implode(" ",$output_tmp_array);
	}

        $output_tmp .= $output_tmp_string."</table></div>";


        # we found something -> show table
        if ($findings >0) $output .= $output_tmp;
        if ( ($findings == "") or ($findings == 0) ) $output = "";
        #print "FDG: $findings<br>";
        return $output;

}

function lhg_menu_hw_scans () {

if ($_POST != "") lhg_db_update_scaninfo();


        $res = lhg_db_get_scan_sids ();
        print "<h1>Hardware Scans</h1>";
        print "<br>";

#print "POST:";
#var_dump ( $_POST );

        #var_dump( $res );

print '<form action="admin.php?page=lhg_menu_hw_scans" method="post">';

        $user = wp_get_current_user();
        $userid = $user->ID;

        #defaults
        $show_new      = true;
        $show_ongoing  = true;
        $show_complete = false;
        $show_feedback = false;


        $show_new      = get_user_meta( $userid , 'lhg_scan_show_new', true);
        $show_ongoing  = get_user_meta( $userid , 'lhg_scan_show_ongoing', true);
        $show_complete = get_user_meta( $userid , 'lhg_scan_show_complete', true);
        $show_feedback = get_user_meta( $userid , 'lhg_scan_show_feedback', true);

        #not defined -> set defaults
        #if ($show_new      == "") {add_user_meta( $userid, 'lhg_scan_show_new',      true , true); $show_new      = true;}
        #if ($show_ongoing  == "") {add_user_meta( $userid, 'lhg_scan_show_ongoing',  true , true); $show_ongoing  = true;}
        #if ($show_complete == "") {add_user_meta( $userid, 'lhg_scan_show_complete', false, true); $show_complete = false;}


print "Show: "; # $userid: $show_new - $show_ongoing - $show_complete";
 ($show_new      == true)? print '<input name="filter_show_new" type="checkbox" value="1" checked /> New ' :
                           print '<input name="filter_show_new" type="checkbox" value="1" /> New ';

 ($show_ongoing  == true)? print '<input name="filter_show_ongoing" type="checkbox" value="true" checked /> Ongoing ' :
                           print '<input name="filter_show_ongoing" type="checkbox" value="false" /> Ongoing ';

 ($show_complete == true)? print '<input name="filter_show_complete" type="checkbox" value="true" checked /> Complete ' :
                           print '<input name="filter_show_complete" type="checkbox" value="false" /> Complete ';

 ($show_feedback == true)? print '<input name="filter_show_feedback" type="checkbox" value="true" checked /> Feedback needed ' :
                           print '<input name="filter_show_feedback" type="checkbox" value="false" /> Feedback needed ';


print "<table border=1><tr>";
print "<td><b>Date</b></td><td><b>Link</b></td><td><b>Comment User</b></td> <td><b>Comment Admins</b></td> <td><b>Status</b></td> </tr>";
        foreach ($res as $resN) {
        	$sid  = $resN->sid;
        	$status  = $resN->status;

                if ( ( ($status == "") ) or
                     ( ($status == "new") && ($show_new == true) ) or
                     ( ($status == "ongoing") && ($show_ongoing == true) ) or
                     ( ($status == "feedback") && ($show_feedback == true) ) or
                     ( ($status == "complete") && ($show_complete == true) ) ) {

	        	$pub_id  = $resN->pub_id;

	        	if ($result->pub_id == "") {
        	        	$pub_id = lhg_create_pub_id($sid);
			}

        		$time = $resN->scandate;
                	$date = gmdate("m/d/Y g:i:s A", $time);
	        	$acomment = $resN->admincomment;
        		$ucomment = $resN->usercomment;
        		$email = $resN->email;
                	$ucomment_short = $ucomment;
	        	if (strlen($ucomment) > 50)
                        $ucomment_short = substr(sanitize_text_field($ucomment),0,50)."...";

                        $distribution = $resN->distribution;

       		 	$status = $resN->status;
       	                #$sid2 = $res[1]->sid;
        		#var_dump ($sid ."--".$sid2);
               		#print "SID: $sid<br>";

	                $statusSelector = '
				<select name="status-'.$sid.'">';

	                $statusSelector .= ($status == "new")? '<option value="new" selected>New</option>' : '<option value="new">New</option>';
       		        $statusSelector .= ($status == "ongoing")? '<option value="ongoing" selected>Ongoing</option>' : '<option value="ongoing">Ongoing</option>';
	                $statusSelector .= ($status == "complete")? '<option value="complete" selected>Complete</option>' : '<option value="complete">Complete</option>';
	                $statusSelector .= ($status == "feedback")? '<option value="feedback" selected>Feedback needed</option>' : '<option value="feedback">Feedback needed</option>';
        	        $statusSelector .= '</select>';

                        $distrilogo = get_distri_logo($distribution);
                        if ($email == "") $maillogo = '<span class="emailgrey" style="color: grey; font-weight: bold; font-size: 1.4em; position: relative; top: -3px;"> @</span>';#<i class="icon-envelope icon-large2"></i>';
                        if ($email != "") $maillogo = '<span class="emailgreen" style="color: green; font-weight: bold; font-size: 1.4em; position: relative; top: -3px;">@</span>';#<i class="icon-envelope icon-large2"></i>';

                        $commentavail = '<span class="emailgrey" style="color: grey; font-weight: bold; font-size: 1.4em; position: relative; top: -3px;">C</span>';
                        if ( ($acomment != "") or ($uceomment != "") )
                        $commentavail = '<span class="emailgreen" style="color: green; font-weight: bold; font-size: 1.4em; position: relative; top: -3px;">C</span>';#<i class="icon-envelope icon-large2"></i>';

                        $ratingavail = '<img src="/wp-content/plugins/wp-postratings/images/stars_crystal/rating_off.gif">';
                        if ( ($rated != "") )
                        $ratingavail = '<img src="/wp-content/plugins/wp-postratings/images/stars_crystal/rating_on.gif">';

	                print "<tr>";
        	        print "<td>$date </td>";
                	print '<td><a href="/hardware-profile/scan-'.$sid.'">'.$sid.'</a> (<a href="/hardware-profile/system-'.$pub_id.'">pub</a>)</td>';

                        $tooltiptext ="Distribution: ".preg_replace( "/\r|\n/", "", $resN->distribution)."\nKernel: ".preg_replace( "/\r|\n/", "",$resN->kversion);

                        print '<td>
                          <img src="'.$distrilogo .'" alt="'.$tooltiptext.'" title="'.$tooltiptext.'" width=20>
                          '.$maillogo." "
                          .$commentavail." "
                          .$ratingavail. " "
                          .$ucomment_short."</td>";
        	        print '<td>
	        	        <input name="hwscan_acomment_'.$sid.'" type="text" size="20" value="'.$acomment.'">
	                       </td>';
        	        print "<td>$statusSelector</td>";
                	print "</tr>";

		}
	}

print "</tr></table>";
print '<input type="submit" value="update">';
print "</form>";
}

function lhg_create_pub_id($sid) {
	global $lhg_price_db;

        $sql = "SELECT id FROM `lhgscansessions` WHERE sid = \"".$sid."\"";
    	$id = $lhg_price_db->get_var($sql);

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	$charactersLength = strlen($characters);
    	$randomString = '';
        $length = 30;
        for ($i = 0; $i < $length; $i++) {
        	$randstring .= $characters[rand(0, $charactersLength - 1)];
	}

        $sql = "UPDATE `lhgscansessions` SET pub_id = \"".$randstring."\" WHERE id = \"".$id."\"";
    	$result = $lhg_price_db->query($sql);

        return $randstring;

}


function lhg_db_get_scan_sids (  ) {

	global $lhg_price_db;


        $sql = "SELECT * FROM `lhgscansessions` ORDER BY scandate DESC";
    	$result = $lhg_price_db->get_results($sql);


        return $result;


}

function lhg_db_update_scaninfo (  ) {

        # reset filters (only true is sumbitted!)
        $user = wp_get_current_user();
        $userid = $user->ID;
        if (!empty($_POST)) {
                #print "RESET!";
                #only reset if update button was used!
                update_user_meta( $userid, 'lhg_scan_show_new',      false );
        	update_user_meta( $userid, 'lhg_scan_show_ongoing',  false );
        	update_user_meta( $userid, 'lhg_scan_show_complete', false );
        	update_user_meta( $userid, 'lhg_scan_show_feedback', false );
	}


	foreach($_POST as $key => $value) {
		#echo $key . " => " . $value;
        	$sid = substr($key,-30);
	        $rawkey = substr($key,0,-31);
                #print "<br>key: $key -> SID: $sid -> $rawkey -> $value<br>";

                if ($key == "filter_show_new")      update_user_meta( $userid, 'lhg_scan_show_new',      true );
                if ($key == "filter_show_ongoing")  update_user_meta( $userid, 'lhg_scan_show_ongoing',  true );
                if ($key == "filter_show_complete") update_user_meta( $userid, 'lhg_scan_show_complete', true );
                if ($key == "filter_show_feedback") update_user_meta( $userid, 'lhg_scan_show_feedback', true );

                if ($rawkey == "hwscan_acomment") lhg_db_update_acomment($sid,$value);
                if ($rawkey == "status") lhg_db_update_status($sid,$value);

	}


}

function lhg_db_update_acomment ( $sid , $value  ) {

	global $lhg_price_db;
        $sql = "SELECT id FROM `lhgscansessions` WHERE sid = \"".$sid."\"";
    	$id = $lhg_price_db->get_var($sql);

        #print "UPDATE id: $id -> $value <br>";

        $sql = "UPDATE `lhgscansessions` SET admincomment = \"".$value."\" WHERE id = \"".$id."\"";
    	$result = $lhg_price_db->get_var($sql);

}

function lhg_db_update_status ( $sid , $value  ) {

	global $lhg_price_db;
        $sql = "SELECT id FROM `lhgscansessions` WHERE sid = \"".$sid."\"";
    	$id = $lhg_price_db->get_var($sql);

        #print "UPDATE id: $id -> $value <br>";

        $sql = "UPDATE `lhgscansessions` SET status = \"".$value."\" WHERE id = \"".$id."\"";
    	$result = $lhg_price_db->get_var($sql);

        if ($value == "complete") {
                #get mail address

                $myquery = $lhg_price_db->prepare("SELECT email FROM `lhgscansessions` WHERE sid = %s ", $sid);
		$email = $lhg_price_db->get_var($myquery);
                #send mail notification to user
		if ($email != "") {
		        $subject = "LHG Hardware Scan - Finished";
        		$message = 'Hello,

Thank you for uploading your hardware data to the Linux-Hardware-Guide.
Your scan data was processed and added to the web page. You can find your scan results at
http://www.linux-hardware-guide.com/hardware-profile/scan-'.$sid.'

Please visit this page and rate the Linux compatibility of your hardware components and also leave
comments (e.g., if additional steps are needed for the hardware or if everything works out of the box).

In case of further questions do not hesitate to contact us.

Best regards,
Linux-Hardware-Guide Team
';

        		wp_mail( $email, $subject, $message );
		}
	}
}

function lhg_store_login_data ( $sid ) {

        $ip = lhg_getUserIP();
        $date = time();
        $user_ID = get_current_user_id();

        #print "IP: $ip<br>
        #Date: $date<br>
        #UID: $user_ID<br>
        #SID: $sid<br>
        #";

        if (strlen($sid) == 30)
        if ($user_ID != 1) {
		global $lhg_price_db;
        	$sql = "INSERT INTO `lhgscan_login` ( date, ip, sid, user_id ) VALUES ( '$date', '$ip', '$sid', '$user_id')";
                #$safe_sql = $lhg_price_db->prepare($sql, $date, $ip, $sid, $user_ID);
    		$inser = $lhg_price_db->query($sql);
	}
}

function lhg_getUserIP()
{
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

function lhg_get_rating_by_scan( $sid , $postid) {
        # get IPs and dates corresponding to raters of postid
        global $lhg_price_db;
        global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM $wpdb->ratings WHERE rating_postid = $postid");

        # check if corresponds to logged in user in priceDB
        foreach ($results as $result) {
                $ip = ($result->rating_ip);
                $date = ($result->rating_timestamp);
                #$ip = $result[0]->rating_ip;

                #print "Search for IP: $ip<br> - date: $date<br>";

		$scan_results = $lhg_price_db->get_results("SELECT * FROM `lhgscan_login` WHERE ip = '$ip'");
	        foreach ($scan_results as $scan_result) {
	                # check rating dates for match
                        $logindate= $scan_result->date;
                        #print "Logindate: $logindate<br>";
                        # check if rating and login were by same user in 10h time window
                        # return corresponding rating
                        if ( ( abs( $date - $logindate)) < 60*60*10 ) return ($result->rating_rating);


	        }
        }

        # no rating found
        return -1;

}

# create star image from rating
function lhg_create_rating_img( $rating ) {

        global $txt_user_rating_for_setup;
        global $txt_out_of;

                if ($rating < 0) return "-";

        for($j=1; $j <= 5; $j++) {
		if($j <= $rating) {
			$output .= '<img src="'.plugins_url('wp-postratings/images/stars_crystal/rating_on.gif').'" alt="'.
                        $txt_user_rating_for_setup." ".$rating." ".$txt_out_of.' 5" title="'.
                        $txt_user_rating_for_setup." ".$rating." ".$txt_out_of.' 5" class="post-ratings-image" />';
		} else {
			$output .= '<img src="'.plugins_url('wp-postratings/images/stars_crystal/rating_off.gif').'" alt="'.
                        $txt_user_rating_for_setup." ".$rating." ".$txt_out_of.' 5" title="'.
                        $txt_user_rating_for_setup." ".$rating." ".$txt_out_of.' 5" class="post-ratings-image" />';
		}
	}
        return $output;
}
		       

?>
