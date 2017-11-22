<?php

	// Set default debug settings
	@ini_set('display_errors', 1);
	@ini_set('display_startup_errors', 1);
	@error_reporting(E_ALL);
	// @ini_set('log_errors', 1);
	// @ini_set('error_log', dirname(dirname(__FILE__)) . '/temp/php.' . time() . '.log');

	if(!defined('LF'))
	{
		define('LF', chr(10)); // Line feed '\n'
	}

	if(!defined('TAB'))
	{
		define('TAB', chr(9)); // Tab '\t'
	}

	if(!defined('DS')) // Directory seperator
	{
		define('DS', (strpos(dirname(__FILE__), '\\') ? '\\' : '/')); 
	}

	if(!defined('PARCELCHECKOUT_PATH'))
	{
		define('PARCELCHECKOUT_PATH', dirname(dirname(__FILE__)));
	}

	if(!defined('SOFTWARE_PATH'))
	{
		define('SOFTWARE_PATH', dirname(dirname(dirname(__FILE__))));
	}

	
	if(is_file(dirname(__FILE__) . '/debug.php'))
	{
		include_once(dirname(__FILE__) . '/debug.php');
	}
	
	if(is_file(dirname(dirname(__FILE__)) . '/webshop/webshop.php'))
	{
		include(dirname(dirname(__FILE__)) . '/webshop/webshop.php');
	}
	
	
	require_once(dirname(__FILE__) . '/ftp.cls.php');
	require_once(dirname(__FILE__) . '/file.cls.php');
	
	// Create a random code with N digits.
	function parcelcheckout_getRandomCode($iLength = 64)
	{
		$aCharacters = array('a', 'b', 'c', 'd', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

		$sResult = '';

		for($i = 0; $i < $iLength; $i++)
		{
			$sResult .= $aCharacters[rand(0, sizeof($aCharacters) - 1)];
		}

		return $sResult;
	}

	// Retrieve ROOT url of script
	function parcelcheckout_getRootUrl($iParent = 0)
	{
		if(empty($_REQUEST['ROOT_URL']))
		{
			// Detect installation directory based on current URL
			$sRootUrl = '';

			// Detect scheme
			if(isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'ON') === 0))
			{
				$sRootUrl .= 'https://';
			}
			else
			{
				$sRootUrl .= 'http://';
			}

			// Detect domain
			$sRootUrl .= $_SERVER['HTTP_HOST'];

			 // Detect port
			if((strpos($_SERVER['HTTP_HOST'], ':') === false) && isset($_SERVER['SERVER_PORT']))
			{
				if(isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'ON') === 0))
				{
					if((strcmp($_SERVER['SERVER_PORT'], '443') !== 0) && (strcmp($_SERVER['SERVER_PORT'], '80') !== 0))
					{
						$sRootUrl .= ':' . $_SERVER['SERVER_PORT'];
					}
				}
				elseif(strcmp($_SERVER['SERVER_PORT'], '80') !== 0)
				{
					$sRootUrl .= ':' . $_SERVER['SERVER_PORT'];
				}
			}

			$sRootUrl .= '/';

			// Detect path
			if(isset($_SERVER['SCRIPT_NAME']))
			{
				$a = explode('/', substr($_SERVER['SCRIPT_NAME'], 1));

				while(sizeof($a) > ($iParent + 1))
				{
					$sRootUrl .= $a[0] . '/';
					array_shift($a);
				}
			}

			$_REQUEST['ROOT_URL'] = $sRootUrl;
		}

		return $_REQUEST['ROOT_URL'];
	}


	// See if website is in debug mode
	function parcelcheckout_getDebugMode()
	{
		if(is_file(dirname(__FILE__) . '/debug.php'))
		{
			return true;
		}
		
		return false;
	}

	
	
	// Print html to screen
	function parcelcheckout_output($sHtml, $bImage = true)
	{
		global $aParcelCheckout;

		// Detect parcelcheckout folder
		$sRootUrl = parcelcheckout_getRootUrl();

		if(($iStrPos = strpos($sRootUrl, '/parcelcheckout/')) !== false)
		{
			$sRootUrl = substr($sRootUrl, 0, $iStrPos) . '/';
		}

		


		$sOutput = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Parcel Checkout</title>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-15">
		<style type="text/css">

html, body, form, div
{
	margin: 0px;
	padding: 0px;
}

div.wrapper
{
	padding: 50px 0px 0px 0px;
	text-align: center;
}

div.error
{
	margin: 10px 0px 10px 0px;
	padding: 8px 8px 8px 8px;
	text-align: center;

	font-family: Arial;
	font-size: 12px;
	background-color: #FFE0E0;
	border: #FF0000 dashed 1px;
}

p
{
	font-family: Arial;
	font-size: 15px;
}

a
{
	color: #FF0000 !important;
}

td
{
	font-family: Arial;
	font-size: 12px;
}

		</style>

	</head>
	<body>

		<!--

			This Parcel Checkout script is developed by:

			Parcel Checkout

			Support & Information:
			W. http://www.parcel-checkout.nl
			E. info@parcel-checkout.nl
			T. +31614707337

		-->
';

		

		$sOutput .= '
		<div class="wrapper">
' . $sHtml . '

		</div>

	</body>
</html>';

		echo $sOutput;
		exit;
	}
	
	
	// Escape SQL values
	function parcelcheckout_escapeSql($sString, $bEscapeLike = false)
	{
		global $aParcelCheckout;
		$oDatabaseConnection = parcelcheckout_database_setup();

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			$sString = mysqli_real_escape_string($oDatabaseConnection, $sString);
		}
		else
		{
			$sString = mysql_real_escape_string($sString, $oDatabaseConnection);
		}

		if($bEscapeLike)
		{
			$sString = str_replace(array('_', '%'), array('\\_', '\\%'), $sString);
		}

