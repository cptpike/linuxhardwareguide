<?php
//Hardware Profile Menu for logged in users

//if ($lang != de)
include_once "/var/www/wordpress/wp-content/themes/blue-and-grey/lhg_functions.php";

function getCurrentUrl() {
	return ((empty($_SERVER['HTTPS'])) ? 'http' : 'https') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}


# show "My Hardware Profile" menu entry at top menu bar for logged in users
if(is_user_logged_in())
add_filter('wp_nav_menu_items','add_hwprofile', 10, 2);

function add_hwprofile($items, $args)
{

        global $txt_hwprofile;
        global $region;
        $lurl = lhg_get_lang_url_from_region( $region );
        if ($lurl != "") $lurl = "/".$lurl;

    //if( $args->theme_location == 'primary' )

        return $items . '<li id="menu-item-hwprofile" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-hwprofile">
        <a href="'.$lurl.'/hardware-profile"><span><span>
        <i class="icon-user menucolor"></i>'.$txt_hwprofile.'
        </span></span></a>
        </li>';

}


if ( ! isset( $content_width ) )
$content_width = 603;

    /*
     * Remove the WordPress Logo from the WordPress Admin Bar 
     */  
function remove_wp_logo() {
        global $wp_admin_bar;  
        $wp_admin_bar->remove_menu('wp-logo');  
}
add_action( 'wp_before_admin_bar_render', 'remove_wp_logo');

register_sidebar(array(
	'name' => __( 'Sidebar Widget Area' ),
	'id' => 'sidebar-widget-area',
	'description' => __( 'The sidebar widget area' ),
	'before_widget' => '<li>',
	'after_widget' => '</li>',
	'before_title' => '<h2>',
	'after_title' => '</h2>',        
));


register_nav_menus(
	array(
	  'primary' => 'Header Menu'
	)
);

function fb_add_custom_user_profile_fields( $user ) { 
 echo "<h3>Payment Information</h3>";

echo '
<table class="form-table">


  <tr>
     <th>
       <label for="flattr-id">Flattr ID</label></th>
     <td>
        <input type="text" name="flattr-id" id="flattr-id" value="'.
        esc_attr( get_the_author_meta( 'flattr-id', $user->ID ) ).
        '" class="regular-text"> <br>
        <span class="description">Please enter your <a href="http://www.flattr.com">Flattr.com</a> ID.</span>
     </td>
  </tr>


  <tr>
     <th>
       <label for="Paypal">Paypal email address</label></th>
     <td>
        <input type="text" name="paypal" id="paypal" value="'.
        esc_attr( get_the_author_meta( 'paypal', $user->ID ) ).
        '" class="regular-text"> <br>
        <span class="description">Provide the email address under which you are registered at Paypal to receive payments.</span>
     </td>
  </tr>

</table>
';
} 

function fb_save_custom_user_profile_fields( $user_id ) {

if ( !current_user_can ('edit_user', $user_id) )
        return FALSE;

update_usermeta ($user_id, 'flattr-id', $_POST['flattr-id'] );
update_usermeta ($user_id, 'paypal', $_POST['paypal'] );
} 
add_action( 'show_user_profile', 'fb_add_custom_user_profile_fields');
add_action( 'edit_user_profile', 'fb_add_custom_user_profile_fields');
add_action( 'personal_options_update', 'fb_save_custom_user_profile_fields');
add_action( 'edit_user_profile_update', 'fb_save_custom_user_profile_fields');



//Multi-level pages menu  
function blue_and_grey_page_menu() {  
	
if (is_page()) { $highlight = "page_item"; } else {$highlight = "menu-item current-menu-item"; }
echo '<ul class="menu">';
wp_list_pages('sort_column=menu_order&title_li=&link_before=<span><span>&link_after=</span></span>&depth=3');
echo '</ul>';
} 



add_editor_style();
add_theme_support('automatic-feed-links');
add_theme_support('post-thumbnails');

set_post_thumbnail_size( 110, 110, true ); // Default size

// Make theme available for translation
// Translations can be filed in the /languages/ directory
load_theme_textdomain('blue_and_grey', get_template_directory() . '/languages');

