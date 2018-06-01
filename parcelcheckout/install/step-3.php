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

	$sSelectedPsp = '';
	$aSelectedPsp = false;
	$bLockPsp = false;

	$sSelectedGateway = '';
	$aSelectedGateway = false;


	// DETECT PSP
	if(!empty($_GET['carrier']) && is_string($_GET['carrier']))
	{
		$sSelectedPsp = $_GET['carrier'];
		$bLockPsp = true;
	}
	elseif(defined('PARCELCHECKOUT_PSP'))
	{
		$sSelectedPsp = PARCELCHECKOUT_PSP;
		$bLockPsp = true;
	}
	elseif(!empty($_POST['parcelcheckout_psp']) && is_string($_POST['parcelcheckout_psp']))
	{
		$sSelectedPsp = $_POST['parcelcheckout_psp'];
		$bLockPsp = !empty($_POST['parcelcheckout_psp_lock']);
	}

	if(array_key_exists($sSelectedPsp, $_REQUEST['carrier']))
	{
		$aSelectedPsp = $_REQUEST['carrier'][$sSelectedPsp];
	}
	else
	{
		$sSelectedPsp = '';
		$bLockPsp = false;
	}


	// DETECT GATEWAY
	if(!empty($aSelectedPsp))
	{
		if(!empty($_GET['gateway']) && is_string($_GET['gateway']))
		{
			$sSelectedGateway = $_GET['gateway'];
		}
		elseif(!empty($_POST['parcelcheckout_gateway']) && is_string($_POST['parcelcheckout_gateway']))
		{
			$sSelectedGateway = $_POST['parcelcheckout_gateway'];
		}

		foreach($_REQUEST['carrier'][$sSelectedPsp]['carriers'] as $k => $aGateway)
		{
			if($sSelectedGateway == $aGateway['code'])
			{
				$aSelectedGateway = $aGateway;
				break;
			}
		}

		if(empty($aSelectedGateway))
		{
			$sSelectedGateway = '';
		}
	}

	$bConfigurationPosted = false;
	$bConfigurationSaved = false;
	$sGatewayCode = false;

	if(!empty($aSelectedGateway))
	{
		if(!empty($_POST['save']) && !empty($_POST['gateway_check']) && (strcasecmp($aSelectedGateway['code'], $_POST['gateway_check']) === 0))
		{
			$bConfigurationPosted = true;
			$bConfigurationSaved = PARCELCHECKOUT_INSTALL::saveFormFields($aSelectedGateway);
			$sGatewayCode = $aSelectedGateway['type'];
		}
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
						<div class="circle active"><div class="text">3</div></div>
						<div class="line active"></div>
						<div class="circle"><div class="text">4</div></div>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td><h1>Verzendmethode configureren</h1></td>
		</tr>
		<tr>
			<td>Via de onderstaande stappen kunt u de gewenste verzendmethoden configureren om echte betalingen te kunnen ontvangen via uw Payment Service Provider.</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>';



	if($bConfigurationPosted)
	{
		if($bConfigurationSaved)
		{
			$sHtml .= '
		<tr class="hide-c">
			<td><div class="success">De configuratie is succesvol opgeslagen. Herhaal stap A t/m C om meer verzendmethoden te configureren, of ga naar de <a href="step-4.php">volgende stap</a>.</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>';
		}
		else
		{
			$sHtml .= '
		<tr class="hide-c">
			<td><div class="success">De configuratie kon niet worden opgeslagen. Controleer uw instellingen en probeer het opnieuw.</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>';
		}
	}



	if($bLockPsp)
	{
		$sHtml .= '
		<tr>
			<td><h2>A. Uw provider</h2></td>
		</tr>
		<tr>
			<td>' . htmlentities($aSelectedPsp['name']) . '<input type="hidden" name="parcelcheckout_psp" value="' . htmlentities($sSelectedPsp) . '"><input type="hidden" name="parcelcheckout_psp_lock" value="1"></td>
		</tr>';
	}
	else
	{
		$sHtml .= '
		<tr>
			<td><h2>A. Uw provider <em>*</em></h2></td>
		</tr>
		<tr>
			<td>De vezendmethoden via een van onze plug-ins kan alleen als u een account<br> of abonnement heeft een van de onderstaande providers.</td>
		</tr>
		<tr>
			<td><select name="parcelcheckout_psp" onchange="javascript: jQuery(\'.hide-b\').hide(); jQuery(\'.hide-c\').hide();">';

		foreach($_REQUEST['carrier'] as $k => $aPsp)
		{
			if($sSelectedPsp == $k)
			{
				$sHtml .= '<option value="' . $k . '" selected="selected">' . htmlentities($aPsp['name']) . '</option>';
			}
			else
			{
				$sHtml .= '<option value="' . $k . '">' . htmlentities($aPsp['name']) . '</option>';
			}
		}

		$sHtml .= '</select></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><input name="set_psp" type="submit" value="Selecteren"></td>
		</tr>';
	}


	if(!empty($aSelectedPsp))
	{
		$sHtml .= '
		<tr class="hide-b">
			<td>&nbsp;</td>
		</tr>
		<tr class="hide-b">
			<td>&nbsp;</td>
		</tr>
		<tr class="hide-b">
			<td><h2>B. Selecteer het abonnement <em>*</em></h2></td>
		</tr>
		<tr class="hide-b">
			<td>Controleer bij uw provider welke verzendmethoden onderdeel zijn van uw abonnement.</td>
		</tr>
		<tr class="hide-b">
			<td><select name="parcelcheckout_gateway" onchange="javascript: jQuery(\'.hide-c\').hide();">'; 

		foreach($aSelectedPsp['carriers'] as $k => $aGateway)
		{
			if($sSelectedGateway == $aGateway['code'])
			{
				$sHtml .= '<option value="' . htmlentities($aGateway['code']) . '" selected="selected">' . htmlentities($aGateway['name']) . '</option>';
			}
			else
			{
				$sHtml .= '<option value="' . htmlentities($aGateway['code']) . '">' . htmlentities($aGateway['name']) . '</option>';
			}
		}

		$sHtml .= '</select></td>
		</tr>
		<tr class="hide-b">
			<td>&nbsp;</td>
		</tr>
		<tr class="hide-b">
			<td><input name="set_gateway" type="submit" value="Selecteren"></td>
		</tr>';
	}

	if(!empty($aSelectedGateway))
	{


		$sHtml .= '
		<tr class="hide-c">
			<td>&nbsp;</td>
		</tr class="hide-c">
		<tr class="hide-c">
			<td>&nbsp;</td>
		</tr>
		<tr class="hide-c">
			<td><h2>C. Bepaal uw specifieke instellingen</h2></td>
		</tr>';

		if(!empty($aSelectedGateway['dashboard']))
		{
			$sHtml .= '
		<tr class="hide-c">
			<td>' . $aSelectedGateway['dashboard'] . '</td>
		</tr>';
		}
		else
		{
			$sHtml .= '
		<tr class="hide-c">
			<td>Van uw bank/PSP heeft u toegang tot een online dashboard gekregen, waarop uw deze<br>gegevens kunt terug vinden.</td>
		</tr>';
		}

		$sHtml .= '
		<tr class="hide-c">
			<td>&nbsp;</td>
		</tr>';

/*
		if($bConfigurationPosted)
		{
			if($bConfigurationSaved)
			{
				$sHtml .= '
		<tr class="hide-c">
			<td><div class="success">De configuratie is succesvol opgeslagen. Herhaal stap A t/m C om meer betaalmethoden te configureren, of ga naar de <a href="step-4.php">volgende stap</a>.<br><br>Tip: Klik <a href="../test.php" target="_blank">hier</a> om de instellingen van uw betaalmethoden direct te testen (opent in nieuw venster).</td>
		</tr>';
			}
			else
			{
				$sHtml .= '
		<tr class="hide-c">
			<td><div class="success">De configuratie kon niet worden opgeslagen. Controleer uw instellingen en probeer het opnieuw.</td>
		</tr>';
			}
		}
*/

		$sHtml .= PARCELCHECKOUT_INSTALL::drawFormFields($aSelectedGateway);

		$sHtml .= '
		<tr class="hide-c">
			<td>&nbsp;</td>
		</tr>
		<tr class="hide-c">
			<td><input name="gateway_check" type="hidden" value="' . htmlentities($aSelectedGateway['code']) . '"><input type="submit" name="save" value="Opslaan"> &nbsp; <i>' . ($bConfigurationSaved ? '<br>Uw instellingen zijn opgeslagen in: /parcelcheckout/configuration/carrier.php' : '') . '</i></td>
		</tr>';
	}


	$sJavascript = '<script type="text/javascript">

	function goNext()
	{
		var sVal = jQuery(\'input[type="text"]:first\').val();
		var bRedirect = true;

		if(sVal && (sVal.length > 0))
		{
			bRedirect = confirm(\'Wilt u verder gaan zonder eerst uw configuratie op te slaan?\');
		}

		if(bRedirect)
		{
			window.location.href = \'step-4.php\';
		}
	}

</script>';
	
	
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
			<td align="right">' . $sJavascript . '<input onclick="javascript: goNext();" type="button" value="Verder"></td>
		</tr>';

	PARCELCHECKOUT_INSTALL::output($sHtml, 'install-step-3');


?>