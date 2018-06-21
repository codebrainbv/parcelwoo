<?php

	class PARCELCHECKOUT_INSTALL
	{
		public static $sSoftwareCode = false;
		public static $sProviderCode = false;

		// ... 
		public static function setSoftware($sSoftwareCode = false)
		{
			self::$sSoftwareCode = false;

			if($sSoftwareCode === false)
			{
				// Try to autodetect code
				$sSoftwareCode = self::getSoftwareCode();
			}

			if($sSoftwareCode !== false)
			{
				$sSoftwarePath = self::getSoftwarePath();
				$sFile = self::getFileName($sSoftwareCode);

				if(is_file($sSoftwarePath . '/' . $sFile))
				{
					require_once($sSoftwarePath . '/' . $sFile);
				}
				else
				{
					$sSoftwareCode = false;
				}
			}

			if($sSoftwareCode)
			{
				self::$sSoftwareCode = $sSoftwareCode;
				return self::getClassName($sSoftwareCode);
			}

			return false;
		}

		// ... 
		public static function setProvider($sProviderCode = false)
		{
			if($sProviderCode !== false)
			{
				$sProviderPath = self::getProviderPath();
				$sFile = self::getFileName($sProviderCode);

				if(is_file($sProviderCode . '/' . $sFile))
				{
					require_once($sProviderCode . '/' . $sFile);
				}
				else
				{
					$sSoftwareCode = false;
				}
			}

			self::$sProviderCode = $sProviderCode;
			return !empty($sProviderCode);
		}

		// Detect current software code (returns FALSE on failure)
		public static function getSoftwareCode()
		{
			if(!empty(self::$sSoftwareCode))
			{
				return self::$sSoftwareCode;
			}

			$sSoftwarePath = self::getSoftwarePath();
			$aFiles = self::getFiles($sSoftwarePath);

			if(sizeof($aFiles) > 1)
			{
				// Set files in reverse order so latest version numbers are checked first
				usort($aFiles, 'strnatcasecmp');
				$aFiles = array_reverse($aFiles);
			}

			foreach($aFiles as $sFile)
			{
				require_once($sSoftwarePath . '/' . $sFile);

				$sClassName = self::getClassName($sFile);

				// Test software
				if(class_exists($sClassName, false))
				{
					$aTest = call_user_func($sClassName . '::isSoftware');

					if(is_array($aTest))
					{
						$bSoftwareFound = array_shift($aTest);
					}
					else
					{
						$bSoftwareFound = !empty($aTest);
					}

					if($bSoftwareFound)
					{
						return call_user_func($sClassName . '::getSoftwareCode');
					}
				}
			}

			return false;
		}

		// ... 
		public static function getSoftwareClass()
		{
			if(self::$sSoftwareCode)
			{
				return self::getClassName(self::$sSoftwareCode);
			}

			return false;
		}

		// ... 
		public static function getSoftwareFile()
		{
			if(self::$sSoftwareCode)
			{
				return self::getFileName(self::$sSoftwareCode);
			}

			return false;
		}

		// ... 
		public static function getSoftwarePath()
		{
			return PARCELCHECKOUT_PATH . '/install/software';
		}




		// Find available provider codes (/parcelcheckout/install/providers/[code].php)
		public static function getProviderCodes()
		{
			$sProviderPath = self::getProviderPath();
			$aFiles = self::getFiles($sProviderPath);

			$aCodes = array();

			foreach($aFiles as $aFile)
			{
				$aCodes[] = substr($aFile, 0, -4);
			}

			return $aCodes;
		}

		// Find 
		public static function getProviderPath()
		{
			return PARCELCHECKOUT_PATH . '/install/provider';
		}


		
		// ...
		public static function getClassName($sCode)
		{
			if(strpos($sCode, '.php') !== false)
			{
				$sCode = substr($sCode, 0, -4);
			}

			return strtoupper(str_replace('-', '_', $sCode));
		}

		// ...
		public static function getFileName($sCode)
		{
			return strtolower(str_replace('_', '-', $sCode)) . '.php';
		}

		// ...
		public static function getFiles($sFolderPath, $bShowHiddenFiles = false, $bAddPath = false)
		{
			$aFiles = array();

			if(is_dir($sFolderPath))
			{
				if($oHandle = opendir($sFolderPath))
				{
					while(($sFile = readdir($oHandle)) !== false) 
					{
						if($sFile == '.')
						{
							// Current Dir
						}
						elseif($sFile == '..')
						{
							// Parent Dir
						}
						else
						{
							$sFirstChar = substr($sFile, 0, 1);

							if($bShowHiddenFiles || !in_array($sFirstChar, array('.', '_', '-')))
							{
								if(is_file($sFolderPath . '/' . $sFile))
								{
									$aFiles[] = ($bAddPath ? $sFolderPath . '/' : '') . $sFile;
								}
							}
						}
					}

					if(sizeof($aFiles) > 1)
					{
						usort($aFiles, 'strcasecmp');
					}

					closedir($oHandle);
				}
			}

			return $aFiles;
		}

		// ...
		public static function getFolders($sFolderPath, $bShowHiddenFolders = false, $bAddPath = false)
		{
			$aFolders = array();

			if(is_dir($sFolderPath))
			{
				if($oHandle = opendir($sFolderPath))
				{
					while(($sFolder = readdir($oHandle)) !== false) 
					{
						if($sFolder == '.')
						{
							// Current Dir
						}
						elseif($sFolder == '..')
						{
							// Parent Dir
						}
						else
						{
							$sFirstChar = substr($sFolder, 0, 1);

							if($bShowHiddenFolders || !in_array($sFirstChar, array('.', '_', '-')))
							{
								if(is_dir($sFolderPath . '/' . $sFolder))
								{
									$aFolders[] = ($bAddPath ? $sFolderPath . '/' : '') . $sFolder;
								}
							}
						}
					}

					if(sizeof($aFolders) > 1)
					{
						usort($aFolders, 'strcasecmp');
					}

					closedir($oHandle);
				}
			}

			return $aFolders;
		}



		public static function getFilesAndFolders($sSoftwareClass = false)
		{
			// Verify read/write privileges
			$aFilesAndFolders = array();

			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'carrier.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'database.php';
			
			if(false)
			{
				$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'install';
				$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'install' . DS . 'index.php';
				$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'install' . DS . 'step-1.php';
				$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'install' . DS . 'step-2.php';
				$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'install' . DS . 'step-3.php';
				$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'install' . DS . 'step-4.php';
			}
			
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'temp';

			if(!empty($sSoftwareClass))
			{
				$sCallable = $sSoftwareClass . '::getFilesAndFolders';

				if(is_callable($sCallable))
				{
					$a = call_user_func($sSoftwareClass . '::getFilesAndFolders');

					if(is_array($a) && sizeof($a))
					{
						foreach($a as $sPath)
						{
							$aFilesAndFolders[] = $sPath;
						}
					}
				}
			}

			return $aFilesAndFolders;
		}


		// ... 
		public static function getAdminFolder($aAdminSubFiles = false, $aAdminSubFolders = false)
		{
			$sAdminFolder = false;

			if(!is_array($aAdminSubFiles))
			{
				$aAdminSubFiles = array();
			}

			if(!is_array($aAdminSubFolders))
			{
				$aAdminSubFolders = array();
			}

			$aRootFolders = self::getFolders(SOFTWARE_PATH);

			foreach($aRootFolders as $sAdminFolder)
			{
				foreach($aAdminSubFiles as $sFile)
				{
					if(!is_file(SOFTWARE_PATH . '/' . $sAdminFolder . '/' . $sFile))
					{
						$sAdminFolder = false;
						break;
					}
				}

				if($sAdminFolder)
				{
					foreach($aAdminSubFolders as $sFolder)
					{
						if(!is_dir(SOFTWARE_PATH . '/' . $sAdminFolder . '/' . $sFolder))
						{
							$sAdminFolder = false;
							break;
						}
					}

					if($sAdminFolder)
					{
						return $sAdminFolder;
					}
				}
			}

			return false;
		}


		// Install plugin, return text
		public static function doInstall(&$aSettings)
		{
			self::setLog('Running installation.', __FILE__, __LINE__);
			
			// Set default timezone
			if(function_exists('date_default_timezone_set'))
			{
				date_default_timezone_set('Europe/Amsterdam');
			}
			
			$sCurrentTime = time();
			
			// Set read/write privileges or output instructions
			$aFilesAndFolders = self::getFilesAndFolders($aSettings['code']);

			foreach($aFilesAndFolders as $sFolder)
			{
				self::setLog('Changing CHMOD for: ' . $sFolder, __FILE__, __LINE__);
				self::chmodFolder($sFolder);
			}

			// Create #_parcelcheckout table
			self::setLog('Creating database table #_parcelcheckout', __FILE__, __LINE__);

			$sql = "CREATE TABLE IF NOT EXISTS `" . $aSettings['db_prefix'] . "parcelcheckout_orders_batch` (
`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
`batch_id` VARCHAR(64) DEFAULT NULL, 
`last_order_id` VARCHAR(64) DEFAULT NULL, 
`orders` LONGTEXT DEFAULT NULL, 
PRIMARY KEY (`id`));";
			parcelcheckout_database_execute($sql);


			$sql = "CREATE TABLE IF NOT EXISTS `" . $aSettings['db_prefix'] . "parcelcheckout_orders` (
`id` int(8) unsigned NOT NULL AUTO_INCREMENT, 
`order_number` varchar(64) DEFAULT NULL,
`order_date` varchar(64) DEFAULT NULL,
`order_time` varchar(64) DEFAULT NULL,
`customer_id` varchar(64) DEFAULT NULL,
`shipment_title` varchar(32) DEFAULT NULL,
`shipment_firstname` varchar(64) DEFAULT NULL,
`shipment_surname` varchar(64) DEFAULT NULL,
`shipment_company` varchar(255) DEFAULT NULL,
`shipment_address_full` varchar(255) DEFAULT NULL,
`shipment_address_street` varchar(255) DEFAULT NULL,
`shipment_address_number` varchar(32) DEFAULT NULL,
`shipment_address_number_extension` varchar(32) DEFAULT NULL,
`shipment_postalcode` varchar(64) DEFAULT NULL,
`shipment_city` varchar(64) DEFAULT NULL,
`shipment_country_iso` varchar(64) DEFAULT NULL,
`shipment_country` varchar(64) DEFAULT NULL,
`shipment_phone` varchar(64) DEFAULT NULL,
`shipment_email` varchar(64) DEFAULT NULL,
`shipment_agent` varchar(64) DEFAULT NULL,
`shipment_type` varchar(64) DEFAULT NULL,
`shipment_product_option` varchar(64) DEFAULT NULL,
`shipment_option` varchar(64) DEFAULT NULL,
`shipment_dateofbirth` varchar(64) DEFAULT NULL,
`shipment_id_expiration` varchar(64) DEFAULT NULL,
`shipment_id_number` varchar(64) DEFAULT NULL,
`shipment_id_type` varchar(64) DEFAULT NULL,
`shipment_delivery_date` varchar(64) DEFAULT NULL,
`shipment_delivery_time` varchar(64) DEFAULT NULL,
`shipment_comment` text DEFAULT NULL,
`billing_title` varchar(64) DEFAULT NULL,
`billing_firstname` varchar(64) DEFAULT NULL,
`billing_surname` varchar(64) DEFAULT NULL,
`billing_company` varchar(255) DEFAULT NULL,
`billing_address_full` varchar(255) DEFAULT NULL,
`billing_address_street` varchar(64) DEFAULT NULL,
`billing_address_number` varchar(32) DEFAULT NULL,
`billing_address_number_extension` varchar(32) DEFAULT NULL,
`billing_postalcode` varchar(64) DEFAULT NULL,
`billing_city` varchar(64) DEFAULT NULL,
`billing_country_iso` varchar(64) DEFAULT NULL,
`billing_phone` varchar(64) DEFAULT NULL,
`billing_email` varchar(64) DEFAULT NULL,
`language` varchar(32) DEFAULT NULL,
`order_products` TEXT,
`order_status` varchar(64) DEFAULT NULL,
`enabled` tinyint(1) DEFAULT NULL,
PRIMARY KEY (`id`));";
			parcelcheckout_database_execute($sql);



			$sql = "CREATE TABLE IF NOT EXISTS `" . $aSettings['db_prefix'] . "parcelcheckout_products` (
`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
`product_id` varchar(64) DEFAULT NULL,
`product_data` TEXT DEFAULT NULL,  
`exported` tinyint(0) DEFAULT NULL,
PRIMARY KEY (`id`));";
			parcelcheckout_database_execute($sql);


			// Add orders batch table
			$sql = "CREATE TABLE IF NOT EXISTS `" . $aSettings['db_prefix'] . "parcelcheckout_product_exports` (
`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
`batch_id` int(11) DEFAULT NULL,
`exported_products` TEXT DEFAULT NULL,
PRIMARY KEY (`id`));";
			parcelcheckout_database_execute($sql);

			
			
			
			
						
			self::setLog('Primary installation completed.', __FILE__, __LINE__);
		}


		// Verify server firewall
		public static function testFirewall($sSoftwareCode = false)
		{
			if($sSoftwareCode === false)
			{
				$sSoftwareCode = self::getSoftwareCode();
			}

			if(parcelcheckout_getDebugMode())
			{
				parcelcheckout_log('Testing firewall through httpRequest to www.ideal-checkout.nl', __FILE__, __LINE__);
			}

			$sFirewallCheck = @parcelcheckout_doHttpRequest('https://www.ideal-checkout.nl/ping.php', array('url' => parcelcheckout_getRootUrl(2), 'software' => $sSoftwareCode), true);

			if(parcelcheckout_getDebugMode())
			{
				parcelcheckout_log('Done testing firewall through httpRequest to www.ideal-checkout.nl, returned:' . $sFirewallCheck, __FILE__, __LINE__);
			}

			if(!empty($sFirewallCheck))
			{
				self::setLog('Firewall check completed, status: OK', __FILE__, __LINE__);
				return true;
			}
			else
			{
				self::setLog('Firewall check failed, status: NOK', __FILE__, __LINE__);
			}

			return false;
		}


		// Verify given FTP settings
		public static function testFtpSettings(&$aFormValues, &$aFormErrors)
		{
			if(!empty($aFormValues['ftp_host']) && !empty($aFormValues['ftp_user']) && !empty($aFormValues['ftp_pass']))
			{
				if(clsFtp::test($aFormValues['ftp_host'], $aFormValues['ftp_user'], $aFormValues['ftp_pass'], $aFormValues['ftp_port'], $aFormValues['ftp_passive']))
				{
					self::setLog('FTP check completed, status: OK', __FILE__, __LINE__);
					return true;
				}
				else
				{
					self::setLog('FTP check failed, status: NOK', __FILE__, __LINE__);
				}
			}

			return false;
		}


		// Verify given DB settings
		public static function testDatabaseSettings(&$aFormValues, &$aFormErrors = array())
		{
			if(!empty($aFormValues['db_host']) && !empty($aFormValues['db_user']) && !empty($aFormValues['db_name']) && !empty($aFormValues['db_type']))
			{
				if(strcasecmp($aFormValues['db_type'], 'mysqli') === 0)
				{
					if(parcelcheckout_getDebugMode())
					{
						parcelcheckout_log('Trying to connect to the database using mysqli with credentials:' . "\r\n" . $aFormValues['db_host'] . ' ' . $aFormValues['db_user'] . ' **password**', __FILE__, __LINE__);
					}
					
					$oDatabase = @mysqli_connect($aFormValues['db_host'], $aFormValues['db_user'], $aFormValues['db_pass']);
					
					if(parcelcheckout_getDebugMode())
					{
						parcelcheckout_log('Succesfully established connection to the database with mysqli', __FILE__, __LINE__);
					}

					if($oDatabase)
					{
						$aFormValues['db_success'] = parcelcheckout_getTranslation(false, 'install', 'Database host, user and password verified.');

						if(@mysqli_select_db($oDatabase, $aFormValues['db_name']))
						{							
							$aFormValues['db_success'] .= "\r\n" . parcelcheckout_getTranslation(false, 'install', 'Database name verified.');

							if(empty($aFormValues['db_prefix']))
							{
								self::setLog('Database check completed, status: OK', __FILE__, __LINE__);
								return true;
							}
							else
							{
								$aTables = array();
								$iPrefixFound = 0;
								$bPrefixUnderscore = (strpos($aFormValues['db_prefix'], '_') !== false);

								$sQuery = "SHOW TABLES FROM `" . $aFormValues['db_name'] . "`;";
								$oRecordset = mysqli_query($oDatabase, $sQuery);

								if($aHeader = mysqli_fetch_assoc($oRecordset))
								{
									foreach($aHeader as $k => $v)
									{										
										while($aRecord = mysqli_fetch_assoc($oRecordset))
										{
											$aTables[] = $aRecord[$k];

											if(strpos($aRecord[$k], $aFormValues['db_prefix']) === 0)
											{
												if(($bPrefixUnderscore === false) && ($iPrefixFound < 1) && (strpos($aRecord[$k], $aFormValues['db_prefix'] . '_') === 0))
												{
													self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
													self::setLog('Database prefix should end with an underscore.', __FILE__, __LINE__);

													$aFormValues['db_error'] = parcelcheckout_getTranslation(false, 'install', 'Database prefix should end with an underscore (Expecting prefix "{0}_" instead of "{0}").', array($aFormValues['db_prefix']));
													return false;
												}
												else
												{
													$iPrefixFound++;
												}
											}
										}

										break;
									}
								}

								if(sizeof($aTables) > 1)
								{
									usort($aTables, 'strcasecmp');
								}

								if($iPrefixFound > 2)
								{
									self::setLog('Database check completed, status: OK', __FILE__, __LINE__);

									$aFormValues['db_success'] .= "\r\n" . parcelcheckout_getTranslation(false, 'install', 'Database prefix is verified (Found "{0}" {1}x).', array($aFormValues['db_prefix'], $iPrefixFound));
									return true;
								}
								else
								{
									self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
									self::setLog('Database prefix is invalid.', __FILE__, __LINE__);

									$aFormValues['db_error'] = parcelcheckout_getTranslation(false, 'install', 'Database prefix is invalid (Found "{0}" {1}x).', array($aFormValues['db_prefix'], $iPrefixFound));
								}
							}
						}
						else
						{
							self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
							self::setLog('Database name is invalid.', __FILE__, __LINE__);

							$aFormValues['db_error'] = parcelcheckout_getTranslation(false, 'install', 'Database name is invalid.');
						}
					}
					else
					{
						self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
						self::setLog('Database host, user and/or password are invalid.', __FILE__, __LINE__);

						$aFormValues['db_error'] = parcelcheckout_getTranslation(false, 'install', 'Database host, user and/or password are invalid.');
					}
				}
				else // if(strcasecmp($aFormValues['db_type'], 'mysql') === 0)
				{

					if(parcelcheckout_getDebugMode())
					{
						parcelcheckout_log('Trying to connect to the database using mysql with credentials:' . "\r\n" . $aFormValues['db_host'] . ' ' . $aFormValues['db_user'] . ' **password**', __FILE__, __LINE__);
					}


					$oDatabase = @mysql_connect($aFormValues['db_host'], $aFormValues['db_user'], $aFormValues['db_pass'], true);

					if(parcelcheckout_getDebugMode())
					{
						parcelcheckout_log('Succesfully established connection to the database with mysql', __FILE__, __LINE__);
					}

					if($oDatabase)
					{
						$aFormValues['db_success'] = parcelcheckout_getTranslation(false, 'install', 'Database host, user and password are verified.');

						if(@mysql_select_db($aFormValues['db_name'], $oDatabase))
						{
							$aFormValues['db_success'] .= "\r\n" . parcelcheckout_getTranslation(false, 'install', 'Database name is verified.');

							if(empty($aFormValues['db_prefix']))
							{
								self::setLog('Database check completed, status: OK', __FILE__, __LINE__);
								return true;
							}
							else
							{
								$aTables = array();
								$iPrefixFound = 0;
								$bPrefixUnderscore = (strpos($aFormValues['db_prefix'], '_') !== false);

								$sQuery = "SHOW TABLES FROM `" . $aFormValues['db_name'] . "`;";
								$oRecordset = mysql_query($sQuery, $oDatabase);

								if($aHeader = mysql_fetch_assoc($oRecordset))
								{
									foreach($aHeader as $k => $v)
									{
										while($aRecord = mysql_fetch_assoc($oRecordset))
										{
											$aTables[] = $aRecord[$k];

											if(strpos($aRecord[$k], $aFormValues['db_prefix']) === 0)
											{
												if(($bPrefixUnderscore === false) && ($iPrefixFound < 1) && (strpos($aRecord[$k], $aFormValues['db_prefix'] . '_') === 0))
												{
													$aFormValues['db_error'] = parcelcheckout_getTranslation(false, 'install', 'Database prefix should end with an underscore (Expecting prefix "{0}_" instead of "{0}").', array($aFormValues['db_prefix']));
													return false;
												}
												else
												{
													$iPrefixFound++;
												}
											}
										}

										break;
									}
								}

								if(sizeof($aTables) > 1)
								{
									usort($aTables, 'strcasecmp');
								}

								if($iPrefixFound > 2)
								{
									self::setLog('Database check completed, status: OK', __FILE__, __LINE__);

									$aFormValues['db_success'] .= "\r\n" . parcelcheckout_getTranslation(false, 'install', 'Database prefix verified (Found "{0}" {1}x).', array($aFormValues['db_prefix'], $iPrefixFound));
									return true;
								}
								else
								{
									self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
									self::setLog('Database prefix is invalid.', __FILE__, __LINE__);

									$aFormValues['db_error'] = parcelcheckout_getTranslation(false, 'install', 'Database prefix invalid (Found "{0}" {1}x).', array($aFormValues['db_prefix'], $iPrefixFound));
								}
							}
						}
						else
						{
							self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
							self::setLog('Database name is invalid.', __FILE__, __LINE__);

							$aFormValues['db_error'] = parcelcheckout_getTranslation(false, 'install', 'Database name invalid.');
						}
					}
					else
					{
						self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
						self::setLog('Database host, user and/or password are invalid.', __FILE__, __LINE__);

						$aFormValues['db_error'] = parcelcheckout_getTranslation(false, 'install', 'Database host, user and/or password invalid.');
					}
				}
			}

			return false;
		}

		public static function setLog($sMessage, $sFile = false, $iLine = false)
		{
			parcelcheckout_log($sMessage, $sFile, $iLine, false);
		}



		// Does $sString starts with $sCompare?
		public static function stringStart($sString, $sCompare, $bCaseSensitive = false)
		{
			$sMatch = substr($sString, 0, strlen($sCompare));

			if($bCaseSensitive)
			{
				return (strcmp($sMatch, $sCompare) === 0);
			}
			else
			{
				return (strcasecmp($sMatch, $sCompare) === 0);
			}
		}



		// Retrieve a value from a configuration file
		public static function getFileValue($sFileData, $aRegularExpressions, $iIndex = 1)
		{
			$aMatches = array();

			if(!is_array($aRegularExpressions))
			{
				$aRegularExpressions = array($aRegularExpressions);
			}

			foreach($aRegularExpressions as $sRegex)
			{
				$aFiler = preg_match_all($sRegex, $sFileData, $aMatches);

				if(isset($aMatches[$iIndex][0]))
				{
					return $aMatches[$iIndex][0];
				}
			}

			return '';
		}



		// Write data to file.
		public static function setFile($sRelativePath, $sFileData = '', $aInstallSettings = false)
		{
			if(self::stringStart($sRelativePath, SOFTWARE_PATH))
			{
				$sRelativePath = substr($sRelativePath, strlen(SOFTWARE_PATH));
			}			
			
			$sLocalPath = SOFTWARE_PATH . $sRelativePath;

			if(!FTP_ACCESS_REQUIRED && @file_put_contents($sLocalPath, $sFileData))
			{
				self::setLog('LOCAL: Creating file: ' . $sLocalPath, __FILE__, __LINE__);

				@chmod($sLocalPath, 0666);
				return true;
			}
			else // Try FTP
			{
				$aInstallSettings = self::getInstallSettings($aInstallSettings);
				$oFtp = self::getFtpConnection($aInstallSettings);

				if($oFtp)
				{
					$sRemotePath = $aInstallSettings['ftp_path'] . $sRelativePath;

					self::setLog('REMOTE: Creating file: ' . $sRemotePath, __FILE__, __LINE__);

					if($oFtp->createFile($sRemotePath, $sFileData))
					{
						return true;
					}
				}
			}

			return false;
		}

		public static function deleteFile($sRelativePath, $aInstallSettings = false)
		{
			if(self::stringStart($sRelativePath, SOFTWARE_PATH))
			{
				$sRelativePath = substr($sRelativePath, strlen(SOFTWARE_PATH));
			}

			$sLocalPath = SOFTWARE_PATH . $sRelativePath;

			if(is_file($sLocalPath))
			{
				if(!FTP_ACCESS_REQUIRED && @unlink($sLocalPath))
				{
					self::setLog('LOCAL: Removing file: ' . $sLocalPath, __FILE__, __LINE__);
					return true;
				}
				else // Try FTP
				{
					$aInstallSettings = self::getInstallSettings($aInstallSettings);
					$oFtp = self::getFtpConnection($aInstallSettings);

					if($oFtp)
					{
						$sRemotePath = $aInstallSettings['ftp_path'] . $sRelativePath;

						self::setLog('REMOTE: Removing file: ' . $sRemotePath, __FILE__, __LINE__);

						if($oFtp->deleteFile($sRemotePath))
						{
							return true;
						}
					}
				}
			}

			return false;
		}

		public static function deleteFolder($sRelativePath, $aInstallSettings = false)
		{
			if(self::stringStart($sRelativePath, SOFTWARE_PATH))
			{
				$sRelativePath = substr($sRelativePath, strlen(SOFTWARE_PATH));
			}

			$sLocalPath = SOFTWARE_PATH . $sRelativePath;

			if(is_dir($sLocalPath))
			{
				if(!FTP_ACCESS_REQUIRED && self::_deleteFolder($sLocalPath))
				{
					self::setLog('LOCAL: Removing folder: ' . $sLocalPath, __FILE__, __LINE__);
					return true;
				}
				else // Try FTP
				{
					$aInstallSettings = self::getInstallSettings($aInstallSettings);
					$oFtp = self::getFtpConnection($aInstallSettings);

					if($oFtp)
					{
						$sRemotePath = $aInstallSettings['ftp_path'] . $sRelativePath;

						self::setLog('REMOTE: Removing folder: ' . $sRemotePath, __FILE__, __LINE__);

						if($oFtp->deleteFolder($sRemotePath))
						{
							return true;
						}
					}
				}
			}

			return false;
		}


		public static function chmodFolder($sRelativePath, $iChmod = 0777, $aInstallSettings = false)
		{
			if(self::stringStart($sRelativePath, SOFTWARE_PATH))
			{
				$sRelativePath = substr($sRelativePath, strlen(SOFTWARE_PATH));
			}

			$sLocalPath = SOFTWARE_PATH . $sRelativePath;

			if(is_dir($sLocalPath))
			{
				if(is_writable($sLocalPath))
				{
					return true;
				}
				elseif(!FTP_ACCESS_REQUIRED)
				{
					if(self::_chmodFolder($sLocalPath, $iChmod))
					{
						self::setLog('LOCAL: Setting chmod(0777) to ' . $sLocalPath, __FILE__, __LINE__);
						return true;
					}

					return false;
				}
				else // Try FTP
				{
					$aInstallSettings = self::getInstallSettings($aInstallSettings);
					$oFtp = self::getFtpConnection($aInstallSettings);

					if($oFtp)
					{
						$sRemotePath = $aInstallSettings['ftp_path'] . $sRelativePath;

						self::setLog('REMOTE: Setting chmod(0777) to ' . $sRemotePath, __FILE__, __LINE__);

						if($_REQUEST['FTP_CONNECTION']->setChmod($sRemotePath))
						{
							return true;
						}
					}
				}
			}
			elseif(is_file($sLocalPath))
			{
				if(is_writable($sLocalPath))
				{
					return true;
				}
				elseif(!FTP_ACCESS_REQUIRED)
				{
					if(@chmod($sLocalPath, $iChmod))
					{
						self::setLog('LOCAL: Setting chmod(0777) to ' . $sLocalPath, __FILE__, __LINE__);
						return true;
					}

					return false;
				}
				else // Try FTP
				{
					$aInstallSettings = self::getInstallSettings($aInstallSettings);
					$oFtp = self::getFtpConnection($aInstallSettings);

					if($oFtp)
					{
						$sRemotePath = $aInstallSettings['ftp_path'] . $sRelativePath;

						self::setLog('REMOTE: Setting chmod(0777) to ' . $sRemotePath, __FILE__, __LINE__);

						if($_REQUEST['FTP_CONNECTION']->setChmod($sRemotePath))
						{
							return true;
						}
					}
				}
			}

			return false;
		}



		public static function getFtpConnection($aInstallSettings = false)
		{
			if(empty($_REQUEST['FTP_CONNECTION']))
			{
				$aInstallSettings = self::getInstallSettings($aInstallSettings);

				if(empty($aInstallSettings['ftp_host']))
				{
					self::addLog('Cannot find any FTP settings in $aInstallSettings', __FILE__, __LINE__);
					self::addLog($aInstallSettings, __FILE__, __LINE__);
					return false;
				}

				self::addLog('Creating FTP connection to ' . $aInstallSettings['ftp_host'], __FILE__, __LINE__);

				require_once(PARCELCHECKOUT_PATH . '/install/includes/ftp.cls.php');

				$_REQUEST['FTP_CONNECTION'] = new clsFtp();
				$bFtpConnected = $_REQUEST['FTP_CONNECTION']->connect($aInstallSettings['ftp_host'], $aInstallSettings['ftp_user'], $aInstallSettings['ftp_pass'], $aInstallSettings['ftp_port'], !empty($aInstallSettings['ftp_passive']), true);

				if(!$bFtpConnected)
				{
					return false;
				}

				// Set root folder
				if(!empty($aInstallSettings['ftp_path']))
				{
					$_REQUEST['FTP_CONNECTION']->setRemotePath($aInstallSettings['ftp_path']);
				}
			}

			return $_REQUEST['FTP_CONNECTION'];
		}

		public static function getInstallSettings($aInstallSettings = false)
		{
			if($aInstallSettings === false)
			{
				return include(PARCELCHECKOUT_PATH . '/configuration/install.php');
			}

			return $aInstallSettings;
		}

		public static function addLog($sString, $sFile = false, $iLine = false)
		{
			parcelcheckout_log($sString, $sFile, $iLine, false);
		}


		public static function appendSlash($sString)
		{
			if(substr($sString, -1, 1) == '/')
			{
				return $sString;
			}

			return $sString . '/';
		}

		public static function prependSlash($sString)
		{
			if(substr($sString, 0, 1) == '/')
			{
				return $sString;
			}

			return '/' . $sString;
		}



		// Draw gateway input fields
		public static function drawFormFields($aSelectedGateway)
		{
			$sHtml = '';

			if($aSelectedGateway['code'] == 'ECS')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Retailer ID</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_retailer_id" type="text" value="" placeholder="119"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Retailer naam</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_retailer_name" type="text" value="" placeholder="im"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Locatie API key</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_location_api" type="text" value="" placeholder="Locatie service API key"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SFTP Host</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sftp_host" type="text" value="" placeholder="sftp.postnl.nl"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SFTP Username</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sftp_username" type="text" value="" placeholder="username"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SFTP keyfile</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sftp_keyfile" accept=".ppk" type="file" value="private.ppk"></td></tr>';
			}
			
			return $sHtml;
		}

		// Save carrier input data to configuration file
		public static function saveFormFields($aSelectedGateway)
		{
			$sData = '';
			$sData .= '<' . '?' . 'php' . LF;
			$sData .= LF;
			$sData .= TAB . '/*' . LF;
			$sData .= TAB . TAB . 'This plug-in was developed by Parcel Checkout.' . LF;
			$sData .= TAB . TAB . 'See www.parcel-checkout.nl for more information.' . LF;
			$sData .= LF;
			$sData .= TAB . TAB . 'This file was generated on ' . date('d-m-Y, H:i:s') . LF;
			$sData .= TAB . '*/' . LF;
			$sData .= LF;
			$sData .= LF;
		
			
			// Add carrier settings
			if($aSelectedGateway['code'] == 'ECS')
			{
				$sData .= TAB . '// Retailer ID' . LF;
				$sData .= TAB . '$aSettings[\'RETAILER_ID\'] = \'' . (empty($_POST['parcelcheckout_retailer_id']) ? '' : addslashes($_POST['parcelcheckout_retailer_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Retailer Name' . LF;
				$sData .= TAB . '$aSettings[\'RETAILER_NAME\'] = \'' . (empty($_POST['parcelcheckout_retailer_name']) ? '' : addslashes($_POST['parcelcheckout_retailer_name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Location API key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_location_api']) ? '' : addslashes($_POST['parcelcheckout_location_api'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// SFTP Host' . LF;
				$sData .= TAB . '$aSettings[\'SFTP_HOST\'] = \'' . (empty($_POST['parcelcheckout_sftp_host']) ? '' : addslashes($_POST['parcelcheckout_sftp_host'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// SFTP Username' . LF;
				$sData .= TAB . '$aSettings[\'SFTP_USER\'] = \'' . (empty($_POST['parcelcheckout_sftp_username']) ? '' : addslashes($_POST['parcelcheckout_sftp_username'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// SFTP PPK file (should be located in /parcelcheckout/keys/)' . LF;
				$sData .= TAB . '$aSettings[\'SFTP_KEY\'] = \'' . (empty($_FILES['parcelcheckout_sftp_keyfile']['name']) ? '' : addslashes($_FILES['parcelcheckout_sftp_keyfile']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'CARRIER_NAME\'] = \'Post NL\';' . LF;
				$sData .= TAB . '$aSettings[\'CARRIER_WEBSITE\'] = \'https://www.postnl.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'CARRIER_METHOD\'] = \'postnl\';' . LF;
				
			}

			$sData .= LF;
			$sData .= '?' . '>';
			
			
			$sFilePath = '/parcelcheckout/configuration/carrier.php';

			if(!PARCELCHECKOUT_INSTALL::setFile($sFilePath, $sData))
			{
				PARCELCHECKOUT_INSTALL::setLog('Cannot create file: ' . $sFilePath, __FILE__, __LINE__);
				return false;
			}

			if(!empty($_SERVER['SERVER_NAME']))
			{
				$sStoreHost = $_SERVER['SERVER_NAME'];
				$aStoreHost = explode('.', $sStoreHost);
				$iStoreHost = sizeof($aStoreHost);

				$sStoreCode = md5($_SERVER['SERVER_NAME']);
				$sFilePath = '/parcelcheckout/configuration/carrier.' . $sStoreCode . '.php';
		
				
				if(!PARCELCHECKOUT_INSTALL::setFile($sFilePath, $sData))
				{
					PARCELCHECKOUT_INSTALL::setLog('Cannot create file: ' . $sFilePath, __FILE__, __LINE__);
					return false;
				}

				if(($iStoreHost > 3) || ($iStoreHost < 2))
				{
					// $sStoreCode is not relevant for IP addresses or localhost.
				}
				elseif(strpos($sStoreHost, 'www.') === false) // No 'www.' found
				{
					$sStoreCode = md5('www.' . $_SERVER['SERVER_NAME']);
					$sFilePath = '/parcelcheckout/configuration/carrier.' . $sStoreCode . '.php';

					if(!PARCELCHECKOUT_INSTALL::setFile($sFilePath, $sData))
					{
						PARCELCHECKOUT_INSTALL::setLog('Cannot create file: ' . $sFilePath, __FILE__, __LINE__);
						return false;
					}
				}
				elseif(strpos($sStoreHost, 'www.') === 0) // Starts with 'www.'
				{
					$sStoreCode = md5(substr($_SERVER['SERVER_NAME'], 4));
					$sFilePath = '/parcelcheckout/configuration/carrier.' . $sStoreCode . '.php';

					if(!PARCELCHECKOUT_INSTALL::setFile($sFilePath, $sData))
					{
						PARCELCHECKOUT_INSTALL::setLog('Cannot create file: ' . $sFilePath, __FILE__, __LINE__);
						return false;
					}
				}
			}

			return true;
		}

		public static function _chmodFolder($sPath, $iChmod)
		{
			$aFiles = array_diff(scandir($sPath), array('.','..'));

			if(@chmod($sPath))
			{
				foreach($aFiles as $sFile)
				{
					if(is_dir($sPath . '/' . $sFile))
					{
						self::_chmodFolder($sPath . '/' . $sFile);
					}
					else
					{
						@chmod($sPath . '/' . $sFile);
					}
				}

				return true;
			}
			
			return false;
		}

		public static function _deleteFolder($sPath)
		{
			$aFiles = array_diff(scandir($sPath), array('.','..'));

			foreach($aFiles as $sFile)
			{
				if(is_dir($sPath . '/' . $sFile))
				{
					self::_deleteFolder($sPath . '/' . $sFile);
				}
				else
				{
					@unlink($sPath . '/' . $sFile);
				}
			}

			return @rmdir($sPath);
		}

		public static function output($sHtml, $sFormName = false, $iColspan = 1)
		{
			
			// <link href="http://www.parcel-checkout.nl/manuals/install/install.css" media="screen" rel="stylesheet" type="text/css">
			
			$sOutput = '
<!DOCTYPE HTML>
<html>
	<head>
		<title>Parcel Checkout - Installatie</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta http-equiv="content-language" content="nl-nl">
		
		<link href="http://www.graphxdemo.nl/woocommerce/parcelcheckout/install/css/install/install.css" media="screen" rel="stylesheet" type="text/css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	</head>
	<body><!--

			This Parcel Checkout script is developed by:

			Parcel Checkout

			Support & Information:
			W. http://www.parcel-checkout.nl
			E. support@parcel-checkout.nl
			T. +31522 746 060

		-->
';

			$sOutput .= '
			<header class="fixed">
				<div class="center">
					<div class="logo-wrapper"><img alt="PostNL" border="0" height="120" src="http://www.parcel-checkout.nl/manuals/install/postnl-logo.png"></div>
					<div class="slogan right">
						<div class="color">Fulfilment</div>
						<div class="text">WooCommerce Plugin</div>
					</div>
				</div>
				<div class="swirl"></div>
			</header>';
			
			
			
			
			if($sFormName)
			{
				if(strcasecmp($sFormName, 'install-step-3') === 0)
				{
					$sOutput .= '
		<form action="" id="' . htmlentities($sFormName) . '" method="post" name="' . htmlentities($sFormName) . '" enctype="multipart/form-data">';
					
				}
				else
				{
				
				$sOutput .= '
		<form action="" id="' . htmlentities($sFormName) . '" method="post" name="' . htmlentities($sFormName) . '">';
				}
		
			$sOutput .= '
			<input name="form" type="hidden" value="' . htmlentities($sFormName) . '">';
			}
			
			$sOutput .= '
			<table align="center" border="0" cellpadding="3" cellspacing="0" width="580">
				<tr>
					<td' . (($iColspan > 1) ? ' colspan="' . $iColspan . '"' : '') . '>&nbsp;</td>
				</tr>';

			if(substr(trim($sHtml), 0, 4) === '<tr>')
			{
				$sOutput .= $sHtml;
			}
			else
			{
				$sOutput .= '
				<tr>
					<td' . (($iColspan > 1) ? ' colspan="' . $iColspan . '"' : '') . '>' . $sHtml . '</td>
				</tr>';
			}

			$sOutput .= '
			</table>';

			if($sFormName)
			{
				$sOutput .= '
		</form>';
			}

			$sOutput .= '
	</body>
	<footer class="footer">
		<div class="text-wrapper">
			<span class="copy">&copy 2016 - Present</span><span>| All rights reserved</span><span>| Trademark of CodeBrain BV</span></div>
		</div>
	</footer>
</html>';

			echo $sOutput;
			exit;
		}
	}

?>