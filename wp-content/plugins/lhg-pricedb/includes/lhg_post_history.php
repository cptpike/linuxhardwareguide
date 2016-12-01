<?php

# collection of routines needed to archive the history of an article
# in the table lhgpricedb/lhgtransverse_post_history

# Fields:
# - id
# - timestamp
# - postid_com
# - postid_de
# - change_type (auto_translation, creation, update)
# - change_comment (possible comment left by user)
# - guid


function lhg_post_history_translation( $lang_from, $lang_to, $postid_from, $postid_to, $guid) {

        global $lhg_price_db;

        if ( ($lang_from == "de") && ($lang_to == "en") ) {
        	#$sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_com, postid_de,  change_type, change_comment, guid) VALUES ('%s', '%s', '%s', '%s', '%s', '%s') ";
		#$safe_sql = $lhg_price_db->prepare( $sql, $guid, $timestamp-1, $results[0]->donation_target_com);
		#$result = $lhg_price_db->query($safe_sql);

        }

        if ( ($lang_from == "en") && ($lang_to == "de") ) {
                $timestamp = time();
        	$sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_com, postid_de,  change_type, change_comment, guid) VALUES ('%s', '%s', '%s', '%s', '%s', '%s') ";
		$safe_sql = $lhg_price_db->prepare( $sql, $timestamp, $postid_from, $postid_to, "auto_translation", "Automatic translation from ".$postid_from." to ".$postid_to, $guid);
		$result = $lhg_price_db->query($safe_sql);

        }

        return;
}


?>
