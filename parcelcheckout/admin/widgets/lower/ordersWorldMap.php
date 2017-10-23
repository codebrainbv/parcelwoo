<?php
	$sHtml = '';
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	
	$sHtml .= '
		<script>
			function minimizeordersWorldMap(){
				jQuery(\'.ordersWorldMap\').toggleClass(\'minimized\');
				$( \'.ordersWorldMapContent\' ).slideToggle(\'fast\');
			}
		</script>
		<link href="widgets/lower/css/ordersWorldMap.css" media="screen" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="widgets/lower/js/mapael/raphael.min.js"></script>
		<script type="text/javascript" src="widgets/lower/js/mapael/jquery.mousewheel.min.js"></script>
		<script type="text/javascript" src="widgets/lower/js/mapael/jquery.mapael.min.js"></script>
		<script type="text/javascript" src="widgets/lower/js/mapael/maps/world_countries_miller.min.js"></script>
		<div class="ordersWorldMap" id="ordersWorldMap">
		<div class="ordersWorldMapTopDeco widgetTopDeco">
		</div>
		<div class="ordersWorldMapTopInfo widgetTopInfo">
			Orders wereld map
			<div class="minusLowerWidget" onclick="minimizeordersWorldMap()"></div>
			<div class="infoIconLowerWidget" data-balloon="Bestellingen per land, afgelopen 30 dagen" data-balloon-pos="left"> 
				<img src="' . $sImagePath . '/info-icon.png" height="100%" >
			</div>
			<div class="moveIconLowerWidget noRightClick" data-balloon="houd ingedrukt om te verplaatsen" data-balloon-pos="left">
				<img src="' . $sImagePath . '/move-icon.png" height="100%" >
			</div>
		</div>
		<div onmousedown="disableLowerDragging()" onmouseup="enableLowerDragging()" ontouchstart="disableLowerDragging()" ontouchend="enableLowerDragging()" class="lowerWidgetContent ordersWorldMapContent">
	';
	// [\*]
	
	// [**] codeer hier wat er in de widget moet komen te staan.
	
		$iMaxDate = date(strtotime('-1 Month'));
		$aDatabaseSettings = parcelcheckout_getDatabaseSettings();
		$sql = "SELECT * FROM `" . $aDatabaseSettings['table'] . "` WHERE (`transaction_status` = 'SUCCESS') AND (`transaction_date` >= '" . $iMaxDate . "') ORDER BY `id` DESC";
		
		$aTransactions = parcelcheckout_database_getRecords($sql);
		
		$aOrdersWorldMapCountryCode = [];
		$aOrdersWorldMapCountryName = [];
		$aOrdersWorldMapCountryAmount = [];
		
		$iAmountOfOrders = 0;
		
		foreach($aTransactions as $aTransaction)
		{
			$iAmountOfOrders++;
			$aOrderParams = parcelcheckout_unserialize($aTransaction['order_params']);

			$sCountryName = $aOrderParams['customer']['payment_country_name'];
			$sCountryCode = $aOrderParams['customer']['payment_country_code'];
			
			if(array_key_exists($sCountryCode, $aOrdersWorldMapCountryAmount)){
				$aOrdersWorldMapCountryAmount = array_replace($aOrdersWorldMapCountryAmount, array($sCountryCode => ($aOrdersWorldMapCountryAmount[$sCountryCode] + 1)));
			}else{
				$aOrdersWorldMapCountryAmount[$sCountryCode] = 1;
				array_push($aOrdersWorldMapCountryCode, $sCountryCode);
				array_push($aOrdersWorldMapCountryName, $sCountryName);
			}
		
		}
		
	$sHtml .= '			
		<script type="text/javascript">
		setTimeout(function() {
			$(".ordersWorldMapContainer").mapael({
				map : {
					name : "world_countries_miller"
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
						
							$aOrdersWorldMapCountryAmountUnique = array_unique($aOrdersWorldMapCountryAmount); 
							asort($aOrdersWorldMapCountryAmountUnique);
							$aOrdersWorldMapCountryAmountMax = max($aOrdersWorldMapCountryAmountUnique);
							$i = 0;
							foreach($aOrdersWorldMapCountryAmountUnique as $ivalue){
								
								$iColorRGBValue = (((round(255*($ivalue / $aOrdersWorldMapCountryAmountMax)) / 100) * 80) + 50);
								
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
					foreach($aOrdersWorldMapCountryCode as $CountryCode){
						$iPercentageOfOrders = round(((100 / $iAmountOfOrders) * $aOrdersWorldMapCountryAmount[$CountryCode]), 2, PHP_ROUND_HALF_DOWN);
						$sHtml .='
						
					"' . $CountryCode . '": {
						"value": ' . $aOrdersWorldMapCountryAmount[$CountryCode] . ',
						"tooltip": {
							"content": "<span style=\"font-weight:bold;\"> ' . $aOrdersWorldMapCountryName[$i] . ' </span> <br> bestellingen: ' . $aOrdersWorldMapCountryAmount[$CountryCode] . '<br>'. $iPercentageOfOrders . '% van bestellingen"
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
		}, 1000);
		</script>
		
		<div class="ordersWorldMapContainer">
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