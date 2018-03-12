<?php

	require(SOFTWARE_PATH . '/wp-load.php');


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
		
		public static function isOrder($iOrderId)
		{
			if(!empty($iOrderId))
			{
				// Lookup order through WooCommerce
				$oOrder = wc_get_order($iOrderId);
				
				$aOrderData = $oOrder->get_data();
				
				// Order found is object?
				if(is_array($aOrderData))
				{
					$sOrderStatus = $aOrderData['status'];
					
					// Order found, do order status check
					if(strcmp($sOrderStatus, 'processing') === 0)
					{
						// Order has not been completed yet, return true
						return true;
					}
					elseif(strcmp($sOrderStatus, 'completed') === 0)
					{
						// Order has been completed already, do nothing
						return false;
					}
					else
					{
						// Order cancelled/refunded
						return false;
					}
				}
				else
				{
					// Order couldn't be found, return false
					return false;
				}
			}
			
		}
		
		
		
		public static function updateProductStock($oStock)
		{
			
			$iProductSku = $oStock->stockdtl_itemnum;
			
			// Get product related to SKU
			$aProduct = get_posts(array(
				'post_type' => 'product',
				'posts_per_page' => 100,
				'meta_query' => array(
					array(
						'key' => '_sku',
						'value' => (string) $iProductSku,
						'compare' => '='
					)
				)
			));



			// Product has been found, change its stock count
			if(sizeof($aProduct))
			{
				$iProductId = $aProduct['ID'];
					
				if(update_post_meta((int) $iProductId, '_stock', (int) $oStock->stockdtl_fysstock))
				{
					return true;	
				}
				else
				{
					parcelcheckout_log('Stock kon niet worden bijgewerkt voor product:' . (string) $iProductSku, __DIR__, __FILE__);
				}
			}
			else
			{
				parcelcheckout_log('Product kon niet gevonden worden met SKU:' . (string) $iProductSku, __DIR__, __FILE__);
			}
		}	

		public static function updateOrderShipment($oShipment)
		{
			/*
			$iProductSku = $oStock->stockdtl_itemnum;
			
			// Get product related to SKU
			$aProduct = get_posts(array(
				'post_type' => 'product',
				'posts_per_page' => 100,
				'meta_query' => array(
					array(
						'key' => '_sku',
						'value' => (string) $iProductSku,
						'compare' => '='
					)
				)
			));



			// Product has been found, change its stock count
			if(sizeof($aProduct))
			{
				$iProductId = $aProduct['ID'];
					
				if(update_post_meta((int) $iProductId, '_stock', (int) $oStock->stockdtl_fysstock))
				{
					return true;	
				}
				else
				{
					parcelcheckout_log('Stock kon niet worden bijgewerkt voor product:' . (string) $iProductSku, __DIR__, __FILE__);
				}
			}
			else
			{
				parcelcheckout_log('Product kon niet gevonden worden met SKU:' . (string) $iProductSku, __DIR__, __FILE__);
			}
			
			
			*/
		}		
	}

?>