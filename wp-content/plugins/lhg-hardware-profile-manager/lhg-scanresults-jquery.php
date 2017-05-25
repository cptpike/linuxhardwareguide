<?php

function lhg_scan_set_tags_jquery ( $_sid ) {
	print '

<script type="text/javascript">
/* <![CDATA[ */


	jQuery(document).ready( function($) {


        	//
                // store tags provided by user
                //

                $(\'[name^="scan-comments-"]\').click(function(){

                                	var button = this;
                                	var id = $(button).attr(\'name\').substring(14);
                                	var boxname = "#updatearea-".concat(id);
                                	var urlinput = "#url-".concat(id);
                                	var loadname = "button-load-".concat(id);
                                	var postidname = "#postid-".concat(id);
                                	var postid = $(postidname).val();
                                	var box = $(boxname);

	                                // "we are processing" indication
        	                        //var indicator_html = \'<img class="scan-load-button" id="button-load-\'.concat(id);
                	                //indicator_html = indicator_html.concat(\'" src="/wp-uploads/2015/11/loading-circle.gif">\');

                        	        //$(box).css(\'background-color\',\'#dddddd\');
                                	//$(button).after(indicator_html);


	                                //prepare Ajax data:
        	                        var session = "'.$_sid.'";
                	                var tags = $("#tag-select-box-"+id).val();
                        	        var data ={
                                	        action: \'lhg_scan_update_tags_ajax\',
                                        	id: id,
	                                        session: session,
        	                                postid: postid,
                	                        tags: tags
                        	        };


                                	//$(box).append("Debug: "+asinURL);


	                                //load & show server output
        	                        $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
                                        //
        	                                //Debug:
                	                        //$(box).append("Response: <br>IMG: "+imageurl+" <br>text: "+responsetext);
                                        //
                        	        });

	                                //prevent default behavior
        	                        return false;

                                });


                //
                // Store tags for mainboard
                //

		$("#mb-submit").click(function(){

		        var postid = $("#hwtext-input-asin-mb").attr(\'name\').substring(7);
		        var session = "'.$_sid.'";
                	var tags = $("#tag-select-box-mb").val();
                        var id = "mb";

	                //prepare Ajax data:
                	var data ={
                		action: \'lhg_scan_update_tags_ajax\',
	                        id: id,
		                session: session,
                		postid: postid,
                        	tags: tags,
		        };

                        // send AJAX request
	                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
	        	        var return_comment     = $(response).find("supplemental return_comment").text();
	                });

        		return false;

	        });




        });



/*]]> */
</script>


';
}


function lhg_scan_set_asin_jquery ( $_sid ) {
	print '

<script type="text/javascript">
/* <![CDATA[ */


	jQuery(document).ready( function($) {

        	// submit button pressed
		$(\'[name^="scan-comments-"]\').click(function(){

        		var button = this;
		        var id = $(button).attr(\'name\').substring(14);
        		var boxname = "#updatearea-".concat(id);
                	var urlinput = "#url-".concat(id);
	                var loadname = "button-load-".concat(id);
        	        var postidname = "#postid-".concat(id);
	        	var postid = $(postidname).val();
	        	var asin = $("#hwtext-input-asin-"+id).val();
        	        var box = $(boxname);

                	// "we are processing" indication
	                //var indicator_html = \'<img class="scan-load-button" id="button-load-\'.concat(id);
		        //indicator_html = indicator_html.concat(\'" src="/wp-uploads/2015/11/loading-circle.gif">\');

	                //$(box).css(\'background-color\',\'#dddddd\');
        	        //$(button).after(indicator_html);


	                //prepare Ajax data:
		        var session = "'.$_sid.'";
                	var data ={
                		action: \'lhg_scan_update_asin_ajax\',
	                        id: id,
		                session: session,
                		postid: postid,
                        	asin: asin,
		        };

                        // send AJAX request
	                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
	        	        var return_comment     = $(response).find("supplemental return_comment").text();
	                });

        		return false;

	        });
        });



/*]]> */
</script>
';

}

