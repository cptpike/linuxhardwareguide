<?php

global $donation;
# Definition of donation targets.
# Value of percentage is obsolete...

$donation = array(
	1 => array (
                "Name" => "Linux Hardware Guide",
                "NameShort" => "LHG",
                ),

	2 => array (
                "Name" => "Open Source Initiative",
                "NameShort" => "Open Source In.",
                ),

	3 => array (
                "Name" => "Free Software Foundation",
                "NameShort" => "Free Software FND",
                ),

	4 => array (
                "Name" => "Apache Software Foundation",
                "NameShort" => "Apache Software FND",
                ),

	5 => array (
                "Name" => "RedHat Foundation",
                "NameShort" => "RedHat FND",
                ),

	6 => array (
                "Name" => "Linux Mint",
                "NameShort" => "Linux Mint",
                ),

	7 => array (
                "Name" => "Apache Software Foundation",
                "NameShort" => "ASF",
                ),

	8 => array (
                "Name" => "Linux Foundation",
                "NameShort" => "Linux FND",
                ),


             );


# show donation selector on profile page
add_filter( 'personal_options' , 'lhg_set_donation' );
function lhg_set_donation() {



	$user_id = get_current_user_id();
  	//get user locale with user id
  	$user_donation_target = get_user_meta($user_id,'user_donation_target',true);

        //debug:
	//echo "UL1: $user_language";

  	if($user_donation_target == ""){
   		//add default locale
                #echo "Unknown donation target ($user_donation_target)";
		add_user_meta($user_id, 'user_donation_target', '1');
                $user_donation_target = 1;
  	}else {
                #echo "Found: $user_donation_target";
        }

?><tr>
 <th scope="row"> Karma points are donated to

 </th>
 <td>
  <select name="lhg_user_donation_target">
<?php
lhg_donation_selector ($user_donation_target);
?>
  </select>
 </td>
</tr>
<?php
}

function lhg_donation_selector ($user_donation_target) {
global $donation;
        /*
        //include check of possible array values
        if($user_l, $lang_array) ){
                $error = "- Error: Lang not found (".$user_language.")";
		$user_language = "com";
        }
        */
?>
   <option value="1" <?php selected('1',$user_donation_target); ?>     ><?php echo $donation[1]["Name"]; ?></option>
   <option value="2" <?php selected('2',$user_donation_target); ?>     ><?php echo $donation[2]["Name"]; ?></option>
   <option value="3" <?php selected('3',$user_donation_target); ?>     ><?php echo $donation[3]["Name"]; ?></option>
   <option value="4" <?php selected('4',$user_donation_target); ?>     ><?php echo $donation[4]["Name"]; ?></option>
   <option value="5" <?php selected('5',$user_donation_target); ?>     ><?php echo $donation[5]["Name"]; ?></option>
   <option value="6" <?php selected('6',$user_donation_target); ?>     ><?php echo $donation[6]["Name"]; ?></option>
   <option value="7" <?php selected('7',$user_donation_target); ?>     ><?php echo $donation[7]["Name"]; ?></option>
   <option value="8" <?php selected('8',$user_donation_target); ?>     ><?php echo $donation[8]["Name"]; ?></option>
<?php

}


