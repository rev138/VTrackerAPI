<?php include("inc/header.inc.php"); ?>

	<div data-role="page" id="index">

		<div data-role="header" data-theme="a">
			<h1>VT Wildlife Tracker</h1>
		</div>

		<div data-role="content">

			<div id="animal-categories-main" class="ui-grid-b iconlist">
				<script id="categories-template-main" type="text/x-handlebars-template">
				{{#list-main categories}}{{/list-main}}
				</script>
			</div>

			<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
				<li><a href="search.php">Search &amp; Browse</a></li>
			</ul>

			<ul id="animal-categories-more" data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
				<li data-role="list-divider">Report Other Species</li>
				<script id="categories-template-more" type="text/x-handlebars-template">
				{{#each categories}}
				  <li><a href="submit_report.php?_id={{this._id}}&name={{this.name}}">{{this.name}}</a></li>
				{{/each}}

				</script>
			</ul>

		</div>
	</div>
<?php include("inc/footer.inc.php"); ?>
