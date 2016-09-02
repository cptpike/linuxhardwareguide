<?php

require_once(plugin_dir_path(__FILE__)."uid.php");
require_once(plugin_dir_path(__FILE__)."../../lhg-pricedb/includes/lhg_autocreate.php");

# debug options
# 1 ... skip autodebug (timing issues)
global $scandebug;
$scandebug = 0;

if ( ($_SERVER['SERVER_ADDR'] == "192.168.56.12") or ($_SERVER['SERVER_ADDR'] == "192.168.56.13") )
$scandebug = 0;

#
#
#


global $lang;
global $txt_category;
global $txt_subscr_compat              ;//  		= "Legende: Y = all comments, R = replies only, C = inactive";
global $txt_subscr_regist_date  	;// = "Registration Date";
global $txt_subscr_modus	  	;//= "Status";
global $txt_subscr_article	  	;//= "Hardware Overview";
global $txt_subscr_select_all		;//= "Select all";
global $txt_subscr_select_inv		;//= "Invert selection";
global $txt_subscr_action		;//= "Action";
global $txt_subscr_delete	 	;//= "Delete Entry";
global $txt_subscr_suspend	 	;//= "Deactivate Entry";
global $txt_subscr_reply_only	 	;//= "Replies to my comments";
global $txt_subscr_activate	 	;//= "Stay informed";
global $txt_subscr_update              ;//= "Update";
global $txt_subscr_active              ;//= "Stay informed";
global $txt_subscr_inactive            ;//= "Deactivated";
global $txt_subscr_manage_hw           ;//= "Manage Your Hardware Profile";
global $txt_subscr_pub_hw_prof;
global $txt_subscr_name;
global $txt_subscr_email;
global $txt_subscr_more;
global $txt_subscr_rating;
global $txt_subscr_edit_rating;
global $txt_subscr_edit_comment;
global $txt_subscr_notrated;
global $show_public_profile;
global $txt_subscr_scanoverview;
global $txt_scan_distribution;
global $txt_subscr_kernelversion; #   = 'Kernel version';
global $txt_subscr_hwcomp; #          = "Hardware Components";
global $txt_subscr_identified; #      = "Identified";
global $txt_subscr_unknown; #         = "Unknown";
global $txt_subscr_knownhw;
global $txt_subscr_newhw;
global $txt_subscr_addhw;#	    = "Add HW to your profile";
global $txt_subscr_ratecomp;#        = "Please rate<br>Linux compatibility";
global $txt_subscr_nohwfound;#       = "No hardware found";
global $txt_subscr_hwfeedback; # Please let us know if ...
global $txt_subscr_multiple;
global $txt_subscr_identified_usb;
global $txt_subscr_option;
global $txt_subscr_thisscan; #        = "This scan was performed at";
global $txt_subscr_notice; #          = "Please note that this web service is still under development....
global $txt_subscr_limitation;
global $txt_subscr_foundhwid; #        = "Hardware Identifier";
global $txt_subscr_rate;#	     = "Hardware bewerten";
global $txt_subscr_pleaserate;
global $txt_subscr_type;        # Type
global $txt_subscr_help;
global $txt_subscr_ifpossible;
global $txt_subscr_new;
global $txt_submit;
global $txt_username;

require_once(plugin_dir_path(__FILE__).'../../lhg-pricedb/includes/lhg.conf');
require_once('/var/www/wordpress/wp-content/plugins/lhg-pricedb/includes/lhg.conf');

# quickfix
# link to .com server for images
$urlprefix = "";
if ($lang == "de") $urlprefix = "http://www.linux-hardware-guide.com";


// Avoid direct access to this piece of code
if (!function_exists('add_action')){
	header('Location: /');
	exit;
}

global $wp_subscribe_reloaded;
global $editmode;


ob_start();

# Extract Session ID fron URL
$url     = ((empty($_SERVER['HTTPS'])) ? 'http' : 'https') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$pieces  = parse_url($url);
$urlpath = $pieces['path'];

$hwscanpos     = strpos($urlpath,"/hardware-profile/scan-");
$hweditscanpos = strpos($urlpath,"/hardware-profile/editscan-");

if ($hwscanpos > 0) {
	$sid = substr($urlpath,$hwscanpos+23);
        $editmode = 0;
        $uploadermode = 1;
} elseif ($hweditscanpos > 0) {
	$sid = substr($urlpath,$hweditscanpos+27);
        $editmode = 1;
}

# check that visitor has necessary rights to see the page
if ( ($editmode == 1) && (get_current_user_id() == 0) ) die("You need to be logged in to edit hardware scans!");
if ( ($editmode == 1) && !current_user_can('edit_posts') ) die("You do not have sufficient Karma to edit hardware scans!");

# get scan id from public id
if ($show_public_profile) {
       $hwscanpos=strpos($urlpath,"/hardware-profile/system-");
       $pub_id = substr($urlpath,$hwscanpos+25);

       $myquery = $lhg_price_db->prepare("SELECT sid FROM `lhgscansessions` WHERE pub_id = %s", $pub_id);
       $sid = $lhg_price_db->get_var($myquery);
}


# check if this is the first visit or upload was performed recently
# In this case the DB server is still processing and we need to wait a little
lhg_check_if_recent_upload( $sid );

# store login data
# we need this info to link scan results with users
# and ratings with kernel versions

 if ($show_public_profile != 1) {
	lhg_store_login_data( $sid );
 }

# different CSS class needed for public page
$csspub = "";
if ($show_public_profile == 1) $csspub = "-pub";





if (sizeOf($_POST) > 0) lhg_scan_check_changes( $sid );



# get list of identified HW
global $lhg_price_db;
$myquery = $lhg_price_db->prepare("SELECT id, postid FROM `lhghwscans` WHERE sid = %s AND postid <> 0 AND scantype <> \"multiple_results\"  GROUP BY postid", $sid);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
$identified_hw = $lhg_price_db->get_results($myquery);
#var_dump( $identified_hw );

$myquery = $lhg_price_db->prepare("SELECT id, postid, usbid, pciid, idstring , usercomment , url , scantype FROM `lhghwscans` WHERE sid = %s AND postid = 0 AND pciid = ''", $sid);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
$unidentified_hw = $lhg_price_db->get_results($myquery);
#var_dump( $unidentified_hw );

$myquery = $lhg_price_db->prepare("SELECT id, postid, usbid, pciid, idstring , usercomment , url , scantype FROM `lhghwscans` WHERE sid = %s AND postid = 0 AND pciid <> ''", $sid);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
$unidentified_hw_pci = $lhg_price_db->get_results($myquery);
#var_dump( $unidentified_hw_pci );



$scantype = "multiple_results";
$myquery = $lhg_price_db->prepare("SELECT id, postid, usbid, pciid, idstring , usercomment , url , scantype FROM `lhghwscans` WHERE sid = %s AND scantype  = %s GROUP BY postid", $sid, $scantype);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
$multi_identified_hw = $lhg_price_db->get_results($myquery);
#var_dump( $multi_identified_hw );

#while ( ($id, $postid) = $sth_glob->fetchrow_array() ){
#
#        print "ID: $id - PID: $postid <br>";
#
#}

$result = $lhg_price_db->get_var($sql);

#print "<br># Results:";
#print_r( count($identified_hw) );
#print "<br>";

# show rescan link if an "author"
if (current_user_can('publish_posts') ) {

	if ( ($_SERVER['SERVER_ADDR'] == "192.168.56.12") or ($_SERVER['SERVER_ADDR'] == "192.168.56.13") )
	$rescan_url = "library.linux-hardware-guide.com";

	if ( ($_SERVER['SERVER_ADDR'] == "192.168.3.112") or ($_SERVER['SERVER_ADDR'] == "192.168.3.113") )
	$rescan_url = "192.168.3.115";

        if ($show_public_profile != 1) print '<br><a href="http://'.$rescan_url.'/rescan.php?sid='.$sid.'">Start rescan!</a><br>';
}

#if ($show_public_profile != 1) print '<b>Thank you for using our Linux-Hardware-Guide scanning software</b> (see <a href="https://github.com/paralib5/lhg_scanner">GitHub</a> for more details).<br>
#This way we can fill our Linux Hardware Database with valuable information for the Linux community.</b>';


# link to other scans

$myquery = $lhg_price_db->prepare("SELECT uid FROM `lhgscansessions` WHERE sid = %s", $sid);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
$uid = $lhg_price_db->get_var($myquery);
#var_dump( $uid );

$myquery = $lhg_price_db->prepare("SELECT COUNT(*) FROM `lhgscansessions` WHERE uid = %s", $uid);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
$num_uid = $lhg_price_db->get_var($myquery);

#print "<br>NUM:".$num_uid;

$email = lhg_get_hwscanmail($sid);
if ($email != "") $userknown = 1;

$buttontext = $txt_submit; #"Submit";
if ($userknown == 1) $buttontext = "Update";
print "";


#Hide Popup section if public profile is shown
if ($show_public_profile == 1) {
echo '
		<script src="https://cdn.rawgit.com/vast-engineering/jquery-popup-overlay/1.7.11/jquery.popupoverlay.js"></script>

                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

		      $("#my_popup").hide();
                      $("[id^=details-hw-]").hide();

                });

                /*]]> */
                </script>

';
}


$uploader_guid = lhg_get_scan_uploader_guid( $sid );

if (
	($show_public_profile == 1) or   
        ( $uploader_guid > 0 ) or
        ( $editmode == 1 )
   ){

	# do not show link box in public profile
	# do not show if scan was already linked to registered user (e.g. by personified upload)
        # hide email from other uses

} else {

print '
<br>&nbsp;<br>
<h2>Contact information</h2>
<form action="?" method="post">
       Please leave us your email address in order to contact you in case of questions regarding your hardware scan results:<br>
       <b>Email</b>: <input name="email" id="email-input" type="text" size="30" maxlength="50" value="'.$email.'">
       <input type="submit" id="email-submit" name="email-login" value="'.$buttontext.'" class="hwscan-email-button-'.$buttontext.'" />
</form>
<br>
';

}

echo '
	<script src="https://cdn.rawgit.com/vast-engineering/jquery-popup-overlay/1.7.11/jquery.popupoverlay.js"></script>

                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

				$(\'#email-submit\').click(function(){

                                var button = this;

                                // "we are processing" indication
                                var indicator_html = \'<img class="scan-load-button" id="button-load-known-hardware-comment" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                                $(button).after(indicator_html);


                                //prepare Ajax data:
                                var session = "'.$sid.'";
                                var email = $("#email-input").val();
                                var data ={
                                        action: \'lhg_scan_update_email_ajax\',
                                        email: email,
                                        session: session
                                };


                                //load & show server output
                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                        $(button).append("Response");
                                        $(button).after(response);
                                        //$(box).append("Response: <br>IMG: "+imageurl+" <br>text: "+responsetext);

                                        //return to normal state
                                        $(button).val("Update");
                                        $(button).attr("class", "hwscan-comment-button-light");
                                        var indicatorid = "#button-load-known-hardware-comment";
                                        $(indicatorid).remove();

                                });

                                //prevent default behavior
                                return false;

                                });


                });



                /*]]> */

                </script>';


# allow registration


#print "<br>The following scan results were achieved:";


