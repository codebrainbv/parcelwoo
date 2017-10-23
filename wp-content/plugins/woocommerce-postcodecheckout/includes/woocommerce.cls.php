<?php

	
require_once(dirname(__FILE__) . '/library.php');


class WooCommercePostcode
{
	protected $sOptionName = 'postcodecheckout_addressvalidation_settings';
	protected $sOptionGroup = 'postcodecheckout_addressvalidation_settings';
	protected $sSectionName = 'postcodecheckout_settings_section1';

	public $bEnabled;

	
	public function __construct()
	{
		add_action('admin_menu', array($this, 'add_to_menu'), 99);
		add_action('admin_init', array($this, 'init_settings_fields'));
		
		// load active
		add_action('wp', array($this, 'load_validation'));
		
		// load validation javascript
		add_action('wp_enqueue_scripts', array($this, 'loadValidationJavascript'));
		
		//Handles the ajax call - logged in users
		add_action('wp_ajax_postcodecheckout', array($this, 'doPostcodeValidation'));
		
		// Handles the ajax call - non logged in users
		add_action('wp_ajax_nopriv_postcodecheckout', array($this, 'doPostcodeValidation'));
	}
	
	/**
	 * Add submenu item to WooCommerce
	 */
	public function add_to_menu() 
	{
		$this->page = add_submenu_page('woocommerce', __( 'Postcode Checkout', ADDR_DOMAIN ),	__( 'Postcode Checkout', ADDR_DOMAIN ), 'manage_woocommerce',	'postcode_menu', array($this, 'render_settings'));
	}

