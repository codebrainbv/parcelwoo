<?php
	$sHtml = '';
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	
	$sHtml .= '
		<script>
			function minimizeordersEuMap(){
				jQuery(\'.ordersEuMap\').toggleClass(\'minimized\');
				$( \'.ordersEuMapContent\' ).slideToggle(\'fast\');
			}
		</script>
		<link href="widgets/lower/css/ordersEuMap.css" media="screen" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="widgets/lower/js/mapael/raphael.min.js"></script>
		<script type="text/javascript" src="widgets/lower/js/mapael/jquery.mousewheel.min.js"></script>
		<script type="text/javascript" src="widgets/lower/js/mapael/jquery.mapael.min.js"></script>
		<script type="text/javascript" src="widgets/lower/js/mapael/maps/european_union.min.js"></script>
		<div class="ordersEuMap" id="ordersEuMap">
		<div class="ordersEuMapTopDeco widgetTopDeco">
		</div>
		<div class="ordersEuMapTopInfo widgetTopInfo">
			Orders Europa map
			<div class="minusLowerWidget" onclick="minimizeordersEuMap()"></div>
			<div class="infoIconLowerWidget" data-balloon="Bestellingen per land, afgelopen 30 dagen" data-balloon-pos="left"> 
				<img src="' . $sImagePath . '/info-icon.png" height="100%" >
			</div>
			<div class="moveIconLowerWidget noRightClick" data-balloon="houd ingedrukt om te verplaatsen" data-balloon-pos="left">
				<img src="' . $sImagePath . '/move-icon.png" height="100%" >
			</div>
		</div>
		<div onmousedown="disableLowerDragging()" onmouseup="enableLowerDragging()" ontouchstart="disableLowerDragging()" ontouchend="enableLowerDragging()" class="lowerWidgetContent ordersEuMapContent">
	';
	// [\*]
	
	// [**] codeer hier wat er in de widget moet komen te staan.
	
		$iMaxDate = date(strtotime('-1 Month'));
		$aDatabaseSettings = parcelcheckout_getDatabaseSettings();
		$sql = "SELECT * FROM `" . $aDatabaseSettings['table'] . "` WHERE (`transaction_status` = 'SUCCESS') AND (`transaction_date` >= '" . $iMaxDate . "') ORDER BY `id` DESC";
		
		$aTransactions = parcelcheckout_database_getRecords($sql);
		
		$aordersEuMapCountryCode = [];
		$aordersEuMapCountryName = [];
		$aordersEuMapCountryAmount = [];
		
		$iAmountOfOrders = 0;
		
		foreach($aTransactions as $aTransaction)
		{
			$iAmountOfOrders++;
			$aOrderParams = parcelcheckout_unserialize($aTransaction['order_params']);

			$sCountryName = $aOrderParams['customer']['payment_country_name'];
			$sCountryCode = $aOrderParams['customer']['payment_country_code'];
			
			if(array_key_exists($sCountryCode, $aordersEuMapCountryAmount)){
				$aordersEuMapCountryAmount = array_replace($aordersEuMapCountryAmount, array($sCountryCode => ($aordersEuMapCountryAmount[$sCountryCode] + 1)));
			}else{
				$aordersEuMapCountryAmount[$sCountryCode] = 1;
				array_push($aordersEuMapCountryCode, $sCountryCode);
				array_push($aordersEuMapCountryName, $sCountryName);
			}
		
		}
		
	$sHtml .= '			
		<script type="text/javascript">
		setTimeout(function() {
			$(".ordersEuMapContainer").mapael({
				map : {
					name : "european_union"
					// Enable zoom on map
					, zoom: {
						enabled: true,
						maxlevel: 20
					}
				},
				legend: {
                    area: {
                        display: false,
                        title: "Bestellingen",
                        marginBottom: 7,
                        slices: [
						';
						
							$aordersEuMapCountryAmountUnique = array_unique($aordersEuMapCountryAmount); 
							asort($aordersEuMapCountryAmountUnique);
							$aordersEuMapCountryAmountMax = max($aordersEuMapCountryAmountUnique);
							$i = 0;
							foreach($aordersEuMapCountryAmountUnique as $ivalue){
								
								$iColorRGBValue = (((round(255*($ivalue / $aordersEuMapCountryAmountMax)) / 100) * 80) + 50);
								
								$sHtml .='
							{
								min: ' . $i . ',
								max: ' . $ivalue . ',
								attrs: {
									fill: "#34' . dechex($iColorRGBValue) . '' . dechex($iColorRGBValue) . '"
								},
								label: "Less than ' . $ivalue . '"
							},
								';
								$i = $ivalue;
							}
						
						
						$sHtml .='

                            {
                                min: 999999999,
                                attrs: {
                                    fill: "#01565E"
                                },
                                label: "More than 12"
                            }
                        ]
                    }
				},
				areas: {
					'; 
					$i=0;
					foreach($aordersEuMapCountryCode as $CountryCode){
						$iPercentageOfOrders = round(((100 / $iAmountOfOrders) * $aordersEuMapCountryAmount[$CountryCode]), 2, PHP_ROUND_HALF_DOWN);
						$sHtml .='
						
					"' . $CountryCode . '": {
						"value": ' . $aordersEuMapCountryAmount[$CountryCode] . ',
						"tooltip": {
							"content": "<span style=\"font-weight:bold;\"> ' . $aordersEuMapCountryName[$i] . ' </span> <br> bestellingen: ' . $aordersEuMapCountryAmount[$CountryCode] . '<br>'. $iPercentageOfOrders . '% van bestellingen"
						}
					},';
						$i++;
					}
					
					$sHtml .='
					
					"END": {
						"value": 0,
						"href": "#",
						"tooltip": {
							"content": "END"
						}
					}
				}
			});
		}, 1100);
		</script>
		
		<div class="ordersEuMapContainer">
			<div class="map">Alternative content</div>
			<div class="areaLegend"></div>
		</div>
	';
	// [\**]
	
	// [*]
	$sHtml .= '
		</div>
		</div>
	';
	echo($sHtml);
	// [\*]
?>