<?php
function lhg_db_get_cheapest_supplier_id($postid) {

        global $region;
        $shopids = lhg_return_shop_ids($region);

        //which shop has this product?
        $products = lhg_get_sorted_products($shopids,$postid, false);

        //return first one = cheapest
        return $products[0]["shop_id"];

}

function lhg_db_get_cheapest_price($postid) {

        global $region;
        $shopids = lhg_return_shop_ids($region);

        //which shop has this product?
        $products = lhg_get_sorted_products($shopids,$postid, false);
        $price = $products[0]["price"]+$products[0]["shipping"];

        //return first one = cheapest
        return $price;

}

function lhg_db_get_cheapest_price_by_region($postid, $search_region) {

        global $region;
        #echo "RG: $region";
        #print "PID: $postid<br>";
        if ( ($search_region == "de") && ($region != "de") ) $postid = lhg_get_postid_de_from_com( $postid );
        #print "PID2: $postid<br>";
        $shopids = lhg_return_shop_ids($search_region);

        #print "SIDS<br>";
        #var_dump($shopids);

        //which shop has this product?
        $products = lhg_get_sorted_products($shopids,$postid, $search_region);
        $price = $products[0]["price"]+$products[0]["shipping"];

        //return first one = cheapest
        return $price;

}

function lhg_show_supplier_square($shopid,$postid) {
        global $region;
        global $txt_buy_from; //defined by amazon-product-in-a-post.php
        global $txt_cheapest_supplier;
        global $txt_supplier;

        //get product info
        //needs info in ->id field!
        $shopids[0]->id=$shopid;
	$products = lhg_get_sorted_products($shopids,$postid, false);
        $url = $products[0]["url"];
        $price = $products[0]["price"]+$products[0]["shipping"];
	$img = lhg_get_shop_square_icon($shopid);
	$name = lhg_get_shop_long_name($shopid);

        #if ($postid == 24875) echo "P: ".$products[0]["price"]." - S: ".$products[0]["shipping"]."<br>";
        #if ($postid == 40247 ) print "DEBUG: $url";

        $shipping  = $products[0]["shipping"];
        $price_net = $products[0]["price"];

        $shipping = lhg_amazon_price_to_float( $shipping , $region );
        $price_net = lhg_amazon_price_to_float( $price_net , $region );

        $price = $shipping + $price_net;
        #if ($postid == 24875) echo "P: ".$price." - S: ".$shipping."<br>";


	$txt_button_string = $txt_buy_from." $name";

	$title=translate_title(get_the_title());
	$s=explode("(",$title);
	$short_title=trim($s[0]);


        #echo "Show supplier";
        #echo "<br>URL: $url";
        #echo "<br>img: $img";
        #echo "<br>Name: $name";
        #echo "<br>Price: $price";

        /*
        $price_meta = "USD";
        if ($region == "de") $price_meta = "EUR";
        if ($region == "it") $price_meta = "EUR";
        if ($region == "fr") $price_meta = "EUR";
        if ($region == "nl") $price_meta = "EUR";
        if ($region == "es") $price_meta = "EUR";
        if ($region == "com") $price_meta = "USD";
        if ($region == "co.uk") $price_meta = "GBP";
        if ($region == "co.jp") $price_meta = "JPY";
        if ($region == "cn") $price_meta = "CNY";
        if ($region == "ca") $price_meta = "CAD";
        if ($region == "in") $price_meta = "INR";
        */

        $price = lhg_float_to_currency_string( $price , $region );
        $currency = lhg_get_currency_symbol( $region );


        echo "\n".'<div class="rating" style="border: 0px solid #222; width: 70%; text-align: center; margin: 0 auto;">';
	echo "\n".'<div style="text-align: center;"><b>'.$txt_supplier.':</b></div>';

        //echo "\n".'<meta itemprop="priceCurrency" content="'.$price_meta.'" />';

        echo "\n".'<div class="amazonbutton">
         	      <a href="'.$url.'" rel="nofollow"><img src="'.$img.'" border="0" width="125" height="125" alt="'.$txt_button_string.': '.$short_title.'" title="'.$txt_button_string.': '.$short_title.'" id="supplier_square" /></a>
                   </div>';

        echo $currency.'&nbsp;'.$price.' <br />  <a href="'.$url.'" rel="nofollow" >'.$name.'</a>';

        echo "\n"."</div>";

 

}

