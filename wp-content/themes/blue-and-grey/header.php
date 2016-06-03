<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes('xhtml'); ?>>
<head profile="http://gmpg.org/xfn/11">
<link rel="icon" type="image/png" href="/favicon.png" />

<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<?php
//Twitter cards

global $lang;
global $region;

if ( is_single() ) {
$twitter_user = "@LinuxHardware";
$tw_domain = "com";
if ($lang == "de") $twitter_user = "@LinuxHWGuide";
if ($lang == "de") $tw_domain = "de";

$postID = get_the_ID();
$title = translate_title( get_the_title( $postID ) );

$post_object = get_post( $postID );
$content = substr( sanitize_text_field( $post_object->post_content ) , 0 , 250);
$content = str_replace ("\"","",$content);
$content = strip_shortcodes($content);

//$content = get_the_content ( $postID );
$thumb = wp_get_attachment_url( get_post_thumbnail_id($postID) );
//echo "L: $lang";
//echo "R: $region";

$twitter_image = "/".catch_that_image();
//echo "IMG: ".$twitter_image;
$twitter_image = str_replace("-130x130", "", $twitter_image);
$twitter_image = str_replace("http://www.linux-hardware-guide.de", "", $twitter_image);
$twitter_image = str_replace("http://www.linux-hardware-guide.com", "", $twitter_image);
$twitter_image = str_replace("/images/default.jpg", "", $twitter_image);
$twitter_image = str_replace("//", "/", $twitter_image);
//check if file exists
$upload_dir = wp_upload_dir();
$testfile = $upload_dir['basedir'].str_replace("wp-uploads","",$twitter_image);
$testfile = str_replace ("//","/",$testfile);
//echo "TEST: $testfile";
if ( !file_exists( $testfile ) ) $twitter_image="";

//echo "II: ".$upload_dir['basedir'].$twitter_image;
//wp_upload_dir().$twitter_image;
//echo "IMG: ".$twitter_image;


//if ( ($lang == "de") or ($region == "com") )
//echo "TI: (".$twitter_image.")";


if ( ($twitter_image != "") and ($twitter_image != "/") ) {
echo '
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="'.$twitter_user.'" />
<meta name="twitter:title" content="'.$title.'" />
<meta name="twitter:description" content="'.$content.'" />
<meta name="twitter:creator" content="'.$twitter_user.'" />
<meta name="twitter:image" content="http://www.linux-hardware-guide.'.$tw_domain.$twitter_image.'" />
<meta name="twitter:domain" content="linux-hardware-guide.'.$tw_domain.'" />

<meta property="og:title" content="'.$title.'" />
<meta property="og:image" content="http://www.linux-hardware-guide.'.$tw_domain.$twitter_image.'" />
<meta property="og:description" content="'.$content.'" />
';
}else{
/*
echo "TID: ".get_post_thumbnail_id($postID);
echo "URL: ". wp_get_attachment_url( get_post_thumbnail_id($postID) );
echo "thumb:". $thumb;
*/
echo '
<meta name="twitter:card" content="summary" />
<meta name="twitter:site" content="'.$twitter_user.'" />
<meta name="twitter:title" content="'.$title.'" />
<meta name="twitter:description" content="'.$content.'" />
<meta name="twitter:creator" content="'.$twitter_user.'" />
<meta name="twitter:image" content="http://www.linux-hardware-guide.'.$tw_domain.$thumb.'" />
<meta name="twitter:domain" content="linux-hardware-guide.'.$tw_domain.'" />

<meta property="og:title" content="'.$title.'" />
<meta property="og:image" content="http://www.linux-hardware-guide.'.$tw_domain.$thumb.'" />
<meta property="og:description" content="'.$content.'" />
';
}

}

# Opengraph common
echo '<meta property="og:type" content="website" />
<meta property="og:site_name" content="Linux Hardware Guide" />
';