//password protections page change
function fb_the_password_form() {
    global $post;

    $label = 'pwbox-'.(empty($post->ID) ? rand() : $post->ID);
    $output = '<form action="' . get_option('siteurl') . '/wp-pass.php" method="post">'.
    '<p>' . __("My post is password protected. Please ask me for a password:") . '</p>'.
    __("Password") . ' <input name="post_password" id="' . $label . '" type="password" size="20" /><input type="submit"'.
    'name="Submit" value="' . esc_attr__("Submit") . '" /></p></form>';

    return $output;
}
add_filter('the_password_form', 'fb_the_password_form');

function read_cookie($name) {
 
	//$name = 'lang';
        //print "Cookie: $_COOKIE[$name]";
        if (isset($_COOKIE[$name])) {
        	return $_COOKIE[$name];
	    }
            else {
        	return "unset";
	    }
	//setcookie( $name, $value, time() + 3600, '/' );
}

function set_cookie($name,$value) {
	setcookie( $name, $value, time() + 3600*24*30*11, '/' );
}
//add_action( 'init', 'set_cookie');

function cookie_choose( ) {
	global $wp_query;

        //lang was set by URL
        /*
	$val = $wp_query->query_vars['lang'];

        if (
        ($val == "de") or
        ($val == "fr") or
        ($val == "es") or
        ($val == "it") or
        ($val == "en") or
        ($val == "in") or
        ($val == "uk") or
        ($val == "jp") or
        ($val == "br") or
        ($val == "cn") or
        ($val == "ca")
        ) {
        	set_cookie("lang",$val);
        	set_cookie("hide",1); //language was chosen. Do not show notice
	}else {

                $val = lang_by_ip();
                if ($val != "unset") set_cookie("lang",$val);
                //no reason to change cookie
                //set_cookie("lang","unset");
	}
        */

	$val = $wp_query->query_vars['sorting'];
        //echo "VAL: $val";
        if($val) set_cookie("sorting",$val);

	$val = $wp_query->query_vars['hide'];
        //echo "VAL: $val";
        if($val) set_cookie("hide",1);


}
add_action( 'template_redirect', 'cookie_choose');

function lang_by_ip(){
//$ip_address = '123.123.123.123';
//$ip_address = "173.194.69.105"; // US
//$ip_address = "93.188.170.32"; // FR
//$ip_address = "178.236.7.217"; // IE
//$ip_address = "212.58.253.67"; // GB
//$ip_address = "220.181.111.86"; // CN
//$ip_address = "199.84.154.81"; //CA
//$ip_address = "89.30.105.26"; // ES
//$ip_address = "194.244.112.36"; //IT
//$ip_address = "210.165.34.236"; //JP
//$ip_address = "213.191.73.65"; // DE


// $headers = apache_request_headers();
//echo "A: ".$headers["X-Forwarded-For"];
//echo "B: ".$_SERVER['HTTP_X_FORWARDED_FOR'];

$fwd_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
$sep=strpos($fwd_ip,",");
if ($sep != ""){
        $fwd_ip = substr($fwd_ip,0,$sep);
}


// works:
$ip_address_orig = $_SERVER["REMOTE_ADDR"];

if ($fwd_ip != ""){
  $ip_address = $fwd_ip;
}else{
  $ip_address = $ip_address_orig;
}


//  if (!empty($headers["X-Forwarded-For"])) {
//    $_SERVER["REMOTE_ADDR"] = $headers["X-Forwarded-For"];
//  }
//$ip_address = getenv("REMOTE_ADDR") ;

	global $quick_flag;
	if(isset($quick_flag) && is_object($quick_flag)){
    	  if(($info = $quick_flag->get_info($ip_address)) != false){
        	//$version = $info->version;      // Quick Flag version (float): 2.00
        	$ip = $info->ip;                // IP address (string): 123.123.123.123
	        $code = $info->code;            // Country code (string): HR
        	//$name = $info->name;            // Country name (string): Croatia
	        //$latitude = $info->latitude;    // Country latitude (float): 45.1667
        	//$longitude = $info->longitude;  // Country longitude (float): 15.5
	        //$flag = $quick_flag->get_flag($info, 'my-own-css-class'); // CSS class is optional, 'quick-flag' by default
	  }
	}

        if (0==1){
		$logFile     = "/tmp/country.log";
	        $fh = fopen($logFile, 'a') or die("can't open file");
        	$stringData = "IP:".$ip." FWD:".$fwd_ip." Country:".$code."\n";
		fwrite($fh, $stringData);
		fclose($fh);
	}


        if ($code == "US") return "en";
        if ($code == "FR") return "fr";
        if ($code == "ES") return "es";
        if ($code == "IT") return "it";
        if ($code == "GB") return "uk";
        if ($code == "IE") return "uk";
        if ($code == "JP") return "jp";
        if ($code == "CN") return "cn";
        if ($code == "IN") return "in";
        if ($code == "CA") return "ca";

        //return "uk";

        return "unset";

}