#
#
##### General Info
#
#

   	$myquery = $lhg_price_db->prepare("SELECT id, scandate, kversion, distribution, status FROM `lhgscansessions` WHERE sid = %s", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$identified_scans = $lhg_price_db->get_results($myquery);

        #var_dump($identified_scans);

	$scandate = $identified_scans[0]->scandate;
	$scandate_txt = gmdate("Y-m-d, H:i:s", $scandate);

        $distribution = $txt_subscr_unknown; #"unknown";
        $kversion = $txt_subscr_unknown; #"unkwnown";
	$status = $identified_scans[0]->status;

        $distribution = $identified_scans[0]->distribution;
        $kversion = $identified_scans[0]->kversion;

        $logo = get_distri_logo($distribution);

        # get data from guid
        $user_tmp = lhg_get_userdata_guid( $uploader_guid );
        $user=$user_tmp[0];
        $user_nicename = $user->user_nicename;
        $avatar = $user->avatar;
        $wpuid_de = $user->wpuid_de;
        $wpuid_com = $user->wpuid;



#	echo "<h2>".$txt_subscr_scanoverview.":</h2>";

                #get and check session ID
                #echo "Session ID: $sid <br>";

                echo '<table id="registration" class="scanoverview-table">';
                echo '<tr id="header">


                <td id="title-colhw">Scan</td>';

                #if ($userknown == 1)
                #echo '<td id="hwscan-col3" width="13%"><nobr>Add HW to your profile</nobr></td>';

        if ($uploader_guid != "") {
                echo '<td id="hwscan-col2" width="8%">';
                echo $txt_username.'</td>';
        } 

                echo '<td id="hwscan-col2" width="8%">Status</td>';
                echo '
                <td id="hwscan-col2" width="25%">'.$txt_scan_distribution.'</td>
                <td id="hwscan-col2" width="20%">'.$txt_subscr_kernelversion.'</td>
                <td id="hwscan-col2" width="13%">'.$txt_subscr_hwcomp.'</td>


                </tr>';

        echo "<tr id=\"regcont\">";

        echo "
        	<td id=\"col-hw\">

                        ".'<div class="scan-overview-distri-logo"><img src="'.$logo.'" width="40" ></div>

                        <div class="subscribe-hwtext-scanlist"><div class="subscribe-hwtext-span-scanlist">&nbsp;'.$scandate_txt.' </div></div>';


	print                       " </td>";



        if ($uploader_guid != "") {
                $user_output="";

                if ( ($lang == "de") && ($user->wpuid_de != 0) ) $user_output .= '<a href="/hardware-profile/user'.$user->wpuid_de.'" class="recent-comments">';
		if ( ($lang != "de") && ($user->wpuid != 0) ) $user_output .= '<a href="/hardware-profile/user'.$user->wpuid.'" class="recent-comments">';
		$user_output .='    <div class="userlist-avatar">'.
		      		$avatar.'
			    </div> ';
		if ( ($lang == "de") && ($user->wpuid_de != 0) ) $user_output .= '</a>';
		if ( ($lang != "de") && ($user->wpuid != 0) ) $user_output .= '</a>';

		$user_output .= '<div class="subscribe-hwtext-scanlist"><div class="subscribe-hwtext-span-scanlist">';
		if ( ($lang == "de") && ($user->wpuid_de != 0) ) $user_output .= '		<a href="/hardware-profile/user'.$user->wpuid_de.'" class="recent-comments">';
		if ( ($lang != "de") && ($user->wpuid != 0) ) $user_output .= '		<a href="/hardware-profile/user'.$user->wpuid.'" class="recent-comments">';
	        $user_output .= $user_nicename;

		if ( ($lang == "de") && ($user->wpuid_de != 0) ) $user_output .= '</a>';
		if ( ($lang != "de") && ($user->wpuid != 0) ) $user_output .= '</a>';
		$user_output .='          </div></div>';


	        echo "
                        <td id=\"col-hw\">
			".$user_output."
                        </td>";
        } else {
                # nothing shown if user unknown
        }

        #scan status
        if ($status == "") $status_txt = "New";
        if ($status == "new") $status_txt = "New";
        if ($status == "ongoing") $status_txt = "ongoing";
        if ($status == "feedback") $status_txt = "User feedback requested";
        if ($status == "duplicate") $status_txt = "Possible duplicate";
        if ($status == "complete") $status_txt = "Completed";

        if ($editmode != 1) {
                   echo "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>$status_txt</span>
                        </td>";
	 } else {
                   #in editmode it is possible to change the status
                   echo "
                        <td id=\"col4\">";

                   echo '<form action="?" method="post" class="scanpage-change-status">';

                        $statusSelector = '<select id="scanpage-status-selector" class="scanpage-status-selector" name="status-'.$sid.'">';
	                $statusSelector .= ( ($status == "new")or ($status == "duplicate"))? '<option value="new" selected>New</option>' : '<option value="new">New</option>';
       		        $statusSelector .= ($status == "ongoing")? '<option value="ongoing" selected>Ongoing</option>' : '<option value="ongoing">Ongoing</option>';
	                $statusSelector .= ($status == "complete")? '<option value="complete" selected>Complete</option>' : '<option value="complete">Complete</option>';
	                $statusSelector .= ($status == "feedback")? '<option value="feedback" selected>Feedback needed</option>' : '<option value="feedback">Feedback needed</option>';
        	        $statusSelector .= '</select>';

                    echo $statusSelector;
                    echo '<input type="submit" id="status-submit" name="status-submit" value="update" class="status-update-button" />
			  </form>';

                    $uid = get_current_user_id();
                    if ($uid == "") $uid = 0;
                    echo '
	                <script type="text/javascript">
        	        /* <![CDATA[ */

	                jQuery(document).ready( function($) {

				$(\'#status-submit\').click(function(){

                                var button = this;

                                // "we are processing" indication
                                var indicator_html = \'<img class="scan-load-button" id="button-update-status" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                                $(button).after(indicator_html);


                                //prepare Ajax data:
                                var session = "'.$sid.'";
                                var uid = "'.$uid.'";
                                var status = $("#scanpage-status-selector").val();
                                var data ={
                                        action: \'lhg_update_scan_status_ajax\',
                                        status: status,
                                        session: session,
                                        uid: uid

                                };


                                //load & show server output
                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                        //return to normal state
                                        $(button).val("Update");
                                        $(button).attr("class", "status-update-button-light");
                                        var indicatorid = "#button-update-status";
                                        $(indicatorid).remove();

                                });

                                //prevent default behavior
                                return false;

                                });


        	        });



                	/*]]> */

	                </script>';



                    echo "</td>";

         }

        echo "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>$distribution</span>
                        </td>";


        echo "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>$kversion</span>
                        </td>";


                        #$registration_date=$a_subscription->dt;
                        #list ($registration_date, $registration_time) = explode(" ",$registration_date);
                        $categorypart2 = "";
                        if ($category_name2 != "")  $categorypart2 = "<br>($category_name2)";


			$myquery = $lhg_price_db->prepare("SELECT COUNT(DISTINCT idstring) FROM `lhghwscans` WHERE sid = %s AND postid = 0", $sid);
			#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
			$num_unidentified_hw = $lhg_price_db->get_var($myquery);
                        #print "NU: $num_unidentified_hw";

			$myquery = $lhg_price_db->prepare("SELECT COUNT(DISTINCT idstring) FROM `lhghwscans` WHERE sid = %s AND postid = 0 AND pciid <> ''", $sid);
			$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
			$num_unidentified_pci = $lhg_price_db->get_var($myquery);
                        #var_dump($num_uni
                        #print "NU: $num_unidentified_hw";

			$myquery = $lhg_price_db->prepare("SELECT COUNT(DISTINCT postid) FROM `lhghwscans` WHERE sid = %s AND postid <> 0", $sid);
			#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
			$num_identified_hw = $lhg_price_db->get_var($myquery);

                        echo "
                        <td id=\"col2\">
                        <span class='subscribe-column-1'><center>".$txt_subscr_identified.": ".$num_identified_hw." <br> ".$txt_subscr_unknown.": $num_unidentified_hw  </center></span>
                        </td>";



                        echo "</tr>\n";

                echo "</table>";

if ( ($uid != "") && ($num_uid > 1) && (strlen($uid)>5) ) {
	if ($show_public_profile != 1) print "<br>&nbsp;<br>See overview of the <a href=./uid-".$uid.">".$num_uid." hardware scans of this user</a>.";
}



# Add user feedback exchange
lhg_feedback_area( $sid );



#
#
##### Identified Hardware / Known Hardware
#
#

if (count($identified_hw) > 0) {

echo "<h2>".$txt_subscr_knownhw."</h2>";

echo '
                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

				$(\'#known-hardware-submit\').click(function(){

                                var button = this;

                                // "we are processing" indication
                                var indicator_html = \'<img class="scan-load-button" id="button-load-known-hardware-comment" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                                $(button).after(indicator_html);


                                //prepare Ajax data:
                                var session = "'.$sid.'";
                                var comment = $("#known-hardware-usercomment").val();
                                var data ={
                                        action: \'lhg_scan_update_known_hardware_comment_ajax\',
                                        session: session,
                                        comment: comment
                                };


                                //load & show server output
                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                        $(button).append("Response");
                                        $(button).after(response);
                                        //$(box).append("Response: <br>IMG: "+imageurl+" <br>text: "+responsetext);

                                        //return to normal state
                                        $(button).val("Update");
                                        $(button).attr("class", "hwscan-comment-button-light");
                                        var indicatorid = "#button-load-known-hardware-comment";
                                        $(indicatorid).remove();

                                });

                                //prevent default behavior
                                return false;

                                });


                });

                // Comment on known hardware
                jQuery(document).ready( function($) {

                                $("[id^=known-hardware-comment-submit]").click(function() {

                                var id = $(this).attr(\'id\').substring(30);
                                var button = this;

                                // "we are processing" indication
                                var indicator_html = \'<img class="scan-load-button" id="button-load-known-hardware-comment" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                                //$(button).after(indicator_html);
                                $("#rot-indicator-"+id).html(indicator_html);
                                $("#known-hardware-comments-outerbox-"+id).css(\'background-color\',\'#dddddd\');


                                //prepare Ajax data:
                                var session = "'.$sid.'";
                                var postid = id;
                                var wpuid_de = "'.$wpuid_de.'";
        			var wpuid_com = "'.$wpuid_com.'";
                                var email = "";
                                var comment = $("#known-hardware-comment-"+id).val();
                                var data ={
                                        action: \'lhg_scan_create_hardware_comment_ajax\',
                                        session: session,
                                        comment: comment,
                                        wpuid_de: wpuid_de,
                                        wpuid_com: wpuid_com,
                                        email: email,
                                        postid: postid
                                };


                                //load & show server output
                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                	var return_comment     = $(response).find("supplemental return_comment").text();

                         		//$(button).append("Response");
                                        //$(button).after(response);
                                        //$(button).append("Response: <br>PID: "+postid);

                                        //return to normal state
                                        $(button).val("Update");
                                        $(button).attr("class", "hwscan-comment-button-light");
                                        var indicatorid = "#button-load-known-hardware-comment";
                                        $("#known-hardware-comments-outerbox-"+postid).html("<i>&quot;"+return_comment+"&quot;</i>");

                                        //$(indicatorid).remove();
                                        $("#rot-indicator-"+id).remove();
                                        $("#known-hardware-comments-outerbox-"+id).css(\'background-color\',\'#ffffff\');


                                });

                                //prevent default behavior
                                return false;

                                });


                });


                /*]]> */
                </script>';



if (count($identified_hw) == 0) {
        print '<div class="no-hw-found">'.$txt_subscr_nohwfound.'</div>';
}else {

                #get and check session ID
                #echo "Session ID: $sid <br>";

                echo '<table id="registration">';
                echo '<tr id="header">


                <td id="title-colhw">'.$txt_subscr_knownhw.'</td>';

                # comment column
                echo '<td id="hwscan-comment-col"><nobr>Please comment on Linux compatibility</nobr></td>';

                echo '<td id="hwscan-col2" width="13%"><nobr>'.$txt_subscr_ratecomp.'</nobr></td>';


                if ($userknown == 1)
                echo '<td id="hwscan-col3" width="13%"><nobr>'.$txt_subscr_addhw.'</nobr></td>';


                echo '<td id="col4">'.$txt_category.'</td>

                <!-- td id="col2">'.$txt_subscr_regist_date.'</td -->




                </tr>';

                foreach($identified_hw as $a_identified_hw){

                        $PID = $a_identified_hw->postid;

                        # If on the German server, we need the transverse post ID
                        # ToDo: Identify cases where no translation was done yet
                        if ($lang == "de") $PID = lhg_get_postid_de_from_com( $PID );

                        # get the rating field
                        ob_start();
		        $returnval .= the_ratings("div",$PID);
		        $out1 = ob_get_contents();
        		ob_end_clean();
                        if (!strpos($out1,"onmouseout")>0) $out1 = "already rated";
                        $out1 = str_replace("(No","<br>(No",$out1);

			echo "<tr id=\"regcont\">";

                        #List identified hw components
                        $comment_excerpt = "";
			$permalink = get_permalink( $PID );
			$title = translate_title( get_the_title( $PID ) );
			$s=explode("(",$title);
			$short_title=trim($s[0]);
			$title_part2=str_replace(")","",trim($s[1]));
                        if (strlen($title_part2) > 1) $title_part2 .= "<br>";

                        $img_attr = array(
					#'src'	=> $src,
					'class'	=> "hwscan-image image55",
					#'alt'	=> trim( strip_tags( $attachment->post_excerpt ) ),
					#'title'	=> trim( strip_tags( $attachment->post_title ) ),
				    );
                        $art_image=get_the_post_thumbnail( $PID , array(55,55), $img_attr, array('class' => 'image55') );

                        if ($art_image == ""){
                                #print "No Image";
                        	$art_image = '<img width="55" height="55" src="/wp-uploads/2013/03/noimage130.jpg" class="hwscan-image wp-post-image" alt="no-image" title="no-image"/>';
                        }

                        $comment= $a_identified_hw->usercomment;
                        $url=$a_identified_hw->url;
                        $id= $a_identified_hw->id;


                        #$category_ids[1]->cat_name = ""; #overwrite old values
                        $category_ids   = get_the_category ( $PID );
                        $category_name = $category_ids[0]->cat_name;
                        #$category_name2 = "";
                        $category_name2 = $category_ids[1]->cat_name;

                        # --- Registered users
		        global $wpdb;
			$usernum = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%' AND post_id = ".$PID);


                        // list HW entry

                        #$commenttext = "";
                        #if ($comment_excerpt != "") $commenttext='<div class="hwprofile-cite"><i>"'.wp_strip_all_tags($comment_excerpt).'"</i> <br>(<a href="'.$permalink.'#comment-'.$CID.'">'.$txt_subscr_more.'</a> - <a href="/wp-admin/comment.php?action=editcomment&c='.$CID.'">'.$txt_subscr_edit_comment.'</a>)</div>';

			$rating=the_ratings_results( $PID ,0,0,0,10);

                        $output_tmp = "
                        <td id=\"col-hw\"> <a class='hwscan-found-image' href='$permalink' target='_blank' >$art_image</a>

                        ".'<div class="subscribe-hwtext"><div class="subscribe-hwtext-span-scan"><a href='.$permalink.' target="_blank">'.$short_title.'</a></div></div>'.$title_part2.'</label>'.
                          "<span class='subscribe-column-23'>$rating</span>";

                        $output_tmp .= "</td>";

                        // --- Leave comment on Linux compatibility of hardware component
                        // check if comment already exists
                        $myquery = $lhg_price_db->prepare("SELECT commentid FROM `lhghwscans` WHERE sid = %s AND postid = %s ", $sid, $PID);
			$result = $lhg_price_db->get_var($myquery);


                        // if no comment provided in past, show comment box
                        if ($result == 0) {

                                if ( ($show_public_profile == 1) or ($editmode == 1) ) {
                                        # do not allow commenting if public profile or editmode
	                        	$output_tmp .= '<td>
                                                 (No comment)
						</td>';

                                }else{
	                        	$output_tmp .= '<td>
	                        		  <div class="known-hardware-comments" id="known-hardware-comments-outerbox-'.$PID.'">
						    <form action="?" method="post" class="scan-comment-on-hardware">
				        	      <textarea id="known-hardware-comment-'.$PID.'" name="hwcomment" cols="10" rows="3"></textarea><br>
				        	      <input type="submit" id="known-hardware-comment-submit-'.$PID.'" name="hwcomment-login" value="Post comment" class="hwscan-comment-button" />
	                                	      <div class="rot-indicator" id="rot-indicator-'.$PID.'"></div>
						    </form>
        		                          </div>
						</td>';
                                }
                        } else {
                                $comment_found = get_comment( $result, ARRAY_A );
                                $comment_text = $comment_found['comment_content'];
	                        $output_tmp .= '<td>
        	                		  <div class="known-hardware-comments-middle" id="known-hardware-comments-outerbox-'.$PID.'">
		                                        <i>&quot;'.$comment_text.'&quot;</i>
                	  		          </div>
						</td>';
                        }

                        //<td id=\"col4\">
                        //<span class='subscribe-column-2'>$deact<br>({$a_subscription->status})</span>
                        //</td>


                        // --- User to rate HW

                        $postid = $PID; #a_identified_hw->postid;

                        #if ($myrating == "n.a.")
                        $output_tmp .= "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>".$out1."</span>
                        </td>";


                        // --- Add to HW profile
                        if ($userknown == 1) {
	                        $hwbutton = lhg_add_hwbutton( $email, $PID );
        	                #$hwbutton = "Test";
                	        $output_tmp .= "
                        	<td id=\"col5\">
	                        <span class='column-hwbutton'>".$hwbutton."</span>
                                <div class='regusers'>(Reg. Linux users: ".$usernum.")</div>
        	                </td>";
                        }




                        #$registration_date=$a_subscription->dt;
                        #list ($registration_date, $registration_time) = explode(" ",$registration_date);
                        $categorypart2 = "";
                        if ($category_name2 != "")  $categorypart2 = "<br>($category_name2)";

                        $output_tmp .= "
                        <td id=\"col2\">
                        <span class='subscribe-column-1'><center>$category_name $categorypart2 </center></span>
                        </td>";



                        $output_tmp .= "</tr>\n";

                        # check if laptop or mainboard was identified
                        if ( (strpos($category_name, 'Laptop') !== false) or
                             (strpos($category_name, 'Mainboard') !== false) or
                             (strpos($category_name2, 'Laptop') !== false) or
                             (strpos($category_name, 'Mainboard') !== false) ) {
                                #error_log("Laptop/Mainboard was identified");
                                $mainboard_found = 1;
        		}


                        # allow sorting of results:
	                if ( (strpos($category_name, 'Laptop') !== false) or
                             (strpos($category_name, 'Mainboard') !== false) or
                             (strpos($category_name2, 'Laptop') !== false) or
                             (strpos($category_name, 'Mainboard') !== false) ) {
                                $output_mb = $output_tmp;
                        }elseif (strpos($category_name, 'CPU') !== false) {
                                $output_cpu = $output_tmp;
                        }elseif (strpos($category_name, 'Graphic') !== false) {
                                $output_graphic .= $output_tmp;
                        }elseif (strpos($category_name, 'Sound') !== false) {
                                $output_sound .= $output_tmp;
                        }else {
                                $output_rest .= $output_tmp;
                        }
                        //echo $output_tmp;

                }

                # output results in correct order
                echo $output_mb .
                     $output_cpu .
                     $output_graphic .
                     $output_sound .
                     $output_rest;

                echo "</table>";
}

$usercomment = lhg_get_usercomment($sid);
$buttontype = "green";
$buttontext = $txt_submit; #"Submit";
if ($usercomment != "") $buttontype = "light";
if ($usercomment != "") $buttontext = "Update";

#Ask, if HW scanner made errors?
if ($show_public_profile != 1)
echo ' <form action="?" method="post" class="usercomment">'.$txt_subscr_hwfeedback.'
       <br>
       <textarea id="known-hardware-usercomment" name="usercomment" cols="10" rows="3">'.$usercomment.'</textarea><br>
       <input type="submit" id="known-hardware-submit" name="email-login" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />
</form>
<br>
';


	#}
	#else{
	#	echo '<p>'.__('No subscriptions match your search criteria.', 'subscribe-reloaded').'</p>';
	#}
#</fieldset>
#</form>

} // do not show comment section if nothing was found


