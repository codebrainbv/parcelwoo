<?php

	class CarrierCore
	{
		protected $aSettings = false;

		public function __construct()
		{
			$this->init();
		}
		
		// Load carrier settings
		public function init()
		{
			$this->aSettings = parcelcheckout_getCarrierSettings();
		}
	}

?>