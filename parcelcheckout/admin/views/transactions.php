<?php
	
	$sHtml = '';	
	$sHtml .= '<h1>Transactie overzicht</h1>';

	$aDatabaseSettings = parcelcheckout_getDatabaseSettings();

	$sql = "SELECT `order_id`, `gateway_code`, `transaction_id`, `transaction_date`, `transaction_amount`, `transaction_status`, `transaction_params`, `order_params` FROM `" . $aDatabaseSettings['table'] . "`  WHERE (`transaction_status` = 'SUCCESS') ORDER BY `transaction_date` DESC;";
	$aSuccessfulTransactions = parcelcheckout_database_getRecords($sql);

	
	if(!empty($_POST['download_button']))
	{
		$iDateRange = $_POST['date_range'];
		downloadTransactionsCsv($aSuccessfulTransactions, $iDateRange);
	}
	
	
	
	
	
	
	function downloadTransactionsCsv($aSuccessfulTransactions, $iDateRange){
		$sCurrentTime = time();
		//$iDateRange = 86400; //dag= 86400, week= 604800, maand= 2592000, jaar= 31536000
		$sCsvData = '"Datum","Tijd","Bestel-ID","Betaal-methode","Bedrag","Transactie-ID","Naam","Achternaam","E-mail"';
		
		function escapeCsv($sString)
		{
			return str_replace('"', '""', $sString);
		}
		
		foreach($aSuccessfulTransactions as $k => $aRecord)
		{
			if($aRecord['transaction_date'] > ($sCurrentTime - $iDateRange)){
				$aParams = parcelcheckout_unserialize($aRecord['order_params']);
				$sCsvData .= "\r\n" . '"' . date('d-m-Y', $aRecord['transaction_date']) . '","' . date('H:i:s', $aRecord['transaction_date']) . '","' . escapeCsv($aRecord['order_id']) .  '","' . escapeCsv($aRecord['gateway_code']) . '","' . escapeCsv($aRecord['transaction_amount']) .  '","' . escapeCsv($aRecord['transaction_id']) . '","' . escapeCsv(empty($aParams['customer']['payment_first_name']) ? '' : $aParams['customer']['payment_first_name']) .  '","' . escapeCsv(empty($aParams['customer']['payment_last_name']) ? '' : $aParams['customer']['payment_last_name']) .  '","' . escapeCsv(empty($aParams['customer']['payment_email']) ? '' : $aParams['customer']['payment_email']) . '"';
			}else{
				break;
			}				
		}
		
		header('Content-Type: text/csv; charset="ISON8895-15"');
		header('Content-Disposition: attachment; filename="transacties-' . date('Y-m-d') . '.csv"');
		header("Pragma: no-cache");
		header("Expires: 0");
		print($sCsvData);
		exit;
	}
	
	
	function getTransactions($HDWM){
		
		$aDatabaseSettings = parcelcheckout_getDatabaseSettings();
		
		
		$sql = "SELECT `transaction_date` FROM `" . $aDatabaseSettings['table'] . "` WHERE (`transaction_status` = 'SUCCESS') ORDER BY `id` DESC";
		$aTransactions = parcelcheckout_database_getRecords($sql);

		//print_r($aTransactions);
		
		$sCurrentTime = time();
		$iHDWMCounter = 0;
		
		//print_r($sCurrentTime);
		foreach($aTransactions as $aRecord){
			if($HDWM == "hour"){
				if(htmlspecialchars($aRecord['transaction_date']) >= ($sCurrentTime - 3600) ){
					$iHDWMCounter++;
				}
				
			}else if($HDWM == "day"){
				if(htmlspecialchars($aRecord['transaction_date']) >= ($sCurrentTime - 86400) ){
					$iHDWMCounter++;
				}
				
			}else if($HDWM == "week"){
				if(htmlspecialchars($aRecord['transaction_date']) >= ($sCurrentTime - 604800) ){
					$iHDWMCounter++;
				}
				
			}else if($HDWM == "month"){
				if(htmlspecialchars($aRecord['transaction_date']) >= ($sCurrentTime - 2592000) ){
					$iHDWMCounter++;
				}
				
			}else if($HDWM == "year"){
				if(htmlspecialchars($aRecord['transaction_date']) >= ($sCurrentTime - 31536000) ){
					$iHDWMCounter++;
				}
				
				
			}else{
				print_r('please configure the $HDWM variable with either hour, day, week or month ');
			}	
		}
		return $iHDWMCounter;
	} 
	
	function transactionPerfCalc($sMWD, $iMonth, $iWeek, $iDay){
		$iMonth = $iMonth / 30;
		if(strcasecmp($sMWD, 'week') === 0){
			if($iMonth == 0){
				return "100";
			}else{
				$iWeek = round(($iWeek / 7) * 100 / $iMonth);
				if($iWeek > 200){
					return "200";
					$iWeek = 200;
				}else{
					return "$iWeek";
				}
			}
		}else if(strcasecmp($sMWD, 'day') === 0){
			if($iMonth == 0){
				return "100";
			}else{
				$iDay = round(($iDay * 100) / $iMonth);
				if($iDay > 200){
					return "200";
					$iWeek = 200;
				}else{
					return "$iDay";
				}
			}
		}
	}
	
	
	$sHtml .= '
