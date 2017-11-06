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
		
		
		/*
			
            'pickup_locations' => array(
                'title'       => 'Locaties',
                'type'        => 'multiselect',
                'description' => '',
                'desc_tip'    => true,
                'placeholder' => '',
                'default'     => '',
                'class'       => 'wc-enhanced-select',
                'options'     => self::get_available_locations(),
            ),
		*/
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
            $class = 'brt-display-none-';
            $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
            if( $chosen_shipping_methods[0] == $oMethod->id ){
                $class = 'brt-display-block';
            }
            
            $meta_data = $oMethod->get_meta_data();
            $aPickupLocations = self::get_available_locations();
            
            if(!empty($aPickupLocations))
			{
                //$checked = !empty($meta_data['pickup_chosen_location']) ? $meta_data['pickup_chosen_location'] : key($meta_data['pickup_locations']);
                $checked = $meta_data['pickup_chosen_location'];
                
                echo "<ul id='multiple-pickup-locations-list' class='pickup-locations {$class}'>";
                $i = 0;
                foreach( $meta_data['pickup_locations'] as $key ){
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
