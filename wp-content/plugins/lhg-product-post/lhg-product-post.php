<?php

/*
Plugin Name: LHG Product Post Plugin
Plugin URI: http://www.linux-hardware-guide.com
Description: Inserts product information in a post. This plugin originates from the "Amazon Product In a Post Plugin"
Author: cptpike
Author URI: 
Version: 1.0.0
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/*

# This plguin originates from the Amazon Product In a Post Plugin mentioned above but was
# in major parts modified for Linux-Hardware-Guide

Original Plugin Name: Amazon Product In a Post Plugin
Original Plugin URI: http://fischercreativemedia.com/wordpress-plugins/amazon-affiliate-product-in-a-post/
Original Description: Quickly add a formatted Amazon Product (image, pricing and buy button, etc.) to a post by using just the Amazon product ASIN (ISBN-10). Great for writing product reviews or descriptions to help monetize your posts and add content that is relevant to your site. You can also customize the styles for the product data. Remember to add your Amazon Affiliate ID on the <a href="admin.php?page=apipp_plugin_admin">options</a> page or all sales credit will go to the plugin creator by default.
Original Author: Don Fischer
Original Author URI: http://www.fischercreativemedia.com/
Original Version: 2.0.2
    Copyright (C) 2009-2012 Donald J. Fischer
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


// Warnings Quickfix
	if(get_option('apipp_hide_warnings_quickfix')==true){
		 ini_set("display_errors", 0); //turns off error display
	}
	register_activation_hook(__FILE__,'appip_install');
	register_deactivation_hook(__FILE__,'appip_deinstall');

// Variables
	global $public_key;
	global $private_key; 
	global $aws_partner_id;
	global $aws_eatra_pages;
	global $aws_plugin_version;
	global $aws_partner_locale;
	global $thedefaultapippstyle;
	global $amazonhiddenmsg;
	global $amazonerrormsg;
	global $apipphookexcerpt;
	global $apipphookcontent;
	global $apippopennewwindow;
	global $apippnewwindowhtml;
	global $encodemode; //1.7 new
	global $appip_text_lgimage;
	global $appip_text_listprice; 
	global $appip_text_newfrom; 
	global $appip_text_usedfrom;
	global $appip_text_instock;
	global $appip_text_outofstock; 
	global $appip_text_author;
	global $appip_text_starring;
	global $appip_text_director;
	global $appip_text_reldate;
	global $appip_text_preorder;
	global $appip_text_releasedon;
	global $appip_text_notavalarea;
	global $buyamzonbutton;
	global $addestrabuybutton;
	global $awspagequery;
	global $apip_language;
	global $appuninstall;
	global $appuninstallall;
	global $thedefaultapippstyle;



// Includes
	#require_once("inc/sha256.inc.php"); //required for php4
	#require_once("inc/aws_signed_request.php"); //major workhorse for plugin
	require_once("inc/amazon-product-in-a-post-tools.php"); //edit box for plugin
	require_once("inc/amazon-product-in-a-post-options.php"); //admin options for plugin
	require_once("inc/amazon-product-in-a-post-translations.php"); //translations for plugin
	require_once("inc/amazon-product-in-a-post-styles-product.php"); //styles for plugin


//upgrade check. Lets me add/change the default style etc to fix/add new items during updrages.
    #    $thisstyleversion=get_option('apipp_product_styles_default_version');
    #    if($thisstyleversion!="1.7" || get_option("apipp_product_styles_default")==''){
    #    	update_option("apipp_product_styles_default",$thedefaultapippstyle);
    #    	update_option("apipp_product_styles_default_version","1.7");
		
		//add the new element style to their custom ones - so at least it has the default functionality. They can change it after if they like
    #    	$apipp_product_styles_cust_temp = get_option("apipp_product_styles");
    #    	if($apipp_product_styles_cust_temp!=''){
    #    		update_option("apipp_product_styles",$apipp_product_styles_cust_temp.'div.appip-multi-divider{margin:10px 0;}');
    #    	}
 		
    #    	update_option("apipp_amazon_notavailable_message","This item is may not be available in your area. Please click the image or title of product to check pricing & availability."); //default message
    #    	update_option("apipp_amazon_hiddenprice_message","Price Not Listed"); //default message - done
    #    	update_option("apipp_hook_content","1"); //default is yes - done
    #    	update_option("apipp_hook_excerpt","0"); //default is no - done
    #    	update_option('apipp_open_new_window',"0"); //default is no - newoption added at 1.6 - done
    #    }

//added in 1.7 to allow those that could not use file_get_contents() to use Curl instead.		
    #    if(get_option('awsplugin_amazon_usefilegetcontents')==''){update_option('awsplugin_amazon_usefilegetcontents','1');}
    #    if(get_option('awsplugin_amazon_usecurl')==''){update_option('awsplugin_amazon_usecurl','0');}
    #    //if(get_option('apipp_API_call_method')==''){update_option('apipp_API_call_method','0');}
    #    if(get_option('apipp_API_call_method')=='' && get_option('awsplugin_amazon_usecurl')=='0'){
    #    	update_option('apipp_API_call_method','0');}
    #    elseif(get_option('apipp_API_call_method')=='' && get_option('awsplugin_amazon_usecurl')!='1'){
    #    	update_option('apipp_API_call_method','1');
    #    }
	
	
    #    session_start();	
    #if(!isset($_SESSION['Amazon-PIPP-Cart-HMAC'])) $_SESSION['Amazon-PIPP-Cart-HMAC'] = '';
    #if(!isset($_SESSION['Amazon-PIPP-Cart-Encoded-HMAC'])) $_SESSION['Amazon-PIPP-Cart-Encoded-HMAC']='';
    #if(!isset($_SESSION['Amazon-PIPP-Cart-ID'])) $_SESSION['Amazon-PIPP-Cart-ID']='';
    
	$awspagequery		= '';
	$public_key 		= get_option('apipp_amazon_publickey'); //Developer Public AWS Key
	$private_key 		= get_option('apipp_amazon_secretkey'); //Developer Secret AWS Key
	$appuninstall 		= get_option('apipp_uninstall'); //Uninstall database and options
	$appuninstallall	= get_option('apipp_uninstall_all'); //Uninstall shortcodes in pages an posts
	//$aws_partner_id		= get_option('apipp_amazon_associateid'); //Amazon Partner ID 
	//$aws_partner_locale	= get_option('apipp_amazon_locale'); //Amazon Locale - moved to translations file
	$awsPageRequest 	= 1;
	$aws_plugin_version = "2.0";
	$amazonhiddenmsg 	= get_option('apipp_amazon_hiddenprice_message'); //Amazon Hidden Price Message
	$amazonerrormsg 	= get_option('apipp_amazon_notavailable_message'); //Amazon Error No Product Message
	$apipphookexcerpt 	= get_option('apipp_hook_excerpt'); //Hook the excerpt?
	$apipphookcontent 	= get_option('apipp_hook_content'); //Hook the content?
	$apippopennewwindow = get_option('apipp_open_new_window'); //open in new window?
	$aws_eatra_pages 	= '';
	$aws_eatra_pages 	= '"ItemPage"=>"'.$awspagequery.'",';
	$thereapippstyles 	= get_option("apipp_product_styles_default"); 
	$apippnewwindowhtml	= '';
	$apip_getmethod 	= get_option('apipp_API_call_method');
	$apip_usefileget 	= '0';
	$apip_usecurlget	= '0';
	$encodemode 		= get_option('appip_encodemode'); //1.7 added - UTF-8 will be default\

        //$aws_partner_locale = get_region();



	// 1.7 api get method defaults/check
	if($apip_getmethod=='0'){
		$apip_usefileget = '1';
	}
	if($apip_getmethod=='1'){
		$apip_usecurlget = '1';
	}
	if($apip_getmethod==''){
		$apip_usefileget = '1'; //set default if not set
	}
	
	//1.7 Encode Mode
	if(get_option('appip_encodemode')==''){
		update_option('appip_encodemode','UTF-8'); //set default to UTF-8
		$encodemode="UTF-8";
	}
	
	//1.8 backward compat.
	if(!function_exists('mb_convert_encoding')){
		function mb_convert_encoding($etext='', $encodemode='', $encis=''){
			return $etext;
		}
	}	
	if(!function_exists('mb_detect_encoding')){
		function mb_detect_encoding($etext='', $encodemode=''){
			return $etext;
		}
	}	
	
	// 1.7 - change encoding if needed via GET
	// use http://yoursite.com/?resetenc=UTF-8 or http://yoursite.com/?resetenc=ISO-8859-1
	// this will be the mode you want the text OUTPUT as.
	if(isset($_GET['resetenc'])){
		if($_GET['resetenc']=='ISO-8859-1' || $_GET['resetenc']=='UTF-8'){
			update_option('appip_encodemode',$_GET['resetenc']);
			$encodemode = $_GET['resetenc'];
		}
	}
	if($apippopennewwindow==true){
		$apippnewwindowhtml=' target="amazonwin" ';
	}
	if($amazonerrormsg==''){
		$amazonerrormsg='Product Unavailable.';
	}
	if($amazonhiddenmsg==''){
		$amazonhiddenmsg='Visit Amazon for Price.';
	}
	if($aws_partner_locale==''){
		update_option('apipp_amazon_locale','com'); //set default to US
		$aws_partner_locale='com';
	}
	if($aws_partner_id==''){
		//$aws_partner_id = "wolvid-20"; //Amazon Partner ID - if one is not set up, we will use Plugin Creator's ID - so be sure to set one up!!
	}
	if($public_key==''){
                global $lhg_amazon_public_key;
		$public_key = $lhg_amazon_public_key; // Developer Public AWS Key
	}
	if($private_key==''){
                global $lhg_amazon_developer_secret;
		$private_key = $lhg_amazon_developer_secret;
	}
	
	if(isset($_GET['awspage'])){ //future item for search results
		if(is_numeric($_GET['awspage'])){
			$awspagequery = (int)$wpdb->escape($_GET['awspage']);
		}
	}
	if($awspagequery>1){ //future item for search results
		$awsPageRequest = $awspagequery;
	}
	
	if(trim(get_option("apipp_product_styles")) == ''){ //reset to default styles if user deletes styles in admin
		update_option("apipp_product_styles",$thedefaultapippstyle);
	}
	

// Filters & Hooks
	
	add_filter('the_content', 'aws_prodinpost_filter_content', 10); //hook content - we will filter the override after
	add_filter('the_excerpt', 'aws_prodinpost_filter_excerpt', 10); //hook excerpt - we will filter the override after 
	add_action('wp_head','aws_prodinpost_addhead',10); //add styles to head
	add_action('wp','add_appip_jquery'); //enqueue scripts
	add_action('admin_head','aws_prodinpost_addadminhead',10); //add admin styles to admin head
	//add_action('wp','aws_prodinpost_cartsetup', 1, 2); //Future Item

// Functions
	function appip_deinstall() {
		global $wpdb;
		$appuninstall 		= get_option('apipp_uninstall'); 
		$appuninstallall	= get_option('apipp_uninstall_all');
		if($appuninstall == 'true'){
			$appiptable = $wpdb->prefix . 'amazoncache'; 
			$deleteSQL = "DROP TABLE $appiptable";
	      	$wpdb->query($deleteSQL);
			delete_option('apipp_amazon_publickey');
			delete_option('apipp_amazon_secretkey');
			delete_option('apipp_uninstall');
			delete_option('apipp_uninstall_all');
			delete_option('apipp_amazon_associateid'); 
			delete_option('apipp_amazon_locale');
			delete_option('apipp_amazon_hiddenprice_message');
			delete_option('apipp_amazon_notavailable_message');
			delete_option('apipp_hook_excerpt');
			delete_option('apipp_hook_content');
			delete_option('apipp_open_new_window');
			delete_option('apipp_product_styles_default'); 
			delete_option('apipp_API_call_method');
			delete_option('appip_encodemode');
			delete_option('apipp_amazon_language');
			delete_option('apipp_product_styles_mine');
			delete_option('apipp_version');
			delete_option('apipp_show_single_only');
			delete_option('apipp_product_styles_default_version');
			delete_option('apipp_product_styles');
		}

		if($appuninstall == 'true' && $appuninstallall == 'true'){
			//DELETE ALL POST META FOR ITEMS WITH APIPP USAGE
			$remSQL = "DELETE FROM $wpdb->postmeta WHERE `meta_key` LIKE '%amazon-product%';";
			$cleanit = $wpdb->query($remSQL);
			//Now get data for IDs with content or excerpt containing the shortcodes.
			$thesqla = "SELECT ID, post_content, post_excerpt FROM $wpdb->posts WHERE post_content like '%[AMAZONPRODUCT%' OR post_excerpt like '%[AMAZONPRODUCT%';";
			$postData = $wpdb->get_results($thesqla);
			if(count($postData)>0){
				foreach ($postData as $pdata){
					$pcontent = $pdata->post_content;
					$pexcerpt = $pdata->post_excerpt;
					$pupdate  = 0;
					$pid 	  = $pdata->ID;
					$search   = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(.+|^\+)\]\s*(?:</p>)*@i"; 
					if(preg_match_all($search, $pcontent, $matches1)) {
						if (is_array($matches1)) {
							foreach ($matches1[1] as $key =>$v0) {
								$search 	= $matches1[0][$key];
								$ASINis		= $matches1[1][$key];
								$pcontent 	= str_replace ($search, '', $pcontent);
							}
							$pupdate  = 1;
						}
					}
					if(preg_match_all($search, $pexcerpt, $matches2)) {
						if (is_array($matches2)) {
							foreach ($matches2[1] as $key =>$v0) {
								$search		= $matches2[0][$key];
								$ASINis		= $matches2[1][$key];
								$pexcerpt	= str_replace ($search, '', $pexcerpt);
							}
							$pupdate  = 1;
						}
					}
					if($pupdate == 1){
						$wpdb->query("UPDATE $wpdb->posts SET post_excerpt = '$pexcerpt', post_content = '$pcontent' WHERE ID = '$pid';");
					}
				}
			}
		}
	}
	// Install Function - called on activation
	function appip_install () {
		global $wpdb, $wp_roles, $wp_version, $aws_plugin_version;
		if(get_option("apipp_version")== ''){
			$appiptable = $wpdb->prefix . 'amazoncache'; 
			$createSQL = "CREATE TABLE IF NOT EXISTS $appiptable (`Cache_id` int(10) NOT NULL auto_increment, `URL` text NOT NULL, `updated` datetime default NULL, `body` text, PRIMARY KEY (`Cache_id`), UNIQUE KEY `URL` (`URL`(255)), KEY `Updated` (`updated`)) ENGINE=MyISAM;";
	      	//echo $createSQL;
	      	$wpdb->query($createSQL);
			add_option("apipp_version", $aws_plugin_version);
		}
	}

	//Single Product API Call - Returns One Product Data
	function getSingleAmazonProduct($asin='',$extratext='',$extrabutton=0){

		global $public_key, $private_key, $aws_partner_id,$aws_partner_locale,$amazonhiddenmsg,$amazonerrormsg,$apippopennewwindow,$apippnewwindowhtml;
		global $appip_text_lgimage;
		global $appip_text_listprice; 
		global $appip_text_newfrom; 
		global $appip_text_usedfrom;
		global $appip_text_instock;
		global $appip_text_outofstock; 
		global $appip_text_author;
		global $appip_text_starring;
		global $appip_text_director;
		global $appip_text_reldate;
		global $appip_text_preorder;
		global $appip_text_releasedon;
		global $appip_text_notavalarea;
		global $buyamzonbutton;
		global $addestrabuybutton;
		global $encodemode;
		global $post;
                global $amazonRun;

                //text strings
                global $lang;
	        global $txt_compat;
	        global $txt_Compat;
	        global $txt_with;
	        global $txt_rating;
	        global $txt_ratings;
                global $txt_price;
                global $txt_out_of_stock;
        	global $txt_button;
        	global $txt_button_width;
                global $siteurl;
                global $txt_pricetrend;
                global $txt_A_preis;
        	global $txt_currency;
	        global $txt_shipping;
	        global $txt_on_stock;
                global $txt_updated;
                global $txt_buy_from;
                global $txt_search_at;
                global $txt_preorder;
                global $txt_not_avail_at;
                global $txt_never_avail;

                $region = get_region();
      		$aws_partner_id = get_id();
                $aws_partner_locale = $region;


                //print "PL: $aws_partner_locale";
                //$apippOpenNewWindow = get_post_meta($post->ID,'amazon-product-newwindow',true);
		//if($apippOpenNewWindow!='3'){$apippnewwindowhtml=' target="amazonwin" ';}

                #print "A0: $asin<br>";

		$asinlist = get_post_meta($post->ID,'amazon-product-asin-list',true);
                #print "CSV: ".str_getcsv($asin.",".$asinlist, ",");
		$data = str_getcsv($asin.",".$asinlist, ",");

                #var_dump($data);

                #
                # check if DB entries for asins already exist -> create if necessary
                #
                $i = 0;
		while ( $i< count($data) ){
                	$asin_tmp = $data[$i];
                        #print " A: $asin";
                        if ($asin_tmp != "") lhg_amazon_create_db_entry($region, $post->ID, $asin_tmp );
                        $i++;
		}


                //print "<br>Data0: $data[0] <br>";
                //print "Data1: $data[1] <br>";


                #print "Af: $asin<br>";
		if ($asin!=''){
                        #print "count: ".count($data);
                        #$i=0;
                        #while ( $i< count($data) ){
                        # one loop is enough. SQL request looks at all ASINs
                        //print "I: $i <br>";
                        global $amazonRun;
                        $amazonRun = 0; // reset loop counter for new asin list element

                	$asin 					= $data[$i]; // $asin; //valid ASIN
                	$ASIN 					= $asin; //valid ASIN

                        //print "ASIN: $asin";

			$errors 				= '';
			#$appip_responsegroup 	= "ItemAttributes,Images,Offers,Reviews";
			#$appip_operation 		= "ItemLookup";
			#$appip_idtype	 		= "ASIN";

                        #if ($region != "nl")
                        	#$pxml = aws_signed_request($aws_partner_locale, array("Operation"=>$appip_operation,"ItemId"=>$ASIN,"ResponseGroup" => $appip_responsegroup,"IdType"=>$appip_idtype,"AssociateTag"=>$aws_partner_id ), $public_key, $private_key);
			
                        //print "<br>PXML: ";var_dump($pxml);
			#if(!is_array($pxml)){
			#	$pxml2=$pxml;
			#	$pxml = array();
			#	$pxml["itemlookuperrorresponse"]["error"]["code"]["message"] = $pxml2;
			#}
			#if(isset($pxml["itemlookuperrorresponse"]["error"]["code"])){
			#	$errors = $pxml["itemlookuperrorresponse"]["error"]["code"]["message"];
			#}
			

                        //print "<br>Article found, checking";
                                //Call 1
                                $amazonRun +=1; //count loops to prevent multiple requests (will be reset for ASIN lists)
                                //echo "<hr>Call 1<br>RUN: $amazonRun<br>";
                                //if ($run == 2) break;
                                #$result = FormatASINResult($pxml,$post->ID);
				$newPrice = lhg_db_get_lowest_amazon_price($post->ID, $region);
                                
                        	#print "<br>New Price: $newPrice - $asin, $region";

                        #if ($newPrice != "" and $newPrice != 0) break;

                        #$i++;
			#}

                        if ($lang != "de")
                        	if ($newPrice == 0) $newPrice = "out of stock";

                        if ($lang == "de")
                        	if ($newPrice == 0) $newPrice = "nicht lieferbar";




			if($errors=='exceeded'){
			#	$hiddenerrors = "<"."!-- HIDDEN AMAZON PRODUCT IN A POST ERROR: Requests Exceeded -->";
			#	$errors = 'Requests Exceeded';
			#	if($extratext!=''){return $hiddenerrors.$extratext;}
			#	return $hiddenerrors;
			#}elseif($errors=='no signature match'){
		        #        //echo "HERE A!";
			#	$hiddenerrors = "<"."!-- HIDDEN AMAZON PRODUCT IN A POST ERROR: Signature does not match AWS Signature. Check AWS Keys and Signature method. -->";
			#	$errors = 'Signature does not match';
			#	if($extratext!=''){return $hiddenerrors.$extratext;}
			#	return $hiddenerrors;
			//}elseif($errors=='not valid'){
		        //        //echo "HERE B!";
			//	$hiddenerrors = "<"."!-- HIDDEN AMAZON PRODUCT IN A POST ERROR: Item Not Valid. Possibly not available in your locale or you did not enter a correct ASIN. -->";
			//	$errors = 'Not a valid item';
			//	if($extratext!=''){return $hiddenerrors.$extratext;}
			//	return $hiddenerrors;
			//}elseif($errors!=''){
			//	$hiddenerrors = "<"."!-- HIDDEN AMAZON PRODUCT IN A POST ERROR: ". $errors ."-->";
			//	if($extratext!=''){return $hiddenerrors.$extratext;}
			//	return $hiddenerrors;
			}else{


                                //global $run;
                                //$run += 1;
                                //Call 2
                                //echo "<hr>Call 2<br>";
				//$result = FormatASINResult($pxml,$post->ID);
                                //print_r ($result);

                                //echo "<br>WHAT: ".$result['URL'];


                                //$returnval  = '	<br /><div class="amazon-box">';
                                //$returnval = '                               ';
                                //$returnval  .= ' 	 			<div class="amazon-box">';
                                //<table cellpadding="0"class="amazon-product-table">'."\n";
				//$returnval .= '		<tr>'."\n";
				//$returnval .= '			<td valign="top">'."\n";
				$returnval .= '				<div class="amazon-image-wrapper">'."\n";
				//$returnval .= '					<a href="' . $result["URL"] . '" '. $apippnewwindowhtml .'>' . awsImageGrabber($result['MediumImage'],'amazon-image') . '</a><br />'."\n";

                                global $txt_cpl_noimage; // = "Kein Bild verf&uuml;gbar";


                                #
                                # ToDo: URL should be stored in DB and not fetched each time
                                #
                                #$amazon_image = awsImageGrabber($result['MediumImage'],'amazon-image');

				$attr = array(
					'alt' => trim(strip_tags( translate_title( get_the_title($PID) ) )),
					'title' => trim(strip_tags( translate_title( get_the_title($PID) ) )),
					);

                                $amazon_image = get_the_post_thumbnail($artid, array(130,130), $attr );
                                if ( strpos($amazon_image,'src=""')) $amazon_image ='<img src="/wp-uploads/2013/03/noimage130.jpg" alt="'.$txt_cpl_noimage.'" title="'.$txt_cpl_noimage.'">';
                                if ( $amazon_image == "" ) $amazon_image ='<img src="/wp-uploads/2013/03/noimage130.jpg" alt="'.$txt_cpl_noimage.'" title="'.$txt_cpl_noimage.'">';

                                $returnval .= '					'. $amazon_image . ''."\n";

                                //$returnval .= '					'. awsImageGrabber($result['MediumImage'],'amazon-image') . ''."\n";
				//if($result['LargeImage']!=''){
				//$returnval .= '				<a target="amazon-image" href="javascript: void(0)" onclick="artwindow=window.open(\'' .$result['LargeImage'] .'\',\'art\',\'directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no, toolbar=no, width=400,height=525\');artwindow.focus();return false;"><span class="amazon-tiny">'.$appip_text_lgimage.'</span></a>'."\n";
				//$returnval .= '					<a rel="appiplightbox" href="'.$result['LargeImage'] .'"><span class="amazon-tiny">'.$appip_text_lgimage.'</span></a>'."\n";
				//$returnval .= '					<span class="amazon-tiny">'.$appip_text_lgimage.'</span>'."\n";
				//}
				$returnval .= '				</div>'."\n";


                                $returnval .= '
<div class="amazon-buying">'."\n";
				//$returnval .= '					<h2 class="amazon-asin-title"><a href="' . $result["URL"] . '" '. $apippnewwindowhtml .'><span class="asin-title">'.$result["Title"].'</span></a></h2>'."\n";
				//$returnval .= '					<h2 class="amazon-asin-title"><a href="' . $result["URL"] . '" '. $apippnewwindowhtml .'><span class="asin-title">'.$result["Title"].'</span></a></h2>'."\n";
				#if(isset($result["Author"])){
				#$returnval .= '					<span class="amazon-author">'.$appip_text_author.' '.$result["Author"].'</span><br />'."\n";
				#}
				#if(isset($result["Director"])){
				#$returnval .= '					<span class="amazon-director-label">'.$appip_text_director.': </span><span class="amazon-director">'.$result["Director"].'</span><br />'."\n";
				#}
				#if(isset($result["Actors"])){
				#$returnval .= '					<span class="amazon-starring-label">'.$appip_text_starring.': </span><span class="amazon-starring">'.$result["Actors"].'</span><br />'."\n";
				#}

                                //if(isset($result["Rating"])){
				//$returnval .= '					<span class="amazon-rating-label">Rating: </span><span class="amazon-rating">'.$result["Rating"].'</span><br />'."\n";
				//}


				ob_start();
                                if(function_exists('the_ratings')) { $returnval .= the_ratings(); }
                                $out1 = ob_get_contents();
                                ob_end_clean();

                                //get the correct! rating
			        $post_id = $post->ID;
                                global $wpdb;
                                global $lhg_price_db;

                                if ($lang != "de") $ratings = $lhg_price_db->get_results("SELECT * FROM  `lhgtransverse_posts` WHERE postid_com = $post_id");
				if ($lang == "de") $ratings = $lhg_price_db->get_results("SELECT * FROM  `lhgtransverse_posts` WHERE postid_de = $post_id");

                                #$get_rates = $wpdb->get_results("SELECT rating_rating FROM $wpdb->ratings WHERE rating_postid = $post_id");


                                $rating_total=0;
                                $rating_total = $ratings[0]->post_ratings_users_com + $ratings[0]->post_ratings_users_de;
                                $rating = $ratings[0]->post_ratings_score_com + $ratings[0]->post_ratings_score_de;

                         	#foreach($get_rates as $get_rate){
                                #        $rating += $get_rate->rating_rating;
                                #        //print_r($get_rate);
			        #        $rating_total++;
			        #}

                                //$returnval .= '<div class="hreview-aggregate">';
                                //$returnval .= '<span class="item">';
                                //$returnval .= '<span class="fn"><span class="value-title" title="'.get_the_title().'"/></span>';
                                //$returnval .= '<span class="photo"><span class="value-title" title="'.wp_get_attachment_url( get_post_thumbnail_id($post->ID) ).'"/></span>';
                                //$returnval .= '</span>';

                                //$returnval .= '<span class="rating"><span class="value-title" title="'.($rating/$rating_total).'"/></span>';
                                //$returnval .= '<span class="reviewer"><span class="value-title" title="linux-hardware-guide.de"/></span>';
                                //$returnval .= '<span class="dtreviewed"><span class="value-title" title="'.get_the_date().'"/></span>';
                                //$returnval .= '<span class="votes"><span class="value-title" title="'.$rating_total.'"/></span>';
                                //$returnval .= '<span class="count"><span class="value-title" title="'.get_comments_number().'"/></span>';


				//$tooltip= 'Bewertet wird an dieser Stelle einzig die Linux-Kompatibilit&auml;t und nicht die Qualit&auml;t des Produktes.<div style="height: 3px;"></div>Sollten Sie dieses Produkt besitzen, dann helfen Sie bitte auch anderen Linux-Benutzern, indem Sie dessen
                                //	   <a href="#comment-box">Linux-Kompatibilit&auml;t bewerten</a>.';

                                //set tooltip depending on logged in or not (due to google rating)
                                global $txt_tooltip;
                                global $txt_amz_tooltip_loggedin;
                                global $txt_amz_tooltip_not_loggedin;

                                  if (is_user_logged_in() )  $txt_tooltip    = $txt_amz_tooltip_loggedin;
				  if (!is_user_logged_in() ) $txt_tooltip    = $txt_amz_tooltip_not_loggedin;

                                $tooltip= $txt_tooltip; //'Bewertet wird an dieser Stelle einzig die Linux-Kompatibilit&auml;t und nicht die Qualit&auml;t des Produktes. Sollten Sie dieses Produkt besitzen, dann helfen Sie bitte auch anderen Linux-Benutzern, indem Sie dessen Linux-Kompatibilit&auml;t im Kommentar-Bereich dieser Seite bewerten.';

				$returnval .= "
	<b>$txt_compat:</b>";
                                $returnval .= '
      <span class="tooltip">
        	'.tooltip($tooltip,-118,380,-178).'
      </span>';
                                $returnval .= '

      <br />

  <div style="border: 0px solid #eee;">

    <div style="border: 0px solid #eee; width:80px; float: left;">
        '.the_ratings_results($post->ID,0,0,0,10).'
    </div>
    ';

                                //Rich Snippet start
                                //$returnval .= '<div itemscope itemtype="http://data-vocabulary.org/Review">';


                                if ($rating_total == 1) $returnval .= '
    <div style="border: 0px solid #eee; white-space: nowrap;">
        &nbsp;&nbsp;(<span class="rating">
          	<span class="value-title" title="'.round(($rating/$rating_total),1).'">
                                '.round(($rating/$rating_total),1).'
                </span>
        </span> '.$txt_with.'

        <span class="votes">
        	<span class="value-title" title="'.$rating_total.'">
                                '.$rating_total.'
                </span>
        </span> '.$txt_rating.')
    </div>

  </div>'."\n";

                                if ($rating_total == 0) {
                                       $rate_average_value = 0;
                                }else{
                                       $rate_average_value = round(($rating/$rating_total),1);
                                }

                                if ($rating_total != 1) $returnval .= '
    <div style="border: 0px solid #eee; white-space: nowrap;">
  	&nbsp;&nbsp;(<span class="rating">
        	<span class="value-title" title="'.$rate_average_value.'">
                                '.$rate_average_value.'
                </span>
        </span> '.$txt_with.'

        <span class="votes">
        	<span class="value-title" title="'.$rating_total.'">
                                '.$rating_total.'
                </span>
        </span> '.$txt_ratings.')
    </div>
  </div>'."\n";

                                //Rich Snippet end
                                //$returnval .= '</div>';


                                $returnval .= '
  <br />
';


                                //also store cheapest price - not used. Use central price DB instead
                                #$cheapest_price = lhg_db_get_cheapest_price($post->ID);
                                #print "<br>Cheapest: $cheapest_price";
                                #$meta="cheapest_price_".$region;
                                #update_post_meta($post->ID,$meta,$cheapest_price);


#                                $returnval .= '<div class="amaz-grey-box" style="border: 0px solid #eee; background-color: #eee;">';
                                //if ($rating_total != 1) $returnval .= ' ('.$rating_total.' Bewertungen)</div></div><br>'."\n";

				//$returnval .= '				<hr noshade="noshade" size="1" />'."\n";
				//$returnval .= '				<div align="left">'."\n";


				//$returnval .= '					<table class="amazon-product-price" cellpadding="0">'."\n";
				//if($extratext!=''){
				//$returnval .= '						<tr>'."\n";
				//$returnval .= '							<td class="amazon-post-text" colspan="2">'.$extratext.'</td>'."\n";
				//$returnval .= '						</tr>'."\n";
				//}
				//If($result["PriceHidden"]==1 ){
				//	$returnval .= '						<tr>'."\n";
				//	$returnval .= '							<td class="amazon-list-price-label">'.$appip_text_listprice.':</td>'."\n";
				//	$returnval .= '							<td class="amazon-list-price-label">'.$amazonhiddenmsg.'</td>'."\n";
				//	$returnval .= '						</tr>'."\n"; 
				//}elseif($result["ListPrice"]!='0'){
				//	$returnval .= '						<tr>'."\n";
				//	$returnval .= '							<td class="amazon-list-price-label">Preis: '.$appip_text_listprice.':</td>'."\n";
				//$returnval .= '							Preis: '.$appip_text_listprice.':'."\n";
				//$returnval .= '							'.  mb_convert_encoding($result["ListPrice"], $encodemode, mb_detect_encoding( $result["ListPrice"], "auto" )) .' '."\n";
				//	$returnval .= '							<td class="amazon-list-price">'.  mb_convert_encoding($result["ListPrice"], $encodemode, mb_detect_encoding( $result["ListPrice"], "auto" )) .'</td>'."\n";
				//	$returnval .= '						</tr>'."\n";
				//}

                        	if( isset( $newPrice ) )  {

                                        #print "AAH!";
                                #$result["LowestNewPrice"])){
					#if($result["LowestNewPrice"]=='Too low to display'){
					#	$newPrice = 'Check Amazon For Pricing';
					#}else{
					#	#$newPrice = $result["LowestNewPrice"];
					#}
					//$returnval .= '						<tr>'."\n";
					//$returnval .= '							<td class="amazon-new-label">'.$appip_text_newfrom.':</td>'."\n";
#					$returnval .= '							'.$txt_price.':'."\n";
                                        #if ($result["TotalNew"] == '') $returnval.="Warn \n";
					#if($result["TotalNew"]>0){
                                                //$ship=scrape($asin);
                                                //print_r($ship);
                                                //$returnval .= "Total: ".$SalePrice['FormattedPrice']."-".$result["ListPrice"]."+".$ship[1]."<br>";


                                        #not needed any longer. Stored in DB
                                        //$newPrice
                                        $myFile = "/home/wordpressftp/amazon-$region.txt";
					$fh = fopen($myFile, 'a') or die("can't open file");
                                        $stringData = $post->ID.";".$newPrice."\n";
					fwrite($fh, $stringData);
					fwrite($fh, $stringData);
					fclose($fh);



                        # store price - currently disabled
			#                //$newPrice with date
                                        $myFile = "/home/wordpressftp/amazon_date-$region.txt";
                                        $timeInf=time();
					$fh = fopen($myFile, 'a') or die("can't open file");
                                        $stringData = $post->ID.";".$newPrice.";".$timeInf.";".$result["URL"]."\n";
					fwrite($fh, $stringData);
					fclose($fh);

                                        #lhg_amazon_price_to_db($region, $post->ID, $asin, $newPrice, $result["URL"], $timeInf);

                                        //remove Amazon string Symbol
                                        $newPrice  = lhg_remove_amazon_currency_symbol( $newPrice , $region);

                                        //add currency symbol
#                                        $returnval .= lhg_get_currency_symbol ( $region );


        /*
                                        if ($region == "co.uk") $returnval .= "&pound;";
                                        if ($region == "co.jp") $returnval .= "&yen;";
                                        if ($region == "cn") {
                                        	$returnval .= "&yen;";
                                                $newPrice  = substr($newPrice,2);
                                        }
                                        if ($region == "co.jp") $newPrice  = substr($newPrice,2);
        */
