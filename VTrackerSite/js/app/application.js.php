<?php header("Content-type: text/javascript"); ?>

$.fn.extend({
	getLocationData: function(locdata){
		if( "geolocation" in navigator ) {
			navigator.geolocation.getCurrentPosition(
				//some properties:
				//position.coords.latitude;
				//position.coords.longitude;
				//position.coords.altitude;
				//position.timestamp;
				function (position) {
					locdata.success(position);
				},
				function (error) {
					alert('unable to access your location');
				},
				{enableHighAccuracy : true}
			);
		}
	}
});

/* INDEX PAGE */
$('#index').on("pageinit", function(event){
	Handlebars.registerHelper('list-main', function(context, options) {
		var ret = "", blockClass;

		//only top 10 categories
		for(var i=0, l=context.length; i<l && i<10; i++) {
			if (i%3 === 0) {
				blockClass= "ui-block-a";
			} else if (i%3 === 1) {
				blockClass= "ui-block-b";
			} else {
				blockClass= "ui-block-c";
			}
			var name = context[i].name,
				icon = context[i].icon;

		ret= ret + '<div class="' + blockClass + '"><a href="submit_report.php?_id=' + context[i]._id + '"><img src="' + icon + '" width="px" border="0" alt="' + name + '" />' + "</a></div>";
		}
		return ret;
	});

	$.ajax({
		type: "GET",
		data: {
			top_category: "1"
			// type : "",
			// sort : "",
			// count : "",
		},
		dataType:"json",
		url: 'http://vtracker.hzsogood.net/api/get_categories',
		success: function(data) {
			//ideally would split the content over the two templates
			var sourceMain = $("#categories-template-main").html();
			var templateMain = Handlebars.compile(sourceMain);
			$('#animal-categories-main').append(templateMain(data)); //refresh equivalent?

			var sourceMore = $("#categories-template-more").html();
			var templateMore = Handlebars.compile(sourceMore);
			$('#animal-categories-more').append(templateMore(data)).listview("refresh");
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest, textStatus, errorThrown);
		}
	});
});

