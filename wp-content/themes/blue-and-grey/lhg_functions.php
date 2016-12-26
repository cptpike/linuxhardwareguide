<?php

# Store comment counting in priceDB by post ID
# extract from wpdb -> store in priceDB
add_action('comment_post', 'lhg_store_comment_numbers', 10, 2 );

// ini_set( 'display_errors', 1 );
// error_reporting(-1);

require_once("/var/www/wordpress/wp-content/themes/blue-and-grey/lhg_currency_functions.php");

global $lang_array;
$lang_array     = array('ca','zh','fr','in','it','ja'   ,'uk'   ,'es','com'  ,'nl');

global $region_array;
$region_array     = array('ca','cn','fr','in','it','co.jp','co.uk','es','com','nl');

global $lang_url_array;
$lang_url_array = array('ca','zh','fr','in','it','ja'    ,'uk'  ,'es',''     ,'nl');

// donation values (manual setting a.t.m.)
global $donation_total;
$donation_total = 30.00;



function get_distri_logo( $distribution )  {

        $distri = lhg_get_distribution_array();
        $distri_name = lhg_get_distri_name( $distribution );
	$logo = $distri[$distri_name]["logo"];


        #fallback:
        if ($logo == "") $logo = "/wp-content/plugins/lhg-hardware-profile-manager/images/unknown-logo.png";

        #print "D: $distribution, LOGO: $logo <br>";
	return $logo;
}

function lhg_get_distribution_array( )  {

        $dist = array(

                "mint" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/linuxmint-logo.png",
                        "url" => "https://www.linuxmint.com/",
                        "donation_target" => 6
                        ),

                "ubuntu" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/ubuntu-logo.png",
                        "url" => "https://www.ubuntu.com/",
                        "donation_target" => 8
                        ),

                "debian" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/debian-logo.jpg",
                        "url" => "https://www.debian.org/",
                        "donation_target" => 9
                        ),

                "fedora" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/fedora-logo.jpg",
                        "url" => "https://getfedora.org",
                        "donation_target" => -1
                        ),

                "zorin" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/zorin-logo.png",
                        "url" => "http://zorinos.com/",
                        "donation_target" => -1
                        ),

                "tanglu" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/tanglu-logo.png",
                        "url" => "http://tanglu.org/",
                        "donation_target" => -1
                        ),

                "korora" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/korora-logo.jpg",
                        "url" => "https://kororaproject.org/",
                        "donation_target" => 10
                        ),

                "arch" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/arch-linux-logo.png",
                        "url" => "https://www.archlinux.org/",
                        "donation_target" => 11
                        ),

                "opensuse" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/OpenSUSE_Logo.png",
                        "url" => "https://www.opensuse.org/",
                        "donation_target" => -1
                        ),

                "manjaro" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/Manjaro-logo.png",
                        "url" => "https://manjaro.org/",
                        "donation_target" => 12
                        ),

                "alt" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/alt-linux-logo.png",
                        "url" => "http://www.altlinux.com/",
                        "donation_target" => -1
                        ),

                "simply" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/simply-linux-logo.png",
                        "url" => "http://simplylinux.ru/",
                        "donation_target" => -1
                        ),

                "kali" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/kalilinux-logo.png",
                        "url" => "https://www.kali.org/",
                        "donation_target" => -1
                        ),

                "raspbian" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/raspbian-logo.png",
                        "url" => "https://www.raspbian.org/",
                        "donation_target" => 13
                        ),

                "ultimate-edition" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/ultimate-edition-logo.png",
                        "url" => "http://ultimateedition.info/",
                        "donation_target" => 14
                        ),

                "gentoo" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/gentoo-logo.png",
                        "url" => "https://www.gentoo.org/",
                        "donation_target" => 15
                        ),

                "lfs" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/linux-from-scratch-logo.png",
                        "url" => "http://linuxfromscratch.org/",
                        "donation_target" => 16
                        ),

                "mageia" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/mageia-logo.png",
                        "url" => "https://www.mageia.org",
                        "donation_target" => 17
                        ),

                "pisi" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/pisi-linux-logo.png",
                        "url" => "http://www.pisilinux.org",
                        "donation_target" => 18
                        ),

                "kde-neon" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/kde-neon-logo.png",
                        "url" => "https://neon.kde.org/",
                        "donation_target" => 19
                        ),

                "elementary-os" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/elementary-os-logo.png",
                        "url" => "https://elementary.io",
                        "donation_target" => 20
                        ),

                "netrunner" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/netrunner-logo.png",
                        "url" => "http://www.netrunner.com/",
                        "donation_target" => -1
                        ),

                "aldos" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/aldos-logo.png",
                        "url" => "http://www.alcancelibre.org/staticpages/index.php/notas-lanzamiento-aldos-1-4",
                        "donation_target" => 21
                        ),

                "centos" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/centos-logo.png",
                        "url" => "https://www.centos.org/",
                        "donation_target" => 22
                        ),

                "pclinuxos" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/pclinuxos-logo.png",
                        "url" => "http://www.pclinuxos.com/",
                        "donation_target" => 23
                        ),

                "chakra" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/chakra-logo.png",
                        "url" => "https://chakralinux.org/",
                        "donation_target" => 24
                        ),

                "slackware" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/slackware-logo.png",
                        "url" => "http://www.slackware.com/",
                        "donation_target" => 25
                        ),

                "devuan" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/devuan-logo.png",
                        "url" => "https://devuan.org/",
                        "donation_target" => 26
                        ),

                "solydxk" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/solyd-xk-logo.png",
                        "url" => "https://solydxk.com/",
                        "donation_target" => 27
                        ),

                "antergos" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/antergos-logo.png",
                        "url" => "https://antergos.com/",
                        "donation_target" => 28
                        ),

                "funtoo" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/funtoo-logo.png",
                        "url" => "http://funtoo.org/",
                        "donation_target" => 29
                        ),

                "bodhi" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/bodhi-logo.png",
                        "url" => "http://www.bodhilinux.com/",
                        "donation_target" => 30
                        ),

                "mx-linux" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/mx-linux-logo.png",
                        "url" => "https://mxlinux.org/",
                        "donation_target" => 31
                        ),

                "handylinux" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/handylinux-logo.png",
                        "url" => "https://handylinux.org/",
                        "donation_target" => -1
                        ),

                "point-linux" => array(
                        "logo" => "/wp-content/plugins/lhg-hardware-profile-manager/images/point-linux-logo.png",
                        "url" => "https://pointlinux.org/",
                        "donation_target" => 32
                        ),

);

        return $dist;

}

function lhg_get_distri_name( $distribution ) {

        if ( strpos($distribution,"Ubuntu") > -1 )
                return "ubuntu";

        if ( strpos($distribution,"Mint") > -1 )
                return "mint";

        if ( strpos($distribution,"LMDE") > -1 )
                return "mint";

        if ( strpos($distribution,"Debian") > -1 )
                return "debian";

        if ( strpos($distribution,"Fedora") > -1 )
                return "fedora";

        if ( strpos($distribution,"Zorin") > -1 )
                return "zorin";

        if ( strpos($distribution,"Tanglu") > -1 )
                return "tanglu";

        if ( strpos($distribution,"Korora") > -1 )
                return "korora";

        if ( strpos($distribution,"Arch Linux") > -1 )
                return "arch";

        if ( strpos($distribution,"openSUSE") > -1 )
                return "opensuse";

        if ( strpos($distribution,"Manjaro") > -1 )
                return "manjaro";

        if ( strpos($distribution,"ALT Linux") > -1 )
                return "alt";

        if ( strpos($distribution,"Simply Linux") > -1 )
                return "simply";

        if ( strpos($distribution,"Kali ") > -1 )
                return "kali";

        if ( strpos($distribution,"Raspbian ") > -1 )
                return "raspbian";

        if ( strpos($distribution,"Ultimate Edition ") > -1 )
                return "ultimate-edition";

        if ( strpos($distribution,"Gentoo") > -1 )
                return "gentoo";

        if ( strpos($distribution,"Linux From Scratch") > -1 )
                return "lfs";

       if ( strpos($distribution,"Mageia") > -1 )
                return "mageia";

        if ( strpos($distribution,"Pisi") > -1 )
                return "pisi";

        if ( strpos($distribution,"KDE neon") > -1 )
                return "kde-neon";

        if ( strpos($distribution,"KDE Neon") > -1 )
                return "kde-neon";

        if ( strpos($distribution,"elementary OS") > -1 )
                return "elementary-os";

        if ( strpos($distribution,"Elementary OS") > -1 )
                return "elementary-os";

        if ( strpos($distribution,"Netrunner") > -1 )
                return "netrunner";

        if ( strpos($distribution,"ALDOS") > -1 )
                return "aldos";

        if ( strpos($distribution,"CentOS") > -1 )
                return "centos";

        if ( strpos($distribution,"PCLinuxOS") > -1 )
                return "pclinuxos";

        if ( strpos($distribution,"Chakra") > -1 )
                return "chakra";

        if ( strpos($distribution,"Slackware") > -1 )
                return "slackware";

        if ( strpos($distribution,"Devuan") > -1 )
                return "devuan";

        if ( strpos($distribution,"Solyd") > -1 )
                return "solydxk";

        if ( strpos($distribution,"Antergos") > -1 )
                return "antergos";

        if ( strpos($distribution,"Funtoo") > -1 )
                return "funtoo";

        if ( strpos($distribution,"Bodhi") > -1 )
                return "bodhi";

        if ( strpos($distribution,"MX ") > -1 )
                return "mx-linux";

        if ( strpos($distribution,"HandyLinux") > -1 )
                return "handylinux";

        if ( strpos($distribution,"Point Linux") > -1 )
                return "point-linux";

}

// Twitter widget on main page
// enhance widget with unicode character substitution
add_filter( 'widget_twitter_content', 'lhg_twitter_unicode' );
function lhg_twitter_unicode( $text ) {
        #uncomment and look into log message to get unicode
        #error_log("Filter: $text");
        # Star substitution
        $text = str_replace("\xe2\xad\x90\xef\xb8\x8f",'<img src="/wp-content/plugins/wp-postratings/images/stars_crystal/rating_on.gif" style="height: 1em;">',$text);
        # Flag substitution
        $text = str_replace("\xf0\x9f\x87\xab\xf0\x9f\x87\xb7",'<img src="/wp-content/plugins/qtranslate/flags/fr.png" style="height: .8em; margin-right: 2px;">',$text);
        $text = str_replace("\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8",'<img src="/wp-content/plugins/qtranslate/flags/es.png" style="height: .8em; margin-right: 2px;">',$text);
        $text = str_replace("\xf0\x9f\x87\xae\xf0\x9f\x87\xb9",'<img src="/wp-content/plugins/qtranslate/flags/it.png" style="height: .8em; margin-right: 2px;">',$text);
        $text = str_replace("\xf0\x9f\x87\xb3\xf0\x9f\x87\xb1",'<img src="/wp-content/plugins/qtranslate/flags/nl.png" style="height: .8em; margin-right: 2px;">',$text);
        $text = str_replace("\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5",'<img src="/wp-content/plugins/qtranslate/flags/jp.png" style="height: .8em; margin-right: 2px;">',$text);
        $text = str_replace("\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3",'<img src="/wp-content/plugins/qtranslate/flags/cn.png" style="height: .8em; margin-right: 2px;">',$text);

	return $text;
}



// Associating a function to login hook
add_action ( 'wp_login', 'lhg_set_last_login' );
 
function lhg_set_last_login ( $login ) {
   //what changed since last login?

   $user = get_userdatabylogin ( $login );
 
   // Setting the last login of the user
   $last_login_time = get_user_meta ( $user->ID, 'this_login', true );

   // Setting the last login of the user
   $last_login_last_comment = get_user_meta ( $user->ID, 'this_login_last_comment', true );

   //find last recent comment
   $recent_comments = get_comments( array(
    'number'    => 1,
    'status'    => 'approve'
   ) );
   foreach($recent_comments as $comment) :
     $latest_comment_id = $comment->comment_ID;
   endforeach;

   if ($last_login_time != "") update_usermeta ( $user->ID, 'last_login', $last_login_time );
   if ($last_login_time == "") update_usermeta ( $user->ID, 'last_login', date ( 'Y-m-d H:i:s' ) );
   update_usermeta ( $user->ID, 'this_login', date ( 'Y-m-d H:i:s' ) );

   if (is_int($last_login_last_comment)) update_usermeta ( $user->ID, 'last_login_last_comment', $last_login_last_comment );
   if (!is_int($last_login_last_comment)) update_usermeta ( $user->ID, 'last_login_last_comment', $latest_comment_id );
   update_usermeta ( $user->ID, 'this_login_last_comment', $latest_comment_id );

}


function lhg_count_new_comments ( ) {

   	//echo "Start Count<br>";
        $last_login_comment = get_user_meta ( get_current_user_id(), 'last_login_last_comment', true );
        //$last_login_comment = 2026;
        //echo "compare: $last_login_comment <br>";
   	//find last recent comment
	$recent_comments = get_comments( array(
	    'number'    => "",
	    'status'    => 'approve'
	   ) );

	//count new ones
	$i=0;
	foreach($recent_comments as $comment) {
	  //echo "CID: $comment->comment_ID <br>";
	  if ( $comment->comment_ID > $last_login_comment ) $i++;
	}

   return $i;
}

//category shortcode
add_shortcode('lhg_category', 'lhg_shortcode_category');
function lhg_shortcode_category ( $attr  ) {

        if (isset ($attr['img'] ) ) {
        	$img=get_the_post_thumbnail($attr['img']);
                $imgurl = wp_get_attachment_url( get_post_thumbnail_id($attr['img']) );
        }

        if (isset ($attr['sub'] ) ) {
                #print "sub: ".$attr['sub'];
        	$subs = explode(",",$attr['sub']);

                $subtext = '<div class="overview-sublist"><ul>';

	        foreach ($subs as &$sub) {
                        #print "SUB: ".$sub;
                        $subname = get_cat_name($sub);
                        $suburl = get_category_link($sub);

                        $subtext .= "<li><a href=\"".$suburl."\">".$subname."</a></li>";
                }
                $subtext .= '</ul></div>';

        }

        if (isset ($attr['tags'] ) ) {
                #print "sub: ".$attr['sub'];
        	$subs = explode(",",$attr['tags']);

                $subtext = '<div class="overview-sublist"><ul>';

	        foreach ($subs as &$sub) {
                        #print "SUB: ".$sub;
                        $tag = get_tag($sub); // <-- your tag ID
			$subname = $tag->name;
                        #$subname = get_tag($sub);
                        $suburl = get_tag_link($sub);

                        $subtext .= "<li><a href=\"".$suburl."\">".$subname."</a></li>";
                }
                $subtext .= '</ul></div>';

        }

        if (isset ($attr['id'] ) ) {

                $id = $attr['id'];
                $cat_url = get_category_link($id);

                $text = '
        <div class="overview-box">

                <div class="overview-title">
		        <a href="'.$cat_url.'"><div class="ot-clickbox">'.get_cat_name($id).'</div></a>
                </div>


                <a href="'.$cat_url.'">
        	<div class="overview-image">
        		  <img src="'.$imgurl.'" width="110px" alt="'.get_cat_name($id).'" />
                </div>
                </a>';


                if ($subtext != "") $text .= $subtext;

        $text .= '
        </div>
        ';


        }

	return $text;
}

function lhg_check_transverse_postid( $postid ) {

	//ToDo: remove comment. only disregarded due to transition phase
        #if ( !current_user_can( 'administrator' ) ) return;

        //if (is_single() ) echo "Post: continue";
        if ( is_page() ) return; //do not store pages in this DB
        if ( is_attachment() ) return; //do not stroe attachment pages in DB

        //check if postid was already added to DB, otherwise create entry
        global $lang;

	$title=translate_title(get_the_title());
	$s=explode("(",$title);
	$short_title=trim($s[0]);


        #echo "PID: $postid";

        if ($lang == "en") {

                //check if postid is in DB
                $postid_com = $postid;
	        $permalink_com = get_permalink($postid_com);
                $permalink_com = str_replace("/uk/","/",$permalink_com);
                $permalink_com = str_replace("/zh/","/",$permalink_com);
                $permalink_com = str_replace("/nl/","/",$permalink_com);
                $permalink_com = str_replace("/fr/","/",$permalink_com);
                $permalink_com = str_replace("/es/","/",$permalink_com);
                $permalink_com = str_replace("/it/","/",$permalink_com);
                $permalink_com = str_replace("/in/","/",$permalink_com);
                $permalink_com = str_replace("/ja/","/",$permalink_com);
                $permalink_com = str_replace("/ca/","/",$permalink_com);



		global $lhg_price_db;
	        $sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE postid_com = ".$postid_com;
		$result = $lhg_price_db->get_var($sql);

                #echo "<br>RESULT: $result<br>";

                if ($result != "") {
                        //already in DB
                        #echo "found<br>";

		}elseif ($result == "0") {
                        #echo "found 2<br>";

		}else{
                        #echo "Update needed<br>";
                        #echo "Inser pid: $postid_com";
                        //write permalink to DB

                        if ($short_title != "") {
				global $lhg_price_db;
                	        $sqlinsert = "INSERT INTO lhgtransverse_posts (product, postid_com, permalink_com) VALUES ('$short_title', '$postid_com','$permalink_com')";
				$resultB = $lhg_price_db->query($sqlinsert);
	                }



                }
	}

        if ($lang == "de") {

                //check if postid is in DB
                $postid_de = $postid;
	        $permalink_de = get_permalink($postid_de);

		global $lhg_price_db;
	        $sql = "SELECT postid_de FROM `lhgtransverse_posts` WHERE postid_de = ".$postid_de;
		$result = $lhg_price_db->get_var($sql);

                #echo "<br>RESULT: $result<br>";

                if ($result != "") {
                        //already in DB
                        #echo "found<br>";

		}elseif ($result == "0") {
                        #echo "found 2<br>";

		}else{
                        #echo "Update needed<br>";
                        #echo "Inser pid: $postid_com";
                        //write permalink to DB

                        if ($short_title != "") {
				global $lhg_price_db;
                	        $sqlinsert = "INSERT INTO lhgtransverse_posts (product, postid_de, permalink_de) VALUES ('$short_title', '$postid_de','$permalink_de')";
				$resultB = $lhg_price_db->query($sqlinsert);
	                }



                }
	}


}

function lhg_URL_chomp( $url ) {
	$url = str_replace("http://www.linux-hardware-guide.com/","",$url);
	$url = str_replace("http://www.linux-hardware-guide.de/","",$url);
	$url = str_replace("http://linux-hardware-guide.com/","",$url);
	$url = str_replace("http://linux-hardware-guide.de/","",$url);
	$url = str_replace("http://m.linux-hardware-guide.com/","",$url);
	$url = str_replace("http://m.linux-hardware-guide.de/","",$url);
	$url = str_replace("http://192.168.3.113/","",$url);
	$url = str_replace("http://192.168.3.112/","",$url);
        return $url;

}


function lhg_get_com_post_URL( $postid ) {
        global $lang;

        #echo "                    PID: $postid";

        if ($lang == "en") {
                // we get the .com postid
		global $lhg_price_db;
	        $sql = "SELECT permalink_com FROM `lhgtransverse_posts` WHERE postid_com = ".$postid;
		$result = $lhg_price_db->get_var($sql);
                if ($result == "") $result = "http://www.linux-hardware-guide.com/";
                return $result;
        }

        if ($lang == "de") {
                // we got the .com postid
		global $lhg_price_db;
	        $sql = "SELECT permalink_com FROM `lhgtransverse_posts` WHERE postid_de = ".$postid;
		$result = $lhg_price_db->get_var($sql);
                if ($result == "") $result = "http://www.linux-hardware-guide.com/";
                return $result;
        }
}

function lhg_get_de_post_URL( $postid ) {
        global $lang;

        if ($lang == "en") {
                // we get the .com postid
                #echo "---------------------------> Here";

                global $lhg_price_db;
	        $sql = "SELECT permalink_de FROM `lhgtransverse_posts` WHERE postid_com = ".$postid;
		$result = $lhg_price_db->get_var($sql);
                #echo "RES: $result";
                if ($result == "") $result = "http://www.linux-hardware-guide.de/";
                return $result;
        }

        if ($lang == "de") {
                // we get the .com postid
		global $lhg_price_db;
	        $sql = "SELECT permalink_de FROM `lhgtransverse_posts` WHERE postid_de = ".$postid;
		$result = $lhg_price_db->get_var($sql);
                if ($result == "") $result = "http://www.linux-hardware-guide.de/";
                return $result;
        }


}

function lhg_check_permalink( $postid ) {

   // check only if administrator - reduces SQL load
   $userid = get_current_user_id();
   if ( current_user_can( 'administrator' )
         or ( $userid == 12476 )
       )  {


        //check if postid was included
        $val = lhg_check_transverse_postid($postid);

	global $lhg_price_db;
        global $lang;

        #echo "PID: $postid";

        if ($lang == "en") {

                $postid_com = $postid;


        	//check permalink
        	$permalink_com = get_permalink($postid_com);
                $permalink_com = str_replace("/uk/","/",$permalink_com);
                $permalink_com = str_replace("/zh/","/",$permalink_com);
                $permalink_com = str_replace("/nl/","/",$permalink_com);
                $permalink_com = str_replace("/fr/","/",$permalink_com);
                $permalink_com = str_replace("/es/","/",$permalink_com);
                $permalink_com = str_replace("/it/","/",$permalink_com);
                $permalink_com = str_replace("/in/","/",$permalink_com);
                $permalink_com = str_replace("/ja/","/",$permalink_com);
                $permalink_com = str_replace("/ca/","/",$permalink_com);


	        $sql = "SELECT permalink_com FROM `lhgtransverse_posts` WHERE postid_com = ".$postid_com;
		$result = $lhg_price_db->get_var($sql);

                #echo "RES: $result <br>";
                #echo "PLK: ".$permalink_com ."<br>";


                if ($result != $permalink_com) {
                        #echo "Update needed";
                        //write permalink to DB

                        $sql = "UPDATE lhgtransverse_posts SET `permalink_com` = \"".$permalink_com."\" WHERE postid_com = ".$postid_com;
			$result = $lhg_price_db->query($sql);

		}

	}

        if ($lang == "de") {

                $postid_de = $postid;


        	//check permalink
        	$permalink_de = get_permalink($postid_de);

	        $sql = "SELECT permalink_de FROM `lhgtransverse_posts` WHERE postid_de = ".$postid_de;
		$result = $lhg_price_db->get_var($sql);

                #echo "RES: $result <br>";
                #echo "PLK: ".$permalink_de ."<br>";


                if ($result != $permalink_de) {
                        #echo "Update needed";
                        //write permalink to DB

                        $sql = "UPDATE lhgtransverse_posts SET `permalink_de` = \"".$permalink_de."\" WHERE postid_de = ".$postid_de;
			$result = $lhg_price_db->query($sql);

		}

	}
   }

}

function lhg_get_postid_de_from_com( $postid_com ) {


        global $lhg_price_db;
        $sql = "SELECT postid_de FROM `lhgtransverse_posts` WHERE postid_com = ".$postid_com;
	$result = $lhg_price_db->get_var($sql);

        $postid_de = $result;

        return ( $postid_de );
}

function lhg_get_postid_com_from_de( $postid_de ) {


        global $lhg_price_db;
        $sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE postid_de = ".$postid_de;
	$result = $lhg_price_db->get_var($sql);

        $postid_com = $result;

        return ( $postid_com );
}


function lhg_voted_donation (  ) {

        global $wpdb;
        global $donation;

        $i=1;
        while (  $i < 8 ) {


		/*
                $donation_detail = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->usermeta WHERE (meta_key = 'user_donation_target') AND (meta_value = '".$i."')
                comment_post_ID = %d AND (comment_approved = '1' OR ( user_id = %d AND comment_approved = '0' ) )  ORDER BY comment_date_gmt", $post_ID, $user_ID));
                */
        	//$donation_vote_d = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE (meta_key = 'user_donation_target') AND (meta_value = %d ) AND (user_id != 1)", $i));

        	$donation_vote = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->usermeta WHERE (meta_key = 'user_donation_target') AND (meta_value = %d )  AND (user_id != 1)", $i));
                //print_r($donation_vote);
                //echo "<br>I: $i  - ";
                //var_dump($donation_vote_d);
                //echo " - ";
                //print_r($donation_vote);
                //echo "Name ".$donation[$i]["Name"];
                $summed_donations[$i]=$donation_vote;

                $i++;

	}

        return $summed_donations;
}

function lhg_most_voted ( $votes ) {

        $i=0;
        $most_voted        = 0;
        $most_voted_count = 0;
        while (  $i < sizeof($votes)+1 ) {

                if ($votes[$i] > $most_voted_count ) {
                        $most_voted_count = $votes[$i];
                        $most_voted = $i;
		}

                $i++;
	}

        return $most_voted;
}

function lhg_most_voted_percent ( $votes ) {

        $i=0;
        $most_voted           = 0;
        $most_voted_count     = 0;
        $most_voted_count_sum = 0;
        while (  $i < sizeof($votes) +1 ) {

                if ($votes[$i] > $most_voted_count ) {
                        $most_voted_count = $votes[$i];
                        $most_voted = $i;
		}

                $most_voted_count_sum += $votes[$i];
                $i++;
	}

        //echo "Perc: $most_voted_count / $most_voted_count_sum";

        return number_format( ($most_voted_count/$most_voted_count_sum)*100.00 , 1) ;
}


// select donation target





function lhg_user_tooltip ( $uid ) {
        global $lang;
        if ( $lang == "de") return;

        global $txt_avt_reguser;
        global $txt_avt_karmapoints;
        global $txt_avt_rank;
        global $txt_avt_click;

	#$karma = cp_getPoints( $uid );
	$karma = lhg_get_karma( $uid );
        //$level = lhg_get_rank_level( $uid );
        $rank_txt = cp_module_ranks_getRank($public_user_ID);
	$user = get_userdata( $uid );
    	$displayname = $user->display_name;

        if ($displayname == "admin") $displayname = "LHG-Team";
        //if ($displayname == "") $displayname = $user->user_nicename;
        //if ($displayname == "") $displayname = $user->user_login;
        //if ($displayname == "") $displayname = $user->first_name . " " . $user->last_name;
        //if ($displayname == "") $displayname = "???";
        $tooltip = "$txt_avt_reguser: $displayname
$txt_avt_karmapoints: $karma
$txt_avt_rank: $rank_txt

($txt_avt_click)";
return $tooltip;

}

function lhg_comment_percent_bar ( $uid ) {
        global $lang;

        if ($lang == "de") return;

	#$karma = cp_getPoints( $uid );
	$karma = lhg_get_karma( $uid );

        $level = lhg_get_rank_level( $uid );
        $rank_txt = cp_module_ranks_getRank($public_user_ID);
        $max_rank = 3;
        $tooltip = lhg_user_tooltip( $uid ) ;

        $output  = '<div class="comment_percent_bar">';

        $output .= '<div class="rateline" style="border: 0px solid #000;" title="'.$tooltip.'">
                       <div style="float: left;"></div>

                       <div class="outerbox" style="background-color: #fff; width: 54px; float: left; margin: 4px 0px; border: 1px solid #eee;">
                         <div class="box" style="border: 0px solid #088; background-color: #2b8fc3; height: 6px; width: '.(100*$level/$max_rank).'%;" >
                         </div>
                       </div>
                     </div>';

        //$output .= ''.$rank_txt.'';
        $output .= '</div>';
        return $output;
}

function lhg_get_rank_level ( $uid ) {

        if (!function_exists('cp_getPoints') ) return;

	#$karma = cp_getPoints( $uid );
        $karma = lhg_get_karma( $uid );
        if ($karma < 1000){ $karma_rank_total = 1000;  $rank_level = 2; }
        if ($karma < 100) { $karma_rank_total = 100 ; $rank_level = 1; }

        return $rank_level;
}

function lhg_get_karma_threshold ( $rank_level ) {

        if ($rank_level == 1 ) { $needed_points = 100; }
        if ($rank_level == 2 ) { $needed_points = 1000; }

        return $needed_points;
}


function lhg_get_comments_number ( $post_ID  ) {
        global $lang;
        global $region;
        $user_ID = get_current_user_id();

        $number = lhg_comment_language_filter ( $post_ID, $user_ID , $lang , $region );

        return $number;

}

//get number of comments associated with language from priceDB
function lhg_get_comment_number_by_language ( $post_id, $user_ID , $lang , $region ) {
        global $lhg_price_db;

        #print "LG: $lang - REG: $region <br>";

        #on .de server
        $number = "-";
        if ($lang == "de") {
		if ( $region == "de" )    $sql = "SELECT post_comments_num_de    FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "com" )   $sql = "SELECT post_comments_num_com   FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "co.uk" ) $sql = "SELECT post_comments_num_com   FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "ca" )    $sql = "SELECT post_comments_num_com   FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "fr" )    $sql = "SELECT post_comments_num_fr    FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "es" )    $sql = "SELECT post_comments_num_es    FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "it" )    $sql = "SELECT post_comments_num_it    FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "nl" )    $sql = "SELECT post_comments_num_nl    FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "in" )    $sql = "SELECT post_comments_num_com   FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "co.jp" ) $sql = "SELECT post_comments_num_co_jp FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
		if ( $region == "cn" )    $sql = "SELECT post_comments_num_cn    FROM `lhgtransverse_posts` WHERE postid_de = ".$post_id;
	}

        if ($lang == "en") {
		if ( $region == "de" )    $sql = "SELECT post_comments_num_de    FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "com" )   $sql = "SELECT post_comments_num_com   FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "co.uk" ) $sql = "SELECT post_comments_num_com   FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "ca" )    $sql = "SELECT post_comments_num_com   FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "fr" )    $sql = "SELECT post_comments_num_fr    FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "es" )    $sql = "SELECT post_comments_num_es    FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "it" )    $sql = "SELECT post_comments_num_it    FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "nl" )    $sql = "SELECT post_comments_num_nl    FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "in" )    $sql = "SELECT post_comments_num_com   FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "co.jp" ) $sql = "SELECT post_comments_num_co_jp FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
		if ( $region == "cn" )    $sql = "SELECT post_comments_num_cn    FROM `lhgtransverse_posts` WHERE postid_com = ".$post_id;
	}

        #print "SQL: $sql<br>";

    	$number = $lhg_price_db->get_var($sql);
        return $number;
}


//calcualte number of comments valid for given language/region combination
function lhg_comment_language_filter ( $post_ID, $user_ID , $lang , $region ) {

                global $wpdb;

                #echo "UID: $user_ID";
                #echo "PID: $post_ID";

		$comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND (comment_approved = '1' OR ( user_id = %d AND comment_approved = '0' ) )  ORDER BY comment_date_gmt", $post_ID, $user_ID));

                //print_r($comments);

                $a_size = sizeof($comments);
                //echo "<br>size: $a_size <br>";
                #echo "LANG: $lang<br>";
                #echo "Reg: $region<br>";

                $j = 0;
                for ($i = 0; $i < $a_size; $i++){
	                $cid = $comments[$i]->comment_ID;
        	        #echo "CID:".$cid;
                	$clang = get_comment_meta($cid,'language',true);
                	#echo "Lang: $clang"."<br>";
                        $check = lhg_show_language ( $clang , $region );

                        if ($region == "de") $check = 1; //all comments in german database used
                        #echo "CHK: $check<br>";


                        if ($check == 1) {
                                //$comments_temp[$j]=$comments[$i];
                                $j++;
			}
		}
        return $j;
}

# exctract comment number from WPDB
# needed to fill db from scratch
function lhg_comments_number_from_wpdb( $region , $post_ID) {

                global $lang;
                global $wpdb;

                $comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND (comment_approved = '1')  ORDER BY comment_date_gmt", $post->ID) );

        	//need number here
                $user_ID = 0;
	        $number = lhg_comment_language_filter ( $post_ID, $user_ID , $lang , $region );

		return $number;

}

function lhg_comments_number_language( $comment_lang, $region , $user_ID , $post_ID, $more = false) {

		global $txt_no_respo;
                global $txt_one_resp;
                global $lang;
                #global $region;

                #$post_ID = get_the_ID();

                //echo "PID: $post_ID";
                //echo "UID: $user_ID";
                //echo "Test";

		//$comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND (comment_approved = '1' OR ( user_id = %d AND comment_approved = '0' ) )  ORDER BY comment_date_gmt", $post->ID, $user_ID));

        //need number here
        #$number = lhg_comment_language_filter ( $post_ID, $user_ID , $lang , $region );
        $number = lhg_get_comment_number_by_language ( $post_ID, $user_ID , $lang , $region );
        if ($more == "return_number") return $number;

        #print "DEB: $number<br>";

	if ( $number > 1 )
		$output = str_replace('%', number_format_i18n($number), ( false === $more ) ? __('% Comments') : $more);
		//$output = str_replace('%', number_format_i18n($number), ( false === $more ) ? __('% Comments') : $more);
	elseif ( $number == 0 )
		$output = ( false === $zero ) ? __('No Comments') : $txt_no_respo;
	else // must be one
		$output = ( false === $one ) ? __('1 Comment') : $txt_one_resp;

        //echo "N: $number";
	//echo "O: $output < ";
        if ($more == "shortversion") $output = $number;
        echo $output;
        //apply_filters('comments_number', $output, $number);

}



function lhg_show_language( $comment_lang, $region ) {

        #print "CL: $comment_lang, REG: $region <br>";

        if ($comment_lang == $region) return 1;

        if ($comment_lang == "ja")
                if ($region == "co.jp") return 1;

        if ($comment_lang == "zh")
                if ($region == "cn") return 1;

        if ($comment_lang == "nl")
                if ($region == "nl") return 1;

        if ($comment_lang == "es")
                if ($region == "es") return 1;

        if ($comment_lang == "fr")
                if ($region == "fr") return 1;

        if ($comment_lang == "it")
                if ($region == "it") return 1;

        #print "AA";

        switch ( $region ) {
                case "co.uk":
                case "ca":
                case "in":
                case "com":
                case "en":
                switch ( $comment_lang ) {
        	        case "en":
        	        case "co.uk":
        	        case "uk":
                	case "in":
                	case "com":
                	case "ca":
                        case "":
                        return 1;

                        case "es":
                        case "ja":
                        case "zh":
                        case "nl":
                        case "fr":
                        case "it":
                        return 0;
		}
                case "de": return 1;
	}

        //ToDo: check, if language was enabled by user

        #print "HERE!<br>";

        //nothing found. Do not show:
        return 0;
}


function lhg_intelligent_answer_buttons() {
?>
<script type="text/javascript">
(function($) {
$(document).ready(function() {
$(".comment-reply-link").click( function() {
    $(".comment-reply-link").show();
    $(this).hide();
});

    //when cancel button is clicked reshow reply button
$("#cancel-comment-reply-link").click( function() {
    $(".comment-reply-link").show();
});

})
})(jQuery);
</script>

<?php
}
add_action( 'wp_head', 'lhg_intelligent_answer_buttons' );

function lhg_get_rating_id_by_user_and_post ( $userid, $postid ) {

	global $wpdb;
	$filter = $wpdb->get_results("SELECT comment_id FROM $wpdb->comments WHERE comment_post_ID = '$postid' AND user_id = '$userid' ");
        $cid = $filter[0]->comment_id;
        $rid = get_comment_meta($cid,'rating-id',true);
        return $rid;
}

function lhg_get_comment_id_by_rating_id ( $ratingid ) {

	global $wpdb;
	$filter = $wpdb->get_results("SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'rating-id' AND meta_value = $ratingid ");
        $cid = $filter[0]->comment_id;
        return $cid;
}


