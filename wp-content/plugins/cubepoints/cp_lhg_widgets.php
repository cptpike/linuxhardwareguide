<?php
/**
 * CubePoints widgets
 */


/** CubePoints Top Users Widget */
class cp_lhg_topUsersWidget extends WP_Widget {
 
	// constructor
	function cp_lhg_topUsersWidget() {
		parent::WP_Widget('cp_lhg_topUsersWidget', 'CubePoints Top Users - LHG Version', array('description' => 'LHG version: Use this widget to showcase the users with the most points.'));	
	}

 
	// widget main
	function widget($args, $instance) {

                global $region;
                global $top_users;
                global $donation;
                global $txt_cp_title;
		global $txt_cp_karma;
		global $txt_cp_points;
		global $txt_cp_donates_to;
		global $txt_cp_longtext;
		global $txt_cp_language;

                # How many users to show for ongoing quarter
                $max_users_to_show = 5;


                $langurl = lhg_get_lang_url_from_region( $region );

		extract($args, EXTR_SKIP);
		echo $before_widget;
		//$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
                $title ='<i class="icon-trophy menucolor"></i>&nbsp;'.$txt_cp_title;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };

		//set default values
		if($instance['num'] == '' || $instance['num'] == 0) { $instance['num'] = 1; }
		if($instance['text'] == '') { $instance['text'] = '%user% (%points%)';}


		# Show table of top users of ongoing Quarter
		list($list_uid, $list_points) = cp_getAllQuarterlyPoints();

                #print "<br>Users:";
                #var_dump($list_uid);
                $i = 0;

                if (sizeof($list_uid) > 0) print '<table id="quarterly-points-table">
                <tr id="quarterly-points-header-row">
                  <td class="qrtly-1" id="quarterly-points-1">Quarterly Points</td>
                  <td class="qrtly-2" id="quarterly-points-2"></td>
                  <td class="qrtly-3" id="quarterly-points-3">Username</td>
                  <td class="qrtly-4" id="quarterly-points-3">Details</td>
                  <td class="qrtly-5" id="quarterly-points-3">Total Points</td>
                </tr>
                ';

                if (sizeof($list_uid) > 0)
		foreach($list_uid as $uid){
                	$user = get_userdata($uid);
                        $name = $user->first_name." ".$user->last_name;
                        $points = $list_points[$i];
                        $avatar = get_avatar($uid, 40);
                        $user_language_txt = lhg_get_locale_from_id ( $uid );
        		$user_language_flag= lhg_show_flag_by_lang ( $user_language_txt );
		        $total_karma = cp_getPoints( $uid ); //$num_com * 3 + $num_art * 50;

                        //registration date
                        $regdate = date("d. M Y", strtotime(get_userdata( $uid ) -> user_registered ) );

                        //donates to
			$donation_target = get_user_meta($uid,'user_donation_target',true);
                        if ($donation_target == "") $donation_target = 1;

                        //print_r($y);
                        //if ($langurl != "") $langurl = "/".$langurl;



	                #print "Name: ".$user->user_nicename." ($uid) - $points<br>";

			echo '<tr>

<td class="qrtly-1" >
	    <div class="userlist-place-quarter">'.
        	    ($points).' '.$txt_cp_points.'
    	    </div>
</td>


<td class="quartery-points-avatar qrtly-2">
<a href="./hardware-profile/user'.$uid.'" class="recent-comments">
    <div class="userlist-avatar">'.
      $avatar.'
    </div>
</a>
</td>


<td class="qrtly-3">
          <div class="userlist-displayname">
		<a href="./hardware-profile/user'.$uid.'" class="recent-comments">';
	            	echo $user->user_nicename.'
                </a>

          </div>
</td>


<td class="qrtly-4">
    <div class="quarterly-points-userlist-details">
      '.$txt_cp_donates_to.': '.$donation[$donation_target]["Name"].'<br>
      '.$txt_cp_language.': '.$user_language_flag.'
    </div>
</td>

<td class="qrtly-5">
          <div class="quartly-points-totalpoints">
	      '.$txt_cp_karma.': '.$total_karma.' '.$txt_cp_points.'<br>
          </div>
</td>

';

print "</tr>";


                        $i++;
                        if ($i > $max_users_to_show-1) break;
		}

