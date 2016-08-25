<?php

/*
Plugin Name: LHG PriceDB
Plugin URI: http://www.linux-hardware-guide.com
Description: Interface to the LHG price data base
Version: 0.1
Author: Captain Pike https://github.com/cptpike
Author URI: http://www.linux-hardware-guide.com
License: Proprietary
*/

require_once(plugin_dir_path(__FILE__).'/includes/lhg.conf');
# sets $lhg_price_db
# e.g.
# $lhg_price_db = new wpdb("wordpress", "PASSWORD", "lhgpricedb", "192.168.1.2");


require_once(plugin_dir_path(__FILE__).'includes/lhg_widget_supplier_overview.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_widget_featured_article.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_widget_article_summary.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_shop_button.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_pricedb_functions.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_scan_overview.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_menu_tags.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_autocreate.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_sanity_checks.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_shortcodes.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_auto_finder.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_donations.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_user_management.php');
require_once(plugin_dir_path(__FILE__).'includes/lhg_chat.php');
#require_once(plugin_dir_path(__FILE__).'includes/lhg_wpadmin_mods.php');
require_once('/var/www/wordpress/version.php');

# store new tags in LHGDB
add_action ('edit_post', 'lhg_update_tag_links' );
add_action ('save_post', 'lhg_update_tag_links' );


# Disable visual editor for all users - breaks too many things
#add_filter('user_can_richedit' , create_function('' , 'return false;') , 50);
# This breaks qtranslate tabs, because the link directly to the visual/html tabs
# Therefore, some other approach is needed
# Set default editor to html
function lhg_my_default_editor() {
	$r = 'html'; // html or tinymce
	return $r;
}
add_filter( 'wp_default_editor', 'lhg_my_default_editor' );

function lhg_remove_visual_tab() {

        if (!current_user_can('activate_plugins'))
        echo '

        <script type="text/javascript">
                /* <![CDATA[ */

		jQuery(window).load(function() {
       			jQuery("#content-html").click();
       			jQuery("#content-tmce").hide();
       			jQuery("#content-html").hide();
       			jQuery("#qtrans_select_ca").hide();
       			jQuery("#qtrans_select_uk").hide();
       			jQuery("#qtrans_select_in").hide();
       		 });

                /*]]> */
        </script>

        ';
}
add_action( 'admin_footer', 'lhg_remove_visual_tab' );

# Add own CSS file for backend pages
add_action('admin_enqueue_scripts', 'lhg_custom_css_files');
function lhg_custom_css_files () {

	wp_enqueue_style('admin-styles', '/wp-content/plugins/lhg-pricedb/css/backend.css');

}


#if (!is_admin()) return;
if (!current_user_can('publish_posts')) return;

//echo "SADDR: ".$_SERVER['SERVER_ADDR'];

//require_once(
require_once(plugin_dir_path(__FILE__).'includes/lhg_pricedb_update.php');

add_action ("admin_menu", "lhg_create_menu");
function lhg_create_menu () {
	add_menu_page ("LHG Tools", "LHG Tools",'publish_posts', "lhg_pricedb_update",'lhg_menu_settings_page',plugins_url('/images/lhg_logo_16x16.png',__FILE__) );

        add_submenu_page( "lhg_pricedb_update","Overview Articles", "Translations", "publish_posts",'lhg_menu_settings_page','lhg_menu_settings_page');
	add_submenu_page( "lhg_pricedb_update","Overview Hardware Scans", "HW scans", "publish_posts",'lhg_menu_hw_scans','lhg_menu_hw_scans');
	add_submenu_page( "lhg_pricedb_update","Overview Tags", "Manage tags", "publish_posts",'lhg_menu_tags','lhg_menu_tags');
	add_submenu_page( "lhg_pricedb_update","DB Sanity Checks", "Sanity Checks", "activate_plugins",'lhg_sanity_checks','lhg_sanity_checks');

}



// Add a column to the edit post list
if ( current_user_can('manage_options') )
add_filter( 'manage_edit-post_columns', 'lhg_db_add_new_columns');
/**
 * Add new columns to the post table
 *
 * @param Array $columns - Current columns on the list post
 */
