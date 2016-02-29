<?php
/*
Description: This widget originates from the wp-one-post plugin version 2.1 by Rafael Tavares
Modified for Linux-Hardware-Guide
*/ 




load_plugin_textdomain('wponepostwidget', false, dirname(plugin_basename(__FILE__)).'/language/');

function wp_one_post_admin_scripts() {
  wp_register_style( 'jquery-ui-css', "http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css");
  wp_enqueue_style( 'jquery-ui-css' );

  wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
  wp_enqueue_script( 'jquery' );

  wp_register_script( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');
  wp_enqueue_script( 'jquery-ui' );

  wp_register_style( 'wp-one-post-admin', plugins_url('css/lhg_widget_article_summary_admin.css', __FILE__));
  wp_enqueue_style( 'wp-one-post-admin' );
}    
add_action('admin_enqueue_scripts', 'wp_one_post_admin_scripts');

function wp_one_post_scripts() {
  wp_register_style( 'wp-one-post-widget', plugins_url('css/lhg_widget_article_summary.css', __FILE__));
  wp_enqueue_style( 'wp-one-post-widget' );
}
add_action('wp_enqueue_scripts', 'wp_one_post_scripts');


add_action('init', 'wp_one_post_widget_register');
function wp_one_post_widget_register() {
	
	$prefix = 'wp-one-post-widget';
	$name = __('WP One Post Widget');
	$widget_ops = array('classname' => 'wp_one_post_widget', 'description' => __('Add content specific to your site.'));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);
	
	$options = get_option('wp_one_post_widget');
  
	if(isset($options[0])) unset($options[0]);
	
	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'wp_one_post_widget', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'wp_one_post_widget_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'wp_one_post_widget', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'wp_one_post_widget_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function wp_one_post_widget($args, $vars = array()) {
  extract($args);
  $widget_number = (int)str_replace('wp-one-post-widget-', '', @$widget_id);
  $options = get_option('wp_one_post_widget');
  if(!empty($options[$widget_number])){
    $vars = $options[$widget_number];
  }

// ------ data used by several parts ----------

$title=translate_title(get_the_title());
$s=explode("(",$title);
$short_title=trim($s[0]);

global $post;
global $wpdb;
global $lhg_price_db;
global $lang;
global $txt_summary;
global $txt_supplier;
global $txt_cheapest_supplier;
global $txt_rating;
global $txt_select;
global $txt_ratings;
global $txt_sim_tags;
global $txt_combine_tags;
global $txt_wpop_register;
global $txt_register_long;
global $txt_send;
global $txt_manage_subscr;
global $txt_not_avail;
global $txt_rate_yourself;
global $txt_wpop_register;

global $txt_button_string; //defined by amazon-product-in-a-post.php

//echo "ID: $post->ID";
if (is_single($post->ID)) {

  $querystr = "
    SELECT $wpdb->posts.* 
    FROM $wpdb->posts
    WHERE $wpdb->posts.post_title = '".$vars['title']."'
    AND $wpdb->posts.post_status = 'publish' 
    AND $wpdb->posts.post_type = 'post'
  ";

  $pageposts = $wpdb->get_results($querystr, OBJECT);
  
  foreach ($pageposts as $post):
    setup_postdata($post);
    $title = $post->post_title;
    $excerpt = $post->post_excerpt; 
    if(!$excerpt): $excerpt = substr($post->post_content,0,100); endif;
    if(!$vars['thumbnail_position']): $vars['thumbnail_position'] = 'left'; endif;
		$thumb = get_the_post_thumbnail($post->ID, 'thumbnail', array('class' => $vars['thumbnail_position']));
		$link = get_permalink($post->ID);
  endforeach;
  echo "
$before_widget";
  echo '
    <div class="topright" style="border: 1px solid #2b8fc3; padding-left: 3px; padding-right:3px; padding-top: 1px; margin-bottom: 10px; background-color: #fff; padding-bottom: 17px;">';
  echo '
        <div class="wp-one-summary">'.$txt_summary."</div>";

//	if(empty($vars['custom_title'])): echo $before_title . "A".$vars['title'] . $after_title; else: echo "<h2>".$vars['custom_title'] . "</h2>"; endif; 

  if($vars['use_thumbnail'] == 'yes'):
    $content_widget = $thumb.'<p>'.$excerpt.' <a href="'.$link.'">'.$vars['readmore'].'</a></p>';
  else:
    $content_widget = '<p>'.$excerpt.' <a href="'.$link.'">'.$vars['readmore'].'</a></p>';
  endif;

  //$thumb = get_the_post_thumbnail($post->ID);
  //echo "$thumb";


        //Rating Overview
        $post_id = $post->ID;
	// Check IP From IP Logging Database
	#$get_rates = $wpdb->get_results("SELECT rating_rating FROM $wpdb->ratings WHERE rating_postid = $post_id");
        //echo "Nr: ".sizeof($get_rates);

        $rating1=0;
        $rating2=0;
        $rating3=0;
        $rating4=0;
        $rating5=0;
        $rating_total=0;

        #foreach($get_rates as $get_rate){
        #        $rating_total++;
        #
	#        if ($get_rate->rating_rating == 1 ) $rating1++;
	#        if ($get_rate->rating_rating == 2 ) $rating2++;
	#        if ($get_rate->rating_rating == 3 ) $rating3++;
	#        if ($get_rate->rating_rating == 4 ) $rating4++;
	#        if ($get_rate->rating_rating == 5 ) $rating5++;
        #
        #}

	if ($lang != "de") $ratings = $lhg_price_db->get_results("SELECT * FROM  `lhgtransverse_posts` WHERE postid_com = $post_id");
	if ($lang == "de") $ratings = $lhg_price_db->get_results("SELECT * FROM  `lhgtransverse_posts` WHERE postid_de = $post_id");


        #var_dump($ratings);

        $rating1 = $ratings[0]->post_ratings_1_com + $ratings[0]->post_ratings_1_de;
        $rating2 = $ratings[0]->post_ratings_2_com + $ratings[0]->post_ratings_2_de;
        $rating3 = $ratings[0]->post_ratings_3_com + $ratings[0]->post_ratings_3_de;
        $rating4 = $ratings[0]->post_ratings_4_com + $ratings[0]->post_ratings_4_de;
        $rating5 = $ratings[0]->post_ratings_5_com + $ratings[0]->post_ratings_5_de;
        $rating_total = $ratings[0]->post_ratings_users_com + $ratings[0]->post_ratings_users_de;

        #$rating1 + $rating2 + $rating3 + $rating4 + $rating5;

        //echo "<table cellspacing=0 cellpadding=0 border=1>
	//        <tr>
        //        <td width=90%>";


        //echo "</td></tr></table> ";

        global $vspacer;
        $vspacer = '<div class="spacer" style="border-top: 1px solid #2b8fc3; height: 0px; margin-top: 9px; margin-bottom: 9px;"></div>';


        // ---- Overview - cheapest supplier

        // get cheapest supplier
        $cheapest_supplier_id = lhg_db_get_cheapest_supplier_id($post->ID);

        #echo "CID: $cheapest_supplier_id <br>";


        if ($cheapest_supplier_id != "")
                lhg_show_supplier_square($cheapest_supplier_id, $post->ID);
        if ($cheapest_supplier_id == "")
                lhg_show_supplier_square_default( $post->ID );

        //get price & url from DB
        $region=get_region();
#        $meta="price-amazon.".$region;
#        $aprice = get_post_meta($post->ID,$meta,true);
#        $meta="url-amazon.".$region;
#        $aurl=get_post_meta($post->ID,$meta,true); //amaz_url($post->ID);

        //still undefined?
#	if ($aurl == ""){
#	  $aid=get_id();
#          $aurl = 'http://www.amazon.'.$region.'/?_encoding=UTF8&camp=15121&creative=390961&linkCode=ur2&tag='.$aid;
#        }

        /*
        if ( ($region == "de") or ($region == "fr") or ($region == "it") or ($region == "es")) {
                $aprice=str_replace(".","",$aprice);
        }else{
	        $aprice=str_replace(",","",$aprice);
	        //$aprice=str_replace(",",".",$aprice);
        }
        */

        //echo "AP0: $aprice";
#        $aprice = lhg_amazon_price_to_float ( $aprice , $region );
        //echo "AP1: $aprice";

        //
        // affilinet prices
        //
        if ( $lang == "de debug" ) {

                //echo "A";

		$meta    = "affilinet_PID";
	        $aff_PID = get_post_meta( $post->ID , $meta , true ); //amaz_url($post->ID);

                if ( $aff_PID == "" ) {
                        //check for wrong entered PID
			$meta    = "PID_affilinet";
	        	$aff_PID = get_post_meta( $post->ID , $meta , true ); //amaz_url($post->ID);
                	//echo "affPID: $aff_PID";
		}

                if ( $aff_PID != "" ) {
                        // check for multiple PIDs
                        $PIDlist = str_getcsv($aff_PID,";");
                        $num=count($PIDlist);

                        //echo "Num: $num <br>PIDlist0: $PIDlist[0]<br>";
                        //echo "PIDlist1: $PIDlist[1]<br>";

                        if ($num == 1) {
                        	list ( $aff_date , $aff_shop_id , $aff_price , $aff_shipping , $aff_url )  = get_price_aff( $aff_PID );

                                $cheapestURL =  str_replace("+++++",";",$aff_url);
                                //echo "Cheap: $cheapestURL<br>";

		                $tprice = $aff_price + $aff_shipping;
        		        //echo "TP: $tprice <br>";
				if ($aff_shop_id == 1739) {
                			$pcount++;
					$pricetable[$pcount]      = $tprice.'###  EUR '.str_replace(".",",",number_format( $tprice ,2)).' &gt;  <a href="'.$aff_url.'" rel="nofollow">Redcoon.de</a>';
					$pricetable_data[$pcount] = $tprice.'###'.str_replace(".",",",number_format( $tprice ,2)).'###'.$aff_url.'###Redcoon.de'.'###'.str_replace(".",",",number_format( $aff_shipping ,2));
		        	}

				if ($aff_shop_id == 1752) {
                			$pcount++;
					$pricetable[$pcount]      = $tprice.'###  EUR '.str_replace(".",",",number_format( $tprice ,2)).' &gt;  <a href="'.$aff_url.'" rel="nofollow">Reichelt.de</a>';
					$pricetable_data[$pcount] = $tprice.'###'.str_replace(".",",",number_format( $tprice ,2)).'###'.$aff_url.'###Reichelt.de'.'###'.str_replace(".",",",number_format( $aff_shipping ,2));
		        	}

	                }


                        if ($num > 1) {
                                //cycle through IDs
                                //$ProductIDList = str_getcsv($ProductID,";");
                                //echo "multiple ids<br>";
                        	list ( $aff_date , $aff_shop_id , $aff_price , $aff_shipping , $aff_url )  = get_price_aff( $aff_PID , $num );
                                //echo "ShopID: $aff_shop_id";

                                $ShopIDList    = str_getcsv($aff_shop_id,";");
                                $PriceList    = str_getcsv($aff_price,";");
                                $ShippingList    = str_getcsv($aff_shipping,";");
                                $URLList    = str_getcsv($aff_url,";");

                                $tpriceListCheapest=9999999999;

                                for ($i = 0; $i < $num; $i++){

			                $tpriceList[$i] = $PriceList[$i] + $ShippingList[$i];

                                        //url of cheapest partner
                                        if ( $tpriceList[ $i ] < $tpriceListCheapest ) {
                                                             $tpriceListCheapest = $tpriceList[ $i ];
                                                             $cheapestURL =  str_replace("+++++",";",$URLList[$i]);
                                                             //echo "Cheap: $cheapestURL<br>";
		        		}

					if ( $ShopIDList[$i] == 1739 ) {
        	        			$pcount++;
						$pricetable[$pcount]      = $tpriceList[$i].'###  EUR '.str_replace(".",",",number_format( $tpriceList[$i] ,2)).' &gt;  <a href="'.$URLList[$i].'" rel="nofollow">Redcoon.de</a>';
						$pricetable_data[$pcount] = $tpriceList[$i].'###'.str_replace(".",",",number_format( $tpriceList[$i] ,2)).'###'.$URLList[$i].'###Redcoon.de'.'###'.str_replace(".",",",number_format( $ShippingList[$i] ,2));
		        		}

					if ( $ShopIDList[$i] == 1752 ) {
        	        			$pcount++;
						$pricetable[$pcount]      = $tpriceList[$i].'###  EUR '.str_replace(".",",",number_format( $tpriceList[$i] ,2)).' &gt;  <a href="'.$URLList[$i].'" rel="nofollow">Reichelt.de</a>';
						$pricetable_data[$pcount] = $tpriceList[$i].'###'.str_replace(".",",",number_format( $tpriceList[$i] ,2)).'###'.$URLList[$i].'###Reichelt.de'.'###'.str_replace(".",",",number_format( $ShippingList[$i] ,2));
		        		}

		                }
	                }


                }

                //echo "ID: $aff_shop_id <br>Price: $aff_price + $aff_shipping";

        }



        //$nbprice=str_replace(",",".",NB_price($post->ID));
        //$nburl=NB_url($post->ID);
        //$region=get_region();
        //$aid=get_id();

        //print $aprice;
        //print "AURL: $aurl<br>";
        //if ($aurl == "") $aurl="http://www.amazon.$region/?_encoding=UTF8&camp=15121&creative=390961&linkCode=ur2&tag=$aid";

        //default: Euro
        //print "2: ".number_format($aprice,2);

        //$pricestring = number_format( str_replace("EUR","",str_replace(",",".",$aprice)),2,',','.');

        //pure price without currency
        //$pureprice   = str_replace("EUR","",$aprice);
        //$pureprice   = str_replace("CND","",$aprice);
        //$pureprice   = str_replace("$","",$aprice);

#        $pricestring = $aprice;
#
#        if ( ($region == "de") or
#             ($region == "es") or
#             ($region == "fr") or
#             ($region == "it") ){
#		//$pricetable[0] =  str_replace(",",".",$pricestring).'###  EUR <span itemprop="price">'.$pricestring.'</span> &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';
#		//$pricetable[0] =  str_replace(",",".",$pricestring).'###  EUR <span itemprop="price">'.$pricestring.'</span> &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';
#		$pricetable_data[0] =  $pricestring.'###'.$pricestring.'###'.$aurl.'###Amazon.'.$region;
#        }


        //float price to string
#        $aprice_str = lhg_float_to_currency_string( $aprice , $region );
#        $cs = lhg_get_currency_symbol( $region );

        //echo "REG: $region";
        /*
        if ($region == "com") $pricetable[0] =    '  $ <span itemprop="price">'.str_replace(".",".",number_format(str_replace("$","",$aprice),2)).'</span> &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';
        if ($region == "in")  $pricetable[0] = '  '.str_replace("INR",'&#8377; <span itemprop="price">',$aprice).'</span> &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';
        if ($region == "ca")  $pricetable[0] = '  CDN$ <span itemprop="price">'.str_replace(".",".",number_format(str_replace("CDN\$","",$aprice),2)).'</span> &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';
        if ($region == "co.uk")  $pricetable[0] = '  '.$cs.' <span itemprop="price">'.$aprice_str.'</span> &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';
        if ($region == "co.jp")  $pricetable[0] = '  &yen; <span itemprop="price">'.$aprice_str.'</span> &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';
        */

#        $pricetable[0] = '  '.$cs.' <span itemprop="price">'.$aprice_str.'</span> &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';

        //echo "PT: $pricetable[0]";

        //print "APrice: $aprice<br>";
#        if ($aprice=='out of stock')
#                $pricetable[0] = '  '.$txt_not_avail.' &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';

        //print "AP: >$aprice< <br>";
#        if ( (strpos($aprice,'nicht vorr') !== false) or (strpos($aprice,'nicht liefer') !== false) or ($aprice=="") )
#                $pricetable[0] = '  '.$txt_not_avail.' &gt; <a href="'.$aurl.'" rel="nofollow">Amazon.'.$region.'</a>';





#        if ($lang == "de"){
#          if ($nbprice != ""){
#                $pcount++;
#        	$pricetable[$pcount] = '  EUR <span itemprop="price">'.str_replace(".",",",number_format(str_replace("EUR","",$nbprice),2)).'</span> &gt;  <a href="'.$nburl.'" rel="nofollow">Notebooksbilliger.de</a>';
#          }
#          //sort($pricetable);
#          array_multisort( $pricetable , SORT_NUMERIC, $pricetable_data );
#	}

        //echo "PT0 ".$pricetable[0];
	//echo "POS: ".strpos($pricetable[0],"###");

        //get rid of sorting string, works only for "de"
#        if ( strpos($pricetable[0],"###") >0 )
 #       foreach ($pricetable as &$value) {
 #           list( $null, $value) = explode("###",$value);
 #       }

        //echo "PT0 ".$pricetable[0];
        //echo "<br>PT1 ".$pricetable[1];
        //echo "PR: $aprice";


        // echo "\n".'<div itemscope itemtype="http://data-vocabulary.org/Product">';
        // echo "\n".'<span itemprop="category" content="Hardware" ></span>';
        // echo "\n".'<span itemprop="name" content="'.translate_title(get_the_title()).'" ></span>';

        //echo "PC: $pcount";
#        if ($pcount > 0)
#	        echo "\n".'<div style="text-align: center;"><b>'.$txt_cheapest_supplier.':</b></div>';
        //echo "aprice: $aprice<br>";
        //echo "PC: ",$pcount;

#        if ($pcount == 0)
#                if ($aprice!='out of stock')
#                        	echo "\n".'<div style="text-align: center;"><b>'.$txt_supplier.'</b></div>';

#        echo "\n".'<div class="rating" style="border: 0px solid #222; width: 70%; text-align: center; margin: 0 auto;">';

#        echo $cheap_txt;

        /* single offer. to be changed for group of offers! */
        //echo "\n".'<span itemprop="offerDetails" itemscope itemtype="http://data-vocabulary.org/Offer">';

#        $price_meta = "USD";
#        if ($region == "de") $price_meta = "EUR";
#        if ($region == "it") $price_meta = "EUR";
#        if ($region == "fr") $price_meta = "EUR";
#        if ($region == "es") $price_meta = "EUR";
#        if ($region == "com") $price_meta = "USD";
#        if ($region == "co.uk") $price_meta = "GBP";
#        if ($region == "co.jp") $price_meta = "JPY";
#        if ($region == "cn") $price_meta = "CNY";
#        if ($region == "ca") $price_meta = "CAD";
#        if ($region == "in") $price_meta = "INR";
#
#        echo "\n".'<meta itemprop="currency" content="'.$price_meta.'" />';
	//echo '<meta itemprop="price" content="'.str_replace(".",",",number_format(str_replace("EUR","",$nbprice),2)).'" />';


        //echo "Amazon.$region";
        //echo "PT: $pricetable[0]";
#        if ( (strpos($pricetable[0],"Amazon.$region")) or ($pricetable[0] == "") ){
#                //if ($aprice!='out of stock')
#                echo "\n".'<div class="amazonbutton"><a href="'.$aurl.'" rel="nofollow"><img src="/wp-uploads/2012/10/Amazon_Logo1.png" border=0 width=125 height=125 alt="'.$txt_button_string.': '.$short_title.'" title="'.$txt_button_string.': '.$short_title.'"></a></div>';
#        }elseif (strpos($pricetable[0],"Notebooksbilliger.de")){
#        	echo "\n".'<div class="amazonbutton"><a href="'.$nburl.'" rel="nofollow"><img src="wp-uploads/2012/10/Logo_125-x-125_5_H.gif" border=0></a></div>';
#        }elseif (strpos($pricetable[0],"Redcoon.de")){
#        	echo "\n".'<div class="amazonbutton" style="border: 1px solid #2B8FC3; width: 140px; margin: 0 auto;"><a href="'.$cheapestURL.'" rel="nofollow"><img src="/wp-uploads/2014/03/redcoon-logo-125x125.jpg" border=0 width=140 height=140></a></div>';
#        }elseif (strpos($pricetable[0],"Reichelt.de")){
#        	echo "\n".'<div class="amazonbutton" style="border: 1px solid #2B8FC3; width: 140px; margin: 0 auto;"><a href="'.$cheapestURL.'" rel="nofollow"><img src="/wp-uploads/2014/03/reichelt_logo_125x125.gif" border=0 width=140 height=140></a></div>';
#        }
#
#        $pricetable[0] = str_replace("+++++",";",$pricetable[0]);

#        echo str_replace(" &gt; ", "<br>",$pricetable[0]);
#        echo "\n"."</div>";


#        if ($pcount == 0){
#	        //echo "<b>Preis:</b>";
#	}else{
#	        //echo "<b>Amazon-Preis:</b><br>";
#                //echo ' '.$aprice.' &gt; <a href="'.$aurl.'">Amazon.de</a>';
#                echo $vspacer;
#	        echo "<b>&Uuml;bersicht der Anbieter:</b>";
#
#	}


        //echo "<br>PT1 ".$pricetable[1];


#	if ($pcount == 0){
#		//echo $line;
#	}else{
#                echo "<br>
#                <table>";
#                //<tr><td><b>Preis</b></td><td><b>Anbieter</b></td><td></td></tr>
#                //";
#        	$i=0;
#        	foreach($pricetable as $line){
#                        list ($price1, $price2, $url , $shop, $shipping) = explode("###",$pricetable_data[$i]);
#
#                        //echo "$price2 .. $url .. $shop";
#
#		        if (strpos($pricetable[$i],"Redcoon.de")){
#			      echo '<tr><td>EUR&nbsp;'.$price2.'<br><div class="portoline">inkl. Porto: '.$shipping.' EUR</div></td><td valign="middle"><a href="'.$url.'" rel="nofollow"><img src="wp-uploads/2014/03/redcoon-logo-110x033.jpg" style="border: 1px solid #2B8FC3;"></a></td><td><a href="'.$url.'" rel="nofollow">Redcoon.de</a></td></tr>';
#
#	    		}elseif (strpos($pricetable[$i],"Amazon.de")){
#			      echo '<tr><td>EUR&nbsp;'.$price2.'<br><div class="portoline">exkl. Porto</div></td><td valign="middle"><a href="'.$url.'" rel="nofollow"><img src="wp-uploads/2014/03/amazon_de_logo_110_33.jpg" style="border: 1px solid #2B8FC3;"></a></td><td><a href="'.$url.'" rel="nofollow">Amazon.de</a></td></tr>';
#			      //echo '<img src="wp-uploads/2014/03/amazon_de_logo_110_33.jpg">'.$pricetable[$i]."<br>";
#  	    		}elseif (strpos($pricetable[$i],"Reichelt.de")){
#                              $url = str_replace("+++++",";",$url);
#                              echo '<tr><td>EUR&nbsp;'.$price2.'<br><div class="portoline">inkl. Porto: '.$shipping.' EUR</div></td><td valign="middle"><a href="'.$cheapestURL.'" rel="nofollow"><img src="wp-uploads/2014/03/reichelt_logo_110x33.jpeg" style="border: 1px solid #2B8FC3;"></a></td><td><a href="'.$url.'" rel="nofollow">Reichelt.de</a></td></tr>';
#			      //echo '<img src="wp-uploads/2014/03/amazon_de_logo_110_33.jpg">'.$pricetable[$i]."<br>";
#	    		}
#                        else{
#			      echo $pricetable[$i]."<br>";
#                        }
#
#	              //if ($i == 0) echo '<div class="priceline" style="border: 0px solid #000; "><div class="pricetop" style="border: 1px solid #2b8fc3; background-color: #eee; margin: 3px 3px;">'.$line.'</div></div>';
#	              //if ($i > 0)  echo '<div class="priceline" style="border: 0px solid #000; "><div class="price" style="border: 0px solid #333; background-color: #fff; margin: 3px 3px;">'.$line.'</div></div>';
#  	              $i++;
#    		}
#                echo"</table>";
#	}


        // echo get_affiliate_prices($post->ID);


        //
        // ---- New Supplier Overview
        //

        #if ($lang == "de")
        lhg_supplier_comparison($post->ID);


        //
        // ---- Rating Overview ==============================================================
        //


        global $txt_opw_num_ratings; //    = "Number of ratings";
	global $txt_opw_average_rating;// = "Average ratings";
	global $txt_opw_rating_overview;
        global $txt_out_of;
        global $txt_opw_hardware;


        global $txt_average;

        global $no_supplier_square;

        #$rating_avg = get_post_meta($post->ID,'ratings_average',true);
	$num_rates = $ratings[0]->post_ratings_users_com + $ratings[0]->post_ratings_users_de;
        if ($num_rates == 0 ) $rating_avg = 0;
        if ($num_rates != 0 ) $rating_avg = ( $ratings[0]->post_ratings_score_com + $ratings[0]->post_ratings_score_de ) / ( $num_rates ) ;

        if ( $no_supplier_square != 1)
        echo $vspacer;
        #echo '<div itemscope itemtype="http://schema.org/Product">';
        #echo '<div itemscope itemtype="http://data-vocabulary.org/Review-aggregate">';
        #echo '<div property="itemReviewed" typeof="Product">';
        #echo '  <div itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">';

        //echo '<div class="rating" style="border: 0px solid #222; width: 90%; margin: 0 auto;">';
	echo '

        <table class="ratingtable" border="0">
           <tr>
             <td><b>'.$txt_opw_hardware.':</b></td>
             <td><span itemprop="name">'.$short_title.'</span></td>
           </tr>


           <tr>
             <td>
             ';

	//if ($rating_total>1) echo "<b>$txt_wpone_num_ratings:</b></td><td> <b>".'<span itemprop="votes">'."$rating_total</span></b><br>";
	//if ($rating_total<2)
        echo "       <b>$txt_opw_num_ratings:</b>
             </td>



             <td>
                $rating_total <br />
             </td>
           </tr>


           <tr>
              <td><b>$txt_opw_average_rating: </b></td>

              <td>";

	           echo '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';

                   	echo the_ratings_results($post->ID,0,0,0,10).'
                            (<span itemprop="ratingValue">'.round($rating_avg,1).'</span> '.$txt_out_of.'
                             <span itemprop="bestRating">5</span>)

                            <span itemprop="ratingCount" content="'.$rating_total.'" />
                            <span itemprop="worstRating" content="0" />
                        </span>

              </td>
           </tr>


           ';

        echo '
           <tr>
              <td class="td-ratingoverview">'."<b>$txt_opw_rating_overview:</b></td>

              <td>";


        if ($rating_total == 0) {
        	$ra5  = 0;
        	$ra4  = 0;
        	$ra3  = 0;
        	$ra2  = 0;
        	$ra1  = 0;
        }else{
        	$ra5  = (100*$rating5/$rating_total);
        	$ra4  = (100*$rating4/$rating_total);
        	$ra3  = (100*$rating3/$rating_total);
        	$ra2  = (100*$rating2/$rating_total);
        	$ra1  = (100*$rating1/$rating_total);
        }

        echo '

        <div class="rateline" style="border: 0px solid #000;">
           <div style="float: left;">5:&nbsp; </div>
           <div class="outerbox" style="background-color: #eee; width: 80px; float: left; margin: 4px 0px;">
             <div class="box" style="border: 0px solid #088; background-color: #2b8fc3; height: 8px; width: '.$ra5.'%;" ></div>
           </div> &nbsp;('.$rating5.')
        </div><br clear="all" />';

        echo '
        <div class="rateline" style="border: 0px solid #000; margin-top: -17px; ">
           <div style="float: left;">4:&nbsp; </div>
           <div class="outerbox" style="background-color: #eee; width: 80px; float: left; margin: 4px 0px;">
             <div class="box" style="border: 0px solid #088; background-color: #2b8fc3; height: 8px; width: '.$ra4.'%;" ></div>
           </div> &nbsp;('.$rating4.')
        </div><br clear="all" />';

        echo '
        <div class="rateline" style="border: 0px solid #000; margin-top: -17px; ">
           <div style="float: left;">3:&nbsp; </div>
           <div class="outerbox" style="background-color: #eee; width: 80px; float: left; margin: 4px 0px;">
             <div class="box" style="border: 0px solid #088; background-color: #2b8fc3; height: 8px; width: '.$ra3.'%;" ></div>
           </div> &nbsp;('.$rating3.')
        </div><br clear="all" />';

        echo '
        <div class="rateline" style="border: 0px solid #000; margin-top: -17px; ">
           <div style="float: left;">2:&nbsp; </div>
           <div class="outerbox" style="background-color: #eee; width: 80px; float: left; margin: 4px 0px;">
              <div class="box" style="border: 0px solid #088; background-color: #2b8fc3; height: 8px; width: '.$ra2.'%;" ></div>
           </div> &nbsp;('.$rating2.')
        </div><br clear="all" />';

        echo '
        <div class="rateline" style="border: 0px solid #000; margin-top: -17px; ">
           <div style="float: left;">1:&nbsp; </div>
           <div class="outerbox" style="background-color: #eee; width: 80px; float: left; margin: 4px 0px;">
              <div class="box" style="border: 0px solid #2b8fc3; background-color: #2b8fc3; height: 8px; width: '.$ra1.'%;" ></div>
           </div> &nbsp;('.$rating1.')
        </div>';
#        echo '
#     </div>';
        echo '      <a href="#comments">'.$txt_rate_yourself.'</a>';

        //echo "</td></tr><tr><td></td><td>";

        echo "
                </td>
              </tr>

          </table>

          ";


        #echo '</div>'; // itemreviewed
        # echo "  </div>"; //Rating
        #echo '</div>'; //Review-aggregate

        //echo $vspacer;


        //
        // -------- Subscriber =============================================================
        //


        //if ($lang == "de"){
	echo $vspacer;
        print "<b>".'<i class="icon-user icon-add-hw-user"></i><i class="icon-plus icon-add-hw-plus"></i>'."$txt_wpop_register</b><br />";
        print "$txt_register_long";

	if ( is_user_logged_in() ) {
        	$current_user = wp_get_current_user();
             	print '<form action="/hardware-profile?srp='.$post->ID.'&#038;sra=s" method="post" onsubmit="if(this.sre.value==\'\' || this.sre.indexOf(\'@\')==0) return false">
  	                 <fieldset style="border:0">
  	                 <input type="hidden" class="subscribe-form-field" name="sre" value="'.$current_user->user_email.'" size="18">
  	                 <button type="submit" value="'.$txt_send.'" >'.$txt_send.'&nbsp;<i class="icon-arrow-right icon-button"></i></button>
  	                 </fieldset>
  	                 </form>';
  	}else{
  	                 print '<form action="/hardware-profile?srp='.$post->ID.'&#038;sra=s" method="post" onsubmit="if(this.sre.value==\'\' || this.sre.indexOf(\'@\')==0) return false">
  	                 <fieldset style="border:0">
  	                 <input type="text" class="subscribe-form-field" name="sre" value="email" size="18" onfocus="if(this.value==this.defaultValue)this.value=\'\'" onblur="if(this.value==\'\')this.value=this.defaultValue"/>
  	                 <button type="submit" value="'.$txt_send.'" >'.$txt_send.'&nbsp;<i class="icon-arrow-right icon-button"></i></button>
  	                 </fieldset>
  	                 </form>';
  	}

        $usernum = 0 ;

        //get number of registered users
        global $txt_opw_registered;
        global $wpdb;
        global $txt_not_avail;

        $usernum = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%' AND post_id = ".$post->ID);
        if ($usernum > 0)
        print "(".$txt_opw_registered.": ".$usernum.")";

        //print do_shortcode('<a href="/manage-subscriptions">'.$txt_manage_subscr."</a>");
	//}


        //
        // ----------- Language Selector ============================================
        //

	#$selected    ='<div class="pricetop" style="border: 1px solid #2b8fc3; background-color: #eee; margin: 3px 3px 3px 3px; padding-top: 3px;">';
        #$selectedEnd ='</div>';

        # Debug:
        #lhg_store_comment_numbers_by_post_id( $post_id );



        echo $vspacer;
        echo "<b>$txt_select:</b><br />";
        //echo "PL:".get_permalink();
        //echo "<br>QPL:".qtrans_convertURL(get_permalink());
        list($null1,$null2,$null3,$posturl)=explode("/",get_permalink());
        list($null1,$null2,$null3,$null4,$posturl2)=explode("/",get_permalink());

        //echo "pu:".$posturl ."<br>";
        //echo "pu2".$posturl2."<br>";

        if ($posturl2 != "") $posturl = $posturl2; //we are in a qtranslate subfolder!
        $posturlde  = $posturl;
        $posturlcom = $posturl;

        if ( (!is_search()) and
          (!($postID == "1821")) and
          (!is_archive())
          )
        {



		$comURL = get_post_meta($post->ID,'COM_URL',true);
		$deURL  = get_post_meta($post->ID,'DE_URL',true);
        	if ($comURL != "") $posturlcom = $comURL;
        	if ($deURL  != "") $posturlde = $deURL;

        	if (substr($comURL,0,1)=="/") $comURL = substr($comURL,1);
        	if (substr($deURL,0,1)=="/") $deURL = substr($deURL,1);

                //echo "LANG: $lang, PID: $postID, ";
                //translation still missing -> redirect to main page
                if ( ($comURL == "") and ($post->ID > 2599) and ($lang == "de")) $posturlcom = "";
                if ( ($deURL == "")  and ($post->ID > 2599) and ($lang == "en")) $posturlde = "";

                # on de server get com link from priceDB
                if ($lang == "de") {
	                $com = lhg_get_com_post_URL( $postID );
        	        $comURL == $com;
	        }
        }

        //remove language selection for .de
        $posturlde = str_replace("?lang=jp","",$posturlde);
        $posturlde = str_replace("?lang=it","",$posturlde);
        $posturlde = str_replace("?lang=es","",$posturlde);
        $posturlde = str_replace("?lang=uk","",$posturlde);
        $posturlde = str_replace("?lang=ca","",$posturlde);
        $posturlde = str_replace("?lang=in","",$posturlde);
        $posturlde = str_replace("?lang=fr","",$posturlde);
        $posturlde = str_replace("?lang=cn","",$posturlde);
        $posturlde = str_replace("?lang=en","",$posturlde);

        $posturlcom = str_replace("?lang=jp","",$posturlcom);
        $posturlcom = str_replace("?lang=it","",$posturlcom);
        $posturlcom = str_replace("?lang=es","",$posturlcom);
        $posturlcom = str_replace("?lang=uk","",$posturlcom);
        $posturlcom = str_replace("?lang=ca","",$posturlcom);
        $posturlcom = str_replace("?lang=in","",$posturlcom);
        $posturlcom = str_replace("?lang=fr","",$posturlcom);
        $posturlcom = str_replace("?lang=cn","",$posturlcom);
        $posturlcom = str_replace("?lang=en","",$posturlcom);



        if ($lang == "de") {
        	$URLC="http://www.linux-hardware-guide.com";
        	$URLD="";
	}

        if ($lang != "de") {
                $URLC="";
                $URLD="http://www.linux-hardware-guide.de";
	}

        //$URLC="http://192.168.3.113"; //Debug

        if (1 == 1)  {

        echo '<div class="countrytable"><table border="0">
        	<tr class="countrytable_header">
                  <td class="cth_country">Country</td>
                  <td class="cth_price">Price</td>
                  <td class="cth_currency"></td>
                  <td class="cth_comment">Comments</td>
                </tr>';

        print lhg_country_row("de"    ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("com"   ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("ca"    ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("co.uk" ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("fr"    ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("es"    ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("it"    ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("nl"    ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("in"    ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("co.jp" ,$URLC, $URLD, $posturlcom, $posturlde);
        print lhg_country_row("cn"    ,$URLC, $URLD, $posturlcom, $posturlde);
        echo "</table></div>";

        }else {

        	//if ($region == "de") print $selected;
	        //echo '&nbsp;&nbsp;<a href="'.$URLD.'/'.$posturlde.'"><img src="/wp-content/plugins/qtranslate/flags/de.png" alt="Germany" /> Germany (&euro;)</a><br />';
        	//if ($region == "de") print $selectedEnd;

        	if ($region == "de") print $selected;
	        echo '&nbsp;&nbsp;<a href="'.$URLC.'/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/us.png" alt="USA" /> USA (&euro;)</a><br />';
        	if ($region == "de") print $selectedEnd;

	        if ($region == "ca") print $selected;
        	echo '&nbsp;&nbsp;<a href="'.$URLC.'/ca/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/ca.png" alt="Canada" /> Canada (CDN $)</a><br />';
	        if ($region == "ca") print $selectedEnd;

        	if ($region == "co.uk") print $selected;
	        echo '&nbsp;&nbsp;<a href="'.$URLC.'/uk/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/uk.png" alt="UK" /> United Kingdom (&pound;)</a><br />';
        	if ($region == "co.uk") print $selectedEnd;

	        if ($region == "fr") print $selected;
        	echo '&nbsp;&nbsp;<a href="'.$URLC.'/fr/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/fr.png" alt="France" /> France (&euro;)</a><br />';
	        if ($region == "fr") print $selectedEnd;

        	if ($region == "es") print $selected;
	        echo '&nbsp;&nbsp;<a href="'.$URLC.'/es/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/es.png" alt="Espana" /> Espana (&euro;)</a><br />';
        	if ($region == "es") print $selectedEnd;

	        if ($region == "it") print $selected;
        	echo '&nbsp;&nbsp;<a href="'.$URLC.'/it/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/it.png" alt="Italia" /> Italia (&euro;)</a><br />';
	        if ($region == "it") print $selectedEnd;

        	if ($region == "nl") print $selected;
	        echo '&nbsp;&nbsp;<a href="'.$URLC.'/nl/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/nl.png" alt="Netherlands" /> Netherlands (&euro;)</a><br />';
        	if ($region == "nl") print $selectedEnd;

	        if ($region == "in") print $selected;
        	echo '&nbsp;&nbsp;<a href="'.$URLC.'/in/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/in.png" alt="India" /> India (&#8377;)</a><br />';
	        if ($region == "in") print $selectedEnd;

        	if ($region == "co.jp") print $selected;
	        echo '&nbsp;&nbsp;<a href="'.$URLC.'/ja/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/jp.png" alt="Japan" /> Japan (&yen;)</a><br />';
        	if ($region == "co.jp") print $selectedEnd;

	        if ($region == "cn") print $selected;
        	echo '&nbsp;&nbsp;<a href="'.$URLC.'/zh/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/cn.png" alt="China" /> China (RMB)</a><br />';
	        if ($region == "cn") print $selectedEnd;

        }

        //
        // Tag List Start
        //
        echo $vspacer;
        echo "\n\n"."  <b>$txt_sim_tags:</b>\n  <br />";
        echo "\n".'  <div style="margin-left: 18px;">';
        echo "\n\n".'    <form method="get" action="/tagsearch/" class="combine_tags">'."\n";
	foreach((get_the_category()) as $category) {
                        $counter++;
                        $CID=$category->cat_ID;
                        break; //stop at main category!
       	}

        $args = array( 'category' => $CID);
        $catposts = get_posts($args);

        foreach( $catposts as $post ) {
		setup_postdata($post);
        	$posttags = get_the_tags(get_the_ID());

                if ( is_array($posttags) )
		foreach($posttags as $tag) {
                        if ($tag->count > 1) { // combination not useful in case of one result
                           $count++;
        	   	   $tagstrings[$count] =  $tag->term_id.'; <a href="'.get_tag_link($tag->term_id).'">'.$tag->name.'</a> ('.$tag->count.')<br /> ';
          		   if( $count >15 ) break;
			}
		}
       	}

        if ( is_array($tagstrings) )
        	$unique_tagstrings = array_unique($tagstrings);

        if ( is_array($unique_tagstrings) )
	        sort($unique_tagstrings);

        $countU=0;

        if ( is_array($unique_tagstrings) )
        foreach($unique_tagstrings as $tagstring) {
                $part=explode(";",$tagstring);
	        $out .= "\n".'      <input type="checkbox" name="tagid['.$countU.']" value="'.$part[0].'" />'.$part[1];
                $countU++;
	}

        echo $out;

        //
        // Tag List End
        //


        echo $content_widget;

        echo "\n".'
      <div class="tagbutton">
        <button type="submit" value="'.$txt_combine_tags.'" >'.$txt_combine_tags.'&nbsp;
          <i class="icon-arrow-right icon-button"></i>
        </button>
      </div>

    </form>';
	echo "\n"."
  </div>\n  ";
        echo "\n"."</div>";

        echo $after_widget;

        //close the blue widget


	}
}


function wp_one_post_widget_control($args) {

	$prefix = 'wp-one-post-widget';
	
	$options = get_option('wp_one_post_widget');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);
		
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number]))
				continue;
			
			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}
		
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}

		$options = multiwidget_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'wp_one_post_widget');
	}
	
	$number = ($args['number'] == -1)? '%i%' : $args['number'];

	$opts = @$options[$number];
	$title = @$opts['title'];
	$custom_title = @$opts['custom_title'];
	$readmore = @$opts['readmore'];
	$thumbnail_position = @$opts['thumbnail_position'];
	$use_thumbnail = @$opts['use_thumbnail'];
	 
	?>
  <p><?php _e('Custom Title', 'wponepostwidget') ?></p>
  <p><input type="text" id="custom_title" name="<?php echo $prefix; ?>[<?php echo $number; ?>][custom_title]" value="<?php echo $custom_title; ?>"/></p> 
  <p><?php _e('Search the content for the keyword and select', 'wponepostwidget') ?></p>
  <p><input type="text" id="autocomplete" name="<?php echo $prefix; ?>[<?php echo $number; ?>][title]" placeholder="<?php _e('keyword...','wponepostwidget'); ?>" value="<?php echo $title; ?>"/></p> 
  <p><?php _e('Label Read More', 'wponepostwidget') ?></p>
	<p><input type="text" id="readmore" name="<?php echo $prefix; ?>[<?php echo $number; ?>][readmore]" value="<?php echo $readmore; ?>"/></p>
  <p><?php _e('Use Thumbnail?', 'wponepostwidget') ?></p>
	<p><input type="radio" id="use_thumbnail" name="<?php echo $prefix; ?>[<?php echo $number; ?>][use_thumbnail]" <?php if($use_thumbnail == 'yes'): echo 'checked="checked"'; endif;?> value="yes"/><?php _e('Yes', 'wponepostwidget') ?>
  <input type="radio" id="use_thumbnail" name="<?php echo $prefix; ?>[<?php echo $number; ?>][use_thumbnail]" <?php if($use_thumbnail == 'no'): echo 'checked="checked"'; endif;?> value="no"/><?php _e('No', 'wponepostwidget') ?></p>
  <p><?php _e('Thumbnail Position', 'wponepostwidget') ?></p>
	<p><input type="radio" id="thumbnail_position" name="<?php echo $prefix; ?>[<?php echo $number; ?>][thumbnail_position]" <?php if($thumbnail_position == 'left'): echo 'checked="checked"'; endif;?> value="left"/><?php echo _e('Left', 'wponepostwidget') ?>
	<input type="radio" id="thumbnail_position" name="<?php echo $prefix; ?>[<?php echo $number; ?>][thumbnail_position]" <?php if($thumbnail_position == 'right'): echo 'checked="checked"'; endif;?> value="right"/><?php echo _e('Right', 'wponepostwidget') ?>
  <input type="radio" id="thumbnail_position" name="<?php echo $prefix; ?>[<?php echo $number; ?>][thumbnail_position]" <?php if($thumbnail_position == 'top'): echo 'checked="checked"'; endif;?> value="top"/><?php echo _e('Top', 'wponepostwidget') ?></p>

  <script type="text/javascript">
    jQuery(document).ready(function($) {
      $("input#autocomplete").autocomplete({
        source: function(request, response) {
					  $.ajax({
              url: "<?php echo plugins_url('data.php', __FILE__);?>",
						  dataType: "json",
						  data: {
							  term : request.term,
							  autocompletar : $("#autocomplete").val()
						  },
						  success: function(data) {
							  response(data);
						  }
					  });
				  },
				  minLength: 1
      });
    });
  </script>

	<?
}

if(!function_exists('multiwidget_update')){
	function multiwidget_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();
		
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];
				
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}
		
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}
		
		return $options;
	}
}

