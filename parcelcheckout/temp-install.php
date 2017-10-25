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
`id` int(8) unsigned NOT NULL,
`delivery_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
`delivery_type` varchar(64) DEFAULT NULL,
`order_number` varchar(64) DEFAULT NULL,
`order_date` int(8) unsigned DEFAULT NULL,
`order_time` int(8) DEFAULT NULL,
`order_price` decimal(10,2) unsigned DEFAULT NULL,
`order_vat` decimal(10,2) unsigned DEFAULT NULL,
`order_status` varchar(64) DEFAULT NULL,
`order_products` text DEFAULT NULL,
`order_email_0` varchar(255) DEFAULT NULL,
`invoice_name` varchar(255) DEFAULT NULL,
`invoice_address` varchar(255) DEFAULT NULL,
`invoice_address_street` varchar(64) DEFAULT NULL,
`invoice_address_number` varchar(64) DEFAULT NULL,
`invoice_address_number_extension` varchar(64) DEFAULT NULL,
`invoice_postalcode` varchar(255) DEFAULT NULL,
`invoice_city` varchar(255) DEFAULT NULL,
`invoice_province` varchar(255) DEFAULT NULL,
`invoice_country` varchar(255) DEFAULT NULL,
`shipment_name` varchar(255) DEFAULT NULL,
`shipment_address` varchar(255) DEFAULT NULL,
`shipment_address_street` varchar(64) DEFAULT NULL,
`shipment_address_number` varchar(64) DEFAULT NULL,
`shipment_address_number_extension` varchar(64) DEFAULT NULL,
`shipment_postalcode` varchar(255) DEFAULT NULL,
`shipment_city` varchar(255) DEFAULT NULL,
`shipment_province` varchar(255) DEFAULT NULL,
`shipment_country` varchar(255) DEFAULT NULL,
`shipment_method` varchar(255) DEFAULT NULL,
`shipment_track_and_trace` varchar(255) DEFAULT NULL,
`shipment_status` tinyint(1) DEFAULT '0',
`shipment_method_delivery_date` varchar(64) DEFAULT NULL,
`shipment_method_pickup_date` varchar(64) DEFAULT NULL,
`shipment_method_pickup_datetime` varchar(64) DEFAULT NULL,
`shipment_method_pickup_time` varchar(64) DEFAULT NULL,
`shipment_method_pickup_location` varchar(255) DEFAULT NULL,
`shipment_method_pickup_timestamp` int(11) DEFAULT NULL,
`payment_method_withdraw_bic` varchar(255) DEFAULT NULL,
`enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
`exported` tinyint(1)  unsigned NOT NULL DEFAULT '0',
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
			$query_html .= '<b>Query:</b> ' . $aQueries[$i] . '<br><b>Error:</b> ' . idealcheckout_database_error() . '<br><br><br>';
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