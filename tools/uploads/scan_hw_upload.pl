#!/usr/bin/perl

# Version 
# 0.2b - Laptop recognition added
# 0.2c - Possibility for user linking by LHG user ID added
# 0.2d - Possibility for user linking by LHG.de user ID added
# 0.2e - Mainboard recognition improved (using fingerprint of unique PCIIDs)
#      - Ignore mainboards in PCI device recognition  

no warnings experimental::smartmatch; 

$sid = $ARGV[0];

print "Version: 0.2e  \n";

# MYSQL CONFIG VARIABLES
use DBI;
#use DBIx::Interp;

our    $host = "192.168.56.14";
# choose correct sql address
use Net::Address::IP::Local;
print "Scanning Session $sid \n";
my $address      = Net::Address::IP::Local->public;
if ($address eq "192.168.3.115") {
    print "On Test Server \n";
    $host = "192.168.3.114";
}

our    $database = "DBI:mysql:lhgpricedb;host=$host";
require ("/var/www/uploads/lhg.conf");
#our    $user = "USERNAME";
#our    $pw = "PASSWORD";
our    $rescan = 0;

# USB IDs of Logitech Unifying Receivers
@lur_uids = ("046d:c52b","046d:c52f");

our $laptop_identified = 0;

#exit 0;

if ( ($ARGV[1] eq "-h") or ($ARGV[0] eq "") ){
    print "
USAGE:
        scan_hw_upload.pl <sessionID> <option>
        
OPTIONS:
        -c 
           Clear stored data
        
        -r
           perform rescan (e.g. after update of hardware database)
        
If <sessionID> equals \"ALL\" a rescan of all available hardware 
uploads is performed.
        
";
    exit 0;
}


if ($ARGV[1] eq "-r") {
    $rescan = 1;
    # rescan requested
    
    #check if rescan is justified
    # do we have entries in lhgwscans?
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT COUNT(*) FROM `lhghwscans` WHERE sid = ?";
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute($sid);
    ($num) = $sth_glob->fetchrow();
    
    if ($num == 0) {
        #never run before
        $rescan = 0;
    
    }
}

if ($ARGV[1] eq "-c") {
    $rescan = 1;
    clear_db($sid);
    exit 0;
}

if ($ARGV[0] eq "ALL") {
    rescan_all();
    exit 0;
}

#cycle = 0 -> first scan
#cycle > 0 -> scan was performed before
$cycle = create_db_metadata();

# prevent upload spam. Check if this upload is a duplicate
check_duplicates();


#send_mail_with_scaninputs();
$laptop_probability = calculate_laptop_probability();

check_mainboard();
check_usb_devices();
check_unifying_receivers();
check_drives();
check_cpu();
check_pci_devices();


#print "Found devices: $found_devices";

print "Scan finished \n";
cleanup();
exit 0;




#
#
###  Mainboard subroutines
#
#

sub check_mainboard {
    
    print "-----> Checking Mainboard / Laptop \n";
    
    open(FILE, "<", "/var/www/uploads/".$sid."/lspci.txt");
    open(FILEN, "<", "/var/www/uploads/".$sid."/lspci.txt");
    $nextline=<FILEN>;
    my $i=0;
    my @identifiedmb=qw();
    my @pciids_tmp=qw(); # array of unique PCI IDs of lspci output
    my $n_fingerprint_total = 0; # total number of elements in lspci output
    while ( <FILE> ) {
        
        # read PCI IDs and Subsystem IDs
        #
        $nextline = <FILEN>;
        #print "Line: $_";
        $pciid   = grab_pciid($_);
        if ($pciid != "")  { 
            $n_fingerprint_total++;
            
            #print "PCIID: $pciid \n";
            $subsystemid   = grab_subsystemid($nextline);
            #print "Subsystem: $subsystemid \n";
        }
        

        #$pciname = grab_pciname($_);
        
        #print "PCIID: $pciid -- ";
        #        
        
        # identify posts that have this pciid
        # 
        $identifiedmb[0]=0;
        if ($pciid != "") {
            
            # check if this Pciid was seen before
            if ($pciid ~~ @pciids_tmp) {
                #print "PCIID $pciid was already counted. Skipping\n";
            }else {
                #print "append $pciid to array. Elements: ";
                push @pciids_tmp, $pciid;
                #print scalar(@pciids_tmp)."\n";

                # count number of pciids of lspci output
                $n_fingerprint +=1; # is identical to scalar(@pciids_tmp)
            
                @postids = checkdb_mainboard_pci($pciid);
                #print "#PIDs: ".scalar(@postids)."  \n";
                foreach $postid (@postids) {
                
                    # Debug
                    #if ( $postid == 154254 ) {
                    #    print "found Pciid: $pciid -> PID: $postid -> counter: $identifiedmb[int($postid)]+1 \n";
                    #}

                    #print $identifiedmb[int($postid)]+1;#."\n";
                    $identifiedmb[int($postid)] +=1;
                }
            }
        }

        #
        #print "\n";
    }
    
    # get number of unique PCI IDs
    
    #print "SA0: ".scalar(@identifiedmb);
    $mindex=findMaxValueIndex(@identifiedmb);
    
    # Cycle through candidates.
    # Look how many percent of mainboards PCIIDs we have found (pure counting not sufficient)
    my $prob_max = 0;
    my $mindex2 = 0;
    my $unique_npciids = 0;
    for (my $i = 0; $i < scalar(@identifiedmb); $i++) {
        if ( $identifiedmb[$i] > 3 ) { # at least 4 pciids to count as mainboard
            
            # get number of unique pciids of postid ($i)
            $unique_npciids_tmp = get_number_unique_pciids($i);
            
            my $prob = $identifiedmb[$i] / $unique_npciids_tmp;
            # 
            #print "PID: $i -> uniq elements: $unique_npciids -> found elements: $identifiedmb[$i]: $prob %\n";
            
            if ($prob > $prob_max) { 
                $prob_max = $prob; 
                $mindex2 = $i;
                $unique_npciids = $unique_npciids_tmp;
            }
        }
    }
    
    # we found pciid $mindex2 with matching probability $prob
    if ($mindex2 != $mindex) { 
        print "       Warning: new matching algorithm found different postid: $mindex -> $mindex2 (prob: $prob_max)\n";
        $mindex = $mindex2;
    }
        
    #    print "
    #MAX finding PostID: $mindex
    #";
    
    # check if % > 70%
    # therefore, get number of PCI IDs
    
    $Npciid = get_number_pciids($mindex);
    print "       lspci fingerprint has $n_fingerprint_total elements (unique: $n_fingerprint)\n";
    print "       Found Postid $mindex -> fingerprint has $unique_npciids elements\n";
    $mb_recognition = $prob_max;
    #if ( $Npciid > 0 ) {
    #    $mb_recognition = $identifiedmb[$mindex]/$Npciid;
    #}
    
    
    if (( $Npciid > 3 ) and ($mb_recognition > 0.85) ) {
        $mbfound = 1;
    
        # check if mainboard was identified but laptop expected
        $mainboard_found_in_db = article_type_is_mainboard( $mindex );
        #$laptop_probability = calculate_laptop_probability ( $sid );
        
        #print "MF: $mainboard_found - PL: $laptop_probability \n";
        if ( ($mainboard_found_in_db == 1) && ($laptop_probability > 0.85) ) {
            $mb_recognition = 0;
            $mbfound = 0;
            print ("Rule 2: Mainboard found but laptop expected\n");
            return;
        }
        
        # Laptop found - check if it is the right one
        if ( ($mainboard_found_in_db == 0) && ($laptop_probability > 0.85) ) {
            print "       Laptop identified ... ";
            
            # check if additional PCI ids exist
            open(FILE, "<", "/var/www/uploads/".$sid."/lspci.txt");
            open(FILE_DBWRITE, "<", "/var/www/uploads/".$sid."/lspci.txt");
            $i=0;
            @knownpciids = qw();
            @pciid_blacklist = get_pciid_blacklist($mindex);
            
            # Print array
            #print "Blacklist";
            #print join(", ", @pciid_blacklist);
            
            while ( <FILE> ) {

                $pciid   = grab_pciid($_);
                $pciname = grab_pciname($_);
                
                if ( ($pciid != "") && (!($pciid ~~ @pciid_blacklist)) )  {
                    print "but unknown PCI ID found ($pciid) \n";
                    return;
                }
                
            }
            print "PCI fingerprint looks correct \n";
            $laptop_identified = 1;
            @usbid_blacklist = get_usbid_blacklist($mindex);
            # checks successful - now write data to DB
            while ( <FILE_DBWRITE> ) {

                $pciid   = grab_pciid($_);
                $pciname = grab_pciname($_);
                
                if  ($pciid != "")    {
                    storescan_mainboard_found($sid, $mindex, $pciid, $pciname);

                }
                
            }
            return;
        }
        
    } else {
        $mbfound = 0;
        printf("       No MB fingerprint found (%d PCI ID(s), %.1f%% match) \n",$Npciid, $mb_recognition*100);
        return;
    }
    
    
    
    
    # We have an identified MB. Store to DB
    
    
    
    
    #
    # blacklist PCI and USB IDs (if mainboard found)
    #
    
    @pciid_blacklist = get_pciid_blacklist($mindex);
    @usbid_blacklist = get_usbid_blacklist($mindex);
    
    #print "USBBL:".join(", ", @usbid_blacklist);
    #print "PCIBL:".join(", ", @pciid_blacklist);
    printf("       MB fingerprint found (postid: %d, %.1f%% match) \n",$mindex,$mb_recognition*100);#    @pciid_blacklist";
    #
    # We have the motherboard's postid
    # now do the real scan&storage
    #
    open(FILE, "<", "/var/www/uploads/".$sid."/lspci.txt");
    $i=0;
    @knownpciids = qw();
    print "       Mainboard based blacklist created\n";
    #print "Blacklist:".join(", ", @pciid_blacklist)."\n";

    while ( <FILE> ) {
        #print "Line: $_";
        
        $pciid   = grab_pciid($_);
        $pciname = grab_pciname($_);
        

        # ignore empty IDs and duplicates
        if ( ($pciid != "") && (! ($pciid ~~ @knownpciids)) )  {
            
            push(@knownpciids, $pciid);
            #print "PCIKnown:".join(", ", @knownpciids)."\n";

            #@postid = checkdb_pci($pciid);
            #print "PCI: $pciid - #Postids: ".scalar(@postid)."\n";
            
            if ( ( $pciid ~~ @pciid_blacklist  ) ) {
                # MB component
                #print "found $pciid  \n";
                storescan_mainboard_found($sid, $mindex, $pciid, $pciname);
        #    } elsif ($postid == -1) {
        #        #print "ignored; $postid; $usbid; $usbname \n";
        #    } else {
        #        storescan_pci_found($sid, $postid, $pciid, $pciname);
            }
            $i++;
        }
    }
    
    
}