function lhg_db_add_new_columns( $columns ) {

        global $region;

        //print "REG: $region";
        $shop_ids = lhg_return_shop_ids( $region );

        //print "Shop IDs:<br>";
        //print_r($shop_ids);


        if ($shop_ids != "")
        foreach ($shop_ids as $shopid ) {
        	$sid = $shopid->id;

                //print_r("-- $sid <br>");
                $shopname = lhg_db_get_shop_name($sid);

                //ToDo: currently skip Amazon
                if (!strpos($shopname, "mazon") > 0 ) {
	                //echo "SN: $shopname";
        	        $column_meta  = array( 'shop-ID-'.$sid => $shopname );

			$columns = array_slice( $columns, 0, 3, true ) +
        	        	$column_meta +
			        array_slice( $columns, 3, NULL, true );
		}
	}

        return $columns;
}

// Add action to the manage post column to display the data
add_action( 'manage_posts_custom_column' , 'lhg_db_custom_columns' );
/**
 * Display data in new columns
 *
 * @param  $column Current column
 *
 * @return Data for the column
 */
function lhg_db_custom_columns( $column ) {
	global $post;
        $oos='<font color="red"><b>out of stock</b></font>';
        //echo "COL: $column";
	switch ( substr($column,0,8) ) {
		case 'shop-ID-':
                        //for which shop ID are we showing information?
                        $shopid=substr($column,8);

                        //check if shop_article_id is defined
			$number = lhg_db_does_article_id_exist( $post->ID, $shopid);

                        if ($number > 1) {
                	        $metaData = "ERROR: too many results";
                                break;
			}

			$shop_article_id = lhg_db_get_shop_article_id( $post->ID, $shopid);


                        if ($number == 1) {
        	                $metaData = lhg_db_get_price( $post->ID, $shopid);
				$id = lhg_db_get_id( $post->ID, $shopid);
				$difftime = time() - lhg_db_get_time( $post->ID, $shopid);

                                $stime = number_format($difftime / (60*60*24),0);
                                if ($stime == 0) $timestring = "today";
                                if ($stime == 1) $timestring = "yesterday";
                                if ($stime > 1) $timestring = $stime. " days ago";

                                if ($shop_article_id == "NOT_AVAILABLE") {
	      			echo '<div class="inline-edit-group">
                                        Not available<br>checked '.$timestring.'<br>
					<a href="admin.php?page=lhg_pricedb_update&mode=update&sid='.$shopid.'&id='.$id.'&pid='.$post->ID.'">
					update</a></div>';
				}else{

                                	if ($metaData == "") $metaData = "(no price found)";
	      				echo '<div class="inline-edit-group">
                                        	'.$shop_article_id.'<br>'.$metaData.'<br>
						<a href="admin.php?page=lhg_pricedb_update&mode=update&sid='.$shopid.'&id='.$id.'&pid='.$post->ID.'">
						update</a></div>';

                                }

                                break;
			}

                        if ($number == 0) {
	                        $metaData = "not in DB";

	      			echo '<div class="inline-edit-group">
					<a href="admin.php?page=lhg_pricedb_update&mode=create&sid='.$shopid.'&pid='.$post->ID.'">
					create</a></div>';

	      			echo '<div class="inline-edit-group">
					(<a href="admin.php?page=lhg_pricedb_update&mode=notavail&sid='.$shopid.'&pid='.$post->ID.'">Not available</a>)</div>';
                                break;
			}



			break;
		case 'metade':
			$metaData = get_post_meta( $post->ID, 'price-amazon.de', true );
			break;

	}


        if ($metaData == "out of stock") $metaData = $oos;
        if ($metaData == "") $metaData = "not found";

        //Redcoon
        // if (substr($column,0,8) == "shop-ID-") echo $metaData;

}

// Register the column as sortable
//function lhg_db_register_sortable_columns( $columns ) {
//    $columns['meta-ID-1'] = 'Redcoon';
//    return $columns;
//}

//add_filter( 'manage_edit-post_sortable_columns', 'lhg_db_register_sortable_columns' );


