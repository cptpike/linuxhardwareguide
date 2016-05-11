<?php

# User management rules
# In addition to standard Wordpress permissions, karma points define what users can and cannot do

define ('LHG_KARMA_edit_posts', 10);
define ('LHG_KARMA_delete_posts', 100);
define ('LHG_KARMA_upload_files', 100);
define ('LHG_KARMA_publish_posts', 300);
define ('LHG_KARMA_edit_published_posts', 300);


#apply_filters ( 'map_meta_cap', $caps, $cap, $user_id, $args );
add_filter ( 'map_meta_cap', 'lhg_check_permissions', 10, 4 );

function lhg_check_permissions( $caps, $cap, $user_id, $args) {

	$karma = cp_getPoints( $user_id ); //get karma points

	#error_log("User $user_id permission check cap: $cap - caps:".join(",",$caps) );

        if ( 'edit_posts' == $cap ) {
                #error_log("User wants to edit post - caps:".join(",",$caps) );
                if ( $karma < LHG_KARMA_edit_posts ) {
                        #error_log("Not enough points!");
                	$caps[] = 'activate_plugins';
                }else{
                        error_log("Enough points. Let go!");
                	$caps[] = 'read';
			#caps = array();
        	}
	}

        if ( 'delete_posts' == $cap ) {
                if ( $karma < LHG_KARMA_delete_posts ) {
                	$caps[] = 'activate_plugins';
                }else{
                	$caps[] = 'read';
			#$caps = array();
			#$caps[] = '';
        	}
	}

        if ( 'upload_files' == $cap ) {
                if ( $karma < LHG_KARMA_upload_files ) {
                	$caps[] = 'activate_plugins';
                }else{
                	$caps[] = 'read';
			#$caps = array();
        	}
	}

        if ( 'publish_posts' == $cap ) {
                if ( $karma < LHG_KARMA_publish_posts ) {
                	$caps[] = 'activate_plugins';
                }else{
                	$caps[] = 'read';

	#			$caps[] = '';
        			#$caps = array();

        	}
	}

        if ( 'edit_published_posts' == $cap ) {
                if ( $karma < LHG_KARMA_edit_published_posts ) {
                	$caps[] = 'activate_plugins';
                }else{
                	$caps[] = 'read';

	#		$caps[] = '';
			#$caps = array();

        	}
	}



	return $caps;
}


#
##### Dashboard
#

add_action('wp_dashboard_setup', 'lhg_dashboard_widgets');
function lhg_dashboard_widgets() {
	global $wp_meta_boxes;
	$logo_url = sprintf( 'http://www.linux-hardware-guide.de/avatars/lhg60-avatar.png' , is_ssl()? 's':'' );

	wp_add_dashboard_widget('lhg_greeting_widget', '<img src="'.$logo_url.'" style="height: 1.2em; margin-right:8px; margin-bottom: -3px;" >Welcome to Linux-Hardware-Guide', 'lhg_greeting_widget');
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

			$user_post_count = count_user_posts( $userid );

		        if (function_exists('cp_getPoints'))
		        $karma = cp_getPoints( $userid ); //$num_com * 3 + $num_art * 50;


			$args = array(
				'user_id' => $userid, // use user_id
		        	'count' => true //return only the count
			);
			$comments = get_comments($args);
			$user_post_count = count_user_posts( $userid );
                       echo '
                       <div class="inside">
                       <div class="table table_content">
			<p class="sub"><h2>'.$txt_twt_statistic.'</h2></p>
			<table>
			   <tr class="first"><td class="first b b-posts">
                             <strong>'.$karma.'</strong></a></td><td class="t posts">Karma points</td></tr>
			   <tr class="first"><td class="first b b-posts">
                             <a href="edit.php"><strong>'.$user_post_count.'</strong></a></td><td class="t pages"><a href="edit.php">'.$txt_twt_hwnum.'</a></td></tr>
                           <tr><td class="first b b-cats">
                             <a href="edit-comments.php"><strong>'.$comments.'</strong></a></td><td class="t cats"><a href="edit-comments.php">'.$txt_twt_commnum.'</a></td></tr>
			</table>
		       </div>
                       </div>';

                       //echo "<h2>Statistik:</h2>";
		       //echo '<p style="border-top: 1px solid #CCC; padding-top: 10px; font-weight: bold;">';


                       echo '
                       <div class="inside">
                       <div class="table table_content">
			<p class="sub"><h2>How to earn Karma</h2></p>
                        Karma points can be donated to certain Linux projects. Select your donation target on your <a href="./profile.php">profile page</a>. You can collect Karma points in the following ways:
                             <p>
                             1) Rate and comment on your Linux hardware
                             <p>
                             2) Upload your <a href="/add-hardware">hardware scan</a>, i.e. start the following command in a terminal<br>
                             <tt>perl <(wget -q http://linux-hardware-guide.com/scan-hardware -O -)</tt>
                             <p>

		       </div>
                       </div>';


                       echo'
                       <div class="inside">
                       <div class="table table_content">
                       </div>
                       </div>';

			echo "</p>";
			echo "</div>";

}

?>