function lhg_scan_set_mb_asin_jquery ( $_sid, $_pid ) {
        # ASIN handler for mainboard entry

	print '

<script type="text/javascript">
/* <![CDATA[ */


	jQuery(document).ready( function($) {


        	// mainboard submit button pressed
                // store ASIN
		$("#mb-submit").click(function(){

		        var postid = $("#hwtext-input-asin-mb").attr(\'name\').substring(7);
	        	var asin = $("#hwtext-input-asin-mb").val();
		        var session = "'.$_sid.'";
                        var id = "mb";


	                //prepare Ajax data:
		        var session = "'.$_sid.'";
		        var postid = "'.$_pid.'";
                	var data ={
                		action: \'lhg_scan_update_asin_ajax\',
	                        id: id,
		                session: session,
                		postid: postid,
                        	asin: asin,
		        };

                        // send AJAX request
	                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
	        	        var return_comment     = $(response).find("supplemental return_comment").text();
	                });

        		return false;

	        });


                // Auto-update of ASIN
		$("#hwtext-input-asin-mb").on("input", function() {


	                //prepare Ajax data:
		        var session = "'.$_sid.'";
		        var postid = "'.$_pid.'";
	        	var asin = $("#hwtext-input-asin-mb").val();

                        if (asin.length != 10) {
				$("#asin-status").html("&nbsp; Incorrect ASIN length "+asin.length);
                                return false;
                        }
                        else if (asin.substring(0,2) != "B0"){
				$("#asin-status").html("&nbsp; Incorrect ASIN start "+asin.substring(0,2));
                                return false;
                        }else{
				$("#asin-status").html("&nbsp;<img class=\"scan-load-button\" id=\"button-load-mb-asin\" src=\"/wp-uploads/2015/11/loading-circle.gif\">&nbsp;Checking ASIN...");
                        }

                        var data ={
                		action: \'lhg_scan_mb_live_update_asin_ajax\',
		                session: session,
                        	asin: asin,
                                postid: postid
		        };


                        // send AJAX request
	                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
	        	        var return_image       = $(response).find("supplemental postimg_url").text();
	        	        var return_amz_image   = $(response).find("supplemental imgurl").text();


                                if (return_image.substring(0,3) == "/wp") {
                                        return_image = "<img src=\""+return_image+"\" class=\"hwscan-image-thumbnail-found\">";
                                }

				$("#lhg-scan-mb-thumbnail").html(return_image);
				$("#asin-status").html("");

	                });



                        return false;

	        });


        });



/*]]> */
</script>
';

}

function lhg_scan_set_designation_jquery( $_sid ) {

        // JQuery code to update designation

	print '

<script type="text/javascript">
/* <![CDATA[ */

	jQuery(document).ready( function($) {

        	// submit button pressed
		$(\'[name^="hwscan-designation-button"]\').click(function(){

        		var button = this;
                	var input = $("#hwtext-input-scan-designation").val();
        	        var box = $("#hwscan-designation-cell");

                	// "we are processing" indication
	                var indicator_html = \'<img class="scan-load-button" id="button-load-designation" src="/wp-uploads/2015/11/loading-circle.gif">\';

	                $(box).css(\'background-color\',\'#dddddd\');
        	        $(button).after(indicator_html);


	                //prepare Ajax data:
		        var sid = "'.$_sid.'";
                	var data ={
                		action: \'lhg_scan_update_designation_ajax\',
	                        sid: sid,
		                input: input,
		        };

                        // send AJAX request
	                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
	        	        var return_comment     = $(response).find("supplemental return_comment").text();

                                $(box).css(\'background-color\',\'#ffffff\');
        	        	$("#button-load-designation").remove();

	                });



        		return false;

	        });

        });



/*]]> */
</script>
';
}


function lhg_usb_selector_jquery($_sid) {

print '

<script type="text/javascript">
/* <![CDATA[ */

	jQuery(document).ready( function($) {


        // USB radio button actions
        $("[id^=usb-radio-n-]").click(function() {
        	var id = $(this).attr(\'id\').substring(12);
	        $("#registration-"+id).show("slow");

	        //prepare Ajax data:
        	var session = "'.$_sid.'";
		var data ={
        		action: \'lhg_scan_onboardn_ajax\',
                	id: id,
	                session: session
                };

        	$.get(\'/wp-admin/admin-ajax.php\', data, function(response){
        		//currently no visual feedback
                });
        });

        $("[id^=usb-radio-y-]").click(function() {
        	var id = $(this).attr(\'id\').substring(12);
	        $("#registration-"+id).hide("slow");

                //prepare Ajax data:
                var session = "'.$_sid.'";
	        var data ={
                	action: \'lhg_scan_onboardy_ajax\',
                        id: id,
                        session: session
                };

        	$.get(\'/wp-admin/admin-ajax.php\', data, function(response){
        		//currently no visual feedback
                });

        });


        });

/*]]> */
</script>
';
}

