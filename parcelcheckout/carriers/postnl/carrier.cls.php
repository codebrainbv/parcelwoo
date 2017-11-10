<?php

	require_once(dirname(dirname(__FILE__)) . '/carrier.core.cls.php');
	

	class Carrier extends CarrierCore
	{
		// Load Carrier settings
		public function __construct()
		{
			$this->init();
		}

		
		// Export orders
		public function doExport()
		{
			global $aParcelCheckout;
			
			date_default_timezone_set('Europe/Amsterdam'); 

			// Find last exported order ID
			$sql = "SELECT `last_order_id` FROM `" . $aParcelCheckout['database']['prefix'] . "orders_batch` ORDER BY `id` DESC LIMIT 1";
			$aLastBatch = parcelcheckout_database_getRecord($sql);
			
			
			$sLastOrder = '';
			
			if(sizeof($aLastBatch))
			{
				$sLastOrder = $aLastBatch['last_order_id'];
			}
			
			
			
			
			

echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($aLastBatch);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";	
print_r($sLastOrder);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";			
exit;		
			
			// Grab orders and products, store in own database
			$aOrders = webshop::getOrders($sLastOrder);
			
			
			
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($aOrders);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
			
			
			
			
			// Use own database to build XML file and place on temp folder
			
			
			$sTimeStamp = date('YmdHis');
	
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($sTimeStamp);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
exit;		
			
			
			/*
			
			$sExportFile = 'ORDyyyymmddhhmmss.xml';
			
			
			
			$localFile  = 'test.xml';
			$remoteFile = 'public_html/ecs/test.xml';
			$port       = 22;
		
		
		*/
			
			
			
			
			
			
			
			// Upload XML file to FTP environment of PostNL
			
			
			
			
			
			echo 'SUCCESS';
		
		
		}
		
		
		
		public function getLocations()
		{
			global $aParcelCheckout;
			
			$aLocations = array();
			
			
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($aLocations);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";		
		}
		
		
	}




?>