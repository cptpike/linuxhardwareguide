<?php

function lhg_scan_tag_selector ( $_id, $_newPostID, $_scantype, $_sid ) {



	$tags = get_tags(array('get'=>'all'));
        #var_dump($tags);

        # check if user defined tags exist
	if ( $_scantype == "mainboard" ) {
        	$_id = "mb";
	} else {

        }

        #error_log("Tag selector: $_id $_sid");
        #error_log( "ID: $_id Type: $_scantype" );

        $usertags = lhg_scan_overview_get_user_tags( $_id, $_sid );

        #error_log( "tags: $usertags" );

	$all_tagids = split( ",", $usertags );
        #var_dump($all_tagids);


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
	        #error_log("Scan: ".§scantype." ID: ". $_id);
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

                #if ($tag->term_id == 482) error_log("ID: $_id TagID: 482");

                #if ($tag != ""){
                	$article_tag = 0;
                	#if ($_id == "mb") error_log("checking $tag->term_id");
                	if ( in_array($tag->term_id, $all_tagids) ) {
                		$article_tag = 1;
	                        #if ($_id == "mb") print "Found $tag->name<br>";
			}

                	print '<option value="'.$tag->term_id.'"';
                	if ($article_tag == 1) print ' selected';
	                print '>'.$tag->name.'</option>';
 	       #}
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

        if( get_post_meta( $_newPostID, $key, TRUE ) ) { //if the custom field already has a value
  		$old_asin = get_post_meta( $_newPostID, $key, TRUE );
        	#error_log( "Stored Type: $_scantype ASIN: $old_asin PID: $_newPostID" );
	}

        # set standard ID for mainboards (no dediacted id available)
        if ($_scantype == "mainboard") {
        	$_id = "mb";
                $test = get_post_meta( $_newPostID, $key );
                #var_dump($test);
        	#error_log( "Stored ASIN: ".get_post_meta( $_newPostID, $key, TRUE )." == $old_asin PID: <$_newPostID>" );
        }

        print '<br><div class="hwscan-asin-setting">Set Amazon.com ID (ASIN). Format: B0xxxxxxxx:</div>';
        print '<input id="hwtext-input-asin-'.$_id.'" name="postid-'.$_newPostID.'" value="'.$old_asin.'" size="15" type="text"></input>';

        // ToDo: add auto selector



        // JQuery code to update ASIN
	lhg_scan_set_asin_jquery ( $_sid );

}


function lhg_scan_set_designation ( $_sid, $_show_public_profile ) {


        // get stored designation

        $old_designation = lhg_scan_overview_get_scan_designation( $_sid );

        if ($_show_public_profile == 1) {
        	print "$old_designation<br>";
                return;
	}


        print '<div class="hwscan-designation-setting">Rename your scan for easier tracking (optional)</div>';
        print '<input id="hwtext-input-scan-designation" name="hwtext-input-scan-designation" value="'.$old_designation.'" size="30" type="text"></input>';
	print '<input type="submit" id="hwscan-designation-button" name="hwscan-designation-button" value="Update" class="hwscan-designation-button" />';

        // ToDo: add auto selector

        lhg_scan_set_designation_jquery( $_sid );


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

function lhg_scan_overview_get_user_tags( $_id, $_sid ) {

	global $lhg_price_db;

        if ( $_id == "mb" ) {
                # mainboard tags needed
		$myquery = $lhg_price_db->prepare("SELECT mb_usertags_ids FROM `lhgscansessions` WHERE sid = %s", $_sid);
		$usertags = $lhg_price_db->get_var($myquery);
	        #error_log( "ID $_id SID: $_sid Tags: $usertags" );
 	}else{
		$myquery = $lhg_price_db->prepare("SELECT usertags_ids FROM `lhghwscans` WHERE id = %s", $_id);
		$usertags = $lhg_price_db->get_var($myquery);
        }

        return $usertags;
}

function lhg_scan_overview_get_scan_designation($_sid) {

	global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("SELECT designation FROM `lhgscansessions` WHERE sid = %s", $_sid);
	$designation = $lhg_price_db->get_var($myquery);

        # return default, if empty
        if ($designation == "") {
        	$myquery = $lhg_price_db->prepare("SELECT scandate FROM `lhgscansessions` WHERE sid = %s", $_sid);
		$designation_utime = $lhg_price_db->get_var($myquery);
                $designation = "My scan at ".gmdate("Y-m-d, H:i:s", $designation_utime);


	}

        return $designation;
}


?>