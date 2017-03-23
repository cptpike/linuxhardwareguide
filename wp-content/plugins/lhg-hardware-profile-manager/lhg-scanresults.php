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