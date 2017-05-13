<?php

# User management rules
# In addition to standard Wordpress permissions, karma points define what users can and cannot do

define ('LHG_KARMA_edit_posts', 50);
define ('LHG_KARMA_delete_posts', 50);
define ('LHG_KARMA_upload_files', 50);
define ('LHG_KARMA_publish_posts', 300);
define ('LHG_KARMA_manage_categories', 500);
define ('LHG_KARMA_edit_published_posts', 500); # e.g. needed for translators
define ('LHG_KARMA_edit_others_posts', 500); # e.g. needed for translators

define ('LHG_KARMA_POINTS_hwscan', 50); # How many points for an uploaded hardware scan

#
# include further sub-components of the user_management
#
# manage authorship of articles
require_once(plugin_dir_path(__FILE__).'/lhg_user_management_authorship.php');


# show comment menu for all users
#add_menu_page('edit-comments.php');

#apply_filters ( 'map_meta_cap', $caps, $cap, $user_id, $args );
add_filter ( 'map_meta_cap', 'lhg_check_permissions', 10, 4 );
#add_filter ( 'map_meta_cap', 'lhg_check_permissions_manufacturers', 10, 4 );
function lhg_check_permissions( $caps, $cap, $user_id, $args) {

        # all of this makes only sense if user is logged in
        if ( ($user_id == 0) or ($user_id == "") ) return $caps;

        $caps_old = $caps;

        # check if this is a manufacturer
        $caps = lhg_check_permissions_manufacturers( $caps, $cap, $user_id, $args );
        #error_log("CAPS: $caps v $caps_old");
        if ($caps !== false ) return $caps;
        $caps = $caps_old;


        # check if users own this hardware (i.e. uploaded corresponding scan)
        if ($user_id > 0)
        $user_owns_hardware = lhg_check_user_owns_hardware( $user_id, get_the_ID() );
        #error_log("HW associated? $user_owns_hardware");
        #if ($return === true ) return array();  # article belongs to user


	#$karma = cp_getPoints( $user_id ); //get karma points
	$karma = lhg_get_karma( $user_id ); // get transversal karma (= sum of all servers)

	#error_log("User $user_id permission check cap: $cap - caps:".join(",",$caps) );


        # user can edit own posts
        if ( ( 'edit_post' == $cap )  && in_array( 'edit_posts', $caps) ) {  # needed for comment activation
                #error_log ("Test ongoign");

        	if ( $user_owns_hardware ) {
			$caps = array();
                } elseif ( $karma < LHG_KARMA_edit_posts ) {
                	$caps[] = 'activate_plugins';
                } else {
			$caps = array();
                        #error_log("Enough points test nr 1. Let go!");
        	}
                return $caps;


	}

        # user can edit other (and own) posts
        if ( ( ( 'edit_post' == $cap )  && in_array( 'edit_others_posts', $caps) ) or
             ( ( 'edit_post' == $cap )  && in_array( 'edit_posts', $caps) ) or   # needed for comment activation
             ( ( 'edit_post' == $cap )  && in_array( 'edit_published_posts', $caps) ) or   # edit translated posts
             ( ( 'edit_others_posts' == $cap )  && in_array( 'edit_others_posts', $caps) ) ){

        	if ( $user_owns_hardware ) {
			$caps = array();
                } elseif ( $karma < LHG_KARMA_edit_others_posts ) {
                	$caps[] = 'activate_plugins';
                } else {
			$caps = array();
                        #error_log("Enough points test nr 1. Let go!");
        	}
                return $caps;
	}


        if ( 'edit_posts' == $cap ) {
                #error_log("User wants to edit post - caps:".join(",",$caps) );
                if ( $user_owns_hardware ) {
			$caps = array();
                } elseif ( $karma < LHG_KARMA_edit_posts ) {
                        #error_log("Not enough points!");
                	$caps[] = 'activate_plugins';
                }else{
                        #error_log("Enough points. Let go!");
                	#$caps[] = 'read';
			$caps = array();
        	}
                return $caps;
	}

        if ( 'delete_posts' == $cap ) {
                if ( $karma < LHG_KARMA_delete_posts ) {
                	$caps[] = 'activate_plugins';
                }else{
                	#$caps[] = 'read';
			$caps = array();
			#$caps[] = '';
        	}
                return $caps;
	}

        if ( 'upload_files' == $cap ) {
                if ( $karma < LHG_KARMA_upload_files ) {
                	$caps[] = 'activate_plugins';
                }else{
                	#$caps[] = 'read';
			$caps = array();
        	}
                return $caps;
	}

        if ( 'publish_posts' == $cap ) {
                #error_log("Check publish_posts");
                if ( $karma < LHG_KARMA_publish_posts ) {
                	$caps[] = 'activate_plugins';
                }else{
        		$caps = array();
        	}
                return $caps;
	}

        if ( 'edit_published_posts' == $cap ) {
                if ( $user_owns_hardware ) {
			$caps = array();
                }elseif ( $karma < LHG_KARMA_edit_published_posts ) {
                	$caps[] = 'activate_plugins';
                }else{
			$caps = array();
        	}
                return $caps;
	}

        # category modificaitons should only be possible for admins
	#if ( 'manage_categories' == $cap ) {
        #        #error_log("User wants to edit cat - caps:".join(",",$caps) );
        #        if ( $karma < LHG_KARMA_manage_categories ) {
        #                #error_log("Not enough points!");
        #        	$caps[] = 'activate_plugins';
        #        }else{
        #                #error_log("Enough points. Let go!");
        #        	#$caps[] = 'read';
	#		$caps = array();
        #	}
        #        return $caps;
	#}

        #error_log("Unknown cap: $cap - caps:".join(",",$caps) );

	return $caps;
}


