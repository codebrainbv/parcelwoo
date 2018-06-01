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

	if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) 
	{		
		include('includes/overrides/order-views.php');
		include('includes/overrides/product-views.php');
		
		
		include('controller.php');
		

	}
	

?>