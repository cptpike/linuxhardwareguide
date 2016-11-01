<?php

# General functions to identify if hardware corresponds to user


function lhg_check_user_owns_hardware( $user_id, $post_id ) {

        #error_log("check for user hw of $user_id");

        global $lhg_price_db;
        global $lang;

	if ($lang != "de") $myquery = $lhg_price_db->prepare("SELECT sid FROM `lhgscansessions` WHERE wp_uid = %s", $user_id);
	if ($lang == "de") $myquery = $lhg_price_db->prepare("SELECT sid FROM `lhgscansessions` WHERE wp_uid_de = %s", $user_id);
	$results = $lhg_price_db->get_results($myquery);

        #print_r($results);
        #error_log("SIDS: $results");

        foreach ($results as $result) {
                $sid = $result->sid;
                #error_log("User: $user_id -> $sid");

                # check if user uploaded this hardware
                $result2 = lhg_check_sid_contains_article( $sid, $post_id );
                #error_log("Found HW in $sid");
                if ($result2 == true) return true;
	}

        # hardware does not belong to user
        return false;
}

function lhg_check_sid_contains_article( $sid, $post_id ) {
        # check if this article was uploaded as part of the given scan session ID

        global $lhg_price_db;
        global $lang;

	$myquery = $lhg_price_db->prepare("SELECT postid FROM `lhghwscans` WHERE sid = %s AND postid = %s", $sid, $post_id);
	$results = $lhg_price_db->get_results($myquery);

        if ( !empty($results) ) return true;

        # nothing found -> ohh!
        return false;
}



?>