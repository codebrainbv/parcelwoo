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
							
							// Find first result and center around it
							// console.log(data.result[0]);

							map = new GMaps({
								el: '#results-map',
								lat: data.result[0]['Latitude'],
								lng: data.result[0]['Longitude'],
								zoom: 13
							});
							
							
							jQuery.each(data.result, function(index, value)
							{								
								map.addMarker({
									lat: value.Latitude,
									lng: value.Longitude,
									title: value.Name,
									click: function(e)
									{
										
										// Change checkbox state < 1.6 compatible
										jQuery('#ship-to-different-address-checkbox').attr('checked', true);
										
										// Somehow the form isnt shown, this code fixes it
										jQuery('.shipping_address').show();
									
										
										// Set selected data in the checkout (alternative address)
										var sFirstName = jQuery('#billing_first_name').val();
										var sSurName = jQuery('#billing_last_name').val();
										
										
										jQuery('#shipping_first_name').val(sFirstName);
										jQuery('#shipping_last_name').val(sSurName);
										
										jQuery('#shipping_company').val(value.Name);
										
										jQuery('#shipping_address_1').val(value.Address.Street + ' ' + value.Address.HouseNr);
										jQuery('#shipping_address_2').val('');
										jQuery('#shipping_postcode').val(value.Address.Zipcode);
										jQuery('#shipping_city').val(value.Address.City);
										

										jQuery('#parcelcheckout-pickuppoint-results').html('<span>U heeft gekozen voor:</span><br>' + value.Name + '<br>' + value.Address.Street + ' ' + value.Address.HouseNr  + '<br>' + value.Address.Zipcode + ' ' + value.Address.City);
										
										if(typeof value.OpeningHours !== 'undefined')
										{											
											jQuery('#parcelcheckout-pickuppoint-openingtime').html('<span>Openingstijden:</span><br>Maandag: ' + value.OpeningHours.Monday.string + '<br>Dinsdag: ' + value.OpeningHours.Tuesday.string + '<br>Woensdag: ' + value.OpeningHours.Wednesday.string + '<br>Donderdag: ' + value.OpeningHours.Thursday.string + '<br>Vrijdag: ' + value.OpeningHours.Friday.string + '<br>Zaterdag: ' + value.OpeningHours.Saturday.string);
											
										}
										
										e.stopPropagation();
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