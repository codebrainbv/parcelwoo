<?php

	if(!class_exists('Pc_Controller')) 
	{
        class Pc_Controller 
		{
			public function __construct()
			{
				// Load methods
				add_action('woocommerce_shipping_init', array($this, 'includeMethods'));
				add_filter('woocommerce_shipping_methods', array($this, 'loadPakjegemakMethod'));
				add_filter('woocommerce_shipping_methods', array($this, 'loadExtraathomeMethod'));
				add_filter('woocommerce_shipping_methods', array($this, 'loadStandardshipping'));
				add_filter('woocommerce_shipping_methods', array($this, 'loadLetterboxDelivery'));
				add_filter('woocommerce_shipping_methods', array($this, 'loadShipmentEu'));
				add_filter('woocommerce_shipping_methods', array($this, 'loadShipmentRow'));
				add_filter('woocommerce_shipping_methods', array($this, 'loadSignatureOnDelivery'));
				
				// Show form for pickup selection
				add_action('woocommerce_after_order_notes', array($this, 'getPickupLocationHtml'));
				// Check if we have a pickup point
				add_action('woocommerce_after_checkout_validation', array($this, 'checkPickupPoint'));

				// Add track and trace to complete email
				add_action('woocommerce_email_order_meta', array($this, 'addTrackandTraceCompleteMail'));

				// Hook on product save, save on our db as well for replenishment
				add_action('save_post', array($this, 'doParcelcheckoutSaveProductChange'));

				// Load scripts
				add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

				//Handles the ajax call - logged in users
				add_action('wp_ajax_pickuplocation_call', array($this, 'doParcelcheckoutPickup'));

				// Handles the ajax call - non logged in users
				add_action('wp_ajax_nopriv_pickuplocation_call', array($this, 'doParcelcheckoutPickup'));


			}
			
			
			public function includeMethods() 
			{
				if(!class_exists('WC_Parcelcheckout_Pakjegemak'))
				{
					include_once('includes/methods/class-wc-parcelcheckout-pakjegemak.php');
				}

				if(!class_exists('WC_Parcelcheckout_Extraathome'))
				{
					include_once('includes/methods/class-wc-parcelcheckout-extra-at-home.php');
				}
				
				if(!class_exists('WC_Parcelcheckout_Standardshipping'))
				{
					include_once('includes/methods/class-wc-parcelcheckout-standard.php');
				}   
				
				if(!class_exists('WC_Parcelcheckout_Letterbox'))
				{
					include_once('includes/methods/class-wc-parcelcheckout-letterbox.php');
				}
				
				if(!class_exists('WC_Parcelcheckout_ShipmentEu'))
				{
					include_once('includes/methods/class-wc-parcelcheckout-shipment-eu.php');
				} 
				
				if(!class_exists('WC_Parcelcheckout_ShipmentRow'))
				{
					include_once('includes/methods/class-wc-parcelcheckout-shipment-row.php');
				} 
				
				if(!class_exists('WC_Parcelcheckout_SignatureOnDelivery'))
				{
					include_once('includes/methods/class-wc-parcelcheckout-signature-on-delivery.php');
				}		
				
				
			}
				
			public function loadPakjegemakMethod($aMethods)
			{
				$aMethods['parcelcheckout_pakjegemak'] = 'WC_Parcelcheckout_Pakjegemak';
				
				return $aMethods;
			}

			public function loadExtraathomeMethod($aMethods)
			{
				$aMethods['parcelcheckout_extraathome'] = 'WC_Parcelcheckout_Extraathome';
				
				return $aMethods;
			}
			
			public function loadStandardshipping($aMethods)
			{
				$aMethods['parcelcheckout_standardshipping'] = 'WC_Parcelcheckout_Standardshipping';
				
				return $aMethods;
			}
			
			public function loadLetterboxDelivery($aMethods)
			{
				$aMethods['parcelcheckout_letterbox'] = 'WC_Parcelcheckout_Letterbox';
				
				return $aMethods;
			}
			
			public function loadShipmentEu($aMethods)
			{
				$aMethods['parcelcheckout_shipmenteu'] = 'WC_Parcelcheckout_ShipmentEu';
				
				return $aMethods;				
			}

			public function loadShipmentRow($aMethods)
			{
				$aMethods['parcelcheckout_shipmentrow'] = 'WC_Parcelcheckout_ShipmentRow';
				
				return $aMethods;				
			}
			
			public function loadSignatureOnDelivery($aMethods)
			{
				$aMethods['parcelcheckout_signatureondelivery'] = 'WC_Parcelcheckout_SignatureOnDelivery';
				
				return $aMethods;	

			}
			
			
			// Add js files
			public function enqueue_scripts()
			{
				global $wp;

				$aParams = array(
					'nonce' => wp_create_nonce('woocommerce-parcelcheckout'),
					'parcelcheckout_ajax_url' => admin_url('admin-ajax.php', 'relative'),
				);

				wp_enqueue_script('woocommerce_parcelcheckout', PARCEL_PLUGIN_URL . 'js/parcelcheckout.js', array('jquery', 'woocommerce'), true);
				wp_localize_script('woocommerce_parcelcheckout', 'woocommerce_parcelcheckout', $aParams);

				wp_enqueue_script('woocommerce_parcelcheckout_gmaps', site_url() . '/parcelcheckout/includes/js/gmaps.js', array('jquery', 'woocommerce'), true);

				wp_enqueue_script('woocommerce_parcelcheckout_maps', 'https://maps.google.com/maps/api/js?key=AIzaSyDFGd5cCAiDH2-4e5on1cVRKz_hTiWG7RQ');
				wp_enqueue_script('woocommerce_parcelcheckout_maps', 'https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');

				// Load our CSS file
				wp_enqueue_style('parcelcheckout-css', PARCEL_PLUGIN_URL . 'css/parcelcheckout.css');

				do_action('parcelcheckout-js', $this);
			}

			public function getPickupLocationHtml()
			{	
				wc_get_template('checkout/form-parcelcheckout.php', array(), '', PARCEL_PLUGIN_PATH . 'includes/templates/');
			}

			public static function checkPickupPoint()
			{		
				$aShippingMethods = WC()->session->get('chosen_shipping_methods', array());
				$aChosenMethod = $aShippingMethods[0];
				
				$aMethod = explode(':', $aChosenMethod);
				
				if($aMethod[0] == 'parcelcheckout_pakjegemak')
				{
					// Are our fields filled in?
					if(empty($_POST['pickup-postcode']) || empty($_POST['pickup-housenumber']))
					{
						wc_add_notice('Selecteer AUB een PostNL ophaalpunt', 'error');
					}
				}
			}

			public function addTrackandTraceCompleteMail($order)
			{
				// Get order id and Track and Trace
				$sTrackandTrace = get_post_meta($order->get_id(), 'trackAndTraceCode', true);

				// Track and trace found?
				if(strlen($sTrackandTrace) == 0)
				{
					// No track and trace found, do nothing!
				}
				else
				{
					// Track and trace found, add to the email!
					$sTrackCodes = explode(";", $sTrackandTrace);

					echo '<h3><strong>Track & Trace code:</strong> </h3>';

					foreach($sTrackCodes as $sCode)
					{
						$sTrackingUrl = 'https://jouw.postnl.nl/#!/track-en-trace/' . $sCode . '/' . $order->get_shipping_country() . '/' . $order->get_shipping_postcode();
						echo ' <a target="_blank" href=' . $sTrackingUrl . ' >' . $sCode . '</a>';
					}
				}
			}

			public function doParcelcheckoutSaveProductChange($iPostId)
			{
				$oProduct = wc_get_product($iPostId);

				$aProduct = array();

				if(is_object($oProduct))
				{

					if(strcmp($oProduct->get_name(), 'auto-draft') === 0)
					{
						return false;
					}

					if(empty($oProduct->get_sku()) || empty($oProduct->get_description()))
					{
						return false;
					}
					else
					{

						// Product ID
						$aProduct['id'] = $oProduct->get_id();

						// Product SKU
						$aProduct['sku'] = $oProduct->get_sku();

						// Product name
						$aProduct['name'] = $oProduct->get_name();

						// Product Description
						$aProduct['description'] = $oProduct->get_description();

						// Product Measure unit
						$aProduct['measureunit'] = 'ST';

						if(!empty($oProduct->get_height()))
						{
							// Product Height
							$aProduct['height'] = $oProduct->get_height();
						}
						else
						{
							// Product Height
							$aProduct['height'] = '1';
						}

						if(!empty($oProduct->get_width()))
						{
							// Product Width
							$aProduct['width'] = $oProduct->get_width();
						}
						else
						{
							// Product Width
							$aProduct['width'] = '1';
						}

						if(!empty($oProduct->get_length()))
						{
							// Product Depth
							$aProduct['depth'] = $oProduct->get_length();
						}
						else
						{
							// Product Depth
							$aProduct['depth'] = '1';
						}

						if(!empty($oProduct->get_weight()))
						{
							// Product Weight
							$aProduct['weight'] = $oProduct->get_weight();
						}
						else
						{
							// Product Weight
							$aProduct['weight'] = '1';
						}


						// Product Ean number
						$sMetaEanCode = get_post_meta($iPostId, 'pc_product_ean', true);

						if(!empty($sMetaEanCode))
						{
							$aProduct['ean'] = $sMetaEanCode;
						}
						else
						{
							$aProduct['ean'] = $oProduct->get_sku();
						}

						// Product BAC
						$aProduct['bac'] = 'A';

						// Product Expiry
						$aProduct['expiry'] = 'false';

						$sProductStatus = $oProduct->get_status();
						$bProductActive = 'false';

						if(strcasecmp($sProductStatus, 'publish') === 0)
						{
							$bProductActive = 'true';
						}
						else
						{
							$bProductActive = 'false';
						}

						// Product Active
						$aProduct['active'] = $bProductActive;

						// Product Min stock
						$aProduct['min_stock'] = '1';

						// Product Max stock
						$aProduct['max_stock'] = '1000';

						// Product Retail Price
						$aProduct['retail_price'] = $oProduct->get_price();

						// Product Purchase price
						$aProduct['purchase_price'] = $oProduct->get_regular_price();

						// Product hanging storage
						$aProduct['hanging_storage'] = 'false';
						
						$sAllowBackorders = $oProduct->get_backorders();
						
						if(strcasecmp($sAllowBackorders, 'yes') === 0)
						{
							// Product Backorder
							$aProduct['backorder'] = 'true';
						}
						else
						{
							// Product Backorder
							$aProduct['backorder'] = 'false';						
						}
						
						// Product Enriched
						$aProduct['enriched'] = 'true';


						$sProductData = json_encode($aProduct);
						$aDatabaseSettings = parcelcheckout_getDatabaseSettings();

						$sql = "SELECT `id` FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_products` WHERE (`product_id` = '" . parcelcheckout_escapeSql($aProduct['id']) . "') AND (`exported` = '0') ORDER BY `id` DESC";


						if($aRecords = parcelcheckout_database_getRecords($sql))
						{
							if(sizeof($aRecords) > 1)
							{
								foreach($aRecords as $k => $v)
								{
									// Delete previous iteration of the product
									$sql = "DELETE FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_products` WHERE (`id` = '" . $v['id'] . "')";

									parcelcheckout_database_query($sql);
								}
							}
							else
							{
								// Delete previous iteration of the product
								$sql = "DELETE FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_products` WHERE (`id` = '" . $aRecords[0]['id'] . "')";
								
								parcelcheckout_database_query($sql);
							}
						}

						// Insert into database, this way we can use a cronjob to automaticly send this to PostNL
						$sql = "INSERT INTO `" . $aDatabaseSettings['prefix'] . "parcelcheckout_products` SET
		`id` = NULL,
		`product_id` = '" . parcelcheckout_escapeSql($aProduct['id']) . "',
		`product_data` = '" . $sProductData . "',
		`exported` = '0';";
						parcelcheckout_database_query($sql);
					}

				}
				else
				{
					// Do nothing

				}
			}	


			// The location magic
			public static function doParcelcheckoutPickup()
			{
				global $wpdb;

				$aCarrierInformation = parcelcheckout_getCarrierSettings();

				$sPostcode = trim(strtoupper(str_replace(' ', '', $_POST['pc_postcode']))); // Postcode
				$sDeliveryOption = 'PG';


				if(!empty($sPostcode))
				{

					$sApiKey = $aCarrierInformation['API_KEY'];
					$sApiUrl = 'https://api-sandbox.postnl.nl/shipment/v2_1/locations/nearest?CountryCode=NL&PostalCode=' . $sPostcode . '&DeliveryOptions=' . $sDeliveryOption;

					$sResponse = parcelcheckout_doHttpRequest($sApiUrl, '', true, 30, false, array('apikey: ' . $sApiKey));

					$aResponse = json_decode($sResponse, true);

					$aLocationOptions = array();

					if(sizeof($aResponse))
					{
						foreach($aResponse['GetLocationsResult']['ResponseLocation'] as $aLocation)
						{
							$aLocationOptions[] = $aLocation;
						}

						echo json_encode(array('success' => true, 'result' => $aLocationOptions));
						wp_die();
					}
					else
					{
						wp_send_json_error(); // {"success":false}
					}
				}
				else
				{
					wp_send_json_error(); // {"success":false}
				}
			}
		}
		
        new Pc_Controller();
    }




?>