                if (sizeof($list_uid) > 0) print "</table>";




/*

		$top = cp_getAllPoints($instance['num'],get_option('cp_topfilter'));
		do_action('cp_topUsersWidget_before');
		echo apply_filters('cp_topUsersWidget_before','<div class="userlist-all"><ul class="bwp-rc-ulist">');
		$line = str_replace('%style%', $instance['style'], $line);
		foreach($top as $x=>$y){
                        $i++;
                        $uid = $y['id'];
			$user = get_userdata($y['id']);
                        $name = $user->first_name." ".$user->last_name;
			$string = str_replace('%string%', '', $instance['text']);
			$string = str_replace('%string%',$string,$line);
			$string = apply_filters('cp_displayUserInfo',$string,$y,$x+1);

                        //Flag
		        $user_language_txt = lhg_get_locale_from_id ( $uid );
        		$user_language_flag= lhg_show_flag_by_lang ( $user_language_txt );

                        //registration date
                        $regdate = date("d. M Y", strtotime(get_userdata( $uid ) -> user_registered ) );

                        //donates to
			$donation_target = get_user_meta($uid,'user_donation_target',true);
                        if ($donation_target == "") $donation_target = 1;

                        $avatar = get_avatar($uid, 40);
                        //print_r($y);
                        //if ($langurl != "") $langurl = "/".$langurl;

global $txt_cp_karma;
global $txt_cp_points;
global $txt_cp_donates_to;
global $txt_cp_longtext;
global $txt_cp_language;

                        echo '
<a href="./hardware-profile/user'.$uid.'" class="recent-comments">
  <div class="comment-box">

    <div class="userlist-avatar">'.
      $avatar.'
    </div>

    <div class="userlist-name">
          <div class="userlist-place">'.
            $i.'.
          </div>
          <div class="userlist-displayname">';
            //if ($name == " ") {
            	echo $y['display_name'];
            //}else{
            //    echo $name;
            //};
            echo '
          </div>

        <!-- div class="userlist-flag">'.
          $user_language_flag.'
        </div -->

    </div>


    <div class="userlist-details">
      '.$txt_cp_karma.': '.$y['points'].' '.$txt_cp_points.'<br>
      '.$txt_cp_donates_to.': '.$donation[$donation_target]["Name"].'<br>
      '.$txt_cp_language.': '.$user_language_flag.'
    </div>

  </div>
</a>';
//      Registered since: '.$regdate.'<br>




                $top_users[$i]["ID"]=$uid;
                $top_users[$i]["Karma"]=$y['points'];
	        $top_users[$i]["DisplayName"]=$y['display_name'];

                        // echo $string;
		}
		echo apply_filters('cp_topUsersWidget_after','</ul></div>');

$topvotes = lhg_voted_donation();
$most_voted = lhg_most_voted ($topvotes);
$most_voted_percent = lhg_most_voted_percent ($topvotes);

//$most_voted = 2;

        // echo "TV: $topvotes";
*/

global $donation_total;

/*
Former donation code
echo '

<div class="userlist-chart">'.$txt_cp_longtext.': <b>'. $donation_total.' &euro;</b>
  '.do_shortcode('[highchart-quarterly]
');
*/
                echo 'Donations:<br>'.do_shortcode('[highchart_quarterly]');

$startQrt = cp_StartOfQuarter();
$endQrt = cp_EndOfQuarter();

#print "Range: ".date('jS \of F Y h:i:s A',$startQrt)." to ".date('jS \of F Y h:i:s A',$endQrt);

//   Currently, '.$most_voted_percent.'% of all users vote for "'.$donation[$most_voted]["Name"].'".

//$topvo



// echo '</div>';

