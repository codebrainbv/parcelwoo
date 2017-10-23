<?php
	$sHtml = '';
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	
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
			function minimizeMethodsHoriChart(){
				jQuery(\'.methodsHoriChart\').toggleClass(\'minimized\');
				$( \'.methodsHoriChartContent\' ).slideToggle(\'fast\');
			}
		</script>
		<script>
			new Chart(document.getElementById("methods-hori-line-chart"), {
				type: "horizontalBar",
				data: {
				  labels: [' . getPaymentMethod('0', 'SUCCESS', '0', 'METHOD') . '],
				  
				  datasets: [
					{
					  backgroundColor: ["rgba(245, 105, 84, 0.65)", "rgba(0, 192, 239, 0.65)", "rgba(243, 156, 18, 0.65)", "rgba(60, 141, 188, 0.65)", "rgba(255, 246, 0, 0.65)", "rgba(245, 105, 84, 0.65)", "rgba(0, 192, 239, 0.65)", "rgba(243, 156, 18, 0.65)", "rgba(60, 141, 188, 0.65)", "rgba(255, 246, 0, 0.65)"],
					  data: [' . getPaymentMethod('0', 'SUCCESS', '0', 'DATA') . ']
					}
				  ]
				},
				options: {
					legend: {
					display: false
					},
					tooltips: {
						enabled: true
					},
					scales: {
						yAxes: [{
							gridLines: {
							  display: false,
							  drawBorder: false
							},
							ticks: {
							  mirror: true,
							  padding: -10
							}
						}],
						  xAxes: [{
							gridLines: {
							  display: false,
							  drawBorder: false
							},
							ticks: {
								beginAtZero: true
							}
						}]
					}
				}				
			});
		</script>
	'; 
	
	$sHtml .= '
		<link href="widgets/lower/css/methodsHoriChart.css" media="screen" rel="stylesheet" type="text/css">
		<div class="methodsHoriChart" id="methodsHoriChart">
		<div class="methodsHoriChartTopDeco widgetTopDeco">
		</div>
		<div class="methodsHoriChartTopInfo widgetTopInfo">
			Methoden %
			<div class="minusLowerWidget" onclick="minimizeMethodsHoriChart()"></div>
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
	$sHtml .= '
		<div onmousedown="disableLowerDragging()" onmouseup="enableLowerDragging()" ontouchstart="disableLowerDragging()" ontouchend="enableLowerDragging()" class="lowerWidgetContent methodsHoriChartContent">
			<canvas id="methods-hori-line-chart" width="2000" height="600" style="margin: auto;"></canvas>
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