function lhg_db_get_price( $postid, $shopid) {
    //connect to external DB to request price information
	/**
	 * Instantiate the wpdb class to connect to your second database, $database_name
	 */

        //Debug mode!
        //$postid = 3701;

        // $lhg_price_db->print_error();
	/**
	 * Use the new database object just like you would use $wpdb
	 */
        //echo "1";

        global $lhg_price_db;
        $sql = "SELECT shop_last_price FROM `lhgprices` WHERE lhg_article_id = ".$postid." AND shop_id = ".$shopid;
	$result = $lhg_price_db->get_var($sql);
        //echo $results;
        //var_dump($results);
        //echo "ERR: ".var_dump($lhg_price_db->last_query) ."ERREND<br>";
        // $lhg_price_db->print_error();

	//echo "R1:". $result["shop_last_price"];
	//echo "R1:". $result["shop_last_price"];
	//echo "R3:". $result;
	return $result; //s -> shop_last_price;

}

function lhg_db_does_article_id_exist ( $postid, $shopid) {
    //check, if article ID for this shop has been defined already

    global $lhg_price_db;

    $sql = "SELECT COUNT(*) FROM `lhgprices` WHERE lhg_article_id = ".$postid." AND shop_id = ".$shopid;
    $result = $lhg_price_db->get_var($sql);

    //var_dump($result);
        //echo "ERR: ".var_dump($lhg_price_db->last_query) ."ERREND<br>";
        // $lhg_price_db->print_error();

	//echo "R1:". $result["shop_last_price"];
	//echo "R1:". $result["shop_last_price"];
	//echo "R3:". $result;
	return $result; //s -> shop_last_price;

}

function lhg_db_get_shop_article_id ( $postid, $shopid) {

    global $lhg_price_db;

    $sql = "SELECT shop_article_id FROM `lhgprices` WHERE lhg_article_id = ".$postid." AND shop_id = ".$shopid;
    $result = $lhg_price_db->get_var($sql);

    return $result; 

}

function lhg_db_get_shop_price ( $postid, $shopid) {

    global $lhg_price_db;

    $sql = "SELECT shop_last_price FROM `lhgprices` WHERE lhg_article_id = ".$postid." AND shop_id = ".$shopid;
    $result = $lhg_price_db->get_var($sql);

    $error = $lhg_price_db->last_error;
    if ($error != "") var_dump($error);

    return $result; 

}

function lhg_db_get_shop_url ( $postid, $shopid) {

        //echo "<br>SID: $shopid";
        //echo "<br>PID: $postid";

    global $lhg_price_db;

    $sql = "SELECT shop_url FROM `lhgprices` WHERE lhg_article_id = \"".$postid."\" AND shop_id = ".$shopid;
    $result = $lhg_price_db->get_var($sql);

    return $result; 

}

function lhg_db_get_shop_name ( $shopid ) {

	global $lhg_price_db;


        $sql = "SELECT name FROM `lhgshops` WHERE id = \"".$shopid."\"";
    	$result = $lhg_price_db->get_var($sql);

        //var_dump($result);

        return $result;


}

function lhg_db_update ( $value, $db_table, $lhg_db_id) {

    global $lhg_price_db;
    //echo "VAL: $value";

    $sql = "";
    if ($db_table == "shop_article_id")
	    $sql = "UPDATE lhgprices SET `shop_article_id` = \"".$value."\" WHERE id = ".$lhg_db_id;

    //echo "<br>SQL: $sql";
    $result = $lhg_price_db->query($sql);
    //echo "<br>Res: $result";
    //echo "<br>LQ: ".var_dump($lhg_price_db->last_query) ."ERREND<br>";
    //echo "<br>LER: ".var_dump($lhg_price_db->last_error) ."ERREND<br>";

    $error = $lhg_price_db->last_error;
    if ($error != "") var_dump($error);

    return $result; 

}

function lhg_db_create_entry ( $post_id, $shop_id, $shop_article_id ) {

    global $lhg_price_db;
    //echo "VAL: $value";

    $sql = "INSERT INTO lhgprices (lhg_article_id, shop_id, shop_article_id)
    			VALUES ('$post_id', '$shop_id', '$shop_article_id')";

    //echo "<br>SQL: $sql";
    $result = $lhg_price_db->query($sql);
    //echo "<br>Res: $result";
    //echo "<br>LQ: ".var_dump($lhg_price_db->last_query) ."ERREND<br>";
    //echo "<br>LER: ".var_dump($lhg_price_db->last_error) ."ERREND<br>";

    $error = $lhg_price_db->last_error;
    if ($error != "") var_dump($error);

    return $result; 

}



function lhg_db_get_id ( $postid, $shopid) {

    global $lhg_price_db;

    $sql = "SELECT id FROM `lhgprices` WHERE lhg_article_id = ".$postid." AND shop_id = ".$shopid;
    $result = $lhg_price_db->get_var($sql);

    return $result; 

}

