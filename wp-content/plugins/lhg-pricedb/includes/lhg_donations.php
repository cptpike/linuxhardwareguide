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

  //echo "Found??: ".$_POST['lhg_user_donation_target'];

  if(!isset($_POST['lhg_user_donation_target']))
   return;
  //get user id
  $user_id = get_current_user_id();
  //validate submitted value otherwise set to default
  $user_donation_target = $_POST['lhg_user_donation_target'];
  //update user locale
  update_user_meta($user_id, 'user_donation_target', $user_donation_target);

  //also store in priceDB
  if ($lang != "de") lhg_update_userdb_by_uid( "donation_target_com", $user_id, $user_donation_target);
  if ($lang == "de") lhg_update_userdb_by_uid( "donation_target_de", $user_id, $user_donation_target);
  if ($lang != "de") lhg_update_userdb_by_uid( "donation_target_date_com", $user_id, time() );
  if ($lang == "de") lhg_update_userdb_by_uid( "donation_target_date_de", $user_id, time() );

 }
add_filter('personal_options_update','lhg_update_donation_settings');


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

	list($list_uid, $list_points) = cp_getAllQuarterlyPoints_transverse();

        #print "Userpoints: ";
        #var_dump($list_points);
        #print "<br>";

        $i=0;
	foreach($list_uid as $uid){
                # Skip anonymously submitted posts
                if ($uid != 12378){

        	//$user = get_userdata($uid);
                //$name = $user->first_name." ".$user->last_name;
                $points = $list_points[$i];
                //donates to
		$donation_target = get_user_meta($uid,'user_donation_target',true);

                # sum up points, default = LHG:
                if ($donation_target == "") $donation_target = 1;
                $donation_target_sum[$donation_target] += $points;
                $donation_target_users[$donation_target] += 1;
		}
                $i++;
	}

	return array($donation_target_sum, $donation_target_users);
	#return array($donation_target_sum, $dnoation_targets);
}

?>