function lhg_show_supplier_square_default( $postid ) {

        global $region;
        global $txt_not_avail;

        $region_tmp = $region;
        if ($region == "nl") $region_tmp = "de";

        $txt_button_string = $txt_not_avail."
Amazon.".$region_tmp;
        $img = "/wp-uploads/2012/10/Amazon_Logo1.png";

        $meta="url-amazon.".$region;
        $url=get_post_meta($postid,$meta,true); //amaz_url($post->ID);

        // see if we have "Check Amazon for pricing"
        $meta="price-amazon.".$region;
        $aprice = get_post_meta($postid,$meta,true);

	$title=translate_title(get_the_title());
	$s=explode("(",$title);
	$short_title=trim($s[0]);

        #if ($postid == 40247 ) print "DEBUG";
        #echo "AP: $aprice";

    if ( ($aprice != "Check Amazon For Pricing") and ($region != "nl") ) {

        //URL still undefined?
	if ($url == ""){
	  $aid=get_id();
          $url = 'http://www.amazon.'.$region_tmp.'/?_encoding=UTF8&camp=15121&creative=390961&linkCode=ur2&tag='.$aid;
        }

        #print_r($url);

/*
//not available - reduce number of outgoing links

        echo "\n".'<div class="rating" style="border: 0px solid #222; width: 70%; text-align: center; margin: 0 auto;">';

        echo "\n".'<div class="amazonbutton">
         	      <a href="'.$url.'" rel="nofollow"><img src="'.$img.'" border="0" width="125" height="125" alt="'.$short_title.'
'.$txt_button_string.'" title="'.$short_title.'
'.$txt_button_string.'" /></a>
                   </div>';
        echo $txt_not_avail."<br />";
        echo '<a href="'.$url.'" rel="nofollow">Amazon.'.$region_tmp.'</a>';

        echo "\n"."</div>";
*/

        global $no_supplier_square;
        $no_supplier_square = 1;

        #echo $txt_not_avail.' &gt; <a href="'.$aurl.'" rel="nofollow"></a>';


        //$shopids = lhg_return_shop_ids($region);

        //which shop has this product?
        //$products = lhg_get_sorted_products($shopids,$postid);

        //return first one = cheapest
        //echo "Show default";

    }elseif ($region == "nl"){
        //do not show anything, if nothing found

    }else{

        echo "\n".'<div class="rating" style="border: 0px solid #222; width: 70%; text-align: center; margin: 0 auto;">';

        echo "\n".'<div class="amazonbutton">
         	      <a href="'.$url.'" rel="nofollow"><img src="'.$img.'" border="0" width="125" height="125" alt="Available at Amazon.com: '.$short_title.'" title="Available at Amazon.com: '.$short_title.'" /></a>
                   </div>';
        echo "Check Amazon for pricing<br />";
        echo '<a href="'.$url.'" rel="nofollow">Amazon.'.$region.'</a>';

        echo "\n"."</div>";

        #echo $txt_not_avail.' &gt; <a href="'.$aurl.'" rel="nofollow"></a>';


        //$shopids = lhg_return_shop_ids($region);

        //which shop has this product?
        //$products = lhg_get_sorted_products($shopids,$postid);


    }

}

function lhg_supplier_comparison($postid) {

        global $region;

        //echo "Anbieter-&Uuml;bersicht:";
        //echo "<br>";
        //echo "PID: $postid<br>";

        //echo "REG: $region";

        //what shops are relevant?
        //echo "REg: $region<br>";
        $shopids = lhg_return_shop_ids($region);

        #print "SIDS:<br>";
        #var_dump($shopids);
        #print "SIDS-ID:<br>";
        #var_dump($shopids->id);
        // do not show amazon prices (already locally included, for now)
        // 4 = amazon.de
        $del_val = 4;
        $num = count($shopids);
        for ($i = 0; $i<$num; $i++)  {
                $val = $shopids[$i]->id;
	        #print "NUM: $i - $val<br>";
                if ($val == $del_val) unset($shopids[$i]->id);
	}


        //which shop has this product?
        $products = lhg_get_sorted_products($shopids,$postid,false);

        lhg_insert_overview_table($products);

}


