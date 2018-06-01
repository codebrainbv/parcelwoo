<?php



	// Add EAN field + Save
	add_action('woocommerce_product_options_inventory_product_data', 'addEanFieldToProduct');
	add_action('woocommerce_process_product_meta', 'saveEanCode');
	// Show notice on product page
	add_action('admin_notices', 'showProductNotice');



	function addEanFieldToProduct()
	{
		 global $post;

		//Add EAN field to a product
		woocommerce_wp_text_input(
			array(
			 'id' => 'pc_product_ean',
			 'label' => 'EAN',
			 'desc_tip' => 'true',
			 'description' => 'Voeg de product specifieke EAN code toe, dit is verplicht voor PostNL',
			 'value' => get_post_meta($post->ID, 'pc_product_ean', true),
			 'custom_attributes' => array(
				'required' => 'required'
				),
			)
		);
	}


	function saveEanCode($iPostId)
	{
		$sPostedEanCode = $_POST['pc_product_ean'];

		// Save the EAN
		if(isset($sPostedEanCode))
		{
			update_post_meta($iPostId, 'pc_product_ean', esc_attr($sPostedEanCode));
		}

		// Remove if EAN meta is empty
		$sMetaEanCode = get_post_meta($iPostId, 'pc_product_ean', true);

		if(empty($sMetaEanCode))
		{

			delete_post_meta($iPostId, 'pc_product_ean', '');
		}
	}

	
	
	
	function showProductNotice()
	{
		global $post;

		$sAction = '';

		if(!empty($_GET['action']))
		{
			$sAction = $_GET['action'];
		}

		
		if($post)
		{
			if((strcasecmp($post->post_type, 'product') === 0) && (strcmp($sAction, 'edit') === 0))
			{
				$oProduct = wc_get_product($post->ID);

				if(empty($oProduct->get_sku()))
				{
					echo '<div class="error is-dismissible">
							<p>Product SKU is leeg, deze is verplicht voor PostNL fulfillment!</p>
					</div>';
				}

				if(empty($oProduct->get_description()))
				{
					echo '<div class="error is-dismissible">
							<p>Product omschrijving is leeg, deze is verplicht voor PostNL fulfillment!</p>
					</div>';
				}
			}
		}
	}




?>