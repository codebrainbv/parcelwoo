<?php

	class clsGraph
	{
		// Data block colors
		protected $aColors = array(
			'black' => array('red' => 0, 'green' => 0, 'blue' => 0), // black 
		);

		public function __construct()
		{
		}

		public function addColor($sColorName, $r, $g, $b)
		{
			$this->aColors[$sColorName] = array('red' => $r, 'green' => $g, 'blue' => $b);
		}

		public function draw($aData)
		{
			$iFontSize = 2;
			$sGraphType = 'lines'; // 'lines', 'dots' or 'bars'

			$aGraphData = array();
			$aImageColors = array();

			$aAxisX = array();
			$aAxisY = array();

			$aAxisX['labels_rotate'] = false;
			$aAxisY['labels_rotate'] = false;
			$aAxisX['min'] = 0;
			$aAxisX['max'] = false;
			$aAxisY['min'] = false;
			$aAxisY['max'] = false;
			$aAxisX['grid'] = !empty($aData['grid']);
			$aAxisY['grid'] = !empty($aData['grid']);


			// Validate type
			if(isset($aData['type']) && in_array($aData['type'], array('lines', 'dots', 'bars')))
			{
				$sGraphType = $aData['type'];
			}


			// Validate font (1-5, default 2)
			if(isset($aData['font']) && in_array($aData['font'], array(1, 2, 3, 4, 5)))
			{
				$iFontSize = $aData['font'];
			}


			// Calculate label width for overlap x/y axis
			$iFontWidth = imagefontwidth($iFontSize);
			$iFontHeight = imagefontheight($iFontSize);

			$iMaxLabelWidthX = 0;
			$iMaxLabelWidthY = 0;
			$iMaxLabelHeight = $iFontHeight;
			


			$aAxisX['spacer'] = 20; // Pixels between X-positions
			$aAxisX['precision'] = 1; // The increasing value of each spacer
			$aAxisX['labels'] = array(); // Labels for X axis


			// Validate x['spacer']
			if(isset($aData['x']['spacer']) && is_numeric($aData['x']['spacer']) && ($aData['x']['spacer'] > 0))
			{
				$aAxisX['spacer'] = intval($aData['x']['spacer']);
			}


			// Validate $aData['labels'] (labels on X-axis)
			if(isset($aData['labels']))
			{
				if(is_array($aData['labels']))
				{
					foreach($aData['labels'] as $k => $v)
					{
						if(is_int($k) && is_string($v))
						{
							if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
							{
								$v = stripslashes($v); 
							}

							$aAxisX['labels'][$k] = $v;

							$iLabelWidth = ($iFontWidth * strlen($v));

							if($aAxisX['min'] > $k)
							{
								$aAxisX['min'] = $k;
							}

							if($aAxisX['max'] < $k)
							{
								$aAxisX['max'] = $k;
							}

							if($iMaxLabelWidthX < $iLabelWidth)
							{
								$iMaxLabelWidthX = $iLabelWidth;
							}
						}
						else
						{
							error('Invalid $aData[\'labels\'][' . $k . '].', __FILE__, __LINE__);
						}
					}

					// Fill empty labels
					for($i = $aAxisX['min']; $i <= $aAxisX['max']; $i++)
					{
						if(empty($aAxisX['labels'][$i]))
						{
							$aAxisX['labels'][$i] = '';
						}
					}

					ksort($aAxisX['labels']);
				}
				elseif(is_string($aData['labels']))
				{
					if(strcasecmp($aData['labels'], 'days') === 0) // 1 - 31
					{
						$aAxisX['labels'] = array('', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31');
						$aAxisX['max'] = 31;

						$iMaxLabelWidthX = ($iFontWidth * 2);
					}
					elseif(strcasecmp($aData['labels'], 'weeks') === 0) // 1 - 53
					{
						$aAxisX['labels'] = array('', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53');
						$aAxisX['max'] = 53;

						$iMaxLabelWidthX = ($iFontWidth * 2);
					}
					elseif(strcasecmp($aData['labels'], 'months') === 0) // Jan - Dec
					{
						$aAxisX['labels'] = array('', clsLanguage::getTranslation('date', 'Jan'), clsLanguage::getTranslation('date', 'Feb'), clsLanguage::getTranslation('date', 'Mar'), clsLanguage::getTranslation('date', 'Apr'), clsLanguage::getTranslation('date', 'May'), clsLanguage::getTranslation('date', 'Jun'), clsLanguage::getTranslation('date', 'Jul'), clsLanguage::getTranslation('date', 'Aug'), clsLanguage::getTranslation('date', 'Sep'), clsLanguage::getTranslation('date', 'Oct'), clsLanguage::getTranslation('date', 'Nov'), clsLanguage::getTranslation('date', 'Dec'));
						$aAxisX['max'] = 12;

						$iMaxLabelWidthX = ($iFontWidth * 3);

						if($iMaxLabelWidthX > ($aAxisX['spacer'] - 8))
						{
							$aAxisX['labels_rotate'] = true;
						}
					}
					else
					{
						error('Invalid $_GET[\'labels\'], please use "days" (1-31), "weeks" (1-53) or "months" (Jan-Dec).', __FILE__, __LINE__);
					}
				}
				else
				{
					error('Invalid $_GET[\'labels\'].', __FILE__, __LINE__);
				}
			}

			if($iMaxLabelWidthX > ($aAxisX['spacer'] - 8))
			{
				$aAxisX['labels_rotate'] = true;
			}

			$aAxisX['labels_width'] = $iMaxLabelWidthX;






			$aAxisY['spacer'] = 20; // Pixels between Y-positions
			$aAxisY['precision'] = 1; // The increasing value of each spacer
			$aAxisY['decimals'] = 0; // Number of decimals in Y-label

			// Validate $aData['y']['spacer']
			if(isset($aData['y']['spacer']) && is_numeric($aData['y']['spacer']) && ($aData['y']['spacer'] > 0))
			{
				$aAxisY['spacer'] = intval($aData['y']['spacer']);
			}

			// Validate $aData['y']['precision']
			if(isset($aData['y']['precision']) && is_numeric($aData['y']['precision']) && ($aData['y']['precision'] > 0))
			{
				$aAxisY['precision'] = floatval($aData['y']['precision']);
			}

			$aAxisY['decimals'] = clsFloat::countDecimals($aAxisY['precision'], true); // Number of decimals in Y-label




			if(isset($aData['priority']))
			{
				$aColors = array();
				$a = explode(',', $aData['priority']);

				foreach($a as $sColorName)
				{
					if(isset($this->aColors[$sColorName]))
					{
						$aColors[$sColorName] = $this->aColors[$sColorName];
					}
				}

				if(sizeof($aColors))
				{
/*
					foreach($this->aColors as $sColorName => $aRGB)
					{
						if(empty($aColors[$sColorName]))
						{
							$aColors[$sColorName] = $this->aColors[$sColorName];
						}
					}
*/

					$this->aColors = $aColors;
				}
			}



			// Process data blocks
			foreach($this->aColors as $sColor => $aRgb)
			{
				$aGraphData[$sColor] = array();

				if(isset($aData[$sColor]) && is_string($aData[$sColor]))
				{
					$a = explode('|', $aData[$sColor]);

					foreach($a as $k => $v)
					{
						if(isset($v) && is_string($v) && preg_match('/^(([-]?)([0-9]+)([.][0-9]+)?,([-]?)([0-9]+)([.][0-9]+)?)$/', $v))
						{
							list($x, $y) = explode(',', $v);

							$x = intval($x);
							$y = round($y, 6);
							$d = clsFloat::countDecimals($y, true);

							if(($x <= $aAxisX['max']) && ($x >= 0))
							{
								$aGraphData[$sColor][] = array('x' => $x, 'y' => $y);

								if($aAxisY['decimals'] < $d)
								{
									$aAxisY['decimals'] = $d;
								}

								if(($aAxisY['min'] === false) || ($aAxisY['max'] === false))
								{
									$aAxisY['min'] = $y;
									$aAxisY['max'] = $y;
								}
								elseif($y < $aAxisY['min'])
								{
									$aAxisY['min'] = $y;
								}
								elseif($y > $aAxisY['max'])
								{
									$aAxisY['max'] = $y;
								}
							}
						}
					}
				}
			}

			if(isset($aData['y']['min']) && clsSyntax::isFloat($aData['y']['min']))
			{
				if($aData['y']['min'] < $aAxisY['min'])
				{
					$aAxisY['min'] = $aData['y']['min'];
				}
			}

			if(isset($aData['y']['max']) && clsSyntax::isFloat($aData['y']['max']))
			{
				if($aData['y']['max'] > $aAxisY['max'])
				{
					$aAxisY['max'] = $aData['y']['max'];
				}
			}

			if(in_array($sGraphType, array('bars')))
			{
				if($aAxisY['min'] > 0)
				{
					$aAxisY['min'] = 0;
				}

				if($aAxisY['max'] < 0)
				{
					$aAxisY['max'] = 0;
				}
			}


			$fRest = clsFloat::rest($aAxisY['min'], $aAxisY['precision']);

			if($fRest)
			{
				if($aAxisY['min'] < 0)
				{
					$aAxisY['min'] = ($aAxisY['min'] - $fRest) - $aAxisY['precision'];
				}
				else
				{
					$aAxisY['min'] = ($aAxisY['min'] - $fRest);
				}
			}

			$fRest = clsFloat::rest($aAxisY['max'], $aAxisY['precision']);

			if($fRest)
			{
				if($aAxisY['max'] < 0)
				{
					$aAxisY['max'] = ($aAxisY['max'] - $fRest);
				}
				else
				{
					$aAxisY['max'] = ($aAxisY['max'] - $fRest) + $aAxisY['precision'];
				}
			}



			// Calculate Y-axis labels
			$aAxisY['labels'] = array('');

			$_fMinY = $aAxisY['min'];
			$iPrecisionDecimals = $aAxisY['decimals'];

			while($_fMinY < $aAxisY['max'])
			{
				if(($_fMinY == 0.000) && ($_fMinY == $aAxisY['min']))
				{
					// Don't create label for value '0'
				}
				else
				{
					$sLabel = clsFloat::round($_fMinY, $iPrecisionDecimals);
					$iLabelWidth = ($iFontWidth * strlen($sLabel));

					if($iMaxLabelWidthY < $iLabelWidth)
					{
						$iMaxLabelWidthY = $iLabelWidth;
					}

					$aAxisY['labels'][] = $sLabel;
				}

				$_fMinY += $aAxisY['precision'];
			}

			$aAxisY['labels'][] = clsFloat::round($_fMinY, $iPrecisionDecimals);

			if(isset($aData['padding']) && is_numeric($aData['padding']))
			{
				$iImagePadding = intval($aData['padding']);
			}
			else
			{
				$iImagePadding = 10;
			}


			$aAxisX['overlap'] = ($aAxisX['labels_rotate'] ? $iMaxLabelWidthX : $iMaxLabelHeight) + 16; // Number of pixels the x-axis should overlap the y-axis
			$aAxisY['overlap'] = ($aAxisY['labels_rotate'] ? $iMaxLabelHeight : $iMaxLabelWidthY) + 16; // Number of pixels the y-axis should overlap the x-axis








			$aLegenda = array();
			$iLegendaMaxWidth = 0;
			$iRealLegendaWidth = 0;

			if(isset($aData['legenda']))
			{
				foreach($this->aColors as $sColorName => $aRGB)
				{
					if(empty($aData['legenda'][$sColorName]))
					{
						$aLegenda[$sColorName] = '';
					}
					else
					{
						$aLegenda[$sColorName] = $aData['legenda'][$sColorName];
						$iLabelWidth = $iFontWidth * strlen($aLegenda[$sColorName]);

						if($iLabelWidth > $iLegendaMaxWidth)
						{
							$iLegendaMaxWidth = $iLabelWidth;
						}
					}
				}

				$iRealLegendaWidth = $iLegendaMaxWidth + ($iImagePadding * 3) + 20;
			}




			// Calculate image width & height
			$iImageWidth = (($iImagePadding * 2) + ($aAxisY['overlap'] + ceil($aAxisX['spacer'] / 2)) + ((sizeof($aAxisX['labels']) - 1) * $aAxisX['spacer'])) + $iRealLegendaWidth;
			$iImageHeight = (($iImagePadding * 2) + ($aAxisX['overlap'] + ceil($aAxisY['spacer'] / 2)) + ((sizeof($aAxisY['labels']) - 1) * $aAxisY['spacer']));





			// Create an empty image
			$oImage = imagecreatetruecolor($iImageWidth, $iImageHeight);


			// Set background color
			$aImageColors['background'] = imagecolorallocate($oImage, 255, 255, 255);
			imagefill($oImage, 0, 0, $aImageColors['background']);


			// Set grid color
			if($aAxisX['grid'] || $aAxisY['grid'])
			{
				$aImageColors['grid'] = imagecolorallocate($oImage, 232, 232, 232);
			}


			// Set draw colors
			foreach($this->aColors as $sColor => $aRgb)
			{
				$aImageColors[$sColor] = imagecolorallocate($oImage, $aRgb['red'], $aRgb['green'], $aRgb['blue']);
			}

			if(!isset($aImageColors['black']))
			{
				$aImageColors['black'] = imagecolorallocate($oImage, 0, 0, 0);
			}






			// Draw X-axis
			$x = $iImagePadding + $aAxisY['overlap'];
			$y = ($iImageHeight - ($iImagePadding + $aAxisX['overlap']));

			$i = 0;

			foreach($aAxisX['labels'] as $k => $v)
			{
				while($i < $k)
				{
					$i++;
					$x += $aAxisX['spacer'];

					if($aAxisY['grid'])
					{
						imageline($oImage, $x, $iImagePadding, $x, $y, $aImageColors['grid']);
					}

					imageline($oImage, $x, ($y - 3), $x, ($y + 3), $aImageColors['black']);

					if(!empty($aAxisX['labels'][$i]))
					{
						$sLabel = $aAxisX['labels'][$i];

						if($aAxisX['labels_rotate'])
						{
							imagestringup($oImage, $iFontSize, round($x - ($iFontHeight / 2)), $y + 8 + (strlen($sLabel) * $iFontWidth), $sLabel, $aImageColors['black']);
						}
						else
						{
							imagestring($oImage, $iFontSize, $x - floor((strlen($sLabel) * $iFontWidth) / 2) + 1, $y + 5, $sLabel, $aImageColors['black']);
						}
					}
				}
			}

			imageline($oImage, $iImagePadding, ($iImageHeight - ($iImagePadding + $aAxisX['overlap'] + 1)), ($iImageWidth - $iImagePadding - $iRealLegendaWidth), ($iImageHeight - ($iImagePadding + $aAxisX['overlap'] + 1)), $aImageColors['black']);





			// Draw Y-axis
			$x = $iImagePadding + $aAxisY['overlap'];
			$y = ($iImageHeight - ($iImagePadding + $aAxisX['overlap'] + 1)) - $aAxisY['spacer'];
			$i = 0;

			while($y > $iImagePadding)
			{
				$i++;

				if($aAxisX['grid'])
				{
					imageline($oImage, $x, $y, ($iImageWidth - $iImagePadding - $iRealLegendaWidth), $y, $aImageColors['grid']);
				}

				imageline($oImage, ($x - 3), $y, ($x + 3), $y, $aImageColors['black']);

				if(!empty($aAxisY['labels'][$i]))
				{
					$sLabel = $aAxisY['labels'][$i];
					imagestring($oImage, $iFontSize, $x - 8 - (strlen($sLabel) * $iFontWidth), $y - 6, $sLabel, $aImageColors['black']);
				}

				$y -= $aAxisY['spacer'];
			}

			imageline($oImage, $x, $iImagePadding, $x, $iImageHeight - $iImagePadding, $aImageColors['black']);


			// Calculate markers
			$aMarkers = array();

			$_iImageY = $iImageHeight - ($iImagePadding + $aAxisX['overlap'] + 1);
			$_iBarWidth = floor(min(round($aAxisX['spacer'] * 0.90), ($aAxisX['spacer'] - 6)) / 2);

			foreach($this->aColors as $sColor => $aRgb)
			{
				foreach($aGraphData[$sColor] as $k => $v)
				{
					$iImageX = $iImagePadding + $aAxisY['overlap'] + ($v['x'] * $aAxisX['spacer']);
				
					if(in_array($sGraphType, array('bars')))
					{
						if($aAxisY['min'] < 0)
						{
							$y1 = round(((abs($aAxisY['min'])) / $aAxisY['precision']) * $aAxisY['spacer']) + (($aAxisY['min'] != 0) ? $aAxisY['spacer'] : 0);
						}
						else
						{
							$y1 = 0;
						}

						if($v['y'] < 0)
						{
							$y2 = round((($v['y'] - $aAxisY['min']) / $aAxisY['precision']) * $aAxisY['spacer']) + (($aAxisY['min'] != 0) ? $aAxisY['spacer'] : 0);
							imagefilledrectangle($oImage, $iImageX - $_iBarWidth, $_iImageY - $y1 + 1, $iImageX + $_iBarWidth, $_iImageY - $y2, $aImageColors[$sColor]);
						}
						elseif($v['y'] > 0)
						{
							$y2 = round((($v['y'] - $aAxisY['min']) / $aAxisY['precision']) * $aAxisY['spacer']) + (($aAxisY['min'] != 0) ? $aAxisY['spacer'] : 0);
							imagefilledrectangle($oImage, $iImageX - $_iBarWidth, $_iImageY - $y1 - 1, $iImageX + $_iBarWidth, $_iImageY - $y2, $aImageColors[$sColor]);
						}

						imageline($oImage, ($iImagePadding + $aAxisY['overlap']), $_iImageY - $y1, ($iImageWidth - $iImagePadding - $iRealLegendaWidth), $_iImageY - $y1, $aImageColors['black']);
					}
					else
					{
						$y = round((($v['y'] - $aAxisY['min']) / $aAxisY['precision']) * $aAxisY['spacer']) + (($aAxisY['min'] != 0) ? $aAxisY['spacer'] : 0);
						$iImageY = $_iImageY - $y;

						imagefilledrectangle($oImage, $iImageX - 1, $iImageY - 1, $iImageX + 1, $iImageY + 1, $aImageColors[$sColor]);

						// Save markers to draw lines
						$aMarkers[$sColor][] = array('x' => $iImageX, 'y' => $iImageY);
					}
				}
			}


			// Draw lines
			if(in_array($sGraphType, array('lines')))
			{
				foreach($this->aColors as $sColor => $aRgb)
				{
					if(!empty($aMarkers[$sColor]))
					{
						for($i = 1; $i < sizeof($aMarkers[$sColor]); $i++)
						{
							imageline($oImage, $aMarkers[$sColor][$i - 1]['x'], $aMarkers[$sColor][$i - 1]['y'], $aMarkers[$sColor][$i]['x'], $aMarkers[$sColor][$i]['y'], $aImageColors[$sColor]);
						}
					}
				}
			}

			// Draw legenda
			if($iRealLegendaWidth)
			{
				$i = 0;

				$iLegendaItemWidth = 20;
				$iLegendaItemHeight = 16;

				foreach($this->aColors as $sColor => $aRgb)
				{
					if(isset($aLegenda[$sColor]))
					{
						$iImageX = $iImageWidth - $iRealLegendaWidth + $iImagePadding;
						$iImageY = $iImagePadding + ($i * ($iImagePadding + $iLegendaItemHeight));

						imagefilledrectangle($oImage, $iImageX, $iImageY, $iImageX + $iLegendaItemWidth, $iImageY + $iLegendaItemHeight, $aImageColors[$sColor]);
						imagerectangle($oImage, $iImageX, $iImageY, $iImageX + $iLegendaItemWidth, $iImageY + $iLegendaItemHeight, $aImageColors['black']);

						if(strlen($aLegenda[$sColor]))
						{
							imagestring($oImage, $iFontSize, $iImageX + $iLegendaItemWidth + $iImagePadding, $iImageY + floor(($iLegendaItemHeight - $iFontHeight) / 2), $aLegenda[$sColor], $aImageColors['black']);
						}

						$i++;
					}
				}
			}



			if(headers_sent() || ob_get_length())
			{
				error('Errors found while creating graph.', __FILE__, __LINE__);
			}
			else
			{
				header('Content-Type: image/png');
				imagepng($oImage);
				imagedestroy($oImage);
			}

			exit;
		}
	}

?>