<?php
	$sHtml = '';
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	
	$sHtml .= '
		<script>
			function minimizeStatsChart(){
				jQuery(\'.statsChart\').toggleClass(\'minimized\');
				$( \'.statsChartContent\' ).slideToggle(\'fast\');
			}
		</script>
		<link href="widgets/lower/css/statsChart.css" media="screen" rel="stylesheet" type="text/css">
		<div class="statsChart" id="statsChart">
	';
	// [\*]
	
	// [**] codeer hier wat er in de widget moet komen te staan.
	
	function getTransactions($sPASTMONTH, $sSTATUS, $iGETMONTHONLY){

		$iTransactionCount = 0;
		
		$iCurrentYear = date("Y");
		$iCurrentMonth = date("n");
		
		$iWantedStartMonth = ($iCurrentMonth - $sPASTMONTH);
		$iWantedStartYear = $iCurrentYear;
		$iWantedEndYear = $iWantedStartYear;
		
		if($iWantedStartMonth == 0){
			$iWantedStartMonth = 12;
			$iWantedStartYear = ($iCurrentYear - 1);
		}else if($iWantedStartMonth == -1){
			$iWantedStartMonth = 11;
			$iWantedStartYear = ($iCurrentYear - 1);
		}else if($iWantedStartMonth == -2){
			$iWantedStartMonth = 10;
			$iWantedStartYear = ($iCurrentYear - 1);
		}else if($iWantedStartMonth == -3){
			$iWantedStartMonth = 9;
			$iWantedStartYear = ($iCurrentYear - 1);
		}else if($iWantedStartMonth == -4){
			$iWantedStartMonth = 8;
			$iWantedStartYear = ($iCurrentYear - 1);
		}else if($iWantedStartMonth == -5){
			$iWantedStartMonth = 7;
			$iWantedStartYear = ($iCurrentYear - 1);
		}
		$iWantedEndMonth = ($iWantedStartMonth + 1);
		if($iWantedEndMonth == 13){
			$iWantedEndMonth = 1;
			$iWantedEndYear = ($iWantedStartYear + 1);
		}
		
		$iWantedStartDate = strtotime('1-' . $iWantedStartMonth . '-' . $iWantedStartYear . '');
		$iWantedEndDate = strtotime('1-' . $iWantedEndMonth . '-' . $iWantedEndYear . '');
		
		//print_r(" startdateTS: ");
		//print_r($iWantedStartDate);
		//print_r(" enddateTS: ");
		//print_r($iWantedEndDate);
		//print_r(" startdate: ");
		//print_r(date('d/m/Y', $iWantedStartDate));
		//print_r(" enddate: ");
		//print_r(date('d/m/Y', $iWantedEndDate));
		
		$iMaxDate = date(strtotime('-6 Month'));		
		
		
		$aDatabaseSettings = parcelcheckout_getDatabaseSettings();
		$sql = "SELECT `transaction_date` FROM `" . $aDatabaseSettings['table'] . "` WHERE (`transaction_status` = '" .parcelcheckout_escapeSql($sSTATUS) . "') AND (`transaction_date` >= '" . $iMaxDate . "') ORDER BY `id` DESC";
		
		$aTransaction = parcelcheckout_database_getRecords($sql);
		
		if($iGETMONTHONLY == 0){
		foreach($aTransaction as $aRecord){
			if(htmlspecialchars($aRecord['transaction_date']) > $iWantedStartDate && htmlspecialchars($aRecord['transaction_date']) <= $iWantedEndDate){
					$iTransactionCount++;
			}
			
		}
		return $iTransactionCount;
		}else if($iGETMONTHONLY == 1){
			$sGETMONTH = date("F", $iWantedStartDate);
			return $sGETMONTH;
		}
	}
	
	$sHtml .= '
		<script>
			new Chart(document.getElementById("bar-chart-grouped"), {
				type: "bar",
				data: {
				  labels: ["' . getTransactions('5', 'SUCCESS', '1') . '", "' . getTransactions('4', 'SUCCESS', '1') . '", "' . getTransactions('3', 'SUCCESS', '1') . '", "' . getTransactions('2', 'SUCCESS', '1') . '", "' . getTransactions('1', 'SUCCESS', '1') . '", "' . getTransactions('0', 'SUCCESS', '1') . '", ],
				  datasets: [
					{
					  label: "SUCCESS",
					  backgroundColor: "#00A65A",
					  data: [' . getTransactions('5', 'SUCCESS') . ',' . getTransactions('4', 'SUCCESS') . ',' . getTransactions('3', 'SUCCESS') . ',' . getTransactions('2', 'SUCCESS') . ',' . getTransactions('1', 'SUCCESS') . ',' . getTransactions('0', 'SUCCESS') . ']
					}, {
					  label: "FAILED",
					  backgroundColor: "#DD4B39",
					  data: [' . getTransactions('5', 'FAILURE') . ',' . getTransactions('4', 'FAILURE') . ',' . getTransactions('3', 'FAILURE') . ',' . getTransactions('2', 'FAILURE') . ',' . getTransactions('1', 'FAILURE') . ',' . getTransactions('0', 'FAILURE') . ']
					}
				  ]
				},	
				options: {
					legend: {
						labels: {
							usePointStyle: true  //<-- set this
						},
						position: "top",
					}
				}
			});
		</script>
	'; 
	
	$sHtml .= '
		<div class="statsChartTopDeco widgetTopDeco">
		</div>
		<div class="statsChartTopInfo widgetTopInfo">
			Order status chart
			<div class="minusLowerWidget" onclick="minimizeStatsChart()"></div>
		
			<div class="infoIconLowerWidget" data-balloon="Succesvolle en gefaalde transacties per maand" data-balloon-pos="left">
				<img src="' . $sImagePath . '/info-icon.png" height="100%" >
			</div>
			<div class="moveIconLowerWidget noRightClick" data-balloon="hou ingedrukt om te verplaatsen" data-balloon-pos="left">
				<img src="' . $sImagePath . '/move-icon.png" height="100%" >
			</div>
		</div>
		<div onmousedown="disableLowerDragging()" onmouseup="enableLowerDragging()" ontouchstart="disableLowerDragging()" ontouchend="enableLowerDragging()" class="lowerWidgetContent statsChartContent">
			<canvas id="bar-chart-grouped" width="2000" height="1000" style="width:100%;"></canvas>
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