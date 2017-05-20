#!/usr/bin/perl

# usage: ./amazon_price.pl ASIN Countrycode
# supported countrycodes: 
#    "de"  ... Amazon.de

use File::Basename;
use warnings;
use LWP::UserAgent;
use XML::Simple;
#use YAML::Syck;
use Data::Dumper;
#use strict;



my $dir = dirname(__FILE__);
require "$dir/APA.pm";
#require "/var/www/cgi-bin/APA.pm";

# catch option -a 
if ($ARGV[0] eq "-a") {
    
    $ItemID = $ARGV[1];
    $region = $ARGV[2];
    $complete_output = 1;
    $return_url = 0;

} else {

    $ItemID = $ARGV[0];
    $region = $ARGV[1];
    $return_url = 0;
}

if ($ItemID eq "") { 
    #$ItemID = "B000EHIA06"; 
    $ItemID = "B00IWRX14A"; 
}
if ($region eq "") { 
    #$region = "de"; 
    $region = "com"; 
}

if (defined( $ARGV[2] ) )  {
    if ($ARGV[2] eq "URL") { $return_url = 1; }
}
    
$AssociateTag = "unknown";
if ($region eq "ca"){
    $aws_partner_id ="linuhardgui01-20";
}elsif ($region eq "co.uk") {
    $aws_partner_id ="linuhardguid-21";
}elsif ($region eq "de"){
    $aws_partner_id ="linuxnetmagor-21";
}elsif ($region eq "fr"){
    $aws_partner_id ="linuhardgui01-21";
}elsif ($region eq "es"){
    $aws_partner_id ="linuhardgu061-21";
}elsif ($region eq "it"){
    $aws_partner_id ="linuhardgui05-21";
}elsif ($region eq "co.jp"){
    $aws_partner_id ="linuhardgui22-22";
    #            //}elseif ($region == "com.br"){
    #//      $aws_partner_id ="linuhardgui22-22";
}elsif ($region eq "cn"){
    $aws_partner_id ="linuhardgui23-23";
}elsif ($region eq "in"){
    $aws_partner_id ="linuxhardwagu-21";
}else {     #// com as default
    $aws_partner_id ="linuhardguid-20";
}

$AssociateTag = $aws_partner_id;

#print "ID: $ItemID";

#use strict;

  $public_key = "abc";
  $private_key = "abcd";
  require ("$dir/lhg.conf");

  #  use URI::Amazon::APA; # instead of URI
  my $u = URI::Amazon::APA->new('http://webservices.amazon.'.$region.'/onca/xml');
   $u->query_form(
    Service     => 'AWSECommerceService',
    Operation   => 'ItemLookup',
    ItemId => $ItemID,
    AssociateTag=> $AssociateTag,
    ResponseGroup => 'Offers',
  );
  
  if ($return_url eq 1) {
    $u->query_form(
    Service     => 'AWSECommerceService',
    Operation   => 'ItemLookup',
    ItemId => $ItemID,
    AssociateTag=> $AssociateTag,
    ResponseGroup => 'Medium',
    );
  }
  
  #return all infos
  if ($complete_output eq 1) {
    $u->query_form(
    Service     => 'AWSECommerceService',
    Operation   => 'ItemLookup',
    ItemId => $ItemID,
    AssociateTag=> $AssociateTag,
    ResponseGroup => 'Images,Medium,Offers',
    );
  }

    $u->sign(
    key    => $public_key,
    secret => $private_key,
  );

  my $ua = LWP::UserAgent->new;
  my $r  = $ua->get($u);
  if ( $r->is_success ) {
      #print "A:";
      $xmloutput = XMLin($r->content);
      #print Dumper( $xmloutput );
      #print YAML::Syck::Dump( XMLin( $r->content ) );
      
      #print Dumper ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'OfferSummary' } ); #->{ 'LowestNewPrice' } );#->{ 'FormattedPrice' } ); 
      #print Dumper ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'OfferSummary' } ); #->{ 'LowestNewPrice' } );#->{ 'FormattedPrice' } ); 

      $lowestNewPrice = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'OfferSummary' }->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      # only used product available?
      if ($lowestNewPrice eq "") {
          $lowestNewPrice = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'OfferSummary' }->{ 'LowestUsedPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      }   
      
      $url = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'DetailPageURL' } ); #["OperationRequest"];
      #$medium_image = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[0]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      $medium_image_tmp = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' } ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      $label = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ItemAttributes' }->{ 'Label' } ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      $title = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ItemAttributes' }->{ 'Title' } ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      $brand = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ItemAttributes' }->{ 'Brand' } ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      #print Dumper( $medium_image_tmp );
      
      if ( ref($medium_image_tmp) eq 'ARRAY' ) {
          #print "IS AN ARRAY!";
          # The last element contains the main image. Therefore, we have to look at all images
          $medium_image1 = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[0]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
          $medium_image2 = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[1]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
          $medium_image3 = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[2]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
          $medium_image4 = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[3]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
          $medium_image5 = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[4]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
          $medium_image6 = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[5]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
          $medium_image7 = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[6]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
          $medium_image8 = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[7]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
          
          if (defined($medium_image8)) {
              $medium_image = $medium_image8;
          }elsif (defined($medium_image7)) {
              $medium_image = $medium_image7;
          }elsif (defined($medium_image6)) {
              $medium_image = $medium_image6;
          }elsif (defined($medium_image5)) {
              $medium_image = $medium_image5;
          }elsif (defined($medium_image4)) {
              $medium_image = $medium_image4;
          }elsif (defined($medium_image3)) {
              $medium_image = $medium_image3;
          }elsif (defined($medium_image2)) {
              $medium_image = $medium_image2;
          } 
          
      } else {
          #print "IS NOT AN ARRAY!";      
          $medium_image = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      }
      #$medium_image = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
  }
  else {
    print $r->status_line, $r->as_string;
  }
  
  
  if ($return_url eq 1) {
      if ( defined($url) ) { 
          print $url;
      }
  }elsif ($complete_output eq 1) {
      #if ( defined($url) ) { 
      #print "A".$medium_image->{MediumImage}[0];
      # print Dumper($medium_image->[0]);
      # print Dumper($medium_image->{MediumImage}->{URL});
      #print "\nOutput: \n";
      print "Image: $medium_image;;URL: $url;;Price: $lowestNewPrice;;Title: $title;;Label: $label;;Brand: $brand;;";
      
      if ( defined($medium_image1)) { print "Image2: $medium_image1;;"; }
      if ( defined($medium_image2)) { print "Image3: $medium_image2;;"; }
      if ( defined($medium_image3)) { print "Image4: $medium_image3;;"; }
      if ( defined($medium_image4)) { print "Image5: $medium_image4;;"; }
      if ( defined($medium_image5)) { print "Image6: $medium_image5;;"; }
      if ( defined($medium_image6)) { print "Image7: $medium_image6;;"; }
      if ( defined($medium_image7)) { print "Image8: $medium_image7;;"; }
      if ( defined($medium_image8)) { print "Image8: $medium_image8;;"; }
      
      #    Image3: $medium_image3;;
      #    Image4: $medium_image4;;
      #    Image5: $medium_image5;;";
      
          #print "Image: ".join(" - ", @medium_image);
          #}

  }else{
      print $lowestNewPrice;
  }
