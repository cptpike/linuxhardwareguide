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

    $region = $ARGV[0];
    $product = $ARGV[1];

if ($region eq "") { $region = "com"; }
if ($product eq "") { 
    #$product = "Intel Core i5 2500 CPU"; 
    $product = "HP DVD DH16ACSH";
}
$product =~ s/__/ /g;

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
    Operation   => 'ItemSearch',
    Keywords => $product,
    AssociateTag=> $AssociateTag,
    SearchIndex => 'PCHardware',
  );
  

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
      #print "length:";
      
      #print Dumper ($xmloutput->{ 'Items' }->{ 'Item' }->{ 'ASIN' } );
      
      if ( ref($xmloutput->{ 'Items' }->{ 'Item' }) eq "ARRAY" ) {
      $item1_ASIN = ( $xmloutput->{ 'Items' }->{ 'Item' }->[0]->{ 'ASIN' });# ->{ 'ItemLinks' }; #->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      $item2_ASIN = ( $xmloutput->{ 'Items' }->{ 'Item' }->[1]->{ 'ASIN' });# ->{ 'ItemLinks' }; #->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      $item3_ASIN = ( $xmloutput->{ 'Items' }->{ 'Item' }->[2]->{ 'ASIN' });# ->{ 'ItemLinks' }; #->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      
      $Title1 = ( $xmloutput->{ 'Items' }->{ 'Item' }->[0] ->{ 'ItemAttributes' } ->{ 'Title' }); #->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      $Title2 = ( $xmloutput->{ 'Items' }->{ 'Item' }->[2] ->{ 'ItemAttributes' } ->{ 'Title' }); #->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      $Title3 = ( $xmloutput->{ 'Items' }->{ 'Item' }->[3] ->{ 'ItemAttributes' } ->{ 'Title' }); #->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      
      
      print "
ASIN1: $item1_ASIN 
Title1: $Title1 
ASIN2: $item2_ASIN 
Title2: $Title2 
ASIN3: $item3_ASIN 
Title3: $Title3 
          ";
      
      }else{
          #only one search result provided
      $item1_ASIN = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ASIN' });# ->{ 'ItemLinks' }; #->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      $Title1 = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ItemAttributes' } ->{ 'Title' }); #->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      print "
ASIN1: $item1_ASIN 
Title1: $Title1 
          ";
      }    
      #print Dumper( $Title1 );
      #print Dumper( $item2 );
      
      #      $lowestNewPrice = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'OfferSummary' }->{ 'LowestNewPrice' }->{ 'FormattedPrice' } ); #["OperationRequest"];
      #$url = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'DetailPageURL' } ); #["OperationRequest"];
      #$medium_image = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[0]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      #$medium_image_tmp = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' } ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      #$label = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ItemAttributes' }->{ 'Label' } ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      #$title = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ItemAttributes' }->{ 'Title' } ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      #$brand = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ItemAttributes' }->{ 'Brand' } ); # ->{ 'MediumImage' } ); #->{ 'URL' } );
      #print Dumper( $medium_image_tmp );
      
      if ( ref($medium_image_tmp) eq 'ARRAY' ) {
          #print "IS AN ARRAY!";
          $medium_image = ( $xmloutput->{ 'Items' }->{ 'Item' }->{ 'ImageSets' }->{ 'ImageSet' }->[0]->{MediumImage}->{URL} ); # ->{ 'MediumImage' } ); #->{ 'URL' } );

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
  }else{
      print $lowestNewPrice;
  }
