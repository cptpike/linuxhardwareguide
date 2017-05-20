<?php
require_once(plugin_dir_path(__FILE__)."scan.php");


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

# load PriceDB settings
require_once(plugin_dir_path(__FILE__).'../../lhg-pricedb/includes/lhg.conf');
require_once('/var/www/wordpress/wp-content/plugins/lhg-pricedb/includes/lhg.conf');



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
$hwscanpos=strpos($urlpath,"/hardware-profile/uid-");
$uid = substr($urlpath,$hwscanpos+22);

# Failsafe mode
if ($uid == "") {
        print '<div class="no-hw-found">Not a valid user ID</div>';
        $skip_list = 1;
}


if (sizeOf($_POST) > 0) lhg_scan_check_changes( $sid );



# get list of scans
global $lhg_price_db;
$myquery = $lhg_price_db->prepare("SELECT id, sid, scandate, kversion, distribution FROM `lhgscansessions` WHERE uid = %s GROUP BY scandate ORDER BY scandate DESC", $uid);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
$identified_scans = $lhg_price_db->get_results($myquery);
#var_dump( $identified_scans );

#var_dump( $unidentified_hw );

#$scantype = "multiple_results";
#$myquery = $lhg_price_db->prepare("SELECT id, postid, usbid, pciid, idstring , usercomment , url , scantype FROM `lhghwscans` WHERE sid = %s AND scantype  = %s", $sid, $scantype);
#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#$multi_identified_hw = $lhg_price_db->get_results($myquery);
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

#print '<b>Scan Overview</b>';
#Thank you for using our Linux-Hardware-Guide scanning software</b> (see <a href="https://github.com/paralib5/lhg_scanner">GitHub</a> for more details).<br>
#This way we can fill our Linux Hardware Database with valuable information for the Linux community.</b>';



$email = lhg_get_hwscanmail($sid);
if ($email != "") $userknown = 1;

#$buttontext = "Submit";
#if ($userknown == 1) $buttontext = "Update";
#print "";
#print '
#<br>&nbsp;<br>
#<h2>Contact information</h2>
#<form action="?" method="post">
#       Please leave us your email address in order to contact you in case of questions regarding your hardware scan results:<br>
#       <b>Email</b>: <input name="email" type="text" size="30" maxlength="50" value="'.$email.'">
#       <input type="submit" name="email-login" value="'.$buttontext.'" class="hwscan-email-button-'.$buttontext.'" />
#</form>
#';

# allow registration


#print "<br>The following scan results were achieved:";
print "<br>";


#
#
##### Identified Hardware
#
#


