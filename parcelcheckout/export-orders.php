<?php

	// Load setup
	require_once(dirname(__FILE__) . '/inludes/php/init.php');
	
	if(parcelcheckout_getDebugMode())
	{
		parcelcheckout_log($_GET, __FILE__, __LINE__);
		parcelcheckout_log($_POST, __FILE__, __LINE__);
	}

	$oCarrier = new Carrier();	
	$oCarrier->doExportOrders();

?>