if ($region == "de")
echo '
<meta property="og:locale" content="de_DE" />
<meta property="og:locale:alternate" content="fr_FR" />
<meta property="og:locale:alternate" content="es_ES" />
<meta property="og:locale:alternate" content="it_IT" />
<meta property="og:locale:alternate" content="ja_JA" />
<meta property="og:locale:alternate" content="nl_NL" />
<meta property="og:locale:alternate" content="en_US" />
<meta property="og:locale:alternate" content="en_GB" />
<meta property="og:locale:alternate" content="en_CA" />
<meta property="og:locale:alternate" content="en_IN" />
';


if ($region != "de"){
#echo " ------------------- REG: $region LG: $lang ";

        if ($region == "com")    echo '<meta property="og:locale" content="en_US" />'."\n";
        if ($region == "ca")     echo '<meta property="og:locale" content="en_CA" />'."\n";
        if ($region == "co.uk")  echo '<meta property="og:locale" content="en_GB" />'."\n";
        if ($region == "fr")     echo '<meta property="og:locale" content="fr_FR" />'."\n";
        if ($region == "es")     echo '<meta property="og:locale" content="es_ES" />'."\n";
        if ($region == "it")     echo '<meta property="og:locale" content="it_IT" />'."\n";
        if ($region == "nl")     echo '<meta property="og:locale" content="nl_NL" />'."\n";
        if ($region == "in")     echo '<meta property="og:locale" content="en_IN" />'."\n";
        if ($region == "co.jp")  echo '<meta property="og:locale" content="ja_JA" />'."\n";
        if ($region == "cn")     echo '<meta property="og:locale" content="cn_CN" />'."\n";

	if ($region != "com")    echo '<meta property="og:locale:alternate" content="en_EN" />'."\n";
	if ($region != "ca")     echo '<meta property="og:locale:alternate" content="en_CA" />'."\n";
	if ($region != "co.uk")  echo '<meta property="og:locale:alternate" content="en_GB" />'."\n";
	if ($region != "fr")     echo '<meta property="og:locale:alternate" content="fr_FR" />'."\n";
	if ($region != "es")     echo '<meta property="og:locale:alternate" content="es_ES" />'."\n";
	if ($region != "it")     echo '<meta property="og:locale:alternate" content="it_IT" />'."\n";
	if ($region != "nl")     echo '<meta property="og:locale:alternate" content="nl_NL" />'."\n";
	if ($region != "in")     echo '<meta property="og:locale:alternate" content="en_IN" />'."\n";
	if ($region != "co.jp")  echo '<meta property="og:locale:alternate" content="ja_JA" />'."\n";
	if ($region != "cn")     echo '<meta property="og:locale:alternate" content="cn_CN" />'."\n";


}




//enter list of translation for german page
if ($region == "de") country_list(1);

//list of keyword tags

$the_cat = get_the_category();
$category_name = $the_cat[0]->cat_name;
$category_name2 = $the_cat[1]->cat_name;

if ( $category_name  != "" ) $lhg_keyword_list .= ", ".$category_name;
if ( $category_name2 != "" ) $lhg_keyword_list .= ", ".$category_name2;

$posttags = get_the_tags();
$count=0;
if ($posttags) {
	foreach($posttags as $tag) {
		$count++;
		$lhg_keyword_list .=  ', '.$tag->name;
		if( $count >6 ) break;
	}
}
//echo $lhg_keyword_list;
echo '
<meta name="keywords" content="Linux, Hardware'.$lhg_keyword_list.'" />';
?>

<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