function lhg_db_get_time ( $postid, $shopid) {

    global $lhg_price_db;

    $sql = "SELECT last_update FROM `lhgprices` WHERE lhg_article_id = ".$postid." AND shop_id = ".$shopid;
    $result = $lhg_price_db->get_var($sql);

    return $result; 

}

function lhg_get_usbid( $postid) {

    global $lhg_price_db;

    $sql = "SELECT usbids FROM `lhgtransverse_posts` WHERE postid_com = ".$postid;
    $result = $lhg_price_db->get_var($sql);

    return $result; 

}

function lhg_get_pciid( $postid) {

    global $lhg_price_db;

    $sql = "SELECT pciids FROM `lhgtransverse_posts` WHERE postid_com = ".$postid;
    $result = $lhg_price_db->get_var($sql);

    return $result; 

}

function lhg_get_idstrg( $postid) {

    global $lhg_price_db;

    $sql = "SELECT idstring FROM `lhgtransverse_posts` WHERE postid_com = ".$postid;
    $result = $lhg_price_db->get_var($sql);

    return $result; 

}

add_action ('edit_post', 'lhg_save_id_widget_data' );
add_action ('save_post', 'lhg_save_id_widget_data_add' );

function lhg_save_id_widget_data_add( $postid ) {

	if ( wp_is_post_revision( $postid ) ) return;
        // i.e. will already be handeled by edit_post
        // otherwise
	lhg_save_id_widget_data( $postid );

}



function lhg_save_id_widget_data( $postid ) {
    #echo "HERE!\n";
    #echo "PID: $postid \n";

    if ($lang == "de") return;


    global $lhg_price_db;

    #$sql = "SELECT idstring FROM `lhgtransverse_posts` WHERE postid_com = ".$postid;
    #$result = $lhg_price_db->get_var($sql);
    #
    #return $result;

    #write data (currently no check if updated)
    #
    # write USB ID
    // warning -> this does not work if post update is initiated by hook-in
    $value_usb = $_POST['product-library-usbid'];
    $value_pci = $_POST['product-library-pciid'];
    $value_strg = $_POST['product-library-idstrg'];
    #echo "Val: $value \n";

    if ($value_usb != "") {
	    $sql = "UPDATE lhgtransverse_posts SET `usbids` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $value_usb, $postid);
	    $result = $lhg_price_db->query($safe_sql);
    }
    #echo "SQL: $result";

    # write PCI ID
    if ($value_pci != "") {
	    $sql = "UPDATE lhgtransverse_posts SET `pciids` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $value_pci, $postid);
	    $result = $lhg_price_db->query($safe_sql);
    }

    # write ID String (but do not blindly overvwrite)
    if ($value_strg != "") {
	    $sql = "UPDATE lhgtransverse_posts SET `idstring` = \"%s\" WHERE postid_com = %s";
	    $safe_sql = $lhg_price_db->prepare($sql, $value_strg, $postid);
	    $result = $lhg_price_db->query($safe_sql);
    }
}