#
##### Dashboard
#

add_action('wp_dashboard_setup', 'lhg_dashboard_widgets');
function lhg_dashboard_widgets() {
	global $wp_meta_boxes;
        global $txt_cp_title;
        global $txt_lhgdb_welcome;

	$logo_url = sprintf( 'http://www.linux-hardware-guide.de/avatars/lhg60-avatar.png' , is_ssl()? 's':'' );

	wp_add_dashboard_widget('lhg_greeting_widget', '<img src="'.$logo_url.'" style="height: 1.2em; margin-right:8px; margin-bottom: -3px;" >'.$txt_lhgdb_welcome, 'lhg_greeting_widget');
	wp_add_dashboard_widget('lhg_user_overview_widget', $txt_cp_title, 'lhg_user_overview_widget');
	wp_add_dashboard_widget('lhg_open_tasks_widget', 'Open Tasks', 'lhg_open_tasks_widget');
}

function lhg_greeting_widget() {

                        global $txt_twt_flattr;
                        global $txt_twt_paypal;
                        global $txt_twt_statistic;
			global $txt_twt_userid;
			global $txt_twt_hwnum;
			global $txt_twt_commnum;
			global $txt_twt_payment;
			global $txt_twt_actnum;
			global $txt_twt_pending;
			global $txt_twt_payd;
			global $txt_twt_maintext1;
			global $txt_twt_maintext2;
                        global $donation;
                        global $txt_lhgdb_karmapoints;
                        global $txt_lhgdb_numhwscan;
                        global $txt_lhgdb_numhwscans;
                        global $txt_lhgdb_karmadescr;
                        global $txt_lhgdb_karmadescr_end;
                        global $txt_lhgdb_karma_howto;
                        global $txt_lhgdb_karma_1;
                        global $txt_lhgdb_karma_2;
                        global $txt_lhgdb_karma_3;
                        global $txt_lhgdb_karma_4;


			#$args = array(
			#	'url'			=> $this->_feed_url,
			#	'items'			=> '3',
			#	'show_date'		=> 1,
			#	'show_summary'	=> 1,
			#);
			$logo_url = sprintf( 'http://www.linux-hardware-guide.de/avatars/lhg60-avatar.png' , is_ssl()? 's':'' );
			$icon = includes_url('images/rss.png');
	                $avatar = get_avatar( $public_user_ID , 96 );

			echo '<div class="rss-widget">';
			#echo '<img class="alignright"  src="' . esc_url_raw( $logo_url ) . '" />';
	                echo '<div class="hwprofile-avatar alignright" title="Avatar" style="padding:30px 0 5px 10px;">'.$avatar.'</div>';

                        //echo "<h3>Willkommen bei Linux-Hardware-Guide.de</h3>";


                        global $current_user;
      			get_currentuserinfo();
                        $userid = $current_user->ID;


                        // Check for Flattr-ID
                        $FL_USERNAME=get_the_author_meta( 'flattr-id', $userid);
                        $FL_PAYPAL=get_the_author_meta( 'paypal', $userid);

                        /*
                        if ($FL_USERNAME == "") echo '
                        <div class="error"><p><strong>'.$txt_twt_flattr.'
                        </strong></div>
                        ';
                        if ($FL_PAYPAL == "") echo '
                        <div class="error"><p><strong>'.$txt_twt_paypal.'
                        </strong></div>
                        ';
                        */

                        # Number of owned posts
			$user_post_count = count_user_posts( $userid );

                        # Number of uploaded hardware scans
                        $guserids = lhg_get_current_users_guids();
                        #error_log("DE: ".$guserids["de"]." COM: ".$guserids["com"]);

                        if ($guserids["de"] > 0) {
                                # if uid = 0 all unassigned scans would be counted
	                        global $lhg_price_db;
        			$sql = "SELECT COUNT(id) FROM `lhgscansessions` WHERE wp_uid_de = \"".$guserids["de"]."\"";
        			$num_hwscans_de = $lhg_price_db->get_var($sql);
                        }

                        if ($guserids["com"] > 0) {
                                # if uid = 0 all unassigned scans would be counted
	                        global $lhg_price_db;
        			$sql = "SELECT COUNT(id) FROM `lhgscansessions` WHERE wp_uid = \"".$guserids["com"]."\"";
        			$num_hwscans_com = $lhg_price_db->get_var($sql);
                        }

                        $num_hwscans = $num_hwscans_de + $num_hwscans_com;

                        #Fallback
                        if (($num_hwscans_de + $num_hwscans_com) == 0 )  {
	                       global $lhg_price_db;
        	       	       $sql = "SELECT COUNT(id) FROM `lhgscansessions` WHERE wp_uid = \"".$userid."\"";
        		       $num_hwscans = $lhg_price_db->get_var($sql);
                        }



		        if (function_exists('cp_getPoints'))
		        $karma = lhg_get_karma( $userid );
		        #$karma = cp_getPoints( $userid ); //$num_com * 3 + $num_art * 50;

                        $donation_target = get_user_meta($userid,'user_donation_target',true);
                        if ($donation_target == "") $donation_target = 1;
                        $donation_target_text = $donation[$donation_target]["Name"];

			$args = array(
				'user_id' => $userid, // use user_id
		        	'count' => true //return only the count
			);
			$comments = get_comments($args);
			$user_post_count = count_user_posts( $userid );

                        $txt_numhwscans = $txt_lhgdb_numhwscans; #"uploaded hardware scans";
                        if ($num_hwscans == 1) $txt_numhwscans = $txt_lhgdb_numhwscan; #"uploaded hardware scan";

                       echo '
                       <div class="inside">
                       <div class="table table_content">
			<p class="sub"><h2>'.$txt_twt_statistic.'</h2></p>
			<table>
			   <tr class="first"><td class="first b b-posts">
                             <strong>'.$karma.'</strong></a></td><td class="t posts">'.$txt_lhgdb_karmapoints.'</td></tr>

                           <tr><td class="first b b-cats">
                             <strong>'.$num_hwscans.'</strong> </td><td class="t cats">'.$txt_numhwscans.'</td></tr>

			   <tr class="first"><td class="first b b-posts">';

                           if (current_user_can('edit_posts') ) print '<a href="edit.php">';
                           	print '<strong>'.$user_post_count.'</strong>';
			   if (current_user_can('edit_posts') ) print '</a>';

                           print '</td><td class="t pages">';

                           if (current_user_can('edit_posts') ) print '<a href="edit.php">';
                           print $txt_twt_hwnum;
                           if (current_user_can('edit_posts') ) print '</a>';

                           print '</td></tr>

                           <tr><td class="first b b-cats">
                             <a href="edit-comments.php"><strong>'.$comments.'</strong></a></td><td class="t cats"><a href="edit-comments.php">'.$txt_twt_commnum.'</a></td></tr>
                             <p>

			</table>
                        <p>
                             '.$txt_lhgdb_karmadescr.
                             	$donation_target_text.'. '.
                               $txt_lhgdb_karmadescr_end.'

		       </div>
                       </div>';

                       //echo "<h2>Statistik:</h2>";
		       //echo '<p style="border-top: 1px solid #CCC; padding-top: 10px; font-weight: bold;">';


                       echo '
                       <div class="inside">
                       <div class="table table_content">
			<p class="sub"><h2>'.$txt_lhgdb_karma_howto.'</h2></p>
                             '.$txt_lhgdb_karma_1.'
                             <br>
                             <tt>'.do_shortcode("[lhg_scancommand ]").'</tt>
                             <p>
                             '.$txt_lhgdb_karma_2.'
                             <p>';

                        if ($karma < 50) print $txt_lhgdb_karma_3.' '.LHG_KARMA_edit_posts.' '.$txt_lhgdb_karma_4;

		       print '</div>
                       </div>';


                       echo'
                       <div class="inside">
                       <div class="table table_content">
                       </div>
                       </div>';

			echo "</p>";
			echo "</div>";

}