/**
* PressTrends Plugin API
*/
	function presstrends_WPOnePostWidget_plugin() {

		// PressTrends Account API Key
		$api_key = 'm269xyyh9z7ewolnfm6y4bup070fu4np1r8b';
		$auth    = 'lr1puahqgb9zdfgk58wzjt4qhku46lwhn';

		// Start of Metrics
		global $wpdb;
		$data = get_transient( 'presstrends_cache_data' );
		if ( !$data || $data == '' ) {
			$api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update/auth/';
			$url      = $api_base . $auth . '/api/' . $api_key . '/';

			$count_posts    = wp_count_posts();
			$count_pages    = wp_count_posts( 'page' );
			$comments_count = wp_count_comments();

			// wp_get_theme was introduced in 3.4, for compatibility with older versions, let's do a workaround for now.
			if ( function_exists( 'wp_get_theme' ) ) {
				$theme_data = wp_get_theme();
				$theme_name = urlencode( $theme_data->Name );
			} else {
				$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
				$theme_name = $theme_data['Name'];
			}

			$plugin_name = '&';
			foreach ( get_plugins() as $plugin_info ) {
				$plugin_name .= $plugin_info['Name'] . '&';
			}
			// CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
			$plugin_data         = get_plugin_data( __FILE__ );
			$posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
			$data                = array(
				'url'             => stripslashes( str_replace( array( 'http://', '/', ':' ), '', site_url() ) ),
				'posts'           => $count_posts->publish,
				'pages'           => $count_pages->publish,
				'comments'        => $comments_count->total_comments,
				'approved'        => $comments_count->approved,
				'spam'            => $comments_count->spam,
				'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
				'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
				'theme_version'   => $plugin_data['Version'],
				'theme_name'      => $theme_name,
				'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
				'plugins'         => count( get_option( 'active_plugins' ) ),
				'plugin'          => urlencode( $plugin_name ),
				'wpversion'       => get_bloginfo( 'version' ),
			);

			foreach ( $data as $k => $v ) {
				$url .= $k . '/' . $v . '/';
			}
			wp_remote_get( $url );
			set_transient( 'presstrends_cache_data', $data, 60 * 60 * 24 );
		}
	}