#
#
##### Multiple Identified Hardware
#
#


if (count($multi_identified_hw) > 0) {
	echo "<h2>".$txt_subscr_multiple."</h2>";
        #small table version at
        $multilimit = 4;


        foreach($multi_identified_hw as $a_identified_hw){


		$title = ($a_identified_hw->idstring);
		$usbid = ($a_identified_hw->usbid);

		echo '<table id="registration">';
                echo '<tr id="header">


                <td id="title-colhw">
                '.$txt_subscr_identified_usb.'<br>
                '.$title.' - (USB ID: '.$usbid.')
                </td>';


                if ($userknown == 1)
                echo '<td id="hwscan-col3" width="13%"><nobr>'.$txt_subscr_addhw.'</nobr></td>';

                echo '<td id="hwscan-col2" width="13%"><nobr>'.$txt_subscr_ratecomp.'</nobr></td>

                <td id="col4">'.$txt_category.'</td>

                <!-- td id="col2">'.$txt_subscr_regist_date.'</td -->
                </tr>';


                # split postids
                $postids = explode(",",$a_identified_hw->postid);

                $i=0;
	        foreach($postids as $postid){
                        $i++;

                        # If on the German server, we need the transverse post ID
                        # ToDo: Identify cases where no translation was done yet
                        if ($lang == "de") $postid = lhg_get_postid_de_from_com( $postid );


                        #print "IDs: ".count($postids)."<br>";
			if (count($postids) > $multilimit) {
                                # skip line

                        }else{
		                echo '<tr><td><div class="hwscan_option">'.$txt_subscr_option.' '.$i."</div>";
	                       #echo "PID: $postid";
        	                echo "</td><td></td><td>";
	        	        if ($userknown == 1) echo "<td></td>";
                        	echo '</td></tr>';
                        }


                        # get the rating field
                        ob_start();
		        $returnval .= the_ratings("div",$postid);
		        $out1 = ob_get_contents();
        		ob_end_clean();
                        if (!strpos($out1,"onmouseout")>0) $out1 = "already rated";


			echo "<tr id=\"regcont\">";

                        #List identified hw components
                        $comment_excerpt = "";
			$permalink = get_permalink($postid);
			$title = translate_title( get_the_title($postid) );
			$s=explode("(",$title);
			$short_title=trim($s[0]);
			$title_part2=str_replace(")","",trim($s[1]));
                        if (strlen($title_part2) > 1) $title_part2 .= "<br>";

                        $img_attr = array(
					#'src'	=> $src,
					'class'	=> "hwscan-image image55",
					#'alt'	=> trim( strip_tags( $attachment->post_excerpt ) ),
					#'title'	=> trim( strip_tags( $attachment->post_title ) ),
				    );
                        $art_image=get_the_post_thumbnail( $postid, array(55,55), $img_attr );

                        if ($art_image == ""){
                                #print "No Image";
                        	$art_image = '<img width="55" height="55" src="/wp-uploads/2013/03/noimage130.jpg" class="hwscan-image wp-post-image" alt="no-image" title="no-image"/>';
                        }

                        $comment= $a_identified_hw->usercomment;
                        $url=$a_identified_hw->url;
                        $id= $a_identified_hw->id;


                        #$category_ids[1]->cat_name = ""; #overwrite old values
                        $category_ids   = get_the_category ( $postid );
                        $category_name = $category_ids[0]->cat_name;
                        #$category_name2 = "";
                        $category_name2 = $category_ids[1]->cat_name;

                        # --- Registered users
		        global $wpdb;
			$usernum = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%' AND post_id = ".$postid);


                        // list HW entry

                        #$commenttext = "";
                        #if ($comment_excerpt != "") $commenttext='<div class="hwprofile-cite"><i>"'.wp_strip_all_tags($comment_excerpt).'"</i> <br>(<a href="'.$permalink.'#comment-'.$CID.'">'.$txt_subscr_more.'</a> - <a href="/wp-admin/comment.php?action=editcomment&c='.$CID.'">'.$txt_subscr_edit_comment.'</a>)</div>';

			$rating=the_ratings_results($postid,0,0,0,10);

			if (count($postids) > $multilimit) {
                                #small version, no image
	                        echo "
        	                <td id=\"col-hw\"> 
                        	".'<div class="subscribe-hwtext"><div class="subscribe-hwtext-span-scan-small"><a href='.$permalink.' target="_blank">'.$short_title.'</a></div></div>'.$title_part2.'</label>'.
	                          "<span class='subscribe-column-23-small'>$rating</span>";
				print    " </td>";

                        }else{
                        echo "
                        <td id=\"col-hw\"> <a class='hwscan-found-image' href='$permalink' target='_blank' >$art_image</a>

                        ".'<div class="subscribe-hwtext"><div class="subscribe-hwtext-span-scan"><a href='.$permalink.' target="_blank">'.$short_title.'</a></div></div>'.$title_part2.'</label>'.
                          "<span class='subscribe-column-23'>$rating</span>";


print                       " </td>";

                        }
                        //<td id=\"col4\">
                        //<span class='subscribe-column-2'>$deact<br>({$a_subscription->status})</span>
                        //</td>

                        // --- Add to HW profile
                        if ($userknown == 1) {
	                        $hwbutton = lhg_add_hwbutton( $email, $a_identified_hw->postid);
        	                #$hwbutton = "Test";
                	        echo "
                        	<td id=\"col5\">
	                        <span class='column-hwbutton'>".$hwbutton."</span>
                                <div class='regusers'>(Reg. Linux users: ".$usernum.")</div>
        	                </td>";
                        }


                        // --- User to rate HW

                        $postid = $a_identified_hw->postid;
                        if ($lang == "de") $postid = lhg_get_postid_de_from_com($postid);

                        #if ($myrating == "n.a.")
                        echo "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>".$out1."</span>
                        </td>";


                        #$registration_date=$a_subscription->dt;
                        #list ($registration_date, $registration_time) = explode(" ",$registration_date);
                        $categorypart2 = "";
                        if ($category_name2 != "")  $categorypart2 = "<br>($category_name2)";

                        echo "
                        <td id=\"col2\">
                        <span class='subscribe-column-1'><center>$category_name $categorypart2 </center></span>
                        </td>";



                        echo "</tr>\n";
		}


		$usercomment_multi = lhg_get_usercomment_multi($sid,$id);
		$buttontype = "green";
		$buttontext = $txt_submit; #"Submit";
		if ($usercomment_multi != "") $buttontype = "light";
		if ($usercomment_multi != "") $buttontext = "Update";

		if ($show_public_profile != 1)
                echo ' 	<tr><td>
			<form action="?" method="post" class="usercomment_multi">
       			Which hardware are you actually using?<br>
       			<textarea name="usercomment_multi_'.$id.'" cols="10" rows="3">'.$usercomment_multi.'</textarea><br>
       			<input type="submit" name="email-login" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />
			</form>
			</td>';

		print ' <td></td> <td></td> ';


                if ($userknown == 1) echo "<td></td>";

		echo '</tr>';
		echo "</table>";

	}

}

#
#
##### Mainboards and Laptops
#
#