sub storescan_mainboard_found {
    my $sid      = shift;
    my $postid   = shift;
    my $pciid    = shift;
    my $pciname  = shift;
    
    
    if ($rescan == 0) {
            $lhg_db = DBI->connect($database, $user, $pw);
            $myquery = "INSERT INTO `lhghwscans` (sid, postid, pciid, idstring, scantype) VALUES (? ,? , ?, ?, ?)";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($sid, $postid, $pciid, $pciname, "mainboard");
    }else{
            #print "RESCANNING";
            #find id to update
            $lhg_db = DBI->connect($database, $user, $pw);
            $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND pciid = ?";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($sid, $pciid);
            ($id) = $sth_glob->fetchrow_array();

            $myquery = "UPDATE `lhghwscans` SET postid = ? , scantype = ?  WHERE id = ?";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($postid, "mainboard", $id);

            # do not add entries again
            #print "PID: $postid - PCIID: $pciid - ID: $id \n";
            
            if ($id == "") {
                # something went wrong. Article not in HW library, although should be
                # have to create it (probably due to bad SQL filling in the past)
                print "Error: missing article in DB -> create $sid, $postid, $pciid, $pciname \n";
                $myquery = "INSERT INTO `lhghwscans` (sid, postid, pciid, idstring, scantype) VALUES (? ,? , ?, ?, ?)";
                $sth_glob = $lhg_db->prepare($myquery);
                $sth_glob->execute($sid, $postid, $pciid, $pciname, "mainboard");
            
            }
    }
    
}

sub storescan_mainboard_found_usb {
    my $sid      = shift;
    my $postid   = shift;
    my $pciid    = shift;
    my $pciname  = shift;
    
    
    if ($rescan == 0) {
            $lhg_db = DBI->connect($database, $user, $pw);
            $myquery = "INSERT INTO `lhghwscans` (sid, postid, usbid, idstring, scantype) VALUES (? ,? , ?, ?, ?)";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($sid, $postid, $pciid, $pciname, "mainboard");
    }else{
            #print "RESCANNING";
            #find id to update
            $lhg_db = DBI->connect($database, $user, $pw);
            $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND usbid = ?";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($sid, $pciid);
            ($id) = $sth_glob->fetchrow_array();

            $myquery = "UPDATE `lhghwscans` SET postid = ? , scantype = ?  WHERE id = ?";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($postid, "mainboard", $id);

            # do not add entries again
            #print "ID: $id   -   ";
    }
    
}


sub findMaxValueIndex() {
    @array = @_ ;
    #print "SA: ".scalar(@array);
    my $max = 0;
    for (my $i = 0 ; $i < scalar(@array); $i++) {
        #print "i: $i - $array[$i] \n";
        if ($array[$i] > $max) {
            #print "FOUND";
            $max = $array[$i];
            $index = $i;
        }
    }
    return $index;
}

sub grab_subsystemid {
    my $line = shift;
    
    #print "-------\nLine: $line ";

    if ( ($line =~ m/Subsystem/) and ($line =~ m/\w\w\w\w:\w\w\w\w/) )  {
        my $pciid = substr($line,$-[0],9);
        #print "PCIID: $pciid \n";
        return $pciid;
    } else {
        #print "   no match \n";
    }
    
}

sub grab_mainboardname {
    $line = shift;
    
    #print "-------\nLine: $line ";

    if ( ($line =~ m/\w\w\w\w:\w\w\w\w/) and !($line =~ m/Subsystem/) ) {
        $pciname = substr($line,8);
        #print "PCIname: $pciname \n";
        return $pciname;
    } else {
        #print "   no match \n";
    }
    
}




#
#
###  USB subroutines
#
#