function country_list($metalist) {
	global $lang;
        global $region;
        $postID = get_the_ID();


        //echo "PID: $postID";




        list($null1,$null2,$null3,$posturl)=explode("/",get_permalink());
        list($null1,$null2,$null3,$null4,$posturl2)=explode("/",get_permalink());

        //We are at the main page (ID==1821)
        if ($postID == "1821") $posturl= "";
        if ($postID == "1821") $posturl2= "";

        list($posturl1,$posturl2,$posturl3,$posturl4)=explode("/",$_SERVER["REQUEST_URI"]);
        //echo "<br>PURL: 1: $posturl1 / 2: $posturl2 / $posturl3 / $posturl4 <br>";

        if ($posturl2 != "") $posturl = $posturl2; //we are in a qtranslate subfolder!


        //check for login page
        //list($posturl1,$posturl2,$posturl3,$posturl4)=explode("/",$_SERVER["REQUEST_URI"]);
        //echo "<br>PURL: $posturl1 / $posturl2 / $posturl3 / $posturl4 <br>";

        if (substr($posturl2,0,6) == "login?") $posturl = $posturl2;
        if (substr($posturl1,0,6) == "login?") $posturl = $posturl1;


        if (is_search()) {
        	//echo "Search".$_SERVER["REQUEST_URI"];
                list($posturl1,$posturl2,$posturl3,$posturl4)=explode("/",$_SERVER["REQUEST_URI"]);

        	//list($null1,$null2,$null3,$null4,$posturl2)=explode("/",get_category_link($cid));

                //echo "<br>PURL: $posturl1 / $posturl2 / $posturl3 / $posturl4 <br>";

		$posturl  = $posturl2;

                //echo "PURL: $posturl";
                //if (defined($posturl2)) {
        	//	$posturl  = $posturl2;
	       	//}
	}

        if (is_404() ) {
        	$posturl= "";
        	$posturl2= "";
	}


        if (is_archive() or is_tag() ) {
                //archives need special handling, otherwise first product will be used
	        //echo "Archiv!".get_permalink()."<br>".$postID;
                //echo "<br>".get_permalink($postID);
                //$cid=get_the_category_ID();

		//echo get_the_category( $postID );

                //ob_start();
                //the_category_ID();
                //$cid = ob_get_clean();
                //echo "CID: $cid[1]";
                //echo "URL: ".$_SERVER["REQUEST_URI"];
                //echo "<br>CID:".the_category_ID();
                //echo "link: ".get_category_link($cid);

                list($posturl1,$posturl2,$posturl3,$posturl4)=explode("/",$_SERVER["REQUEST_URI"]);
        	//list($null1,$null2,$null3,$null4,$posturl2)=explode("/",get_category_link($cid));

                //echo "<br>PURL: $posturl1 / $posturl2 / $posturl3 / $posturl4 <br>";


                if ( ($posturl1 == "category" ) or ($posturl1 == "tag") )
                {
                   $posturl = $posturl1;
                   if ($posturl2 != "") $posturl .= "/$posturl2";
	        }

                if ( ($posturl2 == "category") or ($posturl2 == "tag") )$posturl = "$posturl2";

                if ($posturl3 != "") $posturl .= "/$posturl3";
                if ($posturl4 != "") $posturl .= "/$posturl4";

                //echo "AA: $posturl1";
                //echo "AA: $posturl2";



        }

        //echo "PURL: $posturl";

        $posturlde  = $posturl;
        $posturlcom = $posturl;

        #echo "PU:".$posturlcom;

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
        $posturlde = str_replace("?lang=nl","",$posturlde);
        $posturlde = str_replace("?lang=br","",$posturlde);

        $posturlcom = str_replace("?lang=jp","",$posturlcom);
        $posturlcom = str_replace("?lang=it","",$posturlcom);
        $posturlcom = str_replace("?lang=es","",$posturlcom);
        $posturlcom = str_replace("?lang=uk","",$posturlcom);
        $posturlcom = str_replace("?lang=ca","",$posturlcom);
        $posturlcom = str_replace("?lang=in","",$posturlcom);
        $posturlcom = str_replace("?lang=fr","",$posturlcom);
        $posturlcom = str_replace("?lang=cn","",$posturlcom);
        $posturlcom = str_replace("?lang=en","",$posturlcom);
        $posturlcom = str_replace("?lang=nl","",$posturlcom);
        $posturlcom = str_replace("?lang=br","",$posturlcom);


        if ($posturlcom == "hardware-profile") {
        	$url     = getCurrentUrl();
                $pieces  = parse_url($url);
                $urlpath = $pieces['path'];

		$hwprofpos=strpos($urlpath,"hardware-profile/user");
		$hwprofposg=strpos($urlpath,"hardware-profile/guser");
                # if we have a public page this fails and we need the following value
                $hwprofposs=strpos($urlpath,"hardware-profile/system");

                #print "$urlpath - $hwprofpos - $hwprofposs<br>";

                if ($hwprofposs != "") $rurl = substr($urlpath,$hwprofposs);

                //echo "URL: $posturlcom";
                //echo "<br>URL2: $rurl";

                $posturlcom = "$rurl";
                $posturlde  = "$rurl";

                if ($hwprofpos != "") {
                        # translate public user profile links to guid links to make them available on other servers
                	$rurl = substr($urlpath,$hwprofpos);
                        $hwprofpos   = strpos($urlpath,"/hardware-profile/user");
			$uid = (int)substr($urlpath,$hwprofpos+22);
			$guid = lhg_get_guid_from_uid( $uid );

                        #locally linking to standard user profile, transversally linking to guid
	                if ($lang != "de") $posturlde  = "/hardware-profile/guser".$guid;
	                if ($lang != "de") $posturlcom  = "/hardware-profile/user".$uid;
	                if ($lang == "de") $posturlcom  = "/hardware-profile/guser".$guid;
	                if ($lang == "de") $posturlde  = "/hardware-profile/user".$uid;

		}

                if ($hwprofposg != "") {
                        # translate public user profile links to guid links to make them available on other servers
                	$rurl = substr($urlpath,$hwprofpos);
                        $hwprofpos   = strpos($urlpath,"/hardware-profile/guser");
			$guid = (int)substr($urlpath,$hwprofposg+22);
			#$guid = lhg_get_guid_from_uid( $uid );

                        #locally linking to standard user profile, transversally linking to guid
	        	$rurl = "/hardware-profile/guser".$guid;
	                #if ($lang != "de") $posturlcom  = "/hardware-profile/user".$uid;
	                #if ($lang == "de") $posturlcom  = "/hardware-profile/guser".$guid;
	                #if ($lang == "de") $posturlde  = "/hardware-profile/user".$uid;

	                $posturlcom = "$rurl";
        	        $posturlde  = "$rurl";


		}


	}


        //translate tags
        if ( is_tag() ) {

                if ( ($region == "de") and ( ($posturl1 == "tag") or ($posturl2 == "tag") ) ) {
                        //translated tags to be corrected
                        $posturlcom=translate_tag($posturl);
        	}

                if ( ($region != "de") and ( ($posturl1 == "tag") or ($posturl2 == "tag") ) ){
                        //translated tags to be corrected
                        $posturlde=translate_tag($posturl);
        	}

        }

        //translate categories
        if (is_archive()  ) {

                if ( ($region == "de") and ( ($posturl1 == "category") or ($posturl2 == "category") ) ) {
                        //translated tags to be corrected
                        $posturlcom=translate_category($posturl);
        	}

                if ( ($region != "de") and ( ($posturl1 == "category") or ($posturl2 == "category") ) ){
                        //translated tags to be corrected
                        $posturlde=translate_category($posturl);
        	}

	$posturlde = str_replace("/page/2","",$posturlde);
	$posturlde = str_replace("/page/3","",$posturlde);
	$posturlde = str_replace("/page","",$posturlde);


	$posturlcom = str_replace("/page/2","",$posturlcom);
	$posturlcom = str_replace("/page/3","",$posturlcom);
	$posturlcom = str_replace("/page","",$posturlcom);

        }

     //echo "PU2: $posturlcom";

        if ( (!is_search()) and
          (!($postID == "1821")) and
          (!is_archive())
          )
        {

                //echo " ---------------- PU: $posturlcom  ---------";

                //check if postid is already in DB
	        lhg_check_permalink($postID);

                if ( is_single() ) {
                        //extract URL (w/o domain name) from database
                	$comURL = lhg_URL_chomp( lhg_get_com_post_URL($postID) );
			$deURL  = lhg_URL_chomp( lhg_get_de_post_URL($postID) );

			//$comURL = get_post_meta($postID,'COM_URL',true);
			//$deURL  = get_post_meta($postID,'DE_URL',true);

                        #echo "---------------------- CURL: $comURL";
                        #echo "DURL: $deURL";
	        }else{
                	$comURL = get_post_meta($postID,'COM_URL',true);
			$deURL  = get_post_meta($postID,'DE_URL',true);
                }


               /*
               if ($lang == "en") {
	                $postid_de = lhg_get_postid_de_from_com($postID);
                        #echo "PID_de: $postid_de";


                        #echo "PL: ";
	        }
               */
                //echo "---------------------- PURL-A: $posturlcom";

                // set default
                if ($comURL == "") $comURL = $posturlcom;
                if ($deURL == "") $deURL = $posturlde;

                if (substr($comURL,0,1)=="/") $comURL = substr($comURL,1);
                if (substr($deURL,0,1)=="/") $deURL = substr($deURL,1);

        	#if ($comURL != "") $posturlcom = $comURL;
                $posturlcom = $comURL;

                #if ($deURL  != "") $posturlde = $deURL;
                $posturlde = $deURL;

                //echo "---------------------- PURL-c: $posturlcom";


                //translation still missing -> redirect to main page
                //if ( ($comURL == "") and ($postID > 2599) and ($region == "de")) $posturlcom = "";
                //if ( ($deURL == "")  and ($postID > 2599) and ($region != "de")) $posturlde = "";
        }

//echo "PURL: $posturlcom";
//echo "PURL: $posturlde";

        //echo "LANG: $lang";
        //echo "Reg: $region";
	 if ($region == "de") {
         //echo "Test $posturlcom -- ";
                $URLC="http://www.linux-hardware-guide.com";
                $URLD="";
        }

        if ($region != "de") {
                $URLC="";
                $URLD="http://www.linux-hardware-guide.de";
        }
        //$URLC="http://192.168.3.113"; //Debug


if ( ($metalist == 1)  ) {

if ($region == "de") echo '<meta http-equiv="Content-Language" content="de"/>';
if ($region == "de") $URLD = "http://www.linux-hardware-guide.de";

# store url to be available globally (e.g. by wp-one-post-widget)
global $posturlcom_glob;
global $posturlde_glob;
$posturlcom_glob = $posturlcom;
$posturlde_glob = $posturlde;


        //create header meta-list
        //by qtranslate in "com" or here in "de" cases
        $URLC="http://www.linux-hardware-guide.com";

	echo '
<link hreflang="EN-GB"   href="'.$URLC.'/uk/'.$posturlcom.'" rel="alternate" />
<link hreflang="ZH-Hans" href="'.$URLC.'/zh/'.$posturlcom.'" rel="alternate" />
<link hreflang="FR"      href="'.$URLC.'/fr/'.$posturlcom.'" rel="alternate" />
<link hreflang="IT"      href="'.$URLC.'/it/'.$posturlcom.'" rel="alternate" />
<link hreflang="JA"      href="'.$URLC.'/ja/'.$posturlcom.'" rel="alternate" />
<link hreflang="ES"      href="'.$URLC.'/es/'.$posturlcom.'" rel="alternate" />
<link hreflang="NL"      href="'.$URLC.'/nl/'.$posturlcom.'" rel="alternate" />
<link hreflang="EN-CA"   href="'.$URLC.'/ca/'.$posturlcom.'" rel="alternate" />
<link hreflang="EN-IN"   href="'.$URLC.'/in/'.$posturlcom.'" rel="alternate" />
<link hreflang="EN"      href="'.$URLC.'/'.$posturlcom.'"    rel="alternate" />
';

if ($region != "de") echo '<link hreflang="DE"      href="'.$URLD.'/'.$posturlde.'"     rel="alternate" />';

// <link hreflang="PT-BR"   href="'.$URLC.'/br/'.$posturlcom.'" rel="alternate" />


        }else{

        echo '<span class="flaglist">
        <a href="'.$URLD.'/'.$posturlde.'">
        <img src="/wp-content/plugins/qtranslate/flags/de.png" title="Region: Germany
Currency: &euro;" alt="Germany" /></a>';

        echo '<a href="'.$URLC.'/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/us.png"
title="Region: USA
Currency: &#36;" alt="USA" /></a>';

        echo '<a href="'.$URLC.'/ca/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/ca.png"
title="Region: Canada
Currency: CND&#36;"  alt="Canada" /></a>';

        echo '<a href="'.$URLC.'/uk/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/uk.png"
title="Region: United Kingdom
Currency: &pound;"  alt="UK" /></a>';

        echo '<a href="'.$URLC.'/fr/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/fr.png"
title="Region: France
Currency: &euro;"  alt="France" /></a>';

        echo '<a href="'.$URLC.'/es/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/es.png"
title="Region: Espana
Currency: &euro;"  alt="Espana" /></a>';

        echo '<a href="'.$URLC.'/it/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/it.png"
title="Region: Italia
Currency: &euro;"  alt="Italia" /></a>';

        echo '<a href="'.$URLC.'/nl/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/nl.png"
title="Region: Nederlands
Currency: &euro;"  alt="Nederlands" /></a>';

/*
echo '<a href="'.$URLC.'/br/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/br.png"
title="Region: Brasil
Currency: R&dollar;"></a>';
*/
        echo '<a href="'.$URLC.'/in/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/in.png"
title="Region: India
Currency: &#8377;"  alt="India" /></a>';

        echo '<a href="'.$URLC.'/ja/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/jp.png"
title="Region: Japan
Currency: &yen;"  alt="Japan" /></a>';

        echo '<a href="'.$URLC.'/zh/'.$posturlcom.'"><img src="/wp-content/plugins/qtranslate/flags/cn.png"
title="Region: China
Currency: RMB"  alt="China" /></a>

<br clear="all"/>';
        echo '</span>';
}


}

        ob_start();
	wp_title( '|', true, 'right' );
        $s = ob_get_contents();
	ob_end_clean();
        echo translate_title($s);

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s'), max( $paged, $page ) );

        global $region;
        //get_lang();
        if ($region =="fr") $loc="France";
        if ($region =="es") $loc="España";
        if ($region =="it") $loc="Italia";
        if ($region =="in") $loc="India";
        if ($region =="nl") $loc="Nederlands";
        if ($region =="ca") $loc="Canada";
        if ($region =="co.uk") $loc="United Kingdom";
        if ($region =="com.br") $loc="Brasil";
        if ($region =="co.jp") $loc="日本";
        if ($region =="cn") $loc="中国";

        if ($loc != "") echo " ($loc)";

	?></title>
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<!--[if lt ie 8]><link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/ie7.css" type="text/css" /><![endif]-->
<!--[if lt ie 7]><link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/ie6.css" type="text/css" /><![endif]-->
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); ?>
<?php
wp_head();
?>


