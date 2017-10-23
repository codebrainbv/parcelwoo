<?php
	
	$sHtml = '';	
	
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	
	
	$sHtml .= '
		<script>
			function minimizeOrderspr(){
				jQuery(\'.orders-pr\').toggleClass(\'minimized\');
				$( \'.orders-prContent\' ).slideToggle(\'fast\');
			}
		</script>
		<link href="widgets/lower/css/orders-pr.css" media="screen" rel="stylesheet" type="text/css">
		<script>
			function fShowMoreTransactionsOrdersPr(){
				jQuery(\'.orders-pr\').toggleClass(\'showMore\');
			}
		</script>
		<div class="orders-pr" id="orders-pr">
	';
	// [\*]
	
	
	
	
	// [**] codeer hier wat er in de widget moet komen te staan.
	
	/* $aDatabaseSettings = parcelcheckout_getDatabaseSettings();
			
	$sql = "SELECT `transaction_date` FROM `" . $aDatabaseSettings['table'] . "` WHERE (`transaction_status` = 'SUCCESS') ORDER BY `id` DESC";
	$aTransactions = parcelcheckout_database_getRecords($sql);
	
	print_r($aTransactions);
	*/
	
	$iMaxDate = date(strtotime('-1 Month'));
	
	$aDatabaseSettings = parcelcheckout_getDatabaseSettings();
	$sql = "SELECT `order_id`, `gateway_code`, `transaction_id`, `transaction_status`, `order_params` FROM `" . $aDatabaseSettings['table'] . "` WHERE (`transaction_date` >= '" . $iMaxDate . "') ORDER BY `transaction_date` DESC;";
	$aTransactions = parcelcheckout_database_getRecords($sql);
		

		if(sizeof($aTransactions))
		{			
			$iShowCounter = 0;
			
			$sHtml .= '
			<div class="orders-pr-TopDeco widgetTopDeco">
			</div>
			<div class="orders-pr-TopInfo widgetTopInfo">
				Orders premium
				<div class="minusLowerWidget" onclick="minimizeOrderspr()"></div>
				<div class="infoIconLowerWidget" data-balloon="Transacties afgelopen 30 dagen" data-balloon-pos="left">
					<img src="' . $sImagePath . '/info-icon.png" height="100%" >
				</div>
				<div class="moveIconLowerWidget noRightClick" data-balloon="hou ingedrukt om te verplaatsen" data-balloon-pos="left">
					<img src="' . $sImagePath . '/move-icon.png" height="100%" >
				</div>
			</div>
			<div onmousedown="disableLowerDragging()" onmouseup="enableLowerDragging()" ontouchstart="disableLowerDragging()" ontouchend="enableLowerDragging()" class="lowerWidgetContent orders-prContent">
			<div class="transaction-overview">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<th style="width: 168px">Order</th>
						<th>Methode</th>
						<th style="width: 36px">Status</th>
					</tr>
			';
			foreach($aTransactions as $aRecord)
			{				
				$aParams = parcelcheckout_unserialize($aRecord['order_params']);
				$transactionId = htmlspecialchars($aRecord['transaction_id']);
				$orderId = htmlspecialchars($aRecord['order_id']);
				$sTransactionStatus = htmlspecialchars($aRecord['transaction_status']);
				//if(strlen($transactionId) > 32) $transactionId = substr($transactionId, 0, 29).'...';
				$sHtml .= '
					<tr>
						<td><span id="openerpr' . $orderId . '" style="text-decoration:none; color:#00acd6;">' . $orderId . '</span></td>
						
						<script>
						$( function() {
							$( "#dialogpr' . $orderId . '" ).dialog({
							  autoOpen: false,
							  dialogClass: "widgetOrderDialog",
							  show: {
								effect: "blind",
								duration: 100
							  },
							  hide: {
								effect: "blind",
								duration: 100
							  }
							});
						 
							$( "#openerpr' . $orderId . '" ).on( "click", function(){
								$( "#dialogpr' . $orderId . '" ).dialog( "open" );
							});
						});
						</script>
						
						<div id="dialogpr' . $orderId . '" title="Order: ' . $orderId . '">
							<p>Order: "' . $orderId . '"<br>
							Transactie: "' . $transactionId . '"<br>
							Methode: "' . htmlspecialchars($aRecord['gateway_code']) . '"<br>
							Status: "' . $sTransactionStatus . '"<br>
							Bedrag: "optioneel"<br>
							NAW: "optioneel"<br>
							</p>
						</div>
						
						
						<td style="word-wrap: break-word">' . htmlspecialchars($aRecord['gateway_code']) . '</td>
				';

				if($sTransactionStatus == "SUCCES" || $sTransactionStatus == "SUCCESS"){
					$sHtml .= '
						<td><img class="circle-icon icon" title="succes" height="18" style="margin-top:11px;margin-bottom:7px;margin-left:11px;" src="' . $sImagePath . '/bar-green.png"></td>
				';	
				}else if($sTransactionStatus == "CANCELLED"){
					$sHtml .= '
						<td><img class="circle-icon icon" title="geannuleerd" height="18" style="margin-top:11px;margin-bottom:7px;margin-left:11px;" src="' . $sImagePath . '/bar-yellow.png"></td>
				';	
				}else if($sTransactionStatus == "FAILURE" || $sTransactionStatus == "EXPIRED"){
					$sHtml .= '
						<td><img class="circle-icon icon" title="gefaald of verlopen" height="18" style="margin-top:11px;margin-bottom:7px;margin-left:11px;" src="' . $sImagePath . '/bar-red.png"></td>
				';	
				}else if($sTransactionStatus == "OPEN" || $sTransactionStatus == "PENDING" ){
					$sHtml .= '
						<td><img class="circle-icon icon" title="open / afwachten" height="18" style="margin-top:11px;margin-bottom:7px;margin-left:11px;" src="' . $sImagePath . '/bar-purple.png"></td>
				';	
				}else{
					$sHtml .= '
						<td>' . $sTransactionStatus . '</td>
				';	
				}
				$sHtml .= '
					</tr>
				';	
				
			}
			
		}
		else
		{
			$sHtml .= '
			
				Geen transacties gevonden!
			';
		}	
	$sHtml .= '
			</table>
	';
	
	// [\**]
	
	
	
	
	// [*]
	$sHtml .= '
	</div>
	</div>	
	<div class="ordersPrButtonBar">
		<button class="ShowMoreTransactionsOrdersPrButton" onclick="fShowMoreTransactionsOrdersPr()"> Meer </button>
		<button class="PlaceNewOrderOrdersPrButton" onclick=""> Nieuw order </button>
	</div> 
	';
	echo($sHtml);
	// [\*]
?>