function lhg_get_lang_url_from_region ( $region ) {
        //if unknown, don't output = input
        $lang = $region;
        if ($region == "co.uk") $lang = "uk";
        if ($region == "com") $lang = "";
        if ($region == "co.jp") $lang = "ja";
        if ($region == "cn") $lang = "zh";

        return $lang;
}

function lhg_get_lang_from_region ( $region ) {
        //if unknown, don't output = input
        $lang = $region;
        if ($region == "co.uk") $lang = "uk";
        if ($region == "co.jp") $lang = "ja";
        if ($region == "cn") $lang = "zh";

        return $lang;

}


// language in user column
//add columns to User panel list page
function add_user_columns( $defaults ) {
     $defaults['language'] = __('Language', 'user-column');
     //$defaults['title'] = __('Title', 'user-column');
     return $defaults;
}
function add_custom_user_columns($value, $column_name, $id) {
      $flag_url="/wp-content/plugins/qtranslate/flags/";
      if( $column_name == 'language' ) {
		$lang = get_the_author_meta( 'user_language', $id );
                if ($lang == "com") return '<img src="'.$flag_url."us.png".'">';
                if ($lang == "ca") return '<img src="'.$flag_url."ca.png".'">';
                if ($lang == "zh") return '<img src="'.$flag_url."zh.png".'">';
                if ($lang == "fr") return '<img src="'.$flag_url."fr.png".'">';
                if ($lang == "in") return '<img src="'.$flag_url."in.png".'">';
                if ($lang == "it") return '<img src="'.$flag_url."it.png".'">';
                if ($lang == "jp") return '<img src="'.$flag_url."jp.png".'">';
                if ($lang == "uk") return '<img src="'.$flag_url."uk.png".'">';
                if ($lang == "es") return '<img src="'.$flag_url."es.png".'">';
                if ($lang == "nl") return '<img src="'.$flag_url."nl.png".'">';
                //default
                return '<img src="'.$flag_url."us.png".'">';

      }
}

function lhg_show_flag_by_lang ($lang) {
      $flag_url="/wp-content/plugins/qtranslate/flags/";

      if ($lang == "com") return '<img src="'.$flag_url."us.png".'">';
      if ($lang == "ca") return '<img src="'.$flag_url."ca.png".'">';
      if ($lang == "zh") return '<img src="'.$flag_url."zh.png".'">';
      if ($lang == "fr") return '<img src="'.$flag_url."fr.png".'">';
      if ($lang == "in") return '<img src="'.$flag_url."in.png".'">';
      if ($lang == "it") return '<img src="'.$flag_url."it.png".'">';
      if ($lang == "jp") return '<img src="'.$flag_url."jp.png".'">';
      if ($lang == "uk") return '<img src="'.$flag_url."uk.png".'">';
      if ($lang == "es") return '<img src="'.$flag_url."es.png".'">';
      if ($lang == "nl") return '<img src="'.$flag_url."nl.png".'">';
      if ($lang == "de") return '<img src="'.$flag_url."de.png".'">';
      //default
      return '<img src="'.$flag_url."us.png".'">';

}

add_action('manage_users_custom_column', 'add_custom_user_columns', 15, 3);
add_filter('manage_users_columns', 'add_user_columns', 15, 1);

function add_comment_columns( $columns ) {
     global $lang;
     if ($lang != "de") $columns['language'] = __('Language')." / ".__('Rating');
     if ($lang == "de") $columns['language'] = __('Rating');
     //$defaults['title'] = __('Title', 'user-column');
     return $columns;
}

function add_custom_comment_columns($column_name, $id) {
      global $lang;

      $flag_url="/wp-content/plugins/qtranslate/flags/";
      if( $column_name == 'language' ) {
		$lang   = get_comment_meta( $id, 'language', true );
		$rating_id = get_comment_meta( $id, 'rating-id', true );
                //$rating_result = the_ratings_results($rating_id);


		//$id = intval($rating_id);
                //echo "I: ".$id;
			$rating_result = lhg_get_rating_image_by_rating_id( $rating_id );

                //echo "L: $lang";

                //default
                # show language icon in comment section on servers with multiple
                # language support
                if ($lang != "de") {
                	$out = '<img src="'.$flag_url."us.png".'">';
	                if ($lang == "com") $out= '<img src="'.$flag_url."us.png".'">';
        	        if ($lang == "ca")  $out= '<img src="'.$flag_url."ca.png".'">';
	                if ($lang == "zh")  $out= '<img src="'.$flag_url."cn.png".'">';
        	        if ($lang == "fr")  $out= '<img src="'.$flag_url."fr.png".'">';
	                if ($lang == "in")  $out= '<img src="'.$flag_url."in.png".'">';
        	        if ($lang == "it")  $out= '<img src="'.$flag_url."it.png".'">';
	                if ($lang == "jp")  $out= '<img src="'.$flag_url."jp.png".'">';
        	        if ($lang == "uk")  $out= '<img src="'.$flag_url."uk.png".'">';
	                if ($lang == "es")  $out= '<img src="'.$flag_url."es.png".'">';
        	        if ($lang == "nl")  $out= '<img src="'.$flag_url."nl.png".'">';
	                if ($lang == "de")  $out= '<img src="'.$flag_url."de.png".'">';
                }
                echo $out."<br>".$rating_result;
      }
}

if ( current_user_can( 'administrator' ) )
add_filter('manage_edit-comments_columns', 'add_comment_columns');

if ( current_user_can( 'administrator' ) )
add_filter('manage_comments_custom_column', 'add_custom_comment_columns', 10, 2);

//get rating by rating_id
function lhg_get_rating_by_rating_id ( $ratingid ) {
                global $wpdb;
                $rtg = "n.a.";
		$filter_ratings = $wpdb->get_results("SELECT rating_rating FROM $wpdb->ratings WHERE rating_id = $ratingid ");
		//$rtg = $filter_ratings['rating_rating'];
		$rtg = $filter_ratings[0]->rating_rating;
                if ($rtg == "") $rtg = "n.a.";
                return $rtg;
}

//get rating by rating_id
function lhg_get_rating_image_by_rating_id ( $ratingid ) {
                global $wpdb;
		$filter_ratings = $wpdb->get_results("SELECT rating_rating FROM $wpdb->ratings WHERE rating_id = $ratingid ");
		//$rtg = $filter_ratings['rating_rating'];
		$rtg = $filter_ratings[0]->rating_rating;
		//$rtg3 = $filter_ratings[0];
                //echo "R: $rtg - $rtg2 - $rtg3";
                //print_r ( $filter_ratings );
                //var_dump($filter_ratings);
                $out = lhg_rating_image ( $rtg );
                if ($rtg == "") $out = "n.a.";

                return $out;
}
//              ['']


function lhg_rating_image ( $postratings_rating ) {

        $ratings_max = 5;
        $ratings_image = "stars_crystal";
        $out ="<nobr>";
	for($j=1; $j <= $ratings_max; $j++) {
		if($j <= $postratings_rating) {
			$out .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_on.gif').'" alt="'.sprintf(_n('User Rate This Post %s Star', 'User Rate This Post %s Stars', $postratings_rating, 'wp-postratings'), $postratings_rating).__(' Out Of ', 'wp-postratings').$ratings_max.'" title="'.sprintf(_n('User Rate This Post %s Star', 'User Rate This Post %s Stars', $postratings_rating, 'wp-postratings'), $postratings_rating).__(' Out Of ', 'wp-postratings').$ratings_max.'" class="post-ratings-image" />';
		} else {
			$out .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_off.gif').'" alt="'.sprintf(_n('User Rate This Post %s Star', 'User Rate This Post %s Stars', $postratings_rating, 'wp-postratings'), $postratings_rating).__(' Out Of ', 'wp-postratings').$ratings_max.'" title="'.sprintf(_n('User Rate This Post %s Star', 'User Rate This Post %s Stars', $postratings_rating, 'wp-postratings'), $postratings_rating).__(' Out Of ', 'wp-postratings').$ratings_max.'" class="post-ratings-image" />';
		}
	}
        $out .= "</nobr>";

	return $out;
}

function lhg_update_comment_rating( $rating_id, $comment_rating) {

   global $wpdb;
   $new_values = array( 'rating_rating' => $comment_rating );
   $where = array ( 'rating_id' => $rating_id );
   $wpdb->update( $wpdb->ratings, $new_values, $where);

   $post_id = lhg_get_post_id_by_rating_id ( $rating_id );

   $out = lhg_recalculate_postrating ( $post_id );

   return $post_id;
}

function lhg_get_post_id_by_rating_id ( $ratingid ) {

        global $wpdb;
        $post_id = 0;

	$filter_ratings = $wpdb->get_results("SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'rating-id' AND meta_value = $ratingid ");


	//$filter_ratings = $wpdb->get_results("SELECT rating_rating FROM $wpdb->ratings WHERE rating_id = $ratingid ");
	//$rtg = $filter_ratings['rating_rating'];
	//$rtg = $filter_ratings[0]->rating_rating;
        $comment_id=$filter_ratings[0]->comment_id;

        $filter_ratings2 = $wpdb->get_results("SELECT comment_post_ID FROM $wpdb->comments WHERE comment_ID = '$comment_id'");
        //print_r($filter_ratings2);
        $post_id = $filter_ratings2[0]->comment_post_ID;
        //$post_id = get_
        //echo "RID: $ratingid - PID: $post_id";
        //printr($filter_ratings);
        //echo "PID: $post_id";
        return $post_id;
}

//recalculate the ratings, e.g., after ratings was changed
function lhg_recalculate_postrating ( $postid ) {

        global $wpdb;

        //get all ratings of $postid
	$filter_ratings = $wpdb->get_results("SELECT rating_rating FROM $wpdb->ratings WHERE rating_postid = $postid");

	$total_ratings = $wpdb->get_var("SELECT COUNT(rating_rating) FROM $wpdb->ratings WHERE rating_postid = $postid");
	//$total_users   = $wpdb->get_var("SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'ratings_users'");
	$total_score   = $wpdb->get_var("SELECT SUM((rating_rating+0.00)) FROM $wpdb->ratings WHERE rating_postid = $postid");
        //echo "TR: $total_ratings <br>";
	if($total_ratings == 0) {
		$total_average = 0;
	} else {
		$total_average = $total_score/$total_ratings;
	}

        //print_r($filter_ratings);
        update_post_meta ($postid,'ratings_average',$total_average);
        update_post_meta ($postid,'ratings_score',$total_score);
        update_post_meta ($postid,'ratings_users',$total_ratings);

         return $postid; // . "total_avg: $total_average, score: $total_score, users: $total_ratings -";
       ;

}

//howdy -> hello
add_filter( 'admin_bar_menu', 'howdy_to_hello', 25 );
	function howdy_to_hello( $wp_admin_bar ) {
	    $my_account = $wp_admin_bar->get_node('my-account');
	    $newtitle = str_replace( 'Howdy,', 'Hello!', $my_account->title );
	    $wp_admin_bar->add_node( array(
	        'id' => 'my-account',
	        'title' => $newtitle,
	    ));
	}


function lhg_get_locale_from_id ( $user_id ) {

  //get user locale
  $user_language = get_user_meta($user_id, 'user_language',true);
  //echo "UL: ".$user_language;

  if($user_language == "ca")  return "ca";
  if($user_language == "zh")  return "zh";
  if($user_language == "fr")  return "fr";
  if($user_language == "in")  return "in";
  if($user_language == "it")  return "it";
  if($user_language == "jp")  return "ja";
  if($user_language == "uk")  return "uk";
  if($user_language == "es")  return "es";
  if($user_language == "nl")  return "nl";
  if($user_language == "com") return "";

  return "";
}


function lhg_get_locale_from_lang( $user_language ) {

  $user_locale = "en_US"; // default
  if($user_language == "ca")  $user_locale = "en_US";
  if($user_language == "zh")  $user_locale = "zh_CN";
  if($user_language == "fr")  $user_locale = "fr_FR";
  if($user_language == "in")  $user_locale = "en_US";
  if($user_language == "it")  $user_locale = "it_IT";
  if($user_language == "jp")  $user_locale = "ja";
  if($user_language == "uk")  $user_locale = "en_US";
  if($user_language == "es")  $user_locale = "es_ES";
  if($user_language == "nl")  $user_locale = "nl_NL";
  if($user_language == "com") $user_locale = "en_US";

  return $user_locale;

}


// set profile language
if ($lang != "de")
add_filter( 'personal_options' , 'lhg_set_language' );


function lhg_set_language() {

	global $lang_array;


	$user_id = get_current_user_id();
  	//get user locale with user id
  	$user_language = get_user_meta($user_id,'user_language',true);

        //debug:
	//echo "UL1: $user_language";

  	if(!in_array($user_language, $lang_array) ){
   		//add default locale
                #echo "Unknown language ($user_language)";
		add_user_meta($user_id, 'user_language', 'com');
	   	$user_language = 'com';
  	}else {
                //echo "Found: $user_language";
        }

?><tr>
 <th scope="row"> Language / Country

 </th>
 <td>
  <select name="lhg_user_language">
<?php
lhg_language_selector ($user_language);
?>
  </select>
 </td>
</tr>
<?php
}


function lhg_rating_selector ($rating) {
$rating=intval($rating);
//echo "rtg: $rating";
?>
   <option value="1" <?php selected(1, $rating); ?>     >1</option>
   <option value="2" <?php selected(2, $rating); ?>     >2</option>
   <option value="3" <?php selected(3, $rating); ?>     >3</option>
   <option value="4" <?php selected(4, $rating); ?>     >4</option>
   <option value="5" <?php selected(5, $rating); ?>     >5</option>
<?php

}


//update data according to selection
add_action('personal_options_update','lhg_update_language_settings');


function lhg_language_selector ($user_language) {
global $lang_array;

        if(!in_array($user_language, $lang_array) ){
                $error = "- Error: Lang not found (".$user_language.")";
		$user_language = "com";
        }

?>
   <option value="ca" <?php selected('ca',$user_language); ?>     >Canada</option>
   <option value="zh" <?php selected('zh',$user_language); ?>     >China</option>
   <option value="fr" <?php selected('fr',$user_language); ?>     >France</option>
   <!-- option value="de" <?php selected('de',$user_language); ?> >Germany</option -->
   <option value="in" <?php selected('in',$user_language); ?>     >India</option>
   <option value="it" <?php selected('it',$user_language); ?>     >Italy</option>
   <option value="jp" <?php selected('jp',$user_language); ?>     >Japan</option>
   <option value="uk" <?php selected('uk',$user_language); ?>     >United Kingdom</option>
   <option value="es" <?php selected('es',$user_language); ?>     >Spain</option>
   <option value="nl" <?php selected('nl',$user_language); ?>     >Nederlands</option>
   <option value="com" <?php selected('com',$user_language); ?>    >United States of America <?php echo $error; ?></option>
<?php

}


function lhg_update_language_settings(){

  global $lang_array;

  //echo "Found: ".$_POST['lhg_user_language'];

  if(!isset($_POST['lhg_user_language']))
   return;
  //get user id
  $user_id = get_current_user_id();
  //validate submitted value otherwise set to default
  $user_language = in_array($_POST['lhg_user_language'], $lang_array ) ? $_POST['lhg_user_language'] : 'com';
  //update user locale
  update_user_meta($user_id, 'user_language', $user_language);


  if($user_language == "ca")  $user_locale = "en_US";
  if($user_language == "zh")  $user_locale = "zh_CN";
  if($user_language == "fr")  $user_locale = "fr_FR";
  if($user_language == "in")  $user_locale = "en_US";
  if($user_language == "it")  $user_locale = "it_IT";
  if($user_language == "jp")  $user_locale = "ja";
  if($user_language == "uk")  $user_locale = "en_US";
  if($user_language == "es")  $user_locale = "es_ES";
  if($user_language == "nl")  $user_locale = "nl_NL";
  if($user_language == "com") $user_locale = "en_US";

  update_user_meta($user_id, 'wp_native_dashboard_language', $user_locale);

        //echo "update: $user_language";
 }

//switch language according to selection
add_filter('locale','lhg_change_locale', 10000, 1);
#add_filter('locale', array(&$this, 'lhg_change_locale'), 19999);
#add_action('init', 'lhg_change_locale');
#add_action('admin_init', 'on_admin_init');
#add_action('admin_menu', array(&$this, 'on_admin_menu'));

function lhg_change_locale(){
  //get user id
  $user_id = get_current_user_id();
  //get user locale with user id
  $user_language = get_user_meta($user_id,'user_language',true);
  if(!$user_language){
   //add default locale
   $user_locale = 'en_US';
  }

  if($user_language == "ca")  $user_locale = "en_US";
  if($user_language == "zh")  $user_locale = "zh_CN";
  if($user_language == "fr")  $user_locale = "fr_FR";
  if($user_language == "in")  $user_locale = "en_US";
  if($user_language == "it")  $user_locale = "it_IT";
  if($user_language == "jp")  $user_locale = "ja";
  if($user_language == "uk")  $user_locale = "en_US";
  if($user_language == "es")  $user_locale = "es_ES";
  if($user_language == "nl")  $user_locale = "nl_NL";
  if($user_language == "com") $user_locale = "en_US";


  //use plugin to set backend language
  $u = wp_get_current_user();

  //set locale
  //echo "Locale: $user_locale";
  define('WPLANG', 'es_ES');

  //load_plugin_textdomain('wp-native-dashboard', false, dirname( plugin_basename(__FILE__) ) . '/i18n' );


  if ( !defined('WP_LANG_DIR') )
	define('WP_LANG_DIR', WP_CONTENT_URL.'/languages');
  //echo "DIR: ".WP_LANG_DIR;

  return $user_locale;
 }



// comment meta data

add_action( 'comment_form_logged_in_after', 'plugin_additional_fields' );
add_action( 'comment_form_after_fields', 'plugin_additional_fields' );

function plugin_additional_fields() {

        global $region;
        $lang = lhg_get_lang_from_region($region);

        //redirect to correct language after comment submittal
        $langurl = "/".lhg_get_lang_url_from_region ($region);
        if ($langurl == "/") $langurl = "";
        if ($langurl == "/de") $langurl = "";
        $comment_url = $langurl.$_SERVER['REQUEST_URI'];

        //echo "L: $lang R: $region";

        //store language
        echo '<input id="language" name="language" type="hidden" value="'.$lang.'"  />';
        echo '<input id="redirect_to" name="redirect_to" type="hidden" value="'.$comment_url.'"  />';

	/*
        echo '<p class="comment-form-rating">'.
	'<label for="commenters-rating">' . __( 'Rating' ) . '</label>'.
        '<select name="commenters-rating">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
        </select>';
        */
        // '<input id="commenters-rating" name="commenters-rating" type="text" size="30" tabindex="5" /></p>';

}

add_action( 'comment_post', 'plugin_save_comment_meta_data' );
function plugin_save_comment_meta_data( $comment_id ) {
	if ( ( isset( $_POST['language'] ) ) && ( $_POST['language'] != '') ) {
		$cost = wp_filter_nohtml_kses($_POST['language']);
		add_comment_meta( $comment_id, 'language', $cost );
	}


        $rating_id = get_rating_id($comment_id);
        //echo "Rating ID: $rating_id";
	add_comment_meta( $comment_id, 'rating-id', $rating_id );
}

function get_rating_id($comment_id){
        $found_id = 0;

                /*
                    get rating-id of comment author
                */

		//global $wp_query, $withcomments, $post, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity, $overridden_cpage;
                global $wpdb, $post, $user_ID;

		$cdate=get_comment_date('',$comment_id);
                $cip=get_comment_author_IP( $comment_id);
                //echo "Post:  - UserID: $user_ID - IP: $cip - Date: $cdate<br>";

                $pid=get_the_ID();
                $postratings_where = "AND rating_postid = $pid";


                //$postratings_logs = $wpdb->get_results("SELECT * FROM $wpdb->ratings WHERE 1=1 $postratings_where ORDER BY $postratings_sortby $postratings_sortorder LIMIT $offset, $postratings_log_perpage");
                $postratings_logs = $wpdb->get_results("SELECT * FROM $wpdb->ratings WHERE 1=1 $postratings_where");


                //print "HIER";
                //print_r($wpdb);


                if($postratings_logs) {
                        $found = 0;
	                foreach($postratings_logs as $postratings_log) {
                           if ($found == 0) {

                                $postratings_date = mysql2date(sprintf(__('%s', 'wp-postratings'), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $postratings_log->rating_timestamp));
				$postratings_rating = intval($postratings_log->rating_rating);
                                $postratings_ip = $postratings_log->rating_ip;
                                $postratings_id = $postratings_log->rating_id;

                                if ($postratings_date == $cdate)
                                if ($postratings_ip == $cip)
                                {

                                        /*echo "Found!<br>";
        	                        echo "<p>
                	                PRD: $postratings_date<br>";
                        	        echo "Rating: $postratings_rating<br>
                                        ID:  $postratings_id<br>
                                        IP:  $postratings_ip<br> -<br>";
                                        */

                                $found_id = $postratings_id;
                                $found=1;
		       		}

   	       		   }
	       		}
		}


        return $found_id;

}

//allow users (contributors) to edit their comments
//$contributor= get_role('contributor');
//$contributor->add_cap('edit_comment');

//log online users
add_action('wp', 'update_online_users_status');
function update_online_users_status(){

  if(is_user_logged_in()){

    // get the online users list
    if(($logged_in_users = get_transient('users_online')) === false) $logged_in_users = array();

    $current_user = wp_get_current_user();
    $current_user = $current_user->ID;  
    $current_time = current_time('timestamp');

    if(!isset($logged_in_users[$current_user]) || ($logged_in_users[$current_user] < ($current_time - (15 * 60)))){
      $logged_in_users[$current_user] = $current_time;
      set_transient('users_online', $logged_in_users, 30 * 60);
    }

  }
}


function catch_that_image() {
  global $post, $posts;
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  $first_img = $matches [1] [0];

  if(empty($first_img)){ //Defines a default image
    $first_img = "/images/default.jpg";
  }
  return $first_img;
}


// google analytics for backend
function lhg_analytics_backend ($text)
	{

$google_analytics_com ="<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-35017343-3', 'linux-hardware-guide.com');
  ga('send', 'pageview');

</script>";


$google_analytics_de ="
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-35017343-1', 'linux-hardware-guide.de');
  ga('send', 'pageview');

</script>
";
        global $lang;
    	if ($lang == "en") return $text.$google_analytics_com;
    	if ($lang == "de") return $text.$google_analytics_de;
    }
	
	//
        add_filter('admin_footer_text', 'lhg_analytics_backend');


function get_region() {
        //print "get region: ";



	global $wp_query;
        global $lang;
        global $url_lang;

	//$val = $wp_query->query_vars['lang'];
        //$cval = read_cookie("lang");

        if (!function_exists('qtrans_getLanguage')){
        	//echo "AA";
        	return "de";
        }else{
        	$val = qtrans_getLanguage();
        }

        $url_lang = "/".$val;
        if ($url_lang == "/us") $url_lang = "";
        //if ($url_lang == "/it") $val = "it";
        //if ($url_lang == "/fr") $val = "fr";

        //if ($lang != $val) $val = $lang;
        //print "URLlang: $url_lang<br>";
        //print "SITEURL: $siteurl<br>";
        //print "Lang: $lang<br>";
        //print "$val <br>";

        if ($val == "ca") {
                return "ca";
	}elseif ($val == "en") {
                return "com";
	}elseif ($val == "uk") {
                return "co.uk";
	}elseif ($val == "es") {
                return "es";
	}elseif ($val == "fr") {
                return "fr";
	}elseif ($val == "it") {
                return "it";
	}elseif ($val == "ja") {
                return "co.jp";
	}elseif ($val == "br") {
                return "com.br";
	}elseif ($val == "zh") {
                return "cn";
	}elseif ($val == "in") {
                return "in";
	}elseif ($val == "nl") {
                return "nl";
	}elseif ($val == "de") {
                return "de";

	//if no url parameter set, check for cookie
        /*
        }elseif ($cval == "en") {
                return "com";
	}elseif ($cval == "uk") {
                return "co.uk";
	}elseif ($cval == "de") {
                return "de";
	}elseif ($cval == "fr") {
                return "fr";
	}elseif ($cval == "es") {
                return "es";
	}elseif ($cval == "it") {
                return "it";
	}elseif ($cval == "jp") {
                return "co.jp";
	}elseif ($cval == "br") {
                return "com.br";
	}elseif ($cval == "cn") {
                return "cn";
	}elseif ($cval == "ca") {
                return "ca";
	}elseif ($cval == "in") {
                return "in";
	}elseif ($lang == "de") {
                return "de";
        */
        }

        // default to com

        else{
                return "com";
        }

}

function get_id( ) {

                $region=get_region();
    		if ($region == "ca"){
			$aws_partner_id ="linuhardgui01-20";
		}elseif ($region == "co.uk"){
    			$aws_partner_id ="linuhardguid-21";
    		}elseif ($region == "de"){
		    	$aws_partner_id ="linuxnetmagor-21";
    		}elseif ($region == "fr"){
		    	$aws_partner_id ="linuhardgui01-21";
    		}elseif ($region == "es"){
		    	$aws_partner_id ="linuhardgu061-21";
    		}elseif ($region == "it"){
		    	$aws_partner_id ="linuhardgui05-21";
    		}elseif ($region == "co.jp"){
		    	$aws_partner_id ="linuhardgui22-22";
    		//}elseif ($region == "com.br"){
		//    	$aws_partner_id ="linuhardgui22-22";
    		}elseif ($region == "cn"){
		    	$aws_partner_id ="linuhardgui23-23";
    		}elseif ($region == "in"){
		    	$aws_partner_id ="linuxhardwagu-21";
    		}elseif ($region == "nl"){
		    	$aws_partner_id ="linuxnetmagor-21";
	        }else {     // com as default
		    	$aws_partner_id ="linuhardguid-20";
		}

        //print "Region: $region <br>ID: $aws_partner_id<br>";
        return $aws_partner_id;

}

function translate_tag($url){
        # get slug from url
        $explode = explode("/",$url);
        $slug = array_pop($explode);
        $begin = implode ("/",$explode);
        global $lhg_price_db;
        global $region;

	if ($region == "de"){
	        $sql = "SELECT slug_com FROM `lhgtransverse_tags` WHERE slug_de = \"".$slug."\"";
    		$slug_translated = $lhg_price_db->get_var($sql);
	}else{
	        $sql = "SELECT slug_de FROM `lhgtransverse_tags` WHERE slug_com = \"".$slug."\"";
    		$slug_translated = $lhg_price_db->get_var($sql);
        }

        if ($slug_translated == ""){
                $url = "";
        }else{
                $url = $begin."/".$slug_translated;
        }

        #echo "URL: $url<br>";
        return $url;

        # old stuff

        global $region;
	if ($region == "de"){
	        //$url = str_replace("zoll","inch",$url);
	        $url = str_replace("ueberwachungskamera","surveillance-camera",$url);
	        $url = str_replace("relaiskarte","relay-card",$url);
	        $url = str_replace("nachtmodus","nightvision",$url);
	        $url = str_replace("ip-kamera","ip-camera",$url);
	        $url = str_replace("ansteuerung","control",$url);
	        $url = str_replace("grafikkarte","graphiccard",$url);
	        $url = str_replace("brenner","writer",$url);
	        $url = str_replace("multifunktionsdrucker","all-in-one-printer",$url);
	        $url = str_replace("kinder","children",$url);
	        $url = str_replace("farblaserdrucker","colorlaserprinter",$url);
	        $url = str_replace("laserdrucker","laserprinter",$url);
	        $url = str_replace("kopierer","copier",$url);
	        if ($url == "lautsprecher") $url = str_replace("lautsprecher","speaker",$url);
	        $url = str_replace("mikroskop","microscope",$url);
	        $url = str_replace("fernbedienung","remotecontrol",$url);
	        $url = str_replace("netzwerkkarte","networkcard",$url);
	        $url = str_replace("netzwerk","network",$url);
	        $url = str_replace("festplatte","harddisk",$url);
	        $url = str_replace("dj-konsole","dj-console",$url);
	        $url = str_replace("videoschnitt","video-editing",$url);
	        $url = str_replace("adapter","converter",$url); //no german translation
	        $url = str_replace("seriell","serial",$url); 
	        $url = str_replace("kopfhorer","headphone",$url); //
	        $url = str_replace("kanone","cannon",$url); //
	        $url = str_replace("soundkarte","soundcard",$url); //
	        $url = str_replace("stromversorgung","power-supply",$url); //
	        $url = str_replace("tastatur","keyboard",$url); //
	        $url = str_replace("sockel-am3","socket-am3",$url); //
	        $url = str_replace("sockel-am3am2","socket-am3am2",$url); //
	        $url = str_replace("sockel-1150","socket-lga1150",$url); //
                $url = str_replace("maus","mouse",$url);
                $url = str_replace("fingerabdruck","finger-print-scanner",$url);
                $url = str_replace("raketenwerfer","rocket-launcher",$url);
                $url = str_replace("vorverstarker","preamp",$url);
                if ($url == "intern") $url = str_replace("intern","internal",$url);
                $url = str_replace("laserpointer","laser-pointer",$url);
                $url = str_replace("extern","external",$url);
                $url = str_replace("amateurfunk","ham-radio",$url);
                $url = str_replace("dualband","dual-band",$url);
                $url = str_replace("handfunkgerat","ham-radio",$url);
                $url = str_replace("funk","ham-radio",$url);
                $url = str_replace("gyroskopisch","gyroscopic",$url);
                $url = str_replace("schnurlos","wireless",$url);
                $url = str_replace("7-1-surround","7-1-surround-sound",$url);
	        $url = str_replace("drucker","printer",$url);
	        $url = str_replace("filmscanner","film-scanner",$url);
	        $url = str_replace("diascanner","slide-scanner",$url);
	        $url = str_replace("oszilloskop","oscilloscope",$url);
	        $url = str_replace("schallplattenspieler","record-player",$url);
	        $url = str_replace("schallplatte","record-player",$url);
                $url = str_replace("sockel-lga1150","socket-lga1150",$url);
                $url = str_replace("beschriftungsgerat","label-maker",$url);
                $url = str_replace("mikrofon","microphone",$url);
                $url = str_replace("steckdosenleiste","multiple-socket",$url);
                $url = str_replace("wetterstation","weather-station",$url);
	        $url = str_replace("plattenspieler","turntable",$url);
                $url = str_replace("dokumenten-kamera","document-camera",$url);
                $url = str_replace("14-1-zoll","14-1-inch",$url);
                $url = str_replace("endoskop","endoscope",$url);



                //not yet available for .com pages
                $url = str_replace("intel-xeon","../",$url);
                $url = str_replace("cpu","../",$url);
                //$url = str_replace("satellite","../",$url);
                $url = str_replace("nexus","../",$url);
                //$url = str_replace("modem","../",$url);
	        $url = str_replace("sockel-am3am2","socket-am3am2",$url); //
                $url = str_replace("sockel-c32","../",$url);
                $url = str_replace("sockel-g32","../",$url);
                $url = str_replace("sockel-939","../",$url);
                $url = str_replace("intel-celeron","../",$url);
                //$url = str_replace("ubuntu-touch","../",$url);
                $url = str_replace("intel-core-i7","../",$url);
                $url = str_replace("intel-core2-duo","../",$url);
                $url = str_replace("intel-core-i3","../",$url);
                $url = str_replace("sockel-2011","../",$url);
                $url = str_replace("sempron","../",$url);
                //$url = str_replace("fujitsu","../",$url);
                $url = str_replace("lesegerat","../",$url);
                //$url = str_replace("thinkpad","../",$url);
                //$url = str_replace("iconia","../",$url);
                //$url = str_replace("wortmann","../",$url);
                //$url = str_replace("packard-bell","../",$url);
                $url = str_replace("athlon-ii","../",$url);
                $url = str_replace("14-1-inch","../",$url);
                $url = str_replace("17-3-inch","../",$url);
                $url = str_replace("athlon","../",$url);
                $url = str_replace("amd-opteron","../",$url);
		//$url = str_replace("lifetec","../",$url);
                //$url = str_replace("sharkoon","../",$url);
                $url = str_replace("phenom-ii","../",$url);
                $url = str_replace("intel-pentium","../",$url);
                //$url = str_replace("kinivo","../",$url);
                //$url = str_replace("convertible","../",$url);
                //$url = str_replace("oszilloskop","../",$url);
                //$url = str_replace("startech-com","../",$url);
                //$url = str_replace("rigol","../",$url);
                //$url = str_replace("usbtmc","../",$url);
                //$url = str_replace("label","../",$url);
                //$url = str_replace("lg","../",$url); //breaks elgato
                //$url = str_replace("intenso","../",$url);
                //$url = str_replace("diascanner","../",$url);
                //$url = str_replace("scsi","../",$url);
                //$url = str_replace("filmscanner","../",$url);
                //$url = str_replace("reflecta","../",$url);
                //$url = str_replace("lp","../",$url);
                //$url = str_replace("schallplatte","../",$url);
                //$url = str_replace("plattenspieler","../",$url);
                //$url = str_replace("sockel-lga1150","../",$url);
                //$url = str_replace("ubuntu-phone","../",$url);
                //$url = str_replace("ricoh","../",$url);


        }

	if ($region != "de"){
	        //$url = str_replace("inch","zoll",$url);
	        $url = str_replace("surveillance-camera","uberwachungskamera",$url);
	        $url = str_replace("relay-card","relaiskarte",$url);
	        $url = str_replace("nightvision","nachtmodus",$url);
	        $url = str_replace("ip-camera","ip-kamera",$url);
	        $url = str_replace("controller","ansteuerung",$url);
	        $url = str_replace("remotecontrol","fernbedienung",$url);
	        $url = str_replace("control","ansteuerung",$url);
	        $url = str_replace("graphiccard","grafikkarte",$url);
	        $url = str_replace("writer","brenner",$url);
	        $url = str_replace("all-in-one-printer","multifunktionsdrucker",$url);
	        $url = str_replace("children","kinder",$url);
	        $url = str_replace("colorlaserprinter","farblaserdrucker",$url);
	        $url = str_replace("laserprinter","laserdrucker",$url);
	        $url = str_replace("copier","kopierer",$url);
	        $url = str_replace("speaker","lautsprecher",$url);
	        $url = str_replace("microscope","mikroskop",$url);
	        $url = str_replace("remotecontrol","fernbedienung",$url);
	        $url = str_replace("networkcard","netzwerkkarte",$url);
	        $url = str_replace("network","netzwerk",$url);
	        $url = str_replace("harddisk","festplatte",$url);
	        $url = str_replace("dj-console","dj-konsole",$url);
	        $url = str_replace("video-editing","videoschnitt",$url);
	        $url = str_replace("converter","adapter",$url); //no german translation
	        $url = str_replace("serial","seriell",$url); 
	        $url = str_replace("headphone","kopfhorer",$url); //
	        $url = str_replace("cannon","kanone",$url); //
	        $url = str_replace("soundcard","soundkarte",$url); //
	        $url = str_replace("keyboard","tastatur",$url); //
	        $url = str_replace("socket-am3","sockel-am3",$url); //
                $url = str_replace("mouse","maus",$url);
                $url = str_replace("gaming-maus","gaming-mouse",$url); //revert translation!
                $url = str_replace("finger-print-scanner","fingerabdruck",$url);
                $url = str_replace("rocket-launcher","raketenwerfer",$url);
                $url = str_replace("preamp","vorverstarker",$url);
                $url = str_replace("internal","intern",$url);
                $url = str_replace("laser-pointer","laserpointer",$url);
                $url = str_replace("ham-radio","amateurfunk",$url);
                $url = str_replace("dual-band","dualband",$url);
                $url = str_replace("gyroscopic","gyroskopisch",$url);
                $url = str_replace("wireless","schnurlos",$url);
                $url = str_replace("7-1-surround-sound","7-1-surround",$url);
	        $url = str_replace("printer","drucker",$url);
	        $url = str_replace("film-scanner","filmscanner",$url);
	        $url = str_replace("slide-scanner","diascanner",$url);
	        $url = str_replace("oscilloscope","oszilloskop",$url);
                $url = str_replace("external","extern",$url);
                $url = str_replace("socket-lga1150","sockel-lga1150",$url);
	        $url = str_replace("record-player","schallplattenspieler",$url);
	        $url = str_replace("turntable","plattenspieler",$url);
                $url = str_replace("motherboard","mainboard",$url);
                $url = str_replace("label-maker","beschriftungsgerat",$url);
                $url = str_replace("microphone","mikrofon",$url);
	        $url = str_replace("socket-lga1150","sockel-1150",$url); //
	        $url = str_replace("socket-am3am2","sockel-am3am2",$url); //
	        $url = str_replace("power-supply","stromversorgung",$url); //
                $url = str_replace("multiple-socket","steckdosenleiste",$url);
                $url = str_replace("weather-station","wetterstation",$url);
                $url = str_replace("document-camera","dokumenten-kamera",$url);
                $url = str_replace("14-1-inch","14-1-zoll",$url);
                $url = str_replace("endoscope","endoskop",$url);

                #not yet available for .de
                //$url = str_replace("jabra","../",$url);
                //$url = str_replace("somikon","../",$url);
                //$url = str_replace("ipevo","../",$url);
                //$url = str_replace("document-camera","../",$url);
                //$url = str_replace("docking-station","../",$url);


	$url = str_replace("/page/2","",$url);
	$url = str_replace("/page/3","",$url);
	$url = str_replace("/page","",$url);


        }
        //echo "URL2: $url";
        return $url;
}

