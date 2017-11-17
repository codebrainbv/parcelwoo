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
					
					$sXml = '<?xml version="1.0" encoding="UTF-8" ?>';					
					$sXml .= '';
					
					
					
					/*
					
					
					
		<?xml version="1.0" encoding="UTF-8"?>
<message>
 <type>deliveryOrder</type>
 <messageNo>000088713</messageNo>
 <date>2015-12-17</date>
 <time>11:00:10</time>
 <deliveryOrders>
 <deliveryOrder>
 <orderNo>WO-5130088</orderNo>
 <webOrderNo>ORD01634</webOrderNo>
 <orderDate>2015-12-17</orderDate>
 <orderTime>10:00:49</orderTime>
 <customerNo>DEB-S-0148849</customerNo>
 <onlyHomeAddress>false</onlyHomeAddress>
 <vendorNo/>
 <shipToTitle>Mrs.</shipToTitle>
 <shipToFirstName>Mia</shipToFirstName>
 <shipToLastName>Bamelissa</shipToLastName>
 <shipToCompanyName>PostNL Fulfilment</shipToCompanyName>
 <shipToBuildingName>Block A</shipToBuildingName>
 <shipToDepartment>IT</shipToDepartment>
 <shipToFloor>2th</shipToFloor>
 <shipToDoorcode>A-1342</shipToDoorcode>
 <shipToStreet>Kaagschip</shipToStreet>
9
PostNL Fulfilment
Interfacing Integrator
 <shipToHouseNo>14</shipToHouseNo>
 <shipToAnnex>Bis</shipToAnnex>
 <shipToPostalCode>3991 CS</shipToPostalCode>
 <shipToCity>Houten</shipToCity>
 <shipToCountryCode>NL</shipToCountryCode>
 <shipToCountry>Netherlands</shipToCountry>
 <shipToPhone>+31 306660211</shipToPhone>
 <shipToStreetHouseNrExt/>
 <shipToArea>Business park De Meerpaal</shipToArea>
 <shipToRegion>Utrecht</shipToRegion>
 <shipToRemark>Use the second door to the left</shipToRemark>
 <shipToEmail>postnl_ecs@postnl.nl</shipToEmail>
 <invoiceToTitle>Mr.</invoiceToTitle>
 <invoiceToFirstName>P.</invoiceToFirstName>
 <invoiceToLastName>Schayk</invoiceToLastName>
 <invoiceToCompanyName>PostNL</invoiceToCompanyName>
 <invoiceToBuildingName>De Groene Toren</invoiceToBuildingName>
 <invoiceToDepartment>Finance</invoiceToDepartment>
 <invoiceToFloor>10th Floor</invoiceToFloor>
 <invoiceToDoorcode>B1</invoiceToDoorcode>
 <invoiceToStreet>Pr. Beatrixlaan</invoiceToStreet>
 <invoiceToHouseNo>23</invoiceToHouseNo>
 <invoiceToAnnex/>
 <invoiceToPostalCode>2595 AK</invoiceToPostalCode>
 <invoiceToCity>Den Haag</invoiceToCity>
 <invoiceToCountryCode>NL</invoiceToCountryCode>
 <invoiceToCountry>Netherlands</invoiceToCountry>
 <invoiceToPhone>+31 88 868 6161</invoiceToPhone>
 <invoiceToStreetHouseNrExt/>
 <invoiceToArea>Centrum</invoiceToArea>
 <invoiceToRegion>Zuid-Holland</invoiceToRegion>
 <invoiceToRemark/>
 <invoiceToEmail>postnl_ecs@postnl.nl</invoiceToEmail>
 <language>NL</language>
 <remboursAmount/>
 <shippingAgentCode>03085</shippingAgentCode>
 <shipmentType>Commercial Goods</shipmentType>
 <shipmentProductOption>118</shipmentProductOption>
 <shipmentOption>006</shipmentOption>
 <receiverDateOfBirth>17-05-1970</receiverDateOfBirth>
 <IDExpiration>19-03-2019</IDExpiration>
 <IDNumber>KYNB7P9F1</IDNumber>
 <IDType>01</IDType>
 <requestedDeliveryDate>2015-12-21</requestedDeliveryDate>
 <requestedDeliveryTime/>
 <comment>Comment for order</comment>
 <deliveryOrderLines>
10
PostNL Fulfilment
Interfacing Integrator
 <deliveryOrderLine>
 <itemNo>B425414</itemNo>
 <itemDescription>TEST Product 1</itemDescription>
 <quantity>1</quantity>
 <singlePriceInclTax>9.95</singlePriceInclTax>
 </deliveryOrderLine>
 <deliveryOrderLine>
 <itemNo>B425499</itemNo>
 <itemDescription>TEST Product 2</itemDescription>
 <quantity>2</quantity>
 <singlePriceInclTax>30.99</singlePriceInclTax>
<GiftWrap>1</GiftWrap>
< GiftCardInstruction > kado:neutraal </GiftCardInstruction>
 </deliveryOrderLine>
 </deliveryOrderLines>
 </deliveryOrder>
 </deliveryOrders>
</message>
*/
					
					
					
					
					
					
					
					
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($aOrder);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
exit;
			
					
				}
			}
			else
			{
				echo 'No orders to export';
				
			}
			
			
			
			
			
			
			// Use own database to build XML file and place on temp folder
			
			
			$sTimeStamp = date('YmdHis');
	
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($sTimeStamp);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
exit;		
			
			
			/*
			
			$sExportFile = 'ORDyyyymmddhhmmss.xml';
			
			
			
			$localFile  = 'test.xml';
			$remoteFile = 'public_html/ecs/test.xml';
			$port       = 22;
		
		
		*/
			
			
			
			
			
			
			
			
			/*
			
			$sql = "SELECT * FROM `` WHERE (`exported` = '0') ORDER BY `id` DESC";
			
			
			
			if(sizeof($aOrderIds))
			{
				$sql = "SELECT * FROM `#_parcelcheckout_orders` WHERE `id` IN ('" . implode("', '", $aOrderIds) . "') ORDER BY `id` ASC;";
				$rsOrders = clsDatabase::getRecords($sql);




				$rsOrders = array();
				$rsOrders[] = array('id' => 1, 'contact_name' => 'Test 1', 'contact_address' => 'Jan Steenstraat 175', 'contact_postalcode' => '7944TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '+31614707337', 'contact_email' => 'test1@php-solutions.nl', 'order_number' => 'WEB201100001');
				$rsOrders[] = array('id' => 2, 'contact_name' => 'Test 2', 'contact_address' => 'Jan Steenstraat 175 appartement 5', 'contact_postalcode' => '7944TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '0614707337', 'contact_email' => 'test2@php-solutions.nl', 'order_number' => 'WEB201100002');
				$rsOrders[] = array('id' => 3, 'contact_name' => 'Test 3', 'contact_address' => 'Jan Steenstraat 175/5', 'contact_postalcode' => '7944 TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '+31 (0) 614707337', 'contact_email' => 'test3@php-solutions.nl', 'order_number' => 'WEB201100003');
				$rsOrders[] = array('id' => 4, 'contact_name' => 'Test 4', 'contact_address' => 'Jan Steenstraat 175-5', 'contact_postalcode' => '7944TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '+31 6 14 707 337', 'contact_email' => 'test4@php-solutions.nl', 'order_number' => 'WEB201100004');
				$rsOrders[] = array('id' => 5, 'contact_name' => 'Test 5', 'contact_address' => 'Jan Steenstraat 175b', 'contact_postalcode' => '7944 TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '0031614707337', 'contact_email' => 'test5@php-solutions.nl', 'order_number' => 'WEB201100005');
	

				if(false) //sizeof($rsOrders))
				{
					$sData = 'ShipmentRefNo;CompanyName;LastName;FirstName;Country;Street;HouseNumber;HouseNumberExt;Zipcode;City;ProductCode;COD;Insurance;Telephone;Email;';

					foreach($rsOrders as $aOrder)
					{
						$sReferenceNr = $aOrder['id'];
						$sCompanyName = '';

						if(strcasecmp($aOrder['shipment_enabled'], '1') === 0)
						{
							$sFirstname = '';
							$sLastname = '';

							$aName = explode(' ', str_replace(';', '-', $aOrder['shipment_name']));

							$sLastname = array_pop($aName);
							$sFirstname = implode(' ', $aName);

							$sStreet = '';
							$sHomeNr = '';
							$sHomeNrExt = '';


							$aAddress = explode(' ', str_replace(';', '-', $aOrder['shipment_address']));

							while(sizeof($aAddress))
							{
								$a = array_shift($aAddress);

								if(preg_match('/^[0-9]+/', $a)) // Find number
								{
									$sHomeNr .= intval($a);
									$sHomeNrExt .= trim(substr($a, strlen($sHomeNr) + 1));

									while(sizeof($aAddress))
									{
										$a = array_shift($aAddress);
										$sHomeNrExt .= (empty($sHomeNrExt) ? '' : ' ') . $a;
									}
								}
								else
								{
									$sStreet .= (empty($sStreet) ? '' : ' ') . $a;
								}
							}

							$sZIP = $aOrder['shipment_postalcode'];
							$sCity = $aOrder['shipment_city'];
							$sCountry = $aOrder['shipment_country'];
							$sTelephone = $aOrder['contact_phone'];
							$sEmail = $aOrder['contact_email'];

						}
						elseif(strcasecmp($aOrder['company_enabled'], '1') === 0)
						{
							$sFirstname = '';
							$sLastname = '';
							$sCompanyName = $aOrder['company_name'];

							$aName = explode(' ', str_replace(';', '-', $aOrder['company_name']));

							$sLastname = array_pop($aName);
							$sFirstname = implode(' ', $aName);

							$sStreet = '';
							$sHomeNr = '';
							$sHomeNrExt = '';


							$aAddress = explode(' ', str_replace(';', '-', $aOrder['company_address']));

							while(sizeof($aAddress))
							{
								$a = array_shift($aAddress);

								if(preg_match('/^[0-9]+/', $a)) // Find number
								{
									$sHomeNr .= intval($a);
									$sHomeNrExt .= trim(substr($a, strlen($sHomeNr) + 1));

									while(sizeof($aAddress))
									{
										$a = array_shift($aAddress);
										$sHomeNrExt .= (empty($sHomeNrExt) ? '' : ' ') . $a;
									}
								}
								else
								{
									$sStreet .= (empty($sStreet) ? '' : ' ') . $a;
								}
							}

							$sZIP = $aOrder['company_postalcode'];
							$sCity = $aOrder['company_city'];
							$sCountry = $aOrder['company_country'];
							$sTelephone = $aOrder['company_contact_phone'];
							$sEmail = $aOrder['company_contact_email'];

						}
						else
						{
							$sFirstname = '';
							$sLastname = '';

							$aName = explode(' ', str_replace(';', '-', $aOrder['contact_name']));

							$sLastname = array_pop($aName);
							$sFirstname = implode(' ', $aName);

							$sStreet = '';
							$sHomeNr = '';
							$sHomeNrExt = '';

							$aAddress = explode(' ', str_replace(';', '-', $aOrder['contact_address']));

							while(sizeof($aAddress))
							{
								$a = array_shift($aAddress);

								if(preg_match('/^[0-9]+/', $a)) // Find number
								{
									$sHomeNr .= intval($a);
									$sHomeNrExt .= trim(substr($a, strlen($sHomeNr) + 1));

									while(sizeof($aAddress))
									{
										$a = array_shift($aAddress);
										$sHomeNrExt .= (empty($sHomeNrExt) ? '' : ' ') . $a;
									}
								}
								else
								{
									$sStreet .= (empty($sStreet) ? '' : ' ') . $a;
								}
							}

							$sZIP = $aOrder['contact_postalcode'];
							$sCity = $aOrder['contact_city'];
							$sCountry = $aOrder['contact_country'];
							$sTelephone = $aOrder['contact_phone'];
							$sEmail = $aOrder['contact_email'];
						}

	
	Productcode
	3085 Standaard Pakket
	3089 Pakket, handtekening voor ontvangst, alleen huisadres
	3189 Pakket, handtekening voor ontvangst, burenbelevering toegestaan
	3610 Pallet
	3630 Stukgoed
	4940 Zending binnen Europa (to B)
	4944 Zending binnen Europa (to C)

	

						// Set default to 3085
						$sProductCode = '3085';



						$sData .= CRLF . $sReferenceNr . ';' . $sCompanyName . ';' . $sLastname . ';' . $sFirstname . ';' . $sCountry . ';' . $sStreet . ';' . $sHomeNr . ';' . $sHomeNrExt . ';' . $sZIP . ';' . $sCity . ';' . $sProductCode . ';;;' . $sTelephone . ';' . $sEmail . ';';


					

						// Set order_status to "2"
						$sql = "UPDATE `#_module_" . MODULE_NAME . "_orders` SET `order_status` = '2', `shipment_status` = '2' WHERE (`id` = '" . $aOrder['id'] . "') LIMIT 1;";
						clsDatabase::execute($sql);
					}

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


			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			// Upload XML file to FTP environment of PostNL
			
			
			
			
			
			echo 'SUCCESS';
		
		*/
		
		}
		
		
		
		
		
	}




?>