<?php
//wp_head();
?>

</head>
<body <?php body_class(); ?>>

        <?php country_list(0); ?>

<div class="header">


	<p class="logo">
          <a href="/"><img src="/wp-uploads/2014/11/LHG_Logo_circle-300x290.png" id="logoimg" alt="LHG Logo" /></a>
        <a href="<?php echo bloginfo('url');


$val="";
if (function_exists(qtrans_getLanguage)) $val = "/".qtrans_getLanguage();

        ?>/"><!-- img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt="<?php bloginfo('name'); ?>" /-->
        <?php bloginfo('name');

        //language info
        //echo "VAL: $val";
        if ( ($val != "/us") and ($val != "") ) echo $val;
        echo '</a>';
        $fval=$val;
        if ($val == "/zh") $fval = "/cn";
        if ($val == "/ja") $fval = "/jp";

        //echo "$region";


        ?>
        <?php
        bloginfo('description');

        ?>


<?php
global $lang;
//echo "LANG: $lang";

if ($lang =="en")
if (!is_user_logged_in() ) {


# top banner disabled. disturbed layout too much!
if (1==0) echo '
           <span class="topbanner">
<script async src="http://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Linux-Hardware-Guide.com Top -->
<ins class="adsbygoogle"
     style="display:inline-block;width:234px;height:60px"
     data-ad-client="ca-pub-5383503261197603"
     data-ad-slot="8615507277"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
        </span>';
}

        if ($loc != "") echo '<div class="subtitle">
        <img src="/wp-content/plugins/qtranslate/flags'.$fval.'.png" alt="Logo" />&nbsp;'.$loc.'</div>';


