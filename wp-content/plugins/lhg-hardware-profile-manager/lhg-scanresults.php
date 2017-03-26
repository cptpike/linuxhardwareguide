<?php

function lhg_scan_tag_selector ( $_id, $_newPostID, $scantype ) {

	$tags = get_tags(array('get'=>'all'));
        #var_dump($tags);

        # check if user defined tags exist
        $usertags = lhg_scan_overview_get_user_tags($_id);
        $all_tagids = split(",", $usertags);


        if ($usertags == "") {
	        # get default tags of article
        	# collect them in all_tagids array
	        $all_tagids = array();
        	$post_tagids = wp_get_post_tags($_newPostID, array( 'fields' => 'ids' ));
	        $title_tagids = lhg_taglist_by_title ( get_the_title( $_newPostID ) );

        	# add tags by scantype
	        if ($scantype == "cpu") array_push ( $all_tagids, 874 );
        	if ($scantype == "usb") array_push ( $all_tagids, 156 );
	        if ($scantype == "drive") array_push ( $all_tagids, 581 );



        	#error_log("Post Title: ".get_the_title( $_newPostID ));
	        #error_log("Title tags: ".
        	#var_dump($title_tagids);

	        foreach ($title_tagids as $title_tagid)  {
        	        #$tagslug = get_term_by('id',$tagid,'post_tag');
                	#$result = $tagslug->slug;
	                array_push ( $all_tagids, $title_tagid );
        	}
        }


        print '<br><div class="hwscan-designation-tags">Hardware tags:</div>
        <select id="tag-select-box-'.$_id.'" data-placeholder="Select hardware tags" multiple class="chosen-select" style="width: 80%;">';

        foreach ($tags as $tag){
                $article_tag = 0;
                #error_log("checking $tag->id");
                if ( in_array($tag->term_id, $all_tagids) ) {
                	$article_tag = 1;
                        #print "Found $tag->name<br>";
		}

                print '<option value="'.$tag->term_id.'"';
                if ($article_tag == 1) print ' selected';
                print '>'.$tag->name.'</option>';
	}

 	print '
        </select>';

}

function lhg_scan_category_selector ( $_id, $_newPostID, $_scantype ) {


        # check if user defined tags exist
        $usercategories = lhg_scan_overview_get_user_categories($_id);
        $all_categories_ids=array(874, 478, 333, 335, 583, 507, 475, 470, 562, 472, 328, 738, 476, 569, 521, 325, 329, 5, 322, 588);
        # which category is a sub-category?
        $sub_categories_ids=array(  0,   0, 333, 335, 583,   0,   0,   0, 562,   0,   0, 738, 476, 569, 521, 325, 329, 0, 322, 588);
        $selected_categories = split(",", $usercategories);


        if ($usercategories == "") {

        	# add tags by scantype
	        if ($_scantype == "cpu") array_push ( $selected_categories, 874 );
        	// if ($scantype == "usb") array_push ( $all_categories, 156 );
	        if ($_scantype == "drive") array_push ( $selected_categories, 478 );


	        //foreach ($all_categoriesagids as $title_tagid)  {
        	//        #$tagslug = get_term_by('id',$tagid,'post_tag');
                //	#$result = $tagslug->slug;
	        //        array_push ( $all_tagids, $title_tagid );
        	//}
        }



        print '<br><div class="hwscan-designation-categories">Hardware categories:</div>
        <select id="category-select-box-'.$_id.'" data-placeholder="Select hardware categories" multiple class="chosen-select" style="width: 80%;">';

        foreach ($all_categories_ids as $catid){

	        # check if this is a sub-category
                $is_sub = 0;
                if ( in_array( $catid , $sub_categories_ids) ) $is_sub = 1;


                $cat_was_selected = 0;
                #error_log("checking $tag->id");
                if ( in_array($catid, $selected_categories) ) {
                	$cat_was_selected = 1;
                #        #print "Found $tag->name<br>";
		}

                print '<option value="'.$catid.'"';
                if ( $cat_was_selected == 1 ) print ' selected';
                print '>';
                if ( $is_sub == 0 ) $former_cat = get_cat_name( $catid );
                if ( $is_sub == 1 ) print " $former_cat &rarr; ";
                print get_cat_name( $catid ).'</option>';
	}

 	print '
        </select>';

}

function lhg_scan_set_asin ( $_id, $_newPostID, $_scantype, $_sid ) {


        // get stored ASIN

	$key = "amazon-product-single-asin";

	#error_log("ASIN? $_newPostID $key --- ".get_post_meta($_newPostID, $key, TRUE) );

        if( get_post_meta($_newPostID, $key, TRUE) ) { //if the custom field already has a value
  		$old_asin = get_post_meta($_newPostID, $key, TRUE);
        	#error_log("Stored ASIN: $old_asin");

	} 



        print '<br><div class="hwscan-asin-setting">Set Amazon.com ID (ASIN). Format: B0xxxxxxxx:</div>';
        print '<input id="hwtext-input-asin-'.$_id.'" name="postid-'.$_newPostID.'" value="'.$old_asin.'" size="15" type="text"></input>';

        // ToDo: add auto selector



        // JQuery code to update ASIN
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


function lhg_scan_overview_get_user_title($_id) {

	global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("SELECT usertitle FROM `lhghwscans` WHERE id = %s", $_id);
	$usertitle = $lhg_price_db->get_var($myquery);

        return $usertitle;
}

function lhg_scan_overview_get_user_categories($_id) {

	global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("SELECT usercategories FROM `lhghwscans` WHERE id = %s", $_id);
	$usercategories = $lhg_price_db->get_var($myquery);

        return $usercategories;
}

function lhg_scan_overview_get_user_tags($_id) {

	global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("SELECT usertags_ids FROM `lhghwscans` WHERE id = %s", $_id);
	$usertags = $lhg_price_db->get_var($myquery);

        return $usertags;
}

?>