#                                        $returnval .= '							'. mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' <span itemprop="availability" content="in_stock" class="instock"> ('.$txt_on_stock.') </span>'."\n";
#                                        $returnval .= '<br><div class="shipping"><font size="1">('.$txt_shipping.')';

	                                global $txt_tooltip2;
        	                        $tooltip= $txt_tooltip2; //"Die von Amazon zur Verf&uuml;gung gestellten Preise sind exklusive m&ouml;glicherweise zus&auml;tzlich anfallender Versandkosten (abh&auml;ngig vom jeweiligen Anbieter des Amazon-Marketplace).";
#                	                $returnval .= '<span class="tooltip">'.tooltip($tooltip).'<span>';



#                                        $returnval .= '</font></div>';
#
#					}else{
#//						$returnval .= '							'. mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' <span class="outofstock">'.$appip_text_outofstock.'</span>'."\n";
#						$returnval .= 'BB unbekannt							'; //. mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' <span class="outofstock">'.$appip_text_outofstock.'</span>'."\n";
					#}
					//if($result["TotalNew"]>0){
					//	$returnval .= '							<td class="amazon-new">'. mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' <span class="instock">'.$appip_text_instock.'</span></td>'."\n";
					//}else{
					//	$returnval .= '							<td class="amazon-new">'. mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' <span class="outofstock">'.$appip_text_outofstock.'</span></td>'."\n";
					//}
					//$returnval .= '						</tr>'."\n";
