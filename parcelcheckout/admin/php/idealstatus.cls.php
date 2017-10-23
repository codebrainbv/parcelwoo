<?php

	class clsIdealstatus
	{
		protected static $sCachePath;
		
		
		// Should point to directory where cache is strored
		public static function setCachePath($sPath = false)
		{
			self::$sCachePath = $sPath;
		}
		
		public static function doRequest($bAdvanced = false)
		{
			$sCacheFile = false;

			if(self::$sCachePath)
			{
				$sCacheFile = self::$sCachePath . 'idealstatus.cache';

				if(is_file($sCacheFile) == false)
				{
					// Attempt to create cache file
					if(@touch($sCacheFile))
					{
						@chmod($sCacheFile, 0777);
					}
					else
					{
						$sCacheFile = false;						
					}
				}
				elseif(is_readable($sCacheFile) && is_writable($sCacheFile))
				{
					if(filemtime($sCacheFile) > strtotime('-15 Minutes'))
					{
						// Read data from cache file
						if($sData = file_get_contents($sCacheFile))
						{
							return parcelcheckout_unserialize($sData);
						}
					}
				}
				else
				{
					$sCacheFile = false;
				}
			}
			
			$aIdealstatus = array();
			
			if($bAdvanced)
			{
				$sUrl = 'https://www.ideal-status.nl/static/issuers_current.json';
			}
			else
			{	
				$sUrl = 'https://www.ideal-status.nl/static/consumer_notification_advice.json';
			}
			
			$sPostData = '';
			
			$sResponse = parcelcheckout_doHttpRequest($sUrl, $sPostData, true, 30, false, false);
			
			if(!empty($sResponse))
			{
				parcelcheckout_log($sResponse, __FILE__, __LINE__);

				$aIdealstatus = json_decode($sResponse, true);

				if($aIdealstatus)
				{
					// Save data in cache?
					if($sCacheFile)
					{
						file_put_contents($sCacheFile, $sResponse);
					}
					
					return $aIdealstatus;
				}
				else
				{
					if(parcelcheckout_getDebugMode())
					{
						return $aIdealstatus = array('error' => array('message' => 'Cannot decode JSON response (See logs).'));
					}
					else
					{
						if(is_readable($sCacheFile) && is_writable($sCacheFile))
						{	
							// Read data from cache file
							if($sData = file_get_contents($sCacheFile))
							{
								return parcelcheckout_unserialize($sData);
							}
						} 
					}
				}
			}
			elseif(parcelcheckout_getDebugMode())
			{
				return $aIdealstatus = array('error' => array('message' => 'No response received from ideal-status.nl (See logs).'));
			}
			
			
			return array();
		}
		
		
		
		public static function getHtml($bAdvanced = false)
		{
			$sHtml = '';
			
			$aData = self::doRequest($bAdvanced);
			$sImagePath = parcelcheckout_getRootUrl(). 'images';
			// print_r($aData);	

			
			if($aData === false)
			{
				$sHtml .= 'No data found in request or cache, please contact iDEAL Checkout';
			}
			else
			{
				
				$aSvgBarGraph = array('');
				$sGraphHtml = '';
				$iY = 0;
				$iY2 = 5;
				$iTextCoord = 11;
				foreach($aData as $aIssuer)
				{	
					$sGraphHtml .= '
					<g class="bar-graph-bar">
						<rect rx="5" ry="5" class="rect100" width="100%" height="22" y="' . $iY . '"></rect>
						<rect rx="6" ry="6" width="' . (clsInt::toPercentage($aIssuer['rate_success']) - 32.8125) . '%" height="12" y="' . $iY2 . '" ></rect>
						<text x="100%" dx="-210px" y="' . $iTextCoord . '" dy=".35em"> ' . clsInt::toPercentage($aIssuer['rate_success']) . ' ' . $aIssuer['issuer_name'] . '</text>
					</g>';
					$iY = $iY + 26;
					$iY2 = $iY2 + 26;
					$iTextCoord = $iTextCoord + 26; 	
				}
							
				$sHtml .= '
				<div class="idealstatus-wrapper">
				
				<svg class="idealstatus-bar-graph" viewBox="0 0 640 256" aria-labelledby="title desc" role="img">
				  '. $sGraphHtml . '
				  <rect class="rect-tresh" x="298" width="4px" height="256" y="0"></rect>
				</svg>
				
				<div class="legenda-text-button">
				<div id="ideal-status-legenda" class="no-legenda"> 
					<b>donkergrijze balk:</b> weergeeft het succes percentage<br>
					<b>roze balk:</b> weergeeft het 80% punt, banken onder de 80% hebben mogelijk een storing <br>
					<hr>
					<b>succes:</b> aantal succesvolle betalingen en de duur<br>
					
					<b>mislukt:</b> aantal mislukte betalingen en de duur<br>
					
					<b>geannuleerd:</b> aantal geannuleerde betalingen en de duur<br>
					<b>GECHECKT:</b> de tijd wanneer de data berekend is<br>
					<hr>
					<b>wanneer er 0% staat hebben wij geen data binnen gekregen, dit baart geen zorgen.</b> <br>
				</div> 
				<button class="ideal-status-legenda-button" title="Legenda" type="button" onclick="javascript: jQuery(\'#ideal-status-legenda\').toggleClass(\'show-legenda\');">legenda</button>
				</div>
				
				';	
				
				if($bAdvanced)
				{
					foreach($aData as $aIssuer)
					{
						
						/*
							<div class="issuer-id">' . $aIssuer['issuer_id'] . '</div>
							<div class="issuer-bic">' . $aIssuer['issuer_bic'] . '</div>
						
						*/
						$sHtml .= '
						<div class="idealstatus-issuer-advanced issuer-status-' . ((floatval($aIssuer['rate_success']) < 0.795) ? 'nok' : 'ok') . ' brand-border">
							<div class="issuer-name">' . $aIssuer['issuer_name'] . '</div>
							
							<table style="width:100%">
								<tr>
									<td><b>succes:</b> </td>
									<td>' . clsInt::toPercentage($aIssuer['rate_success']) . ', ' . $aIssuer['avg_time_success'] . ' seconden</td>
								</tr>
								<tr>
									<td><b>mislukt:</b> </td>
									<td>' . clsInt::toPercentage($aIssuer['rate_failure']) . ', ' . $aIssuer['avg_time_failure'] . ' seconden</td>
								</tr>
								<tr>
									<td><b>geannuleerd:</b> </td>
									<td>' . clsInt::toPercentage($aIssuer['rate_cancelled']) . ', ' . $aIssuer['avg_time_cancelled'] . ' seconden</td>
								</tr>
							</table>
								';
						if(!empty($aIssuer['notifications']))
						{
							
							
						}
							
							$sHtml .= '
							<div class="issuer-lastreport" style="padding-top: 6px;"><b>GECHECKT:</b><br>' . $aIssuer['datetime'] . '</div>
						
						</div>
						';
					}
					
					/*
			[issuer_id] => 0021
            [datetime] => 2016-09-13 13:07:49
            [avg_time_trx] => 0
            [avg_time_success] => 72
            [avg_time_failure] => 68
            [avg_time_expired] => 0
            [avg_time_cancelled] => 110
            [avg_time_open] => 0
            [rate_success] => 0.9238
            [rate_success_finalized] => 0.9510
            [rate_failure] => 0.0190
            [rate_failure_finalized] => 0.0196
            [rate_expired] => 0.0000
            [rate_expired_finalized] => 0.0000
            [rate_cancelled] => 0.0286
            [rate_cancelled_finalized] => 0.0294
            [rate_open] => 0.0286
            [rate_error] => 0.0000
            [bench_success] => 0.6500
            [bench_failure] => 0.1500
            [issuer_name] => Rabobank
            [notifications] => Array
					*/
					
				}
				else
				{

					foreach($aData as $aIssuer)
					{
						
						/*
							<div class="issuer-id">' . $aIssuer['issuer_id'] . '</div>
						
						*/
						
						$sHtml .= '
						<div class="idealstatus-issuer-basic issuer-status-' . strtolower($aIssuer['status']) . ' brand-border">
							<div class="issuer-name">' . $aIssuer['issuer_name'] . '</div>';
							
							if($aIssuer['status'] == 'OK'){
								$sHtml .= '	<div class="issuer-status">STATUS:<img class="status-img" src="' . $sImagePath . '/status-ok.png"></div>';
							}else{
								$sHtml .= '	<div class="issuer-status">STATUS:<img class="status-img" src="' . $sImagePath . '/status-fail.png"></div>
											<div class="issuer-message">INFO:&nbsp' . $aIssuer['message'] . '</div>';
							}
							$sHtml .= '
							<div class="issuer-bic">BIC-nummer:&nbsp' . $aIssuer['issuer_bic'] . '</div>
							<div class="issuer-lastreport">GECHECKT:&nbsp' . $aIssuer['datetime'] . '</div>
						</div>
						';
					}
				}
				
				$sHtml .= '<div class="cleared"></div></div>';
			}
			
			return $sHtml;
		}
		
		
		
		
		
		
		
		
		
	}
	
	
	
?>