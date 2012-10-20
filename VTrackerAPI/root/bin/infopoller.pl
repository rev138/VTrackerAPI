#!/usr/bin/perl
use strict;
use warnings;
use MongoDB;
use LWP::UserAgent;
use JSON;
$MongoDB::BSON::looks_like_number = 1;


# Initialize variables, database, and collection
my $ip = '127.0.0.1';
my $api_key = '00000000000000000000000000000000';
my $mapsapiurl = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&latlng=';
my $wunderapiurl = 'http://api.wunderground.com/api/34d4a15f513f62b7/conditions/q/';
my $logfile = '/tmp/infopoller.log';
my $mongo = MongoDB::Connection->new(); # defaults to localhost with no auth
my $database = $mongo->vtracker;
my $reports = $database->reports;
my $ua = LWP::UserAgent->new;
$ua->agent("VTrackerAPI/0.2 ");
if($MongoDB::BSON::looks_like_number) {}
sub logThis($);

logThis("Let's begin!");

# Main program loop
START:

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
	my $early = 0;

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
			if($town ne 'NULL' && $county ne 'NULL' && $state ne 'NULL' && $abbr ne 'NULL' && $zip ne 'NULL' && $country ne 'NULL')
			{
				logThis("Fast exit: ".$record->{'_id'}." - $town, $county, $state ($abbr), $zip, $country");
				$early = 1;
				last;
			}
		}
		logThis("Default: ".$record->{'_id'}." - $town, $county, $state ($abbr), $zip, $country") if(!$early);
		logThis("Done with searching for ".$record->{'_id'});

		# Update the database with the newly-gathered data
		$reports->update( { '_id' => $record->{'_id'} }, {'$set' => {
									'location.town' => $town,
									'location.county' => $county,
									'location.state' => $state,
									'location.abbr' => $abbr,
									'location.zip' => $zip,
									'location.country' => $country
									} });
	}
	else
	{
		logThis("Uh, oh: ".$google_json->{'status'});
	}
	sleep(8);
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
    my ($wunder_json,%weatherbits);
    %weatherbits = (
			'weather' => 'NULL', 
			'temp_c' => 'NULL', 
			'relhumid' => 'NULL', 
			'wind_deg' => 'NULL', 
			'wind_kph' => 'NULL', 
			'pressure_mb' => 'NULL', 
			'dewpoint_c' => 'NULL', 
			'uv' => 'NULL'
			);

    # Get the ZIP code
    my $zip = $record->{'location'}->{'zip'};

    logThis($record->{'_id'}." - ZIP: $zip");
    # Skip to the next record if the ZIP code isn't numeric
    next if($zip eq "NULL");

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
		$weatherbits{'weather'} = $co->{'weather'};
		$weatherbits{'temp_c'} = $co->{'temp_c'} + 0;
		$weatherbits{'relhumid'} = $co->{'relative_humidity'};
		$weatherbits{'wind_deg'} = $co->{'wind_degrees'} + 0;
		$weatherbits{'wind_kph'} = $co->{'wind_kph'} + 0;
		$weatherbits{'pressure_mb'} = $co->{'pressure_mb'} + 0;
		$weatherbits{'dewpoint_c'} = $co->{'dewpoint_c'} + 0;
		$weatherbits{'uv'} = $co->{'UV'} + 0;
	}
	
	# Sanitize the output
	foreach my $bit (keys %weatherbits)
	{
		if (!defined($weatherbits{$bit}) || $weatherbits{$bit} eq "")
		{
			$weatherbits{$bit} = 'NULL';
		}
		# Strip the percent sign off the humidity
		if($bit eq "relhumid")
		{
			$weatherbits{$bit} =~ s/(-?\d+)%$/$1/;
			$weatherbits{$bit} += 0 if($weatherbits{$bit} ne 'NULL');
		}
	}
	logThis("$zip: ".$weatherbits{'weather'}.", ".$weatherbits{'temp_c'}.", ".$weatherbits{'relhumid'}.", ".$weatherbits{'wind_deg'}.", ".$weatherbits{'wind_kph'}.", ".$weatherbits{'pressure_mb'}.", ".$weatherbits{'dewpoint_c'}.", ".$weatherbits{'uv'});

	# Update the database with the newly-gathered data
	$reports->update( { '_id' => $record->{'_id'} }, {'$set' => {
								'conditions.weather' => $weatherbits{'weather'},
								'conditions.temp_c' => $weatherbits{'temp_c'},
								'conditions.relative_humidity_percent' => $weatherbits{'relhumid'},
								'conditions.wind_degrees' => $weatherbits{'wind_deg'},
								'conditions.wind_kph' => $weatherbits{'wind_kph'},
								'conditions.pressure_mb' => $weatherbits{'pressure_mb'},
								'conditions.dewpoint_c' => $weatherbits{'dewpoint_c'},
								'conditions.uv' => $weatherbits{'uv'}
								} });
	sleep(8);
}

### END CONDITIONS ###

# Sleep one second, then go back and do it again
sleep(1);
goto START;


### Functions ###
sub logThis($)
{
        my($out) = @_;
        my $stamp = localtime(time);
        open(LOG,">>$logfile");
        print LOG "[$stamp] $out\n";
        close(LOG);
}


