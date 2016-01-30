<?php

function lhg_get_shop_button($postid) {

        global $region;

        global $txt_on_stock;
        global $txt_out_of_stock;
        global $txt_shipping;
	global $txt_tooltip2;
        global $txt_button;
        global $txt_not_avail_at;
        global $txt_price;

        global $txt_search_at;
        global $txt_preorder;
        global $txt_buy_from;
        global $txt_shipping_costs;

        $tooltip= $txt_tooltip2; //"Die von Amazon zur Verf&uuml;gung gestellten Preise sind exklusive m&ouml;glicherweise zus&auml;tzlich anfallender Versandkosten (abh&auml;ngig vom jeweiligen Anbieter des Amazon-Marketplace).";

        //$button .= "PID: $postid<br>";

	$shopid = lhg_db_get_cheapest_supplier_id($postid);
	$products = lhg_get_sorted_products($shopid,$postid,false);
        $url = $products[0]["url"];
        $price = $products[0]["price"]; #+$products[0]["shipping"];

        #$price_total = $products[0]["price"]+$products[0]["shipping"];
        $shipping = $products[0]["shipping"];

        $shipping = lhg_float_to_currency_string( $shipping , $region );
        $price = lhg_float_to_currency_string( $price , $region );
	$name = lhg_get_shop_long_name($shopid);
        $txt_currency = lhg_get_currency_symbol( $region );



        //format button depending on availability
        if ($shopid != "")  {

                //product available at a shop

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


                $schema_start = '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">';
                $schema_end   = '</div>';

	        $button_string = '<i class="icon-shopping-cart icon-large3"></i>&nbsp;'.$txt_buy_from.' '.$name;
                $price_string = $txt_price.':&nbsp;'.$txt_currency.' <span itemprop="price">'.$price.'</span>

		        	<meta itemprop="priceCurrency" content="'.$price_meta.'" />

  				<span itemprop="availability" itemtype="http://schema.org/InStock" class="instock">
				    ('.$txt_on_stock.')
			        </span>';

                $shipping_string = '<span class="shippingcosts">
                			(+ '.$txt_shipping_costs.': '.$txt_currency.'&nbsp;'.$shipping.')
				    </span>';

                //unknown shipping costs for Amazon, add warning
                if (strpos($name,"mazon") > 0)
                $shipping_string = '<font size="1">
					('.$txt_shipping.')</font>
				       <span class="tooltip">'.tooltip($tooltip).'</span>
				     ';

                //no shipping costs in DB
                if (strpos($products[0]["shipping"],"ULL") > 0)
                $shipping_string = '<font size="1">
					('.$txt_shipping.')
				     </font>';


	}elseif($region == "nl"){
                //currently no Amazon.de shown


	}else{

	        // see if we have "Check Amazon for pricing"
        	$meta="price-amazon.".$region;
        	$aprice = get_post_meta($postid,$meta,true);

                #print "AP: $aprice";

	        if ($aprice != "Check Amazon For Pricing"){

                	$aid = get_id();

	                //nowhere available! not even in Amazon catalog
        	        //
                        $region_tmp = $region;
                        if ($region == "nl") $region_tmp = "de";

                	$price_string = '<span class="outofstock">('.$txt_out_of_stock.')</span>
                                <br /><div class="amazon-cr"></div>'.
                                $txt_never_avail_at.'
                                <a target="_blank" href="http://www.amazon.'.$region_tmp.'/?_encoding=UTF8&amp;camp=15121&amp;creative=390961&amp;linkCode=ur2&amp;tag='.$aid.'" rel="nofollow">
                                  Amazon.'.$region_tmp.'
                                </a>
                                <div class="amazon-cr"></div>';


		        $button_string = '<i class="icon-search icon-large3"></i>&nbsp;'.$txt_search_at.' Amazon.'.$region_tmp;
        	        $url = 'http://www.amazon.'.$region_tmp.'/?_encoding=UTF8&amp;camp=15121&amp;creative=390961&amp;linkCode=ur2&amp;tag='.$aid;

                	$shipping_string = ""; #'<font size="1">('.$txt_shipping.')
				       #<span class="tooltip">'.tooltip($tooltip).'<span>
				       #</font>';


	                // Check if at least a Amazon URL exists?!
        	        //
                	$meta="url-amazon.".$region;
	                $pageurl=get_post_meta($postid,$meta,true);
        	        if ($pageurl  != "") {


                                // not possible because we have no price ...
	                        //$schema_start = '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">';
        	                //$schema_end   = '</div>';

	                        $region_tmp = $region;
        	                if ($region == "nl") $region_tmp = "de";

                		//$price_string = '<span itemprop="availability" itemtype="http://schema.org/OutOfStock" class="outofstock">('.$txt_out_of_stock.')</span>
                		$price_string = '<span class="outofstock">('.$txt_out_of_stock.')</span>
                        	        <br /><div class="amazon-cr"></div>'.
                                $txt_not_avail_at.'
                                	<a target="_blank" href="http://www.amazon.'.$region_tmp.'/?_encoding=UTF8&amp;camp=15121&amp;creative=390961&amp;linkCode=ur2&amp;tag='.$aid.'" rel="nofollow">
	                                  Amazon.'.$region_tmp.'
        	                        </a>
                	                <div class="amazon-cr"></div>';

		        	$button_string = '<i class="icon-search icon-large3"></i>&nbsp;'.$txt_preorder.' Amazon.'.$region_tmp;
	                        $url = $pageurl;
			}

                        $do_not_show = 1;

                }else{

                	//echo "AAA";

                        $schema_start = '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">';
                        $schema_end   = '</div>';

                	//Check Amazon for pricing returned

	                $meta="url-amazon.".$region;
        	        $pageurl=get_post_meta($postid,$meta,true);

	                $price_string = '
                                <span itemprop="availability" itemtype="http://schema.org/InStock" class="instock">
				    '.$txt_on_stock.' at <a href="'.$pageurl.'">Amazon.com</a>
			        </span>';

                        $region_tmp = $region;
                        if ($region == "nl") $region_tmp = "de";

		        $button_string = '<i class="icon-search icon-large3"></i>&nbsp;Check Price at Amazon.'.$region_tmp;
                        $url = $pageurl;




            	}


    	}





        $button .= '
<div class="amaz-grey-box" style="border: 0px solid #eee; background-color: #eee;">
'.
    $schema_start .

    $price_string.'
  <br />';

if ($shipping_string != "")
        $button .= '
  <div class="shipping">'.
    $shipping_string.'
  </div>

  <br />';


        $button .= '

  <a href="'. $url .'" class="css_btn_class" rel="nofollow">'.
    $button_string.'
  </a>
'.
  $schema_end
  .'
</div>

<br />
';

        if ( ($region == "nl") and ($shopid == "") ) $button = "";
        if ( $do_not_show == 1 ) $button = "";

        return $button;

}

?>