function lhg_pci_selector_jquery($_sid) {

print '

<script type="text/javascript">
/* <![CDATA[ */

	jQuery(document).ready( function($) {


        // PCI radio button actions
        $("[id^=radio-n-]").click(function() {
        	var id = $(this).attr(\'id\').substring(8);
	        $("#registration-"+id).show("slow");

	        //prepare Ajax data:
        	var session = "'.$_sid.'";
		var data ={
        		action: \'lhg_scan_onboardn_ajax\',
                	id: id,
	                session: session
                };

        	$.get(\'/wp-admin/admin-ajax.php\', data, function(response){
        		//currently no visual feedback
                });
        });

        $("[id^=radio-y-]").click(function() {
        	var id = $(this).attr(\'id\').substring(8);
	        $("#registration-"+id).hide("slow");

                //prepare Ajax data:
                var session = "'.$_sid.'";
	        var data ={
                	action: \'lhg_scan_onboardy_ajax\',
                        id: id,
                        session: session
                };

        	$.get(\'/wp-admin/admin-ajax.php\', data, function(response){
        		//currently no visual feedback
                });

        });

        // hide all components auto-selected as on-board
        $("[id^=radio-y-]").each(function(){
        	var id = $(this).attr(\'id\').substring(8);
                if($(this).is(":checked")) {
                	$("#registration-"+id).hide("slow");
                }
        });


        // hide all recognized PCI components by default
        // show if new mainboard needed
        $(".mb-default-hidden").hide();
        $("#create-mainboard").click(function(){
	        $(".mb-default-hidden").show();
        });



        });

/*]]> */
</script>
';

}

function lhg_mainboard_jquery($_sid) {
	echo '
                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

                                //
                                //
                                // User leaves comment on Mainboard
				//
                                //

                                $(\'#mb-submit\').click(function(){

                                var button = this;

                                // "we are processing" indication
                                var indicator_html = \'<img class="scan-load-button" id="button-load-mb-comment" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                                $(button).after(indicator_html);


                                //prepare Ajax data:
                                var session = "'.$_sid.'";
                                var comment = $("#mb-usercomment").val();
                                var mb_url = $("#url-mb").val();
                                var data ={
                                        action: \'lhg_scan_update_mb_comment_ajax\',
                                        session: session,
                                        url: mb_url,
                                        comment: comment
                                };


                                //load & show server output
                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                        $(button).append("Response");
                                        $(button).after(response);
                                        //$(box).append("Response: <br>IMG: "+imageurl+" <br>text: "+responsetext);

                                        //return to normal state
                                        $(button).val("Update");
                                        $(button).attr("class", "hwscan-comment-button-light");
                                        var indicatorid = "#button-load-mb-comment";
                                        $(indicatorid).remove();

                                });

                                //prevent default behavior
                                return false;

                                });



                                //
                                //
                                // Store MB Title
                                //
                                //

                                $(\'#mb-submit\').click(function(){

                                	var button = this;


	                                //prepare Ajax data:
        	                        var session = "'.$_sid.'";
                                        var title = $("#hwtext-input-title-mb").val();
                        	        var data ={
                                	        action: \'lhg_scan_update_mb_title_ajax\',
                                        	session: session,
	                                        title: title
        	                        };

	                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

        	                        });

	                                //prevent default behavior
        	                        return false;

                                });

                                //
                                //
                                // Store MB/laptop status
                                //
                                //

                                $(\'#mb-submit\').click(function(){

                                	var button = this;


	                                //prepare Ajax data:
        	                        var session = "'.$_sid.'";
                	                var laptop_prob = "";
                        	        var data ={
                                	        action: \'lhg_scan_update_mb_laptop_status_ajax\',
                                        	session: session,
	                                        laptop_prob: laptop_prob
        	                        };


	                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

        	                        });

	                                //prevent default behavior
        	                        return false;

                                });





                });

                /*]]> */
                </script>';

}


