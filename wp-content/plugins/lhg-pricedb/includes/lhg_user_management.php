<?php

# User management rules
# In addition to standard Wordpress permissions, karma points define what users can and cannot do

define ('LHG_KARMA_edit_posts', 10);
define ('LHG_KARMA_delete_posts', 100);
define ('LHG_KARMA_upload_files', 100);
define ('LHG_KARMA_publish_posts', 300);
define ('LHG_KARMA_edit_published_posts', 300);


#apply_filters ( 'map_meta_cap', $caps, $cap, $user_id, $args );
add_filter ( 'map_meta_cap', 'lhg_check_permissions', 10, 4 );

function lhg_check_permissions( $caps, $cap, $user_id, $args) {

	$karma = cp_getPoints( $user_id ); //get karma points

	#error_log("User $user_id permission check cap: $cap - caps:".join(",",$caps) );

        if ( 'edit_posts' == $cap ) {
                #error_log("User wants to edit post - caps:".join(",",$caps) );
                if ( $karma < LHG_KARMA_edit_posts ) {
                        #error_log("Not enough points!");
                	$caps[] = 'activate_plugins';
                }else{
                        error_log("Enough points. Let go!");
                	$caps[] = 'read';
			#caps = array();
        	}
	}

        if ( 'delete_posts' == $cap ) {
                if ( $karma < LHG_KARMA_delete_posts ) {
                	$caps[] = 'activate_plugins';
                }else{
                	$caps[] = 'read';
			#$caps = array();
			#$caps[] = '';
        	}
	}

        if ( 'upload_files' == $cap ) {
                if ( $karma < LHG_KARMA_upload_files ) {
                	$caps[] = 'activate_plugins';
                }else{
                	$caps[] = 'read';
			#$caps = array();
        	}
	}

        if ( 'publish_posts' == $cap ) {
                if ( $karma < LHG_KARMA_publish_posts ) {
                	$caps[] = 'activate_plugins';
                }else{
                	$caps[] = 'read';

	#			$caps[] = '';
        			#$caps = array();

        	}
	}

        if ( 'edit_published_posts' == $cap ) {
                if ( $karma < LHG_KARMA_edit_published_posts ) {
                	$caps[] = 'activate_plugins';
                }else{
                	$caps[] = 'read';

	#		$caps[] = '';
			#$caps = array();

        	}
	}



	return $caps;
}

?>