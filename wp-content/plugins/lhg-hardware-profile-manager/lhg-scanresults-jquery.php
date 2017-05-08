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


        	// mainboard submit button pressed
                // store ASIN
		$("#mb-submit").click(function(){

		        var postid = $("#hwtext-input-asin-mb").attr(\'name\').substring(7);
	        	var asin = $("#hwtext-input-asin-mb").val();
		        var session = "'.$_sid.'";
                        var id = "mb";


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
        		action: \'lhg_scan_usb_onboardn_ajax\',
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
                	action: \'lhg_scan_usb_onboardy_ajax\',
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


        // USB radio button actions
        $("[id^=radio-n-]").click(function() {
        	var id = $(this).attr(\'id\').substring(8);
	        $("#registration-"+id).show("slow");

	        //prepare Ajax data:
        	var session = "'.$_sid.'";
		var data ={
        		action: \'lhg_scan_pci_onboardn_ajax\',
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
                	action: \'lhg_scan_pci_onboardy_ajax\',
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


function lhg_editor_tools_mainboard_jquery($_sid) {

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

                        var PCI_IDs = [];
                        var USB_IDs = [];
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
		        };

                        // send AJAX request
	                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
	        	        var return_comment     = $(response).find("supplemental return_comment").text();

                                //$(box).css(\'background-color\',\'#ffffff\');
        	        	$("#button-load-mb-publish").remove();

	                });



        		return false;

	        });

        	return false;

        });

/*]]> */
</script>
';
}

?>