if ( (count($identified_scans) == 0) or ($skip_list == 1) ) {
        print '<div class="no-hw-found">No scans found</div>';
}else {

	if (sizeof($identified_scans) == 1) print "There is ".sizeof($identified_scans)." scan result that is linked to the anonymous user account ".$uid.".<br>&nbsp;<br>";
	if (sizeof($identified_scans) > 1)  print "There are ".sizeof($identified_scans)." scan results that are linked to the anonymous user account ".$uid.".<br>&nbsp;<br>";

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

                foreach($identified_scans as $a_identified_scan){

                        $SID = $a_identified_scan->sid;

			$myquery = $lhg_price_db->prepare("SELECT COUNT(DISTINCT idstring) FROM `lhghwscans` WHERE sid = %s AND postid = 0", $SID);
			#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
			$num_unidentified_hw = $lhg_price_db->get_var($myquery);
                        #print "NU: $num_unidentified_hw";

			$myquery = $lhg_price_db->prepare("SELECT COUNT(DISTINCT postid) FROM `lhghwscans` WHERE sid = %s AND postid <> 0", $SID);
			#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
			$num_identified_hw = $lhg_price_db->get_var($myquery);
                        #print "NI: $num_identified_hw";

                        $scandate = $a_identified_scan->scandate;
			$scandate_txt = gmdate("Y-m-d, H:i:s", $scandate);

                        $distribution = "unknown";
                        $kversion = "unkwnown";

                        $distribution = $a_identified_scan->distribution;
                        $kversion = $a_identified_scan->kversion;

                        $logo = get_distri_logo($distribution);

                        #ToDo:
                        #$num_identified_hw = 0;
                        #$num_total_hw = 0;

                        # get the rating field
                        #ob_start();
		        #$returnval .= the_ratings("div",$PID);
		        #$out1 = ob_get_contents();
        		#ob_end_clean();
                        #if (!strpos($out1,"onmouseout")>0) $out1 = "already rated";


			echo "<tr id=\"regcont\">";

                        #List identified hw components

                        #$comment= $a_identified_hw->usercomment;
                        #$url=$a_identified_hw->url;
                        #$id= $a_identified_hw->id;


                        #$category_ids[1]->cat_name = ""; #overwrite old values
                        #$category_ids   = get_the_category ( $a_identified_hw->postid );
                        #$category_name = $category_ids[0]->cat_name;
                        #$category_name2 = "";
                        #$category_name2 = $category_ids[1]->cat_name;

                        # --- Registered users
		        #global $wpdb;
			#$usernum = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%' AND post_id = ".$a_identified_hw->postid);


                        // list HW entry

                        #$commenttext = "";
                        #if ($comment_excerpt != "") $commenttext='<div class="hwprofile-cite"><i>"'.wp_strip_all_tags($comment_excerpt).'"</i> <br>(<a href="'.$permalink.'#comment-'.$CID.'">'.$txt_subscr_more.'</a> - <a href="/wp-admin/comment.php?action=editcomment&c='.$CID.'">'.$txt_subscr_edit_comment.'</a>)</div>';

			#$rating=the_ratings_results($a_identified_hw->postid,0,0,0,10);

                        echo "
                        <td id=\"col-hw\">

                        ".'<div class="scan-overview-distri-logo"><a href="./scan-'.$SID.'" target="_blank"><img src="'.$logo.'" width="40" ></a></div>

                        <div class="subscribe-hwtext-scanlist"><div class="subscribe-hwtext-span-scanlist">&nbsp;<a href="./scan-'.$SID.'" target="_blank">'.$scandate_txt.' (see details ...)</a></div></div>';


print                       " </td>";


                        //<td id=\"col4\">
                        //<span class='subscribe-column-2'>$deact<br>({$a_subscription->status})</span>
                        //</td>

                        // --- Add to HW profile
                        #if ($userknown == 1) {
	                #        $hwbutton = lhg_add_hwbutton( $email, $a_identified_hw->postid);
        	        #        #$hwbutton = "Test";
                	#        echo "
                        #	<td id=\"col5\">
	                #        <span class='column-hwbutton'>".$hwbutton."</span>
                        #        <div class='regusers'>(Reg. Linux users: ".$usernum.")</div>
        	        #        </td>";
                        #}


                        // --- User to rate HW

                        #$postid = $a_identified_hw->postid;

                        #if ($myrating == "n.a.")
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

                        echo "
                        <td id=\"col2\">
                        <span class='subscribe-column-1'><center>Identified: $num_identified_hw <br> Unknown: $num_unidentified_hw  </center></span>
                        </td>";



                        echo "</tr>\n";
		}
                echo "</table>";
}

#$usercomment = lhg_get_usercomment($sid);
#$buttontype = "green";
#$buttontext = "Submit";
#if ($usercomment != "") $buttontype = "light";
#if ($usercomment != "") $buttontext = "Update";

#Ask, if HW scanner made errors?
#echo ' <form action="?" method="post" class="usercomment">
#       Please let us know if certain hardware was recognized incorrectly or not recognized at all.<br>
#       This helps us improving the automatic hardware recognition for future scans:<br>
#       <textarea name="usercomment" cols="10" rows="3">'.$usercomment.'</textarea><br>
#       <input type="submit" name="email-login" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />
#</form>
#<br>
#';

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

#
#
##### Multiple Identified Hardware
#
#