//				$returnval .= '								</div>'."\n";

                                        //store in DB
                                       	$meta="price-amazon.".$region;
                                       	update_post_meta($post->ID,$meta,$newPrice);


                                        //$meta="url-amazon.".$region;
                                       	//update_post_meta($post->ID,$meta,$result["URL"]);



				}elseif($region != "pl"){


                                        #Price tag
                                      $aid=get_id();
				      //if( ($errors!='') && ($region == "en")){
                                      //   $returnval .='Not available at <a target="_blank" href="http://www.amazon.'.$region.'/b/?_encoding=UTF8&camp=1789&creative=390957&linkCode=ur2&node=2956536011&tag=linuhardguid-20">Amazon.'.$region.'</a><img src="https://www.assoc-amazon.com/e/ir?t=linuhardguid-20&l=ur2&o=1" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />';
                                      //}else{
                                        //$returnval .= '							'.$txt_price.':'."\n";
                                        $ProductIsUnavailable=1;

                                        if ($result["URL"] == ""){
                                    	  $meta="url-amazon.".$region;
	                              	  $result["URL"]=get_post_meta($post->ID,$meta,true);
	                              	}

                                	//still undefined?
                                	if ($result["URL"] == "") {
                                          $region_tmp = $region;
                                          if ($region == "nl") $region_tmp = "de";
                                    	  $result["URL"] = 'http://www.amazon.'.$region_tmp.'/?_encoding=UTF8&amp;camp=15121&amp;creative=390961&amp;linkCode=ur2&amp;tag='.$aid;
                                    	  $NoUrlAvailable=1;
		                        }

#                                        $returnval .= '							'. mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' <span class="outofstock">('.$txt_out_of_stock.')</span>'."\n";
#     					if ($region !== "de") if ($NoUrlAvailable != 1) $returnval .='<br><div class="amazon-cr"></div>'.$txt_not_avail_at.' <a target="_blank" href="http://www.amazon.'.$region.'/?_encoding=UTF8&camp=15121&creative=390961&linkCode=ur2&tag='.$aid.'" rel="nofollow">Amazon.'.$region.'</a><div class="amazon-cr"></div>';
#     					if ($region !== "de") if ($NoUrlAvailable == 1) $returnval .='<br><div class="amazon-cr"></div>'.$txt_never_avail.' <a target="_blank" href="http://www.amazon.'.$region.'/?_encoding=UTF8&camp=15121&creative=390961&linkCode=ur2&tag='.$aid.'" rel="nofollow">Amazon.'.$region.'</a><div class="amazon-cr"></div>';
#     					if ($region == "de") $returnval .='<br>Nicht lieferbar von <a target="_blank" href="http://www.amazon.'.$region.'/?_encoding=UTF8&camp=15121&creative=390961&linkCode=ur2&tag='.$aid.'" rel="nofollow">Amazon.'.$region.'</a>';
                                        //<img src="https://www.assoc-amazon.com/e/ir?t=linuhardguid-20&l=ur2&o=1" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />';
                                      // $returnval .= '							'. mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' <span class="instock"> (auf Lager) </span>'."\n";
                                      //}


                                        #Not needed any longer. Stored in DB
                                        $myFile = "/home/wordpressftp/amazon-$region.txt";
					$fh = fopen($myFile, 'a') or die("can't open file");
                                        $stringData = $post->ID.";".$txt_out_of_stock."\n";
					fwrite($fh, $stringData);
					fclose($fh);


                         # store price to file currently disabled
                         #
                         #              //print 'Res:'. $result["URL"];
                         #               //$newPrice with date
                                        $myFile = "/home/wordpressftp/amazon_date-$region.txt";
                                        $timeInf=time();
			        	$fh = fopen($myFile, 'a') or die("can't open file");
                                        $stringData = $post->ID.";".$newPrice.";".$timeInf.";".$result["URL"]."\n";
			        	fwrite($fh, $stringData);
			        	fclose($fh);

                                        //store in DB
                                       	$newPrice = "out of stock";
                                        if ($region === "de") $newPrice = "nicht lieferbar";
                                        $meta="price-amazon.".$region;
                                       	update_post_meta($post->ID,$meta,$newPrice);
                                        //$meta="url-amazon.".$region;
                                       	//update_post_meta($post->ID,$meta,$result["URL"]);
                                        #print "BBB<br>";
                                        #lhg_amazon_price_to_db($region, $post->ID, $asin, $newPrice, $result["URL"], $timeInf);



//                                $returnval.="Warn \n";
       				}
				//if(isset($result["LowestUsedPrice"])){
				//	$returnval .= '						<tr>'."\n";
				//	$returnval .= '							<td class="amazon-used-label">'.$appip_text_usedfrom.':</td>'."\n";
				//	if($result["TotalUsed"]>0){
				//		
				//		$returnval .= '						<td class="amazon-used">'. mb_convert_encoding($result["LowestUsedPrice"], $encodemode, mb_detect_encoding( $result["LowestUsedPrice"], "auto" )) .' <span class="instock">'.$appip_text_instock.'</span></td>'."\n";
				//	}else{
				//		$returnval .= '						<td class="amazon-new">'. mb_convert_encoding($result["LowestNewPrice"], $encodemode, mb_detect_encoding( $result["LowestUsedPrice"], "auto" )) . ' <span class="outofstock">'.$appip_text_outofstock.'</span></td>'."\n";
				//	}
				//	$returnval .= '						</tr>'."\n";
				//}
				//$returnval .= '						<tr>'."\n";
				//$returnval .= '							<td valign="top" colspan="2">'."\n";


                                //$returnval .= '								<div class="amazon-dates">'."\n";
				//if(isset($result["ReleaseDate"])){
				//	if(strtotime($result["ReleaseDate"]) > strtotime(date("Y-m-d",time()))){
				//$returnval .= '									<span class="amazon-preorder"><br />'.$appip_text_releasedon.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
				//	}else{
				//$returnval .= '									<span class="amazon-release-date">'.$appip_text_reldate.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
				//	}
				//}
                                

                                //update amazon price first (see above), now show the button with updated price
                                $returnval .= lhg_get_shop_button($post->ID);

       			      if($errors==''){

                                //product not available at amazon
                                if ($result["URL"] == ""){
                                    $meta="url-amazon.".$region;
                                    $result["URL"]=get_post_meta($post->ID,$meta,true);
                              	}

                                //still undefined?
                                if ($result["URL"] == "") {
                                    $result["URL"] = 'http://www.amazon.'.$region.'/?_encoding=UTF8&camp=15121&creative=390961&linkCode=ur2&tag='.$aid;
                                    $NoUrlAvailable=1;
	                        }
                                // href="http://www.amazon.'.$region.'/?_encoding=UTF8&camp=15121&creative=390961&linkCode=ur2&tag='.$aid.'">

                                //gif for german page
#                                if ($lang == "de") $returnval .= '<br><a href="' . $result["URL"] .'" rel="nofollow"><img src="'.$txt_button.'" border="0" '.$txt_button_width.'></a>';
#
#                                //dynamic button for everyone else
#        			if ($lang != "de") {
#
#                                        global $txt_button_string;
#
#                                        if ($ProductIsUnavailable == 1) if ($NoUrlAvailable == 1) {
##                                        	$txt_button_string = $txt_search_at.' Amazon.'.$region;
 #                                       	$returnval .= '<br><a href="'. $result["URL"] .'" class="css_btn_class" rel="nofollow"><i class="icon-search icon-large3"></i>&nbsp;'.$txt_button_string.'</a>';
 #                                       }
 #
 #                                       if ($ProductIsUnavailable == 1) if ($NoUrlAvailable != 1) {
 #                                       	$txt_button_string = $txt_preorder.' Amazon.'.$region;
#                                        	$returnval .= '<br><a href="'. $result["URL"] .'" class="css_btn_class" rel="nofollow"><i class="icon-shopping-cart icon-large3"></i>&nbsp;'.$txt_button_string.'</a>';
#                                        }
#
#                                        if ($ProductIsUnavailable != 1) {
#                                                $txt_button_string = $txt_buy_from.' Amazon.'.$region;
#                                                $returnval .= '<br><a href="'. $result["URL"] .'" class="css_btn_class" rel="nofollow"><i class="icon-shopping-cart icon-large3"></i>&nbsp;'.$txt_button_string.'</a>';
#	                        	}
#	                        }
                              }
                              if($extrabutton==1 && $aws_partner_locale!='.com'){
				//$returnval .= '									<br /><div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img src="'.WP_PLUGIN_URL.'/amazon-product-in-a-post-plugin/images/buyamzon-button.png" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;"/></a></div>'."\n";
				}
                                //close amzbox
                                $returnval .= '</div>';

