<?php


if(!defined('ABSPATH'))
{
    exit;
}

class WC_Parcelcheckout_SignatureOnDelivery extends WC_Shipping_Method 
{	
    function __construct($iInstanceId = 0) 
	{
        $this->instance_id        = absint($iInstanceId);
        $this->id                 = 'parcelcheckout_signatureondelivery';
        $this->method_title       = __('PC Signature on Delivery', 'woocommerce-parcelcheckout');
        $this->method_description = sprintf( __( '%s is a shipping method for PostNL.', 'woocommerce-parcelcheckout'), $this->method_title );
        $this->supports           = array(
            'shipping-zones',
            'instance-settings',
        );
        
		// Possibly restrict to Netherlands?
		/*
		$this->availability = 'including';
		$this->countries = array(
			'NL', // The Netherlands
		);
		 */		
		
        // Load the form fields.
        $this->init_form_fields();
        
        // Define user set variables.
        $this->enabled                = $this->get_option('enabled');
        $this->title                  = $this->get_option('title');
        $this->shipping_class         = $this->get_option('shipping_class');
        $this->agentcode         	  = $this->get_option('agentcode');
        $this->fee                    = $this->get_option('fee');
        $this->export_status          = $this->get_option('order_states');
        $this->debug                  = $this->get_option('debug');

        // Save admin options.
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
		
		// Load method options in WooCommerce shipping rate.
		add_action('woocommerce_after_shipping_rate', array($this, 'method_options'), 10, 2);
    }

	
	
    // Get shipping classes
    protected function get_shipping_classes_options() 
	{
        $aShippingClasses = WC()->shipping->get_shipping_classes();
        $aShippingOptions = array(
            '' => __( '-- Select a shipping class --', 'woocommerce-parcelcheckout' ),
        );

        if(!empty($aShippingClasses)) 
		{
            $aShippingOptions += wp_list_pluck($aShippingClasses, 'name', 'slug');
        }

        return $aShippingOptions;
    }
	
	protected function wc_get_order_statuses() 
	{
		$aOrderStatuses = array(
			'wc-pending'    => _x( 'Pending payment', 'Order status', 'woocommerce' ),
			'wc-processing' => _x( 'Processing', 'Order status', 'woocommerce' ),
			'wc-on-hold'    => _x( 'On hold', 'Order status', 'woocommerce' ),
			'wc-completed'  => _x( 'Completed', 'Order status', 'woocommerce' ),
			'wc-cancelled'  => _x( 'Cancelled', 'Order status', 'woocommerce' ),
			'wc-refunded'   => _x( 'Refunded', 'Order status', 'woocommerce' ),
			'wc-failed'     => _x( 'Failed', 'Order status', 'woocommerce' ),
		);
		
		return apply_filters('wc_order_statuses', $aOrderStatuses);
	}
	
	// Get shipping agent codes
    protected function get_shipping_agentcode_options()
	{		
		$aAgentCodes = array(
			'03189'    => 'Handtekening voor ontvangst',
			'03089'    => 'Handtekening voor Ontvangst + Alleen Huisadres',
		);
		
		return $aAgentCodes;
	}
    
	public function init_form_fields()
	{
        $this->instance_form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'woocommerce-parcelcheckout' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this shipping method', 'woocommerce-parcelcheckout' ),
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => __( 'Title', 'woocommerce-parcelcheckout' ),
                'type'        => 'text',
                'description' => __('Change the titel that the customer see\'s during the checkout process', 'woocommerce-parcelcheckout'),
                'desc_tip'    => true,
                'default'     => $this->method_title,
            ),
            'track_and_trace_to_mail' => array(
                'title'       => __('Track en trace', 'woocommerce-parcelcheckout'),
                'type'        => 'checkbox',
                'label'       => __('Add track and trace to order confirmation email', 'woocommerce-parcelcheckout'),
                'description' => __('Add track and trace to order confirmation email', 'woocommerce-parcelcheckout'),
                'desc_tip'    => true,
                'default'     => 'yes',
            ),
            'fee' => array(
                'title'       => __('Handling Fee', 'woocommerce-parcelcheckout'),
                'type'        => 'price',
                'description' => __( 'Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'woocommerce-parcelcheckout' ),
                'desc_tip'    => true,
                'placeholder' => '0.00',
                'default'     => '',
            ),
            'shipping_class' => array(
                'title'       => __('Shipping Class', 'woocommerce-parcelcheckout'),
                'type'        => 'select',
                'description' => __( 'Select for which shipping class this method will be applied.', 'woocommerce-parcelcheckout' ),
                'desc_tip'    => true,
                'default'     => '',
                'class'       => 'wc-enhanced-select',
                'options'     => $this->get_shipping_classes_options(),
            ),
			'agentcode' => array(
                'title'       => __('Variant', 'woocommerce-parcelcheckout'),
                'type'        => 'select',
                'description' => __( 'Select which agent code should be used in the export.', 'woocommerce-parcelcheckout' ),
                'desc_tip'    => true,
                'default'     => '',
                'class'       => 'wc-enhanced-select',
                'options'     => $this->get_shipping_agentcode_options(),
            )
		);
    }
        
    public function is_available($aPackage = array())
	{
        $bAvailable = true;
        // $aPickupLocations = self::get_available_locations();
		
		// Possibly check package for availability
		// Think of Weight, Size etc.
		
		
        if(false) // empty($aPickupLocations))
		{
            $bAvailable = false;
        }
		
        return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', $bAvailable, $aPackage);
    }
    
	public static function method_options($oMethod, $index)
	{		
        if($oMethod->method_id == 'parcelcheckout_signatureondelivery')
		{
            $aChosenMethod = WC()->session->get('parcelcheckout_signatureondelivery');
		
            if($aChosenMethod[0] == $oMethod->id)
			{
                $class = 'brt-display-block';
            }
            
            $aMetaData = $oMethod->get_meta_data();	
        }
    }
	
	
	
    public function calculate_shipping($aPackage = array())
	{		
		$this->add_rate(array(
			'label'     => $this->title,
			'cost'      => $this->fee,
			'taxes'     => false,
			'package'   => false,
		));
    }
}
