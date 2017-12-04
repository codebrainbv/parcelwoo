var parcelcheckout_ajax_url = woocommerce_parcelcheckout.parcelcheckout_ajax_url;



var initializeParcelcheckout = function()
{
	// Capture results color
	parcelcheckout_results_color = jQuery('#parcelcheckout-result').css('color');

	// Delay function
	var delay = (function () 
	{
		var timer = 0;
		
		return function (callback, ms) 
		{
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

	jQuery('#parcelcheckout-postcode').val(jQuery('#billing_postcode').val());
		

	// If Postcode input changes
	jQuery('#billing_postcode').change(function () 
	{
		if(jQuery(this).val()) 
		{
			var sPostcode = jQuery('#billing_postcode').val();
		
			jQuery('#parcelcheckout-postcode').val(sPostcode);
			// jQuery('#parcelcheckout-postcode').hide();
		}
		else 
		{
			jQuery('#parcelcheckout-postcode').val();
		}
	});

	
	
	
	
	jQuery('#parcelcheckout-button').click(function () 
	{
		var postcode = jQuery('#parcelcheckout-postcode').val().replace(/\s/g, "");
		var deliveryoption = jQuery('#parcelcheckout-deliveryoption').val();
			
		if(postcode.length >= 6) 
		{
			delay(function () 
			{
				jQuery.ajax({
					url: parcelcheckout_ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {
						pc_postcode: postcode,
						pc_option: deliveryoption,
						action: 'pickuplocation_call'
					},
					success: function (data) 
					{						
						if(typeof data.result !== 'undefined')
						{
							var map;

							map = new GMaps({
								el: '#results-map',
								lat: 52.698695699999995,
								lng: 6.207006499999999
							});
							
							
							jQuery.each(data.result, function(index, value)
							{
								console.log(value);
								
								map.addMarker({
									lat: value.Latitude,
									lng: value.Longitude,
									title: value.Name,
									click: function(e)
									{
										jQuery('#parcelcheckout-pickuppoint-results').html('<span>U heeft gekozen voor:</span><br>' + value.Name + '<br>' + value.Address.Street + ' ' + value.Address.HouseNr  + '<br>' + value.Address.City);
										
										console.log(e);
									}
								});
							});
						}
						else
						{
							jQuery('#parcelcheckout-pickuppoint-results').html('Wij konden geen locatie vinden, controleer de Postcode of neem contact op met de webshophouder');

						}						
					},
					error: function (exception)
					{
						alert('Exception:' + exception);
					}
						
				});
			}, 300);
		}
	});
};

jQuery(document).ready(function () 
{
	initializeParcelcheckout();
});