function translate_category($url){
        global $region;
	if ($region == "de"){
	        $url = str_replace("/drucker/laserdrucker","/printer/laser-printer",$url);
	        $url = str_replace("/printer/laserdrucker","/printer/laser-printer",$url);
	        $url = str_replace("/drucker/Laserdrucker","/printer/laser-printer",$url);
	        $url = str_replace("/drucker/multifunktionsdrucker","/printer/all-in-one-printer",$url);
	        $url = str_replace("/printer/multifunktionsdrucker","/printer/all-in-one-printer",$url);
	        $url = str_replace("/drucker/Multifunktionsdrucker","/printer/all-in-one-printer",$url);
	        $url = str_replace("/drucker","/printer",$url);
	        $url = str_replace("/grafikkarten","/graphiccards",$url);
	        $url = str_replace("/dvd-cd/extern","/dvd-cd/external",$url);
	        $url = str_replace("/dvd-cd/intern","/dvd-cd/internal",$url);
	        $url = str_replace("/sonstiges","/misc",$url);
	        $url = str_replace("/Sonstiges","/misc",$url);
	        $url = str_replace("/ultrabooks-notebooks","/ultrabook",$url);
	        $url = str_replace("/netzwerk","/network",$url);
	        $url = str_replace("/dlan","/powerlan-dlan",$url);
	        $url = str_replace("/stromspar-pc-pc-systeme-2","/low-power-pcs",$url);
                $url = str_replace("/ssd-festplatten","/ssd",$url);
                $url = str_replace("/uberwachungskamera","/cctv",$url);

                //not yet available for .de pages
                $url = str_replace("tablets","../",$url);
                $url = str_replace("cpu","../",$url);

        }

	if ($region != "de"){
	        $url = str_replace("/printer/laser-printer","/drucker/laserdrucker",$url);
	        $url = str_replace("/printer/all-in-one-printer","/drucker/multifunktionsdrucker",$url);
	        $url = str_replace("/printer","/drucker",$url);
	        $url = str_replace("/graphiccards","/grafikkarten",$url);
	        $url = str_replace("/dvd-cd/external","/dvd-cd/extern",$url);
	        $url = str_replace("/dvd-cd/internal","/dvd-cd/intern",$url);
	        $url = str_replace("/misc","/sonstiges",$url);
	        $url = str_replace("/Misc","/sonstiges",$url);
	        $url = str_replace("/ultrabook","/ultrabooks-notebooks",$url);
	        $url = str_replace("/network","/netzwerk",$url);
	        $url = str_replace("/powerlan-dlan","/dlan",$url);
	        $url = str_replace("/low-power-pcs","/stromspar-pc-pc-systeme-2",$url);
                $url = str_replace("/ssd","/ssd-festplatten",$url);
                $url = str_replace("/cctv","/uberwachungskamera",$url);
        }

	$url = str_replace("/page/2","",$url);
	$url = str_replace("/page/3","",$url);
	$url = str_replace("/page","",$url);

        //echo "URL2: $url";
        return $url;
}

function lhg_translate_search_url($url){
        # We can not translate by $region or $lang because these values are set incorrectly by the AJAX request.
        # Instead, we use the HTTP_REFERER to identify the language

	$ref = $_SERVER["HTTP_REFERER"];
        $langurl = "";
        if (strpos($ref, "/fr/") !== false ) $langurl = "fr";
        if (strpos($ref, "/ca/") !== false ) $langurl = "ca";
        if (strpos($ref, "/uk/") !== false ) $langurl = "uk";
        if (strpos($ref, "/es/") !== false ) $langurl = "es";
        if (strpos($ref, "/it/") !== false ) $langurl = "it";
        if (strpos($ref, "/nl/") !== false ) $langurl = "nl";
        if (strpos($ref, "/in/") !== false ) $langurl = "in";
        if (strpos($ref, "/ja/") !== false ) $langurl = "ja";
        if (strpos($ref, "/zh/") !== false ) $langurl = "zh";

	if ($langurl == "") return $url;

        //translate urls
        $url = str_replace("http://192.168.3.113/", "http://192.168.3.113/".$langurl."/",$url);
        $url = str_replace("http://192.168.56.13/", "http://192.168.56.13/".$langurl."/",$url);
        $url = str_replace("http://www.linux-hardware-guide.com/", "http://www.linux-hardware-guide.com/".$langurl."/",$url);
        $url = str_replace("http://linux-hardware-guide.com/", "http://linux-hardware-guide.com/".$langurl."/",$url);

        return $url;
}



function translate_title($title){
        global $region;
        //$region = get_region();
        //echo "LANG: $region, ";
	if ($lang == "de") return $title;

        //global translations
        $title = str_replace("Brenner", "Writer",$title);
        $title = str_replace("interner", "internal",$title);


	if ($region == "com") {
        	return $title;
        }

	if ($region == "co.uk") {
        	$title = str_replace("analog TV", "TV Analogue ",$title);
        	$title = str_replace("Analog TV", "TV Analogue ",$title);
        	$title = str_replace("analog ", "Analogu ",$title);
        	$title = str_replace("Analog ", "Analogue ",$title);
        	$title = str_replace("Analog,", "Analogue,",$title);
        	$title = str_replace("Color", "Colour",$title);


        	return $title;
        }

	if ($region == "fr") {

        # combinations

        	$title = str_replace("Wireless Mobile Broadband Modem", "Modem Sans Fil haut débit mobile",$title);
        	$title = str_replace("Server Adapter", "Adapteur Serveur",$title);
        	$title = str_replace("Serial Adapter", "Adapteur Port S&eacute;rie",$title);
        	$title = str_replace("Gaming Keyboard", "Clavier de Jeu",$title);
        	$title = str_replace("Wireless Keyboard", "Clavier Sans Fil",$title);
        	$title = str_replace("Dual Paper Trays", "Double alimentation papier",$title);
        	$title = str_replace("Color Laser Printer", "Imprimante Laser Couleur",$title);
        	$title = str_replace("Wireless Weather Station", "Station M&eacute;t&eacute;o Sans Fil",$title);
        	$title = str_replace("Weather Station", "Station M&eacute;t&eacute;o",$title);
        	$title = str_replace("Monochrome Laser Printer", "Imprimante Laser Monochrome",$title);
        	$title = str_replace("Monochrome Laser Printer", "Imprimante Laser Monochrome",$title);
        	$title = str_replace("Internal DVD Writer", "Graveur de DVD Interne",$title);
        	$title = str_replace("Dual Layer", "Double Couche",$title);
        	$title = str_replace("External DVD", "DVD Externe",$title);
        	$title = str_replace("External Blu-ray Writer", "Externe Graveur Blu-ray",$title);
        	$title = str_replace("Internal Blu-ray Writer", "Interne Graveur Blu-ray",$title);
        	$title = str_replace("Internal Blu-ray Drive", "Lecteur de Blu-ray Interne",$title);
        	$title = str_replace("External Harddisk", "Disque Dur Externe",$title);
        	$title = str_replace("USB Parallel Port Adatper", "Adapteur USB &agrave; Porte Parall&egrave;le",$title);
        	$title = str_replace("USB to Serial Converter", "Converteur USB &agrave; Serial",$title);
        	$title = str_replace("Ultra Thin", "Ultra Mince",$title);
        	$title = str_replace("Parallel Port", "Port Parall&egrave;le",$title);
        	$title = str_replace("Washable USB Keyboard", "Lavable Clavier USB",$title);
        	$title = str_replace("Laser Mouse", "Souris Laser",$title);
        	$title = str_replace("Wired Keyboard", "Clavier Filaire",$title);
        	$title = str_replace("Circus Cannon", "Canon de Cirque",$title);
        	$title = str_replace("Relay Board", "Carte Relais",$title);
        	$title = str_replace("Overvoltage Protection", "Protection Contre les Surtensions",$title);
        	$title = str_replace("Multiple Socket", "Prise Multiple",$title);
        	$title = str_replace("USB Device Server", "Serveur Device USB",$title);
        	$title = str_replace("Gaming Mouse", "Souris de Jeu",$title);
        	$title = str_replace("PCI Card", "Carte PCI",$title);
        	$title = str_replace("Optical Resolution", "R&eacute;solution Optique",$title);
        	$title = str_replace("Scanner Resolution", "R&eacute;solution du Scanneur",$title);
        	$title = str_replace("Optical Mouse", "Souris Optique",$title);
        	$title = str_replace("Hand Scanner", "Scanneur &agrave; Main",$title);
        	$title = str_replace("Barcode Scanner", "Scanneur de Code &agrave; Barres",$title);
        	$title = str_replace("Outlet Socket", "Prise de Courant",$title);
        	$title = str_replace("Print Server", "Serveur D'impression",$title);
        	$title = str_replace("Fingerprint Scanner", "Lecteur D'empreintes Digitales",$title);
        	$title = str_replace("USB Hub", "Hub USB",$title);
        	$title = str_replace("Digital Oscilloscope", "Oscilloscope Digital",$title);
        	$title = str_replace("Digital Microscope", "Microscope Digital",$title);
        	$title = str_replace("Remote Control", "Télécommande",$title);
        	$title = str_replace("Video Editing", "Montage Vid&eacute;o",$title);
        	$title = str_replace("Document Camera", "Scanner de Documents",$title);
        	$title = str_replace("Integrated Microphone", "Microphone Int&eacute;gr&eacute;",$title);
        	$title = str_replace("Network Card", "Carte R&eacute;seau",$title);
        	$title = str_replace("USB Adapter", "Adapteur USB",$title);
        	$title = str_replace("PCI Adapter", "Adapteur PCI",$title);
        	$title = str_replace("USB 2.0 Adapter", "Adapteur USB 2.0",$title);
        	$title = str_replace("Nano Adapter", "Adapteur Nano",$title);
        	$title = str_replace("Micro Adapter", "Adapteur Micro",$title);
        	$title = str_replace("WiFi Adapter", "Adapteur WiFi",$title);
        	$title = str_replace("Docking Station", "Station D'accueil",$title);
        	$title = str_replace("Range Extender", "R&eacute;p&eacute;teur",$title);
        	$title = str_replace("Network Switch", "Commutateur R&eacute;seau",$title);
        	$title = str_replace("Pre-installed Ubuntu", "Ubuntu pr&eacute;install&eacute;",$title);

                $title = str_replace("Laser Printer", "Imprimante Laser",$title);
                $title = str_replace("Label Maker", "&Eacute;tiqueteuse",$title);
                $title = str_replace("Label Printer", "&Eacute;tiqueteuse",$title);
                $title = str_replace("High Speed", "Grande Vitesse",$title);
        	$title = str_replace("Printer Server", "Serveur D'impression",$title);
        	$title = str_replace("Color Photo Scanner", "Scanneur Photo Couleur",$title);
        	$title = str_replace("Photo Scanner", "Scanneur Photo",$title);
        	$title = str_replace("Document Scanner", "Scanneur de Documents",$title);
        	$title = str_replace("Slide Scanner", "Scanneur de Diapositives",$title);
        	$title = str_replace("Dia Scanner", "Scanneur de Diapositives",$title);
        	$title = str_replace("ISDN Adapter", "Adapteur ISDN",$title);
        	$title = str_replace("internal ISDN Card", "ISDN Carte Interne",$title);
                                              

        	$title = str_replace(" and ", " et ",$title);
        	$title = str_replace(" to ", " &agrave; ",$title);
        	$title = str_replace("Wireless-N ", "LAN Sans Fil ",$title);
        	//$title = str_replace("WLAN", "LAN Sans Fil",$title);

        # A
        	$title = str_replace("Adapter", "Adapteur",$title);
        	$title = str_replace("adapter", "Adapteur",$title);

        # B
        	$title = str_replace("Blue-ray Writer", "Graveur Blue-ray",$title);
        	$title = str_replace("-based", "-bas&eacute;",$title);
        	$title = str_replace("Buttons", "Boutons",$title);


        # C
        	$title = str_replace("Card", "Carte",$title);
        	$title = str_replace("Channels", "Fili&egrave;res",$title);
        	$title = str_replace("Controller", "Contrôleur",$title);

        # D
        	$title = str_replace("DVD writer", "Graveur DVD",$title);


        # G
        	$title = str_replace("Gyroscopic", "Gyroscopique",$title);
        # E
        	$title = str_replace("Extender", "R&eacute;p&eacute;teur",$title);
        	$title = str_replace("External", "Externe",$title);

        # H
                $title = str_replace("Hardware Encoder", "Encodeur Mat&eacute;riel",$title);

        # I
                $title = str_replace("Inkjet", "Jet d'encre",$title);
                $title = str_replace("Internal", "Interne",$title);
                $title = str_replace("Illuminated", "Illumine&eacute;",$title);

        # J
        	$title = str_replace("Joystick", "Manche &agrave; Balai",$title);

        # M
        	$title = str_replace("Motherboard", "Carte M&egrave;re",$title);
        	$title = str_replace("Mainboard", "Carte M&egrave;re",$title);
                $title = str_replace("Wireless Mouse", "Souris Sans Fil",$title);
                $title = str_replace("Mouse ", "Souris ",$title);
                $title = str_replace("Mouse,", "Souris,",$title);
        	$title = str_replace("Mouse)", "Souris)",$title);
        	$title = str_replace("Monitor", "&Eacute;cran",$title);

        # P
        	$title = str_replace("Presenter", "Pr&eacute;sentation",$title);
        	$title = str_replace("Passive Cooling", "Refroidissement Passif",$title);
        	$title = str_replace("Parallel", "Parall&egrave;le",$title);

        # R
        	$title = str_replace("Remote", "Télécommande",$title);

        # S
        	$title = str_replace("Solar", "&Eacute;nergie Solaire",$title);
        	$title = str_replace("Scrollball", "Boule de D&eacute;filement",$title);
        	$title = str_replace("Slim", "Mince",$title);
        	$title = str_replace("Speed", "Vitesse",$title);
        	$title = str_replace("Surfstick", "Cl&eacute; 3G",$title);
        	$title = str_replace("Stick", "Adapteur",$title);
        	$title = str_replace("Server", "Serveur",$title);
        	$title = str_replace("Serial", "S&eacute;rie",$title);
        	$title = str_replace("Scanner ", "Scanneur ",$title);
        	$title = str_replace("Scanner,", "Scanneur,",$title);
        	$title = str_replace("Scanner", "Scanneur",$title);
        	$title = str_replace("Sound card ", "Carte Son ",$title);
        	$title = str_replace("Audio and Video Grabber", "Acquisition Audio et Vid&eacute;o",$title);
		$title = str_replace("for Notebooks", "pour Portables",$title);

        # T
		$title = str_replace("TV Tuner", "Tuner TV",$title);
		$title = str_replace("Touchpad", "Pav&eacute; Tactile",$title);
		$title = str_replace("Touchscreen", "&Eacute;cran Tactile",$title);
		$title = str_replace("Turntable", "Tourne-disque",$title);

        # W
		$title = str_replace("Writer", "Graveur",$title);
		$title = str_replace("Wide Angle,", "Grand Angle,",$title);
        	$title = str_replace("Wireless ", "Sans Fil ",$title);
        	$title = str_replace("wireless", "Sans Fil",$title);
        	$title = str_replace("Wireless", "Sans Fil",$title);

        	$title = str_replace("Laser Printer ", "Imprimante Laser ",$title);
        	$title = str_replace("All-in-one Printer", "Imprimante tout-en-un",$title);
        	$title = str_replace("All-in-One Printer", "Imprimante tout-en-un",$title);
        	$title = str_replace("All-in-One printer", "Imprimante tout-en-un",$title);
        	$title = str_replace("All-in-one printer", "Imprimante tout-en-un",$title);
        	$title = str_replace("All-in-One", "Imprimante tout-en-un",$title);
        	$title = str_replace("Printserver ", "Serveur Imprimante ",$title);
        	$title = str_replace("Printer ", "Imprimante ",$title);
        	$title = str_replace("printer ", "Imprimante ",$title);
        	$title = str_replace("Printer,", "Imprimante,",$title);
        	$title = str_replace("Copier,", "Copieur,",$title);
        	$title = str_replace("Copier ", "Copieur ",$title);
        	$title = str_replace("analog TV", "TV Analogique",$title);
        	$title = str_replace("Analog TV", "TV Analogique",$title);
        	$title = str_replace("Analog-TV", "TV Analogique",$title);
        	$title = str_replace("analog ", "Analogique ",$title);
        	$title = str_replace("Analog ", "Analogique ",$title);
        	$title = str_replace("Analog,", "Analogique,",$title);
        	$title = str_replace("Analog)", "Analogique)",$title);
        	$title = str_replace("with ", "avec ",$title);
        	$title = str_replace("internal ", "interne ",$title);
        	$title = str_replace("Router ", "Routeur ",$title);
        	$title = str_replace("Remote Control", "T&eacute;l&eacute;commande",$title);
        	$title = str_replace("external ", "Externe ",$title);
        	$title = str_replace("Network Card ", "Carte R&eacute;seau ",$title);
        	$title = str_replace("Network ", "R&eacute;seau ",$title);
        	$title = str_replace("card ", "Carte ",$title);
        	$title = str_replace("Card", "Carte",$title);
        	$title = str_replace("CONTROLLER", "Contr&ocirc;leur",$title);
        	$title = str_replace("Conference Camera", "Caméra de conférence",$title);
        	$title = str_replace("Endoscope Camera", "Caméra endoscope",$title);
        	$title = str_replace("Goose Neck", "col-de-cygne",$title);
        	$title = str_replace("Writer,", "graveur, ",$title);
        	$title = str_replace("Color)", "Couleur)",$title);
        	$title = str_replace("Gaming Laser Mouse", "Souris Laser Gaming",$title);
        	$title = str_replace("Washable", "lavable",$title);
        	$title = str_replace("Keyboard ", "Clavier ",$title);
        	$title = str_replace("keyboard ", "Clavier ",$title);
        	$title = str_replace("Keyboard)", "Clavier)",$title);
        	$title = str_replace("Keyboard", "Clavier",$title);
        	$title = str_replace("Ultrathin", "Ultri-mince",$title);
        	$title = str_replace("Wired)", "Filaire)",$title);
        	$title = str_replace("wearable webcam", "Webcam Portable",$title);
        	$title = str_replace("Night Vision", "Vision Nocturne",$title);
        	$title = str_replace("Speaker", "Haut-Parleur",$title);
        	$title = str_replace("Headphone", "Casque D'&eacute;coute",$title);

        	return $title;
        }

	if ($region == "es") {

                # combinations
        	$title = str_replace("Wireless Mobile Broadband Modem", "Módem de banda ancha móvil inalámbrica",$title);

        	$title = str_replace("Server Adapter", "Adaptador de Servidor",$title);
        	$title = str_replace("Serial Adapter", "Adaptador de Serial",$title);
        	$title = str_replace("Gaming Keyboard", "Teclado para Jugar",$title);
        	$title = str_replace("Wireless Keyboard", "Teclado Sin Hilos",$title);
        	$title = str_replace("Dual Paper Trays", "Bendejas de Papel Duales",$title);
        	$title = str_replace("Color Laser Printer", "Impresora L&aacute;ser a Color",$title);
        	$title = str_replace("Wireless Weather Station", "Estaci&oacute;n Meteorol&oacute;gica Inal&aacute;mrica",$title);
        	$title = str_replace("Weather Station", "Estaci&oacute;n Meteorol&oacute;gica",$title);
        	$title = str_replace("Monochrome Laser Printer", "Impresora L&aacute;ser Monocromo",$title);
        	//$title = str_replace("Monochrome Laser Printer", "Imprimante Laser Monochrome",$title);
        	$title = str_replace("Internal DVD Writer", "Grabadora de DVD Interna",$title);
        	$title = str_replace("Dual Layer", "Doble Capa",$title);
        	$title = str_replace("External DVD", "DVD Externo",$title);
        	$title = str_replace("External Blu-ray Writer", "Grabadora Blu-ray Externo",$title);
        	$title = str_replace("Internal Blu-ray Writer", "Grabadora Blu-ray Interna",$title);
        	$title = str_replace("Internal Blu-ray Drive", "Unidad de Blu-ray Interna",$title);
        	$title = str_replace("External Harddisk", "Disco Duro Externo",$title);
        	$title = str_replace("USB Parallel Port Adatper", "Adaptador de puerto paralelo a USB",$title);
        	$title = str_replace("USB to Serial Converter", "Convertidor USB a Serial",$title);
        	$title = str_replace("Ultra Thin", "Ultra Delegado",$title);
        	$title = str_replace("Parallel Port", "Puerto Paralelo",$title);
        	$title = str_replace("Washable USB Keyboard", "Teclado USB lavable",$title);
        	$title = str_replace("Laser Mouse", "Rat&oacute;n L&aacute;ser",$title);
        	$title = str_replace("Wired Keyboard", "Teclado con Cable",$title);
        	$title = str_replace("Circus Cannon", "Cañón de Circo",$title);
        	$title = str_replace("Relay Board", "Tarjeta Rel&eacute;",$title);
        	$title = str_replace("Overvoltage Protection", "Protecci&oacute;n contra Sobretensiones",$title);
        	$title = str_replace("Multiple Socket", "Toma de Corrient M&uacute;ltiple",$title);
        	$title = str_replace("USB Device Server", "Servidor de Dispositivos USB",$title);
        	$title = str_replace("Gaming Mouse", "Rat&oacute;n para Juegos",$title);
        	$title = str_replace("PCI Card", "Tarjeta PCI",$title);
        	$title = str_replace("Optical Resolution", "Resoluci&oacute;n &Oacute;ptica",$title);
        	$title = str_replace("Scanner Resolution", "R&eacute;solucion del Esc&aacute;ner",$title);
        	$title = str_replace("Optical Mouse", "Rat&oacute;n &Oacute;ptico",$title);
        	$title = str_replace("Hand Scanner", "Esc&aacute;ner de Mano",$title);
        	$title = str_replace("Barcode Scanner", "Esc&aacute;ner de C&oacute;digo de Barras",$title);
        	$title = str_replace("Outlet Socket", "Toma de Corriente",$title);
        	$title = str_replace("Print Server", "Servidor de Impresi&oacute;n",$title);
        	$title = str_replace("Fingerprint Scanner", "Lector de Huellas Dactilares",$title);
        	$title = str_replace("USB Hub", "Hub USB",$title);
        	$title = str_replace("Digital Oscilloscope", "Oscilloscope Digital",$title);
        	$title = str_replace("Digital Microscope", "Microscopio Digital",$title);
        	$title = str_replace("Remote Control", "Mando a Distancia",$title);
        	$title = str_replace("Video Editing", "Edici&oacute;n de V&iacute;deo",$title);
        	$title = str_replace("Document Camera", "Esc&aacute;ner de Documentos",$title);
        	$title = str_replace("Integrated Microphone", "Micr&oacute;fono integrado",$title);
        	$title = str_replace("Network Card", "Tarjeta de Red",$title);
        	$title = str_replace("USB Adapter", "Adaptador USB",$title);
        	$title = str_replace("PCI Adapter", "Adaptador PCI",$title);
        	$title = str_replace("USB 2.0 Adapter", "Adaptador USB 2.0",$title);
        	$title = str_replace("Nano Adapter", "Adaptador Nano",$title);
        	$title = str_replace("Micro Adapter", "Adaptador Micro",$title);
        	$title = str_replace("WiFi Adapter", "Adaptador WiFi",$title);
        	$title = str_replace("Docking Station", "Estci&oacute;n de Acoplamiento",$title);
        	$title = str_replace("Range Extender", "Extensor de Alcance",$title);
        	$title = str_replace("Network Switch", "Conmutador de Red",$title);
        	$title = str_replace("Pre-installed Ubuntu", "Ubuntu preinstalado",$title);

                $title = str_replace("Laser Printer", "Impresora L&aacute;ser",$title);
                $title = str_replace("Label Maker", "Etiquetadoras",$title);
                $title = str_replace("Label Printer", "Etiquetadoras",$title);
                $title = str_replace("High Speed", "Alta Velocidad",$title);
        	$title = str_replace("Printer Server", "Servidor de Impresi&oacute;n",$title);
        	$title = str_replace("Color Photo Scanner", "Foto de Color del Esc&aacute;ner",$title);
        	$title = str_replace("Photo Scanner", "Esc&aacute;ner Fotogr&aacute;fico",$title);
        	$title = str_replace("Document Scanner", "Esc&aacute;ner de Documentos",$title);
        	$title = str_replace("Slide Scanner", "Esc&aacute;ner de Diapositivas",$title);
        	$title = str_replace("Dia Scanner", "Esc&aacute;ner de Diapositivas",$title);
        	$title = str_replace("ISDN Adapter", "Adaptador ISDN",$title);
        	$title = str_replace("internal ISDN Card", "Scheda ISDN Interna",$title);
                                              

        	$title = str_replace(" and ", " e ",$title);
        	$title = str_replace(" to ", " a ",$title);
        	$title = str_replace("Wireless-N ", "LAN Sans Fil ",$title);
        	//$title = str_replace("WLAN", "LAN Sans Fil",$title);


        # A
        	$title = str_replace("Adapter", "Adaptadpr",$title);
        	$title = str_replace("adapter", "Adaptador",$title);

        # B
        	$title = str_replace("Blue-ray Writer", "Grabadora de Blu-ray",$title);
        	$title = str_replace("Blu-ray Writer", "Grabadora de Blu-ray",$title);
        	$title = str_replace("-based", "-basado",$title);
        	$title = str_replace("Buttons", "Botones",$title);


        # C
        	$title = str_replace("Card", "Tarjeta",$title);
        	$title = str_replace("Channels", "Canales",$title);
        	$title = str_replace("Controller", "Controlador",$title);

        # D
        	$title = str_replace("DVD writer", "Grabadora DVD",$title);


        # G
        	$title = str_replace("Gyroscopic", "Girosc&oacute;pico",$title);
        # E
        	$title = str_replace("Extender", "Extensor",$title);
        	$title = str_replace("External", "Externo",$title);

        # H
                $title = str_replace("Hardware Encoder", "Codificador de Hardware",$title);

        # I
                $title = str_replace("Inkjet", "Tinta",$title);
                $title = str_replace("Internal", "Interno",$title);
                $title = str_replace("Illuminated", "Iluminado",$title);

        # J
        	$title = str_replace("Joystick", "Palanca de Mando",$title);

        # M
        	$title = str_replace("Motherboard", "Placa Madre",$title);
        	$title = str_replace("Mainboard", "Placa Madre",$title);
                $title = str_replace("Wireless Mouse", "Sin Hilos",$title);
                $title = str_replace("Mouse ", "Rat&oacute;n ",$title);
                $title = str_replace("Mouse,", "Rat&oacute;n,",$title);
        	$title = str_replace("Mouse)", "Rat&oacute;n)",$title);
        	//$title = str_replace("Monitor", "&Eacute;cran",$title);

        # P
        	$title = str_replace("Presenter", "Presentaci&oacute;n",$title);
        	$title = str_replace("Passive Cooling", "Refrigeraci&oacute;n Pasiva",$title);
        	$title = str_replace("Parallel", "Paralelo",$title);

        # R
        	$title = str_replace("Remote", "Mando a Distancia",$title);

        # S
        	$title = str_replace("Solar", "Energ&iacute;a Solar",$title);
        	//$title = str_replace("Scrollball", "Boule de D&eacute;filement",$title);
        	$title = str_replace("Slim", "Delgado",$title);
        	$title = str_replace("Speed", "Velocidad",$title);
        	//$title = str_replace("Surfstick", "Cl&eacute; 3G",$title);
        	$title = str_replace("Stick", "Adaptador",$title);
        	$title = str_replace("Server", "Servidor",$title);
        	$title = str_replace("Serial", "S&eacute;rie",$title);
        	$title = str_replace("Scanner ", "Esc&aacute;ner ",$title);
        	$title = str_replace("Scanner,", "Esc&aacute;ner,",$title);
        	$title = str_replace("Scanner", "Esc&aacute;ner",$title);
        	$title = str_replace("Sound card ", "Tarjeta de Sonido ",$title);
        	$title = str_replace("Audio and Video Grabber", "Capturador de Audio e V&iacute;deo",$title);
		$title = str_replace("for Notebooks", "para Ordenador Port&aacute;til",$title);

        # T
		$title = str_replace("TV Tuner", "Sintonizador de TV",$title);
		//$title = str_replace("Touchpad", "Pav&eacute; Tactile",$title);
		$title = str_replace("Touchscreen", "Pantalla T&aacute;ctil",$title);
		$title = str_replace("Turntable", "Tocadiscos",$title);

        # W
		$title = str_replace("Writer", "Grabadora",$title);
		$title = str_replace("Wide Angle,", "Gran Angular,",$title);
        	$title = str_replace("Wireless ", "Sin Hilos ",$title);
        	$title = str_replace("wireless", "Sin Hilos",$title);
        	$title = str_replace("Wireless", "Sin Hilos",$title);


                //mouse
		$title = str_replace("Wireless Laser Mouse", "Ratón láser inalámbrico",$title);
		$title = str_replace("Programmable Gaming Laser Mouse", "Juegos programable ratón láser",$title);
		$title = str_replace("Gaming Laser Mouse", "Juegos láser del ratón",$title);
		$title = str_replace("Laser Gaming Mouse", "juegos láser del ratón",$title);
		$title = str_replace("Gaming Mouse", "Ratón del juego",$title);
		$title = str_replace("USB to Serial Converter", "USB al convertidor serial",$title);
        	$title = str_replace("Wireless Mouse", "Ratón inalámbrico",$title);
        	$title = str_replace("wireless mouse", "Ratón inalámbrico",$title);
        	$title = str_replace("Barcode Scanner", "Escáner de código de barras",$title);
        	$title = str_replace("Optical Mouse", "Ratón óptico",$title);
        	$title = str_replace("Mouse ", "Rató ",$title);


        	$title = str_replace("Mainboard ", "Placa Base ",$title);
        	$title = str_replace("Mainboard", "Placa Base",$title);
        	$title = str_replace("Motherboard ", "Placa Base ",$title);
        	$title = str_replace("Mainboard", "Placa Base",$title);
        	$title = str_replace("Wireless-N ", "Wifi N ",$title);
        	//$title = str_replace("WLAN", "Wifi",$title);
        	//$title = str_replace("Wireless ", "Wifi ",$title);

                //Adapter
        	$title = str_replace("USB-Ethernet Adapter", "Adaptador USB-Ethernet",$title);
        	$title = str_replace("Parallel Port Adapter", "Adaptador de puerto paralelo",$title);
        	$title = str_replace("Serial Adapter", "Adaptador serie",$title);
        	$title = str_replace("Parallel Port", "Puerto paralelo",$title);
        	$title = str_replace("USB WiFi Adapter", "Adaptador USB WiFi",$title);
        	$title = str_replace("Ethernet Adapter", "Adaptador Ethernet",$title);
        	$title = str_replace("WiFi Adapter", "Adaptador WiFi",$title);
                $title = str_replace("Adapter", "Adaptador",$title);
        	$title = str_replace("adapter", "Adaptador",$title);
        	$title = str_replace("Stick", "Adaptador",$title);

                //Writer
        	$title = str_replace("Writer", "Grabadora",$title);
        	$title = str_replace("writer", "Grabadora",$title);


                //Card
        	$title = str_replace("internal ISDN Card", "Placa ISDN Interna",$title);
        	$title = str_replace("Network Card ", "Placa de red ",$title);
        	$title = str_replace("Express Card", "Placa Express",$title);
        	$title = str_replace("PCI Card", "Placa PCI",$title);
        	$title = str_replace("card ", "Placa ",$title);
        	$title = str_replace("Card", "Placa",$title);
        	$title = str_replace("buttons", "botones",$title);
        	$title = str_replace("Card ", "Placa ",$title);
        	$title = str_replace("Sound card ", "Placa de Sonido ",$title);


		$title = str_replace("for Notebooks", "por Ordenador Port&aacute;til",$title);
        	$title = str_replace("Laser Printer ", "Impresora L&aacute;ser ",$title);
        	$title = str_replace("All-in-one Printer", "Impresora Multifuncti&oacute;n",$title);
        	$title = str_replace("All-in-One Printer", "Impresora Multifuncti&oacute;n",$title);
        	$title = str_replace("All-in-One printer", "Impresora Multifuncti&oacute;n",$title);
        	$title = str_replace("All-in-one printer", "Impresora Multifuncti&oacute;n",$title);
        	$title = str_replace("All-in-One", "Impresora Multifuncti&oacute;n",$title);
        	$title = str_replace("Printserver ", "Impresora Server ",$title);
        	$title = str_replace("Printer ", "Impresora ",$title);
        	$title = str_replace("printer ", "Impresora ",$title);
        	$title = str_replace("Printer,", "Impresora,",$title);
        	$title = str_replace("Copier,", "Copiadora,",$title);
        	$title = str_replace("Copier ", "Copiadora ",$title);
        	$title = str_replace("Color Printer", "Impresora de Color",$title);
        	$title = str_replace("Scanner", "Esc&aacute;ner",$title);
        	$title = str_replace("Monochrome", "Monocroma",$title);
        	$title = str_replace("Dia Scanner", "Esc&aacute;ner dia",$title);
        	$title = str_replace("Document Scanner", "Escáner de documentos",$title);
        	$title = str_replace("Photo Scanner", "Escáner de fotos",$title);
        	$title = str_replace("Film and Slide Scanner", "Pel&iacute;culas y diapositivas escáner",$title);
        	$title = str_replace("Optical Resolution", "Resolución &Oacute;ptica",$title);

        	$title = str_replace("analog TV", "TV Anal&oacute;gico ",$title);
        	$title = str_replace("Analog TV", "TV Anal&oacute;gico ",$title);
        	$title = str_replace("Analog-TV", "TV Anal&oacute;gico ",$title);
        	$title = str_replace("analog ", "Anal&oacute;gico ",$title);
        	$title = str_replace("Analog ", "Anal&oacute;gico ",$title);
        	$title = str_replace("Analog,", "Anal&oacute;gico,",$title);
        	$title = str_replace("Analog)", "Anal&oacute;gico)",$title);
        	$title = str_replace("with ", "con ",$title);
        	$title = str_replace("Router ", "Enrutador ",$title);
        	$title = str_replace("external ", "Externo ",$title);
        	$title = str_replace("Network Card ", "Tarjeta de Red ",$title);
        	$title = str_replace("Network ", "Red ",$title);
        	$title = str_replace("card ", "Placa ",$title);
        	$title = str_replace("Card", "Placa",$title);
        	$title = str_replace(" Ports", " Puertos",$title);

		// Keyboard
                $title = str_replace("Washable USB Keyboard", "Teclado USB lavable",$title);
                $title = str_replace("Bluetooth Keyboard", "Telcado Bluetooth",$title);
                $title = str_replace("wireless Keyboard", "Tastiera senza fili",$title);
        	$title = str_replace("Keyboard)", "Telcado)",$title);
        	$title = str_replace("(Keyboard,", "(Telcado,",$title);
        	$title = str_replace("illuminated ", "iluminado ",$title);
        	$title = str_replace("keyboard,", "Telcado,",$title);
        	$title = str_replace(" keyboard ", " Telcado ",$title);
                $title = str_replace("Keyboard", "Telcado",$title);
        	$title = str_replace("Keyboard ", "Teclado ",$title);

 		$title = str_replace("wearable webcam", "Webcam Portable",$title);
        	$title = str_replace("Night Vision", "Indulgencia",$title);
        	$title = str_replace("Speaker", "Altavoz",$title);
        	$title = str_replace("Headphone", "Cascos",$title);
		$title = str_replace("Telephone, ", "Tel&eacute;fono, ",$title);

        	$title = str_replace("wireless", "Inalámbrico",$title);
        	$title = str_replace("Wireless", "Inalámbrico",$title);
        	$title = str_replace("Duplex", "D&uacute;plex",$title);

        	$title = str_replace(" and ", " e ",$title);

                $title = str_replace("Label Maker", "Aparato etiquetador",$title);


        	$title = str_replace("internal", "Interno",$title);
        	$title = str_replace("Internal", "Interno",$title);
        	$title = str_replace("external", "externo",$title);
        	$title = str_replace("External", "externo",$title);

        	$title = str_replace("Oscilloscope ", "Osciloscopio ",$title);
        	$title = str_replace("Channels ", "Canales ",$title);

                //Cleaning some strings
        	$title = str_replace("FRITZ!Placa", "FRITZ!Card",$title);
        	$title = str_replace("Video ", "V&iacute;deo ",$title);
        	$title = str_replace("video ", "v&iacute;deo ",$title);

        	return $title;
        }

	if ($region == "it") {

                # combinations
        	$title = str_replace("Wireless Mobile Broadband Modem", "Modem wireless banda larga mobile",$title);
                #new
        	$title = str_replace("Server Adapter", "Adattore Server",$title);
        	$title = str_replace("Serial Adapter", "Adattore per Porta Seriale",$title);
        	$title = str_replace("Gaming Keyboard", "Tastiera per Giocare",$title);
        	$title = str_replace("Wireless Keyboard", "Tastiera Senza Fili",$title);
        	$title = str_replace("Dual Paper Trays", "Due Vassoi Carta",$title);
        	$title = str_replace("Color Laser Printer", "Stampante Laser a Colori",$title);
        	$title = str_replace("Wireless Weather Station", "Stazione Meteo Senza Fili",$title);
        	$title = str_replace("Weather Station", "Stazione Meteo",$title);
        	$title = str_replace("Monochrome Laser Printer", "Stampante Laser Monocromatica",$title);
        	$title = str_replace("Internal DVD Writer", "Masterizzatore DVD interno",$title);
        	//$title = str_replace("Dual Layer", "Double Couche",$title);
        	$title = str_replace("External DVD", "DVD Esterno",$title);
        	$title = str_replace("External Blu-ray Writer", "Masterizzatore Blu-ray Esterno",$title);
        	$title = str_replace("Internal Blu-ray Writer", "Masterizzatore Blu-ray Interno",$title);
        	$title = str_replace("Internal Blu-ray Drive", "Lettore Blu-ray Interno",$title);
        	$title = str_replace("External Harddisk", "Harddisk Externo",$title);
        	$title = str_replace("USB Parallel Port Adatper", "Adattatore USB a Porta Parallela",$title);
        	$title = str_replace("USB to Serial Converter", "USB Serail Converter",$title);
        	$title = str_replace("Ultra Thin", "Ultra Sottile",$title);
        	$title = str_replace("Parallel Port", "Porta Parallela",$title);
        	$title = str_replace("Washable USB Keyboard", "Tastiera Lavabile USB",$title);
        	//$title = str_replace("Laser Mouse", "Souris Laser",$title);
        	$title = str_replace("Wired Keyboard", "Tastiera Cablato",$title);
        	$title = str_replace("Circus Cannon", "Cannone da Circo",$title);
        	$title = str_replace("Relay Board", "Scheda Rel&egrave;",$title);
        	$title = str_replace("Overvoltage Protection", "Protezione da Sovratensioni",$title);
        	$title = str_replace("Multiple Socket", "Presa Multipla",$title);
        	//$title = str_replace("USB Device Server", "Serveur Device USB",$title);
        	//$title = str_replace("Gaming Mouse", "Souris de Jeu",$title);
        	$title = str_replace("PCI Card", "Scheda PCI",$title);
        	$title = str_replace("Optical Resolution", "Risoluzione Ottica",$title);
        	$title = str_replace("Scanner Resolution", "Risoluzione Scanner",$title);
        	$title = str_replace("Optical Mouse", "Mouse Ottico",$title);
        	$title = str_replace("Hand Scanner", "Scanner Manuale",$title);
        	$title = str_replace("Barcode Scanner", "Scanner di Codici a Barre",$title);
        	$title = str_replace("Outlet Socket", "Presa di Corrente",$title);
        	$title = str_replace("Print Server", "Server di Stampa",$title);
        	$title = str_replace("Fingerprint Scanner", "Scanner di Impronte Digitali",$title);
        	$title = str_replace("USB Hub", "Hub USB",$title);
        	$title = str_replace("Digital Oscilloscope", "Oscilloscopio Digitale",$title);
        	$title = str_replace("Digital Microscope", "Microscopio Digital",$title);
        	$title = str_replace("Remote Control", "Te�ecomando",$title);
        	//$title = str_replace("Video Editing", "Montage Vid&eacute;o",$title);
        	$title = str_replace("Document Camera", "Scanner Documento",$title);
        	$title = str_replace("Integrated Microphone", "Microfono Integrato",$title);
        	$title = str_replace("Network Card", "Scheda di Rete",$title);
        	$title = str_replace("USB Adapter", "Adattatore USB",$title);
        	$title = str_replace("PCI Adapter", "Adattatore PCI",$title);
        	$title = str_replace("USB 2.0 Adapter", "Adattatore USB 2.0",$title);
        	$title = str_replace("Nano Adapter", "Adattatore Nano",$title);
        	$title = str_replace("Micro Adapter", "Adattatore Micro",$title);
        	$title = str_replace("WiFi Adapter", "Adattatore WiFi",$title);
        	//$title = str_replace("Docking Station", "Station D'accueil",$title);
        	//$title = str_replace("Range Extender", "R&eacute;p&eacute;teur",$title);
        	$title = str_replace("Network Switch", "Interruttore di Rete",$title);
        	$title = str_replace("Pre-installed Ubuntu", "Ubuntu pre-installato",$title);

                $title = str_replace("Laser Printer", "Stampante Laser",$title);
                $title = str_replace("Label Maker", "Stampanti per Etichette",$title);
                $title = str_replace("Label Printer", "Stampanti per Etichette",$title);
                $title = str_replace("High Speed", "Alta Velocit&agrave;",$title);
        	$title = str_replace("Printer Server", "Server di Stampante",$title);
        	$title = str_replace("Color Photo Scanner", "Scanner Photo Colore",$title);
        	$title = str_replace("Photo Scanner", "Scanner Photo",$title);
        	$title = str_replace("Document Scanner", "Scanner Documento",$title);
        	$title = str_replace("Slide Scanner", "Scanneur de Diapositives",$title);
        	$title = str_replace("Dia Scanner", "Scanner Diapositive",$title);
        	$title = str_replace("ISDN Adapter", "Adattatore ISDN",$title);
        	$title = str_replace("internal ISDN Card", "Scheda ISDN Interno",$title);

        	$title = str_replace(" and ", " e ",$title);
        	$title = str_replace(" to ", " a ",$title);
        	//$title = str_replace("Wireless-N ", "LAN Sans Fil ",$title);

        # A
        	$title = str_replace("Adapter", "Adattatore",$title);
        	$title = str_replace("adapter", "Adattatore",$title);

        # B
        	$title = str_replace("Blue-ray Writer", "Masterizzatore Blu-ray",$title);
        	$title = str_replace("Blu-ray Writer", "Masterizzatore Blu-ray",$title);
        	$title = str_replace("-based", "-basata",$title);
        	$title = str_replace("Buttons", "Pulsanti",$title);


        # C
        	$title = str_replace("Card", "Scheda",$title);
        	$title = str_replace("Channels", "Canali",$title);
        	$title = str_replace("Controller", "Controllore",$title);

        # D
        	$title = str_replace("DVD writer", "Masterizzatore DVD",$title);

        # G
        	$title = str_replace("Gyroscopic", "Giroscopico",$title);
        # E
        	//$title = str_replace("Extender", "R&eacute;p&eacute;teur",$title);
        	$title = str_replace("External", "Externo",$title);

        # H
                //$title = str_replace("Hardware Encoder", "Encodeur Mat&eacute;riel",$title);

        # I
                //$title = str_replace("Inkjet", "Jet d'encre",$title);
                $title = str_replace("Internal", "Interno",$title);
                $title = str_replace("Illuminated", "Illuminato",$title);

        # J
        	//$title = str_replace("Joystick", "Manche &agrave; Balai",$title);

        # M
        	$title = str_replace("Motherboard", "Scheda Madre",$title);
        	$title = str_replace("Mainboard", "Scheda Madre",$title);
                $title = str_replace("Wireless Mouse", "Mouse Senza Fili",$title);
                //$title = str_replace("Mouse ", "Souris ",$title);
                //$title = str_replace("Mouse,", "Souris,",$title);
        	//$title = str_replace("Mouse)", "Souris)",$title);
        	//$title = str_replace("Monitor", "&Eacute;cran",$title);

        # P
        	$title = str_replace("Presenter", "Presentazione",$title);
        	$title = str_replace("Passive Cooling", "Raffreddamento Passivo",$title);
        	$title = str_replace("Parallel", "Parallelo",$title);

        # R
        	$title = str_replace("Remote", "Te�ecomando",$title);

        # S
        	$title = str_replace("Solar", "Energia Solare",$title);
        	$title = str_replace("Scrollball", "Pallina di Scorrimento",$title);
        	$title = str_replace("Slim", "Sottile",$title);
        	$title = str_replace("Speed", "Velocit&agrave;",$title);
        	//$title = str_replace("Surfstick", "Cl&eacute; 3G",$title);
        	$title = str_replace("Stick", "Adattatore",$title);
        	//$title = str_replace("Server", "Server",$title);
        	$title = str_replace("Serial", "Seriale",$title);
        	//$title = str_replace("Scanner ", "Scanneur ",$title);
        	//$title = str_replace("Scanner,", "Scanneur,",$title);
        	//$title = str_replace("Scanner", "Scanneur",$title);
        	$title = str_replace("Sound card ", "Scheda Audio ",$title);
        	$title = str_replace("Audio and Video Grabber", "Acquisizione Audio e Video",$title);
		//$title = str_replace("for Notebooks", "pour Portables",$title);

        # T
		$title = str_replace("TV Tuner", "Sintonizzatore TV",$title);
		//$title = str_replace("Touchpad", "Pav&eacute; Tactile",$title);
		//$title = str_replace("Touchscreen", "&Eacute;cran Tactile",$title);
		$title = str_replace("Turntable", "Piatto",$title);

        # W
		$title = str_replace("Writer", "Masterizzatore",$title);
		$title = str_replace("Wide Angle,", "Grandangolo,",$title);
        	$title = str_replace("Wireless ", "Senza Fili ",$title);
        	$title = str_replace("wireless", "Senza Fili",$title);
        	$title = str_replace("Wireless", "Senza Fili",$title);


                //mouse
		/*$title = str_replace("Wireless Laser Mouse", "Mouse laser senza fili",$title);
		$title = str_replace("Programmable Gaming Laser Mouse", "Mouse laser da gioco programmabili",$title);
		$title = str_replace("Gaming Laser Mouse", "Mouse laser da gioco",$title);
		$title = str_replace("Laser Gaming Mouse", "Mouse laser da gioco",$title);
		$title = str_replace("Gaming Mouse", "Mouse da gioco",$title);
		$title = str_replace("USB to Serial Converter", "Convertitore da USB a seriale",$title);
        	$title = str_replace("Wireless Mouse", "Mouse senza fili",$title);
        	$title = str_replace("wireless mouse", "Mouse senza fili",$title);
        	$title = str_replace("Barcode Scanner", "Scanner di codici a barre",$title);
                */

                $title = str_replace("Mainboard ", "Scheda Madre ",$title);
        	$title = str_replace("built-in ", "incassato ",$title);
        	$title = str_replace("Camera, ", "Telecamera, ",$title);
        	$title = str_replace("Turntable)", "Giradischi)",$title);
        	$title = str_replace("Color, ", "Colore, ",$title);
        	$title = str_replace(" and ", " e ",$title);
        	$title = str_replace("Low Profile)", "Basso Profilo)",$title);
        	$title = str_replace("Remote Control", "Telecomando",$title);
        	$title = str_replace("Microphone)", "Microfono)",$title);
        	$title = str_replace("(Mainboard,", "(Scheda Madre,",$title);
        	$title = str_replace("Motherboard ", "Scheda Madre ",$title);
        	$title = str_replace("Motherboard,", "Scheda Madre,",$title);
        	$title = str_replace("Wireless-N ", "Wireless N ",$title);
        	//$title = str_replace("WLAN", "WLAN",$title);
        	//$title = str_replace("Wireless ", "Wifi ",$title);
        	$title = str_replace("Indoor CCTV", "CCTV in casa",$title);

                //Adapter
        	$title = str_replace("USB-Ethernet Adapter", "Adattatore USB-Ethernet",$title);
        	$title = str_replace("Parallel Port Adapter", "Adattatore Porta Parallela",$title);
        	$title = str_replace("Serial Adapter", "Adattatore Serial",$title);
        	$title = str_replace("Parallel Port", "Porta Parallela",$title);
        	$title = str_replace("USB WiFi Adapter", "Adattatore USB WiFi",$title);
        	$title = str_replace("Ethernet Adapter", "Adattatore Ethernet",$title);
        	$title = str_replace("WiFi Adapter", "Adattatore WiFi",$title);
        	$title = str_replace("Adapter", "Adattatore",$title);
        	$title = str_replace("adapter", "Adattatore",$title);
        	$title = str_replace("Stick", "Adattatore",$title);

        	$title = str_replace("integrated microphone", "microfono integrato",$title);
        	$title = str_replace("Digital Microscope", "Microscopio digitale",$title);
        	$title = str_replace("Optical Resolution", "Risoluzione ottica",$title);
        	$title = str_replace("Document Camera", "Fotocamera per documenti",$title);
        	$title = str_replace("Turntable", "Tocadiscos",$title);
        	//$title = str_replace("Mouse ", "Rat&oecute;n ",$title);
        	$title = str_replace("Optical Mouse", "Mouse Ottico",$title);
        	$title = str_replace("Graphic Card", "Scheda Graphica",$title);
        	$title = str_replace("PCI Card ", "Scheda PCI",$title);
        	$title = str_replace("Network Card", "Scheda Rete",$title);
        	$title = str_replace("Card ", "Scheda ",$title);
        	$title = str_replace("Sound card ", "Scheda Audio ",$title);
		$title = str_replace("for Notebooks", "per portatile",$title);
        	$title = str_replace("Mono Laser Printer", "Stampante Laser Monocromatica",$title);
        	$title = str_replace("Laser Printer ", "Stampante Laser ",$title);
		$title = str_replace("Laser Printer", "Stampante Laser",$title);
        	$title = str_replace("All-in-one Color Laser Printer", "Stampante Laser Multifunzione a Colori",$title);
        	$title = str_replace("All-in-one Printer", "Stampanti Multifunzione",$title);
        	$title = str_replace("All-in-One Printer", "Stampanti Multifunzione",$title);
        	$title = str_replace("All-in-One printer", "Stampanti Multifunzione",$title);
        	$title = str_replace("All-in-one printer", "Stampanti Multifunzione",$title);
        	$title = str_replace("All-in-One", "Stampanti Multifunzione",$title);
        	$title = str_replace("Printserver ", "Server di Stampa ",$title);
        	$title = str_replace("Printer ", "Stampante ",$title);
        	$title = str_replace("printer ", "Stampante ",$title);
        	$title = str_replace("Printer,", "Stampante,",$title);
        	$title = str_replace("Copier,", "Copista,",$title);
        	$title = str_replace("Copier ", "Copista ",$title);


        	$title = str_replace("analog TV", "TV Analogica ",$title);
        	$title = str_replace("Analog TV", "TV Analogica ",$title);
        	$title = str_replace("Analog-TV", "TV Analogica ",$title);
        	$title = str_replace("analog ", "Analogco ",$title);
        	$title = str_replace("Analog ", "Analogico ",$title);
        	$title = str_replace("Analog,", "Analogico,",$title);
        	$title = str_replace("Analog)", "Analogico)",$title);
        	$title = str_replace("with ", "con ",$title);
        	//$title = str_replace("Router ", "Enrutador ",$title);
        	$title = str_replace("external ", "Esterno ",$title);

                //Card
        	$title = str_replace("internal ISDN Card", "Scheda ISDN Interno",$title);
        	$title = str_replace("Network Card ", "Scheda di Rete ",$title);
        	$title = str_replace("Network ", "Rete ",$title);
        	$title = str_replace("Express Card", "Scheda Express",$title);
        	$title = str_replace("card ", "Scheda ",$title);
        	$title = str_replace("Card", "Scheda",$title);
        	$title = str_replace("buttons", "pulsanti",$title);
        	$title = str_replace("FRITZ!Scheda", "FRITZ!Card",$title);

		// Keyboard
                $title = str_replace("Washable USB Keyboard", "Tastiera USB Lavabile",$title);
                $title = str_replace("Bluetooth Keyboard", "Tastiera Bluetooth",$title);
                $title = str_replace("wireless Keyboard", "Tastiera senza fili",$title);
                $title = str_replace("Keyboard ", "Tastiera ",$title);
        	$title = str_replace("Keyboard)", "Tastiera)",$title);
        	$title = str_replace("(Keyboard,", "(Tastiera,",$title);
        	$title = str_replace("illuminated ", "illuminator ",$title);
        	$title = str_replace("keyboard,", "Tastiera,",$title);
        	$title = str_replace(" keyboard ", " Tastiera ",$title);
                $title = str_replace("Keyboard", "Tastiera",$title);


        	$title = str_replace("wearable webcam", "Webcam Portatile",$title);
        	//$title = str_replace("Night Vision", "Indulgencia",$title);
        	$title = str_replace("Speaker", "Altoparlante",$title);
        	$title = str_replace("Headphone", "Cuffietta",$title);
		$title = str_replace("Telephone, ", "Telefonon, ",$title);
		$title = str_replace("Color ", "a colori ",$title);
		$title = str_replace("Label Maker", "Etichettatrice",$title);
		$title = str_replace("Wireless", "Senza fili",$title);
		$title = str_replace("wireless", "Senza fili",$title);
		$title = str_replace("Ultrathin Touch Mouse", "Touch Mouse Ultrasottile",$title);
		$title = str_replace("internal", "Intero",$title);
		$title = str_replace("Internal", "Intero",$title);
		$title = str_replace("External", "Esterno",$title);

                //Writer
        	$title = str_replace("Writer", "Masterizzatore",$title);
        	$title = str_replace("writer", "Masterizzatore",$title);

                $title = str_replace("Parallel", "Parallela",$title);
        	$title = str_replace("Controller", "Controllore",$title);
        	$title = str_replace("Digital Oscilloscope", "Oscilloscopio Digitale",$title);
        	$title = str_replace("Channels", "Canali",$title);
        	$title = str_replace("Dolby Support", "Supporto Dolby",$title);
        	$title = str_replace("Degree Wide Angle", "Gradi Grandangolare",$title);
        	$title = str_replace("Digital Microscope", "Microscopio Digitale",$title);
        	$title = str_replace("Document Camera", "Camera Documento",$title);
        	$title = str_replace("with Goose Neck", "con collo di cigno",$title);
        	$title = str_replace("Preamplifier", "Preamplificatore",$title);
        	$title = str_replace("preamp", "Preamplificatore",$title);
        	$title = str_replace("Analog Cable", "Cavo Analogico",$title);

        	return $title;
        }


	if ($region == "cn") {

                # combinations
        	$title = str_replace("Wireless Mobile Broadband Modem", "无线移动宽带调制解调器",$title);

		$title = str_replace("wearable webcam", "可穿戴式摄像头",$title);
		$title = str_replace("for Notebooks", "用于笔记本电脑",$title);
		$title = str_replace("Video Grabber", "视频采集卡",$title);


		$title = str_replace("Firewire", "火线",$title);
		$title = str_replace("Radio", "收音机",$title);
		$title = str_replace("Analog", "类似物",$title);
		$title = str_replace("Ethernet", "以太网",$title);
		$title = str_replace("Microphone", "麦克风",$title);
		$title = str_replace("Stereo", "立体声",$title);
		$title = str_replace("Headphone", "耳机",$title);
		$title = str_replace("Laser Mouse", "激光鼠标",$title);
		$title = str_replace("USB Adapter", "USB适配器",$title);
		$title = str_replace("Lenovo ", "Lenovo 联想 ",$title);
		$title = str_replace("Samsung ", "Samsung 三星 ",$title);
		$title = str_replace("Mainboard ", "主板 ",$title);
		$title = str_replace("Scanner ", "扫描器 ",$title);
		$title = str_replace("Scanner, ", "扫描器, ",$title);
		$title = str_replace("Ultrabook ", "超极本 ",$title);
		$title = str_replace("Bluetooth ", "蓝牙 ",$title);
		$title = str_replace("Bluetooth", "蓝牙",$title);
		$title = str_replace("Internet ", "因特网 ",$title);
		$title = str_replace("Internet, ", "因特网, ",$title);
		$title = str_replace("Intel ", "Intel 英特尔 ",$title);
		$title = str_replace("Network Card ", "网卡 ",$title);
		$title = str_replace("Fujitsu ", "Fujitsu 富士通 ",$title);
		$title = str_replace("Telephone, ", "电话, ",$title);
		$title = str_replace("Sony ", "Sony 索尼 ",$title);
		$title = str_replace("Toshiba ", "Toshiba 东芝 ",$title);
		$title = str_replace("Laser Printer", "激光打印机",$title);
		$title = str_replace("Printer, ", "打印机, ",$title);
		$title = str_replace("Copier, ", "复印机, ",$title);
		$title = str_replace("Fax, ", "传真, ",$title);
		$title = str_replace("WiFi", "无线",$title);
		$title = str_replace("HDD", "硬盘",$title);
		$title = str_replace("with", "同",$title);
		$title = str_replace("Webcam", "摄像头",$title);
		$title = str_replace(" TV", " 电视",$title);
		$title = str_replace("Logitech ", "Logitech 罗技",$title);
		$title = str_replace("Sound card", "声卡",$title);
		$title = str_replace("Speaker", "扬声器",$title);
		$title = str_replace("Asus", "Asus 华硕",$title);
		$title = str_replace("Microscope", "显微镜",$title);
		$title = str_replace("Hauppauge", "Hauppauge 哈帕克",$title);
		$title = str_replace("Keyboard", "键盘",$title);
		$title = str_replace("Fax", "传真",$title);
		$title = str_replace("Mouse", "鼠标",$title);
		$title = str_replace("Wireless", "无线",$title);
		$title = str_replace("Remote", "远程",$title);
		$title = str_replace("Card", "卡",$title);
		$title = str_replace("card", "卡",$title);
		$title = str_replace("Headphone", "耳机",$title);

        	return $title;
        }


	if ($region == "co.jp") {
                # combinations
        	$title = str_replace("Wireless Mobile Broadband Modem", "ワイヤレスモバイルブロードバンドモデム",$title);


		$title = str_replace("Lenovo ", "レノボ(Lenovo) ",$title);
		$title = str_replace("Samsung ", "サムスン(Samsung) ",$title);
		$title = str_replace("Mainboard ", "メインボード ",$title);
		$title = str_replace("Scanner ", "スキャナー ",$title);
		$title = str_replace("Scanner, ", "スキャナー, ",$title);
		$title = str_replace("Ultrabook ", "超极本 ",$title);
		$title = str_replace("Bluetooth ", "ブルートゥース ",$title);
		$title = str_replace("Internet ", "インターネット ",$title);
		$title = str_replace("Internet, ", "インターネット, ",$title);
		$title = str_replace("Intel ", "インテル(Intel) ",$title);
		$title = str_replace("Network Card ", "ネットワークカード ",$title);
		$title = str_replace("Fujitsu ", "富士通(Fujitsu) ",$title);
		$title = str_replace("Telephone, ", "電話, ",$title);
		$title = str_replace("Sony ", "ソニー(Sony) ",$title);
		$title = str_replace("Toshiba ", "東芝(Toshiba) ",$title);
		$title = str_replace("Laser Printer", "レーザープリンタ",$title);
		$title = str_replace("Printer, ", "プリンタ, ",$title);
		$title = str_replace("Copier, ", "コピー機, ",$title);
		$title = str_replace("Fax, ", "ファックス, ",$title);
		$title = str_replace("WiFi", "無線LAN",$title);
		$title = str_replace("HDD", "ハードディスク",$title);
		$title = str_replace("with", "共に",$title);
		$title = str_replace("Webcam", "ウェブカメラ",$title);
		$title = str_replace(" TV", " テレビ",$title);
		$title = str_replace("Logitech ", "テレビ Logicool ",$title);
		$title = str_replace("Sound card", "テレビ",$title);
		$title = str_replace("Speaker", "テレビ",$title);
		//$title = str_replace("Asus", "Asus 华硕",$title);
		$title = str_replace("Microscope", "テレビ",$title);
		$title = str_replace("Hauppauge", "ホーポージ(Hauppauge)",$title);

        	return $title;
        }

        return $title;


}