?>
        </p>


	<?php get_search_form(); ?>

	<div class="header-menu">
		<?php wp_nav_menu( array('fallback_cb' => 'blue_and_grey_page_menu', 'menu' => 'primary', 'depth' => '4', 'theme_location' => 'primary', 'link_before' => '<span><span>', 'link_after' => '</span></span>') ); ?>
	</div>


<div class="Impressum">
<?php

global $lang;
global $txt_reg;
global $txt_login;


if ($lang == "de") echo '<a href="/impressum">Impressum</a>';
if ( ($lang == "de") && !is_user_logged_in() ) echo ' | ';

$val="";
if (function_exists(qtrans_getLanguage)) $val = "/".qtrans_getLanguage();
if ($val == "/us") $val = "";
if ( !is_user_logged_in() ) echo '<a href="'.$val.'/login?action=register">'.$txt_reg.'</a> | <a href="'.$val.'/login">'.$txt_login.'</a>&nbsp;';


?>
</div>

</div>

<?php

        //Debug code
        // Get current user object
	#$current_user = wp_get_current_user();
        // Get the last login time of the user
	#$last_login_time = get_user_meta ( $current_user->ID, 'last_login', true );
	#$this_login_time = get_user_meta ( $current_user->ID, 'this_login', true );
        #echo "This li: $this_login_time<br>";
        #echo "Last li: $last_login_time<br>";

	#$last_login_comment = get_user_meta ( $current_user->ID, 'last_login_last_comment', true );
	#$this_login_comment = get_user_meta ( $current_user->ID, 'this_login_last_comment', true );
        #echo "This li com: $this_login_comment<br>";
        #echo "Last li com: $last_login_comment";



