<?php

	require_once(dirname(dirname(__FILE__)) . '/includes/php/parcelcheckout.php');
	// require_once(dirname(dirname(__FILE__)) . '/includes/webshop-library.php');
	require_once(dirname(__FILE__) . '/includes/install.php');
	require_once(dirname(__FILE__) . '/includes/ftp.cls.php');
	require_once(dirname(__FILE__) . '/includes/settings.php');

	if(empty($_REQUEST['software']) || empty($_REQUEST['settings']))
	{
		header('Location: index.php');
		exit;
	}


	if(!empty($_POST['form']) && (strcasecmp($_POST['form'], 'delete-install') === 0))
	{
		if(PARCELCHECKOUT_INSTALL::deleteFolder('/parcelcheckout/install'))
		{
			// echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
		}

		if(PARCELCHECKOUT_INSTALL::deleteFile('/parcelcheckout/configuration/install.php'))
		{
			// echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
		}

		header('Location: ../../');
		exit;
	}





	$sJavascript = '<script type="text/javascript">

	function goNext()
	{
		var sVal = jQuery(\'input[type="button"]:first\').val();
		var bRedirect = true;

		if(sVal.length > 0)
		{
			bRedirect = confirm(\'Wilt u verder gaan zonder de installatie map te verwijderen?\');
		}

		if(bRedirect)
		{
			window.location.href = \'../../\';
		}
	}

</script>';
	

	$sHtml = '
	<tr>
		<td colspan="2">
			<div class="progress-bar-wrapper">
				<div class="progress-bar-container">
					<div class="circle active"><div class="text">1</div></div>
					<div class="line active"></div>
					<div class="circle active"><div class="text">2</div></div>
					<div class="line active"></div>
					<div class="circle active"><div class="text">3</div></div>
					<div class="line active"></div>
					<div class="circle active"><div class="text">4</div></div>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td><h1>Installatie wizard voltooid</h1></td>
	</tr>
	<tr>
		<td>De installatie van de plugin is voltooid!</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><h2>Vragen of feedback</h2></td>
	</tr>
	<tr>
		<td>Mocht u vragen hebben over de installatie van onze plug-ins, of suggesties hebben om zaken te verbeteren, kijk dan op <a href="http://www.parcel-checkout.nl" target="_blank">www.parcel-checkout.nl</a>.</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><h2>Installatie wizard verwijderen</h2></td>
	</tr>
	<tr>
		<td>Na het verwijderen van de installatie wizard, wordt u automatisch verwezen naar uw website.<br>Mocht u de wizard nog willen gebruiken om van configuratie te wisselen kan u hem nog behouden door de wizard af te sluiten.<br>Het verwijderen wordt geadviseerd om veiligheids redenen, denk aan het wijzigen van verzendmethode configuratie.</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><form action="" method="post" name="delete-install" value=""><input name="form" type="hidden" value="delete-install"><input type="submit" value="Verwijderen"></form></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><hr size="1"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td align="right">' . $sJavascript . '<input onclick="javascript: goNext();" type="button" value="Afsluiten"></td>
	</tr>';


/*
	$sConfigFile = SOFTWARE_PATH . '/parcelcheckout/configuration/install.php';

	if(is_file($sConfigFile))
	{
			$sHtml .= '
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><code>' . nl2br(htmlentities(file_get_contents($sConfigFile))) . '</code></td>
	</tr>
	<tr>
		<td><b>/parcelcheckout/configuration/install.php</b></td>
	</tr>';
	}
*/


	PARCELCHECKOUT_INSTALL::output($sHtml);

?>