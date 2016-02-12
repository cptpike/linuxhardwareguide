<?php

# This script is used to receive upload requests by the lhg_hwscan client

#echo "TEST";

if ($_FILES["file1"]["tmp_name"] == "") {
check_sid();

echo '
<form action="/uploadfiles.php" method="post" enctype="multipart/form-data">
<input type="file" name="file1" accept="text/*" maxlength="2097152">
<input type="file" name="file2" accept="text/*" maxlength="2097152">
<input type="file" name="file3" accept="text/*" maxlength="2097152">
<input type="file" name="file4" accept="text/*" maxlength="2097152">
<input type="file" name="file5" accept="text/*" maxlength="2097152">
<input type="file" name="file6" accept="text/*" maxlength="2097152">
<input type="file" name="file7" accept="text/*" maxlength="2097152">
<input type="file" name="file8" accept="text/*" maxlength="2097152">
<input type="file" name="file9" accept="text/*" maxlength="2097152">
<input type="file" name="file10" accept="text/*" maxlength="2097152">
<input type="file" name="file11" accept="text/*" maxlength="2097152">
<input type="file" name="file12" accept="text/*" maxlength="2097152">
<input type="file" name="file13" accept="text/*" maxlength="2097152">
<input type="file" name="file14" accept="text/*" maxlength="2097152">
<input type="file" name="file15" accept="text/*" maxlength="2097152">
<input type="file" name="file16" accept="text/*" maxlength="2097152">
<input type="submit" value="Submit file"><input type="reset">
</form>
';

exit;
}

//otherwise check uploaded files

# v0.2

#echo "Files received\n";

    $allfiles = array("alsa_cards.txt","alsa_devices.txt","aplay.txt","cpuinfo.txt",
                    "dd.err.txt","dd.txt","dmesg.txt","lsb_release.txt","lspci.txt",
                    "lsusb.txt","lsusb_v.err.txt","lsusb_v.txt","scanimage.txt",
                    "version.txt","hdparm.txt","hdparm_direct.txt");


# 1. check if we received the right files
$i=0;
foreach ($allfiles as &$value) {
        $i++;
        #echo "Checking $i \n";
        if ($value == $_FILES["file".$i]["name"])
        {
         	#echo "File $i ($value) correct \n";
	}else{
                echo "unknown files\n";
                exit;
        }
}


# 2. move files
$sid = $_GET['SID'];
$i=0;
foreach ($allfiles as &$value) {
        $i++;

	if ( $_FILES["file".$i]["error"] == UPLOAD_ERR_OK) {
        	$tmp_name = $_FILES["file".$i]["tmp_name"];
	        $name = $_FILES["file".$i]["name"];
	        move_uploaded_file($tmp_name, "/var/www/uploads/".$sid."/".$name);
	}else{
                echo "File broken\n";
                exit;
        }
}

        echo "All files uploaded";

# All files received. start scanning
shell_exec('/var/www/uploads/scan_hw_upload.pl $sid');


exit;

function check_sid(){
   #check if SID exists
   # ToDo: more intelligent check. E.g., if corresponding directory exists
   $sid = $_GET['SID'];

   if ($sid == "") {
   	echo "unknown request. Ignoring";
   	exit;
   }

}


?>
