#!/usr/bin/perl
use strict;
use warnings;
use MongoDB;
use LWP::UserAgent;
use JSON;
use Data::Dumper;


# Initialize variables, database, and collection
my $ip = '127.0.0.1';
my $api_key = '00000000000000000000000000000000';
my $mapsapiurl = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&latlng=';
my $mongo = MongoDB::Connection->new(); # defaults to localhost with no auth
my $database = $mongo->vtracker;
my $reports = $database->reports;
my $ua = LWP::UserAgent->new;
$ua->agent("VTrackerAPI/0.2 ");

# Lookup records that are missing detailed location data
my $workingset = $reports->find( { '$or' => [
						{'location.town' => { '$exists' => 0 } },
						{'location.county' => { '$exists' => 0 } },
						{'location.state' => { '$exists' => 0 } },
						{'location.abbr' => { '$exists' => 0 } },
						{'location.zip' => { '$exists' => 0 } },
						{'location.country' => { '$exists' => 0 } }
					    ]}, { location => 1 } );
while(my $something = $workingset->next)
{
	# Initialize vars
	my ($google_json,$town,$county,$state,$abbr,$zip,$country);

	# Create lat/long string
	my $latlng = $something->{'location'}->{'lat_long'}[0].",".$something->{'location'}->{'lat_long'}[1];

	# Query Google Maps' API for detailed location information
	my $req = HTTP::Request->new(GET => $mapsapiurl.$latlng);

	# Pass request to the user agent and get a response back
	my $res = $ua->request($req);

	# Check the outcome of the response
	if ($res->is_success) 
	{
		$google_json = decode_json($res->content);
	}
	else
	{
		print $res->status_line, "\n";
	}

	if ($google_json->{'status'} eq "OK")
	{
		foreach my $response (@{$google_json->{'results'}})
		{
			if ($response->{'types'}[0] eq "street_address")
			{
				foreach my $component ($response->{'address_components'})
				{
					foreach my $detail (@{$component})
					{
						if($detail->{'types'}[0] eq "administrative_area_level_1")
						{
							$abbr = $detail->{'short_name'};
							$state = $detail->{'long_name'};
						}
						elsif($detail->{'types'}[0] eq "locality")
						{
							$town = $detail->{'long_name'};
						}
						elsif($detail->{'types'}[0] eq "administrative_area_level_2")
						{
							$county = $detail->{'long_name'};
						}
						elsif($detail->{'types'}[0] eq "postal_code")
						{
							$zip = $detail->{'long_name'};
						}
						elsif($detail->{'types'}[0] eq "country")
						{
							$country = $detail->{'long_name'};
						}
					}
				}
			}
			# Early escape if we've located all of the fields
			if($town && $county && $state && $abbr && $zip && $country)
			{
				print "$town, $county, $state ($abbr), $zip, $country\n";
				last;
			}
		}
	}
}
