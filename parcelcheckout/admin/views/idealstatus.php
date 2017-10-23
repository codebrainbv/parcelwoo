<?php

	$sHtml = ''; //sHtml leeg maken
	$bAdvanced = TRUE;
	
	$sHtml .= '<h1>iDEAL Status</h1>'; //aan sHtml toevoegen (.=) '<h1>iDEAL Status</h1>'
	
	$sql = "SELECT `value` FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_settings` WHERE (`key` = 'idealstatus_advanced') LIMIT 1";
	$iAdvanced = parcelcheckout_database_getValue($sql);

	if($iAdvanced > 0)
	{
		$bAdvanced = true;		
	}

	
	$sStatusHtml = clsIdealstatus::getHtml($bAdvanced); // getHtml is een functie in de clsIdealstatus bibliotheek 
	
	$sHtml .= '<div class="ideal-status-wrapper">' . $sStatusHtml . '</div>'; // aan sHtml toevoegen (.=) '<div class="ideal-status-wrapper">'

	return $sHtml; // laat de inhoud van sHtml zien
	
	
	
?>