<?php
/**
 * Plugin Name: Pie Highchart jQuery
 * Plugin URI: http://wpsoft.com.br
 * Description: Highcharts Action
 * Version: 1.0
 * Author: diegpl, pkelbert
 * Author URI: http://wpsoft.com.br
 * License: GPL2
 */
 

function highChartsAction() {



//check if we run the first time

$lhg_latest_chart_id = get_option ( "lhg_pie_chart_id" );
if ( $lhg_latest_chart_id === false ) {
  	add_option ("lhg_pie_chart_id", 1);
  	add_option ("lhg_pie_chart_update", 1);
}

//print "chartID: $lhg_latest_chart_id  ";


//do we need to create a for chart id "lhg_pie_chart_id"
$lhg_pie_chart_update = get_option ( "lhg_pie_chart_update" );

        //check if external event triggers update of if we haven't updated for quite some time

//$debug = true;
$debug = false;

if ( is_user_logged_in() or ( $lhg_pie_chart_update == 1 ) or ( get_transient('lhg_chart_updated') === false ) or $debug ) {

//echo "Update needed";
// regenerate transient
set_transient('lhg_chart_updated', 1 , 2*60*60 );

//update chart id
$lhg_chart_id = get_option ("lhg_pie_chart_id");
$lhg_chart_id++;
//we cycle
if ($lhg_chart_id > 100) $lhg_chart_id = 1;
update_option ("lhg_pie_chart_id", $lhg_chart_id);

//mark as updated
update_option ("lhg_pie_chart_update", 0);

//$top_users[$i]["Karma"]=$y['points'];

// get voted donation
	global $donation_total;
	global $donation;
	global $top_users;

// get Top 3  donation targets and percentage
$karma_total = $top_users[1]["Karma"] + $top_users[2]["Karma"] + $top_users[3]["Karma"];

//echo "Karma Total: $karma_total";

$donationID1 = get_user_meta($top_users[1]["ID"],'user_donation_target',true);
$donationID2 = get_user_meta($top_users[2]["ID"],'user_donation_target',true);
$donationID3 = get_user_meta($top_users[3]["ID"],'user_donation_target',true);

//echo "DID: ".$top_users[1]["ID"];
//echo "DID: $donationID1";
if ($donationID1 == "") $donationID1 = 1;
if ($donationID2 == "") $donationID2 = 1;
if ($donationID3 == "") $donationID3 = 1;

if ($karma_total != 0) {
	$percent1= $top_users[1]["Karma"] / $karma_total*0.75;
	$percent2= $top_users[2]["Karma"] / $karma_total*0.75;
	$percent3= $top_users[3]["Karma"] / $karma_total*0.75;
}else{
	$percent1= 0; #$top_users[1]["Karma"] / $karma_total*0.75;
	$percent2= 0; #$top_users[2]["Karma"] / $karma_total*0.75;
	$percent3= 0; #$top_users[3]["Karma"] / $karma_total*0.75;

}

//print $donation[1]["Name"];
//print $donation[1]["Percentage"];

//print "P1: $percent1  -- ";

//most voted by users:
$topvotes = lhg_voted_donation();
$most_voted = lhg_most_voted ($topvotes);


	//$script_lang = "fr";
        global $lang_array;

        //print "Test";

        foreach ($lang_array as &$script_lang) {
                //print "LG: $script_lang";
		$filename = "/var/www/charts/pie/piechart-donation.js.".$lhg_chart_id.".".$script_lang;
		$piechart = lhg_create_pie_script($script_lang, $percent1, $percent2, $percent3 , $donationID1, $donationID2, $donationID3, $most_voted);
		lhg_create_file($filename, $piechart);
        }

}else{
    // print "no update needed";
}




        global $region;
        //echo "LANG_web: $region";
	$lhg_chart_id = get_option ("lhg_pie_chart_id");

        $langfile=$region;
        if ($region=="co.jp") $langfile="ja";
        if ($region=="co.uk") $langfile="uk";
        if ($region=="cn") $langfile="zh";

	//echo "<link rel='stylesheet' id='style-css'  href='".plugins_url( 'style.css' , __FILE__ )."' type='text/css' media='all' />";
	
	echo "<script type='text/javascript' src='".plugins_url( 'highcharts.js' , __FILE__ )."'></script>";
	//echo "<script type='text/javascript' src='".plugins_url( 'modules/exporting.js' , __FILE__ )."'></script>";
	echo "<script type='text/javascript' src='/wp-uploads/charts/pie/piechart-donation.js.".($lhg_chart_id).".".$langfile."'></script>";
}

