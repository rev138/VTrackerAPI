<?php include("inc/header.inc.php"); ?>

	<div data-role="page" id="index">

		<div data-role="header" data-theme="a">
			<h1>VT Wildlife Tracker</h1>
		</div>

		<div data-role="content">

			<div id="animal-categories-main" class="ui-grid-b iconlist">
				<script id="categories-template-main" type="text/x-handlebars-template">
				<!-- not currently functioning -->
				{{#list-main categories}}
				  {{name}}
				{{/list-main}}
				</script>
				<div class="ui-block-a"><a href="#"><img src="images/animals/deer.jpg" width="90px" border="0" alt="Deer" /><span>Deer</span></a></div>
				<div class="ui-block-b"><a href="#"><img src="images/animals/moose.jpg" width="90px" border="0" alt="Moose" /><span>Moose</span></a></div>
				<div class="ui-block-c"><a href="#"><img src="images/animals/bear.gif" width="90px" border="0" alt="Bear" /><span>Bear</span></a></div>

			</div>

			<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
				<li><a href="search.php">Search &amp; Browse</a></li>
			</ul>

			<ul id="animal-categories-more" data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
				<li data-role="list-divider">Report Other Species</li>
				<script id="categories-template-more" type="text/x-handlebars-template">
				{{#each categories}}
				  <li><a href="#">{{this.name}}</a></li>
				{{/each}}

				</script>
			</ul>

		</div>

	</div>
<script type="text/javascript">
	$(document).ready(function () {

	//in progress... this does not seem to be rendering properly
	Handlebars.registerHelper('list-main', function(context, block) {
	var ret = "";

	//only top 10 categories
	for(var i=0; i<=10; i++) {
		if (i%3 == 1) {
			ret = ret + '<div class="ui-block-a">' + block(context[i]) + "</a></div>";
			//<a href="#"><img src="/path/to/icon.png" border="0" alt="Deer" />Deer</a></div>
		} else if (i%3 == 2) {
			ret = ret + '<div class="ui-block-b">' + block(context[i]) + "</a></div>";
		} else {
			ret = ret + '<div class="ui-block-c">' + block(context[i]) + "</a></div>";
		}
	}
	return ret;
	});

$.ajax({
	type: "GET",
	data: {
		// type : "",
		// sort : "",
		// count : "",
	},
	dataType:"json",
	// url: 'json/getDefaultCategories.json',
	url: 'http://vtracker.hzsogood.net/api/get_categories',
	success: function(data) {
			//todo - get the main list working
		//$('#animal-categories-main').append(template(data)).iconlist("refresh");
		var source = $("#categories-template-more").html();
		var template = Handlebars.compile(source);
		$('#animal-categories-more').append(template(data)).listview("refresh");
	},
	error: function (XMLHttpRequest, textStatus, errorThrown) {
	    console.log(XMLHttpRequest, textStatus, errorThrown);
	}
});

	});
</script>
<?php include("inc/footer.inc.php"); ?>