#                                $returnval .= '</div></div>'."\n";
				//$returnval .= '							</td>'."\n";
				//$returnval .= '						</tr>'."\n";
				//If(!isset($result["LowestUsedPrice"]) && !isset($result["LowestNewPrice"]) && !isset($result["ListPrice"])){
				//	$returnval .= '						<tr>'."\n";
				//	$returnval .= '							<td class="amazon-price-save-label" colspan="2">'.$appip_text_notavalarea.'</td>'."\n";
				//	$returnval .= '						</tr>'."\n";
				//}
				//$returnval .= '					</table>'."\n";
//				$returnval .= '				</div>';
                                // <br style="clear:left;">'."\n";
				//$returnval .= '				</div>'."\n";
				//$returnval .= '			</td>'."\n";
				//$returnval .= '		</tr>'."\n";
				//$returnval .= '	</table>'."\n";


			        //$siteurl="linux-hardware-guide.com";

                		$catlist = category_links();

                                global $lhg_is_mobile_mode;

                                if ($lhg_is_mobile_mode != 1)
                                $returnval = $returnval.'<div class="amazon-cat-list">'.$catlist."</div>";

                                /*<table class=producttop border=0>
                                #<tr class="producttop">
                                #<td class="producttop" width=50%>'.
                                $returnval
                                .'</td>
                                <td class="producttop" width=50%>'. $catlist .' </td></tr></table>';
                                */

                                if ($lhg_is_mobile_mode == 1)
                                $returnval = '<div class="amazon_product_top">'.
                                $returnval
                                .'</div>
                                <div class="amazon-cat-list">'. $catlist .' </div>
                                <br style="clear:left;" /> ';



                		if (is_category()) {
        				// is category, not chart output
                                        // echo "<h2>Cat</h2>";
				}else{

       			      	if($errors==''){


                                   # creation of chart should be performed on separate machine
                                   # currently disabled
                                   #
				   #     //echo "Here";
				        start_pricechart($post->ID);

                                        //print "lang: $lang";


                                        // Pricetrend image alt text:
                                        $currency = lhg_get_currency_symbol( $region );


                                        $imgtext=$txt_pricetrend;
	                                if (file_exists("/var/www/charts/".$post->ID."-".$region.".dat")){
					        $myFile = "/var/www/charts/".$post->ID."-".$region.".dat";
     						$file = file($myFile);
					        list ($min, $max, $chartdate) = split(";",$file[0]);
                                                //echo "Min, Max: $min $max";
                                                $title= get_the_title();
                                                //echo "T: $title";
                                                $sp = strpos($title,"(");
                                                //echo "Pos: $sp";
                                                if ($sp != "") $title=substr($title,0,$sp);
                                                //echo "T: $title";
                                                $imgtext=$txt_pricetrend."\n".translate_title($title)."\n-\nMinimum: $currency $min \nMaximum: $currency $max  \n$txt_updated: $chartdate";
                                        }




                                        if ($lang == "de"){
                                        // en has no support for multiple affiliates a.t.m.

                                        	//if (file_exists("/var/www/charts/".$post->ID."m.png")){
                                        	if (false){
         						$returnval .= '<br style="clear:left;" /><img src="http://'.$siteurl.'/wp-uploads/charts/'.$post->ID.'m.png" alt="'.$imgtext.'" title="'.$imgtext.'" />';

						}else{
	                                        	if (file_exists("/var/www/charts/".$post->ID."-".$region.".png"))
   	        				          if ($charterror == 0){
       							    $returnval .= '<br style="clear:left;" /><img src="/wp-uploads/charts/'.$post->ID.'-'.$region.'.png" alt="'.$imgtext.'" title="'.$imgtext.'" />';
       	 				 	          }

                                                }
					}else{

	  				          if ($charterror == 0){
	                                        	if (file_exists("/var/www/charts/".$post->ID."-".$region.".png"))
       		 					    $returnval .= '<br style="clear:left;" /><img src="/wp-uploads/charts/'.$post->ID."-".$region.'.png" alt="'.$imgtext.'" title="'.$imgtext.'" />';
       	 			 	          }

		       	 		}



        	                        #list known prices
			                #if (file_exists("/var/www/charts/list".$post->ID.".txt")){
				        #$PriceList = file("/var/www/charts/list".$post->ID.".txt");
                                        #
	                                #$returnval .= '<ul>Preisvergleich
        	                        #               <li>Amazon.de: '.mb_convert_encoding($newPrice , $encodemode, mb_detect_encoding( $newPrice, "auto" )).' (<a href="' . $result["URL"] .'">bestellen</a>)';
                                        #
                        	        #$returnval .= $PriceList[0];
                                	#$returnval .="</ul>";
					#}
			        }
			        }


                                //List related categories
                                //$returnval .= category_links();

                                //show CPU table
                                #if (in_category('CPU')) $returnval .= cpu_comparison();