if (count($unidentified_hw_pci) > 0) {


        if ($mainboard_found != 1) {
	        # We are still searching for a mainboard/laptop


        	# check if mainboard or laptop
	        $laptop_prob = lhg_scan_is_laptop($sid);
        	if ($laptop_prob > 0.8) $mb_or_laptop = "Laptop";
	        if ($laptop_prob <= 0.8) $mb_or_laptop = "Mainboard";

	        $mb_name = lhg_get_mainboard_name( $sid );
        	$clean_mb_name = lhg_clean_mainboard_name( $mb_name );
		print "<h2>".$txt_subscr_new." ".$mb_or_laptop.": ".$clean_mb_name."</h2>";
	        #print '<div id="mbname">Identified name: '.$clean_mb_name."<span id='details-mb' class='details-link'></span></div>";
	        if ($show_public_profile != 1)
		        print '<div id="hidden-details-mb">Full identifier: '.$mb_name.'</div>';


		$mb_usercomment = lhg_get_mb_usercomment($sid);
		$url_mb = lhg_get_mb_url($sid);
		$buttontype = "green";
		$buttontext = $txt_submit; #"Submit";
		if ($mb_usercomment != "") $buttontype = "light";
		if ($mb_usercomment != "") $buttontext = "Update";

	        $newPostID_mb = lhg_create_mainboard_article($mb_name, $sid);

	        // creating rating stars
		ob_start();
		$returnval .= the_ratings("div",$newPostID_mb);
		$out1 = ob_get_contents();
	        ob_end_clean();
        	if (!strpos($out1,"onmouseout")>0) $out1 = "already rated";
	        $out1 = str_replace("(No Ratings Yet)","",$out1);

        	# only scanning person should be allowed to rate here!
	        if ($show_public_profile != 1)
			$article_created = '<span class="rating-mb"><nobr>'.$out1.'</nobr></span>';


		if ($show_public_profile != 1)
			echo ' <form action="?" method="post" class="mb-usercomment">
		       Please rate the '.$mb_or_laptop.': '.$article_created.'
		       Let us know if the '.$mb_or_laptop.' was recognized incorrectly and how it is supported under Linux:<br>
		       <textarea id="mb-usercomment" name="mb-usercomment" cols="10" rows="3">'.$mb_usercomment.'</textarea><br>
		       If possible, please leave an URL to a web page where the '.$mb_or_laptop.' is described (e.g. manufacturer`s data sheet or Amazon.com page).<br>URL:
		       <input id="url-mb" name="url-mb" type="text" value="'.$url_mb.'" size="40" maxlenght="290">
		       <br>
		       <input type="submit" id="mb-submit" name="email-login" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />';

	       if (current_user_can('publish_posts') && ($show_public_profile != 1) && ($editmode == 1) ) {
        	   print '&nbsp;&nbsp;&nbsp;(<a href="/wp-admin/post.php?post='.$newPostID_mb.'&action=edit&scansid='.$sid.'">finalize article</a>)';
	       }

	       print '</form>';
	}else{
		print "<h2>Unknown PCI components</h2>";
        }


        # if we have identified the wrong mainboard we need a way to create a new MB article:
        if ($mainboard_found == 1)
        if ( ($editmode == 1) && (count($unidentified_hw_pci) > 0) ) {

                $myquery = $lhg_price_db->prepare("SELECT dmi FROM `lhgscansessions` WHERE sid = %s", $sid);
		#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
		$dmi = $lhg_price_db->get_var($myquery);
                list ( $null, $dmi) = explode(" DMI:",$dmi);
                if ($dmi == "") $dmi = "(nothing found)";
                print "DMI Info: ".$dmi;
                print '<br><a href="" id="create-mainboard"> Create new mainboard article</a>';

print           '<script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

                                $("#create-mainboard").click(function() {
                                	var indicator_html = \'<img class="scan-load-button" id="button-load-new-mb" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                                	$(this).after(indicator_html);

                                    //var id = $(this).attr(\'id\').substring(8);
                                    //$("#pci-feedback-"+id).show("slow");
                                    //$("#updatearea-"+id).show();
				    //$("#scan-comments-"+id).show();

                                    //prepare Ajax data:
                                    var session = "'.$sid.'";
                                    var title = "'.trim($dmi).'";
	                            var data ={
                                        action: \'lhg_create_mainboard_post_ajax\',
                                        session: session,
                                        title: title
                                    };

                                    $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
                                       //currently no visual feedback
                                        var postid     = $(response).find("supplemental postid").text();

	                                $("#button-load-new-mb").hide();
                	                //$("#create-mainboard").after(" DoneA"+postid);
                                        var newurl = "/wp-admin/post.php?post=" + postid + "&action=edit&scansid=" + session;
        	                        $("#create-mainboard").after("(<a id=\"created-mb-article\" href=\"" + newurl + "\">finalize article</a>)");
                                        $("#create-mainboard").hide();
                                    });
                                    //$(this).replaceWith("Test");

	                            //prevent default behavior
        	                    return false;

                                });


	                        //prevent default behavior
        	                return false;

        	});

                /*]]> */
                </script>';
	} // end create MB article


        print "<table id='mainboard-scan-table'>
                 <tr id='mainboard-header'><td class='pci-column-onboard'>On-board?</td><td>".$txt_subscr_foundhwid."</td><td class='pciid-column'>PCI ID</td></tr>";

        # list of IDs that should be hidden in overview
        $hidearray = array();

        foreach($unidentified_hw_pci as $a_identified_hw){

                #error_log("Num: ".count($unidentified_hw_pci));

        	$skip = 0;
                #List UN-identified hw components
		$title = ($a_identified_hw->idstring);
		$usbid = ($a_identified_hw->usbid);
		$pciid = ($a_identified_hw->pciid);
                $art_image=get_the_post_thumbnail( $a_identified_hw->postid, array(55,55) );
                $comment=( $a_identified_hw->usercomment );
                $url=( $a_identified_hw->url );
                $id=( $a_identified_hw->id );
                $scantype=( $a_identified_hw->scantype );

                $default_y = 'checked="checked"';
                $default_n = "";
                $buttontype = "green";
		$buttontext = $txt_submit; #"Submit";
                $showme = lhg_show_scanned_component( $title , $id, $pciid );
                $short_pci_title = lhg_clean_pci_title( $title );
                $is_onboard = lhg_pci_component_is_onboard( $title, $sid, $id, $pciid);

                global $txt_yes;
                global $txt_no;

                if ($is_onboard == "yes")  {  $default_y = 'checked'; $default_n = '';	}
                if ($is_onboard == "no") {    $default_y = ''; $default_n = 'checked'; $showme = true; }
		#if ($is_onboard == "yes")  { array_push( $pci_obl , $pciid); }
                #print "IO: $is_onboard - OBL: ".var_dump($pci_obl)." - PCIID: $pciid<br>";


		if ( ($comment != "") or ($url != "") ) $buttontype = "light";
		if ( ($comment != "") or ($url != "") ) $buttontext = "Update";
		if ( ($comment != "") or ($url != "") ) $showme = true;

                # if only some components left this obviously is no mainboard.
                # Therefore, show everything
                if (count($unidentified_hw_pci) < 5) { $showme = true; $allshown = true; }

                $hidearray = array();
                $tr_class="";
                if ( !$showme ) $hidearray = array_push( $hidearray, $id);
                if ( !$showme ) $tr_class = 'class="mb-default-hidden"';


                $newPostID_pci = lhg_create_pci_article($short_pci_title, $sid, $id);

	        // creating rating stars
		ob_start();
		$returnval .= the_ratings("div",$newPostID_pci);
		$out1 = ob_get_contents();
        	ob_end_clean();
        	if (!strpos($out1,"onmouseout")>0) $out1 = "already rated";
	        $out1 = str_replace("(No Ratings Yet)","",$out1);

        	# only scanning person should be allowed to rate here!
                $article_created = '<span class="rating-mb"></span>';
                if ($show_public_profile != 1)
		$article_created = '<div class="rating-pci"><nobr>'.$out1.'</nobr></div>';


                print '
                	<tr '.$tr_class.'>';

        if ($show_public_profile != 1) {
                print '
                        <form action="?" method="post" class="hwcomments">
                        <td class="pci-column-onboard pci-column-onboard-radiob">
                          <fieldset>
                             '.$txt_yes.' <input type="radio" id="radio-y-'.$id.'" name="on-board" value="y" '.$default_y.' />
                             <input type="radio" id="radio-n-'.$id.'" name="on-board" value="n" '.$default_n.' /> '.$txt_no.'
                          </fieldset>
                        </td>
                        <td><div class="pci-title"><b>'.$short_pci_title.'</b><span id="show-details-hw-'.$id.'"></span></div>
                           <div class="pci-feedback" id="pci-feedback-'.$id.'">
                              <div id="details-hw-'.$id.'">Full identifier: '.$title.'</div>

			       <div id="updatearea-'.$id.'">
                                Please rate hardware: '.$article_created.'
       				Help us adding this hardware to our database. Please identify this hardware and describe its Linux compatibility:<br>
       				<textarea id="comment-'.$id.'" name="comment-'.$id.'" cols="10" rows="3">'.lhg_clean_scan_comment($comment).'</textarea><br>
       				If possible, please leave an URL to a web page where the hardware is described (e.g. manufacturer`s data sheet or Amazon.com page).<br>URL:
       				<input id="url-'.$id.'" name="url-'.$id.'" type="text" value="'.$url.'" size="40" maxlenght="290">
       				<input id="postid-'.$id.'" name="postid-'.$id.'" type="hidden" value="'.$newPostID.'">
			       </div>
       			       <br><input type="submit" name="scan-comments-'.$id.'" id="scan-comments-'.$id.'" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />';
	} else {
                print '
                        <td class="pci-column-onboard pci-column-onboard-radiob">';

                if ($default_y != "") print $txt_yes;
                if ($default_n != "") print $txt_no;
                print '
                        </td>
                        <td><div class="pci-title"><b>'.$short_pci_title.'</b><span id="show-details-hw-'.$id.'"></span></div>';
       }


	       if (current_user_can('publish_posts') && ($show_public_profile != 1) ) {
        	   print '&nbsp;&nbsp;&nbsp;(<a href="/wp-admin/post.php?post='.$newPostID_pci.'&action=edit&scansid='.$sid.'">finalize article</a>)';
	       }

               print '
                           </div>
                        </td>
                        <td class="pciid-column">'.$pciid.'</td>
                        </form>
                        </tr>';
        }
        print "</table>";
        print '<div id="mainboard-show-more"></div>';

	# not needed any longer due to pci backend selector
        #if (current_user_can('publish_posts') && ($show_public_profile != 1) ) {
        #	   print '&nbsp;&nbsp;&nbsp;(<a href id="update-pcilist">Update PCI lists</a>)';
        #}

        if ($show_public_profile != 1)
	echo '
                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

				$(\'.hwdetail\').hide();
				$(\'.pciid-column\').hide();
				$(\'.mb-default-hidden\').hide();
                                ';
                                // do not show this option if everything is already shown
                                if ($allshown != true) print '$(\'<a href id="toggleButton">Show hidden components</a>\').prependTo(\'#mainboard-show-more\');';

                                print '
                                $(\'<a href id="show-more-mb">Show details</a>\').prependTo(\'#details-mb\');
                                $(\'#hidden-details-mb\').hide();

                                $(\'#details-mb\').click(function(){
                                  $(\'#hidden-details-mb\').show("slow");
                                  $("#show-more-mb").hide("slow");
                                  return false;

                                });


                                // show hidden mainboard components
                                $(\'#toggleButton\').click(function(){
                                  $(\'.mb-default-hidden\').toggle("slow");

       				  if( $(\'.mb-default-hidden\').is(":visible")) {
                                     $(\'.pciid-column\').show("slow");
                                     $("#toggleButton").html("Hide minor components");
                                  } else {
                                     $(\'.pciid-column\').hide("slow");
                                     $("#toggleButton").html("Show hidden components");
                                  }

                                  return false;

                                });

                                // radio button actions
                                $("[id^=radio-n-]").click(function() {
                                    var id = $(this).attr(\'id\').substring(8);
                                    $("#pci-feedback-"+id).show("slow");
                                    //$("#updatearea-"+id).show();
				    //$("#scan-comments-"+id).show();

                                    //prepare Ajax data:
                                    var session = "'.$sid.'";
	                            var data ={
                                        action: \'lhg_scan_onboardn_ajax\',
                                        id: id,
                                        session: session
                                    };

                                    $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
                                       //currently no visual feedback

                                    });


                                });

                                $("[id^=radio-y-]").click(function() {
                                    var id = $(this).attr(\'id\').substring(8);
                                    //$("#updatearea-"+id).hide("slow");
	                            $("#pci-feedback-"+id).hide("slow");
                                    //$("#scan-comments-"+id).hide("slow");

                                    //prepare Ajax data:
                                    var session = "'.$sid.'";
	                            var data ={
                                        action: \'lhg_scan_onboardy_ajax\',
                                        id: id,
                                        session: session
                                    };

                                    $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
                                        //currently no visual feedback

                                    });

                                });

                                $("[id^=radio-y-]").each(function(){
                                        var id = $(this).attr(\'id\').substring(8);

                                	if ($(this).is(":checked")) {
	                                    //$("#updatearea-"+id).hide();
	                                    $("#pci-feedback-"+id).hide();
        	                            //$("#scan-comments-"+id).hide();
                	                }

                                        // check if comments/URL provided -> do not hide in such cases!
                                	if ($("textarea#comment-"+id).val()) {
	                                    $("#pci-feedback-"+id).show();
                                        }

                                	if ($("input#url-"+id).val()) {
	                                    $("#pci-feedback-"+id).show();
                                        }

                                });

                                //User leaves comment on Mainboard
				$(\'#mb-submit\').click(function(){

                                var button = this;

                                // "we are processing" indication
                                var indicator_html = \'<img class="scan-load-button" id="button-load-mb-comment" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                                $(button).after(indicator_html);


                                //prepare Ajax data:
                                var session = "'.$sid.'";
                                var comment = $("#mb-usercomment").val();
                                var mb_url = $("#url-mb").val();
                                var data ={
                                        action: \'lhg_scan_update_mb_comment_ajax\',
                                        session: session,
                                        url: mb_url,
                                        comment: comment
                                };


                                //load & show server output
                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                        $(button).append("Response");
                                        $(button).after(response);
                                        //$(box).append("Response: <br>IMG: "+imageurl+" <br>text: "+responsetext);

                                        //return to normal state
                                        $(button).val("Update");
                                        $(button).attr("class", "hwscan-comment-button-light");
                                        var indicatorid = "#button-load-mb-comment";
                                        $(indicatorid).remove();

                                });

                                //prevent default behavior
                                return false;

                                });

                });

                /*]]> */
                </script>';


        # update list of components -> on board or not?
        lhg_update_mainboard_fingerprint( $sid, $newPostID_mb );

}


# check if remaining HW is filtered and nothing remains to be shown
foreach($unidentified_hw as $a_identified_hw){

                        $skip = 0;
                        #List UN-identified hw components
			$title = ($a_identified_hw->idstring);
			$usbid = ($a_identified_hw->usbid);
			$pciid = ($a_identified_hw->pciid);
                        $art_image=get_the_post_thumbnail( $a_identified_hw->postid, array(55,55) );
                        $comment=( $a_identified_hw->usercomment );
                        $url=( $a_identified_hw->url );
                        $id=( $a_identified_hw->id );
                        $scantype=( $a_identified_hw->scantype );

                        # ToDo: check as part of sub-routine (used twice! in code)

                        if ( ($usbid != "") && (strpos($title, "Matching Hub") > 0) ) { $skip = 1; $num_skip_tmp ++; }
                        if ( ($usbid != "") && (strpos($title, "root hub") > 0) ) { $skip = 1; $num_skip_tmp ++; }
                        # Skip "Intel Corp."
                        if ( ($usbid == "8087:8000") ) { $skip = 1; $num_skip_tmp ++; }
                        if ( ($usbid == "8087:8008") ) { $skip = 1; $num_skip_tmp ++; }
}


if ($num_skip_tmp == count($unidentified_hw) ) {
        # all remaining components are filtered components!
        $skip_unknown_hw = 1;
}


if ( (count($unidentified_hw) > 0) && ($skip_unknown_hw != 1) ) {

print "<h2>".$txt_subscr_newhw."</h2>";



                //print "Insert AJAX";
                if ($show_public_profile != 1)
		echo '
                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

                      //Popup handler
		      $("#my_popup").popup({
                        onclose: function() { $("#my_popup_contents").empty();},
                        opacity: 0.3,
                        transition: "all 0.3s"
                      });


                                // binding for elements not existing at page load
                                // amazon suggestion selected
                                $(document.body).on("click","[id^=autocreate-]", function() {


                                    var option = $(this).attr(\'id\').substring(11,12);
                                    var id = $(this).attr(\'id\').substring(13);
                                    var pid = $("#PID-"+id).val();

                                    var asin1 = $("#ASIN1-"+id).val();
                                    var title1 = $("#TITLE1-"+id).val();
                                    var imgurl1 = $("#IMGURL1-"+id).val();
                                    var produrl1 = $("#PRODURL1-"+id).val();

                                    var asin2 = $("#ASIN2-"+id).val();
                                    var title2 = $("#TITLE2-"+id).val();
                                    var imgurl2 = $("#IMGURL2-"+id).val();
                                    var produrl2 = $("#PRODURL2-"+id).val();

                                    var asin3 = $("#ASIN3-"+id).val();
                                    var title3 = $("#TITLE3-"+id).val();
                                    var imgurl3 = $("#IMGURL3-"+id).val();
                                    var produrl3 = $("#PRODURL3-"+id).val();

                                    var session = "'.$sid.'";
                                    //var area = $("#updatearea-"+id);
                                    var area= $("#my_popup");

                                    //alert("Pressed:"+id);

                                    if (option == 1) { $("#url-"+id).val(produrl1); }
                                    if (option == 2) { $("#url-"+id).val(produrl2); }
                                    if (option == 3) { $("#url-"+id).val(produrl3); }
                                    $("#scan-comments-"+id).click();
                                    // $("#rating_"+pid+"_5").click();

	                            var data ={
                                        action: \'lhg_update_article_by_amazon_search\',
                                        id: id,
                                        pid: pid,

                                        asin1: asin1,
                                        asin2: asin2,
                                        asin3: asin3,

                                        title1: title1,
                                        title2: title2,
                                        title3: title3,

                                        imgurl1: imgurl1,
                                        imgurl2: imgurl2,
                                        imgurl3: imgurl3,

                                        produrl1: produrl1,
                                        produrl2: produrl2,
                                        produrl3: produrl3,

                                        //asin2: asin2,
                                        //asin3: asin3,
                                        option: option,
                                        session: session
                                    };

                                    $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
                                       //currently no visual feedback

                                       //Debug:
                                       //$(area).append("Response update: "+response);
                                       //$(area).append("Produrl1: "+produrl1);

                                    });

                                    // var cnt = $("#my_popup_wrapper").contents();
				    // $("#my_popup_wrapper").remove();
                                    // $("#my_popup_background").replaceWith(cnt);

                                   //Disabled for debugging
                                       $("#my_popup_close").click();
                                       $("#my_popup_contents").empty();

                                    //$("#my_popup").popup({
				    //      background: false
				    //    });
                                    // $("#my_popup").toggle();

                                    return false;

                                });



                                // Show further scan details on request

                                $("[id^=show-details-hw-]").each(function(){
                                	var id = $(this).attr(\'id\').substring(16);
	                                // $(\'<a href id="show-more-details-\'+id+\'" class="show-details-link">Show details</a>\').prependTo(\'#show-details-hw-\'+id);
                                        $("#details-hw-"+id).hide();
                                });
                                $("[id^=show-details-hw-]").click(function(){
                                	var id = $(this).attr(\'id\').substring(16);
	                                $(\'#details-hw-\'+id).show("slow");
                                  	$("#show-details-hw-"+id).hide("slow");
                                        // in case of mainboard components show full comment panel
                                        $("#pci-feedback-"+id).show("slow");
                                        return false;
                                });





				$(\'[name^="scan-comments-"]\').click(function(){

                                var button = this;
                                var id = $(button).attr(\'name\').substring(14);
                                var boxname = "#updatearea-".concat(id);
                                var urlinput = "#url-".concat(id);
                                var loadname = "button-load-".concat(id);
                                var postidname = "#postid-".concat(id);
                                var postid = $(postidname).val();
                                var box = $(boxname);

                                // "we are processing" indication
                                var indicator_html = \'<img class="scan-load-button" id="button-load-\'.concat(id);
                                indicator_html = indicator_html.concat(\'" src="/wp-uploads/2015/11/loading-circle.gif">\');

                                $(box).css(\'background-color\',\'#dddddd\');
                                $(button).after(indicator_html);


                                //prepare Ajax data:
                                var session = "'.$sid.'";
                                var asinURL = $(urlinput).val();
                                var comment = $("#comment-"+id).val();
                                var data ={
                                        action: \'lhg_scan_update_ajax\',
                                        id: id,
                                        session: session,
                                        asinURL: asinURL,
                                        postid: postid,
                                        comment: comment
                                };


                                //$(box).append("Debug: "+asinURL);


                                //load & show server output
                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                        var imageurl     = $(response).find("supplemental imgurl").text();
                                        var responsetext = $(response).find("supplemental text").text();
                                        var newtitle     = $(response).find("supplemental newtitle").text();
                                        var properties   = $(response).find("supplemental properties").text();


                                        //Debug:
                                        //$(box).append("Response: <br>IMG: "+imageurl+" <br>text: "+responsetext);

                                        if (!imageurl.trim()){
                                                //update image
	                                        //$(box).append("Image empty");
                                        }else{
                                                // Image found - replace USB logo
                                                var imageid = "#hwscan-usblogo-"+id;
                                                $(imageid).attr("src",imageurl);
                                                //$(box).append("Image not empty");

                                        }
                                        //return to normal state
                                        $(box).css(\'background-color\',\'#ffffff\');
                                        $(button).val("Update");
                                        $(button).attr("class","hwscan-comment-button-light");
                                        var indicatorid = "#button-load-".concat(id);
                                        $(indicatorid).remove();
                                        $("#properties-"+id).text(properties);
                                        $("#title-"+id).text(newtitle);
                                        $(\'#details-hw-\'+id).show("slow");


                                });

                                //prevent default behavior
                                return false;

                                });


                                //
                                // auto-finder click handling
                                //

				$(\'[id^="finder-"]\').click(function(){

                                        $("#my_popup").popup("show");

                                        var indicator_html = \'<div id="scan-load-area">Searching for hardware...<img class="scan-load-button" id="auto_search_ongoing" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" /></div>\';

	                                var clickedlink = this;
        	                        var id = $(clickedlink).attr(\'id\').substring(7);
                	                var pid = $(clickedlink).attr(\'name\').substring(4);
                                        //var area = $("#updatearea-"+id);
                                        var area= $("#my_popup_contents");
                                        $(area).append(indicator_html);
                                        //$(area).append("Test-> PID: "+pid+" ID: "+id);


	                                //prepare Ajax data:
        	                        var session = "'.$sid.'";
                                	var data ={
                                        	action: \'lhg_amazon_finder_ajax\',
                                                session: session,
	                                        id: id,
        	                                pid: pid
	                                };


	                                //load & show server output
        	                        $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                  	      //Debug:
                                                $("#scan-load-area").remove();
                                        	$(area).append(response);
	                                });

        				return false;
                                });


                                //
                                // transform to comment
                                //
                                $(\'[id^="transform-to-comment-"]\').click(function(){

                                        var indicator_html = \'<img class="scan-load-button" id="auto_search_ongoing" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
	                                var clickedlink = this;
        	                        var id = $(clickedlink).attr(\'id\').substring(21);
                	                var pid = $(clickedlink).attr(\'name\').substring(7);
                                        $(clickedlink).append(indicator_html);


	                                //prepare Ajax data:
        	                        var session = "'.$sid.'";
                	                var postid = pid;
                        	        var wpuid_de = "'.$wpuid_de.'";
	        			var wpuid_com = "'.$wpuid_com.'";
        	                        var email = "";
                	                var comment = $("#comment-"+id).val();

                                	var data ={
                                        	action: \'lhg_scan_create_hardware_comment_ajax\',
	                                        session: session,
        	                                comment: comment,
                	                        wpuid_de: wpuid_de,
                        	                wpuid_com: wpuid_com,
                                	        email: email,
	                                        postid: postid
        	                        };


                                	//load & show server output
	                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

        	                        	var return_comment     = $(response).find("supplemental return_comment").text();

                                  	      	//Debug:
                                                $(clickedlink).after("<b>&nbsp;done</b>");
                                                $("#auto_search_ongoing").remove();
                                        	//$(area).append(response);
	                                });

        				return false;
                                });


                                //
                                // append comment to article
                                //
                                $(\'[id^="add-comment-"]\').click(function(){

                                        var indicator_html = \'<img class="scan-load-button" id="auto_search_ongoing" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
	                                var clickedlink = this;
        	                        var id = $(clickedlink).attr(\'id\').substring(12);
                	                var pid = $(clickedlink).attr(\'name\').substring(7);
                                        $(clickedlink).append(indicator_html);


	                                //prepare Ajax data:
        	                        var session = "'.$sid.'";
                	                var postid = pid;
                        	        var wpuid_de = "'.$wpuid_de.'";
	        			var wpuid_com = "'.$wpuid_com.'";
                                        var editor = "'.get_current_user_id(),'";
        	                        var email = "";
                	                var comment = $("#comment-"+id).val();

                                	var data ={
                                        	action: \'lhg_scan_append_hardware_comment_ajax\',
	                                        session: session,
        	                                comment: comment,
                	                        wpuid_de: wpuid_de,
                        	                wpuid_com: wpuid_com,
                                	        email: email,
                                	        editor: editor,
	                                        postid: postid
        	                        };


                                	//load & show server output
	                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

        	                        	var return_comment     = $(response).find("supplemental return_comment").text();

                                  	      	//Debug:
                                                $(clickedlink).after("<b>&nbsp;done</b>");
                                                $("#auto_search_ongoing").remove();
                                        	//$(area).append(response);
	                                });

        				return false;
                                });


                });



                /*]]> */
                </script>';


print '
<!-- Add an optional button to open the popup -->

  <!-- Add content to the popup -->
  <div id="my_popup">

    <div id="my_popup_contents">

    </div>


    <!-- Add an optional button to close the popup -->
    <button class="my_popup_close" id="my_popup_close">Close</button>

  </div>

  ';


	# JQuery code which updates article if selected
	print '
                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {


                });

                /*]]> */
                </script>';


                echo '<table id="registration">';
                echo '<tr id="header">


                <td id="title-colhw">'.$txt_subscr_foundhwid.'</td>
                <td>';
	        if ($show_public_profile != 1) print $txt_subscr_rate; #"Rate Hardware";
                print '
                </td>
                <td id="col2" width="13%"><nobr>'.$txt_subscr_type.'</nobr></td>

                <!-- td id="col4">'.$txt_subscr_modus.'</td -->

                <!-- td id="col2">'.$txt_subscr_regist_date.'</td -->




                </tr>';
                $mainboard_array="";
                foreach($unidentified_hw as $a_identified_hw){

                        $skip = 0;
                        #List UN-identified hw components
			$title = ($a_identified_hw->idstring);
			$usbid = ($a_identified_hw->usbid);
			$pciid = ($a_identified_hw->pciid);
                        $art_image=get_the_post_thumbnail( $a_identified_hw->postid, array(55,55) );
                        $comment=( $a_identified_hw->usercomment );
                        $url=( $a_identified_hw->url );
                        $id=( $a_identified_hw->id );
                        $scantype=( $a_identified_hw->scantype );


                        if ( ($usbid != "") && (strpos($title, "Matching Hub") > 0) ) { $skip = 1; $num_skip ++; }
                        if ( ($usbid != "") && (strpos($title, "root hub") > 0) ) { $skip = 1; $num_skip ++; }
                        # Skip "Intel Corp."
                        if ( ($usbid == "8087:8000") ) { $skip = 1; $num_skip ++; }
                        if ( ($usbid == "8087:8008") ) { $skip = 1; $num_skip ++; }

                        if ($skip == 0) {

                        #if ($pciid != ""){
                        #        $is_mainboard_component = 0;
                        #        # check if this is a Mainboard component
                        #        if ( strpos(" ".$title, "PCI bridge") > 0) $is_mainboard_component = 1;
                        #        if ( strpos(" ".$title, "ISA bridge") > 0) $is_mainboard_component = 1;
                        #        if ( strpos(" ".$title, "USB controller") > 0) $is_mainboard_component = 1;
                        #
                        #        if ($is_mainboard_component == 1) $mainboard_array = array_merge($mainboard_array, $a_identified_hw);
			#}

                	if ($usbid != "") $logo = "<img src='".$urlprefix."/wp-uploads/2014/12/USB_logo.jpg' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown USB device'>";
                	if ($pciid != "") $logo = "<div class='hwscan-pcilogo'>&nbsp;&nbsp;PCI</div>";
                	if ($scantype == "cpu") $logo = "<img src='".$urlprefix."/wp-uploads/2014/12/cpu-image.png' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown CPU'>";

                	if ($scantype == "drive") {
                        	$logo = "<img src='".$urlprefix."/wp-uploads/2014/12/drive-hdd.png' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown Drive'>";
                                if (strpos(" ".$title,"CD-ROM") > 0) $logo = "<img src='".$urlprefix."/wp-uploads/2014/12/drive-cd.png' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown CD/DVD Drive'>";
                                if (strpos(" ".$title,"SSD") > 0) $logo = "<img src='".$urlprefix."/wp-uploads/2014/12/drive-ssd.png' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown SSD Drive'>";

	                        #clean title
        	                $title = str_replace("Direct-Access","",$title);
                	        if (strpos(" ".$title,"CD-ROM") == 1) $title = substr($title, 6);

			}


                        $titleshort = str_replace(" ","",$title);
			if ($titleshort == "") $title = "(No identifier found)";

                	if ($scantype == "logitech_unifying_receiver") {
                                $title= "Logitech ".$title;
			}

                        //ignore standard logo, if already something was identified
        		$imgurl = lhg_get_imgurl( $sid, $id);
                        //print "IMGurl: $imgurl<br>";
        	        if ($imgurl != "") $logo = "<img src='".$imgurl."' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Logo'>";



$buttontype = "green";
$buttontext = $txt_submit ; #"Submit";
if ( ($comment != "") or ($url != "") ) $buttontype = "light";
if ( ($comment != "") or ($url != "") ) $buttontext = "Update";


# Auto-added new articles

if ($scandebug != 1) {
# skip auto creation in debug mode (timing!!)

$article_created = "";
if ($scantype == "cpu") {
	$newPostID = lhg_create_cpu_article($title, $sid, $id);

	ob_start();
	  $returnval .= the_ratings("div",$newPostID);
	  $out1 = ob_get_contents();
        ob_end_clean();
        if (!strpos($out1,"onmouseout")>0) $out1 = "already rated";
        $out1 = str_replace("(No Ratings Yet)","",$out1);

        # only scanning person should be allowed to rate here!
        if ($show_public_profile != 1)
	$article_created = $txt_subscr_pleaserate.": <br><nobr>$out1</nobr>";

}

if ($scantype == "drive") {
	$newPostID = lhg_create_drive_article($title, $sid, $id);

	ob_start();
	  $returnval .= the_ratings("div",$newPostID);
	  $out1 = ob_get_contents();
        ob_end_clean();
        if (!strpos($out1,"onmouseout")>0) $out1 = "already rated";
        $out1 = str_replace("(No Ratings Yet)","",$out1);

        # only scanning person should be allowed to rate here!
        if ($show_public_profile != 1)
	$article_created = $txt_subscr_pleaserate.": <br><nobr>$out1</nobr>";

}

if ( ($usbid != "") && ($scantype != "mainboard") ){

	$newPostID = lhg_create_usb_article($title, $sid, $usbid);

	ob_start();
	  $returnval .= the_ratings("div",$newPostID);
	  $out1 = ob_get_contents();
        ob_end_clean();
        if (!strpos($out1,"onmouseout")>0) $out1 = "already rated";
        $out1 = str_replace("(No Ratings Yet)","",$out1);

        # only scanning person should be allowed to rate here!
        if ($show_public_profile != 1)
        $article_created = $txt_subscr_pleaserate.": <br><nobr>$out1</nobr>";

}

} // end of autocreate skipping in debug mode



                        $css_id = "col-hw";
                        if ($show_public_profile == 1) $css_id = "col-hw-pub";



                        echo "
                        <tr>
                        <td id=\"".$css_id."\">

                        $logo


                        ";

                $otitle = $title;
                # CPUs
                if ($scantype == "cpu") {
                	$title = lhg_get_short_title( lhg_clean_cpu_title($otitle) );
                        $meta_info = lhg_get_title_metas( lhg_clean_cpu_title( $otitle ) );

                }

                # CPUs
                if ($scantype == "drive") {
                	$title = lhg_get_short_title( lhg_clean_drive_title($otitle) );
                        $meta_info = lhg_get_title_metas( lhg_clean_drive_title( $otitle ) );

                }

                # USB devices
                if ( ($usbid != "") && ($scantype != "mainboard") ) {
                	$title = lhg_get_short_title( lhg_clean_usb_title($otitle) );
                        $meta_info = lhg_get_title_metas( lhg_clean_usb_title( $otitle ) );
                        $scantype = "usb";
                }

                if ($title == "") $title = "(No identifier found)";

		#$title = "Scantype: $scantype".$title;
                print '<div class="subscribe-hwtext">';
                print '   <div class="subscribe-hwtext-span"><b>'.$title.'</b><span id="show-details-hw-'.$id.'"></span></div>';

                if ( ($scantype == "cpu") or ($scantype == "usb") or ($scantype == "drive") ) {
                	#print '   <div id="details-hw-'.$id.'" class="details">Full identifier: '.$otitle.'
                	print '
                        	<div id="details-hw-'.$id.'" class="details">
                    	    		<br>Properties: <span id="properties-'.$id.'">'.$meta_info.'</span>
                	    		<br>Title: <span id="title-'.$id.'">'.$title_info.'</span>
		        	</div>';
                }

                print '</div>';

if ($show_public_profile != 1){
	print '<form action="?" method="post" class="hwcomments">
       <div id="updatearea-'.$id.'">';

       # Help us adding this hardware to our database. Please identify this hardware and describe its Linux compatibility:
	print $txt_subscr_help;

	print '<br>
       <textarea id="comment-'.$id.'" name="comment-'.$id.'" cols="10" rows="3">'.lhg_clean_scan_comment($comment).'</textarea>';

       if ($editmode == 1) {
        #processingoptions for user comments
       	 print '<div class="scan-commentoptions"><a href="#" id="transform-to-comment-'.$id.'" name="postid-'.$newPostID.'">transform to public comment</a><br>
         	<a href="#" id="add-comment-'.$id.'" name="postid-'.$newPostID.'">add comment to article</a></div>';
       }

       print '<br>';

       # If possible, please leave an URL to a web page where the hardware is described (e.g. manufacturer`s data sheet or Amazon.com page).<br>URL:
	print $txt_subscr_ifpossible;

	print '<input id="url-'.$id.'" name="url-'.$id.'" type="text" value="'.$url.'" size="40" maxlenght="290">
       <input id="postid-'.$id.'" name="postid-'.$id.'" type="hidden" value="'.$newPostID.'">
       </div>
       <br><input type="submit" name="scan-comments-'.$id.'" id="scan-comments-'.$id.'" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />
	</form>';
 }


print '
</td><td>
'.$article_created.'
                        </td>';

                        //<td id=\"col4\">
                        //<span class='subscribe-column-2'>$deact<br>({$a_subscription->status})</span>
                        //</td>


                        // --- show HW Type info
                        #$postid = $a_subscription->post_id;
                        #$userid = $UID;
                        #$ratingID = lhg_get_rating_id_by_user_and_post ($userid, $postid);
                        //echo "PID: $postid, UID: $userid, RID: "; print_r($ratingID);
                        #$myrating = lhg_get_rating_by_rating_id ( $ratingID );
                        #echo "ST: $scantype ($id)<br>";
                        if ( ($usbid != "") && ($scantype != "mainboard") ) {
                        echo "
                        <td id=\"col4\">
                          <span class='subscribe-column-2'>
                            USB Device: $usbid";

                            if ( current_user_can('publish_posts') && ($show_public_profile != 1) ) {
                                print '<br><a href="/wp-admin/post.php?post='.$newPostID.'&action=edit&scansid='.$sid.'">finalize article</a>';
                                print '<br>Rating: '.lhg_get_rating_value($newPostID);
	                    }
                        echo "
                          </span>
                        </td>";
	                    }

                        if ($pciid != "")
                        echo "
                        <td id=\"col4\">
                          <span class='subscribe-column-2'>
                            PCI Device: $pciid
                          </span>
                        </td>";

                        if ($scantype == "cpu"){

                        echo "
                        <td id=\"col4\">
                          <span class='subscribe-column-2'>
                            CPU";

                            if ( current_user_can('publish_posts') && ($show_public_profile != 1) ) {
                                print '<br><a href="/wp-admin/post.php?post='.$newPostID.'&action=edit&scansid='.$sid.'">finalize article</a>';
                                print '<br><a href="./" id="finder-'.$id.'" name="pid-'.$newPostID.'">Amazon finder</a>';
                                print '<br>Rating: '.lhg_get_rating_value($newPostID);
	                    }
                         echo "
                          </span>
                        </td>";



                        }

                        if ($scantype == "drive") {
                          echo "
                          <td id=\"col4\">
                            <span class='subscribe-column-2'>
                              Drive";

                              if  ( current_user_can('publish_posts') && ($show_public_profile != 1) ) {
                                print '<br><a href="/wp-admin/post.php?post='.$newPostID.'&action=edit&scansid='.$sid.'">finalize article</a>';
                                print '<br><a href="./" id="finder-'.$id.'" name="pid-'.$newPostID.'">Amazon finder</a>';
                                print '<br>Rating: '.lhg_get_rating_value($newPostID);
	                      }

                          echo "
                            </span>
                          </td>";
	                }

                        #if ($myrating == "n.a.")
                        #echo "
                        #<td id=\"col4\">
                        #<span class='subscribe-column-2'>".$txt_subscr_notrated."</span>
                        #</td>";



			#echo "
                        #<td id=\"col4\">
                        #<span class='subscribe-column-2'>$deact</span>
                        #</td>";

                        #$registration_date=$a_subscription->dt;
                        #list ($registration_date, $registration_time) = explode(" ",$registration_date);
                        #echo "
                        #<td id=\"col2\">
                        #<span class='subscribe-column-1'><center>$registration_date</center></span>
                        #</td>



                        echo "</tr>\n";
		}
		} // Skip if not to be shown component

                echo "</table>";


                #var_dump ($mainboard_array);

}

		//echo '</ul>';