add_action('wp_footer', 'highChartsAction');

//[highchart]
function highchartAction( $atts ){
	return "<div id='container-test' style='min-width: 310px; height: 300px; max-width: 600px; margin: 0 auto'></div>";
}
add_shortcode( 'highchart', 'highchartAction' );


function highchartAction_quarterly( $atts ){
	return "<div id='container-quarterly' style='min-width: 310px; height: 300px; max-width: 600px; margin: 0 auto'></div>";
}
add_shortcode( 'highchart_quarterly', 'highchartAction_quarterly' );


function lhg_create_pie_script($scriptlang , $percent1, $percent2, $percent3, $donationID1, $donationID2, $donationID3, $most_voted) {

        //echo "Perce1: $percent1";
        //echo "SL: $scriptlang";

	global $donation_total;
	global $donation;
	global $top_users;

	$txt_chart_all		= "voted by all users";
	$txt_chart_vote_by     	= "voted by";


	if ($scriptlang == "fr") {
		$txt_chart_all		= "voté par tous les utilisateurs";
		$txt_chart_vote_by     	= "voté par ";
	}
	if ($scriptlang == "es") {
		$txt_chart_all		= "votado por todos los usuarios";
		$txt_chart_vote_by     	= "votado por";
	}
	if ($scriptlang == "it") {
		$txt_chart_all		= "votato da tutti gli utenti";
		$txt_chart_vote_by     	= "votado da";
	}
	if ($scriptlang == "zh") {
		$txt_chart_all		= "所有用户投票";
		$txt_chart_vote_by     	= "通过投票";
	}
	if ($scriptlang == "ja") {
		$txt_chart_all		= "すべてのユーザーによる投票 ";
		$txt_chart_vote_by     	= "による投票";
	}

        list($top_donation_target_names, $top_donation_target_points) = lhg_return_donation_targets();

$piechart=
"
jQuery(function () {
    jQuery('#container').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
        text: ''
        },
        tooltip: {
    	    pointFormat: '{series.name}: <b> EUR {point.y:.2f}</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b><br>EUR {point.y:.2f}',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Donation',
            data: [
                {
                    name: '".$donation[$most_voted]["NameShort"]."<br>(".$txt_chart_all.")',
                    y: ".(0.25*$donation_total).",
                    sliced: true,                                    selected: true
                },


                ['".$donation[$donationID1]["NameShort"]."<br>(".$txt_chart_vote_by." ".$top_users[1]["DisplayName"].")',       ".$percent1*$donation_total."],
                ['".$donation[$donationID2]["NameShort"]."<br>(".$txt_chart_vote_by." ".$top_users[2]["DisplayName"].")',       ".$percent2*$donation_total."],
                ['".$donation[$donationID3]["NameShort"]."<br>(".$txt_chart_vote_by." ".$top_users[3]["DisplayName"].")',       ".$percent3*$donation_total."]
            ]
        }]
    });


    // Quarterly Pie Chart
    jQuery('#container-quarterly').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
        text: ''
        },
        tooltip: {
    	    pointFormat: 'Quarterly Collected Points: {point.y:.2f}</b><br>Donation Percentage: {point.percentage:.0f}%'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}: {point.percentage:.0f}%</b><br>Points: {point.y:.2f}',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Donation',
            data: [
                {
                    name: '".$top_donation_target_names[0]."',
                    y: ".$top_donation_target_points[0].",
                    sliced: false,                                    selected: true
                },
                ";
                $j=1;
                while ( $top_donation_target_names[$j] != "" ) {
	            	$piechart .= "['".$top_donation_target_names[$j]."',       ".$top_donation_target_points[$j]."],";
                        $j++;
                }
$piechart .= "
	        ]
        }]
    });


});
";
return $piechart;
}

function lhg_create_file($filename, $piechart) {

	$handle = fopen($filename, "w");

	if (is_writable($filename)) {


   		 if (!$handle = fopen($filename, "w")) {
	        	print "Kann die Datei $filename nicht öffnen";
	         	exit;
		 }
    		if (!fwrite($handle, $piechart)) {
        		print "Kann in die Datei $filename nicht schreiben";
        		exit;
		}

	   	 //print "Fertig, in Datei $filename wurde $somecontent geschrieben";

    		fclose($handle);
	} else {
    		print "Die Datei $filename ist nicht schreibbar";
	}
}

?>
