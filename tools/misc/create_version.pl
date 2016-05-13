#!/usr/bin/perl

# Creates version number from latest code release and makes it public

my $git_commit_nr = `cd ~/git/linuxhardwareguide/ && git describe --tags --long`;



my $file="/var/www/wordpress/version.php";
open(my $fh, '>', $file) or die "Could not open file '$filename' $!";
print $fh "<?php\n";
print $fh 'global $code_version;'."\n";
print $fh '$code_version = "'.trim($git_commit_nr)."\";\n";
print $fh "?>\n";
close $fh;

exit 0;

sub  trim { my $s = shift; $s =~ s/^\s+|\s+$//g; return $s };
