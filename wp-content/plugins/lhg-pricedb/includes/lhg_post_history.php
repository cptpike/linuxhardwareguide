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



# ######################################
#
# hooks
#
########################################

#add_action ('save_post', 'lhg_post_history_save_post' );
add_action ('edit_post', 'lhg_post_history_edit_post' );
add_action ('wp_insert_post', 'lhg_post_history_created_post', 10, 3 );

add_action ('new_to_publish'    , 'lhg_post_history_publish_post' );
add_action ('pending_to_publish', 'lhg_post_history_publish_post' );
add_action ('draft_to_publish'  , 'lhg_post_history_publish_post' );



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
		$safe_sql = $lhg_price_db->prepare( $sql, $timestamp, $postid_from, $postid_to, "auto_translation_en->de", "Automatic translation from ".$postid_from." to ".$postid_to, $guid);
		$result = $lhg_price_db->query($safe_sql);
        }

        return;
}

function lhg_post_history_translation_update( $lang_from, $lang_to, $postid_from, $postid_to, $guid) {

        global $lhg_price_db;

        if ( ($lang_from == "de") && ($lang_to == "en") ) {
        	#$sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_com, postid_de,  change_type, change_comment, guid) VALUES ('%s', '%s', '%s', '%s', '%s', '%s') ";
		#$safe_sql = $lhg_price_db->prepare( $sql, $guid, $timestamp-1, $results[0]->donation_target_com);
		#$result = $lhg_price_db->query($safe_sql);

        }

        if ( ($lang_from == "en") && ($lang_to == "de") ) {
                $timestamp = time();
        	$sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_com, postid_de,  change_type, change_comment, guid) VALUES ('%s', '%s', '%s', '%s', '%s', '%s') ";
		$safe_sql = $lhg_price_db->prepare( $sql, $timestamp, $postid_from, $postid_to, "auto_translation_update_en->de", "Automatic translation update from ".$postid_from." to ".$postid_to, $guid);
		$result = $lhg_price_db->query($safe_sql);
        }

        return;
}


# article was created by hardware scan
# we save only the scan's session ID since the link to its
# user could change at any given time
function lhg_post_history_scancreate( $postid, $sid ) {

        global $lhg_price_db;
        global $lang;
        $timestamp = time();

        if ($lang == "de")  $sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_de,  change_type, change_comment) VALUES ('%s', '%s', '%s', '%s') ";
        if ($lang != "de") $sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_com, change_type, change_comment) VALUES ('%s', '%s', '%s', '%s') ";

	$safe_sql = $lhg_price_db->prepare( $sql, $timestamp, $postid, "article_created_by_scan", $sid );
	$result = $lhg_price_db->query($safe_sql);

	$error = $lhg_price_db->last_error;
    	if ($error != "") { var_dump($error); exit; }

        return;
}


function lhg_post_history_published( $postid, $guid) {

        if ($postid == "") {
                error_log("Empty postid lhg_post_history_published()");
                return;
	}

        global $lhg_price_db;
        global $lang;
        $timestamp = time();

        # check if article was already published
        if ($lang == "de")  $sql = "SELECT id FROM lhgtransverse_post_history WHERE postid_de = '%s' AND change_type = '%s'";
        if ($lang != "de")  $sql = "SELECT id FROM lhgtransverse_post_history WHERE postid_com = '%s' AND change_type = '%s' ";
	$safe_sql = $lhg_price_db->prepare( $sql,  $postid, "article_published" );
	$result = $lhg_price_db->get_var($safe_sql);

        if ( ($result > 0) ) return; # post exists, we do not need to create corresponding history entry



        # write to DB
        if ($lang == "de")  $sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_de,  change_type, change_comment, guid) VALUES ('%s', '%s', '%s', '%s', '%s') ";
        if ($lang != "de") $sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_com, change_type, change_comment, guid) VALUES ('%s', '%s', '%s', '%s', '%s') ";

	$safe_sql = $lhg_price_db->prepare( $sql, $timestamp, $postid, "article_published", "Article ".$postid." was published by ".$guid, $guid);
	$result = $lhg_price_db->query($safe_sql);

	$error = $lhg_price_db->last_error;
    	if ($error != "") { var_dump($error); exit; }


        return;
}

function lhg_post_history_edit( $postid, $guid) {

        global $lhg_price_db;
        global $lang;
        $timestamp = time();

        # check if this edit request is coming together with a publishing
        # (Wordpress initiates publishing action and immediately afterwards the editing ection)
        # In this case published and edited article are identical
        # dirty hack:

        if ($lang == "de")  $sql = "SELECT MAX(timestamp) FROM lhgtransverse_post_history WHERE postid_de = '%s' AND change_type = '%s'";
        if ($lang != "de")  $sql = "SELECT MAX(timestamp) FROM lhgtransverse_post_history WHERE postid_com = '%s' AND change_type = '%s' ";
	$safe_sql = $lhg_price_db->prepare( $sql,  $postid, "article_published" );
	$result = $lhg_price_db->get_var($safe_sql);

        if ( ($timestamp - $result) < 4 ) {
                # No one can edit the article this quickly
                return;
	}


        # write to DB
        if ($lang == "de")  $sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_de,  change_type, change_comment, guid) VALUES ('%s', '%s', '%s', '%s', '%s') ";
        if ($lang != "de") $sql = "INSERT INTO lhgtransverse_post_history ( timestamp, postid_com, change_type, change_comment, guid) VALUES ('%s', '%s', '%s', '%s', '%s') ";

	$safe_sql = $lhg_price_db->prepare( $sql, $timestamp, $postid, "article_edited", "Article ".$postid." was edited by ".$guid, $guid);
	$result = $lhg_price_db->query($safe_sql);

	$error = $lhg_price_db->last_error;
    	if ($error != "") { var_dump($error); exit; }

        return;
}



#
#
## hook related functions


function lhg_post_history_save_post( $postid ) {


	#if ( wp_is_post_revision( $postid ) ) return;

        #error_log("post $postid saved");
}

function lhg_post_history_edit_post( $postid ) {
        #error_log("post $postid modified");

        $uid  = get_current_user_id();
        $guid = lhg_get_guid_from_uid( $uid );

        # write to history that article was published
        lhg_post_history_edit ( $postid, $guid);

}

function lhg_post_history_publish_post( $post ) {

        $postid = $post->ID;
        $uid  = get_current_user_id();
        $guid = lhg_get_guid_from_uid( $uid );

        # write to history that article was published
        lhg_post_history_published ( $postid, $guid);
}

function lhg_post_history_created_post( $postid, $post ) {


	#if ( wp_is_post_revision( $postid ) ) error_log("cond1");
	#if ( !wp_is_post_revision( $postid ) ) error_log("cond2");
        #
        #error_log("post $postid created? $update");
}



?>