/*
		if($bEscapeLike)
		{
			// _ : represents a single character in a LIKE value
			// % : represents 0 or more character in a LIKE value
			$sString = str_replace(array('\\', '\'', '_', '%'), array('\\\\', '\\\'', '\\_', '\\%'), $sString);
		}
		else
		{
			$sString = str_replace(array('\\', '\''), array('\\\\', '\\\''), $sString);
		}
*/

		return $sString;
	}


	// Escape quoted strings
	function parcelcheckout_escapeQuotes($sString, $bEscapeDoubleQuotes = false)
	{
		if($bEscapeDoubleQuotes)
		{
			$sString = str_replace(array('\\', '"'), array('\\\\', '\\"'), $sString);
		}
		else
		{
			$sString = str_replace(array('\\', '\''), array('\\\\', '\\\''), $sString);
		}

		return $sString;
	}

	function parcelcheckout_replace($sString, $aSearch, $aReplace)
	{
		$sResult = '';

		if(!is_array($aSearch))
		{
			$aSearch = array($aSearch);
		}

		while(strlen($sString))
		{
			$bMatchFound = false;

			foreach($aSearch as $iIndex => $sSearch)
			{
				$iLength = strlen($sSearch);
				$sCompare = substr($sString, 0, $iLength);

				if(strcmp($sCompare, $sSearch) === 0)
				{
					$bMatchFound = true;

					if(is_array($aReplace))
					{
						if(isset($aReplace[$iIndex]))
						{
							$sResult .= $aReplace[$iIndex];
						}
					}
					else
					{
						$sResult .= $aReplace;
					}

					$sString = substr($sString, $iLength);
					break;
				}
			}

			if(!$bMatchFound)
			{
				// Go to next char
				$sResult .= substr($sString, 0, 1);
				$sString = substr($sString, 1);
			}
		}

		return $sResult;
	}



	// Serialize data
	function parcelcheckout_serialize($mData)
	{
		return json_encode($mData);
	}

	// See if data contains serialized strings (possible injection?!)
	function parcelcheckout_serialize_hasInjection($mData)
	{
		if(is_array($mData) || is_object($mData))
		{
			foreach($mData as $k => $v)
			{
				if(parcelcheckout_serialize_hasInjection($v) || parcelcheckout_serialize_hasInjection($k))
				{
					return true;
				}
			}
		}
		elseif(is_string($mData) && strpos($mData, ':'))
		{
			if(preg_match('/([aAoOsS]:[0-9]+:[\{"])/', $mData))
			{
				return true;
			}
		}

		return false;
	}


	// Unserialize data
	function parcelcheckout_unserialize($sString)
	{
		// Recalculate multibyte strings
		// $sString = preg_replace_callback('/s:(\d+):"(.*?)";/', 'parcelcheckout_unserialize_callback', $sString);
		// return unserialize($sString);

		if(empty($sString))
		{
			parcelcheckout_log('String is empty.', __FILE__, __LINE__);
			return array();
			
		}
			
		if(substr($sString, 0, 2) == 'a:') // Treat as SERIALIZED string
		{
			// Recalculate multibyte strings
			if(PHP_VERSION < 7)
			{
				$sString = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $sString);
			}

			return unserialize($sString);
		}
		else // Treat as JSON string
		{
			return json_decode($sString, true);
		}
	}

	function parcelcheckout_unserialize_callback($aMatch)
	{
		return 's:' . strlen($aMatch[2]) .':"' . $aMatch[2] . '";';
	}


	// Replace characters with accents
	function parcelcheckout_escapeAccents($sString)
	{
		return str_replace(array('à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ð', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', '§', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', '€', 'Ð', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', '§', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'Ÿ', chr(96), chr(132), chr(133), chr(145), chr(146), chr(147), chr(148), chr(150), chr(151)), array('a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'ed', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 's', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'EUR', 'ED', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'S', 'U', 'U', 'U', 'U', 'Y', 'Y', '\'', '"', '...', '\'', '\'', '"', '"', '-', '-'), $sString);
	}

	
	// doHttpRequest (Uses curl-library)
	function parcelcheckout_doHttpRequest($sUrl, $sPostData = false, $bRemoveHeaders = false, $iTimeout = 30, $bDebug = false, $aAdditionalHeaders = false)
	{
		global $bIdealcheckoutCurlVerificationError;

		if(!isset($bIdealcheckoutCurlVerificationError))
		{
			$bIdealcheckoutCurlVerificationError = false;
		}

		$aUrl = parse_url($sUrl);

		$bHttps = false;
		$sRequestUrl = '';

		if(in_array($aUrl['scheme'], array('ssl', 'https')))
		{
			$sRequestUrl .= 'https://';
			$bHttps = true;

			if(empty($aUrl['port']))
			{
				$aUrl['port'] = 443;
			}
		}
		else
		{
			$sRequestUrl .= 'http://';

			if(empty($aUrl['port']))
			{
				$aUrl['port'] = 80;
			}
		}

		$sRequestUrl .= $aUrl['host'] . (empty($aUrl['path']) ? '/' : $aUrl['path']) . (empty($aUrl['query']) ? '' : '?' . $aUrl['query']);

		if(is_array($sPostData))
		{
			$sPostData = str_replace(array('%5B', '%5D'), array('[', ']'), http_build_query($sPostData));
		}


		if($bDebug === true)
		{
			$sRequest  = 'Requested URL: ' . $sRequestUrl . "\r\n";
			$sRequest .= 'Portnumber: ' . $aUrl['port'] . "\r\n";

			if($sPostData)
			{
				$sRequest .= 'Posted data: ' . $sPostData . "\r\n";
			}

			echo "\r\n" . "\r\n" . '<h1>SEND DATA:</h1>' . "\r\n" . '<code style="display: block; background: #E0E0E0; border: #000000 solid 1px; padding: 10px;">' . str_replace(array("\n", "\r"), array('<br>' . "\r\n", ''), htmlspecialchars($sRequest)) . '</code>' . "\r\n" . "\r\n";
		}


		$oCurl = curl_init();
		$oCertInfo = false;

		if($bHttps && parcelcheckout_getDebugMode())
		{
			$oCertInfo = tmpfile();

			$sHostName = ($bHttps ? 'https://' : 'http://') . $aUrl['host'] . (empty($aUrl['port']) ? '' : ':' . $aUrl['port']);
			parcelcheckout_getUrlCertificate($sHostName);
		}

		curl_setopt($oCurl, CURLOPT_URL, $sRequestUrl);
		curl_setopt($oCurl, CURLOPT_PORT, $aUrl['port']);

		if($bHttps && ($bIdealcheckoutCurlVerificationError == false))
		{
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);

			if($oCertInfo)
			{
				curl_setopt($oCurl, CURLOPT_STDERR, $oCertInfo);
				curl_setopt($oCurl, CURLOPT_VERBOSE, true);
				curl_setopt($oCurl, CURLOPT_CERTINFO, true);
			}
		}

		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_TIMEOUT, $iTimeout);
		curl_setopt($oCurl, CURLOPT_HEADER, $bRemoveHeaders == false);


		if(substr($sPostData, 0, 1) == '{') // JSON string
		{
			if(!is_array($aAdditionalHeaders))
			{
				$aAdditionalHeaders = array();
			}

			$aAdditionalHeaders[] = 'Content-Type: application/json';
		}


		if(is_array($aAdditionalHeaders) && sizeof($aAdditionalHeaders))
		{
			curl_setopt($oCurl, CURLOPT_HTTPHEADER, $aAdditionalHeaders);
		}


		if($sPostData != false)
		{
			curl_setopt($oCurl, CURLOPT_POST, true);
			curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sPostData);
		}

		$sResponse = curl_exec($oCurl);


		// Capture certificate info
		if($bHttps && $oCertInfo)
		{
			fseek($oCertInfo, 0);

			$sCertInfo = '';

			while($s = fread($oCertInfo, 8192))
			{
				$sCertInfo .= $s;
			}

			fclose($oCertInfo);

			parcelcheckout_log('cURL Retrieved SSL Certificate:' . "\r\n" . $sCertInfo, __FILE__, __LINE__);
		}

		if(parcelcheckout_getDebugMode())
		{
			if(curl_errno($oCurl) && (strpos(curl_error($oCurl), 'self signed certificate') !== false))
			{
				parcelcheckout_log('cURL error #' . curl_errno($oCurl) . ': ' . curl_error($oCurl), __FILE__, __LINE__);
				parcelcheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
				$bIdealcheckoutCurlVerificationError = true;

				curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($oCurl, CURLOPT_VERBOSE, false);
				curl_setopt($oCurl, CURLOPT_CERTINFO, false);

				// cURL Retry
				$sResponse = curl_exec($oCurl);
			}

			if(curl_errno($oCurl) == CURLE_SSL_CACERT)
			{
				parcelcheckout_log('cURL error #' . curl_errno($oCurl) . ': ' . curl_error($oCurl), __FILE__, __LINE__);
				parcelcheckout_log('ca-bundle.crt not installed?!', __FILE__, __LINE__);
				parcelcheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);

				$sBundlePath = dirname(dirname(__FILE__)) . '/certificates/ca-bundle.crt';

				if(is_file($sBundlePath))
				{
					curl_setopt($oCurl, CURLOPT_CAINFO, $sBundlePath);

					// cURL Retry
					$sResponse = curl_exec($oCurl);
				}
			}

			if((curl_errno($oCurl) == CURLE_SSL_PEER_CERTIFICATE) || (curl_errno($oCurl) == 77))
			{
				parcelcheckout_log('cURL error: ' . curl_error($oCurl), __FILE__, __LINE__);
				parcelcheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
				curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);

				// cURL Retry
				$sResponse = curl_exec($oCurl);
			}

			if(curl_errno($oCurl) && (strpos(curl_error($oCurl), 'error setting certificate verify locations') !== false))
			{
				parcelcheckout_log('cURL error: ' . curl_error($oCurl), __FILE__, __LINE__);
				parcelcheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
				curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);

				// cURL Retry
				$sResponse = curl_exec($oCurl);
			}

			if(curl_errno($oCurl) && (strpos(curl_error($oCurl), 'certificate subject name ') !== false) && (strpos(curl_error($oCurl), ' does not match target host') !== false))
			{
				parcelcheckout_log('cURL error: ' . curl_error($oCurl), __FILE__, __LINE__);
				parcelcheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
				curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);

				// cURL Retry
				$sResponse = curl_exec($oCurl);
			}
		}

		if(curl_errno($oCurl))
		{
			parcelcheckout_log('cURL cannot rely on SSL verification. All SSL verification is disabled from this point.', __FILE__, __LINE__);
			parcelcheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
			$bIdealcheckoutCurlVerificationError = true;

			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCurl, CURLOPT_VERBOSE, false);
			curl_setopt($oCurl, CURLOPT_CERTINFO, false);

			// cURL Retry
			$sResponse = curl_exec($oCurl);
		}

		if(curl_errno($oCurl))
		{
			// cURL Failed
			parcelcheckout_log('cURL error: ' . curl_error($oCurl), __FILE__, __LINE__);
			parcelcheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
			parcelcheckout_die('Error while calling url: ' . $sRequestUrl, __FILE__, __LINE__);
		}

		curl_close($oCurl);


		if($bDebug === true)
		{
			echo "\r\n" . "\r\n" . '<h1>RECIEVED DATA:</h1>' . "\r\n" . '<code style="display: block; background: #E0E0E0; border: #000000 solid 1px; padding: 10px;">' . str_replace(array("\n", "\r"), array('<br>' . "\r\n", ''), htmlspecialchars($sResponse)) . '</code>' . "\r\n" . "\r\n";
		}


		if(empty($sResponse))
		{
			return '';
		}

		return $sResponse;
	}


	// Curl verifcation error has occured
	function parcelcheckout_getCurlVerificationError()
	{
		global $bIdealcheckoutCurlVerificationError;

		if(isset($bIdealcheckoutCurlVerificationError))
		{
			return $bIdealcheckoutCurlVerificationError;
		}

		return false;
	}


	// Curl verifcation error has occured
	function parcelcheckout_getUrlCertificate($sUrl, $bDebug = false)
	{
		if($bDebug || parcelcheckout_getDebugMode())
		{
			if(version_compare(PHP_VERSION, '5.3.0') < 0)
			{
				parcelcheckout_log('PHP version is to low for retrieving certificates.', __FILE__, __LINE__);
			}
			else
			{
				if($oStream = @stream_context_create(array('ssl' => array('capture_peer_cert' => true))))
				{
					parcelcheckout_log('Fetching peer certificate for: ' . $sUrl, __FILE__, __LINE__);

					if($oHandle = @fopen($sUrl, 'rb', false, $oStream))
					{
						if(function_exists('stream_context_get_params'))
						{
							$aParams = stream_context_get_params($oHandle);

							if(isset($aParams['options'], $aParams['options']['ssl'], $aParams['options']['ssl']['peer_certificate']))
							{
								$oPeerCertificate = $aParams['options']['ssl']['peer_certificate'];

								$sTempPath = dirname(__DIR__) . '/temp';

								// Save certificate
								if(@openssl_x509_export_to_file($oPeerCertificate, $sTempPath . '/peer.' . time() . '.crt'))
								{
									return true;
								}
							}
							else
							{
								return false;
							}
						}
						else
						{
							parcelcheckout_log('Stream function does not exist on this PHP version.', __FILE__, __LINE__);
						}
					}

					parcelcheckout_log('Peer certificate capture failed for: ' . $sUrl, __FILE__, __LINE__);
				}
			}
		}

		return false;
	}

	
	// Retrieve ROOT url of script
	function parcelcheckout_getRootPath()
	{
		$sRootPath = dirname(dirname(dirname(__FILE__)));

		if(strpos($sRootPath, '\\') !== false)
		{
			$sRootPath .= '\\';
		}
		else
		{
			$sRootPath .= '/';
		}

		return $sRootPath;
	}


	// Retrieve ROOT url of script
	function parcelcheckout_isLocalFile($sFile)
	{
		if(strpos($sFile, '../') === false) // No relative paths..
		{
			$sRootPath = parcelcheckout_getRootPath();
			$sFilePath = substr($sFile, 0, strlen($sRootPath));

			if(strcmp($sFilePath, $sRootPath) === 0)
			{
				if(@is_file($sFile) && @is_readable($sFile))
				{
					return true;
				}
			}
		}

		return false;
	}
	

	// Load database settings
	function parcelcheckout_getDatabaseSettings()
	{
		global $aParcelCheckout;

		$sDatabaseFile = dirname(dirname(__FILE__)) . '/configuration/database.php';
		$sDatabaseError = 'No configuration file available for database.';

		$aSettings = array();

		// Database Server/Host
		$aSettings['host'] = 'localhost';

		// Database Type
		$aSettings['type'] = 'mysqli';

		// Database Username
		$aSettings['user'] = '';

		// Database Password
		$aSettings['pass'] = '';

		// Database Name
		$aSettings['name'] = '';

		// Database Table Prefix (if any)
		$aSettings['prefix'] = '';


		if(parcelcheckout_isLocalFile($sDatabaseFile))
		{
			include($sDatabaseFile);
		}
		else
		{
			parcelcheckout_die('ERROR: ' . $sDatabaseError . ', FILE #1: ' . $sDatabaseFile, __FILE__, __LINE__, false);
		}

		return $aSettings;
	}

	// Load gateway settings
	function parcelcheckout_getCarrierSettings()
	{
		global $aParcelcheckout;

		$sConfigFile = dirname(dirname(__FILE__)) . '/configuration/carrier.php';
		$sConfigError = 'No configuration file available.';

		$aSettings = array();

		if(parcelcheckout_isLocalFile($sConfigFile))
		{
			include($sConfigFile);
		}
		else
		{
			parcelcheckout_die('ERROR: ' . $sConfigError, __FILE__, __LINE__, false);
		}

		$aSettings['TEMP_PATH'] = dirname(dirname(__FILE__)) . '/temp/';

		// Fix gateway path
		if(!empty($aSettings['CARRIER_METHOD']))
		{
			$aSettings['CARRIER_FILE'] = dirname(dirname(__FILE__)) . '/carriers/' . $aSettings['CARRIER_METHOD'] . '/carrier.cls.php';
		}
		elseif(strcasecmp(substr($aSettings['CARRIER_FILE'], 0, 10), '/carriers/') === 0)
		{
			$aSettings['CARRIER_FILE'] = dirname(dirname(__FILE__)) . $aSettings['CARRIER_FILE'];
		}
		elseif(strcasecmp(substr($aSettings['CARRIER_FILE'], 0, 9), 'carriers/') === 0)
		{
			$aSettings['CARRIER_FILE'] = dirname(dirname(__FILE__)) . '/' . $aSettings['CARRIER_FILE'];
		}

		return $aSettings;
	}
	

	function parcelcheckout_log($sText, $sFile = false, $iLine = false, $bDebugCheck = true)
	{
		if(!$bDebugCheck || parcelcheckout_getDebugMode())
		{
			if(is_array($sText) || is_object($sText))
			{
				$sText = var_export($sText, true);
			}

			// Reformat text
			$sText = str_replace("\n", "\n      ", trim($sText));

			$sLog = "\n" . 'TEXT: ' . $sText . "\n";

			if($sFile !== false)
			{
				$sLog .= 'FILE: ' . $sFile . "\n";
			}

			if($sFile !== false)
			{
				$sLog .= 'LINE: ' . $iLine . "\n";
			}

			$sLog .= "\n";


			$sLogFile = dirname(dirname(__FILE__)) . '/temp/' . date('Ymd.His') . '.log';

			if(@file_put_contents($sLogFile, $sLog, FILE_APPEND))
			{
				chmod($sLogFile, 0777);
				return true;
			}
		}

		return false;
	}

	// Streetname 1a => array('Streetname', '1a')
	function parcelcheckout_splitAddress($sAddress)
	{
		$sAddress = trim($sAddress);

		$a = preg_split('/([0-9]+)/', $sAddress, 2, PREG_SPLIT_DELIM_CAPTURE);
		$sStreetName = trim(array_shift($a));
		$sStreetNumber = trim(implode('', $a));

		if(empty($sStreetName)) // American address notation
		{
			$a = preg_split('/([a-zA-Z]{2,})/', $sAddress, 2, PREG_SPLIT_DELIM_CAPTURE);

			$sStreetNumber = trim(implode('', $a));
			$sStreetName = trim(array_shift($a));
		}

		return array($sStreetName, $sStreetNumber);
	}

	function parcelcheckout_database_setup($oDatabaseConnection = false)
	{
		global $aParcelCheckout;

		if(empty($aParcelCheckout['database']['connection']))
		{
			// Find database configuration
			$aParcelCheckout['database'] = parcelcheckout_getDatabaseSettings();

			// Connect to database
			$aParcelCheckout['database']['connection'] = parcelcheckout_database_connect($aParcelCheckout['database']['host'], $aParcelCheckout['database']['user'], $aParcelCheckout['database']['pass']) or parcelcheckout_die('ERROR: Cannot connect to ' . $aParcelCheckout['database']['type'] . ' server. Error in hostname, username and/or password.', __FILE__, __LINE__, false);
			parcelcheckout_database_select_db($aParcelCheckout['database']['connection'], $aParcelCheckout['database']['name']) or parcelcheckout_die('ERROR: Cannot find database `' . $aParcelCheckout['database']['name'] . '` on ' . $aParcelCheckout['database']['host'] . '.', __FILE__, __LINE__, false);
		}

		return $aParcelCheckout['database']['connection'];
	}


	function parcelcheckout_database_query($sQuery, $oDatabaseConnection = false)
	{
		global $aParcelCheckout;

		if($oDatabaseConnection === false)
		{
			$oDatabaseConnection = parcelcheckout_database_setup();
		}

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			return mysqli_query($oDatabaseConnection, $sQuery);
		}
		else
		{
			return mysql_query($sQuery, $oDatabaseConnection);
		}
	}


	function parcelcheckout_database_isRecord($sQuery, $oDatabaseConnection = false)
	{
		$aRecords = parcelcheckout_database_getRecords($sQuery, $oDatabaseConnection);

		if(sizeof($aRecords) > 0)
		{
			return true;
		}

		return false;
	}


	function parcelcheckout_database_getRecord($sQuery, $oDatabaseConnection = false)
	{
		$aRecords = parcelcheckout_database_getRecords($sQuery, $oDatabaseConnection);

		if(sizeof($aRecords) > 0)
		{
			return $aRecords[0];
		}

		return false;
	}


	function parcelcheckout_database_getRecords($sQuery, $oDatabaseConnection = false)
	{
		global $aParcelCheckout;

		if($oDatabaseConnection === false)
		{
			$oDatabaseConnection = parcelcheckout_database_setup();
		}

		$aRecords = array();

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			if($oRecordset = mysqli_query($oDatabaseConnection, $sQuery))
			{
				while($aRecord = mysqli_fetch_assoc($oRecordset))
				{
					$aRecords[] = $aRecord;
				}

				mysqli_free_result($oRecordset);
			}
		}
		else
		{
			if($oRecordset = mysql_query($sQuery, $oDatabaseConnection))
			{
				while($aRecord = mysql_fetch_assoc($oRecordset))
				{
					$aRecords[] = $aRecord;
				}

				mysql_free_result($oRecordset);
			}
		}

		return $aRecords;
	}
	
	function parcelcheckout_database_getValue($sQuery)
	{
		$sColumn = false;
		
		$rs = parcelcheckout_database_getRecord($sQuery);

		if(is_array($rs) && sizeof($rs))
		{
			if($sColumn === false) // Detect first column name
			{
				foreach($rs as $k => $v)
				{
					$sColumn = $k;
					break;
				}
			}

			return $rs[$sColumn];
		}

		return false;
	}
	

	function parcelcheckout_database_execute($sQuery, $oDatabaseConnection = false)
	{
		global $aParcelCheckout;

		if($oDatabaseConnection === false)
		{
			$oDatabaseConnection = parcelcheckout_database_setup();
		}

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			return mysqli_query($oDatabaseConnection, $sQuery);
		}
		else
		{
			return mysql_query($sQuery, $oDatabaseConnection);
		}
	}


	function parcelcheckout_database_error($oDatabaseConnection = false)
	{
		global $aParcelCheckout;

		if($oDatabaseConnection === false)
		{
			$oDatabaseConnection = parcelcheckout_database_setup();
		}

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			return @mysqli_error($oDatabaseConnection);
		}
		else
		{
			return @mysql_error($oDatabaseConnection);
		}
	}


	function parcelcheckout_database_fetch_assoc($oRecordSet)
	{
		global $aParcelCheckout;

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			return mysqli_fetch_assoc($oRecordSet);
		}
		else
		{
			return mysql_fetch_assoc($oRecordSet);
		}
	}


	function parcelcheckout_database_connect($oDatabaseConnection = false)
	{
		global $aParcelCheckout;

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			return mysqli_connect($aParcelCheckout['database']['host'], $aParcelCheckout['database']['user'], $aParcelCheckout['database']['pass']);
		}
		else
		{
			return mysql_connect($aParcelCheckout['database']['host'], $aParcelCheckout['database']['user'], $aParcelCheckout['database']['pass']);
		}
	}


	function parcelcheckout_database_select_db($oDatabaseConnection = false, $sDatabaseName = false)
	{
		global $aParcelCheckout;

		if($oDatabaseConnection === false)
		{
			$oDatabaseConnection = parcelcheckout_database_setup();
		}

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			return mysqli_select_db($oDatabaseConnection, $sDatabaseName);
		}
		else
		{
			return mysql_select_db($sDatabaseName, $oDatabaseConnection);
		}
	}


	function parcelcheckout_database_num_rows($oRecordSet)
	{
		global $aParcelCheckout;

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			return mysqli_num_rows($oRecordSet);
		}
		else
		{
			return mysql_num_rows($oRecordSet);
		}
	}


	function parcelcheckout_database_insert_id($oDatabaseConnection = false)
	{
		global $aParcelCheckout;

		if($oDatabaseConnection === false)
		{
			$oDatabaseConnection = parcelcheckout_database_setup();
		}

		if(!empty($aParcelCheckout['database']['type']) && (strcmp($aParcelCheckout['database']['type'], 'mysqli') === 0))
		{
			return mysqli_insert_id($oDatabaseConnection);
		}
		else
		{
			return mysql_insert_id($oDatabaseConnection);
		}
	}


	function parcelcheckout_arrayToText($aArray, $iWhiteSpace = 0)
	{
		$sData = '';

		if(is_array($aArray) && sizeof($aArray))
		{
			foreach($aArray as $k1 => $v1)
			{
				if(strlen($sData))
				{
					$sData .= "\n";
				}

				$sData .= str_repeat(' ', $iWhiteSpace) . $k1 . ': ';

				if(is_object($v1))
				{
					$sData .= '[' . get_class($v1) . ' object], ';
				}
				elseif(is_array($v1))
				{
					$sData .= "\n" . parcelcheckout_arrayToText($v1, $iWhiteSpace + strlen($k1) + 2) . ', ';
				}
				elseif($v1 === true)
				{
					$sData .= 'TRUE, ';
				}
				elseif($v1 === false)
				{
					$sData .= 'FALSE, ';
				}
				elseif($v1 === null)
				{
					$sData .= 'NULL, ';
				}
				else
				{
					$sData .= $v1 . ', ';
				}
			}

			$sData = substr($sData, 0, -2); // Remove last comma-space
		}

		return $sData;
	}	

	
	// Retrieve a value from a configuration file
	function parcelcheckout_getFileValue($sFileData, $aRegularExpressions, $iIndex = 1)
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
	
	
	function parcelcheckout_createFolder($sName, $sRootFolder = '')
	{
		if((strcmp($sRootFolder, '') === 0))
		{
			if($iOffset = strrpos($sName, '\\'))
			{
				$sRootFolder = substr($sName, 0, $iOffset);
			}
		}

		if($sRootFolder && (strcmp(substr($sName, 0, strlen($sRootFolder)), $sRootFolder) === 0))
		{
			$sName = substr($sName, strlen($sRootFolder));
		}


		$aFolders = explode('/', str_replace('\\', '/', $sName));
		$sFolder = $sRootFolder;

		for($i = 0; $i < sizeof($aFolders); $i++)
		{
			if(strlen($aFolders[$i]) > 0)
			{
				if(!in_array(substr($sFolder, -1, 1), array('/', '\\')))
				{
					$sFolder .= '/';
				}

				$sFolder .= $aFolders[$i];

				if($sFolder && is_dir($sFolder) === false)
				{
					if(@mkdir($sFolder, 0777) && chmod($sFolder, 0777))
					{
						// Folder created
					}
					else
					{
						// error('Cannot create directory: ' . $sFolder, __FILE__, __LINE__);
						return false;
					}
				}
			}
		}

		return true;
	}
	
	
	
	
?>