function lhg_create_article_image( $image_url , $image_title ) {

  $id = getmypid();

  #title -> file name
  $image_title = str_replace(" ","_",$image_title);
  $image_title = str_replace("/","_",$image_title);
  $image_title = str_replace("&","_",$image_title);
  $image_title = str_replace("(","_",$image_title);
  $image_title = str_replace(")","_",$image_title);
  $image_title = str_replace("�","_",$image_title);
  $image_title = preg_replace('/[^A-Za-z0-9\-]/', '', $image_title);
  $image_title = sanitize_file_name($image_title);
  $image_title = str_replace("__","_",$image_title);
  $image_title = str_replace("__","_",$image_title);

  #print "IURL: $image_url";
  #print "<br>Title: $image_title";


  $local_file = "/tmp/image.".$id.".jpg";

  file_put_contents($local_file, fopen($image_url, 'r'));

  #      print "<br>loc file: $local_file";

  $im = @imagecreatefromjpeg($local_file);
  list($width, $height) = getimagesize($local_file);

  #print "<br>BxH = $width x $height";

  #if ( ($width < 130)  ){
  #      print "too small width!!<br>";
  #      imagecopyresized( $im, $im, 0, 0, 0, 0, 130, $height, $width, $heigth);
  #
  #        list($width, $height) = getimagesize($im);
  #	print "<br>New BxH = $width x $height";
  #
  #}



  if ( ($width != 130) or ($height != 130) ){
  	#rescaling necessary !!
        $w_new = 130;
        $h_new = 130;
	$newimage = imagecreatetruecolor($w_new, $h_new);
        $backgroundColor = imagecolorallocate($newimage, 255, 255, 255);
	imagefill($newimage, 0, 0, $backgroundColor);

        #if ($width > 130) {
           	#print "<br>Scale width: ".$w_new/$width;
        $scaling = $w_new/$width;
        if ($height * $scaling > 130 ) $scaling = $h_new/$height;

                #print "Scaling: ".$scaling;
                #imagecopyresized($newimage, $im, ($w_new - ($width*$scaling))/2, ($h_new - ($height*$scaling))/2, 0, 0, $width*$scaling, $height*$scaling,
                #                               $width , $height  );

        # Resize image (130x130) and copy to white background
        imagecopyresampled($newimage, $im, ($w_new - ($width*$scaling))/2, ($h_new - ($height*$scaling))/2, 0, 0,
        					$width*$scaling, $height*$scaling,
                                                $width , $height  );
	#}

	$im = $newimage;

  }

/*

    if (!$im) {
        $im  = imagecreatetruecolor(150, 30);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);

        imagestring($im, 1, 5, 5, 'Fehler beim Öffnen von ' . $imgname, $tc);
    }
*/

    $picdir = "/var/www/wordpress";
    $created_gif_url = "/wp-uploads/autoimage/".$image_title.".gif";
    #print "<br>URL: $created_gif_url<br>";
    imagegif($im, $picdir.$created_gif_url);
    imagedestroy($im);

    return $created_gif_url;

}


# Store ratings of different language servers in central database
# Will allow combining ratings accross servers
function lhg_store_ratings ( $post_id, $post_ratings_users, $post_ratings_score, $post_ratings_value ) {

	global $lang;
    	global $lhg_price_db;
        global $wpdb;

        #echo "<br>HERE: $lang - ";

        if ($lang != "de") {
                # find id corresponding to post_id

		$sql = "SELECT id FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
    		$id = $lhg_price_db->get_var($sql);

                if ($id != "0") {
                	#echo "ID: $id";
	                # store values
        	        $sql = "UPDATE lhgtransverse_posts SET  `post_ratings_users_com` = %s, `post_ratings_score_com` = %s, `post_ratings_value_com` = %s WHERE id = %s";
	        	$safe_sql = $lhg_price_db->prepare($sql, $post_ratings_users, $post_ratings_score, $post_ratings_value, $id);
	    		$result = $lhg_price_db->query($safe_sql);

	        	//Count categories of rating
		        $get_rates = $wpdb->get_results("SELECT rating_rating FROM $wpdb->ratings WHERE rating_postid = $post_id");
	        	$rating1=0;
	        	$rating2=0;
		        $rating3=0;
			$rating4=0;
			$rating5=0;
		        $rating_total=0;

	        	foreach($get_rates as $get_rate){
	        		$rating_total++;
		        	if ($get_rate->rating_rating == 1 ) $rating1++;
	        		if ($get_rate->rating_rating == 2 ) $rating2++;
				if ($get_rate->rating_rating == 3 ) $rating3++;
				if ($get_rate->rating_rating == 4 ) $rating4++;
		        	if ($get_rate->rating_rating == 5 ) $rating5++;
			}

	        	$sql = "UPDATE lhgtransverse_posts SET  `post_ratings_1_com` = %s, `post_ratings_2_com` = %s, `post_ratings_3_com` = %s, `post_ratings_4_com` = %s, `post_ratings_5_com` = %s WHERE id = %s";
		        $safe_sql = $lhg_price_db->prepare($sql, $rating1, $rating2, $rating3, $rating4, $rating5, $id);
	    		$result = $lhg_price_db->query($safe_sql);

		}
	}

        if ($lang == "de") {
                # find id corresponding to post_id

		$sql = "SELECT id FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
    		$id = $lhg_price_db->get_var($sql);

                if ($id != "0") {
                	#echo "ID: $id";
	                # store values
        	        $sql = "UPDATE lhgtransverse_posts SET  `post_ratings_users_de` = %s, `post_ratings_score_de` = %s, `post_ratings_value_de` = %s WHERE id = %s";
	        	$safe_sql = $lhg_price_db->prepare($sql, $post_ratings_users, $post_ratings_score, $post_ratings_value, $id);
	    		$result = $lhg_price_db->query($safe_sql);

	        	//Count categories of rating
		        $get_rates = $wpdb->get_results("SELECT rating_rating FROM $wpdb->ratings WHERE rating_postid = $post_id");
	        	$rating1=0;
	        	$rating2=0;
		        $rating3=0;
			$rating4=0;
			$rating5=0;
		        $rating_total=0;

	        	foreach($get_rates as $get_rate){
	        		$rating_total++;
		        	if ($get_rate->rating_rating == 1 ) $rating1++;
	        		if ($get_rate->rating_rating == 2 ) $rating2++;
				if ($get_rate->rating_rating == 3 ) $rating3++;
				if ($get_rate->rating_rating == 4 ) $rating4++;
		        	if ($get_rate->rating_rating == 5 ) $rating5++;
			}

	        	$sql = "UPDATE lhgtransverse_posts SET  `post_ratings_1_de` = %s, `post_ratings_2_de` = %s, `post_ratings_3_de` = %s, `post_ratings_4_de` = %s, `post_ratings_5_de` = %s WHERE id = %s";
		        $safe_sql = $lhg_price_db->prepare($sql, $rating1, $rating2, $rating3, $rating4, $rating5, $id);
	    		$result = $lhg_price_db->query($safe_sql);

		}
	}

}