#		echo '<p id="subscribe-reloaded-select-all-p"><a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=true}else{for(var i=0;i<c;i++){t[i].checked=true}};return false;">'.__($txt_subscr_select_all,'subscribe-reloaded').'</a> ';



#		echo '<a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=!t.checked}else{for(var i=0;i<c;i++){t[i].checked=false}};return false;">'.__($txt_subscr_select_inv,'subscribe-reloaded').'</a></p>';
#
#
#		echo '<table id="subscribe-actions-table"><tr><td id="subscribe-actions" ><p id="subscribe-reloaded-action-p">'.__($txt_subscr_action.':<br>','subscribe-reloaded').'
#			</td><td id="subscribe-actions">
#                        <div class="sub-selector">
#                          <input type="radio" name="sra" value="delete" id="action_type_delete" />
#                            <label id="registration2" for="action_type_delete">'.__($txt_subscr_delete,'subscribe-reloaded').'</label>
#                          </div>
#			</td>
#
#                        <td id="subscribe-actions"><div class="sub-selector">
#                          <input type="radio" name="sra" value="suspend" id="action_type_suspend"  />
#                          <label id="registration2" for="action_type_suspend">'.__($txt_subscr_suspend,'subscribe-reloaded').'</label> </div>
#                        </td>
#
#                        <!-- td id="subscribe-actions"><div class="sub-selector">
#                          <input type="radio" name="sra" value="force_r" id="action_type_force_y" />
#                          <label id="registration2" for="action_type_force_y">'.__($txt_subscr_reply_only,'subscribe-reloaded').'</label> </div>
#                        </td -->
#
#                        <td id="subscribe-actions"><div class="sub-selector">
#                          <input type="radio" name="sra" value="activate" id="action_type_activate" checked="checked" />
#                          <label id="registration2" for="action_type_activate">'.__($txt_subscr_activate,'subscribe-reloaded').'</label> </div>
#                        </td>
#
#                      </tr></table>
#                      </p>';

