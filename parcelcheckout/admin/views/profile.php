<?php

	$sHtml = '';
	
	$sHtml .= '<h1>Uw profiel</h1>';

	$sProfileHtml = clsProfile::getHtml();
	$sHtml .= '<div class="profile-wrapper">' . $sProfileHtml . '</div>';
	return $sHtml;

?>