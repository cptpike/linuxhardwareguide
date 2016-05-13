<?php
//Debug area

//$ratingid = 219;
//echo lhg_get_post_id_by_rating_id ( $ratingid )

/*
$postid = 2839;
$out = lhg_recalculate_postrating ( $postid ) ;
echo $out;
*/

// end of google keywords - Rich Snippets
if ( is_single() ) echo '</div>';

?>


	<div class="footer">


<?php
global $lang;

global $url_lang;

global $twitAcc;//="LinuxHardware";
global $FL_Thing;//="1446180/Linux-Hardware-Guide-com";
global $FURL;// = "http://www.linux-hardware-guide.com";
global $FMail;// = "linux.hardware.guide@gmail.com";
global $txt_follow_twitter;// = "Follow us on Twitter";
global $txt_mail_us;// = "Mail us";
global $txt_flattr_us;// = "Flattr us";
global $txt_designed;
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

$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";



$actual_link_full = "http://www.".str_replace("m.","",$_SERVER[HTTP_HOST]).$url_lang.$_SERVER[REQUEST_URI];
$actual_link_mobile = "http://m.".str_replace("www.","",$_SERVER[HTTP_HOST]).$url_lang.$mobswitch.$_SERVER[REQUEST_URI];

	$output = '<p>

        <a href="'.$FURL.$url_lang.'"><i class="icon-home icon-large2"></i> <span class="footerlinks">Linux-Hardware-Guide</span></a>

        &nbsp;-&nbsp;

        <a href="https://twitter.com/'.$twitAcc.'"><i class="icon-twitter icon-large2"></i> <span class="footerlinks">'.$txt_follow_twitter.'</span></a>

	&nbsp;-&nbsp;

	<a href="mailto:'.$FMail.'"><i class="icon-envelope icon-large2"></i> <span class="footerlinks">'.$txt_mail_us.'</span></a>

	&nbsp;-&nbsp;

        <a href="https://plus.google.com/112616688581972649007" rel="publisher" target="_blank"><i class="icon-google-plus-sign icon-large2"></i> <span class="footerlinks">Google+</span></a>

	&nbsp;-&nbsp;

	<a href="http://flattr.com/thing/'.$FL_Thing.'" target="_blank"><i class="icon-star-empty icon-large2"></i> <span class="footerlinks">'.$txt_flattr_us.'</span></a>

	&nbsp;-&nbsp;';

//	<a href="#"
//        onclick=\'document.cookie="wpsmart_view_full_site=0;";window.location.href = "'.$actual_link.'";return false;\' id="view_full_site"><i class="icon-google-phone icon-large2"></i> <span class="footerlinks">Mobile optimized site</span></a>

        global $code_version;
	$output .= '
        <a href="'.$actual_link_mobile.'"><i class="icon-resize-small icon-large2"></i> <span class="footerlinks">'.$txt_for_mobile.'</span></a>

	&nbsp;-&nbsp;

	<a href="https://github.com/cptpike/linuxhardwareguide" target="_blank"><i class="icon-github-sign icon-large2"></i> <span class="footerlinks">Code base: '.$code_version.'</span></a>



        </p>';
        echo $output;



$url_contact="contact";
$url_donate="donations";
$region=get_region();
if ($region == "de") $url_contact = "kontakt";
if ($region == "de") $url_donate = 'faq#donate';

echo '
	</div>

	<div id="bottom-links">

           <div id="general-box">
              <b>'.$txt_footer_general.'</b>
              <br /> <a href="'.$url_lang.'/'.$url_contact.'">'.$txt_footer_contact.'</a>
              <br /> <a href="'.$url_lang.'/faq">'.$txt_footer_faq.'</a>
              <br /> <a href="'.$url_lang.'/credits">'.$txt_footer_credits.'</a>
           </div>

           <div id="general-box-2">
              <b>'.$txt_footer_contributors.'</b>
              <br /> <a href="'.$url_lang.'/faq#contribute">'.$txt_footer_support.'</a>
              <br /> <a href="'.$url_lang.'/faq#report">'.$txt_footer_problem.'</a>
              <br /> <a href="'.$url_lang.'/'.$url_donate.'">'.$txt_footer_donate.'</a>
           </div>

           <div id="general-box-3">
              <b>'.$txt_footer_suppliers.'</b>
              <br /> <a href="'.$url_lang.'/faq#supplier">'.$txt_footer_hwsuppliers.'</a>
              <br /> <a href="'.$url_lang.'/faq#manufacturer">'.$txt_footer_manufacturers.'</a>
              <br /> <a href="'.$url_lang.'/faq#advertiser">'.$txt_footer_advertisers.'</a>
           </div>

        </div>

';

wp_footer();

?>
</body>
</html>