function get_country(){
//$ip_address = '123.123.123.123';
//$ip_address = "173.194.69.105"; // US
//$ip_address = "93.188.170.32"; // FR
//$ip_address = "178.236.7.217"; // IE
//$ip_address = "212.58.253.67"; // GB
//$ip_address = "220.181.111.86"; // CN
//$ip_address = "199.84.154.81"; //CA
//$ip_address = "89.30.105.26"; // ES
//$ip_address = "194.244.112.36"; //IT
//$ip_address = "210.165.34.236"; //JP
//$ip_address = "213.191.73.65"; // DE


// $headers = apache_request_headers();
//echo "A: ".$headers["X-Forwarded-For"];
//echo "B: ".$_SERVER['HTTP_X_FORWARDED_FOR'];

$fwd_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
$sep=strpos($fwd_ip,",");
if ($sep != ""){
        $fwd_ip = substr($fwd_ip,0,$sep);
}


// works:
$ip_address_orig = $_SERVER["REMOTE_ADDR"];

if ($fwd_ip != ""){
  $ip_address = $fwd_ip;
}else{
  $ip_address = $ip_address_orig;
}


//  if (!empty($headers["X-Forwarded-For"])) {
//    $_SERVER["REMOTE_ADDR"] = $headers["X-Forwarded-For"];
//  }
//$ip_address = getenv("REMOTE_ADDR") ;

	global $quick_flag;
	if(isset($quick_flag) && is_object($quick_flag)){
    	  if(($info = $quick_flag->get_info($ip_address)) != false){
        	//$version = $info->version;      // Quick Flag version (float): 2.00
        	$ip = $info->ip;                // IP address (string): 123.123.123.123
	        $code = $info->code;            // Country code (string): HR
        	//$name = $info->name;            // Country name (string): Croatia
	        //$latitude = $info->latitude;    // Country latitude (float): 45.1667
        	//$longitude = $info->longitude;  // Country longitude (float): 15.5
	        //$flag = $quick_flag->get_flag($info, 'my-own-css-class'); // CSS class is optional, 'quick-flag' by default
	  }
	}

        /*
        if (1==1){
		$logFile     = "/tmp/country.log";
	        $fh = fopen($logFile, 'a') or die("can't open file");
        	$stringData = "IP:".$ip." FWD:".$fwd_ip." Country:".$code."\n";
		fwrite($fh, $stringData);
		fclose($fh);
	}
        */

        if ($code == "US") return "en";
        if ($code == "DE") return "de";
        if ($code == "FR") return "fr";
        if ($code == "ES") return "es";
        if ($code == "IT") return "it";
        if ($code == "GB") return "uk";
        if ($code == "IE") return "uk";
        if ($code == "JP") return "jp";
        if ($code == "CN") return "cn";
        if ($code == "IN") return "in";
        if ($code == "CA") return "ca";

        return "fr";

}


function get_sorting( ) {
        //print "get region: ";

	global $wp_query;
        global $sorting;
	$val = $wp_query->query_vars['sorting'];
        $cval = read_cookie("sorting");
        if ($val) return $val;
        return $cval;
}


function get_hide( ) {
        //print "get region: ";

	global $wp_query;
        global $hide;

        //also check if language was defined -> no notification box!
	//$val = $wp_query->query_vars['lang'];
        //if ($val) return 1;


	$val = $wp_query->query_vars['hide'];
        $cval = read_cookie("hide");
        if ($val) return 1;

        return $cval;
}



