<?php

# add chat functionality

add_action( 'wp_footer', 'lhg_chat_jquery' );

function lhg_chat_jquery() {
?>
<script type="text/javascript">
  jQuery.ajaxSetup({ cache: true });
  
  jQuery.getScript("https://static.jappix.com/server/get.php?l=en&t=js&g=mini.xml", function() {
     JappixMini.launch({
        connection: {
           domain: "anonymous.jappix.com",
        },

        application: {
           network: {
              autoconnect: true,
           },

           interface: {
              showpane: false,
              animate: true,
           },

           user: {

           <?php
                if (!is_user_logged_in() ) {
                        global $region;
                        $rnd = rand(1,999);
                	print "random_nickname: false,";
                	print "mini_nickname: 'guest.$region.$rnd',";
                	print "nickname: 'guest.$region.$rnd',";

                }else{
                        global $current_user;
                        get_currentuserinfo();

                	print "random_nickname: false,";
                	print "mini_nickname: '".$current_user->display_name."',";
                	print "nickname: '".$current_user->display_name."',";
                }

           ?>

           },

           groupchat: {
              open: ["lhg-chatroom"],
           },
        },
     });
  });
</script>

<?php

}


?>