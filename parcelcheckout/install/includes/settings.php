<?php

	if(!defined('LF'))
	{
		define('LF', chr(10)); // Line feed '\n'
	}

	if(!defined('TAB'))
	{
		define('TAB', chr(9)); // Tab '\t'
	}

	if(!defined('DS')) // Directory seperator
	{
		define('DS', (strpos(dirname(__FILE__), '\\') ? '\\' : '/')); 
	}

	if(!defined('PARCELCHECKOUT_PATH'))
	{
		define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__FILE__))));
	}

	if(!defined('SOFTWARE_PATH'))
	{
		define('SOFTWARE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
	}





	// Initiate software
	$sSoftwareClass = PARCELCHECKOUT_INSTALL::setSoftware();

	if(empty($sSoftwareClass))
	{
		parcelcheckout_output('<p>Cannot detect the current software!<br>Please try to install another plug-in or version suitable for your software.</p>');
	}

	$_REQUEST = array();
	$_REQUEST['software'] = $sSoftwareClass;
	$_REQUEST['settings'] = false;

	$sInstallFile = dirname(dirname(dirname(__FILE__))) . '/configuration/install.php';

	if(is_file($sInstallFile))
	{
		$_REQUEST['settings'] = include($sInstallFile);
	}





	// Verify read/write privileges
	$_REQUEST['files-and-folders'] = PARCELCHECKOUT_INSTALL::getFilesAndFolders($sSoftwareClass);

	$bWritable = true;

	foreach($_REQUEST['files-and-folders'] as $sPath)
	{
		if(!is_writable($sPath))
		{
			$bWritable = false;
			break;
		}
	}

	if(!defined('FTP_ACCESS_REQUIRED'))
	{
		define('FTP_ACCESS_REQUIRED', $bWritable === false);
	}



	$_REQUEST['carrier'] = array();

	$_REQUEST['carrier']['postnl']['name'] = 'PostNL';
	$_REQUEST['carrier']['postnl']['carriers'] = array();
	$_REQUEST['carrier']['postnl']['carriers'][] = array('code' => 'ECS', 'type' => 'postnl', 'name' => 'PostNL ECS', 'dashboard' => 'Begeleidende tekst dashboard/inlog pagina van de fulfillment toevoegen');
	
	/*
	$_REQUEST['carrier'] = array();

	$_REQUEST['carrier']['postnl']['name'] = 'PostNL';
	$_REQUEST['carrier']['postnl']['carrier'] = array();
	$_REQUEST['carrier']['postnl']['gateways'][] = array('code' => 'ABN Amro - iDEAL Easy', 'type' => 'ideal', 'name' => 'iDEAL Easy', 'dashboard' => 'Van uw bank/PSP heeft u een e-mail of brief ontvangen, waarop u uw PSP ID kunt terug vinden. U kunt deze ook opvragen bij de "Special Desk e-Commerce" van de ABN Amro: 020 - 383 24 94 (lokaal tarief).<br><br>iDEAL Easy is beveiligd zodat alleen betalingen vanaf specifieke URLs gestart mogen worden. U moet aan de "Special Desk e-Commerce" doorgeven vanaf welke URLs de betalingen gestart kunnen worden. Houdt hierbij rekening of uw URLs beginnen met http:// of https:// en of uw URLs wel of niet "www" bevatten.<br><br><span class="lightbulb"><i>Let op: Deze iDEAL variant biedt geen terugkoppeling van de betaalstatus, en is daarom voor veel webshoppakketten ongeschikt! U kunt in veel gevallen beter gebruik maken van iDEAL Zelfbouw.</i></span>');
	*/

?>