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
			
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r(webshop::getDatabaseSettings());
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
			
			
			
			
			
			
			
			
			
			
			
			echo 'SUCCESS';
		
		
		}
	}




?>