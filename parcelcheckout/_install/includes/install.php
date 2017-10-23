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




		// Find available provider codes (/idealcheckout/install/providers/[code].php)
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

			// $aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'certificates';
			// $aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'authorizedtransfer.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'creditcard.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'database.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'directebanking.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'ideal.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'klarnaaccount.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'klarnainvoice.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'maestro.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'manualtransfer.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'mastercard.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'mistercash.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'paypal.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'paysafecard.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'visa.php';
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'configuration' . DS . 'vpay.php';
			
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
			$aFilesAndFolders[] = PARCELCHECKOUT_PATH . DS . 'temp' . DS . 'issuers.cache';

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

			// Create #_idealcheckout table
			self::setLog('Creating database table #_idealcheckout', __FILE__, __LINE__);

			$sql = "CREATE TABLE IF NOT EXISTS `" . $aSettings['db_prefix'] . "parcelcheckout_transactions` (
`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
`order_id` VARCHAR(64) DEFAULT NULL, 
`order_code` VARCHAR(64) DEFAULT NULL, 
`order_params` TEXT DEFAULT NULL, 
`store_code` VARCHAR(64) DEFAULT NULL, 
`gateway_code` VARCHAR(64) DEFAULT NULL, 
`language_code` VARCHAR(2) DEFAULT NULL, 
`country_code` VARCHAR(2) DEFAULT NULL, 
`currency_code` VARCHAR(3) DEFAULT NULL, 
`transaction_id` VARCHAR(64) DEFAULT NULL, 
`transaction_code` VARCHAR(64) DEFAULT NULL, 
`transaction_params` TEXT DEFAULT NULL, 
`transaction_date` INT(11) UNSIGNED DEFAULT NULL, 
`transaction_amount` DECIMAL(10, 2) UNSIGNED DEFAULT NULL, 
`transaction_description` VARCHAR(100) DEFAULT NULL, 
`transaction_status` VARCHAR(16) DEFAULT NULL, 
`transaction_url` TEXT DEFAULT NULL, 
`transaction_payment_url` VARCHAR(255) DEFAULT NULL, 
`transaction_success_url` VARCHAR(255) DEFAULT NULL, 
`transaction_pending_url` VARCHAR(255) DEFAULT NULL, 
`transaction_failure_url` VARCHAR(255) DEFAULT NULL, 
`transaction_notify_url` VARCHAR(255) DEFAULT NULL, 
`transaction_log` TEXT DEFAULT NULL, 
PRIMARY KEY (`id`));";
			ic_database_execute($sql);
			
			
			
			
			$sql = "CREATE TABLE IF NOT EXISTS `" . $aSettings['db_prefix'] . "parcelcheckout_ssl` (
`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
`file_name` VARCHAR(255) DEFAULT NULL,
`file_content` TEXT DEFAULT NULL,
`enabled` TINYINT(1) DEFAULT NULL,
PRIMARY KEY (`id`));";
			ic_database_execute($sql);
			
					
			$sql = "SHOW COLUMNS FROM `" . $aSettings['db_prefix'] . "parcelcheckout_ssl` LIKE `enabled`";
			
			if(!$aColumn = ic_database_getRecord($sql))
			{
				$sql = "ALTER TABLE `" . $aSettings['db_prefix'] . "parcelcheckout_ssl` ADD COLUMN `enabled` TINYINT AFTER `file_content`";
				ic_database_execute($sql);
			}

			$sql = "CREATE TABLE IF NOT EXISTS `" . $aSettings['db_prefix'] . "parcelcheckout_settings` (
