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


// Add buttons to html editor
add_action('admin_print_footer_scripts','eg_quicktags');
function eg_quicktags() {

if (isset($_GET['scansid'] )) {
        global $lhg_price_db;
        global $dmesg_content_from_library;

	$sid = $_GET['scansid'];

        $myquery = $lhg_price_db->prepare("SELECT * FROM `lhgscansessions` WHERE sid = %s", $sid);
       	$results = $lhg_price_db->get_results($myquery);

        $kernel       = $results[0]->kversion;
        $distribution = $results[0]->distribution;
	$distribution = str_replace("\n","",$distribution);

        if ( $dmesg_content_from_library == "" ) {
		$url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=dmesg.txt";
  		$dmesg_content_from_library = file_get_contents($url);
	}

	$dmesg_content_array = split("\n",$dmesg_content_from_library);
	$dmi_array = preg_grep("/DMI: /",$dmesg_content_array);
	$dmi_line = implode("\n",$dmi_array);
	$dmi_line = str_replace("[    0.000000]","",$dmi_line);
	$dmi_line = str_replace("#"," ",$dmi_line);
	$dmi_line = str_replace("\n"," ",$dmi_line);

	$xorg_server_array = preg_grep("/X.Org X Server /",$dmesg_content_array);
        $xorg_server = implode(" ",$xorg_server_array);
	$xorg_server = str_replace("\n"," ",$xorg_server);

        $nvidia_module_array = preg_grep("/NVIDIA GLX Module /",$dmesg_content_array);
        $nvidia_module = implode(" ", $nvidia_module_array);
	$nvidia_module = str_replace("\n"," ",$nvidia_module);
        # clean string
        $tmp = explode("(II) ",$nvidia_module);
        $nvidia_module = substr($tmp[1],0,27);
        #error_log("NVmod: $nvidia_module");

        if ($nvidia_module != "") $nvtxt = 'nvidia_module="'.$nvidia_module.'"';

        $graphicscard_text = '[lhg_graphicscard distribution="'.$distribution.'" version="'.$kernel.'" xserver="'.$xorg_server.'" '.$nvtxt.']';

}

print '
<script type="text/javascript" charset="utf-8">
buttonA = edButtons.length;
edButtons[edButtons.length] = new edButton(\'ed_mainboard\',\'Mainboard\',\'[lhg_mainboard_intro distribution="'.$distribution.'" version="'.$kernel.'" dmi_output="'.$dmi_line.'"]\n\n[lhg_mainboard_lspci] \n[/lhg_mainboard_lspci]\',\'\',\'Mainboard shortcode\');
buttonB = edButtons.length;
edButtons[edButtons.length] = new edButton(\'ed_hplip\',\'hplip\',\'[lhg_hplip version="" usb="" url=""]\',\'\',\'hplip shortcode\');
buttonC = edButtons.length;
edButtons[edButtons.length] = new edButton(\'ed_graphicscard\',\'Graphics Card\',\''.$graphicscard_text.'\',\'\',\'Graphics Card\');

jQuery(document).ready(function($){
    jQuery("#ed_toolbar").append(\'<input type="button" value="Mainboard" id="ed_mainboard" class="ed_button" onclick="edInsertTag(edCanvas, buttonA);" title="Mainboard shortcode" />\');
    jQuery("#ed_toolbar").append(\'<input type="button" value="hplip" id="ed_hplip" class="ed_button" onclick="edInsertTag(edCanvas, buttonB);" title="hplip" />\');
    jQuery("#ed_toolbar").append(\'<input type="button" value="Graphics Card" id="ed_graphicscard" class="ed_button" onclick="edInsertTag(edCanvas, buttonC);" title="hplip" />\');
}); 
</script>
';
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
		#echo '<br />&nbsp;&nbsp;<input type="text" name="amazon-product-single-asin" id="amazon-product-single-asin" value="'.get_post_meta($post->ID, 'amazon-product-single-asin', true).'" /> <i>'.$txt_amz_getfrom.'</i><br /><br />';



                        //
                        // --- Image selector window
                        //
                	print ' <div id="more-image-options" style="display:none;">';
                        print "<p>";
                        print "Select image:<br>";
                        #if ($image_url_com != "") print '1: <a href="#" id="altimg-1"><img id="imgsrc-1" src="'.$image_url_com.'"></a><br>';
                        #if ($image_url_com2 != "") print '2: <a href="#" id="altimg-2"><img id="imgsrc-2" src="'.$image_url_com2.'"></a><br>';
                        #if ($image_url_com3 != "") print '3: <a href="#" id="altimg-3"><img id="imgsrc-3" src="'.$image_url_com3.'"></a><br>';
                        #if ($image_url_com4 != "") print '4: <a href="#" id="altimg-4"><img id="imgsrc-4" src="'.$image_url_com4.'"></a><br>';
                        print '<div id="imgsel-1" class="image-selector-az">1: <a href="#" id="altimg-1"><img id="imgsrc-1" src="'.$image_url_com.'" class="selector-images"></a></div><br>';
                        print '<div id="imgsel-2" class="image-selector-az">2: <a href="#" id="altimg-2"><img id="imgsrc-2" src="'.$image_url_com2.'" class="selector-images"></a></div><br>';
                        print '<div id="imgsel-3" class="image-selector-az">3: <a href="#" id="altimg-3"><img id="imgsrc-3" src="'.$image_url_com3.'" class="selector-images"></a></div><br>';
                        print '<div id="imgsel-4" class="image-selector-az">4: <a href="#" id="altimg-4"><img id="imgsrc-4" src="'.$image_url_com4.'" class="selector-images"></a></div><br>';

                        print '<br clear="all"><hr>
                                <div class="imageurl-box"><div class="imageurl-text">Image URL:</div>
			';
                        print '<form action="?" method="post">
			         <input name="image-url" id="image-url" type="text" size="30" maxlength="500" value="">
			         <input type="submit" id="image-url-submit" value="Submit image" class="button" />
 			       </form>
                               </div>
                               ';

                        print "</p>";
                        print '</div>';




        // jQuery code for image selection

			print'

                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {

                	$("#more-image-options-link").click(function() {

                		 if ( $("#imgsrc-1").attr("src") === "" ) {
	                        	$("#imgsel-1").hide();
	                         }else{
		                        $("#imgsel-1").show();
                	         }
                		 if ( $("#imgsrc-2").attr("src") === "" ) {
		                        $("#imgsel-2").hide();
        	                 }else{
	        	                $("#imgsel-2").show();
                        	 }
	                	 if ( $("#imgsrc-3").attr("src") === "" ) {
		                        $("#imgsel-3").hide();
                	         }else{
	                	        $("#imgsel-3").show();
	                         }
        	        	 if ( $("#imgsrc-4").attr("src") === "" ) {
	        	                $("#imgsel-4").hide();
                        	 }else{
		                        $("#imgsel-4").show();
        	                 }
                        });



                            $("[id^=altimg-]").click(function() {

                                var id = $(this).attr(\'id\').substring(7);

                                // "we are processing" indication
                                //var indicator_html = \'<img class="scan-load-button" id="button-load-known-hardware-comment" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                                //$(button).after(indicator_html);


                                //prepare Ajax data:
                                var url = $("#imgsrc-"+id).attr("src");
                                var data ={
                                        action: \'lhg_update_teaser_image_ajax\',
                                        postid: '.$post->ID.',
                                        id: id,
                                        url: url
                                };


                                //load & show server output
                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                        //$(button).append("Response");
                                        //$(button).after(response);
                                        //$(box).append("Response: <br>IMG: "+imageurl+" <br>text: "+responsetext);

                                        //return to normal state
                                        //$(button).val("Update");
                                        //$(button).attr("class", "hwscan-comment-button-light");
                                        //var indicatorid = "#button-load-known-hardware-comment";
                                        //$(indicatorid).remove();

                                        var new_image     = $(response).find("supplemental file").text();
                                        $("#amz-info-reload-box").append("<img src=\"/wp-uploads/"+new_image+"\">");

                                        tb_remove();


                                });

	                    return false;

                            });



                            // Image URL button
                            $("#image-url-submit").click(function() {

                                        var button = this;
	                                // "we are processing" indication
        	                        var indicator_html = \'<img class="scan-load-button" id="button-load-known-hardware-comment" src="'.$urlprefix.'/wp-uploads/2015/11/loading-circle.gif" />\';
                	                $(button).after(indicator_html);


                                //prepare Ajax data:
                                var url = $("#image-url").val();
                                var data ={
                                        action: \'lhg_update_teaser_image_ajax\',
                                        postid: '.$post->ID.',
                                        url: url
                                };


                                //load & show server output
                                $.get(\'/wp-admin/admin-ajax.php\', data, function(response){

                                        //$(button).append("Response");
                                        //$(button).after(response);
                                        //$(box).append("Response: <br>IMG: "+imageurl+" <br>text: "+responsetext);

                                        //return to normal state
                                        //$(button).val("Update");
                                        //$(button).attr("class", "hwscan-comment-button-light");
                                        //var indicatorid = "#button-load-known-hardware-comment";
                                        //$(indicatorid).remove();

                                        var new_image     = $(response).find("supplemental file").text();
                                        $("#amz-info-reload-box").append("<img src=\"/wp-uploads/"+new_image+"\">");

                                        tb_remove();
                                });


                                //prevent default behavior
                                return false;

                            });


                });



                /*]]> */

                </script>';





echo '
                 <script type="text/javascript">
                 /* <![CDATA[ */

                 jQuery(document).ready( function($) {

                               $(\'a.ajax-amazon-update\').click(function(){

                                 //var link = this;
                                 var link = $("#amz-info-reload-box");

                                 //change link text
                                 $(link).html(\'<img src="/wp-uploads/2015/11/loading-circle.gif">\');

                                 var asin = $("#amazon-product-single-asin").val();
                                 //var asin = 55x;

                                 var post_id = '.$post->ID.';

                                 //prepare Ajax data:
                                 var data ={
                                         action: \'lhg_amazon_update_ajax\',
                                         post_id:  post_id,
                                         test: 5,
                                         asin: asin
                                 };


                                 //load & show server output
                                 $.get(\'/wp-admin/admin-ajax.php\', data, function(data){
                                         //$(link).after(data).remove;
                                         $(link).html(data);

                                 });

                                 //prevent default behavior
                                 return false;

			       });

                                $(\'a.ajax-amazon-update\').trigger(\'click\');

			 });

                 /*]]> */
                 </script>


                 <br />&nbsp;&nbsp;<input type="text" name="amazon-product-single-asin" id="amazon-product-single-asin" value="'.get_post_meta($post->ID, 'amazon-product-single-asin', true).'" /> <a href="" class="ajax-amazon-update button"><i class="icon-refresh"></i>&nbsp;Refresh</a> <div id="amz-info-reload-box">Empty</div> <i>'.$txt_amz_getfrom.'</i><br /><br />';

                #if ($image_url_com2 != "") {
                #always active due to jQuery event that are linked.
                #however, his hidden if no images were loaded
                #add_thickbox();
                #}


                if ( ($lang != "de") and (current_user_can("delete_posts") ) ){ //allow to update identification markers (only on com a.t.m.)


                	$library_usbid = lhg_get_usbid($post->ID);
	                $library_pciid = lhg_get_pciid($post->ID);
        	        $library_idstrg = lhg_get_idstrg($post->ID);
                	$txt_amz_usbid = "separated by comma, if several USB IDs";
	                $txt_amz_pciid = "separated by comma, if several PCI IDs";

                	echo '<label for="product-usbid"><b>USB ID</b></label>
	                      <br>&nbsp;&nbsp;<input type="text" name="product-library-usbid" id="product-library-usbid" value="'.$library_usbid.'"> <i>'.$txt_amz_usbid.'</i>';

                        // show USB Inof
			global $lsusb_content_from_library;
			$sid = $_GET['scansid'];
                        if ($sid != "") {
                                // initiated by scan results. show usb output
			        if ( $lsusb_content_from_library == "" ) {
					$url="http://library.linux-hardware-guide.com/showdata.php?sid=".$sid."&file=lsusb.txt";
			  		$lsusb_content_from_library = file_get_contents($url);
				}

	                        print '<a href="#TB_inline?width=700&height=550&inlineId=lsusb-window" class="thickbox ajax-usb-update ">&nbsp;Show USB info</a>';
                                print ' <div id="lsusb-window" style="display:none;">';
	                        print "<p>";
        	                print "lsusb output:<br>";
                        	print '<pre>';
                                print $lsusb_content_from_library;
                                print '</pre>';
                                print "</div>";
                        }


                        print '

                              <br />';

        	        echo '<br /> <label for="product-pciid"><b>PCI ID</b></label>
                	      <br>&nbsp;&nbsp;<input type="text" name="product-library-pciid" id="product-library-pciid" value="'.$library_pciid.'">

                              <a href="#TB_inline?width=700&height=550&inlineId=modal-window-id" class="thickbox ajax-pciid-update button"><i class="icon-refresh"></i>&nbsp;Select PCI IDs</a>
                              <i>'.$txt_amz_pciid.'</i><br />';

	                echo '<br /> <label for="product-string"><b>Identification String</b>
        	              <br>&nbsp;&nbsp;<input type="text" name="product-library-idstrg" id="product-library-idstrg" value="'.$library_idstrg.'"> <i>'.$txt_amz_idstrg.'</i><br />';






                        //
                        // ----  Content of PCI selector window
                        //
                        wp_enqueue_style('admin-styles', '/wp-content/plugins/lhg-pricedb/css/backend.css');
                        echo ' <div id="modal-window-id" style="display:none;">';


                        
                        if (isset($_GET['scansid'] )) $sid = $_GET['scansid'];
                        #error_log("SID: $sid");

                        global $lhg_price_db;
                        $myquery = $lhg_price_db->prepare("SELECT * FROM `lhghwscans` WHERE sid = %s", $sid);
       			$results = $lhg_price_db->get_results($myquery);

			echo '<p>SID: '.$sid.'<br>';
                        echo '  <input href="#" id="create-fingerprint" class="button-primary create-fingerprint" value="Create fingerprint" />
                              </p>';

                        echo '

                        <a href="?" id="select_all_pci_yes">all yes</a><br><a href="?" id="select_all_pci_no">all no</a>
                        <table class="table-pci-selector"><tr>
                                <td class="pci-selector-1">select</td>
                                <td class="pci-selector-2">Description</td>
                        	</tr>';

                        global $txt_yes;
                        global $txt_no;


		        foreach($results as $result){
                                if ($result->pciid != "") {

	                        $default_y = 'checked="checked"';
        	        	$default_n = "";

                                #check if pciid was lsited in text filed:
                                if (strpos($library_pciid,$result->pciid) !== false) {
                                        # pciid found, nothing to do
				} else {
		                        $default_y = "";
        		        	$default_n = 'checked="checked"';
                                }

                                //error_log ("PCIID: ".$result->pciid);
                                print '<tr class="pci-selector-row">
                                        <td>

                                        <form action="?" method="post" class="hwcomments">
                                        <fieldset>
		                             '.$txt_yes.' <input type="radio" id="radio-y-'.$result->id.'" name="on-board-'.$result->pciid.'" value="y" '.$default_y.' />
                		             <input type="radio" id="radio-n-'.$result->id.'" name="on-board-'.$result->pciid.'" value="n" '.$default_n.' /> '.$txt_no.'
		                        </fieldset>
                                        </form>

                                        </td>
                                        <td>'.$result->idstring."<br>".
                                        str_replace("\n","<br>",$result->idstring_subsystem)."
                                        </td>";
				}
			}
                        echo "</tr></table>";

			echo '</div>';

                        #
                        # jquery code to exchange pciid string
                        #
                        # 1. get pciids of component (i.e. "yes" was checked)
                        # 2. store pciids to DB (onboard)
                        # (3. store pciids to WPDB post metadata - will be done by publis button)
                        # 4. exchange string in text field

                echo '
                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {


                                // radio button actions
                                $("[id^=radio-n-]").click(function() {
                                    var id = $(this).attr(\'id\').substring(8);
                                    $("#pci-feedback-"+id).show("slow");
                                    //$("#updatearea-"+id).show();
				    //$("#scan-comments-"+id).show();

                                    //prepare Ajax data:
                                    var session = "'.$sid.'";
	                            var data ={
                                        action: \'lhg_scan_onboardn_ajax\',
                                        id: id,
                                        session: session
                                    };

                                    $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
                                       //currently no visual feedback

                                    });


                                });

                                $("[id^=radio-y-]").click(function() {
                                    var id = $(this).attr(\'id\').substring(8);
                                    //$("#updatearea-"+id).hide("slow");
	                            $("#pci-feedback-"+id).hide("slow");
                                    //$("#scan-comments-"+id).hide("slow");

                                    //prepare Ajax data:
                                    var session = "'.$sid.'";
	                            var data ={
                                        action: \'lhg_scan_onboardy_ajax\',
                                        id: id,
                                        session: session
                                    };

                                    $.get(\'/wp-admin/admin-ajax.php\', data, function(response){
                                        //currently no visual feedback

                                    });

                                });

                                $("[id^=radio-y-]").each(function(){
                                        var id = $(this).attr(\'id\').substring(8);

                                	if ($(this).is(":checked")) {
	                                    //$("#pci-feedback-"+id).hide();
        	                            //$("#scan-comments-"+id).hide();
                	                }


                                });

                                // create list of selected pciids
                                //
                                $("#create-fingerprint").click(function() {

                                        var pcilist = "";
	                                $("[id^=radio-y-]").each(function(){
        	                                var id = $(this).attr(\'id\').substring(8);
        	                                var pciid = $(this).attr(\'name\').substring(9);

                	                	if ($(this).is(":checked")) {
                                                        if (!pcilist) {
                                                                pcilist = pciid;
                                                        } else {
        	                                                pcilist = pcilist + ","+ pciid;
		                	                }
	                	                }
                                	});
                                        // replace value in pcilist text input field
                                        $("#product-library-pciid").val(pcilist);
                                        //alert("PCI List:"+pcilist);

                                        var origtext = $("#content").val();
                                        var start_pos = origtext.indexOf("[lhg_mainboard_lspci]");
                                        var end_pos   = origtext.indexOf("[/lhg_mainboard_lspci]");
                                        var delta = 0;
                                        //alert("1: Start: "+start_pos+" End:"+end_pos);

                                        if (start_pos == end_pos ) {
                                        	start_pos = origtext.indexOf("[code lang=\"plain\" title=\"lspci -nnk\"]");
                                        	end_pos   = origtext.indexOf("[/code]");
                                                delta = 17;
                                                //alert("2: Start: "+start_pos+" End:"+end_pos);

        	                            	};
                                        if (start_pos == -1 ) {
                                        	start_pos = origtext.indexOf("[code lang=\"plain\" title=\"lspci\"]");
                                        	end_pos   = origtext.indexOf("[/code]");
                                                delta = 17-5;
                                                //alert("2: Start: "+start_pos+" End:"+end_pos);

        	                            	};

                                        // get new PCI list from PriceDB via AJAX
                                        //
                                        // send list of pci ids and get lspci extract as text
                                    	var session = "'.$sid.'";
	                            	var data ={
                                        		action: \'lhg_pci_extract_ajax\',
	                                        	pcilist: pcilist,
	                                        	session: session
        	                            	};

                                    	$.get(\'/wp-admin/admin-ajax.php\', data, function(response){
	                                        var pcilist_txt = "";
                                        	pcilist_txt = $(response).find("supplemental pcilist_txt").text();
                                                var newtext = origtext.substr(0,start_pos+21+delta) + "\n" + pcilist_txt + origtext.substr(end_pos);
                                                //newtext = newtext.replace("<!--:us-->","");
                                                //newtext = newtext.replace("<!--:-->","");
                                                //newtext = newtext.replace("&lt;!--:us--&gt;","");
                                                //newtext = newtext.replace("&lt;!--:--&gt;","");

                                                //tinymce.activeEditor.execCommand("mceReplaceContent", false, newtext);

                                                //$("#content").val(newtext);
                                                //qtrans_save(newtext);

                                                //tinyMCE.get2("content").remove();
		                    		$("#qtrans_textarea_content").val(newtext);
		                    		$("#content").val(newtext);
                    				//window.clearInterval(waitForTinyMCE);

                                                //$("#qtrans_textarea_content").val(newtext);
	                                        //alert("Text: "+newtext);

                                      	 	//currently no visual feedback
	                                 });


                                        //qtrans_editorInit();
                                        //qtrans_editorInit3();
                                        //qtrans_updateTinyMCE();
                                        //tinyMCE.triggerSave();
                                        tb_remove();


                                });

                                // all PCI components to onboard (i.e. yes)
                                $("#select_all_pci_yes").click(function() {
	                                $("[id^=radio-y-]").prop("checked",true);
	                                $("[id^=radio-n-]").prop("checked",false);
                                        //prevent default behavior
                                	return false;

                                });

                                // all PCI components to not onboard (i.e. no)
                                $("#select_all_pci_no").click(function() {
	                                $("[id^=radio-y-]").prop("checked",false);
	                                $("[id^=radio-n-]").prop("checked",true);
                                        //prevent default behavior
                                	return false;

                                });


                                //prevent default behavior
                                return false;


                });

                /*]]> */
                </script>';



		}

	
	}

         #
         #
         #### AJAX handlers
         #
         #

         //update of Amazon information - for logged in users
         add_action('wp_ajax_lhg_amazon_update_ajax', 'lhg_amazon_update_ajax');
         function lhg_amazon_update_ajax() {
                 $pid = absint( $_REQUEST['post_id'] );
                 $asin = $_REQUEST['asin'] ;

                 if ( ($asin == "") or (substr($asin,0,5) == "00000") ) {
                        # No ASIN of no valid ASIN provided - spare us the hazzle
                        print "ASIN not valid";
                        print '<a id="more-image-options-link" class="thickbox more-image-options button" href="#TB_inline?width=700&height=550&inlineId=more-image-options">Show more options</a>';

                        die();
		 }


               $output = lhg_aws_get_price($asin,"com");
                 list($image_url_com, $product_url_com, $price_com , $product_title, $label, $brand, $image_url_com2, $image_url_com3 , $image_url_com4, $image_url_com5 ) = split(";;",$output);

                 $product_title = str_replace("Title: ","", $product_title);

               $output = lhg_aws_get_price($asin,"fr");
                 list($image_url_fr, $product_url_fr, $price_fr) = split(";;",$output);

               $output = lhg_aws_get_price($asin,"de");
                 list($image_url_de, $product_url_de, $price_de) = split(";;",$output);

                 $image_url_com   = str_replace("Image: ","", $image_url_com);
                 $image_url_com2   = str_replace("Image2: ","", $image_url_com2);
                 $image_url_com3   = str_replace("Image3: ","", $image_url_com3);
                 $product_url_com = str_replace("URL: ","", $product_url_com);
                 $price_com = str_replace("Price: ","", $price_com);

                 $product_url_de = str_replace("URL: ","", $product_url_de);
                 $price_de = str_replace("Price: ","", $price_de);

                 $product_url_fr = str_replace("URL: ","", $product_url_fr);
                 $price_fr = str_replace("Price: ","", $price_fr);

                $success_image_com = ($price_com != "")? '<span class="amz-ajax-found">found</div>' : '<div class="amz-ajax-not-found">not found</div>';;
                 $success_image_fr  = ($price_fr  != "")? '<span class="amz-ajax-found">found</div>' : '<div class="amz-ajax-not-found">not found</div>';;
                 $success_image_de  = ($price_de  != "")? '<span class="amz-ajax-found">found</div>' : '<div class="amz-ajax-not-found">not found</div>';;

                 #
                 #### add image to article, if icon empty
                 #
                 if ($image_url_com == "") {
                       $scaled_image_url = "/wp-uploads/2013/03/noimage130.jpg";
               }else{

                       $scaled_image_url = lhg_create_article_image( $image_url_com, $product_title );
                         $si_filename = str_replace("/wp-uploads/","",$scaled_image_url);

                       if ( !has_post_thumbnail( $pid ) ) {

                                 $file = "/var/www/wordpress".$scaled_image_url;
                               #print "PID: $pid";
                               #print "<br>Store Thumbnail!";
                               #print "<br>SIURL: $scaled_image_url";

                               $wp_filetype = wp_check_filetype($file, null );

                               $attachment = array(
                                   'post_mime_type' => $wp_filetype['type'],
                                   'post_title' => sanitize_title($product_title),
                                   'post_content' => '',
                                   'post_status' => 'inherit'
                               );

                               #  var_dump($attachment);

                               $attach_id = wp_insert_attachment( $attachment, $si_filename, $pid );
                                 #print "AID: ".$attach_id;
                                 require_once(ABSPATH . 'wp-admin/includes/image.php');
                               $attach_data = wp_generate_attachment_metadata( $attach_id, $si_filename );
                               wp_update_attachment_metadata( $attach_id, $attach_data );
                               set_post_thumbnail( $pid, $attach_id );

                       }

                 }
                echo '<div class="amz-ajax-image"><a href="'.$product_url_com.'"><img src="'.$scaled_image_url.'"/></a>';
                print '<a id="more-image-options-link" class="thickbox more-image-options button" href="#TB_inline?width=700&height=550&inlineId=more-image-options">Show more options</a>';
                echo '</div>';





                 echo '
                 <div class="ajax-amazon-table">
                 <table ><tr>

                 <td>Region</td> <td>status</td>  <td>Price</td> <td> URL </td>

                 </tr>
                 <td>com</td> <td><span class="amz-ajax-return">'.$success_image_com.'</span></td>
                 <td> '.$price_com.'</td>
                 <td> ';
                 if ($product_url_com != "") print '(<a href="'.$product_url_com.'">visit</a>)';
                 print '</td>
                 </tr>

                 </tr>
                 <td>fr</td> <td><span class="amz-ajax-return">'.$success_image_fr.'</span></td>
                 <td> '.$price_fr.'</td>
                 <td> ';
                 if ($product_url_fr != "") print '(<a href="'.$product_url_fr.'">visit</a>)';
                 print '</td>
                 </tr>

                 </tr>
                 <td>de</td> <td><span class="amz-ajax-return">'.$success_image_de.'</span></td>
                 <td> '.$price_de.'</td>
                 <td>';
                 if ($product_url_de != "") print '(<a href="'.$product_url_de.'">visit</a>)';
                 print '</td>
                 </tr>

                 </table>
                 </div>';

                // exchange images in Amazon image selector popup

                print'

                <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(document).ready( function($) {
                ';

                if ($image_url_com != "") {
                	print ' $("#imgsrc-1").attr("src","'.$image_url_com.'");';
                }else{
                        print ' $("#imgsel-1").hide();';
                }

                if ($image_url_com2 != "") {
                	print ' $("#imgsrc-2").attr("src","'.$image_url_com2.'");';
                }else{
                        print ' $("#imgsel-2").hide();';
                }

                if ($image_url_com3 != "") {
                	print ' $("#imgsrc-3").attr("src","'.$image_url_com3.'");';
                }else{
                        print ' $("#imgsel-3").hide();';
                }

                if ($image_url_com4 != "") {
                	print ' $("#imgsrc-4").attr("src","'.$image_url_com4.'");';
                }else{
                        print ' $("#imgsel-4").hide();';
                }

		#error_log(' $("#imgsrc-2").attr("src","'.$image_url_com2.'");');

                print '
                });



                /*]]> */

                </script>';



                 die();
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