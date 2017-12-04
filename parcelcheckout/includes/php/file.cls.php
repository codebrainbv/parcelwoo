<?php

	class clsFile
	{
		public static function getFolder($sFilePath)
		{
			return substr($sFilePath, 0, strlen($sFilePath) - strlen(self::getName($sFilePath)) - 1);
		}

		public static function getRealPath($sFilePath, $bValidate = false)
		{
			if(($bValidate === false) || @file_exists($sFilePath))
			{
				return $sFilePath;
			}

			return '';
		}

		public static function getName($sFilePath, $bRemoveExtension = false)
		{
			$sFilePath = str_replace('\\', '/', $sFilePath); // Windows fix

			$a = explode('/', $sFilePath);
			$sFileName = $a[sizeof($a) - 1];

			if($bRemoveExtension)
			{
				$iOffset = strrpos($sFileName, '.');

				if($iOffset !== false)
				{
					$sFileName = substr($sFileName, 0, $iOffset);
				}
			}

			return $sFileName;
		}

		public static function getExtension($sFilePath, $bLowerCase = true)
		{
			if(($iOffset = strpos($sFilePath, '?')) !== false)
			{
				$sFilePath = substr($sFilePath, 0, $iOffset);
			}

			if(($iOffset = strpos($sFilePath, '#')) !== false)
			{
				$sFilePath = substr($sFilePath, 0, $iOffset);
			}

			$a = explode('.', $sFilePath);
			$sExtension = array_pop($a);

			if($bLowerCase === true) // Force to lowercase
			{
				$sExtension = strtolower($sExtension);
			}

			return $sExtension;
		}

		public static function getSize($sFilePath)
		{
			$sFilePath = self::getRealPath($sFilePath);

			if(self::isReadable($sFilePath))
			{
				return filesize($sFilePath);
			}

			return 0;
		}

		public static function getData($sFilePath)
		{
			$sFilePath = self::getRealPath($sFilePath);

			if(self::isReadable($sFilePath))
			{
				return file_get_contents($sFilePath);
			}

			return '';
		}

		public static function getMime($sFilePath)
		{
			$sExtension = self::getExtension($sFilePath);
			$sMime = 'application/octet-stream';
			// $sMime = 'application/force-download';

			switch($sExtension)
			{
				case 'asf' : $sMime = 'video/x-ms-asf'; break;
				case 'asx' : $sMime = 'video/x-ms-asf'; break;
				case 'avi' : $sMime = 'video/avi'; break;
				case 'dv' : $sMime = 'video/x-dv'; break;
				case 'dvi' : $sMime = 'application/x-dvi'; break;
				case 'gif' : $sMime = 'image/gif'; break;
				case 'ico' : $sMime = 'image/x-icon'; break;
				case 'jfif' : $sMime = 'image/jpeg'; break;
				case 'jpe' : $sMime = 'image/jpeg'; break;
				case 'jpeg' : $sMime = 'image/jpeg'; break;
				case 'jpg' : $sMime = 'image/jpeg'; break;
				case 'mov' : $sMime = 'video/quicktime'; break;
				case 'mpeg' : $sMime = 'video/mpeg'; break;
				case 'png' : $sMime = 'image/png'; break;
				case 'qif' : $sMime = 'image/x-quicktime'; break;
				case 'qt' : $sMime = 'video/quicktime'; break;
				case 'qtc' : $sMime = 'video/x-qtc'; break;
				case 'qti' : $sMime = 'image/x-quicktime'; break;
				case 'qtif' : $sMime = 'image/x-quicktime'; break;
				case 'rf' : $sMime = 'image/vnd.rn-realflash'; break;
				case 'swf' : $sMime = 'application/x-shockwave-flash'; break;
				case 'tif' : $sMime = 'image/tiff'; break;
				case 'tiff' : $sMime = 'image/tiff'; break;
				case 'xml' : $sMime = 'application/xml'; break;

				case 'js' : $sMime = 'text/javascript'; break;
				case 'css' : $sMime = 'text/css'; break;
			}

			return $sMime;
		}

		public static function getTime($sFilePath, $sDateFormat = false)
		{
			$sFilePath = self::getRealPath($sFilePath);

			if($sDateFormat)
			{
				return date($sDateFormat, filemtime($sFilePath));
			}
			else
			{
				return filemtime($sFilePath);
			}
		}

		public static function getEtag($sFilePath)
		{
			$sFilePath = self::getRealPath($sFilePath);

			return md5($sFilePath);
		}

		public static function isReadable($sFilePath)
		{
			$sFilePath = self::getRealPath($sFilePath);

			if($sFilePath && file_exists($sFilePath))
			{
				return (is_readable($sFilePath) ? true : false);
			}

			return false;
		}

		public static function isWritable($sFilePath)
		{
			$sFilePath = self::getRealPath($sFilePath);
			
			if($sFilePath && file_exists($sFilePath))
			{
				return (is_writable($sFilePath) ? true : false);
			}

			return false;
		}

		public static function delete($sFilePath)
		{
			$sFilePath = self::getRealPath($sFilePath);

			if($sFilePath && self::isReadable($sFilePath))
			{
				if(@unlink($sFilePath))
				{
					return true;
				}
			}

			return false;
		}

		public static function copy($sSourcePath, $sDestinationPath)
		{
			$sSourcePath = self::getRealPath($sSourcePath);
			$sDestinationPath = self::getRealPath($sDestinationPath);

			if(copy($sSourcePath, $sDestinationPath))
			{
				@chmod($sDestinationPath, 0777);

				return true;
			}

			return false;
		}

		public static function move($sSourcePath, $sDestinationPath)
		{
			$sSourcePath = self::getRealPath($sSourcePath);
			$sDestinationPath = self::getRealPath($sDestinationPath);

			parcelcheckout_createFolder(self::getFolder($sDestinationPath));

			if(rename($sSourcePath, $sDestinationPath))
			{
				@chmod($sDestinationPath, 0777);

				return true;
			}

			return false;
		}

		public static function truncate($sFilePath)
		{
			$sFilePath = self::getRealPath($sFilePath);
			return self::write($sFilePath);
		}

		public static function read($sFilePath)
		{
			$sFilePath = self::getRealPath($sFilePath);
			if(self::isReadable($sFilePath))
			{
				return file_get_contents($sFilePath);
			}

			return '';
		}

		public static function write($sFilePath, $sData = '')
		{
			$sFilePath = self::getRealPath($sFilePath);
			parcelcheckout_createFolder(self::getFolder($sFilePath));

			touch($sFilePath);
			@chmod($sFilePath, 0777);

			if(file_put_contents($sFilePath, $sData))
			{
				return true;
			}

			return false;
		}

		public static function append($sFilePath, $sData = '')
		{
			$sFilePath = self::getRealPath($sFilePath);
			parcelcheckout_createFolder(self::getFolder($sFilePath));

			touch($sFilePath);
			@chmod($sFilePath, 0777);

			if(file_put_contents($sFilePath, $sData, FILE_APPEND))
			{
				return true;
			}

			return false;
		}

		// Load a PHP file, providing upto 10 arguments, without the worry of overwriting existing variables
		// Output is recieved via a 'return [any string]', or catched via 'output buffering' (ob) functions
		public static function load($sFilePath, $aParams = array())
		{
			$_____sFilePath = self::getRealPath($sFilePath, true);
			$_____sFileData = '';

			if($_____sFilePath)
			{
				if(strcasecmp(substr($_____sFilePath, -4, 4), '.php') !== 0)
				{
					$_____sFileData = clsFile::read($_____sFilePath);

					if(strpos($_____sFileData, '<?php') !== false)
					{
						// Detect invalid open tags
						if(ini_get('short_open_tag'))
						{
							$aMatches = array();
							preg_match_all('/<\?([a-zA-Z]*)/', $_____sFileData, $aMatches);

							foreach($aMatches[1] as $v)
							{
								if(strcasecmp($v, 'php') !== 0)
								{
									die('Error in file: ' . $_____sFilePath . '.' . LF . 'File contains invalid PHP syntax (It uses "<?" instead of "<?php").');
								}
							}
						}

						ob_start();

						$_____sFileData = include($_____sFilePath);

						if($_____sFileData === 1)
						{
							if(ob_get_length())
							{
								$_____sFileData = ob_get_contents();
							}
							else
							{
								$_____sFileData = '';
							}
						}

						ob_end_clean();
					}
				}
				else
				{
					// Include file
					$_____sFileData = include($_____sFilePath);

					if($_____sFileData === 1)
					{
						$_____sFileData = '';
					}
				}
			}
			elseif(class_exists('clsLog', false))
			{
				clsLog::warning('Cannot load file: ' . $sFilePath, __FILE__, __LINE__);
			}

			return $_____sFileData;
		}


		// Manage lockfile
		public static function setLock($sFilePath, $iTimestamp = false)
		{
			if($iTimestamp <= 0)
			{
				$iTimestamp = false;
			}
			elseif($iTimestamp < 1000)
			{
				// Treat timestamp as hours, '1.5' becomes strtotime('-90 Minutes');
				$iTimestamp = strtotime('-' . round($iTimestamp * 60) . ' Minutes');
			}

			if($iTimestamp === false) // Remove lockfile when available
			{
				self::delete($sFilePath);

				return true;
			}
			elseif(self::isReadable($sFilePath) == false) // Create a new lockfile
			{
				touch($sFilePath);
				@chmod($sFilePath, 0777);

				return true;
			}
			elseif($iTimestamp && (filemtime($sFilePath) < $iTimestamp)) // Override lockfile when it's to old
			{
				touch($sFilePath);
				@chmod($sFilePath, 0777);

				return true;
			}

			return false;
		}

		public static function useCache($sFilePath, $iTimestamp = false, $iMinBytes = 0)
		{
			if(is_file($sFilePath)) // See if cache file exists
			{
				if(filesize($sFilePath) > $iMinBytes) // See if cache file has enough content
				{
					if($iTimestamp) // Validate cache timestamp
					{
						if(filemtime($sFilePath) <= $iTimestamp) // File to old
						{
							return false;
						}
					}

					return true;
				}
			}

			return false;
		}

		public static function output($sFilePath, $sFileMask = false, $bForceDownload = false)
		{
			// Files to display inline
			$aInlineFiles = explode('|', FILE_EXTENSIONS_INLINE);

			if((strpos($sFilePath, LF) === false) && is_file($sFilePath))
			{
				$sFileExtension = self::getExtension($sFilePath);
				$sFileMime = self::getMime($sFilePath);
				$sFileName = self::getName($sFilePath);
				$sFileSize = self::getSize($sFilePath);
				$sFileData = false;

				if($sFileMask === false)
				{
					$sFileMask = $sFileName;
				}
			}
			elseif($sFileMask)
			{
				$sFileExtension = self::getExtension($sFileMask);
				$sFileMime = self::getMime($sFileMask);
				$sFileName = self::getName($sFileMask);
				$sFileSize = strlen($sFilePath);
				$sFileData = $sFilePath;
			}
			else
			{
				echo 'File not found';
				exit;
			}

			if(!in_array($sFileExtension, $aInlineFiles))
			{
				$bForceDownload = true;
			}


			// Output file
			header('Content-Type: ' . $sFileMime);
			header('Content-Length: ' . $sFileSize);

			if($bForceDownload)
			{
				header('Pragma: public');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Content-Type: application/force-download');
				header('Content-Type: application/octet-stream');
				header('Content-Type: application/download');
				header('Content-Disposition: attachment; filename="' . $sFileMask . '"');
			}
			else
			{
				header('Content-Disposition: inline; filename="' . $sFileMask . '"');
			}

			header('Content-Transfer-Encoding: binary');

			if($sFileData === false)
			{
				readfile($sFilePath);
			}
			else
			{
				echo $sFileData;
			}

			exit;
		}

		// Unzip a ZIP archive
		// Use $aEntryPaths to unzip specific files and folders from the archive file. 
		public static function unzip($sSourceFile, $sDestinationPath, $bCreateZipFolder = false, $aEntryPaths = false, $bOverrideFiles = true)
		{
			// See if source is a valid .ZIP file
			if(!is_file($sSourceFile))
			{
				echo '<b>ZIP Error</b><br>Invalid archive: ' . $sSourceFile . '<br>File not found.';
				exit;
			}
			elseif(strcasecmp(substr($sSourceFile, -4, 4), '.zip') !== 0)
			{
				echo '<b>ZIP Error</b><br>Invalid archive: ' . $sSourceFile . '<br>File extension should be ".zip"';
				exit;
			}

			if($bCreateZipFolder)
			{
				// Use filename as foldername, 'archive.zip' will append '/archive' to the destination path
				$sDestinationPath .= '/' . self::getName($sSourceFile, true);
			}

			if($oArchive = zip_open($sSourceFile))
			{
				// Set time limit to unzip all data (or script will timeout)
				set_time_limit(600);

				// Create destination folder
				self::_unzip_createFolders($sDestinationPath);

				// For each file in the ZIP-archive
				while($oArchiveEntry = zip_read($oArchive))
				{
					// Find entry name (eg. my/file/here.gif)
					$sEntryName = zip_entry_name($oArchiveEntry);
					$bUnzipEntry = true;

					if($aEntryPaths && is_array($aEntryPaths))
					{
						$bUnzipEntry = false;

						foreach($aEntryPaths as $k => $v)
						{
							if(strpos($sEntryName, $v) === 0)
							{
								$bUnzipEntry = true;
							}
						}
					}

					if($bUnzipEntry)
					{
						// The name of the file to save on the disk
						$sEntryPath = $sDestinationPath . '/' . $sEntryName;

						if(substr($sEntryPath, -1, 1) == '/') // Folder
						{
							self::_unzip_createFolders($sDestinationPath . '/' . substr($sEntryName, 0, -1));
						}
						elseif(($bOverrideFiles === true) || !is_file($sEntryPath))
						{
							$iSlashPosition = strrpos($sEntryName, '/');

							// Entry should be placed within a subfolder
							if($iSlashPosition !== false)
							{
								// Create destination folder
								self::_unzip_createFolders($sDestinationPath . '/' . substr($sEntryName, 0, $iSlashPosition));
							}

							// Open the entry
							if(zip_entry_open($oArchive, $oArchiveEntry, 'r'))
							{
								// Get the content of the zip entry
								$sFileData = zip_entry_read($oArchiveEntry, zip_entry_filesize($oArchiveEntry));

								if(file_put_contents($sEntryPath, $sFileData) === false)
								{
									echo '<b>ZIP Error</b><br>Cannot save ' . $sEntryName . ' to ' . $sEntryPath;
									exit;
								}
								else
								{
									@chmod($sEntryPath, 0777);
								}

								// Close the entry
								zip_entry_close($oArchiveEntry);
							}
							else
							{
								echo '<b>ZIP Error</b><br>Cannot extract ' . $sEntryName . ' from archive ' . $sSourceFile;
								exit;
							}
						}
					}
				}

				// Close the zip-file
				zip_close($oArchive);
			}
			else
			{
				echo '<b>ZIP Error</b><br>Cannot open ' . $sSourceFile . '<br>File is not a valid archive.';
				exit;
			}

			return $sDestinationPath;
		}

		public static function _unzip_createFolders($sFolderPath)
		{
			if(!is_dir($sFolderPath))
			{
				$iSlashPosition = strrpos($sFolderPath, '\\');

				if($iSlashPosition !== false) // Windows
				{
					$aDirectories = explode('/', substr($sFolderPath, $iSlashPosition + 1));
					$sFolderPath = substr($sFolderPath, 0, $iSlashPosition + 1);
				}
				else // Unix
				{
					$aDirectories = explode('/', $sFolderPath);
					$sFolderPath = '/';

					array_pop($aDirectories);
				}

				foreach($aDirectories as $sDirectory)
				{
					$sFolderPath .= $sDirectory;

					if(!is_dir($sFolderPath))
					{
						if(@mkdir($sFolderPath, 0777))
						{
							@chmod($sFolderPath, 0777);
						}
						else
						{
							echo '<b>ZIP Error</b><br>Cannot create folder: ' . $sFolderPath;
							exit;
						}
					}

					$sFolderPath .= '/';
				}
			}

			return true;
		}

		public static function toZip($sSourcePath, $sDestinationPath, $sZipPath = '')
		{
			if(is_array($sSourcePath))
			{
				$aFiles = $sSourcePath;
			}
			else
			{
				$aFiles = array($sSourcePath);
			}

			$bOutput = false;

			if((strpos($sDestinationPath, '/') === false) && (strpos($sDestinationPath, '\\') === false))
			{
				$bOutput = true;
				$sOutputFileName = $sDestinationPath;
				$sDestinationPath = self::getTemp();
			}

			$oZipArchive = new ZipArchive();
			$oZipArchive->open($sDestinationPath, ZIPARCHIVE::CREATE);

			if(strlen($sZipPath))
			{
				$a = explode('/', str_replace('\\', '/', $sZipPath));
				$s = '';

				foreach($a as $k => $v)
				{
					if(strlen($v))
					{
						$s .= ($s ? '/' : '') . $v;
						// $oZipArchive->addEmptyDir($s);
					}
				}

				$sZipPath = $s . ($s ? '/' : '');
			}

			foreach($aFiles as $k => $v)
			{
				if(is_array($v))
				{
					if(isset($v['path'], $v['name']))
					{
						$sFileName = $v['name'];
						$sFilePath = $v['path'];
					}
					else
					{
						continue;
					}
				}
				else
				{
					$sFileName = basename($v);
				}

				if(is_file($sFilePath))
				{
					$sRelativePath = $sZipPath . $sFileName;
					$oZipArchive->addFile($sFilePath, $sRelativePath);
				}
			}

			$oZipArchive->close();

			if($bOutput)
			{
				header('Pragma: public');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Content-Type: application/force-download');
				header('Content-Type: application/octet-stream');
				header('Content-Type: application/download');
				header('Content-Disposition: attachment; filename="' . $sOutputFileName . '"');
				header('Content-Length: ' . filesize($sDestinationPath));
				header('Content-Transfer-Encoding: binary');
				readfile($sDestinationPath);
				exit;
			}
			else
			{
				@chmod($sDestinationPath, 0777);
			}

			return true;
		}

		public static function getTemp($sPrefix = false)
		{
			if($sPrefix === false)
			{
				$sPrefix = strtoupper(md5(__FILE__ . time()));

/*
				$sRandomFile = dirname(__FILE__) . '/random.cls.php';

				if(is_file($sRandomFile))
				{
					require_once($sRandomFile);
					$sPrefix = clsRandom::getUppercase(32);
				}
*/
			}

			return tempnam(sys_get_temp_dir(), $sPrefix);
		}
	}

?>