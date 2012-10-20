<?php include("inc/header.inc.php"); ?>

	<div data-role="page" id="submit-report">

		<div data-role="header" data-theme="b">
			<a href="/" data-icon="home">Home</a>
			<h1>Wildlife Search</h1>
		</div>

		<div data-role="content">

			<form action="submit_report" method="POST">

				<div data-role="fieldcontain">
					<input type="hidden" name="apikey"  id="apikey" value="">
					<input type="hidden" name="latitude"  id="latitude" value="">
					<input type="hidden" name="longitude" id="longitude" value="">
					<input type="hidden" name="altitude" id="altitude" value="">
				</div>

				<!-- TODO : second level category picker? -->

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

				<div data-role="fieldcontain">
					<label for="name">Name:</label>
					<input type="text" name="name" id="name" value=""  />
				</div>

				<div data-role="fieldcontain">
					<label for="email">Email:</label>
					<input type="email" name="email" id="email" value=""  />
				</div>

				<div class="ui-body ui-body-b">
					<fieldset class="ui-grid-a">
						<div class="ui-block-a"><button type="submit" data-theme="d">Cancel</button></div>
						<div class="ui-block-b"><button type="submit" data-theme="a">Submit</button></div>
					</fieldset>
				</div>
			</form>

		</div>

	</div>
<script type="text/javascript">

		$('#submit-report').on('pageinit', function() {

		Handlebars.registerHelper('iter', function(context, options) {
		  var fn = options.fn, inverse = options.inverse;
		  var ret = "";

		  if(context && context.length > 0) {
		    for(var i=0, j=context.length; i<j; i++) {
		      ret = ret + fn($.extend({}, context[i], { i: i, iPlus1: i + 1 , label : context[i]}));
		    }
		  } else {
		    ret = inverse(this);
		  }
		  return ret;
		});

		// GET/STORE API KEY
		// TODO : hide/show name & address input based on stored key ... a timing issue here
		var keyLabel = "VTrackAPIKey";
		var keyValue = localStorage.getItem(keyLabel);
		if (!keyValue) {
			$.ajax({
				type: "POST",
				data: {
				},
				dataType:"json",
				url: 'http://vtracker.hzsogood.net/api/new_key',
				success: function(data) {
					localStorage.setItem(keyLabel, data.key);
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					console.log("couldn't get API key");
				}
			});
		}
		$('#apikey').val(keyValue).after('<p>APIkey:' + keyValue +'</p>');

		// GET LOCATION
		var locdata = {
			success : function (position) {
				if (position.coords.hasOwnProperty('latitude')) {
					$('#latitude').val(position.coords.latitude).after('<p>latitude:' + position.coords.latitude +'</p>');
				}
				if (position.coords.hasOwnProperty('longitude')) {
					$('#longitude').val(position.coords.longitude).after('<p>longitude:' + position.coords.longitude +'</p>');
				};
				if (position.coords.hasOwnProperty('altitude')) {
					$('#altitude').val(position.coords.altitude).after('<p>altitude:' + position.coords.altitude +'</p>');
				};
			}
		};
		$().getLocationData(locdata);

		// GET ANIMAL CATEGORY DATA
		$.ajax({
			type: "GET",
			data: {
			},
			dataType:"json",
			url: 'json/getCustomCategories.json',
			//url: 'http://vtracker.hzsogood.net/api/get_categories',
			success: function(data) {
				var thisCategory = data["categories"][0];
				var source = $("#species-template").html();
				var template = Handlebars.compile(source);
				$('#species').append(template(thisCategory));
				$("#submit-report").trigger("pagecreate");
				//refresh controlgroup?
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
		    console.log(XMLHttpRequest, textStatus, errorThrown);
			}
		});

	});
</script>

<?php include("inc/footer.inc.php"); ?>
