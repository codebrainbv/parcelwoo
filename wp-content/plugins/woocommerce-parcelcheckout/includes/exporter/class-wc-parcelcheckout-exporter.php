<?php

if(!defined('ABSPATH')) 
{
	exit;
}


/**
* Parcelcheckout Export class
*/


class WC_Parcelcheckout_Exporter 
{
	public static function do_export($sPostType = 'product') 
	{
		global $wpdb;

		$sExportLimit = !empty($_POST['limit']) ? intval($_POST['limit']) : 9999;
		$sExportCount = 0;
		$sLimit = 100;
		$sExportOffset = !empty($_POST['offset']) ? intval($_POST['offset']) : 0;
		
		$aProductColumns = include('data/parcelcheckout-post-columns.php');	
		$aUserColumns = !empty($_POST['columns_name']) ? $_POST['columns_name'] : $aProductColumns;

		$sProductTaxonomies = get_object_taxonomies('product', 'name'); 
		$aExportColumns = !empty($_POST['columns']) ? $_POST['columns'] : '';
		$bHiddenMeta = !empty($_POST['include_hidden_meta']) ? true : false;
		$iProductLimit = !empty($_POST['product_limit']) ? sanitize_text_field($_POST['product_limit']) : '';
		$aHiddenColumns = include('data/parcelcheckout-meta-columns.php' );
			
		if($sLimit > $sExportLimit)
		{
			$sLimit = $sExportLimit;
		}
		
		$wpdb->hide_errors();
		
		// Get all metakeys from the products
		$aAllMetaKeys = self::get_all_metakeys('product');
		$aProductAttributes = self::get_all_product_attributes('product');
		
		// Loop products and load meta data
		$aProductMetaData = array();
		
		foreach($aAllMetaKeys as $sMetaKey)
		{
            if(!$sMetaKey)
			{
				continue;
			}
			
            if(!$bHiddenMeta && !in_array($sMetaKey, array_keys($aProductColumns)) && substr($sMetaKey, 0, 1 ) == '_')
			{
            	continue;
			}
			
            if($bHiddenMeta && (in_array($sMetaKey, $aHiddenColumns) || in_array($sMetaKey, array_keys($aProductColumns))))
			{
				continue;
			}
			
            $aProductMetaData[] = $sMetaKey;
        }

		$aProductMetaData = array_diff($aProductMetaData, array_keys($aProductColumns));

	
		$sXml = '<?xml version="1.0">';
		
		
	
		
		
		
		
		
		/*
		
		XML FILE
		
		
		<?xml version="1.0">
<message>
<type>item</type>
<messageNo>4318</messageNo>
<date>2016-11-27</date>
<time>17:48:34</time>
<items>
<item>
<itemNo>929000893806</itemNo>
<description>Xitanium 20W/m 0.15-0.5A 48V</description>
<description2/>
<unitOfMeasure>ST</unitOfMeasure>
<height>1</height>
<width>1</width>
<depth>1</depth>
<weight>1</weight>
<vendorItemNo/>
<eanNo>871829176663600</eanNo>
<bac>A</bac>
<validFrom/>
<validTo/>
<expiry>false</expiry>
<adr/>
<active>true</active>
<lot/>
<sortOrder/>
<minStock/>
<maxStock/>
<retailPrice>17.99</retailPrice>
<purchasePrice>15.99</purchasePrice>
<productType/>
<defaultMasterProduct>false</defaultMasterProduct>
<hangingStorage>false</hangingStorage>
<backOrder>false</backOrder>
<enriched>true</enriched>
</item>
</items>
</message>



*/
		
		
		
	}
	
