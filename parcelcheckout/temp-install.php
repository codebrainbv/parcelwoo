<?php

	global $aParcelCheckout;
	
	
	// Load database settings
	require_once(dirname(__FILE__) . '/php/init.php');
		
	
	$aQueries = array();

	// Add orders batch table
	$aQueries[] = "CREATE TABLE IF NOT EXISTS `" . $aParcelCheckout['database']['prefix'] . "parcelcheckout_orders_batch` (
`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
`batch_id` VARCHAR(64) DEFAULT NULL, 
`last_order_id` VARCHAR(64) DEFAULT NULL, 
`orders` LONGTEXT DEFAULT NULL, 
PRIMARY KEY (`id`));";


	$aQueries[] = "CREATE TABLE IF NOT EXISTS `" . $aParcelCheckout['database']['prefix'] . "parcelcheckout_orders` (
`id` int(8) unsigned NOT NULL AUTO_INCREMENT, 
`order_number` varchar(64) DEFAULT NULL,
`order_date` varchar(64) DEFAULT NULL,
`order_time` varchar(64) DEFAULT NULL,
`customer_id` varchar(64) DEFAULT NULL,
`shipment_title` varchar(32) DEFAULT NULL,
`shipment_firstname` varchar(64) DEFAULT NULL,
`shipment_surname` varchar(64) DEFAULT NULL,
`shipment_company` varchar(255) DEFAULT NULL,
`shipment_address_full` varchar(255) DEFAULT NULL,
`shipment_address_street` varchar(255) DEFAULT NULL,
`shipment_address_number` varchar(32) DEFAULT NULL,
`shipment_address_number_extension` varchar(32) DEFAULT NULL,
`shipment_postalcode` varchar(64) DEFAULT NULL,
`shipment_city` varchar(64) DEFAULT NULL,
`shipment_country_iso` varchar(64) DEFAULT NULL,
`shipment_country` varchar(64) DEFAULT NULL,
`shipment_phone` varchar(64) DEFAULT NULL,
`shipment_email` varchar(64) DEFAULT NULL,
`shipment_agent` varchar(64) DEFAULT NULL,
`shipment_type` varchar(64) DEFAULT NULL,
`shipment_product_option` varchar(64) DEFAULT NULL,
`shipment_option` varchar(64) DEFAULT NULL,
`shipment_dateofbirth` varchar(64) DEFAULT NULL,
`shipment_id_expiration` varchar(64) DEFAULT NULL,
`shipment_id_number` varchar(64) DEFAULT NULL,
`shipment_id_type` varchar(64) DEFAULT NULL,
`shipment_delivery_date` varchar(64) DEFAULT NULL,
`shipment_delivery_time` varchar(64) DEFAULT NULL,
`shipment_comment` text DEFAULT NULL,
`billing_title` varchar(64) DEFAULT NULL,
`billing_firstname` varchar(64) DEFAULT NULL,
`billing_surname` varchar(64) DEFAULT NULL,
`billing_company` varchar(255) DEFAULT NULL,
`billing_address_full` varchar(255) DEFAULT NULL,
`billing_address_street` varchar(64) DEFAULT NULL,
`billing_address_number` varchar(32) DEFAULT NULL,
`billing_address_number_extension` varchar(32) DEFAULT NULL,
`billing_postalcode` varchar(64) DEFAULT NULL,
`billing_city` varchar(64) DEFAULT NULL,
`billing_country_iso` varchar(64) DEFAULT NULL,
`billing_phone` varchar(64) DEFAULT NULL,
`billing_email` varchar(64) DEFAULT NULL,
`language` varchar(32) DEFAULT NULL,
`order_products` TEXT,
`order_status` varchar(64) DEFAULT NULL,
`exported` tinyint(0) DEFAULT NULL,
`enabled` tinyint(1) DEFAULT NULL,
PRIMARY KEY (`id`));";



	$query_html = '';
	
	for($i = 0; $i < sizeof($aQueries); $i++)
	{
		if(parcelcheckout_database_query($aQueries[$i]))
		{
			// Query success
		}
		else
		{
			$query_html .= '<b>Query:</b> ' . $aQueries[$i] . '<br><b>Error:</b> ' . parcelcheckout_database_error() . '<br><br><br>';
		}
	}


	// Validate files & directories
	$sBasePath = dirname(__FILE__);

	$aPaths = array();
	$aPaths[] = array('path' => $sBasePath . '/configuration', 'write' => false);
	$aPaths[] = array('path' => $sBasePath . '/methods', 'write' => false);
	$aPaths[] = array('path' => $sBasePath . '/carriers/carrier.core.cls.php', 'write' => false);
	$aPaths[] = array('path' => $sBasePath . '/images', 'write' => false);

	$aPaths[] = array('path' => $sBasePath . '/temp', 'write' => true);
	$aPaths[] = array('path' => $sBasePath . '/.htaccess', 'write' => false);
	$aPaths[] = array('path' => $sBasePath . '/php/init.php', 'write' => false);
	$aPaths[] = array('path' => $sBasePath . '/index.php', 'write' => false);


	$files_html = '';

	for($i = 0; $i < sizeof($aPaths); $i++)
	{
		if(is_file($aPaths[$i]['path']))
		{
			if($aPaths[$i]['write'] && !is_writable($aPaths[$i]['path']))
			{
				$files_html .= 'File <b>' . $aPaths[$i]['path'] . '</b> not writable.<br>';
			}
		}
		elseif(is_dir($aPaths[$i]['path']))
		{
			if($aPaths[$i]['write'] && !is_writable($aPaths[$i]['path']))
			{
				$files_html .= 'Directory <b>' . $aPaths[$i]['path'] . '</b> not writable.<br>';
			}
		}
		else
		{
			$files_html .= 'File <b>' . $aPaths[$i]['path'] . '</b> does not exist.<br>';
		}
	}


	echo '
<h1>INSTALL LOG</h1>
<p style="color: red;">Please remove this file (FTP: /parcelcheckout/install.php) after installation!</p>

<p>&nbsp;</p>

<h3>Queries:</h3>
<code>' . ($query_html ? $query_html : 'No warnings found') . '</code>

<p>&nbsp;</p>

<h3>Files &amp; Folders:</h3>
<code>' . ($files_html ? $files_html : 'No warnings found') . '</code>

<h3>Server checks:</h3>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td align="left" valign="top" width="150">PHP Version</td>
		<td align="left" valign="top">' . PHP_VERSION . '</td>
	</tr>
	<tr>
		<td align="left" valign="top">OPENSSL Library</td>
		<td align="left" valign="top">' . (function_exists('openssl_sign') && defined('OPENSSL_VERSION_TEXT') ? 'Installed &nbsp; <i>(Version: ' . OPENSSL_VERSION_TEXT . ')</i>' : 'Not installed') . '</td>
	</tr>
	<tr>
		<td align="left" valign="top">FSOCK Library</td>
		<td align="left" valign="top">' . (function_exists('fsockopen') ? 'Installed' : 'Not installed') . '</td>
	</tr>
	<tr>
		<td align="left" valign="top">CURL Library</td>
		<td align="left" valign="top">' . (function_exists('curl_init') ? 'Installed' : 'Not installed') . '</td>
	</tr>
</table>';

?>