$googlecom='<script async src="http://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-5383503261197603"
     data-ad-slot="4611540473"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>';

$googlede='<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Linux-Hardware-Guide.de -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-5383503261197603"
     data-ad-slot="3000901678"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>';


$google_small_mobile='
<center>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Mobile Banner -->
<ins class="adsbygoogle"
     style="display:inline-block;width:320px;height:50px"
     data-ad-client="ca-pub-5383503261197603"
     data-ad-slot="9621646075"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</center>';
                                global $lhg_is_mobile_mode;
                                global $lhg_mobile_size;

                                if ($lhg_is_mobile_mode != 1)
                                if ($lang =="en")
                                if (!is_user_logged_in() ) $returnval .= $googlecom;


                                //
                                if ($rating_total == 0) {
                                       $rate_average_value = 0;
                                }else{
                                       $rate_average_value = round(($rating/$rating_total),1);
                                }


                                //only show in case of low rating
                                if ($lhg_is_mobile_mode != 1)
                                if ($lang =="de")
                                if ($rate_average_value < 5 )
                                if (!is_user_logged_in() ) $returnval .= $googlede;

                                if ($lhg_is_mobile_mode == 1)
				if (!is_user_logged_in() )
                                if ($lhg_mobile_size == "small") {
	                                $returnval .= $google_small_mobile;
                                } elseif ($lhg_mobile_size == "large") {
	                                $returnval .= $googlecom;
                                }else{
	                                $returnval .= $google_small_mobile;
                                }

                                //show article header
                                //itemprop description is closed in related-post-thumbnails
                                $returnval .= '<br /><h2>'.$txt_Compat.'</h2><div itemprop="description"><div class="description">';

                                $returnval .= affilinet();
                                //article_list();
 				return $returnval;



			}
		}
	}
	


function start_pricechart($post_id){

        global $lang;
        if (!isset($region)) $region = get_region();

	//check if file has recently been created
        //$myFile = "/var/www/charts/".$post_id."-".$region.".txt";
     	//$file = file($myFile);
        //echo "datum:".$file[0];

        //$dt = (time()-$file[0]);

      	//if ($dt < 2*60){
                //recently created, do nothing
      	//}else if ($dt < 12*60*60){
        //        //generated chart is new.
        //        //Will create update in background with risk of showing old image
        //        exec("/usr/bin/php /var/www/wordpress/wp-content/plugins/amazon-product-in-a-post-plugin/chart.php $post_id $region $lang >> /tmp/chartlog.txt 2>> /tmp/chartlog.txt &");

      	//}else{
                //generated chart is too old. Will create update in RT
        //        exec("/usr/bin/php /var/www/wordpress/wp-content/plugins/amazon-product-in-a-post-plugin/chart.php $post_id $region $lang >> /tmp/chartlog.txt 2>> /tmp/chartlog.txt");
        //}
        //}else{
                //generated chart is too old. Will create update in RT
        //          exec("nice -n 50 /usr/bin/php /var/www/wordpress/wp-content/plugins/amazon-product-in-a-post-plugin/chart.php $post_id $region $lang >> /tmp/chartlog.txt 2>> /tmp/chartlog.txt &");
        //}

        //echo "TEST";
        //always create update in the backgrond due to better page load time
        //	    exec("nice -n 30 /usr/bin/php /var/www/wordpress/wp-content/plugins/amazon-product-in-a-post-plugin/chart.php $post_id $region $lang >> /tmp/chartlog.txt 2>> /tmp/chartlog.txt &");

        return;
}