	/**
	 ** This initialises the settings field
	 */
	function init_settings_fields()
	{
			
		register_setting(
				'postcodecheckout_addressvalidation_optiongroup', // Option group
				'postcodecheckout_addressvalidation_optionname', // Option name
				array($this, 'sanitize')
		);

		add_settings_section(
				'postcodecheckout_section_setting_id', // ID in the html
		__('Postcode Address Validation Settings', ADDR_DOMAIN), // Title
		array($this, 'print_section_info'), // Callback
				'postcodecheckout-addressvalidation-setting-admin' // Page
		);

		add_settings_field(
				'enable',
		__('Enable/Disable', ADDR_DOMAIN),
		array($this, 'render_enable_setting'),
				'postcodecheckout-addressvalidation-setting-admin',
				'postcodecheckout_section_setting_id'
				);

		add_settings_field(
				'license_key',
		__('License Key Postcode Checkout', ADDR_DOMAIN),
		array($this, 'render_license_key'),
				'postcodecheckout-addressvalidation-setting-admin',
				'postcodecheckout_section_setting_id'
				);			
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info()
	{
		//print 'Enter your settings below:';
	}

	/**
	 * This renders the main page for the plugin
	 */
	public function render_settings()
	{
?>

<div class="wrap">
<?php screen_icon(); ?>
	<h2>Postcode Checkout - Settings</h2>
	<p><i>Configure your Postcode plugin for the use of the Postcode API. <br><br>
	* License Key: This is your Postcode Checkout License Key. If you do not have one, <a target="_blank"  href="http://www.postcode-checkout.nl">sign up for a Postcode Checkout account here</a>.</p>
	
	<form method="post" action="options.php">
	<?php
	// This prints out all hidden setting fields
		settings_fields('postcodecheckout_addressvalidation_optiongroup');
		do_settings_sections('postcodecheckout-addressvalidation-setting-admin');
		submit_button();
	?>
	</form>
</div>

	<?php
	}


	//Render enable button
	function render_enable_setting() 
	{
		$aOptions = get_option('postcodecheckout_addressvalidation_optionname');
			
		if(isset($aOptions['enable']) && $aOptions['enable'] == 'checked')
		{
			$bChecked = "checked='checked'";
		} 
		else 
		{
			$bChecked = '';
		}
?>

	<input type='checkbox' name='postcodecheckout_addressvalidation_optionname[enable]'<?php echo $bChecked ?>'>

<?php
	}

	function render_license_key() 
	{
		$aOptions = get_option('postcodecheckout_addressvalidation_optionname');

		// Is license filled in?
		isset($aOptions['license_key']) ? $sValue = $aOptions['license_key'] : $sValue = '';
?>
<input type='text' id='license_key' name='postcodecheckout_addressvalidation_optionname[license_key]' size="45" 
	value='<?php echo $sValue ?>'><span> <a target="_blank" href="http://www.postcode-checkout.nl">Get your License Key </a></span>
<?php
	}

		 
	// Empty form field
	public function sanitize($aInput)
	{
		//Enabled is checked?
		if(isset($aInput['enable']))
		{
			$aInput['enable'] = "checked";
		} 
		else 
		{
			$aInput['enable'] = "";
		}

		return $aInput;
	}
	
	public function load_validation() 
	{
		global $wp;
		
		if(!$this->validationRequired()) 
		{
			return;
		}

		add_action('wp_footer', array($this, 'showPostcodecheckoutBillingFields'), 0);

		if(WC()->cart->needs_shipping_address() || is_account_page()) 
		{
			add_action('wp_footer', array($this, 'showPostcodecheckoutShippingFields'), 0);
		}
	}
	

	public function loadValidationJavascript()
	{
		global $wp;
		
		$aParams = array(
			'nonce'                					 => wp_create_nonce('woocommerce-postcodecheckout'),
			'postcodecheckout_ajax_url'              => admin_url('admin-ajax.php', 'relative'),
		);
		
		wp_enqueue_script('woocommerce_postcodecheckout', PC_PLUGIN_URL . 'js/postcodecheckout.js', array('jquery', 'woocommerce'), true);
		wp_localize_script('woocommerce_postcodecheckout', 'woocommerce_postcodecheckout', $aParams);

		echo '<style type="text/css">.postcode-checkout-result.form-row { overflow: visible !important; }</style>';

		do_action('postcodecheckout-js', $this);
	}
	
	public function validationRequired() 
	{
		global $wp;
		$bValidationNeeded = false;
		
		// Checkout page or Edit address page, both should have validation?
		if((is_checkout()) || isset($wp->query_vars['edit-address']))
		{
			$bValidationNeeded = true;
		}
		
		// Check if onepage checkout is used/active
		if(function_exists('is_wcopc_checkout'))
		{
			$bValidationNeeded = is_wcopc_checkout();
		}
		
		// Possible admin fix
		if((!is_admin()) && $bValidationNeeded)
		{
			$bValidationNeeded = true;
		}
		
		return apply_filters('postcodecheckout-required', $bValidationNeeded);		
	}		
	
	public function showPostcodecheckoutBillingFields() 
	{
		// Load billing template, same for now. Needs possible changing
		wc_get_template('checkout/form-postcodecheckout.php', array('address_type' => 'billing'), '', PC_PLUGIN_PATH . 'templates/' );
	}
	
	public function showPostcodecheckoutShippingFields() 
	{
		// Load shipping template, same for now. Needs possible changing
		wc_get_template('checkout/form-postcodecheckout.php', array('address_type' => 'shipping'), '', PC_PLUGIN_PATH . 'templates/' );
	}

	// The magic
	public function doPostcodeValidation()
	{
		// Load configuration
		$aOptions = get_option('postcodecheckout_addressvalidation_optionname');
		
		$sPostcodeCheckoutLicense = $aOptions['license_key'];

		$sPostcode = trim(strtoupper(str_replace(' ', '', $_POST['postcode']))); // Postcode
		$sHouseNumber = $_POST['housenumber']; // Housenumber + extension
		
		$sUrl = 'https://www.ideal-checkout.nl/postcode/';
		
		$aRequest['license_key'] = $sPostcodeCheckoutLicense;
		$aRequest['website'] = site_url();
		
		$aRequest['addressdata']['postcode'] = $sPostcode;
		$aRequest['addressdata']['housenumber'] = $sHouseNumber;
		
		$sPostData = json_encode($aRequest);
		
		// echo $sPostData;	

		$sResponse = doHttpRequest_curl($sUrl, $sPostData, true, 30, false, false);
		
		// print_r($sResponse);
		$aResponse = json_decode($sResponse, true);	
			
		if(sizeof($aResponse))
		{
			echo json_encode(array('success' => true, 'result' => $aResponse));
			wp_die();
		}
		else
		{
			wp_send_json_error(); // {"success":false}
		}
	}
	
	// doHttpRequest (Uses curl-library)
	function doHttpRequest_curl($sUrl, $sPostData = false, $bRemoveHeaders = false, $iTimeout = 30, $bDebug = false, $aAdditionalHeaders = false)
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

		if($bHttps && $bDebug)
		{
			$oCertInfo = tmpfile();

			$sHostName = ($bHttps ? 'https://' : 'http://') . $aUrl['host'] . (empty($aUrl['port']) ? '' : ':' . $aUrl['port']);
			getUrlCertificate($sHostName);
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

			postcodecheckout_log('cURL Retrieved SSL Certificate:' . "\r\n" . $sCertInfo, __FILE__, __LINE__);
		}

		if($bDebug)
		{
			if(curl_errno($oCurl) && (strpos(curl_error($oCurl), 'self signed certificate') !== false))
			{
				postcodecheckout_log('cURL error #' . curl_errno($oCurl) . ': ' . curl_error($oCurl), __FILE__, __LINE__);
				postcodecheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
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
				postcodecheckout_log('cURL error #' . curl_errno($oCurl) . ': ' . curl_error($oCurl), __FILE__, __LINE__);
				postcodecheckout_log('ca-bundle.crt not installed?!', __FILE__, __LINE__);
				postcodecheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);

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
				postcodecheckout_log('cURL error: ' . curl_error($oCurl), __FILE__, __LINE__);
				postcodecheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
				curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);

				// cURL Retry
				$sResponse = curl_exec($oCurl);
			}

