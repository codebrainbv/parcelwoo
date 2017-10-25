<?php

	if(clsPost::match('form', 'parcelware'))
	{
		clsFolder::clean(FRONTEND_PATH . '/temp/temp', strtotime('-24 Hours'));
		
		
		$aFilesToZip = array();
		$bExportAndPrint = !empty($_POST['EXPORT_AND_PRINT']);

		if(isset($_POST['orders']) && is_array($_POST['orders']))
		{
			$aOrderIds = $_POST['orders'];
		}
		else
		{
			$aOrderIds = array();
		}
		
		if(sizeof($aOrderIds))
		{
			$sql = "SELECT * FROM `#_module_cart_orders` WHERE `id` IN ('" . implode("', '", $aOrderIds) . "') ORDER BY `id` ASC;";
			$rsOrders = clsDatabase::getRecords($sql);


			$sql = "SELECT *, `name` AS `label`, `id` AS `value` FROM `#_template_print` WHERE (`group` = 'cart-backend-print') ORDER BY `name` ASC;";
			$rsPrintTemplates = clsDatabase::getRecords($sql);

/*
			$rsOrders = array();
			$rsOrders[] = array('id' => 1, 'contact_name' => 'Test 1', 'contact_address' => 'Jan Steenstraat 175', 'contact_postalcode' => '7944TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '+31614707337', 'contact_email' => 'test1@php-solutions.nl', 'order_number' => 'WEB201100001');
			$rsOrders[] = array('id' => 2, 'contact_name' => 'Test 2', 'contact_address' => 'Jan Steenstraat 175 appartement 5', 'contact_postalcode' => '7944TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '0614707337', 'contact_email' => 'test2@php-solutions.nl', 'order_number' => 'WEB201100002');
			$rsOrders[] = array('id' => 3, 'contact_name' => 'Test 3', 'contact_address' => 'Jan Steenstraat 175/5', 'contact_postalcode' => '7944 TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '+31 (0) 614707337', 'contact_email' => 'test3@php-solutions.nl', 'order_number' => 'WEB201100003');
			$rsOrders[] = array('id' => 4, 'contact_name' => 'Test 4', 'contact_address' => 'Jan Steenstraat 175-5', 'contact_postalcode' => '7944TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '+31 6 14 707 337', 'contact_email' => 'test4@php-solutions.nl', 'order_number' => 'WEB201100004');
			$rsOrders[] = array('id' => 5, 'contact_name' => 'Test 5', 'contact_address' => 'Jan Steenstraat 175b', 'contact_postalcode' => '7944 TT', 'contact_city' => 'Meppel', 'contact_country' => 'Nederland', 'contact_phone' => '0031614707337', 'contact_email' => 'test5@php-solutions.nl', 'order_number' => 'WEB201100005');
*/

			if(sizeof($rsOrders))
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

/*
Productcode
3085 Standaard Pakket
3089 Pakket, handtekening voor ontvangst, alleen huisadres
3189 Pakket, handtekening voor ontvangst, burenbelevering toegestaan
3610 Pallet
3630 Stukgoed
4940 Zending binnen Europa (to B)
4944 Zending binnen Europa (to C)

*/

					// Set default to 3085
					$sProductCode = '3085';



					$sData .= CRLF . $sReferenceNr . ';' . $sCompanyName . ';' . $sLastname . ';' . $sFirstname . ';' . $sCountry . ';' . $sStreet . ';' . $sHomeNr . ';' . $sHomeNrExt . ';' . $sZIP . ';' . $sCity . ';' . $sProductCode . ';;;' . $sTelephone . ';' . $sEmail . ';';


					if($bExportAndPrint)
					{
						foreach($rsPrintTemplates as $rsTemplate)
						{
							$oTemplate = new clsTemplate($rsTemplate['pdf_body']);

							// Set general tags
							$oTemplate->setTag('system.root_url', FRONTEND_URL);
							$oTemplate->setTag('system.page_id', PAGE_ID);
							$oTemplate->setTag('system.date', date('d-m-Y', NOW));
							$oTemplate->setTag('system.time', date('H:i:s', NOW));
							$oTemplate->setTag('user.ip', $_SERVER['REMOTE_ADDR']);

							// Set order tags
							$oTemplate->setTags(cart_getOrderFormFields(), $aOrder, 'order.');

							$aCustomFields = module_cart_getCustomOrderFields($aOrder);

							$sOrderData = str_replace(array('<div class="label">', '<div class="text">', '<div class="icon">', '</div>'), array('<span style="color: #000000; font-family: Arial; font-size: 12px; font-weight: bold;">', '<span style="color: #000000; font-family: Arial; font-size: 12px;">', '<span style="color: #000000; font-family: Arial; font-size: 10px;">', '</span>'), $aCustomFields['order.data']);
							$sRecieptData = str_replace(array('<div class="label">', '<div class="text">', '<div class="icon">', '</div>'), array('<span style="color: #000000; font-family: Arial; font-size: 12px; font-weight: bold;">', '<span style="color: #000000; font-family: Arial; font-size: 12px;">', '<span style="color: #000000; font-family: Arial; font-size: 10px;">', '</span>'), $aCustomFields['order.reciept']);
							$sContactData = str_replace(array('<div class="label">', '<div class="text">', '<div class="icon">', '</div>'), array('<span style="color: #000000; font-family: Arial; font-size: 12px; font-weight: bold;">', '<span style="color: #000000; font-family: Arial; font-size: 12px;">', '<span style="color: #000000; font-family: Arial; font-size: 10px;">', '</span>'), $aCustomFields['contact.data']);
							$sProductData = str_replace(array('<div class="label">', '<div class="text">', '<div class="icon">', '</div>'), array('<span style="color: #000000; font-family: Arial; font-size: 12px; font-weight: bold;">', '<span style="color: #000000; font-family: Arial; font-size: 12px;">', '<span style="color: #000000; font-family: Arial; font-size: 10px;">', '</span>'), $aCustomFields['product.data']);
							$sOrderNotes = str_replace(array('<div class="label">', '<div class="text">', '<div class="icon">', '</div>'), array('<span style="color: #000000; font-family: Arial; font-size: 12px; font-weight: bold;">', '<span style="color: #000000; font-family: Arial; font-size: 12px;">', '<span style="color: #000000; font-family: Arial; font-size: 10px;">', '</span>'), $aCustomFields['order.notes']);

							$oTemplate->setTag('order.data', $sOrderData);
							$oTemplate->setTag('order.reciept', $sRecieptData);
							$oTemplate->setTag('contact.data', $sContactData);
							$oTemplate->setTag('product.data', $sProductData);
							$oTemplate->setTag('order.notes', $sOrderNotes);

							$oTemplate->setTag('order.shipment_label', clsSettings::getText('instance', $aOrder['shipment_method'] . '_LABEL'));
							$oTemplate->setTag('order.payment_label', clsSettings::getText('instance', $aOrder['payment_method'] . '_LABEL'));

							$sPdfHtml = $oTemplate->getContent();

							// Fix seperators
							$sPdfHtml = str_replace(array('<td colspan="7"><div class="seperator seperator-0"></span></td>', '<td colspan="7"><div class="seperator seperator-1"></span></td>', '<td colspan="7"><div class="seperator seperator-2"></span></td>', '<td colspan="7"><div class="seperator seperator-3"></span></td>', '<td colspan="7"><div class="seperator seperator-4"></span></td>'), array('<td colspan="7"><div class="seperator seperator-0"></div></td>', '<td colspan="7"><div class="seperator seperator-1"></div></td>', '<td colspan="7"><div class="seperator seperator-2"></div></td>', '<td colspan="7"><div class="seperator seperator-3"></div></td>', '<td colspan="7"><div class="seperator seperator-4"></div></td>'), $sPdfHtml);

							// Custom Fixes
							$sPdfHtml = str_replace(array('BTW.</i></td>'), array('BTW.</i></span></td>'), $sPdfHtml);
							// $sPdfHtml = str_replace(array(FRONTEND_URL . '/data/'), array(FRONTEND_PATH . '/data/'), $sPdfHtml);

							$sFile = FRONTEND_PATH . '/temp/temp/' . $aOrder['order_number'] . ' - ' . $rsTemplate['name'] . '.pdf';
							clsHtml::toPdf($sPdfHtml, $sFile, false, true);

							$aFilesToZip[] = $sFile;
						}
					}

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
	}

	$sOrderOptions = '';

	$sql = "SELECT `id`, `order_number`, `order_date`, `order_status`, `shipment_method`, `shipment_status`, `payment_method`, `payment_status` FROM `#_module_cart_orders` ORDER BY `id` DESC;";
	$rsOrders = clsDatabase::getRecords($sql);

	$sIds = array();

	foreach($rsOrders as $aOrder)
	{
		$bSelected = false;

		if(!in_array($aOrder['order_status'], array('1', '2', '9')))
		{
			if(in_array($aOrder['shipment_method'], array('SHIPMENT_NORMAL', 'SHIPMENT_SECURE', 'SHIPMENT_COD')))
			{
				if(in_array($aOrder['payment_method'], array('PAYMENT_IDEAL', 'PAYMENT_MISTERCASH', 'PAYMENT_CREDITCARD', 'PAYMENT_COD', 'PAYMENT_PAYPAL', 'PAYMENT_MINITIX', 'PAYMENT_PAYSAFECARD', 'PAYMENT_INVOICE')))
				{
					if(strcasecmp($aOrder['payment_status'], '1') === 0)
					{
						$bSelected = true;
					}
				}
				else
				{
					$bSelected = true;
				}
			}

			$sOrderOptions .= '<option value="' . $aOrder['id'] . '"' . ($bSelected ? ' selected="selected"' : '') . '>' . clsText::escapeHtml($aOrder['order_number']) . ', ' . date('d-m-Y', clsInt::toDate($aOrder['order_date'])) . ((in_array($aOrder['shipment_method'], array('SHIPMENT_NORMAL', 'SHIPMENT_SECURE', 'SHIPMENT_COD'))) ? ', Verzenden' : ', Afhalen') . (in_array($aOrder['payment_method'], array('PAYMENT_IDEAL', 'PAYMENT_MISTERCASH', 'PAYMENT_CREDITCARD', 'PAYMENT_COD', 'PAYMENT_PAYPAL', 'PAYMENT_MINITIX', 'PAYMENT_PAYSAFECARD', 'PAYMENT_INVOICE')) ? ', Betalen voor ontvangst' : ', Betalen na ontvangst') . ((strcasecmp($aOrder['payment_status'], '1') === 0) ? ', Betaald' : ', Niet betaald') . ((strcasecmp($aOrder['shipment_status'], '1') === 0) ? ', Verzonden' : '') . '</option>';
		}
	}


	$sHtml = '
<form action="' . clsText::escapeHtml(SELF_URL) . '" method="post">
	<input name="form" type="hidden" value="parcelware">
	<table border="0" cellpadding="0" cellspacing="0" class="form">
		<tr>
			<td align="left" valign="top" width="150"><div class="label">Orders</div></td>
			<td align="left" valign="top"><div class="input"><select class="select select_l select_multiple" multiple="multiple" name="orders[]" size="35">' . $sOrderOptions . '</select></div></td>
		</tr>
		<tr>
			<td align="left" valign="top"></td>
			<td align="left" valign="top"><div class="input"><input class="button" type="submit" value="Exporteren"> <input class="button" name="EXPORT_AND_PRINT" type="submit" value="CSV &amp; PDFs Downloaden &amp; In behandeling zetten"></div></td>
		</tr>
	</table>
</form>';




	$sTitle = 'Parcelware export';
	$sContent = '
<p>Exporteer orders t.b.v. Parcelware.</p>
' . $sHtml;



	// Add toolbar items
	clsToolbar::add('/images/parcelware.png', MODULE_EDIT_URL . '&' . MODULE_NAME . '[view]=parcelware', 'Parcelware export', true);
	clsToolbar::add('/images/import_export.png', MODULE_EDIT_URL . '&' . MODULE_NAME . '[view]=order-import-export', 'Bestellingen exporteren');
	clsToolbar::add('/images/list-red.png', MODULE_EDIT_URL . '&' . MODULE_NAME . '[view]=order-list', 'Bestellingen overzicht', false);

	if(clsBackend::isSupervisor() || clsBackend::match('instance', 'coupon_access', '1'))
	{
		clsToolbar::add('/images/list-blue.png', MODULE_EDIT_URL . '&' . MODULE_NAME . '[view]=coupon-list', 'Coupons overzicht');
	}

	clsToolbar::add('/images/settings.png', MODULE_EDIT_URL . '&' . MODULE_NAME . '[view]=module-edit', 'Algemene instellingen');
	clsToolbar::add('/images/view.png', FRONTEND_URL . '/index.php?core[page]=' . PAGE_ID, 'Pagina bekijken', false, '_blank');
	clsToolbar::add('/images/back.png', BACKEND_URL . '/index.php?core[view]=website-page-info&core[id]=' . PAGE_ID, 'Terug');


	// Set title & content
	$_REQUEST['rsView']['title'] = $sTitle;
	$_REQUEST['rsView']['content'] = $sContent;


?>