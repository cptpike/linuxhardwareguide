<?php

global $donation;
# Definition of donation targets.
# Value of percentage is obsolete...

$donation = array(
	1 => array (
                "Name" => "Linux Hardware Guide",
                "NameShort" => "LHG",
                "Percentage" => .50
                ),

	2 => array (
                "Name" => "Open Source Initiative",
                "NameShort" => "Open Source In.",
                "Percentage" => .10
                ),

	3 => array (
                "Name" => "Free Software Foundation",
                "NameShort" => "Free Software FND",
                "Percentage" => .10
                ),

	4 => array (
                "Name" => "Apache Software Foundation",
                "NameShort" => "Apache Software FND",
                "Percentage" => .10
                ),

	5 => array (
                "Name" => "RedHat Foundation",
                "NameShort" => "RedHat FND",
                "Percentage" => .20
                ),

	6 => array (
                "Name" => "Linux Mint",
                "NameShort" => "Linux Mint",
                "Percentage" => .20
                ),

	7 => array (
                "Name" => "Apache Software Foundation",
                "Percentage" => .20
                ),

	8 => array (
                "Name" => "Linux Foundation",
                "NameShort" => "Linux FND",
                "Percentage" => .50
                ),


             );


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

	list($list_uid, $list_points) = cp_getAllQuarterlyPoints();

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