`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
`key_name` VARCHAR(255) DEFAULT NULL,
`value` VARCHAR(255) DEFAULT NULL,
PRIMARY KEY (`id`));";
			ic_database_execute($sql);
			
			$sql = "SHOW COLUMNS FROM `" . $aSettings['db_prefix'] . "parcelcheckout_settings` LIKE `synchronisation_datetime`";
			
			if(!$aColumn = ic_database_getRecord($sql))
			{
				$sql = "INSERT INTO `" . $aSettings['db_prefix'] . "parcelcheckout_settings` SET
`id` = NULL, 
`key_name` = 'synchronisation_datetime',
`value` = '" . parcelcheckout_escapeSql($sCurrentTime) . "'";
				ic_database_execute($sql);
				
			}
			else
			{
				$sql = "UPDATE `" . $aSettings['db_prefix'] . "parcelcheckout_settings` SET
`value` = '" . parcelcheckout_escapeSql($sCurrentTime) . "' WHERE (`key_name` = 'synchronisation_datetime') LIMIT 1";
				ic_database_execute($sql);
			}
						
			self::setLog('Primary installation completed.', __FILE__, __LINE__);
		}


		// Verify server firewall
		public static function testFirewall($sSoftwareCode = false)
		{
			if($sSoftwareCode === false)
			{
				$sSoftwareCode = self::getSoftwareCode();
			}

			if(ic_getDebugMode())
			{
				ic_log('Testing firewall through httpRequest to www.ideal-checkout.nl', __FILE__, __LINE__);
			}

			$sFirewallCheck = @ic_doHttpRequest('https://www.ideal-checkout.nl/ping.php', array('url' => ic_getRootUrl(2), 'software' => $sSoftwareCode), true);

			if(ic_getDebugMode())
			{
				ic_log('Done testing firewall through httpRequest to www.ideal-checkout.nl, returned:' . $sFirewallCheck, __FILE__, __LINE__);
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
					if(ic_getDebugMode())
					{
						ic_log('Trying to connect to the database using mysqli with credentials:' . "\r\n" . $aFormValues['db_host'] . ' ' . $aFormValues['db_user'] . ' **password**', __FILE__, __LINE__);
					}

					$oDatabase = @mysqli_connect($aFormValues['db_host'], $aFormValues['db_user'], $aFormValues['db_pass']);

					if(ic_getDebugMode())
					{
						ic_log('Succesfully established connection to the database with mysqli', __FILE__, __LINE__);
					}

					if($oDatabase)
					{
						$aFormValues['db_success'] = ic_getTranslation(false, 'install', 'Database host, user and password verified.');

						if(@mysqli_select_db($oDatabase, $aFormValues['db_name']))
						{
							$aFormValues['db_success'] .= "\r\n" . ic_getTranslation(false, 'install', 'Database name verified.');

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

													$aFormValues['db_error'] = ic_getTranslation(false, 'install', 'Database prefix should end with an underscore (Expecting prefix "{0}_" instead of "{0}").', array($aFormValues['db_prefix']));
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

									$aFormValues['db_success'] .= "\r\n" . ic_getTranslation(false, 'install', 'Database prefix is verified (Found "{0}" {1}x).', array($aFormValues['db_prefix'], $iPrefixFound));
									return true;
								}
								else
								{
									self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
									self::setLog('Database prefix is invalid.', __FILE__, __LINE__);

									$aFormValues['db_error'] = ic_getTranslation(false, 'install', 'Database prefix is invalid (Found "{0}" {1}x).', array($aFormValues['db_prefix'], $iPrefixFound));
								}
							}
						}
						else
						{
							self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
							self::setLog('Database name is invalid.', __FILE__, __LINE__);

							$aFormValues['db_error'] = ic_getTranslation(false, 'install', 'Database name is invalid.');
						}
					}
					else
					{
						self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
						self::setLog('Database host, user and/or password are invalid.', __FILE__, __LINE__);

						$aFormValues['db_error'] = ic_getTranslation(false, 'install', 'Database host, user and/or password are invalid.');
					}
				}
				else // if(strcasecmp($aFormValues['db_type'], 'mysql') === 0)
				{

					if(ic_getDebugMode())
					{
						ic_log('Trying to connect to the database using mysql with credentials:' . "\r\n" . $aFormValues['db_host'] . ' ' . $aFormValues['db_user'] . ' **password**', __FILE__, __LINE__);
					}


					$oDatabase = @mysql_connect($aFormValues['db_host'], $aFormValues['db_user'], $aFormValues['db_pass'], true);

					if(ic_getDebugMode())
					{
						ic_log('Succesfully established connection to the database with mysql', __FILE__, __LINE__);
					}

					if($oDatabase)
					{
						$aFormValues['db_success'] = ic_getTranslation(false, 'install', 'Database host, user and password are verified.');

						if(@mysql_select_db($aFormValues['db_name'], $oDatabase))
						{
							$aFormValues['db_success'] .= "\r\n" . ic_getTranslation(false, 'install', 'Database name is verified.');

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
													$aFormValues['db_error'] = ic_getTranslation(false, 'install', 'Database prefix should end with an underscore (Expecting prefix "{0}_" instead of "{0}").', array($aFormValues['db_prefix']));
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

									$aFormValues['db_success'] .= "\r\n" . ic_getTranslation(false, 'install', 'Database prefix verified (Found "{0}" {1}x).', array($aFormValues['db_prefix'], $iPrefixFound));
									return true;
								}
								else
								{
									self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
									self::setLog('Database prefix is invalid.', __FILE__, __LINE__);

									$aFormValues['db_error'] = ic_getTranslation(false, 'install', 'Database prefix invalid (Found "{0}" {1}x).', array($aFormValues['db_prefix'], $iPrefixFound));
								}
							}
						}
						else
						{
							self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
							self::setLog('Database name is invalid.', __FILE__, __LINE__);

							$aFormValues['db_error'] = ic_getTranslation(false, 'install', 'Database name invalid.');
						}
					}
					else
					{
						self::setLog('Database check failed, status: NOK', __FILE__, __LINE__);
						self::setLog('Database host, user and/or password are invalid.', __FILE__, __LINE__);

						$aFormValues['db_error'] = ic_getTranslation(false, 'install', 'Database host, user and/or password invalid.');
					}
				}
			}

			return false;
		}

		public static function setLog($sMessage, $sFile = false, $iLine = false)
		{
			ic_log($sMessage, $sFile, $iLine, false);
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
			ic_log($sString, $sFile, $iLine, false);
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

			if($aSelectedGateway['code'] == 'ABN Amro - iDEAL Easy')
			{
				$sHtml .= '<tr class="hide-c"><td><b>PSP ID</b> <em>*</em> (Deze ontvangt u van de ABN Amro)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_psp_id" type="text" value="TESTiDEALEASY"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Easy (Beveiligd)')
			{
				$sHtml .= '<tr class="hide-c"><td><b>PSP ID</b> <em>*</em> (Deze ontvangt u van de ABN Amro)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_psp_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SHA-1-IN Key</b> <em>*</em> (Deze moet u laten instellen door de ABN Amro e-commerce helpdesk)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sha1_in" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Hosted')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze vindt u op uw TEST en LIVE dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sub ID</b> <em>*</em> (Deze vindt u op uw TEST en LIVE dashboard, standaard "0")</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sub_id" type="text" value="0"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Hash Key</b> <em>*</em> (Deze moet u zelf instellen in uw TEST en LIVE dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Integrated')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze vindt u op uw TEST en LIVE dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sub ID</b> <em>*</em> (Deze vindt u op uw TEST en LIVE dashboard, standaard "0")</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sub_id" type="text" value="0"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key Pass</b> <em>*</em> (Wachtwoord waarmee uw private key file is gegenereerd)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_pass" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key File</b> <em>*</em> (Bestandsnaam van uw private key file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_file" type="file" accept=".key" value="private.key"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Certificate File</b> <em>*</em> (Bestandsnaam van uw private certificate file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_certificate_file" type="file" accept=".cer" value="private.cer"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Internet Kassa')
			{
				$sHtml .= '<tr class="hide-c"><td><b>PSP ID</b> <em>*</em> (Deze ontvangt u van de ABN Amro/Ogone)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_psp_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SHA-1-IN Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sha1_in" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SHA-1-OUT Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sha1_out" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Only')
			{
				$sHtml .= '<tr class="hide-c"><td><b>PSP ID</b> <em>*</em> (Deze ontvangt u van de ABN Amro/Ogone)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_psp_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SHA-1-IN Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sha1_in" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SHA-1-OUT Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sha1_out" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Zelfbouw')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze vindt u op uw TEST en LIVE dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sub ID</b> <em>*</em> (Deze vindt u op uw TEST en LIVE dashboard, standaard "0")</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sub_id" type="text" value="0"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key Pass</b> <em>*</em> (Wachtwoord waarmee uw private key file is gegenereerd)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_pass" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key File</b> <em>*</em> (Bestandsnaam van uw private key file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_file" type="file" accept=".key" value="private.key"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Certificate File</b> <em>*</em> (Bestandsnaam van uw private certificate file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_certificate_file" type="file" accept=".cer" value="private.cer"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Easy iDEAL - iDEAL')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Merchant Key</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Merchant Secret</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Fortis Bank - iDEAL Hosted')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze ontvangt u van de Fortis Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sub ID</b> <em>*</em> (Deze ontvangt u van de Fortis Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sub_id" type="text" value="0"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Hash Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Fortis Bank - iDEAL Integrated')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze ontvangt u van de Fortis Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sub ID</b> <em>*</em> (Deze ontvangt u van de Fortis Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sub_id" type="text" value="0"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key Pass</b> <em>*</em> (Wachtwoord waarmee uw private key file is gegenereerd)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_pass" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key File</b> <em>*</em> (Bestandsnaam van uw private key file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_file" type="file" accept=".key" value="private.key"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Certificate File</b> <em>*</em> (Bestandsnaam van uw private certificate file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_certificate_file" type="file" accept=".cer" value="private.cer"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Friesland Bank - iDEAL Zakelijk')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze ontvangt u van de Friesland Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sub ID</b> <em>*</em> (Deze ontvangt u van de Friesland Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sub_id" type="text" value="0"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Hash Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Friesland Bank - iDEAL Zakelijk Plus')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze ontvangt u van de Friesland Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sub ID</b> <em>*</em> (Deze ontvangt u van de Friesland Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sub_id" type="text" value="0"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key Pass</b> <em>*</em> (Wachtwoord waarmee uw private key file is gegenereerd)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_pass" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key File</b> <em>*</em> (Bestandsnaam van uw private key file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_file" type="file" accept=".key" value="private.key"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Certificate File</b> <em>*</em> (Bestandsnaam van uw private certificate file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_certificate_file" type="file" accept=".cer" value="private.cer"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Creditcard')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Direct E-Banking')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Authorized Transfer')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Manual Transfer')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - iDEAL')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - iDEAL Advanced/Integrated/Professional/Zelfbouw')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Klarnaaccount')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Klarnainvoice')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Maestro')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Mastercard')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - MisterCash')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - PayPal')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - PaySafeCard')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Visa')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - VPAY')
			{
				$sHtml .= '<tr class="hide-c"><td>Geen extra instellingen nodig voor deze test omgeving.</td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'ING Bank - iDEAL Advanced')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze ontvangt u van de ING Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sub ID</b> <em>*</em> (Deze ontvangt u van de ING Bank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sub_id" type="text" value="0"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key Pass</b> <em>*</em> (Wachtwoord waarmee uw private key file is gegenereerd)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_pass" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key File</b> <em>*</em> (Bestandsnaam van uw private key file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_file" type="file" accept=".key" value="private.key"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Certificate File</b> <em>*</em> (Bestandsnaam van uw private certificate file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_certificate_file" type="file" accept=".cer" value="private.cer"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'ING Bank - iDEAL Internet Kassa')
			{
				$sHtml .= '<tr class="hide-c"><td><b>PSP ID</b> <em>*</em> (Deze ontvangt u van de ING Bank/Ogone)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_psp_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SHA-1-IN Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sha1_in" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SHA-1-OUT Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sha1_out" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Mollie - iDEAL (oud)')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Partner ID</b> <em>*</em> (Deze ontvangt u van Mollie)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_partner_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Profiel ID/KEY</b> <em>*</em> (Deze ontvangt u van Mollie. Let op: Dit is NIET uw wachtwoord, maar een uniek ID om verschillende websites/webshops via 1 Mollie account te kunnen beheren. Maakt u geen gebruik van verschillende profielen, dan kunt u dit veld leeg laten)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_profile_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Mollie - Creditcard')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Mollie Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Mollie - Direct E-Banking/Sofort')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Mollie Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Mollie - Handmatig overboeken')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Mollie Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Mollie - iDEAL')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Mollie Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Mollie - Mistercash')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Mollie Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Mollie - PayPal')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Mollie Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Mollie - PaySafeCard')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Mollie Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - iDEAL')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Profile UID</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_profile_uid" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - Creditcard')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Profile UID</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_profile_uid" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - Handmatig Overboeken')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Profile UID</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_profile_uid" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - MisterCash')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Profile UID</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_profile_uid" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - Paypal')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Profile UID</b> <em>*</em> (Deze vindt u op uw Online Betaalplatform Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_profile_uid" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - iDEAL')
			{
				$sHtml .= '<tr class="hide-c"><td><p><b>Webshop ID</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_webshop_id" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Encryption Password</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_encryption_password" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Test Mode</b> (Werk met de TEST of LIVE omgeving)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><select name="parcelcheckout_test_mode"><option value="true">Ja (Test omgeving)</option><option value="false">Nee (Live omgeving)</option></select></p></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Creditcard')
			{
				$sHtml .= '<tr class="hide-c"><td><p><b>Webshop ID</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_webshop_id" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Encryption Password</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_encryption_password" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Test Mode</b> (Werk met de TEST of LIVE omgeving)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><select name="parcelcheckout_test_mode"><option value="true">Ja (Test omgeving)</option><option value="false">Nee (Live omgeving)</option></select></p></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - MisterCash')
			{
				$sHtml .= '<tr class="hide-c"><td><p><b>Webshop ID</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_webshop_id" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Encryption Password</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_encryption_password" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Test Mode</b> (Werk met de TEST of LIVE omgeving)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><select name="parcelcheckout_test_mode"><option value="true">Ja (Test omgeving)</option><option value="false">Nee (Live omgeving)</option></select></p></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Direct E-Banking/Sofort')
			{
				$sHtml .= '<tr class="hide-c"><td><p><b>Webshop ID</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_webshop_id" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Encryption Password</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_encryption_password" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Test Mode</b> (Werk met de TEST of LIVE omgeving)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><select name="parcelcheckout_test_mode"><option value="true">Ja (Test omgeving)</option><option value="false">Nee (Live omgeving)</option></select></p></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Klarnaaccount')
			{
				$sHtml .= '<tr class="hide-c"><td><p><b>Webshop ID</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_webshop_id" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Encryption Password</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_encryption_password" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Test Mode</b> (Werk met de TEST of LIVE omgeving)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><select name="parcelcheckout_test_mode"><option value="true">Ja (Test omgeving)</option><option value="false">Nee (Live omgeving)</option></select></p></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Klarnainvoice')
			{
				$sHtml .= '<tr class="hide-c"><td><p><b>Webshop ID</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_webshop_id" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Encryption Password</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_encryption_password" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Test Mode</b> (Werk met de TEST of LIVE omgeving)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><select name="parcelcheckout_test_mode"><option value="true">Ja (Test omgeving)</option><option value="false">Nee (Live omgeving)</option></select></p></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Paypal')
			{
				$sHtml .= '<tr class="hide-c"><td><p><b>Webshop ID</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_webshop_id" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Encryption Password</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_encryption_password" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Test Mode</b> (Werk met de TEST of LIVE omgeving)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><select name="parcelcheckout_test_mode"><option value="true">Ja (Test omgeving)</option><option value="false">Nee (Live omgeving)</option></select></p></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Sepa')
			{
				$sHtml .= '<tr class="hide-c"><td><p><b>Webshop ID</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_webshop_id" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Encryption Password</b> (Deze vindt u op het PayCheckout dashboard.)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><input class="text" type="text" name="parcelcheckout_encryption_password" value=""></p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><b>Test Mode</b> (Werk met de TEST of LIVE omgeving)</p></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><p><select name="parcelcheckout_test_mode"><option value="true">Ja (Test omgeving)</option><option value="false">Nee (Live omgeving)</option></select></p></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Postcode iDEAL - iDEAL Professional')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze vind je terug op het dashboard van Postcode iDEAL)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key Pass</b> <em>*</em> (Wachtwoord waarmee uw private key file is gegenereerd)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_pass" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key File</b> <em>*</em> (Uw private key file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_file" type="file" accept=".key" value="private.key"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Certificate File</b> <em>*</em> (Uw private certificate file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_certificate_file" accept=".cer" type="file" value="private.cer"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Qantani - Creditcard')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Qantani Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Qantani - Direct E-Banking')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Qantani Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Qantani - iDEAL')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Qantani Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Qantani - PayPal')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Qantani Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Qantani - Mistercash')
			{
				$sHtml .= '<tr class="hide-c"><td><b>API Key</b> <em>*</em> (Deze vindt u op uw Qantani Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_api_key" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Rabobank - iDEAL Internet Kassa')
			{
				$sHtml .= '<tr class="hide-c"><td><b>PSP ID</b> <em>*</em> (Deze ontvangt u van de Rabobank/Ogone)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_psp_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SHA-1-IN Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sha1_in" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>SHA-1-OUT Key</b> <em>*</em> (Deze moet u zelf instellen in uw iDEAL Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sha1_out" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Rabobank - iDEAL Professional')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em> (Deze ontvangt u van de Rabobank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sub ID</b> <em>*</em> (Deze ontvangt u van de Rabobank)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_sub_id" type="text" value="0"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key Pass</b> <em>*</em> (Wachtwoord waarmee uw private key file is gegenereerd)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_pass" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Key File</b> <em>*</em> (Bestandsnaam van uw private key file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_key_file" type="file" accept=".key" value="private.key"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Private Certificate File</b> <em>*</em> (Bestandsnaam van uw private certificate file)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_private_certificate_file" type="file" accept=".cer" value="private.cer"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - Creditcard')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Winkel-ID</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value="002020000000001"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em><br>Gebruik de TEST of LIVE omgeving.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Geheime Sleutel</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value="002020000000001_KEY1"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sleutel versie/nummer</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key_version" type="text" value="1"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - iDEAL')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Winkel-ID</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value="002020000000001"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em><br>Gebruik de TEST of LIVE omgeving.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Geheime Sleutel</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value="002020000000001_KEY1"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sleutel versie/nummer</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key_version" type="text" value="1"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - Maestro')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Winkel-ID</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value="002020000000001"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em><br>Gebruik de TEST of LIVE omgeving.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Geheime Sleutel</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value="002020000000001_KEY1"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sleutel versie/nummer</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key_version" type="text" value="1"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - Mastercard')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Winkel-ID</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value="002020000000001"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em><br>Gebruik de TEST of LIVE omgeving.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Geheime Sleutel</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value="002020000000001_KEY1"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sleutel versie/nummer</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key_version" type="text" value="1"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - MisterCash')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Winkel-ID</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Geheime Sleutel</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sleutel versie/nummer</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key_version" type="text" value="1"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><i>Testbetalingen zijn op dit moment nog niet mogelijk met MisterCash. Om testbetalingen<br>uit te voeren kunt u gebruik maken van o.a. iDEAL, MiniTix, Maestro, Visa en Mastercard.</i></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - Visa')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Winkel-ID</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value="002020000000001"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em><br>Gebruik de TEST of LIVE omgeving.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Geheime Sleutel</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value="002020000000001_KEY1"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sleutel versie/nummer</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key_version" type="text" value="1"></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - VPAY')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Winkel-ID</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Geheime Sleutel</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Sleutel versie/nummer</b> <em>*</em><br>Deze vindt u op de downloadsite van de <a href="https://download.omnikassa.rabobank.nl/" target="_blank" title="https://download.omnikassa.rabobank.nl/">Rabo OmniKassa</a>.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_hash_key_version" type="text" value="1"></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><i>Testbetalingen zijn op dit moment nog niet mogelijk met VPAY. Om testbetalingen<br>uit te voeren kunt u gebruik maken van o.a. iDEAL, MiniTix, Maestro, Visa en Mastercard.</i></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Sisow - Direct E-Banking')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Merchant KEY</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Shop ID</b> <em>*</em> (Deze ontvangt u van Sisow)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_shop_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Sisow - iDEAL')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Merchant KEY</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Shop ID</b> <em>*</em> (Deze ontvangt u van Sisow)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_shop_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Sisow - MisterCash')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Merchant KEY</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Shop ID</b> <em>*</em> (Deze ontvangt u van Sisow)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_shop_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'Sisow - PayPal')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Merchant ID</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Merchant KEY</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_merchant_key" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Shop ID</b> <em>*</em> (Deze ontvangt u van Sisow)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_shop_id" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Test Mode</b> <em>*</em> (Gebruik de TEST of LIVE omgeving)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><select name="parcelcheckout_test_mode"><option value="true">TEST omgeving gebruiken</option><option value="false">LIVE omgeving gebruiken</option></select></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - Creditcard')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Layout Code</b> <em>*</em> (Deze vindt u terug in uw TargetPay Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_layout_code" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - Direct E-Banking')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Layout Code</b> <em>*</em> (Deze vindt u terug in uw TargetPay Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_layout_code" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - iDEAL')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Layout Code</b> <em>*</em> (Deze vindt u terug in uw TargetPay Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_layout_code" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - MisterCash')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Layout Code</b> <em>*</em> (Deze vindt u terug in uw TargetPay Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_layout_code" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - PaySafeCard')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Layout Code</b> <em>*</em> (Deze vindt u terug in uw TargetPay Dashboard)</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_layout_code" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayDutch - Direct E-Banking')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Gebruikersnaam van uw PayDutch account</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_username" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Wachtwoord van uw PayDutch account</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_password" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>"ClientName" van uw PayDutch account</b> <em>*</em><br>(Deze moet u zelf instellen in uw PayDutch account bij "Technical Settings..")</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_callback_username" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>"ClientPassword" van uw PayDutch account</b> <em>*</em><br>(Deze moet u zelf instellen in uw PayDutch account bij "Technical Settings..")</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_callback_password" type="text" value=""></td></tr>';
			}
			elseif($aSelectedGateway['code'] == 'PayDutch - WeDeal')
			{
				$sHtml .= '<tr class="hide-c"><td><b>Gebruikersnaam van uw PayDutch account</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_username" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Wachtwoord van uw PayDutch account</b> <em>*</em></td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_password" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>"ClientName" van uw PayDutch account</b> <em>*</em><br>(Deze moet u zelf instellen in uw PayDutch account bij "Technical Settings..")</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_callback_username" type="text" value=""></td></tr>';
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>"ClientPassword" van uw PayDutch account</b> <em>*</em><br>(Deze moet u zelf instellen in uw PayDutch account bij "Technical Settings..")</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_callback_password" type="text" value=""></td></tr>';
			}
			else
			{
				$sHtml .= '<tr class="hide-c"><td>Unknown gateway: ' . htmlentities($aSelectedGateway['code']) . '</td></tr>';
			}

			if(!in_array($aSelectedGateway['code'], array('ABN Amro - iDEAL Easy', 'ABN Amro - iDEAL Easy (Beveiligd)')))
			{
				$sHtml .= '<tr class="hide-c"><td>&nbsp;</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><b>Webmaster E-mail</b><br>Laat de plug-in, bij status updates, een e-mail verzenden naar het onderstaande adres.<br>U kunt meerdere adressen opgegeven door deze te scheiden met een komma.</td></tr>';
				$sHtml .= '<tr class="hide-c"><td><input name="parcelcheckout_email_to" type="text" value="" placeholder="mijn@email.adres"></td></tr>';
			}

			return $sHtml;
		}

		// Save gateway input data to configuration file
		public static function saveFormFields($aSelectedGateway)
		{
			$sData = '';
			$sData .= '<' . '?' . 'php' . LF;
			$sData .= LF;
			$sData .= TAB . '/*' . LF;
			$sData .= TAB . TAB . 'This plug-in was developed by iDEAL Checkout.' . LF;
			$sData .= TAB . TAB . 'See www.ideal-checkout.nl for more information.' . LF;
			$sData .= LF;
			$sData .= TAB . TAB . 'This file was generated on ' . date('d-m-Y, H:i:s') . LF;
			$sData .= TAB . '*/' . LF;
			$sData .= LF;
			$sData .= LF;
		
			
			// Add gateway settings
			if($aSelectedGateway['code'] == 'ABN Amro - iDEAL Easy')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_psp_id']) ? '' : addslashes($_POST['parcelcheckout_psp_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'ABN AMRO - iDEAL Easy\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.abnamro.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-easy\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Easy (Beveiligd)')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'PSP_ID\'] = \'' . (empty($_POST['parcelcheckout_psp_id']) ? '' : addslashes($_POST['parcelcheckout_psp_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate/validate hashes' . LF;
				$sData .= TAB . '$aSettings[\'SHA_1_IN\'] = \'' . (empty($_POST['parcelcheckout_sha1_in']) ? '' : addslashes($_POST['parcelcheckout_sha1_in'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'ABN AMRO - iDEAL Easy (Beveiligd)\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.abnamro.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-easy-secure\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Hosted')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'ABN Amro - iDEAL Hosted\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.abnamro.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-lite\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Integrated')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate private key file' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_PASS\'] = \'' . (empty($_POST['parcelcheckout_private_key_pass']) ? '' : addslashes($_POST['parcelcheckout_private_key_pass'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-KEY-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_key_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_key_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-CERTIFICATE-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_CERTIFICATE_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_certificate_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_certificate_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'ABN Amro - iDEAL Integrated\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.abnamro.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-professional-v3\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Internet Kassa')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'PSP_ID\'] = \'' . (empty($_POST['parcelcheckout_psp_id']) ? '' : addslashes($_POST['parcelcheckout_psp_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate/validate hashes' . LF;
				$sData .= TAB . '$aSettings[\'SHA_1_IN\'] = \'' . (empty($_POST['parcelcheckout_sha1_in']) ? '' : addslashes($_POST['parcelcheckout_sha1_in'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'SHA_1_OUT\'] = \'' . (empty($_POST['parcelcheckout_sha1_out']) ? '' : addslashes($_POST['parcelcheckout_sha1_out'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'ABN Amro - iDEAL Internet Kassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.abnamro.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-internetkassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Only')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'PSP_ID\'] = \'' . (empty($_POST['parcelcheckout_psp_id']) ? '' : addslashes($_POST['parcelcheckout_psp_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate/validate hashes' . LF;
				$sData .= TAB . '$aSettings[\'SHA_1_IN\'] = \'' . (empty($_POST['parcelcheckout_sha1_in']) ? '' : addslashes($_POST['parcelcheckout_sha1_in'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'SHA_1_OUT\'] = \'' . (empty($_POST['parcelcheckout_sha1_out']) ? '' : addslashes($_POST['parcelcheckout_sha1_out'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'ABN Amro - iDEAL Only\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.abnamro.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-internetkassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'ABN Amro - iDEAL Zelfbouw')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate private key file' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_PASS\'] = \'' . (empty($_POST['parcelcheckout_private_key_pass']) ? '' : addslashes($_POST['parcelcheckout_private_key_pass'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-KEY-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_key_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_key_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-CERTIFICATE-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_CERTIFICATE_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_certificate_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_certificate_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'ABN AMRO - iDEAL Zelfbouw\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.abnamro.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-professional-v3\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Easy iDEAL - iDEAL')
			{
				$sData .= TAB . '// Your Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your Merchant Key' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_KEY\'] = \'' . (empty($_POST['parcelcheckout_merchant_key']) ? '' : addslashes($_POST['parcelcheckout_merchant_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your Merchant Secret Hash Key' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_SECRET\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Easy iDEAL - iDEAL\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.easy-ideal.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-easyideal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Fortis Bank - iDEAL Hosted')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Fortis Bank - iDEAL Hosted\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.fortisbank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-lite\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Fortis Bank - iDEAL Integrated')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate private key file' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_PASS\'] = \'' . (empty($_POST['parcelcheckout_private_key_pass']) ? '' : addslashes($_POST['parcelcheckout_private_key_pass'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-KEY-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_key_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_key_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-CERTIFICATE-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_CERTIFICATE_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_certificate_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_certificate_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Fortis Bank - iDEAL Integrated\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.fortisbank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-professional-v3\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Friesland Bank - iDEAL Zakelijk')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Friesland Bank - iDEAL Zakelijk\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.frieslandbank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-lite\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Friesland Bank - iDEAL Zakelijk Plus')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate private key file' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_PASS\'] = \'' . (empty($_POST['parcelcheckout_private_key_pass']) ? '' : addslashes($_POST['parcelcheckout_private_key_pass'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-KEY-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_key_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_key_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-CERTIFICATE-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_CERTIFICATE_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_certificate_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_certificate_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Friesland Bank - iDEAL Zakelijk Plus\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.frieslandbank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-professional-v3\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Credit Card')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Credit Card\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'creditcard-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Direct E-Banking')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Direct E-Banking\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'directebanking-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - AfterPay')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - AfterPay\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'afterpay-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Carte Bleue')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Carte Bleue\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'cartebleue-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - eBon')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - eBon\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ebon-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Authorized Transfer')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Eenmalige machtiging\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'authorizedtransfer-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - FasterPay')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - FasterPay\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'fasterpay-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Klarnaaccount')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Klarnaaccount\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'klarnaaccount-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Klarnainvoice')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Klarnainvoice\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'klarnainvoice-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - GiroPay')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - GiroPay\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'giropay-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Manual Transfer')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Handmatige overboeking\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'manualtransfer-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - iDEAL')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - iDEAL\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - iDEAL Advanced/Integrated/Professional/Zelfbouw')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'123456789\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'0\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = true;' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate private key file' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_PASS\'] = \'Password\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-KEY-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_FILE\'] = \'private.key\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-CERTIFICATE-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_CERTIFICATE_FILE\'] = \'private.cer\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Checkout - iDEAL Simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-checkout.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-professional-v3\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Maestro')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Maestro\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'maestro-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Mastercard')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Mastercard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mastercard-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - MiniTix')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - MiniTix\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'minitix-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - MisterCash')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - MisterCash\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mistercash-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - PayPal')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - PayPal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'paypal-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - PaySafeCard')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - PaySafeCard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'paysafecard-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - PostePay')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - PostePay\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'postepay-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - Visa')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - Visa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'visa-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - VPAY')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - VPAY\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'vpay-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'iDEAL Simulator - WebshopGiftCard')
			{
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'iDEAL Simulator - WebshopGiftCard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ideal-simulator.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'webshopgiftcard-simulator\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'ING Bank - iDEAL Advanced')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate private key file' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_PASS\'] = \'' . (empty($_POST['parcelcheckout_private_key_pass']) ? '' : addslashes($_POST['parcelcheckout_private_key_pass'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-KEY-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_key_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_key_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-CERTIFICATE-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_CERTIFICATE_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_certificate_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_certificate_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'ING Bank - iDEAL Advanced\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ingbank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-professional-v3\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'ING Bank - iDEAL Internet Kassa')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'PSP_ID\'] = \'' . (empty($_POST['parcelcheckout_psp_id']) ? '' : addslashes($_POST['parcelcheckout_psp_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate/validate hashes' . LF;
				$sData .= TAB . '$aSettings[\'SHA_1_IN\'] = \'' . (empty($_POST['parcelcheckout_sha1_in']) ? '' : addslashes($_POST['parcelcheckout_sha1_in'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'SHA_1_OUT\'] = \'' . (empty($_POST['parcelcheckout_sha1_out']) ? '' : addslashes($_POST['parcelcheckout_sha1_out'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'ING Bank - iDEAL Internet Kassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.ingbank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-internetkassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Mollie - Creditcard')
			{
				$sData .= TAB . '// Mollie API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Mollie - Creditcard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.mollie.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'creditcard-mollie\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Mollie - Direct E-Banking/Sofort')
			{
				$sData .= TAB . '// Mollie API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Mollie - Direct E-Banking\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.mollie.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'directebanking-mollie\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Mollie - Handmatig overboeken')
			{
				$sData .= TAB . '// Mollie API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Mollie - Handmatig overboeken\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.mollie.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'manualtransfer-mollie\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Mollie - iDEAL')
			{
				$sData .= TAB . '// Mollie API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Mollie - iDEAL\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.mollie.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-mollie\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Mollie - Mistercash')
			{
				$sData .= TAB . '// Mollie API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Mollie - Mistercash\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.mollie.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mistercash-mollie\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Mollie - PayPal')
			{
				$sData .= TAB . '// Mollie API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Mollie - PayPal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.mollie.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'paypal-mollie\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Mollie - PaySafeCard')
			{
				$sData .= TAB . '// Mollie API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Mollie - PaySafeCard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.mollie.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'paysafecard-mollie\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - iDEAL')
			{
				$sData .= TAB . '// Online Betaalplatform API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Online Betaalplatform Profile UID' . LF;
				$sData .= TAB . '$aSettings[\'PROFILE_UID\'] = \'' . (empty($_POST['parcelcheckout_profile_uid']) ? '' : addslashes($_POST['parcelcheckout_profile_uid'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Online Betaalplatform - iDEAL\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://onlinebetaalplatform.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-onlinebetaalplatform\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - Creditcard')
			{
				$sData .= TAB . '// Online Betaalplatform API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Online Betaalplatform Profile UID' . LF;
				$sData .= TAB . '$aSettings[\'PROFILE_UID\'] = \'' . (empty($_POST['parcelcheckout_profile_uid']) ? '' : addslashes($_POST['parcelcheckout_profile_uid'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Online Betaalplatform - Creditcard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://onlinebetaalplatform.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'creditcard-onlinebetaalplatform\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - Handmatig Overboeken')
			{
				$sData .= TAB . '// Online Betaalplatform API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Online Betaalplatform Profile UID' . LF;
				$sData .= TAB . '$aSettings[\'PROFILE_UID\'] = \'' . (empty($_POST['parcelcheckout_profile_uid']) ? '' : addslashes($_POST['parcelcheckout_profile_uid'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Online Betaalplatform - Handmatig Overboeken\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://onlinebetaalplatform.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'manualtransfer-onlinebetaalplatform\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - MisterCash')
			{
				$sData .= TAB . '// Online Betaalplatform API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Online Betaalplatform Profile UID' . LF;
				$sData .= TAB . '$aSettings[\'PROFILE_UID\'] = \'' . (empty($_POST['parcelcheckout_profile_uid']) ? '' : addslashes($_POST['parcelcheckout_profile_uid'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Online Betaalplatform - MisterCash\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://onlinebetaalplatform.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mistercash-onlinebetaalplatform\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Online Betaalplatform - Paypal')
			{
				$sData .= TAB . '// Online Betaalplatform API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Online Betaalplatform Profile UID' . LF;
				$sData .= TAB . '$aSettings[\'PROFILE_UID\'] = \'' . (empty($_POST['parcelcheckout_profile_uid']) ? '' : addslashes($_POST['parcelcheckout_profile_uid'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Online Betaalplatform - Paypal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://onlinebetaalplatform.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'paypal-onlinebetaalplatform\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - iDEAL')
			{
				$sData .= TAB . '// Webshop ID' . LF;
				$sData .= TAB . '$aSettings[\'WEBSHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_webshop_id']) ? '' : addslashes($_POST['parcelcheckout_webshop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Encryption Password' . LF;
				$sData .= TAB . '$aSettings[\'ENCRYPTION_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_encryption_password']) ? '' : addslashes($_POST['parcelcheckout_encryption_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayCheckout - iDEAL\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.paycheckout.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-paycheckout\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Creditcard')
			{
				$sData .= TAB . '// Webshop ID' . LF;
				$sData .= TAB . '$aSettings[\'WEBSHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_webshop_id']) ? '' : addslashes($_POST['parcelcheckout_webshop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Encryption Password' . LF;
				$sData .= TAB . '$aSettings[\'ENCRYPTION_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_encryption_password']) ? '' : addslashes($_POST['parcelcheckout_encryption_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayCheckout - Creditcard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.paycheckout.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'creditcard-paycheckout\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - MisterCash')
			{
				$sData .= TAB . '// Webshop ID' . LF;
				$sData .= TAB . '$aSettings[\'WEBSHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_webshop_id']) ? '' : addslashes($_POST['parcelcheckout_webshop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Encryption Password' . LF;
				$sData .= TAB . '$aSettings[\'ENCRYPTION_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_encryption_password']) ? '' : addslashes($_POST['parcelcheckout_encryption_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayCheckout - Mistercash\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.paycheckout.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mistercash-paycheckout\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Direct E-Banking/Sofort')
			{
				$sData .= TAB . '// Webshop ID' . LF;
				$sData .= TAB . '$aSettings[\'WEBSHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_webshop_id']) ? '' : addslashes($_POST['parcelcheckout_webshop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Encryption Password' . LF;
				$sData .= TAB . '$aSettings[\'ENCRYPTION_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_encryption_password']) ? '' : addslashes($_POST['parcelcheckout_encryption_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayCheckout - Direct ebanking\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.paycheckout.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'directebanking-paycheckout\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Klarnaaccount')
			{
				$sData .= TAB . '// Webshop ID' . LF;
				$sData .= TAB . '$aSettings[\'WEBSHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_webshop_id']) ? '' : addslashes($_POST['parcelcheckout_webshop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Encryption Password' . LF;
				$sData .= TAB . '$aSettings[\'ENCRYPTION_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_encryption_password']) ? '' : addslashes($_POST['parcelcheckout_encryption_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayCheckout - Klarnaaccount\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.paycheckout.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'\klarnaaccount-paycheckout\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Klarnainvoice')
			{
				$sData .= TAB . '// Webshop ID' . LF;
				$sData .= TAB . '$aSettings[\'WEBSHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_webshop_id']) ? '' : addslashes($_POST['parcelcheckout_webshop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Encryption Password' . LF;
				$sData .= TAB . '$aSettings[\'ENCRYPTION_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_encryption_password']) ? '' : addslashes($_POST['parcelcheckout_encryption_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayCheckout - Klarnainvoice\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.paycheckout.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'klarnainvoice-paycheckout\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Paypal')
			{
				$sData .= TAB . '// Webshop ID' . LF;
				$sData .= TAB . '$aSettings[\'WEBSHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_webshop_id']) ? '' : addslashes($_POST['parcelcheckout_webshop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Encryption Password' . LF;
				$sData .= TAB . '$aSettings[\'ENCRYPTION_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_encryption_password']) ? '' : addslashes($_POST['parcelcheckout_encryption_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayCheckout - Paypal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.paycheckout.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'paypal-paycheckout\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayCheckout - Sepa')
			{
				$sData .= TAB . '// Webshop ID' . LF;
				$sData .= TAB . '$aSettings[\'WEBSHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_webshop_id']) ? '' : addslashes($_POST['parcelcheckout_webshop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Encryption Password' . LF;
				$sData .= TAB . '$aSettings[\'ENCRYPTION_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_encryption_password']) ? '' : addslashes($_POST['parcelcheckout_encryption_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayCheckout - Sepa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.paycheckout.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'manualtransfer-paycheckout\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Postcode iDEAL - iDEAL Professional')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate private key file' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_PASS\'] = \'' . (empty($_POST['parcelcheckout_private_key_pass']) ? '' : addslashes($_POST['parcelcheckout_private_key_pass'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-KEY-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_key_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_key_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-CERTIFICATE-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_CERTIFICATE_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_certificate_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_certificate_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Postcode iDEAL - iDEAL Professional\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://services.postcode.nl/ideal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-postcodeideal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Qantani - Credit Card')
			{
				$sData .= TAB . '// Qantani API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Qantani - Creditcard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.qantani.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'creditcard-qantani\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Qantani - Direct E-Banking')
			{				
				$sData .= TAB . '// Qantani API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Qantani - Direct E-Banking\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.qantani.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'directebanking-qantani\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Qantani - iDEAL')
			{
				$sData .= TAB . '// Qantani API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Qantani - iDEAL\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.qantani.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-qantani\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Qantani - PayPal')
			{				
				$sData .= TAB . '// Qantani API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Qantani - Paypal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.qantani.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'paypal-qantani\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Qantani - Mistercash')
			{				
				$sData .= TAB . '// Qantani API Key' . LF;
				$sData .= TAB . '$aSettings[\'API_KEY\'] = \'' . (empty($_POST['parcelcheckout_api_key']) ? '' : addslashes($_POST['parcelcheckout_api_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Qantani - Mistercash\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'https://www.qantani.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mistercash-qantani\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Rabobank - iDEAL Internet Kassa')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'PSP_ID\'] = \'' . (empty($_POST['parcelcheckout_psp_id']) ? '' : addslashes($_POST['parcelcheckout_psp_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate/validate hashes' . LF;
				$sData .= TAB . '$aSettings[\'SHA_1_IN\'] = \'' . (empty($_POST['parcelcheckout_sha1_in']) ? '' : addslashes($_POST['parcelcheckout_sha1_in'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'SHA_1_OUT\'] = \'' . (empty($_POST['parcelcheckout_sha1_out']) ? '' : addslashes($_POST['parcelcheckout_sha1_out'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Rabobank - iDEAL Internet Kassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.rabobank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-internetkassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Rabobank - iDEAL Professional')
			{
				$sData .= TAB . '// Merchant ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate private key file' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_PASS\'] = \'' . (empty($_POST['parcelcheckout_private_key_pass']) ? '' : addslashes($_POST['parcelcheckout_private_key_pass'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-KEY-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_KEY_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_key_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_key_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Name of your PRIVATE-CERTIFICATE-FILE (should be located in /idealcheckout/certificates/)' . LF;
				$sData .= TAB . '$aSettings[\'PRIVATE_CERTIFICATE_FILE\'] = \'' . (empty($_FILES['parcelcheckout_private_certificate_file']['name']) ? '' : addslashes($_FILES['parcelcheckout_private_certificate_file']['name'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Rabobank - iDEAL Professional\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.rabobank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-professional-v3\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - Credit Card')
			{
				$sData .= TAB . '// Webshop-ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'KEY_VERSION\'] = \'' . document.getElementById('parcelcheckout_hash_key_version').value . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Rabo OmniKassa - Credit Card\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.rabobank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'creditcard-omnikassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - iDEAL')
			{
				$sData .= TAB . '// Webshop-ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'KEY_VERSION\'] = \'' .(empty($_POST['parcelcheckout_hash_key_version']) ? '' : addslashes($_POST['parcelcheckout_hash_key_version'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Rabo OmniKassa - iDEAL\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.rabobank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-omnikassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - Maestro')
			{
				$sData .= TAB . '// Webshop-ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'KEY_VERSION\'] = \'' .(empty($_POST['parcelcheckout_hash_key_version']) ? '' : addslashes($_POST['parcelcheckout_hash_key_version'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Rabo OmniKassa - Maestro\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.rabobank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'maestro-omnikassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - Mastercard')
			{
				$sData .= TAB . '// Webshop-ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'KEY_VERSION\'] = \'' .(empty($_POST['parcelcheckout_hash_key_version']) ? '' : addslashes($_POST['parcelcheckout_hash_key_version'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Rabo OmniKassa - Mastercard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.rabobank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mastercard-omnikassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - MisterCash')
			{
				$sData .= TAB . '// Webshop-ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'KEY_VERSION\'] = \'' .(empty($_POST['parcelcheckout_hash_key_version']) ? '' : addslashes($_POST['parcelcheckout_hash_key_version'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				// $sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = false;' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Rabo OmniKassa - MisterCash\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.rabobank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mistercash-omnikassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - Visa')
			{
				$sData .= TAB . '// Webshop-ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'KEY_VERSION\'] = \'' .(empty($_POST['parcelcheckout_hash_key_version']) ? '' : addslashes($_POST['parcelcheckout_hash_key_version'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Rabo OmniKassa - Visa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.rabobank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'visa-omnikassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Rabo OmniKassa - VPAY')
			{
				$sData .= TAB . '// Webshop-ID' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your iDEAL Sub ID' . LF;
				$sData .= TAB . '$aSettings[\'SUB_ID\'] = \'' . (empty($_POST['parcelcheckout_sub_id']) ? '0' : addslashes($_POST['parcelcheckout_sub_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Password used to generate hash' . LF;
				$sData .= TAB . '$aSettings[\'HASH_KEY\'] = \'' . (empty($_POST['parcelcheckout_hash_key']) ? '' : addslashes($_POST['parcelcheckout_hash_key'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'KEY_VERSION\'] = \'' .(empty($_POST['parcelcheckout_hash_key_version']) ? '' : addslashes($_POST['parcelcheckout_hash_key_version'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				// $sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = false;' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Rabo OmniKassa - VPAY\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.rabobank.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'vpay-omnikassa\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = false;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Sisow - Direct E-Banking')
			{
				$sData .= TAB . '// Merchant ID or Email' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Merchant Key or password' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_KEY\'] = \'' . (empty($_POST['parcelcheckout_merchant_key']) ? '' : addslashes($_POST['parcelcheckout_merchant_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your SHOP ID (for multiple shops in 1 account)' . LF;
				$sData .= TAB . '$aSettings[\'SHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_shop_id']) ? '' : addslashes($_POST['parcelcheckout_shop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Sisow - Direct E-Banking\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.sisow.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'directebanking-sisow\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Sisow - iDEAL')
			{
				$sData .= TAB . '// Merchant ID or Email' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Merchant Key or password' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_KEY\'] = \'' . (empty($_POST['parcelcheckout_merchant_key']) ? '' : addslashes($_POST['parcelcheckout_merchant_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your SHOP ID (for multiple shops in 1 account)' . LF;
				$sData .= TAB . '$aSettings[\'SHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_shop_id']) ? '' : addslashes($_POST['parcelcheckout_shop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Sisow - iDEAL\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.sisow.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-sisow\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Sisow - MisterCash')
			{
				$sData .= TAB . '// Merchant ID or Email' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Merchant Key or password' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_KEY\'] = \'' . (empty($_POST['parcelcheckout_merchant_key']) ? '' : addslashes($_POST['parcelcheckout_merchant_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your SHOP ID (for multiple shops in 1 account)' . LF;
				$sData .= TAB . '$aSettings[\'SHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_shop_id']) ? '' : addslashes($_POST['parcelcheckout_shop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Sisow - MisterCash\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.sisow.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mistercash-sisow\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'Sisow - PayPal')
			{
				$sData .= TAB . '// Merchant ID or Email' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_ID\'] = \'' . (empty($_POST['parcelcheckout_merchant_id']) ? '' : addslashes($_POST['parcelcheckout_merchant_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Merchant Key or password' . LF;
				$sData .= TAB . '$aSettings[\'MERCHANT_KEY\'] = \'' . (empty($_POST['parcelcheckout_merchant_key']) ? '' : addslashes($_POST['parcelcheckout_merchant_key'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Your SHOP ID (for multiple shops in 1 account)' . LF;
				$sData .= TAB . '$aSettings[\'SHOP_ID\'] = \'' . (empty($_POST['parcelcheckout_shop_id']) ? '' : addslashes($_POST['parcelcheckout_shop_id'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// Use TEST/LIVE mode; true=TEST, false=LIVE' . LF;
				$sData .= TAB . '$aSettings[\'TEST_MODE\'] = ' . (empty($_POST['parcelcheckout_test_mode']) ? '' : addslashes($_POST['parcelcheckout_test_mode'])) . ';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'Sisow - PayPal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.sisow.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'paypal-sisow\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - Credit Card')
			{
				$sData .= TAB . '// TargetPay Layout Code' . LF;
				$sData .= TAB . '$aSettings[\'LAYOUT_CODE\'] = \'' . (empty($_POST['parcelcheckout_layout_code']) ? '' : addslashes($_POST['parcelcheckout_layout_code'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'TargetPay - Credit Card\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.targetpay.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'creditcard-targetpay\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - Direct E-Banking')
			{
				$sData .= TAB . '// TargetPay Layout Code' . LF;
				$sData .= TAB . '$aSettings[\'LAYOUT_CODE\'] = \'' . (empty($_POST['parcelcheckout_layout_code']) ? '' : addslashes($_POST['parcelcheckout_layout_code'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'TargetPay - Direct E-Banking\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.targetpay.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'directebanking-targetpay\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - iDEAL')
			{
				$sData .= TAB . '// TargetPay Layout Code' . LF;
				$sData .= TAB . '$aSettings[\'LAYOUT_CODE\'] = \'' . (empty($_POST['parcelcheckout_layout_code']) ? '' : addslashes($_POST['parcelcheckout_layout_code'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'TargetPay - iDEAL\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.targetpay.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-targetpay\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - MisterCash')
			{
				$sData .= TAB . '// TargetPay Layout Code' . LF;
				$sData .= TAB . '$aSettings[\'LAYOUT_CODE\'] = \'' . (empty($_POST['parcelcheckout_layout_code']) ? '' : addslashes($_POST['parcelcheckout_layout_code'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'TargetPay - MisterCash\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.targetpay.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'mistercash-targetpay\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'TargetPay - PaySafeCard')
			{
				$sData .= TAB . '// TargetPay Layout Code' . LF;
				$sData .= TAB . '$aSettings[\'LAYOUT_CODE\'] = \'' . (empty($_POST['parcelcheckout_layout_code']) ? '' : addslashes($_POST['parcelcheckout_layout_code'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'TargetPay - PaySafeCard\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.targetpay.com/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'paysafecard-targetpay\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayDutch - Direct E-Banking')
			{
				$sData .= TAB . '// PayDutch account settings' . LF;
				$sData .= TAB . '$aSettings[\'PAYDUTCH_USERNAME\'] = \'' . (empty($_POST['parcelcheckout_username']) ? '' : addslashes($_POST['parcelcheckout_username'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'PAYDUTCH_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_password']) ? '' : addslashes($_POST['parcelcheckout_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// PayDutch callback security settings' . LF;
				$sData .= TAB . '$aSettings[\'PAYDUTCH_CALLBACK_USERNAME\'] = \'' . (empty($_POST['parcelcheckout_callback_username']) ? '' : addslashes($_POST['parcelcheckout_callback_username'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'PAYDUTCH_CALLBACK_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_callback_password']) ? '' : addslashes($_POST['parcelcheckout_callback_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayDutch - Direct E-Banking\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.paydutch.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'directebanking-paydutch\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}
			elseif($aSelectedGateway['code'] == 'PayDutch - WeDeal')
			{
				$sData .= TAB . '// PayDutch account settings' . LF;
				$sData .= TAB . '$aSettings[\'PAYDUTCH_USERNAME\'] = \'' . (empty($_POST['parcelcheckout_username']) ? '' : addslashes($_POST['parcelcheckout_username'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'PAYDUTCH_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_password']) ? '' : addslashes($_POST['parcelcheckout_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= TAB . '// PayDutch callback security settings' . LF;
				$sData .= TAB . '$aSettings[\'PAYDUTCH_CALLBACK_USERNAME\'] = \'' . (empty($_POST['parcelcheckout_callback_username']) ? '' : addslashes($_POST['parcelcheckout_callback_username'])) . '\';' . LF;
				$sData .= TAB . '$aSettings[\'PAYDUTCH_CALLBACK_PASSWORD\'] = \'' . (empty($_POST['parcelcheckout_callback_password']) ? '' : addslashes($_POST['parcelcheckout_callback_password'])) . '\';' . LF;
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// Basic gateway settings' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_NAME\'] = \'PayDutch - WeDeal\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_WEBSITE\'] = \'http://www.paydutch.nl/\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_METHOD\'] = \'ideal-paydutch\';' . LF;
				$sData .= TAB . '$aSettings[\'GATEWAY_VALIDATION\'] = true;' . LF;
			}

			if(!empty($_POST['parcelcheckout_email_to']))
			{
				$sData .= LF;
				$sData .= LF;
				$sData .= TAB . '// E-mailadresses for transaction updates (comma seperated)' . LF;
				$sData .= TAB . '$aSettings[\'TRANSACTION_UPDATE_EMAILS\'] = \'' . (empty($_POST['parcelcheckout_email_to']) ? '' : addslashes($_POST['parcelcheckout_email_to'])) . '\';' . LF;
			}

			$sData .= LF;
			$sData .= '?' . '>';
			
			
			if(in_array($aSelectedGateway['code'], array('Rabobank - iDEAL Professional', 'Postcode iDEAL - iDEAL Professional', 'ABN Amro - iDEAL Zelfbouw', 'ING Bank - iDEAL Advanced', 'Fortis Bank - iDEAL Integrated')))
			{
				
				$aFormValues['db_host'] = 'localhost';
				$aFormValues['db_port'] = '';
				$aFormValues['db_user'] = '';
				$aFormValues['db_pass'] = '';
				$aFormValues['db_name'] = '';
				$aFormValues['db_prefix'] = '';
						
				$aDatabaseSettings = call_user_func($_REQUEST['software'] . '::getDatabaseSettings', $aFormValues);
										
				if(isset($_FILES['parcelcheckout_private_key_file']['error']) && isset($_FILES['parcelcheckout_private_certificate_file']['error']))
				{
					if(empty($_FILES['parcelcheckout_private_key_file']['error']) && !empty($_FILES['parcelcheckout_private_key_file']['tmp_name']) && empty($_FILES['parcelcheckout_private_certificate_file']['error']) && !empty($_FILES['parcelcheckout_private_certificate_file']['tmp_name'])) // No errors found
					{
						$sPrivateKeyData = file_get_contents($_FILES['parcelcheckout_private_key_file']['tmp_name']);
			
						if(!empty($sPrivateKeyData))
						{
							$sPrivateKeyName = $_FILES['parcelcheckout_private_key_file']['name'];
							
							// Check if key exists
							$sql = "SELECT `id` FROM `" . $aDatabaseSettings['db_prefix'] . "parcelcheckout_ssl` WHERE (`file_name` = '" . ic_escapeSql($sPrivateKeyName) . "') LIMIT 1";
							
							if(!ic_database_getRecord($sql))
							{
								// Save key
								$sql = "INSERT INTO `" . $aDatabaseSettings['db_prefix'] . "parcelcheckout_ssl` (`id`, `file_name`, `file_content`, `enabled`) VALUES (NULL, '" . ic_escapeSql($sPrivateKeyName) . "', '" . ic_escapeSql($sPrivateKeyData) . "', '1');";
								ic_database_execute($sql);
							}
							else
							{
								// Set other keyfiles to disabled and insert new one
								$sql = "UPDATE `" . $aDatabaseSettings['db_prefix'] . "parcelcheckout_ssl` SET `enabled` = '0' WHERE (`file_name` = '" .  ic_escapeSql($sPrivateKeyName) . "')";
								ic_database_execute($sql);
								
								// Save new key
								$sql = "INSERT INTO `" . $aDatabaseSettings['db_prefix'] . "parcelcheckout_ssl` (`id`, `file_name`, `file_content`, `enabled`) VALUES (NULL, '" . ic_escapeSql($sPrivateKeyName) . "', '" . ic_escapeSql($sPrivateKeyData) . "', '1');";
								ic_database_execute($sql);
								
							}
							
						}
						
						$sCertificateData = file_get_contents($_FILES['parcelcheckout_private_certificate_file']['tmp_name']);
						
						if(!empty($sCertificateData))
						{
							$sPrivateCertificateName = $_FILES['parcelcheckout_private_certificate_file']['name'];
							
							
							// Check if key exists
							$sql = "SELECT `id` FROM `" . $aDatabaseSettings['db_prefix'] . "parcelcheckout_ssl` WHERE (`file_name` = '" . ic_escapeSql($sPrivateCertificateName) . "') LIMIT 1";
							
							if(!ic_database_getRecord($sql))
							{
								// Save certificate
								$sql = "INSERT INTO `" . $aDatabaseSettings['db_prefix'] . "parcelcheckout_ssl` (`id`, `file_name`, `file_content`, `enabled`) VALUES (NULL, '" . ic_escapeSql($sPrivateCertificateName) . "', '" . ic_escapeSql($sCertificateData) . "', '1');";
								ic_database_execute($sql);
							}
							else
							{
								// Set other keyfiles to disabled and insert new one
								$sql = "UPDATE `" . $aDatabaseSettings['db_prefix'] . "parcelcheckout_ssl` SET `enabled` = '0' WHERE (`file_name` = '" .  ic_escapeSql($sPrivateCertificateName) . "')";
								ic_database_execute($sql);
								
								// Save new key
								$sql = "INSERT INTO `" . $aDatabaseSettings['db_prefix'] . "parcelcheckout_ssl` (`id`, `file_name`, `file_content`, `enabled`) VALUES (NULL, '" . ic_escapeSql($sPrivateCertificateName) . "', '" . ic_escapeSql($sCertificateData) . "', '1');";
								ic_database_execute($sql);

								
							}
						}
					}
				}
				else
				{
					PARCELCHECKOUT_INSTALL::setLog('Cannot find private cer: ' . $sFilePath, __FILE__, __LINE__);
					return false;
				}				
			}
			
			$sFilePath = '/idealcheckout/configuration/' . $aSelectedGateway['type'] . '.php';

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
				$sFilePath = '/idealcheckout/configuration/' . $aSelectedGateway['type'] . '.' . $sStoreCode . '.php';
		
				
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
					$sFilePath = '/idealcheckout/configuration/' . $aSelectedGateway['type'] . '.' . $sStoreCode . '.php';

					if(!PARCELCHECKOUT_INSTALL::setFile($sFilePath, $sData))
					{
						PARCELCHECKOUT_INSTALL::setLog('Cannot create file: ' . $sFilePath, __FILE__, __LINE__);
						return false;
					}
				}
				elseif(strpos($sStoreHost, 'www.') === 0) // Starts with 'www.'
				{
					$sStoreCode = md5(substr($_SERVER['SERVER_NAME'], 4));
					$sFilePath = '/idealcheckout/configuration/' . $aSelectedGateway['type'] . '.' . $sStoreCode . '.php';

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
			$sOutput = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>iDEAL Checkout Installatie Wizard</title>

		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-15">
		<meta http-equiv="content-language" content="nl-nl">

		<link href="https://www.ideal-checkout.nl/manuals/install/install.css" media="screen" rel="stylesheet" type="text/css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	</head>
	<body>';

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
					<td align="left"' . (($iColspan > 1) ? ' colspan="' . $iColspan . '"' : '') . ' height="180" valign="top"><a href="https://www.ideal-checkout.nl" target="_blank"><img alt="iDEAL Checkout" border="0" height="131" src="https://www.ideal-checkout.nl/manuals/install/ideal-checkout-logo.png" width="456"></a></td>
				</tr>
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
</html>';

			echo $sOutput;
			exit;
		}
	}

?>