<div class="transaction-summary brand-border">
	<div class="transaction-summary-text">
	Transacties, prestatie:
		<div class="hourly-wrapper">
			<div class="hourly-title-wrapper">afgelopen uur: '. getTransactions("hour") . '</div>

			<div class="hourly-graph-wrapper"></div>
		</div>
		<div class="daily-wrapper">
			<div class="daily-title-wrapper">afgelopen dag: '. getTransactions("day") . ',
			<b>' . transactionPerfCalc("day", getTransactions("month"), getTransactions("week"),  getTransactions("day")) . '%</b></div>
			<div class="daily-graph-wrapper"></div>
		</div>	
		<div class="weekly-wrapper">		
			<div class="weekly-title-wrapper">afgelopen week: '. getTransactions("week") . ',
			<b>' . transactionPerfCalc("week", getTransactions("month"), getTransactions("week"),  getTransactions("day")) . '%</b></div>
			<div class="weekly-graph-wrapper"></div>
		</div>
		<div class="monthly-wrapper">
			<div class="monthly-title-wrapper">afgelopen maand: '. getTransactions("month") . ', <b>100%</b></div>		
			<div class="monthly-graph-wrapper"></div>	
		</div>	
		<div class="yearly-wrapper">
			<div class="monthly-title-wrapper">afgelopen jaar: '. getTransactions("year") . '</div>		
			<div class="monthly-graph-wrapper"></div>	
		</div>	
		<div class="download-transactions">
			<form action="" method="post" name="download-transactions">
				<input type="submit" class="download-button" name="download_button" value="Download afgelopen">
					<select name="date_range">
						<option value="31536000">Jaar</option>
						<option value="2592000">Maand</option>
						<option value="604800">Week</option>
						<option value="86400">Dag</option>
						<option value="3600">Uur</option>
					</select>
			</form>
		</div>
	</div>
	<div class="transaction-performance-svg-div">
		<svg class="transaction-performance-svg" viewBox="0 0 200 200" aria-labelledby="title desc" role="img">
			<g transform="translate(0,200)">
				<g transform="scale(1,-1)">
					<rect class="perf-rect-back" rx="15px" ry="15px" x="0%" y="0" width="100%" height="100%"></rect>
					<rect class="perf-rect-week" rx="20px" ry="20px" x="5%" y="0" width="35%" height="' . transactionPerfCalc("week", getTransactions("month"), getTransactions("week"),  getTransactions("day")) / 2 . '%" ></rect>
					<rect class="perf-rect-day" rx="20px" ry="20px" x="60%" y="0" width="35%" height="' . transactionPerfCalc("day", getTransactions("month"), getTransactions("week"),  getTransactions("day")) / 2 . '%" ></rect>
					<rect class="perf-rect-month" x="0%" y="98" width="100%" height="4px" ></rect>
					<rect class="perf-rect-month" x="25%" y="90px" width="50%" height="20px" rx="4px" ry="4px"></rect>
				</g>
			</g>
			<text class="perf-rect-text" text-anchor="middle" x="50%" y="105px"   >maand gem*</text>
			<text class="perf-rect-text" text-anchor="middle" x="22.5%" y="180px" >week</text>
			<text class="perf-rect-text" text-anchor="middle" x="77.5%" y="180px" >dag</text>
		</svg>
	</div>	
	<div class="cleared"></div>
		
</div>
<p>Overzicht van alle succesvolle transacties via de iDEAL Checkout plugin.</p>
';
	
	
$sHtml .= '
<div class="transactions-overview">';	

	if(sizeof($aSuccessfulTransactions))
	{			
		$iShowCounter = 0;
		foreach($aSuccessfulTransactions as $aRecord)
		{				
			$aParams = parcelcheckout_unserialize($aRecord['order_params']);

			$sHtml .= '
		<div class="seperate-transaction brand-border">
			<div class="seperate-transaction-date">
				<b>' . htmlspecialchars(date('d-m-Y', $aRecord['transaction_date'])) . '</b>
			</div>
			
			<div class="seperate-transaction-amount">
				<b>Bedrag:</b> ' . htmlspecialchars(number_format($aRecord['transaction_amount'], 2, ',', '.')) . '
			</div>
			<div class="seperate-transaction-method">
				<b>Methode:</b> ' . htmlspecialchars($aRecord['gateway_code']) . '
			</div>';
			
			$sClientName = '';
			
			if(!empty($aParams['customer']['payment_first_name']))
			{
				$sClientName .= $aParams['customer']['payment_first_name'];
				
			}
			
			if(!empty($aParams['customer']['payment_first_name']))
			{
				if(strlen($sClientName) > 0)
				{
					$sClientName .= ' ';
					
				}
				
				$sClientName .= $aParams['customer']['payment_first_name'];
				
			}
			
			
			
			$sHtml .= '
			<div class="seperate-transaction-name">
				<b>Naam:</b> ' . $sClientName . '
			</div>';
			
			
			
			if(!empty($aParams['customer']['payment_email']))
			{
				$sMailHtml = 	'
			<div class="seperate-transaction-email">
				<b>E-mail:</b> <a href="mailto:">' . htmlspecialchars($aParams['customer']['payment_email']) . '</a>
			</div>';
			}
			else
			{
				$sMailHtml = 	'
			<div class="seperate-transaction-no-email">
				<b>E-mail:</b>
			</div>';
				
			}			
			

			$sHtml .= '
			' . $sMailHtml . '
			
			
			<div class="seperate-transaction-order">
				<b>Order:</b> ' . htmlspecialchars($aRecord['order_id']) . '<br>
			</div>
			<div class="seperate-transaction-transaction">
				<span title="' .htmlspecialchars($aRecord['transaction_id']) . '">';

			$transactionId = htmlspecialchars($aRecord['transaction_id']);
			if(strlen($transactionId) > 20) $transactionId = substr($transactionId, 0, 20).'...';
			
			$sHtml .= '<b>Transactie:</b> ' . $transactionId . '</span>
			</div>
			<div class="cleared"></div>
		</div>';	
		if(++$iShowCounter >= 50 ) break; 
		}
		
	}
	else
	{

		$sHtml .= '
		
			Geen transacties gevonden!
		';
	}	
$sHtml .= '
</div>';
	
return $sHtml;

?>