#if (count($multi_identified_hw) > 0) {
#	echo "<h2>Multiple possibilites (Ambigously Identified Hardware)</h2>";
#        #small table version at
#        $multilimit = 4;
#
#
#        foreach($multi_identified_hw as $a_identified_hw){
#
#
#		$title = ($a_identified_hw->idstring);
#		$usbid = ($a_identified_hw->usbid);
#
#		echo '<table id="registration">';
#                echo '<tr id="header">
#
#
#                <td id="title-colhw">
#                Identified USB Device<br>
#                '.$title.' - (USB ID: '.$usbid.')
#                </td>';
#
#
#                if ($userknown == 1)
#                echo '<td id="hwscan-col3" width="13%"><nobr>Add HW to your profile</nobr></td>';
#
#                echo '<td id="hwscan-col2" width="13%"><nobr>Please rate<br>Linux compatibility </nobr></td>
#
#                <td id="col4">Category</td>
#
#                <!-- td id="col2">'.$txt_subscr_regist_date.'</td -->
#                </tr>';
#
#
#                # split postids
#                $postids = explode(",",$a_identified_hw->postid);
#
#                $i=0;
#	        foreach($postids as $postid){
#                        $i++;
#
#                        #print "IDs: ".count($postids)."<br>";
#			if (count($postids) > $multilimit) {
#                                # skip line
#
#                        }else{
#		                echo '<tr><td><div class="hwscan_option">Option '.$i."</div>";
#	                       #echo "PID: $postid";
#        	                echo "</td><td></td><td>";
#	        	        if ($userknown == 1) echo "<td></td>";
#                        	echo '</td></tr>';
#                        }
#
#
#                        # get the rating field
#                        ob_start();
#		        $returnval .= the_ratings("div",$postid);
#		        $out1 = ob_get_contents();
#        		ob_end_clean();
#                        if (!strpos($out1,"onmouseout")>0) $out1 = "already rated";
#
#
#			echo "<tr id=\"regcont\">";
#
#                        #List identified hw components
#                        $comment_excerpt = "";
#			$permalink = get_permalink($postid);
#			$title = translate_title( get_the_title($postid) );
#			$s=explode("(",$title);
#			$short_title=trim($s[0]);
#			$title_part2=str_replace(")","",trim($s[1]));
#                        if (strlen($title_part2) > 1) $title_part2 .= "<br>";
#
#                        $img_attr = array(
#					#'src'	=> $src,
#					'class'	=> "hwscan-image",
#					#'alt'	=> trim( strip_tags( $attachment->post_excerpt ) ),
#					#'title'	=> trim( strip_tags( $attachment->post_title ) ),
#				    );
#                        $art_image=get_the_post_thumbnail( $postid, array(55,55), $img_attr );
#
#                        if ($art_image == ""){
#                                #print "No Image";
#                        	$art_image = '<img width="55" height="55" src="/wp-uploads/2013/03/noimage130.jpg" class="hwscan-image wp-post-image" alt="no-image" title="no-image"/>';
#                        }
#
#                        $comment= $a_identified_hw->usercomment;
#                        $url=$a_identified_hw->url;
#                        $id= $a_identified_hw->id;
#
#
#                        #$category_ids[1]->cat_name = ""; #overwrite old values
#                        $category_ids   = get_the_category ( $postid );
#                        $category_name = $category_ids[0]->cat_name;
#                        #$category_name2 = "";
#                        $category_name2 = $category_ids[1]->cat_name;
#
#                        # --- Registered users
#		        global $wpdb;
#			$usernum = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%' AND post_id = ".$postid);
#
#
#                        // list HW entry
#
#                        #$commenttext = "";
#                        #if ($comment_excerpt != "") $commenttext='<div class="hwprofile-cite"><i>"'.wp_strip_all_tags($comment_excerpt).'"</i> <br>(<a href="'.$permalink.'#comment-'.$CID.'">'.$txt_subscr_more.'</a> - <a href="/wp-admin/comment.php?action=editcomment&c='.$CID.'">'.$txt_subscr_edit_comment.'</a>)</div>';
#
#			$rating=the_ratings_results($postid,0,0,0,10);
#
#			if (count($postids) > $multilimit) {
#                                #small version, no image
#	                        echo "
#        	                <td id=\"col-hw\">
#                        	".'<div class="subscribe-hwtext"><div class="subscribe-hwtext-span-scan-small"><a href='.$permalink.' target="_blank">'.$short_title.'</a></div></div>'.$title_part2.'</label>'.
#	                          "<span class='subscribe-column-23-small'>$rating</span>";
#				print    " </td>";
#
#                        }else{
#                        echo "
#                        <td id=\"col-hw\"> <a class='hwscan-found-image' href='$permalink' target='_blank' >$art_image</a>
#
#                        ".'<div class="subscribe-hwtext"><div class="subscribe-hwtext-span-scan"><a href='.$permalink.' target="_blank">'.$short_title.'</a></div></div>'.$title_part2.'</label>'.
#                          "<span class='subscribe-column-23'>$rating</span>";
#
#
#print                       " </td>";
#
#                        }
#                        //<td id=\"col4\">
#                        //<span class='subscribe-column-2'>$deact<br>({$a_subscription->status})</span>
#                        //</td>
#
#                        // --- Add to HW profile
#                        if ($userknown == 1) {
#	                        $hwbutton = lhg_add_hwbutton( $email, $a_identified_hw->postid);
#        	                #$hwbutton = "Test";
#                	        echo "
#                        	<td id=\"col5\">
#	                        <span class='column-hwbutton'>".$hwbutton."</span>
#                                <div class='regusers'>(Reg. Linux users: ".$usernum.")</div>
#        	                </td>";
#                        }
#
#
#                        // --- User to rate HW
#
#                        $postid = $a_identified_hw->postid;
#
#                        #if ($myrating == "n.a.")
#                        echo "
#                        <td id=\"col4\">
#                        <span class='subscribe-column-2'>".$out1."</span>
#                        </td>";
#
#
#
#                        #$registration_date=$a_subscription->dt;
#                        #list ($registration_date, $registration_time) = explode(" ",$registration_date);
#                        $categorypart2 = "";
#                        if ($category_name2 != "")  $categorypart2 = "<br>($category_name2)";
#
#                        echo "
#                        <td id=\"col2\">
#                        <span class='subscribe-column-1'><center>$category_name $categorypart2 </center></span>
#                        </td>";
#
#
#
#                        echo "</tr>\n";
#		}
#
#
#		$usercomment_multi = lhg_get_usercomment_multi($sid,$id);
#		$buttontype = "green";
#		$buttontext = "Submit";
#		if ($usercomment_multi != "") $buttontype = "light";
#		if ($usercomment_multi != "") $buttontext = "Update";
#
#		echo ' 	<tr><td>
#			<form action="?" method="post" class="usercomment_multi">
#       			Which hardware are you actually using?<br>
#       			<textarea name="usercomment_multi_'.$id.'" cols="10" rows="3">'.$usercomment_multi.'</textarea><br>
#       			<input type="submit" name="email-login" value="'.$buttontext.'" class="hwscan-comment-button-'.$buttontype.'" />
#			</form>
#			</td>
#
#                        <td></td> <td></td> ';
#
#
#                if ($userknown == 1) echo "<td></td>";
#
#		echo '</tr>';
#		echo "</table>";
#
#	}
#
#}





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




