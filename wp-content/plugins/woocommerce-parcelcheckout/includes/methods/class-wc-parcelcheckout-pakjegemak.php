<?php


if(!defined('ABSPATH'))
{
    exit;
}

class WC_Parcelcheckout_Pakjegemak extends WC_Shipping_Method 
{	
    public function __construct($iInstanceId = 0) 
	{
        $this->instance_id        = absint($iInstanceId);
        $this->id                 = 'parcelcheckout_pakjegemak';
        $this->method_title       = __('PC Pakjegemak', 'woocommerce-parcelcheckout');
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
        $this->show_delivery_time     = $this->get_option('show_delivery_time');
        $this->additional_time        = $this->get_option('additional_time');
        $this->fee                    = $this->get_option('fee');
        $this->export_status          = $this->get_option('order_states');
        $this->pickup_locations       = $this->get_option('pickup_locations');
        $this->debug                  = $this->get_option('debug');

        // Save admin options.
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
		
		// Show form for pickup selection
		add_action('woocommerce_after_order_notes', array($this, 'getPickupLocationHtml'), 10, 1);
		
		add_action('woocommerce_thankyou', array($this, 'insertOrderInParcelCheckout'), 10, 1 ); 	
		
		
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
            'show_delivery_time' => array(
                'title'       => __('Delivery Time', 'woocommerce-parcelcheckout'),
                'type'        => 'checkbox',
                'label'       => __('Show estimated delivery time', 'woocommerce-parcelcheckout'),
                'description' => __('Display the estimated delivery time in working days.', 'woocommerce-parcelcheckout'),
                'desc_tip'    => true,
                'default'     => 'no',
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
			'order_states' => array(
                'title'       => __('Order Status', 'woocommerce-parcelcheckout'),
                'type'        => 'select',
                'description' => __( 'Select the order status that needs to be used for exporting', 'woocommerce-parcelcheckout' ),
                'desc_tip'    => true,
                'default'     => '',
                'class'       => 'wc-enhanced-select',
                'options'     => $this->wc_get_order_statuses(),
            ),
		);
    }
        