#		echo '<p id="subscribe-reloaded-update-p"><button type="submit" class="subscribe-form-button" value="'.__($txt_subscr_update,'subscribe-reloaded').'" />'.__($txt_subscr_update,'subscribe-reloaded').'&nbsp;<i class="icon-arrow-right icon-button"></i></button><input type="hidden" name="sre" value="'.urlencode($email).'"/></p>';

	#}
	#else{
	#	echo '<p>'.__('No subscriptions match your search criteria.', 'subscribe-reloaded').'</p>';
	#}
#</fieldset>
#</form>


# Scan date not needed. Already listed in overview box at top
#$scandate = lhg_get_hwscandate($sid);
#$scandate = gmdate("Y-m-d\TH:i:s\Z", $scandate);
# This scan was performed at
#print "<br>".$txt_subscr_thisscan.": ".$scandate;

#if ( ($editmode == "") or ($editmode != 1) ) {
#        # nothing to show for editors
#}else{

if  ($uploadermode == 1) {
        # not public, not in edit mode

	# Thank you for using...
	global $txt_subscr_thankyou;
	print $txt_subscr_thankyou;

	#Please note that this web service is still under development. All your scan results were successfully transferred to the Linux-Hardware-Guide team.
	#However, the automatic recognition of hardware and its representation on this scan overview page for sure is still incomplete.
	print "<br>".$txt_subscr_notice;

	#print "<p>This tool is currently limited to following hardware components:";
	#print "<ul><li>USB devices";
	#print "<li>PCI devices";
	#print "<li>Mainboards (experimental)";
	#print "<li>Laptops (experimental)";
	#print "<li>CPUs";
	#print "<li>Storage media (HDD, CD, DVD, SSD)";
	#print "</ul>";
	print $txt_subscr_limitation;
}



