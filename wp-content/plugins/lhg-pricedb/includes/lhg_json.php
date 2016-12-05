<?php

# Json interface to LHG database

function lhg_url_request_json( ) {


        if ($_POST["request"] == "create_article_translation") {
                lhg_json_request_create_article_translation( $_POST, "create" );
	}elseif ($_POST["request"] == "article_translation_update"){
                lhg_json_request_create_article_translation( $_POST, "update" );
	}elseif ($_POST["request"] == "move_comment"){
                lhg_json_request_move_comment( $_POST );
        }else{
        	error_log("Unknown request type: ".$_POST["request"]);
        }

        exit;
}


function lhg_json_request_create_article_translation( $data , $request_type ) {

        #error_log("autocreate: $request_type - data:".json_encode($data) );

	global $lhg_price_db;

        #check guid
        # auto translation only allowed from "admin"
        if ($_SERVER['SERVER_ADDR'] == "192.168.56.12") {
                $allowed_guid = 9;
        }

        if ($_SERVER['SERVER_ADDR'] == "192.168.56.13") {
                $allowed_guid = 9;
        }

        if ($_SERVER['SERVER_ADDR'] == "192.168.3.112") {
                $allowed_guid = 22;
        }

        if ($_SERVER['SERVER_ADDR'] == "192.168.3.113") {
                $allowed_guid = 22;
        }



        if ( ($data["guid"]) == $allowed_guid) {
                $guid = $allowed_guid;
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
                if ($request_type == "create") {
	        	if ( $data["postid_server"] == "com" ) $sql = "SELECT postid_de FROM `lhgtransverse_posts` WHERE postid_com = \"%s\" ";
        		if ( $data["postid_server"] == "de" ) $sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE postid_de = \"%s\" ";
			$safe_sql = $lhg_price_db->prepare( $sql, $data["postid"] );
			$transverse_postid = $lhg_price_db->get_var($safe_sql);

        	        if ( $transverse_postid == 0 ) {
                	        # post not yet translated
	                } else {
		                lhg_json_error("article_translated", $transverse_postid );
                	}
                }

                # all tests passed - start translation
                if ($request_type == "create") lhg_create_article_translation( $data["postid"], $data["postid_server"], $data );
                if ($request_type == "update") lhg_update_article_translation( $data["postid"], $data["postid_server"], $data );


	}else{
                lhg_json_error("unknown_postid", $data["postid"] );
	}

}

function lhg_json_request_move_comment( $data ) {

        #error_log("autocreate: $request_type");

	global $lhg_price_db;

        #check guid
        # auto translation only allowed from "admin"
        if ($_SERVER['SERVER_ADDR'] == "192.168.56.12") {
                $allowed_guid = 9;
        }

        if ($_SERVER['SERVER_ADDR'] == "192.168.56.13") {
                $allowed_guid = 9;
        }

        if ($_SERVER['SERVER_ADDR'] == "192.168.3.112") {
                $allowed_guid = 22;
        }

        if ($_SERVER['SERVER_ADDR'] == "192.168.3.113") {
                $allowed_guid = 22;
        }

        if ( ($data["comment_id"]) > 0) {
                # $guid = $allowed_guid;
	}else{
                lhg_json_error("no_comment_id", $data["comment_id"] );
	}


        #check password
        if ( $data["password"] != "" ) {
                $sql = "SELECT json_password FROM `lhgtransverse_users` WHERE id = \"%s\" ";
		$safe_sql = $lhg_price_db->prepare( $sql, $allowed_guid );
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
        if ( ( $data["commentid_server"] == "com" ) OR ( $data["commentid_server"] == "de" ) ) {
                # either "com" or "de" server indicated as origin
	}else{
                lhg_json_error("unknown_server", $data["commentid_server"] );
	}


        #check postid
        if ( is_numeric( $data["comment_postid"] ) ) {

                # check if original article exists
        	if ( $data["commentid_server"] == "com" ) $sql = "SELECT id FROM `lhgtransverse_posts` WHERE postid_com = \"%s\" ";
        	if ( $data["commentid_server"] == "de" ) $sql = "SELECT id FROM `lhgtransverse_posts` WHERE postid_de = \"%s\" ";
		$safe_sql = $lhg_price_db->prepare( $sql, $data["comment_postid"] );
		$dbid = $lhg_price_db->get_var($safe_sql);


                # check if postid exists in DB
                if ( $dbid > 0 ) {
                        # postid exists
                } else {
	                lhg_json_error("unknown_postid", $data["comment_postid"] );
                }

                # check if article already translated
                if ($request_type == "create") {
	        	if ( $data["commentid_server"] == "com" ) $sql = "SELECT postid_de FROM `lhgtransverse_posts` WHERE postid_com = \"%s\" ";
        		if ( $data["commentid_server"] == "de" ) $sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE postid_de = \"%s\" ";
			$safe_sql = $lhg_price_db->prepare( $sql, $data["comment_postid"] );
			$transverse_postid = $lhg_price_db->get_var($safe_sql);

        	        if ( $transverse_postid == 0 ) {
		                lhg_json_error("article_not_translated", $transverse_postid );
	                } else {
                                #
                	}
                }

                # all tests passed - start translation
		 lhg_create_comment_by_json_request( $data );

	}else{
                lhg_json_error("unknown_comment", $data["comment_id"] );
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
                        'error_message' => "Unknown post ID: $value"
                        );

	} elseif ($type == "unknown_server") {
                $data = array (
                	'error_code' => 4,
                        'error_message' => "Unknown server: $value"
                        );

	} elseif ($type == "article_translated") {
                $data = array (
                	'error_code' => 5,
                        'error_message' => "Article is already translated: $value"
                        );

	} elseif ($type == "no_comment_id") {
                $data = array (
                	'error_code' => 6,
                        'error_message' => "No comment ID provided: $value"

                        );

	} elseif ($type == "article_not_translated") {
                $data = array (
                	'error_code' => 7,
                        'error_message' => "Article is not yet translated: $value"
                        );

	} elseif ($type == "unknown_comment") {
                $data = array (
                	'error_code' => 8,
                        'error_message' => "Unknown comment: $value"
                        );


	} else {
                $data = array (
                	'error_code' => 999,
                        'error_message' => "Unknown error: $type, $value"
                        );
        } 

        error_log("JSON error: ".json_encode($data));
        print json_encode($data);
        exit;
}



?>
