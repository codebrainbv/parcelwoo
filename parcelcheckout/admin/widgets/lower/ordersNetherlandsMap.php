<?php
	$sHtml = '';
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	
	$sHtml .= '
		<script>
			function minimizeordersNetherlandsMap(){
				jQuery(\'.ordersNetherlandsMap\').toggleClass(\'minimized\');
				$( \'.ordersNetherlandsMapContent\' ).slideToggle(\'fast\');
			}
		</script>
		<link href="widgets/lower/css/ordersNetherlandsMap.css" media="screen" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="widgets/lower/js/mapael/raphael.min.js"></script>
		<script type="text/javascript" src="widgets/lower/js/mapael/jquery.mousewheel.min.js"></script>
		<script type="text/javascript" src="widgets/lower/js/mapael/jquery.mapael.min.js"></script>
		<script type="text/javascript" src="widgets/lower/js/mapael/maps/netherlands_provinces.min.js"></script>
		<div class="ordersNetherlandsMap" id="ordersNetherlandsMap">
		<div class="ordersNetherlandsMapTopDeco widgetTopDeco">
		</div>
		<div class="ordersNetherlandsMapTopInfo widgetTopInfo">
			Orders Nederland map
			<div class="minusLowerWidget" onclick="minimizeordersNetherlandsMap()"></div>
			<div class="infoIconLowerWidget" data-balloon="Bestellingen per provincie, afgelopen 30 dagen" data-balloon-pos="left">
				<img src="' . $sImagePath . '/info-icon.png" height="100%" >
			</div>
			<div class="moveIconLowerWidget noRightClick" data-balloon="houd ingedrukt om te verplaatsen" data-balloon-pos="left">
				<img src="' . $sImagePath . '/move-icon.png" height="100%" >
			</div>
		</div>
		<div onmousedown="disableLowerDragging()" onmouseup="enableLowerDragging()" ontouchstart="disableLowerDragging()" ontouchend="enableLowerDragging()" class="lowerWidgetContent ordersNetherlandsMapContent">
	';
	// [\*]
	
	// [**] codeer hier wat er in de widget moet komen te staan.
		
	$sHtml .= '			
		<script type="text/javascript">
		setTimeout(function() {
			$(".ordersNetherlandsMapContainer").mapael({
				map : {
					name : "netherlands"
					// Enable zoom on map
					, zoom: {
						enabled: false,
						maxlevel: 20
					}
				},
				legend: {
                    area: {
                        display: false,
                        title: "Bestellingen",
                        slices: [
							{
								min: "0 ",
								max: "99",
								attrs: {
									fill: "#343434"
								},
								label: "Less than 99"
							},
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
					"Drenthe": {
						"value": "2",
						"tooltip": {
							"content": "<span style=\"font-weight:bold;\"> Drenthe </span> <br> bestellingen: 2"
						}
					},
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
		
		<div class="ordersNetherlandsMapContainer">
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