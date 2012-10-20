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
				  <li><a href="submit_report.php?id={{this.id}}">{{this.name}}</a></li>
				{{/each}}

				</script>
			</ul>

		</div>

	</div>
<script type="text/javascript">
	$(document).ready(function () {

	Handlebars.registerHelper('list-main', function(context, options) {
		var ret = "", blockClass;

		//only top 10 categories
		for(var i=0, l=context.length; i<l && i<10; i++) {
			if (i%3 == 0) {
				blockClass= "ui-block-a";
			} else if (i%3 == 1) {
				blockClass= "ui-block-b";
			} else {
				blockClass= "ui-block-c";
			}
		ret= ret + '<div class="' + blockClass + '"><a href="submit_report.php?id=context[i].id"><img src="images/animals/deer.jpg" width="90px" border="0" alt="' + context[i].name + '" />' + context[i].name + "</a></div>";
		}
		return ret;
	});

$.ajax({
	type: "GET",
	data: {
		top_category : "1",
		// type : "",
		// sort : "",
		// count : "",
	},
	dataType:"json",
	// url: 'json/getDefaultCategories.json',
	url: 'http://vtracker.hzsogood.net/api/get_categories',
	success: function(data) {
		//ideally would split the content over the two templates
		var sourceMain = $("#categories-template-main").html();
		var templateMain = Handlebars.compile(sourceMain);
		$('#animal-categories-main').append(templateMain(data)); //refresh equivalent?

		var sourceMore = $("#categories-template-more").html();
		var templateMore = Handlebars.compile(sourceMore);
		$('#animal-categories-more').append(templateMore(data)).listview("refresh");
	},
	error: function (XMLHttpRequest, textStatus, errorThrown) {
	    console.log(XMLHttpRequest, textStatus, errorThrown);
	}
});

	});
</script>
<?php include("inc/footer.inc.php"); ?>
