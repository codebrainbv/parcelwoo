<?php


	/*
	if($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'] || $_SERVER['REMOTE_ADDR'] != "127.0.0.1" )
	{
		die('access is not permitted');
	}
	*/

	// Load setup
	require_once(dirname(__FILE__) . '/includes/php/init.php');
	

	if(parcelcheckout_getDebugMode())
	{
		parcelcheckout_log($_GET, __FILE__, __LINE__);
		parcelcheckout_log($_POST, __FILE__, __LINE__);
	}

	$oCarrier = new Carrier();	
	$oCarrier->doExportProducts();

?>