//menu
global $txt_hwprofile;

//wp-one-post-widget.php
global $txt_summary;
global $txt_summary;//    	= "&Uuml;bersicht";
global $txt_supplier;// 		= "Anbieter";
global $txt_cheapest_supplier;// 	= "G&uuml;nstigster Anbieter";
global $txt_rating;//     	= "Bewertung";
global $txt_ratings;//    	= "Bewertungen";
global $txt_select;//     	= "Select country &amp; currency";
global $txt_sim_tags;//   	= "Verwandte Begriffe";
global $txt_combine_tags;// 	= "Begriffe kombinieren";
global $txt_register;//   	= "Artikel beobachten:";
global $txt_register_long;//	= "Info bei Aktualisierungen und Kommentaren erhalten";
global $txt_send;//               = "Senden";
global $txt_manage_subscr;//      = "Registrierungen bearbeiten";
global $txt_not_avail;//          = "Nicht vorr&auml;tig bei";
global $txt_curr_not_avail;//          = "Nicht vorr&auml;tig bei";
global $txt_rate_yourself;//      = "Rate Hardware";
global $txt_opw_num_ratings; //    = "Number of ratings";
global $txt_opw_average_rating;// = "Average ratings";
global $txt_opw_rating_overview;
global $txt_opw_hardware;
global $txt_opw_registered;

//Scan overview widget
global $txt_Rating;//     	= "Bewertung";
global $txt_user_rating_for_setup;


//comments.php
global $txt_no_respo;   	//= "No responses";
global $txt_one_resp;  		//= "One response";
global $txt_responses;
global $txt_to;  		//= "to";
global $txt_comments;   	//= "Comments";
global $txt_comments_intro;     //= "Please use the comment section to submit corrections to the article as well as relevant excerpts of <tt>lspci, lsusb, lshw, dmesg</tt> e.t.c.
global $txt_comments_new_discussion;
//searchform.php
global $txt_search;

//amazon-product-in-a-post.php
global $txt_compat;//  	= "Linux-Kompatibilit&auml;t";
global $txt_Compat;//  	= $txt_compat;
global $txt_with;//       = "bei";
global $txt_rating;//     = "Bewertung";
global $txt_ratings;//    = "Bewertungen";
global $txt_price;//      = "Preis";
global $txt_out_of_stock;// = "nicht lieferbar";
global $txt_button;//     = "/wp-uploads/2012/11/amazon-130.png";
global $txt_button_width;// = "";
global $siteurl;// 	= "linux-hardware.no-ip.org";
global $txt_pricetrend;// = "Preisentwicklung";
global $txt_A_preis;//    = "Amazon.de-Preis";
global $txt_currency;//   = "Euro";
global $txt_shipping;//	= "ohne Versandkosten";
global $txt_shipping_costs;//	= "ohne Versandkosten";
global $txt_on_stock;//   = "auf Lager";
global $txt_category;//   = "Kategorie";
global $txt_date;//       = "Datum";
global $txt_updated;//    = "Stand";
global $txt_tooltip2;//   = "Die von Amazon zur Verf&uuml;gung gestellten Preise sind exklusive m&ouml;glicherweise zus&auml;tzlich anfallender Versandkosten (abh&auml;ngig vom jeweiligen Anbieter des Amazon-Marketplace).";
global $txt_buy_from;
global $txt_search_at;
global $txt_preorder;
global $txt_not_avail_at;
global $txt_never_avail;
global $txt_amz_getfrom;
global $txt_amz_asin;//   = "Amazon Product ASIN (ISBN-10)";
global $txt_amz_tooltip_loggedin;
global $txt_amz_tooltip_not_loggedin;


//header.php
global $txt_reg;
global $txt_login;

//footer.php
global $twitAcc;//="LinuxHardware";
global $FL_Thing;//="1446180/Linux-Hardware-Guide-com";
global $FURL;// = "http://www.linux-hardware-guide.com";
global $FMail;// = "linux.hardware.guide@gmail.com";
global $txt_follow_twitter;// = "Follow us on Twitter";
global $txt_mail_us;// = "Mail us";
global $txt_flattr_us;// = "Flattr us";
global $txt_design;
global $txt_for_mobile;
global $txt_footer_general;
global $txt_footer_contact;
global $txt_footer_faq;
global $txt_footer_contributors;
global $txt_footer_support;
global $txt_footer_problem;
global $txt_footer_donate;
global $txt_footer_suppliers;
global $txt_footer_hwsuppliers;
global $txt_footer_manufacturers;
global $txt_footer_advertisers;
global $txt_footer_credits;

//comments-template.php
global $txt_privacy_stat;//	= "Email address will not be published.";
global $txt_compat_rat;//             = "Rate Linux compatibility";
global $txt_use_tags;//           = 'Use the following <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes';
global $txt_logged_in_as;//       = "Logged in as";
global $txt_logout;//             = "Log out";
global $txt_send_comment;//       = "Submit comment";
global $txt_cancel_reply;//       = 'Cancel reply';
global $txt_reply_to;//           = 'Reply to';
global $txt_comments;//           = "Comments";
global $txt_comment;//            = "";
global $txt_to_comment;//         = "Comment";
global $txt_website;//            = "Web site";
global $txt_reply;//              = "Reply";
global $txt_says;//               = "says";
global $txt_product_rating;

//avatar tooltip
global $txt_avt_reguser;
global $txt_avt_karmapoints;
global $txt_avt_rank;
global $txt_avt_click;


// attachment
global $txt_att_maxsize;
global $txt_att_filetypes;
global $txt_att_attachment;

// Cubepoints
global $txt_cp_karma;
global $txt_cp_points;
global $txt_cp_donates_to;
global $txt_cp_language;
global $txt_cp_title;
global $txt_cp_quarterly;
global $txt_cp_totalkarma;

//user-submit-form
global $txt_submit_name;
global $txt_product;
global $txt_link;
global $txt_description;
global $txt_descr_details;
global $txt_picture;
global $txt_more_pic;
global $txt_submit;

//search.php
global $txt_search_results;
global $txt_no_results;

//s8-custom-login
global $txt_remember_me;// 'Remember me'
global $txt_username;//  	= "Username";
global $txt_register;//  	= "Register";
global $txt_mail;//  	= "Email Adress";
global $txt_pwd_send;//   = "A password will be e-mailed to you.";
global $txt_new_pwd;//    = "Get New Password";
global $txt_user_or_mail;// = "Username or Email";

//registration-form-widget
global $txt_reg_user;// = "Username";
global $txt_reg_mail;// = "Email";

//pluggable.php
global $txt_mail_username;
global $txt_mail_password;
global $txt_mail_URL;
global $txt_mail_welcome;
global $txt_mail_end;
global $txt_mail_unp;

//project tasks plugin
global $txt_pt_open;
global $txt_pt_overview;
global $txt_pt_overview;// = "Payment Overview"; //Bezahlungs-&Uuml;bersicht
global $txt_pt_article;// = "Article"; //Artikel
global $txt_pt_user   ;// = "User"; //Bearbeiter
global $txt_pt_payment;// = "Payment"; //Bezahlung
global $txt_pt_payedat;// = "Payed at"; //Bezahlt am
global $txt_pt_add_pay;// = "Add payment";
global $txt_pt_payments;// = "Payment"; //Bezahlung
global $txt_pt_amount; // Amount
global $txt_pt_pending;
global $txt_pt_paid;

//twitter widget (+ dashboad overview)
global $txt_twt_flattr;
global $txt_twt_paypal;
global $txt_twt_overview;
global $txt_twt_statistic;
global $txt_twt_userid;
global $txt_twt_hwnum;
global $txt_twt_commnum;
global $txt_twt_payment;
global $txt_twt_actnum;
global $txt_twt_pending;
global $txt_twt_payd;
global $txt_twt_maintext1;
global $txt_twt_maintext2;
global $txt_twt_title;

//related posts thumbnails
global $txt_rpt_creation; //="creation date";
global $txt_rpt_update; //="last update";
global $txt_rpt_title;
global $txt_rpt_sub_by;

//wp-admin/admin-footer.php
global $txt_admin_footer;
global $lhg_txt_new;

//category post list
global $txt_cpl_noimage;
global $txt_cpl_comments; //="Kommentare";
global $txt_cpl_comment;

//wp-postrating
global $txt_vote;// = "Bewertung";
global $txt_votes;// = "Bewertungen";
global $txt_average;// = "Durchschnitt";
global $txt_out_of;// = "von maximal";

