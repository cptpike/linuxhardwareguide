#!/usr/bin/perl

# Scan through all posts and find metadata needed for hardware recognition
# implemented functions:
#      - extract subsystem ID from articles

no warnings experimental::smartmatch; 

use DBI;

our    $host = "192.168.56.14";
# choose correct sql address
use Net::Address::IP::Local;
my $address      = Net::Address::IP::Local->public;
if ($address eq "192.168.3.115") {
    print "On Test Server \n";
    $host = "192.168.3.114";
}

our    $database = "DBI:mysql:lhgpricedb;host=$host";
require ("/var/www/uploads/lhg.conf");
#our    $user = "USERNAME";
#our    $pw = "PASSWORD";


# get list of all published posts
$lhg_db = DBI->connect($database, $user, $pw);
$myquery = "SELECT * FROM `lhgtransverse_posts` WHERE status_com = 'published'";
$sth_glob = $lhg_db->prepare($myquery);
$sth_glob->execute();
#($num) = $sth_glob->fetchrow();

#my @row;
while ($row = $sth_glob->fetchrow_hashref) {  # retrieve one row
    #print join(", ", @row), "\n";
    
    $content = $row-> { postcontent_com };
    $pid = $row-> { postid_com };
    $id = $row-> { id };
    $product = $row-> { product };
    #@db_pciids = split(",",$row-> { pciids });
    $db_pciids = $row-> { pciids };
    print $row-> { id }."/".$pid.": ".$product."\n";
    
    #get arrays of pciids and corresponding subsystem ids
    $pciarray_ref = "";
    $subsystemidarray_ref = "";
    ($pciarray_ref, $subsystemidarray_ref) = parse_content_for_subsystem_id($content);
    
    #create list corresponding to database PCI IDs
    $subsystemids = create_subsystem_list($db_pciids, $pciarray_ref, $subsystemidarray_ref);
    
    #print "    PCI: $db_pciids \n";
    #print "    Sub: $subsystemids \n";
    #print "    ----- \n";
    
    # for debug usage:
    # print PCI IDs and Subsystem IDs in table formatting
    #niceprint($db_pciids, $subsystemids);
    
    #store to DB if not empty
    if ( ($subsystemids =~ /[a-f]/) or ($subsystemids =~ /[0-9]/) ){
        store_to_db( $subsystemids, $id);
    }
    
    # Debug counter
    $i++;
    if ($i>100) { exit 0; }

}

exit 0;

sub parse_content_for_subsystem_id { 
    my $_content = shift;
    
    my $_lastline = "";
    my $pciid = "";
    my @text = split(/\n/, $_content);
    my @pciid_array = qw();
    my @subsystemid_array = qw();
    
    #print "Text: $_content $text[3]\n";
    
    foreach( @text) { 
        #check for subsystem line
        #print $_;
        
        if ($_ =~ m/Subsystem:/) {
            
            $pciline = $_lastline;
            $pciline =~ m/\w\w\w\w:\w\w\w\w/;
            
            if ($-[0]>1) {

                $pciid = substr($pciline,$-[0],9);
                #print "    PCIID at $-[0] : $pciid\n";
            
            }else{
                # check one line more (e.g. if DeviceName was printed)
                #print "    second try\n";
                $pciline = $_lastline2;
                $pciline =~ m/\w\w\w\w:\w\w\w\w/;
                
                if ($-[0]>1) {
                    $pciid = substr($pciline,$-[0],9);
                    #print "    PCIID at $-[0] try2 : $pciid\n";
            
                }else{
                    #print "    No PCIID found\n";
                }
            }
            
            $subsystemline = $_;
            $subsystemline =~ m/\w\w\w\w:\w\w\w\w/;
            $subsystemid = substr($subsystemline,$-[0],9);
            #print "    SID: $subsystemid\n";
            #print "    PID: $pciid\n";
            #\n----\n";
            
            # write both IDs to array
            push ( @pciid_array, $pciid);
            push ( @subsystemid_array, $subsystemid);
            
            #print "Found: $pciid--$subsystemid\n";
            
        
        }
        $_lastline2 = $_lastline;
        $_lastline = $_;
        $pciid = "";
        $subsystemid = "";
    }
    
    #return the references to these arrays
    return (\@pciid_array, \@subsystemid_array);
}

sub create_subsystem_list {
    # retrieves comma separated list of pciids
    # creates corresponding list of subsystem ids

    my $_db_pcis = shift;
    my $pciid_array_ref = shift;
    my $subid_array_ref = shift;
    
    # return list of subsystem ids, comma separated
    $returnlist = "";

    @pciids = @$pciid_array_ref;
    @subids = @$subid_array_ref;
    
    $num = scalar @pciids;
    
    @db_pciids = split(/,/,$_db_pcis);
    
    foreach( @db_pciids ) { 
        # cycle through all PCIIDs stored in the data base
        #print "Search $_ > ";
        
        $index = search_in_array($_, \@pciids);
        #my $match = first_index { /$_/ } @pciids;
        #print "Match id: ".$index." = ".@pciids[$index]."\n";
        if ($index eq "") {
            #no matching index found
            $returnlist .="," ;
        }else{
            $returnlist .= @subids[$index].",";
        }
    }
    
    #remove tailing comma
    return substr($returnlist,0,-1);
}

sub search_in_array {
    my $_searchfor = shift;
    my $_pciids_ref = shift;
    @_pciids = @$_pciids_ref;
    
    
    my $_num = scalar @_pciids;
    #print "NUM2! $_num -- ";
    
    # return the index where the given string was found in the array
    for (my $i=0; $i< $_num ; $i++) {
        if( $_searchfor eq @_pciids[$i]) {
            return $i;
        }
    }
    return "";
}


sub niceprint {
    my $a = shift;
    my $b = shift;
    @_a = split(/,/,$a);
    @_b = split(/,/,$b);
    
    for (my $i=0; $i< scalar @_a ; $i++) {
        print "$i: @_a[$i] -> @_b[$i] \n";

    }
}

sub store_to_db {
    my $string = shift;
    my $id = shift;
    
    $lhg_db = DBI->connect($database, $user, $pw);
    my $myquery_upd = "UPDATE `lhgtransverse_posts` SET subids = ?  WHERE id = ?";
    my $sth_glob_upd = $lhg_db->prepare($myquery_upd);
    $sth_glob_upd->execute($string, $id);

}