//Amazon Product Image from ASIN function - Returns HTML Image Code
function awsImageGrabber($imgurl, $class=""){

            #takes too long. Should be prevented
            return;

		global $asin;
	    $base_url0 = '<'.'img itemprop="photo" alt="'.translate_title(get_the_title($post->ID)).'" title="'.translate_title(get_the_title($post->ID)).'" src="';
	    $base_url = $imgurl;
	    $base_url1 = '"';
	    $base_url1 = $base_url1.' class="amazon-image '.$class.'"';
	    $base_url1 = $base_url1.' />';
		
		if($base_url!=''){
	    	return $base_url0.$base_url.$base_url1;
		}else{
			//$base_url = WP_PLUGIN_URL .'/amazon-product-in-a-post-plugin/images/noimage.jpg';

			//$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
			//$base_url = wp_get_attachment_image_src( $post_thumbnail_id, $poststhname );
			$base_url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
                        //echo "URL=".$base_url;
                        //$base_url = WP_PLUGIN_URL .'/amazon-product-in-a-post-plugin/images/noimage.jpg';
	    	return $base_url0.$base_url.$base_url1;
		}
	}
	
	//Amazon Product Image from ASIN function - Returns URL only
	function awsImageGrabberURL($asin, $size="M"){

            #takes too long. Should be prevented
            return;

	    $base_url = 'http://images.amazon.com/images/P/'.$asin.'.03.';
	    if (strcasecmp($size, 'S') == 0){
	      $base_url .= 'THUMBZZZ';
	    }
	    else if (strcasecmp($size, 'L') == 0){
	      $base_url .= 'LZZZZZZZ';
	    }
	    else{
	      $base_url .= 'MZZZZZZZ';
	    }
	    $base_url .= '.jpg';
	    return $base_url;
	}
	
	  function aws_prodinpost_filter_excerpt($text){

            #takes too long. Should be prevented
            return;

	  	global $post,$apipphookexcerpt;
	  	$ActiveProdPostAWS = get_post_meta($post->ID,'amazon-product-isactive',true);
	  	$singleProdPostAWS = get_post_meta($post->ID,'amazon-product-single-asin',true);
	  	$AWSPostLoc = get_post_meta($post->ID,'amazon-product-content-location',true);
	  	$apippExcerptHookOverride = get_post_meta($post->ID,'amazon-product-excerpt-hook-override',true);
	  	$apippShowSingularonly = '0';
	  	if(get_option('appip_show_single_only')=='1'){$apippShowSingularonly = '1';}
	  	$apippShowSingularonly2 = get_post_meta($post->ID,'amazon-product-singular-only',true);
		if($apippShowSingularonly2=='1'){$apippShowSingularonly = '1';}
		
		if(($apipphookexcerpt==true && $apippExcerptHookOverride!='3')){ //if options say to show it, show it
			//replace short tag here. Handle a bit different than content so they get stripped if they don't want to hook excerpt 
			//we don't want to show the [AMAZON-PRODUCT=XXXXXXXX] tag in the excerpt text!
		 	if ( stristr( $text, '[AMAZONPRODUCT' )) {
				$search = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i"; 
				if	(preg_match_all($search, $text, $matches)) {
					if (is_array($matches)) {
						foreach ($matches[1] as $key =>$v0) {
							$search = $matches[0][$key];
							$ASINis	= $matches[1][$key];
							if($apippShowSingularonly=='1' && !is_singular()){
								$text	= str_replace ($search, '', $text);
                                                                $text .= "test2";
							}else{
								$text	= str_replace ($search, getSingleAmazonProduct($ASINis,''), $text);
                                                                $text .= "test";
							}
						}
					}
				}
		  	}		
			if($apippShowSingularonly=='1'){
                        //echo "Hier";
			  	if(is_singular()&& ($singleProdPostAWS!='' && $ActiveProdPostAWS!='')){
			  		if($AWSPostLoc=='2'){
			  			//Post Content is the description
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text);
			  		}elseif($AWSPostLoc=='3'){
			  			//Post Content before product
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
			  		}else{
			  			//Post Content after product - default
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').'<br />'.$text;
			  		}
			  		return $theproduct;
			  	} else {
			  		return $text;
			  	}
			}else{
                        //Alle Produkte, lieferbar + nicht lieferbar
                        //echo "Hier2";
			  	if($singleProdPostAWS!='' && $ActiveProdPostAWS!=''){
                                // echo "
                                //
                                //                                                      Hier2.1";
                                if($AWSPostLoc=='2'){
			  			//Post Content is the description
			  			$theproduct = "H5.4".getSingleAmazonProduct($singleProdPostAWS,$text);
			  		}elseif($AWSPostLoc=='3'){
			  			//Post Content before product
			  			$theproduct = "H5.5".$text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
			  		}else{
			  			//Post Content after product - default
			  			//$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').'<br />'.$text;
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').$text; //<-- rueckgaengig!!
			  		}
			  		return $theproduct;
			  	} else {
                                //echo "Hier2.2";
			  		return $text;
			  	}
			}
		}else{
		   if ( stristr( $text, '[AMAZONPRODUCT' )) {
				$search = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i"; 
				if	(preg_match_all($search, $text, $matches)) {
					if (is_array($matches)) {
						foreach ($matches[1] as $key =>$v0) {
							$search = $matches[0][$key];
							$ASINis	= $matches[1][$key];
							$text	= str_replace ($search, '', $text); //take the darn thing out!
						}
					}
				}
		    }		
		   //$text .="Here";
		}
		return $text;
	  }


  function scrape($asin)
  {
    global $webserver,$oneq,$twoq,$br;
    $scraped=NULL;
    $scraped['image']=NULL;  // default
  
    //     fetch:
    $call='http://www.amazon.de/gp/offer-listing/'.$asin.'?condition=new';
    $html=file_get_contents($call);
    //$html=$call;
    //echo $call;
    //echo $html;

    //     pull image:
    //$start=strpos($html,'http://ecx.images-amazon.com/');
    //$cut=strpos($html,$twoq,$start);
    //$image=substr($html,$start,$cut-$start);
    //$image=str_replace('110','160',$image);
    //$scraped['image']=$image;
       
    //     pull shipping:
    $needle='<tbody class="result">';
    //echo('Raw Count: '.substr_count($html,$needle).$br);
    $start=strpos($html,$needle);
    if ($start!==FALSE)
    {
      $html=substr($html,$start+22);  // pass over first marker
      $html=explode($needle,$html);
      //echo('HTML offers: '.count($html).$br);
      //     extract:
      foreach ($html as $vendor)
      {
        $start=strpos($vendor,'registerImage("original_image",');
        //if ($start!==FALSE)
        //{
        //  $start=strpos($vendor,'http://ecx.images-amazon.com/');
        //  $cut=strpos($vendor,$twoq,$start);
        //  $image=substr($vendor,$start,$cut-$start);
        //  $scraped['image']=$image;
        //}
        $start=strpos($vendor,'"price_shipping">');
        if ($start===FALSE)
        {
          $shipping='0.00';
         } else {
          $start=$start+20;
          $cut=strpos($vendor,'<',$start);
          $shipping=substr($vendor,$start,$cut-$start);
        }
        $start=7+strpos($vendor,'seller=');
        //echo "start: $start";
        $cut=strpos($vendor," ",$start);
        $sellerid=substr($vendor,$start,$cut-$start);

        //
        //echo "Seller: $sellerid - ";
        //echo "Shipping: $shipping<br>";

        //echo "end: ".($cut-$start);
        $scraped[$sellerid]=$shipping;
      }
    }
    return $scraped;
  }


  function aws_prodinpost_filter_content($text){

                //echo "Hier3";
	  	global $post,$apipphookcontent;
	  	$ActiveProdPostAWS = get_post_meta($post->ID,'amazon-product-isactive',true);
	  	$singleProdPostAWS = get_post_meta($post->ID,'amazon-product-single-asin',true);
	  	$AWSPostLoc = get_post_meta($post->ID,'amazon-product-content-location',true);
	  	$apippContentHookOverride = get_post_meta($post->ID,'amazon-product-content-hook-override',true);
	  	$apippShowSingularonly = get_post_meta($post->ID,'amazon-product-singular-only',true);

                // If no AWS ID is available replace by empty one
                // Otherwise, rating etc. are not shown and page design is broken
                if ($singleProdPostAWS == "") $singleProdPostAWS = "0000000x";


		//replace short tag here
		   if ( stristr( $text, '[AMAZONPRODUCT' )) {
				//$search = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(\w+|^\+|,)\]\s*(?:</p>)*@i"; //need to change to allow commas in regex
				$search = "@(?:<p>)*\s*\[AMAZONPRODUCT\s*=\s*(.+|^\+)\]\s*(?:</p>)*@i"; 
				if	(preg_match_all($search, $text, $matches)) {
					if (is_array($matches)) {
						foreach ($matches[1] as $key =>$v0) {
							$search = $matches[0][$key];
							$ASINis	= $matches[1][$key];
							if($apippShowSingularonly=='1' && !is_singular()){
								$text	= str_replace ($search, '', $text);
							}else{
								if(strpos($ASINis,',')){
									$product_text = '';
									//clean the spaces out if any
									$ASINis = str_replace(' ','',$ASINis);
									$ASINisArray = explode(',',$ASINis);
									//loop through them
									foreach($ASINisArray as $ASINmt){
										$product_text	.= getSingleAmazonProduct($ASINmt,'');
										$product_text	.= '<div class="appip-multi-divider"><!--appip divider--></div>';
									}
									//replace the original shortcode with new multi products
									$text	= str_replace ($search, $product_text, $text);
								}else{
									$text	= str_replace ($search, getSingleAmazonProduct($ASINis,''), $text);
								}
							}
						}
					}
				}
		    }
			if($apippShowSingularonly=='1'){
			    if(is_singular() && (($apipphookcontent==true && $apippContentHookOverride!='3') || $apippContentHookOverride=='' || $apipphookcontent=='')){ //if options say to show it, show it
				  	if($singleProdPostAWS!='' && $ActiveProdPostAWS!=''){
				  		if($AWSPostLoc=='2'){
				  			//Post Content is the description
				  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text);
				  		}elseif($AWSPostLoc=='3'){
				  			//Post Content before product
				  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
				  		}else{
				  			//Post Content after product - default
				  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').'<br />'.$text;
				  		}
				  		return $theproduct;
				  	} else {
				  		return $text;
				  	}
				 }
			}else{
                        //echo "Hier4";
                        //$counter++;
                        //echo "-".$counter."-";
			    if(($apipphookcontent==true && $apippContentHookOverride!='3') || $apippContentHookOverride=='' || $apipphookcontent==''){ //if options say to show it, show it
				  	if($singleProdPostAWS!='' && $ActiveProdPostAWS!=''){
				  		if($AWSPostLoc=='2'){
				  			//Post Content is the description
				  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text);
				  		}elseif($AWSPostLoc=='3'){
				  			//Post Content before product
				  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'');
				  		}else{
				  			//Post Content after product - default
				  			//$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').'Test<br />'.$text;
				  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'').$text;
				  		}
				  		return $theproduct;
				  	} else {
				  		return $text;
				  	}
				 }
			}
		 return $text;
	  }
	function aws_prodinpost_addadminhead(){
	  echo '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/lhg-product-post/css/amazon-product-in-a-post-styles-icons.css" type="text/css" media="screen" />'."\n";
	}
	function aws_prodinpost_addhead(){
		global $aws_plugin_version;
		$amazonStylesToUseMine = get_option("apipp_product_styles_mine"); //is box checked?
		echo '<'.'!-- Amazon Product In a Post Plugin Styles & Scripts - Version '.$aws_plugin_version.' -->'."\n";
		if($amazonStylesToUseMine=='true'){ //use there styles
			echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?apipp_style=custom" type="text/css" media="screen" />'."\n";
		}else{ //use default styles
		       // echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?apipp_style=default" type="text/css" media="screen" />'."\n";
		}
		echo '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/lhg-product-post/css/amazon-lightbox.css" type="text/css" media="screen" />'."\n";
		echo '<'.'!-- End Amazon Product In a Post Plugin Styles & Scripts-->'."\n";
	}
	function add_appip_jquery(){
		wp_register_script('appip-amazonlightbox', WP_PLUGIN_URL . '/lhg-product-post/js/amazon-lightbox.js');
		wp_enqueue_script('jquery'); 
		wp_enqueue_script('appip-amazonlightbox'); 
	}

	function category_links(){

                global $txt_category;
                global $txt_arch_home;


                $option ='<b>'.$txt_category.':</b><br />';
                //<h3>Verwandte Kategorien</h3>';

                //get urls from 'en'
                if ( ($lang != "de") )
                $us_links = get_category_links_us();

                $counter=0;
                $i = 0;
		foreach((get_the_category()) as $category) {
                        $counter++;
                        $CID=$category->cat_ID;
                        //$option .= "CID: $CID";

			$childlist=get_category_children($CID);

                //print "CL: $childlist<br>";

                if (($childlist !== "") or ($counter == 111) ){

        	/*if (category_has_children()) {
    			$option .="A"; //DISPLAY SOMETHING
		} else {
    			$option .="B"; //DISPLAY SOMETHING DIFFERENT
		}
                $CIDold=$CID;
                */
                        //$option .= "URL: ".get_category_link($category->term_id );

                        //$option .= '<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
                        //$txt_arch_home = ''; //<i class="icon-home icon-category"></i>';
                        $option .= '<a href="/" class="icon-home icon-category" title="'.$txt_arch_home.'"></a> &gt; <a href="'.get_category_link($category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category->name ) ) . '" itemprop="url">'.$category->cat_name.' ('.$category->category_count.')</a>'.$seperator;


                        //$clist = get_the_category($CID);
                        //$option .= "$CID";
                        //$option .= $clist[0]->name;

                        //$category_id = get_cat_ID( $cat->cat_name );
			//$option .= "(In: ".in_category($category_id).")";

                        //$option .= "</div>";
//		        $option .= "<br>";



			$categories        = get_categories("child_of=$CID"); //.$this_category->cat_ID);
			$categories_parent = get_categories("parent=1&child_of=$CID"); //.$this_category->cat_ID);
			//$categories = wp_list_categories('child_of='.$this_category->cat_ID);

			//echo "<br>Lang: ".$q_config['language'];
			$categories_en        = get_categories("child_of=$CID"); //.$this_category->cat_ID);
			$categories_parent_en = get_categories("parent=1&child_of=$CID"); //.$this_category->cat_ID);

                        //$option .= $txt_category."<br>";

                        //get url from 'en'

                        //echo "Lang: orig<br>";
                 	foreach ($categories as $cat) {

				//remove_filter('get_category','qtrans_useTermLib',0);

                                $category_id    = get_cat_ID( $cat->cat_name ,false);

                                //$tcatname = qtrans_use('en', __($cat->cat_name) ,false);

                                /*
                                echo "<br>TID: ",$category->term_id;
                                echo "<br>Link: ".get_category_link($category->term_id );
                                echo "<br>CID: ".$category_id;
                                echo "<br>Link2:".get_category_link($category_id);
                                echo "<br>Name: ".$cat->cat_name;
                                echo "<br>TransName: ".$tcatname;
                                echo "<br>TransCID: ".qtrans_use('us',$category_id,true);
                                echo "<br>Lang: ".qtrans_getLanguage();
                                */

				//$locale = $new_locale;
                                //echo "<br>Lang: ".$q_config['language'];
				//$q_config['language'] = 'xy'; //substr('en_EN', 0, 2);

                                //global $txt_arch_home;
                                //$option.= "Test: $txt_arch_home";

                                $option .= "<br />";
                                if (in_category($category_id) == true) $option .= '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
                                $option .= '<a href="/" class="icon-home icon-category" title="'.$txt_arch_home.'"></a> &gt; ';
                                $option .= '<a href="'.get_category_link($category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category->name ) ) . '" itemprop="url">';
                                if (in_category($category_id) == true) $option .= '<span itemprop="title">';
                                $option .= $category->cat_name;
                                if (in_category($category_id) == true) $option .= '</span>';
                                $option .= '</a>';//.$seperator;
	                        //$option .= "</div>";

		                $option .= ' &gt; ';
                                if (in_category($category_id) == true) $option .= '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';

                                //
                                if ($lang != 'de'){
                                $option .= '<a href="'.$us_links[$i].'" itemprop="url">';
                                //get_category_link($category_id ).
                                }else{
                                $option .= '<a href="'.get_category_link($category_id ).'" itemprop="url">';
                                //.
                                }

                                if (in_category($category_id) == true) $option .= '<span itemprop="title">';
                                $option .= $cat->cat_name;
                                if (in_category($category_id) == true) $option .= '</span>';
                                $option .= ' ('.$cat->category_count.')</a>';
	                        if (in_category($category_id) == true) $option .= "</span>";
	                        if (in_category($category_id) == true) $option .= "</span>";

				//echo $option;
                       	 	//this_category;
                                $i++;
			}
//                        $option .= "</div>";
                }else{
                        $parent  = get_category($CID);
                        $PID = $parent->category_parent;

                        //only list if this is a main category, and hence was not already listed as a child
	                if ( $PID == 0 ){
        	                $option .= '<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
				$option .= '     <a href="/" class="icon-home icon-category" title="'.$txt_arch_home.'"></a> &gt; <a href="'.get_category_link($category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category->name ) ) . '" itemprop="url">
                                                 <span itemprop="title">'.$category->cat_name.'</span>
                                                 ('.$category->category_count.')</a>'.$seperator;
                	        $option .= "</div>";
	                }
                }

                }

        //$option .= "</div>";

        return $option;

	}