    public function is_available($aPackage = array())
	{
        $bAvailable = true;
        // $aPickupLocations = self::get_available_locations();
		
        if(false) // empty($aPickupLocations))
		{
            $bAvailable = false;
        }
		
        return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', $bAvailable, $aPackage);
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
	
    public static function method_options($oMethod, $iIndex)
	{

		// Our method options
        if($oMethod->method_id == 'parcelcheckout_pakjegemak')
		{
            $sCssClass = 'pc-display-none-';
			
            $sAvailableMethods = WC()->session->get('chosen_shipping_methods');
						
            if($sAvailableMethods[0] == $oMethod->id)
			{
                $sCssClass = 'pc-display-block';
            }
            
            $aMetaData = $oMethod->get_meta_data();
            $aPickupLocations = self::get_available_locations();
            
            if(!empty($aPickupLocations))
			{
                //$checked = !empty($aMetaData['chosen_location']) ? $aMetaData['chosen_location'] : key($aMetaData['pickup_locations']);
                $checked = $aMetaData['chosen_location'];
                
                echo "<ul id='multiple-pickup-locations-list' class='pickup-locations {$sCssClass}'>";
				
                $i = 0;
				
                foreach( $aMetaData['pickup_locations'] as $key ){
                    $is_checked = checked( $key, $checked, false );
                    if( $i == 0 and empty($checked) ){
                        $is_checked = checked( 1, 1, false );
                    }
                    echo "<li><label><input type='radio' name='pickup-location' value='{$key}' id='pickup-location-{$key}' {$is_checked} /> <strong>{$key}</strong>: {$aPickupLocations[$key]}</label></li>";
                    $i++;
                }
				
                echo "</ul>";
            }
            
        }
    }
    
	public static function getPickupLocationHtml()
	{
		wc_get_template('checkout/form-parcelcheckout.php', array(), '', PARCEL_PLUGIN_PATH . 'includes/templates/');
	}
	

	
	public static function insertOrderInParcelCheckout($sOrderId)
	{
		// Retrieve order object and order details
		$oOrder = wc_get_order($sOrderId); 
	
		// Get all order related data
		$aOrderData = $oOrder->get_data();
	
		// Address fields
		$iUserId = $aOrderData['customer_id'];
		// $sShippingMethod = $aOrderData['shipping_method'];
		$sShippingCost = $aOrderData['shipping_total'];

		// Get Ordered products
		$aOrderedItems = $oOrder->get_items();

		$aProductData = array();
		
		foreach($aOrderedItems as $k => $v)
		{
			$aProduct = array();
			$aProduct['name'] = $v['name'];
			$aProduct['quantity'] = $v['qty'];
			$aProduct['total'] = $v['total'];

			$aProduct['id'] = $v['product_id'];
			$oProduct = new WC_Product($aProduct['id']);
			$aProduct['sku'] = $oProduct->get_sku();
			
			$aProductData[] = $aProduct;
		}
		
		$sOrderProducts = json_encode($aProductData);
		
		// Split date and time
		$sOrderDate = $aOrderData['date_created']->getTimestamp();
		
		$aShippingData = $aOrderData['shipping'];
		$aBillingData = $aOrderData['billing'];
		
		$sCustomerEmail = $aBillingData['email'];
		$sPhoneNumber = $aBillingData['phone'];
		
		// Shipment data
		list($sShippingStreetName, $sShippingStreetNumber) = parcelcheckout_splitAddress($aShippingData['address_1'] . ' ' . $aShippingData['address_2']);
		$sShippingAddressComplete = $aShippingData['address_1'] . ' ' . $aShippingData['address_2'];
		
		// Invoice data
		list($sInvoiceStreetName, $sInvoiceStreetNumber) = parcelcheckout_splitAddress($aBillingData['address_1'] . ' ' . $aBillingData['address_2']);
		$sInvoiceAddressComplete = $aBillingData['address_1'] . ' ' . $aBillingData['address_2'];
		
		// Setup database connection and get settings
		$aDatabaseSettings = parcelcheckout_getDatabaseSettings();

		
		// TODO Product option based on pickup time/location

		
		// Check if order is already inserted also as processing
		$sql = "SELECT `id` FROM `" . $aDatabaseSettings['prefix'] . "parcelcheckout_orders` WHERE (`order_number` = '" . parcelcheckout_escapeSql($sOrderId) . "')";
		
		$aRecord = parcelcheckout_database_getRecord($sql);
		
		if(!sizeof($aRecord) && strcasecmp($aOrderData['status'], $this->export_status) === 0)
		{
			// Query for order into parcelcheckout_orders		
			$sql = "INSERT INTO `" . $aDatabaseSettings['prefix'] . "parcelcheckout_orders` SET
`id` = NULL,
`order_number` = '" . parcelcheckout_escapeSql($sOrderId) . "',
`order_date` = '" . parcelcheckout_escapeSql(date('Y-m-d', $sOrderDate)) . "',
`order_time` = '" . parcelcheckout_escapeSql(date('H:i:s', $sOrderDate)) . "',
`customer_id` = '" . parcelcheckout_escapeSql($iUserId) . "',
`shipment_title` = NULL,
`shipment_firstname` = '" . parcelcheckout_escapeSql($aShippingData['first_name']) . "',
`shipment_surname` = '" . parcelcheckout_escapeSql($aShippingData['last_name']) . "',
`shipment_company` = " . ($aShippingData['company'] ? "'" . idealcheckout_escapeSql($aShippingData['company']) . "'" : "NULL") . ",
`shipment_address_full` = '" . parcelcheckout_escapeSql($sShippingAddressComplete) . "',
`shipment_address_street` = '" . parcelcheckout_escapeSql($sShippingStreetName) . "',
`shipment_address_number` = '" . parcelcheckout_escapeSql($sShippingStreetNumber) . "',
`shipment_address_number_extension` = NULL,
`shipment_postalcode` = '" . parcelcheckout_escapeSql($aShippingData['postcode']) . "',
`shipment_city` = '" . parcelcheckout_escapeSql($aShippingData['city']) . "',
`shipment_country_iso` = '" . parcelcheckout_escapeSql($aShippingData['country']) . "',
`shipment_country` = NULL,
`shipment_phone` = '" . parcelcheckout_escapeSql($sPhoneNumber) . "',
`shipment_email` = '" . parcelcheckout_escapeSql($sCustomerEmail) . "',
`shipment_agent` = '03085',
`shipment_type` = 'Commercial Goods',
`shipment_product_option` = NULL,
`shipment_option` = NULL,
`shipment_dateofbirth` = NULL,
`shipment_id_expiration` = NULL,
`shipment_id_number` = NULL,
`shipment_id_type` = NULL,
`shipment_delivery_date` = NULL,
`shipment_delivery_time` = NULL,
`shipment_comment` = '" . parcelcheckout_escapeSql($sOrderDate['customer_note']) . "',
`billing_title` = NULL,
`billing_firstname` = '" . parcelcheckout_escapeSql(substr($aBillingData['first_name'], 0, 1)) . "',
`billing_surname` = '" . parcelcheckout_escapeSql($aBillingData['last_name']) . "',
`billing_company` = " . ($aBillingData['company'] ? "'" . idealcheckout_escapeSql($aBillingData['company']) . "'" : "NULL") . ",
`billing_address_full` = '" . parcelcheckout_escapeSql($sInvoiceAddressComplete) . "',
`billing_address_street` = '" . parcelcheckout_escapeSql($sInvoiceStreetName) . "',
`billing_address_number` = '" . parcelcheckout_escapeSql($sInvoiceStreetNumber) . "',
`billing_address_number_extension` = NULL,
`billing_postalcode` = '" . parcelcheckout_escapeSql($aBillingData['postcode']) . "',
`billing_city` = '" . parcelcheckout_escapeSql($aBillingData['city']) . "',
`billing_country_iso` = '" . parcelcheckout_escapeSql($aBillingData['country']) . "',
`billing_phone` = NULL,
`billing_email` = NULL,
`language` = NULL,
`order_products` = '" . $sOrderProducts . "',
`order_status` = '" . parcelcheckout_escapeSql($aOrderData['status']) . "',
`exported` = '0';";

			parcelcheckout_database_query($sql);

			
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
   
    public static function get_available_locations()
	{
		
		// Do pickuplocations call
		
		
		
		
        return apply_filters( 'pc_pakjegemak_locations_list', array() );
    }
    

    function generate_brt_repeater_html( $key, $data ){
        $field_key = $this->get_field_key( $key );
        $defaults  = array(
            'title'             => '',
            'disabled'          => false,
            'class'             => '',
            'css'               => '',
            'placeholder'       => '',
            'type'              => 'text',
            'desc_tip'          => false,
            'description'       => '',
            'custom_attributes' => array(),
        );

        $data = wp_parse_args( $data, $defaults );

        ob_start();
		
		
		/*
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
                <?php echo $this->get_tooltip_html( $data ); ?>
            </th>
            <td class="forminp">
                <fieldset>
                    <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
                    <input 
                        class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" 
                        type="text" 
                        name="<?php echo esc_attr( $field_key ); ?>" 
                        id="<?php echo esc_attr( $field_key ); ?>" 
                        style="<?php echo esc_attr( $data['css'] ); ?>" 
                        value="<?php echo esc_attr( wc_format_localized_price( $this->get_option( $key ) ) ); ?>" 
                        placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" 
                        <?php disabled( $data['disabled'], true ); ?> 
                        <?php echo $this->get_custom_attribute_html( $data ); ?>
                    />
                    <?php echo $this->get_description_html( $data ); ?>
                    <p>ESTE É UM CAMPO DE TESTE DE LOCAIS DE RETIRADA</p>
                </fieldset>
            </td>
        </tr>
        <?php
		
		
		*/
        return ob_get_clean();
    }
    
    
    
}