function lhg_editor_tools_mainboard_jquery( $_sid, $_postid, $_dmi ) {

// Create article if MB is recognized incorrectly

print           '<script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {


                                if($("#lhg-scan-mb-inputarea-found").length > 0) {
                                	// a mainboard was found
					$(".mb-buttons-article-exists").hide();
                                        // hide mainboard input area by default (if already on MB was recognized)
	                                $("#lhg-scan-mb-inputarea-found").hide();

                                }else{
					$(".mb-buttons-create-new").hide();
                                }

                                $("#create-mainboard").click(function() {
	                                $("#lhg-scan-mb-inputarea-found").show();
				        //$(".mb-buttons-article-exists").show();

                                        // tag selector needs to be resized. Has zero width because of hidden status
                                        $("#tag_select_box_mb_chosen").width("80%");

                                	var indicator_html = \'<img class="scan-load-button" id="button-load-new-mb" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                                	$(this).after(indicator_html);

                                    //var id = $(this).attr(\'id\').substring(8);
                                    //$("#pci-feedback-"+id).show("slow");
                                    //$("#updatearea-"+id).show();
				    //$("#scan-comments-"+id).show();

                                    //prepare Ajax data:
                                    var session = "'.$_sid.'";
                                    var title = "'.trim($_dmi).'";
	                            var data ={
                                        action: \'lhg_create_mainboard_post_ajax\',
                                        session: session,
                                        title: title
                                    };

                                    $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
                                       //currently no visual feedback
                                        var postid     = $(response).find("supplemental postid").text();

	                                $("#button-load-new-mb").hide();
                	                //$("#create-mainboard").after(" DoneA"+postid);
                                        var newurl = "/wp-admin/post.php?post=" + postid + "&action=edit&scansid=" + session;
        	                        $("#publish-mb-new").after("<a id=\"created-mb-article\" href=\"" + newurl + "\">Edit new mainboard article</a><br>");
                                        $("#create-mainboard").hide();
                                    });
                                    //$(this).replaceWith("Test");

	                            //prevent default behavior
        	                    return false;

                                });


	                        //prevent default behavior
        	                return false;

        	});

                /*]]> */
                </script>';
       // } // end create MB article



print '

<script type="text/javascript">
/* <![CDATA[ */

	jQuery(document).ready( function($) {


        	// submit button pressed
		$("[id^=publish-mb-]").click(function(){

	        	var id = $(this).attr(\'id\').substring(11);

                	// "we are processing" indication
	                var indicator_html = \'<img class="scan-load-button" id="button-load-mb-publish" src="/wp-uploads/2015/11/loading-circle.gif">\';

	                //$(box).css(\'background-color\',\'#dddddd\');
        	        $(this).after(indicator_html);

                        var PCI_IDs  = [];
                        var USB_IDs  = [];
                        var asin_mb  = $("#hwtext-input-asin-mb").val();
                        var title_mb = $("#hwtext-input-title-mb").val();
                	var tags = $("#tag-select-box-mb").val();
                	var type = $("#scan-selector-mb-type").val();
                        var postid = "'.$_postid.'";

			$("[id^=radio-y-]").filter(":checked").each(function(){ PCI_IDs.push(this.id.substring(8)); });
			$("[id^=usb-radio-y-]").filter(":checked").each(function(){ USB_IDs.push(this.id.substring(12)); });
                	//var idarray = $("[id^=radio-y-]").attr("id");


	                //prepare Ajax data:
		        var sid = "'.$_sid.'";
                	var data ={
                		action: \'lhg_scan_publish_mb_article_ajax\',
	                        sid: sid,
		                id: id,
                                idarray_pci: PCI_IDs,
                                idarray_usb: USB_IDs,
                                asin_mb: asin_mb,
                                title_mb: title_mb,
                                postid: postid,
                                type: type,
                                idarray_tags: tags
		        };

                        // send AJAX request
	                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
	        	        var return_comment     = $(response).find("supplemental return_comment").text();

                                //$(box).css(\'background-color\',\'#ffffff\');
        	        	$("#button-load-mb-publish").remove();

	                });

                        // also save all possible modifications by clicking the submit button (quick & dirty solution)
                        $("#mb-submit").click();

        		return false;

	        });

        	return false;

        });

/*]]> */
</script>
';
}

function lhg_scan_set_mb_type_jquery( $_sid ) {

        // JQuery code to update designation

	print '

<script type="text/javascript">
/* <![CDATA[ */

	jQuery(document).ready( function($) {

        	// submit button pressed
		$(\'select[name^="scan-selector-mb-type"]\').change(function(){

                	var val = $(this).val();

	                //prepare Ajax data:
		        var sid = "'.$_sid.'";
                	var data ={
                		action: \'lhg_scan_update_mb_type_ajax\',
	                        sid: sid,
		                val: val,
		        };

                        // send AJAX request
	                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
	        	        var return_comment     = $(response).find("supplemental return_comment").text();

	                });



        		return false;

	        });

        });



/*]]> */
</script>
';
}


?>