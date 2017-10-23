<?php
/*
Plugin Name: Parcel Checkout - WooCommerce
Plugin URI: https://www.parcel-checkout.nlDescription: Parcel Checkout plug-in for Woocommerce and fulfillment 
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
define('PC_PLUGIN_PATH' , plugin_dir_path(__FILE__));
define('PC_PLUGIN_URL' , plugin_dir_url(__FILE__));
define('ADDR_DOMAIN', 'woocommerce-parcelcheckout');
include_once(PC_PLUGIN_PATH . 'includes/woocommerce.cls.php');
function load_parcelcheckout_scripts()
{
	// Load jQuery and our JS file
	wp_enqueue_script('jquery');
	// wp_enqueue_script('parcelcheckout-js', plugins_url('/js/parcelcheckout.js', __FILE__), 'jquery');
	// Load our CSS file
	wp_enqueue_style('parcelcheckout-css', PC_PLUGIN_URL . 'css/parcelcheckout.css');
}
add_action('admin_enqueue_scripts', 'load_parcelcheckout_scripts');
add_action('wp_enqueue_scripts', 'load_parcelcheckout_scripts');
$oWooCommercePostcode = new WooCommercePostcode();

?>