function url_parameter( $qvars ) {
	//$qvars[] = 'lang';
	$qvars[] = 'hide';

        //sorting only for category and tag pages
        //echo "URI: ".$_SERVER["REQUEST_URI"]."<br>";
	if (
        ( strpos($_SERVER["REQUEST_URI"],"category") > 0) or
        ( strpos($_SERVER["REQUEST_URI"],"tag") > 0 )
        ) $qvars[] = 'sorting';

        //echo "category";
        //sorting only for archives
        //print "cat: ". is_category();
        //if ( is_category() ) print "archive or category";
        //if ( !is_category() ) print "other page";
        //if ( is_category() ) $qvars[] = 'sorting';


	$qvars[] = 'tagid';
	return $qvars;
}

add_filter('query_vars', 'url_parameter' );

//add_filter( 'rewrite_rules_array', 'my_rewrite_rules' );
add_filter( 'template_redirect', 'my_rewrite_rules' );
add_filter( 'init', 'flushRules' );

function my_rewrite_rules( ) {


	global $wp_query;
        global $tagid;
	$val = $wp_query->query_vars['tagid'];
        $turl = "/tag/";
        $tfound=0;

	//print "tagid: $val";
        if ($val != "")
	foreach ($val as &$tid){

        	if ($tid != ""){
                //print "TID: $tid ";
                        $term=get_term_by("id",$tid,"post_tag");
	        	$turl .= "".$term->slug."+";
                        $tfound++;
        	}
        }

  /*  $wp_rewrite->rules =
    array_merge( array(
        '^product/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$' =>
        'index.php?level1=$matches[1]&level2=$matches[2]&level3=$matches[3]&level4=$matches[4]'
    ), $wp_rewrite->rules);
    */
	//print "URL $turl";

        	if ($tfound > 0){
        $turl=substr($turl,0,-1);
print '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
	<meta http-equiv="Refresh" content="0; url='.$turl.'">
	</head>

	<body>
	</body>
	</html>';
        exit;
	}
 	//$newrules = array();
    	//$newrules['(tagsearch)/([a-zA-Z0-9 ]+)$'] = '/tag/'.$turl;

	//return $newrules + $wp_rewrite;
}


function flushRules(){
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}


function edit_admin_menus( $user ) {
    global $menu;  
    global $submenu;  
    global $lang;

    global $current_user;
    get_currentuserinfo();
    $user_id = $current_user->ID;

/*
  echo "<pre>



    ";
    echo "</pre>";
*/

/*
    if ($lang == "de") {
      $menu[5][0] = 'Artikel'; // Change Posts to Recipes
      $menu[2][0] = '&Uuml;bersicht'; // Change Posts to Recipes

      $oldvar=$menu[25][0];
      $oldvar=str_replace("Comments","Kommentare",$oldvar);
      $menu[25][0]=$oldvar;

    }
*/

//    $submenu['edit.php'][5][0] = 'All Recipes';
//    $submenu['edit.php'][10][0] = 'Add a Recipe';
//    $submenu['edit.php'][15][0] = 'Meal Types'; // Rename categories to meal types
//    $submenu['edit.php'][16][0] = 'Ingredients'; // Rename tags to ingredients


if ($user_id != 1) remove_menu_page('tools.php'); // Remove the Tools Menu
}  
add_action( 'admin_menu', 'edit_admin_menus' );


//Make menu URL qtrans friendly
if (function_exists('qtrans_convertURL')) {
function qtrans_in_nav_el($output, $item, $depth, $args) {

$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';

$attributes .=!empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';

$attributes .=!empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

// Integration with qTranslate Plugin

$attributes .=!empty($item->url) ? ' href="' . esc_attr( qtrans_convertURL($item->url) ) . '"' : '';

$output = $args->before;

$output .= '<a' . $attributes . '>';

$output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;

$output .= '</a>';

$output .= $args->after;

return $output;

}

add_filter('walker_nav_menu_start_el', 'qtrans_in_nav_el', 10, 4);

}

//
// translation
//

?>
