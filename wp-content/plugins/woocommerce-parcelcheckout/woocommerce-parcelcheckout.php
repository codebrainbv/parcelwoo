<?php

/*
	Plugin Name: Parcel Checkout - WooCommerce
	Plugin URI: https://www.parcel-checkout.nl
	Description: ParcelCheckout plug-in for Woocommerce and PostNL fulfillment 
	Version: 1.0.0
	Author: Parcel Checkout
	Author URI: http://www.parcel-checkout.nl
	Requires at least: 4.4
	Tested up to: 4.8
	Text Domain: woocommerce-parcelcheckout
*/

	// Block output if accessed directly
	if(!defined('ABSPATH')) 
	{
		exit; 
	}

	require_once(ABSPATH . 'parcelcheckout/includes/php/parcelcheckout.php');
	
	@ini_set('display_errors', 1);
	@ini_set('display_startup_errors', 1);
	// @error_reporting(E_ALL | E_STRICT);
	@error_reporting(E_ALL);
	
	define('PARCEL_PLUGIN_PATH', plugin_dir_path(__FILE__));
	define('PARCEL_PLUGIN_URL', plugin_dir_url(__FILE__));

	if(!class_exists('WC_Parcelcheckout'))
	{
		class WC_Parcelcheckout 
		{			
			// Define constants
			const VERSION = '1.0.0';
					
			protected static $instance = null;			

			// Get instance
			public static function get_instance() 
			{
				// If there is no instance, create one
				if(null === self::$instance) 
				{
					self::$instance = new WC_Parcelcheckout;
				}

				return self::$instance;
			}
			
			private function __construct()
			{
				add_action('init', array($this, 'load_plugin_textdomain'), -1);

				// Checks with WooCommerce is installed.
				if(class_exists('WC_Integration'))
				{
					// Include the method files
					include_once dirname(__FILE__) . '/includes/methods/class-wc-parcelcheckout-pakjegemak.php';
										
					// Load order views overrides
					include_once dirname(__FILE__) . '/includes/overrides/order-views.php';
					
					// add_action('init', array($this, 'getParcelcheckoutExport'), 20);
					
					
					// Add method to WooCommerce Shipping 
					add_filter('woocommerce_shipping_methods', array($this, 'include_parcelcheckout_methods'));
					
					// Show admin page WooCommerce PostNL Product Import/Export
					// add_action('admin_menu', array($this, 'showParcelcheckoutSubmenuItem'));
			
			
					// Hook on product save, save on our db as well for replenishment
					add_action('save_post', array($this, 'doParcelcheckoutSaveProductChange'));
					
					// Load scripts
					add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
					
					//Handles the ajax call - logged in users
					add_action('wp_ajax_pickuplocation_call', array($this, 'doParcelcheckoutPickup'));
					
					// Handles the ajax call - non logged in users
					add_action('wp_ajax_nopriv_pickuplocation_call', array($this, 'doParcelcheckoutPickup'));
				} 
				else 
				{
					add_action('admin_notices', array($this, 'show_woocommerce_message'));
				}
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

				
			// Load the plugin text domain for translation.
			public function load_plugin_textdomain() 
			{
				load_plugin_textdomain('woocommerce-parcelcheckout', false, basename(dirname(__FILE__)) . '/languages/' );
			}

			// WooCommerce is not active, show message
			public function show_woocommerce_message() 
			{
				// Shows as an error message. You could add a link to the right page if you wanted.
				showMessage('Parcel Checkout is niet actief omdat WooCommerce niet actief is, activeer WooCommerce om de Parcel Checkout plugin te gebruiken', true);
			}
			
			// Include shipping methods to WooCommerce.
			public function include_parcelcheckout_methods($aMethods)
			{
				// Add our shipping method
				$aMethods['parcelcheckout_pakjegemak'] = 'WC_Parcelcheckout_Pakjegemak';
				
				return $aMethods;
			}
			
			public function doParcelcheckoutSaveProductChange($iPostId)
			{
				$oProduct = wc_get_product($iPostId);

				$aProduct = array();
				
				if(is_object($oProduct))
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
					
					// Product Height
					$aProduct['height'] = $oProduct->get_height();
					
					// Product Width
					$aProduct['width'] = $oProduct->get_width();
					
					// Product Depth
					$aProduct['depth'] = $oProduct->get_length();
					
					// Product Weight
					$aProduct['weight'] = $oProduct->get_weight();
					
					// Product Ean number
					$aProduct['ean'] = $oProduct->get_sku();
					
					// Product BAC
					$aProduct['bac'] = 'A';
					
					// Product Expiry
					$aProduct['expiry'] = 'false';
					
					$sProductStatus = $oProduct->get_status();
					$bProductActive = false;
					
					
					if(strcasecmp($sProductStatus, 'publish') === 0)
					{
						$bProductActive = true;						
					}
					
					
					// Product Active
					$aProduct['active'] = $bProductActive			
					
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
					
					// Product Backorder
					$aProduct['backorder'] = 'false';
					
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
							$sql = "DELETE FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_products` WHERE (`id` = '" . $aRecords['id'] . "')";
							
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
				else
				{
					// Do nothing
					
				}
			}
			
			
					
			/*
			public function showParcelcheckoutSubmenuItem() 
			{
				add_submenu_page('woocommerce', 'PostNL Replenishment', 'PostNL Replenishment', 'manage_options', 'postnl-replenishment', array($this, 'showParcelcheckoutSubmenuCallback')); 				
			}			
					
			public function showParcelcheckoutSubmenuCallback()
			{
				// Include our template files
				include('includes/templates/admin/parcelcheckout-import-products.php');
				$aPageColumns = include('includes/exporter/data/parcelcheckout-post-columns.php');
				include('includes/templates/admin/parcelcheckout-export-products.php');
			}
			
			
			
			public function getParcelcheckoutExport() 
			{
				if(!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'postnl-replenishment')
				{
					if(strcasecmp($_GET['action'], 'export') === 0)
					{					
						include_once('includes/exporter/class-wc-parcelcheckout-exporter.php');
						WC_Parcelcheckout_Exporter::do_export('product');
						   
					}
					else
					{
						// Do nothing
					}
				}
			}
			*/
			
			
			// The magic
			public static function doParcelcheckoutPickup()
			{
				global $wpdb; 
				
				$aCarrierInformation = parcelcheckout_getCarrierSettings();

				$sPostcode = trim(strtoupper(str_replace(' ', '', $_POST['pc_postcode']))); // Postcode
				$sDeliveryOption = $_POST['pc_option'];
				
				
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
		
		add_action('plugins_loaded', array('WC_Parcelcheckout', 'get_instance'));
	}
	
?>