			if(curl_errno($oCurl) && (strpos(curl_error($oCurl), 'error setting certificate verify locations') !== false))
			{
				postcodecheckout_log('cURL error: ' . curl_error($oCurl), __FILE__, __LINE__);
				postcodecheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
				curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);

				// cURL Retry
				$sResponse = curl_exec($oCurl);
			}

			if(curl_errno($oCurl) && (strpos(curl_error($oCurl), 'certificate subject name ') !== false) && (strpos(curl_error($oCurl), ' does not match target host') !== false))
			{
				postcodecheckout_log('cURL error: ' . curl_error($oCurl), __FILE__, __LINE__);
				postcodecheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
				curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);

				// cURL Retry
				$sResponse = curl_exec($oCurl);
			}
		}

		if(curl_errno($oCurl))
		{
			postcodecheckout_log('cURL cannot rely on SSL verification. All SSL verification is disabled from this point.', __FILE__, __LINE__);
			postcodecheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
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
			postcodecheckout_log('cURL error: ' . curl_error($oCurl), __FILE__, __LINE__);
			postcodecheckout_log(curl_getinfo($oCurl), __FILE__, __LINE__);
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
	function getUrlCertificate($sUrl, $bDebug = false)
	{
		if($bDebug)
		{
			if(version_compare(PHP_VERSION, '5.3.0') < 0)
			{
				postcodecheckout_log('PHP version is to low for retrieving certificates.', __FILE__, __LINE__);
			}
			else
			{
				if($oStream = @stream_context_create(array('ssl' => array('capture_peer_cert' => true))))
				{
					postcodecheckout_log('Fetching peer certificate for: ' . $sUrl, __FILE__, __LINE__);

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
							postcodecheckout_log('Stream function does not exist on this PHP version.', __FILE__, __LINE__);
						}
					}

					postcodecheckout_log('Peer certificate capture failed for: ' . $sUrl, __FILE__, __LINE__);
				}
			}
		}

		return false;
	}


	function postcodecheckout_log($sText, $sFile = false, $iLine = false, $bDebugCheck = true)
	{
		if(!$bDebugCheck)
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


			$sLogFile = dirname((__FILE__)) . '/postcode-temp/' . date('Ymd.His') . '.log';

			if(@file_put_contents($sLogFile, $sLog, FILE_APPEND))
			{
				chmod($sLogFile, 0777);
				return true;
			}
		}

		return false;
	}
}

?>
