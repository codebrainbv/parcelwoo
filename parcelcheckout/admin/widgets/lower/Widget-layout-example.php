<?php
	$sHtml = '';
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	
	$sHtml .= '
		<script>
			function minimizelowerWidget3(){
				jQuery(\'.lowerWidget3\').toggleClass(\'minimized\');
				$( \'.lowerWidget3Content\' ).slideToggle(\'fast\');
			}
		</script>
		<link href="widgets/lower/css/lowerwidget3.css" media="screen" rel="stylesheet" type="text/css">
		<div class="lowerWidget3" id="lowerWidget3">
		<div class="lowerWidget3TopDeco widgetTopDeco">
		</div>
		<div class="lowerWidget3TopInfo widgetTopInfo">
			WIDGET EXPL
			<div class="minusLowerWidget" onclick="minimizelowerWidget3()"></div>
			<div class="infoIconLowerWidget" data-balloon="Whats up!" data-balloon-pos="left">
				<img src="' . $sImagePath . '/info-icon.png" height="100%" >
			</div>
			<div class="moveIconLowerWidget noRightClick" data-balloon="houd ingedrukt om te verplaatsen" data-balloon-pos="left">
				<img src="' . $sImagePath . '/move-icon.png" height="100%" >
			</div>
		</div>
		<div onmousedown="disableLowerDragging()" onmouseup="enableLowerDragging()" ontouchstart="disableLowerDragging()" ontouchend="enableLowerDragging()" class="lowerWidgetContent lowerWidget3Content">
	';
	// [\*]
	
	// [**] codeer hier wat er in de widget moet komen te staan.
	$sHtml .= '
			
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