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
		ret= ret + '<div class="' + blockClass + '"><a href="submit_report.php?id=context[i]._id"><img src="images/animals/deer.jpg" width="90px" border="0" alt="' + context[i].name + '" />' + context[i].name + "</a></div>";
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
				// TODO - make a comma delimited list of common names as the label
				ret = ret + fn($.extend({}, context[i], { i: i, iPlus1: i + 1 , label : context[i].common_names[0]}));
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
			if (position.coords.hasOwnProperty('latitude')) {
				$('#latitude').val(position.coords.latitude).after('<p>latitude:' + position.coords.latitude +'</p>');
			}
			if (position.coords.hasOwnProperty('longitude')) {
				$('#longitude').val(position.coords.longitude).after('<p>longitude:' + position.coords.longitude +'</p>');
			}
			if (position.coords.hasOwnProperty('altitude')) {
				$('#altitude').val(position.coords.altitude).after('<p>altitude:' + position.coords.altitude +'</p>');
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
			$('#category-info').append(templateCatInfo(thisCategory));
			$('#species').append(templateSpecies(thisCategory));
			$("#submit-report").trigger("pagecreate");
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
			//IN PROGRESS
			// console.log(this, keyValue);

			// var data = {
			//       "key" : keyValue,
			//       "latitude"      : "43.0",
			//       "longitude"     : "73.0",
			//       "altitude"      : "35.0",
			//       "attributes"    : {
			//               "species"  : "Champtanystropheus americansus",
			//               "count_male"    : "0",
			//               "count_female"  : "0",
			//               "count_juvenile" : "0",
			//               "count_unknown" : "2",
			//               "count_total"   : "2",
			//               "is_track"      : "0"
			//         },
			//       };
			// console.log(data);
		if(formCheck(this)) {
			// $.ajax({
			//   type: "POST",
			//   data: {
			//       "key" : keyValue,
			//       "latitude"      : "43.0",
			//       "longitude"     : "73.0",
			//       "altitude"      : "35.0",
			//       "attributes"    : {
			//               "species"  : "Champtanystropheus americansus",
			//               "count_male"    : "0",
			//               "count_female"  : "0",
			//               "count_juvenile" : "0",
			//               "count_unknown" : "2",
			//               "count_total"   : "2",
			//               "is_track"      : "0"
			//         },
			//       },
			//   dataType:"json",
			//   url: 'http://vtracker.hzsogood.net/api/submit_report',
			//   //url: 'http://vtracker.hzsogood.net/api/get_categories',
			//   success: function(data) {
			//		alert('POSTED');
			//   },
			//   error: function (XMLHttpRequest, textStatus, errorThrown) {
			//     console.log(XMLHttpRequest, textStatus, errorThrown);
			//   }
			// });
		}
		return false;
	});
});

/* SEARCH PAGE */
$('#search').on('pageinit', function() {
	// TODO: Do the json API call

	// TODO: ON success of the json api call, do everything below this line
	// We need to bind the map with the "init" event otherwise bounds will be null
	$('#map').each(function() {
		$(this).gmap({
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

			// TODO: Replace this with actual markers from JSON
			<?php include("../../inc/markers.inc.php"); ?>

			// TODO: Instead of this for loop, do a loop to iterate over the json data
			for ( var i = 0; i < markers.length; i++ ) {
				var points = markers[i].split(',');
				var lat = points[0];
				var lng = points[1];
				$(this).gmap('addMarker', { 
					'position': new google.maps.LatLng(lat, lng) 
				}).click(function() {
					$(this).gmap('openInfoWindow', { content : "<a href='http://en.wikipedia.org/w/index.php?title=Bear&action=render' class='dialog'>Read about Bears</a>" }, this);
				});
			}

			$(this).gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $(this).gmap('get', 'markers')));
			// To call methods in MarkerClusterer simply call
			// $('#map_canvas').gmap('get', 'MarkerClusterer').callingSomeMethod();
		});	
	});

	// $(".dialog").live("click", function() {
	// 	$.mobile.changePage($(this).attr('href'),'pop',false,true);
	// });
});