# Returns the rating data combined over all servers
function lhg_get_post_ratings_data ( $post_id ) {

                global $lang;
                global $lhg_price_db;


        	if ($lang != "de") $safe_sql = "SELECT * FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
        	if ($lang == "de") $safe_sql = "SELECT * FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
                        $sql = $lhg_price_db-> prepare($safe_sql);
                        $result = $lhg_price_db->get_results($sql);

                        #var_dump($result[0]->id);
                        #print "<br>".$lhg_price_db->last_error."-----<br>";

	                #if ($id != "0") {
			#	$sql = "SELECT * FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		    	#	$result = $lhg_price_db->query($sql);

        		        $post_ratings_data['ratings_users'] = intval($result[0]->post_ratings_users_de + $result[0]->post_ratings_users_com);
			        $post_ratings_data['ratings_score'] = intval($result[0]->post_ratings_score_de + $result[0]->post_ratings_score_com);
			        if ($post_ratings_data['ratings_users'] != 0) $post_ratings_data['ratings_average'] = floatval($post_ratings_data['ratings_score'] / $post_ratings_data['ratings_users']);
			        if ($post_ratings_data['ratings_users'] == 0) $post_ratings_data['ratings_average'] = 0;

			#}


                #var_dump($post_ratings_data);
                return $post_ratings_data;
}

# Store comment counting in priceDB
function lhg_store_comment_numbers ( $comment_id, $comment_approved) {
        if ($comment_approved === 1) {
                $comment = get_comment( $comment_id );
                $post_id = $comment->comment_post_ID;
                lhg_store_comment_numbers_by_post_id ( $post_id );
                #error_log("Comment: $post_id ");

	}
}


