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

		<div id="wikipedia">
			<div class="wikiwrap">
				<div class="wikiheader">
					<a href="#" data-icon="delete" data-iconpos="notext" class="ui-btn-left ui-btn ui-btn-icon-notext ui-btn-corner-all ui-shadow ui-btn-down-c ui-btn-up-c close" title="Close" data-theme="c"><span class="ui-btn-inner ui-btn-corner-all"><span class="ui-btn-text">Close</span><span class="ui-icon ui-icon-delete ui-icon-shadow"></span></span></a>
					<span class="heading"><h3 style="display:inline; color: #fff !important; font-weight: normal;">Black Bears</h3></span>
				</div>
				<div data-role="content" data-theme="c" class="wikicontent">
				</div>
			</div>
		</div>

	<?php include("inc/footerbar.inc.php"); ?>

	</div>


<?php include("inc/footer.inc.php"); ?>
