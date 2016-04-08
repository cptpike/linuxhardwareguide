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


global $lhg_price_db;
if ( ($_SERVER['SERVER_ADDR'] == "192.168.56.12") or ($_SERVER['SERVER_ADDR'] == "192.168.56.13") )
$lhg_price_db = new wpdb("wordpress", "6DQ2O3PR", "lhgpricedb", "192.168.56.14");

if ( ($_SERVER['SERVER_ADDR'] == "192.168.3.112") or ($_SERVER['SERVER_ADDR'] == "192.168.3.113") )
$lhg_price_db = new wpdb("wordpress", "6DQ2O3PR", "lhgpricedb", "192.168.3.114");

// Avoid direct access to this piece of code
if (!function_exists('add_action')){
	header('Location: /');
	exit;
}

global $wp_subscribe_reloaded;


ob_start();

#if (!empty($_POST['post_list'])){
#	$post_list = array();
#	foreach($_POST['post_list'] as $a_post_id){
#		if (!in_array($a_post_id, $post_list))
#			$post_list[] = intval($a_post_id);
#	}
#
#	$action = !empty($_POST['sra'])?$_POST['sra']:(!empty($_GET['sra'])?$_GET['sra']:'');
#	switch($action){
#		case 'delete':
#			$rows_affected = $wp_subscribe_reloaded->delete_subscriptions($post_list, $email);
#			echo '<p class="updated">'.__('Subscriptions deleted:', 'subscribe-reloaded')." $rows_affected</p>";
#			break;
#		case 'suspend':
#			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email, 'C');
#			echo '<p class="updated">'.__('Subscriptions suspended:', 'subscribe-reloaded')." $rows_affected</p>";
#			break;
#		case 'activate':
#			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email, '-C');
#			echo '<p class="updated">'.__('Subscriptions activated:', 'subscribe-reloaded')." $rows_affected</p>";
#			break;
#		case 'force_r':
#			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email, 'R');
#			echo '<p class="updated">'.__('Subscriptions updated:', 'subscribe-reloaded')." $rows_affected</p>";
#			break;
#		default:
#			break;
#	}
#}


#$message = html_entity_decode(stripslashes(get_option('subscribe_reloaded_user_text')), ENT_COMPAT, 'UTF-8');
#if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'))
#	$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($message);
#echo "$message<br>


# Extract Session ID fron URL
$url     = ((empty($_SERVER['HTTPS'])) ? 'http' : 'https') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$pieces  = parse_url($url);
$urlpath = $pieces['path'];
$hwscanpos=strpos($urlpath,"/hardware-profile/scan-");
$sid = substr($urlpath,$hwscanpos+23);

# get scan id from public id
 if ($show_public_profile) {
       $hwscanpos=strpos($urlpath,"/hardware-profile/system-");
       $pub_id = substr($urlpath,$hwscanpos+25);

       $myquery = $lhg_price_db->prepare("SELECT sid FROM `lhgscansessions` WHERE pub_id = %s", $pub_id);
       $sid = $lhg_price_db->get_var($myquery);
 }


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

if ($show_public_profile != 1) print '<b>Thank you for using our Linux-Hardware-Guide scanning software</b> (see <a href="https://github.com/paralib5/lhg_scanner">GitHub</a> for more details).<br>
This way we can fill our Linux Hardware Database with valuable information for the Linux community.</b>';


# link to other scans

$myquery = $lhg_price_db->prepare("SELECT uid FROM `lhgscansessions` WHERE sid = %s", $sid);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
$uid = $lhg_price_db->get_var($myquery);
#var_dump( $uid );

$myquery = $lhg_price_db->prepare("SELECT COUNT(*) FROM `lhgscansessions` WHERE uid = %s", $uid);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
$num_uid = $lhg_price_db->get_var($myquery);

#print "<br>NUM:".$num_uid;

if ( ($uid != "") && ($num_uid > 1) && (strlen($uid)>5) ) {
	if ($show_public_profile != 1) print "<br>&nbsp;<br>See overview of the <a href=./uid-".$uid.">".$num_uid." hardware scans of this user</a>.";
	#var_dump( $uid );
}

$email = lhg_get_hwscanmail($sid);
if ($email != "") $userknown = 1;

