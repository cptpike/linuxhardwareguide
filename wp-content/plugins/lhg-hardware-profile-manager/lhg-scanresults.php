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

function lhg_scan_overview_get_user_title($_id) {

	global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("SELECT usertitle FROM `lhghwscans` WHERE id = %s", $_id);
	$usertitle = $lhg_price_db->get_var($myquery);

        return $usertitle;
}

function lhg_scan_overview_get_user_tags($_id) {

	global $lhg_price_db;

	$myquery = $lhg_price_db->prepare("SELECT usertags_ids FROM `lhghwscans` WHERE id = %s", $_id);
	$usertags = $lhg_price_db->get_var($myquery);

        return $usertags;
}

?>