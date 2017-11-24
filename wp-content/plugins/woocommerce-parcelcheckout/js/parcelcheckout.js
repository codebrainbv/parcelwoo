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