function lhg_open_tasks_widget() {
                       echo '
                       <div class="inside">
                       <div class="table table_content">
			<p class="sub"><h2>'.$txt_twt_statistic.'</h2></p>
			<table>
                                <tr><td>
                                List of open tasks
                                </td></tr>
                        </table>
                        </div>
                        </div>';
}

function lhg_user_overview_widget() {
        #print "User statistics".

        print do_shortcode("[lhg_donation_table]");


}


add_action('wp_dashboard_setup', 'lhg_add_scan_points');
#add_action('test', 'lhg_add_scan_points');
function lhg_add_scan_points() {
        # Check if scans were uploaded and points need to be given to users

        # when to check?
        # first login of user
        # login on hardware scan page
        # each login
        # ???

        global $lhg_price_db;
        global $lang;
        $sql = "SELECT * FROM `lhgscansessions` ORDER BY id DESC LIMIT 300";
        $results = $lhg_price_db->get_results($sql);

        foreach($results as $result){
                #print "ID: ".$result->id;
                #print " SID: ".$result->sid;
                #print " UID: ".$result->uid;
                #print " WPUID: ".$result->wp_uid;
                #print " EM: ".$result->email;
                #print "<br>";


                if  ( ($lang != "de") && ($result->wp_uid != 0 ) ) {
                        # user was identified by its ID
                        # error_log("UID exists but no karma");
                        lhg_link_hwscan( $result->wp_uid, $result->sid);
                } elseif  ( ($lang == "de") && ($result->wp_uid_de != 0 ) ) {
                        # user was identified by its .de ID
                        lhg_link_hwscan( $result->wp_uid_de, $result->sid);

		}
                else{
                        # no linked internal uid found. Check if we can link results to an account

                        # check 1: email matching?
	                if  ($result->email != "") {
                                # email known?
                                $user = get_user_by( 'email', $result->email );
                                if ($user->ID != ""){
                                        lhg_link_hwscan( $user->ID, $result->sid);
                                        lhg_update_userdb_by_uid( 'email' , $user->ID , $result->email );
				}
                                # maybe username was entered instead of email
                                $user = get_user_by( 'user_login', $result->email );
                                if ($user->ID != ""){
                                        lhg_link_hwscan( $user->ID, $result->sid);
				}

			}

                        # check 3: uid already linked for other scans?
	                if  ($result->uid != "") {


			}
		}
	}

        #var_dump ($result);

        #error_log("First shot -> needs to be improved");

        return;

        $uid = get_current_user_id();
        $points = 5;

	cp_points('addpoints', $uid, $points, 'Test description');

}

function lhg_link_hwscan( $uid, $sid ) {

        #error_log("Create link for $uid with $sid");
        global $lang;
        global $lhg_price_db;

        # check if points were already stored in DB
        $sql = "SELECT id FROM `lhgtransverse_points` WHERE comment LIKE '%".$sid."%'";
        $result = $lhg_price_db->get_var($sql);

        if ($result == "") {

		if ($lang != "de") cp_points('addpoints', $uid, LHG_KARMA_POINTS_hwscan , 'Hardware scan added <a href="/hardware-profile/scan-'.$sid.'">'.$sid.'</a>');
		if ($lang == "de") cp_points('addpoints', $uid, LHG_KARMA_POINTS_hwscan , 'Hardware Scan hinzugefügt <a href="/hardware-profile/scan-'.$sid.'">'.$sid.'</a>');
        	#error_log("Lang: $lang -> Points $sid added for $uid");

	}else{
                #error_log("Points $sid for $uid already existing");
        }


        $sql = "UPDATE `lhgscansessions` SET `karma`=  \"linked\" WHERE sid = \"$sid\"";
    	$result = $lhg_price_db->query($sql);

        # ToDo: if wp_uid is already set (e.g. by scan script), it is set again. This seems unnecessary.
        if ($lang != "de") $sql = "UPDATE `lhgscansessions` SET `wp_uid` = \"".$uid."\" WHERE sid = \"$sid\"";
        if ($lang == "de") $sql = "UPDATE `lhgscansessions` SET `wp_uid_de` = \"".$uid."\" WHERE sid = \"$sid\"";
    	$result = $lhg_price_db->query($sql);

}