function lhg_return_shop_ids($region) {

        global $lhg_price_db;
        //echo "SQL request";
        //echo $lhg_price_db;


        $sql = "SELECT id FROM `lhgshops` WHERE region = \"".$region."\"";

        //show all languages in backend
        if (is_admin())
        if ($region == "com")
        $sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";

        $result = $lhg_price_db->get_results($sql);

        //var_dump($result);

        return $result;

}


function lhg_get_sorted_products($shopids_input,$productid, $region) {

        global $region;

        //check if input is an array or single shop integer
        if (!is_array($shopids_input)) {
        	$shopids[0]->id = $shopids_input;
	}else {
        	$shopids = $shopids_input;
        }

        global $lhg_price_db;

        $n_result = 0;

        foreach ($shopids as $shopid)  {

                $sid = $shopid->id;
                #if ($productid == 40247) print "SID: $sid <br>";

                //ToDo: support for multiple products per shop needed (e.g. Amazon)
                //echo "SQL request";
        	//echo $lhg_price_db;

	        $sql = "SELECT id, shop_id, shop_last_price, shop_shipping_costs, shop_url, last_update  FROM `lhgprices` WHERE shop_id = \"".$sid."\" AND lhg_article_id = \"".$productid."\" AND shop_last_price != \"Too low to display\"";
                #echo "SQL: $sql";
    		$result[$n_result] = $lhg_price_db->get_results($sql);
                #if ($productid ==26619) print_r ($result[$n_result]);
                $n_result += 1;

	}

        //$result_tmp = $result[0][0];
        //print_r ($result);

        // sort array

        #$total[0]  = 0;
        #$sortid[0] = 0;
        $foundproducts = 0;
        for ($i = 0; $i< $n_result; $i++) {
                $price 	= $result[$i][0]->shop_last_price;
                $ship 	= $result[$i][0]->shop_shipping_costs;

	        #if ($productid == 40247) print "<br>price0: $price";

                $price = lhg_amazon_price_to_float($price, $region);
                $ship  = lhg_amazon_price_to_float($ship, $region);

	        //print "Price ".$result[$i][0]->shop_id.": $price + $ship";
	        #if ($productid == 26619) print "<br>price: $price";
		#if ($productid == 26619) var_dump($rval);

                //skip empty results, even ignore if only shipping costs are available
        	if ($price  > 0) {
                	$total[$i]  = $price + $ship;
	                $sortid[$i] = $i;
                        $foundproducts +=1 ;
		}
                //print "<br>";
	}

        #if ($productid == 40247) print "<br>FP: $foundproducts";


        //add Amazon information
        // ToDo: currently information is from Wordpress DB, should be retrieved from PriceDB
        // ToDo: PriceDB needs to be filled by amazon-product-in-post plugin for that to work

/*

        if ( ( $region == "de") and ( get_region() != "de") ) {
                # searching for de price on com webpage
                # get_post_meta would be empty
	} else  {
          //echo "Search for Amazon";
          //get price & url from DB
          #print "REG: $region<br>";
          if ($region == "") $region=get_region();
          $meta="price-amazon.".$region;
          $aprice = get_post_meta($productid,$meta,true);

          $meta="url-amazon.".$region;
          $aurl =get_post_meta($productid,$meta,true); //amaz_url($post->ID);
          //$aurl stays empty for new articles

          //echo "Aprice: $aprice<br>";

          if ( ($aprice!='out of stock') and ($aprice!='nicht lieferbar') and ($aprice!='Check Amazon For Pricing')  and ($aurl != "") )   {

                //echo "adding Amazon";

                $i += 1;

                #$meta="url-amazon.".$region;
        	#$aurl =get_post_meta($productid,$meta,true); //amaz_url($post->ID);
                #$aurl 	= $result[][0]->shop_url;
                #if ($productid == 40247) print "URL: $url <br>";

	        $price = lhg_amazon_price_to_float ( $aprice , $region );

                //echo "URL: $aurl<br>";
                //echo "Reg: $region";
                $result[$i][0]->shop_last_price = $price;
                $result[$i][0]->shop_url = $aurl;
                $result[$i][0]->shop_id = "-1";

                if ($region == "de")
                $result[$i][0]->shop_id = "4";

                if ($region == "it")
                $result[$i][0]->shop_id = "6";

                if ($region == "com")
                $result[$i][0]->shop_id = "7";

                if ($region == "co.uk")
                $result[$i][0]->shop_id = "8";

                if ($region == "ca")
                $result[$i][0]->shop_id = "9";

                if ($region == "fr")
                $result[$i][0]->shop_id = "10";

                if ($region == "in")
                $result[$i][0]->shop_id = "11";

                if ($region == "co.jp")
                $result[$i][0]->shop_id = "12";

                if ($region == "cn")
                $result[$i][0]->shop_id = "13";

                if ($region == "es")
                $result[$i][0]->shop_id = "14";

                $total[$i]  = $price + $ship;
	        $sortid[$i] = $i;

                $foundproducts +=1 ;

	  }
        }

*/
        //var_dump($sortid);
        if (is_array($total))
        array_multisort($total, $sortid);
        #if ($productid == 40247) print "TTT:<br>"; #var_dump($sortid);
        #if ($productid == 40247) var_dump($result);


        //format output
        //$rval = array("url","price","shipping","shopid");

        for ($i = 0; $i< $foundproducts; $i++) {
                //echo "SID: $sortid[$i]";
        	$rval[$i]["url"] = $result[$sortid[$i]][0]->shop_url;
        	$rval[$i]["shop_id"] = $result[$sortid[$i]][0]->shop_id;
        	$rval[$i]["last_update"] = $result[$sortid[$i]][0]->last_update;
        	$rval[$i]["id"] = $result[$sortid[$i]][0]->id;


                $price 	= $result[$sortid[$i]][0]->shop_last_price;
                $ship 	= $result[$sortid[$i]][0]->shop_shipping_costs;

                $price = lhg_amazon_price_to_float($price, $region);
                $ship  = lhg_amazon_price_to_float($ship, $region);
                #$price = lhg_price_clean_format($price);
                #$ship  = lhg_price_clean_format($ship);

        	$rval[$i]["price"] = $price;
        	$rval[$i]["shipping"] = $ship;

	        //var_dump($rval);
	}

	#echo "<p>DUMP:";
	#if ($productid == 40247) echo "<p>DUMP: ";
	#if ($productid == 26619) var_dump($rval);

        // Set values locally for faster access by category-post-list
          $setprice = $rval[0]["price"];
          if ( ($setprice == "") or ($setprice == 0) )  $setprice= "out of stock";
          if ($region == "") $region=get_region();
          $meta="price-amazon.".$region;
          update_post_meta($productid,$meta,$setprice);
        //

        return $rval;

}