sub check_usb_devices {
    
    print "-----> Checking USB devices \n";
    @usb_known = qw ();
    
    open(FILE, "<", "/var/www/uploads/".$sid."/lsusb.txt");
    $i=0;
    while ( <FILE> ) {
        #print "Line: $_ \n";
        
        $usbid   = grab_usbid($_);
        $usbname = grab_usbname($_);
        #print "\n ----\nUSBID: $usbid \n";
        
        if (!($usbid ~~ @usbid_blacklist)) {
            # not mainboard component
            #print "USBID-after: $usbid \n";
            
            #print "New: $usbid - All: ".join(", ", @usb_known)."\n";

            if ( ($usbid ne "") && (! ($usbid ~~ @usb_known) ) ) {
                #@postids = "123";
                #print "USBID Found: $usbid \n";
                push ( @usb_known , $usbid );
                
                @postids = checkdb_usb($usbid, $usbname);
                
                if ($usbid ~~ @lur_uids){
                    #it's a Logitech Unifying Receiver - redirect to receiver page
                    #The actually paired device will be recognized separately
                    print "       Logitech Unifying Receiver found \n";
                    @postids="";
                    $postids[0] = 21006;
                }
                    
                if ( scalar(@postids) > 1 ) {
                    print "too many results (".scalar(@postids).")- not smart enough... please program me \n";
                    #print "PIDS:".join(", ", @postids);
                    $postid = smartsearch_usb($usbid,@postids);
                    #print "PIDSS: $postid \n";
                }else{
                    $postid = $postids[0];
                }

                #print "PID: $postid \n";

                if ($postid eq "") {
                    #print "PID: empty \n";
                    storescan_usb_notfound($sid, $usbid, $usbname);
                } elsif ($postid == -1) {
                    #print "PID: ignoring \n";
                    #print "ignored; $postid; $usbid; $usbname \n";
                }else{
                    #print "PID: storing \n";
                    storescan_usb_found($sid, $postid, $usbid, $usbname);
                }
            }else{
                print "USBID is empty or known ($usbid)! \n";
                # or usb id already added
            }

        }else{
            print "Mainboard USB component found ($usbid) \n";
            
            # add to Mainboard DB
            storescan_mainboard_found_usb($sid, $mindex, $usbid, $usbname);
        }    
        $i++;
        #print "\n";
    }
}

sub grab_usbid {
    $line = shift;
    
    #print "-------\nLine: $line ";

    if ($line =~ m/\w\w\w\w:\w\w\w\w/) {
        return substr($line,$-[0],9);
    } else {
        #print "   no match \n";
    }
    
}

sub grab_usbname {
    $line = shift;
    
    #print "-------\nLine: $line ";

    if ($line =~ m/\w\w\w\w:\w\w\w\w/) {
        return substr($line,$-[0]+9,-1);
    } else {
        #print "   no match \n";
    }
    
}

sub checkdb_usb {
    my $usbid   = shift;
    my $usbname = shift;
    
    # filter hubs
    if ($usbname =~ /root hub/) {
        #ignore
        return -1;
    }

    $lhg_db = DBI->connect($database, $user, $pw);
    my $myquery = "SELECT postid_com FROM lhgtransverse_posts WHERE usbids like ? AND (status_com = \"published\" OR status_com = \"\") ";   
    my $sth_glob = $lhg_db->prepare($myquery);
    $search = "%".$usbid."%";
    $sth_glob->execute($search);
    #($postid, $id) = $sth_glob->fetchrow_array();
    my @postids = @{$lhg_db->selectcol_arrayref($sth_glob)};
    
    #print "SQLres:".join(", ", @postids);

    
    @foundpostids = ();
    # we have several findings. Skip mainboards
    foreach my $postid (@postids) {
        # identify mainboards by PCI IDs, not USB IDs
        my $Npciids = get_number_pciids($postid);
        #print "NPCI: $Npciids -- \n";
        #print "found Pciid: $pciid -> PDI: $postid \n";
        $identifiedmb[int($postid)] +=1;
        
        #print "HERE \n";
        # number of pciids <7 -> does not look like a mainboard
        if ($Npciids < 7) {
            #print "PID: $postid \n";
            push ( @foundpostids, $postid);
        }
    }

    #print "Search: $search -> $postid -> $id \n";
    #return $postid;
    return @foundpostids;

}

sub storescan_usb_notfound {
    my $sid   = shift;
    my $usbid   = shift;
    my $usbname = shift;
    
    #ToDo: Sanity checks necessary!!
    if ($rescan == 0) {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, usbid, idstring) VALUES (? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute( $sid, $usbid, $usbname);
    }else{
        # do not add entries again
    }
}

sub storescan_mainboard_found_usb {
    my $sid = shift;
    my $mindex = shift;
    my $usbid = shift;
    my $usbname = shift;
    
    if ($rescan == 0) {
        # is this ever relevant?
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, postid, usbid, idstring) VALUES (? ,? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $mindex, $usbid, $usbname);
    }else{
        #print "RESCANNING";
        #find id to update
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND usbid = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $usbid);
        ($id) = $sth_glob->fetchrow_array();
        
        $myquery = "UPDATE `lhghwscans` SET postid = ? WHERE id = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($mindex, $id);

        # do not add entries again
        #print "ID: $id   -   ";
    }


}

sub storescan_usb_found {
    my $sid     = shift;
    my $postid  = shift;
    my $usbid   = shift;
    my $usbname = shift;
    
    #print "PID Write: $postid \n";
    #ToDo: Sanity checks necessary!!
    #print "RESCAN: $rescan";
    if ($rescan == 0) {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, postid, usbid, idstring) VALUES (? ,? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $postid, $usbid, $usbname);
    }else{
        #print "RESCANNING";
        #find id to update
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND usbid = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $usbid);
        ($id) = $sth_glob->fetchrow_array();
        
        $myquery = "UPDATE `lhghwscans` SET postid = ? WHERE id = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($postid, $id);

        # do not add entries again
        #print "ID: $id   -   ";
    }
    
    #multiple findings! (comma separated postids)
    if ($postid =~ m/,/) {
        #print "DB: Multiple results \n";
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND usbid = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $usbid);
        ($id) = $sth_glob->fetchrow_array();
        
        $myquery = "UPDATE `lhghwscans` SET scantype = ? WHERE id = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $scantype = "multiple_results";
        $sth_glob->execute($scantype, $id);
    }else {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND usbid = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $usbid);
        ($id) = $sth_glob->fetchrow_array();

        $myquery = "UPDATE `lhghwscans` SET scantype = ? WHERE id = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $scantype = "";
        $sth_glob->execute($scantype, $id);
    }
}


sub smartsearch_usb {
    my $usbid = shift;
    my @postids = @_;
    
    print "smartsearch=> postids: ".join(", ", @postids)."\n";

    
    print "smartsearch=> still dumb ... \n";
    
    #if smartsearch failed, return all results (comma separated)
    $postid_result = join(",", @postids);
        #    $postids[0];
    
    return $postid_result;

}

#
#
### store metadata 
#
#