/* SUBMIT REPORT */
$('#submit-report').on('pageinit', function() {

	Handlebars.registerHelper('iter', function(context, options) {
		var fn = options.fn, inverse = options.inverse;
		var ret = "";

		if(context && context.length > 0) {
			for(var i=0, j=context.length; i<j; i++) {
				// TODO - make a comma delimited list of common names as the label]
				ret = ret + fn($.extend({}, context[i], { i: i, iPlus1: i + 1 , label : context[i].common_names[0], value : context[i]._id }));
			}
		} else {
			ret = inverse(this);
		}
		return ret;
	});

	// GET/STORE API KEY
	// TODO : hide/show name & address input based on stored key ... a timing issue here
	var keyLabel = "VTrackAPIKey";
	var keyValue = localStorage.getItem(keyLabel);
	if (!keyValue) {
		$.ajax({
			type: "POST",
			data: {
			},
			dataType:"json",
			url: 'http://vtracker.hzsogood.net/api/new_key',
			success: function(data) {
				localStorage.setItem(keyLabel, data.key);
				keyValue = localStorage.getItem(keyLabel);
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				console.log("couldn't get API key");
			}
		});
	}
	$('#apikey').val(keyValue);

	// GET LOCATION
	var locdata = {
		success : function (position) {
			if (position.coords.hasOwnProperty('latitude')) {
				$('#latitude').val(position.coords.latitude);
			}
			if (position.coords.hasOwnProperty('longitude')) {
				$('#longitude').val(position.coords.longitude);
			}
			if (position.coords.hasOwnProperty('altitude')) {
				$('#altitude').val(position.coords.altitude);
			}
			if (position.hasOwnProperty('timestamp')) {
				$('#timestamp').val(position.timestamp);
			}
		}
	};
	$().getLocationData(locdata);

	// GET ANIMAL CATEGORY DATA TO POPULATE WITH
	$.ajax({
		type: "GET",
		data: {
			_id: "<?php echo $_GET['_id'] ?>",
			species: "1"
		},
		dataType:"json",
		url: 'http://vtracker.hzsogood.net/api/get_categories',
		success: function(data) {
			var thisCategory = data["categories"][0];
			var sourceCatTitle = $("#category-title-template").html();
			var templateCatTitle = Handlebars.compile(sourceCatTitle);

			var sourceCatInfo = $("#category-info-template").html();
			var templateCatInfo = Handlebars.compile(sourceCatInfo);

			var sourceSpecies = $("#species-template").html();
			var templateSpecies = Handlebars.compile(sourceSpecies);

			$('#category-info').append(templateCatInfo(thisCategory));
			$('h1').prepend(templateCatTitle(thisCategory));
			$('#species').append(templateSpecies(thisCategory));
			$('#submit-report').trigger('pagecreate');
			//refresh controlgroup?
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest, textStatus, errorThrown);
		}
	});

	// VALIDATION - SKIP FOR NOW
	function formCheck (form) {
		return true;
	}

	$('#cancel').bind('click', function(e) {
		window.location.href = "/";
		e.preventDefault();
	})

	$('form[name=submit-report]').submit(function() {
        	var totalCount = 0;
        	var maleCount = (!$("#count_male",this).val())? "0" : $("#count_male",this).val();
        	var femaleCount =(!$("#count_female",this).val())? "0" : $("#count_female",this).val();
        	var juvenileCount = (!$("#count_juvenile",this).val())? "0" : $("#count_juvenile",this).val();
        	var unknownCount = (!$("#count_unknown",this).val())? "0" : $("#count_unknown",this).val();
        	totalCount = parseInt(maleCount) + parseInt(femaleCount) + parseInt(juvenileCount) + parseInt(unknownCount);
        	var notes = $("#notes",this).val();
	        var data = { "key" : keyValue,
	              "latitude"      : $("#latitude",this).val(),
	              "longitude"     : $("#longitude",this).val(),
	              "altitude"      : $("#altitude",this).val(),
	              "attributes"    : {
	                      "species"  : $('#species input:checked',this).val(),
	                      "count_male"    : maleCount,
	                      "count_female"  : femaleCount,
	                      "count_juvenile" : juvenileCount,
	                      "count_unknown" : unknownCount,
	                      "count_total"   : totalCount,
	                      "notes"         : notes,
	                      "is_track"      : "0"
	                },
	              };

	         $.ajax({
	           type: "POST",
	           data: JSON.stringify(data),
	           processData: "false",
	           dataType:"json",
	           contentType: "application/json;charset=UTF-8",
	           url: 'http://vtracker.hzsogood.net/api/submit_report',
	           success: function(data) {
	           		alert('Your Report has been sent');
	           		window.location.href = "/";
	           },
	           error: function (XMLHttpRequest, textStatus, errorThrown) {
	             console.log(XMLHttpRequest, textStatus, errorThrown);
	           }
	         });
		return false;
	});
});