add_action('wp_dashboard_setup', 'lhg_user_linking');
add_action('wp_login', 'lhg_user_linking');
#add_action('test', 'lhg_add_scan_points');
function lhg_user_linking() {
        global $lhg_price_db;
        global $lang;

        # transverse linking. Look if email is also used for accounts on other servers
        $current_user = wp_get_current_user();
        $email = $current_user->user_email;
        $cuid = $current_user->ID;

        if ($lang != "de")
        if (email != "") {
                        # email found but no
                        #error_log("email & wpuid found: ".$result->email." ".$result->wp_uid );
                        lhg_update_userdb_by_uid( 'wpuid' , $cuid , $cuid );
	}

        if ($lang == "de")
        if (email != "") {
                        # email found but no
                        #error_log("email & wpuid found: ".$email." ".$cuid );
                        lhg_update_userdb_by_uid( 'wpuid_de' , $cuid , $cuid );
	}





        # Link users with available scans
        $sql = "SELECT * FROM `lhgscansessions` ORDER BY id DESC LIMIT 100";
        $results = $lhg_price_db->get_results($sql);

        foreach($results as $result){

                if ($lang != "de")
        	if  ( ($result->email != "") && ($result->wp_uid != 0) ){
                        # email found but no
                        #error_log("email & wpuid found: ".$result->email." ".$result->wp_uid );
                        lhg_update_userdb_by_uid( 'email' , $result->wp_uid , $result->email );
		}

                if ($lang == "de")
        	if  ( ($result->email != "") && ($result->wp_uid_de != 0) ){
                        # email found but no
                        #error_log("email & wpuid found: ".$result->email." ".$result->wp_uid );
                        lhg_update_userdb_by_uid( 'email' , $result->wp_uid_de , $result->email );
		}

	}

        # check for user ids that were added by scan server but not yet linked with karma
        # Not needed because already handled by lhg_get_scan_points
        #$sql = "SELECT * FROM `lhgscansessions` WHERE `wp_uid` != 0 and `karma` IS NULL";
        #$results = $lhg_price_db->get_results($sql);
        #
        #var_dump($results);
        #foreach($results as $result){
        #        error_log("not linked SID: $result->sid - $result->wp_uid");
	#}


}

#
# find all users for this quarter and update the values in priceDB
#
function lhg_update_karma_values( $type ) {
	global $lang;
        global $lhg_price_db;

	list($list_uid, $list_points) = cp_getAllQuarterlyPoints();

        $i=0;
        if (sizeof($list_uid) > 0)
	foreach($list_uid as $uid){
                #error_log("CP UID return: $uid");

                $user = get_userdata($uid);

        	if ( $user !== false )
                if ($uid != 12378){
                	$name = $user->nickname; #$user->first_name." ".$user->last_name;
                        $username = $user->user_login;
                	$points = $list_points[$i];
                        $avatar = get_avatar($uid, 40);
	                $user_language_txt = lhg_get_locale_from_id ( $uid );
        		$user_language_flag= lhg_show_flag_by_lang ( $user_language_txt );
			$total_karma = cp_getPoints( $uid ); //$num_com * 3 + $num_art * 50;
                        $donation_target = get_user_meta($uid,'user_donation_target',true);
                        if ($lang != "de") $user_language = get_user_meta($uid,'user_language',true);

                        error_log("User $username -> quarterly_karma: $points");


			# check if user exists
		        if ($lang != "de"){
				$sql = "SELECT id, karma_com, karma_quarterly_com, donation_target_com, user_nicename, language, username_com, emails FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";
		        }else{
				$sql = "SELECT id, karma_de, karma_quarterly_de, donation_target_de, user_nicename, username_de, emails  FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";
			}
		        $result = $lhg_price_db->get_results($sql);
                        #error_log( "UID: $uid");
                        #error_log("GUID: ".$result[0] ->id);
                        #var_dump($result);
                        #print "<br>";

                        if ($result[0] ->id == "") {
	                        # user not yet in priceDB, add
                                $guid = lhg_add_user_to_pricedb($uid);
                                if  ($lang != "de") lhg_update_userdb_by_guid("karma_com", $guid, $total_karma);
                                if  ($lang != "de") lhg_update_userdb_by_guid("karma_quarterly_com", $guid, $points);
                                if  ($lang != "de") lhg_update_userdb_by_guid("donation_target_com", $guid, $donation_target);
                                if  ($lang != "de") lhg_update_userdb_by_guid("language", $guid, $user_language);
                                if  ($lang != "de") lhg_update_userdb_by_guid("username_com", $guid, $username);
                                if  ($lang == "de") lhg_update_userdb_by_guid("karma_de", $guid, $total_karma);
                                if  ($lang == "de") lhg_update_userdb_by_guid("karma_quarterly_de", $guid, $points);
                                if  ($lang == "de") lhg_update_userdb_by_guid("donation_target_de", $guid, $donation_target);
                                if  ($lang == "de") lhg_update_userdb_by_guid("username_de", $guid, $username);
                                #error_log("A: $uid - $guid - ".$result->id);

                                lhg_update_userdb_by_guid("user_nicename", $guid, $name);
                                lhg_update_userdb_by_guid("avatar", $guid, $avatar);

			}else{
                        	# user in priceDB, check if update necessary
                                if ( ($lang != "de") && ($total_karma != $result[0]->karma_com) ) lhg_update_userdb_by_guid("karma_com", $result[0]->id, $total_karma);
                                if ( ($lang != "de") && ($points != $result[0]->karma_quarterly_com) ) lhg_update_userdb_by_guid("karma_quarterly_com", $result[0]->id, $points);
                                if ( ($lang != "de") && ($donation_target != $result[0]->donation_target_com) ) lhg_update_userdb_by_guid("donation_target_com", $result[0]->id, $donation_target);
                                if ( ($lang != "de") && ($user_language != $result[0]->language) ) lhg_update_userdb_by_guid("language", $result[0]->id, $user_language);
                                if ( ($lang != "de") && ($username != $result[0]->username_com) ) lhg_update_userdb_by_guid("username_com", $result[0]->id, $username);
                                if ( ($lang == "de") && ($total_karma != $result[0]->karma_de) ) lhg_update_userdb_by_guid("karma_de", $result[0]->id, $total_karma);
                                if ( ($lang == "de") && ($points != $result[0]->karma_quarterly_de) ) lhg_update_userdb_by_guid("karma_quarterly_de", $result[0]->id, $points);
                                if ( ($lang == "de") && ($donation_target != $result[0]->donation_target_de) ) lhg_update_userdb_by_guid("donation_target_de", $result[0]->id, $donation_target);
                                if ( ($lang == "de") && ($username != $result[0]->username_com) ) lhg_update_userdb_by_guid("username_de", $result[0]->id, $username);
                                if ($name != $result[0]->user_nicename) lhg_update_userdb_by_guid("user_nicename", $result[0]->id, $name);
                                if ($avatar != $result[0]->avatar) lhg_update_userdb_by_guid("avatar", $result[0]->id, $avatar);
                                if ( $user->user_email != $result[0]->emails ) lhg_update_userdb_by_guid("emails", $result[0]->id, $user->user_email);

                                # check if user was already set for other language
                                if ( ($lang == "de") && ($uid != $result[0]->wpuid_de) ) lhg_update_userdb_by_guid("wpuid_de", $result[0]->id, $uid);
                                if ( ($lang != "de") && ($uid != $result[0]->wpuid) ) lhg_update_userdb_by_guid("wpuid", $result[0]->id, $uid);


                                #error_log("NAME: $name");
                                #error_log("B Points: $points vs. ".$result[0]->karma_quarterly_com);

                        }
		}
        $i++; // points array counter

	}
}


