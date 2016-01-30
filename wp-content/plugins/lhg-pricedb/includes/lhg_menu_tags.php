<?php

function lhg_menu_tags () {


        # two tag ids were linked:
        if ( ( $_POST[id_com] > 0 ) && ( $_POST[id_de] > 0) ) lhg_link_tags($_POST[id_com],$_POST[id_de]);



#if ($_POST != "") lhg_db_update_scaninfo();


#        $res = lhg_db_get_scan_sids ();
        print "<h1>Tag overview</h1>";
        print "<br>";


        global $wpdb;

	$sql1 = "SELECT $wpdb->terms.term_id, $wpdb->terms.name, $wpdb->terms.slug
			FROM $wpdb->terms";

	$sql2 = "SELECT $wpdb->term_taxonomy.term_id, $wpdb->term_taxonomy.taxonomy, $wpdb->term_taxonomy.term_taxonomy_id
			FROM $wpdb->term_taxonomy";

        # works but everything included
	#$sql = "SELECT $wpdb->terms.term_id, $wpdb->terms.name, $wpdb->terms.slug
	#		FROM $wpdb->terms";

	#$sql = "SELECT $wpdb->term_taxonomy.term_id, $wpdb->term_taxonomy.name, $wpdb->term_taxonomy.slug
	#		FROM $wpdb->term_taxonomy";
        $safe_sql1 = $wpdb->prepare( $sql1 );
      	$results1 = $wpdb->get_results( $safe_sql1, OBJECT );

        $safe_sql2 = $wpdb->prepare( $sql2 );
      	$results2 = $wpdb->get_results( $safe_sql2, OBJECT );

        #$sql = ("SELECT term_id FROM $wpdb->wp_term_taxonomy");
        #$result = $wpdb->get_results($sql);

        #if (0 < $numtags) $numtags = number_format($numtags);
        #echo $numtags . '';
        #print "Tag List";
        #var_dump($results2);



#print "POST:";
#var_dump ( $_POST );

        #var_dump( $res );



        $i=0;
        foreach ($results2 as $result) {
        	$i++;
        	$tid  = $result->term_id;
        	$ttid  = $result->term_taxonomy_id;
        	#$name = $result->name;
                $tax = $result->taxonomy;


        	$slug = lhg_get_slug_by_termid($tid);
        	$name = lhg_get_name_by_termid($tid);



                #$date = gmdate("m/d/Y g:i:s A", $time);
        	#$acomment = $resN->admincomment;
        	#$ucomment = $resN->usercomment;
                #$ucomment_short = $ucomment;
        	#if (strlen($ucomment) > 50)
	        #        $ucomment_short = substr(sanitize_text_field($ucomment),0,50)."...";
                #
        	#$status = $resN->status;
	        #$sid2 = $res[1]->sid;
        	#var_dump ($sid ."--".$sid2);
                #print "SID: $sid<br>";

                if ($tax == "post_tag") {
                        global $lang;
        	        #print "TID: $tid -- $ttid -- $name -- $slug -- $tax<br>";
                	if ($lang == "en") lhg_check_slug_com_available( $slug );
                	if ($lang == "de") lhg_check_slug_de_available( $slug );
		} else {
                        # skip
                }
            #    print "<tr>";
            #
            #    print "<td>$date </td>";
            #    print '<td><a href="/hardware-profile/scan-'.$sid.'">'.$sid.'</a></td>';
            #    print "<td>$ucomment_short</td>";
            #    print '<td>
	    #            <input name="hwscan_acomment_'.$sid.'" type="text" size="20" value="'.$acomment.'">
            #           </td>';
            #    print "<td>$status</td>";
            #
            #    print "</tr>";

	}

#
# Linking table
#

	global $lhg_price_db;

    	$sql = "SELECT * FROM `lhgtransverse_tags` WHERE `slug_de` = 0 OR `slug_com` = 0 ORDER BY slug_de, slug_com DESC ";
    	$result = $lhg_price_db->get_results($sql);

        #echo "FOUND:";
        #var_dump($result);

print '<form action="admin.php?page=lhg_menu_tags" method="post">';
print '<table border=1>
 	  <tr>
            <td><b>Slug COM</b></td>

            <td><b>Slug DE</b></td>
            <td></td> <td></td>
	  </tr>';


        for ($i=0; $i<sizeof($result); $i++) {
        # hide linked tags
        if ( ( $result[$i]->slug_com == "") or ($result[$i]->slug_de == "") ) {

                print "<tr>";
                print "<td>".$result[$i]->slug_com."</td>";
                print "<td>".$result[$i]->slug_de."</td>";

                print '<td>';
	        if ($result[$i]->slug_com != "") echo '<input type="radio" name="id_com" value="'.$result[$i]->id.'">';
                print '</td>';

                print '<td>';
                if ($result[$i]->slug_de != "") echo '<input type="radio" name="id_de" value="'.$result[$i]->id.'">';
                print '</td>';


                print "</tr>";
	}
        }

        echo "</tr></table>";

        echo "<button>Link Tags</button>";
        echo "</form>";

}

