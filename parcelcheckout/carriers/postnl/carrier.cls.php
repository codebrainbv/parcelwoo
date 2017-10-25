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
			
			
			// Grab orders and products, store in own database
			$aOrders = webshop::getOrders();
			
			
			
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($aOrders);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
exit;
			
			
			
			
			// Use own database to build XML file and place on temp folder
			
			
			
			
			
			
			
			// Upload XML file to FTP environment of PostNL
			
			
			
			
			
			echo 'SUCCESS';
		
		
		}
	}




?>