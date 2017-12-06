<?php

if(!defined('ABSPATH'))
{
	exit;
}


?>

<div class="parcelcheckout-formgroup" id="parcelcheckout-formgroup" >
	<h3>Vind uw PostNL ophaalpunt</h3>
	<p class="form-row form-row-wide">
		<label class="" for="parcelcheckout-postcode-label">Postcode <abbr class="required" title="verplicht">*</abbr></label>
		<input type="text" class="input-text" name="parcelcheckout-postcode" id="parcelcheckout-postcode" placeholder="Postcode" value="">
	</p>
	<p class="form-row form-row-wide">
		<label class="" for="parcelcheckout-deliveryoption-label">Verzendopties <abbr class="required" title="verplicht">*</abbr></label>
		<input type="radio" id="parcelcheckout-deliveryoption" name="parcelcheckout-deliveryoption" value="PG" checked="checked"> Ophalen bij PostNL Locatie<br>
		<input type="radio" id="parcelcheckout-deliveryoption" name="parcelcheckout-deliveryoption" value="PGE"> Extra vroeg ophalen<br>
		<!-- <input type="radio" id="parcelcheckout-deliveryoption" name="parcelcheckout-deliveryoption" value="KEL"> Klant Eigen Locatie -->
	</p>
	<p class="form-row form-row-wide">
		<a href="#" class="parcelcheckout-button" id="parcelcheckout-button">Vind ophaalpunt</a>
	</p>
	<div class="clear"></div>
	<p class="form-row message notes parcelcheckout-pickuppoint-results" id="parcelcheckout-pickuppoint-results"></p>
	<p class="form-row message notes parcelcheckout-pickuppoint-openingtime" id="parcelcheckout-pickuppoint-openingtime"></p>
	<div id="results-map" style="height: 300px; width: 100%;"></div>
</div>

<?php
