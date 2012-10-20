<?php include("inc/header.inc.php"); ?>

	<div data-role="page" id="search">

		<div data-role="header" data-theme="b">
			<a href="/" data-icon="home">Home</a>
			<h1>Search &amp; Browse</h1>
		</div>

		<div data-role="content">

			<div id="map"></div>

		</div>

		<div data-role="content">

			<ul data-role="listview" data-filter="true" data-theme="c" data-dividertheme="b">
				<li data-role="list-divider">Species</li>
				<li><a href="/search.php?species=bear">Bear</a></li>
				<li><a href="/search.php?species=deer">Deer</a></li>
				<li><a href="/search.php?species=moose">Moose</a></li>
				<li><a href="/search.php?species=catamount">Catamount</a></li>
			</ul>

		</div>


	<?php include("inc/footerbar.inc.php"); ?>

	</div>


<?php include("inc/footer.inc.php"); ?>
