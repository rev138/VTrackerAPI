<?php include("inc/header.inc.php"); ?>

	<div data-role="content">

		<div class="ui-grid-b iconlist">
			<div class="ui-block-a"><a href="#"><img src="/path/to/icon.png" border="0" alt="Deer" />Deer</a></div>
			<div class="ui-block-b"><a href="#"><img src="/path/to/icon.png" border="0" alt="Moose" />Moose</a></div>
			<div class="ui-block-c"><a href="#"><img src="/path/to/icon.png" border="0" alt="Bear" />Bear</a></div>
		</div>

		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<li><a href="search.html">Search &amp; Browse</a></li>
		</ul>

		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<li data-role="list-divider">Report Other Species</li>
			<li><a href="#">Test Species</a></li>
			<li><a href="#">Test Species</a></li>
			<li><a href="#">Test Species</a></li>
			<li><a href="#">Test Species</a></li>
		</ul>

	</div>

<?php include("inc/footer.inc.php"); ?>