$buttontext = "Submit";
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


if ($show_public_profile != 1) {
echo '
	<script src="https://cdn.rawgit.com/vast-engineering/jquery-popup-overlay/1.7.11/jquery.popupoverlay.js"></script>

                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

				$(\'#email-submit\').click(function(){

                                var button = this;

                                // "we are processing" indication
                                var indicator_html = \'<img class="scan-load-button" id="button-load-known-hardware-comment" src="/wp-uploads/2015/11/loading-circle.gif" />\';
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


# allow registration


#print "<br>The following scan results were achieved:";


#
#
##### General Info
#
#

   	$myquery = $lhg_price_db->prepare("SELECT id, scandate, kversion, distribution FROM `lhgscansessions` WHERE sid = %s", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$identified_scans = $lhg_price_db->get_results($myquery);

        #var_dump($identified_scans);

	$scandate = $identified_scans[0]->scandate;
	$scandate_txt = gmdate("Y-m-d, H:i:s", $scandate);

        $distribution = "unknown";
        $kversion = "unkwnown";

        $distribution = $identified_scans[0]->distribution;
        $kversion = $identified_scans[0]->kversion;

        $logo = get_distri_logo($distribution);

	echo "<h2>Scan overview:</h2>";

                #get and check session ID
                #echo "Session ID: $sid <br>";

                echo '<table id="registration">';
                echo '<tr id="header">


                <td id="title-colhw">Scan</td>';

                #if ($userknown == 1)
                #echo '<td id="hwscan-col3" width="13%"><nobr>Add HW to your profile</nobr></td>';

                echo '<td id="hwscan-col2" width="30%">Distribution</td>
                      <td id="hwscan-col2" width="20%">Kernel Version</td>
                <td id="hwscan-col2" width="13%">Hardware Components</td>


                </tr>';

        echo "<tr id=\"regcont\">";

        echo "
        	<td id=\"col-hw\">

                        ".'<div class="scan-overview-distri-logo"><img src="'.$logo.'" width="40" ></div>

                        <div class="subscribe-hwtext-scanlist"><div class="subscribe-hwtext-span-scanlist">&nbsp;'.$scandate_txt.' </div></div>';


	print                       " </td>";


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
                        <span class='subscribe-column-1'><center>Identified: $num_identified_hw <br> Unknown: $num_unidentified_hw  </center></span>
                        </td>";



                        echo "</tr>\n";

                echo "</table>";


# Add user feedback exchange
lhg_feedback_area( $sid );



#
#
##### Identified Hardware
#
#

if (count($identified_hw) > 0) {

echo "<h2>Known Hardware</h2>";

echo '
                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

				$(\'#known-hardware-submit\').click(function(){

                                var button = this;

                                // "we are processing" indication
                                var indicator_html = \'<img class="scan-load-button" id="button-load-known-hardware-comment" src="/wp-uploads/2015/11/loading-circle.gif" />\';
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

                /*]]> */
                </script>';



if (count($identified_hw) == 0) {
        print '<div class="no-hw-found">No hardware found</div>';
}else {

                #get and check session ID
                #echo "Session ID: $sid <br>";

                echo '<table id="registration">';
                echo '<tr id="header">


                <td id="title-colhw">Identified Hardware</td>';

                if ($userknown == 1)
                echo '<td id="hwscan-col3" width="13%"><nobr>Add HW to your profile</nobr></td>';

                echo '<td id="hwscan-col2" width="13%"><nobr>Please rate<br>Linux compatibility </nobr></td>

                <td id="col4">Category</td>

                <!-- td id="col2">'.$txt_subscr_regist_date.'</td -->




                </tr>';

                foreach($identified_hw as $a_identified_hw){

                        $PID = $a_identified_hw->postid;

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
			$permalink = get_permalink($a_identified_hw->postid);
			$title = translate_title( get_the_title($a_identified_hw->postid) );
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
                        $art_image=get_the_post_thumbnail( $a_identified_hw->postid, array(55,55), $img_attr, array('class' => 'image55') );

                        if ($art_image == ""){
                                #print "No Image";
                        	$art_image = '<img width="55" height="55" src="/wp-uploads/2013/03/noimage130.jpg" class="hwscan-image wp-post-image" alt="no-image" title="no-image"/>';
                        }

                        $comment= $a_identified_hw->usercomment;
                        $url=$a_identified_hw->url;
                        $id= $a_identified_hw->id;


                        #$category_ids[1]->cat_name = ""; #overwrite old values
                        $category_ids   = get_the_category ( $a_identified_hw->postid );
                        $category_name = $category_ids[0]->cat_name;
                        #$category_name2 = "";
                        $category_name2 = $category_ids[1]->cat_name;

                        # --- Registered users
		        global $wpdb;
			$usernum = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%' AND post_id = ".$a_identified_hw->postid);


                        // list HW entry

                        #$commenttext = "";
                        #if ($comment_excerpt != "") $commenttext='<div class="hwprofile-cite"><i>"'.wp_strip_all_tags($comment_excerpt).'"</i> <br>(<a href="'.$permalink.'#comment-'.$CID.'">'.$txt_subscr_more.'</a> - <a href="/wp-admin/comment.php?action=editcomment&c='.$CID.'">'.$txt_subscr_edit_comment.'</a>)</div>';

			$rating=the_ratings_results($a_identified_hw->postid,0,0,0,10);

                        echo "
                        <td id=\"col-hw\"> <a class='hwscan-found-image' href='$permalink' target='_blank' >$art_image</a>

                        ".'<div class="subscribe-hwtext"><div class="subscribe-hwtext-span-scan"><a href='.$permalink.' target="_blank">'.$short_title.'</a></div></div>'.$title_part2.'</label>'.
                          "<span class='subscribe-column-23'>$rating</span>";


print                       " </td>";


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
                echo "</table>";
}

$usercomment = lhg_get_usercomment($sid);
$buttontype = "green";
$buttontext = "Submit";
if ($usercomment != "") $buttontype = "light";
if ($usercomment != "") $buttontext = "Update";

#Ask, if HW scanner made errors?
if ($show_public_profile != 1)
echo ' <form action="?" method="post" class="usercomment">
       Please let us know if certain hardware was recognized incorrectly or not recognized at all.<br>
       This helps us improving the automatic hardware recognition for future scans:<br>
       <textarea id="known-hardware-usercomment" name="usercomment" cols="10" rows="3">'.$usercomment.'</textarea><br>
       <input type="submit" id="known-hardware-submit" name="email-login" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />
</form>
<br>
';

/*		//echo '</ul>';
		echo '<p id="subscribe-reloaded-select-all-p"><a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=true}else{for(var i=0;i<c;i++){t[i].checked=true}};return false;">'.__($txt_subscr_select_all,'subscribe-reloaded').'</a> ';
		echo '<a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=!t.checked}else{for(var i=0;i<c;i++){t[i].checked=false}};return false;">'.__($txt_subscr_select_inv,'subscribe-reloaded').'</a></p>';


		echo '<table id="subscribe-actions-table"><tr><td id="subscribe-actions" ><p id="subscribe-reloaded-action-p">'.__($txt_subscr_action.':<br>','subscribe-reloaded').'
			</td><td id="subscribe-actions">
                        <div class="sub-selector">
                          <input type="radio" name="sra" value="delete" id="action_type_delete" />
                            <label id="registration2" for="action_type_delete">'.__($txt_subscr_delete,'subscribe-reloaded').'</label>
                          </div> 
			</td>

                        <td id="subscribe-actions"><div class="sub-selector">
                          <input type="radio" name="sra" value="suspend" id="action_type_suspend"  />
                          <label id="registration2" for="action_type_suspend">'.__($txt_subscr_suspend,'subscribe-reloaded').'</label> </div>
                        </td>

                        <!-- td id="subscribe-actions"><div class="sub-selector">
                          <input type="radio" name="sra" value="force_r" id="action_type_force_y" />
                          <label id="registration2" for="action_type_force_y">'.__($txt_subscr_reply_only,'subscribe-reloaded').'</label> </div>
                        </td -->

                        <td id="subscribe-actions"><div class="sub-selector">
                          <input type="radio" name="sra" value="activate" id="action_type_activate" checked="checked" />
                          <label id="registration2" for="action_type_activate">'.__($txt_subscr_activate,'subscribe-reloaded').'</label> </div>
                        </td>

                      </tr></table>
                      </p>';

		echo '<p id="subscribe-reloaded-update-p"><button type="submit" class="subscribe-form-button" value="'.__($txt_subscr_update,'subscribe-reloaded').'" />'.__($txt_subscr_update,'subscribe-reloaded').'&nbsp;<i class="icon-arrow-right icon-button"></i></button><input type="hidden" name="sre" value="'.urlencode($email).'"/></p>';

*/

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
	echo "<h2>Multiple possibilites (Ambiguously Identified Hardware)</h2>";
        #small table version at
        $multilimit = 4;


        foreach($multi_identified_hw as $a_identified_hw){


		$title = ($a_identified_hw->idstring);
		$usbid = ($a_identified_hw->usbid);

		echo '<table id="registration">';
                echo '<tr id="header">


                <td id="title-colhw">
                Identified USB Device<br>
                '.$title.' - (USB ID: '.$usbid.')
                </td>';


                if ($userknown == 1)
                echo '<td id="hwscan-col3" width="13%"><nobr>Add HW to your profile</nobr></td>';

                echo '<td id="hwscan-col2" width="13%"><nobr>Please rate<br>Linux compatibility </nobr></td>

                <td id="col4">Category</td>

                <!-- td id="col2">'.$txt_subscr_regist_date.'</td -->
                </tr>';


                # split postids
                $postids = explode(",",$a_identified_hw->postid);

                $i=0;
	        foreach($postids as $postid){
                        $i++;

                        #print "IDs: ".count($postids)."<br>";
			if (count($postids) > $multilimit) {
                                # skip line

                        }else{
		                echo '<tr><td><div class="hwscan_option">Option '.$i."</div>";
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
					'class'	=> "hwscan-image",
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
		$buttontext = "Submit";
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

        # check if mainboard or laptop
        $laptop_prob = lhg_scan_is_laptop($sid);
        if ($laptop_prob > 0.8) $mb_or_laptop = "Laptop";
        if ($laptop_prob <= 0.8) $mb_or_laptop = "Mainboard";

        $mb_name = lhg_get_mainboard_name( $sid );
        $clean_mb_name = lhg_clean_mainboard_name( $mb_name );
	print "<h2>Unknown ".$mb_or_laptop."</h2>";
        print '<div id="mbname">Identified name: '.$clean_mb_name."<span id='details-mb' class='details-link'></span></div>";
        print '<div id="hidden-details-mb">Full identifier: '.$mb_name.'</div>';


	$mb_usercomment = lhg_get_mb_usercomment($sid);
	$url_mb = lhg_get_mb_url($sid);
	$buttontype = "green";
	$buttontext = "Submit";
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

       if (current_user_can('publish_posts') && ($show_public_profile != 1) ) {
           print '&nbsp;&nbsp;&nbsp;(<a href="/wp-admin/post.php?post='.$newPostID_mb.'&action=edit">finalize article</a>)';
       }

       print '
       </form>';

        print "<table id='mainboard-scan-table'>
                 <tr id='mainboard-header'><td class='pci-column-onboard'>On-board?</td><td>Hardware Component Name</td><td class='pciid-column'>PCI ID</td></tr>";

        # list of IDs that should be hidden in overview
        $hidearray = array();

        foreach($unidentified_hw_pci as $a_identified_hw){

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
		$buttontext = "Submit";
                $showme = lhg_show_scanned_component( $title , $id, $pciid );
                $short_pci_title = lhg_clean_pci_title( $title );
                $is_onboard = lhg_pci_component_is_onboard( $title, $sid, $id, $pciid);

                if ($is_onboard == "yes")  {  $default_y = 'checked'; $default_n = '';	}
                if ($is_onboard == "no") {    $default_y = ''; $default_n = 'checked';  }
		#if ($is_onboard == "yes")  { array_push( $pci_obl , $pciid); }
                #print "IO: $is_onboard - OBL: ".var_dump($pci_obl)." - PCIID: $pciid<br>";


		if ( ($comment != "") or ($url != "") ) $buttontype = "light";
		if ( ($comment != "") or ($url != "") ) $buttontext = "Update";
		if ( ($comment != "") or ($url != "") ) $showme = true;

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
                             yes <input type="radio" id="radio-y-'.$id.'" name="on-board" value="y" '.$default_y.' />
                             <input type="radio" id="radio-n-'.$id.'" name="on-board" value="n" '.$default_n.' /> no
                          </fieldset>
                        </td>
                        <td><div class="pci-title"><b>'.$short_pci_title.'</b><span id="show-details-hw-'.$id.'"></span></div>
                           <div class="pci-feedback" id="pci-feedback-'.$id.'">
                              <div id="details-hw-'.$id.'">Full identifier: '.$title.'</div>

			       <div id="updatearea-'.$id.'">
                                Please rate hardware: '.$article_created.'
       				Help us adding this hardware to our database. Please identify this hardware and describe its Linux compatibility:<br>
       				<textarea id="comment-'.$id.'" name="comment-'.$id.'" cols="10" rows="3">'.$comment.'</textarea><br>
       				If possible, please leave an URL to a web page where the hardware is described (e.g. manufacturer`s data sheet or Amazon.com page).<br>URL:
       				<input id="url-'.$id.'" name="url-'.$id.'" type="text" value="'.$url.'" size="40" maxlenght="290">
       				<input id="postid-'.$id.'" name="postid-'.$id.'" type="hidden" value="'.$newPostID.'">
			       </div>
       			       <br><input type="submit" name="scan-comments-'.$id.'" id="scan-comments-'.$id.'" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />';
	} else {
                print '
                        <td class="pci-column-onboard pci-column-onboard-radiob">';

                if ($default_y != "") print "yes";
                if ($default_n != "") print "no";
                print '
                        </td>
                        <td><div class="pci-title"><b>'.$short_pci_title.'</b><span id="show-details-hw-'.$id.'"></span></div>';
       }


	       if (current_user_can('publish_posts') && ($show_public_profile != 1) ) {
        	   print '&nbsp;&nbsp;&nbsp;(<a href="/wp-admin/post.php?post='.$newPostID_pci.'&action=edit">finalize article</a>)';
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

	if (current_user_can('publish_posts') && ($show_public_profile != 1) ) {
        	   print '&nbsp;&nbsp;&nbsp;(<a href id="update-pcilist">Update PCI lists</a>)';
        }


	echo '
                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

				$(\'.hwdetail\').hide();
				$(\'.pciid-column\').hide();
				$(\'.mb-default-hidden\').hide();
                                $(\'<a href id="toggleButton">Show hidden components</a>\').prependTo(\'#mainboard-show-more\');
                                //$(\'<a href id="show-more-mb">Show details</a>\').prependTo(\'#details-mb\');
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
                                var indicator_html = \'<img class="scan-load-button" id="button-load-mb-comment" src="/wp-uploads/2015/11/loading-circle.gif" />\';
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

print "<h2>Unknown Hardware</h2>";



                //print "Insert AJAX";
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


                      // show additional properties from the beginning if amazon URL was provided
                      // hide otherwise
                      $("[id^=url-]").each(function(){
                      	  var id = $(this).attr(\'id\').substring(4);

                          url = $("#url-"+id).val();
			  //$("#updatearea-8921").append("URL: "+url);
			  //$("#updatearea-8921").append("POS: "+ url.toLowerCase().indexOf("amazon.") );
                          if ( url.toLowerCase().indexOf("amazon.") >= 0 ) {
                          	$(\'#details-hw-\'+id).show("slow");
                                //$("#updatearea-8921").append( "FOUND!" );
                          } else {
                          	$(\'#details-hw-\'+id).hide();
                          }
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
                                        // $("#details-hw-"+id).hide();
                                });
                                $("[id^=show-details-hw-]").click(function(){
                                	var id = $(this).attr(\'id\').substring(16);
	                                $(\'#details-hw-\'+id).show("slow");
                                  	$("#show-details-hw-"+id).hide("slow");
                                        // in case of mainboard components show full comment panel
                                        $("#pci-feedback-"+id).show("slow");
                                        return false;
                                });





                                // Submit a comment or an URL to a unknown hardware component

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

                                        // show new title
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

                                        var indicator_html = \'<div id="scan-load-area">Searching for hardware...<img class="scan-load-button" id="auto_search_ongoing" src="/wp-uploads/2015/11/loading-circle.gif" /></div>\';

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


                <td id="title-colhw">Found Hardware Identifier</td>
                <td>';
	        if ($show_public_profile != 1) print "Rate Hardware";
                print '
                </td>
                <td id="col2" width="13%"><nobr>Type</nobr></td>

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

                	if ($usbid != "") $logo = "<img src='/wp-uploads/2014/12/USB_logo.jpg' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown USB device'>";
                	if ($pciid != "") $logo = "<div class='hwscan-pcilogo'>&nbsp;&nbsp;PCI</div>";
                	if ($scantype == "cpu") $logo = "<img src='/wp-uploads/2014/12/cpu-image.png' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown CPU'>";

                	if ($scantype == "drive") {
                        	$logo = "<img src='/wp-uploads/2014/12/drive-hdd.png' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown Drive'>";
                                if (strpos(" ".$title,"CD-ROM") > 0) $logo = "<img src='/wp-uploads/2014/12/drive-cd.png' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown CD/DVD Drive'>";
                                if (strpos(" ".$title,"SSD") > 0) $logo = "<img src='/wp-uploads/2014/12/drive-ssd.png' class='hwscan-usblogo".$csspub."' id='hwscan-usblogo-".$id."' title='Unknown SSD Drive'>";

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
$buttontext = "Submit";
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
	$article_created = "Please rate: <br><nobr>$out1</nobr>";

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
	$article_created = "Please rate: <br><nobr>$out1</nobr>";

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
        $article_created = "Please rate: <br><nobr>$out1</nobr>";

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

                $title_info = get_the_title( $newPostID );
                $properties_string = lhg_get_properties_string( $newPostID );

                if ( ($scantype == "cpu") or ($scantype == "usb") or ($scantype == "drive") ) {
                	#print '   <div id="details-hw-'.$id.'" class="details">Full identifier: '.$otitle.'
                	print '
                        	<div id="details-hw-'.$id.'" class="details">
                	    		<br>Title: <span id="title-'.$id.'">'.$title_info.'</span>
                    	    		<br>Properties: <span id="properties-'.$id.'">'.$properties_string.'</span>
		        	</div>';
                }

                print '</div>';

if ($show_public_profile != 1)
print '<form action="?" method="post" class="hwcomments">
       <div id="updatearea-'.$id.'">
       Help us adding this hardware to our database. Please identify this hardware and describe its Linux compatibility:<br>
       <textarea id="comment-'.$id.'" name="comment-'.$id.'" cols="10" rows="3">'.$comment.'</textarea><br>
       If possible, please leave an URL to a web page where the hardware is described (e.g. manufacturer`s data sheet or Amazon.com page).<br>URL:
       <input id="url-'.$id.'" name="url-'.$id.'" type="text" value="'.$url.'" size="40" maxlenght="290">
       <input id="postid-'.$id.'" name="postid-'.$id.'" type="hidden" value="'.$newPostID.'">
       </div>
       <br><input type="submit" name="scan-comments-'.$id.'" id="scan-comments-'.$id.'" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />
</form>';
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

                            if (current_user_can('publish_posts') ) {
                                print '<br><a href="/wp-admin/post.php?post='.$newPostID.'&action=edit">finalize article</a>';
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

                            if (current_user_can('publish_posts') ) {
                                print '<br><a href="/wp-admin/post.php?post='.$newPostID.'&action=edit">finalize article</a>';
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

                              if (current_user_can('publish_posts') ) {
                                print '<br><a href="/wp-admin/post.php?post='.$newPostID.'&action=edit">finalize article</a>';
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



$scandate = lhg_get_hwscandate($sid);
$scandate = gmdate("Y-m-d\TH:i:s\Z", $scandate);
print "<br>This scan was performed at: ".$scandate;
print "<br>Please note that this web service is still under development. All your scan results were successfully transferred to the Linux-Hardware-Guide team.
However, the automatic recognition of hardware and its representation on this scan overview page for sure is still incomplete.";

print "<p>This tool is currently limited to following hardware components:";
print "<ul><li>USB devices";
print "<li>PCI devices";
print "<li>Mainboards (experimental)";
print "<li>Laptops (experimental)";
print "<li>CPUs";
print "<li>Storage media (HDD, CD, DVD, SSD)";
print "</ul>";

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
        # store feedback exchange (user <--> LHG-Team) in DB

        # routine called twice, once empty -> ignore
        if ($sid == "") return;

        global $lhg_price_db;
	$myquery = $lhg_price_db->prepare("INSERT INTO `lhgscan_comments` (comment_text, comment_date, scanid, user)
        							VALUES  (%s, %s, %s, %s)", $comment_text, time(), $sid, $uid);
	$result = $lhg_price_db->query($myquery);

        # send mail
        # if first comment:

	$myquery = $lhg_price_db->prepare("SELECT COUNT(id) FROM `lhgscan_comments` WHERE scanid = %s", $sid);
	$counter = $lhg_price_db->get_var($myquery);

        #print "<br>DEBUG: Number of comments:".$counter;

        #var_dump($counter);

	$email = lhg_get_hwscanmail($sid);
        if ( ( $counter == 1) && ($uid == 1 ) && ($email != "") ) {
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

        if ( ( $counter > 1) && ($uid == 1 ) && ($email != "") ) {
                # follow-up comment - coming from LHG-Team
	        $subject = "LHG Hardware Scan - Open Questions";
        	$message = 'Hello,

Following comment was sent to you regarding your hardware scan:
------------------------------------

'.$comment_text.'

------------------------------------
To answer to this request, please visit: http://www.linux-hardware-guide.com/hardware-profile/scan-'.$sid.'

Best regards,
your Linux-Hardware-Guide Team
';

        wp_mail( $email, $subject, $message );
	}

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
	$myquery = $lhg_price_db->prepare("SELECT comment_text, comment_date, user FROM `lhgscan_comments` WHERE scanid = %s ", $sid);
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$results = $lhg_price_db->get_results($myquery);
	$error = $lhg_price_db->last_error;
	if ($error != "") var_dump($error);

        #var_dump($results);

# 2. if exist show old comments

        if ( !empty($results) ) {
                #print "<br>Comments found -> <br>";

                foreach ( $results as $result ) {
                        print '<div class="user-feedback">';
                        $uid = $result->user;
                        $userinfo = get_userdata($uid);
                        $uname = $userinfo->user_login;
                        if ($uname == "") $uname = "Anonymous";
                        if ($uname == "admin") $uname = "LHG-Team";
                        $comment = $result->comment_text;
                        $date = date("jS \of F Y h:i:s A",$result->comment_date);
                        print "<b>".$uname."</b> at ".$date.':<br> <div class="user-feedback-text">'.$result->comment_text."</div></div>";
		}
	}


# 3. show field for new comment

        #only if not in public mode
        global $show_public_profile;
        if ($show_public_profile != 1) {

        $rand=rand(1,9999); # prevent browser caching... ugly
        # admin can always post
        if ( current_user_can ('edit_posts') ) {

	echo ' <form action="?'.$rand.'" method="post" class="scan-feedback">
      		Ask additional questions to scan submitter:<br>
	       <textarea id="known-hardware-userfeedback" name="usercomment-feedback" class="usercomment-feedback" cols="10" rows="3"> </textarea><br>
       	       <input type="submit" id="scan-comment" value="Submit" name="button-usercomment-feedback" class="hwscan-comment-button-green" />
	       </form><br>';
	}

        # reply possible?
        if ( !empty($results) && !current_user_can ('edit_posts') ) {

	echo ' <form action="?'.$rand.'" method="post" class="scan-feedback">
      		Reply to comment:<br>
	       <textarea id="known-hardware-userfeedback" name="usercomment-feedback" class="usercomment-feedback" cols="10" rows="3"> </textarea><br>
       	       <input type="submit" id="scan-comment" value="Reply" name="button-usercomment-feedback" class="hwscan-comment-button-green" />
	       </form><br>';
	}
	}
}

#         <textarea id="known-hardware-usercomment" name="usercomment" cols="10" rows="3">'.$usercomment.'</textarea><br>
#       <input type="submit" id="known-hardware-submit" name="email-login" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />



$output = ob_get_contents();
ob_end_clean();
return $output;

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

?>