/*
function amaz_price($post_id) {
                $region=get_region();
                //print "Region: $region<br>";
                $myFile = "/home/wordpressftp/amazon_date-$region-short.txt";
                //$myFile = "/home/wordpressftp/amazon-$region.txt";
		$file = file($myFile);
		$file = array_reverse($file);

                foreach($file as $f){
                  //echo "pid: $post_id = $f ?<br>";
                  if (strpos($f, (string)$post_id) !== false){
                   //echo "$post_id found<br>";
                   $out="OUT:".$post_id.":".$f." ";

                   $price = explode(";", $f);

                   return chop($price[1]);

		  } else {
                   //echo "$post_id not found<br>";

                  }
                }
                //$out .= "xx";
                unset ($file);
	return $out;

 
	}
*/

/*
	function amaz_url($post_id) {
                $region=get_region();



                //$myFile = "/home/wordpressftp/amazon_date-$region.txt";
                $myFile = "/home/wordpressftp/amazon_date-$region-short.txt";
		//print "File: $myFile<br>";
                $file = file($myFile);
		//$file = array_reverse($file);

                foreach($file as $f){
                  //echo "pid: $post_id = $f ?<br>";
                  if (strpos($f, (string)$post_id) !== false){
                   //echo "$post_id found<br>";
                   $out="OUT:".$post_id.":".$f." ";

                   $price = explode(";", $f);

                   return chop($price[3]);

		  } else {
                   //echo "$post_id not found<br>";

                  }
                }
                //$out .= "xx";
	return $out;
	}
        */

	function get_price_aff( $aff_PID , $num = 1) {

                global $post;

                $time = time();

                //echo "postid: ".$post->ID."<br>";
                //echo "aff_PID: $aff_PID<br>";
                //echo "Time: $time<br>";
                //echo "num: $num<br>";

		$meta      = "affilinet_date";
	        $aff_date  = get_post_meta( $post->ID , $meta , true ); //amaz_url($post->ID);

		$meta      = "affilinet_price";
	        $aff_price = get_post_meta( $post->ID , $meta , true ); //amaz_url($post->ID);

                $tdiff     = $time - $aff_date;


                if ($num != count(str_getcsv($aff_price,";")) ){
                	//inconsistency in product numbers. Maybe one was added recently
                        //echo "inconsistency: $aff_PID<br>";
                        system ( "php /usr/share/wordpress/wp-content/plugins/amazon-product-in-a-post-plugin/affilinet.php ".str_replace(";",":",$aff_PID)." 0 de &" );
                        $inconsistency = 1;
                }


                //echo "affdate: $aff_date <br>";
                //echo "Diff: $tdiff (".(60*60*22).")";

                if ( ( $tdiff  < 60*60*22 ) && ( $aff_price != "" ) && ( $inconsistency == 0 ) ) {
                        //echo "Found new data<br>";


			$meta         = "affilinet_shipping";
		        $aff_shipping = get_post_meta( $post->ID , $meta , true ); //amaz_url($post->ID);

			$meta         = "affilinet_shop_id";
		        $aff_shop_id  = get_post_meta( $post->ID , $meta , true ); //amaz_url($post->ID);

			$meta         = "affilinet_url";
		        $aff_url  = get_post_meta( $post->ID , $meta , true ); //amaz_url($post->ID);

                        return  array( $aff_date , $aff_shop_id , $aff_price , $aff_shipping , $aff_url );

                }else{
                        // no price available or too old
                        //echo "No price available<br>";

                        //
                        // 1. check file for updated price
                        //
                        //echo "Check file<br>";
                        $myFile = "/home/wordpressftp/affilinet_data.txt";
			$file   = file( $myFile );
			$file   = array_reverse( $file );

                        //expected format:
                        //aff_PID1;aff_PID2###aff_date###shop_id1;shop_id2###aff_price(1):aff_price(2):...###url(1):url(2):...

                        //echo "APID : $aff_PID<br>";

                        $found = 0;
	                foreach( $file as $f ){
	                  if (strpos($f, chop($aff_PID)) !== false){
	                  	//echo "pid: $post_id ? Pos:".strpos($f, (string)$post_id)." Found<br>";
        	          	//echo "FOUND:".$aff_PID.":".$f." ";

	                   	list ($null,$aff_date,$shop_id,$price,$shipping,$url) = explode("###", $f);

        	          	//echo "<br>aff_date:".$aff_date;
				$meta      = "affilinet_date";
			        update_post_meta( $post->ID , $meta , $aff_date ); //amaz_url($post->ID);
                                //echo "<br>wrote to file";
                                /*
                                echo "Null: $null<br>";
                                echo "affDate: $aff_date<br>";
                                echo "shop_id: $shop_id<br>";
                                echo "price: $price<br>";
                                echo "shipping: $shipping<br>";
                                echo "url: $url<br>";
                                */

                                $found=1;

	                   }else{
                                //echo "$aff_PID not found in $f<br>";
                           }
                           //if ($found == 1) echo "Found $found";
                           if ($found == 1) break;
                	}


                        //
                        // 2. write to DB if newer than 0.5d
                        //
                        $diff2 = $time - $aff_date;
                        //echo "Diff2: $diff2";

                        if ( $diff2 < 12*60*60 ) {

                                //echo "<br>writing from file to DB<br>";

	                        $meta      = "affilinet_date";
		        	update_post_meta( $post->ID , $meta , $aff_date ); //amaz_url($post->ID);

				$meta      = "affilinet_price";
			        update_post_meta( $post->ID , $meta , $price ); //amaz_url($post->ID);

				$meta      = "affilinet_shipping";
			        update_post_meta( $post->ID , $meta , $shipping ); //amaz_url($post->ID);

				$meta      = "affilinet_shop_id";
		       	        update_post_meta( $post->ID , $meta , $shop_id ); //amaz_url($post->ID);

				$meta      = "affilinet_url";
		        	update_post_meta( $post->ID , $meta , $url ); //amaz_url($post->ID);

                	}else{
				// request update otherwise
        	                //echo "request via API<br>";

                	        //echo ( "/usr/share/wordpress/wp-content/plugins/amazon-product-in-a-post-plugin/affilinet.php $aff_PID 0 de & 2>>/tmp/aff.log " );

                        	//background version
	                        //system ( 'php "/usr/share/wordpress/wp-content/plugins/amazon-product-in-a-post-plugin/affilinet.php '.$aff_PID.' 0 de" &' );

        	                //foreground version
                	        system ( "php /usr/share/wordpress/wp-content/plugins/amazon-product-in-a-post-plugin/affilinet.php $aff_PID 0 de &" );


                        	// create new one
                        }

                        //check if multiple IDs but only one price

                        if ($found == 1) return array( $aff_date , $shop_id , $price , $shipping , $url );

                        return -1;

                }


                /*

                $myFile = "/home/wordpressftp/nb_date.txt";
		$file = file($myFile);
		$file = array_reverse($file);

                foreach($file as $f){
                  //echo "pid: $post_id = $f ? Pos:".strpos($f, (string)$post_id)."<br>";
                  if (strpos($f, (string)$post_id) !== false){
                  //echo "pid: $post_id ? Pos:".strpos($f, (string)$post_id)." Found<br>";

		  //take care that string correspond to ID and not to URL
                  if (strpos($f, (string)$post_id) < 1){

                  //echo "$post_id found<br>";
                   $out="OUT:".$post_id.":".$f." ";

                   $price = explode(";", $f);

                   return chop($price[1]);
                   }
		  } else {
                   //echo "$post_id not found<br>";

                  }
                }
                //$out .= "xx";
                */
	}


	function NB_price($post_id) {

                $myFile = "/home/wordpressftp/nb_date.txt";
		$file = file($myFile);
		$file = array_reverse($file);

                foreach($file as $f){
                  //echo "pid: $post_id = $f ? Pos:".strpos($f, (string)$post_id)."<br>";
                  if (strpos($f, (string)$post_id) !== false){
                  //echo "pid: $post_id ? Pos:".strpos($f, (string)$post_id)." Found<br>";

		  //take care that string correspond to ID and not to URL
                  if (strpos($f, (string)$post_id) < 1){

                  //echo "$post_id found<br>";
                   $out="OUT:".$post_id.":".$f." ";

                   $price = explode(";", $f);

                   return chop($price[1]);
                   }
		  } else {
                   //echo "$post_id not found<br>";

                  }
                }
                //$out .= "xx";
	return $out;

 
	}

	function NB_url($post_id) {

                $myFile = "/home/wordpressftp/nb_date.txt";
		$file = file($myFile);
		$file = array_reverse($file);

                foreach($file as $f){
                  //echo "pid: $post_id = $f ?<br>";
                  if (strpos($f, (string)$post_id) !== false){
                   //echo "$post_id found<br>";
                   $out="OUT:".$post_id.":".$f." ";

                   $price = explode(";", $f);

                   return chop($price[3]);

		  } else {
                   //echo "$post_id not found<br>";

                  }
                }
                //$out .= "xx";
	return $out;

 
	}

