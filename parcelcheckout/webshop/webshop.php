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
		
		
		
		public static function getOrders($sLastOrder)
		{
			global $aParcelCheckout;
			
				
			
			
			
			
			
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($sLastOrder);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
			
			
			// Get orders from WooCommerce and import in our own table
			
			$sql = "SELECT * FROM `` WHERE ()";
			
			
			
			
			
			
			/*
			
			$sql = "SELECT * FROM `` WHERE (`exported` = '0') ORDER BY `id` DESC";
			
			
			
			if(sizeof($aOrderIds))
			{
				$sql = "SELECT * FROM `#_parcelcheckout_orders` WHERE `id` IN ('" . implode("', '", $aOrderIds) . "') ORDER BY `id` ASC;";
				$rsOrders = clsDatabase::getRecords($sql);




				$rsOrders = array();
				$rsOrders[] = array('id' => 1, 'contact_name' => 'Test 1', 'contact_address' => 'Jan Steenstraat 175', 'contact_postalcode' => '7944TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '+31614707337', 'contact_email' => 'test1@php-solutions.nl', 'order_number' => 'WEB201100001');
				$rsOrders[] = array('id' => 2, 'contact_name' => 'Test 2', 'contact_address' => 'Jan Steenstraat 175 appartement 5', 'contact_postalcode' => '7944TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '0614707337', 'contact_email' => 'test2@php-solutions.nl', 'order_number' => 'WEB201100002');
				$rsOrders[] = array('id' => 3, 'contact_name' => 'Test 3', 'contact_address' => 'Jan Steenstraat 175/5', 'contact_postalcode' => '7944 TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '+31 (0) 614707337', 'contact_email' => 'test3@php-solutions.nl', 'order_number' => 'WEB201100003');
				$rsOrders[] = array('id' => 4, 'contact_name' => 'Test 4', 'contact_address' => 'Jan Steenstraat 175-5', 'contact_postalcode' => '7944TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '+31 6 14 707 337', 'contact_email' => 'test4@php-solutions.nl', 'order_number' => 'WEB201100004');
				$rsOrders[] = array('id' => 5, 'contact_name' => 'Test 5', 'contact_address' => 'Jan Steenstraat 175b', 'contact_postalcode' => '7944 TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '0031614707337', 'contact_email' => 'test5@php-solutions.nl', 'order_number' => 'WEB201100005');
	

				if(false) //sizeof($rsOrders))
				{
					$sData = 'ShipmentRefNo;CompanyName;LastName;FirstName;Country;Street;HouseNumber;HouseNumberExt;Zipcode;City;ProductCode;COD;Insurance;Telephone;Email;';

					foreach($rsOrders as $aOrder)
					{
						$sReferenceNr = $aOrder['id'];
						$sCompanyName = '';

						if(strcasecmp($aOrder['shipment_enabled'], '1') === 0)
						{
							$sFirstname = '';
							$sLastname = '';

							$aName = explode(' ', str_replace(';', '-', $aOrder['shipment_name']));

							$sLastname = array_pop($aName);
							$sFirstname = implode(' ', $aName);

							$sStreet = '';
							$sHomeNr = '';
							$sHomeNrExt = '';


							$aAddress = explode(' ', str_replace(';', '-', $aOrder['shipment_address']));

							while(sizeof($aAddress))
							{
								$a = array_shift($aAddress);

								if(preg_match('/^[0-9]+/', $a)) // Find number
								{
									$sHomeNr .= intval($a);
									$sHomeNrExt .= trim(substr($a, strlen($sHomeNr) + 1));

									while(sizeof($aAddress))
									{
										$a = array_shift($aAddress);
										$sHomeNrExt .= (empty($sHomeNrExt) ? '' : ' ') . $a;
									}
								}
								else
								{
									$sStreet .= (empty($sStreet) ? '' : ' ') . $a;
								}
							}

							$sZIP = $aOrder['shipment_postalcode'];
							$sCity = $aOrder['shipment_city'];
							$sCountry = $aOrder['shipment_country'];
							$sTelephone = $aOrder['contact_phone'];
							$sEmail = $aOrder['contact_email'];

						}
						elseif(strcasecmp($aOrder['company_enabled'], '1') === 0)
						{
							$sFirstname = '';
							$sLastname = '';
							$sCompanyName = $aOrder['company_name'];

							$aName = explode(' ', str_replace(';', '-', $aOrder['company_name']));

							$sLastname = array_pop($aName);
							$sFirstname = implode(' ', $aName);

							$sStreet = '';
							$sHomeNr = '';
							$sHomeNrExt = '';


							$aAddress = explode(' ', str_replace(';', '-', $aOrder['company_address']));

							while(sizeof($aAddress))
							{
								$a = array_shift($aAddress);

								if(preg_match('/^[0-9]+/', $a)) // Find number
								{
									$sHomeNr .= intval($a);
									$sHomeNrExt .= trim(substr($a, strlen($sHomeNr) + 1));

									while(sizeof($aAddress))
									{
										$a = array_shift($aAddress);
										$sHomeNrExt .= (empty($sHomeNrExt) ? '' : ' ') . $a;
									}
								}
								else
								{
									$sStreet .= (empty($sStreet) ? '' : ' ') . $a;
								}
							}

							$sZIP = $aOrder['company_postalcode'];
							$sCity = $aOrder['company_city'];
							$sCountry = $aOrder['company_country'];
							$sTelephone = $aOrder['company_contact_phone'];
							$sEmail = $aOrder['company_contact_email'];

						}
						else
						{
							$sFirstname = '';
							$sLastname = '';

							$aName = explode(' ', str_replace(';', '-', $aOrder['contact_name']));

							$sLastname = array_pop($aName);
							$sFirstname = implode(' ', $aName);

							$sStreet = '';
							$sHomeNr = '';
							$sHomeNrExt = '';

							$aAddress = explode(' ', str_replace(';', '-', $aOrder['contact_address']));

							while(sizeof($aAddress))
							{
								$a = array_shift($aAddress);

								if(preg_match('/^[0-9]+/', $a)) // Find number
								{
									$sHomeNr .= intval($a);
									$sHomeNrExt .= trim(substr($a, strlen($sHomeNr) + 1));

									while(sizeof($aAddress))
									{
										$a = array_shift($aAddress);
										$sHomeNrExt .= (empty($sHomeNrExt) ? '' : ' ') . $a;
									}
								}
								else
								{
									$sStreet .= (empty($sStreet) ? '' : ' ') . $a;
								}
							}

							$sZIP = $aOrder['contact_postalcode'];
							$sCity = $aOrder['contact_city'];
							$sCountry = $aOrder['contact_country'];
							$sTelephone = $aOrder['contact_phone'];
							$sEmail = $aOrder['contact_email'];
						}

	
	Productcode
	3085 Standaard Pakket
	3089 Pakket, handtekening voor ontvangst, alleen huisadres
	3189 Pakket, handtekening voor ontvangst, burenbelevering toegestaan
	3610 Pallet
	3630 Stukgoed
	4940 Zending binnen Europa (to B)
	4944 Zending binnen Europa (to C)

	

						// Set default to 3085
						$sProductCode = '3085';



						$sData .= CRLF . $sReferenceNr . ';' . $sCompanyName . ';' . $sLastname . ';' . $sFirstname . ';' . $sCountry . ';' . $sStreet . ';' . $sHomeNr . ';' . $sHomeNrExt . ';' . $sZIP . ';' . $sCity . ';' . $sProductCode . ';;;' . $sTelephone . ';' . $sEmail . ';';


					

						// Set order_status to "2"
						$sql = "UPDATE `#_module_" . MODULE_NAME . "_orders` SET `order_status` = '2', `shipment_status` = '2' WHERE (`id` = '" . $aOrder['id'] . "') LIMIT 1;";
						clsDatabase::execute($sql);
					}

					if($bExportAndPrint)
					{
						$sFile = FRONTEND_PATH . '/temp/temp/parcelware.csv';
						clsFile::write($sFile, $sData);

						$aFilesToZip[] = $sFile;

						clsFile::toZip($aFilesToZip, 'export.' . date('Ymd.His', NOW) . '.zip');
					}
					else
					{
						clsFile::output($sData, 'parcelware.' . date('Ymd.His', NOW) . '.csv');
					}
				}
			}




*/			
		}
	}

?>