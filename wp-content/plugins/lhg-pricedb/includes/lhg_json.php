<?php

# Json interface to LHG database

function lhg_url_request_json( ) {


        if ($_POST["request"] == "create_article_translation")
                lhg_json_request_create_article_translation( $_POST );


        exit;
}


function lhg_json_request_create_article_translation( $data ) {

	global $lhg_price_db;

        #check guid
        # auto translation only allowed from "admin"
        if ( ($data["guid"]) == 22) {
                $guid = 22;
	}else{
                lhg_json_error("invalid_guid", $data["guid"] );
	}


        #check password
        if ( $data["password"] != "" ) {
                $sql = "SELECT json_password FROM `lhgtransverse_users` WHERE id = \"%s\" ";
		$safe_sql = $lhg_price_db->prepare( $sql, $guid );
		$password = $lhg_price_db->get_var($safe_sql);

                if ( $password ==  $data["password"] ) {
                        # password valid
                } else {
	                lhg_json_error("invalid_password", "");
                }

	}else{
                lhg_json_error("invalid_password", "");
	}


        #check postid server
        if ( ( $data["postid_server"] == "com" ) OR ( $data["postid_server"] == "de" ) ) {
                # either "com" or "de" server indicated as origin
	}else{
                lhg_json_error("unknown_server", $data["postid_server"] );
	}


        #check postid
        if ( is_numeric( $data["postid"] ) ) {

        	if ( $data["postid_server"] == "com" ) $sql = "SELECT id FROM `lhgtransverse_posts` WHERE postid_com = \"%s\" ";
        	if ( $data["postid_server"] == "de" ) $sql = "SELECT id FROM `lhgtransverse_posts` WHERE postid_de = \"%s\" ";
		$safe_sql = $lhg_price_db->prepare( $sql, $data["postid"] );
		$dbid = $lhg_price_db->get_var($safe_sql);

                # check if postid exists in DB
                if ( $dbid > 0 ) {
                        # postid exists
                } else {
	                lhg_json_error("unknown_postid", $data["postid"] );
                }

                # check if article already translated
        	if ( $data["postid_server"] == "com" ) $sql = "SELECT postid_de FROM `lhgtransverse_posts` WHERE postid_com = \"%s\" ";
        	if ( $data["postid_server"] == "de" ) $sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE postid_de = \"%s\" ";
		$safe_sql = $lhg_price_db->prepare( $sql, $data["postid"] );
		$transverse_postid = $lhg_price_db->get_var($safe_sql);

                if ( $transverse_postid == 0 ) {
                        # post not yet translated
                } else {
	                lhg_json_error("article_translated", $transverse_postid );
                }

                # all tests passed - start translation
                lhg_create_article_translation( $data["postid"], $data["postid_server"] );



	}else{
                lhg_json_error("unknown_postid", $data["postid"] );
	}



}

function lhg_json_error( $type , $value ) {

        if ($type == "invalid_password") {
                $data = array (
                	'error_code' => 1,
                        'error_message' => "Invalid password"
                        );

	} elseif ($type == "invalid_guid") {
                $data = array (
                	'error_code' => 2,
                        'error_message' => "Invalid GUID: $value"
                        );

	} elseif ($type == "unknown_postid") {
                $data = array (
                	'error_code' => 3,
                        'error_message' => "Uknown post ID: $value"
                        );

	} elseif ($type == "unknown_server") {
                $data = array (
                	'error_code' => 4,
                        'error_message' => "Uknown server: $value"
                        );

	} elseif ($type == "article_translated") {
                $data = array (
                	'error_code' => 5,
                        'error_message' => "Article is already translated: $value"
                        );

	} else {
                $data = array (
                	'error_code' => 999,
                        'error_message' => "Unknown error: $type, $value"
                        );
        } 

        print json_encode($data);
        exit;
}



?>