function get_affiliate_prices($post_id) {
        global $vspacer;

        //echo $post_id;

        $PartnerID   = array(939,1752,2580,1739);
        $PartnerName = array("Notebooksbilliger.de","Reichelt Elektronik","Atelco.de","Redcoon");

        for ($i=0; $i<sizeof($PartnerID); $i++){
                //echo "I: $i - ";
                $myFile = "/home/wordpressftp/affilinet-".$PartnerID[$i]."_date-de.txt";
		$file = file($myFile);
		$file = array_reverse($file);

		 foreach($file as $f){
                  //echo "pid: $post_id = $f ?<br>";
                  if (strpos($f, (string)$post_id) !== false){
	            if (strpos($f, (string)$post_id) == 0){

                  	$foundID=split(";",$f);

                        if ($foundID[0] == (string)$post_id){
                	   	//echo "$post_id found<br>";

	                   	//TBD: Check date if offer still valid

                   		//$out="OUT:".$post_id.":".$f." ";

                	   	$price = explode(";", $f);
        	           	//echo "Price: $price[1]";
	                   	//echo "URL: $price[3]";


                                // Do not show old findings
                                $date = chop($price[2]);
                                $now=time();
                                //echo "DATE: $date, Now: $now diff:".($now-$date);
                                if ( ($now - $date) > 60*60*24*4) break;


                   		$PID_price[$i]     = chop($price[1]);
                	   	$PID_url[$i]       = chop($price[3]);
        	           	$PID_Shipping[$i]  = chop($price[4]);
	        	   	$PID_url[$i]=str_replace("+++++",";",$PID_url[$i]);

                   		break;
                   	}
                    }
                  } else {
                   //echo "$post_id not found<br>";

                  }
		}
        }
                //$out .= "xx";

        // format output
        $found=0;
        $out .= '<table class="invisible">';
        $out .= '<tr><td>&nbsp;<b>Weiterer Anbieter</b></td><td width=15%><b>Preis</b></td><td><center><b>Versand</b></center></td></tr>';

        for ($i=0; $i<sizeof($PartnerID); $i++){
                if ($PID_price[$i] != ""){
        	        $out .= '<tr>
                        <td>&nbsp;<a href="'.$PID_url[$i].'">'.$PartnerName[$i].'</a></td>
                        <td width=15%>EUR&nbsp;'.str_replace(".",",",number_format($PID_price[$i],2)).' </td>
                        <td width=15%><center>'.str_replace(".",",",number_format($PID_Shipping[$i],2)).'</center></td></tr>';
                        $found++;
		}
	}

        $out = $vspacer."".$out."</table>";

        if ($found>0) return $out;
        return;
}


