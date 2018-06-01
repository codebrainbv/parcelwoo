<?php

	class PARCELCHECKOUT_FOR_WORDPRESS_3_9_1_AND_WOOCOMMERCE_2_1_1
	{
		// Return the software name
		public static function getSoftwareName()
		{
			return 'Wordpress 3.9.1 en WooCommerce 2.1.1+';
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



		// Return path to main cinfig file (if any)
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
		public static function getDatabaseSettings($aSettings)
		{
			$aSettings['db_prefix'] = 'wp_';
			$sConfigData = self::getConfigData();

			if(!empty($sConfigData))
			{
				$aSettings['db_host'] = PARCELCHECKOUT_INSTALL::getFileValue($sConfigData, '/define\(\'DB_HOST\', \'([^\']+)\'\);/');
				$aSettings['db_user'] = PARCELCHECKOUT_INSTALL::getFileValue($sConfigData, '/define\(\'DB_USER\', \'([^\']+)\'\);/');
				$aSettings['db_pass'] = PARCELCHECKOUT_INSTALL::getFileValue($sConfigData, '/define\(\'DB_PASSWORD\', \'([^\']+)\'\);/');
				$aSettings['db_name'] = PARCELCHECKOUT_INSTALL::getFileValue($sConfigData, '/define\(\'DB_NAME\', \'([^\']+)\'\);/');
				$aSettings['db_prefix'] = PARCELCHECKOUT_INSTALL::getFileValue($sConfigData, '/\$table_prefix ? = \'([^\']+)\';/');
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




		// Install plugin, return text
		public static function doInstall($aSettings)
		{
			PARCELCHECKOUT_INSTALL::doInstall($aSettings);
						
			return true;
		}



		// Install plugin, return text
		public static function getInstructions($aSettings)
		{
			$sHtml = '';
			$sHtml .= '<ol>';
			$sHtml .= '<li>Log in op de beheeromgeving van uw webshop.</li>';
			$sHtml .= '<li>Ga naar plug-ins en activeer de "Parcel Checkout - WooCommerce" plug-in.</li>';
			$sHtml .= '<li>Klik in het hoofdmenu op WooCommerce / Settings.</li>';
			$sHtml .= '<li>Klik op het tabblad "Shipment", en klik op bewerken op zone Nederland.</li>';
			$sHtml .= '<li>Activeer de verzendmethode die u wil gebruiken.</li>';
			$sHtml .= '<li>Klik op bewerken voor eventuele instellingen</li>';
			$sHtml .= '<li>Activeer desgewenst op dezelfe manier overige verzendmethoden.</li>';
			$sHtml .= '</ol>';

			return $sHtml;
		}
	}

?>