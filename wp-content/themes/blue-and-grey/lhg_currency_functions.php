<?php

function lhg_float_to_currency_string ($float, $region) {


        #remove leading "."
        if (
        (
	 ($region=="de") or
	 ($region=="fr") or
	 ($region=="es") or
	 ($region=="nl") or
	 ($region=="it")
	 )

        and
        (
        (strpos($float,",") > 0) and
        (strpos($float,".") > 0)
        )
        )
         {
         	$float = str_replace(".","",$float);
	 }


        $float = (double) lhg_remove_amazon_currency_symbol ($float, $region);
        #echo "O1: $float";

	$text ="<$region> not found";

	 if (
	 ($region=="de") or
	 ($region=="fr") or
	 ($region=="es") or
	 ($region=="nl") or
	 ($region=="it")
	 ) {
         	$text = number_format($float,2,',','.');
	 }

 	if ( ($region=="co.jp") ) {
        	$text = number_format($float,0,'.',',');
	}

 	if (
	 ($region=="com") or
	 ($region=="") or
	 ($region == "co.uk") or
	 ($region == "ca") or
	 ($region == "in") or
	 ($region=="cn")
	 ) {
         	$text = number_format($float,2,'.',',');
	 }

        //echo "O2: $text";

	return $text;

}

function lhg_remove_amazon_currency_symbol( $price, $region ) {

        //echo "O: $price";
	$price  = str_replace( "INR" , "" , $price );
	$price  = str_replace( "$" , "" , $price );
	$price  = str_replace( "CDN" , "" , $price );
	$price  = str_replace( "EUR" , "" , $price );
	$price  = str_replace( "? " , "" , $price );
        $price  = str_replace("�","",$price); //remove broken pound sign
        //echo "N: $price";

	return $price;                

}

function lhg_get_currency_symbol( $region ) {
        //returns html code

        //default
        $currency="<$region> currency unkown";

                if ($region == "com"){
                	$currency="$";
                }
                if ($region == "co.uk"){
                	$currency="&pound;";
                }
                if ($region == "ca"){
                	$currency="CDN$";
                }
                if ($region == "de"){
                	$currency="EUR";
                }
                if ($region == "fr"){
                	$currency="EUR";
                }
                if ($region == "es"){
                	$currency="EUR";
                }
                if ($region == "it"){
                	$currency="EUR";
                }
                if ($region == "nl"){
                	$currency="EUR";
                }
                if ($region == "in"){
                	$currency="&#x20b9;";
                }
                if ($region == "com.br"){
                	$currency="R$";
                }
                if ($region == "co.jp"){
                	$currency="&yen;";
                }
                if ($region == "cn"){
                	$currency="&yen;";
                }


	return $currency;

}

function lhg_amazon_price_to_float_chart ( $price, $region ) {
        //echo "POS: ".strpos(" ",$price);


	//extract currency symbol if necessary
        $cut = 4; // default for EUR
        if ($region == "com") $cut = 1;
        if ($region == "ca") $cut = 4;
        if ($region == "co.uk") $cut = 1;
        if ($region == "co.jp") $cut = 2;
        if ($region == "cn") $cut = 2;
	//if ($region == "de") $cut = 4;

        $price = substr(chop($price),$cut);#$=1, EUR=4

        if ( ($region == "de") or ($region == "es") or ($region == "fr") or ($region == "it") or ($region == "nl") ) $price = str_replace(".","",$price);
        if ( ($region == "de") or ($region == "es") or ($region == "fr") or ($region == "it") or ($region == "nl") ) $price = str_replace(",",".",$price);

        #$price = lhg_amazon_price_to_float( $price, $region );

        return $price;

}

function lhg_amazon_price_to_float( $price, $my_region ) {

        $price = trim($price);


        # check if this is a affilinet price
        # EUR formatting is different than from Amazon, i.e. "XX.XX EUR" instead of "EUR XX,XX"
        if (preg_match("/[0-9].[0-9][0-9] EUR/",$price, $match) == 1){
        	#error_log("Found".$match[0]);
                # make amazon like string
		$price = str_replace(",","",$price);
		$price = str_replace(".",",",$price);
		$price = str_replace("EUR","",$price);

        }



        global $region;
        if ($my_region == "") $my_region = $region;

        #print "OP(reg: $region): $price<br>";

        //$currency_region = lhg_get_currency_region ( $price );
        $price = lhg_remove_amazon_currency_symbol( $price, $my_region );

	# thousand separator needs to be removed
        if ( (strpos($price,".") > ( strpos($price,",") ) ) ) $price = str_replace(",","",$price); 


        //broken pound symbol sometimes in co.uk prices
        if (!is_numeric( substr( $price , 0 , 1) ) )
       		 $price = substr( $price, 1 );

        // EUR values e.g. 1.345,03 or 32,43
        if (
        (
	 ($my_region=="de") or
	 ($my_region=="fr") or
	 ($my_region=="es") or
	 ($my_region=="nl") or
	 ($my_region=="it")
	 )

        //and
        //(
        //(strpos($price,".") > 0) and
        //(strpos($price,",") > 0)
        //)
        )
         {

        # looks like amazon (EUR) price

	//
        //switch to US format
        //if ($region == "co.jp") $price = str_replace(",","",$price);
        //if ($region == "cn")    $price = str_replace(",","",$price);
        if ( ($my_region == "de") or ($my_region == "es") or ($my_region == "fr") or ($my_region == "it") or ($my_region == "nl") ) $price = str_replace(".","",$price);
        if ( ($my_region == "de") or ($my_region == "es") or ($my_region == "fr") or ($my_region == "it") or ($my_region == "nl") ) $price = str_replace(",",".",$price);

        //remove thousand separator
        #$price = floatval ( str_replace(",","",$price) );

	 }

        #print "P2F (reg: $region): $price<br>";
        return $price;
}

# Get region corresponding to currency. Works for amazon prices
function lhg_get_currency_region ( $price ) {

        if ( strpos("EUR", $price) > 0 ) $currency_region = "EUR";
        if ( strpos("CDN$", $price) > 0 ) $currency_region = "CANADA";
        if ( strpos("£", $price) > 0 ) $currency_region = "UK";
        if ( strpos("ï¿¥", $price) > 0 ) $currency_region = "JAPAN";


        return $currency_region;
}


?>