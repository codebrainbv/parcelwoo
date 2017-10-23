<?php

/*

	Plugin Name: Parcel Checkout - WooCommerce
	Plugin URI: https://www.parcel-checkout.nl
	Description: Parcel Checkout plug-in for Woocommerce and fulfillment 
	Version: 1.0.0
	Author: Parcel Checkout
	Author URI: http://www.parcel-checkout.nl
	Requires at least: 4.4
	Tested up to: 4.8
	Text Domain: woocommerce-parcelcheckout

*/

	if(!defined('ABSPATH')) 
	{
		exit; 
	}

	define('PARCEL_PLUGIN_PATH' , plugin_dir_path(__FILE__));
	define('PARCEL_PLUGIN_URL' , plugin_dir_url(__FILE__));
	define('PLUGIN_DOMAIN', 'woocommerce-parcelcheckout');

	
	// Load order views adjustments
	include_once(PARCEL_PLUGIN_PATH . 'admin/order-views.php');
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	














	// include_once(PARCEL_PLUGIN_PATH . 'includes/woocommerce.cls.php');

	function load_parcelcheckout_scripts()
	{
		// Load jQuery and our JS file
		wp_enqueue_script('jquery');
		// wp_enqueue_script('parcelcheckout-js', plugins_url('/js/parcelcheckout.js', __FILE__), 'jquery');
		// Load our CSS file
		wp_enqueue_style('parcelcheckout-css', PARCEL_PLUGIN_URL . 'css/parcelcheckout.css');
	}

	function dbtable_install() 
	{
		global $wpdb;
		
		$sTableName = $wpdb->prefix . 'parcelcheckout';
		$sCharset = $wpdb->get_charset_collate();
		
		if($wpdb->get_var("SHOW TABLES LIKE '$sTableName'") != $sTableName) 
		{
			$sql = "CREATE TABLE `" . $sTableName . "` (
`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
`last_order_id` VARCHAR(64) DEFAULT NULL, 
PRIMARY KEY (`id`)) $sCharset;";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	}

	function dbmetatable_install() 
	{
		global $wpdb;
	
		$sTableName = $wpdb->prefix . 'parcelcheckout_metadata';
		$sCharset = $wpdb->get_charset_collate();
		
		if($wpdb->get_var("SHOW TABLES LIKE '$sTableName'") != $sTableName) 
		{
			$sql = "CREATE TABLE `" . $sTableName . "` 
`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
`setting` int(8) NOT NULL, 
`key` VARCHAR(255) DEFAULT NULL,
`value` VARCHAR(255) DEFAULT NULL, 
PRIMARY KEY (`id`)) $sCharset;";
		
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	}
	
	
	
	add_action('admin_enqueue_scripts', 'load_parcelcheckout_scripts');
	add_action('wp_enqueue_scripts', 'load_parcelcheckout_scripts');
	register_activation_hook(__FILE__, 'dbtable_install');
	register_activation_hook(__FILE__, 'dbmetatable_install');

	// $oWooCommercePostcode = new WoocommerceParcel();

?>