<?php

# Get lowes Amazon price from DB
# needed for replacement of old grabber script (amazon-product plugin)
function lhg_db_get_lowest_amazon_price($postid, $region) {

        $shopid = 0;

        if ($region == "de")    $shopid = 4;
        if ($region == "it")    $shopid = 6;
        if ($region == "com")   $shopid = 7;
        if ($region == "co.uk") $shopid = 8;
        if ($region == "ca")    $shopid = 9;
        if ($region == "fr")    $shopid = 10;
        if ($region == "in")    $shopid = 11;
        if ($region == "co.jp") $shopid = 12;
        if ($region == "cn")    $shopid = 13;
        if ($region == "es")    $shopid = 14;

        if ($shopid == 0) return;


        global $lhg_price_db;
        $sql = "SELECT shop_last_price_float FROM `lhgprices` WHERE (lhg_article_id  = ".$postid." AND shop_id = ".$shopid.") AND shop_last_price_float > 0 ORDER BY shop_last_price_float";
	$result = $lhg_price_db->get_var($sql);
        #print "<br>SQL: $sql";
        if ($result == "") $result = 0;
        return $result;

}

# Create row for Amazon price in DB
function lhg_amazon_create_db_entry( $region, $postid, $asin ){

        #print "Reg: $region";
        #print "PID: $postid";
        #print "AID: $asin<br>";
        #print "Pr: $price<br>";
        #print "URL: $url<br>";
        #print "Date: $time<br>";

        $shopid = 0;

        # Amazon shop ids
        # could be extracted from DB, if needed for flexibility
        if ($region == "de")    $shopid = 4;
        if ($region == "it")    $shopid = 6;
        if ($region == "com")   $shopid = 7;
        if ($region == "co.uk") $shopid = 8;
        if ($region == "ca")    $shopid = 9;
        if ($region == "fr")    $shopid = 10;
        if ($region == "in")    $shopid = 11;
        if ($region == "co.jp") $shopid = 12;
        if ($region == "cn")    $shopid = 13;
        if ($region == "es")    $shopid = 14;

        if ($shopid == 0) return;

        # check if entry exists
        $DBid = lhg_amazon_db_entry_exists($shopid, $postid, $asin);
        #print "<br>DBID: $DBid";

        if ($DBid > 0) {
                # Update DB entry
                # nothing to do
	}else{
                #create new entry but without price information
                $time = 1; # ensures update by script
	        global $lhg_price_db;
	        $sql = "INSERT INTO lhgprices (lhg_article_id, shop_id, shop_article_id, last_update)
    				VALUES ('$postid', '$shopid', '$asin', '$time' )";
		$result = $lhg_price_db->query($sql);
                $error = $lhg_price_db->last_error;
		if ($error != "") var_dump($error);

                # Create DB entry
		#lhg_create_amazon_db_entry($shopid, $postid, $asin, $price, $url, $time);
        }
}

function lhg_amazon_price_to_db($region, $postid, $asin, $price, $url, $time){

        #print "Reg: $region";
        #print "PID: $postid";
        #print "AID: $asin<br>";
        #print "Pr: $price<br>";
        #print "URL: $url<br>";
        #print "Date: $time<br>";

        $shopid = 0;

        # Amazon shop ids
        # could be extracted from DB, if needed for flexibility
        if ($region == "de")    $shopid = 4;
        if ($region == "it")    $shopid = 6;
        if ($region == "com")   $shopid = 7;
        if ($region == "co.uk") $shopid = 8;
        if ($region == "ca")    $shopid = 9;
        if ($region == "fr")    $shopid = 10;
        if ($region == "in")    $shopid = 11;
        if ($region == "co.jp") $shopid = 12;
        if ($region == "cn")    $shopid = 13;
        if ($region == "es")    $shopid = 14;

        if ($shopid == 0) return;

        # check if entry exists
        $DBid = lhg_amazon_db_entry_exists($shopid, $postid, $asin);
        #print "<br>DBID: $DBid";

        if ($DBid > 0) {
                # Update DB entry
		lhg_update_amazon_db_entry($DBid, $price, $url, $time);
	}else{
                # Create DB entry
		lhg_create_amazon_db_entry($shopid, $postid, $asin, $price, $url, $time);
        }
}


# Check if DB entry already exists. Return id of entry.
# 0 means: no entry found
function lhg_amazon_db_entry_exists($shopid, $postid, $asin){

        global $lhg_price_db;
        $sql = "SELECT id FROM `lhgprices` WHERE (lhg_article_id = ".$postid." AND shop_id = ".$shopid.") AND shop_article_id = '".$asin."'";
	$result = $lhg_price_db->get_var($sql);
        #print "<br>SQL: $sql";
        if ($result == "") $result = 0;
        return $result;
}

# create entry in lhgprices DB
function lhg_create_amazon_db_entry($shopid, $postid, $asin, $price, $url, $time){

        global $lhg_price_db;
        $sql = "INSERT INTO lhgprices (lhg_article_id, shop_id, shop_url, shop_article_id, last_update, shop_last_price)
    			VALUES ('$postid', '$shopid', '$url', '$asin', '$time' , '$price' )";

	$result = $lhg_price_db->query($sql);

        $error = $lhg_price_db->last_error;
	if ($error != "") var_dump($error);

    	return $result;

}

# update existing price entry
function lhg_update_amazon_db_entry($id, $price, $url, $time){

        #print "<br>UPDATE";

        global $lhg_price_db;
        $sql = "UPDATE lhgprices SET shop_url = '$url' , last_update = '$time', shop_last_price = '$price'
        		WHERE id = $id";

	$result = $lhg_price_db->query($sql);

        $error = $lhg_price_db->last_error;
	if ($error != "") var_dump($error);

    	return $result;

}


?>