# Store comment counting in priceDB by post ID
# extract from wpdb -> store in priceDB
#add_action('comment_post', 'lhg_store_comment_numbers', 10, 2 );
#add_action('wp_insert_comment','lhg_store_comment_numbers',99,2);
function lhg_store_comment_numbers_by_post_id ( $post_id ) {

        global $lang;
        global $lhg_price_db;

        #print "PID: $post_id<br>";
        if ($lang == "de") $p_region_array = array( "de" );
        if ($lang != "de") $p_region_array = array( "com", "ca", "co.uk", "fr", "es", "it", "nl", "in", "co.jp", "cn");

	if ($post_id != "0") {
        	foreach ( $p_region_array as $p_region) {
                        # Do not extract data from PriceDB
                        #$n_comment = lhg_comments_number_language( $p_region, $p_region , 0 , $post_id, "return_number");
                        # instead get data from local WPDB
                        $n_comment = lhg_comments_number_from_wpdb( $p_region, $post_id );

        	        #echo "ID: $id";
	        	# store values
                        $sql = "";
        	        if ( $p_region == "de" )    $sql = "UPDATE lhgtransverse_posts SET `post_comments_num_de` = %s WHERE postid_de = %s";
        	        if ( $p_region == "com" )   $sql = "UPDATE lhgtransverse_posts SET `post_comments_num_com` = %s WHERE postid_com = %s";
        	        if ( $p_region == "fr" )    $sql = "UPDATE lhgtransverse_posts SET `post_comments_num_fr` = %s WHERE postid_com = %s";
        	        if ( $p_region == "es" )    $sql = "UPDATE lhgtransverse_posts SET `post_comments_num_es` = %s WHERE postid_com = %s";
        	        if ( $p_region == "it" )    $sql = "UPDATE lhgtransverse_posts SET `post_comments_num_it` = %s WHERE postid_com = %s";
        	        if ( $p_region == "nl" )    $sql = "UPDATE lhgtransverse_posts SET `post_comments_num_nl` = %s WHERE postid_com = %s";
        	        if ( $p_region == "co.jp" ) $sql = "UPDATE lhgtransverse_posts SET `post_comments_num_co_jp` = %s WHERE postid_com = %s";
        	        if ( $p_region == "cn" )    $sql = "UPDATE lhgtransverse_posts SET `post_comments_num_cn` = %s WHERE postid_com = %s";

		        $safe_sql = $lhg_price_db->prepare($sql, $n_comment, $post_id);
		    	$result = $lhg_price_db->query($safe_sql);

                	#if ($post_id == 3120) print "store=> PID: $post_id, Reg: $p_region, Num: $n_comment <br>";
		}
	}
}

function lhg_get_rating_value( $post_id ) {
        global $lang;
        global $lhg_price_db;

	if ($lang != "de") $ratings = $lhg_price_db->get_results("SELECT * FROM  `lhgtransverse_posts` WHERE postid_com = $post_id");
	if ($lang == "de") $ratings = $lhg_price_db->get_results("SELECT * FROM  `lhgtransverse_posts` WHERE postid_de = $post_id");

	$num_rates = $ratings[0]->post_ratings_users_com + $ratings[0]->post_ratings_users_de;
        if ($num_rates == 0 ) $rating_avg = 0;
        if ($num_rates != 0 ) $rating_avg = ( $ratings[0]->post_ratings_score_com + $ratings[0]->post_ratings_score_de ) / ( $num_rates ) ;

        return $rating_avg;
}

function lhg_get_properties_string ( $postid ) {

        $posttags = get_the_tags( $postid );
        $properties_array = array();
        if ($posttags) {
  		foreach($posttags as $tag) {
		   array_push( $properties_array, $tag->name);
                   #error_log("TAG: ".$tag->name);
		}
	}
        $properties = join( ", " , $properties_array );
        return $properties;
}

function lhg_update_tag_links ( $postid ) {
        global $lhg_price_db;
        global $lang;

        $posttags = get_the_tags( $postid );
        $properties_array = array();
        if ($posttags) {
  		foreach($posttags as $tag) {
		   array_push( $properties_array, $tag->name);
                   #error_log("TAG2: ".$tag->name. " SLUG: ".$tag->slug);

                   if ($lang != "de") $myquery = $lhg_price_db->prepare("SELECT id FROM `lhgtransverse_tags` WHERE `slug_com` = '%s'", $tag->slug);
                   if ($lang == "de") $myquery = $lhg_price_db->prepare("SELECT id FROM  `lhgtransverse_tags` WHERE `slug_de` = '%s'", $tag->slug);
                   $slugid  = $lhg_price_db->get_var($myquery);

		   #error_log("SID: ($slugid)");

                   if ($slugid > 0) {
                   	// error_log("FOUND $tag->slug : $slugid");
                   } else {
                        # Tag not in LHGDB -> has to be added
	                   if ($lang != "de") $sql = "INSERT INTO lhgtransverse_tags ( slug_com ) VALUES ( '".$tag->slug."' ) ";
        	           if ($lang == "de") $sql = "INSERT INTO lhgtransverse_tags ( slug_de ) VALUES  ( '".$tag->slug."' ) ";
                           #error_log("Inserting $sql");
	                   $result  = $lhg_price_db->query($sql);

			}
		}
	}
        #$properties = join( ", " , $properties_array );
        #return $properties;
}


?>
