<?php
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


// Avoid direct access to this piece of code
if (!function_exists('add_action')){
	header('Location: /');
	exit;
}

global $wp_subscribe_reloaded;

ob_start();
if (!empty($_POST['post_list'])){
	$post_list = array();
	foreach($_POST['post_list'] as $a_post_id){
		if (!in_array($a_post_id, $post_list))
			$post_list[] = intval($a_post_id);
	}

	$action = !empty($_POST['sra'])?$_POST['sra']:(!empty($_GET['sra'])?$_GET['sra']:'');
	switch($action){
		case 'delete':
			$rows_affected = $wp_subscribe_reloaded->delete_subscriptions($post_list, $email);
			echo '<p class="updated">'.__('Subscriptions deleted:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'suspend':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email, 'C');
			echo '<p class="updated">'.__('Subscriptions suspended:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'activate':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email, '-C');
			echo '<p class="updated">'.__('Subscriptions activated:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'force_r':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email, 'R');
			echo '<p class="updated">'.__('Subscriptions updated:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		default:
			break;
	}
}
$message = html_entity_decode(stripslashes(get_option('subscribe_reloaded_user_text')), ENT_COMPAT, 'UTF-8');
if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'))
	$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($message);
echo "$message<br>
<h2>$txt_subscr_manage_hw</h2>";
?>

<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" id="post_list_form" name="post_list_form" onsubmit="if(this.sra[0].checked) return confirm('<?php _e('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded') ?>')">
<fieldset style="border:0">
<?php
	$subscriptions = $wp_subscribe_reloaded->get_subscriptions('email', 'equals', $email, 'dt', 'DESC');
	if (is_array($subscriptions) && !empty($subscriptions)){

		$current_user = wp_get_current_user();

	    	#echo 'Username: ' . $current_user->user_login . '<br />';
    		#echo 'User email: ' . $current_user->user_email . '<br />';
    		#echo 'User first name: ' . $current_user->user_firstname . '<br />';
    		#echo 'User last name: ' . $current_user->user_lastname . '<br />';
    		$displayname = $current_user->display_name;
                $avatar = get_avatar( $email, 45 );

                global $region;
                $urllang = lhg_get_lang_url_from_region( $region );
                if ($urllang != "") $urllang = "/".$urllang;


                if ( is_user_logged_in() )
		if ( $lang != "de" ) {
                        $url_public_profile_txt = "http://www.linux-hardware-guide.com".$urllang."/hardware-profile/user".get_current_user_id();
                	$url_public_profile = $urllang."/hardware-profile/user".get_current_user_id();
                }
		if ( $lang == "de" ) {
                	$url_public_profile_txt = "http://www.linux-hardware-guide.de/hardware-profile/user".get_current_user_id();
                	$url_public_profile = "/hardware-profile/user".get_current_user_id();
                }

                echo '<div class="hwprofile-avatar">'.$avatar.'</div>';
		if ($displayname != "") echo '<div id="subscribe-reloaded-displayname">'.__('<strong>'.$txt_subscr_name.'</strong>','subscribe-reloaded').': '.$displayname.'</div>';
		echo '<div id="subscribe-reloaded-email-p">'.__('<strong>'.$txt_subscr_email.'</strong>','subscribe-reloaded').': '.$email.'</div>';
		if ($url_public_profile != "") echo '<div id="subscribe-reloaded-url-p">'.__('<strong>'.$txt_subscr_pub_hw_prof.'</strong>','subscribe-reloaded').': <a href="'.$url_public_profile.'">'.$url_public_profile_txt.'</div>';
		echo '<p id="subscribe-reloaded-legend-p">'.__($txt_subscr_legend.'', 'subscribe-reloaded').'</p>';
		//echo '<ul id="subscribe-reloaded-list">';


                echo '<table id="registration">';
                echo '<tr id="header">

                <td id="col1"></td>

                <td id="title-colhw">'.$txt_subscr_article.'</td>

                <td id="col2" width="13%"><nobr>'.$txt_subscr_rating.'</nobr></td>

                <td id="col4">'.$txt_subscr_modus.'</td>

                <td id="col2">'.$txt_subscr_regist_date.'</td>




                </tr>';
                foreach($subscriptions as $a_subscription){
                        $comment_excerpt = "";

			$permalink = get_permalink($a_subscription->post_id);
			$title = translate_title( get_the_title($a_subscription->post_id) );
                        $art_image=get_the_post_thumbnail( $a_subscription->post_id, array(55,55) );

                        $PID = $a_subscription->post_id;
                        $UID = get_current_user_id();

	                $searchargs = array(
			'status' => 'approve',
                        'user_id'=> $UID,
			'post_id' => $PID // use post_id, not post_ID
			);
			$comments = get_comments($searchargs);
			foreach($comments as $comment) :
				//$comment_excerpt = $comment->comment_content;
                                //echo "CID: $comment->comment_ID";
                                $CID = $comment->comment_ID;
                                $comment_found = get_comment($CID);

                                //shorten comment
                                if ( strlen($comment_found->comment_content) > 310){
	                                $comment_excerpt = substr($comment_found->comment_content,0,300)."...";
                                }else{
	                                $comment_excerpt = $comment_found->comment_content;
                                }
                                // ToDo: break, if rated comment
			endforeach;
                        //echo "<br>-B-";


			$deact='<span class="active">'.$txt_subscr_active.'</span>';
			if (strpos($a_subscription->status,"C") != false)
                         $deact='<span class="warning">'.$txt_subscr_inactive.'</span>';


			//echo "<li><label for='post_{$a_subscription->post_id}'><input type='checkbox' name='post_list[]' value='{$a_subscription->post_id}' id='post_{$a_subscription->post_id}'/> <span class='subscribe-column-1'>$a_subscription->dt</span> <span class='subscribe-separator subscribe-separator-1'>&mdash;</span> <span class='subscribe-column-2'>{$a_subscription->status}</span> <span class='subscribe-separator subscribe-separator-2'>&mdash;</span> <a class='subscribe-column-3' href='$permalink'>$title</a></label></li>\n";
			echo "<tr id=\"regcont\">


                        <td id=\"col1\">
                        <center><label id=\"registration\" for='post_{$a_subscription->post_id}'>
                        <input type='checkbox' name='post_list[]' value='{$a_subscription->post_id}' id='post_{$a_subscription->post_id}'/>
                        </center>
                        </td>";


                        // list HW entry
			$rating=the_ratings_results($a_subscription->post_id,0,0,0,10);

                        $commenttext = "";
                        if ($comment_excerpt != "") $commenttext='<div class="hwprofile-cite"><i>"'.wp_strip_all_tags($comment_excerpt).'"</i> <br>(<a href="'.$permalink.'#comment-'.$CID.'">'.$txt_subscr_more.'</a> - <a href="/wp-admin/comment.php?action=editcomment&c='.$CID.'">'.$txt_subscr_edit_comment.'</a>)</div>';

                        echo "
                        <td id=\"col-hw\"> <a class='subscribe-column-3' href='$permalink'>$art_image</a>

                        ".'<div class="subscribe-hwtext"><div class="subscribe-hwtext-span"><a href='.$permalink.'>'.$title.'</a><br>'.$rating.'<br>'.$commenttext.'</div></div></label>
                        
                        </td>';

                        //<td id=\"col4\">
                        //<span class='subscribe-column-2'>$deact<br>({$a_subscription->status})</span>
                        //</td>


                        // --- show user rating
                        $postid = $a_subscription->post_id;
                        $userid = $UID;
                        $ratingID = lhg_get_rating_id_by_user_and_post ($userid, $postid);
                        //echo "PID: $postid, UID: $userid, RID: "; print_r($ratingID);
                        $myrating = lhg_get_rating_by_rating_id ( $ratingID );

                        if ($myrating != "n.a.")
                        echo "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>$myrating / 5<br>(<nobr><a href=\"/wp-admin/comment.php?action=editcomment&c=$CID\">".$txt_subscr_edit_rating."</a></nobr>)</span>
                        </td>";

                        if ($myrating == "n.a.")
                        echo "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>".$txt_subscr_notrated."</span>
                        </td>";



			echo "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>$deact</span>
                        </td>";

                        $registration_date=$a_subscription->dt;
                        list ($registration_date, $registration_time) = explode(" ",$registration_date);
                        echo "
                        <td id=\"col2\">
                        <span class='subscribe-column-1'><center>$registration_date</center></span>
                        </td>



                        </tr>\n";
		}
                echo "</table>";
		//echo '</ul>';
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

	}
	else{
		echo '<p>'.__('No subscriptions match your search criteria.', 'subscribe-reloaded').'</p>';
	}
?>
</fieldset>
</form>
<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
?>