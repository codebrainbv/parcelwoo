<?php
	$sHtml = '';
	// [*] (stop deze code in iedere widget) 
	define('PARCELCHECKOUT_PATH', dirname(dirname(dirname(__DIR__))));
	require_once(PARCELCHECKOUT_PATH . '/php/parcelcheckout.php');
	$sImagePath = parcelcheckout_getRootUrl(2). 'images';
	
	$sHtml .= '
		<link href="widgets/top/css/currencyconverter.css" media="screen" rel="stylesheet" type="text/css">
		<div class="currencyconverter" id="currencyconverter">
		<div class="currencyconverterTopDeco widgetTopDeco">
		</div>
		<div class="currencyconverterTopInfo widgetTopInfo">
			<b>Currency converter</b>
			<div class="infoIconTopWidget" data-balloon="Data van http://fixer.io/ per 24H" data-balloon-pos="left">
				<img src="' . $sImagePath . '/info-icon.png" height="100%" >
			</div>
		</div>
		<div onmousedown="disableTopDragging()" onmouseup="enableTopDragging()" class="topWidgetContent">
	';
	// [\*]
	
	// [**] codeer hier wat er in de widget moet komen te staan.
	
	

		
	$sHtml .= '
		<script>
		
		
		function getCurrencyData(convertTo)
		{
			var sFromCurrency = document.getElementById("select-from-currency")
			var sToCurrency = document.getElementById("select-to-currency")
			var sFromCurrencyValue = sFromCurrency.options[sFromCurrency.selectedIndex].value
			var sToCurrencyValue = sToCurrency.options[sToCurrency.selectedIndex].value
			var sFromCurrency = sFromCurrencyValue;
			var sToCurrency = sToCurrencyValue;
			
			var iFromCurrencyAmt = document.getElementById("currencyConv_from_amt").value;

			var sUrl = "http://api.fixer.io/latest?base=" + sFromCurrency;
			
			jQuery.ajax({
				url: sUrl,
				contentType: "application/json; charset=utf-8",
				type: "GET",
				dataType: "json",
				success: function (data) 
				{
					var sCurrencyValue = data.rates[sToCurrency];
					var iEndResult = iFromCurrencyAmt * sCurrencyValue;
					jQuery("#currencyConv_to_amt").val(iEndResult);
					
				},
				error: function () 
				{
					console.log("fail");
				}
			});
		}
		
		
		

		</script>';
		
		$aCurrencies = array(
			"AUD" => "AUD - Australische Dollar",
			"BGN" => "BGN - Bulgarische Leva",
			"BRL" => "BRL - Braziliaanse Reaal",
			"CAD" => "CAD - Canadese Dollar",
			"CHF" => "CHF - Zwitserse Frank",
			"CNY" => "CNY - Chinese Yuan",
			"CZK" => "CZK - Tsjechische Kroon",
			"DKK" => "DKK - Deense Kroon",
			"EUR" => "EUR - Euro",
			"GBP" => "GBP - Britse Pond",
			"HKD" => "HKD - Hong Kong Dollar",
			"HRK" => "HRK - Kroatische Kuna",
			"HUF" => "HUF - Hongaarse Forint",
			"IDR" => "IDR - Indonesische Rupiah",
			"ILS" => "ILS - Israel Shekel",
			"INR" => "INR - Indische Roepie",
			"JPY" => "JPY - Japanse Yen",
			"KRW" => "KRW - Zuid-Koreaanse Won",
			"MXN" => "MXN - Mexicaanse Peso",
			"MYR" => "MYR - Maleise Ringgit",
			"NOK" => "NOK - Noorse Kroon",
			"NZD" => "NZD - Nieuw-zeelandse Dollar",
			"PHP" => "PHP - Filipijnse Peso",
			"PLN" => "PLN - Poolse Zloty",
			"RON" => "RON - Roemeense Leu",
			"RUB" => "RUB - Russische Roebel",
			"SEK" => "SEK - Zweedse Kroon",
			"SGD" => "SGD - Singapore Dollar",
			"THB" => "THB - Thaise Baht",
			"TRY" => "TRY - Turkse Lira",
			"USD" => "USD - Amerikaanse Dollar",
			"ZAR" => "ZAR - Zuid-Afrikaanse Rand"
		);
		
		$sHtml.= '
		
		<div class="currencyConvFromContainer">
			<div class="currencyConvFromToText"> Van: </div>
			<select name="select-from-currency" class="currencyConvField" id="select-from-currency">
			
				<option value="EUR">EUR - Euro</option>';
				foreach($aCurrencies as $k => $v)
				{				
					$sHtml .= '
					<option value="' . $k . '">' . $v . '</option>';
				}			
			
			
				$sHtml .= '
			</select>
			<input name="currencyConv_from_amt" class="currencyConvField" id="currencyConv_from_amt" placeholder="234.95" type="text">
		</div>
		
		<div class="currencyConvToContainer">
			<div class="currencyConvFromToText"> Naar: </div>
			<select name="select-to-currency" class="currencyConvField" id="select-to-currency">
			
				<option value="GBP">GBP - Britse Pond</option>';
				foreach($aCurrencies as $k => $v)
				{				
					$sHtml .= '
					<option value="' . $k . '">' . $v . '</option>';
				}			
				
				
				$sHtml .= '
			</select>
			<input name="currencyConv_to_amt" class="currencyConvField" id="currencyConv_to_amt" placeholder="Resultaat" type="text" readonly>
		</div>
		<button onclick="getCurrencyData()" class="currencyConvConvBtn"> Convert </button>
	';
	// [\**]
	
	// [*]
	$sHtml .= '
		</div>
		</div>
	';
	echo($sHtml);
	// [\*]
	
	
	
	
	
	
	
?>