function lhg_get_hwscandate( $sid ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("SELECT scandate FROM `lhgscansessions` WHERE sid = %s ", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$scandate = $lhg_price_db->get_var($myquery);
        return $scandate;
}

function lhg_get_hwscanmail( $sid ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("SELECT email FROM `lhgscansessions` WHERE sid = %s ", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$scandate = $lhg_price_db->get_var($myquery);
        return $scandate;
}

function lhg_get_imgurl( $sid, $id ) {
        //get amazon Image URL if it was set earlier (e.g. by Ajax)

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("SELECT imgurl FROM `lhghwscans` WHERE sid = %s AND id = %s", $sid, $id);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$scandate = $lhg_price_db->get_var($myquery);
        //print "DEBUG: $sid - $id - $scandate <br>";
        return $scandate;
}

function lhg_get_usercomment( $sid ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("SELECT usercomment FROM `lhgscansessions` WHERE sid = %s ", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$usercomment = $lhg_price_db->get_var($myquery);
        return $usercomment;
}

function lhg_get_mb_usercomment( $sid ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("SELECT mb_usercomment FROM `lhgscansessions` WHERE sid = %s ", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$usercomment = $lhg_price_db->get_var($myquery);
        return $usercomment;
}

function lhg_get_mb_url( $sid ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("SELECT mb_url FROM `lhgscansessions` WHERE sid = %s ", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$usercomment = $lhg_price_db->get_var($myquery);
        return $usercomment;
}

function lhg_get_usercomment_multi( $sid, $id ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("SELECT usercomment FROM `lhghwscans` WHERE sid = %s AND id = %s ", $sid, $id);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$usercomment = $lhg_price_db->get_var($myquery);
        return $usercomment;
}

function lhg_update_usercomment( $sid, $usercomment ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET usercomment = %s WHERE sid = %s ", $usercomment, $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->query($myquery);
}

function lhg_update_mb_usercomment( $sid, $mb_usercomment ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET mb_usercomment = %s WHERE sid = %s ", $mb_usercomment, $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->query($myquery);
}

function lhg_update_mb_url( $sid, $mb_url ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET mb_url = %s WHERE sid = %s ", $mb_url, $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->query($myquery);
}

function lhg_create_feedbackcomment( $sid, $comment_text, $uid ) {
        global $lhg_price_db;

        # routine called twice, once empty -> ignore
        if ($sid == "") return;


        # 1. store feedback exchange (user <--> LHG-Team) in DB
	$myquery = $lhg_price_db->prepare("INSERT INTO `lhgscan_comments` (comment_text, comment_date, scanid, user)
        							VALUES  (%s, %s, %s, %s)", $comment_text, time(), $sid, $uid);
	$result = $lhg_price_db->query($myquery);


        # get list of users that need to be informed
        $myquery = $lhg_price_db->prepare("SELECT DISTINCT(user) FROM `lhgscan_comments` WHERE scanid = %s", $sid);
	$userarray = $lhg_price_db->get_results($myquery);
        //$userarray = $userarray_tmp[0]->user;
        //var_dump($userarray); die();

        # send mail
        # if first comment:
	$myquery = $lhg_price_db->prepare("SELECT COUNT(id) FROM `lhgscan_comments` WHERE scanid = %s", $sid);
	$counter = $lhg_price_db->get_var($myquery);

        #userid of sumbmitter
	$myquery = $lhg_price_db->prepare("SELECT wp_uid FROM `lhgscansessions` WHERE sid = %s", $sid);
	$userid_submitter = $lhg_price_db->get_var($myquery);


        #print "<br>DEBUG: Number of comments:".$counter;

        #var_dump($counter);

	$email = lhg_get_hwscanmail($sid);
        if ( ( $counter == 1) && ($email != "") ) {
                # very first comment - normally comes from LHG Team
	        $subject = "LHG Hardware Scan - Open Questions";
        	$message = 'Hello,

Thank you for uploading your hardware data to the Linux-Hardware-Guide.
Your scan data was analyzed by our team. However, it seems there are still some questions before we can add all of your components to the database:

Request:
------------------------------------

'.$comment_text.'

------------------------------------
To answer to this request, please visit: http://www.linux-hardware-guide.com/hardware-profile/scan-'.$sid.'

Best regards,
your Linux-Hardware-Guide Team
';

        wp_mail( $email, $subject, $message );
	}

	$user_info = get_userdata($uid);
        $author_displayname = $user_info->display_name;



        if ( ( $counter > 1) ) {

                foreach ($userarray as $user) {
                        $userid = $user->user;
                        if ($userid != 0) {
                                $user_info = get_userdata($userid);
                        	$email = $user_info->user_email;
                        	$to_displayname = $user_info->display_name;
			} else {
				$email = lhg_get_hwscanmail($sid);
                                $to_displayname = "";
                        }


        	        # follow-up comment - coming from LHG-Team
	        	$subject = "LHG Hardware Scan - Open Questions";


                        # do not send notification to author
                        if ($userid != $uid ) {

                        # message by LHG registered user for an anonymous user

                        	if ( ($userid == 0) or ($userid == $userid_submitter) ) {
                                	#this seems to be the submitter of the scan
	                                $scanedit = "";
        	                        $scanprop = "your";
                	        } else {
                        	        # modify url
                                	$scanedit = "edit";
	                                $scanprop = "a";
                	        }

	                        if ($author_displayname != "") $by_text = "by ".$author_displayname." ";
        	                $to_text = "";
                	        if ($to_displayname != "") $to_text = " ".$to_displayname;

                        $message = 'Hello'.$to_text.',

Following new comment was left '.$by_text.'regarding '.$scanprop.' hardware scan:
------------------------------------

'.$comment_text.'

------------------------------------
To answer to this comment, please visit: http://www.linux-hardware-guide.com/hardware-profile/'.$scanedit.'scan-'.$sid.'

Best regards,
your Linux-Hardware-Guide Team
';

                        	error_log("send email UID: $userid em: $email");
        			if ($email != "") wp_mail( $email, $subject, $message );
			}

		}
	}

/*
if ( ($uid != 1 ) ) {
                # any reply from user sent to webmaster
                $lhg_email = "webmaster@linux-hardware-guide.com";
	        $subject = "LHG Hardware Scan - Open Questions";
        	$message = 'Hello,

Automated message from scan '.$sid.':
------------------------------------

'.$comment_text.'

------------------------------------
To answer to this request, please visit: http://www.linux-hardware-guide.com/hardware-profile/scan-'.$sid.'
';

        wp_mail( $lhg_email, $subject, $message );
	}
*/

}

function lhg_update_usercomment_multi( $sid, $usercomment, $id ) {
        #print "UPDATE: $usercomment, $id";

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET usercomment = %s WHERE id = %s ", $usercomment, $id);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->query($myquery);
}


function lhg_update_mail( $sid, $email ) {

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET email = %s WHERE sid = %s ", $email, $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->query($myquery);

        $to = "webmaster@linux-hardware-guide.com";
        $subject = "LHG Hardware Scan";
        $message = 'A hardware scan was performed and the user left a contact address.
ScanID: '.$sid.'
email: '.$email.'

Please visit: http://www.linux-hardware-guide.com/hardware-profile/scan-'.$sid.'

';

        wp_mail( $to, $subject, $message );

}


function lhg_scan_updateid( $sid, $cid ) {

        global $lhg_price_db;

        $comment = $_POST["comment-".$cid];
        $url     = $_POST["url-".$cid];

        #print "cid: $cid <br>";
        #print "New comment: $comment <br>";
        #print "New url: $url <br>";

	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET usercomment = %s WHERE id = %s ", $comment, $cid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->query($myquery);

	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET url = %s WHERE id = %s ", $url, $cid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$result = $lhg_price_db->query($myquery);

}

function lhg_scan_check_mail( $sid ) {

        $db_email = lhg_get_hwscanmail ($sid);
        if ($db_email != $_POST["email"]) lhg_update_mail( $sid, $_POST["email"] );

}


function lhg_scan_check_changes( $sid ) {

        #ä user submitted something
        #print "Changes identified!<br>";
        #var_dump($_POST);


        # check which id number he submitted
	$keys = array_keys($_POST);
	#echo $keys[0];

        if ( substr($keys[0], 0, 8) == "comment-" ) {

             $commentid = substr($keys[0], 8);
             #print "ID: $commentid";
             lhg_scan_updateid( $sid , $commentid );

	}

        if ( substr($keys[0], 0, 20) == "usercomment-feedback" ) {
             # feedback was provided. Write to DB
             $comment_text = $_POST["usercomment-feedback"];
             $uid = get_current_user_id();
	     if ($_POST["usercomment-feedback"] != "") lhg_create_feedbackcomment( $sid, $comment_text, $uid );

	}

        #print substr($keys[0], 0, 17);
        #var_dump($keys);

      if ( substr($keys[0], 0, 17) == "usercomment_multi" ) {

             $commentid = substr($keys[0], 18);
             #print "ID multi: $commentid - Text:".$_POST["usercomment_multi_".$commentid];
             lhg_update_usercomment_multi( $sid , $_POST["usercomment_multi_".$commentid], $commentid );

	}


        print "<br>";

	#check if values are updated
	if ($_POST["email"] != "") lhg_scan_check_mail( $sid );

        $db_email = lhg_get_hwscanmail ($sid);
        if (( $db_email != $_POST["email"]) and ($_POST["email"] != "") ) lhg_update_mail( $sid, $_POST["email"] );

        $db_usercomment = lhg_get_usercomment ($sid);
        if ( ($db_usercomment != $_POST["usercomment"]) and ($_POST["usercomment"] != "") ) lhg_update_usercomment( $sid, $_POST["usercomment"] );

        $db_mb_usercomment = lhg_get_mb_usercomment ($sid);
        if ( ($db_mb_usercomment != $_POST["mb-usercomment"]) and ($_POST["mb-usercomment"] != "") ) lhg_update_mb_usercomment( $sid, $_POST["mb-usercomment"] );

        $db_url_mb = lhg_get_mb_url ($sid);
        if ( ($db_url_mb != $_POST["url-mb"]) and ($_POST["url-mb"] != "") ) lhg_update_mb_url( $sid, $_POST["url-mb"] );

        #$db_usercomment_multi = lhg_get_usercomment_multi ($sid);
        #if ( ($db_usercomment != $_POST["usercomment"]) and ($_POST["usercomment"] != "") ) lhg_update_usercomment( $sid, $_POST["usercomment"] );

}

function lhg_add_hwbutton ( $email, $pid ) {

#check if already added

#ToDo !!

#show button

$button =  '<form action="/hardware-profile?srp='.$pid.'&#038;sra=s" method="post" onsubmit="if(this.sre.value==\'\' || this.sre.indexOf(\'@\')==0) return false" target="_blank">
  	                 <fieldset style="border:0">
  	                 <input type="hidden" class="subscribe-form-field-hwlist" name="sre" value="'.$email.'" size="18"  />
  	                 <button type="submit" value="Add" class="hwlist-add-button" />Add&nbsp;<i class="icon-arrow-right icon-button"></i></button></p>
  	                 </fieldset>
  	                 </form>';

return $button;
}


function lhg_feedback_area ( $sid  ) {
#
### Allow communication with scan submitter
#
        #global $lhg_price_db;

	#$myquery = $lhg_price_db->prepare("SELECT COUNT(id) FROM `lhgscan_comments` WHERE scanid = %s", $sid);
	#$counter = $lhg_price_db->get_var($myquery);

        #print "<br>DEBUG: Number of comments:".$counter;


# 1. Check if comments already exist
        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("SELECT comment_text, comment_date, user, commenttype FROM `lhgscan_comments` WHERE scanid = %s ", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$results = $lhg_price_db->get_results($myquery);
	$error = $lhg_price_db->last_error;
	if ($error != "") var_dump($error);

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("SELECT email FROM `lhgscansessions` WHERE sid = %s ", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$email = $lhg_price_db->get_var($myquery);
	$error = $lhg_price_db->last_error;
	if ($error != "") var_dump($error);

        #var_dump($results);

# 2. if exist show old comments

        if ( !empty($results) ) {
                #print "<br>Comments found -> <br>";

                foreach ( $results as $result ) {

                        # get user infos
                        $uid = $result->user;
                        $userinfo = get_userdata($uid);
                        $uname = $userinfo->user_login;
                        if ($show_public_profile != 1) if ( ($uname == "") && ($email != "") ) list( $uname, $domain) = explode("@",$email);

                        if ($uname == "") $uname = "Anonymous";
                        if ($uname == "admin") $uname = "LHG-Team";
                        $comment = $result->comment_text;
                        $comment = lhg_clean_scan_comment($comment);
                        $date = date("jS \of F Y h:i:s A",$result->comment_date);


                        print '<div class="scancomment-outer">';


                      //print '<div class="user-feedback">';
                      if ( $result->commenttype == "status_change") {
                              # this is not a real comment but a status change
                                $status_change = explode(" -> ",$result->comment_text);
                                $status_old = lhg_status_text($status_change[0]);
                                $status_new = lhg_status_text($status_change[1]);


	                        print '<div class="scancomment-userinfo-left">&nbsp;</div>';

                                print '<div class="scancomment-bubblecontainer">';
        		        print '<div class="scancomment-statuschange">'."Scan status was changed at ".$date." by <b>".$uname."</b> from ".$status_old.' to '.$status_new."</div>";
	                        print "</div>"; // scancomment-outer

                                print '<div class="scancomment-userinfo-right">&nbsp;<!-- placeholder --></div>';


                      } else {

                        if ($uid == get_current_user_id() ) {
	                        print '<div class="scancomment-userinfo-left">
                                	  <div class="scancomment-userinfo-image">'.get_avatar( $uid, 60).'<br>
                                          <div class="scancomment-userinfo-name">'.$uname.'
                                          </div></div>
                                	</div>';

                                print '<div class="scancomment-bubblecontainer">';
                        	print '  <div class="scan-bubble-left">';

			        print '<div class="bubbletext">';
        		        print '   <span class="scancomment-intro-text">'."<b>".$uname."</b> wrote at ".$date.' the following comment:</span><br> '.$comment;
	                        print "</div>"; // scancomment-outer

                                print "  </div>"; //bubbletext
                        	print "</div>"; //bubble (left/right)
                                print '<div class="scancomment-userinfo-right">&nbsp;<!-- placeholder --></div>';

			} else {
	                        print '<div class="scancomment-userinfo-left">&nbsp;<!-- placeholder --></div>';

                                print '<div class="scancomment-bubblecontainer">';
        	        	print '<div class="scan-bubble-right">';
				print '  <div class="bubbletext">';
        		        print '   <span class="scancomment-intro-text">'."<b>".$uname."</b> wrote at ".$date.' the following comment:</span><br> '.$comment;
	        	        print "  </div>"; // scancomment-outer
	                        print "</div>"; //bubbletext
        	                print "</div>"; //bubble (left/right)

                                print '<div class="scancomment-userinfo-right">
                                	 <div class="scancomment-userinfo-image-right">'.get_avatar( $uid, 60).'<br>
                                            <div class="scancomment-userinfo-name">'.$uname.'
                                            </div>
                                         </div>
                                       </div>';

			}
		      }
                        //print "<b>".$uname."</b> at ".$date.':<br> <div class="user-feedback-text">'.$result->comment_text."</div></div>";
                        print '</div>';
                        print '<br clear="all">';
		}
	}


# 3. show field for new comment

        #only if not in public mode
        global $show_public_profile;
        global $editmode;
        if ($show_public_profile != 1) {

        $rand=rand(1,9999); # prevent browser caching... ugly
        # admin can always post
        #error_log("em: $editmode");
        if ( $editmode == 1 ) {

                # check if user data is available
	        global $lhg_price_db;
        	$sql = "SELECT wp_uid FROM `lhgscansessions` WHERE sid = %s";
	  	$safe_sql = $lhg_price_db->prepare($sql, $sid);
  		$wp_uid = $lhg_price_db->get_var($safe_sql);

        	$sql = "SELECT wp_uid_de FROM `lhgscansessions` WHERE sid = %s";
	  	$safe_sql = $lhg_price_db->prepare($sql, $sid);
  		$wp_uid_de = $lhg_price_db->get_var($safe_sql);

        	$sql = "SELECT email FROM `lhgscansessions` WHERE sid = %s";
	  	$safe_sql = $lhg_price_db->prepare($sql, $sid);
  		$email = $lhg_price_db->get_var($safe_sql);

	        if ( ($wp_uid > 0) ) $userdata = "UID (com): $wp_uid ";
        	if ( ($wp_uid_de > 0) ) $userdata .= "UID (de): $wp_uid_de ";
        	if ( ($email != "") ) $userdata .= "email: $email ";

                if ( $userdata != "") $userdata = '<span class="scan-userdata-status">'.$userdata.'</span>';
        	if ( ($wp_uid == 0) && ($wp_uid_de == 0) && ($email == "") ) $userdata_txt = '<span class="scan-userdata-status">(No user data available, i.e. user cannot be informed about question by mail)</span>';

                # show userinfo of current user
                $cuid = get_current_user_id();
                $userinfo = get_userdata($cuid);
                $uname = $userinfo->user_login;
                if ($show_public_profile != 1) if ( ($uname == "") && ($email != "") ) {
                        list( $uname, $domain) = explode("@",$email);
		}
                if ($uname == "") $uname = "Anonymous";
                if ($uname == "admin") $uname = "LHG-Team";

                print '<div class="scancomment-outer">';
	        print '  <div class="scancomment-userinfo-left">
                	    <div class="scancomment-userinfo-image">'.get_avatar( $cuid , 60).'<br>
                               <div class="scancomment-userinfo-name">'.$uname.'
                               </div>
                            </div>
                         </div>';

                print '  <div class="scancomment-bubblecontainer">';
                print '    <div class="scan-bubble-left">';

		print '      <div class="bubbletext">';

		echo '
                <form action="?'.$rand.'" method="post" class="scan-feedback">
      		Ask additional questions to scan submitter:<br>
	        <textarea id="known-hardware-userfeedback" name="usercomment-feedback" class="usercomment-feedback" cols="10" rows="3"></textarea><br>
       	        <input type="submit" id="scan-comment" value="Submit" name="button-usercomment-feedback" class="hwscan-comment-button-green" />'.$userdata_txt.$userdata.'
	        </form><br>';


                print "      </div>"; //bubbletext
                print "    </div>"; //bubble (left/right)
                print '    <div class="scancomment-userinfo-right">&nbsp;<!-- placeholder --></div>';
                print '  </div>';
                print '</div>';


	}

        # reply possible?
        if ( !empty($results) && !current_user_can ('edit_posts') ) {

                # show userinfo of current user
                $cuid = get_current_user_id();
                $userinfo = get_userdata($cuid);
                $uname = $userinfo->user_login;
                if ($show_public_profile != 1) if ( ($uname == "") && ($email != "") ) list( $uname, $domain) = explode("@",$email);
                if ($uname == "") $uname = "Anonymous";
                if ($uname == "admin") $uname = "LHG-Team";

                print '<div class="scancomment-outer">';
	        print '  <div class="scancomment-userinfo-left">
                	    <div class="scancomment-userinfo-image">'.get_avatar( $cuid , 60).'<br>
                               <div class="scancomment-userinfo-name">'.$uname.'
                               </div>
                            </div>
                         </div>';

                print '  <div class="scancomment-bubblecontainer">';
                print '    <div class="scan-bubble-left">';

		print '      <div class="bubbletext">';


	echo ' <form action="?'.$rand.'" method="post" class="scan-feedback">
      		Reply to comment:<br>
	       <textarea id="known-hardware-userfeedback" name="usercomment-feedback" class="usercomment-feedback" cols="10" rows="3"></textarea><br>
       	       <input type="submit" id="scan-comment" value="Reply" name="button-usercomment-feedback" class="hwscan-comment-button-green" />
	       </form><br>';


                print "      </div>"; //bubbletext
                print "    </div>"; //bubble (left/right)
                print '    <div class="scancomment-userinfo-right">&nbsp;<!-- placeholder --></div>';
                print '  </div>';
                print '</div>';

	}
	}
}

#         <textarea id="known-hardware-usercomment" name="usercomment" cols="10" rows="3">'.$usercomment.'</textarea><br>
#       <input type="submit" id="known-hardware-submit" name="email-login" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />



$output = ob_get_contents();
ob_end_clean();
return $output;

function lhg_status_text( $status ) {

	if ( $status == "complete") {
        	$status_text = '<span class="scanstatus-text-complete">complete</span>';
	}elseif ( $status == "ongoing") {
		$status_text = '<span class="scanstatus-text-ongoing">ongoing</span>';
	}elseif ( $status == "new") {
		$status_text = '<span class="scanstatus-text-new">new</span>';
	}else {
                $status_text = $status;
        }
        return $status_text;

}


function lhg_show_scanned_component( $title, $id, $pciid ) {

        $showme = true; # default is to show entry

        #print "P0: ".strpos($title,"SATA controller");
        #print "P0: ".strpos($title,"PCI to PCI bridge");
        #strpos($title,"PCI bridge[");

        // Hiding only allowed for onboard components!
        if (is_int(strpos($title,"PCI to PCI bridge") ) ) $showme = false;
        if (is_int(strpos($title,"ATA controller ") ) )	  $showme = false;
        if (is_int(strpos($title,"ISA bridge ") ) )	  $showme = false;
        if (is_int(strpos($title,"Host bridge ") ) )	  $showme = false;
        if (is_int(strpos($title,"PCI bridge [") ) )	  $showme = false;
        if (is_int(strpos($title,"SMBus [") ) )	  	  $showme = false;
        if (is_int(strpos($title,"Communication controller") ) )	$showme = false;


        # some additional rules for laptops
        $prob_laptop = lhg_scan_is_laptop( $sid );
        if (is_int(strpos($title,"USB controller [") ) )  $showme = false;

        # show if comment available

        # show if on-board = "no" in DB
        #print "shwome: $showme - $title<br>";
        return $showme;

}

function lhg_pci_component_is_onboard ( $title, $sid, $id, $pciid  ) {

        # ToDo: default = onboard (should be smarter)
        $onboardstatus = "yes";

        # not shown components are onboard
        $showme = lhg_show_scanned_component( $title, $id, $pciid);
        if ($showme == false) $onboardstatus = "yes";

        # check DB if something was defined by user
        global $lhg_price_db;
        $sql = "SELECT onboard FROM `lhghwscans` WHERE id = %s";
  	$safe_sql = $lhg_price_db->prepare($sql, $id);
  	$DB_onboard = $lhg_price_db->get_var($safe_sql);

        #print "DB: $id - $DB_onboard<br>";

        $prob_laptop = lhg_scan_is_laptop( $sid );

        if ($DB_onboard == "yes") {$onboardstatus = "yes"; return $onboardstatus; }
        if ($DB_onboard == "no") {$onboardstatus = "no"; return $onboardstatus; }

        if (is_int(strpos($title,"USB controller [") ) ) $onboardstatus = "no";
        if (is_int(strpos($title,"VGA compatible controller [") ) ) $onboardstatus = "no";
        if (is_int(strpos($title,"Communication controller") ) ) $onboardstatus = "no";
        if (is_int(strpos($title,"Intel Corporation") ) && is_int(strpos($title,"Graphics Controller") ) ) $onboardstatus = "yes";
        if (is_int(strpos($title,"Intel Corporation") ) && is_int(strpos($title,"High Definition Audio") ) ) $onboardstatus = "yes";
        if (is_int(strpos($title,"Intel Corporation") ) && is_int(strpos($title,"SATA Controller") ) ) $onboardstatus = "yes";

        if ($prob_laptop > 0.8) {
                # looks like a laptop. All PCI components onboard
	        #if (is_int(strpos($title,"USB controller [") ) )            $onboardstatus = "yes";
        	#if (is_int(strpos($title,"VGA compatible controller [") ) ) $onboardstatus = "yes";
	        #if (is_int(strpos($title,"Audio device [") ) )              $onboardstatus = "yes";
        	#if (is_int(strpos($title,"Ethernet controller [") ) )       $onboardstatus = "yes";
        	#if (is_int(strpos($title,"Network controller [") ) )        $onboardstatus = "yes";
                $onboardstatus = "yes";
        }

        #print "PL: $prob_laptop - ";
        if ($prob_laptop < 0.1) {
        	if (is_int(strpos($title,"IDE interface") ) && is_int(strpos($title,"Intel Corporation") )) $onboardstatus = "yes";
        	if (is_int(strpos($title,"Bridge ") ) && is_int(strpos($title,"Intel Corporation") )) $onboardstatus = "yes";
        	if (is_int(strpos($title,"USB controller ") ) && is_int(strpos($title,"Intel Corporation") )) $onboardstatus = "yes";
                #print "11<br>";
        }

  	return $onboardstatus;
 
}

function lhg_scan_is_laptop ( $sid ) {
        # returns probability (0 ... 1) that this is a laptop
        $probability = 0;

  	# get mainboard name
        global $lhg_price_db;
        $sql = "SELECT laptop_probability FROM `lhgscansessions` WHERE sid = %s";
  	$safe_sql = $lhg_price_db->prepare($sql, $sid);
  	$probability = $lhg_price_db->get_var($safe_sql);

        if ($probability == "") $probability = 0;

        #print "Prob: $probability<br>";

        return $probability;

}


function lhg_get_mainboard_name ( $sid  ) {
        #print "SID: $sid<br>";
	$url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=dmesg.txt";
        #print "URL: $url<br>";
	#extract relevant dmesg output

	ini_set('default_socket_timeout', 5);
  	$content = file_get_contents($url);

  	$dmesg = explode("\n",$content);
        #var_dump($dmesg);
        #$find = array_keys( $dmesg, $title);

	$keyword = "DMI: ";
  	foreach($dmesg as $key => $arrayItem){
        	if( stristr( $arrayItem, $keyword ) ){
            		#print "Key: $key - ".$dmesg[$key]."<br>";
            		$foundkey = $key;
            		break;
        	}
  	}
  	list( $null, $mbstring ) = explode("DMI: ",$dmesg[$foundkey]);

        #print "AA: $mbstring -- <br>";

        #
        # Maybe a Thinkpad or Zenbook?
        #
        if ( (is_int(strpos($mbstring,"LENOVO") ) ) or
             (is_int(strpos($mbstring,"Lenovo") ) ) or
             (is_int(strpos($mbstring,"ASUSTeK") ) )

           ){

	        $keyword = "ACPI: DMI detected:";
        	#$find = array_keys( $dmesg, $title);
	  	foreach($dmesg as $key => $arrayItem){
        		if( stristr( $arrayItem, $keyword ) ){
            			#print "Key: $key - ".$dmesg[$key]."<br>";
            			$foundkey = $key;
            			break;
	        	}
  		}
                # found! merge after ID
                $TPid = strpos($dmesg[$foundkey],"ThinkPad");
                if ($TPid > 0) {
        	        $TPname = substr($dmesg[$foundkey], $TPid);
                	#print "k: $key fk: $foundkey id: $TPid - name: $TPname<br>";
                	list($name0, $name1) = explode(", ",$mbstring);
	                $mbstring = $name0." - ".$TPname.", ".$name1;
	  	}

                $TPid = strpos($dmesg[$foundkey],"ASUS Zenbook");
                if ($TPid > 0) {
        	        $TPname = substr($dmesg[$foundkey], $TPid);
                	#print "k: $key fk: $foundkey id: $TPid - name: $TPname<br>";
                	list($name0, $name1) = explode(", ",$mbstring);
	                $mbstring = $name0." - ".$TPname.", ".$name1;
	  	}

  	}

        if ($mbstring == "") $mbstring = lhg_get_mainboard_name_from_DMI( $dmesg );

        return $mbstring;
}

function lhg_update_mainboard_fingerprint ( $sid , $postid ) {
        $pcilist = lhg_get_mainboard_fingerprint( $sid );

        # ToDo: Write to DB!
        global $lhg_price_db;
        $sql = "UPDATE lhgtransverse_posts SET `pciids` = \"%s\" WHERE postid_com = %s";
	$safe_sql = $lhg_price_db->prepare($sql, $pcilist, $postid);
	$result = $lhg_price_db->query($safe_sql);

}

function lhg_get_mainboard_name_from_DMI ( $dmesg ) {

	$keyword = "Vendor: ";
        $dmesg_inv = array_reverse( $dmesg );
  	foreach($dmesg_inv as $key => $arrayItem){
        	if( stristr( $arrayItem, $keyword ) ){
            		#print "Key: $key - ".$dmesg[$key]."<br>";
            		$foundkey = $key;
                        break;
        	}
  	}
        list( $null, $mbstring_tmp ) = explode($keyword ,$dmesg_inv[$foundkey]);
        $mbstring .= $mbstring_tmp." ";


	#$keyword = "Product Name: ";
	$keyword = "Product Name: ";
  	foreach($dmesg as $key => $arrayItem){
        	if( stristr( $arrayItem, $keyword ) ){
            		#print "Key: $key - ".$dmesg[$key]."<br>";
            		$foundkey = $key;
                        list( $null, $mbstring_tmp ) = explode($keyword ,$dmesg[$foundkey]);
                        $mbstring .= $mbstring_tmp." ";
        	}
  	}
        #print "DMI String:";
        #var_dump($mbstring);
        #print "<br>";

        return $mbstring;
}

function lhg_check_if_recent_upload ( $sid ) {

        if ($sid == "") return;
        #error_log( "SID: $sid" );

	global $lhg_price_db;

        # look when scan was uploaded
        # scandate = 1 means that scan is still ongoing
        # scandate = 0 means not yet created in DB
        $myquery = $lhg_price_db->prepare("SELECT scandate FROM `lhgscansessions` WHERE sid = %s", $sid);
        $scandate = $lhg_price_db->get_var($myquery);

        # look if already someone logged in
        #$myquery = $lhg_price_db->prepare("SELECT date FROM `lhgscan_login` WHERE sid = %s", $sid);
        #$logindate = $lhg_price_db->get_var($myquery);

        $diff = time() - $scandate;

        if ( ($diff < 15) or ($scandate < 2) ) {

        # The data was uploaded less than 15 seconds ago
        # in order to keep the visitor informed that something is ongoing on server side
        # we show some progress bar and forward to the real page after 15 seconds

print '
<html>

<head>
	<meta charset=\'UTF-8\'>
        <meta http-equiv="refresh" content="15">
	
	<title>Progressing Data - Please wait</title>
	
	<link rel=\'stylesheet\' href=\'css/style.css\'>
	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
	<script>
		$(function() {
			$(".meter > span").each(function() {
				$(this)
					.data("origWidth", $(this).width())
					.width(0)
					.animate({
						width: $(this).data("origWidth")
					}, 15000);
			});
		});
	</script>
	
	<style>
		.meter { 
			height: 20px;  /* Can be anything */
			position: relative;
			margin: 5px 0 20px 0; /* Just for demo spacing */
			background: #555;
			-moz-border-radius: 25px;
			-webkit-border-radius: 25px;
			border-radius: 25px;
			padding: 10px;
			-webkit-box-shadow: inset 0 -1px 1px rgba(255,255,255,0.3);
			-moz-box-shadow   : inset 0 -1px 1px rgba(255,255,255,0.3);
			box-shadow        : inset 0 -1px 1px rgba(255,255,255,0.3);
                        width: 50%;
                        margin-left: auto;
                        margin-right: auto;
		}
		.meter > span {
			display: block;
			height: 100%;
			   -webkit-border-top-right-radius: 8px;
			-webkit-border-bottom-right-radius: 8px;
			       -moz-border-radius-topright: 8px;
			    -moz-border-radius-bottomright: 8px;
			           border-top-right-radius: 8px;
			        border-bottom-right-radius: 8px;
			    -webkit-border-top-left-radius: 20px;
			 -webkit-border-bottom-left-radius: 20px;
			        -moz-border-radius-topleft: 20px;
			     -moz-border-radius-bottomleft: 20px;
			            border-top-left-radius: 20px;
			         border-bottom-left-radius: 20px;
			background-color: rgb(43,194,83);
			background-image: -webkit-gradient(
			  linear,
			  left bottom,
			  left top,
			  color-stop(0, rgb(43,194,83)),
			  color-stop(1, rgb(84,240,84))
			 );
			background-image: -moz-linear-gradient(
			  center bottom,
			  rgb(43,194,83) 37%,
			  rgb(84,240,84) 69%
			 );
			-webkit-box-shadow: 
			  inset 0 2px 9px  rgba(255,255,255,0.3),
			  inset 0 -2px 6px rgba(0,0,0,0.4);
			-moz-box-shadow: 
			  inset 0 2px 9px  rgba(255,255,255,0.3),
			  inset 0 -2px 6px rgba(0,0,0,0.4);
			box-shadow: 
			  inset 0 2px 9px  rgba(255,255,255,0.3),
			  inset 0 -2px 6px rgba(0,0,0,0.4);
			position: relative;
			overflow: hidden;
		}
		.meter > span:after, .animate > span > span {
			content: "";
			position: absolute;
			top: 0; left: 0; bottom: 0; right: 0;
			background-image: 
			   -webkit-gradient(linear, 0 0, 100% 100%, 
			      color-stop(.25, rgba(255, 255, 255, .2)), 
			      color-stop(.25, transparent), color-stop(.5, transparent), 
			      color-stop(.5, rgba(255, 255, 255, .2)), 
			      color-stop(.75, rgba(255, 255, 255, .2)), 
			      color-stop(.75, transparent), to(transparent)
			   );
			background-image: 
				-moz-linear-gradient(
				  -45deg, 
			      rgba(255, 255, 255, .2) 25%, 
			      transparent 25%, 
			      transparent 50%, 
			      rgba(255, 255, 255, .2) 50%, 
			      rgba(255, 255, 255, .2) 75%, 
			      transparent 75%, 
			      transparent
			   );
			z-index: 1;
			-webkit-background-size: 50px 50px;
			-moz-background-size: 50px 50px;
			-webkit-animation: move 2s linear infinite;
			   -webkit-border-top-right-radius: 8px;
			-webkit-border-bottom-right-radius: 8px;
			       -moz-border-radius-topright: 8px;
			    -moz-border-radius-bottomright: 8px;
			           border-top-right-radius: 8px;
			        border-bottom-right-radius: 8px;
			    -webkit-border-top-left-radius: 20px;
			 -webkit-border-bottom-left-radius: 20px;
			        -moz-border-radius-topleft: 20px;
			     -moz-border-radius-bottomleft: 20px;
			            border-top-left-radius: 20px;
			         border-bottom-left-radius: 20px;
			overflow: hidden;
		}
		
		.animate > span:after {
			display: none;
		}
		
		@-webkit-keyframes move {
		    0% {
		       background-position: 0 0;
		    }
		    100% {
		       background-position: 50px 50px;
		    }
		}
		
		.orange > span {
			background-color: #f1a165;
			background-image: -moz-linear-gradient(top, #f1a165, #f36d0a);
			background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f1a165),color-stop(1, #f36d0a));
			background-image: -webkit-linear-gradient(#f1a165, #f36d0a); 
		}
		
		.red > span {
			background-color: #f0a3a3;
			background-image: -moz-linear-gradient(top, #f0a3a3, #f42323);
			background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f0a3a3),color-stop(1, #f42323));
			background-image: -webkit-linear-gradient(#f0a3a3, #f42323);
		}
		
		.nostripes > span > span, .nostripes > span:after {
			-webkit-animation: none;
			background-image: none;
		}
                .text {
                        margin-top: 20px;
                        text-align: center;
                }

	</style>
</head>

<body>

<div class="text">Please wait while the server is processing your data</div>

<div class="meter animate">
	<span style="width: 100%"><span></span></span>
</div>
</body>
</html>
';
                exit;
	}

        # no need to let the visitor wait
        return;
}


# The scan comments stored in the LHG DB are formatted for safety reasons. Therefore, some reverse formatting
# is needed before insterting them in the web page
function lhg_clean_scan_comment ( $comment ) {

#        $modified_comment = str_replace('\"',"&quot;",$comment);
#        $modified_comment = str_replace("\'","&#39;",$modified_comment);

        $modified_comment = str_replace('\"','"',$comment);
        $modified_comment = str_replace("\'","'",$modified_comment);

        return $modified_comment;

}


?>