# use user id to update entry
function lhg_update_userdb_by_uid( $type , $uid , $data) {

        global $lang;

        # 1. check if user exists
        global $lhg_price_db;
        $user = get_userdata( $uid );
	$sql = "SELECT * FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";
	#if ($lang == "de") $sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid_de = \"".$uid."\" ";
        $results = $lhg_price_db->get_results($sql);


        if ( $results[0]->id != "") {
                # User exists
                #error_log("User exists");
	}else{
                # user does not exist, will be added
	                #error_log("Adding user by email -> $uid $data");
		        if ($lang != "de") $sql = "INSERT INTO `lhgtransverse_users` ( wpuid, emails) VALUES (\"".$uid."\",  \"$data\")";
		        if ($lang == "de") $sql = "INSERT INTO `lhgtransverse_users` ( wpuid_de, emails) VALUES (\"".$uid."\",  \"$data\")";
    			$result = $lhg_price_db->query($sql);

			if ($lang != "de") $sql = "SELECT * FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";
			#if ($lang != "de") $sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid = \"".$uid."\" ";
			#if ($lang == "de") $sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid_de = \"".$uid."\" ";
		        $results = $lhg_price_db->get_results($sql);

        }

        # now that user exists, we can update the values
        lhg_update_userdb_by_guid( $type, $results[0]->id, $data);
}