function get_category_links_us(){
        //echo "Testt";
	global $q_config;

        $oldlang = $q_config['language'];
	$q_config['language'] = 'us'; //substr('en_EN', 0, 2);

        //print_r(get_the_category());

        $counter = 0;
        $i       = 0;

        foreach(get_the_category() as $category) {
        	$counter++;
                $CID=$category->cat_ID;
                //$option .= "CID: $CID";
       		$childlist=get_category_children($CID);
                //print "CL: $childlist<br>";

                if (($childlist !== "") or ($counter == 111) ){
			$categories        = get_categories("child_of=$CID"); //.$this_category->cat_ID);
			$categories_parent = get_categories("parent=1&child_of=$CID"); //.$this_category->cat_ID);
			//$categories = wp_list_categories('child_of='.$this_category->cat_ID);

                        foreach ($categories as $cat) {

				//remove_filter('get_category','qtrans_useTermLib',0);

                                //use translated category names
                                global $lang;
                                if ( $lang == "de" ) {
                                	$category_id    = get_cat_ID( $cat->cat_name ) ;
                                	$cat_link       = get_category_link( $category->term_id );
                                $cat_link2      = get_category_link( $category_id );

				}else{

                                	$category_id    = qtrans_use('us',get_cat_ID( $cat->cat_name) );
                                	$cat_link       = qtrans_use('us',get_category_link($category->term_id ) );
                                	$cat_link2      = qtrans_convertURL(qtrans_use($oldlang,get_category_link($category_id) ));

                                	$q_config['language'] = $oldlang; //substr('en_EN', 0, 2);
                        		$cat_link2      = qtrans_convertURL($cat_link2);
					$q_config['language'] = 'us'; //substr('en_EN', 0, 2);
                                }

                                //$category_id    = get_cat_ID( $cat->cat_name);


                                //echo "<br>CID: ".$category_id;
                                //echo "<br>Link: ".$cat_link;
                                //echo "<br>Link2: ".$cat_link2;

                                //$tcatname = qtrans_use('en', __($cat->cat_name) ,false);

                                /*
                                echo "<br>TID: ",$category->term_id;
                                echo "<br>Link: ".get_category_link($category->term_id );
                                echo "<br>Link2:".get_category_link($category_id);
                                echo "<br>Name: ".qtrans_use('us',$cat->cat_name);
                                echo "<br>TransName: ".$tcatname;
                                echo "<br>TransCID: ".qtrans_use('us',$category_id,true);
                                echo "<br>Lang: ".qtrans_getLanguage();
                                */

				//$locale = $new_locale;
                                //echo "<br>Lang: ".$q_config['language'];
				//$q_config['language'] = 'xy'; //substr('en_EN', 0, 2);

                                //$option .= "<br>";
                                //if (in_category($category_id) == true) $option .= '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
                                //$option .= '<a href="/" class="icon-home icon-category" title="'.$txt_arch_home.'"></a> &gt; ';
                                //$option .= '<a href="'.get_category_link($category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category->name ) ) . '" itemprop="url">';
                                //if (in_category($category_id) == true) $option .= '<span itemprop="title">';
                                //7$option .= $category->cat_name;
                                //if (in_category($category_id) == true) $option .= '</span>';
                                //$option .= '</a>';//.$seperator;
	                        //$option .= "</div>";

		                /*
                                $option .= ' &gt; ';
                                if (in_category($category_id) == true) $option .= '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
                                $option .= '<a href="'.get_category_link($category_id ).'" itemprop="url">';
                                if (in_category($category_id) == true) $option .= '<span itemprop="title">';
                                $option .= $cat->cat_name;
                                if (in_category($category_id) == true) $option .= '</span>';
                                $option .= ' ('.$cat->category_count.')</a>';
	                        if (in_category($category_id) == true) $option .= "</span>";
	                        if (in_category($category_id) == true) $option .= "</span>";
                                */

                                //print "I: $i, url:".$cat_link2."<br>";
                                $links[$i] = $cat_link2;
                                $i++;
			}
                }
        }

	$q_config['language'] = $oldlang; //substr('en_EN', 0, 2);
        //echo "Links:<br>";
        //print_r($links);
        return $links;

}



