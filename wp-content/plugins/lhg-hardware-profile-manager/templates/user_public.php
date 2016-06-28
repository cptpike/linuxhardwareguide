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
global $txt_subscr_language;
global $txt_subscr_regdate;
global $txt_subscr_numart;
global $txt_subscr_numcom;
global $txt_subscr_public_hw_profile;
global $txt_subscr_lat_sub;
global $txt_subscr_rank;
global $txt_subscr_nextpromo;  //Next promotion
global $txt_subscr_nohw;

// Avoid direct access to this piece of code
if (!function_exists('add_action')){
	header('Location: /');
	exit;
}

global $wp_subscribe_reloaded;

ob_start();

//extract user id
$url     = ((empty($_SERVER['HTTPS'])) ? 'http' : 'https') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$pieces  = parse_url($url);
$urlpath = $pieces['path'];
//echo "PATH: $urlpath";
$hwprofpos   = strpos($urlpath,"/hardware-profile/user");
$url_user_id = (int)substr($urlpath,$hwprofpos+22);


# Support for Global UID (guid) instead of UID
# needed to see (simplified) user profiles from other servers
if ( $show_guser_profile == 1) {
	$hwprofpos   = strpos($urlpath,"/hardware-profile/guser");
	$guid = (int)substr($urlpath,$hwprofpos+23);
	$url_user_id =  lhg_get_uid_from_guid( $guid );
	#error_log("GUID $guid instead of UID $url_user_id");

}

//echo "UID: $url_user_id";


//check if user exists of fail;
$check = get_userdata( $url_user_id );

if ($check == false) {
        # user not available on local server. Have to rely on data from transverse server

        #return "Profile does not exist";
        $transverse_profile = 1;
}else{
//        echo "User exists<br>";
}

//echo "Public Hardware Profile<br>";

$public_user_ID = $url_user_id;


/*
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
*/
?>

<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" id="post_list_form" name="post_list_form" onsubmit="if(this.sra[0].checked) return confirm('<?php _e('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded') ?>')">
<fieldset style="border:0">
<?php

if ($public_user_ID != 0) {
                # we have user data
        	$user = get_userdata( $public_user_ID );
                $email = $user->user_email;
	        $karma = lhg_get_karma( $public_user_ID ); //$num_com * 3 + $num_art * 50;
                $displayname = $user->display_name;
                $avatar = get_avatar( $public_user_ID , 96 );
}

