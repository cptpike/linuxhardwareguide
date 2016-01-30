<?php

function lhg_menu_settings_page () {

//var_dump($_GET);
//var_dump($_POST);

//GET variables (from overview)
if ( ($_GET ["mode"] == "update") or ($_GET ["mode"] == "create")) {
	$mode = $_GET ["mode"];
	$shop_id = $_GET ["sid"];
	$post_id = $_GET ["pid"];
	$lhg_db_id = $_GET ["id"];
}elseif ( ($_GET ["mode"] == "notavail") ) {
	$mode = $_GET ["mode"];
	$shop_id = $_GET ["sid"];
	$post_id = $_GET ["pid"];
	$lhg_db_id = $_GET ["id"];
}elseif ($_GET ["mode"] == "translate_comde")  {
        $mode = "translate";

}else {

	//POST variables
	if ($_POST ["mode"] == "created") $mode = "created";
	if ($_POST ["mode"] == "update") $mode = "update";
	$shop_id = $_POST ["sid"];
	$post_id = $_POST ["pid"];
	$lhg_db_id = $_POST ["id"];

}

if ($mode == "update") {
	if ($shop_id == "") {
		lhg_write_to_db();
	}else{
                lhg_show_update_table($shop_id, $post_id, $lhg_db_id);
	}
}elseif ($mode == "create") {
	lhg_show_create_table($shop_id, $post_id);
}elseif ($mode == "notavail") {
	lhg_show_notavail_table($shop_id, $post_id);
}elseif ($mode == "created") {
	lhg_show_created_table();
}elseif ($mode == "translate") {
	$post_id = $_GET ["postid"];
	lhg_show_translate_process($post_id);
}else {
        #echo "HIER!";
	//lhg_show_update_table($shop_id, $post_id, $lhg_db_id);

        //echo "Check missing links";
        lhg_show_missing_links();

}


}

function lhg_write_to_db () {

if ($shop_article_id = "" ) {
        echo "Something wrong, no article id";
        exit;
}

$lhg_db_id = $_POST ["lhg_db_id"];
$shop_id = $_POST ["shop_id"];
$post_id = $_POST ["post_id"];
$shop_article_id = $_POST ["shop_article_id"];

//var_dump ($_POST);
echo "<br>Updating data base..";

$result = lhg_db_update( $shop_article_id, "shop_article_id", $lhg_db_id);


//echo "$result";
echo ".. done";

echo "<br>refresh database..";
lhg_refresh_database_entry($lhg_db_id, $shop_id);
echo "..done";


lhg_show_update_table ($shop_id, $post_id, $lhg_db_id);

}


function lhg_show_update_table($shop_id, $post_id, $lhg_db_id) {
	echo "<h3>Update properties:</h3> ";

	echo '<form action="admin.php?page=lhg_pricedb_update" method="post">';
        echo '<input name="mode" type="hidden" value="update">';

	//ToDo: nonce check missing!

	echo "<table border=1><tr>";

	$title=get_the_title ( $post_id );
	$art_image=get_the_post_thumbnail( $post_id, array(55,55) );

        $shop_img  = lhg_get_shop_small_icon($shop_id);


	echo "<td>Title:</td><td>$title</td>";
	echo "</tr><tr>";


	echo "<td>Image:</td><td>$art_image </td>";
	echo "</tr><tr>";

	echo "<td>Post ID:</td><td>$post_id</td>";
        echo '<input name="post_id" type="hidden" value="'.$post_id.'">';
	echo "</tr><tr>";

	echo "<td>Shop ID:</td><td>$shop_id".'<img src="'.$shop_img.'"></td>';
        echo '<input name="shop_id" type="hidden" value="'.$shop_id.'">';
	echo "</tr><tr>";

	echo "<td>LHG DB ID:</td><td>$lhg_db_id";
        echo '<input name="lhg_db_id" type="hidden" value="'.$lhg_db_id.'">
        </td>';
	echo "</tr><tr>";

	//get article ID
	$shop_article_id = lhg_db_get_shop_article_id ( $post_id, $shop_id);

	echo '<td>Article ID of shop:</td><td  bgcolor="red"><input name="shop_article_id" type="text" size="30" value="'.$shop_article_id.'"></td>';
	echo "</tr><tr>";

	$price = lhg_db_get_shop_price ($post_id, $shop_id);
	echo "<td>Price:</td><td>$price</td>";
	echo "</tr><tr>";

	$aff_url = lhg_db_get_shop_url ($post_id, $shop_id);
	echo "<td>Affiliate URL:</td><td>$aff_url</td>";

	echo "</tr></table>";

	echo '<input type="submit" value="update">';

	echo "</form>";
}