function lhg_price_clean_format($price) {
        $price = str_replace("EUR","",$price);
        return $price;
}

function lhg_insert_overview_table($products) {

        //echo "<br>Num Prod:".count($products);
        //echo "<br>ID: ".$products[0]["shop_id"];
        //print_r($products);
        //only amazon, then skip
        //$region == get_region();
        global $region;
        //echo "<br>REG: $region";
        if ($region == "de") $amazon_shop_id = 4;
        //echo "ASID: $amazon_shop_id";
        if ( (count($products) == 1) && ($products[0]["shop_id"] == $amazon_shop_id ) ) {
                //only Amazon
                //skip
	}elseif ( (count($products) == 0)  ) {
                //nothing found
                //skip
	}elseif ( (count($products) == 1)  ) {
                //only one found, which is already shown as main square
                //skip
	}elseif ( (count($products) == 1)  && $products[0]["shop_id"] == -1) {
                //something strange e.g. only Amazon on .com sites
                //skip
	} else  {


	        $price_meta = "USD";
        	if ($region == "de") $price_meta = "EUR";
	        if ($region == "it") $price_meta = "EUR";
        	if ($region == "fr") $price_meta = "EUR";
	        if ($region == "es") $price_meta = "EUR";
        	if ($region == "com") $price_meta = "USD";
	        if ($region == "co.uk") $price_meta = "GBP";
        	if ($region == "co.jp") $price_meta = "JPY";
	        if ($region == "cn") $price_meta = "CNY";
        	if ($region == "ca") $price_meta = "CAD";
	        if ($region == "in") $price_meta = "INR";

		$vspacer = '<div class="spacer" style="border-top: 1px solid #2b8fc3; height: 0px; margin-top: 9px; margin-bottom: 9px;"></div>';
        	echo $vspacer;

                global $txt_lhgdb_overview;
                global $txt_lhgdb_exclporto;
                global $txt_lhgdb_inclporto;
                echo "<b>$txt_lhgdb_overview:</b>";

                echo "<br />
                <table>";

		for ($i = 0; $i< count($products); $i++) {

                        $shop_img  = lhg_get_shop_small_icon($products[$i]["shop_id"]);
                        $shop_name = lhg_get_shop_name($products[$i]["shop_id"]);
                        $shop_name_long = lhg_get_shop_long_name($products[$i]["shop_id"]);

                	//print "$i: ".$products[$i]["shop_id"]."<br>";
                	// print "$i: ".$products[$i]["last_update"]."<br>";

                        if ( (strpos($shop_name,"mazon") > 0) or (strpos($products[$i]["shipping"],"ULL") > 0 ) ){

                                // no shipping costs available

                        	echo '<tr><td>EUR&nbsp;'.lhg_float_to_currency_string($products[$i]["price"] + $products[$i]["shipping"],$region).'<br /><div class="portoline">'.$txt_lhgdb_exclporto.'</div>
                                </td><td valign="middle"><a href="'.$products[$i]["url"].'" rel="nofollow">
        	                <img src="'.$shop_img.'" style="border: 1px solid #2B8FC3;" title="'.$shop_name_long.'" alt="'.$shop_name_long.'" /></a></td>';
                                #<td>
                	        #<a href="'.$products[$i]["url"].'" rel="nofollow">'.$shop_name.'</a></td></tr>';
			}else{
                        	echo '<tr><td>EUR&nbsp;'.lhg_float_to_currency_string($products[$i]["price"] + $products[$i]["shipping"],$region).'<br /><div class="portoline">'.$txt_lhgdb_inclporto.': '
	                        .lhg_float_to_currency_string($products[$i]["shipping"],$region).'&nbsp;EUR</div></td><td valign="middle"><a href="'.$products[$i]["url"].'" rel="nofollow">
        	                <img src="'.$shop_img.'" style="border: 1px solid #2B8FC3;" title="'.$shop_name_long.'"></a></td>';#<td>
                	        #<a href="'.$products[$i]["url"].'" rel="nofollow">'.$shop_name.'</a></td></tr>';
                        }

		}

                echo"</table>";


	}
}

function lhg_get_shop_small_icon($shopid) {

        global $lhg_price_db;

        $sql = "SELECT image_small FROM `lhgshops` WHERE id = \"".$shopid."\"";
    	$shop_img = $lhg_price_db->get_var($sql);

        return $shop_img;
}

function lhg_get_shop_square_icon($shopid) {

        global $lhg_price_db;

        $sql = "SELECT image_square FROM `lhgshops` WHERE id = \"".$shopid."\"";
    	$shop_img = $lhg_price_db->get_var($sql);

        return $shop_img;
}

function lhg_get_shop_name($shopid) {

        global $lhg_price_db;

        $sql = "SELECT name FROM `lhgshops` WHERE id = \"".$shopid."\"";
    	$shop_name = $lhg_price_db->get_var($sql);

        return $shop_name;
}

function lhg_get_shop_long_name($shopid) {

        global $lhg_price_db;

        $sql = "SELECT name_long FROM `lhgshops` WHERE id = \"".$shopid."\"";
    	$shop_name = $lhg_price_db->get_var($sql);

        return $shop_name;
}

?>
