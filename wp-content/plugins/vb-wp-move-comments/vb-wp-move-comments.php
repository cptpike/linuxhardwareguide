<?php
/*
Plugin Name: WordPress Move Comments
Plugin URI: http://www.velvetblues.com/web-development-blog/wp-plugin-wordpress-move-comments/
Description: Moving comments between posts or parents can be a chore, especially if your blog uses threaded comments. WordPress Move Comments simplifies this process by enabling users to easily change the comment's post or page, the parent comment, and the comment author. Edits can be made via both Quick Edit and the simpler single-comment edit screen. To get started: 1) Activate, 2) Edit your comments.
Author: VelvetBlues.com
Author URI: http://www.velvetblues.com/
Author Email: info@velvetblues.com
Version: 1.0
License: GPLv2 or later
*/
/*  Copyright 2012  Velvet Blues Web Design  (email : info@velvetblues.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
if ( !function_exists( 'add_action' ) ) {
?><h3>Oops! This page cannot be accessed directly.</h3>
<p>For support using the Velvet Blues WordPress Move Comments plugin, <a href="http://www.velvetblues.com/web-development-blog/wordpress-move-comments-plugin/" title="Velvet Blues WordPress Move Comments plugin">click here</a>.</p>
<p>If you are looking for general WordPress assistance, <a href="http://www.velvetblues.com/" title="WordPress Web Development and Services">Velvet Blues can help with that too</a>.</p><?php
	exit;
}

add_action('admin_menu', 'velvetblues_wmc_add_meta');
add_filter('comment_save_pre', 'velvetblues_wmc_save' );
add_filter('plugin_row_meta', 'velvetblues_wmc_links', 10, 2 );
add_filter('current_screen', 'velvetblues_wmc_check_screen' );


function velvetblues_wmc_add_meta() {
	add_meta_box( 'comment-info', __('Comment Options'), 'velvetblues_wmc_meta_box', 'comment', 'normal' );
}

function velvetblues_wmc_meta_box() {
    global $comment;

	$tmp   = get_comment_meta( $comment->comment_ID, "language", true );
        $comment_lang = $tmp;

        $tmp   = get_comment_meta( $comment->comment_ID, "rating-id" );
        $rating_id = $tmp[0];

	$rating       = lhg_get_rating_by_rating_id ($rating_id);
	$rating_image = lhg_get_rating_image_by_rating_id ($rating_id);

?>
<table class="widefat form-table editcomment" cellspacing="0">
<tbody>
	<tr class="alternate">
		<td class="textright">
			<label for="vb_comment_post_id"><?php _e('Page/Post ID'); ?></label>
		</td>
		<td>
			<input type="text" name="vb_comment_post_id" id="vb_comment_post_id" value="<?php echo esc_attr( $comment->comment_post_ID ); ?>" size="40" />
		</td>
	</tr>
	<tr>
		<td class="textright">
			<label for="vb_comment_parent_id"><?php _e('Comment Parent ID'); ?></label>
		</td>
		<td>
			<input type="text" name="vb_comment_parent_id" id="vb_comment_parent_id" value="<?php echo esc_attr( $comment->comment_parent ); ?>" size="40" />
		</td>
	</tr>
	<tr class="alternate">
		<td class="textright">
			<label for="vb_comment_user_id"><?php _e('Comment Author ID'); ?></label>
		</td>
		<td>
			<input type="text" name="vb_comment_user_id" id="vb_comment_user_id" value="<?php echo esc_attr( $comment->user_id ); ?>" size="40" />
		</td>
	</tr>

	<tr class="alternate">
		<td class="textright">
			<label for="vb_comment_user_id"><?php _e('Rating'); ?></label>
		</td>
		<td>
                        <?php

echo $rating_image;
if ($rating_image != "n.a.")
{
?>

                        <select name="vb_comment_rating">
                        <?php
                        lhg_rating_selector( $rating );
                        ?>
                        </select>
                        <?php
                        echo "ID: ".$rating_id;

}

                        ?>
		</td>
	</tr>

	<tr class="alternate">
		<td class="textright">
			<label for="vb_comment_user_id"><?php _e('Language'); ?></label>
		</td>
		<td>

                          <select name="vb_comment_language">
                          <?php lhg_language_selector ($comment_lang); ?>
                          </select>
			<?php //echo $comment_lang;

                        # check if translated article exists
                        global $lhg_price_db;
                        global $lang;

			if ($lang == "de") $sql = "SELECT `postid_com` FROM `lhgtransverse_posts` WHERE postid_de = %s";
			if ($lang != "de") $sql = "SELECT `postid_de` FROM `lhgtransverse_posts` WHERE postid_com = %s";
		        $safe_sql = $lhg_price_db->prepare($sql, $comment->comment_post_ID );
		        $trans_postid = $lhg_price_db->get_var($safe_sql);

                        if ($trans_postid > 0){
                        	print '<br><a href="../wp-admin/admin.php?movecomment&cid='.$comment->comment_ID.'">move comment to transverse server</a>';
                        }else{
                                if ($lang == "de") print "<br>Artikel kann nicht auf .com Server verschoben werden";
                                if ($lang != "de") print "<br>Article kann not be moved to .de server";
                        }
                        ?>
		</td>
	</tr>

</tbody>
</table>
<?php
}
function velvetblues_wmc_save($comment_content) {
    global $wpdb;
         
    $comment_post_ID = absint($_POST['vb_comment_post_id']);
    $comment_parent = absint($_POST['vb_comment_parent_id']);
    $user_id = esc_attr($_POST['vb_comment_user_id']);
    $comment_ID = absint($_POST['comment_ID']);
    $comment_language = $_POST['vb_comment_language'];
    $comment_rating = $_POST['vb_comment_rating'];

        //set language
	//update_comment_meta( $comment_ID, "language" , "com");
        $tmp   = get_comment_meta( $comment_ID, "language",true );
        if ($comment_language != $tmp) update_comment_meta( $comment_ID, "language" , $comment_language);
        //debug:
        //$comment_content .= "L: $comment_language";

        //set rating
        $rating_id  = get_comment_meta( $comment_ID, "rating-id",true );
	$rating_orig       = lhg_get_rating_by_rating_id ($rating_id);
        if ($comment_rating != $rating_orig) $out = lhg_update_comment_rating( $rating_id, $comment_rating);
        //$comment_content .= "RID1: $rating_id - OUT: $out";
        //$comment_content .= "- update ID $rating_id RT $comment_rating ORT $rating_orig";
 
    if ($comment_parent == $comment_ID)
        return $comment_content;
             
    $post = get_post($comment_post_ID);
    if (!$post)
		return $comment_content;
    
	$parent = get_comment($comment_parent);
	if(!$parent) $comment_parent = 0;
	
	$commentarr = get_comment($comment_ID, ARRAY_A);
	$prev_comment_post_ID = $commentarr['comment_post_ID'];     
    
	$data = compact('comment_post_ID', 'comment_parent', 'user_id');
    $rval = $wpdb->update( $wpdb->comments, $data, compact( 'comment_ID' ) );
         
    wp_update_comment_count($comment_post_ID);
	
	if( $prev_comment_post_ID != $comment_post_ID ){
		wp_update_comment_count( $prev_comment_post_ID );
		wp_update_comment_count( $comment_post_ID );
	}



	
    return $comment_content;
}

function velvetblues_wmc_links( $links, $file ) {
	if ( strpos( $file, 'vb-wp-move-comments.php' ) !== false ) {
		$links = array_merge( $links, array( '<a href="http://www.velvetblues.com/web-development-blog/wp-plugin-wordpress-move-comments/" title="Need help?">' . __('Support') . '</a>' ) );
		$links = array_merge( $links, array( '<a href="http://www.velvetblues.com/go/wpmovecommentsdonate/" title="Support plugin development">' . __('Donate') . '</a>' ) );
	}
	return $links;
}
 
function velvetblues_wmc_quick_edit_menu($str, $input) {
    extract($input);
    $table_row = TRUE;
    if ( $mode == 'single' ) {
        $wp_list_table = _get_list_table('WP_Post_Comments_List_Table');
    } else {
        $wp_list_table = _get_list_table('WP_Comments_List_Table');
    }
 
    ob_start();
        $quicktags_settings = array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close' );
    wp_editor( '', 'replycontent', array( 'media_buttons' => false, 'tinymce' => false, 'quicktags' => $quicktags_settings, 'tabindex' => 104 ) );
    $editorStr = ob_get_contents();
    ob_end_clean();
 
    ob_start();    
    wp_nonce_field( "replyto-comment", "_ajax_nonce-replyto-comment", false );
        if ( current_user_can( "unfiltered_html" ) )
        wp_nonce_field( "unfiltered-html-comment", "_wp_unfiltered_html_comment", false );
    $nonceStr = ob_get_contents();
    ob_end_clean();
 
    $content = '<form method="get" action="">';
    if ( $table_row ) :
        $content .= '<table style="display:none;"><tbody id="com-reply"><tr id="replyrow" style="display:none;"><td colspan="'.$wp_list_table->get_column_count().'" class="colspanchange">';
    else :
        $content .= '<div id="com-reply" style="display:none;"><div id="replyrow" style="display:none;">';
    endif;
 
    $content .= '
            <div id="replyhead" style="display:none;"><h5>'.__('Reply to Comment').'</h5></div>
            <div id="addhead" style="display:none;"><h5>'.__('Add new Comment').'</h5></div>
            <div id="edithead" style="display:none;">';
             
    $content .= '  
                <div class="inside">
                <label for="author">'.__('Name').'</label>
                <input type="text" name="newcomment_author" size="50" value="" id="author" />
                </div>
         
                <div class="inside">
                <label for="author-email">'.__('E-mail').'</label>
                <input type="text" name="newcomment_author_email" size="50" value="" id="author-email" />
                </div>
         
                <div class="inside">
                <label for="author-url">'.__('URL').'</label>
                <input type="text" id="author-url" name="newcomment_author_url" size="103" value="" />
                </div>
                <div style="clear:both;"></div>';
                       
    $content .= '
                <div class="inside">
                <label for="comment-post-id">'.__('Page/Post ID').'</label>
                <input type="text" id="comment-post-id" name="vb_comment_post_id" size="50" value="" />
                </div>
 
                <div class="inside">
                <label for="comment-parent">'.__('Comment Parent ID').'</label>
                <input type="text" id="comment-parent" name="vb_comment_parent_id" size="50" value="" />
                </div>
 
                <div class="inside">
                <label for="comment-author-id">'.__('Comment Author ID').'</label>
                <input type="text" id="comment-author-id" name="vb_comment_user_id" size="103" value="" />
                </div>
                <div style="clear:both;"></div>
 
            </div>
            ';
         
    $content .= "<div id='replycontainer'>\n";   
    $content .= $editorStr;
    $content .= "</div>\n";  
             
    $content .= '          
            <p id="replysubmit" class="submit">
            <a href="#comments-form" class="save button-primary alignright">
            <span id="addbtn" style="display:none;">'.__('Add Comment').'</span>
            <span id="savebtn" style="display:none;">'.__('Update Comment').'</span>
            <span id="replybtn" style="display:none;">'.__('Submit Reply').'</span></a>
			<a href="#comments-form" class="cancel button-secondary alignleft">'.__('Cancel').'</a>
            <span class="waiting spinner"></span>
            <span class="error" style="display:none;"></span>
            <br class="clear" />
            </p>';
             
        $content .= '
            <input type="hidden" name="user_ID" id="user_ID" value="'.get_current_user_id().'" />
            <input type="hidden" name="action" id="action" value="" />
            <input type="hidden" name="comment_ID" id="comment_ID" value="" />
            <input type="hidden" name="comment_post_ID" id="comment_post_ID" value="" />
            <input type="hidden" name="status" id="status" value="" />
            <input type="hidden" name="position" id="position" value="'.$position.'" />
            <input type="hidden" name="checkbox" id="checkbox" value="';
         
    if ($checkbox) $content .= '1'; else $content .=  '0';
    $content .= "\" />\n";
        $content .= '<input type="hidden" name="mode" id="mode" value="'.esc_attr($mode).'" />';
         
    $content .= $nonceStr;
    $content .="\n";
         
    if ( $table_row ) :
        $content .= '</td></tr></tbody></table>';
    else :
        $content .= '</div></div>';
    endif;
    $content .= "\n</form>\n";
    return $content;
}
 
function velvetblues_wmc_quick_edit_data($comment_text, $comment ) {
    ?>
        <div id="inline-vbaddl-<?php echo $comment->comment_ID; ?>" class="hidden">
        <div class="comment-post-id"><?php echo esc_attr( $comment->comment_post_ID ); ?></div>
        <div class="comment-parent"><?php echo esc_attr( $comment->comment_parent ); ?></div>
        <div class="comment-author-id"><?php echo esc_attr( $comment->user_id ); ?></div>
        </div>
        <?php
    return $comment_text;
}
 
function velvetblues_wmc_quick_edit_js() {
?>
    <script type="text/javascript">
    function expandedOpen(id) {
        editRow = jQuery('#replyrow');
        rowData = jQuery('#inline-vbaddl-'+id);
		jQuery('#comment-post-id', editRow).val( jQuery('div.comment-post-id', rowData).text() );
		jQuery('#comment-parent', editRow).val( jQuery('div.comment-parent', rowData).text() );
		jQuery('#comment-author-id', editRow).val( jQuery('div.comment-author-id', rowData).text() );
    }  
    </script>
   <?php
}
 
function velvetblues_wmc_quick_edit_action($actions, $comment ) {
    global $post;
    $actions['quickedit'] = '<a onclick="commentReply.close();if (typeof(expandedOpen) == \'function\') expandedOpen('.$comment->comment_ID.');commentReply.open( \''.$comment->comment_ID.'\',\''.$post->ID.'\',\'edit\' );return false;" class="vim-q" title="'.esc_attr__( 'Quick Edit' ).'" href="#">' . __( 'Quick&nbsp;Edit' ) . '</a>';
    return $actions;
}

function velvetblues_wmc_check_screen($screen) {
    if ( in_array( $screen->id, array('edit-comments','post','page')) ) {
        add_filter('wp_comment_reply', 'velvetblues_wmc_quick_edit_menu', 10, 2);
        add_filter('comment_text', 'velvetblues_wmc_quick_edit_data', 10, 2);
        add_filter('comment_row_actions', 'velvetblues_wmc_quick_edit_action', 10, 2);
        add_action('admin_footer', 'velvetblues_wmc_quick_edit_js');
    }
        return $screen;
}
?>