                //end of userlist all
		do_action('cp_topUsersWidget_after');
		echo $after_widget;
	}
 
	// widget settings update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['num'] = ((int) $new_instance['num'] > 0 ) ? (int) $new_instance['num'] : 1 ;
		$instance['text'] = trim($new_instance['text']);
		$instance['style'] = trim($new_instance['style']);
		return $instance;
	}
 
	// widget settings form
	function form($instance) {
		$default = 	array( 'title' => __('Top Users', 'cp') , 'num' => 3 , 'text' => '%user% (%points%)', 'style' => 'list-style:none;' );
		$instance = wp_parse_args( (array) $instance, $default );
 
		$field = 'title';
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Title', 'cp').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.esc_attr( $instance[$field] ).'" /><label></p>';
		
		$field = 'num';
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Number of top users to show', 'cp').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.esc_attr( $instance[$field] ).'" /><label></p>';
		
		$field = 'text';
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Text', 'cp').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.esc_attr( $instance[$field] ).'" /><label></p>';

		echo "\r\n".'<small><strong>'.__('Shortcodes', 'cp') . ':</strong><br />';
		echo __('Number of points', 'cp') . ' - %points%' . '<br />';
		echo __('Points (number only)', 'cp') . ' - %npoints%' . '<br />';
		echo __('User display name', 'cp') . ' - %username%' . '<br />';
		echo __('User login ID', 'cp') . ' - %user%' . '<br />';
		echo __('User ID', 'cp') . ' - %userid%' . '<br />';
		echo __('User ranking', 'cp') . ' - %place%' . '<br />';
		echo __('Email MD5 hash', 'cp') . ' - %emailhash%' . '<br />';
		echo '<br /></small>';
		
		$field = 'style';
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Style', 'cp').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.esc_attr( $instance[$field] ).'" /><label></p>';
		echo "\r\n".'<small><strong>'.__('Note', 'cp') . ':</strong> '.__('This adds the following style to the list element. Shortcodes from above may be used here. The %emailhash% shortcode, for example, could be used to display gravatars.', 'cp').'</small><br />';
	}
}


add_action('widgets_init', 'cp_lhg_widgets');

function cp_lhg_widgets(){
	// register points widget
	//register_widget("cp_pointsWidget");

	// register top users widget
	register_widget("cp_lhg_topUsersWidget");
	
}


function cp_getAllQuarterlyPoints(){

        global $wpdb;

	$startQrt = cp_StartOfQuarter();
	$endQrt = cp_EndOfQuarter();

        #print "<br>Quaterly results ($startQrt - $endQrt):";
	$results = $wpdb->get_results( apply_filters('cp_logs_dbquery', 'SELECT * FROM `'.CP_DB.'` WHERE timestamp > '.$startQrt.' AND timestamp < '.$endQrt.' '.$q.'ORDER BY timestamp DESC ') . $limitq);

        # Sum up achieved points of the accumulation time span
	foreach($results as $result){
		$user = get_userdata($result->uid);
		$username = $user->user_login;
		$user_nicename = $user->display_name;
		$points = $result->points;
                #$cp_inQuarter = cp_TimeInQuarter($result->timestamp);
		#print "$user_nicename: $result->timestamp --> $result->type, $result->uid, $result->points, $result->data <br>";

                $founduser_points[$result->uid] += $result->points;
                $founduser_uid[$result->uid] = $result->uid;
        }
        #print "USERP: <br>";
        #var_dump($founduser_points);
        #print "<br>ID: <br>";
        #var_dump($founduser_uid);

        array_multisort($founduser_points, SORT_DESC, SORT_NUMERIC, $founduser_uid);

        #print "<br>USERP sorted: <br>";
        #var_dump($founduser_points);
        #print "<br>ID sorted: <br>";
        #var_dump($founduser_uid);

        return array($founduser_uid, $founduser_points);

}