//archive.php
global $txt_arch_related_cat ;//  = "Verwandte Kategorien";
global $txt_arch_category    ;// = "Kategorie:";
global $txt_arch_search_terms;// = "Suchbegriffe";
global $txt_arch_and         ;// = "und";
global $txt_arch_combination ;// = "Kombination aus:";
global $txt_arch_display_opt ;// = "Anzeige-Optionen";
global $txt_arch_sort_price  ;// = "Sortieren nach Preis";
global $txt_arch_sort_rating ;// = "Sortieren nach Bewertung";
global $txt_arch_show_avail  ;// = "nur lieferbare anzeigen";
global $txt_arch_stars       ;// = "Sterne";
global $txt_arch_home; // Home
global $txt_filter_by_rating;
global $txt_filter_by_tags;
global $txt_sorting_options;

//bwp-comments
global $txt_bwp_commenton;

//scan overview table
global $txt_scan_distribution;
global $txt_scan_kernel;
global $txt_scan_scandate;
global $txt_scan_result;
global $txt_scan_results;
global $txt_scan_title;
global $txt_scan_text;


//subscribe
global $txt_subscr_component_added;
global $txt_subscr_return;
global $txt_subscr_to_hw;
global $txt_subscr_or_manage;
global $txt_subscr_answer;
global $txt_subscr_answer_logged_in;  //Added: 30.05.
global $txt_compat              ;//  		= "Legende: Y = all comments, R = replies only, C = inactive";
global $txt_regist_date  	;// = "Registration Date";
global $txt_modus	  	;//= "Status";
global $txt_article	  	;//= "Hardware Overview";
global $txt_select_all		;//= "Select all";
global $txt_select_inv		;//= "Invert selection";
global $txt_action		;//= "Action";
global $txt_delete	 	;//= "Delete Entry";
global $txt_suspend	 	;//= "Deactivate Entry";
global $txt_reply_only	 	;//= "Replies to my comments";
global $txt_activate	 	;//= "Stay informed";
global $txt_update              ;//= "Update";
global $txt_active              ;//= "Stay informed";
global $txt_inactive            ;//= "Deactivated";
global $txt_manage_hw           ;//= "Manage Your Hardware Profile";
global $txt_subscr_pub_hw_prof;
global $txt_subscr_name;
global $txt_subscr_email;
global $txt_subscr_more;
global $txt_subscr_rating;
global $txt_subscr_edit;
global $txt_subscr_notrated;
global $txt_subscr_language;
global $txt_subscr_regdate;
global $txt_subscr_numart;
global $txt_subscr_numcom;
global $txt_subscr_public_hw_profile;
global $txt_subscr_lat_sub; 	//= "Latest hardware submissions";
global $txt_subscr_rank; 	//= "Latest hardware submissions";
global $txt_subscr_nextpromo;  //Next promotion
global $txt_subscr_nohw;
global $txt_subscr_scanoverview;  # Scan overview
global $txt_subscr_kernelversion; #   = 'Kernel version';
global $txt_subscr_hwcomp; #          = "Hardware Components";
global $txt_subscr_identified; #      = "Identified";
global $txt_subscr_unknown; #         = "Unknown";
global $txt_subscr_knownhw;
global $txt_subscr_addhw;#	    = "Add HW to your profile";
global $txt_subscr_ratecomp;#        = "Please rate<br>Linux compatibility";
global $txt_subscr_nohwfound;#       = "No hardware found";
global $txt_subscr_hwfeedback;
global $txt_subscr_multiple;
global $txt_subscr_identified_usb;
global $txt_subscr_option;
global $txt_subscr_thisscan;#        = "This scan was performed at";
global $txt_subscr_notice;#          = "Please note that this web service is still under development. All your scan results were successfully transferred to the Linux-Hardware-Guide team.
global $txt_subscr_limitation; #This tool is limited to...
global $txt_subscr_newhw;
global $txt_subscr_foundhwid; #        = "Hardware Identifier";
global $txt_subscr_rate;
global $txt_subscr_pleaserate;
global $txt_subscr_type;
global $txt_subscr_help;
global $txt_subscr_ifpossible;
global $txt_subscr_new;
global $txt_subscr_thankyou;
global $txt_subscr_hwscantitle;
global $txt_yes;
global $txt_no;


global $txt_hwprof_of;

//mobile theme
global $txt_mobile_search; //Search by article title
global $txt_mobile_show_all;
global $txt_mobile_add_hw;
global $txt_mobile_forum;

//curstom recent post
global $txt_crp_title; 

//lhg price db
global $txt_lhgdb_overview;
global $txt_lhgdb_exclporto;
global $txt_lhgdb_inclporto;
global $txt_lhgdb_welcome;
global $txt_lhgdb_karmapoints;
global $txt_lhgdb_karmadescr;
global $txt_lhgdb_karma_howto;
global $txt_lhgdb_karma_1;
global $txt_lhgdb_karma_2;
global $txt_lhgdb_karma_3;
global $txt_lhgdb_karma_4;

//Live Search
global $moreResultsText;


//
//default translation = english
//

$txt_hwprofile          = " My Hardware Profile";



//wp-one-post-widget
$txt_summary    	= "Summary";
$txt_supplier  		= "Supplier";
$txt_cheapest_supplier 	= "Cheapest supplier";
$txt_rating     	= "rating";
$txt_Rating     	= "Rating";
$txt_ratings    	= "ratings";
$txt_select     	= "Select country &amp; currency";
$txt_sim_tags   	= "Similar tags";
$txt_combine_tags 	= "Combine";
$txt_wpop_register   	= "Add to your hardware profile:";
$txt_register_long	= "Stay informed about Linux news and discussions concerning this component.";
$txt_send               = "Add";
$txt_manage_subscr      = "Manage subscriptions";
$txt_not_avail          = "Out of stock at";
$txt_rate_yourself      = "Rate Hardware";
$txt_opw_num_ratings    = "Number of ratings";
$txt_opw_average_rating = "Average ratings";
$txt_opw_rating_overview= "Rating overview";
$txt_opw_hardware       = "Hardware";
$txt_opw_registered	= "Registered Linux users";

$txt_user_rating_for_setup = "The user provided the following rating for this hardware:";


//comments.php
$txt_no_respo   	= "No responses";
$txt_one_resp  		= "One response";
$txt_responses          = "Responses";
$txt_to  		= "to";
$txt_comments   	= "Comments";
$txt_comments_intro     = "Please use the comment section to submit corrections to the article as well as relevant excerpts of <tt>lspci, lsusb, lshw, dmesg</tt> e.t.c.
	 Furthermore, use the section for the exchange of experiences with this hardware component or search for configuration help from other owners of this hardware.
 	<br />&nbsp;<br />";

// Cubepoints
$txt_cp_karma		= "Karma";
$txt_cp_points		= "points";
$txt_cp_donates_to	= "Donates to";
$txt_cp_language	= "Language";
$txt_cp_longtext	= 'The Linux-Hardware-Guide donates monthly banner advertising revenue back to of the Linux community.
  Registered users vote with their Karma points to whom donations go (<a href="./donations">see more details</a>). Donation sum of ongoing month';
$txt_cp_title		= "Most active users and donations";
$txt_cp_quarterly       = "Quarterly Points";
$txt_cp_totalkarma      = "Total Karma";
$txt_cp_details         = "Details";


//searchform.php
$txt_search 		= "Search".'&nbsp;<i class="icon-arrow-right icon-button"></i>';

//amazon-product-in-a-post.php
$txt_compat  	= "Linux compatibility";
$txt_Compat  	= "Review of Linux Compatibility";
$txt_with       = "with";
$txt_rating     = "rating";
$txt_ratings    = "ratings";
$txt_price      = "Price";
$txt_out_of_stock = "out of stock";
$txt_button     = "/wp-uploads/amazon-button.png";
$txt_button_width = 'width="185"';
$siteurl 	= "linux-hardware-guide.com";
$txt_pricetrend = "Price trend";
$txt_shipping	= "without shipping costs";
$txt_shipping_costs	= "Shipping costs";
$txt_on_stock   = "on stock";
$txt_category   = "Category";
$txt_date       = "Date";
$txt_updated    = "Updated";
$txt_tooltip2   = "Prices provided by Amazon do not include shipping costs that might be due depending on product and Amazon Marketplace supplier.";
$txt_buy_from   = "Buy from";
$txt_search_at  = "Search at";
$txt_preorder   = "Reserve at";
$txt_not_avail_at = "Not available at the moment at";
$txt_never_avail= "Not in the catalog of";
$txt_amz_getfrom= 'You will need to get this from <a href="http://amazon.com/">Amazon.com</a>';
$txt_amz_asin   = "Amazon Product ASIN (ISBN-10)";
//$txt_amz_title  = "Amazon Product Information";
$txt_amz_tooltip_loggedin     = 'Only the Linux compatibility is rated on this page, not the general quality of the product. <hr style="height: 4pt; visibility: hidden;">If you use this product with Linux, please rate it and share your experience <a href="#comments">in the comment area of this page (bottom)</a> to support other Linux users.';
$txt_amz_tooltip_not_loggedin = 'Only the Linux compatibility is rated on this page, not the general quality of the product. If you use this product with Linux, please rate it and share your experience in the comment area of this page (bottom) to support other Linux users.';


//header.php
$txt_reg   = "Register";
$txt_login = "Login";

//footer.php
$twitAcc="LinuxHardware";
$FL_Thing="1446180/Linux-Hardware-Guide-com";
$FURL = "http://www.linux-hardware-guide.com";
$FMail = "linux.hardware.guide@gmail.com";
$txt_follow_twitter = "Follow us on Twitter";
$txt_mail_us = "Mail us";
$txt_flattr_us = "Flattr us";
$txt_designed = "Designed by";
$txt_for_mobile = "Small screen version";
$txt_footer_general="General";
$txt_footer_contact="About us";
$txt_footer_faq="F.A.Q.";
$txt_footer_contributors="Contributors";
$txt_footer_support="Support the community";
$txt_footer_problem="Report a problem";
$txt_footer_donate="Donations";
$txt_footer_suppliers="Suppliers &amp; Advertisers";
$txt_footer_hwsuppliers="Hardware Supplies";
$txt_footer_manufacturers="Hardware Manufacturers";
$txt_footer_advertisers="Advertisers";
$txt_footer_credits="Credits";


//comments-template.php
$txt_privacy_stat	= "Email address will not be published.";
$txt_compat_rat         = "Rate Linux compatibility";
$txt_use_tags           = 'Use the following <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes';
$txt_logged_in_as       = "Logged in as";
$txt_logout             = "Log out";
$txt_send_comment       = "Send comment";
$txt_cancel_reply       = 'Cancel reply';
$txt_reply_to           = 'Reply to';
$txt_comments           = "Comments";
$txt_comments_new_discussion  = "Start new Discussion";
$txt_comment            = "";
$txt_to_comment         = "Comment";
$txt_website            = "Web site";
$txt_reply              = "Reply";
$txt_says               = "says";
$txt_product_rating     = "Product rating";

//avatar tooltip
$txt_avt_reguser	= "Registered user";
$txt_avt_karmapoints	= "Karma points";
$txt_avt_rank		= "Rank";
$txt_avt_click		= "click avatar for user's profile";

//scan overview table
$txt_scan_distribution  = "Distribution";
$txt_scan_kernel        = "Kernel";
$txt_scan_scandate      = "Scan Date";
$txt_scan_result	= "Result";
$txt_scan_results	= "Results";
$txt_scan_title		= "Identified Hardware Configurations";
$txt_scan_text          = 'This hardware component was used by Linux users under the following system configurations. These results were collected by our <a href="./add-hardware">LHG Scan Tool</a>:';


//attachment
$txt_att_filetypes	= "Allowed file types";
$txt_att_maxsize  	= "maximum file size";
$txt_att_attachment  	= "Attachment";

//user-submit-form.php
$txt_submit_name = "Your Name or e-mail address";
$txt_product  	 = "Product ID or product name:";
$txt_link        = "Web page with product specification (e.g. supplier page or Amazon page):";
$txt_description = "Description:";
$txt_descr_details =
'For example:
necessary configuration steps to use the hardware with Linux,
Linux kernel version,
distribution,
output of dmesg,
output of lspci, lsusb, and lshw...';
$txt_picture     = "Upload picture(s):";
$txt_more_pic    = "more pictures";
$txt_submit      = 'Submit';
$txt_anonymous   = "Anonymous";

//search.php
$txt_search_results  = "Search results";
$txt_no_result       = "No posts found. Try a different search?";

//s8-custom-login
$txt_remember_me = 'Remember me';
$txt_username  	= "Username";
$txt_register  	= "Register";
$txt_mail  	= "Email Adress";
$txt_pwd_send   = "A password will be e-mailed to you.";
$txt_new_pwd    = "Get New Password";
$txt_user_or_mail = "Username or Email";

//registration-form-widget
$txt_reg_user = "Username";
$txt_reg_mail = "Email";

//pluggable.php
$txt_mail_username = "Username";
$txt_mail_password = "Password";
$txt_mail_URL = "http://www.linux-hardware-guide.com/login";
$txt_mail_welcome = "Welcome to Linux-Hardware-Guide.com:\r\nPlease find below your username and your automatically created password.\r\n";
$txt_mail_end = "You can login at http://www.linux-hardware-guide.com/login and change your settings, e.g. password and avatar.\r\nBest regards,\rthe Linux-Hardware-Guide Team";
$txt_mail_unp = "Your username and password";

$region = get_region();
//echo "REG: $region";

//project tasks plugin
$txt_pt_open = "Earnings";
$txt_pt_overview = "Earning Overview"; //Bezahlungs-&Uuml;bersicht
$txt_pt_article = "Article"; //Artikel
$txt_pt_user    = "User"; //Bearbeiter
$txt_pt_payment = "Earning"; //Bezahlung
$txt_pt_payedat = "Payed at"; //Bezahlt am
$txt_pt_add_pay = "Add payment";
$txt_pt_payments = "Payments"; //Bezahlung
$txt_pt_amount = "Amount";
$txt_pt_pending = "pending";
$txt_pt_paid = "paid";


//Twitter Widget (+dahsboard overview)
$txt_twt_title='<i class="icon-twitter menucolor"></i> News @Twitter';
$txt_twt_flattr='You have not set your Flattr-ID. Please enter this information in your <a href="/wp-admin/profile.php">Profile Page</a> to earn money with your comments.';
$txt_twt_paypal='You have not set your PayPal email addresse and therefore can not get paid. Please enter this information in your <a href="/wp-admin/profile.php">Profil Page</a>.';
$txt_twt_overview="Overview";
$txt_twt_statistic="Statistic";
$txt_twt_userid="User ID";
$txt_twt_hwnum="added hardware posts";
$txt_twt_commnum="comments posted";
$txt_twt_payment="Earnings and Payments";
$txt_twt_actnum="Activities related to earnings";
$txt_twt_pending="Pending earnings";
$txt_twt_payd="Already received earnings";
$txt_twt_maintext1='We would like to thank everyone who contributes to our data base. Therefore, we will pay everyone who writes posts for the Linux-Hardware-Guide:

 			<table>
			   <tr class="first"><td class="first b b-posts" width="100px" align=right>
                             <strong>maximum 2.00&euro;</strong></td><td class="t posts">per accepted post*</a></td></tr>
                           <tr><td class="first b b_pages" align=right>
                             <strong>at least 0.50&euro;</strong></td><td class="t pages">per accepted post*</td></tr>
                           <tr><td class="first b b-cats" valign=top align=right>
                             <strong>+0.50&euro;</strong></td><td class="t cats">if the described product is available at Amazon.com at the release date of the post</td></tr>
                           <tr><td class="first b b-cats" align=right>
                             <strong>up to +1.00&euro;</strong></td><td class="t cats">depending on the quality of the posts (number of words, pictures, ...)</td></tr>
			</table>';

$txt_twt_maintext2='<span>
                       The following rules for earnings are valid:
                            <br>- At least 10&euro; have to be earned to receive a payment via PayPal.
                       <br>During the momentarily active test phase the following additional rules are valid:
                            <br>- At maximum 30 articles per month are paid.
                            <br>- Total maximum payment for all authors: 150&euro; per month.
                        </span>


                       <p style="padding-top: 5px;">
                       If you have questions please contact us at <a href="mailto:linux.hardware.guide@gmail.com">linux.hardware.guide@gmail.com</a>.
                       <p style="border-top: 1px solid #CCC; padding-top: 10px;">
                        *The Linux-Hardware-Guide Team decides if a post has the necessary quality to be accepted.
';

//related posts thumbnails
$txt_rpt_creation="creation date";
$txt_rpt_update="last update";
$txt_rpt_title="<h2>Similar Linux Hardware</h2>";//<!--:us--><!--:--><!--:fr--><h2>Mat&eacute;riel Linux Similaire</h2><!--:--><!--:zh--><h2>类似Linux的硬件</h2><!--:--><!--:ja--><h2>同じようなLinuxのハードウェア</h2><!--:--><!--:es--><h2>Linux Hardware Similares</h2><!--:--><!--:it--><h2>Simile Linux Hardware</h2><!--:-->
$txt_rpt_sub_by="submitted by";

//wp-admin/admin-footer.php
$txt_admin_footer='Please contact <a mailto="linux.hardware.guide@gmail.com">linux.hardware.guide@gmail.com</a> in case of questions.';

//category post list
$txt_cpl_noimage = "No image available";
$txt_cpl_comments= "comments"; //="Kommentare";
$txt_cpl_comment = "comment";

//wp-postrating
$txt_vote = "vote";
$txt_votes = "votes";
$txt_average = "average";
$txt_out_of = "out of";

//archive.php
$txt_arch_related_cat  = "Related Categories";
$txt_arch_category     = "Category:";
$txt_arch_search_terms = "Search Terms";
$txt_arch_and          = "and";
$txt_arch_combination  = "Combination of:";
$txt_arch_display_opt  = "Display Options";
$txt_arch_sort_price   = "sort by price";
$txt_arch_sort_rating  = "sort by rating";
$txt_arch_show_avail   = "only show if available";
$txt_arch_show_avail   = "only show if available";
$txt_arch_stars        = "stars";
$txt_arch_home	       = "Home";
$txt_filter_by_rating  = "Filter by rating";
$txt_filter_by_tags    = "Filter by tags";
$txt_sorting_options   = "Sorting options";

//bwp-comments
$txt_bwp_commenton     = "Comment on";

//subscribe
$txt_subscr_component_added = "Component Added to Profile";
$txt_subscr_was_added       = "The following hardware component was added to your Hardware Profile";
$txt_subscr_return 	    = "Return to the";
$txt_subscr_to_hw	    = "hardware description";
$txt_subscr_or_manage	    = "or manage your Hardware Profile below.";
$txt_subscr_answer 	    = "Receive answers per mail";
$txt_subscr_answer_logged_in = "Add to hardware profile and receive answers per mail.";
$txt_subscr_compat  	    = "Legende: Y = all comments, R = replies only, C = inactive";
$txt_subscr_regist_date     = "Registration Date";
$txt_subscr_modus	    = "Status";
$txt_subscr_article	    = "Hardware Overview";
$txt_subscr_select_all	    = "Select all";
$txt_subscr_select_inv	    = "Invert selection";
$txt_subscr_action	    = "Action";
$txt_subscr_delete	    = "Delete Entry";
$txt_subscr_suspend	    = "Deactivate Entry";
$txt_subscr_reply_only	    = "Replies to my comments";
$txt_subscr_activate	    = "Stay informed";
$txt_subscr_update          = "Update";
$txt_subscr_active          = "Stay informed";
$txt_subscr_inactive        = "Deactivated";
$txt_subscr_manage_hw       = "Manage Your Hardware Profile";
$txt_subscr_pub_hw_prof     = "Public Hardware Profile";
$txt_subscr_name 	    = "Name";
$txt_subscr_email	    = "Email";
$txt_subscr_more	    = "Show more";
$txt_subscr_rating          = "Your Rating";
$txt_subscr_edit_rating	    = "Edit rating";
$txt_subscr_edit_comment    = "Edit comment";
$txt_subscr_notrated	    = "No comment with rating found";
$txt_subscr_language	    = "Language";
$txt_subscr_regdate	    = "Registered user since";
$txt_subscr_numart	    = "Number of submitted hardware descriptions";
$txt_subscr_numcom	    = "Number of comments";
$txt_subscr_public_hw_profile = "Hardware Profile";
$txt_subscr_lat_sub	    = "Latest hardware submissions";
$txt_subscr_rank	    = "Rank";
$txt_subscr_nextpromo       = "Next promotion";
$txt_subscr_nohw	    = 'No hardware components were added to the profile so far.';
$txt_subscr_scanoverview    = 'Scan overview';
$txt_subscr_kernelversion   = 'Kernel version';
$txt_subscr_hwcomp          = "Hardware Components";
$txt_subscr_identified      = "Identified";
$txt_subscr_unknown         = "Unknown";
$txt_subscr_knownhw	    = "Known Hardware";
$txt_subscr_addhw	    = "Add HW to your profile";
$txt_subscr_ratecomp        = "Please rate<br>Linux compatibility";
$txt_subscr_nohwfound       = "No hardware found";
$txt_subscr_hwfeedback      = "Please let us know if certain hardware was recognized incorrectly or not recognized at all.<br>
				       This helps us improving the automatic hardware recognition for future scans:";
$txt_subscr_multiple        = "Multiple possibilites (Ambiguously Identified Hardware)";
$txt_subscr_identified_usb  = "Identified USB Device";
$txt_subscr_option          = "Option";
$txt_subscr_thisscan        = "This scan was performed at";
$txt_subscr_notice          = "Please note that this web service is still under development. All your scan results were successfully transferred to the Linux-Hardware-Guide team.
			       However, the automatic recognition of hardware and its representation on this scan overview page for sure is still incomplete.";
$txt_subscr_limitation      = "
<p>This tool is currently limited to following hardware components:
<ul><li>USB devices
<li>PCI devices
<li>Mainboards (experimental)
<li>Laptops (experimental)
<li>CPUs
<li>Storage media (HDD, CD, DVD, SSD)
</ul>";
$txt_subscr_newhw	     = "New Hardware";
$txt_subscr_foundhwid        = "Hardware Identifier";
$txt_subscr_rate	     = "Rate hardware";
$txt_subscr_pleaserate	     = "Please rate";
$txt_subscr_type             = "Hardware Type";
$txt_subscr_help             = "Help us adding this hardware to our database. Please identify this hardware and describe its Linux compatibility:";
$txt_subscr_ifpossible       = "If possible, please leave an URL to a web page where the hardware is described (e.g. manufacturer`s data sheet or Amazon.com page).<br>URL:";
$txt_subscr_new		     = "New";
$txt_subscr_thankyou	     = '<b>Thank you for using our Linux-Hardware-Guide scanning software</b> (see <a href="https://github.com/paralib5/lhg_scanner">GitHub</a> for more details).<br>
This way we can fill our Linux Hardware Database with valuable information for the Linux community.<br>';
$txt_subscr_hwscantitle	     = "Hardware Scan Overview:";
$txt_yes                     = "yes";
$txt_no                      = "no";



$txt_hwprof_of              = "Public Hardware Profile of";


//mobile theme
$txt_mobile_search	    = "Search for hardware";
$txt_mobile_show_all        = "Showing all articles";
$txt_mobile_fullsite        = "Large screen version";
$txt_mobile_add_hw          = "Add Hardware";
$txt_mobile_forum           = "Forum";

//custom recent post
$txt_crp_title		    = "Recently Added Linux Hardware";//[:fr]Matériel compatible Linux récemment ajouté[:es]Linux Hardware Recientemente Adjuntando[:zh]最近添加的硬件的Linux[:ja]最近追加されたLinuxのハードウェア[:it]Hardware Linux aggiunti di recente

//priceDB
$txt_lhgdb_overview         = "Supplier comparison";
$txt_lhgdb_exclporto	    = "Excl. shipping";
$txt_lhgdb_inclporto	    = "Incl. shipping";
$txt_lhgdb_karmapoints	    = "Karma points";
$txt_shipping_costs	    = "Shipping costs";
$txt_lhgdb_welcome          = "Weclome to the Linux-Hardware-Guide";
$txt_lhgdb_numhwscans       = "uploaded hardware scans";
$txt_lhgdb_numhwscan        = "uploaded hardware scan";
$txt_lhgdb_karmadescr       = 'Your Karma points are used for financially supporting certain Linux projects. Your donations are currently going to the '.$donation_target_text.'. Select your donation target on your <a href="./profile.php">profile page</a>.';
$txt_lhgdb_karmadescr_end   = 'Select your donation target on your <a href="./profile.php">profile page</a>.';
$txt_lhgdb_karma_howto	    = 'How to quickly earn Karma';
$txt_lhgdb_karma_1	    = '1) Upload your <a href="/add-hardware">hardware scan</a>, i.e. start the following command in a terminal';
$txt_lhgdb_karma_2	    = '2) Rate and comment on your Linux hardware';
$txt_lhgdb_karma_3	    = 'You need at least';
$txt_lhgdb_karma_4	    = 'Karma points to create new hardware articles.';

//misc
$lhg_txt_new		    = "New";

//Live Search
$moreResultsText	    = "View more results";