function lhg_update_donation_settings(){
  global $lang;
  global $lhg_price_db;

  error_log("Update settings");

  //echo "Found??: ".$_POST['lhg_user_donation_target'];

  if(!isset($_POST['lhg_user_donation_target']))
   return;
  //validate submitted value otherwise set to default
  $user_donation_target = $_POST['lhg_user_donation_target'];


  // get user id
  // check if own or other user's profile is edited
  $user_id = get_current_user_id();
  $edituser = $_POST['user_id'];

  if ( ($edituser > 0 ) && ($edituser != $user_id) ) {
	  #error_log("Editing of other user: ".$edituser);
          $guid = lhg_get_guid( $edituser );
          $user_id = $edituser;
  }else{
	  $current_user = wp_get_current_user();
	  $cuid = $current_user->ID;
	  $guid = lhg_get_guid( $cuid);
	  #error_log("UID settings to be conged for  changed: $user_id - $guid");
  }
  
  // first add to history DB before value is overwritten
  $timestamp = time();

  // check if old value exists, if not, store for history reasons
  $sql = "SELECT id FROM `lhgtransverse_donations` WHERE guid = \"".$guid."\" ";
  $result = $lhg_price_db->get_var($sql);
  if ($result == "") {
        # old value was not stored. Entry needed. First get old value

        #error_log("No entry found for $guid");

	$sql = "SELECT *  FROM `lhgtransverse_users` WHERE id = \"".$guid."\" ";
  	$results = $lhg_price_db->get_results($sql);

        #var_dump($results);

        #error_log("DB res: ".$results[0]->donation_target_com);
        #error_log("DB res: ".$results[0]->donation_target_de);

	if ( ( $results[0]->donation_target_com != "" ) && ( $results[0]->donation_target_date_com > $results[0]->donation_target_date_de ) ) {
                # found an old and valid com value
		$sql = "INSERT INTO lhgtransverse_donations (guid, timestamp, donation_target) VALUES (%s, %s, %s) ";
		$safe_sql = $lhg_price_db->prepare( $sql, $guid, $timestamp-1, $results[0]->donation_target_com);
		$result = $lhg_price_db->query($safe_sql);
	}elseif ( ( $results[0]->donation_target_d != "" ) && ( $results[0]->donation_target_date_de > $results[0]->donation_target_date_com ) ) {
                # found an old and valid de value
		$sql = "INSERT INTO lhgtransverse_donations (guid, timestamp, donation_target) VALUES (%s, %s, %s) ";
		$safe_sql = $lhg_price_db->prepare( $sql, $guid, $timestamp-1, $results[0]->donation_target_de);
		$result = $lhg_price_db->query($safe_sql);
	}else{
                #nothing found, fallback value used
                # found an old and valid de value
		$sql = "INSERT INTO lhgtransverse_donations (guid, timestamp, donation_target) VALUES (%s, %s, %s) ";
		$safe_sql = $lhg_price_db->prepare( $sql, $guid, $timestamp-1, 1);
		$result = $lhg_price_db->query($safe_sql);
        }

  }

  #write new settings to DB
  $sql = "INSERT INTO lhgtransverse_donations (guid, timestamp, donation_target) VALUES (%s, %s, %s) ";
  $safe_sql = $lhg_price_db->prepare( $sql, $guid, $timestamp, $user_donation_target);
  $result = $lhg_price_db->query($safe_sql);



  //afterwards, modify user entires (locally and in transverse DB)
  update_user_meta($user_id, 'user_donation_target', $user_donation_target);

  //also store in priceDB
  if ($lang != "de") lhg_update_userdb_by_uid( "donation_target_com", $user_id, $user_donation_target);
  if ($lang == "de") lhg_update_userdb_by_uid( "donation_target_de", $user_id, $user_donation_target);
  if ($lang != "de") lhg_update_userdb_by_uid( "donation_target_date_com", $user_id, time() );
  if ($lang == "de") lhg_update_userdb_by_uid( "donation_target_date_de", $user_id, time() );


}
add_filter('personal_options_update','lhg_update_donation_settings');
add_filter('edit_user_profile_update','lhg_update_donation_settings');


function lhg_return_donation_targets() {
        # returns two arrays
        # 1 ... name of company/organization to which donation will go
        # 2 ... amount of points for this company/organization
        # 3 ... number of users that donated to this company/organization

        global $donation;
        # returns collected points of ongoing quarter
        list($donation_target_sums, $donation_target_users)  = lhg_return_donation_results(false, false);

        $j=0;
        foreach ($donation_target_sums as $key => $points){

                $donation_targets[$j] = $donation[$key]["Name"];
                $donation_points[$j] = $points;
                $donation_users[$j] = $donation_target_users[$key];
                $j++;

	}

        return array($donation_targets, $donation_points, $donation_users);
}



function lhg_return_donation_results($startdate, $enddate) {

        # ToDo: Currently, provided time frame is ignored
        # ongoing quarter is always used

	list($list_guid, $list_points) = cp_getAllQuarterlyPoints_transverse( $startdate, $enddate );

        #error_log("Start: $startdate End: $enddate");

        #print "Userpoints: ";
        #var_dump($list_points);
        #print "<br>";

        $i=0;
	foreach($list_guid as $guid){
                # Skip anonymously submitted posts
                if ($uid != 12378){

        	//$user = get_userdata($uid);
                //$name = $user->first_name." ".$user->last_name;
                $points = $list_points[$i];


                //donates to
                # target has to be extracted from transverse database!
		#$donation_target = get_user_meta($uid,'user_donation_target',true);

	        # checking for latest change
        	global $lhg_price_db;
		$sql = "SELECT * FROM `lhgtransverse_users` WHERE id = \"".$guid."\" ";
                $results = $lhg_price_db->get_results($sql);
                $timestamp_de = $results[0]->donation_target_date_de;
                $timestamp_com = $results[0]->donation_target_date_com;


                if ($timestamp_de > $timestamp_com) {
                	$donation_target = $results[0]->donation_target_de;
		} else {
                	$donation_target = $results[0]->donation_target_com;
		} 

                #error_log("TSde/com: $timestamp_de/$timestamp_com -> Target: $donation_target");

                # sum up points, default = LHG:
                if ($donation_target == "") $donation_target = 1;
                if ($donation_target == 0) $donation_target = 1;
                $donation_target_sum[$donation_target] += $points;
                $donation_target_users[$donation_target] += 1;
		}
                $i++;
	}

	return array($donation_target_sum, $donation_target_users);
	#return array($donation_target_sum, $dnoation_targets);
}

