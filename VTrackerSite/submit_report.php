<?php include("inc/header.inc.php"); ?>

<script id="category-info-template" type="text/x-handlebars-template">
	{{name}}
</script>

<div data-role="page" id="submit-report">

	<div data-role="header" data-theme="b">
		<a href="/" data-icon="home">Home</a>
		<h1>Report</h1>
	</div>

	<div data-role="content">

		<form action="submit_test.php" name="submit-report" method="POST">

			<!-- TODO : second level category picker? -->

			<div id="category-info">
				<script id="category-info-template" type="text/x-handlebars-template">
					<h2>{{name}}</h2>
				</script>
			</div>

			<div data-role="fieldcontain">
				<input type="hidden" name="apikey"  id="apikey" value="">
				<input type="hidden" name="latitude"  id="latitude" value="">
				<input type="hidden" name="longitude" id="longitude" value="">
				<input type="hidden" name="altitude" id="altitude" value="">
				<input type="hidden" name="timestamp" id="timestamp" value="">
			</div>

			<div data-role="fieldcontain">
				<fieldset id="species" data-role="controlgroup">
					<legend>Species:</legend>
					<script id="species-template" type="text/x-handlebars-template">
					{{#iter species}}
						<input type="radio" name="species" id="radio-choice-{{iPlus1}}" value="{{label}}" />
						<label for="radio-choice-{{iPlus1}}">{{label}}</label>
					{{/iter}}
					</script>

				</fieldset>
			</div>

			<div id="counts" class="ui-br" data-role="none" data-enhance="false">
				<legend>How Many?</legend>

				<div class="count-input">
					<input type="number" name="count_female" id="count_female" value=""  />
					<label for="count_female">Females</label>
				</div>

				<div class="count-input">
					<input type="number" name="count_male" id="count_male" value=""  />
					<label for="count_male">Males</label>
				</div>

				<div class="count-input">
					<input type="number" name="count_juvenile" id="count_juvenile" value=""  />
					<label for="count_juvenile">Young</label>
				</div>

				<div class="count-input">
					<input type="number" name="count_unknown" id="count_unknown" value=""  />
					<label for="count_juvenile">Unknown</label>
				</div>
			</div>



<!-- 			<div data-role="fieldcontain">
				<label for="name">Your Name:</label>
				<input type="text" name="name" id="name" value=""  />
			</div> -->

<!-- 			<div data-role="fieldcontain">
				<label for="email">Email:</label>
				<input type="email" name="email" id="email" value=""  />
			</div> -->

			<div class="ui-body ui-body-b">
				<fieldset class="ui-grid-a">
					<div class="ui-block-a"><button type="submit" data-theme="d">Cancel</button></div>
					<div class="ui-block-b"><button type="submit" data-theme="a">Submit</button></div>
				</fieldset>
			</div>
		</form>

	</div>
</div> <!-- #submit-report -->

<?php include("inc/footer.inc.php"); ?>
