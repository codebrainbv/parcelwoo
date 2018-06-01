<?php

	if(!empty($_GET['phpinfo']))
	{
		phpinfo();
		exit;
	}
	
	require_once(dirname(dirname(__FILE__)) . '/includes/php/parcelcheckout.php');
	require_once(dirname(__FILE__) . '/includes/install.php');
	require_once(dirname(__FILE__) . '/includes/ftp.cls.php');
	require_once(dirname(__FILE__) . '/includes/settings.php');

	$sConfigFile = dirname(dirname(__FILE__)) . '/configuration/install.php';

	if(is_file($sConfigFile))
	{
		header('Location: step-2.php');
		exit;
	}

	$aCurlVersion = array();
	
	if(function_exists('curl_version'))
	{
		$aCurlVersion = curl_version();
	}
	
	$sPhpVersion = PHP_VERSION;
	$sOpensslVersion = (defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : '<b>Onbekend</b>');
	$sCurlVersion = (!empty($aCurlVersion['version']) ? $aCurlVersion['version'] : '<b>Onbekend</b>') . (!empty($aCurlVersion['ssl_version']) ? ' met ' . $aCurlVersion['ssl_version'] : '');
	$sMysqliVersion = (function_exists('mysqli_get_client_info') ? mysqli_get_client_info() : '<b>Onbekend</b>');

	
	$sHtml = '
	<tr>
		<td colspan="2">
			<div class="spacer-top">
			</div>
		</td>
	</tr>
	<tr>
		<td><h1>Parcel Checkout Installatie Wizard</h1></td>
	</tr>
	<tr>
		<td>Welkom bij de installatie wizard van deze plug-in. Heeft u vragen over de installatie van de plug-ins, wilt u gebruik maken van onze installatie service, of heeft u suggesties om zaken te verbeteren, kijk dan op <a href="https://www.parcel-checkout.nl" target="_blank">www.parcel-checkout.nl</a> of neem contact met ons op.</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><h3>Let op!</h3></td>
	</tr>
	<tr>
		<td>Op sommige server configuraties kan de installatie wizard of de plugin niet correct werken. Enkele voorbeelden van server beperkingen die voor problemen kunnen zorgen:<br>&nbsp;
			<ul>
				<li>Verouderde PHP versie (Minimaal versie 5.3, u gebruikt versie: ' . $sPhpVersion . ')</li>
				<li>Verouderde OpenSSL bibliotheek (U gebruikt versie: ' . $sOpensslVersion . ')</li>
				<li>Ontbreken van de cUrl bibliotheek (U gebruikt versie: ' . $sCurlVersion . ')</li>
				<li>Ontbreken van de MySQLi bibliotheek (U gebruikt versie: ' . $sMysqliVersion . ')</li>
				<li>Onjuiste configuratie van de ca-bundle, <a href="http://en.wikipedia.org/wiki/Intermediate_certificate_authorities" target="_blank">klik hier</a> (nieuw venster) voor meer informatie</li>
				<li>Ontbreken van de beveiligingspatch "veiligheidspatch 5746" van 2010.</li>
			</ul>
			
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td align="right"><a href="index.php?phpinfo=1" target="_blank">PHP informatie bekijken</a> <small>(Nieuw venster)</small></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><h3>Disclaimer</h3></td>
	</tr>
	<tr>
		<td>Het gebruik van onze scripts/plug-ins is op eigen risico! Maak altijd een back-up (van uw bestanden en uw database) voor u de scripts installeert.<br><br>Wij zijn experts op gebied van PHP en online betaalsystemen, maar hebben echter (zeer) beperkte kennis van het specifieke software pakket dat u gebruikt. Voor vragen over het gebruik, installatie of configuratie van uw webshop/webapplicatie kunt u dan ook het beste contact op nemen met de makers van deze software.<br><br>Bekijk onze disclaimer <a href="https://www.ideal-checkout.nl/over-ons/disclaimer" target="_blank">hier</a>.</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><h3>Webshop installeren en testen</h3></td>
	</tr>
	<tr>
		<td><p>Zorg dat uw webshop/webapplicatie volledig heb geinstalleerd en geconfigureerd is. Test uw webshop grondig voordat u de installatie van onze plug-in start, zodat u zeker weet dat alles goed functioneert.</td>
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
		<td align="right"><input onclick="javascript: window.location.href = \'step-1.php\';" type="button" value="Verder"></td>
	</tr>';

	PARCELCHECKOUT_INSTALL::output($sHtml);

?>