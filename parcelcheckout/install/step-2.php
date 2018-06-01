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

	$sInstructionsHtml = '';

	if(is_callable($_REQUEST['software'] . '::getInstructions'))
	{
		$sInstructionsHtml = call_user_func($_REQUEST['software'] . '::getInstructions', $_REQUEST['settings']); // $_REQUEST['software']::getInstructions($_REQUEST['settings']);
	}


	$sAdminUrl = false;

	if(is_callable($_REQUEST['software'] . '::getAdminUrl'))
	{
		$sAdminUrl = call_user_func($_REQUEST['software'] . '::getAdminUrl'); // $_REQUEST['software']::getAdminUrl();
	}

	if(empty($sAdminUrl))
	{
		$sAdminUrl = parcelcheckout_getRootUrl(2);
	}


	$sHtml = '
	<tr>
		<td colspan="2">
			<div class="progress-bar-wrapper">
				<div class="progress-bar-container">
					<div class="circle active"><div class="text">1</div></div>
					<div class="line active"></div>
					<div class="circle active"><div class="text">2</div></div>
					<div class="line active"></div>
					<div class="circle"><div class="text">3</div></div>
					<div class="line"></div>
					<div class="circle"><div class="text">4</div></div>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td><h1>Verzendmethode activeren</h1></td>
	</tr>
	<tr>
		<td>Om de verzendmethoden voor uw klanten beschikbaar te maken in uw webshop, moeten deze eerst geactiveerd/ingeschakeld worden via de <a href="' . htmlentities($sAdminUrl) . '" target="_blank">beheeromgeving van uw webshop</a>.<br><br><span class="lightbulb">Activeer alleen de verzendmethoden die ondersteund worden bij het abonnement dat u, bij PostNL heeft afgesloten.</span></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>';


	if(empty($sInstructionsHtml))
	{
		$sHtml .= '
	<tr>
		<td><h1>Instructies</h1></td>
	</tr>
	<tr>
		<td>Door in te loggen op de beheeromgeving van uw website kunt u de gewenste verzendmethode in- en uitschakelen voor uw klanten.</td>
	</tr>';
	}
	else
	{
		$sHtml .= '
	<tr>
		<td><h1>Instructies</h1></td>
	</tr>
	<tr>
		<td>' . $sInstructionsHtml . '</td>
	</tr>';
	}


	$sHtml .= '
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
		<td align="right"><input onclick="javascript: window.location.href = \'step-3.php\';" type="button" value="Verder"></td>
	</tr>';


	PARCELCHECKOUT_INSTALL::output($sHtml);

?>