<!doctype html>
<html>
<head>
	<title>VTracker</title>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">

	<!-- Home screen icon  Mathias Bynens mathiasbynens.be/notes/touch-icons -->
	<!-- For iPhone 4 with high-resolution Retina display: -->
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="apple-touch-icon.png">
	<!-- For first-generation iPad: -->
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="apple-touch-icon.png">
	<!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
	<link rel="apple-touch-icon-precomposed" href="apple-touch-icon-precomposed.png">
	<!-- For nokia devices and desktop browsers : -->
	<link rel="shortcut icon" href="favicon.ico" />
	
	<!-- Mobile IE allows us to activate ClearType technology for smoothing fonts for easy reading -->
	<meta http-equiv="cleartype" content="on">

	<!-- jQuery Mobile CSS bits -->
	<link rel="stylesheet" href="css/jquery.mobile-1.2.0.min.css" />
	<link rel="stylesheet"  href="themes/vtracker.css" />

	<!-- Custom CSS -->
	<link rel="stylesheet" href="css/custom.css" />

	<!-- Javascript includes -->
	<script src="js/jquery/jquery-1.8.2-min.js"></script>
	<script type="text/javascript">
		$(document).bind("mobileinit", function () {
			$.mobile.ajaxEnabled = false;
		});
	</script>
	<script src="js/jquery/jquery.mobile-1.2.0.min.js"></script>
	<script src="js/handlebars/handlebars-1.0.rc.1.js"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&libraries=places"></script> 
	<script src="js/markerclusterer/markerclusterer.min.js"></script>
	<script src="js/jqueryui/jquery.ui.map.js"></script>
	<script src="js/jqueryui/jquery.ui.map.extensions.js"></script>
	<script src="js/jquery.raptorize.1.0.js"></script>
	
	<!-- Startup Images for iDevices -->
	<script>(function(){var a;if(navigator.platform==="iPad"){a=window.orientation!==90||window.orientation===-90?"images/startup-tablet-landscape.png":"images/startup-tablet-portrait.png"}else{a=window.devicePixelRatio===2?"images/startup-retina.png":"images/startup.png"}document.write('<link rel="apple-touch-startup-image" href="'+a+'"/>')})()</script>
	<!-- The script prevents links from opening in mobile safari. https://gist.github.com/1042026 -->
	<script>(function(a,b,c){if(c in b&&b[c]){var d,e=a.location,f=/^(a|html)$/i;a.addEventListener("click",function(a){d=a.target;while(!f.test(d.nodeName))d=d.parentNode;"href"in d&&(d.href.indexOf("http")||~d.href.indexOf(e.host))&&(a.preventDefault(),e.href=d.href)},!1)}})(document,window.navigator,"standalone")</script>
</head> 
<body> 
		
