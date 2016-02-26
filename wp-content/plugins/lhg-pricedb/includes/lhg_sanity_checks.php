<?php

function lhg_sanity_checks ( ) {
        echo "Sanity Checks";

        echo "<h2>Test 1: Redcoon Products</h2>";
        echo "Sanity Check:<br>Look for Products at Redcoon.de, copy to .it & .nl<br>";
        lhg_sanity_redcoon();

        echo "<h2>Update all ratings</h2>";
        #echo "Sanity Check:<br>Look for Products at Redcoon.de, copy to .it & .nl<br>";
        lhg_sanity_ratings();

}

function lhg_sanity_ratings ( ) {

        global $lang;
    	global $lhg_price_db;

	#write for com pages only
	if ($lang != "de") {

    		$sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE `status_com` != 'draft'";
	    	$results = $lhg_price_db->get_results($sql);

        	foreach ($results as $result) {

                	if ($result->postid_com != 0) {

                                $pid = intval($result->postid_com);

                        	$post_ratings_users   = get_post_meta($result->postid_com, 'ratings_users');
				$post_ratings_score   = get_post_meta($result->postid_com, 'ratings_score');
				$post_ratings_average = get_post_meta($result->postid_com, 'ratings_average');

                                # ignore empty ratings
	                	if ( ($post_ratings_users[0] != 0 ) && ($post_ratings_users[0] != "" )) {
        		                print "Post ID ".$result->postid_com." -> ".$post_ratings_users[0].", ".$post_ratings_score[0].", ".$post_ratings_average[0]."<br>";

                                        lhg_store_ratings ( $pid,
                                        $post_ratings_users[0],
                                        $post_ratings_score[0],
                                        $post_ratings_average[0] );
				}
			}

		}
	}
	if ($lang == "de") {

    		$sql = "SELECT postid_de FROM `lhgtransverse_posts` WHERE `status_com` != 'draft'";
	    	$results = $lhg_price_db->get_results($sql);

        	foreach ($results as $result) {

                	if ($result->postid_de != 0) {

                                $pid = intval($result->postid_de);

                        	$post_ratings_users   = get_post_meta($result->postid_de, 'ratings_users');
				$post_ratings_score   = get_post_meta($result->postid_de, 'ratings_score');
				$post_ratings_average = get_post_meta($result->postid_de, 'ratings_average');

                                # ignore empty ratings
	                	if ( ($post_ratings_users[0] != 0 ) && ($post_ratings_users[0] != "" )) {
        		                print "Post ID ".$result->postid_de." -> ".$post_ratings_users[0].", ".$post_ratings_score[0].", ".$post_ratings_average[0]."<br>";

                                        lhg_store_ratings ( $pid,
                                        $post_ratings_users[0],
                                        $post_ratings_score[0],
                                        $post_ratings_average[0] );
				}
			}

		}
	}

}


function lhg_sanity_redcoon ( ) {
        #echo "Searching for SID 1";


        $sid = 1;
    	global $lhg_price_db;
    	$sql = "SELECT lhg_article_id FROM `lhgprices` WHERE `shop_id` = 1 AND `shop_article_id` != 'NOT_AVAILABLE'";
    	$results = $lhg_price_db->get_results($sql);

        #print "<br>Results: ";
        #var_dump($results);
        #return $result;

        foreach ($results as $result) {
                $print_me = 0;

	    	$sql = "SELECT shop_article_id FROM `lhgprices` WHERE `shop_id` = 1 AND `lhg_article_id` = ".$result->lhg_article_id;
    		$shop_article_id = $lhg_price_db->get_var($sql);

	    	$sql = "SELECT permalink_de FROM `lhgtransverse_posts` WHERE `postid_de` = ".$result->lhg_article_id;
    		$shop_article_url_de = $lhg_price_db->get_var($sql);

	    	$sql = "SELECT permalink_com FROM `lhgtransverse_posts` WHERE `postid_com` = ".$result->lhg_article_id;
    		$shop_article_url_com = $lhg_price_db->get_var($sql);

                #print "<br>Res: "; var_dump($result);
                if ($shop_article_url_de != "") $output = '<br>ID_de: <a href="'.$shop_article_url_de.'">'. $result->lhg_article_id ."</a>";
                if ($shop_article_url_de == "") $output = '<br>ID_de: '. $result->lhg_article_id;

	    	$sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE `postid_de` = ".$result->lhg_article_id;
    		$pid_com = $lhg_price_db->get_var($sql);
                if ($shop_article_url_com != "") $output .= ' -> ID_com: <a href="'.$shop_article_url_com.'">'.$pid_com."</a>";
                if ($shop_article_url_com == "") $output .= ' -> ID_com: '.$pid_com;

                if ($pid_com != 0) {
                  #check if available
                  $output .= "-> check if already available (it:";

		  $sql = "SELECT id FROM `lhgprices` WHERE `lhg_article_id` = ".$pid_com." AND shop_id = 5";
    		  $price_id_it = $lhg_price_db->get_var($sql);

                  if ($price_id_it != "") $output .= " $price_id_it) ";
                  if ($price_id_it == "") $output .= " none) ";
                  if ($price_id_it == "") $print_me = 1;

                  $output .= " (nl:";
		  $sql = "SELECT id FROM `lhgprices` WHERE `lhg_article_id` = ".$pid_com." AND shop_id = 15";
    		  $price_id_nl = $lhg_price_db->get_var($sql);

                  if ($price_id_nl != "") $output .= " $price_id_nl) ";
                  if ($price_id_nl == "") $output .= " none) ";
                  if ($price_id_nl == "") $print_me = 1;


                  #create entry
                  if ($price_id_it == "") {
			$output .= "<br>&nbsp;&nbsp;-> create entry IT ";

                        $shop_id = 5;
                        $post_id = $pid_com;
			$result = lhg_db_create_entry( $post_id, $shop_id, $shop_article_id );
			$output .= ".. done";
			//get newly created lhg_db_id
			$lhg_db_id = lhg_db_get_id ( $post_id, $shop_id);
		        lhg_refresh_database_entry($lhg_db_id,$shop_id);
                        $print_me = 1;
	          }
                  if ($price_id_nl == "") {
			$output .= "<br>&nbsp;&nbsp;-> create entry NL ";

                        $shop_id = 15;
                        $post_id = $pid_com;
			$result = lhg_db_create_entry( $post_id, $shop_id, $shop_article_id );
			$output .= ".. done";
			//get newly created lhg_db_id
			$lhg_db_id = lhg_db_get_id ( $post_id, $shop_id);
		        lhg_refresh_database_entry($lhg_db_id,$shop_id);
                        $print_me = 1;
	          }



		}else{
                  $output .= "-> Product not found in COM DB";
                  $print_me = 1;

                }

        if ($print_me == 1) print $output;
	}
}

