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

		ret= ret + '<div class="' + blockClass + '"><a href="submit_report.php?_id=' + context[i]._id + '"><img src="' + icon + '" width="90px" border="0" alt="' + name + '" />' + "</a></div>";
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
	$('#apikey').val(keyValue).after('<p>APIkey:' + keyValue +'</p>');

	// GET LOCATION
	var locdata = {
		success : function (position) {
		console.log(position);
			if (position.coords.hasOwnProperty('latitude')) {
				$('#latitude').val(position.coords.latitude).after('<p>latitude:' + position.coords.latitude +'</p>');
			}
			if (position.coords.hasOwnProperty('longitude')) {
				$('#longitude').val(position.coords.longitude).after('<p>longitude:' + position.coords.longitude +'</p>');
			}
			if (position.coords.hasOwnProperty('altitude')) {
				$('#altitude').val(position.coords.altitude).after('<p>altitude:' + position.coords.altitude +'</p>');
			}
			if (position.hasOwnProperty('timestamp')) {
				$('#timestamp').val(position.timestamp).after('<p>timestamp:' + position.timestamp +'</p>');
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
		//url: 'json/getCustomCategories.json',
		url: 'http://vtracker.hzsogood.net/api/get_categories',
		success: function(data) {
			var thisCategory = data["categories"][0];
			var sourceCatInfo = $("#category-info-template").html();
			var templateCatInfo = Handlebars.compile(sourceCatInfo);
			var sourceSpecies = $("#species-template").html();
			var templateSpecies = Handlebars.compile(sourceSpecies);
			$('h1').prepend(templateCatInfo(thisCategory));
			$('#category-info').append(templateCatInfo(thisCategory));
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

	$('form[name=submit-report]').submit(function() {
        	var totalCount = 0;
        	var maleCount = (!$("#count_male",this).val())? "0" : $("#count_male",this).val();
        	var femaleCount =(!$("#count_female",this).val())? "0" : $("#count_female",this).val();
        	var juvenileCount = (!$("#count_juvenile",this).val())? "0" : $("#count_juvenile",this).val();
        	var unknownCount = (!$("#count_unknown",this).val())? "0" : $("#count_unknown",this).val();
        	totalCount = parseInt(maleCount) + parseInt(femaleCount) + parseInt(juvenileCount) + parseInt(unknownCount);
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

// GET ANIMAL CATEGORY DATA TO POPULATE WITH
	$.ajax({
		type: "GET",
		data: {
			expanded : 1,
			// type : "",
			// sort : "",
			// count : "",
		},
		dataType:"json",
		url: 'http://vtracker.hzsogood.net/api/get_reports',
		success: function(data) {

			// We need to bind the map with the "init" event otherwise bounds will be null
			$('#map').gmap({
				'center': '44.260113, -72.575386',
				'zoom': 7,
				'disableDefaultUI': false
			}).bind('init', function(evt, map) { 

				// Vermont bounds
				//# top left: 44.816855,-73.119176
				//# bottom right: 43.25283,-72.468923

				// var bounds = map.getBounds();
				// var southWest = bounds.getSouthWest();
				// var northEast = bounds.getNorthEast();
				// var lngSpan = northEast.lng() - southWest.lng();
				// var latSpan = northEast.lat() - southWest.lat();

				// _id: "50827f98c8f06d8c12000000"
				// attributes: Object
				// 		count_female: 0
				// 		count_juvenile: 0
				// 		count_male: 0
				// 		count_total: 1
				// 		count_unknown: 1
				// 		is_tracks: 0
				// 		species: "Odocoileus virginianus"
				// conditions: Object
				// 		dewpoint_c: 12
				// 		pressure_mb: 1004
				// 		relative_humidity_percent: 98
				// 		temp_c: 12.1
				// 		uv: 0
				// 		weather: "Clear"
				// 		wind_degrees: 112
				// 		wind_kph: 0
				// 		ip_address: "127.0.0.1"
				// location: Object
				// 		abbr: "VT"
				// 		country: "United States"
				// 		county: "Windsor"
				// 		elevation: 65.831186759603
				// 		lat_long: Array[2]
				// 		state: "Vermont"
				// 		town: "Plymouth"
				// 		zip: "05056"
				// timestamp: Object
				// 		day: 18
				// 		epoch_time: 1350589809
				// 		hour: 15
				// 		minute: 50
				// 		month: 10
				// 		second: 7
				// 		string: "Thu Oct 18 15:50:07 EDT 2012"
				// 		timezone: -400
				// 		year: 2012

				var reports = data["reports"],
					report;

				for ( var i = 0; i < reports.length; i++ ) {
	
					report = reports[i];

					attributes = report.attributes,
						count_female = attributes.count_female,
						count_juvenile = attributes.count_juvenile,
						count_male = attributes.count_male,
						count_total = attributes.count_total,
						count_unknown = attributes.count_unknown,
						is_tracks = attributes.is_tracks;

					if (attributes.species) {
						species = attributes.species;
						current_species = species.common_names[0];
						latin_name = species._id;
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

					if (report.timestamp) {
						timestamp = report.timestamp;
						day = timestamp.day;
						epoch_time = timestamp.epoch_time;
						hour = timestamp.hour;
						minute = timestamp.minute;
						month = timestamp.month;
						second = timestamp.second;
						string = timestamp.string;
						timezone = timestamp.timezone;
						year = timestamp.year;
					}

					$('#map').gmap('addMarker', { 
						'position': new google.maps.LatLng(lat, lng) 
					}).click(function() {
						$('#map').gmap('openInfoWindow', { content : "<a href='/inc/getpage.inc.php?query=" + latin_name + "' data-rel='dialog' data-role='button'>Read about " + current_species + "</a>" }, this);
					});
				}

				$('#map').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $('#map').gmap('get', 'markers')));
				// To call methods in MarkerClusterer simply call
				// $('#map_canvas').gmap('get', 'MarkerClusterer').callingSomeMethod();
			});	

		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			console.log(XMLHttpRequest, textStatus, errorThrown);
		}
	});
	$(".dialog").live("click", function() {
		$.mobile.changePage($(this).attr('href'),'pop',false,true);
	});
});