sub create_db_metadata {
    
    # check if first scan
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT id FROM lhgscansessions WHERE sid = ?";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute($sid);
    ($id) = $sth_glob->fetchrow_array();
    
    
    # set UID if available
    #
    open(FILE, "<", "/var/www/uploads/".$sid."/version.txt");
    $i=0;
    while ( <FILE> ) {
        if ( substr($_,0,5) eq "UID =" ) {
            $uid = substr($_,5,-1);
            $uid =~ s/ //;
            $uid =~ s/\n//;
        }
        if ( substr($_,0,8) eq "LHGUID =" ) {
            $lhguid = substr($_,8,-1);
            $lhguid =~ s/ //;
            $lhguid =~ s/\n//;
        }
        if ( substr($_,0,10) eq "LHGUIDDE =" ) {
            $lhguidde = substr($_,10,-1);
            $lhguidde =~ s/ //;
            $lhguidde =~ s/\n//;
        }
    }
    
    #if ($uid ne "") {
    if ($uid eq " \\n"){ $uid = "none"; }
    if ($uid eq "")  { $uid = "none"; }
        print "UID: ".trim($uid)."\n";
    
    if ($lhguid eq " \\n"){ $lhguid = "none"; }
    if ($lhguid eq "")  { $lhguid = "none"; }
    if ($lhguidde eq " \\n"){ $lhguidde = "none"; }
    if ($lhguidde eq "")  { $lhguidde = "none"; }
    print "LHGUID: $lhguid \n";
    print "LHGUID.de: $lhguidde \n";
#    $lhg_db = DBI->connect($database, $user, $pw);
    #    $myquery = "INSERT INTO `lhgscansessions` (uid) VALUES (?)";   
    #    $sth_glob = $lhg_db->prepare($myquery);
    #    $sth_glob->execute($uid);
    #}
    
    
   
    # set scan time
    #
    $k_version = get_kernel_version();
    $distribution = get_linux_distribution();
    if ($distribution eq "") { $distribution = "unknown";}
    
    $scandate = time;
    #print "ID: $id";
    
    if ($id == "") {
        # fist scan store info 
        # Scandate needs to be set at the end of the script (see cleanup() routine)
        # Otherwise, data processing can
        # take too long and web site can not see if data was alread processed
        $scandate_ongoing = 1;
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhgscansessions` (sid, scandate, uid, kversion, distribution, wp_uid, wp_uid_de) VALUES (? ,? ,? ,? ,?, ?, ?)";   
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $scandate_ongoing, $uid, $k_version, $distribution, $lhguid, $lhguidde);
        $cycle=0;
        create_pub_id();
        return $cycle;
    }else {
        # running again
        $cycle=1;
        
        # insert user id
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "UPDATE `lhgscansessions` SET uid = ? , kversion = ? , distribution = ? WHERE id = ?";   
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($uid, $k_version, $distribution, $id );
        
        # do not overwrite UIDs
        if ( ($lhguid != "") && ($lhguid != 0) ){
            $lhg_db = DBI->connect($database, $user, $pw);
            $myquery = "UPDATE `lhgscansessions` SET wp_uid = ? WHERE id = ?";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($lhguid, $id );
        }
        if ( ($lhguidde != "") && ($lhguidde != 0) ){
            $lhg_db = DBI->connect($database, $user, $pw);
            $myquery = "UPDATE `lhgscansessions` SET wp_uid_de = ? WHERE id = ?";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($lhguidde, $id );
        }

        create_pub_id();

        return $cycle;
    }
    
    
    
}

sub create_pub_id {

    # create pub_id string
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT pub_id FROM lhgscansessions WHERE sid = ?";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute($sid);
    ($pub_id) = $sth_glob->fetchrow_array();
    
    #print "PubID0: $pub_id\n";
    

    if ($pub_id eq "") {
        my @chars = ("A".."Z", "a".."z", "0".."9");
        my $pub_id;
        $pub_id .= $chars[rand @chars] for 1..30;
        print "PubID: $pub_id\n";
        
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "UPDATE `lhgscansessions` SET pub_id = ? WHERE sid = ?";   
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($pub_id, $sid);

    }
}


sub get_kernel_version {
    
    open(FILE_V, "<", "/var/www/uploads/".$sid."/version.txt");
    
    #
    #Searching for Kernel version
    #
    $i=0;
    while ( <FILE_V> ) {
        #print "Line: $_";
        $pos = index($_,"Linux version ");
        
        if ($pos > -1) {
            #print "POS: $pos \n";
            $version_tmp = substr($_,$pos+14);
            ($version, $null) = split(/ /,$version_tmp);
            #print "V: $version \n";
        }
        
    }
    
    print "Kernel: $version\n";
    return $version;
    
}

sub get_linux_distribution {
    
    #
    #Searching for Distribution
    #
    
    open(FILE_R, "<", "/var/www/uploads/".$sid."/lsb_release.txt");

    # works for Ubuntu
    $i=0;
    while ( <FILE_R> ) {
        
        # Fedora introduces new lsb_release format...
        if ( index($_,"Fedora") == 0 ) {
            
            $dist = trim($_);
            break;
        }
        
        #print "Line: $_";
        $pos = index($_,"Description:");
        
        if ($pos > -1) {
            $dist = substr($_,$pos+12);
            #print "A: $dist \n";
            #remove leading " "
            #print "S:".index($dist," ");
            while (index($dist,"\t") == 0) {
                $pos2 = index($dist," ");
                $dist = substr($dist,1);
            }
            #    print "POS: $pos \n";
            #$dist =
        #    ($version, $null) = split(/ /,$version_tmp);
            #print "D: $dist \n";

        }
        
    }
    close FILE_R;
    
    if ($dist ne "") {
        
        # Clean Gentoo name
        if ( index($dist,"NAME=") == 0) {
            $dist = trim(substr($dist,$pos+6));
        }

        print "Distribution: $dist \n";
        return $dist;
    }
    
    # identify by /etc/*-release output 
    if ($dist eq "") {
        print "Error: Distribution not recognized ... fallback 1";
        open(FILE_R, "<", "/var/www/uploads/".$sid."/lsb_release.txt");
        while ( <FILE_R> ) {
        #print "Line: $_";
        $pos = index($_,"NAME=");
        
        if ($pos == 0) {
            $dist = substr($_,$pos+6,-2);
            #remove leading " "
            #while (index($dist,"\t") == 0) {
            #    $pos2 = index($dist," ");
            #    $dist = substr($dist,1);
            #}
            #    print "POS: $pos \n";
            #$dist =
        #    ($version, $null) = split(/ /,$version_tmp);
            #print "D: $dist \n";

        }
        }
        close FILE_R;
    }
    
    # identify by miscellaneous output 
    if ($dist eq "") {
        print ",2";
        open(FILE_R, "<", "/var/www/uploads/".$sid."/lsb_release.txt");
        
        while ( <FILE_R> ) {
            if ($_  =~ /ALT Linux/) {
                $dist = "ALT Linux";
                break;
            }
        }
        close FILE_R;
    }

    # Recognize by kernel - not very reliable
    if ($dist eq "") {
        print ",3";
        if ($kversion  =~ /-ARCH/) {
            $dist = "Arch Linux";
        }
    }
    
    print "\n";
    print "Distribution: $dist \n";
    return $dist;
}

sub send_mail_with_scaninputs {
    
    #    
    #use MIME::Lite;
    #
    #$to   = 'webmaster@linux-hardware-guide.com';
    #$from = 'libaray@linux-hardware-guide.com';
    #$subject = 'automatic HW scan';
    #$message = 'The following scan results were collected';
    #
    #$msg = MIME::Lite->new(
    #             From     => $from,
    #             To       => $to,
    #             Subject  => $subject,
    #             Type     => 'multipart/mixed'
    #             );
    #             
    ## Add text
    #$msg->attach(Type         => 'text',
    #             Data         => $message
    #            );
    #
    ## Specify your file as attachement.
    #$msg->attach(Type        => 'text/txt',
    #             Path        => '/var/www/uploads/'.$sid.'/dmesg.txt',
    #             Filename    => 'dmesg.txt',
    #             Disposition => 'attachment'
    #            );
    #
    #$msg->send;
    
}


#
#
### PCI subroutines
#
#

sub check_pci_devices {
    
    print "-----> Checking PCI devices \n";
    
    # We have a unknown laptop 
    #$laptop_probability = calculate_laptop_probability ( $sid );
    #print "LP: $laptop_probability";
    if ( ($laptop_probability > 0.9) && ($laptop_identified != 1 ) )  {
        print "Rule 4: Unknwon Laptop found - no separate PCI devices possible \n";
        open(FILE1, "<", "/var/www/uploads/".$sid."/lspci.txt");
        $i=0;
        while ( <FILE1> ) {
            #print "Line: $_";
        
            $pciid   = grab_pciid($_);
            $pciname = grab_pciname($_);
            
            if ($pciid ne "") {
                #print "ID: $pciid - name: $pciname \n";
                # also ignore empty ids and duplicates
                storescan_pci_notfound($sid, $pciid, $pciname);
                $i++;
            }
            
        }
        
        return;
    }
        
    
    # We have no laptop
    $i=0;
    @knownpciids = qw();
    open(FILE2, "<", "/var/www/uploads/".$sid."/lspci.txt");
    while ( <FILE2> ) {
        #print "Line: $_";
        
        $pciid   = grab_pciid($_);
        $pciname = grab_pciname($_);
        
        undef @postids;
        
        if (!( $pciid ~~ @pciid_blacklist )) {
            # PCI ID not allowed to be on blacklist (e.g. mainboard component)
            
            # also ignore empty ids and duplicates
            if ( ($pciid ne "") && (! ($pciid ~~ @knownpciids)) ){
                
                push(@knownpciids, $pciid);
                #print "PCIID: $pciid -  \n";
                @postids = checkdb_pci($pciid);
                #print "options: ".scalar(@postids);
                
                # use first result as default
                $postid = $postids[0];

                # check if this is a mainboard -> try next option
                for (my $i; $i < scalar(@postids); $i++){
                    if ( postid_is_mainboard( $postids[$i] ) ) {
                        print "-> skip! \n";

                        # check if we have more results
                        if ( defined( $postids[$i+1] ) ) {
                            print "OK. Next try";
                        }else{
                            # nothing left, so ignore this result
                            $postid = "";
                            break;
                        }
                    }else{
                        $postid = $postids[$i];
                    }

                }
                
                
                if ( scalar(@postids) > 1 ) {
                    print "too many results - not smart enough... please program me \n";
                }
            
                #print "PID: $postid - $postids[0] - $postids[1] \n";
                if ($postid == "") {
                    storescan_pci_notfound($sid, $pciid, $pciname);
                } elsif ($postid == -1) {
                    #print "ignored; $postid; $usbid; $usbname \n";
                } else {
                    storescan_pci_found($sid, $postid, $pciid, $pciname);
                }
                $i++;
            }
        }else{
            #print "blacklisted device: $pciid \n";
        }
        
        #print "\n";
    }
}

sub grab_pciid {
    $line = shift;
    
    #print "-------\nLine: $line ";

    if ( ($line =~ m/\w\w\w\w:\w\w\w\w/) and !($line =~ m/Subsystem/) )  {
        $pciid = substr($line,$-[0],9);
        #print "PCIID: $pciid \n";
        return $pciid;
    } else {
        #print "   no match \n";
    }
    
}

sub grab_pciname {
    $line = shift;
    
    #print "-------\nLine: $line ";

    if ( ($line =~ m/\w\w\w\w:\w\w\w\w/) and !($line =~ m/Subsystem/) ) {
        $pciname = substr($line,8);
        #print "PCIname: $pciname \n";
        return $pciname;
    } else {
        #print "   no match \n";
    }
    
}

sub checkdb_pci {
    my $pciid   = shift;
    
    # filter hubs
    #if ($usbname =~ /root hub/) {
    #    #ignore
    #    return -1;
    #}
    
    #print "Search for: $pciid";
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT postid_com FROM lhgtransverse_posts WHERE pciids like ? AND (status_com = \"published\" OR status_com = \"\") ";   
    $sth_glob = $lhg_db->prepare($myquery);
    $search = "%".$pciid."%";
    $sth_glob->execute($search);
    #($postid) = $sth_glob->fetchrow_array();
    @postids = @{$lhg_db->selectcol_arrayref($sth_glob)};
    undef @foundpostids;
    
    # we have several findings. Skip mainboards
    foreach $postid (@postids) {
        $Npciids = get_number_pciids($postid);
        #print "NPCI: $Npciids -- \n";
        #print "found Pciid: $pciid -> PDI: $postid \n";
        $identifiedmb[int($postid)] +=1;
        
        # number of pciids <7 -> does not look like a mainboard
        if ($Npciids < 7) {
            push ( @foundpostids, $postid);
        }
    }

    #print "Postid: $postid";
    #return $postids[0];
    #return $foundpostids[0];
    #print "Number: ".scalar(@foundpostids)."\n";
    return @foundpostids;

}

sub checkdb_mainboard_pci {
    my $pciid   = shift;
    
    # filter hubs
    #if ($usbname =~ /root hub/) {
    #    #ignore
    #    return -1;
    #}
    
    #print "Search for: $pciid";
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT postid_com FROM lhgtransverse_posts WHERE pciids like ? AND (status_com = \"published\" OR status_com = \"\") ";   
    $sth_glob = $lhg_db->prepare($myquery);
    $search = "%".$pciid."%";
    $sth_glob->execute($search);
    my @postids = @{$lhg_db->selectcol_arrayref($sth_glob)};
    
    #print "Postids-found: ".scalar(@postids)." ";
    return @postids;

}

sub get_number_pciids {
    # how many PCI IDs do we expect for a motherboard?
    my $postid   = shift;
    
    # filter hubs
    #if ($usbname =~ /root hub/) {
    #    #ignore
    #    return -1;
    #}
    
    #print "Search for: $pciid";
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT pciids FROM lhgtransverse_posts WHERE postid_com = ? AND (status_com = \"published\" OR status_com = \"\") ";   
    $sth_glob = $lhg_db->prepare($myquery);
    $search = $postid;
    $sth_glob->execute($search);
    my $pciids = $sth_glob->fetchrow_array();
    # separated by , or by ;
    my @pciarray=();
    if (index($pciids, ",") != -1) {
        @pciarray = split(',',$pciids);
    }
    if (index($pciids, ";") != -1) {
        @pciarray = split(';',$pciids);
    }
    
    
    $Npciids = scalar(@pciarray);
    #print "PID: $postid -- $pciids -- ";
    return $Npciids;

}

sub get_number_unique_pciids {
    #get number of unique pciids of mainboard with article id $postid
    my $postid   = shift;
    
    # get array of pciids
    # 
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT pciids FROM lhgtransverse_posts WHERE postid_com = ? AND (status_com = \"published\" OR status_com = \"\") ";   
    $sth_glob = $lhg_db->prepare($myquery);
    $search = $postid;
    $sth_glob->execute($search);
    my $pciids = $sth_glob->fetchrow_array();
    # separated by , or by ;
    my @pciarray=();
    if (index($pciids, ",") != -1) {
        @pciarray = split(',',$pciids);
    }
    if (index($pciids, ";") != -1) {
        @pciarray = split(';',$pciids);
    }

    # cycle through pciids to get array of unique elements
    my @pciarray_unique = qw();
    for (my $i = 0; $i < scalar(@pciarray); $i++) {
        if ( $pciarray[$i] ~~ @pciarray_unique ) {
            # already in array -> skip
        } else {
            push @pciarray_unique , $pciarray[$i];
        }
    }
    #print "unique PCIIDs: PID $postid: ".scalar(@pciarray)." -> ".scalar(@pciarray_unique)."\n";
    return scalar(@pciarray_unique);
}

sub get_pciid_blacklist {
    # which pciids to skip during later recognition
    # returns an array of PCI IDs associated with the mainboard
    my $postid   = shift;
    
    #print "PID -->". $postid;
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT pciids FROM lhgtransverse_posts WHERE postid_com = ? AND (status_com = \"published\" OR status_com = \"\") ";   
    $sth_glob = $lhg_db->prepare($myquery);
    $search = $postid;
    $sth_glob->execute($search);
    my $pciids = $sth_glob->fetchrow_array();
    my @pciarray=();
    if (index($pciids, ",") != -1) {
        @pciarray = split(',',$pciids);
    }
    if (index($pciids, ";") != -1) {
        @pciarray = split(';',$pciids);
    }
    return @pciarray;

}

sub get_usbid_blacklist {
    #which pciids to skip during later recognition
    my $postid   = shift;
    
    #print "PID -->". $postid;
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT usbids FROM lhgtransverse_posts WHERE postid_com = ? AND (status_com = \"published\" OR status_com = \"\")";   
    $sth_glob = $lhg_db->prepare($myquery);
    $search = $postid;
    $sth_glob->execute($search);
    my $usbids = $sth_glob->fetchrow_array();
    my @usbarray = split(',',$usbids);
    return @usbarray;

}

sub storescan_pci_notfound {
    my $sid   = shift;
    my $pciid   = shift;
    my $pciname = shift;
    
    #ToDo: Sanity checks necessary!!
    
    if ($rescan == 0) {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, pciid, idstring) VALUES (? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute( $sid, $pciid, $pciname);
    }else{
        # nothing
    }
}

sub storescan_pci_found {
    my $sid     = shift;
    my $postid  = shift;
    my $pciid   = shift;
    my $pciname = shift;
    
    #ToDo: Sanity checks necessary!!

    if ($rescan == 0) {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, postid, pciid, idstring) VALUES (? ,? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $postid, $pciid, $pciname);
    }else{
        #print "RESCANNING";
        #find id to update
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND pciid = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $pciid);
        ($id) = $sth_glob->fetchrow_array();
        
        $myquery = "UPDATE `lhghwscans` SET postid = ? WHERE id = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($postid, $id);

    }
}


#
#
### CPU subroutines
#
#

sub check_cpu {
    
    print "-----> Checking CPU \n";
    
    open(FILE, "<", "/var/www/uploads/".$sid."/cpuinfo.txt");
    $i=0;
    while ( ( $_ = <FILE> ) and ($i == 0) ) {
        #print "Line: $_";
        
        if ($_ =~ m/model name/) {
            
            $pos=index($_,":");
            $cpuname = substr($_,$pos+2,-1);
            #print "CPU: $cpuname";
            
            $postid = checkdb_cpu($cpuname);
            
            if ($postid == "") {
                storescan_cpu_notfound($sid, $cpuname);
            } elsif ($postid == -1) {
                #print "ignored; $postid; $usbid; $usbname \n";
            } else {
                storescan_cpu_found($sid, $postid, $cpuname);
            }
            $i++;
        #print "\n";
        }
    }
}

sub checkdb_cpu {
    my $cpuname = shift;
    
    # filter hubs
    #if ($usbname =~ /root hub/) {
    #    #ignore
    #    return -1;
    #}

    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT postid_com FROM lhgtransverse_posts WHERE idstring like ? AND (status_com = \"published\" OR status_com = \"\")";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute($cpuname);
    ($postid) = $sth_glob->fetchrow_array();
    
    #print "Postid: $postid";
    return $postid;

}

sub storescan_cpu_notfound {
    my $sid   = shift;
    my $cpuname   = shift;
    my $scantype = "cpu";
    
    #ToDo: Sanity checks necessary!!
    
    if ($rescan == 0) {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, idstring, scantype) VALUES (? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute( $sid, $cpuname, $scantype);
    }else{
        #nothing
    }
}

sub storescan_cpu_found {
    my $sid     = shift;
    my $postid  = shift;
    my $cpuname = shift;
    my $scantype = "cpu";
    
    #ToDo: Sanity checks necessary!!

    if ($rescan == 0) {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, postid, idstring, scantype) VALUES (? ,? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $postid, $cpuname, $scantype);
    }else{
        #print "RESCANNING";
        #find id to update
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND idstring = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $cpuname);
        ($id) = $sth_glob->fetchrow_array();
        
        $myquery = "UPDATE `lhghwscans` SET postid = ? WHERE id = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($postid, $id);

    }

    
}


#
#
### Find drives + subroutines
#
#

sub check_drives {
    
    my @known_drivenames = qw( );
    #print "All: ".join(", ", @known_drivenames);
    
    print "-----> Checking Drives \n";
    
    open(FILE, "<", "/var/www/uploads/".$sid."/dmesg.txt");
    $i=0;
    
    while ( ( $_ = <FILE> ) ) {
        
        if ($_ =~ m/ANSI: /) {
            
            #extract drive name, removes firmware vesion
            $drivename = substr($_,29,-20);
            
            #print "Drivename: $drivename\n";

            #many disks or many wake-ups cause high numbers
            $sp = index($drivename,"Direct-Access");
            if ($sp > 0) {
                $drivename = substr($drivename,$sp);
            }
            # print "DN: $drivename (sp: $sp) \n\n";
            
            if (substr($drivename,0,3) eq ":0:") {
                $drivename = substr($drivename,3);
            }
            
            if (substr($drivename,0,3) eq "0: ") {
                $drivename = substr($drivename,3);
            }
            
            if (substr($drivename,0,3) eq "1: ") {
                $drivename = substr($drivename,3);
            }
            
            while (substr($drivename,0,1) eq ":") {
                $drivename = substr($drivename,1);
            }

            while (substr($drivename,0,1) eq " ") {
                $drivename = substr($drivename,1);
            }
            
            
    
            while (substr($drivename,-1,1) eq " ") {
                $drivename = substr($drivename,0,-1);
            }
                
            
            #print "Final-Drive: $drivename \n";
            
            
            if (!( $drivename ~~ @known_drivenames )) {
              if ($drivename ne "") {
                 push(@known_drivenames, $drivename);

                $postid = checkdb_drive($drivename);
            
                if ($postid == "") {
                    storescan_drive_notfound($sid, $drivename);
                } elsif ($postid == -1) {
                    #print "ignored; $postid; $usbid; $usbname \n";
                } else {
                    storescan_drive_found($sid, $postid, $drivename);
                }
              }
            }else{
                #print "duplicate \n";
            }
            
            #print "All: ".join(", ", @known_drivenames);

            
            $i++;
        #print "\n";
        }
    }
}

sub checkdb_drive {
    my $drivename = shift;
    
    # filter hubs
    #if ($usbname =~ /root hub/) {
    #    #ignore
    #    return -1;
    #}
    
    #print "SEARCH: > $drivename < \n";
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT postid_com FROM lhgtransverse_posts WHERE idstring like ? AND (status_com = \"published\" OR status_com = \"\")";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute("%".$drivename."%");
    ($postid) = $sth_glob->fetchrow_array();
    
    #print "Postid: $postid \n";
    return $postid;

}


sub storescan_drive_notfound {
    my $sid   = shift;
    my $drivename   = shift;
    my $scantype = "drive";
    
    #ToDo: Sanity checks necessary!!
    
    if ($rescan == 0) {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, idstring, scantype) VALUES (? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute( $sid, $drivename, $scantype);
    }else{
        #nothing
    }
}

sub storescan_drive_found {
    my $sid     = shift;
    my $postid  = shift;
    my $drivename = shift;
    my $scantype = "drive";
    
    #ToDo: Sanity checks necessary!!

    if ($rescan == 0) {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, postid, idstring, scantype) VALUES (? ,? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $postid, $drivename, $scantype);
    }else{
        #print "RESCANNING \n";
        #find id to update
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND idstring = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($sid, $drivename);
        ($id) = $sth_glob->fetchrow_array();
        
        #print "ID: $id \n";
        
        $myquery = "UPDATE `lhghwscans` SET postid = ? WHERE id = ?";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($postid, $id);

    }
}


#
#
#### Rescan requested
#
#
#

#sub delete_old_scan_results {
#    my $sid     = shift;
#    
#    $lhg_db = DBI->connect($database, $user, $pw);
#    $myquery = "DELETE FROM `lhghwscans` WHERE id = ?";   
#    $sth_glob = $lhg_db->prepare($myquery);
#    $sth_glob->execute($sid);
#
#}


#
#
#### Clear request
#
#
#

sub clear_db {
    print "Cleaning DB -> $sid \n";
    my $sid     = shift;
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "DELETE FROM `lhghwscans` WHERE sid = ?";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute($sid);

}



#
#
###  Logitech Unifying Receivers subroutines
#
#

sub check_unifying_receivers {
    
    print "-----> Checking Logitech Unifying Receivers \n";
    
   
    open(FILE, "<", "/var/www/uploads/".$sid."/lsusb.txt");
    $i=0;
    $lur_found = 0;
    
    while ( <FILE> ) {
        #print "Line: $_ \n";
        $usbid   = grab_usbid($_);
        $usbname = grab_usbname($_);
        
        #print "\n ----\nUSBID: $usbid \n";
        
        if ($usbid ~~ @lur_uids) {
            $lur_found = 1;
        }
    }
        
    if ($lur_found == 0){
            #no LUR found
            return;
    }    
        
    
    @lurnames = get_lur_devices();
    if ( scalar(@lurnames) > 0 ) {
        store_lur_data(@lurnames);
    }else{
        #No names were extracted
        return;
    }
    #print "LURs:".join(", ", @lurnames);
        
    
    #    if ($usbid ne "") {
    #            #@postids = "123";
    #            #print "USBID Found: $usbid \n";
    #            @postids = checkdb_usb($usbid, $usbname);
    #
    #            if ( scalar(@postids) > 1 ) {
    #                print "too many results (".scalar(@postids).")- not smart enough... please program me \n";
    #                #print "PIDS:".join(", ", @postids);
    #                $postid = smartsearch_usb($usbid,@postids);
    #                #print "PIDSS: $postid \n";
    #            }else{
    #                $postid = $postids[0];
    #            }
    #
    #            #print "PID: $postid \n";
    #
    #            if ($postid eq "") {
    #                #print "PID: empty \n";
    #                storescan_usb_notfound($sid, $usbid, $usbname);
    #            } elsif ($postid == -1) {
    #                #print "PID: ignoring \n";
    #                #print "ignored; $postid; $usbid; $usbname \n";
    #            }else{
    #                #print "PID: storing \n";
    #                storescan_usb_found($sid, $postid, $usbid, $usbname);
    #            }
    #        }else{
    #            #print "USBID is empty! \n";
    #        }
    #
    #    $i++;
    #    #print "\n";
    
}

sub get_lur_devices {
    
    print "       Receiver found. Paired devices:";
    
    # extract solaar information from dmesg
    open(FILE, "<", "/var/www/uploads/".$sid."/dmesg.txt");
    $i=0;
    $lur_found = 0;
    
    while ( <FILE> ) {
        
        #get number of devices
        if ($_ =~ m/solaar-cli output/) {
            $empty = <FILE>;
            $numdevices_tmp = <FILE>;
            $pos_with = index($numdevices_tmp,"with");
            $pos_device = index($numdevices_tmp,"device");
            $numdevices = substr($numdevices_tmp,$pos_with+5, $pos_device-$pos_with-5);
            print " $numdevices \n";
            
            for ($j=0; $j<$numdevices; $j++) {
                #Extract device
                $devicename_tmp = <FILE>;
                $pos_dd = index($devicename_tmp,": ");
                $pos_br = index($devicename_tmp," [");
                $devicename = substr($devicename_tmp,$pos_dd+2, $pos_br-$pos_dd-2);

                print "       Device found $j: $devicename \n";
                push(@foundnames, $devicename);

            }
        }
    }
    return @foundnames;
}

sub store_lur_data {
    @array = @_ ;
    
    #write data to db
    for ($i=0; $i<scalar(@array);$i++) {
        $dstrg = @array[$i];
        #print "        Device store: ".$dstrg;
        $postid = get_lur_postid($dstrg);
        if ($postid ne "") {
            write_lur_data($sid, $postid, $dstrg);
        }else{
            print "       *** $dstrg not found in DB\n";
            write_lur_data_notfound($sid, $dstrg);
        }
    }
}

sub get_lur_postid {
    my $lurname = shift;
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT postid_com FROM lhgtransverse_posts WHERE product like ? AND (status_com = \"published\" OR status_com = \"\")";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute("%Logitech ".$lurname."%");
    ($postid) = $sth_glob->fetchrow_array();
    
    #print "Postid-1: $postid";

    if ($postid != ""){
        return $postid;
    }
    
    $myquery = "SELECT postid_com FROM lhgtransverse_posts WHERE product like ? AND (status_com = \"published\" OR status_com = \"\")";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute("% ".$lurname." %");
    ($postid) = $sth_glob->fetchrow_array();
    
    #print "Postid-2: $postid";
    
    return $postid;

}

sub write_lur_data {
    my $sid      = shift;
    my $postid   = shift;
    my $dstrg    = shift;
    
    
    if ($rescan == 0) {
            $lhg_db = DBI->connect($database, $user, $pw);
            $myquery = "INSERT INTO `lhghwscans` (sid, postid, idstring, scantype) VALUES (? ,? , ?, ?)";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($sid, $postid, $dstrg, "logitech_unifying_receiver");
    }else{
            #print "RESCANNING";
            #find id to update
            $lhg_db = DBI->connect($database, $user, $pw);
            $myquery = "SELECT id FROM `lhghwscans` WHERE sid = ? AND idstring = ?";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($sid, $dstrg);
            ($id) = $sth_glob->fetchrow_array();

            $myquery = "UPDATE `lhghwscans` SET postid = ? , scantype = ?  WHERE id = ?";
            $sth_glob = $lhg_db->prepare($myquery);
            $sth_glob->execute($postid, "logitech_unifying_receiver", $id);

            # do not add entries again
            #print "ID: $id   -   ";
    }
}

sub write_lur_data_notfound {
    my $sid   = shift;
    my $drivename   = shift;
    my $scantype = "logitech_unifying_receiver";
    
    #ToDo: Sanity checks necessary!!
    
    if ($rescan == 0) {
        $lhg_db = DBI->connect($database, $user, $pw);
        $myquery = "INSERT INTO `lhghwscans` (sid, idstring, scantype) VALUES (? , ?, ?)";
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute( $sid, $drivename, $scantype);
    }else{
        #nothing
    }
}

sub storescan_laptop_probability {
    my $sid              = shift;
    my $probability      = shift;

    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "UPDATE `lhgscansessions` SET laptop_probability = ? WHERE sid = ?";
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute($probability, $sid);
}

sub calculate_laptop_probability {
    
    print "-----> Calculate Laptop Probability: ";
    $probability = 0;


    # get mainboard name
    $title = get_mainboard_name( $sid );
    #print "Mainboard name: $title\n";

    if ( ( index($title,"TOSHIBA") != -1 )  && index($title,"Satellite") != -1 ) {$probability = 1;}
    if ( ( index($title,"Toshiba") != -1 )  && index($title,"Satellite") != -1 ) {$probability = 1;}
    if ( ( index($title,"TOSHIBA") != -1 )  && index($title,"SATELLITE") != -1 ) {$probability = 1;}
    if ( ( index($title,"Toshiba") != -1 )  && index($title,"SATELLITE") != -1 ) {$probability = 1;}
    if ( ( index($title,"FUJITSU") != -1 )  && index($title,"LIFEBOOK")  != -1 ) {$probability = 1;}
    if ( ( index($title,"Dell") != -1 )  && index($title,"Inspiron")  != -1 )    {$probability = 1;}
    if ( index($title,"Dell Inc. Studio")  != -1 ) {$probability = 1;}
    if ( index($title,"LIFEBOOK")  != -1 ) {$probability = 1;}
    if ( index($title,"Notebook")  != -1 ) {$probability = 1;}
    if ( index($title,"SAMSUNG")  != -1 ) {$probability = 1;}
    if ( index($title,"Samsung")  != -1 ) {$probability = 1;}
    if ( index($title,"Compaq Presario")  != -1 ) {$probability = 1;}

    if ( ( $title =~ /ASUSTeK/ ) && ( $title =~ / K[0-9][0-9]/ ) ) {$probability = 1;}
    if ( ( $title =~ /Acer/ ) && ( $title =~ / Aspire [0-9][0-9]/ ) ) {$probability = 1;}
    
    if ($probability == 1) {
        #stop already here
        #print "Rule 1: Name found \n-------->$title \n";
        storescan_laptop_probability($sid, $probability);
        print "100% - Laptop found \n";
        return $probability;
    }
    
    #print "T: $title";
    # check for Think Pads, ASUS laptops
    if ( 
        ( index($title,"LENOVO") != -1 ) or
        ( index($title,"Lenovo") != -1 ) or
        ( index($title,"ASUS") != -1 ) 
       
       ) {
        #print "Found something";
        open(FILE, "<", "/var/www/uploads/".$sid."/dmesg.txt");
    
        while ( <FILE> ) {
            if ( 
                ( ($_ =~ m/ACPI: DMI detected: /) && ( index($_,"ThinkPad") != -1 ) ) or
                ( ($_ =~ m/ACPI: DMI detected: /) && ( index($_,"Zenbook") != -1 ) )
               ){
                $probability = 1;
                storescan_laptop_probability($sid, $probability);
                break;
            }
        }
    
    }

    
    print $probability."\n";
    return $probability;
}

sub get_mainboard_name {
    $dmesg_file = "cat /var/www/uploads/".$sid."/dmesg.txt | grep DMI:";
    
    open(FILE, "<", "/var/www/uploads/".$sid."/dmesg.txt");
    open(FILE2, "<", "/var/www/uploads/".$sid."/dmesg.txt");
    
    while ( <FILE> ) {
        if ($_ =~ m/DMI: /) {
            $dmiline = $_;
            break;
        }
    }
    
    if ($dmiline eq "") { 
        #Try to get name from other DMI output
        while ( <FILE2> ) {
            if ($_ =~ m/Product Name: /) {
                #print "Found!";
                $line = $_;
                $line =~ s/Product Name: //;
                $line =~ s/\n//;
                $dmiline .= $line;
            }
        }
    }
    
    #print "DMI: $dmiline\n";

    
    if ($dmiline eq "") { $dmiline = "unkown name"; }
    
    return $dmiline;

}

sub article_type_is_mainboard {
    my $postid = shift;
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT categories_com FROM `lhgtransverse_posts` WHERE postid_com = ?";
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute($postid);
    ($categories) = $sth_glob->fetchrow_array();
    
    if ($categories =~ m/notebooks/) {
            $mainboard = 0;
    }
    
    if ($categories =~ m/mainboards/) {
            $mainboard = 1;
    }
    
    #print "M: $mainboard\n";
    #print "To be implemented: Check if $postid is a mainboard
    #    " . $categories ."
    #    \n";
    
    return $mainboard;
}

sub rescan_all {
    # get list of all session IDs
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT DISTINCT sid FROM `lhgscansessions`";
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute();
    
    while (@sids = $sth_glob->fetchrow_array() ) {
        my $sid=$sids[0];
        system($0,$sid,"-r");
    }

}

# remove leading and tailing white spaces
# miss this from PHP...
sub  trim { 
    my $s = shift; 
    $s =~ s/^\s+|\s+$//g; 
    $s =~ s/\\n$//g; 
    return $s 
}

sub check_duplicates {
    # look into DB if similar upload was carried out before.
    # Should prevent upload spam to steal Karma
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT kversion, distribution, scandate, status FROM `lhgscansessions` WHERE sid = ?";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute( $sid );
    ($kversion, $distribution, $scandate, $status) = $sth_glob->fetchrow_array();
    
    # scandate needs to be set manually, if this is the first scan attemp
    # In this case $scandate is set to 1 
    if ($scandate < 2) { $scandate = time(); }

    #print "KV: $kversion - D: $distribution - S: $scandate";
    
    # check if similar uploads exist
    
    $myquery = "SELECT sid FROM `lhgscansessions` WHERE kversion = ? AND distribution = ? AND scandate < ? AND sid != ?";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute( $kversion, $distribution, $scandate, $sid );
    #@sids = $sth_glob->fetchrow_array();
    $sids = $sth_glob->fetchall_arrayref();
    
    #print "\n--------SIDS\n";
    #    foreach (@{$sids}){
    #        print "@{$_}";
    #    }

    
    # own fingerprint
    @lfp = `/var/www/uploads/fingerprints.pl $sid 2> /dev/null`;
    
    
    foreach ( @{$sids} ) {
        print "Duplicate candidate: @{$_} ";
        
        # compare fingerprints
        @fp = `/var/www/uploads/fingerprints.pl @{$_} 2> /dev/null`;
        
        #print "FP\n: $fp\n";
        #print "LFP\n: $lfp";
        
        #print "\n--------Own FP\n";
        #foreach (@fp){
        #    print "$_";
        #}
        #print "\n--------Candidate FP\n";
        #foreach (@lfp){
        #    print "$_";
        #}
        
        if (@fp ~~ @lfp) {
            #if ( array_diff(@fp, @lfp) == "" ) {
            print "-> dup\n";# Scan looks like a duplicate of @{$_}\n";
            
            # do not overwrite status value 
            if ($status eq "") {
                $myquery = "UPDATE `lhgscansessions` SET status = ? WHERE sid = ?";
                $sth_glob = $lhg_db->prepare($myquery);
                $sth_glob->execute("duplicate", $sid);
            }
        } else {
            print "-> OK\n"; #Scan @{$_} is NOT a duplicate\n";
        }

    }
}

sub  cleanup { 
    # Whats left at the end?
    
    # 1. write final scandate
    # 1.1 check if scandate still set to 1
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT scandate FROM `lhgscansessions` WHERE sid = ?";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute( $sid );
    ($scandate_db) = $sth_glob->fetchrow_array();
    
    if ($scandate_db == 1) {
        # first scan. No scandate yet set
        $scandate = time;
        $myquery = "UPDATE `lhgscansessions` SET scandate = ? WHERE sid = ?";   
        $sth_glob = $lhg_db->prepare($myquery);
        $sth_glob->execute($scandate, $sid);
        #print "New scandate set";
    } else {
        #print "Nothing to do";
    }

}

sub  postid_is_mainboard { 
    #check if this postid is part of a mainboard or laptop
    my $postid = shift;
    
    $lhg_db = DBI->connect($database, $user, $pw);
    $myquery = "SELECT categories_com FROM `lhgtransverse_posts` WHERE postid_com = ?";   
    $sth_glob = $lhg_db->prepare($myquery);
    $sth_glob->execute( $postid );
    ($categories) = $sth_glob->fetchrow_array();
    
    #print "CAT: $categories \n";
    
    if ( (index($categories, "mainboard") != -1) 
        or (index($categories, "notebook") != -1) )
        {
        print "This ($postid) is a mainboard / laptop";
        return 1;
    }else{
        return 0;
    }
}