if ($lang == "de") {


	$txt_hwprofile          = " Mein Hardware-Profil";

        //wp-one-post-widget
        $txt_summary    	= "&Uuml;bersicht";
	$txt_supplier 		= "Anbieter";
        $txt_cheapest_supplier 	= "G&uuml;nstigster Anbieter";
        $txt_rating     	= "Bewertung";
        $txt_Rating     	= $txt_rating; #"Bewertung";
        $txt_ratings    	= "Bewertungen";
        $txt_select     	= "Select country &amp; currency";
        $txt_sim_tags   	= "Verwandte Begriffe";
        $txt_combine_tags 	= "Begriffe kombinieren";
        $txt_wpop_register   	= "Zum Hardware-Profil hinzuf&uuml;gen:";
        $txt_register_long	= "Informiert bleiben bei Diskussionen und Linux-Neuigkeiten zu dieser Komponente.";
        $txt_send               = "Hinzuf&uuml;gen";
        $txt_manage_subscr      = "Registrierungen bearbeiten";
        $txt_not_avail          = "Nicht vorr&auml;tig bei";
	$txt_rate_yourself      = "Hardware bewerten";
	$txt_opw_num_ratings    = "Anzahl Bewertungen";
	$txt_opw_average_rating = "Durchschnittl. Bewertung";
	$txt_opw_rating_overview= "&Uuml;bersicht Bewertung";
	$txt_opw_hardware       = "Hardware";
	$txt_opw_registered	= "Registerierte Linux-Nutzer";

	$txt_user_rating_for_setup = "Folgende Bewertung wurde vom Benutzer der Hardware-Konfiguration abgegeben:";


        //comments.php
        $txt_no_respo   	= "Keine Antworten";
	$txt_one_resp  		= "Eine Antwort";
	$txt_responses          = "Antworten";
	$txt_to  		= "auf";
        $txt_comments   	= "Kommentare";
        $txt_comments_intro     = "Bitte hinterlassen Sie im Kommentar-Bereich Ihren eigenen Testbericht oder Anmerkungen und Korrekturen zum Artikel.
        Besonders hilfreich sind Ausz&uuml;ge von <tt>lspci -nnk, lsusb, lshw, dmesg</tt> u.s.w.,
        falls diese noch nicht im Artikel enthalten sein sollten. Weiterhin dient der
        Kommentar-Bereich dem Erfahrungsaustausch zwischen Besitzern der Hardware-Komponente und der Suche nach Konfigurationshilfe.<br />&nbsp;<br />";
	$txt_comments_new_discussion  = "Neue Diskussion beginnen";

	//attachment
	$txt_att_filetypes	= "Erlaubte Dateitformate";
	$txt_att_maxsize  	= "maximale Dateigr&ouml;&szlig;e";
	$txt_att_attachment  	= "Anhang";

	// Cubepoints
	$txt_cp_karma		= "Karma";
	$txt_cp_points		= "Punkte";
	$txt_cp_donates_to	= "Spendet an";
	$txt_cp_language	= "Sprache";
	$txt_cp_longtext	= 'The Linux-Hardware-Guide donates monthly banner advertising revenue back to of the Linux community.
  Registered users vote with their Karma points to whom donations go (<a href="./donations">see more details</a>). Donation sum of ongoing month';
	$txt_cp_title		= "Aktivste Mitglieder und Spenden";
	$txt_cp_quarterly       = "Quartals-Punkte";
	$txt_cp_totalkarma      = "Karma";
	$txt_cp_details         = "Details";


        //searchform.php
        $txt_search             = "Suche".'&nbsp;<i class="icon-arrow-right icon-button"></i>';

        //amazon-product-in-a-post.php
	$txt_compat  	= "Linux-Kompatibilit&auml;t";
	$txt_Compat  	= $txt_compat;
        $txt_with       = "bei";
        $txt_rating     = "Bewertung";
        $txt_ratings    = "Bewertungen";
        $txt_price      = "Preis";
        $txt_out_of_stock = "nicht lieferbar";
        $txt_button     = "/wp-uploads/2012/11/amazon-130.png";
        $txt_button_width = "";
        $siteurl 	= "linux-hardware.no-ip.org";
        $txt_pricetrend = "Preisentwicklung";
        $txt_A_preis    = "Amazon.de-Preis";
        $txt_currency   = "Euro";
        $txt_shipping	= "ohne Versandkosten";
        $txt_shipping_costs	    = "Versandkosten";
	$txt_on_stock   = "auf Lager";
        $txt_category   = "Kategorie";
        $txt_date       = "Datum";
        $txt_updated    = "Stand";
        $txt_tooltip2   = "Die von Amazon zur Verf&uuml;gung gestellten Preise sind exklusive m&ouml;glicherweise zus&auml;tzlich anfallender Versandkosten (abh&auml;ngig vom jeweiligen Anbieter des Amazon-Marketplace).";
	$txt_amz_asin   = "Amazon ASIN (ISBN-10) Produkt-Nummer";
	$txt_amz_getfrom= 'Die ASIN-Nummer findet man auf der zugeh&ouml;rigen Produkt-Seite bei <a href="http://amazon.de/">Amazon.de</a>';
	//$txt_amz_title  = "Amazon Produkt-Kennung";
	$txt_amz_tooltip_loggedin     = 'Bewertet wird an dieser Stelle einzig die Linux-Kompatibilit&auml;t und nicht die Qualit&auml;t des Produktes. <hr style="height: 4pt; visibility: hidden;">Sollten Sie dieses Produkt besitzen, dann helfen Sie bitte auch anderen Linux-Benutzern, indem Sie dessen Linux-Kompatibilit&auml;t <a href="#comments">im Kommentar-Bereich dieser Seite</a> bewerten.';
	$txt_amz_tooltip_not_loggedin = 'Bewertet wird an dieser Stelle einzig die Linux-Kompatibilit&auml;t und nicht die Qualit&auml;t des Produktes. Sollten Sie dieses Produkt besitzen, dann helfen Sie bitte auch anderen Linux-Benutzern, indem Sie dessen Linux-Kompatibilit&auml;t im Kommentar-Bereich dieser Seite bewerten.';


        //header.php
	$txt_reg   = "Registrieren";
	$txt_login = "Anmelden";

        //footer.php
	$twitAcc="LinuxHWGuide";
	$FL_Thing="1446140/Linux-Hardware-Guide";
	$FURL = "http://www.linux-hardware-guide.de";
	$txt_follow_twitter = "Bei Twitter folgen";
	$txt_mail_us = "E-Mail Kontakt";
	$txt_flattr_us = "Uns Flattr'n";
        $txt_designed = "Design von";
        $txt_for_mobile = "Optimiert f&uuml;r Tablet &amp; Smartphone";
	$txt_footer_general="Allgemeines";
	$txt_footer_contact="Kontakt";
	$txt_footer_faq="Fragen &amp; Antworten";
	$txt_footer_contributors="Unterst&uuml;tzung";
	$txt_footer_support="Mitarbeiten";
	$txt_footer_problem="Problem melden";
	$txt_footer_donate="Spenden";
	$txt_footer_suppliers="Hersteller &amp; Verk&auml;ufer";
	$txt_footer_hwsuppliers="Hardware-Vertrieb";
	$txt_footer_manufacturers="Hardware-Hersteller";
	$txt_footer_advertisers="Werbe-Kunden";


        //comments-template.php
	$txt_privacy_stat	= "Email-Adresse wird nicht ver&ouml;ffentlich.";
        $txt_compat             = "Linux-Kompatibilit&auml;t";
	$txt_compat_rat         = "Linux-Kompatibilit&auml;t bewerten";
        $txt_use_tags           = 'Benutze folgende <abbr title="HyperText Markup Language">HTML</abbr>-Tags und -Attribute';
        $txt_logged_in_as       = "Angemeldet als";
        $txt_logout             = "Abmelden";
        $txt_send_comment       = "Absenden";
        $txt_cancel_reply       = 'Antwort verwerfen';
        $txt_reply_to           = 'Antworten auf';
        $txt_comments           = "Kommentieren und Bewerten";
        $txt_comment            = "";
        $txt_to_comment         = "Kommentar schreiben";
        $txt_website            = "Webseite";
        $txt_reply              = "Antworten";
        $txt_says               = "schrieb";
	$txt_product_rating     = "Produktbewertung";

	//avatar tooltip
	$txt_avt_reguser	= "Registrierter Nutzer";
	$txt_avt_karmapoints	= "Karma-Punkte";
	$txt_avt_rank		= "Rang";
	$txt_avt_click		= "Avatar anklicken f&uuml; Profilseite";

	//scan overview table
	$txt_scan_distribution  = "Distribution";
	$txt_scan_kernel        = "Kernel";
	$txt_scan_scandate      = "Scan-Datum";
	$txt_scan_result	= "Ergebnis";
	$txt_scan_results	= "Ergebnisse";
	$txt_scan_title		= "Identifizierte Hardware-Konfigurationen";
	$txt_scan_text          = 'Diese Hardware-Komponente wurde von Linux-Benutzern unter folgenden System-Konfigurationen eingesetzt. S&auml;mtliche Ergebnisse wurdens mit dem <a href="./hardware-eintragen">LHG Scan Tool</a> gesammelt:';


        //user-submit-form.php
	$txt_submit_name = "Dein Name oder Email-Adresse";
        $txt_product  	 = "Produktbezeichnung / Hardware-Kennung:";
        $txt_link        = "Link zum Produkt (z.B. Herstellerseite oder Amazon-Seite):";
        $txt_description = "Beschreibung:";
        $txt_descr_details =
'z.B.
notwendige Konfigurationsschritte,
Linux Kernel-Version,
benutzte Distribution,
Ausgabe von dmesg,
Ausgabe von lspci, lsusb und lshw...';
        $txt_picture     = "Bild(er) hinzuf&uuml;gen:";
        $txt_more_pic    = "weiteres Bild";
        $txt_submit      = 'Eintragen';
	$txt_anonymous   = "Anonym";

        //search.php
        $txt_search_results  = "Suchergebnisse";
  	$txt_no_result       = "Keine Suchergebnisse. Andere Suchbegriffe ausprobieren?";

	//s8-custom-login
	$txt_remember_me = 'Login merken';
        $txt_username  	= "Benutzername";
	$txt_register  	= "Registrieren";
	$txt_mail  	= "Email Adresse";
        $txt_pwd_send   = "Ein Passwort wird Ihnen zugeschickt.";
        $txt_new_pwd    = "Neues Passwort erstellen";
        $txt_user_or_mail = "Benutzername oder E-mail";

        //registration-form-widget
	$txt_reg_user = "Benutzername";
	$txt_reg_mail = "E-mail";

        //pluggable.php
        $txt_mail_username = "Benutzername";
	$txt_mail_password = "Passwort";
	$txt_mail_URL = "http://www.linux-hardware-guide.de/login";
	$txt_mail_welcome = "Willkommen beim Linux-Hardware-Guide.de:\r\nIhnen wurden folgende Zugangsdaten zugewiesen:\r\n";
	$txt_mail_end = "Bitte loggen Sie sich unter folgender Adresse ein: http://www.linux-hardware-guide.de/login und ändern Sie dort Ihre persönlichen Einstellungen wie z.B. Passwort und Avatar.\r\nViele Grüße,\rIhr Linux-Hardware-Guide Team";
	$txt_mail_unp = "Ihr Benutzername und Passwort";

	//project tasks plugin
	$txt_pt_open = "Einnahmen";
	$txt_pt_overview = "Einnahmen-&Uuml;bersicht";
	$txt_pt_article = "Artikel";
	$txt_pt_user    = "Bearbeiter";
	$txt_pt_payment = "Einnahmen";
	$txt_pt_payedat = "Bezahlt am";
        $txt_pt_add_pay = "Bezahlung hinzuf&uuml;gen";
	$txt_pt_payments= "Bezahlungen";
        $txt_pt_amount  = "Betrag";
        $txt_pt_pending = "offen";
	$txt_pt_paid = "bezahlt";

        //Twitter widget
	$txt_twt_title		= '<i class="icon-twitter menucolor"></i>'." Kurz-Mitteilungen (&uuml;ber Twitter)";
        $txt_twt_flattr='Sie haben bisher keine Flattr-ID hinterlegt. Bitte geben sie diese unter <a href="/wp-admin/profile.php">Profil</a> an, um mit Ihren Kommentaren Geld zu verdienen.';
	$txt_twt_paypal='Sie haben bisher keine PayPal-Email-Adresse hinterlegt und k&ouml;nnen somit keine Verdienstaussch&uuml;ttung f&uuml;r Ihre Artikel erhalten. Bitte geben sie die Kontaktdaten unter <a href="/wp-admin/profile.php">Profil</a> an.';
	$txt_twt_overview="&Uuml;bersicht";
	$txt_twt_statistic="Statistik";
        $txt_twt_userid="Benutzer-ID";
        $txt_twt_hwnum="erstellte Hardware-Artikel";
        $txt_twt_commnum="erstellte Kommentaren";
	$txt_twt_payment="Bezahlung";
	$txt_twt_actnum="Anzahl bezahlter Aktivit&auml;ten";
	$txt_twt_pending="Ausstehende Einnahmen";
	$txt_twt_payd="Bereits ausbezahlte Einnahmen";
        $txt_twt_maintext1='Erstellen Sie Artikel f&uuml;r die Linux-Hardware-Guide Datenbank gegen Bezahlung:
 			<table>
			   <tr class="first"><td class="first b b-posts" width="100px" align=right>
                             <strong>maximal 2.00&euro;</strong></td><td class="t posts">pro akzeptiertem Artikel*</a></td></tr>
                           <tr><td class="first b b_pages" align=right>
                             <strong>mind. 0.50&euro;</strong></td><td class="t pages">pro akzeptiertem Artikel*</td></tr>
                           <tr><td class="first b b-cats" valign=top align=right>
                             <strong>+0.50&euro;</strong></td><td class="t cats">falls die beschriebende Hardware zum Freigabetermin des Artikels bei Amazon.de k&auml;uflich zu erwerben ist</td></tr>
                           <tr><td class="first b b-cats" align=right>
                             <strong>bis +1.00&euro;</strong></td><td class="t cats">abh&auml;ngig von der Qualit&auml;t des Artikels (Anzahl Worte, Bilder, ...)</td></tr>
			</table>';

        $txt_twt_maintext2='<span>
                       F&uuml;r die Verdienstaussch&uuml;ttung gilt:
                            <br>- Auszahlung erfolgt ab einem Mindestverdienst von 10&euro; via PayPal.
                       <br>W&auml;hrend der momentan stattfindenden Testphase gilt:
                            <br>- Maximal 30 Artikel pro Monat werden verg&uuml;tet.
                            <br>- Maximale Gesamt-Verdienstaussch&uuml;ttung f&uuml;r alle beteiligten Autoren: 150&euro; pro Monat.
                        </span>


                       <p style="padding-top: 5px;">
                       Bei Fragen erreichen Sie uns unter <a href="mailto:linux.hardware.guide@gmail.com">linux.hardware.guide@gmail.com</a>.
                       <p style="border-top: 1px solid #CCC; padding-top: 10px;">
                        *Das Linux-Hardware-Guide Team entscheidet, ob ein Artikel die notwendige Qualit&auml;t besitzt, um akzeptiert werden zu k&ouml;nnen.
';

	//related posts thumbnails
	$txt_rpt_creation="Erstellt am";
	$txt_rpt_update="letzte Aktualisierung am";
	$txt_rpt_title="<h2>&Auml;hnliche Linux-Hardware</h2>";//<!--:fr--><h2>Mat&eacute;riel Linux Similaire</h2><!--:--><!--:zh--><h2>类似Linux的硬件</h2><!--:--><!--:ja--><h2>同じようなLinuxのハードウェア</h2><!--:--><!--:es--><h2>Linux Hardware Similares</h2><!--:--><!--:it--><h2>Simile Linux Hardware</h2><!--:-->
	$txt_rpt_sub_by="erstellt von";

	//wp-admin/admin-footer.php
	$txt_admin_footer='Bei Fragen wenden Sie sich bitte an <a mailto="webmaster@linux-hardware-guide.de">webmaster@linux-hardware-guide.de</a>.';

	//category post list
	$txt_cpl_noimage = "Kein Bild verf&uuml;gbar";
	$txt_cpl_comments= "Kommentare"; //="Kommentare";
	$txt_cpl_comment = "Kommentar";

        //wp-postrating
	$txt_vote = "Bewertung";
	$txt_votes = "Bewertungen";
	$txt_average = "Durchschnitt";
	$txt_out_of = "von maximal";

        //archive.php
        $txt_arch_related_cat  = "Verwandte Kategorien";
  	$txt_arch_category     = "Kategorie:";
  	$txt_arch_search_terms = "Suchbegriffe";
  	$txt_arch_and          = "und";
  	$txt_arch_combination  = "Kombination aus:";
  	$txt_arch_display_opt  = "Anzeige-Optionen";
        $txt_arch_sort_price   = "Sortieren nach Preis";
        $txt_arch_sort_rating  = "Sortieren nach Bewertung";
	$txt_arch_show_avail   = "nur lieferbare anzeigen";
  	$txt_arch_stars        = "Sterne";
	$txt_arch_home	       = "Hauptseite";
	$txt_filter_by_rating  = "Nach Bewertung filtern";
	$txt_filter_by_tags    = "Suchbegriffe filtern";
	$txt_sorting_options   = "Sortier-Optionen";


	//bwp-comments
	$txt_bwp_commenton     = "Kommentar auf";

	//subscribe
	$txt_subscr_component_added = "Komponente dem Profil hinzugef&uuml;gt";
	$txt_subscr_was_added       = "Die folgende Komponente ist dem Hardware-Profil hinzugef&uuml;gt worden";
	$txt_subscr_return 	    = "Zur&uuml;ckkehren zur ";
	$txt_subscr_to_hw	    = "Hardware-Beschreibung";
	$txt_subscr_or_manage	    = "oder unten das Hardware-Profil verwalten.";
        $txt_subscr_answer 	    = "Antworten per Mail erhalten";
	$txt_subscr_answer_logged_in = "Meinem Hardware-Profil hinzuf&uuml;gen und Antworten per E-Mail erhalten.";
	$txt_subscr_regist_date     = "Registrierungs-Datum";
	$txt_subscr_modus	    = "Status";
	$txt_subscr_article	    = "Hardware";
	$txt_subscr_select_all	    = "Alles ausw&auml;hlen";
	$txt_subscr_select_inv	    = "Auswahl invertieren";
	$txt_subscr_action	    = "Aktion";
	$txt_subscr_delete	    = "L&ouml;schen";
	$txt_subscr_suspend	    = "Deaktivieren";
	$txt_subscr_reply_only	    = "Antworten auf eigene Kommentare";
	$txt_subscr_activate	    = "Informiert werden";
        $txt_subscr_update          = "&Auml;ndern";
        $txt_subscr_active          = "Informiert werden";
        $txt_subscr_inactive        = "Inaktiv";
        $txt_subscr_manage_hw       = "Hardware-Profil bearbeiten";
	$txt_subscr_pub_hw_prof     = "&Ouml;ffentliches Hardware-Profil";
	$txt_subscr_name 	    = "Name";
	$txt_subscr_email	    = "Email";
	$txt_subscr_more	    = "mehr";
	$txt_subscr_regdate	    = "Registriert seit";
	$txt_subscr_language	    = "Sprache";
	$txt_subscr_public_hw_profile = "Hardware Profil";
	$txt_subscr_lat_sub	    = "Letzte Hardware-Eintr&auml;ge";
	$txt_subscr_numart	    = "Anzahl eingereichter Hardware-Artikel";
	$txt_subscr_numcom	    = "Anzahl an Kommentaren";
	$txt_subscr_rank	    = "Rang";
        $txt_subscr_nextpromo       = "N&auml;chster Rang" ;  //Next promotion
	$txt_subscr_nohw	    = 'Der Nutzer hat bisher keine Hardware-Komponenten seinem Profil hinzugef&uuml;gt.';
	$txt_subscr_scanoverview    = 'Hardware-Scan &Uuml;bersicht';
	$txt_subscr_kernelversion   = 'Kernel-Version';
	$txt_subscr_hwcomp          = "Hardware-Komponenten";
	$txt_subscr_identified      = "Identifiziert";
	$txt_subscr_unknown         = "Unbekannt";
	$txt_subscr_knownhw	    = "Bekannte Hardware";
	$txt_subscr_addhw	    = "Hardware dem Profil hinzuf&uuml;gen";
	$txt_subscr_ratecomp        = "Bitte bewerte die<br>Linux-Kompatibilit&auml;t";
	$txt_subscr_nohwfound       = "Keine Hardware gefunden";
        $txt_subscr_hwfeedback      = "Bitte teile mit, falls Hardware-Komponenten nicht oder fehlerhaft identifiziert wurden.<br>
				       Dies hilft uns die automatische Erkennung zu verbessern:";
        $txt_subscr_multiple        = "Mehrere M&ouml;glichkeiten (Mehrdeutige Hardware-Identifizierung)";
	$txt_subscr_identified_usb  = "Identifizierte USB-Ger&auml;te";
	$txt_subscr_option          = "Option";
	$txt_subscr_thisscan        = "Dieser Hardware-Scan wurde ausgef&uuml;hrt um";
	$txt_subscr_notice          = "Bitte beachte, dass dieser Web-Service weiterhin in Entwicklung ist. Alle Scan-Resultate wurden an die Linux-Hardware-Guide Datenbank &uuml;bertragen.
        Allerdings ist die automatische Hardware-Erkennung und die zugeh&ouml;rige &Uuml;bersicht auf dieser Seite nicht immer fehlerfrei.";
	$txt_subscr_limitation      = "
					<p>Dieses Tool ist momentan limitiert auf die Erkennung von:
					<ul><li>USB Komponenten
					<li>PCI Komponenten
					<li>Mainboards (experimentell)
					<li>Laptops (experimentell)
					<li>CPUs
					<li>Speichermedien (HDD, CD, DVD, SSD)
					</ul>";
	$txt_subscr_newhw	     = "Neue Hardware";
        $txt_subscr_foundhwid        = "Hardware-Kennung";
	$txt_subscr_rate	     = "Hardware bewerten";
	$txt_subscr_pleaserate	     = "Bitte bewerten";
	$txt_subscr_type             = "Typ";
	$txt_subscr_help             = "Hilf uns die Hardware-Komponente der Datenbank hinzuzuf&uuml;gen. Bitte identifiziere die Hardware (falls n&ouml;tig) und beschreibe ihre Linux-Kompatibilit&auml;t:";
        $txt_subscr_ifpossible       = "Bitte hinterlasse die URL einer Web-Seite, auf welcher die Hardware beschrieben wird (z.B. Hersteller-Spezifikation oder Amazon-Seite).<br>URL:";
	$txt_subscr_new		     = "Neues";
	$txt_subscr_thankyou	     = '<b>Danke, dass die die Linux-Hardware-Guide Scan-Software eingesetzt haben</b> (mehr Informationen zur Software unter <a href="https://github.com/paralib5/lhg_scanner">GitHub</a>).<br>
					Auf diese Weise k&ouml;nnen wir die Linux Hardware Datenbank mit f&uuml;r die Linux-Community wertvollen Informationen f&uuml;llen.<br>';
	$txt_subscr_hwscantitle	     = "&Uuml;bersicht Hardware-Scan:";

	$txt_hwprof_of              = "�ffentliche Hardware-Profil von";
	$txt_yes                     = "ja";
	$txt_no                      = "nein";



	//mobile theme
	$txt_mobile_search	    = "Nach Hardware suchen";
	$txt_mobile_show_all        = "Alle Suchergebnisse aufgelistet";
	$txt_mobile_fullsite        = "Zur Standard-Ansicht";

	//custom recent post
	$txt_crp_title		    = "K&uuml;rzlich hinzugef&uuml;gte Linux-Hardware"; //[:fr]Matériel compatible Linux récemment ajouté[:es]Linux Hardware Recientemente Adjuntando[:zh]最近添加的硬件的Linux[:ja]最近追加されたLinuxのハードウェア[:it]Hardware Linux aggiunti di recente

	//priceDB
	$txt_lhgdb_overview         = "&Uuml;bersicht der Anbieter";
	$txt_lhgdb_exclporto	    = "Exkl. Porto";
	$txt_lhgdb_inclporto	    = "Inkl. Porto";
	$txt_lhgdb_welcome          = "Willkommen beim Linux-Hardware-Guide";
	$txt_lhgdb_karmapoints	    = "Karma-Punkte";
        $txt_lhgdb_numhwscans       = "beigetragene Hardware-Scans";
        $txt_lhgdb_numhwscan        = "beigetragener Hardware-Scan";
	$txt_lhgdb_karmadescr       = 'Deine Karma-Punkte werden zur finanziellen Unterst&uuml;tzung von Linux-Projekten eingesetzt. Deine Spenden gehen an ';
	$txt_lhgdb_karmadescr_end   = 'Unter Deinen <a href="./profile.php">Profil-Einstellungen</a> kannst Du ausw&auml;hlen, an wen Du spenden m&ouml;chtest.';
	$txt_lhgdb_karma_howto	    = 'Wie sammelt man schnell Karma?';
	$txt_lhgdb_karma_1	    = '1) <a href="/add-hardware">Hardware Scan hochladen</a>, d.h. folgenden Befehl im Terminal ausf&uuml;hren';
	$txt_lhgdb_karma_2	    = '2) Linux Hardware bewerten und kommentieren';
	$txt_lhgdb_karma_3	    = 'Du ben&ouml;tigst mindestens';
	$txt_lhgdb_karma_4	    = 'Karma-Punkte, um neue Artikel erstellen zu k&ouml;nnen.';


	$txt_buy_from   	    = "Erh&auml;ltlich bei";
	$txt_preorder   	    = "Vormerken bei";
	$txt_not_avail_at 	    = "Momentan nicht lieferbar von";
	$txt_curr_not_avail_at 	    = "Nicht lieferbar von";
	$txt_search_at  	    = "Suchen bei";

	//misc
	$lhg_txt_new		    = "Neu";

	//Live Search
	$moreResultsText	    = "Weitere Ergebnisse anzeigen";


}


if ($region == "nl") {


	$txt_hwprofile          = "Mijn Hardware Profiel";

        //wp-one-post-widget
        $txt_summary    	= "Survey";
	$txt_supplier 		= "Provider";
        $txt_cheapest_supplier 	= "Bestseller";
        $txt_rating     	= "Assessment";
        $txt_Rating     	= $txt_rating; #"Assessment";
        $txt_ratings    	= "Beoordelingen";
        $txt_select     	= "Select country &amp; currency";
        $txt_sim_tags   	= "Gerelateerde termen";
        $txt_combine_tags 	= "Combineer termen";
        $txt_wpop_register   	= "In Hardware Profiel:";
        $txt_register_long	= "Blijf op de hoogte discussies en Linux updates van deze component.";
        $txt_send               = "Toevoegen";
        $txt_manage_subscr      = "Bewerk registraties";
        $txt_not_avail          = "Niet op voorraad met";
	$txt_rate_yourself      = "Evalueer hardware";
	$txt_opw_num_ratings    = "Aantal beoordelingen";
	$txt_opw_average_rating = "Gemiddelde waardering";
	$txt_opw_rating_overview= "Overzicht Rating";
	$txt_opw_hardware       = "Hardware";
	$txt_opw_registered	= "Geregistreerd Linux User";

	$txt_user_rating_for_setup = "De gebruiker op voorwaarde dat de volgende waardering voor dit hardware-installatie:";


        //comments.php
        $txt_no_respo   	= "Geen reacties";
	$txt_one_resp  		= "Een antwoord";
	$txt_responses          = "Antwoorden";
	$txt_to  		= "op";
        $txt_comments   	= "Reacties";
        $txt_comments_intro     = "Laat in de comments sectie beoordeling of commentaar en correcties op het artikel.
         Bijzonder nuttig zijn Ausz navigatie gebruik van ge <tt>lspci -nnk, lsusb, lshw, dmesg</tt>, enz,
         indien deze niet worden opgenomen in het artikel. Blijft het serveren. Het commentaar gebied van ervaringen
         tussen de eigenaars van de hardware-component en de Zoeken Configuratie Help Hotel<br />&nbsp; <br />";
	$txt_comments_new_discussion  = "Start nieuwe discussie";

	//attachment
	$txt_att_filetypes	= "Toegestaan bestandsformaten";
	$txt_att_maxsize  	= "Maximale bestandsgrootte";
	$txt_att_attachment  	= "Gehechtheid";

	// Cubepoints
	$txt_cp_karma		= "Karma";
	$txt_cp_points		= "Punten";
	$txt_cp_donates_to	= "Doneert aan";
	$txt_cp_language	= "Taal";
	$txt_cp_longtext	= 'De Linux-Hardware-Guide doneert maandelijks banner reclame-inkomsten terug naar de Linux-gemeenschap.
   Geregistreerde gebruikers stemmen met hun Karma punten aan wie donaties gaan (<a href="./donations">zie meer details</a>).
   Donatie som van de lopende maand ';
	$txt_cp_title		= "Meest actieve leden en donaties";
	$txt_cp_quarterly       = "Punten van kwartaal";
	$txt_cp_totalkarma      = "Karma";
	$txt_cp_details         = "Gegevens";

        //searchform.php
        $txt_search             = "Zoeken".'&nbsp;<i class="icon-arrow-right icon-button"></i>';

        //amazon-product-in-a-post.php
	$txt_compat  	= "Linux compatibiliteit";
	$txt_Compat  	= $txt_compat;
        $txt_with       = "big";
        $txt_rating     = "beoordeling";
        $txt_Rating     = "Beoordeling";
        $txt_ratings    = "beoordelingen";
        $txt_price      = "Prijs";
        $txt_out_of_stock = "niet beschikbaar";
        $txt_button     = "/wp-uploads/2012/11/amazon-130.png";
        $txt_button_width = "";
        $siteurl 	= "linux-hardware.no-ip.org";
        $txt_pricetrend = "Prijs geschiedenis";
        $txt_A_preis    = "Amazon.de Prijs";
        $txt_currency   = "Euro";
        $txt_shipping	= "zonder verzendkosten";
        $txt_shipping_costs	    = "Verzendkosten";
	$txt_on_stock   = "op voorraad";
        $txt_category   = "Categorie";
        $txt_date       = "Datum";
        $txt_updated    = "Stand";
        $txt_tooltip2   = "Die von Amazon zur Verf&uuml;gung gestellten Preise sind exklusive m&ouml;glicherweise zus&auml;tzlich anfallender Versandkosten (abh&auml;ngig vom jeweiligen Anbieter des Amazon-Marketplace).";
	$txt_amz_asin   = "Amazon ASIN (ISBN-10) Produkt-Nummer";
	$txt_amz_getfrom= 'Die ASIN-Nummer findet man auf der zugeh&ouml;rigen Produkt-Seite bei <a href="http://amazon.de/">Amazon.de</a>';
	//$txt_amz_title  = "Amazon Produkt-Kennung";
	$txt_amz_tooltip_loggedin     = 'Bewertet wird an dieser Stelle einzig die Linux-Kompatibilit&auml;t und nicht die Qualit&auml;t des Produktes. <hr style="height: 4pt; visibility: hidden;">Sollten Sie dieses Produkt besitzen, dann helfen Sie bitte auch anderen Linux-Benutzern, indem Sie dessen Linux-Kompatibilit&auml;t <a href="#comments">im Kommentar-Bereich dieser Seite</a> bewerten.';
	$txt_amz_tooltip_not_loggedin = 'Geëvalueerd op dit moment alleen Linux verenigbaarheid pijl Xenical t en niet de kwaliteit van het product. Als u eigenaar van dit product dan kunt u helpen ook andere Linux-gebruikers, door het evalueren van zijn Linux-compatibiliteit in de commentaar sectie van deze pagina.';


        //header.php
	$txt_reg   = "Registreer";
	$txt_login = "Aanmelden";

        //footer.php
	$twitAcc="LinuxHWGuide";
	$FL_Thing="1446140/Linux-Hardware-Guide";
	$FURL = "http://www.linux-hardware-guide.de";
	$txt_follow_twitter = "Volgen op Twitter";
	$txt_mail_us = "Contact per e-mail";
	$txt_flattr_us = "Flattr";
        $txt_designed = "Ontwerp van";
        $txt_for_mobile = "Geoptimaliseerd voor Tablet &amp; Smartphone";
	$txt_footer_general="Allgemeen";
	$txt_footer_contact="Contact";
	$txt_footer_faq="Vragen en Antwoorden";
	$txt_footer_contributors="Ondersteuning";
	$txt_footer_support="Samenwerken";
	$txt_footer_problem="Meld een probleem";
	$txt_footer_donate="Doneren";
	$txt_footer_suppliers="Fabrikant &amp; verkoper";
	$txt_footer_hwsuppliers="Hardware verkopen";
	$txt_footer_manufacturers="Hardwarefabrikanten";
	$txt_footer_advertisers="Adverteerders";


        //comments-template.php
	$txt_privacy_stat	= "E-mailadres wordt niet gepubliceerd.";
        $txt_compat             = "Linux compatibiliteit";
	$txt_compat_rat         = "Rate Linux compatibiliteit";
        $txt_use_tags           = 'Gebruik de volgende  <abbr title="HyperText Markup Language">HTML</abbr>-tags en attributen';
        $txt_logged_in_as       = "Ingelogd als";
        $txt_logout             = "Afmelden";
        $txt_send_comment       = "Reactie indienen";
        $txt_cancel_reply       = 'Weggooien Reageren';
        $txt_reply_to           = 'Antwoorden op';
        $txt_comments           = "Reageren en beoordelen";
        $txt_comment            = "";
        $txt_to_comment         = "Plaats een reactie";
        $txt_website            = "Webpagina";
        $txt_reply              = "Reageer";
        $txt_says               = "schreef";
	$txt_product_rating     = "Productie beoordeling";

	//avatar tooltip
	$txt_avt_reguser	= "Registered User";
	$txt_avt_karmapoints	= "Karma Punten";
	$txt_avt_rank		= "Rank";
	$txt_avt_click		= "Klik Avatar voor navigatie gebruik profielpagina";

	//scan overview table
	$txt_scan_distribution  = "Distributie";
	$txt_scan_kernel        = "Kernel";
	$txt_scan_scandate      = "Scan datum";
	$txt_scan_result	= "resultaat";
	$txt_scan_results	= "resultaten";
	$txt_scan_title		= "Geïdentificeerde hardwareconfiguratie";
	$txt_scan_text          = 'Deze hardware component werd gebruikt door de Linux-gebruikers onder de volgende systeemconfiguraties. Deze resultaten werden verzameld door onze <a href="./add-hardware">LHG Scan Tool</a>:';



        //user-submit-form.php
	$txt_submit_name = "Je naam of e-mailadres";
        $txt_product  	 = "Product / hardware-id:";
        $txt_link        = "Link naar het product (website bijvoorbeeld fabrikant of Amazon pagina):";
        $txt_description = "Beschrijving:";
        $txt_descr_details =
'bv
nodige configuratie stappen,
Linux kernel versie,
gebruikte distributie,
output van dmesg,
output van lspci -nnk, lsusb en lshw...';
        $txt_picture     = "Afbeelding(en) toevoegen:";
        $txt_more_pic    = "meer foto";
        $txt_submit      = 'voeren';
	$txt_anonymous   = "Anoniem";

        //search.php
        $txt_search_results  = "Zoekresultaten";
  	$txt_no_result       = "Geen zoekresultaten. Probeer andere zoekwoorden?";

	//s8-custom-login
	$txt_remember_me = 'Onthoud mij Inloggen';
        $txt_username  	= "User name";
	$txt_register  	= "Registreren";
	$txt_mail  	= "E-mail";
        $txt_new_pwd    = "Een watchwoord zal naar u worden verzonden.";
        $txt_user_or_mail = "User name of E-mail";

        //registration-form-widget
	$txt_reg_user = "User name";
	$txt_reg_mail = "E-mail";

        //pluggable.php
        $txt_mail_username = "User name";
	$txt_mail_password = "Watchwoord";
	$txt_mail_URL = "http://www.linux-hardware-guide.de/login";
	$txt_mail_welcome = "Welkom op de Linux-Hardware-Guide.com:\r\nU hebt de volgende toegang toegewezen:\r\n";
	$txt_mail_end = "Gelieve in te loggen op het volgende adres: http://www.linux-hardware-guide.com/login en daar heb je je persoonlijke instellingen te wijzigen, zoals Wachtwoord en avatar.\r\nGroetene,\ruw Linux-Hardware-Guide team";
	$txt_mail_unp = "Uw User name en watchwoord";

	//project tasks plugin
	$txt_pt_open = "Inkomsten";
	$txt_pt_overview = "Inkomen index";
	$txt_pt_article = "Artikel";
	$txt_pt_user    = "Editor";
	$txt_pt_payment = "Inkomsten";
	$txt_pt_payedat = "Betaald op";
        $txt_pt_add_pay = "Betaling toe te voegen";
	$txt_pt_payments= "Betalingen";
        $txt_pt_amount  = "Bedrag";
        $txt_pt_pending = "open";
	$txt_pt_paid = "betaalde";

        //Twitter widget
	$txt_twt_title		= '<i class="icon-twitter menucolor"></i>'." Nieuws in het kort (via Twitter)";
        $txt_twt_flattr='Ze hebben tot nu toe niet voorzien welke Flattr ID. Dient u dit aan onder <a href="/wp-admin/profile.php">Profiel</a>.';
	#$txt_twt_paypal='Ze hebben tot nu toe niet voorzien welke PayPal-Email gedeponeerd en k ouml; dus niet kan Verdienstaussch navigatie gebruik ttung f navigatie gebruik r uw items
        #hinterlegt und k&ouml;nnen somit keine Verdienstaussch&uuml;ttung f&uuml;r Ihre Artikel erhalten. Bitte geben sie die Kontaktdaten unter <a href="/wp-admin/profile.php">Profil</a> an.';
	$txt_twt_overview="Survey";
	$txt_twt_statistic="Statistieken";
        $txt_twt_userid="Gebruikers-ID";
        #$txt_twt_hwnum="Anzahl an Hardware-Artikel";
        #$txt_twt_commnum="Anzahl an Kommentaren";
	#$txt_twt_payment="Bezahlung";
	#$txt_twt_actnum="Anzahl bezahlter Aktivit&auml;ten";
	#$txt_twt_pending="Ausstehende Einnahmen";
	#$txt_twt_payd="Bereits ausbezahlte Einnahmen";
        #$txt_twt_maintext1='Erstellen Sie Artikel f&uuml;r die Linux-Hardware-Guide Datenbank gegen Bezahlung:
 	#		<table>
	#		   <tr class="first"><td class="first b b-posts" width="100px" align=right>
        #                     <strong>maximal 2.00&euro;</strong></td><td class="t posts">pro akzeptiertem Artikel*</a></td></tr>
        #                   <tr><td class="first b b_pages" align=right>
        #                     <strong>mind. 0.50&euro;</strong></td><td class="t pages">pro akzeptiertem Artikel*</td></tr>
        #                   <tr><td class="first b b-cats" valign=top align=right>
        #                     <strong>+0.50&euro;</strong></td><td class="t cats">falls die beschriebende Hardware zum Freigabetermin des Artikels bei Amazon.de k&auml;uflich zu erwerben ist</td></tr>
        #                   <tr><td class="first b b-cats" align=right>
        #                     <strong>bis +1.00&euro;</strong></td><td class="t cats">abh&auml;ngig von der Qualit&auml;t des Artikels (Anzahl Worte, Bilder, ...)</td></tr>
	#		</table>';
        #
        #$txt_twt_maintext2='<span>
        #               F&uuml;r die Verdienstaussch&uuml;ttung gilt:
        #                    <br>- Auszahlung erfolgt ab einem Mindestverdienst von 10&euro; via PayPal.
        #               <br>W&auml;hrend der momentan stattfindenden Testphase gilt:
        #                    <br>- Maximal 30 Artikel pro Monat werden verg&uuml;tet.
        #                    <br>- Maximale Gesamt-Verdienstaussch&uuml;ttung f&uuml;r alle beteiligten Autoren: 150&euro; pro Monat.
        #                </span>
        #
        #
        #               <p style="padding-top: 5px;">
        #               Bei Fragen erreichen Sie uns unter <a href="mailto:linux.hardware.guide@gmail.com">linux.hardware.guide@gmail.com</a>.
        #               <p style="border-top: 1px solid #CCC; padding-top: 10px;">
        #                *Das Linux-Hardware-Guide Team entscheidet, ob ein Artikel die notwendige Qualit&auml;t besitzt, um akzeptiert werden zu k&ouml;nnen.
	#';

	//related posts thumbnails
	$txt_rpt_creation="Gemaakt op";
	$txt_rpt_update="laatst bijgewerkt op";
	$txt_rpt_title="<h2>Vergelijkbare Linux hardware</h2>";//<!--:fr--><h2>Mat&eacute;riel Linux Similaire</h2><!--:--><!--:zh--><h2>类似Linux的硬件</h2><!--:--><!--:ja--><h2>同じようなLinuxのハードウェア</h2><!--:--><!--:es--><h2>Linux Hardware Similares</h2><!--:--><!--:it--><h2>Simile Linux Hardware</h2><!--:-->
	$txt_rpt_sub_by="aangemaakt door";

	//wp-admin/admin-footer.php
	$txt_admin_footer='Voor vragen, neem dan contact op met <a mailto="webmaster@linux-hardware-guide.de">webmaster@linux-hardware-guide.de</a>.';

	//category post list
	$txt_cpl_noimage = "Geen foto beschikbaar";
	$txt_cpl_comments= "Reacties"; //="Kommentare";
	$txt_cpl_comment = "Reactie";

        //wp-postrating
	$txt_vote = "Beoordeling";
	$txt_votes = "Beoordelingen";
	$txt_average = "gemiddelde";
	$txt_out_of = "van de maximale";

        //archive.php
        $txt_arch_related_cat  = "Verwante categorieën";
  	$txt_arch_category     = "Categorie:";
  	$txt_arch_search_terms = "Zoekopdrachten";
  	$txt_arch_and          = "en";
  	$txt_arch_combination  = "Combinatie van:";
  	$txt_arch_display_opt  = "Weergave opties";
        $txt_arch_sort_price   = "Sorteren op Prijs";
        $txt_arch_sort_rating  = "Sorteer op beoordeling";
	$txt_arch_show_avail   = "Alleen beschikbaar";
  	$txt_arch_stars        = "Sterren";
	$txt_arch_home	       = "Startpagina";
	$txt_filter_by_rating  = "Filteren op beoordeling";
	$txt_filter_by_tags    = "Filter zoekopdrachten";
	$txt_sorting_options   = "Sorteeropties";


	//bwp-comments
	$txt_bwp_commenton     = "Reageer op";

	//subscribe
	$txt_subscr_component_added = "Component aan het profiel toegevoegd";
	$txt_subscr_was_added       = "De volgende component wordt toegevoegd aan de hardware profiel";
	$txt_subscr_return 	    = "Terug te keren naar ";
	$txt_subscr_to_hw	    = "Beschrijving van de hardware";
	$txt_subscr_or_manage	    = "of onder het beheer van de hardware-profiel.";
        $txt_subscr_answer 	    = "Abonneer via e-mail";
	$txt_subscr_answer_logged_in = "Voeg toe aan mijn hardware profiel, en antwoorden via e-mail te ontvangen";
	$txt_subscr_regist_date     = "Datum van registratie";
	$txt_subscr_modus	    = "Status";
	$txt_subscr_article	    = "Hardware";
	$txt_subscr_select_all	    = "Selecteer alle";
	$txt_subscr_select_inv	    = "Selectie omkeren";
	$txt_subscr_action	    = "Actie";
	$txt_subscr_delete	    = "Delete";
	$txt_subscr_suspend	    = "Deactiveren";
	$txt_subscr_reply_only	    = "Antwoorden op uw commentaar";
	$txt_subscr_activate	    = "Worden geïnformeerd";
        $txt_subscr_update          = "Wijzigen";
        $txt_subscr_active          = "Worden geïnformeerd";
        $txt_subscr_inactive        = "Inactief";
        $txt_subscr_manage_hw       = "Bewerk Hardware Profiel";
	$txt_subscr_pub_hw_prof     = "Openbare Hardware profiel";
	$txt_subscr_name 	    = "Naam";
	$txt_subscr_email	    = "Email";
	$txt_subscr_more	    = "meer";
	$txt_hwprof_of              = "Publieke Hardware Profiel van";


	//mobile theme
	$txt_mobile_search	    = "Zoeken naar Hardware";
	$txt_mobile_show_all        = "Vermeld alle zoekresultaten";
	$txt_mobile_fullsite        = "Ga naar de website";

	//custom recent post
	$txt_crp_title		    = "Recent toegevoegde Linux-Hardware"; //[:fr]Matériel compatible Linux récemment ajouté[:es]Linux Hardware Recientemente Adjuntando[:zh]最近添加的硬件的Linux[:ja]最近追加されたLinuxのハードウェア[:it]Hardware Linux aggiunti di recente

	//priceDB
	$txt_lhgdb_overview         = "Overzicht Provider";
	$txt_lhgdb_exclporto	    = "Exc. Porto";
	$txt_lhgdb_inclporto	    = "Incl. Porto";
	$txt_buy_from   	    = "Verkrijgbaar bij";
	$txt_preorder   	    = "Toevoegen aan wachtrij bij";
	$txt_not_avail_at 	    = "Momenteel niet beschikbaar vanaf";
	$txt_curr_not_avail_at 	    = "Niet beschikbaar vanaf";
	$txt_search_at  	    = "Zoeken in";
	$txt_shipping_costs	    = "Verzendkosten";
	$txt_lhgdb_welcome          = "Welkom bij de Linux-hardware-Guide";
	$txt_lhgdb_karmapoints	    = "Karma punten";
        $txt_lhgdb_numhwscans       = "geüploade hardware scans";
        $txt_lhgdb_numhwscan        = "geüploade hardware scan";
	$txt_lhgdb_karmadescr       = 'Uw Karma punten worden gebruikt voor het financieel ondersteunen van bepaalde Linux-projecten. Uw giften zijn op dit moment naar de';
	$txt_lhgdb_karmadescr_end   = 'Selecteer uw donatie doel op je <a href="./profile.php">profielpagina</a>.';
	$txt_lhgdb_karma_howto	    = 'Hoe om snel te verdienen Karma?';
	$txt_lhgdb_karma_1	    = '1) Upload uw <a href="/add-hardware">hardware Scan</a>: start the following command in a terminal';
	$txt_lhgdb_karma_2	    = '2) te beoordelen en commentaar op uw Linux-hardware';
	$txt_lhgdb_karma_3	    = 'Je moet ten minste';
	$txt_lhgdb_karma_4	    = 'Karma punten om nieuwe artikelen te creëren';

	//Live Search
	$moreResultsText	    = "Meer resultaten";


}


