﻿<?php

	require_once(dirname(dirname(__FILE__)) . '/carrier.core.cls.php');
	
	@ini_set('display_errors', 1);
	@ini_set('display_startup_errors', 1);
	// @error_reporting(E_ALL | E_STRICT);
	@error_reporting(E_ALL);
	
	class Carrier extends CarrierCore
	{
		// Load Carrier settings
		public function __construct()
		{
			$this->init();
		}

		
		// Export orders
		public function doExportOrders()
		{
			global $aParcelCheckout;
			
			date_default_timezone_set('Europe/Amsterdam'); 

			// Find last exported order ID
			$sql = "SELECT `last_order_id` FROM `" . $aParcelCheckout['database']['prefix'] . "orders_batch` ORDER BY `id` DESC LIMIT 1";
			$aLastBatch = parcelcheckout_database_getRecord($sql);
			
			$sLastOrderId = '';
			
			if(sizeof($aLastBatch))
			{
				$sLastOrderId = $aLastBatch['last_order_id'];
			}
			
			// Grab orders, stored in own database
			$sql = "SELECT * FROM `" . $aParcelCheckout['database']['prefix'] . "parcelcheckout_orders` WHERE (`exported` = '0') AND (`order_number` > '" . parcelcheckout_escapeSql($sLastOrderId) . "') ORDER BY `id` ASC";
			$aExportableOrders = parcelcheckout_database_getRecords($sql);
			
			
			if(sizeof($aExportableOrders))
			{
				foreach($aExportableOrders as $aOrder)
				{
	
					$sCurrentTimestamp = time();
	
					$sCurrentDate = date('Y-m-d', $sCurrentTimestamp);
					$sCurrentTime = date('H:i:s', $sCurrentTimestamp);
					
									
					// Generic order data xml part
					$sXml = '<' . '?' . 'xml version="1.0" encoding="UTF-8"' . '?' . '>' . "\n";				
					$sXml .= '<message>';
					$sXml .= '<type>deliveryOrder</type>';
					$sXml .= '<messageNo>' . $aOrder['id'] .'</messageNo>';
					$sXml .= '<date>' . $sCurrentDate . '</date>';
					$sXml .= '<time>' . $sCurrentTime . '</time>';
					$sXml .= '<deliveryOrders>';
					$sXml .= '<deliveryOrder>';
					$sXml .= '<orderNo>' . $aOrder['order_number'] . '</orderNo>';
					$sXml .= '<webOrderNo>' . $aOrder['order_number'] . '</webOrderNo>';
					$sXml .= '<orderDate>' . $aOrder['order_date'] . '</orderDate>';
					$sXml .= '<orderTime>' . $aOrder['order_time'] . '</orderTime>';
					$sXml .= '<customerNo>' . $aOrder['customer_id'] . '</customerNo>';
					$sXml .= '<onlyHomeAddress>true</onlyHomeAddress>';
					$sXml .= '<vendorNo></vendorNo>';
					
					// Shipping data xml part
					$sXml .= '<shipToTitle>' . $aOrder['shipment_title'] . '</shipToTitle>';
					$sXml .= '<shipToFirstName>' . $aOrder['shipment_firstname'] . '</shipToFirstName>';
					$sXml .= '<shipToLastName>' . $aOrder['shipment_surname'] . '</shipToLastName>';
					$sXml .= '<shipToCompanyName>' . $aOrder['shipment_company'] . '</shipToCompanyName>';
					$sXml .= '<shipToBuildingName></shipToBuildingName>';
					$sXml .= '<shipToDepartment></shipToDepartment>';
					$sXml .= '<shipToFloor></shipToFloor>';
					$sXml .= '<shipToDoorcode></shipToDoorcode>';
					$sXml .= '<shipToStreet>' . $aOrder['shipment_address_street'] . '</shipToStreet>';
					$sXml .= '<shipToHouseNo>' . $aOrder['shipment_address_number'] . '</shipToHouseNo>';
					$sXml .= '<shipToAnnex>' . $aOrder['shipment_address_number_extension'] . '</shipToAnnex>';
					$sXml .= '<shipToPostalCode>' . $aOrder['shipment_postalcode'] . '</shipToPostalCode>';
					$sXml .= '<shipToCity>' . $aOrder['shipment_city'] . '</shipToCity>';
					$sXml .= '<shipToCountryCode>' . $aOrder['shipment_country_iso'] . '</shipToCountryCode>';
					$sXml .= '<shipToCountry></shipToCountry>';
					$sXml .= '<shipToPhone>' . $aOrder['shipment_phone'] . '</shipToPhone>';
					$sXml .= '<shipToStreetHouseNrExt></shipToStreetHouseNrExt>';
					$sXml .= '<shipToArea></shipToArea>';
					$sXml .= '<shipToRegion></shipToRegion>';
					$sXml .= '<shipToRemark></shipToRemark>';
					$sXml .= '<shipToEmail>' . $aOrder['shipment_email'] . '</shipToEmail>';
					
					// Invoice data xml part
					$sXml .= '<invoiceToTitle>' . $aOrder['billing_title'] . '</invoiceToTitle>';
					$sXml .= '<invoiceToFirstName>' . strtoupper($aOrder['billing_firstname']) . '.</invoiceToFirstName>';
					$sXml .= '<invoiceToLastName>' . $aOrder['billing_surname'] . '</invoiceToLastName>';
					$sXml .= '<invoiceToCompanyName>' . $aOrder['billing_company'] . '</invoiceToCompanyName>';
					$sXml .= '<invoiceToBuildingName></invoiceToBuildingName>';
					$sXml .= '<invoiceToDepartment></invoiceToDepartment>';
					$sXml .= '<invoiceToFloor></invoiceToFloor>';
					$sXml .= '<invoiceToDoorcode></invoiceToDoorcode>';
					$sXml .= '<invoiceToStreet>' . $aOrder['billing_address_street'] . '</invoiceToStreet>';
					$sXml .= '<invoiceToHouseNo>' . $aOrder['billing_address_number'] . '</invoiceToHouseNo>';
					$sXml .= '<invoiceToAnnex>' . $aOrder['billing_address_number_extension'] . '</invoiceToAnnex>';
					$sXml .= '<invoiceToPostalCode>' . $aOrder['billing_postalcode'] . '</invoiceToPostalCode>';
					$sXml .= '<invoiceToCity>' . $aOrder['billing_city'] . '</invoiceToCity>';
					$sXml .= '<invoiceToCountryCode>' . $aOrder['billing_country_iso'] . '</invoiceToCountryCode>';
					$sXml .= '<invoiceToCountry></invoiceToCountry>';
					$sXml .= '<invoiceToPhone>' . $aOrder['billing_phone'] . '</invoiceToPhone>';
					$sXml .= '<invoiceToStreetHouseNrExt></invoiceToStreetHouseNrExt>';
					$sXml .= '<invoiceToArea></invoiceToArea>';
					$sXml .= '<invoiceToRegion></invoiceToRegion>';
					$sXml .= '<invoiceToRemark></invoiceToRemark>';
					$sXml .= '<invoiceToEmail>' . $aOrder['billing_email'] . '</invoiceToEmail>';
					
					// Shipment method xml part
					$sXml .= '<language>' . empty($aOrder['language'] ? 'NL' : $aOrder['language']) . '</language>';
					$sXml .= '<remboursAmount></remboursAmount>';
					$sXml .= '<shippingAgentCode>' . $aOrder['shipment_agent'] . '</shippingAgentCode>';
					$sXml .= '<shipmentType>' . $aOrder['shipment_type'] . '</shipmentType>';
					$sXml .= '<shipmentProductOption>' . $aOrder['shipment_product_option'] . '</shipmentProductOption>';
					$sXml .= '<shipmentOption>' . $aOrder['shipment_option'] . '</shipmentOption>';
					$sXml .= '<receiverDateOfBirth>' . $aOrder['shipment_dateofbirth'] . '</receiverDateOfBirth>';
					$sXml .= '<IDExpiration>' . $aOrder['shipment_id_expiration'] . '</IDExpiration>';
					$sXml .= '<IDNumber>' . $aOrder['shipment_id_number'] . '</IDNumber>';
					$sXml .= '<IDType>' . $aOrder['shipment_id_type'] . '</IDType>';
					$sXml .= '<requestedDeliveryDate>' . $aOrder['shipment_delivery_date'] . '</requestedDeliveryDate>';
					$sXml .= '<requestedDeliveryTime>' . $aOrder['shipment_delivery_time'] . '</requestedDeliveryTime>';
					$sXml .= '<comment>' . $aOrder['shipment_comment'] . '</comment>';
					
					$sXml .= '<deliveryOrderLines>';
					
					$aProducts = json_decode($aOrder['order_products'], true);
					
					// Each Product in the XML file
					foreach($aProducts as $aProduct)
					{					
						$sXml .= '<deliveryOrderLine>';
						$sXml .= '<itemNo>' . $aProduct['sku'] . '</itemNo>';
						$sXml .= '<itemDescription>' . $aProduct['name'] . '</itemDescription>';
						$sXml .= '<quantity>' . $aProduct['quantity'] . '</quantity>';
						$sXml .= '<singlePriceInclTax>' . $aProduct['total'] . '</singlePriceInclTax>';
						$sXml .= '<GiftWrap></GiftWrap>';
						$sXml .= '<GiftCardInstruction></GiftCardInstruction>';
						$sXml .= '</deliveryOrderLine>';
					}
					
					// Ending
					$sXml .= '</deliveryOrderLines>';
					$sXml .= '</deliveryOrder>';
					$sXml .= '</deliveryOrders>';
					$sXml .= '</message>';

					
					$sFilePrefix = 'ORD';
					$sTimeStamp = date('Ymdhis', $sCurrentTimestamp);
					$sCompleteFileName = $sFilePrefix . $sTimeStamp;
					
					$sLocalFile = PARCELCHECKOUT_PATH . '/temp/export-orders/' . $sCompleteFileName . '.xml';
					
					
					// Write file into: parcelcheckout/temp/export/
					clsFile::write($sLocalFile, $sXml);
					
					
					echo 'All orders have been exported';
					
				}				
			}
			else
			{
				echo 'No orders to export';
			}		
		}
		
		
		// Upload orders to SFTP environment
		public function doUploadOrders()
		{
			global $aParcelCheckout;
		
		
		
		}
		
		public function doExportProducts()
		{
			
			
			global $aParcelCheckout;
			
			date_default_timezone_set('Europe/Amsterdam'); 

		
			// Find last exported order ID
			$sql = "SELECT `id` FROM `" . $aParcelCheckout['database']['prefix'] . "parcelcheckout_products_exports` ORDER BY `id` DESC LIMIT 1";
			$aLastProductExport = parcelcheckout_database_getRecord($sql);
			
			$sLastExportId = '0';
			
			if(sizeof($aLastProductExport))
			{
				$sLastExportId = $aLastProductExport['id'];
			}
			
			
			
			// Grab products, stored in own database
			$sql = "SELECT * FROM `" . $aParcelCheckout['database']['prefix'] . "parcelcheckout_products` WHERE (`exported` = '0') ORDER BY `id` ASC";
			$aExportableProducts = parcelcheckout_database_getRecords($sql);
			
			
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($aExportableProducts);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
			
			
			if(sizeof($aExportableProducts))
			{
				
				$sCurrentTimestamp = time();
	
				$sCurrentDate = date('Y-m-d', $sCurrentTimestamp);
				$sCurrentTime = date('H:i:s', $sCurrentTimestamp);
				
				// Generic product data xml part
				$sXml = '<' . '?' . 'xml version="1.0"' . '?' . '>' . "\n";		
				$sXml .= '<message>';			
				$sXml .= '<type>item</type>';
				$sXml .= '<messageNo>' . $sLastExportId . '</messageNo>';
				$sXml .= '<date>' . $sCurrentDate . '</date>';
				$sXml .= '<time>' . $sCurrentTime . '</time>';
				$sXml .= '<items>';
			
			
				foreach($aExportableProducts as $aProduct)
				{
					$aProductData = json_decode($aProduct['product_data'], true);
					
					$sXml .= '<item>';
					$sXml .= '<itemNo>' . $aProductData['sku'] . '</itemNo>';
					$sXml .= '<description>' . $aProductData['description'] . '</description>';
					$sXml .= '<description2></description2>';
					$sXml .= '<unitOfMeasure>'  . $aProductData['measureunit'] . '</unitOfMeasure>';
					$sXml .= '<height>'  . $aProductData['height'] . '</height>';
					$sXml .= '<width>'  . $aProductData['width'] . '</width>';
					$sXml .= '<depth>'  . $aProductData['depth'] . '</depth>';
					$sXml .= '<weight>'  . $aProductData['weight'] . '</weight>';
					$sXml .= '<vendorItemNo></vendorItemNo>';
					$sXml .= '<eanNo>'  . $aProductData['ean'] . '</eanNo>';
					$sXml .= '<bac>'  . $aProductData['bac'] . '</bac>';
					$sXml .= '<validFrom></validFrom>';
					$sXml .= '<validTo></validTo>';
					$sXml .= '<expiry>'  . $aProductData['expiry'] . '</expiry>';
					$sXml .= '<adr></adr>';
					$sXml .= '<active>'  . ($aProductData['active'] ? 'true' : 'false') . '</active>';
					$sXml .= '<lot></lot>';
					$sXml .= '<sortOrder></sortOrder>';
					$sXml .= '<minStock>'  . $aProductData['min_stock'] . '</minStock>';
					$sXml .= '<maxStock>'  . $aProductData['max_stock'] . '</maxStock>';
					$sXml .= '<retailPrice>'  . $aProductData['retail_price'] . '</retailPrice>';
					$sXml .= '<purchasePrice>'  . $aProductData['purchase_price'] . '<purchasePrice>';
					$sXml .= '<productType></productType>';
					$sXml .= '<defaultMasterProduct>false</defaultMasterProduct>';
					$sXml .= '<hangingStorage>false</hangingStorage>';
					$sXml .= '<backOrder>'  . $aProductData['backorder'] . '</backOrder>';
					$sXml .= '<enriched>'  . $aProductData['enriched'] . '</enriched>';
					$sXml .= '</item>';					
				}
				
				
				$sXml .= '</items>';
				$sXml .= '</message>';
				
			}
			

					
			$sFilePrefix = 'ART';
			$sTimeStamp = date('Ymdhis', $sCurrentTimestamp);
			$sCompleteFileName = $sFilePrefix . $sTimeStamp;
			
			$sLocalFile = PARCELCHECKOUT_PATH . '/temp/export-products/' . $sCompleteFileName . '.xml';
			
			
			// Write file into: parcelcheckout/temp/export/
			clsFile::write($sLocalFile, $sXml);
			
			
			echo 'All orders have been exported';
				
			
			
		}
		
		
	}




?>