function lhg_check_slug_com_available( $slug ) {
        # check if slug is in DB, otherwise -> add

	global $lhg_price_db;

        $sql = "SELECT id FROM `lhgtransverse_tags` WHERE slug_com = \"".$slug."\"";
    	$result = $lhg_price_db->get_var($sql);

        #echo "RES: -- $result --";
        #var_dump($result);

        if ($result == "" )  {
                #echo "create $slug ... ";
	        $sql = "INSERT INTO lhgtransverse_tags (slug_com) VALUES ('".$slug."')";
                $result = $lhg_price_db->query($sql);
                $error = $lhg_price_db->last_error;
    		if ($error != "") var_dump($error);
                #echo "done <br>";
        }else{
                #echo "$slug exists <br>";
        }

        return $result;


}

function lhg_check_slug_de_available( $slug ) {
        # check if slug is in DB, otherwise -> add

	global $lhg_price_db;

        $sql = "SELECT id FROM `lhgtransverse_tags` WHERE slug_de = \"".$slug."\"";
    	$result = $lhg_price_db->get_var($sql);

        #echo "RES: -- $result --";
        #var_dump($result);

        if ($result == "" )  {
                echo "create $slug ... ";
	        $sql = "INSERT INTO lhgtransverse_tags (slug_de) VALUES ('".$slug."')";
                $result = $lhg_price_db->query($sql);
                $error = $lhg_price_db->last_error;
    		if ($error != "") var_dump($error);
                echo "done <br>";
        }else{
                echo "$slug exists <br>";
        }

        return $result;


}


function lhg_get_slug_by_termid ( $tid) {

        #print "TID: $tid";

        global $wpdb;
	$sql1 = "SELECT $wpdb->terms.slug
			FROM $wpdb->terms WHERE $wpdb->terms.term_id = ".$tid;

        $safe_sql1 = $wpdb->prepare( $sql1 );
      	$results1 = $wpdb->get_results( $safe_sql1, OBJECT );
        #var_dump($results1);
        return $results1[0]->slug;
}

function lhg_get_name_by_termid ( $tid) {

        #print "TID: $tid";

        global $wpdb;
	$sql1 = "SELECT $wpdb->terms.name
			FROM $wpdb->terms WHERE $wpdb->terms.term_id = ".$tid;

        $safe_sql1 = $wpdb->prepare( $sql1 );
      	$results1 = $wpdb->get_results( $safe_sql1, OBJECT );
        #var_dump($results1);
        return $results1[0]->name;
}

function lhg_link_tags ( $id_com, $id_de ) {
        global $lhg_price_db;

        echo "Link the tags ... $id_com - $id_de <br>";

        # 1. add slug_de to slug_com

        $sql = "SELECT slug_de FROM `lhgtransverse_tags` WHERE id = \"".$id_de."\"";
    	$slug_de = $lhg_price_db->get_var($sql);

        $sql = "UPDATE `lhgtransverse_tags` SET slug_de = '".$slug_de."' WHERE id = \"".$id_com."\"";
        $result = $lhg_price_db->query($sql);

        # 2. delete slug_de
        $sql = "DELETE FROM lhgtransverse_tags WHERE `id` = $id_de ";
	$result = $lhg_price_db->query($sql);

}