if ($region == "fr") {

	$txt_hwprofile          = " Mon Profil Mat&eacute;riel";

        //wp-one-post-widget
        $txt_summary    	= "R&eacute;sum&eacute;"; #checked by Valentin C.
	$txt_supplier 		= "Vendeur"; #checked by Valentin C.
        $txt_cheapest_supplier   = "Vendeur le moins cher";
        $txt_rating     	= "&Eacute;valuation";
        $txt_Rating     	= $txt_rating; #"&Eacute;valuation";
        $txt_ratings    	= "&eacute;valuations";
        $txt_sim_tags   	= "Filtres similaires"; #checked by Valentin C.
        $txt_combine_tags 	= "Appliquer les filtres"; #checked by Valentin C.
	$txt_wpop_register      = "Ajouter &agrave; profil mat&eacute;riel:";
        $txt_register_long	= "et soyez inform&eacute; si de nouveaux drivers existent ou si des utilisateurs Linux qui ont le m&ecirc;me mat&eacute;riel ont besoin d'aide.";
        $txt_send               = "Ajouter";
        $txt_manage_subscr      = "G&eacurer; mes enregistrements";
        $txt_not_avail          = "Stock &eacute;puis&eacute;";
	$txt_rate_yourself      = "&Eacute;valuez le mat&eacute;riel"; #checked by Valentin C.
	$txt_opw_num_ratings    = "Nombre de &eacute;valuations";
	$txt_opw_average_rating = "&Eacute;valuation moyenne";
	$txt_opw_rating_overview= "Apercu des &eacute;valuations";
	$txt_opw_hardware       = "Mat&eacute;riel";
	$txt_opw_registered	= "Utilisateurs de Linux enregistrés ";

	$txt_user_rating_for_setup = "L'utilisateur a fourni la note suivante pour cette configuration matérielle:";


	//amazon-product-in-a-post.php
	$txt_compat  	= "Examen de compatibilit&eacute; avec Linux"; #checked by Valentin C.
	$txt_Compat  	= $txt_compat;
	$txt_with       = "avec";
	$txt_rating     = "&eacute;valuation";
	$txt_ratings    = "&eacute;valuations";
	$txt_price      = "Prix";
	$txt_out_of_stock = "Stock &eacute;puis&eacute;";
	$txt_button     = "/wp-uploads/amazon-button.png";
	$txt_button_width = 'width="185"';
	$siteurl 	= "linux-hardware-guide.com";
	$txt_pricetrend = "courbe de prix";
	$txt_shipping	= "sans frais de ports"; #checked by Valentin C.
	$txt_on_stock   = "En stock";
	$txt_category   = "Cat&eacute;gories"; #checked by Valentin C.
	$txt_date       = "Date";
	$txt_updated    = "Mis &agrave; jour";
	$txt_tooltip2   = "Les prix d'Amazon.fr ne contiennent pas de frais de ports, mais cela peut d&eacute;pendre du produit et du vendeur d'Amazon Marketplace.";
	$txt_buy_from   = "Achetez sur"; #checked by Valentin C.
	$txt_search_at  = "Cherchez sur";
	$txt_preorder   = "Reservez sur";
	$txt_not_avail_at = "Non disponible pour l'instant sur";
	$txt_never_avail = "Pas dans le catalogue de";
	$txt_amz_tooltip_not_loggedin = "Seule la compatibilité Linux est évalué sur cette page, pas la qualité générale du produit. Si vous utilisez ce produit avec Linux, s'il vous plaît noter et partager votre expérience dans la zone commentaire de cette page (en bas) pour soutenir d'autres utilisateurs de Linux.";


	//attachment
	$txt_att_filetypes	= "Types de fichiers autoris&eacute;s ";
	$txt_att_maxsize  	= "taille maximale de fichiers ";
	$txt_att_attachment  	= "Attachement";


	// Cubepoints
	$txt_cp_karma		= "Karma";
	$txt_cp_points		= "points";
	$txt_cp_donates_to	= "Fait un don &agrave;";
	$txt_cp_language	= "Langue";
	$txt_cp_longtext	= 'Le Linux Hardware-Guide don mensuel recettes publicitaires de la bannière vers de la communauté Linux.
   Les utilisateurs enregistrés votent avec leurs points de Karma à qui des dons vont (<a href="./donations">voir plus de détails</a>).
   Donation somme du mois en cours';
	$txt_cp_title		= "Classement de l'utilisateur et leurs dons";
	$txt_cp_quarterly       = "Points trimestriels";
	$txt_cp_totalkarma      = "Karma totale";
	$txt_cp_details         = "Détails";


        //header.php
	$txt_reg   = "S&#8217;inscrire"; #checked by Valentin C.
	$txt_login = "Connexion"; #checked by Valentin C.

        //footer.php
	$txt_follow_twitter = "Suivre-nous sur Twitter";
	$txt_mail_us = "Contactez nous per email";
	$txt_flattr_us = "Flattr";
        $txt_designed = "Design de";
        $txt_for_mobile = "Optimis&eacute; pour tablette et Smartphone";
	$txt_footer_general="Thèmes généraux";
	$txt_footer_contact="Contacter";
	$txt_footer_faq="FAQ";
	$txt_footer_contributors="Soutien";
	$txt_footer_support="Collaborer";
	$txt_footer_problem="Reporte un probl&eacute;me";
	$txt_footer_donate="Donner";
	$txt_footer_suppliers="Fournissuers et publicit&eacute;";
	$txt_footer_hwsuppliers="Fournissuers de mat&eacute;riel";
	$txt_footer_manufacturers="Fabricants de mat&eacute;riel";
	$txt_footer_advertisers="Annonceurs";

	//comments.php
	$txt_no_respo   	= "Pas des r&eacute;ponses";
	$txt_one_resp  		= "Une r&eacute;ponse";
	$txt_responses          = "Reponses";
	$txt_to  		= "&agrave;";
	$txt_comments   	= "Commentaires";
	$txt_comments_intro     = "Utilisez la section commentaires pour nous sugg&eacute;rer des corrections de l'article et des sorties de commandes lspci -vnn, lsusb, lshw, dmesg, etc...
<br />
De plus, vous pouvez utiliser les commentaires pour nous communiquer vos exp&eacute;riences avec le mat&eacute;riel ou pour rechercher de l'aide pour la configuration.
<br />&nbsp;<br />"; #checked by Valentin C.
	$txt_comments_new_discussion  = "Démarrez une nouvelle discussion";

        //comments-template.php
	$txt_privacy_stat	= "L'adresse email n'est pas rendue publique."; #checked by Valentin C.
        $txt_use_tags           = 'Tags <abbr title="HyperText Markup Language">HTML</abbr> et attributes disponible ';
        //$txt_logged_in_as       = "Angemeldet als";
        $txt_logout             = "D&eacute;connexion";
        $txt_send_comment       = "Envoyez votre commentaire";
        $txt_cancel_reply       = 'Annuler la reponse';
        $txt_reply_to           = 'R&eacute;ponse &agrave;';
        $txt_comments           = "Commentaires";
        $txt_comment            = "";
        $txt_to_comment         = "Commentaire";
        $txt_website            = "Site internet";
        $txt_reply              = "Repondre";
        $txt_says               = "dit";
        $txt_compat_rat         = "Notez la compatibilit&eacute; avec Linux";
	$txt_product_rating     = "&Eacute;valuation";

	//avatar tooltip
	$txt_avt_reguser	= "Utilisateur enregistr&eacute;";
	$txt_avt_karmapoints	= "Points de Karma";
	$txt_avt_rank		= "Classement";
	$txt_avt_click		= "Cliquez avatar pour le profil de l'utilisateur";

	//scan overview table
	$txt_scan_distribution  = "Distribution";
	$txt_scan_kernel        = "Kernel";
	$txt_scan_scandate      = "Date du balayage";
	$txt_scan_result	= "résultat";
	$txt_scan_results	= "résultats";
	$txt_scan_title		= "Configurations matérielles identifiées";
	$txt_scan_text          = 'Ce composant matériel a été utilisé par les utilisateurs de Linux dans les configurations système suivantes. Ces résultats ont été collectées par notre <a href="./add-hardware">LHG Scan Tool</a>:';



	//searchform.php
	$txt_search 		= 'Chercher';


        //user-submit-form.php
	$txt_submit_name = "Votre Nom or Email:";
        $txt_product  	 = "ID de produit ou nom de produit:";
        $txt_link        = "Page web avec les d&eacute;tails du produit (Exemple: Page du constructeur ou page Amazon):";
        $txt_description = "Description:";
        $txt_descr_details =
'Par exemple:
Etapes de configuration n&eacute;cesasires pour utiliser le mat&eacute;riel avec Linux,
Version du kernel Linux,
Distribution,
Sortie de dmesg,
Sortie de lspci -vnn, lsusb -v et lshw...
';
        $txt_picture     = "Envoyer d'image";
        $txt_more_pic    = "+ d'images";
        $txt_submit      = 'Ajoutez';
	$txt_anonymous   = "Anonymous";

        //search.php
        $txt_search_results  = "R&eacute;sultats";
  	$txt_no_result       = "Pas des r&eacute;sultats. Une autre recherche?";

	//s8-custom-login
	$txt_remember_me = 'Rester connect&eacute;';
	$txt_username  	= "Nom d'utilisateur";
	$txt_register  	= "Register";
	$txt_mail  	= "Email Adress";
	$txt_pwd_send   = "Un mot de passe sera envoyé par mail ";
	$txt_new_pwd    = "Obtenir";
	$txt_user_or_mail = "Nom d'utilisateur ou adresse email";

	//registration-form-widget
	$txt_reg_user = "Nom d'utilisateur";
	$txt_reg_mail = "Adresse email";

        //pluggable.php
        $txt_mail_username = "Identifiant";
	$txt_mail_password = "Mot de passe";
	$txt_mail_URL = "http://www.linux-hardware-guide.com/fr/login";
	$txt_mail_welcome = "Bienvenue à Linux-Hardware-Guide.com/fr \r\nVous avez été affecté les accès suivants :\r\n\n";
	$txt_mail_end = "\r\nS'il vous plaît vous connecter à l'adresse http://www.linux-hardware-guide.com/fr/login et changer vos paramètres personnels tels que mot de passe et avatar.\r\nCordialement,\rvotre équipede Linux-Hardware-Guide";
	$txt_mail_unp = "Votre identifiant et mot de passe";


	//related posts thumbnails
	$txt_rpt_creation="Cr&eacute;&eacute;";
	$txt_rpt_update="Mis &agrave; jour";
	$txt_rpt_title="<h2>Mat&eacute;riel Linux Similaire</h2>";//<!--:zh--><h2>类似Linux的硬件</h2><!--:--><!--:ja--><h2>同じようなLinuxのハードウェア</h2><!--:--><!--:es--><h2>Linux Hardware Similares</h2><!--:--><!--:it--><h2>Simile Linux Hardware</h2><!--:-->
	$txt_rpt_sub_by="pr&eacute;sent&eacute; par";

	//wp-admin/admin-footer.php
	$txt_admin_footer='Si vous avez des questions, contactez <a mailto="linux.hardware.guide@gmail.com">linux.hardware.guide@gmail.com</a>.';

	//category post list
	$txt_cpl_noimage = "Image pas disponible";
	$txt_cpl_comments= "commentaires"; //="Kommentare";
	$txt_cpl_comment = "commentaire";

	//wp-postrating
	$txt_vote = "&eacute;valuation";
	$txt_votes = "&eacute;valuations";
	$txt_average = "en myoenne";
	$txt_out_of = "de";

        //archive.php
        $txt_arch_related_cat  = "Cat&eacute;gorie similaire";
  	$txt_arch_category     = "Cat&eacute;gorie:";
  	$txt_arch_search_terms = "Mots de recherche";
  	$txt_arch_and          = "et";
  	$txt_arch_combination  = "combinaison de:";
  	$txt_arch_display_opt  = "Options de visualisation";
        $txt_arch_sort_price   = "Triez de prix";
        $txt_arch_sort_rating  = "Triez de &eacute;valuation";
	$txt_arch_show_avail   = "Disponible";
  	$txt_arch_stars        = "&eacute;toiles";
	$txt_arch_home	       = "Page d'accueil";
	$txt_filter_by_rating  = "Trier par &eacute;valuation";
	$txt_filter_by_tags    = "Filtre mots de recherche";
	$txt_sorting_options   = "Options de tri";



	//bwp-comments
	$txt_bwp_commenton     = "Commentaire sur";

        //Twitter widget
	$txt_twt_title		= '<i class="icon-twitter menucolor"></i> '."Flash d'information (via Twitter)";

	//mobile theme
	$txt_mobile_search	    = "Recherche matériel";
	$txt_mobile_show_all        = "Tous les résultats de la recherche sont affichés";
	$txt_mobile_fullsite        = "Voir la version complète";


	//custom recent post
	$txt_crp_title		    = "Matériel compatible Linux récemment ajouté";//[:es]Linux Hardware Recientemente Adjuntando[:zh]最近添加的硬件的Linux[:ja]最近追加されたLinuxのハードウェア[:it]Hardware Linux aggiunti di recente

	//subscribe
	$txt_subscr_component_added = "Hardware ajouté au profil";
	$txt_subscr_was_added       = "Le matériel suivant a été ajouté au profil";
	$txt_subscr_return 	    = "Retour à la ";
	$txt_subscr_to_hw	    = "Description du matériel";
	$txt_subscr_or_manage	    = "ou vers le bas pour modifier le profil matériel";
        $txt_subscr_answer 	    = "Abonnez-vous par e-mail";
	$txt_subscr_regist_date     = "Date d'enregistrement ";
	$txt_subscr_modus	    = "Statut";
	$txt_subscr_article	    = "Exposé sommaire du mat&eacute;riel";
	$txt_subscr_select_all	    = "S&eacute;lectionner tout";
	$txt_subscr_select_inv	    = "Inverser la s&eacute;levtion";
	$txt_subscr_action	    = "Action";
	$txt_subscr_delete	    = "Effacer entrée";
	$txt_subscr_suspend	    = "Désactiver Entrée";
	$txt_subscr_reply_only	    = "Réponses à mes commentaires";
	$txt_subscr_activate	    = "Restez informé";
	$txt_subscr_update          = "Mettre à jour";
	$txt_subscr_active          = "Restez informé";
	$txt_subscr_inactive        = "Désactivé";
	$txt_subscr_manage_hw       = "Gérez votre profil matériel";
	$txt_subscr_pub_hw_prof     = "Public profil mat&eacute;riel";
	$txt_subscr_name 	    = "Nom";
	$txt_subscr_email	    = "Email";
	$txt_subscr_more	    = "plus";
        $txt_hwprof_of              = "Profil Matériel Public de";


	//priceDB
	$txt_lhgdb_overview         = "Vue d'ensemble des fournisseurs";
	$txt_lhgdb_exclporto	    = "excl. les frais de livraison";
	$txt_lhgdb_inclporto	    = "incl. les frais de livraison";
	$txt_lhgdb_welcome          = "Bienvenue sur le Linux-Hardware-Guide";
	$txt_lhgdb_karmapoints	    = "points de Karma";
        $txt_lhgdb_numhwscans       = "analyses du matériel téléchargé";
        $txt_lhgdb_numhwscan        = "analyse du matériel téléchargé";
	$txt_lhgdb_karmadescr       = 'Vos points de Karma sont utilisés pour soutenir financièrement certains projets de Linux. Vos dons sont actuellement en cours au ';
	$txt_lhgdb_karmadescr_end   = 'Sélectionnez votre cible de don sur votre <a href="./profile.php">page de profil</a>.';
	$txt_lhgdb_karma_howto	    = 'Comment gagner Karma rapidement ?';
	$txt_lhgdb_karma_1	    = '1)<a href="/add-hardware">Téléchargez votre</a> analyse du matériel: lancez la commande suivante dans un terminal';
	$txt_lhgdb_karma_2	    = '2) Noter et commenter sur votre matériel Linu';
	$txt_lhgdb_karma_3	    = "Vous avez besoin d'au moins";
	$txt_lhgdb_karma_4	    = 'points de Karma pour créer de nouveaux articles.';

	//Live Search
	$moreResultsText	    = "Voir plus de résultats";


}

if ($region == "es") {

	$txt_hwprofile          = " Mi perfil de hardware";

        //wp-one-post-widget
	$txt_summary    	= "Resumen";
	$txt_supplier  		= "Suministrador";
	$txt_cheapest_supplier 	= "Proveedor m�s barato";
	$txt_rating     	= "valoraci&oacute;n";
	$txt_Rating     	= "Valoraci&oacute;n";
	$txt_ratings    	= "valoraciones";
	$txt_select     	= "Select country &amp; currency";
	$txt_sim_tags   	= "Etiquetas similares";
	$txt_combine_tags 	= "Combine";
	$txt_wpop_register   	= "Añada a su perfil de hardware:";
	$txt_register_long	= "Manténgase al día a cerca de las novedades de Linux y de las opiniones sobre este componenente.";
	$txt_send               = "Añadir";
	$txt_manage_subscr      = "Administrar Suscripciones";
	$txt_not_avail          = "No disponible en";
	$txt_rate_yourself      = "Tasa de Hardware";
	$txt_opw_num_ratings    = "N&uacute;mero de valoraciones";
	$txt_opw_average_rating = "Valoraci&oacute;n media";
	$txt_opw_rating_overview= "Visi&oacute;n general de valoraciones";
	$txt_opw_hardware       = "Hardware";
	$txt_opw_registered	= "Usarios de Linux registrados";

	$txt_user_rating_for_setup = "El usuario proporciona los siguientes comentarios para esta configuración de hardware:";


	//related posts thumbnails
	$txt_rpt_creation="Creado el";
	$txt_rpt_update="Actualizado a las";
	$txt_rpt_title="<h2>Hardware de Linux similares</h2>";//<!--:zh--><h2>类似Linux的硬件</h2><!--:--><!--:ja--><h2>同じようなLinuxのハードウェア</h2><!--:--><!--:es--><h2></h2><!--:--><!--:it--><h2>Simile Linux Hardware</h2><!--:-->
	$txt_rpt_sub_by="enviado por";


	//amazon-product-in-a-post.php
	$txt_compat  	= "Compatibilidad con Linux";
	$txt_Compat  	= "Revisi&oacute;n de Compatibilidad con Linux";
	$txt_with       = "con";
	$txt_rating     = "valoraci&oacute;n";
	$txt_ratings    = "valoraciones";
	$txt_price      = "Precio";
	$txt_out_of_stock = "No disponible";
	$txt_button     = "/wp-uploads/amazon-button.png";
	$txt_button_width = 'width="185"';
	$siteurl 	= "linux-hardware-guide.com";
	$txt_pricetrend = "Tendencia del precio";
	$txt_shipping	= "sin gastos de env&iacute;o";
	$txt_on_stock   = "disponible";
	$txt_category   = "Categor&iacute;a";
	$txt_date       = "Fecha";
	$txt_updated    = "Actualizado";
	$txt_tooltip2   = "Los precios proporcionados por Amazon no incluyen los gastos de envío adicionales derivados del producto y del proveedor de Amazon Marketplace.";
	$txt_buy_from   = "comprar a";
	$txt_search_at  = "Búsqueda en";
	$txt_preorder   = "Reserve en";
	$txt_not_avail_at = "No disponible en este momento en";
	$txt_never_avail= "No se encuentra en el catálogo de";
	$txt_amz_getfrom= 'Necesitará obtner este número de <a href="http://amazon.com/">Amazon.com</a>';
	$txt_amz_tooltip_loggedin = "Si utiliza este producto con Linux por favor evalúelo y comparta sus experiencias en la sección de comentarios al final de esta página para apoyar así a otros usarios de Linux. ";
	$txt_amz_tooltip_not_loggedin = "En esta página sólo se evalúa la compatibilidad con Linux del producto, no la calidad general del mismo.Si utiliza este producto con Linux, por favor evalúelo y comparta sus experiencias en la sección de comentarios al final de esta página para apoyar así a otros usarios de Linux.";

	//comments.php
	$txt_no_respo   	= "Sin respuesta";
	$txt_one_resp  		= "Una respuesta";
	$txt_responses          = "Respuestas";
	$txt_to  		= "a";
	$txt_comments   	= "Commentarios";
	$txt_comments_intro     = "Por favor, emplee la sección de comentarios para indicar correciones del artículo así como extractos de <tt>lspci, lsusb, lshw, dmesg</tt> etc.
        Además, utilice la sección para el intercambio de experiencias con este componente de hardware o para obtener ayuda para la configuración de otros propietarios de este mismo hardware.
	<br />&nbsp;<br />";
	$txt_comments_new_discussion  = "Comenzar una nueva discusión";


	// Cubepoints
	$txt_cp_karma		= "Karma";
	$txt_cp_points		= "puntos";
	$txt_cp_donates_to	= "Dona a";
	$txt_cp_language	= "Idioma";
	$txt_cp_longtext	= 'El-Hardware-Guía Linux dona ingresos mensuales publicidad de la bandera de nuevo a la comunidad Linux.
   Los usuarios registrados votan con sus puntos de Karma a quien donaciones van (<a href="./donations">ver más detalles</a>).
Suma Donación de mes en curso';
	$txt_cp_title		= "Ranking de usuarios y sus donaciones";
	$txt_cp_quarterly       = "Puntos Trimestrales";
	$txt_cp_totalkarma      = "Karma totales";
	$txt_cp_details         = "Detalles";


        //comments-template.php
	$txt_privacy_stat	= "La dirección de correo electrónico no será publicada";
        $txt_use_tags           = 'Use los siguientes etiquetas y atributos <abbr title="HyperText Markup Language">HTML</abbr>:';
        $txt_logged_in_as       = "Conectado como";
        $txt_logout             = "Desconnectarse";
        $txt_send_comment       = "Enviar comentario";
        $txt_cancel_reply       = 'Anular respuesta';
        $txt_reply_to           = 'Responder a';
        $txt_comments           = "Commentarios";
        $txt_comment            = "";
        $txt_to_comment         = "Commentario";
        $txt_website            = "Sitio web";
        $txt_reply              = "Respuesta";
        $txt_says               = "dice";
        $txt_compat_rat         = "Evaluar la compatibilidad con Linux";
	$txt_product_rating     = "Evaluación del producto";

	//avatar tooltip
	$txt_avt_reguser	= "Usario registrado";
	$txt_avt_karmapoints	= "Puntos Karma";
	$txt_avt_rank		= "Clasificaci&oecute;n";
	$txt_avt_click		= "Haga clic avatar para el perfil de usuario";

	//scan overview table
	$txt_scan_distribution  = "Distribución";
	$txt_scan_kernel        = "Versión del núcleo";
	$txt_scan_scandate      = "Fecha de exploración";
	$txt_scan_result	= "Resultado";
	$txt_scan_results	= "Resultados";
	$txt_scan_title		= "Configuraciones de hardware identificados";
	$txt_scan_text          = 'Este componente de hardware fue utilizado por los usuarios de Linux en las siguientes configuraciones del sistema. Estos resultados se recogieron por nuestra <a href="./add-hardware">LHG Scan Tool</a>:';


	//attachment
	$txt_att_filetypes	= "Tipos de archivo permitidos";
	$txt_att_maxsize  	= "tamaño máximo de archivo";
	$txt_att_attachment  	= "Apego";


	//header.php
	$txt_reg   = "Registrarse";
	$txt_login = "Iniciar sesi&oacute;n";

        //footer.php
	$txt_follow_twitter = "Síguenos en Twitter";
	$txt_mail_us = "enviar correo electrónico";
	$txt_flattr_us = "Flattr";
        $txt_designed = "Diseñado por";
        $txt_for_mobile = "Optimizado para tableta y smartphone";
	$txt_footer_general="General";
	$txt_footer_contact="Contacto";
	$txt_footer_faq="Preguntas más frecuentes";
	$txt_footer_contributors="Contribuyente";
	$txt_footer_support="Soporte LHG";
	$txt_footer_problem="Communicar un problema";
	$txt_footer_donate="Donar";
	$txt_footer_suppliers="Fabricante y anunciantes";
	$txt_footer_hwsuppliers="Proveedor de hardware";
	$txt_footer_manufacturers="Fabricante de hardware";
	$txt_footer_advertisers="Anunciantes";


	//registration-form-widget
	$txt_reg_user = "Nombre de usuario";
	$txt_reg_mail = "e-mail";

	//s8-custom-login
	$txt_remember_me = 'Ricordarme';
        $txt_username  	= "Nombre de usuario";
	$txt_register  	= "Registrarse";
	$txt_mail  	= "Dirección de correo electrónico";
        $txt_pwd_send   = "Se le enviará su constraseña a su correo electrónico.";
        $txt_new_pwd    = "Obtener contraseña nueva";
        $txt_user_or_mail = "Nombre de usuario o correo electrónico";


	//searchform.php
	$txt_search 		= 'Buscar'.'&nbsp;<i class="icon-arrow-right icon-button"></i>';


	//category post list
	$txt_cpl_noimage = "Imagen no disponible";
	$txt_cpl_comments= "comentarios"; //="Kommentare";
	$txt_cpl_comment = "comentario";

        //wp-postrating
	$txt_vote = "voto";
	$txt_votes = "votos";
	$txt_average = "media";
	$txt_out_of = "sobre un total de";

        //archive.php
        $txt_arch_related_cat  = "Categor&iacute;as similar";
  	$txt_arch_category     = "Categor&iacute;a:";
  	$txt_arch_search_terms = "Conceptos de b&uacute;squeda";
  	$txt_arch_and          = "y";
  	$txt_arch_combination  = "combinaci&oacute;n de:";
  	$txt_arch_display_opt  = "Opciones de visualización";
        $txt_arch_sort_price   = "Clasificar por precio";
        $txt_arch_sort_rating  = "Clasificar por valoración";
	$txt_arch_show_avail   = "Mostrar sólo si está disponible";
  	$txt_arch_stars        = "estrellas";
	$txt_arch_home	       = "Página de inicio";
	$txt_filter_by_rating  = "Filtrar por valoración";
	$txt_filter_by_tags    = "Filtrar por etiquetas";
	$txt_sorting_options   = "Criterios de clasifición";


	//bwp-comments
	$txt_bwp_commenton     = "Opina sobre";


        //Twitter widget
	$txt_twt_title		= '<i class="icon-twitter menucolor"></i> '."Noticias (v&iacute;a Twitter)";

	//mobile theme
	$txt_mobile_search	    = "Búsqueda de hardware";
	$txt_mobile_show_all        = "Todos los resultados de la búsqueda";
	$txt_mobile_fullsite        = "Ver todo el sitio web";

	//custom recent post
	$txt_crp_title		    = "Hardware de Linux añadido recientemente";

	//subscribe
	$txt_subscr_component_added = "Componente añadido al perfil";
	$txt_subscr_was_added       = "El siguiente componente de hardware ha sido añadido a su perfil de hardware";
	$txt_subscr_return 	    = "Volver a";
	$txt_subscr_to_hw	    = "Descripción del hardware";
	$txt_subscr_or_manage	    = "o administrar su perfil de hardware a continuación";
	$txt_subscr_answer 	    = "Recibir respuesta por correo electrónico";
	$txt_subscr_regist_date     = "Fecha de registro";
	$txt_subscr_modus	    = "Estado";
	$txt_subscr_article	    = "Sumario del hardware";
	$txt_subscr_select_all	    = "Seleccione todos";
	$txt_subscr_select_inv	    = "Invertir selecci&oacute;n";
	$txt_subscr_action	    = "Acción ";
	$txt_subscr_delete	    = "Cancelar la entrada ";
	$txt_subscr_suspend	    = "Desactivar la entrada ";
	$txt_subscr_reply_only	    = "Respuestas a mis comentarios ";
	$txt_subscr_activate	    = "Manténgase informado ";
	$txt_subscr_update          = "Actualización ";
	$txt_subscr_active          = $txt_subscr_activate;
	$txt_subscr_inactive        = "Desactivado ";
	$txt_subscr_manage_hw       = "Administre su perfil de hardware ";
	$txt_subscr_pub_hw_prof     = "Perfil de hardware p&uacute;blico";
	$txt_subscr_name 	    = "Nombre";
	$txt_subscr_email	    = "Email";
	$txt_subscr_more	    = "más";
        $txt_hwprof_of              = "Perfil de hardware público del";


        //user-submit-form.php
	$txt_submit_name = "Su nombre o correo electrónico";
        $txt_product  	 = "Código o nombre del producto:";
        $txt_link        = "Página web con las epecificaciones del producto (ej. página del proveedor o página de Amazon):";
        $txt_description = "Descripción:";
        $txt_descr_details =
'Por ejemplo:
pasos de configuración necesarios para utilizar el hardware con Linux, versión del kernel Linux, distribución de Linux, salida de "dmesg", salida de "lspci", "lsusb" y "lshw"...';
        $txt_picture     = "Subir foto(s):";
        $txt_more_pic    = "más fotos";
        $txt_submit      = 'Enviar';
	$txt_anonymous   = "An&oacute;nimo";

        //search.php
        $txt_search_results  = "Resultados de la búsqueda";
  	$txt_no_result       = "No se ha encontrado ningún comentario. ¿Nueva búsqueda?";

        //pluggable.php
        $txt_mail_username = "Nombre de usuario";
	$txt_mail_password = "Contraseña";
	$txt_mail_URL = "http://www.linux-hardware-guide.com/es/login";
	$txt_mail_welcome = "Bienvenido/a a Linux-Hardware-Guide.com:\r\n
        a continuación le indicamos su nombre de usuario y su contraseña, generada automáticamente.\r\n";

	$txt_mail_end = "Puede iniciar sesión en at http://www.linux-hardware-guide.com/es/login y modificar sus características, ej. contraseña y avatar.\r\nSaludos,\rEl equipo de Linux-Hardware-Guide";
	$txt_mail_unp = "Su nombre de usuario y contraseña";

	//wp-admin/admin-footer.php
	$txt_admin_footer='Por favor, póngase en contacto con<a mailto="linux.hardware.guide@gmail.com">linux.hardware.guide@gmail.com</a>.';

	//priceDB
	$txt_lhgdb_overview         = "Visión general de los proveedores";
	$txt_lhgdb_exclporto	    = "incluyendo los gastos de envío";
	$txt_lhgdb_inclporto	    = "excluyendo los gastos de envío";
	$txt_lhgdb_welcome          = "Bienvenido a la Linux-Hardware-Guide";
	$txt_lhgdb_karmapoints	    = "puntos de karma";
        $txt_lhgdb_numhwscans       = "exploraciones de hardware subidos";
        $txt_lhgdb_numhwscan        = "exploración de hardware Subida";
	$txt_lhgdb_karmadescr       = 'Sus puntos de Karma se utilizan para apoyar económicamente a ciertos proyectos Linux. Sus donaciones son actualmente van a la';
	$txt_lhgdb_karmadescr_end   = 'Seleccione su destino donación en su <a href="./profile.php">página de perfil</a>.';
	$txt_lhgdb_karma_howto	    = 'Cómo acumular rápidamente Karma?';
	$txt_lhgdb_karma_1	    = '1) Cargar su exploración de hardware: iniciar el siguiente comando en un terminal';
	$txt_lhgdb_karma_2	    = '2) Tasa y hacer comentarios sobre su hardware Linux';
	$txt_lhgdb_karma_3	    = "Se necesita al menos";
	$txt_lhgdb_karma_4	    = 'puntos de Karma para crear nuevos artículos.';

	//Live Search
	$moreResultsText	    = "Ver más resultados";


}


