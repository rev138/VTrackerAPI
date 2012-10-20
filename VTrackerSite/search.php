<?php include("inc/header.inc.php"); ?>

	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&libraries=places"></script> 
	<script src="js/markerclusterer/markerclusterer.min.js"></script>
	<script src="js/jqueryui/jquery.ui.map.js"></script>
	<script src="js/jqueryui/jquery.ui.map.extensions.js"></script>

	<div data-role="page" id="gps_map">

		<div data-role="header" data-theme="b">
			<a href="/" data-icon="home">Home</a>
			<h1>Wildlife Search</h1>
		</div>

		<div data-role="content">

			<div id="map_canvas" style="height:300px;"></div>

			<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
				<li data-role="list-divider">Browse by Species</li>
				<li><a href="#">Test Species</a></li>
				<li><a href="#">Test Species</a></li>
				<li><a href="#">Test Species</a></li>
				<li><a href="#">Test Species</a></li>
			</ul>

		</div>


	<?php include("inc/footerbar.inc.php"); ?>

	</div>

	<script type="text/javascript">

		$('#gps_map').live('pageinit', function() {

			// Do the json API call

			// ON success of the json api call, do everything below this line

			// 
			// We need to bind the map with the "init" event otherwise bounds will be null
			$('#map_canvas').gmap({
				'center': '44.260113, -72.575386',
				'zoom': 8, 'disableDefaultUI': true
			}).bind('init', function(evt, map) { 

				// Vermont bounds
				//# top left: 44.816855,-73.119176
				//# bottom right: 43.25283,-72.468923

				var bounds = map.getBounds();
				var southWest = bounds.getSouthWest();
				var northEast = bounds.getNorthEast();
				var lngSpan = northEast.lng() - southWest.lng();
				var latSpan = northEast.lat() - southWest.lat();

				// Instead of this for loop, do a loop to iterate over the json data
				for ( var i = 0; i < 200; i++ ) {

					// replace these randomly generated map points with the map points set in the json data
					var lat = southWest.lat() + latSpan * Math.random();
					var lng = southWest.lng() + lngSpan * Math.random();
					$('#map_canvas').gmap('addMarker', { 
						'position': new google.maps.LatLng(lat, lng) 
					}).click(function() {
						$('#map_canvas').gmap('openInfoWindow', { content : 'Hello world!' }, this);
					});
				}

				$('#map_canvas').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $(this).gmap('get', 'markers')));
				// To call methods in MarkerClusterer simply call
				// $('#map_canvas').gmap('get', 'MarkerClusterer').callingSomeMethod();
			});
		});
	</script>

<?php include("inc/footer.inc.php"); ?>