function lhg_show_create_table($shop_id, $post_id) {

	echo "<h3>Create Entry:</h3> ";

	echo '<form action="admin.php?page=lhg_pricedb_update" method="post">';
        echo '<input name="mode" type="hidden" value="created">';


	//ToDo: nonce check missing!

	echo "<table border=1><tr>";

	$title=get_the_title ( $post_id );
	$art_image=get_the_post_thumbnail( $post_id, array(55,55) );
        $shop_img  = lhg_get_shop_small_icon($shop_id);


	echo "<td>Title:</td><td>$title</td>";
	echo "</tr><tr>";


	echo "<td>Image:</td><td>$art_image </td>";
	echo "</tr><tr>";

	echo "<td>Post ID:</td><td>$post_id</td>";
        echo '<input name="post_id" type="hidden" value="'.$post_id.'">';
	echo "</tr><tr>";

	echo "<td>Shop ID:</td><td>$shop_id".'<img src="'.$shop_img.'"></td>';
        echo '<input name="shop_id" type="hidden" value="'.$shop_id.'">';
	echo "</tr><tr>";

	echo "<td>LHG DB ID:</td><td>Will be created</td>";
	echo "</tr><tr>";

	echo '<td>Article ID of shop:</td><td bgcolor="red"><input name="shop_article_id" type="text" size="30" value=""></td>';
	echo "</tr><tr>";

	echo "<td>Price:</td><td>Will be created</td>";
	echo "</tr><tr>";

	$aff_url = lhg_db_get_shop_url ($post_id, $shop_id);
	echo "<td>Affiliate URL:</td><td>Will be created</td>";

	echo "</tr></table>";

	echo '<input type="submit" value="create">';

	echo "</form>";

}

function lhg_show_created_table() {

	$shop_id = $_POST ["shop_id"];
	$post_id = $_POST ["post_id"];
	$shop_article_id = $_POST ["shop_article_id"];

	//var_dump ($_POST);
	echo "<br>Create data base entry..";

	$result = lhg_db_create_entry( $post_id, $shop_id, $shop_article_id );

	//echo "$result";
	echo ".. done";

	//get newly created lhg_db_id

	$lhg_db_id = lhg_db_get_id ( $post_id, $shop_id);
        lhg_refresh_database_entry($lhg_db_id,$shop_id);
	lhg_show_update_table ($shop_id, $post_id, $lhg_db_id);

}

function lhg_show_notavail_table($shop_id, $post_id) {


        global $lhg_price_db;
        $time = time();
        echo "TIME: $time";

        $shop_article_id = "NOT_AVAILABLE";
	$result = lhg_db_create_entry( $post_id, $shop_id, $shop_article_id );
	echo ".. done";

	//get newly created lhg_db_id
	$lhg_db_id = lhg_db_get_id ( $post_id, $shop_id);


	$sql = "UPDATE lhgprices SET `last_update` = $time WHERE `id` = $lhg_db_id " ;
	$result = $lhg_price_db->query($sql);

	echo "<h3>Not Available:</h3> ";

	#echo '<form action="admin.php?page=lhg_pricedb_update" method="post">';
        #echo '<input name="mode" type="hidden" value="created">';


	//ToDo: nonce check missing!

	echo "<table border=1><tr>";

	$title=get_the_title ( $post_id );
	$art_image=get_the_post_thumbnail( $post_id, array(55,55) );
        $shop_img  = lhg_get_shop_small_icon($shop_id);


	echo "<td>Title:</td><td>$title</td>";
	echo "</tr><tr>";


	echo "<td>Image:</td><td>$art_image </td>";
	echo "</tr><tr>";

	echo "<td>Post ID:</td><td>$post_id</td>";
        echo '<input name="post_id" type="hidden" value="'.$post_id.'">';
	echo "</tr><tr>";

	echo "<td>Shop ID:</td><td>$shop_id".'<img src="'.$shop_img.'"></td>';
        echo '<input name="shop_id" type="hidden" value="'.$shop_id.'">';
	echo "</tr><tr>";

	echo '<td>Article ID of shop:</td><td bgcolor="red">Not Available</td>';
	echo "</tr><tr>";

	echo "</tr></table>";

}


function lhg_refresh_database_entry($shop_article_id,$shop_id) {

	//echo "<br>1 (send update request)";

        //$r = new HttpRequest( '192.168.3.114/updatedb.pl?aid='.$shop_aricle_id);
        if ( ($_SERVER['SERVER_ADDR'] == "192.168.56.12") or ($_SERVER['SERVER_ADDR'] == "192.168.56.13") )
	$url = 'http://192.168.56.14/lhgupdatedb.php?aid='.$shop_article_id."&sid=".$shop_id;

	if ( ($_SERVER['SERVER_ADDR'] == "192.168.3.112") or ($_SERVER['SERVER_ADDR'] == "192.168.3.113") )
	$url = 'http://192.168.3.114/lhgupdatedb.php?aid='.$shop_article_id."&sid=".$shop_id;

        //echo "<br>URL $url";

        $respTMP = wp_remote_post( $url );
        $resp = $respTMP["body"];
        //echo "<br>2 Response: <br>";
        //print_r($resp);
        return $resp;

}


