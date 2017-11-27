<?php

	require_once(dirname(dirname(__FILE__)) . '/carrier.core.cls.php');
	

	class Carrier extends CarrierCore
	{
		// Load Carrier settings
		public function __construct()
		{
			$this->init();
		}

		
		// Export orders
		public function doExport()
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
			
			// Grab orders and products, store in own database
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
					
					$sFile = PARCELCHECKOUT_PATH . '/temp/export/' . $sCompleteFileName . '.xml';
					
					
					// Write file into: parcelcheckout/temp/export/
					clsFile::write($sFile, $sXml);
					
					
					// Transfer xml file with SFTP
					$sHostname = $this->aSettings['SFTP_HOST'];
					$sUsername = $this->aSettings['SFTP_USER'];
					$sKeyName = $this->aSettings['SFTP_KEY'];
					
					// Test FTP ROOT & PATH
					$oFtp = new clsFtp();
					
					if($oFtp->connect($sHostname, $sUsername, $sKeyName, 22, 0, false))
					{
						// $sLocalPath = IDEALCHECKOUT_PATH;
						// $sRemotePath = $oFtp->findRemotePath($sLocalPath);
						$oFtp->getFilesAndFolders();
						
						
					
					}
					else
					{
						parcelcheckout_log('FTP Connection failed, please check the credentials', __FILE__, __LINE__);
					}
					
					
					/*
					
					$aFormValues['ftp_host'] = (empty($_POST['ftp_host']) ? '' : $_POST['ftp_host']);
					$aFormValues['ftp_port'] = (empty($_POST['ftp_port']) ? 0 : intval($_POST['ftp_port']));
					$aFormValues['ftp_user'] = (empty($_POST['ftp_user']) ? '' : $_POST['ftp_user']);
					$aFormValues['ftp_pass'] = (empty($_POST['ftp_pass']) ? '' : $_POST['ftp_pass']);
					$aFormValues['ftp_passive'] = 0;
						
					*/
						
					
					
					
					$sRemotePath = 'public_html/Order';
					
					
echo $sXml;
exit;					
					
					
					
					

			
					
				}
			}
			else
			{
				echo 'No orders to export';
				
			}
			
			
			
			
			
			/*
					if($bExportAndPrint)
					{
						$sFile = FRONTEND_PATH . '/temp/temp/parcelware.csv';
						clsFile::write($sFile, $sData);

						$aFilesToZip[] = $sFile;

						clsFile::toZip($aFilesToZip, 'export.' . date('Ymd.His', NOW) . '.zip');
					}
					else
					{
						clsFile::output($sData, 'parcelware.' . date('Ymd.His', NOW) . '.csv');
					}
				}
			}


			
			
			
			*/
			
			
			
		
		}
		
		
		
		
		
	}




?>