<?php

	
// require_once(dirname(__FILE__) . '/library.php');


class WoocommerceParcel
{
	protected $sOptionName = 'parcelcheckout_settings';
	protected $sOptionGroup = 'parcelcheckout_settings';
	protected $sSectionName = 'postcodecheckout_settings_section1';

	public $bEnabled;

	
	public function __construct()
	{
		add_action('admin_menu', array($this, 'add_to_menu'), 99);
		add_action('admin_init', array($this, 'init_settings_fields'));
		
		// load active
		// add_action('wp', array($this, 'load_validation'));
	}
	
	/**
	 * Add submenu item to WooCommerce
	 */
	public function add_to_menu() 
	{
		$this->page = add_submenu_page('woocommerce', __( 'Parcel Checkout', ADDR_DOMAIN ),	__( 'Parcel Checkout', ADDR_DOMAIN ), 'manage_woocommerce',	'parcel_menu', array($this, 'render_settings'));
	}
	
	/**
	 ** This initialises the settings field
	 */
	function init_settings_fields()
	{
			
		register_setting(
				'parcelcheckout_optiongroup', // Option group
				'parcelcheckout_optionname', // Option name
				array($this, 'sanitize')
		);

		add_settings_section(
				'parcelcheckout_setting_id', // ID in the html
		__('Parcelcheckout - Instellingen', ADDR_DOMAIN), // Title
		array($this, 'print_section_info'), // Callback
				'parcelcheckout-setting-admin' // Page
		);

		add_settings_field(
				'enable',
		__('Enable/Disable', ADDR_DOMAIN),
		array($this, 'render_enable_setting'),
				'parcelcheckout-setting-admin',
				'parcelcheckout_setting_id'
				);
	}
	
	
	public function render_settings()
	{
?>

<div class="wrap">
<?php screen_icon(); ?>
	<h2>Parcel Checkout - Instellingen</h2>
	<p>Configureer uw Parcel Checkout plug-in zodat deze werkt met het PostNL ECS.</p>
	
	<form method="post" action="options.php">
	<?php
		// This prints out all hidden setting fields
		settings_fields('parcelcheckout_optiongroup');
		do_settings_sections('parcelcheckout-setting-admin');
		submit_button();
	?>
	</form>
</div>

	<?php
	}

	//Render enable button
	function render_enable_setting() 
	{
		$aOptions = get_option('parcelcheckout_optionname');
			
		if(isset($aOptions['enable']) && $aOptions['enable'] == 'checked')
		{
			$bChecked = "checked='checked'";
		} 
		else 
		{
			$bChecked = '';
		}
?>

	<input type='checkbox' name='parcelcheckout_optionname[enable]'<?php echo $bChecked ?>'>

<?php
	}



}

?>
