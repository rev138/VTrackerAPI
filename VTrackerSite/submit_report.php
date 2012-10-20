<?php include("inc/header.inc.php"); ?>

	<div data-role="page" id="index">

		<div data-role="header" data-theme="a">
			<h1>VT Wildlife Tracker</h1>
		</div>

		<div data-role="content">

			<form action="submit_report" method="POST">

				<div data-role="fieldcontain">
					<input type="hidden" name="latitude"  id="latitude" value="">
					<input type="hidden" name="longitude" id="longitude" value="">
					<input type="hidden" name="altitude" id="altitude" value="">
				</div>

				<div data-role="fieldcontain">
					<fieldset data-role="controlgroup">
						<legend>Speicies:</legend>
						<input type="radio" name="radio-choice-1" id="radio-choice-1" value="choice-1" checked="checked" />
						<label for="radio-choice-1">Cat</label>

						<input type="radio" name="radio-choice-1" id="radio-choice-2" value="choice-2"  />
						<label for="radio-choice-2">Dog</label>

						<input type="radio" name="radio-choice-1" id="radio-choice-3" value="choice-3"  />
						<label for="radio-choice-3">Hamster</label>

						<input type="radio" name="radio-choice-1" id="radio-choice-4" value="choice-4"  />
						<label for="radio-choice-4">Lizard</label>
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
	$(document).ready(function () {
		var locdata = {
			success : function (position) {console.log(position);
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

	});
</script>
<?php include("inc/footer.inc.php"); ?>
