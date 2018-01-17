<?php


	// Set default timezone (required in PHP 5+)
	if(function_exists('date_default_timezone_set'))
	{
		date_default_timezone_set('Europe/Amsterdam');
	}


	global $aParcelCheckout;
	$aParcelCheckout = array();
	
	
	require_once(dirname(__FILE__) . '/parcelcheckout.php');

	// Setup database
	parcelcheckout_database_setup();

	
	// Load carrier configuration
	$aParcelCheckout['carrier'] = parcelcheckout_getCarrierSettings();
	
	
	if(is_array($aParcelCheckout['carrier']))
	{
		if(file_exists($aParcelCheckout['carrier']['CARRIER_FILE']) == false)
		{
			parcelcheckout_die('ERROR: Cannot load file "' . $aParcelCheckout['carrier']['CARRIER_FILE'] . '".', __FILE__, __LINE__, false);
		}
		else
		{
			require_once($aParcelCheckout['carrier']['CARRIER_FILE']);
		}
	}

?>