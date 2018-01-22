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

			if($aRecord) 
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
		// $sTrackTrace  = get_post_meta($oOrder->get_id(), 'trackTraceCode', true);
		$aDatabaseSettings = parcelcheckout_getDatabaseSettings();
		
		$sql = "SELECT `exported` FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_orders` WHERE (`order_number` = '" . parcelcheckout_escapeSql($oOrder->get_id()) . "') ORDER BY `id` DESC LIMIT 1";
		$aRecord = parcelcheckout_database_getRecord($sql);
		
		$sExported = ' No ';
	
		if($aRecord) 
		{
			$sExported = ' Yes ';
		} 
		else 
		{
			$sExported = ' No ';
		}
	
	
		echo '<h3><strong>Exported:</strong> </h3> <p>' . ($sExported) . '</p>';
	
	/*
	
		if(empty($sTrackTrace))
		{
			echo 'No track and trace found';
		}
		else
		{
			// Find track and trace code 
			
			
			print_r($sTrackTrace);
			$aCodes = '';
			
			
			
		}
		
		*/
		
	}



?>