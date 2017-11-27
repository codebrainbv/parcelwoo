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

	jQuery('#parcelcheckout-country').val(jQuery('#billing_country').val());
	jQuery('#parcelcheckout-city').val(jQuery('#billing_city').val());
	jQuery('#parcelcheckout-postcode').val(jQuery('#billing_postcode').val());
	jQuery('#parcelcheckout-address').val(jQuery('#billing_address_1').val() +  ' ' + jQuery('#billing_address_2').val());
	jQuery('#parcelcheckout-name').val(jQuery('#billing_first_name').val() +  ' ' + jQuery('#billing_last_name').val());
	
	
	
	// If Country selectbox changes
	jQuery('#billing_country').change(function () 
	{
		if(jQuery(this).val())
		{
			var sCountry = jQuery('#billing_country').val();
		
			jQuery('#parcelcheckout-country').val(sCountry);
			// jQuery('#parcelcheckout-country').hide();
		}
		else 
		{
			jQuery('#parcelcheckout-country').val();
		}
	});
	
	// If City input changes
	jQuery('#billing_city').change(function () 
	{
		if(jQuery(this).val()) 
		{
			var sCity = jQuery('#billing_city').val();
		
			jQuery('#parcelcheckout-city').val(sCity);
			// jQuery('#parcelcheckout-city').hide();
		}
		else 
		{
			jQuery('#parcelcheckout-city').val();
		}
	});

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

	// If Address 1 inputs change
	jQuery('#billing_address_1').change(function () 
	{
		alert('Change detected');
		
		
		if(jQuery(this).val()) 
		{
			var sAddress1 = jQuery('#billing_address_1').val();	
			var sAddress2 = jQuery('#billing_address_2').val();		
			
			jQuery('#parcelcheckout-address').val(sAddress1 +  ' ' + sAddress2);
			// jQuery('#parcelcheckout-address').hide();
			
		}
		else 
		{
			jQuery('#parcelcheckout-address').val();
		}
	});
	
	// If Address 2 inputs change
	jQuery('#billing_address_2').change(function () 
	{
		if(jQuery(this).val()) 
		{
			var sAddress1 = jQuery('#billing_address_1').val();
			var sAddress2 = jQuery('#billing_address_2').val();
			
			jQuery('#parcelcheckout-address').val(sAddress1 +  ' ' + sAddress2);
			// jQuery('#parcelcheckout-address').hide();
		}
		else
		{
			jQuery('#parcelcheckout-address').val();	
		}
	});
	
	// If Firstname inputs change
	jQuery('#billing_first_name').change(function () 
	{		
		if(jQuery(this).val()) 
		{
			var sFirstName = jQuery('#billing_first_name').val();	
			var sSurName = jQuery('#billing_last_name').val();		
			
			jQuery('#parcelcheckout-name').val(sFirstName +  ' ' + sSurName);
			// jQuery('#parcelcheckout-name').hide();
			
		}
		else 
		{
			jQuery('#parcelcheckout-name').val();
		}
	});
	
	// If Surname inputs change
	jQuery('#billing_last_name').change(function () 
	{
		if(jQuery(this).val()) 
		{
			var sFirstName = jQuery('#billing_first_name').val();
			var sSurName = jQuery('#billing_last_name').val();
			
			jQuery('#parcelcheckout-name').val(sFirstName +  ' ' + sSurName);
			// jQuery('#parcelcheckout-name').hide();
		}
		else
		{
			jQuery('#parcelcheckout-name').val();	
		}
	});	

	
	jQuery('#parcelcheckout-button').click(function () 
	{
		var country = jQuery('#parcelcheckout-country').val().replace(/\s/g, "");
		var city = jQuery('#parcelcheckout-city').val().replace(/\s/g, "");
		var postcode = jQuery('#parcelcheckout-postcode').val().replace(/\s/g, "");
		var address = jQuery('#parcelcheckout-address').val();
		var name = jQuery('#parcelcheckout-name').val();
			
		if(postcode.length >= 6) 
		{
			delay(function () 
			{
				jQuery.ajax({
					url: parcelcheckout_ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {
						pc_country: country,
						pc_city: city,
						pc_postcode: postcode,
						pc_address: address,
						pc_name: name,
						action: 'pickuplocation_call'
					},
					success: function (result) 
					{
						
						alert(result);
						
						jQuery('#parcelcheckout-pickuppoint-results').html(result);
						
						
						
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