<?php

	$sHtml = '';
	
	$sHtml .= '<h1>Dashboard</h1>';
	$sHtml .= '
	<div class="top-wrapper">
		<div class="news">
			<div class="news-title"><h3>Nieuws</h3></div>'; 
			
			$sNewsData = file_get_contents('http://graphxdemo.nl/newsapi');
			//print_r($sNewsData);
			$aNewsData = json_decode($sNewsData, true);
			//print_r($aNewsData);
			
			foreach($aNewsData as $aItem){
			$sHtml .= '
			<div class="news-wrapper brand-border">
				<div class="news-title">' . htmlspecialchars($aItem['title']) . '</div>
				<div class="news-description">' . htmlspecialchars($aItem['description']) . '</div>
				<div class="news-timestamp">' . date('d-m-Y', $aItem['timestamp']) . ' <a href="https://www.ideal-checkout.nl">meer info</a></div>
			</div>';
			}
	
	
	
	
	
	$sHtml .= '
		</div>
		<div class="updates">
			<div class="updates-title"><h3>Updates</h3></div>';
			
			
	/*
			<div class="updates-wrapper">Updates plugins/website</div>
			<div class="updates-wrapper">Updates plugins/website</div>
			<div class="updates-wrapper">Updates plugins/website</div>
			
	*/
			
			$sHtml .= '
		</div>
	</div>
	<div class="bottom-wrapper">
		<div class="extensions">
			<div class="extensions-title"><h3>Extensies</h3></div>';
			
			
	/*
			<div class="extensions-wrapper">Nieuwe extensies</div>
			<div class="extensions-wrapper">Nieuwe extensies</div>
			<div class="extensions-wrapper">Nieuwe extensies</div>
			
	*/
			
			$sHtml .= '
		</div>
		<div class="status">
			<div class="status-title"><h3>iDEAL status</h3></div>
			<div class="status-wrapper">iDEAL status graph?</div>
		</div>
	</div>';

	return $sHtml;

?>