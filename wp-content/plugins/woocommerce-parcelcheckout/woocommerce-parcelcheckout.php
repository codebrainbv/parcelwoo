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

	
	@ini_set('display_errors', 1);
	@ini_set('display_startup_errors', 1);
	// @error_reporting(E_ALL | E_STRICT);
	@error_reporting(E_ALL);
	

	if(!class_exists('WC_Parcelcheckout'))
	{
		class WC_Parcelcheckout 
		{
			
			// Define constants
			const VERSION = '1.0.0';
		
			protected static $instance = null;			
			protected $ajax_endpoint = 'parcelcheckout_ajax';

			// Get instance
			public static function get_instance() 
			{
				// If there is no instance, create one
				if(null === self::$instance) 
				{
					self::$instance = new self;
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
					include_once dirname( __FILE__ ) . '/includes/methods/class-wc-parcelcheckout-pakjegemak.php';
					
					
					// Load order views overrides
					include_once dirname( __FILE__ ) . '/includes/overrides/order-views.php';
					
					// Add method to WooCommerce Shipping 
					add_filter('woocommerce_shipping_methods', array($this, 'include_parcelcheckout_methods'));
					
					// Load method options in WooCommerce shipping rate.
					add_action('woocommerce_after_shipping_rate', array('WC_Parcelcheckout_Pakjegemak', 'method_options'), 10, 2);
					
					// Load scripts
					add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
					
					// add_action( 'wc_ajax_' . $this->ajax_endpoint, array( $this, 'update_pickup_location' ) );
					
					// add_filter( 'woocommerce_order_shipping_to_display', array( $this, 'shipping_to_display_order_frontend' ), 10, 2 );
					
					// add_filter( 'woocommerce_attribute_label', array( $this, 'admin_order_location_label' ), 10, 3 );
					
					// add_filter( 'woocommerce_order_shipping_method', array( $this, 'order_shipping_method' ), 10, 2 );
				} 
				else 
				{
					add_action('admin_notices', array($this, 'show_woocommerce_message'));
				}
			}

			
			
			
			// Load the plugin text domain for translation.
			public function load_plugin_textdomain() 
			{
				load_plugin_textdomain('woocommerce-parcelcheckout', false, dirname(plugin_basename(__FILE__)) . '/languages/' );
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

					
			// Add js files
			public function enqueue_scripts()
			{
				wp_enqueue_script('jquery');
				// wp_enqueue_script('parcelcheckout-js', plugins_url('/js/parcelcheckout.js', __FILE__), 'jquery');
			
				
				wp_localize_script(
					'woocommerce-parcelcheckout',
					'WCParcelcheckoutPakjegemakParams',
					array(
						'url' => WC_AJAX::get_endpoint($this->ajax_endpoint),
					)
				);
			}
			
			// Update the selected pickup location
			function update_pickup_location()
			{
				if(isset($_POST['location']))
				{
					$value = $_POST['location'];
					
					WC()->session->set('pickup_chosen_location', $value);
				}
				
				die();
			}
			
			// Frontend view of the pickup locations
			function shipping_to_display_order_frontend( $string, $order )
			{
				$shippings = $order->get_items('shipping');
				
				if(!empty($shippings))
				{
					foreach( $shippings as $shipping )
					{
						$locations = WC_Shipping_Multiple_Local_Pickup::get_available_locations();
						
						if(isset($shipping['item_meta']['pickup_chosen_location']))
						{
							$pickup_chosen_location = $shipping['item_meta']['pickup_chosen_location'][0];
							
							if(isset($locations[ $pickup_chosen_location ]))
							{
								if(is_admin())
								{
									return "{$string} <div class='pickup-location'>{$locations[ $pickup_chosen_location ]}</div>";
								}
								else
								{
									return "{$string} <div class='pickup-location'><strong>{$pickup_chosen_location}</strong>: {$locations[ $pickup_chosen_location ]}</div>";
								}
							}
							return $string;
						}
					}
				}
				
				return $string;
			}
			
			// Label correction
			function admin_order_location_label($label, $name, $product)
			{
				if($name == 'pickup_chosen_location')
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
							if(isset($shipping['item_meta']['pickup_chosen_location']))
							{
								$address = $all_locations[$shipping['item_meta']['pickup_chosen_location'][0]];
								
								return "{$string}: {$shipping['item_meta']['pickup_chosen_location'][0]}";
							}
						}
					}
				}
				
				return $string;
			}
			
			// List of possible "Pakje gemak" pickup points
			public static function get_available_locations()
			{
				return apply_filters('multiple_local_pickup_locations_list', array() );
			}
		}
		
		add_action('plugins_loaded', array('WC_Parcelcheckout', 'get_instance'));
	}

	
	

		
		/*
		
		function load_parcelcheckout_scripts()
		{
			// Load jQuery and our JS file
			wp_enqueue_script('jquery');
			// wp_enqueue_script('parcelcheckout-js', plugins_url('/js/parcelcheckout.js', __FILE__), 'jquery');
			
			// Load our CSS file
			// wp_enqueue_style('parcelcheckout-css', PARCEL_PLUGIN_URL . 'css/parcelcheckout.css');
		}

		
		
		add_action('admin_enqueue_scripts', 'load_parcelcheckout_scripts');
		add_action('wp_enqueue_scripts', 'load_parcelcheckout_scripts');
		
		*/
	
	
?>