#function lhg_get_hwscandate( $sid ) {
#
#        global $lhg_price_db;
#	$myquery = $lhg_price_db->prepare("SELECT scandate FROM `lhgscansessions` WHERE sid = %s ", $sid);
#	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#	$scandate = $lhg_price_db->get_var($myquery);
#        return $scandate;
#}
#
#function lhg_get_hwscanmail( $sid ) {
#
#        global $lhg_price_db;
#	$myquery = $lhg_price_db->prepare("SELECT email FROM `lhgscansessions` WHERE sid = %s ", $sid);
#	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#	$scandate = $lhg_price_db->get_var($myquery);
#        return $scandate;
#}
#
#function lhg_get_usercomment( $sid ) {
#
#        global $lhg_price_db;
#	$myquery = $lhg_price_db->prepare("SELECT usercomment FROM `lhgscansessions` WHERE sid = %s ", $sid);
#	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#	$usercomment = $lhg_price_db->get_var($myquery);
#        return $usercomment;
#}
#
#function lhg_get_usercomment_multi( $sid, $id ) {
#
#        global $lhg_price_db;
#	$myquery = $lhg_price_db->prepare("SELECT usercomment FROM `lhghwscans` WHERE sid = %s AND id = %s ", $sid, $id);
#	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#	$usercomment = $lhg_price_db->get_var($myquery);
#        return $usercomment;
#}
#
#function lhg_update_usercomment( $sid, $usercomment ) {
#
#        global $lhg_price_db;
#	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET usercomment = %s WHERE sid = %s ", $usercomment, $sid);
#	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#	$result = $lhg_price_db->query($myquery);
#}
#
#function lhg_update_usercomment_multi( $sid, $usercomment, $id ) {
#
#        #print "UPDATE: $usercomment, $id";
#
#        global $lhg_price_db;
#	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET usercomment = %s WHERE id = %s ", $usercomment, $id);
#	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#	$result = $lhg_price_db->query($myquery);
#}