function lhg_update_points_db(){

        #error_log("Updating Points DB");
        global $wpdb;
        global $lang;

        # get latest timestamp from PriceDB
        global $lhg_price_db;
	if ($lang != "de") $sql = "SELECT MAX(timestamp) FROM lhgtransverse_points WHERE wpuid_com > 0";
	if ($lang == "de") $sql = "SELECT MAX(timestamp) FROM lhgtransverse_points WHERE wpuid_de > 0";
        $timestamp = $lhg_price_db->get_var($sql);

        #error_log("Found timestamp: $timestamp");

        # Need this if run for the very first time
        if ($timestamp == "") {
                # first run
                $timestamp = 1;
	        #error_log("Timestamp: $timestamp");
        	# find new entries
		$results = $wpdb->get_results( apply_filters('cp_logs_dbquery', 'SELECT * FROM `'.CP_DB.'` ORDER BY timestamp DESC ') );
        }else{
        	# find new entries
		$results = $wpdb->get_results( apply_filters('cp_logs_dbquery', 'SELECT * FROM `'.CP_DB.'` WHERE timestamp > `'.$timestamp.'` ORDER BY timestamp DESC ') . $limitq);
	}

        # Sum up achieved points of the accumulation time span
	foreach($results as $result){
		$user = get_userdata($result->uid);
		$username = $user->user_login;
		$user_nicename = $user->display_name;
		$points = $result->points;
                #$cp_inQuarter = cp_TimeInQuarter($result->timestamp);
		#error_log( "($lang) $user_nicename: $result->timestamp --> $result->type, $result->uid, $result->points, $result->data ");

                lhg_add_points_to_db( $result->uid, $result->points, $result->timestamp, $result->type, $result->data );

        }
}

function lhg_add_points_to_db( $uid, $points, $timestamp, $type, $comment){

        	global $lhg_price_db;
                global $lang;
		$sql = "SELECT id FROM lhgtransverse_points WHERE wpuid_com = \"".$uid."\" AND timestamp = \"".$timestamp."\" ";
                $results = $lhg_price_db->get_var($sql);

                if ( !empty($results) ) return; #data already in DB, do not overwrite

		if ($lang != "de" ) $sql = "INSERT INTO lhgtransverse_points (wpuid_com, points, timestamp, type, comment) VALUES (%s, %s, %s, %s, %s) ";
		if ($lang == "de" ) $sql = "INSERT INTO lhgtransverse_points (wpuid_de , points, timestamp, type, comment) VALUES ('%s', '%s', '%s', '%s', '%s') ";
                $safe_sql = $lhg_price_db->prepare( $sql, $uid, $points, $timestamp, $type, $comment);
                #error_log("SQL: $safe_sql");
                $result = $lhg_price_db->query($safe_sql);

}

# This function returns the selected donation target, which was selected by a user at a certain date
# needed for calculation of donation histories
function lhg_get_donation_target_by_date($guid, $timestamp) {

        global $lhg_price_db;

        # 1 look if donation target is set in history DB
        $sql = "SELECT donation_target FROM `lhgtransverse_donations` WHERE guid = \"".$guid."\" AND timestamp < \"".$timestamp."\" ORDER BY timestamp DESC LIMIT 1 ";
  	$result = $lhg_price_db->get_var($sql);
        #error_log("Test 1: $result for $guid");
        if ($result > 0) return $result;

        # 2 look if a later entry exists (i.e. donation target was never changed before)
        $sql = "SELECT donation_target FROM `lhgtransverse_donations` WHERE guid = \"".$guid."\" ORDER BY timestamp ASC LIMIT 1 ";
  	$result = $lhg_price_db->get_var($sql);
        #error_log("Test 2: $result for $guid");
        if ($result > 0) return $result;

        # 3 look if value exists in transverse user DB
        $user_tmp = lhg_get_userdata_guid($guid);
        $user=$user_tmp[0];
        if ($user->donation_target_date_de > $user->donation_target_date_com) $donation_target = $user->donation_target_de;
        if ($user->donation_target_date_de <= $user->donation_target_date_com) $donation_target = $user->donation_target_com;
        #error_log("Test 3: $donation_target for $guid");
        if ($donation_target > 0) return $donation_target;

        #error_log("Test 4: default for $guid");
        return 1; # default value if nothing found
}


?>
