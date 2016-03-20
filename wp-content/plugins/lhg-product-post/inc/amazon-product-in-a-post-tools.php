<?php
// Tools
global $appipBulidBox;
//ACTIONS
	//Create the Meta Box for edits
        global $lang;
        $txt_amz_title = "Product Identification";
        if ($lang == "de") $txt_amz_title = "Produktkennung";

        //echo "<br><br>L: $lang TT: $txt_amz_title";
	add_action('admin_menu', create_function("$appipBulidBox","if( function_exists( 'add_meta_box' ))add_meta_box( 'amazonProductInAPostBox1', __( '$txt_amz_title', 'appplugin' ), 'amazonProductInAPostBox1', 'post', 'normal', 'high' );"));
	//add_action('admin_menu', create_function("$appipBulidBox","if( function_exists( 'add_meta_box' ))add_meta_box( 'amazonProductInAPostBox1', __( 'Amazon Product In a Post Settings', 'appplugin' ), 'amazonProductInAPostBox1', 'page', 'normal', 'high' );"));
	//Create the Admin Menus
	add_action('admin_menu', 'apipp_plugin_menu');
//FUNCTIONS
	//Custom Save Post items for Quick Add
	if(isset($_POST['createpost'])){ //form saved
		if(isset($_POST['post_category_count'])){
			$totalcategories = $_POST['post_category_count'];
			for($i=0;$i<=$totalcategories;$i++){
					$teampappcats[$i] = $_POST['post_category'.$i];
			}
		}
		$_POST['post_category']=$teampappcats;
		ini_set('display_errors', 1);
		$createdpostid = wp_insert_post($_POST, "false");
		amazonProductInAPostSavePostdata($createdpostid,$post);
		header("Location: admin.php?page=apipp-add-new&appmsg=1");
	}else{
		add_action('save_post', 'amazonProductInAPostSavePostdata', 1, 2); // save the custom fields
	}
	
	/* Prints the inner fields for the custom post/page section */
	function amazonProductInAPostBox1() {
		global $post;
                global $lang;

		// Use nonce for verification ... ONLY USE ONCE!
		echo '<input type="hidden" name="amazonpipp_noncename" id="amazonpipp_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
		echo '<input type="hidden" name="post_save_type_apipp" id="post_save_type_apipp" value="1" />';
		// The actual fields for data entry
		if(get_option('apipp_amazon_associateid')==''){
			echo '<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade below-h2"><p><b>WARNING:</b> You will not get credit for Amazon purchases until you add your Amazon Affiliate ID on the <a href="admin.php?page=apipp_plugin_admin">options</a> page.</p></div>';
		}
                echo '<div style="border: solid; display: none;">';

		echo '<label for="amazon-product-isactive"><b>' . __("Product is Active?", 'appplugin' ) . '</b></label> ';
		//if(get_post_meta($post->ID, 'amazon-product-isactive', true)!=''){$menuhide="checked";}else{$menuhide="";}
		$menuhide="checked";
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-isactive" value="1" '.$menuhide.' /> <i>if checked the product will be live</i><br /><br />';
		echo '<label for="amazon-product-content-hook-override"><b>' . __("Hook into Content?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-content-hook-override', true)=='2' || (get_post_meta($post->ID, 'amazon-product-content-hook-override', true)=='' && get_option('apipp_hook_content')==true)){$hookcontent="checked";}else{$hookcontent="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-content-hook-override" value="2" '.$hookcontent.' /> <i>if checked the product will be added when <code>the_content()</code> is used. On by default unless set in options.</i><br /><br />';
		echo '<label for="amazon-product-excerpt-hook-override"><b>' . __("Hook into Excerpt?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-excerpt-hook-override', true)=='2' || (get_post_meta($post->ID, 'amazon-product-excerpt-hook-override', true)=='' && get_option('apipp_hook_excerpt')==true)){$hookexcerpt="checked";}else{$hookexcerpt="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-excerpt-hook-override" value="2" '.$hookexcerpt.' /> <i>if checked the product will be added to the EXCERPT when <code>the_excerpt()</code> is used. Off by default unless set in options.</i><br /><br />';
		echo '<label for="amazon-product-singular-only"><b>' . __("Show Only on Single Page?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-singular-only', true)=='1'){$singleonly="checked";}else{$singleonly="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-singular-only" value="1" '.$singleonly.' /> <i>if checked the product will only show when in single view. In other words, only when on the permalink page. Off by default.</i><br /><br />';
		echo '<label for="amazon-product-newwindow"><b>' . __("Open Product Link in New Window?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-newwindow', true)=='2' || (get_post_meta($post->ID, 'amazon-product-newwindow', true)=='' && get_option('apipp_open_new_window')==true)){$newwin="checked";}else{$newwin="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-newwindow" value="2" '.$newwin.' /> <i>if checked the product will open a new browser window. Off by default unless set in options.</i><br /><br />';
		echo '<label for="amazon-product-showusbutton"><b>' . __("Show Amazon.com button along with Local button?", 'appplugin' ) . '</b></label> ';
		if(get_post_meta($post->ID, 'amazon-product-showusbutton', true)=='1' || (get_post_meta($post->ID, 'amazon-product-showusbutton', true)=='' && get_option('apipp_open_showusbutton')==true)){$newwin="checked";}else{$newwin="";}
		echo '<br /><br />&nbsp;&nbsp;<input type="checkbox" name="amazon-product-showusbutton" value="2" '.$newwin.' /> <i>if checked and your Locale is set to anything other than US(.com), an addtional Buy button with Amazon.com will be shown. Off by default.</i><br /><br />';
	
		echo '<label for="amazon-product-content-location"><b>' . __("Where would you like your product to show within the post?", 'appplugin' ) . '</b></label>';
		echo '<br /><br />&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="1" '. ((get_post_meta($post->ID, 'amazon-product-content-location', true)==='1') || (get_post_meta($post->ID, 'amazon-product-content-location', true)=='') ? "checked" : '') .' /> Above Post Content - <i>Default - Product will be first then post text</i><br />';
		echo '&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="3" '. ((get_post_meta($post->ID, 'amazon-product-content-location', true)==='3') ? "checked" : '') .' /> Below Post Content - <i>Post text will be first then the Product</i><br />';
		echo '&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="2" '. ((get_post_meta($post->ID, 'amazon-product-content-location', true)==='2') ? "checked" : '') .' /> Post Text becomes Description - <i>Post text will become part of the Product layout</i><br /><br />';
                echo '</div>';

                global $txt_amz_asin;
		echo '<br /><label for="amazon-product-single-asin"><b>' . __($txt_amz_asin, 'appplugin' ) . '</b></label> ';

                global $txt_amz_getfrom;
		echo '<br />&nbsp;&nbsp;<input type="text" name="amazon-product-single-asin" id="amazon-product-single-asin" value="'.get_post_meta($post->ID, 'amazon-product-single-asin', true).'" /> <i>'.$txt_amz_getfrom.'</i><br /><br />';


                if ( ($lang != "de") and (current_user_can("delete_posts") ) ){ //allow to update identification markers (only on com a.t.m.)

                	$library_usbid = lhg_get_usbid($post->ID);
	                $library_pciid = lhg_get_pciid($post->ID);
        	        $library_idstrg = lhg_get_idstrg($post->ID);
                	$txt_amz_usbid = "separated by comma, if several USB IDs";
	                $txt_amz_pciid = "separated by comma, if several PCI IDs";

                	echo '<label for="product-usbid"><b>USB ID</b></label>
	                      <br>&nbsp;&nbsp;<input type="text" name="product-library-usbid" id="product-library-usbid" value="'.$library_usbid.'"> <i>'.$txt_amz_usbid.'</i><br />';

        	        echo '<br /> <label for="product-pciid"><b>PCI ID</b></label>
                	      <br>&nbsp;&nbsp;<input type="text" name="product-library-pciid" id="product-library-pciid" value="'.$library_pciid.'"> <i>'.$txt_amz_pciid.'</i><br />';

	                echo '<br /> <label for="product-string"><b>Identification String</b>
        	              <br>&nbsp;&nbsp;<input type="text" name="product-library-idstrg" id="product-library-idstrg" value="'.$library_idstrg.'"> <i>'.$txt_amz_idstrg.'</i><br />';
		}

	
	}
	
	/* When the post is saved, saves our custom data */
	function amazonProductInAPostSavePostdata($post_id, $post) {
		if($post_id==''){$post_id=$post->ID;}
		/*
		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ))
				return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ))
				return $post_id;
		}
		*/
		if(!isset($_POST['post_save_type_apipp'])){return;}
		$mydata['amazon-product-isactive'] = $_POST['amazon-product-isactive'];
		$mydata['amazon-product-content-location'] = $_POST['amazon-product-content-location'];
		$mydata['amazon-product-single-asin'] = $_POST['amazon-product-single-asin'];
		$mydata['amazon-product-excerpt-hook-override'] = $_POST['amazon-product-excerpt-hook-override'];
		$mydata['amazon-product-content-hook-override'] = $_POST['amazon-product-content-hook-override'];
		$mydata['amazon-product-newwindow'] = $_POST['amazon-product-newwindow'];
		//$mydata['amazon-product-showusbutton'] = $_POST['amazon-product-showusbutton'];
		$mydata['amazon-product-singular-only'] = $_POST['amazon-product-singular-only'];
		if($mydata['amazon-product-isactive']=='' && $mydata['amazon-product-single-asin']==""){$mydata['amazon-product-content-location']='';}
		if($mydata['amazon-product-excerpt-hook-override']==''){$mydata['amazon-product-excerpt-hook-override']='3';}
		if($mydata['amazon-product-content-hook-override']==''){$mydata['amazon-product-content-hook-override']='3';}
		if($mydata['amazon-product-newwindow']==''){$mydata['amazon-product-newwindow']='3';}
		
		// Add values of $mydata as custom fields
		foreach ($mydata as $key => $value) { //Let's cycle through the $mydata array!
			if( $post->post_type == 'revision' ) return; //don't store custom data twice
			$value = implode(',', (array)$value); //if $value is an array, make it a CSV (unlikely)
			if(get_post_meta($post_id, $key, FALSE)) { //if the custom field already has a value
				update_post_meta($post_id, $key, $value);
			} else { //if the custom field doesn't have a value
				add_post_meta($post_id, $key, $value);
			}
			if(!$value) delete_post_meta($post_id, $key); //delete if blank
		}
	}
	
	function apipp_plugin_menu() {
		global $fullname_apipp, $shortname_apipp, $options_apipp;
		apipp_options_add_admin_page($fullname_apipp,$shortname_apipp,$options_apipp);
	  	add_menu_page('Amazon Page In a Post New', 'Amazon PIP', 8, 'apipp-add-new', 'apipp_add_new_post');
	  	add_submenu_page('apipp-add-new', $fullname_apipp." Options", "Amazon PIP Options", 8 , $shortname_apipp."_plugin_admin", 'apipp_options_add_subpage');
	  	//add_submenu_page('apipp-add-new', 'New Product Post', 'New Product Post', 8, 'apipp-add-new', 'apipp_add_new_post');
	}
	
	function apipp_add_new_post(){
	global $user_ID;
	global $current_user;
	get_currentuserinfo();
    $myuserpost = $current_user->ID;
		echo '<div class="wrap"><div id="icon-amazon" class="icon32"><br /></div><h2>Add New Amazon Product Post</h2>';
		if($_GET['appmsg']=='1'){	echo '<div style="background-color: rgb(255, 251, 204);" id="message" class="updated fade below-h2"><p><b>Product post has been saved. To edit, use the standard Post Edit options.</p></div>';}
		echo '<br />This function will allow you to add a new post for an Amazon Product - no need to create a post then add the ASIN.<br />Once you add a Product Post, you can edit the information with the normal Post Edit options.<br />';
		?>	<form method="post" action="">
				<input type="hidden" name="amazon-product-isactive" id="amazon-product-isactive" value="1" />
				<input type="hidden" name="post_type" id="post_type" value="post" />
				<input type="hidden" name="post_author" id="post_author" value="<?php echo $myuserpost;?>" />
				<div align="center">
					<table border="0" cellpadding="2" cellspacing="0" width="100%">
						<tr>
							<td align="left" valign="top">Title</td>
							<td align="left"><input type="text" name="post_title" size="65" /></td>
						</tr>
						<tr>
							<td align="left" valign="top">Post Status</td>
							<td align="left"><select size="1" name="post_status" >
							<option selected>draft</option>
							<option>publish</option>
							<option>private</option>
							</select></td>
						</tr>
						<tr>
							<td align="left" valign="top">Amazon Product ASIN Number</td>
							<td align="left"><input type="text" name="amazon-product-single-asin" size="29" /> 
							(may also be called ISBN-10)</td>
						</tr>
						<tr>
							<td align="left" valign="top">Post Content</td>
							<td align="left">
							<textarea rows="11" name="post_content" id="post_content_app" cols="56"></textarea></td>
						</tr>
						<tr>
							<td align="left" valign="top">&nbsp;</td>
							<td align="left">&nbsp;</td>
						</tr>
						<tr>
							<td align="left" valign="top">Product Location</td>
							<td align="left">
					&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="1"  checked /> Above Post Content - <i>Default - Product will be first then post text</i><br />
					&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="3" /> Below Post Content - <i>Post text will be first then the Product</i><br />
					&nbsp;&nbsp;<input type="radio" name="amazon-product-content-location" value="2" /> Post Text becomes Description - <i>Post text will become part of the Product layout</i><br />
			</td>
						</tr>
						<tr>
							<td align="left" valign="top">&nbsp;</td>
							<td align="left">&nbsp;</td>
						</tr>
						<tr>
							<td align="left" valign="top">Post Category</td>
							<td align="left"><?php 
									$categories = get_categories('hide_empty=0');	
									$ii=0;
									foreach($categories as $cat) {
										echo '&nbsp;&nbsp;<input type="checkbox" name="post_category'.$ii,'" value="' . $cat->cat_ID . '" /> ' . $cat->cat_name . '<br />';
										$ii=$ii+1;
									} 
								 ?>
									<input type="hidden" name="post_category_count" value="<?php echo $ii-1;?>" />
							</td>
						</tr>
						<tr>
							<td align="left" valign="top">&nbsp;</td>
							<td align="left">&nbsp;</td>
						</tr>
						<tr>
							<td align="left" valign="top">&nbsp;</td>
							<td align="left">
							<input type="submit" value="Create Post" name="createpost" /></td>
						</tr>
					</table>
				</div>
			</form>
			</div>
		<?php }
?>