function cp_getAllQuarterlyPoints_transverse( $startdate, $enddate ){

        global $lhg_price_db;

        if (!is_numeric( $startdate ) or !is_numeric( $enddate ) ){
                #error_log("No start & end date given");
                $startdate = cp_StartOfQuarter();
                $enddate   = cp_EndOfQuarter();

                #error_log("start: $startdate end: $enddate");
	}


        if (is_numeric( $startdate ) && is_numeric( $enddate ) ){
	        $sql = "SELECT * FROM `lhgtransverse_points` WHERE timestamp > $startdate AND timestamp < $enddate";
	        $results = $lhg_price_db->get_results($sql);

	        # Sum up achieved points of the accumulation time span
		foreach($results as $result){

                        # skip admin points
                        if ( ( ($result->wpuid_com) > 1) or ( ($result->wpuid_de) > 1 ) ) {

        	                $points = $result->points;

                	        # get guid
                        	if ( ($result->wpuid_com) > 0) $guid = lhg_get_guid_from_wpuid_com($result->wpuid_com);
	                        if ( ($result->wpuid_de) > 0) $guid = lhg_get_guid_from_wpuid_de($result->wpuid_de);

                                # prevent doubple counting of scans
                                $skip = 0;
                                if ( strpos($result->comment, "/hardware-profile" ) > 0 ) {
				        $sql_double = "SELECT MIN(timestamp) FROM `lhgtransverse_points` WHERE comment = '%s'";
                                        $safe_sql = $lhg_price_db->prepare($sql_double, $result->comment);
				    	$min_timestamp = $lhg_price_db->get_var($safe_sql);
                                        if ($min_timestamp < $result->timestamp) $skip = 1; # this is a duplicate!
                                        #error_log("min: $min_timestamp = $result->timestamp ?");
	                	}

                                if ($guid == "") $skip = 1;

                	        # collect data in array
                                if ($skip == 0) {
					$founduser_points[$guid] += $points;
	        		        $founduser_guid[$guid] = $guid;
        	        	        #error_log("P: $points -> $guid -> ".$result->wpuid_com."");
	                	}

                	}
	        }


	}else{
                # skip admin points
        	$sql = "SELECT * FROM `lhgtransverse_users` WHERE ( karma_quarterly_com <> 0 OR karma_quarterly_de <> 0 ) AND wpuid <> 1";
	        $results = $lhg_price_db->get_results($sql);

	        # Sum up achieved points of the accumulation time span
		foreach($results as $result){
	                        #error_log("ID: ".$result->id);
				$user_nicename = $result->user_nicename;
				$points = $result->karma_quarterly_com + $result->karma_quarterly_de;
	        	        #$cp_inQuarter = cp_TimeInQuarter($result->timestamp);
				#print "$user_nicename: $result->timestamp --> $result->type, $result->uid, $result->points, $result->data <br>";

	        	        $founduser_points[$result->id] = $points;
        	        	$founduser_guid[$result->id] = $result->id;
	        }
        }


        #print "USERP: <br>";
        #var_dump($founduser_points);
        #print "<br>ID: <br>";
        #var_dump($founduser_uid);

        if ( $founduser_points != "" )
        array_multisort( $founduser_points, SORT_DESC, SORT_NUMERIC, $founduser_guid );

        #print "<br>USERP sorted: <br>";
        #var_dump($founduser_points);
        #print "<br>ID sorted: <br>";
        #var_dump($founduser_guid);

        return array($founduser_guid, $founduser_points);

}

function cp_StartOfQuarter(){

        #find start of actual quarter
        $i=1;
        $searchquarter = mktime(0,0,0,1,1,2015);
        #print "Comp: ".time()." > ".$searchquarter."<br>";
        while ( ( time() > $searchquarter) and ($i < 100) ) {
                #print "Quarter $i: Mod: ".intval($i / 12)."Date: ".date('jS \of F Y h:i:s A',$searchquarter)."<br>";
                $i++;
                $searchquarter = mktime(0,0,0, ( (intval($i/3)*3) % 12)+1 , 1, 2015 + intval($i/12) );
	}
        $searchquarter = mktime(0,0,0, ( (intval(($i-1)/3)*3) % 12)+1 , 1, 2015 + intval(($i-1)/12) );
        return $searchquarter;
}

function cp_EndOfQuarter(){

        #find end of actual quarter
        $i=1;
        $searchquarter = mktime(0,0,0,1,1,2015);
        #print "Comp: ".time()." > ".$searchquarter."<br>";
        while ( ( time() > $searchquarter) and ($i < 100) ) {
                #print "Quarter $i: Mod: ".intval($i / 12)."Date: ".date('jS \of F Y h:i:s A',$searchquarter)."<br>";
                $i++;
                $searchquarter = mktime(0,0,0, ( (intval($i/3)*3) % 12)+1 , 1, 2015 + intval($i/12) );
	}
                $searchquarter = mktime(0,0,0, ( (intval( ($i)/3)*3) % 12)+1 , 1, 2015 + intval(($i)/12) );
        return $searchquarter;
}



 
?>