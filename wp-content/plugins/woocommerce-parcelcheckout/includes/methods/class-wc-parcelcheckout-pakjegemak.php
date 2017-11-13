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
        $this->pickup_locations       = $this->get_option('pickup_locations');
        $this->debug                  = $this->get_option('debug');

        // Save admin options.
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
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
    
	
	
	function insertOrderInParcelCheckout($sOrderId)
	{
		
		// Retrieve order object and order details
		$oOrder = new WC_Order($sOrderId); 
				
		$sCustomerEmail = $oOrder->billing_email;
		$sPhoneNumber = $oOrder->billing_phone;
		$sShippingMethod = $oOrder->get_shipping_method();
		$sShippingCost = $oOrder->get_total_shipping();

		// Address fields
		$iUserId = $oOrder->user_id;
		$aAddressData = array(
			'country',
			'title',
			'first_name',
			'last_name',
			'company',
			'address_1',
			'address_2',
			'address_3',
			'address_4',
			'city',
			'state',
			'postcode'
		);

echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($iUserId);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
		
		
		
		$aAddress = array();
		
		if(is_array($aAddressData))
		{
			foreach($aAddressData as $sFieldName)
			{
				$aAddress['billing_' . $sFieldName] = get_user_meta($iUserId, 'billing_' . $sFieldName, true);
				$aAddress['shipping_' . $sFieldName] = get_user_meta($iUserId, 'shipping_' . $sFieldName, true);
			}
		}

echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
print_r($aAddress);
echo "<br>\n" . 'DEBUG: ' . __FILE__ . ' : ' . __LINE__ . "<br>\n";
exit;
		
		
		
		/*
		// get coupon information (if applicable)
		$cps = array();
		$cps = $oOrder->get_items( 'coupon' );

		$coupon = array();
		foreach($cps as $cp){
		// get coupon titles (and additional details if accepted by the API)
		$coupon[] = $cp['name'];
		}

		// get product details
		$items = $oOrder->get_items();

		$item_name = array();
		$item_qty = array();
		$item_price = array();
		$item_sku = array();

		foreach( $items as $key => $item){
		$item_name[] = $item['name'];
		$item_qty[] = $item['qty'];
		$item_price[] = $item['line_total'];

		$item_id = $item['product_id'];
		$product = new WC_Product($item_id);
		$item_sku[] = $product->get_sku();
		}

		
		$transaction_key = get_post_meta( $order_id, '_transaction_id', true );
		$transaction_key = empty($transaction_key) ? $_GET['key'] : $transaction_key;   

		// set the username and password
		$api_username = 'testuser';
		$api_password = 'testpass';

		// to test out the API, set $api_mode as ‘sandbox’
		$api_mode = 'sandbox';
		if($api_mode == 'sandbox'){
		// sandbox URL example
		$endpoint = "http://sandbox.example.com/"; 
		}
		else{
		// production URL example
		$endpoint = "http://example.com/"; 
		}

		// setup the data which has to be sent
		$data = array(
		'apiuser' => $api_username,
		'apipass' => $api_password,
		'customer_email' => $email,
		'customer_phone' => $phone,
		'bill_firstname' => $address['billing_first_name'],
		'bill_surname' => $address['billing_last_name'],
		'bill_address1' => $address['billing_address_1'],
		'bill_address2' => $address['billing_address_2'],
		'bill_city' => $address['billing_city'],
		'bill_state' => $address['billing_state'],
		'bill_zip' => $address['billing_postcode'],
		'ship_firstname' => $address['shipping_first_name'],
		'ship_surname' => $address['shipping_last_name'],
		'ship_address1' => $address['shipping_address_1'],
		'ship_address2' => $address['shipping_address_2'],
		'ship_city' => $address['shipping_city'],
		'ship_state' => $address['shipping_state'],
		'ship_zip' => $address['shipping_postcode'],
		'shipping_type' => $shipping_type,
		'shipping_cost' => $shipping_cost,
		'item_sku' => implode(',', $item_sku), 
		'item_price' => implode(',', $item_price), 
		'quantity' => implode(',', $item_qty), 
		'transaction_key' => $transaction_key,
		'coupon_code' => implode( ",", $coupon )
		);

		// send API request via cURL
		$ch = curl_init();

		
		curl_setopt($ch, CURLOPT_URL, $endpoint."buyitem.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec ($ch);

		curl_close ($ch);

		// the handle response    
		if (strpos($response,'ERROR') !== false) {
		print_r($response);
		} else {
		// success
		}

		*/
		
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