# use global user id to update entry
function lhg_update_userdb_by_guid( $type , $guid , $data) {

        global $lhg_price_db;
        global $lang;

        if ($type == "email") {
		        $sql = "UPDATE `lhgtransverse_users` SET emails =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "karma_com") {
		        $sql = "UPDATE `lhgtransverse_users` SET karma_com =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "karma_de") {
		        $sql = "UPDATE `lhgtransverse_users` SET karma_de =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "karma_quarterly_com") {
		        $sql = "UPDATE `lhgtransverse_users` SET karma_quarterly_com =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "karma_quarterly_de") {
		        $sql = "UPDATE `lhgtransverse_users` SET karma_quarterly_de =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "donation_target_com") {
		        $sql = "UPDATE `lhgtransverse_users` SET donation_target_com =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "donation_target_de") {
		        $sql = "UPDATE `lhgtransverse_users` SET donation_target_de =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "donation_target_date_com") {
		        $sql = "UPDATE `lhgtransverse_users` SET donation_target_date_com =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "donation_target_date_de") {
		        $sql = "UPDATE `lhgtransverse_users` SET donation_target_date_de =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "user_nicename") {
		        $sql = "UPDATE `lhgtransverse_users` SET user_nicename =  \"$data\" WHERE id = \"".$guid."\" ";
    			$result = $lhg_price_db->query($sql);
                        return;
	}
        if ($type == "avatar") {
                        # add domain in .de uploaded avatars
                        if ( ($lang == "de") && (strpos($data, "<img src='/") > -1 ) ) {
                                $data = str_replace( "<img src='/", "<img src='http://www.linux-hardware-guide.de/", $data);
			}

		        $sql = "UPDATE `lhgtransverse_users` SET avatar =  %s WHERE id = \"".$guid."\" ";
                        $safe_sql=$lhg_price_db->prepare($sql, $data);
    			$result = $lhg_price_db->query($safe_sql);
                        return;
	}
        if ($type == "language") {
		        $sql = "UPDATE `lhgtransverse_users` SET language =  %s WHERE id = \"".$guid."\" ";
                        $safe_sql=$lhg_price_db->prepare($sql, $data);
    			$result = $lhg_price_db->query($safe_sql);
                        return;
	}
        if ($type == "username_com") {
		        $sql = "UPDATE `lhgtransverse_users` SET username_com =  %s WHERE id = \"".$guid."\" ";
                        $safe_sql=$lhg_price_db->prepare($sql, $data);
    			$result = $lhg_price_db->query($safe_sql);
                        return;
	}
        if ($type == "username_de") {
		        $sql = "UPDATE `lhgtransverse_users` SET username_de =  %s WHERE id = \"".$guid."\" ";
                        $safe_sql=$lhg_price_db->prepare($sql, $data);
    			$result = $lhg_price_db->query($safe_sql);
                        return;
	}
        if ($type == "emails") {
		        $sql = "UPDATE `lhgtransverse_users` SET emails =  %s WHERE id = \"".$guid."\" ";
                        $safe_sql=$lhg_price_db->prepare($sql, $data);
    			$result = $lhg_price_db->query($safe_sql);
                        return;
	}
        if ($type == "wpuid_de") {
		        $sql = "UPDATE `lhgtransverse_users` SET wpuid_de =  %s WHERE id = \"".$guid."\" ";
                        $safe_sql=$lhg_price_db->prepare($sql, $data);
    			$result = $lhg_price_db->query($safe_sql);
                        return;
	}
        if ($type == "wpuid") {
		        $sql = "UPDATE `lhgtransverse_users` SET wpuid =  %s WHERE id = \"".$guid."\" ";
                        $safe_sql=$lhg_price_db->prepare($sql, $data);
    			$result = $lhg_price_db->query($safe_sql);
                        return;
	}

        error_log("ERROR: unknown type for lhg_update_userdb_by_guid: $type");
}

#
# store date of last login
# will be used to identify spam accounts
add_action('wp_login', 'lhg_store_login_date',1,2);
function lhg_store_login_date( $user_login, $user ) {
        global $lang;
        global $lhg_price_db;

        #$user = wp_get_current_user();
        #does not work because user not yet fully logged in

        if ($user->ID == 0) return;

	# check if user exists
	$sql = "SELECT id FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";


        $id = $lhg_price_db->get_var($sql);
        #error_log("ID: $id");

        #if not, create:
        if ($id == "") {
                        #error_log("create new");

		        if ($lang != "de"){
        		        $sql = "INSERT INTO `lhgtransverse_users` ( wpuid, emails ) VALUES (\"".$user->ID."\",  \"".$user->user_email."\")";
    				$result = $lhg_price_db->query($sql);
		                $sql = "SELECT id FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";
			        $id = $lhg_price_db->get_var($sql);
		        }else{
        		        $sql = "INSERT INTO `lhgtransverse_users` ( wpuid_de, emails ) VALUES (\"".$user->ID."\",  \"".$user->user_email."\")";
    				$result = $lhg_price_db->query($sql);
		                $sql = "SELECT id FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";
			        $id = $lhg_price_db->get_var($sql);
			}
	}

        # store login date
        $date=time();

        if ($lang != "de"){
        	$sql = "UPDATE `lhgtransverse_users` SET `lastlogin_com` = %s WHERE id = %s ";
        }else{
        	$sql = "UPDATE `lhgtransverse_users` SET `lastlogin_de` = %s WHERE id = %s ";

	}
        $safe_sql = $lhg_price_db->prepare($sql, $date, $id);
    	$result = $lhg_price_db->query($safe_sql);

}

function lhg_add_user_to_pricedb( $uid ) {
        #error_log("add_user: $uid");
        global $lang;
        global $lhg_price_db;

        $user = get_userdata( $uid );

        if ($user->ID == "") {
                # this can happen if the user was deleted in the meantime
	        error_log("User not found by ID. Maybe deleted?");
        	return;
        }

	# check if user exists
	$sql = "SELECT id FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";
        $id = $lhg_price_db->get_var($sql);

        #error_log("Store user: ".$user->user_email.", uid:".$user->ID.", GUID:".$id);

        #if not, create:
        if ($id == "") {
                        #error_log("create new:".$user->ID);

		        if ($lang != "de"){
        		        $sql = "INSERT INTO `lhgtransverse_users` ( wpuid, emails ) VALUES (\"".$user->ID."\",  \"".$user->user_email."\")";
    				$result = $lhg_price_db->query($sql);
		                #$sql = "SELECT id FROM `lhgtransverse_users` WHERE wpuid = \"".$user->ID."\" ";
			        #$id = $lhg_price_db->get_var($sql);
		        }else{
        		        $sql = "INSERT INTO `lhgtransverse_users` ( wpuid_de, emails ) VALUES (\"".$user->ID."\",  \"".$user->user_email."\")";
    				$result = $lhg_price_db->query($sql);
		                #$sql = "SELECT id FROM `lhgtransverse_users` WHERE wpuid_de = \"".$user->ID."\" ";
			        #$id = $lhg_price_db->get_var($sql);
			}
	}

        # return global uid:
        if ($lang != "de"){
		$sql = "SELECT id FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";
        }else{
		$sql = "SELECT id FROM `lhgtransverse_users` WHERE emails = \"".$user->user_email."\" ";
	}
        $guid = $lhg_price_db->get_var($sql);
        return $guid;
}


