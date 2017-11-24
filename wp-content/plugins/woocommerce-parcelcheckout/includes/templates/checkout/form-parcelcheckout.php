<?php

if(!defined('ABSPATH'))
{
	exit;
}

// Vind uw PostNL ophaalpunt

?>

<div class="parcelcheckout-formgroup" id="parcelcheckout-formgroup" >
	<h3>Titel</h3>
	<p class="form-row form-row-wide">
		<label class="" for="parcelcheckout-postcode-label">Postcode <abbr class="required" title="verplicht">*</abbr></label>
		<input type="text" class="input-text" name="parcelcheckout-postcode" id="parcelcheckout-postcode" placeholder="Postcode" value="" />
	</p>
	<p class="form-row form-row-wide">
		<a href="#" class="parcelcheckout-button" id="parcelcheckout-button">Vind ophaalpunt</a>
	</p>
	<div class="clear"></div>
	<p class="form-row message notes parcelcheckout-pickuppoint-results" id="parcelcheckout-pickuppoint-results"></p>
</div>

<?php
