<?php

	$sInstallFolder = dirname(__FILE__) . '/install';

	if(is_dir($sInstallFolder))
	{
		header('Location: install/index.php');
		exit;
	}
	else
	{
		$sHtml = '
<!DOCTYPE HTML>
<html>
	<head>
		<title>Parcel Checkout - Installatie</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta http-equiv="content-language" content="nl-nl">
		
		<link href="http://www.parcel-checkout.nl/manuals/install/install.css" media="screen" rel="stylesheet" type="text/css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	</head>
	<header class="fixed">
		<div class="center">
			<div class="logo-wrapper"><img alt="PostNL" border="0" height="120" src="http://www.parcel-checkout.nl/manuals/install/postnl-logo.png"></div>
			<div class="slogan right">
				<div class="color">Fulfilment</div>
				<div class="text">&nbsp;</div>
			</div>
		</div>
		<div class="swirl"></div>
	</header>
	<body><!--

			This Parcel Checkout script is developed by:

			Parcel Checkout

			Support & Information:
			W. http://www.parcel-checkout.nl
			E. support@parcel-checkout.nl
			T. +31522 746 060

		-->
		<table align="center" border="0" cellpadding="3" cellspacing="0" width="580">
			<tr>
				<td align="left" height="180" valign="top"><a href="http://www.parcel-checkout.nl" target="_blank"><img alt="Parcel Checkout" border="0" src="http://www.ideal-checkout.nl/manuals/parcel-install/parcel-checkout-logo.png"></a></td>
				
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><h1>Security Warning</h1></td>
			</tr>
			<tr>
				<td>You are not allowed to view any files within this directory due to security reasons.</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><hr size="1"></td>
			</tr>
		</table>
	</body>
</html>';



	// To change when the website/space is live:
	/* 
	<link href="http://www.parcel-checkout.nl/manuals/install/install.css" media="screen" rel="stylesheet" type="text/css">
	
	
	<td align="left" height="180" valign="top"><a href="http://www.parcel-checkout.nl" target="_blank"><img alt="Parcel Checkout" border="0" src="http://www.parcel-checkout.nl/manuals/parcel-install/parcel-checkout-logo.png"></a></td>


	
	
	
	*/

		echo $sHtml;
		exit;
	}


?>