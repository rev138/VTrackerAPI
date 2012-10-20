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

$(document).on("pageinit", function(event){
	function getLocation () {

	}
});
