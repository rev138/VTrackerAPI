<?php
	$url = "http://en.wikipedia.org/w/index.php?title=" . urlencode($_GET['query']) ."&action=render";
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_USERAGENT, "VTracker/1.0 (http://vtracker.hzsogood.net/; info@hzsogood.net)");
	$output = curl_exec($curl);
	print $output;
	curl_close($curl);
?>