/* SEARCH PAGE */
$('#search').on('pageinit', function() {
	// TODO: Do the json API call

	$.ajax({
		type: "GET",
		data: {
			top_category: "1"
			// type : "",
			// sort : "",
			// count : "",
		},
		dataType:"json",
		url: 'http://vtracker.hzsogood.net/api/get_categories',
		success: function(data) {
			//ideally would split the content over the two templates
			var sourceMore = $("#categories-template-more2").html();
			var templateMore = Handlebars.compile(sourceMore);
			$('#animal-categories-more2').append(templateMore(data)).listview("refresh");
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest, textStatus, errorThrown);
		}
	});

// GET ANIMAL CATEGORY DATA TO POPULATE WITH
	$.ajax({
		type: "GET",
		data: {
			expanded : 1<?php if ($_GET['species']) echo ','; ?>
			<?php
				if ($_GET['species']) {
					$species = split(",", $_GET["species"]);
					$species = $species[0];
				?>species : "<?php echo $species; ?>"<?php
				}
			?>
		},
		dataType:"json",
		url: 'http://vtracker.hzsogood.net/api/get_reports',
		success: function(data) {

			var center = new google.maps.LatLng(44.260113, -72.575386);
			var map = new google.maps.Map(document.getElementById('map'), {
				zoom: 7,
				center: center,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			});
			var markers = [];

			var reports = data["reports"], report;

			for ( var i = 0; i < reports.length; i++ ) {

				report = reports[i];

				attributes = report.attributes,
					count_female = attributes.count_female,
					count_juvenile = attributes.count_juvenile,
					count_male = attributes.count_male,
					count_total = attributes.count_total,
					count_unknown = attributes.count_unknown,
					is_tracks = attributes.is_tracks;

				if (attributes.species !== null) {
					species = attributes.species;
					current_species = species.common_names[0];
					latin_name = species._id;
					icon = species.icon;
				} else {
					continue;
				}

				if (report.conditions) {
					conditions = report.conditions;
					dewpoint_c = conditions.dewpoint_c;
					pressure_mb = conditions.pressure_mb;
					relative_humidity_percent = conditions.relative_humidity_percent;
					temp_c = conditions.temp_c;
					uv = conditions.uv;
					weather = conditions.weather;
					wind_degrees = conditions.wind_degrees;
					wind_kph = conditions.wind_kph;
				}

				if (report.location) {
					loc = report.location;
					abbr = loc.abbr;
					country = loc.country;
					county = loc.county;
					elevation = loc.elevation;
					lat = loc.lat_long[0];
					lng = loc.lat_long[1];
					state = loc.state;
					town = loc.town;
					zip = loc.zip;
				}

				if (report.time) {
					timestamp = report.time;
					timestamp_day = timestamp.day;
					timestamp_epoch_time = timestamp.epoch_time;
					timestamp_hour = timestamp.hour;
					timestamp_minute = timestamp.minute;
					timestamp_month = timestamp.month;
					timestamp_second = timestamp.second;
					timestamp_string = timestamp.string;
					timestamp_timezone = timestamp.timezone;
					timestamp_year = timestamp.year;
				}

				var latLng = new google.maps.LatLng(lat, lng);
				var marker = new google.maps.Marker({
					position: latLng,
					species: current_species,
					latin_name: latin_name,
					title: current_species + " sighting",
					map: map
				});

				/* Create Info Windows */
				var infowindow = new google.maps.InfoWindow({
					content: " "
				});

				google.maps.event.addListener(marker, 'click', function() {

					var markerContent = '<p><strong>' + this.title + '</strong></p>';
						markerContent += '<p>This animal was seen on ' + timestamp_string + '</p>';
						markerContent += '<p>At the time of sighting, there were ' + weather + ' skies, the temperature was ' + temp_c + 'C degrees and the relative humidity was ' + relative_humidity_percent + '%.</p>';
						markerContent += '<p>Learn more about <a href="#" class="dialog" data-species="' + this.species + '" data-latin-name="' + this.latin_name + '" data-rel="dialog">' + this.species + '</a></p>';

					infowindow.setContent(
						markerContent
					);

					infowindow.open(map, this);
					setTimeout(function() {
						$(document).trigger('create');
					}, 150);
				});
				markers.push(marker);
			}
			var markerCluster = new MarkerClusterer(map, markers);

			$('.dialog').live('click', function() {
				var species = $(this).attr('data-species');
				var latin_name = $(this).attr('data-latin-name');
				$('.wikiheader h3').html(species + ' (' + latin_name + ')');

				$.get('/inc/getpage.inc.php?query='+latin_name, function(data) {
					$('.wikicontent').html(data);
				});

				$('#wikipedia').show().dialog();
				$('.wikicontent').show();
				return false;
			})

			$('.close').live('click', function() {
				$('.ui-dialog').dialog('close');
				$('#wikipedia').hide();
				$('.wikicontent').html('').hide();
			});
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest, textStatus, errorThrown);
		}
	});
});
