<?php include("inc/header.inc.php"); ?>

	<div data-role="page" id="search">

		<div data-role="header" data-theme="b">
			<a href="/" data-icon="home">Home</a>
			<h1>Search &amp; Browse</h1>
		</div>

		<div data-role="content">

			<div id="map"></div>

			<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
				<li data-role="list-divider">Browse by Species</li>
				<li><a href="#">Test Species</a></li>
				<li><a href="#">Test Species</a></li>
				<li><a href="#">Test Species</a></li>
				<li><a href="#">Test Species</a></li>
			</ul>

		</div>

	<?php include("inc/footerbar.inc.php"); ?>

		<script type="text/javascript">

			$('#search').on('pageinit', function() {

				// TODO: Do the json API call

				// TODO: ON success of the json api call, do everything below this line
				// We need to bind the map with the "init" event otherwise bounds will be null
				setTimeout(function() {
					$('#map').gmap({
						'center': '44.260113, -72.575386',
						'zoom': 12,
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
						<?php include("inc/markers.inc.php"); ?>

						// TODO: Instead of this for loop, do a loop to iterate over the json data
						for ( var i = 0; i < markers.length; i++ ) {
							var points = markers[i].split(',');
							var lat = points[0];
							var lng = points[1];
							$('#map').gmap('addMarker', { 
								'position': new google.maps.LatLng(lat, lng) 
							}).click(function() {
								$('#map').gmap('openInfoWindow', { content : "<a href='http://en.wikipedia.org/w/index.php?title=Bear&action=render' class='dialog'>Read about Bears</a>" }, this);
							});
						}

						$('#map').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $(this).gmap('get', 'markers')));
						// To call methods in MarkerClusterer simply call
						// $('#map_canvas').gmap('get', 'MarkerClusterer').callingSomeMethod();
					});	

					$(".dialog").live("click", function() {
						$.mobile.changePage($(this).attr('href'),'pop',false,true);
					});
				}, 250);
			});
		</script>
	</div>


<?php include("inc/footer.inc.php"); ?>