if ($region == "it") {

	$txt_hwprofile          = " Mio profilo hardware";

        //wp-one-post-widget
	$txt_summary    	= "Riassunto";
	$txt_supplier  		= "Fornitore";
	$txt_cheapest_supplier 	= "Cheapest fornitore";
	$txt_rating     	= "valutazione";
	$txt_Rating     	= "Valutazione";
	$txt_ratings    	= "valutazioni";
	$txt_select     	= "Select country &amp; currency";
	$txt_sim_tags   	= "Tag simili";
	$txt_combine_tags 	= "Combinare";
	$txt_wpop_register   	= "Aggiungi al tuo profilo hardware:";
	$txt_register_long	= "Tieniti informato sulle novità Linux e sulle discussioni relative a questo componente.";
	$txt_send               = "Aggiungere";
	$txt_manage_subscr      = "Gestione sottoscrizioni";
	$txt_not_avail          = "Non disponibile da";
	$txt_rate_yourself      = "Tasso di Hardware";
	$txt_opw_num_ratings    = "Numero di valutazioni";
	$txt_opw_average_rating = "Valutazione media";
	$txt_opw_rating_overview= "Panoramica dei valutazioni";
	$txt_opw_hardware       = "Hardware";
	$txt_opw_registered	= "Utenti registrati Linux";

	$txt_user_rating_for_setup = "L'utente ha fornito il seguente voto per questa configurazione hardware:";


	//related posts thumbnails
	$txt_rpt_creation="Creato il";
	$txt_rpt_update="Aggiornato al";
	$txt_rpt_title="<h2>Linux Hardware simile</h2>";//<!--:zh--><h2>类似Linux的硬件</h2><!--:--><!--:ja--><h2>同じようなLinuxのハードウェア</h2><!--:--><!--:es--><h2></h2><!--:--><!--:it--><h2></h2><!--:-->
	$txt_rpt_sub_by="insertio da";


	//amazon-product-in-a-post.php
	$txt_compat  	= "Compatibilità con Linux";
	$txt_Compat  	= "Recensione di Compatibilità con Linux";
	$txt_with       = "con";
	$txt_rating     = "valuatione";
	$txt_ratings    = "valuationi";
	$txt_price      = "Prezzo";
	$txt_out_of_stock = "Non disponibile";
	$txt_button     = "/wp-uploads/amazon-button.png";
	$txt_button_width = 'width="185"';
	$siteurl 	= "linux-hardware-guide.com";
	$txt_pricetrend = "Andamento dei prezzi";
	$txt_shipping	= "senza spese di spedizione";
	$txt_on_stock   = "disponible";
	$txt_category   = "Categoria";
	$txt_date       = "Data";
	$txt_updated    = "Aggiornato";
	$txt_tooltip2   = "Prezzi forniti da Amazon non includono le spese di spedizione che potrebbero essere causa seconda del prodotto e Amazon Marketplace fornitore.";
	$txt_buy_from   = "Acquistare da";
	$txt_search_at  = "Ricerca su";
	$txt_preorder   = "Riserva a";
	$txt_not_avail_at = "Non disponibile al momento";
	$txt_never_avail= "Non nel catalogo di";
	$txt_amz_tooltip_not_loggedin = "Solo la compatibilità Linux è valutato su questa pagina, non la qualità generale del prodotto. Se si utilizza questo prodotto con Linux, si prega di votarla e condividere la vostra esperienza nell'area dei commenti di questa pagina (in basso) per supportare altri utenti Linux.";

	//comments.php
	$txt_no_respo   	= "Senza risposta";
	$txt_one_resp  		= "Una risposta";
	$txt_responses          = "risposte";
	$txt_to  		= "a";
	$txt_comments   	= "Commenti";
	$txt_comments_intro     = "Si prega di utilizzare la sezione commenti a presentare correzioni per l'articolo e le pertinenti stralci di
        <tt>lspci -vnn, lsusb, lshw, dmesg,</tt>
        Inoltre, utilizzare la sezione per lo scambio di esperienze con questo componente hardware o ricerca di aiuto configurazione da altri proprietari di questo hardware.
	<br />&nbsp;<br />";

        //comments-template.php
	$txt_privacy_stat	= "L'indirizzo di posta elettronica non sarà pubblicato.";
        $txt_use_tags           = 'Utilizza i seguenti tag e attributi <abbr title="HyperText Markup Language">HTML</abbr>:';
        $txt_logged_in_as       = "Loggato come";
        $txt_logout             = "Esci";
        $txt_send_comment       = "Invia commento";
        $txt_cancel_reply       = 'Annulla la risposta';
        $txt_reply_to           = 'Rispondi a';
        $txt_comments           = "Commenti";
        $txt_comment            = "";
        $txt_to_comment         = "Commento";
        $txt_website            = "Sito web";
        $txt_reply              = "Rispondi";
        $txt_says               = "dice";
        $txt_compat_rat         = "Valuta la compatibilità con Linux";
	$txt_product_rating     = "Valutazione prodotto";
	$txt_comments_new_discussion  = "Inizia nuova discussione";


	//avatar tooltip
	$txt_avt_reguser	= "Utente registrato";
	$txt_avt_karmapoints	= "Punti Karma";
	$txt_avt_rank		= "Ranking";
	$txt_avt_click		= "Clicca avatar per il profilo dell'utente";

	//scan overview table
	$txt_scan_distribution  = "Distribuzione";
	$txt_scan_kernel        = "Versione kernel";
	$txt_scan_scandate      = "Data di scansione";
	$txt_scan_result	= "Risultato";
	$txt_scan_results	= "Risultati";
	$txt_scan_title		= "Configurazioni hardware identificati";
	$txt_scan_text          = 'Questo componente hardware è stato utilizzato dagli utenti Linux nelle seguenti configurazioni di sistema. Questi risultati sono stati raccolti dal nostro <a href="./add-hardware">LHG Scan Tool</a>:';



	// Cubepoints
	$txt_cp_karma		= "Karma";
	$txt_cp_points		= "punti";
	$txt_cp_donates_to	= "Dona a";
	$txt_cp_language	= "Lingua";
	$txt_cp_longtext	= 'Il Linux Hardware-Guide dona mensile introiti banner pubblicitari torna della comunità Linux.
   Gli utenti registrati votano con i loro punti Karma ai quali le donazioni stanno andando
        (<a href="./donations">vedi maggiori dettagli</a>). Donazione somma di mese in corso';
	$txt_cp_title		= "Ranking utenti e le loro donazioni ";
	$txt_cp_quarterly       = "Punti trimestrali";
	$txt_cp_totalkarma      = "Karma totale";
	$txt_cp_details         = "Dettagli";


	//attachment
	$txt_att_filetypes	= "Tipi di file consentiti";
	$txt_att_maxsize  	= "Dimensione massima del file";
	$txt_att_attachment  	= "Attaccamento";


	//header.php
	$txt_reg   = "Registro";
	$txt_login = "Entra";

	//footer.php
	$twitAcc="LinuxHardware";
	$FL_Thing="1446180/Linux-Hardware-Guide-com";
	$FURL = "http://www.linux-hardware-guide.com";
	$FMail = "linux.hardware.guide@gmail.com";
	$txt_follow_twitter = "Seguici su Twitter";
	$txt_mail_us = "Contattarci via email";
	$txt_flattr_us = "Donate via Flattr";
	$txt_designed = "Progettato da";
        $txt_for_mobile = "Ottimizzato per tablet e smartphone";
	$txt_footer_general="Generale";
	$txt_footer_contact="Contatto";
	$txt_footer_faq="FAQ";
	$txt_footer_contributors="Collaboratori";
	$txt_footer_support="Supporto LHG";
	$txt_footer_problem="Communicare un problema";
	$txt_footer_donate="Donare";
	$txt_footer_suppliers="produzione e inserzionisti";
	$txt_footer_hwsuppliers="Fornitori di hardware";
	$txt_footer_manufacturers="Produttori di hardware";
	$txt_footer_advertisers="Inserzionisti";


	//registration-form-widget
	$txt_reg_user = "Nome utente";
	$txt_reg_mail = "Email";

	//searchform.php
	$txt_search 		= 'Ricerca'.'<i class="icon-arrow-right icon-button"></i>';


	//category post list
	$txt_cpl_noimage = "Immagine non disponibile";
	$txt_cpl_comments= "commenti"; //="Kommentare";
	$txt_cpl_comment = "commento";

        //wp-postrating
	$txt_vote = "valutazione";
	$txt_votes = "valutazioni";
	$txt_average = "media";
	$txt_out_of = "su";

        //archive.php
        $txt_arch_related_cat  = "Categorie simili";
  	$txt_arch_category     = "Categoria:";
  	$txt_arch_search_terms = "Termini di ricerca";
  	$txt_arch_and          = "e";
  	$txt_arch_combination  = "Combinazione di:";
  	$txt_arch_display_opt  = "Opzioni di visualizzazione";
        $txt_arch_sort_price   = "Ordina per prezzo";
        $txt_arch_sort_rating  = "Ordina per valutazione";
	$txt_arch_show_avail   = "Mostra solamente se disponibile";
  	$txt_arch_stars        = "stelle";
	$txt_arch_home	       = "Pagina principale";
	$txt_filter_by_rating  = "Filtrare per valutazione";
	$txt_filter_by_tags    = "Filtrare per tag";
	$txt_sorting_options   = "Opzioni di ordinamento";


	//bwp-comments
	$txt_bwp_commenton     = "Commentare su";

        //Twitter widget
	$txt_twt_title		= '<i class="icon-twitter menucolor"></i> '."Messaggi (via Twitter)";

	//mobile theme
	$txt_mobile_search	    = "Cerca hardware";
	$txt_mobile_show_all        = "Visualizza tutti i risultati della ricerca";
	$txt_mobile_fullsite        = "Visualizza pagina completa";

	//custom recent post
	$txt_crp_title		    = "Hardware Linux aggiunti di recente";//[:zh]最近添加的硬件的Linux[:ja]最近追加されたLinuxのハードウェア[:it

	//subscribe
	$txt_subscr_component_added = "Componente aggiunto al profilo ";
	$txt_subscr_was_added       = "Il seguente componente hardware è stato aggiunto al tuo profilo hardware ";
	$txt_subscr_return 	    = "Tornare alla ";
	$txt_subscr_to_hw	    = "descrizione dell'hardware ";
	$txt_subscr_or_manage	    = "o modifica il tuo profilo hardware sottostante.";
	$txt_subscr_answer 	    = "Ricevi risposte per posta elettronica";
	$txt_subscr_regist_date     = "Data di registrazione";
	$txt_subscr_modus	    = "Stato";
	$txt_subscr_article	    = "Sommario hardware";
	$txt_subscr_select_all	    = "Seleziona tutti";
	$txt_subscr_select_inv	    = "Inverti la selezione";
	$txt_subscr_action	    = "Azione";
	$txt_subscr_delete	    = "Annulla inserimento";
	$txt_subscr_suspend	    = "Disattiva inserimento";
	$txt_subscr_reply_only	    = "Le risposte ai miei commenti";
	$txt_subscr_activate	    = "Tieniti informato";
	$txt_subscr_update          = "Aggiornamento";
	$txt_subscr_active          = $txt_subscr_activate;
	$txt_subscr_inactive        = "Disattivato";
	$txt_subscr_manage_hw       = "Modifica il tuo profilo hardware";
	$txt_subscr_pub_hw_prof     = "Profilo pubblico hardware";
	$txt_subscr_name 	    = "Nome";
	$txt_subscr_email	    = "Email";
	$txt_subscr_more	    = "di più";
        $txt_hwprof_of              = "Profilo hardware pubblico di";


        //user-submit-form.php
	$txt_submit_name = "Tuo nome o indirizzo e-mail";
        $txt_product  	 = "ID prodotto o nome prodotto:";
        $txt_link        = "Sito web con specifiche del prodotto (p.es. pagina del fornitore o pagina Amazon):";
        $txt_description = "Descrizione:";
        $txt_descr_details =
'Per esempio:
accorgimenti per la configurazione necessari per l\'utilizzo del hardware con Linux, versione Linux kernel, distribuzione Linux, uscita di „dmesg“, uscita di „lspci -nnk“, „lsusb“, e „lshw“...';
        $txt_picture     = "Carica immagine/i:";
        $txt_more_pic    = "altre immagini";
        $txt_submit      = 'Invia';
	$txt_anonymous   = "Anonimo";


        //search.php
        $txt_search_results  = "Cerca risultati";
  	$txt_no_result       = "Nessun post trovato. Vuoi tentare un'altra ricerca?";

	//s8-custom-login
	$txt_remember_me = 'Ricordami';
        $txt_username  	= "Nome utente";
	$txt_register  	= "Registrati";
	$txt_mail  	= "Indirizzo e-mail";
        $txt_pwd_send   = "Riceverai una password via e-mail.";
        $txt_new_pwd    = "Ottieni una nuova password";
        $txt_user_or_mail = "Nome utente o e-mail";


        //pluggable.php
        $txt_mail_username = "Nome utente";
	$txt_mail_password = "Password";
	$txt_mail_URL = "http://www.linux-hardware-guide.com/it/login";
	$txt_mail_welcome = "Benvenuto su Linux-Hardware-Guide.com:\r\n
Sotto trovi il tuo nome utente e la password generata automaticamente.\r\n";
	$txt_mail_end = "Puoi effettuare il login su http://www.linux-hardware-guide.com/it/login e cambiare le tue impostazioni, ad es. la password e l'avatar.\r\nCordiali saluti,\rIl team Linux-Hardware-Guide";
	$txt_mail_unp = "Il tuo nome utente e la password";

	//wp-admin/admin-footer.php
	$txt_admin_footer='Si prega di contattare abx@xyz.com <a mailto="linux.hardware.guide@gmail.com">linux.hardware.guide@gmail.com</a>.';

	//priceDB
	$txt_lhgdb_overview         = "Panoramica dei fornitori";
	$txt_lhgdb_exclporto	    = "Escl. spese di spedizione";
	$txt_lhgdb_inclporto	    = "Affrancatura";
	$txt_lhgdb_welcome          = "Benvenuti al Linux-Hardware-Guide";
	$txt_lhgdb_karmapoints	    = "punti karma";
        $txt_lhgdb_numhwscans       = "scansioni hardware caricati";
        $txt_lhgdb_numhwscan        = "scansione dell'hardware caricati";
	$txt_lhgdb_karmadescr       = 'I suoi punti di Karma sono utilizzati per sostenere finanziariamente alcuni progetti Linux. Le vostre donazioni sono attualmente in corso per la';
	$txt_lhgdb_karmadescr_end   = 'Seleziona il tuo target donazione sulla <a href="./profile.php">pagina del tuo profilo</a>.';
	$txt_lhgdb_karma_howto	    = 'Come guadagnare rapidamente Karma?';
	$txt_lhgdb_karma_1	    = "1) <a href='/it//add-hardware'>Carica la scansione dell'hardware</a>: avviare il seguente comando in un terminale";
	$txt_lhgdb_karma_2	    = '2) votare e commentare sul proprio hardware Linux';
	$txt_lhgdb_karma_3	    = 'Hai bisogno di almeno';
	$txt_lhgdb_karma_4	    = 'punti Karma per creare nuovi articoli.';
	$txt_shipping_costs	    = "Costi di spedizione";

	//Live Search
	$moreResultsText	    = "Visualizza più risultati";


}


if ($region == "cn") {

	$txt_hwprofile          = " 我的硬件配置文件";


	//amazon-product-in-a-post.php
	$txt_compat  	= "Linux兼容";
	$txt_Compat  	= "回顾Linux的兼容性";
	$txt_with       = "同";
	$txt_rating     = "等级";
	$txt_Rating     = $txt_rating;
	$txt_ratings    = "等级";
	$txt_price      = "P价格";
	$txt_out_of_stock = "缺货";
	$txt_button     = "/wp-uploads/amazon-button.png";
	$txt_button_width = 'width="185"';
	$siteurl 	= "linux-hardware-guide.com";
	$txt_pricetrend = "价格走势";
	$txt_shipping	= "不含运费成本";
	$txt_on_stock   = "可用的";
	$txt_category   = "类别";
	$txt_date       = "日期";
	$txt_updated    = "更新";
	$txt_tooltip2   = "由亚马逊提供的价格不包括可能是由于根据产品和亚马逊的市场供应商的运输成本。";
	$txt_buy_from   = "买的起";
	$txt_search_at  = "在搜索";
	$txt_preorder   = "在搜索";
	$txt_not_avail_at = "在搜索";
	$txt_never_avail= "不在目录";
	$txt_amz_getfrom= 'You will need to get this from <a href="http://amazon.com/">Amazon.com</a>';
	$txt_amz_asin   = "Amazon Product ASIN (ISBN-10)";
	//$txt_amz_title  = "Amazon Product Information";
	$txt_amz_tooltip_not_loggedin = "只有Linux兼容的额定此页面上，而不是产品的总体质量。如果您使用本产品使用Linux，请率和分享您的经验，在此页（底部）的注释区域支持其他的Linux用户。";

	$txt_user_rating_for_setup = “用户提供了以下的评价为这个硬件设置：”;


	//header.php
	$txt_reg   = "挂号";
	$txt_login = "登录";

        //footer
	$txt_follow_twitter = "后续我们的Twitter";
	$txt_mail_us = "发送电子邮件";
	$txt_flattr_us = "通过捐赠";
	$txt_designed = "通过设计";
        $txt_for_mobile = "优化平板电脑和智能手机";
	$txt_footer_general="一般";
	$txt_footer_contact="聯繫";
	$txt_footer_faq="常問問題";
	$txt_footer_contributors="貢獻者";
	$txt_footer_support="支持我們";
	$txt_footer_problem="報告問題";
	$txt_footer_donate="捐贈";
	$txt_footer_suppliers="生產商和廣告商";
	$txt_footer_hwsuppliers="硬件供應商 ";
	$txt_footer_manufacturers="硬件製造商";
	$txt_footer_advertisers="廣告商";


	//searchform.php
	$txt_search 		= '&nbsp;&nbsp;搜寻&nbsp;&nbsp;'.'&nbsp;<i class="icon-arrow-right icon-button"></i>';

	//registration-form-widget
	$txt_reg_user = "用户名";
	$txt_reg_mail = "电子邮件";

	//archive.php
	$txt_arch_related_cat  = "相关类别";
	$txt_arch_category     = "类别:";
	$txt_arch_search_terms = "搜索字词";
	$txt_arch_and          = "和";
	$txt_arch_combination  = "组合:";
	$txt_arch_display_opt  = "显示选项";
	$txt_arch_sort_price   = "按价格排序";
	$txt_arch_sort_rating  = "排序方式评价";
	$txt_arch_show_avail   = "只显示（如果可用）";
	$txt_arch_stars        = "明星";
	$txt_arch_home	       = "首页";

	//wp-one-post-widget
$txt_summary    	= "摘要";
$txt_supplier  		= "提供者";
$txt_cheapest_supplier 	= "最便宜的供应商";
$txt_rating     	= "等级";
$txt_ratings    	= "等级";
$txt_select     	= "Select country &amp; currency";
$txt_sim_tags   	= "类似的标签";
$txt_combine_tags 	= "结合";
$txt_wpop_register   	= "添加到硬件配置文件:";
$txt_register_long	= "并得到通知，如果新的驱动程序存在或Linux的用户提供相同的硬件寻求帮助。";
$txt_send               = "加";
$txt_manage_subscr      = "管理订阅";
$txt_not_avail          = "缺货的";
$txt_rate_yourself      = "估价硬件";
	$txt_opw_num_ratings    = "评分数目 ";
	$txt_opw_average_rating = "平均评级 ";
	$txt_opw_rating_overview= "评级概览 ";
	$txt_opw_hardware       = "硬件";
	$txt_opw_registered	= "注册Linux用户";

//comments.php
$txt_no_respo   	= "没有回应";
$txt_one_resp  		= "1回应";
$txt_responses          = "回应";
$txt_to  		= "给";
$txt_comments   	= "评论";
$txt_comments_intro     = "请使用注释部分提出更正的文章，以及相关的摘录 <tt>lspci -nnv, lsusb -v, lshw, dmesg</tt> ... 
	 
此外，使用一节的经验与此硬件组件或搜索该硬件的其他业主配置帮助交流。
 	<br />&nbsp;<br />";
	$txt_comments_new_discussion  = "开始新的讨论";


	// Cubepoints
	$txt_cp_karma		= "因果报应 ";
	$txt_cp_points		= "积分 ";
	$txt_cp_donates_to	= "捐赠给 ";
	$txt_cp_language	= "语言 ";
	$txt_cp_longtext	= 'Linux的硬件指南捐出每月的横幅广告收入回到了Linux社区。
  注册用户投票与他们的噶分给谁捐赠会
        (<a href="./donations">详情请见</a>)。持续一个月的捐赠款项';
	$txt_cp_title		= "用户排名和他们的捐款 ";
	$txt_cp_quarterly       = "分季度";
	$txt_cp_totalkarma      = "详细信息";
	$txt_cp_details         = "总因果报应";



//comments-template.php
$txt_privacy_stat	= "电子邮件地址不会被公开。";
$txt_compat_rat         = "率的Linux兼容性";
$txt_use_tags           = '使用下面的 <abbr title="HyperText Markup Language">HTML</abbr> 
标签和属性';
$txt_logged_in_as       = "登录为";
$txt_logout             = "注销";
$txt_send_comment       = "提交评论";
$txt_cancel_reply       = '取消回复';
$txt_reply_to           = '回复';
$txt_comments           = "评论";
$txt_comment            = "";
$txt_to_comment         = "评论";
$txt_website            = "网站";
$txt_reply              = "回复";
$txt_says               = "说";
$txt_product_rating     = "产品评级";


	//avatar tooltip
	$txt_avt_reguser	= "注册用户 ";
	$txt_avt_karmapoints	= "噶点 ";
	$txt_avt_rank		= "排名 ";
	$txt_avt_click		= "点击头像用户的个人资料";


	//attachment
	$txt_att_filetypes	= "允许的文件类型";
	$txt_att_maxsize  	= "最大文件大小 ";
	$txt_att_attachment  	= "附件 ";

	//scan overview table
	$txt_scan_distribution  = "分配";
	$txt_scan_kernel        = "内核版本";
	$txt_scan_scandate      = "扫描日期";
	$txt_scan_result	= "结果";
	$txt_scan_results	= "结果";
	$txt_scan_title		= "确定硬件配置";
	$txt_scan_text          = '这种硬件组件在以下系统配置下使用的Linux用户。这些结果通过收集我们的 <a href="./add-hardware">扫描工具</a>:';



	//related posts thumbnails
	$txt_rpt_creation   ="創建";
	$txt_rpt_update     ="更新";
	$txt_rpt_title="<h2>类似Linux的硬件</h2>";//<!--:zh--><h2>类似Linux的硬件</h2><!--:--><!--:ja--><h2>同じようなLinuxのハードウェア</h2><!--:--><!--:es--><h2></h2><!--:--><!--:it--><h2></h2><!--:-->
	$txt_rpt_sub_by="提交";

	//bwp-comments
	$txt_bwp_commenton     = "发表评论";

	//wp-postrating
	$txt_vote = "投票";
	$txt_votes = "投票";
	$txt_average = "平均";
	$txt_out_of = "出最多";

        //archive.php
	$txt_filter_by_rating  = "按等级筛选";
	$txt_filter_by_tags    = "通过搜索词过滤";
	$txt_sorting_options   = "排序选项";


        //Twitter widget
	$txt_twt_title		= '<i class="icon-twitter menucolor"></i> '."信息 (通过 Twitter)";

	//mobile theme
	$txt_mobile_search	    = "搜索硬件";
	$txt_mobile_show_all        = "所有的搜索结果都显示";
	$txt_mobile_fullsite        = "查看完整版";


	//custom recent post
	$txt_crp_title		    = "最近添加的硬件的Linux";//[:ja]最近追加されたLinuxのハードウェア[:it


	//subscribe
	$txt_subscr_component_added = "组件添加到配置文件 ";
	$txt_subscr_was_added       = "下面的硬件组件添加到您的硬件配置文件 ";
	$txt_subscr_return 	    = "返回到 ";
	$txt_subscr_to_hw	    = "硬件描述 ";
	$txt_subscr_or_manage	    = "或管理您的硬件配置文件 ";
	$txt_subscr_answer 	    = "每个邮件接收答案";
	$txt_subscr_regist_date     = "注册日期 ";
	$txt_subscr_modus	    = "状态 ";
	$txt_subscr_article	    = "硬件概述 ";
	$txt_subscr_select_all	    = "选择所有 ";
	$txt_subscr_select_inv	    = "反选 ";
	$txt_subscr_action	    = "行动 ";
	$txt_subscr_delete	    = "取消报名 ";
	$txt_subscr_suspend	    = "关闭入口 ";
	$txt_subscr_reply_only	    = "回复我的评论 ";
	$txt_subscr_activate	    = "保持联络 ";
	$txt_subscr_update          = "更新 ";
	$txt_subscr_active          = $txt_subscr_activate;
	$txt_subscr_inactive        = "停用 ";
	$txt_subscr_manage_hw       = "管理您的硬件配置文件 ";
	$txt_subscr_pub_hw_prof     = "公共硬件配置文件 ";
	$txt_subscr_name 	    = "名称 ";
	$txt_subscr_email	    = "电子邮件 ";
	$txt_subscr_more	    = "更多";
        $txt_hwprof_of              = "公共硬件配置文件";

	//priceDB
	$txt_lhgdb_overview         = "供应商的概述";
	$txt_lhgdb_exclporto	    = "including shipping costs";
	$txt_lhgdb_inclporto	    = "excluding shipping costs";
	$txt_lhgdb_welcome          = "欢迎来到 Linux-Hardware-Guide";
	$txt_lhgdb_karmapoints	    = "噶点";
        $txt_lhgdb_numhwscans       = "上传的硬件扫描";
        $txt_lhgdb_numhwscan        = "上传的硬件扫描";
	$txt_lhgdb_karmadescr       = '你噶点用于财政上支持某些Linux项目。目前您的捐款是要';
	$txt_lhgdb_karmadescr_end   = '选择您的个人资料页上您的捐款目标。';
	$txt_lhgdb_karma_howto	    = '如何快速赚噶？';
	$txt_lhgdb_karma_1	    = '1）上传您的硬件扫描：在终端启动下面的命令';
	$txt_lhgdb_karma_2	    = '2）率和评论你的Linux硬件';
	$txt_lhgdb_karma_3	    = '你至少需要';
	$txt_lhgdb_karma_4	    = '噶点来创建新的文章。';

	//Live Search
	$moreResultsText	    = "查看更多结果";



}
if ($region == "co.jp") {

	$txt_hwprofile          = " 私のハードウェアプロファイル";

	//header.php
	$txt_reg   = "登録";
	$txt_login = "ログイン";

	$txt_user_rating_for_setup = " 「ユーザーは、このハードウェアのセットアップについては、以下の評価を与えました：";


        //footer.php
	$txt_follow_twitter = "フォローたちをTwitterで";
	$txt_mail_us = "メールを送信";
	$txt_flattr_us = "Flattr を通じて寄付";
        $txt_designed = "によって設計された";
        $txt_for_mobile = "タブレットとスマートフォン向けに最適化";
	$txt_footer_general="一般 ";
	$txt_footer_contact="連絡 ";
	$txt_footer_faq="よくある質問 ";
	$txt_footer_contributors="貢献者 ";
	$txt_footer_support="私たちをサポートしている ";
	$txt_footer_problem="問題を報告する ";
	$txt_footer_donate="寄付する ";
	$txt_footer_suppliers="メーカーや広告主 ";
	$txt_footer_hwsuppliers="ハードウェア·サプライヤ ";
	$txt_footer_manufacturers="ハードウェアの製造元 ";
	$txt_footer_advertisers="広告主";



	//searchform.php
	$txt_search 		= '&nbsp;&nbsp;探究&nbsp;&nbsp;'.'&nbsp;<i class="icon-arrow-right icon-button"></i>';


	//registration-form-widget
	$txt_reg_user = "ユーザ名";
	$txt_reg_mail = "メール";

//amazon-product-in-a-post.php
$txt_compat  	= "Linuxの互換性";
$txt_Compat  	= "Linuxの互換性の検討";
$txt_with       = "とともに";
$txt_rating     = "考課";
$txt_Rating     = $txt_rating;
$txt_ratings    = "考課";
$txt_price      = "考課";
$txt_out_of_stock = "在庫切れ";
$txt_button     = "/wp-uploads/amazon-button.png";
$txt_button_width = 'width="185"';
$siteurl 	= "linux-hardware-guide.com";
$txt_pricetrend = "価格動向";
$txt_shipping	= "郵送料なしで";
$txt_on_stock   = "株式の";
$txt_category   = "カテゴリ";
$txt_date       = "カテゴリ";
$txt_updated    = "更新";
$txt_tooltip2   = "
Amazonが提示された価格は、製品とAmazonマーケットプレイスのサプライヤによっては原因である可能性があり輸送費は含まれていません。";
$txt_buy_from   = "から購入する";
$txt_search_at  = "検索で";
$txt_preorder   = "区で";
$txt_not_avail_at = "で、現時点ではご利用いただけません";
$txt_never_avail= "いないのカタログに";
$txt_amz_getfrom= 'あなたはからこれを取得する必要があります <a href="http://amazon.com/">Amazon.com</a>';
$txt_amz_asin   = "Amazon Product ASIN (ISBN-10)";
//$txt_amz_title  = "Amazon Product Information";
$txt_amz_tooltip_not_loggedin = "唯一のLinux互換性は、製品の一般的な品質は、このページ上に存在しないと評価されています。あなたがLinuxで、本製品を使用する場合は、それを評価し、他のLinuxユーザをサポートするために、このページ（下）のコメント領域であなたの経験を共有してください。";

//wp-one-post-widget
$txt_summary    	= "要約";
$txt_supplier  		= "サプライヤー";
$txt_cheapest_supplier 	= "最低価格のサプライヤ";
$txt_rating     	= "評価";
$txt_ratings    	= "評価";
$txt_select     	= "Select country &amp; currency";
$txt_sim_tags   	= "似たタグ";
$txt_combine_tags 	= "組み合わせる";
$txt_wpop_register   	= "ハードウェアプロファイルに追加:";
$txt_register_long	= "そして新しいドライバが存在する場合に通知または得るのを助けるために同じハードウェアの検索とLinuxユーザ";
$txt_send               = "加える";
$txt_manage_subscr      = "サブスクリプションを管理";
$txt_not_avail          = "サブスクリプションを管理";
$txt_rate_yourself      = "レートウェア";
$txt_opw_num_ratings    = "数件の ";
$txt_opw_average_rating = "平均評価 ";
$txt_opw_rating_overview= "評価の概要 ";
$txt_opw_hardware       = "ハードウェア";
$txt_opw_registered	= "登録Linuxユーザ";


//comments-template.php
$txt_privacy_stat	= "メールアドレスが公開されることはありません。.";
$txt_compat_rat         = "レートLinux互換";
$txt_use_tags           = '以下を使用 <abbr title="HyperText Markup Language">HTML</abbr> タグと属性';
$txt_logged_in_as       = "としてログイン";
$txt_logout             = "ログアウト";
$txt_send_comment       = "コメントを送信";
$txt_cancel_reply       = '返信をキャンセル';
$txt_reply_to           = 'に返信';
$txt_comments           = "注釈";
$txt_comment            = "";
$txt_to_comment         = "注釈";
$txt_website            = "ウェブサイト";
$txt_reply              = "返信";
$txt_says               = "返信";
$txt_product_rating     = "製品の評価";
$txt_comments_new_discussion  = "新しいクチコミを作成する";

	//avatar tooltip
	$txt_avt_reguser	= "登録ユーザー ";
	$txt_avt_karmapoints	= "カルマポイント ";
	$txt_avt_rank		= "ランキング ";
	$txt_avt_click		= "ユーザーのプロファイルにアバターをクリック";




	//attachment
	$txt_att_filetypes	= "許可されているファイルの種類";
	$txt_att_maxsize  	= "最大ファイル·サイズ ";
	$txt_att_attachment  	= "アタッチメント ";

//archive.php
$txt_arch_related_cat  = "関連カテゴリ";
$txt_arch_category     = "カテゴリ:";
$txt_arch_search_terms = "検索用語";
$txt_arch_and          = "及び";
$txt_arch_combination  = "及び:";
$txt_arch_display_opt  = "表示オプション";
$txt_arch_sort_price   = "表示オプション";
$txt_arch_sort_rating  = "表示オプション";
$txt_arch_show_avail   = "のみが表示可能な場合";
$txt_arch_show_avail   = "のみが表示可能な場合";
$txt_arch_stars        = "星";
$txt_arch_home	       = "ホームページ";
	$txt_filter_by_rating  = "評価でフィルタ";
	$txt_filter_by_tags    = "検索用語によってフィルタリングする";
	$txt_sorting_options   = "並べ替えオプション";


	//scan overview table
	$txt_scan_distribution  = "分布";
	$txt_scan_kernel        = "カーネルのバージョン";
	$txt_scan_scandate      = "スキャン日付";
	$txt_scan_result	= "結果";
	$txt_scan_results	= "結果";
	$txt_scan_title		= "特定されたハードウェア構成";
	$txt_scan_text          = 'このハードウェア・コンポーネントは、次のシステム構成の下でLinuxユーザーによって使用されました。これらの結果は、私たちによって回収し <a href="./add-hardware">ツールをスキャン </a>:';



//comments.php
$txt_no_respo   	= "いいえ応答しない";
$txt_one_resp  		= "1つの応答";
$txt_responses          = "つの応答";
$txt_to  		= "に対して";
$txt_comments   	= "注釈";
$txt_comments_intro     = "記事の訂正を提出するだけでなく、の<tt> lspci -nnv, lsusb, lshw, dmesg</tt>などの関連抜粋するコメントセクションを使用してください
さらに、このハードウェアコンポーネントまたはこのハードウェアの他の所有者からの構成のヘルプを検索すると経験の交換のためのセクションを使用します。
 	<br />&nbsp;<br />";

	// Cubepoints
	$txt_cp_karma		= "カルマ ";
	$txt_cp_points		= "ポイント ";
	$txt_cp_donates_to	= "に寄付 ";
	$txt_cp_language	= "言語 ";
	$txt_cp_longtext	= 'Linuxベースのハードウェア·ガイドは、Linuxコミュニティのへのバック毎月バナー広告収入を寄付しています。
  登録ユーザは、誰に寄付しようとしているに
        (<a href="./donations">詳細を参照</a>) 、それらのカルマポイントと投票。進行中の月の寄付合計';
	$txt_cp_title		= "ユーザーのランキングとその寄付 ";
	$txt_cp_quarterly       = "四半期ごとのポイント";
	$txt_cp_totalkarma      = "総カルマ";
	$txt_cp_details         = "細部";


	//related posts thumbnails
	$txt_rpt_creation   ="作成";
	$txt_rpt_update     ="更新した";
	$txt_rpt_title="<h2>同じようなLinuxのハードウェア</h2>";
	$txt_rpt_sub_by="から提出された";

	//bwp-comments
	$txt_bwp_commenton     = "にコメント";

$txt_vote = "投票";
$txt_votes = "投票";
$txt_average = "平均";
$txt_out_of = "最大のうち";

        //Twitter widget
	$txt_twt_title		= '<i class="icon-twitter menucolor"></i> '."ニュース通じてTwitter";

	//mobile theme
	$txt_mobile_search	    = "サーチハードウェア";
	$txt_mobile_show_all        = "すべての検索結果が示されている";
	$txt_mobile_fullsite        = "フルバージョンを表示";

	//custom recent post
	$txt_crp_title		    = "最近追加されたLinuxのハードウェア";

	//subscribe
	$txt_subscr_component_added = "コンポーネントは、プロファイルに追加 ";
	$txt_subscr_was_added       = "次のハードウェアコンポーネントは、ハードウェアプロファイルに追加されました ";
	$txt_subscr_return 	    = "戻る ";
	$txt_subscr_to_hw	    = "ハードウェア記述 ";
	$txt_subscr_or_manage	    = "以下、ハードウェアプロファイル（個人情報）の管理 ";
	$txt_subscr_answer 	    = "メールごとに答えを受け取る";
	$txt_subscr_regist_date     = "登録日 ";
	$txt_subscr_modus	    = "ステータス ";
	$txt_subscr_article	    = "ハードウェアの概要 ";
	$txt_subscr_select_all	    = "すべてを選択 ";
	$txt_subscr_select_inv	    = "反転選択 ";
	$txt_subscr_action	    = "アクション ";
	$txt_subscr_delete	    = "エントリーをキャンセル ";
	$txt_subscr_suspend	    = "エントリーを無効化する ";
	$txt_subscr_reply_only	    = "私のコメントに返信する ";
	$txt_subscr_activate	    = "最新ニュースを入手 ";
	$txt_subscr_update          = "アップデート ";
	$txt_subscr_active          = $txt_subscr_activate;
	$txt_subscr_inactive        = "非アクティブ化 ";
	$txt_subscr_manage_hw       = "お使いのハードウェアプロファイル（個人情報）の管理 ";
	$txt_subscr_pub_hw_prof     = "公共ハードウェアプロファイル ";
	$txt_subscr_name 	    = "名前 ";
	$txt_subscr_email	    = "Eメール ";
	$txt_subscr_more	    = "もっと";
        $txt_hwprof_of              = "の公共ハードウェアプロファイル";

	//Live Search
	$moreResultsText	    = "もっと結果を見ます";


}

if ($region == "co.uk") {

	$txt_mail_URL = "http://www.linux-hardware-guide.com/uk/login";
	//$txt_mail_welcome = "Welcome to Linux-Hardware-Guide.com:\r\nPlease find below your username and your automatically created password.\r\n";
	$txt_mail_end = "You can login at http://www.linux-hardware-guide.com/uk/login and change your settings, e.g. password and avatar.\r\nBest regards,\rthe Linux-Hardware-Guide Team";

}

if ($region == "ca") {

	$txt_mail_URL = "http://www.linux-hardware-guide.com/ca/login";
	//$txt_mail_welcome = "Welcome to Linux-Hardware-Guide.com:\r\nPlease find below your username and your automatically created password.\r\n";
	$txt_mail_end = "You can login at http://www.linux-hardware-guide.com/ca/login and change your settings, e.g. password and avatar.\r\nBest regards,\rthe Linux-Hardware-Guide Team";

}

if ($region == "in") {

	$txt_mail_URL = "http://www.linux-hardware-guide.com/in/login";
	//$txt_mail_welcome = "Welcome to Linux-Hardware-Guide.com:\r\nPlease find below your username and your automatically created password.\r\n";
	$txt_mail_end = "You can login at http://www.linux-hardware-guide.com/in/login and change your settings, e.g. password and avatar.\r\nBest regards,\rthe Linux-Hardware-Guide Team";

}

?>