	public static function get_all_metakeys($sPostType = 'product') 
	{
        global $wpdb;

        $aMetaKeys = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT pm.meta_key
            FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status IN ('publish', 'pending', 'private', 'draft')",
            $sPostType
        ));

        sort($aMetaKeys);

        return $aMetaKeys;
    }
		
	public static function get_all_product_attributes($sPostType = 'product') 
	{
        global $wpdb;

        $aResults = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT pm.meta_value
            FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status IN ('publish', 'pending', 'private', 'draft')
            AND pm.meta_key = '_product_attributes'",
            $sPostType
        ));

        // Go through each result, and look at the attribute keys within them.
        $aResult = array();

        if(!empty($aResults)) 
		{
            foreach($aResults as $_product_attributes) 
			{
                $aProductAttributes = maybe_unserialize(maybe_unserialize($_product_attributes));
				
                if(!empty($aProductAttributes) && is_array($aProductAttributes))
				{
                	foreach($aProductAttributes as $k => $v) 
					{
                   		if(!$k) 
						{
                   	 		continue;
                   		}
						
                   	 	if(!strstr($k, 'pa_')) 
						{
                   	 		if(empty($v['name'])) 
							{
                   	 			continue;
                   	 		}
							
                   	 		$k = $v['name'];
                   	 	}

                   	 	$aResult[$k] = $k;
                   	 }
                }
            }
        }

        sort($aResult);

        return $aResult;
    }	
		
		
		
		
		
		
		
		// Possibly no needed?
		/*
		@set_time_limit(0);
		
		if(function_exists('apache_setenv'))
		{
			@apache_setenv('no-gzip', 1);
		}
		
		@ini_set('zlib.output_compression', 0);
		@ob_clean();
		

		header('Content-Type: text/xml; charset=UTF-8');
		header('Content-Disposition: attachment; filename=woocommerce-product-export.xml');
		header('Pragma: no-cache');
		header('Expires: 0');
		
		$fp = fopen('php://output', 'w');
		*/
		
		
		
		
		
		
		
		
		// 
        
		/*      
		
		
		// Handle special fields like taxonomies
		if ( ! $aExportColumns || in_array( 'images', $aExportColumns ) ) {
			$row[] = 'images';
		}

		if ( ! $aExportColumns || in_array( 'file_paths', $aExportColumns ) ) {
			if ( function_exists( 'wc_get_filename_from_url' ) ) {
				$row[] = 'downloadable_files';
			} else {
				$row[] = 'file_paths';
			}
		}

		if ( ! $aExportColumns || in_array( 'taxonomies', $aExportColumns ) ) {
			foreach ( $sProductTaxonomies as $taxonomy ) {
				if ( strstr( $taxonomy->name, 'pa_' ) ) continue; // Skip attributes

				$row[] = 'tax:' . self::format_data( $taxonomy->name );
			}
		}

		if ( ! $aExportColumns || in_array( 'meta', $aExportColumns ) ) {
			foreach ( $found_product_meta as $product_meta ) {
				$row[] = 'meta:' . self::format_data( $product_meta );
			}
		}

		if ( ! $aExportColumns || in_array( 'attributes', $aExportColumns ) ) {
			foreach ( $aProductAttributes as $attribute ) {
				$row[] = 'attribute:' . self::format_data( $attribute );
				$row[] = 'attribute_data:' . self::format_data( $attribute );
				$row[] = 'attribute_default:' . self::format_data( $attribute );
			}
		}

		
		
		// WF: Adding product permalink.
		if ( ! $aExportColumns || in_array( 'product_page_url', $aExportColumns ) ) {
			$row[] = 'Product Page URL';
		}

		$row = array_map( 'WF_ProdImpExpCsv_Exporter::wrap_column', $row );
		fwrite( $fp, implode( ',', $row ) . "\n" );
		unset( $row );

		while ( $sExportCount < $export_limit ) {

			$product_args = apply_filters( 'woocommerce_csv_product_export_args', array(
				'numberposts' 	=> $sLimit,
				'post_status' 	=> array( 'publish', 'pending', 'private', 'draft' ),
				'post_type'		=> array('product'),
				'orderby' 		=> 'ID',
                                'suppress_filters'      => false,
				'order'			=> 'ASC',
				'offset'		=> $sExportOffset
			) );


				if ( $iProductLimit ) {
					$parent_ids               = array_map( 'intval', explode( ',', $iProductLimit ) );
					$child_ids                = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_parent IN (" . implode( ',', $parent_ids ) . ");" );
					$product_args['post__in'] = $child_ids;
				}
			
			$products = get_posts( $product_args );
			if ( ! $products || is_wp_error( $products ) )
				break;

			// Loop products
			foreach ( $products as $product ) {
                                if($product->post_parent == 0) $product->post_parent = '';
				$row = array();

				// Pre-process data
				$meta_data = get_post_custom( $product->ID );

				$product->meta = new stdClass;
				$product->attributes = new stdClass;

				// Meta data
				foreach ( $meta_data as $meta => $value ) {
					if ( ! $meta ) {
						continue;
					}
					if ( ! $bHiddenMeta && ! in_array( $meta, array_keys( $aProductColumns ) ) && substr( $meta, 0, 1 ) == '_' ) {
						continue;
					}
					if ( $bHiddenMeta && in_array( $meta, $aHiddenColumns ) ) {
						continue;
					}

					$meta_value = maybe_unserialize( maybe_unserialize( $value[0] ) );

					if ( is_array( $meta_value ) ) {
						$meta_value = json_encode( $meta_value );
					}

					$product->meta->$meta = self::format_export_meta( $meta_value, $meta );
				}

				// Product attributes
				if ( isset( $meta_data['_product_attributes'][0] ) ) {

					$attributes = maybe_unserialize( maybe_unserialize( $meta_data['_product_attributes'][0] ) );

					if ( ! empty( $attributes ) && is_array( $attributes ) ) {
						foreach ( $attributes as $key => $attribute ) {
							if ( ! $key ) {
								continue;
							}

							if ( $attribute['is_taxonomy'] == 1 ) {
								$terms = wp_get_post_terms( $product->ID, $key, array("fields" => "names") );
								if ( ! is_wp_error( $terms ) ) {
									$attribute_value = implode( '|', $terms );
								} else {
									$attribute_value = '';
								}
							} else {
								if ( empty( $attribute['name'] ) ) {
	                   	 			continue;
	                   	 		}
								$key             = $attribute['name'];
								$attribute_value = $attribute['value'];
							}

							if ( ! isset( $attribute['position'] ) ) {
								$attribute['position'] = 0;
							}
							if ( ! isset( $attribute['is_visible'] ) ) {
								$attribute['is_visible'] = 0;
							}
							if ( ! isset( $attribute['is_variation'] ) ) {
								$attribute['is_variation'] = 0;
							}

							$attribute_data      = $attribute['position'] . '|' . $attribute['is_visible'] . '|' . $attribute['is_variation'];
							$_default_attributes = isset( $meta_data['_default_attributes'][0]  ) ? maybe_unserialize( maybe_unserialize( $meta_data['_default_attributes'][0] ) ) : '';

							if ( is_array( $_default_attributes ) ) {
								$_default_attribute = isset( $_default_attributes[ $key ] ) ? $_default_attributes[ $key ] : '';
							} else {
								$_default_attribute = '';
							}

							$product->attributes->$key = array(
								'value'		=> $attribute_value,
								'data'		=> $attribute_data,
								'default'	=> $_default_attribute
							);
						}
					}
				}

				// GPF
				if ( isset( $meta_data['_woocommerce_gpf_data'][0] ) ) {
					$product->gpf_data = $meta_data['_woocommerce_gpf_data'][0];
				}

				// Get column values
				foreach ( $aProductColumns as $column => $value ) {
					if ( ! $aExportColumns || in_array( $column, $aExportColumns ) ) {

						if ($column == '_regular_price' && empty( $product->meta->$column ) ) {
							$column = '_price';
						}

						if ( isset( $product->meta->$column ) ) {
							$row[] = self::format_data( $product->meta->$column );
						} elseif ( isset( $product->$column ) && ! is_array( $product->$column ) ) {
							if ( $column === 'post_title' ) {
								$row[] = sanitize_text_field( $product->$column );
							} else {
								$row[] = self::format_data( $product->$column );
							}
						} else {
							$row[] = '';
						}
					}
				}

				// Export images/gallery
				if ( ! $aExportColumns || in_array( 'images', $aExportColumns ) ) {

					$image_file_names = array();

					// Featured image
					if ( ( $featured_image_id = get_post_thumbnail_id( $product->ID ) ) && ( $image = wp_get_attachment_image_src( $featured_image_id, 'full' ) ) ) {
						$image_file_names[] = current( $image );
					}

					// Images
					$images  = isset( $meta_data['_product_image_gallery'][0] ) ? explode( ',', maybe_unserialize( maybe_unserialize( $meta_data['_product_image_gallery'][0] ) ) ) : false;
					$aResults = array();

					if ( $images ) {
						foreach ( $images as $image_id ) {
							if ( $featured_image_id == $image_id ) {
								continue;
							}
							$image = wp_get_attachment_image_src( $image_id, 'full' );
							if ( $image ) {
								$image_file_names[] = current( $image );
							}
						}
					}

					$row[] = implode( ' | ', $image_file_names );

				}

				// Downloadable files
				if ( ! $aExportColumns || in_array( 'file_paths', $aExportColumns ) ) {
					if ( ! function_exists( 'wc_get_filename_from_url' ) ) {
						$file_paths           = maybe_unserialize( maybe_unserialize( $meta_data['_file_paths'][0] ) );
						$file_paths_to_export = array();

						if ( $file_paths ) {
							foreach ( $file_paths as $file_path ) {
								$file_paths_to_export[] = $file_path;
							}
						}

						$file_paths_to_export = implode( ' | ', $file_paths_to_export );
						$row[]                = self::format_data( $file_paths_to_export );
					} elseif ( isset( $meta_data['_downloadable_files'][0] ) ) {
						$file_paths           = maybe_unserialize( maybe_unserialize( $meta_data['_downloadable_files'][0] ) );
						$file_paths_to_export = array();

						if ( $file_paths ) {
							foreach ( $file_paths as $file_path ) {
								$file_paths_to_export[] = ( ! empty( $file_path['name'] ) ? $file_path['name'] : wc_get_filename_from_url( $file_path['file'] ) ) . '::' . $file_path['file'];
							}
						}
						$file_paths_to_export = implode( ' | ', $file_paths_to_export );
						$row[]                = self::format_data( $file_paths_to_export );
					} else {
						$row[]                = '';
					}
				}

				// Export taxonomies
				if ( ! $aExportColumns || in_array( 'taxonomies', $aExportColumns ) ) {
					foreach ( $sProductTaxonomies as $taxonomy ) {
						if ( strstr( $taxonomy->name, 'pa_' ) ) continue; // Skip attributes

						if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
							$terms           = wp_get_post_terms( $product->ID, $taxonomy->name, array( "fields" => "all" ) );
							$formatted_terms = array();

							foreach ( $terms as $term ) {
								$ancestors      = array_reverse( get_ancestors( $term->term_id, $taxonomy->name ) );
								$formatted_term = array();

								foreach ( $ancestors as $ancestor )
									$formatted_term[] = get_term( $ancestor, $taxonomy->name )->name;

								$formatted_term[]  = $term->name;

								$formatted_terms[] = implode( ' > ', $formatted_term );
							}

							$row[] = self::format_data( implode( '|', $formatted_terms ) );
						} else {
							$terms = wp_get_post_terms( $product->ID, $taxonomy->name, array( "fields" => "names" ) );

							$row[] = self::format_data( implode( '|', $terms ) );
						}
					}
				}

				// Export meta data
				if ( ! $aExportColumns || in_array( 'meta', $aExportColumns ) ) {
					foreach ( $found_product_meta as $product_meta ) {
						if ( isset( $product->meta->$product_meta ) ) {
							$row[] = self::format_data( $product->meta->$product_meta );
						} else {
							$row[] = '';
						}
					}
				}

				// Find and export attributes
				if ( ! $aExportColumns || in_array( 'attributes', $aExportColumns ) ) {
					foreach ( $aProductAttributes as $attribute ) {
						if ( isset( $product->attributes ) && isset( $product->attributes->$attribute ) ) {
							$values = $product->attributes->$attribute;
							$row[] = self::format_data( $values['value'] );
							$row[] = self::format_data( $values['data'] );
							$row[] = self::format_data( $values['default'] );
						} else {
							$row[] = '';
							$row[] = '';
							$row[] = '';
						}
					}
				}

				// Export GPF
				if ( function_exists( 'woocommerce_gpf_install' ) && ( ! $aExportColumns || in_array( 'gpf', $aExportColumns ) ) ) {

					$gpf_data = empty( $product->gpf_data ) ? '' : maybe_unserialize( $product->gpf_data );

					$row[] = empty( $gpf_data['availability'] ) ? '' : $gpf_data['availability'];
					$row[] = empty( $gpf_data['condition'] ) ? '' : $gpf_data['condition'];
					$row[] = empty( $gpf_data['brand'] ) ? '' : $gpf_data['brand'];
					$row[] = empty( $gpf_data['product_type'] ) ? '' : $gpf_data['product_type'];
					$row[] = empty( $gpf_data['google_product_category'] ) ? '' : $gpf_data['google_product_category'];
					$row[] = empty( $gpf_data['gtin'] ) ? '' : $gpf_data['gtin'];
					$row[] = empty( $gpf_data['mpn'] ) ? '' : $gpf_data['mpn'];
					$row[] = empty( $gpf_data['gender'] ) ? '' : $gpf_data['gender'];
					$row[] = empty( $gpf_data['age_group'] ) ? '' : $gpf_data['age_group'];
					$row[] = empty( $gpf_data['color'] ) ? '' : $gpf_data['color'];
					$row[] = empty( $gpf_data['size'] ) ? '' : $gpf_data['size'];
					$row[] = empty( $gpf_data['adwords_grouping'] ) ? '' : $gpf_data['adwords_grouping'];
					$row[] = empty( $gpf_data['adwords_labels'] ) ? '' : $gpf_data['adwords_labels'];
				}
				
				// WF: Adding product permalink.
				if ( ! $aExportColumns || in_array( 'product_page_url', $aExportColumns ) ) {
					$product_page_url = '';
					if ( $product->ID ) {
						$product_page_url = get_permalink( $product->ID );
					}
					if ( $product->post_parent ) {
						$product_page_url = get_permalink( $product->post_parent );
					}
					
					$row[] = $product_page_url;
				}

				// Add to csv
				$row = array_map( 'WF_ProdImpExpCsv_Exporter::wrap_column', $row );
				fwrite( $fp, implode( ',', $row ) . "\n" );
				unset( $row );
				
			}
			$sExportOffset += $sLimit;
			$sExportCount   += $sLimit;
			unset( $products );
		}
		
		fclose( $fp );
		exit;
		
		
		*/
	

	/*

	public static function format_export_meta( $meta_value, $meta ) {
		switch ( $meta ) {
			case '_sale_price_dates_from' :
			case '_sale_price_dates_to' :
				return $meta_value ? date( 'Y-m-d', $meta_value ) : '';
			break;
			case '_upsell_ids' :
			case '_crosssell_ids' :
				return implode( '|', array_filter( (array) json_decode( $meta_value ) ) );
			break;
			default :
				return $meta_value;
			break;
		}
	}

	public static function format_data( $data ) {
		$enc  = mb_detect_encoding( $data, 'UTF-8, ISO-8859-1', true );
		$data = ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
		return $data;
	}

	
	public static function wrap_column( $data ) {
		return '"' . str_replace( '"', '""', $data ) . '"';
	}

	
    

    
	
	*/
}