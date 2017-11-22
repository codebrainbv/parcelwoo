var parcelcheckout_ajax_url = woocommerce_parcelcheckout.parcelcheckout_ajax_url;


var initializePostcodecheckout = function () 
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

	jQuery('#parcelcheckout-button').click(function () 
	{
		var postcode = jQuery('#parcelcheckout-postcode').val().replace(/\s/g, "");
		
		
		if(postcode.length >= 6) 
		{
			delay(function () 
			{
				jQuery.ajax({
					url: parcelcheckout_ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {
						postcode: postcode,
						action: 'parcelcheckout'
					},
					success: function (data) 
					{
						if(typeof data.result !== 'undefined')
						{							
							if(typeof data.result.street !== 'undefined' && typeof data.result.houseNumber !== 'undefined' && typeof data.result.city !== 'undefined')
							{
								
								// jQuery('#billing_address_1').parent().show();
								// jQuery('#billing_address_2').parent().show();
								// jQuery('#billing_postcode').parent().show();
								// jQuery('#billing_city').parent().show();

								
								jQuery('#billing_address_1').val(data.result.street);
								jQuery('#billing_address_2').val(data.result.houseNumber + ' ' + addition);
								jQuery('#billing_city').val(data.result.city);
								jQuery('#billing_postcode').val(data.result.postcode);

								jQuery('#parcelcheckout-billing-result').html(
										data.result.street + ' ' + data.result.houseNumber + addition + '<br>' +
										data.result.postcode + '<br>' + data.result.city
								);

								// jQuery('#parcelcheckout-postcode').parent().hide(parcelcheckout_anim_duration);
								// jQuery('#parcelcheckout-housenumber').parent().hide(parcelcheckout_anim_duration);
								// jQuery('#parcelcheckout-button').parent().hide(parcelcheckout_anim_duration);
								// jQuery('#parcelcheckoutresult').hide(parcelcheckout_anim_duration);
							}
							else
							{								
								jQuery('#parcelcheckout-billing-result').html('Wij konden geen adres vinden, controleer de Postcode of het Huisnummer');
								jQuery('#billing_postcode').val(postcode);
								
								jQuery('#parcelcheckout-disable').parent().show(parcelcheckout_anim_duration);
								
								
								// jQuery('#billing_address_1').parent().show();
								// jQuery('#billing_address_2').parent().show();
								// jQuery('#billing_postcode').parent().show();
								// jQuery('#billing_city').parent().show();
							
								
								// jQuery('#parcelcheckout-postcode').parent().hide(parcelcheckout_anim_duration);
								// jQuery('#parcelcheckout-housenumber').parent().hide(parcelcheckout_anim_duration);
								// jQuery('#parcelcheckout-button').parent().hide(parcelcheckout_anim_duration);
								// jQuery('#parcelcheckoutresult').hide(parcelcheckout_anim_duration);
								
							}
						}
						else
						{
							jQuery('#parcelcheckout-billing-result').html('Wij konden geen adres vinden, controleer de Postcode of het Huisnummer');
							
							jQuery('#parcelcheckout-disable').parent().show(parcelcheckout_anim_duration);
						}
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