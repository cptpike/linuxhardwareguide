<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>


		<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
			<h1 class="content-title">
			<span style="font-family: PTSansBold;">
                        <?php
                        if ($region != "de") {
                        	$title = translate_title(qtrans_use('en',get_the_title(),false));
			}else{
                        	$title = get_the_title();
                        }

                        $s=explode("(",$title);
			$short_title1=trim($s[0]);
			$short_title2="(".trim($s[1]);

                        //check for empty part2
                        if ($short_title2 == "(") $short_title2 = "";

                        echo $short_title1;
			?>
                        </span><?php echo $short_title2; ?></h1>
<p class="meta">
<!-- span class="date"><a href="<?php the_permalink() ?>"><?php the_time( get_option( 'date_format' ) ) ?></a></span> <span class="author"><?php the_author() ?></span -->

<span class="cats">

<?php
$the_cat = get_the_category();
$category_name = $the_cat[0]->cat_name;
$category_name2 = $the_cat[1]->cat_name;
$category_link = get_category_link( $the_cat[0]->cat_ID );
$category_link2 = get_category_link( $the_cat[1]->cat_ID );
?>

<a href="<?php echo $category_link; ?>"><?php echo $category_name; $category_name2; ?></a>
</span>

<span class="tags">
<?php
$posttags = get_the_tags();
$count=0;
if ($posttags) {
	foreach($posttags as $tag) {
        	# filter "featured articles" - used for rss feed of special articles only
                if ($tag->term_id != 981 ) {
			$count++;
			$tagstring .=  '<a href="'.get_tag_link($tag->term_id).'">'.$tag->name.'</a>, ';
			if( $count >8 ) break;
		}
	}
}
echo substr($tagstring,0,-2);
?>
</span></p>

				<?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>');

                                ?>

                                <?php //closing of rich snippet

                                ?>

				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>				
										
				<?php edit_post_link('Edit this entry.', '<p class="more">', '</p>'); ?>
		</div>

<?php wp_carousel(0); ?>

	<?php comments_template(); ?>

	<?php endwhile; endif; ?>




		<?php get_sidebar(); ?>


<?php get_footer(); ?>