function lhg_get_userdata_guid( $guid ) {
        global $lhg_price_db;

	$sql = "SELECT * FROM `lhgtransverse_users` WHERE id = \"".$guid."\" ";
        $user = $lhg_price_db->get_results($sql);

        # repair avatar
        #error_log("avatar: ".$user[0]->avatar);
        if ( ($user[0]->wpuid_de > 0 ) && (strpos($user[0]->avatar, "<img src='/") > -1 ) ) {
                # we have a .de user with obviously broken .de avatar
                #error_log("Repair avatar!");
                $user[0]->avatar = str_replace( "<img src='/", "<img src='http://www.linux-hardware-guide.de/", $user[0]->avatar);
	}


        #var_dump($user);
        #error_log("Avatar: ".$user->avatar);

        return $user;

}

function lhg_get_avatar_url_by_guid( $guid ) {
        global $lhg_price_db;

	$sql = "SELECT avatar FROM `lhgtransverse_users` WHERE id = \"".$guid."\" ";
        $avatar = $lhg_price_db->get_var($sql);

        #error_log("AVT: $avatar");

        #Gravatar stored avatar image
        if (strpos($avatar,"gravatar") > 1) {
        	$start = strpos($avatar, "src='");
	        $imgurl = substr($avatar, $start+5);
                $tmp = explode("' class", $imgurl);
                $imgurl = $tmp[0];
                $usertxt = '<img src="'.$imgurl.'" width="20px" heigth="20px" title="User: '.$user_nicename.'" alt="User: '.$user_nicename.'">';
	}else{

        	if ( strpos($avatar,"http://") &&  strpos($avatar, 'src="') ) {

		        #local avatar .com
	        	$start = strpos($avatar, 'src="');
			$imgurl = substr($avatar, $start+5);
        		$tmp = explode('" class', $imgurl);
                	$imgurl = $tmp[0];

        	} elseif ( ( strpos($avatar,"http://") == "" ) && strpos($avatar, "src='") ) {


	                $start = strpos($avatar, "src='");
		        $imgurl = substr($avatar, $start+5);
        		$tmp = explode("' class", $imgurl);
                	$imgurl = $tmp[0];


	        	# repair needed?
			$sql = "SELECT * FROM `lhgtransverse_users` WHERE id = \"".$guid."\" ";
		        $user = $lhg_price_db->get_results($sql);
                        #error_log("pos:".strpos($user[0]->avatar, "/ava") );
                        if ( ($user[0]->wpuid_de > 0 ) && (strpos($user[0]->avatar, "/ava") == 1 ) ) {
		                # we have a .de user with obviously broken .de avatar
	                	$imgurl = str_replace( "/avatar", "http://www.linux-hardware-guide.de/avatar", imgurl);
                                #error_log("repair!");
			}


	        } else {

	        	#local avatar .de
        	        $start = strpos($avatar, "src='");
		        $imgurl = substr($avatar, $start+5);
        		$tmp = explode("' class", $imgurl);
                	$imgurl = $tmp[0];
                }
        }



        # add server if image URL still starts with absolute path
        #error_log("IMGURL_START: ".strpos($imgurl,"http://")." -> ".$imgurl);
	if ( strpos($imgurl,"/avat") == 0 ) {
		$sql = "SELECT * FROM `lhgtransverse_users` WHERE id = \"".$guid."\" ";
		$user = $lhg_price_db->get_results($sql);

                if ($user[0]->wpuid_de > 0 ) {
	        	$imgurl = "http://www.linux-hardware-guide.de".$imgurl;
		} elseif ($user[0]->wpuid > 0 ) {
	        	$imgurl = "http://www.linux-hardware-guide.com".$imgurl;
		}

	}

        #error_log("IMGURL: $imgurl");
        return $imgurl;


}


function lhg_get_karma( $uid ) {

        global $lhg_price_db;
        global $lang;

        # 1. look if user is available in transcerse DB
	if ($lang == "de") $sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid_de = \"".$uid."\" ";
	if ($lang != "de") $sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid = \"".$uid."\" ";
        $results = $lhg_price_db->get_results($sql);
        $karma = $results[0]->karma_com + $results[0]->karma_de;


        # 2. default to internal routine
        if ( ($karma == 0) or ($karma == "") ) $karma = cp_getPoints( $uid ); 

        return $karma;


}

function lhg_get_current_users_guids() {
        global $lhg_price_db;

        $current_user = wp_get_current_user();
        $cuid = $current_user->ID;
        $guid = lhg_get_guid( $cuid);
        #error_log("GUID: $guid");

	$sql = "SELECT * FROM `lhgtransverse_users` WHERE id = \"".$guid."\" ";
        $results = $lhg_price_db->get_results($sql);

        $uids = array(
        		"de"  => $results[0]->wpuid_de,
        		"com" => $results[0]->wpuid,
        		);
        return $uids;
}

function lhg_get_guid_from_uid( $uid ) {
        $guid = lhg_get_guid( $uid );
        return $guid;
}

function lhg_get_guid( $uid ) {
        global $lhg_price_db;
        global $lang;

        if ($uid == "") {
                error_log("ERROR: lhg_get_guid -> empty uid provided");
                return;
	}

	if ($lang == "de") $sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid_de = \"".$uid."\" ";
	if ($lang != "de") $sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid = \"".$uid."\" ";
        $results = $lhg_price_db->get_results($sql);

        return $results[0]->id;
}

function lhg_get_guid_from_wpuid_com( $uid ) {
        global $lhg_price_db;

        if ($uid == "") {
                error_log("ERROR: lhg_get_guid_from_wpuid_com -> empty uid provided");
                return;
	}

	$sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid = \"".$uid."\" ";
        $results = $lhg_price_db->get_results($sql);

        return $results[0]->id;
}

