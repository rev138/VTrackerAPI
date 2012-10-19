#!/usr/bin/perl
use strict;
use warnings;
use MongoDB;


# Initialize variables, database, and collection
my $ip = '127.0.0.1';
my $api_key = '00000000000000000000000000000000';
my $count = shift;
my $mongo = MongoDB::Connection->new(); # defaults to localhost with no auth
my $database = $mongo->vtracker;
my $reports = $database->reports;

# Lookup records that are missing detailed location data
my $workingset = $reports->find( { 'location.county' => { '$exists' => 0 } }, { location => 1 } );
while(my $something = $workingset->next)
{
	print "LA ".$something->{'location'}->{'lat_long'}[0]."\n";
	print "LO ".$something->{'location'}->{'lat_long'}[1]."\n";
	print "EL ".$something->{'location'}->{'elevation'}."\n";
}