#function lhg_update_mail( $sid, $email ) {
#
#        global $lhg_price_db;
#	$myquery = $lhg_price_db->prepare("UPDATE `lhgscansessions` SET email = %s WHERE sid = %s ", $email, $sid);
#	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#	$result = $lhg_price_db->query($myquery);
#
#        $to = "webmaster@linux-hardware-guide.com";
#        $subject = "LHG Hardware Scan";
#        $message = 'A hardware scan was performed and the user left a contact address.
#ScanID: '.$sid.'
#email: '.$email.'
#
#Please visit: http://www.linux-hardware-guide.com/hardware-profile/scan-'.$sid.'
#
#';
#
#        wp_mail( $to, $subject, $message );
#
#}


#function lhg_scan_updateid( $sid, $cid ) {
#
#        global $lhg_price_db;
#
#        $comment = $_POST["comment-".$cid];
#        $url     = $_POST["url-".$cid];
#
#        #print "cid: $cid <br>";
#        #print "New comment: $comment <br>";
#        #print "New url: $url <br>";
#
#	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET usercomment = %s WHERE id = %s ", $comment, $cid);
#	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#	$result = $lhg_price_db->query($myquery);
#
#	$myquery = $lhg_price_db->prepare("UPDATE `lhghwscans` SET url = %s WHERE id = %s ", $url, $cid);
#	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
#	$result = $lhg_price_db->query($myquery);
#
#}

#function lhg_scan_check_mail( $sid ) {
 #
 #       $db_email = lhg_get_hwscanmail ($sid);
#        if ($db_email != $_POST["email"]) lhg_update_mail( $sid, $_POST["email"] );
#
#}


#function lhg_scan_check_changes( $sid ) {
#
#        #ä user submitted something
#        #print "Changes identified!<br>";
#        #var_dump($_POST);
#
#
#        # check which id number he submitted
#	$keys = array_keys($_POST);
#	#echo $keys[0];
#
#        if ( substr($keys[0], 0, 8) == "comment-" ) {
#
#             $commentid = substr($keys[0], 8);
#             #print "ID: $commentid";
#             lhg_scan_updateid( $sid , $commentid );
#
#	}

        #print substr($keys[0], 0, 17);
        #var_dump($keys);
#
#      if ( substr($keys[0], 0, 17) == "usercomment_multi" ) {
#
#             $commentid = substr($keys[0], 18);
#             #print "ID multi: $commentid - Text:".$_POST["usercomment_multi_".$commentid];
#             lhg_update_usercomment_multi( $sid , $_POST["usercomment_multi_".$commentid], $commentid );
#
#	}
#
#
#        print "<br>";
#
#	#check if values are updated
#	if ($_POST["email"] != "") lhg_scan_check_mail( $sid );
#
#        $db_email = lhg_get_hwscanmail ($sid);
#        if (( $db_email != $_POST["email"]) and ($_POST["email"] != "") ) lhg_update_mail( $sid, $_POST["email"] );
#
#        $db_usercomment = lhg_get_usercomment ($sid);
#        if ( ($db_usercomment != $_POST["usercomment"]) and ($_POST["usercomment"] != "") ) lhg_update_usercomment( $sid, $_POST["usercomment"] );
#
#        #$db_usercomment_multi = lhg_get_usercomment_multi ($sid);
#        #if ( ($db_usercomment != $_POST["usercomment"]) and ($_POST["usercomment"] != "") ) lhg_update_usercomment( $sid, $_POST["usercomment"] );
#
#}
#
#function lhg_add_hwbutton ( $email, $pid ) {
#
##check if already added
#
##ToDo !!
#
##show button
#
#$button =  '<form action="/hardware-profile?srp='.$pid.'&#038;sra=s" method="post" onsubmit="if(this.sre.value==\'\' || this.sre.indexOf(\'@\')==0) return false" target="_blank">
#  	                 <fieldset style="border:0">
#  	                 <input type="hidden" class="subscribe-form-field-hwlist" name="sre" value="'.$email.'" size="18"  />
#  	                 <button type="submit" value="Add" class="hwlist-add-button" />Add&nbsp;<i class="icon-arrow-right icon-button"></i></button></p>
#  	                 </fieldset>
#  	                 </form>';
#
#return $button;
#}



$output = ob_get_contents();
ob_end_clean();
return $output;

?>