// PressTrends WordPress Action
add_action('admin_init', 'presstrends_WPOnePostWidget_plugin');

function lhg_country_row ($p_region, $URLC, $URLD, $posturlcom, $posturlde){

	# store url to be available globally (e.g. by wp-one-post-widget)
	global $posturlcom_glob;
	global $posturlde_glob;
	$posturlcom = $posturlcom_glob;
	$posturlde  = $posturlde_glob;


        global $post;
        global $txt_out_of_stock;
        global $region;

        $selected = "";
	if ($region == $p_region) $selected = 'class="ct_pricetop" ';

        #print "URLD: $URLD -- $posturlde<br>";
        print "<tr $selected>";
        #print "PID: $post->ID";

        # get the correct postid for .com and .de server
        $relevant_postid = $post->ID;
        if ($lang == "de") $relevant_postid = lhg_get_postid_de_from_com( $post->ID );

        $price = lhg_db_get_cheapest_price_by_region($relevant_postid, $p_region);
	$price = lhg_float_to_currency_string( $price , $p_region );

        if ($price == 0) {
        	$price = $txt_out_of_stock;
                $txt_currency = "";
	}else{
        	$txt_currency = lhg_get_currency_symbol( $p_region );
                $price = $price ;
        }

        #if ($p_region == "de"){
        #
        #        print "PRI: $price<br>";
        #        # ToDo: need access to .de database or Amazon prices to be stored on priceDB
	#        $price = "-";
        #        $txt_currency = "";
        #}

        echo '<td class="ct_country">&nbsp;&nbsp;';

        if ($p_region == "de")    { $URL_add = '/'; $flag = "de"; $country= "Germany";}
        if ($p_region == "ca")    { $URL_add = '/ca/'; $flag = "ca"; $country= "Canada";}
        if ($p_region == "com")   { $URL_add = '/'   ; $flag = "us"; $country= "USA";}
        if ($p_region == "co.uk") { $URL_add = '/uk/'; $flag = "uk"; $country= "United Kingdom";}
        if ($p_region == "fr")    { $URL_add = '/fr/'; $flag = "fr"; $country= "France";}
        if ($p_region == "es")    { $URL_add = '/es/'; $flag = "es"; $country= "Espana";}
        if ($p_region == "it")    { $URL_add = '/it/'; $flag = "it"; $country= "Italia";}
        if ($p_region == "nl")    { $URL_add = '/nl/'; $flag = "nl"; $country= "Netherlands";}
        if ($p_region == "in")    { $URL_add = '/in/'; $flag = "in"; $country= "India";}
        if ($p_region == "co.jp") { $URL_add = '/ja/'; $flag = "jp"; $country= "Japan";}
        if ($p_region == "cn")    { $URL_add = '/zh/'; $flag = "cn"; $country= "China";}

        if ($p_region == "de") $URL = $URLD.$URL_add.$posturlde;
        if ($p_region != "de") $URL = $URLC.$URL_add.$posturlcom;
        echo '<a href="'.$URL.'"><img src="/wp-content/plugins/qtranslate/flags/'.$flag.'.png" alt="'.$country.'" /> '.$country.'</a></td>';
        echo '<td class="ct_price">'.$price.'</td>';
        echo '<td class="ct_currency">'.$txt_currency.'</td>';
        echo '<td class="ct_comment">';
        # comment number
        #not yet implemented for "de"
        #if ($p_region == "de"){
	#        echo "-";
	#}else{
        	lhg_comments_number_language( $p_region, $p_region , 0 , $post->ID, "shortversion");
	#}
        echo '</td></tr>';


}


?>