function article_list(){

        print "Creating article list...";

	 $args = array(
	'posts_per_page'  => 1000,
	'numberposts'     => 1000,
	'offset'          => 0,
	'category'        => '',
	'orderby'         => 'post_date',
	'order'           => 'DESC',
	'include'         => '',
	'exclude'         => '',
	'meta_key'        => '',
	'meta_value'      => '',
	'post_type'       => 'post',
	'post_mime_type'  => '',
	'post_parent'     => '',
	'post_status'     => 'publish',
	'suppress_filters' => true ,
        'fields'	   => 'ids');

        $posts_array = get_posts( $args );

        print "<pre>";
        print_r($posts_array);
        print "</pre>";
        print "done<br />";
}

	
	function cpu_comparison(){
        //skip!
        return;

        foreach((get_the_category()) as $category) { 
    		$cid=$category->cat_ID;
	}

        //$out .= "CID: $cid";

	$argscat=array( 'numberposts' => 100, 'category' => $cid );
	$posts_array = get_posts( $argscat );
        //print_r($posts_array);

        //search for CPU articles
	$i=0;
	foreach( $posts_array as $post ) :
        	//setup_postdata($post);
		$postid[$i]=$post->ID;
                //$out .= "PID: $postid[$i]<br>";
                $i++;
	endforeach;
        $imax=$i-1;

        //$postid[0]="2765";
        //$postid[2]="2770";
        //$postid[1]="2779";

        $bogomipsmax=0;

        for ($i=0; $i<$imax+1; $i++){
	  	$bogomips[$i]  = get_post_meta($postid[$i],'CPU_bogomips',true);
	  	$siblings[$i]  = get_post_meta($postid[$i],'CPU_siblings',true);
	  	$vendor[$i]    = get_post_meta($postid[$i],'CPU_vendor',true);
	  	$name[$i]      = get_post_meta($postid[$i],'CPU_name',true);
	  	$socket[$i]    = get_post_meta($postid[$i],'CPU_socket',true);
                $preis_str[$i] = get_post_meta($postid[$i],'CPU_socket',true);
                //$preis_str[$i] = amaz_price($postid[$i]);
                //$preis[$i]     = (float)str_replace( ",",".", substr(amaz_price($postid[$i]),3)  );
                $preis[$i]     = get_post_meta($postid[$i],'price-amazon.de',true);
                $preis_str[$i] = $preis[$i];

                $preis[$i]     = str_replace(",",".", substr($preis[$i],3) );

                //$out .= "Val: ".str_replace( ",",".", substr( amaz_price($postid[$i]),3) )."<br>";
                if (($bogomips[$i]*$siblings[$i])>$bogomipsmax) $bogomipsmax=$bogomips[$i]*$siblings[$i];

        //$out .="ID: ".$postid[$i]."<br>
        //Bogomips: ".$bogomips[$i]."<br><br>";

	}

        $preissort=$preis;

        array_multisort($preissort, SORT_DESC, $preis_str);
        $preissort=$preis;
        array_multisort($preissort, SORT_DESC, $bogomips);
        $preissort=$preis;
        array_multisort($preissort, SORT_DESC, $siblings);
        $preissort=$preis;
        array_multisort($preissort, SORT_DESC, $vendor);
        $preissort=$preis;
        array_multisort($preissort, SORT_DESC, $name);
        $preissort=$preis;
        array_multisort($preissort, SORT_DESC, $socket);

        array_multisort($preis, SORT_DESC, $postid);
//        asort($preis);


$out .='<h2>CPU-Vergleich</h2><table class="cpulist"><tr class="cpulistheader">';
$out .="<td >CPU Name</td> <td>Hersteller</td> <td>Sockel</td> <td>Siblings x Bogomips</td>
                <td>CPU-Geschwindigkeit vs. Preis</td> <td>Preis</td>
        </tr>";

        $preismax=0;
        for ($i=0; $i<$imax+1; $i++){
		if (get_the_ID() == $postid[$i] ){

                        $preisrefpx=200*($bogomips[$i]*$siblings[$i]/$bogomipsmax);
                        $preisref=$preis[$i];

		}
                	if ($preis[$i]>$preismax) { $preismax=$preis[$i]; }
	}

        //price too large?
        $scaler1=1;
        $scaler2=1;

        if ( ($preisrefpx * $preismax/$preisref) > 200) $scaler2= 200/($preisrefpx * $preismax/$preisref);
        $scaler1=$scaler2;

        if ($preisref==0) {
	        $preisref=$preisrefpx;
                $scaler1=1;
                $scaler2= 200/$preismax;
	}

        //$out .= "preismax: $preismax, Scaler= $scaler, maxpx =".($preisrefpx * $preismax/$preisref)."<br>";

        for ($i=0; $i<$imax+1; $i++){

		if (get_the_ID() == $postid[$i] ) $out .= '<tr bgcolor="#eeeeee">';
		if (get_the_ID() != $postid[$i] ) $out .= "<tr> ";

                $out .= "<td><a href=".get_permalink( $postid[$i] ).">$name[$i]</a></td> <td>$vendor[$i]</td> <td>$socket[$i]</td>
                <td>$siblings[$i] x $bogomips[$i]</td><td>";

                //bogomips graph
        	$out .= '<div class="horizontalgraph" style= "height: 45px;">
                <ul class="bars">';
		$wdth=(int)($scaler1*200*($bogomips[$i]*$siblings[$i]/$bogomipsmax));
        	if ($wdth>100)  $out .= '<li class="bar1 blue" style="width: '.(int)($scaler1*200*($bogomips[$i]*$siblings[$i]/$bogomipsmax)).'px;">'.str_replace(".",",",$bogomips[$i]).'&nbsp;x&nbsp;'.$siblings[$i]  .'</li>';
        	if ($wdth<=100) $out .= '<li class="bar1 blue" style="width: '.(int)($scaler1*200*($bogomips[$i]*$siblings[$i]/$bogomipsmax)).'px;"><span class="outside">'.str_replace(".",",",$bogomips[$i]).'&nbsp;x&nbsp;'.$siblings[$i].'</span></li>';

                $wdth=(int)($scaler2* $preisrefpx * $preis[$i]/$preisref);

        	if ($wdth!=0) {
                        if ($wdth>120)  $out .= '<li class="bar2 pink" style="width: '.(int)($scaler2* $preisrefpx * $preis[$i]/$preisref).'px;">'.str_replace(".",",",$preis[$i]).' EUR</li>';
                	if ($wdth<=120) $out .= '<li class="bar2 pink" style="width: '.(int)($scaler2* $preisrefpx * $preis[$i]/$preisref).'px;"><span class="outside">'.str_replace(".",",",number_format($preis[$i],2)).'&nbsp;EUR</span></li>';
		}

                $out .= "</ul>";


                $out .='</td> <td><a href="'.get_post_meta($postid[$i],'url-amazon.de',true).'">'.$preis_str[$i].'<br />(Amazon.de)</a></td></tr>';
	}
$out .="</table>";



/*

        $out .='
        <div class="horizontalgraph" style= "height: 90px;">
<ul class="bars">';

        for ($i=0; $i<$imax+1; $i++){
        	$out .= '<li class="bar'.($i+1).' blue" style="width: '.(int)(100*($bogomips[$i]/$bogomipsmax)).'px;">'.$bogomips[$i].'</li>';
	}
//<li class="bar5 blue" style="width: 32px;"><span class="outside">17</span></li>
        $out .= '</ul>

	<ul class="ylabel">';

        for ($i=0; $i<$imax+1; $i++){
        $out .= "<li>".$vendor[$i]."</li>";
	}
	$out .= '</ul>
		<p>Gross National Products of Countries in Dollars
		(GNP Per Capita units times 1,000)</p>
		</div>
        	';
*/

        return $out;
	}


function affilinet() {
//echo "Affilinet Output:<br>";


        $PostID=get_the_ID();
        $ProductID_TMP  = get_post_meta($PostID,'PID_affilinet',true);
        $ProductID = split(",",$ProductID_TMP);

        //$ProductID=106020024;
        if ($ProductID == "") return;

        $region=get_region();

        $path="/var/www/wordpress/wp-content/plugins/lhg-product-post";

	if (is_array($ProductID)) {
        	//echo "Is array!";
        	for ($i=0; $i<sizeof($ProductID); $i++){
                        //echo "I: $i";
			exec("/usr/bin/php $path/affilinet.php \"".(int)$ProductID[$i]."\" $PostID $region >> /tmp/affilinet.log 2>> /tmp/affilinet.log &");
                }
        }else{

		exec("/usr/bin/php $path/affilinet.php \"".(int)$ProductID."\" $PostID $region >> /tmp/affilinet.log 2>> /tmp/affilinet.log &");
        }

return;
}


function tooltip($content,$top = -130, $width = 240, $right = -107) {

  global $lhg_is_mobile_mode;

  //no tooltips in mobile mode
  if ($lhg_is_mobile_mode == 1) return;


  if (!is_user_logged_in()) {
     $out = '<img class="trigger" src="/wp-content/themes/blue-and-grey/images/info-icon.png" id="download" title="'.$content.'" alt="'.$content.'"/>';

  }else{
     //use better tooltip, which google hates
     $out = '
          <span class="bubbleInfo">
            <span>
              <img class="trigger" src="/wp-content/themes/blue-and-grey/images/info-icon.png" id="download" />
            </span>

            <table id="dpop" class="popup" style="top: '.$top.'px; width: '.$width.'px; right: '.$right.'px;" >
        	<tbody><tr><td id="topleft" class="corner"></td><td class="top"></td><td id="topright" class="corner"></td></tr><tr>
        		<td class="left"></td>
        		<td class="contentbox">
                        <table class="popup-contents">
        				<tr><td>'.
                                        $content.
                        '</td></tr>
			</table>
        		</td>
        		<td class="right"></td>    
        	</tr>
        	<tr>
        		<td class="corner" id="bottomleft"></td>
        		<td class="bottom"><img width="30" height="29" alt="popup tail" src="/wp-content/themes/blue-and-grey/images/bubble-tail2.png"/></td>
        		<td id="bottomright" class="corner"></td></tr></tbody></table>
          </span>
          ';
  }

  return $out;
}

function lhg_aws_get_price($awsid,$region) {

        #get  price from LHG-PriceDB via URL
        #      http://192.168.3.114/lhgupdatedb.php?mode=getprice&aid=B000EHIA06
        #returns URL, Image, Price

	if ( ($_SERVER['SERVER_ADDR'] == "192.168.56.12") or ($_SERVER['SERVER_ADDR'] == "192.168.56.13") )
	$lhg_price_db_ip = "192.168.56.14";

	if ( ($_SERVER['SERVER_ADDR'] == "192.168.3.112") or ($_SERVER['SERVER_ADDR'] == "192.168.3.113") )
	$lhg_price_db_ip = "192.168.3.114";


        $price = file_get_contents('http://'.$lhg_price_db_ip.'/lhgupdatedb.php?mode=getprice&aid='.$awsid.'&region='.$region);
        #print ('<pre>http://'.$lhg_price_db_ip.'/lhgupdatedb.php?mode=getprice&aid='.$awsid.'&region='.$region."</pre>");

        return $price;
}

?>