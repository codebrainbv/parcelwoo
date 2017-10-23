<?php
	$sHtml = '';
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	
	$sHtml .= '
		<script>
			function minimizeMethodsPercentDonut(){
				jQuery(\'.methodsPercentDonut\').toggleClass(\'minimized\');
				$( \'.methodsPercentDonutContent\' ).slideToggle(\'fast\');
			}
		</script>
		<link href="widgets/lower/css/methodsPercentDonut.css" media="screen" rel="stylesheet" type="text/css">
		<div class="methodsPercentDonut" id="methodsPercentDonut">
		<div class="methodsPercentDonutTopDeco widgetTopDeco">
		</div>
		<div class="methodsPercentDonutTopInfo widgetTopInfo">
			Methoden %donut
			<div class="minusLowerWidget" onclick="minimizeMethodsPercentDonut()"></div>
			<div class="infoIconLowerWidget" data-balloon="Percentage per betaalmethode deze maand" data-balloon-pos="left">
				<img src="' . $sImagePath . '/info-icon.png" height="100%" >
			</div>
			<div class="moveIconLowerWidget noRightClick" data-balloon="hou ingedrukt om te verplaatsen" data-balloon-pos="left">
				<img src="' . $sImagePath . '/move-icon.png" height="100%" >
			</div>
		</div>
	';
	// [\*]
	
	// [**] codeer hier wat er in de widget moet komen te staan.
	
	function getPaymentMethod($iPASTMONTH, $sSTATUS, $iGETMONTHONLY, $sFunction){

		$iTransactionCount = 0;
		$aPaymentMethodCount = array();
		
		$iCurrentYear = date("Y");
		$iCurrentMonth = date("n");
		
		$iWantedStartMonth = ($iCurrentMonth - $iPASTMONTH);
		$iWantedStartYear = $iCurrentYear;
		$iWantedEndYear = $iWantedStartYear;
		
		$iWantedEndMonth = ($iWantedStartMonth + 1);
		if($iWantedEndMonth == 13){
			$iWantedEndMonth = 1;
			$iWantedEndYear = ($iWantedStartYear + 1);
		}
		
		$iWantedStartDate = strtotime('1-' . $iWantedStartMonth . '-' . $iWantedStartYear . '');
		$iWantedEndDate = strtotime('1-' . $iWantedEndMonth . '-' . $iWantedEndYear . '');
		
		$iMaxDate = date(strtotime('-1 Month'));		
		
		
		$aDatabaseSettings = parcelcheckout_getDatabaseSettings();
		$sql = "SELECT `transaction_date`, `gateway_code` FROM `" . $aDatabaseSettings['table'] . "` WHERE (`transaction_status` = '" .parcelcheckout_escapeSql($sSTATUS) . "') AND (`transaction_date` >= '" . $iMaxDate . "') ORDER BY `id` DESC";
		
		$aTransaction = parcelcheckout_database_getRecords($sql);
		
		if($iGETMONTHONLY == 0){
			foreach($aTransaction as $aRecord){
				if(htmlspecialchars($aRecord['transaction_date']) > $iWantedStartDate && htmlspecialchars($aRecord['transaction_date']) <= $iWantedEndDate){
						$iTransactionCount++;
						array_push($aPaymentMethodCount,  $aRecord['gateway_code']);
				}
				
			}
			
			$aPaymentMethodCounted = array_count_values($aPaymentMethodCount);
			arsort($aPaymentMethodCounted);
			$aPaymentMethodCountSum = array_sum($aPaymentMethodCounted);
			
			$sReturnString = '';
			
			foreach ($aPaymentMethodCounted as $sMethod => $Count) {
				$aPaymentMethodPercentage = round((100 / $aPaymentMethodCountSum * $Count), 2);
				if($sFunction == "METHOD"){
					$sReturnString .= '"' . $sMethod . '", '; 
				}else if($sFunction == "DATA"){
					$sReturnString .= '' . $aPaymentMethodPercentage . ', ';
				}
				
			}
			$sReturnString = substr($sReturnString, 0, -2);
			return $sReturnString;
			
		}else if($iGETMONTHONLY == 1){
			$sGETMONTH = date("F", $iWantedStartDate);
			return $sGETMONTH;
		}
	}
	
	
	$sHtml .= '
	<script>
		new Chart(document.getElementById("Donut-percent-chart-PaymentMethod"), {
			type: "doughnut",
			data: {
				datasets: [{
					data: [' . getPaymentMethod('0', 'SUCCESS', '0', 'DATA') . '],
					backgroundColor: ["#F56954", "#00C0EF", "#F39C12", "#3C8DBC", "#f7da00", "#00A65A", "#DD4B39"],
					label: "Dataset 1"
				}],
				labels: [' . getPaymentMethod('0', 'SUCCESS', '0', 'METHOD') . ']
			},
			options: {
				responsive: true,
				legend: {
					labels: {
						usePointStyle: true  //<-- set this
					},
					position: "right",
				},
				animation: {
					animateScale: true,
					animateRotate: true
				},
			}
		});
	</script>
	';
	
	$sHtml .= '
	
	<div onmousedown="disableLowerDragging()" onmouseup="enableLowerDragging()" ontouchstart="disableLowerDragging()" ontouchend="enableLowerDragging()" class="lowerWidgetContent methodsPercentDonutContent">
		<canvas id="Donut-percent-chart-PaymentMethod" width="2100" height="900" style="margin: auto;"></canvas>
	</div>
	';
	// [\**]
	
	// [*]
	$sHtml .= '
		</div>
	';
	echo($sHtml);
	// [\*]
?>