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
my $wunderapiurl = 'http://api.wunderground.com/api/34d4a15f513f62b7/conditions/q/';
my $mongo = MongoDB::Connection->new(); # defaults to localhost with no auth
my $database = $mongo->vtracker;
my $reports = $database->reports;
my $ua = LWP::UserAgent->new;
$ua->agent("VTrackerAPI/0.2 ");


### LOCATION ###

# Lookup records that are missing detailed location data
my $locationset = $reports->find( { '$or' => [
						{'location.town' => { '$exists' => 0 } },
						{'location.county' => { '$exists' => 0 } },
						{'location.state' => { '$exists' => 0 } },
						{'location.abbr' => { '$exists' => 0 } },
						{'location.zip' => { '$exists' => 0 } },
						{'location.country' => { '$exists' => 0 } }
					    ]}, { location => 1 } );

# Iterate over the records
while(my $record = $locationset->next)
{
	# Initialize vars
	my ($google_json,$town,$county,$state,$abbr,$zip,$country);
	$town = 'NULL';
	$county = 'NULL';
	$state = 'NULL';
	$abbr = 'NULL';
	$zip = 'NULL';
	$country = 'NULL';

	# Create lat/long string
	my $latlng = $record->{'location'}->{'lat_long'}[0].",".$record->{'location'}->{'lat_long'}[1];

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

	# If the API call worked, start processing the location data
	if ($google_json->{'status'} eq "OK")
	{	
		# Loop through every result set within the results
		foreach my $response (@{$google_json->{'results'}})
		{
			# Run through each component of the address components
			foreach my $component ($response->{'address_components'})
			{
				# Run through the details within each component
				foreach my $detail (@{$component})
				{
					my $type = $detail->{'types'}[0];
					# Set the values for the fields we're looking for
					if($type eq "administrative_area_level_1")
					{
						$abbr = $detail->{'short_name'};
						$state = $detail->{'long_name'};
					}
					elsif($type eq "locality")
					{
						$town = $detail->{'long_name'};
					}
					elsif($type eq "administrative_area_level_2")
					{
						$county = $detail->{'long_name'};
					}
					elsif($type eq "postal_code")
					{
						$zip = $detail->{'long_name'};
					}
					elsif($type eq "country")
					{
						$country = $detail->{'long_name'};
					}
				}
			}
			
			# Early escape if we've located all of the fields
			if($town && $county && $state && $abbr && $zip && $country)
			{
				print "Fast exit: ".$record->{'_id'}." - $town, $county, $state ($abbr), $zip, $country\n";
				last;
			}
		}
		print "Done with searching for ".$record->{'_id'}."\n";
		print "Default: ".$record->{'_id'}." - $town, $county, $state ($abbr), $zip, $country\n";

		# Update the database with the newly-gathered data
			
	}
}

### END LOCATION ###

### CONDITIONS ###

# Lookup records that are missing conditions data, but only
# if there is a valid zip code present for that record
my $conditionset = $reports->find( { '$and' => [
                        {'conditions' => { '$exists' => 0 } },
                        {'location.zip' => { '$exists' => 1 } }
                        ]}, { 'location.zip' => 1 } );

# Iterate over the records
while(my $record= $conditionset->next)
{
    # Initialize vars
    my ($wunder_json,$weather,$temp_c,$relhumid,$wind_deg,$wind_kph,$pressure_mb,$dewpoint_c,$uv);

    # Get the ZIP code
    my $zip = $record->{'location'}->{'zip'};

	# Skip to the next record if the ZIP code isn't numeric
	next if($zip !~ /^\d+$/);

    # Query Google Maps' API for detailed location information
    my $req = HTTP::Request->new(GET => $wunderapiurl.$zip.".json");

    # Pass request to the user agent and get a response back
    my $res = $ua->request($req);

    # Check the outcome of the response
    if ($res->is_success)
    {
        $wunder_json = decode_json($res->content);
    }
    else
    {
        print $res->status_line, "\n";
    }

	my $co = $wunder_json->{'current_observation'};
	# If the API call worked, start processing the condition data
	if ($co->{'weather'})
	{
		$weather = $co->{'weather'};
		$temp_c = $co->{'temp_c'};
		$relhumid = $co->{'relative_humidity'};
		$wind_deg = $co->{'wind_degrees'};
		$wind_kph = $co->{'wind_kph'};
		$pressure_mb = $co->{'pressure_mb'};
		$dewpoint_c = $co->{'dewpoint_c'};
		$uv = $co->{'UV'};
	}
    if ($weather ne "" && $temp_c ne "" && $relhumid ne "" && $wind_deg ne "" && $wind_kph ne "" && $pressure_mb ne "" && $dewpoint_c ne "" && $uv ne "")
	{
		# Strip the percent sign off the humidity
		$relhumid =~ s/^(\d+)%$/$1/;
		print "$zip: $weather, $temp_c, $relhumid, $wind_deg, $wind_kph, $pressure_mb, $dewpoint_c, $uv\n";
	}
}

### END CONDITIONS ###