function lhg_show_missing_links () {
	echo "<h3>Checking missing links:</h3> ";

	global $lhg_price_db;

    	$sql = "SELECT * FROM `lhgtransverse_posts` WHERE (postid_de = 0 OR postid_com = 0) AND (status_com = \"published\" OR status_com = \"\") ORDER BY product";
    	$result = $lhg_price_db->get_results($sql);

	#$result = $lhg_price_db->query($sql);
	#$result = $lhg_price_db->get_var($sql);

        #$aa=$result[0];
        #
        #print_r($aa);
        #echo "Extract";
        #echo "ID: ".$aa->id;



	//ToDo: nonce check missing!

        if ( ($_POST[id_com] > 0) && ($_POST[id_de] >0) )
                lhg_link_articles($_POST[id_com],$_POST[id_de]);
        #echo "postid_com: $_POST[postid_com]<br>";
        #echo "postid_de: $_POST[postid_de]<br>";

        echo '<form action="/wp-admin/admin.php?page=lhg_pricedb_update" method="post">';

	echo "<table border=1><tr>";

	echo "<td>Title:</td><td>URL com</td><td>URL de</td><td></td><td></td>";
	echo "</tr>";

        if ( ($_SERVER['SERVER_ADDR'] == "192.168.56.12") or ($_SERVER['SERVER_ADDR'] == "192.168.56.13") ) {
		$urltranslate = 'http://www.linux-hardware-guide.de/wp-admin/admin.php?page=lhg_menu_settings_page&mode=translate_comde';
		$urledit_com = 'http://www.linux-hardware-guide.com/wp-admin/post.php?post=';
        }

	if ( ($_SERVER['SERVER_ADDR'] == "192.168.3.112") or ($_SERVER['SERVER_ADDR'] == "192.168.3.113") )  {
		$urltranslate = 'http://192.168.3.112/wp-admin/admin.php?page=lhg_menu_settings_page&mode=translate_comde';
		$urledit_com = 'http://192.168.3.113/wp-admin/post.php?post=';
        }


        for ($i=0; $i<sizeof($result); $i++) {
                print "<tr>";
                print "<td>".$result[$i]->product."</td>";
                #print "<td>".$result[$i]->postid_com." </td>";
                #print "<td>".$result[$i]->postid_de." </td>";

                ( $result[$i]->postid_com != 0 ) ? print '<td><a href="'.$urledit_com.$result[$i]->postid_com.'&action=edit">'.$result[$i]->postid_com.'</a> (<a href="'.$result[$i]->permalink_com.'">view</a>) </td>' :
                				   print '<td>-</td>';

                ( $result[$i]->postid_de != 0 ) ?  print '<td><a href="'.$result[$i]->permalink_de.'">'.$result[$i]->postid_de.'</a> </td>' :
                				   print '<td><a href="'.$urltranslate.'&postid='.$result[$i]->postid_com.'">translate</a></td>';

                print '<td>';
	        if ($result[$i]->postid_com != "0") echo '<input type="radio" name="id_com" value="'.$result[$i]->id.'">';
                print '</td>';

                print '<td>';
                if ($result[$i]->postid_de != "0") echo '<input type="radio" name="id_de" value="'.$result[$i]->id.'">';
                print '</td>';


                print "</tr>";
        }

	echo "</tr></table>";

        echo "<button>Link Articles</button>";
        echo "</form>";

        #echo "<br>dump:<br>";
        #print_r($result);


}

function lhg_link_articles ( $id_com, $id_de ) {

        #echo "postid_com: $postid_com<br>";
        #echo "postid_de: $postid_de<br>";
        echo "id_com: $id_com<br>";
        echo "id_de: $id_de<br>";

        // -- get postid_com and _de values
        global $lhg_price_db;
    	$sql = "SELECT postid_com FROM `lhgtransverse_posts` WHERE id = $id_com ";
    	$postid_com = $lhg_price_db->get_var($sql);

    	$sql = "SELECT postid_de FROM `lhgtransverse_posts` WHERE id = $id_de ";
    	$postid_de = $lhg_price_db->get_var($sql);


        echo "postid_com: $postid_com<br>";
        echo "postid_de: $postid_de<br>";

	// -- add de id to com
	$sql = "UPDATE lhgtransverse_posts SET `postid_de` = $postid_de WHERE `id` = $id_com";
	$result = $lhg_price_db->query($sql);

	#echo "<br>LQ: ".var_dump($lhg_price_db->last_query) ."ERREND<br>";
    	#echo "<br>LER: ".var_dump($lhg_price_db->last_error) ."ERREND<br>";


        // -- remove de entry
	$sql = "DELETE FROM lhgtransverse_posts WHERE `id` = $id_de ";
	$result = $lhg_price_db->query($sql);

    	//$sql = "SELECT * FROM `lhgtransverse_posts` WHERE postid_de = 0 OR postid_com = 0 ORDER BY product";
  
	#echo "<br>LQ: ".var_dump($lhg_price_db->last_query) ."ERREND<br>";
    	#echo "<br>LER: ".var_dump($lhg_price_db->last_error) ."ERREND<br>";
}


?>