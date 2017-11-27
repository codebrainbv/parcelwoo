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

	require_once(ABSPATH . 'parcelcheckout/php/parcelcheckout.php');
	
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
					// Include the method file
					include_once dirname(__FILE__) . '/includes/methods/class-wc-parcelcheckout-pakjegemak.php';
					
					// Load order views overrides
					include_once dirname(__FILE__) . '/includes/overrides/order-views.php';
					
					// Add method to WooCommerce Shipping 
					add_filter('woocommerce_shipping_methods', array($this, 'include_parcelcheckout_methods'));
					
					// Load scripts
					add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
					
					//Handles the ajax call - logged in users
					add_action('wp_ajax_pickuplocation_call', array($this, 'doParcelcheckoutPickup'));
					
					// Handles the ajax call - non logged in users
					add_action('wp_ajax_nopriv_pickuplocation_call', array($this, 'doParcelcheckoutPickup'));
					
					
					// Display pickup location chosen after payment complete
					add_filter('woocommerce_order_shipping_to_display', array($this, 'shipping_to_display_order_frontend'), 10, 2 );

					
					// Hook for adding admin menus
					// add_action('admin_menu', array($this, 'parcelcheckout_admin_menu'), 10, 1);
			
					
					// add_filter( 'woocommerce_attribute_label', array( $this, 'admin_order_location_label' ), 10, 3 );
					
					// add_filter( 'woocommerce_order_shipping_method', array( $this, 'order_shipping_method' ), 10, 2 );
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
					
			
			
			// The magic
			public static function doParcelcheckoutPickup()
			{
				global $wpdb; 
				
				$aCarrierInformation = parcelcheckout_getCarrierSettings();

				$sCountry = $_POST['pc_country'];
				$sCity = $_POST['pc_city'];
				$sPostcode = trim(strtoupper(str_replace(' ', '', $_POST['pc_postcode']))); // Postcode
				
				$sAddress = $_POST['pc_address'];
				
				list($sStreetName, $sStreetNumber) = parcelcheckout_splitAddress($sAddress);
				
				
				$aRequest = array();
				
				$aRequest['City'] = $sCity;
				$aRequest['Country'] = $sCountry;
				$aRequest['PostalCode'] = $sPostcode;
				$aRequest['Street'] = $sStreetName;
				$aRequest['HouseNumber'] = $sStreetNumber;
				
				$sPostData = json_encode($aRequest);
				
				$sApiKey = $aCarrierInformation['API_KEY'];
				$sApiUrl = 'https://api-sandbox.postnl.nl/shipment/v2_1/locations';
				
				$sResponse = parcelcheckout_doHttpRequest($sApiUrl, $sPostData, true, 30, false, array('Authorization: Bearer ' . $sApiKey));
				

				echo json_encode($aRequest);
				wp_die(); 
				
				/*
				
				
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
				
				*/
			}
					
				
			
			// Update the selected pickup location
			function update_pickup_location()
			{
				if(isset($_POST['location']))
				{
					$sValue = $_POST['location'];
					
					WC()->session->set('chosen_location', $sValue);
				}
				
				die();
			}
			
			// Frontend view of the pickup locations
			function shipping_to_display_order_frontend($sString, $oOrder)
			{
				$aShippings = $oOrder->get_items('shipping');

				if(!empty($aShippings))
				{
					foreach($aShippings as $aShipping)
					{
						$aLocations = WC_Parcelcheckout_Pakjegemak::get_available_locations();
						
						if(isset($aShipping['item_meta']['chosen_location']))
						{
							$aChosenLocation = $aShipping['item_meta']['chosen_location'][0];
							
							if(isset($aLocations[$aChosenLocation]))
							{
								if(is_admin())
								{
									return "{$sString} <div class='pickup-location'>{$aLocations[$aChosenLocation]}</div>";
								}
								else
								{
									return "{$sString} <div class='pickup-location'><strong>{$aChosenLocation}</strong>: {$aLocations[ $aChosenLocation ]}</div>";
								}
							}
							
							return $sString;
						}
					}
				}
				
				return $sString;
			}
			/*
			
			// Admin menu script
			function parcelcheckout_admin_menu()
			{				
				add_menu_page('PostNL', 'PostNL', 'manage_options', 'parcelcheckout-about', 'parcelcheckout_about_html', get_bloginfo('wpurl') . '/parcelcheckout/images/parcelcheckout_16x16.png');
				add_submenu_page('parcelcheckout-about', 'Transacties', 'Transacties', 'manage_options', 'parcelcheckout-page', 'parcelcheckout_page');
			}

			function parcelcheckout_about_html()
			{
				if(!current_user_can('manage_options'))
				{
					wp_die( __('You do not have sufficient permissions to access this page.'));
				}
				
				$sHtml = '
<div class="wrap">
	<h2>Parcel Checkout - gegevens etc?</h2>
</div>';
			
				echo $sHtml;
			}
					
			
			function parcelcheckout_page()
			{
	
				if(!current_user_can('manage_options'))
				{
					wp_die( __('You do not have sufficient permissions to access this page.'));
				}
				
				echo 'Im fine!';
			}
			
			
			*/
			
			// Label correction
			function admin_order_location_label($label, $name, $product)
			{
				if($name == 'chosen_location')
				{
					return 'Local';
				}
				
				return $label;
			}			
			
			// Update notification mail with selected Pakjegemak location
			function order_shipping_method($string, $order)
			{
				if(is_admin())
				{
					$shippings = $order->get_items('shipping');
					
					if(!empty($shippings))
					{
						$all_locations = self::get_available_locations();
						
						foreach($shippings as $shipping)
						{
							if(isset($shipping['item_meta']['chosen_location']))
							{
								$address = $all_locations[$shipping['item_meta']['chosen_location'][0]];
								
								return "{$string}: {$shipping['item_meta']['chosen_location'][0]}";
							}
						}
					}
				}
				
				return $string;
			}
			
			// List of possible "Pakje gemak" pickup points
			public static function get_available_locations()
			{
				return apply_filters('pc_pakjegemak_locations_list', array() );
			}
		}
		
		add_action('plugins_loaded', array('WC_Parcelcheckout', 'get_instance'));
	}
	
?>