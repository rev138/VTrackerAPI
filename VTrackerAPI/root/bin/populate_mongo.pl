#!/usr/bin/perl

use strict;
use warnings;
use MongoDB;
$MongoDB::BSON::looks_like_number = 1;


# bounds of a rectangle inside VT
# top left: 44.816855,-73.119176
# bottom right: 43.25283,-72.468923
# diff: 1.564025, 0.650253

my $ip = '127.0.0.1';
my $api_key = '00000000000000000000000000000000';
my $count = shift;
my $mongo = MongoDB::Connection->new(); # defualts to localhost with no auth
my $database = $mongo->vtracker;
my $reports = $database->reports;
if($MongoDB::BSON::looks_like_number) {}

$count = 10 unless $count;

my @species = ( 'Odocoileus virginianus', 'Odocoileus virginianus', 'Odocoileus virginianus', 'Odocoileus virginianus', 'Ursa americanus', 'Ursa americanus', 'Alces alces', 'Alces alces', 'Haliaeetus leucocephalus' );
for( my $i = 0; $i < $count; $i++ ){
	my $lat = 44.816855 - rand( 1.564025 );
        my $lon = -73.119176 + rand( 0.650253 );
        my $animal = $species[ int( rand( @species ) ) ];
        my $count = int( rand( 2 ) ) + 1;
        my $elevation = rand( 50 ) + 35;


        $reports->insert(
                {
                        api_key         => $api_key,
                        ip_address      => $ip,
                        location        => { 
                                                lat_long        => [ $lat, $lon ],
                                                elevation       => $elevation,
                                        },
                        attributes      => {
                                                species         => $animal,
                                                count_male      => 0, 
                                                count_female    => 0, 
                                                count_juvenile  => 0,
                                                count_unknown   => $count,
                                                count_total     => $count,,
                                                is_tracks       => 0,
 
                                        },
                        time       => {
                                                epoch      => "1350589809",
                                                string          => "Thu Oct 18 15:50:07 EDT 2012",
                                                year            => "2012",
                                                month           => "10",
                                                day             => "18",
                                                hour            => "15",
                                                minute          => "50",
                                                second          => "7",
                                                timezone        => "EDT",
                                        },
                }
        );
}


