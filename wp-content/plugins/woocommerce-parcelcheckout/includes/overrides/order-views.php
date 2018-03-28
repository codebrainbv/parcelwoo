<?php


	// Add custom column to order list
	add_filter('manage_edit-shop_order_columns', 'addCustomColumn', 11);

	function addCustomColumn($aColumns) 
	{
		$aColumns['parcelcheckout-order'] = __('Exported', 'theme_slug');
		
		return $aColumns;
	}

	// Add content to our column
	add_action('manage_shop_order_posts_custom_column', 'addContentCustomColumn', 10, 2);

	function addContentCustomColumn($aColumn) 
	{
		global $post, $woocommerce, $the_order;
		
		$sOrderId = $the_order->get_id();
		
		if($aColumn == 'parcelcheckout-order') 
		{
			$aDatabaseSettings = parcelcheckout_getDatabaseSettings();

			$sql = "SELECT `exported` FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_orders` WHERE (`order_number` = '" . parcelcheckout_escapeSql($sOrderId) . "') ORDER BY `id` DESC LIMIT 1";
			$aRecord = parcelcheckout_database_getRecord($sql);	
			
			
			if(strcasecmp($aRecord['exported'], '1') === 0) 
			{
				echo ' Yes ';
			} 
			else 
			{
				echo ' No ';
			}
		}
	}

	// Show track and trace + export status in order detail
	add_action('woocommerce_admin_order_data_after_shipping_address', 'showOrderMetaShipping', 10, 1);
	
	function showOrderMetaShipping($oOrder) 
	{
		// Get Track and Trace
		$sTrackTrace = get_post_meta($oOrder->get_id(), 'trackAndTraceCode', true);
		$aDatabaseSettings = parcelcheckout_getDatabaseSettings();
		
		$sql = "SELECT `exported` FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_orders` WHERE (`order_number` = '" . parcelcheckout_escapeSql($oOrder->get_id()) . "') ORDER BY `id` DESC LIMIT 1";
		$aRecord = parcelcheckout_database_getRecord($sql);
		
		$sExported = ' No ';
	
		if(strcasecmp($aRecord['exported'], '1') === 0) 
		{
			$sExported = ' Yes ';
		} 
		else 
		{
			$sExported = ' No ';
		}
	
		echo '<h3><strong>Exported:</strong> </h3> <p>' . ($sExported) . '</p>';

		if(empty($sTrackTrace))
		{
			$sTrackTrace = 'Geen track en trace gevonden';
		}
		
		$aOrderData = $oOrder->get_data();
		$aShippingData = $aOrderData['shipping'];
		$sPostcode = $aShippingData['postcode'];
		$sCountryCode = $aShippingData['country'];
				
		echo '<h3><strong>Track en Trace:</strong> </h3> <p><a href="http://postnl.nl/tracktrace/?B=' . $sTrackTrace . '&P=' . $sPostcode . '&D=' . $sCountryCode . '&T=C" target="_blank">' . $sTrackTrace . '</a></p>';	
		
		
	}



?>