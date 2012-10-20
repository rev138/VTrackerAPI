<?php include("inc/header.inc.php"); ?>

	<div data-role="page" id="submit-report">

		<div data-role="header" data-theme="b">
			<a href="/" data-icon="home">Home</a>
			<h1><?php echo $_GET['name']; ?> Report</h1>
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
						<input type="text" name="count_female" id="count_female" value=""  />
						<label for="count_female">Females</label>
					</div>

					<div class="count-input">
						<input type="text" name="count_male" id="count_male" value=""  />
						<label for="count_male">Males</label>
					</div>

					<div class="count-input">
						<input type="text" name="count_juvenile" id="count_juvenile" value=""  />
						<label for="count_juvenile">Young</label>
					</div>

					<div class="count-input">
						<input type="text" name="count_unknown" id="count_unknown" value=""  />
						<label for="count_juvenile">Unknown</label>
					</div>

				</div>

				<div data-role="fieldcontain">
					<label for="name">Your Name:</label>
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

<script type="text/javascript">

	$('#submit-report').on('pageinit', function() {

		Handlebars.registerHelper('iter', function(context, options) {
		  var fn = options.fn, inverse = options.inverse;
		  var ret = "";

		  if(context && context.length > 0) {
		    for(var i=0, j=context.length; i<j; i++) {
		    	// TODO - make a comma delimited list of common names as the label
		      ret = ret + fn($.extend({}, context[i], { i: i, iPlus1: i + 1 , label : context[i].common_names[0]}));
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
					keyValue = localStorage.getItem(keyLabel);
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

		// GET ANIMAL CATEGORY DATA TO POPULATE WITH
		$.ajax({
			type: "GET",
			data: {
				_id: "<?php echo $_GET['_id'] ?>",
				species: "1"
			},
			dataType:"json",
			//url: 'json/getCustomCategories.json',
			url: 'http://vtracker.hzsogood.net/api/get_categories',
			success: function(data) {
				var thisCategory = data["categories"][0];
				var sourceCatInfo = $("#category-info-template").html();
				var templateCatInfo = Handlebars.compile(sourceCatInfo);
				var sourceSpecies = $("#species-template").html();
				var templateSpecies = Handlebars.compile(sourceSpecies);
				$('#category-info').append(templateCatInfo(thisCategory));
				$('#species').append(templateSpecies(thisCategory));
				$("#submit-report").trigger("pagecreate");
				//refresh controlgroup?
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
		    console.log(XMLHttpRequest, textStatus, errorThrown);
			}
		});

// VALIDATION - SKIP FOR NOW

		function formCheck (form) {
			return true;
		} 

      $('form[name=submit-report]').submit(function() {
      		//IN PROGRESS
        	// console.log(this, keyValue);

	        // var data = {
	        //       "key" : keyValue,
	        //       "latitude"      : "43.0",
	        //       "longitude"     : "73.0",
	        //       "altitude"      : "35.0",
	        //       "attributes"    : {
	        //               "species"  : "Champtanystropheus americansus",
	        //               "count_male"    : "0",
	        //               "count_female"  : "0",
	        //               "count_juvenile" : "0",
	        //               "count_unknown" : "2",
	        //               "count_total"   : "2",
	        //               "is_track"      : "0"
	        //         },
	        //       };
	        // console.log(data);
          if(formCheck(this)) {
	        // $.ajax({
	        //   type: "POST",
	        //   data: {
	        //       "key" : keyValue,
	        //       "latitude"      : "43.0",
	        //       "longitude"     : "73.0",
	        //       "altitude"      : "35.0",
	        //       "attributes"    : {
	        //               "species"  : "Champtanystropheus americansus",
	        //               "count_male"    : "0",
	        //               "count_female"  : "0",
	        //               "count_juvenile" : "0",
	        //               "count_unknown" : "2",
	        //               "count_total"   : "2",
	        //               "is_track"      : "0"
	        //         },
	        //       },
	        //   dataType:"json",
	        //   url: 'http://vtracker.hzsogood.net/api/submit_report',
	        //   //url: 'http://vtracker.hzsogood.net/api/get_categories',
	        //   success: function(data) {
	        //   	alert('POSTED');
	        //   },
	        //   error: function (XMLHttpRequest, textStatus, errorThrown) {
	        //     console.log(XMLHttpRequest, textStatus, errorThrown);
	        //   }
	        // });
          }
          return false;
      });

	});
</script>
</div> <!-- #submit-report -->

<?php include("inc/footer.inc.php"); ?>
