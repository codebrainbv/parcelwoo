<?php

	class webshop
	{
		// Return the software name
		public static function getSoftwareName()
		{
			return 'Wordpress 3.9.1 en WooCommerce 3.0.0+';
		}

		// Return the software code
		public static function getSoftwareCode()
		{
			return str_replace('_', '-', substr(basename(__FILE__), 0, -4));
		}



		// Return path to main cinfig file (if any)
		public static function getConfigFile()
		{
			return SOFTWARE_PATH . DS . 'wp-config.php';
		}



		// Return path to main config file (if any)
		public static function getConfigData()
		{
			$sConfigFile = self::getConfigFile();

			// Detect DB settings via configuration file
			if(is_file($sConfigFile))
			{
				return file_get_contents($sConfigFile);
			}

			return '';
		}



		// Find default database settings
		public static function getDatabaseSettings()
		{
			$aSettings['db_prefix'] = 'wp_';
			$sConfigData = self::getConfigData();

			if(!empty($sConfigData))
			{
				$aSettings['db_host'] = parcelcheckout_getFileValue($sConfigData, '/define\(\'DB_HOST\', \'([^\']+)\'\);/');
				$aSettings['db_user'] = parcelcheckout_getFileValue($sConfigData, '/define\(\'DB_USER\', \'([^\']+)\'\);/');
				$aSettings['db_pass'] = parcelcheckout_getFileValue($sConfigData, '/define\(\'DB_PASSWORD\', \'([^\']+)\'\);/');
				$aSettings['db_name'] = parcelcheckout_getFileValue($sConfigData, '/define\(\'DB_NAME\', \'([^\']+)\'\);/');
				$aSettings['db_prefix'] = parcelcheckout_getFileValue($sConfigData, '/\$table_prefix ? = \'([^\']+)\';/');
				$aSettings['db_type'] = (version_compare(PHP_VERSION, '5.3', '>') ? 'mysqli' : 'mysql');
			}

			return $aSettings;
		}
		
		
		// See if current software == self::$sSoftwareCode
		public static function isSoftware()
		{
			$aFiles = array();
			$aFiles[] = SOFTWARE_PATH . DS . 'wp-config.php';
			$aFiles[] = SOFTWARE_PATH . DS . 'wp-admin';
			$aFiles[] = SOFTWARE_PATH . DS . 'wp-content' . DS . 'plugins' . DS . 'woocommerce';

			foreach($aFiles as $sFile)
			{
				if(!is_file($sFile) && !is_dir($sFile))
				{
					return false;
				}
			}

			return true;
		}
	}

?>