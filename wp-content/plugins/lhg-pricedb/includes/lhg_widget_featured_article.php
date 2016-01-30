<?php

/*
main page featured article
*/


add_action( 'widgets_init', function(){register_widget( 'lhg_widget_featured_article' ); } );


#'lhg_register_widgets');

#function lhg_register_widgets(){
#        register_widget('lhg_widget_featured_article');
#}

#register_widget('lhg_widget_featured_article');

class lhg_widget_featured_article extends WP_Widget{

        #function __construct() {
	#	parent::__construct(
	#		'lhg_widget_featured_article', // Base ID
	#		__( 'Featured Article', 'text_domain' ), // Name
	#		array( 'description' => __( 'Show featured article', 'text_domain' ) ) // Args
	#	);
	#}

	#public $defaults = array();


        function lhg_widget_featured_article(){
        	$widget_ops = array(
	        'classname' => 'lhg_widget_featured_article_class',
        	'description' => 'Show featured article'
	        );

                parent::WP_Widget( 'lhg_widget_featured_article', $name = 'Featured Article', $widget_ops );
        }

        // widget settings
        public function form($lhg_instance){
                #echo "OLD: ";
                #var_dump($lhg_instance);

                #echo "<br>";
                $lhg_defaults = array(
                  'title' => 'please reload',
                  'artid' => "please reload"
                );

                if ($lhg_instance['title'] == "") $lhg_instance['title']=$lhg_defaults['title'];
                if ($lhg_instance['artid'] == "") $lhg_instance['artid']=$lhg_defaults['artid'];

                #$instance = wp_parse_args( $lhg_instance, $lhg_defaults );
                #echo "DEF:<br>";
                #var_dump($lhg_defaults);
                #echo "<br>FIN:<br>";
                #extract($instance);
                #var_dump($lhg_instance);

                $title = $lhg_instance['title'];
                $artid = $lhg_instance['artid'];
                ?>
                        <p>Title:
                        <input id="<?php echo $this->get_field_id('title'); ?>"
                        class="widefat"
                        type="text" name="<?php echo $this->get_field_name('title'); ?>"
                        value="<?php echo $title; ?>" />


                        <p>Post ID:
                        <input id="<?php echo $this->get_field_id('artid'); ?>"
                        class="widefat"
                        type="text" name="<?php echo $this->get_field_name('artid'); ?>"
                        value="<?php echo $artid; ?>" />


                <?php
        }

        // save widget settings
        public function update($new_instance, $old_instance){
                $lhg_instance = $old_instance;
                #var_dump($old_instance);
                $lhg_instance['title'] = strip_tags( $new_instance['title'] );
                $lhg_instance['artid'] = strip_tags( $new_instance['artid'] );

                return $lhg_instance;
        }

        //display widget
        public function widget( $args, $lhg_instance ) {


                //show only for english pages (missing translation of articles)
                // ToDo: Check if translation exists, otherwise hide
                global $region;
                if ( ($region == "fr") or ($region == "es")
                or ($region == "it") or ($region == "nl") or ($region == "co.jp")
                or ($region == "cn")
                ) return;

                #echo "INS: ".$instance['title'];
                #var_dump($lhg_instance);

                $title = apply_filters( 'widget_title', $lhg_instance['title']);
                $artid = $lhg_instance['artid'];
                $default_image = "";

                $text = get_post_field('post_content',$artid);
                $text = lhg_clean_extract($text,600);
                $more = "(more)";

                $posttitle = translate_title( get_the_title($artid) );
		$s=explode("(",$posttitle);
		$short_title=trim($s[0]);
		$sub_title=trim($s[1]);
                $sub_title=str_replace(")","",$sub_title);

                //get the latest date

                global $wpdb;
                $sql = "
                SELECT post_modified
                FROM $wpdb->posts
                WHERE ID = ".$artid;

		$last_update = $wpdb->get_var($sql);
                $wpdf=get_option('date_format');
                $lupdate = date($wpdf,strtotime($last_update));

                // get comments
                $cn=lhg_get_comments_number($artid);

        	if ($cn > 0) {
	                //Comment tooltip
        	        $txt_comments=$txt_cpl_comments; //"Kommentare";
        		//if ($lang == "en") $txt_comments="comments";

	                if ($cn == 1) {
		        	$txt_comments=$txt_cpl_comment;//"Kommentar";
        			//if ($lang == "en") $txt_comments="comment";
	                }

	                $coutInv=''; //<span class="comment-count-out-invisible"></span>';
        		$cout='<a href="'.$arturl.'#comments" alt="'.($cn).' '.$txt_comments.'" title="'.($cn).' '.$txt_comments.'">
                	        <div class="comment-count-out">
                        	  <span class="comment-bubble"></span>
	                           <span class="comment-count">'.($cn).'</span>
        	                </div>
                	       </a>';
	        }else{
        	        $coutInv="";
                	$cout="";
	        }

               //ratings
		$ratings = the_ratings_results($artid,0,0,0,10);



		#var_dump($last_update);
                #var_dump($wpdf);

    		#$sql = "SELECT post_modified
		#        FROM $wpdb->posts
                #        WHERE post_id ='".$artid."'";
		#$last_update = $wdpb->get_var( $sql );
    		#echo( "Last updated $lupdate ." );

                #$date = get_the_modified_date($artid)

                $permalink = get_permalink($artid);
                $image = get_the_post_thumbnail($artid, array(130,130) );

                #echo "ARGS: $args";
                #var_dump($args);
                extract($args);

                #var_dump($instance);
                #echo "TIT: ".$instance['title'];

                echo "$before_widget";
                #$title = apply_filters ( 'widget_title', $instance['title'] );
                #$artid = empty( $instance['artid'] ) ? '&nbsp;' : $instance['artid'];

                #if ( !empty( $title ) ) {
                echo $before_title . $title . $after_title;
                #};

                echo '<div class="featured-hw">';

		#echo '<div class="featured-title-date">'.$lupdate.':</div>';

                echo '<div class="featured-title"><a href="'. $permalink . '">'.$short_title .'</a></div>
                <span class="featured-comments">'.$cout.'</span>
                <span class="featured-ratings">'.$ratings.'</span>

                <div class="featured-title-sub"><a href="'. $permalink . '">('.$sub_title .')</a></div>
                <div class="featured-image"><a href="'.$permalink.'">'.$image.'</a></div>
                '.
                $text .'... <a href="'.$permalink.'">'.$more.'</a></p>';
                echo "</div>";
                echo $after_widget;
        }


}

#register_widget('lhg_widget_featured_article');


function lhg_clean_extract($text,$length) {

        $text = wp_strip_all_tags ( $text );

        $search1='/\[code.*\]/';
        $search2='/\[\/code*\]/';
        $text = preg_replace($search1,"",$text);
        $text = preg_replace($search2,"",$text);
        $text = substr($text,0,$length);
        return $text;
}