function lhg_get_guid_from_wpuid_de( $uid ) {
        global $lhg_price_db;

        if ($uid == "") {
                error_log("ERROR: lhg_get_guid_from_wpuid_de -> empty uid provided");
                return;
	}

	$sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid_de = \"".$uid."\" ";
        $results = $lhg_price_db->get_results($sql);

        return $results[0]->id;
}

# return the UID (of current server) based on global UID
function lhg_get_uid_from_guid( $guid ) {
        global $lhg_price_db;
        global $lang;

        if ($guid == "") {
                error_log("ERROR: lhg_get_guid -> empty uid provided");
                return;
	}

	$sql = "SELECT * FROM `lhgtransverse_users` WHERE id = \"".$guid."\" ";
        $results = $lhg_price_db->get_results($sql);

        if ($lang == "de") $uid = $results[0]->wpuid_de;
        if ($lang != "de") $uid = $results[0]->wpuid;

        return $uid;

}

# get GUID of scan uploader
function lhg_get_scan_uploader_guid( $sid ) {
        global $lhg_price_db;
        global $lang;


	$myquery = $lhg_price_db->prepare("SELECT wp_uid, wp_uid_de FROM `lhgscansessions` WHERE sid = %s", $sid);
	$uids = $lhg_price_db->get_results($myquery);

        $guid = 0; # default

        if ( $uids[0]->wp_uid_de ) {
        	$uid = $uids[0]->wp_uid_de;
                $sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid_de = \"".$uid."\" ";
	        $results = $lhg_price_db->get_results($sql);
	        $guid = $results[0]->id;
        } elseif ( $uids[0]->wp_uid ) {
        	$uid = $uids[0]->wp_uid;
                $sql = "SELECT * FROM `lhgtransverse_users` WHERE wpuid = \"".$uid."\" ";
	        $results = $lhg_price_db->get_results($sql);
	        $guid = $results[0]->id;
        }
        return $guid;
}


function lhg_check_permissions_manufacturers( $caps, $cap, $user_id, $args) {

        global $post;

        if ( lhg_uid_is_manufacturer($user_id) ) {
                error_log("Manufactruer");
                #error_log (" PID: ".get_the_ID() );
                #error_log (" post: ".$post->ID );

                #check if current page is an article
                if ( $post->ID == "" ) return false;

                #error_log ("this is a single page. PID: ".get_the_ID() );

                # check if article tags belong to manufacturer
                if ( !lhg_check_if_manufacturer_can_edit( $user_id, get_the_ID() ) ) return false;


                # all checks passed - manufacturer can edit this article
                if ( ( ( 'edit_post' == $cap )  && in_array( 'edit_others_posts', $caps) ) or
	             ( ( 'edit_post' == $cap )  && in_array( 'edit_posts', $caps) ) or   # needed for comment activation
        	     ( ( 'edit_post' == $cap )  && in_array( 'edit_published_posts', $caps) ) or   # edit translated posts
	             ( ( 'edit_others_posts' == $cap )  && in_array( 'edit_others_posts', $caps) ) ){
			$caps = array();
	                return $caps;
		}

        } else {
                error_log("No manufactruer");
                return false;
        }

        return false;

}

function lhg_check_if_manufacturer_can_edit( $user_id, $post_id ) {

        global $lhg_price_db;
        global $lang;

	if ($lang != "de") $myquery = $lhg_price_db->prepare("SELECT manufacturer_tags_com FROM `lhgtransverse_users` WHERE wpuid = %s", $user_id);
	if ($lang == "de") $myquery = $lhg_price_db->prepare("SELECT manufacturer_tags_de FROM `lhgtransverse_users` WHERE wpuid_de = %s", $user_id);
	$taglist = $lhg_price_db->get_var($myquery);

	if ($lang != "de") $myquery = $lhg_price_db->prepare("SELECT manufacturer_categories_com FROM `lhgtransverse_users` WHERE wpuid = %s", $user_id);
	if ($lang == "de") $myquery = $lhg_price_db->prepare("SELECT manufacturer_categories_de FROM `lhgtransverse_users` WHERE wpuid_de = %s", $user_id);
	$categorylist = $lhg_price_db->get_var($myquery);

        # check if article has tags
        $tagarray = explode(",",$taglist);


        $posttags = wp_get_post_tags($post_id, array('fields' => 'ids') );

        foreach ($tagarray as $usertag){
	        foreach ($posttags as $posttag){
                	#error_log ("UTag: $usertag = $posttag ?");
                        if ($usertag == $posttag) return true; #corresponding manufacturer found
		}
	}

        # check if article is in category list
        $categoryarray = explode(",",$categorylist);
        #error_log("Category check: $categorylist");


        $postcategories = wp_get_post_tags($post_id, array('fields' => 'ids') );

        foreach ($categoryarray as $usercategory){
	        foreach ($postcategories as $postcategory){
                	#error_log ("CTag: $usercategory = $postcategory ?");
                        if ($usercategory == $postcategory) return true; #corresponding manufacturer found
		}
	}
        return false; # article does not belong to manufacturer
}

function lhg_uid_is_manufacturer( $user_id ) {

        global $lhg_price_db;
        global $lang;

	if ($lang != "de") $myquery = $lhg_price_db->prepare("SELECT validated_user FROM `lhgtransverse_users` WHERE wpuid = %s", $user_id);
	if ($lang == "de") $myquery = $lhg_price_db->prepare("SELECT validated_user FROM `lhgtransverse_users` WHERE wpuid_de = %s", $user_id);
	$validation_type = $lhg_price_db->get_var($myquery);

        #error_log("Type: $validation_type");

        if ($validation_type == "manufacturer") return true;
        return false;

}


?>