if ($public_user_ID == 0) {
                # we are limited to guser data
        	$user_tmp = lhg_get_userdata_guid($guid);
                $user=$user_tmp[0];
                $email = $user->emails;
		$karma = $user->karma_com + $user->karma_de; //$num_com * 3 + $num_art * 50;
        	$displayname = $user->user_nicename;
                $avatar = $user->avatar;
                # make it bigger!
                $avatar = str_replace("s=40","s=96",$avatar);
                $avatar = str_replace("s%3D40","s%3D96",$avatar);
                $avatar = str_replace("avatar-40","avatar-96",$avatar);
                $avatar = str_replace("height='40'","height='96'",$avatar);
                $avatar = str_replace("width='40'","width='96'",$avatar);
                #error_log("Avatar: $avatar");
                #var_dump($user);
}


        //Get user Language
        $user_tmp = lhg_get_userdata_guid($guid);
        $user=$user_tmp[0];

        # Check if user has .de account -> add flag
        if ($user->wpuid != 0) $user_language_txt = lhg_get_locale_from_id ( $public_user_ID );
        if ($user->wpuid != 0) $user_language_flag= lhg_show_flag_by_lang ( $user_language_txt );

        # Check if user has .de account -> add flag
        if ($user->wpuid_de != 0) $user_language_flag .= " ".lhg_show_flag_by_lang( "de" )." ";



        //get user registration date
        $regdate = date("d. M Y", strtotime(get_userdata( $public_user_ID ) -> user_registered ) );

        //get number of contributed hardware articles
        $num_art = count_user_posts ( $public_user_ID );

        //get number of comments
        global $wpdb;
    	$where = 'WHERE comment_approved = 1 AND user_id = ' . $public_user_ID ;
    	$num_com = $wpdb->get_var(
    	    "SELECT COUNT( * ) AS total
	    FROM {$wpdb->comments}
	    {$where}");

        //first steps to implement karma
        //$karma = $num_com * 3 + $num_art * 50;
        #if (function_exists('cp_getPoints'))
        #$karma = cp_getPoints( $public_user_ID ); //$num_com * 3 + $num_art * 50;


	$subscriptions = $wp_subscribe_reloaded->get_subscriptions('email', 'equals', $email, 'dt', 'DESC');


	    	#echo 'Username: ' . $current_user->user_login . '<br />';
    		#echo 'User email: ' . $current_user->user_email . '<br />';
    		#echo 'User first name: ' . $current_user->user_firstname . '<br />';
    		#echo 'User last name: ' . $current_user->user_lastname . '<br />';


                $rank_level = lhg_get_rank_level( $public_user_ID );
                $karma_rank_total = lhg_get_karma_threshold ( $rank_level );
	        if (function_exists('cp_module_ranks_getRank'))
        	        $rank_txt = cp_module_ranks_getRank($public_user_ID);

                //if ($karma < 1000){ $karma_rank_total = 1000;  $rank_level = 2; }
                //if ($karma < 100) { $karma_rank_total = 100 ; $rank_level = 1; }



                $max_rank_levels = 7;
                $squares_full  = '<div class="square_full"></div>';
                $squares_empty = '<div class="square_empty"></div>';


                $squares = '<br><div class="squarebar">';
                $i=0;
                while ($i < $rank_level){
                        $squares .= $squares_full;
                        $i++;
                }

                while ($i < $max_rank_levels){
                        $squares .= $squares_empty;
                        $i++;
                }

                $squares .= '<br>'.$rank_txt.'</div>';

                echo '<div class="hwprofile-avatar" title="'.$tooltip.'">'.$avatar.$squares.'</div>';
                echo '<div class="hwprofile-karma">Karma: <br><div class="hwprofile-karma-num">'.$karma.'</div></div>';

		if ($displayname != "") echo '<div id="subscribe-reloaded-displayname">'.__('<strong>'.$txt_subscr_name.'</strong>','subscribe-reloaded').': '.$displayname.'</div>';
                if ($show_email_address)
                echo '<div id="subscribe-reloaded-email-p">'.__('<strong>Email</strong>','subscribe-reloaded').': '.$email.'</div>';

                if ($public_user_ID != 0) echo '<div id="subscribe-reloaded-email-p">'.$txt_subscr_regdate.': '.$regdate.'</div>';
                echo '<div id="subscribe-reloaded-email-p">'.$txt_subscr_language.': '.$user_language_flag.'</div>';

                //Rank+Title info
                if (function_exists('cp_getAllPoints'))
                                $allpoints=cp_getAllPoints(0,array("admin","test"),0);
                $sumpoints = 0;
                $highestpoints = 0;

                foreach ($allpoints as $allpoint) {

                        if ( $allpoint['points'] > $highestpoints ) $highestpoints = $allpoint['points'];
                        $sumpoints += $allpoint['points'];

                        /*
                        $i++;
                        print "<br> $i: ";
                        print_r($allpoint);
                        print "<br>".$allpoint['points'];
                        //print "PTS: ". $allpoint => 'points';
                        */

                /*
                foreach ($subArray as $id=>$value) {
		    $sumArray[$id]+=$value;
		  }
                */
                }

                //echo "Allpoints: $sumpoints";

                echo '<br><div id="subscribe-reloaded-email-p">'.$txt_subscr_rank.': '.$rank_txt.' (#'.$rank_level.')</div>';

        	echo '<div class="rateline" style="border: 0px solid #000;"><div style="float: left;">'.$txt_subscr_nextpromo.':&nbsp; </div> <div class="outerbox" style="background-color: #eee; width: 80px; float: left; margin: 4px 0px;"><div class="box" style="border: 0px solid #088; background-color: #2b8fc3; height: 8px; width: '.(100*$karma/$karma_rank_total).'%;" ></div></div> &nbsp;('.$karma.' of '.$karma_rank_total.' points)</div><br clear="all">';



		echo '</fieldset></form>';




#  -----  Section Hardware Profile
# We do not have the following data because we are on a transverse server. We therefore skip this output.
if ($public_user_ID != 0) {

	echo '<br><form id="post_list_form"><fieldset>';
        echo "<h2>$txt_subscr_public_hw_profile</h2>";


        echo '<p id="subscribe-reloaded-legend-p">'.__($txt_subscr_legend.'', 'subscribe-reloaded').'</p>';
	//echo '<ul id="subscribe-reloaded-list">';

	if (is_array($subscriptions) && !empty($subscriptions)){

                echo '<table id="registration">';
                echo '<tr id="header">

                <td id="col1"></td>

                <td id="title-colhw">'.$txt_subscr_article.'</td>';

//                <td id="col4">'.$txt_modus.'</td>

                echo '
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


                        <td id=\"col1\">&nbsp;";
//                        <center><label id=\"registration\" for='post_{$a_subscription->post_id}'>
//                        <input type='checkbox' name='post_list[]' value='{$a_subscription->post_id}' id='post_{$a_subscription->post_id}'/>
//                        </center>
			echo "</td>";


                        // list HW entry
			$rating=the_ratings_results($a_subscription->post_id,0,0,0,10);

                        $commenttext = "";
                        if ($comment_excerpt != "") $commenttext='<div class="hwprofile-cite"><i>"'.wp_strip_all_tags($comment_excerpt).'"</i> (<a href="'.$permalink.'#comment-'.$CID.'">'.$txt_subscr_more.'</a>)</div>';

                        echo "
                        <td id=\"col-hw\"> <a class='subscribe-column-3' href='$permalink'>$art_image</a>

                        ".'<div class="subscribe-hwtext"><div class="subscribe-hwtext-span"><a href='.$permalink.'>'.$title.'</a><br>'.$rating.'<br>'.$commenttext.'</div></div></label>
                        
                        </td>';


/*
echo "
                        <td id=\"col-hw\"> <a class='subscribe-column-3' href='$permalink'>$art_image</a>

                        ".'<div class="subscribe-hwtext"><span class="subscribe-hwtext-span"><a href='.$permalink.'>'.$title.'</a><br>'.$rating.'</span></div></label>
                        </td>';

                        //<td id=\"col4\">
                        //<span class='subscribe-column-2'>$deact<br>({$a_subscription->status})</span>
                        //</td>
*/

			/*
                        echo "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>$deact</span>
                        </td>";
                        */

                        $registration_date=$a_subscription->dt;
                        list ($registration_date, $registration_time) = explode(" ",$registration_date);
                        echo "
                        <td id=\"col2\">
                        <span class='subscribe-column-1'><center>$registration_date</center></span>
                        </td>



                        </tr>\n";
		}
                echo "</table>";

	}


/*
		query_posts('author=1&order=ASC&showposts=-1');
	 if(have_posts()) : while(have_posts()) : the_post();
	  	echo '<a href="'.the_permalink().'">'.the_title().'</a>';
	  	endwhile;
         endif;
*/

                /*
		//echo '</ul>';
		echo '<p id="subscribe-reloaded-select-all-p"><a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=true}else{for(var i=0;i<c;i++){t[i].checked=true}};return false;">'.__($txt_select_all,'subscribe-reloaded').'</a> ';
		echo '<a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=!t.checked}else{for(var i=0;i<c;i++){t[i].checked=false}};return false;">'.__($txt_select_inv,'subscribe-reloaded').'</a></p>';


		echo '<table id="subscribe-actions-table"><tr><td id="subscribe-actions" ><p id="subscribe-reloaded-action-p">'.__($txt_action.':<br>','subscribe-reloaded').'
			</td><td id="subscribe-actions">
                        <div class="sub-selector">
                          <input type="radio" name="sra" value="delete" id="action_type_delete" />
                            <label id="registration2" for="action_type_delete">'.__($txt_delete,'subscribe-reloaded').'</label>
                          </div> 
			</td>

                        <td id="subscribe-actions"><div class="sub-selector">
                          <input type="radio" name="sra" value="suspend" id="action_type_suspend"  />
                          <label id="registration2" for="action_type_suspend">'.__($txt_suspend,'subscribe-reloaded').'</label> </div>
                        </td>

                        <!-- td id="subscribe-actions"><div class="sub-selector">
                          <input type="radio" name="sra" value="force_r" id="action_type_force_y" />
                          <label id="registration2" for="action_type_force_y">'.__($txt_reply_only,'subscribe-reloaded').'</label> </div>
                        </td -->

                        <td id="subscribe-actions"><div class="sub-selector">
                          <input type="radio" name="sra" value="activate" id="action_type_activate" checked="checked" />
                          <label id="registration2" for="action_type_activate">'.__($txt_activate,'subscribe-reloaded').'</label> </div>
                        </td>

                      </tr></table>
                      </p>';

		echo '<p id="subscribe-reloaded-update-p"><button type="submit" class="subscribe-form-button" value="'.__($txt_update,'subscribe-reloaded').'" />'.__($txt_update,'subscribe-reloaded').'&nbsp;<i class="icon-arrow-right icon-button"></i></button><input type="hidden" name="sre" value="'.urlencode($email).'"/></p>';
                */
	else{
	        # We do not have the following data because we are on a transverse server. We therefore skip this output.
        	if ($public_user_ID != 0) echo '<p>'.__($txt_subscr_nohw, 'subscribe-reloaded').'</p>';
	}

        print "</fieldset>
        </form>";
}


# ---- Section last hardware entries
if ($public_user_ID != 0) {
	print '<br><form id="post_list_form">';

                echo "<h2>".$txt_subscr_lat_sub."</h2>";

                echo '<div id="subscribe-reloaded-email-p">'.$txt_subscr_numart.': '.$num_art.'</div>';


        	$authors_posts = get_posts( array( 'author' => $public_user_ID, 'posts_per_page' => 10 ) );

	    	$output = '<ul>';
	    	foreach ( $authors_posts as $authors_post ) {
                        $i++;
	        	$output .= '<li><a href="' . get_permalink( $authors_post->ID ) . '">' . apply_filters( 'the_title', $authors_post->post_title, $authors_post->ID ) . '</a></li>';
		}
		$output .= '</ul>';

echo $output;


echo '<div id="subscribe-reloaded-email-p">'.$txt_subscr_numcom.': '.$num_com.'</div>';

echo' </form>';
}

$output = ob_get_contents();
ob_end_clean();
return $output;



?>