if ( $debug == 1)
if ( get_current_user_id() == 1)
if ( is_user_logged_in() )
{
echo "DOM: ".$_SERVER['HTTP_HOST'];
echo "<br>DOM2: ".$_SERVER['SERVER_NAME'];
echo "<br>DOM3: ".$_SERVER["HTTP_X_FORWARDED_FOR"];
echo "<br>DOM4: ".$_SERVER["HTTP_VIA"];
echo "<br>DOM5: ".$_SERVER['HTTP_REFERER'];
echo "<br>DOM6: ".$_SERVER['REQUEST_URI'];
echo "<br>DOM7: ".$_SERVER['HTTP_X_FORWARDED_HOST'];
}

?>





<?php
//check language setting. Propose switch

if (1==0)
$hval = get_hide();

//echo "hide: $hval";
if (1==0)
if ($hval != 1) //never closed notice box
if ($lang != "de") { //switch proposal only on english page
  $skip=0;
  $cval = read_cookie("lang");
  $langIP=get_country();


  if ($langIP != "en") { // US default. Nothing to do
  if ($langIP != "") { //only if we know where to go!
  if ($langIP != "unset") { //only if we know where to go!

//  if ($cval != $langIP) {  // first time visit, only propose in this case
//  if ($cval != "en") { // US market is default, no message



         if (is_page())  {
	 	 if ($_SERVER["REQUEST_URI"]=="/") {
		        $posturlcom = "";
	        	$posturlde = "";
		 }else{
                	$skip=1;
                 }
	 }elseif (is_category())  {
	        $posturlcom = substr($_SERVER["REQUEST_URI"],1);
	        $posturlde = "";
         }else{
        	list($null1,$null2,$null3,$posturl)=explode("/",get_permalink());
	        $posturlde  = $posturl;
	        $posturlcom = $posturl;

		$comURL = get_post_meta($postID,'COM_URL',true);
		$deURL  = get_post_meta($postID,'DE_URL',true);
	        if ($comURL != "") $posturlcom = $comURL;
	        if ($deURL  != "") $posturlde = $deURL;
	}

        //echo "Skip: $skip;";
        if ($skip != 1){
	//global $lang;
	//global $region;
	//$region=get_region();

        //$lang=get_lang();
	print '<p><div class="langwarning">';
        print '<div class="notice">NOTICE:</div>';
        print '<div class="closenotice"><a href="?hide=1">close</a></div>';
        print "We propose to switch your language settings to:<br>";

        //print "";
	//$selected    ='<div class="pricetop" style="border: 1px solid #2b8fc3; background-color: #eee; margin: 3px 3px 3px 3px; padding-top: 3px;">';
        //$selectedEnd ='</div>';


        echo "<b>";
        if ($langIP == "de") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.de/'.$posturlde.'"><img src="/wp-content/plugins/qtranslate/flags/de.png"> Germany (&euro;)</a><br>';
        if ($langIP == "en") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=en"><img src="/wp-content/plugins/qtranslate/flags/us.png"> USA (US $)</a><br>';
        if ($langIP == "ca") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=ca"><img src="/wp-content/plugins/qtranslate/flags/ca.png"> Canada (CDN $)</a><br>';
        if ($langIP == "uk") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=uk"><img src="/wp-content/plugins/qtranslate/flags/uk.png"> United Kingdom (&pound;)</a><br>';
        if ($langIP == "fr") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=fr"><img src="/wp-content/plugins/qtranslate/flags/fr.png"> France (&euro;)</a><br>';
        if ($langIP == "es") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=es"><img src="/wp-content/plugins/qtranslate/flags/es.png"> Espana (&euro;)</a><br>';
        if ($langIP == "it") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=it"><img src="/wp-content/plugins/qtranslate/flags/it.png"> Italia (&euro;)</a><br>';
        if ($langIP == "nl") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=nl"><img src="/wp-content/plugins/qtranslate/flags/nl.png"> Netherlands (&euro;)</a><br>';
        if ($langIP == "in") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=in"><img src="/wp-content/plugins/qtranslate/flags/in.png"> India (&#8377;)</a><br>';
        if ($langIP == "jp") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=jp"><img src="/wp-content/plugins/qtranslate/flags/jp.png"> Japan (&yen;)</a><br>';
        if ($langIP == "cn") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=cn"><img src="/wp-content/plugins/qtranslate/flags/cn.png"> China (RMB)</a><br>';
        echo "</b>";

  	print 'to benefit from local market prices.<hr style="height: 14px; visibility: hidden;">';
        print "Other available markets are:";
        if ($langIP != "en") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=en"><img src="/wp-content/plugins/qtranslate/flags/us.png"></a>';
        if ($langIP != "de") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.de/'.$posturlde.'"><img src="/wp-content/plugins/qtranslate/flags/de.png"></a>';
        if ($langIP != "ca") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=ca"><img src="/wp-content/plugins/qtranslate/flags/ca.png"></a>';
        if ($langIP != "uk") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=uk"><img src="/wp-content/plugins/qtranslate/flags/uk.png"></a>';
        if ($langIP != "fr") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=fr"><img src="/wp-content/plugins/qtranslate/flags/fr.png"></a>';
        if ($langIP != "es") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=es"><img src="/wp-content/plugins/qtranslate/flags/es.png"></a>';
        if ($langIP != "it") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=it"><img src="/wp-content/plugins/qtranslate/flags/it.png"></a>';
        if ($langIP != "nl") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=nl"><img src="/wp-content/plugins/qtranslate/flags/nl.png"></a>';
        if ($langIP != "in") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=in"><img src="/wp-content/plugins/qtranslate/flags/in.png"></a>';
        if ($langIP != "jp") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=jp"><img src="/wp-content/plugins/qtranslate/flags/jp.png"></a>';
        if ($langIP != "cn") echo '&nbsp;&nbsp;<a href="http://www.linux-hardware-guide.com/'.$posturlcom.'?lang=cn"><img src="/wp-content/plugins/qtranslate/flags/cn.png"></a><br>';


        /*
        print "Lang by IP: $langIP<br>";
	print "Lang: $lang<br>";
	print "Region: $region<br>";
	print "Cookie: $cval<br>";
        */
	print '</div>';

 //       }
 //       }
	}
	}
  }
  }

}

 if ( is_single() ) echo '<div itemscope itemtype="http://schema.org/Product">';

?>

<div class="content">
	<div class="main">