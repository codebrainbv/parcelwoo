<?php
	class FailedOrder {
		var $OrderiD;
		var $arrayObject = array();
		// Add new element
		public function addError($errror) {
			array_push($this->arrayObject, $errror);
		}
		public function get_errors() {
			return $this->arrayObject;
		}
		public function set_orderID($OrderiD) {
			$this->OrderiD = $OrderiD;
		}
		public function get_orderID() {
			return $this->OrderiD;
		}
	}
	
	class ni_order_list {
		public function __construct() {
			
		}
		
		public function page_init() {
			
		}
		
		function productExport() {
		
				require_once(dirname(__DIR__) . "/ecsSftpProcess.php");
				require_once(dirname(__DIR__) . "/export/EcsProductSettings.php");
				$EcsSftpSettings = ecsSftpProcess::init();
		
				$EcsProductSettings = ecsProductSettings::init();
				$Path = '';
				$settingID = $EcsProductSettings->getSettingId();
				if ($settingID) 
				$statesmeta = $EcsProductSettings->loadProductSettings($settingID);
				else {
					error_log('Product settings not found');
					return;
				}
				foreach ($statesmeta as $k) {
				
				if ($k->keytext == "Path") {
					$Path = $k->value;
				}
				if ($k->keytext == "no") {
					$no = $k->value;
				}
				
			}
				$ftpCheck = $EcsSftpSettings->checkSftpSettings($Path);
				
				if($ftpCheck[0] == 'SUCCESS') {
				$sftp = $ftpCheck[1];
				
				$lastfile = '';
				
				global $wpdb;
				$table_name_ecs = $wpdb->prefix . 'ecs';
				$qrymeta = "SELECT * FROM $table_name_ecs " . "WHERE keytext = 'LastproductID'  ";
				$statesmeta = $wpdb->get_results($qrymeta);
				$orderNo = '0';

				foreach($statesmeta as $k) {
					$orderNo = $k->type;
					$NextorderNo = $orderNo + 1;
					$wpdb->query($wpdb->prepare("UPDATE $table_name_ecs SET type = '".$NextorderNo."' WHERE  id= %d", $k->id));
				}

				$xml = new DOMDocument('1.0');
				$message = $xml->createElementNS(null, 'message');
				$xml->appendChild($message);
				$message->appendChild($xml->createElement('type', 'item'));
				$message->appendChild($xml->createElement('messageNo', $orderNo));
				$t = time();
				$message->appendChild($xml->createElement('date', date("Y-m-d", $t)));
				$message->appendChild($xml->createElement('time', date("H:i:s", $t)));
				$products = $xml->createElement('items');
				$message->appendChild($products); 
				$Products = get_posts(array(
					'post_type' => 'product',
					//'post_status' => wc_get_order_statuses(), //get all available order statuses in an array
					'posts_per_page' => 100,
					'meta_query' => array(
						array(
							'key' => 'ecsExport',
							'compare' => 'NOT EXISTS'
						)
					)
				));
				
				$Productchunck = array_chunk($Products, $no);
				$FailedOrders = array();

				foreach($Productchunck as $Product_split) {
					$isEmpty = 0;
					foreach($Product_split as $product) {
						$product_id = $product->ID;
						
						$isvalidate = true;
						$failed = new FailedOrder();
						$failed->set_orderID($product_id);
						$productpost = $product;
						$product = new WC_Product($product_id);
						$node = $xml->createElement('item');
						
						
					if(strlen($product->get_sku()) == 0) {
							$failed->addError(" itemNo length is null");
							$isvalidate = false;
						} else {
							if(strlen($product->get_sku()) > 24) {
								$failed->addError(" itemNo length is greater than 24 characters");
								$isvalidate = false;
								error_log("itemNo length is greater than 24 characters");
							}
						} 
						$node->appendChild($xml->createElement('itemNo', $product->get_sku()));
						if(strlen($productpost->post_name) == 0) {
							$failed->addError(" description length is null");
							$isvalidate = false;
						} else {
							$description2 = '';
							if(strlen($productpost->post_name) > 35) {
								$description2 = substr($productpost->post_name, -35);
								//split in two
								$node->appendChild($xml->createElement('description', substr($productpost->post_name, 0, 34)));
								if(strlen($description2) > 35) {
									$failed->addError(" post_name length is greater than 35 characters");
									$isvalidate = false;
								}
								$node->appendChild($xml->createElement('description2', $description2));
							} else {
								//split in two
								$node->appendChild($xml->createElement('description', $productpost->post_name));
								$node->appendChild($xml->createElement('description2', ''));
							}
						}
						$attributes = $product->get_attributes();
						$unitOfMeasure = '';
						$vendorItemNo = '';
						$bac = '';
						$validFrom = '';
						$validTo = '';
						$adr = '';
						$lot = '';
						$sortOrder = '';
						$minStock = '';
						$maxStock = '';
						$productType = '';
						
						foreach($attributes as $attribute) {
							$var1 = $attribute['name'];
							$var2 = "eanNo";
							$unitOfMeasure1 = "unitOfMeasure";
							$vendorItemNo1 = "vendorItemNo";
							$bac1 = "bac";
							$unitOfMeasure1 = "unitOfMeasure";
							if(strcasecmp($var1, $unitOfMeasure1) == 0) {
								$unitOfMeasure = $attribute['value'];
							}
							if(strcasecmp($var1, $vendorItemNo1) == 0) {
								$vendorItemNo = $attribute['value'];
							}
							if(strcasecmp($var1, $bac1) == 0) {
								$bac = $attribute['value'];
							}
							if(strcasecmp($var1, 'validFrom') == 0) {
								$validFrom = $attribute['value'];
							}
							if(strcasecmp($var1, 'validTo') == 0) {
								$validTo = $attribute['value'];
							}
							if(strcasecmp($var1, 'adr') == 0) {
								$adr = $attribute['value'];
							}
							if(strcasecmp($var1, 'lot') == 0) {
								$lot = $attribute['value'];
							}
							if(strcasecmp($var1, 'sortOrder') == 0) {
								$sortOrder = $attribute['value'];
							}
							if(strcasecmp($var1, 'minStock') == 0) {
								$minStock = $attribute['value'];
							}
							if(strcasecmp($var1, 'maxStock') == 0) {
								$maxStock = $attribute['value'];
							}
							if(strcasecmp($var1, 'productType') == 0) {
								$productType = $attribute['value'];
							}
						}
						
						if(strlen($unitOfMeasure) > 10) {
							$failed->addError(" unitOfMeasure length is greater than 10 characters");
							$isvalidate = false;
						}
						if(strlen($unitOfMeasure) == 0) {
							$node->appendChild($xml->createElement('unitOfMeasure', 'ST'));
						} else {
							$node->appendChild($xml->createElement('unitOfMeasure', $unitOfMeasure));
						}
						$height = $product->get_height();
						if(strlen($product->get_height()) == 0) {
							$height = 1;
						} else {
							if(strlen($product->get_height()) > 255) {
								$failed->addError(" height length is greater than 255 characters");
								$isvalidate = false;
							}
						}
						$node->appendChild($xml->createElement('height', $height));
						$width = $product->get_width();
						if(strlen($product->get_width()) == 0) {
							$width = 1;
						} else {
							if(strlen($product->get_width()) > 255) {
								$failed->addError(" width length is greater than 255 characters");
								$isvalidate = false;
							}
						}
						$node->appendChild($xml->createElement('width', $width));
						$length = $product->get_height();
						if(strlen($product->get_height()) == 0) {
							$length = 1;
						} else {
							if (strlen($product->get_length()) > 255) {
								$failed->addError(" Product length length is greater than 255 characters");
								$isvalidate = false;
							}
						}
						$node->appendChild($xml->createElement('depth', $length));
						$weight = $product->get_weight();
						if(strlen($product->get_weight()) == 0) {
							$weight = 1;
						} else {
							if (strlen($product->get_weight()) > 255) {
								$failed->addError(" Product weight length is greater than 255 characters");
								$isvalidate = false;
							}
						}
						$node->appendChild($xml->createElement('weight', $weight));
						if(strlen($vendorItemNo) > 30) {
							$failed->addError(" vendorItemNo length is greater than 30 characters");
							$isvalidate = false;
						}
						$node->appendChild($xml->createElement('vendorItemNo', $vendorItemNo));
						$eanNo = '';
						if(strlen($eanNo) == 0) {
							$eanNo = $product->get_sku();
							
						} else {
							if(strlen($eanNo) > 24) {
								$failed->addError(" eanNo length is greater than 24 characters");
								$isvalidate = false;
							}
						}
						$node->appendChild($xml->createElement('eanNo', $eanNo));
						if(strlen($bac) > 255) {
							$failed->addError(" bac length is greater than 255 characters");
							$isvalidate = false;
						}
						$node->appendChild($xml->createElement('bac', $bac));
						$node->appendChild($xml->createElement('validFrom', $validFrom));
						$node->appendChild($xml->createElement('validTo', $validTo));
						$node->appendChild($xml->createElement('expiry', 'false'));
						$node->appendChild($xml->createElement('adr', $adr));
						$node->appendChild($xml->createElement('active', 'true'));
						$node->appendChild($xml->createElement('lot', $lot));
						$node->appendChild($xml->createElement('sortOrder', $sortOrder));
						$node->appendChild($xml->createElement('minStock', $minStock));
						$node->appendChild($xml->createElement('maxStock', $maxStock));
						$node->appendChild($xml->createElement('retailPrice', $product->get_regular_price()));
						if(strlen($product->get_sale_price()) == 0) {
							$node->appendChild($xml->createElement('purchasePrice', $product->get_regular_price()));
						} else {
							$node->appendChild($xml->createElement('purchasePrice', $product->get_sale_price()));
						}
						$node->appendChild($xml->createElement('productType', $productType));
						$node->appendChild($xml->createElement('defaultMasterProduct', 'false'));
						$node->appendChild($xml->createElement('hangingStorage', 'false'));
						$back = $product->get_backorders();
						if(strcmp($back, "no") !== 0) {
							$back = 'true';
						} else {
							$back = 'false';
						}
						$node->appendChild($xml->createElement('backOrder', $back));
						$node->appendChild($xml->createElement('enriched', 'true'));
						if($isvalidate == true) {
							$products->appendChild($node);
							add_post_meta($product_id, 'ecsExport', 'yes');
							$isEmpty = $isEmpty + 1;
						} else {
							array_push($FailedOrders, $failed);
						}
					}
					
					$result = count($Products);
					if($isEmpty > 0) {
						$t = time();
						$filename = 'PRD' . date("YmdHis", $t) . '.xml';
						$Errors = '
							<!DOCTYPE html>
							<html>
								<body><p>';
									$Errors .= 'An error occurred processing  Product export file';
									$Errors .= '<br><b>Message:</b><br>';
									foreach($FailedOrders as $fails) {
										$Errors .= '<br>';
										$Errors .= 'Product ID :' . $fails->get_orderID();
										$Errors .= '<br>';
										foreach ($fails->get_errors() as $fail) {
											$Errors .= $fail;
											$Errors .= '<br>';
										}
									}
								'</p></body>
							</html>';
							
						global $wpdb;
						$name = '';
						$email = ''; 
						// find list of states in DB
						$table_name_ecs = $wpdb->prefix . 'ecs';
						$qry = "SELECT * FROM $table_name_ecs " . "WHERE keytext ='general' ORDER BY id DESC  LIMIT 1 ";
						$states = $wpdb->get_results($qry);
						$settingID = '';
						
						foreach($states as $k) {
							$settingID = $k->id;
						}
						
						$table_name = $wpdb->prefix . 'ecsmeta';
						// find list of states in DB
						$qrymeta = "SELECT * FROM ".$table_name." WHERE settingid = '".$settingID."' ";
						$statesmeta = $wpdb->get_results($qrymeta);
						foreach($statesmeta as $k) {
							if($k->keytext == "Name") {
								$name = $k->value;
							}
							if($k->keytext == "Email") {
								$email = $k->value;
							}
						} 
						
						$to = $email;
						$subject = 'PostNL ECS plugin processing error';
						$body = $Errors;
						$headers = array(
							'Content-Type: text/html; charset=UTF-8'
						);
					
						
						if(count($FailedOrders) > 0) {
							wp_mail( $to, $subject, $body, $headers );
							
						}
						
						$message->appendChild($products);
						$xml->appendChild($message);
						$xml->save(ECS_DATA_PATH."/product.xml");
						$t = time();
						$filename = 'PRD' . date("YmdHis", $t) . '.xml';
						$local_directory = ECS_DATA_PATH.'/product.xml';
						
						$remote_directory = 'woocommerce_test/Productdata/';
						$remote_directory = $Path . '/';
						$success = $sftp->put($remote_directory . $filename, $local_directory, NET_SFTP_LOCAL_FILE);
						global $wpdb;
						$table_name_ecs = $wpdb->prefix . 'ecs';
						$querylast = "SELECT * FROM $table_name_ecs " . "WHERE keytext = 'lastproductname'  ";
						$statesmeta = $wpdb->get_results($querylast);
						$lastname = '';
						if(count($statesmeta) > 0) {
							foreach($statesmeta as $k) {
								$wpdb->query($wpdb->prepare("UPDATE ".$table_name_ecs." SET type = '".$filename."' WHERE id= %d", $k->id));
							}
						} else {
							$wpdb->insert($table_name_ecs, array(
								'type' => $filename,
								'enable' => 'true',
								'keytext' => 'lastproductname'
							));
						}
					} 
				}
	
				if(count($FailedOrders) > 0) {
					$t = time();
					$filename = 'PRD' . date("YmdHis", $t) . '.xml';
					$Errors = '
						<!DOCTYPE html>
						<html>
							<body><p>';
								$Errors .= 'An error occurred processing  Product export file';
								$Errors .= '<br><b>Message:</b><br>';
								foreach($FailedOrders as $fails) {
									$Errors .= '<br>';
									$Errors .= 'Product ID :' . $fails->get_orderID();
									$Errors .= '<br>';
									foreach($fails->get_errors() as $fail) {
										$Errors .= $fail;
										$Errors .= ' <br>';
									}
								}
							'</p></body>
						</html>';
						
					global $wpdb;
					$name = '';
					$email = '';
					
						
					// find list of states in DB
					$table_name_ecs = $wpdb->prefix . 'ecs';
					$qry = "SELECT * FROM ".$table_name_ecs." WHERE keytext ='general' ORDER BY id DESC  LIMIT 1";
					$states = $wpdb->get_results($qry);
					$settingID = '';
					foreach($states as $k) {
						$settingID = $k->id;
					}
					$table_name = $wpdb->prefix . 'ecsmeta';
					// find list of states in DB
					$qrymeta = "SELECT * FROM ".$table_name." WHERE settingid = '".$settingID."'";
					$statesmeta = $wpdb->get_results($qrymeta);
					
					foreach($statesmeta as $k) {
						if($k->keytext == "Name") {
							$name = $k->value;
						}
						if($k->keytext == "Email") {
							$email = $k->value;

						}
					}
					
						
					$to = $email;
					$subject = 'PostNL ECS plugin processing error';
					$body = $Errors;
					$headers = array(
						'Content-Type: text/html; charset=UTF-8'
					);
					
					if (count($FailedOrders) > 0) {
						wp_mail($to, $subject, $body, $headers);

					}
				}
			
				
				
				} else {
				error_log('ERROR: POSTNL ECS Product Export: '. $ftpCheck[1]); 
				}
			
				

				
		}  
						

		function woocommerce_version_check($version = '2.1') {
		
			if(function_exists('is_woocommerce_active') && is_woocommerce_active()) {
				global $woocommerce;
				if(version_compare($woocommerce->version, $version, '>=' )) {
				return true;
				}
			
			}
			return false;


		}
  		
		function orderExport() {
		
	
			require_once(dirname(__DIR__) . "/ecsSftpProcess.php");
				require_once(dirname(__DIR__) . "/export/EcsOrderSettings.php");
				$EcsSftpSettings = ecsSftpProcess::init();
				$EcsOrderSettings = ecsOrderSettings::init(); 
				$Path = '';
				$settingID = $EcsOrderSettings->getSettingId();
				if($settingID) {
					$statesmeta = $EcsOrderSettings->loadOrderSettings($settingID); }
				else { 
				error_log('Order Settings not found'); 
				return;
				}
				
				foreach ($statesmeta as $k) {
				
				if ($k->keytext == "Path") {
					$Path = $k->value;
				}
				if ($k->keytext == "no") {
					$no = $k->value;
				}
				
			} 
			$ftpCheck = $EcsSftpSettings->checkSftpSettings($Path);
			
			if($ftpCheck[0] != 'SUCCESS') {
			
			error_log('ERROR: POSTNL ECS Product Export: '. $ftpCheck[1]); 
			
			}
			 else {
			
				$sftp = $ftpCheck[1];
				$order = new WC_Order();
				global $wpdb;
				
				
				foreach($statesmeta as $k) {
					if($k->keytext == "Cron") {
						$Cron = $k->value;
					}
					if($k->keytext == "Path") {
						$Path = $k->value;
					}
					if($k->keytext == "Shipping") {
						$Shipping = $k->value;
					}
					if($k->keytext == "Status") {
						$Status = $k->value;
					}
				}
				
				$StartPath = $sftp->pwd();
				$sftp->chdir($Path);
				$endPath = $sftp->pwd();
				$date = date_create($order->get_date_created());
				$xml = new DOMDocument();
				$table_name_ecs = $wpdb->prefix . 'ecs';
				$qrymeta = "SELECT * FROM ".$table_name_ecs." WHERE keytext = 'LastOrderID'";
				$statesmeta = $wpdb->get_results($qrymeta);
				$orderNo = '';
				
				foreach($statesmeta as $k) {
					$orderNo = $k->type;
					$NextorderNo = $orderNo + 1;
					$wpdb->query($wpdb->prepare("UPDATE ".$table_name_ecs." SET type = '".$NextorderNo."' WHERE   id= %d", $k->id));
				}
				
				$NextorderNo = $orderNo + 1;
				$message = $xml->createElementNS(null, 'message');
				$xml->appendChild($message);
				$message->appendChild($xml->createElement('type', 'deliveryOrder'));
				$message->appendChild($xml->createElement('messageNo', $orderNo));
				$t = time();
				$message->appendChild($xml->createElement('date', date("Y-m-d", $t)));
				$message->appendChild($xml->createElement('time', date("H:i:s", $t)));
				$orders = $xml->createElement('deliveryOrders');
				$message->appendChild($orders);
				global $wpdb;
				$orderStatus = '';
				$shipment = '';
				$no = '';
				
			
				// find list of states in DB
				$table_name_ecs = $wpdb->prefix . 'ecs';
				$qry = "SELECT * FROM ".$table_name_ecs." WHERE keytext ='OrderExport' ORDER BY id DESC LIMIT 1";
				$states = $wpdb->get_results($qry);
				$settingID = '';
				
				foreach($states as $k) {
					$settingID = $k->id;
				}
				
				$table_name = $wpdb->prefix . 'ecsmeta';
				// find list of states in DB
				$qrymeta = "SELECT * FROM ".$table_name." WHERE settingid = '".$settingID."'";
				$statesmeta = $wpdb->get_results($qrymeta);
				
				foreach($statesmeta as $k) {
					if($k->keytext == "Status") {
						$orderStatus = $k->value;
					}
					if($k->keytext == "Shipping") {
						$shipment = $k->value;
					}
					if($k->keytext == "no") {
						$no = $k->value;
					}
				}
				
				$ordersW = '';
				$orderStatusArray = explode(":", $orderStatus);
				$orderStatusArray2 = array();
				
				foreach($orderStatusArray as $orderss) {
					array_push($orderStatusArray2, 'wc-' . $orderss);
				}
				
				$ordersW = get_posts(array(
					'post_type' => 'shop_order',
					'post_status' => $orderStatusArray2,
					'posts_per_page' => 100,
					'meta_query' => array(
						array(
							'key' => 'ecsExport',
							'compare' => 'NOT EXISTS'
						)
					)
				));
				
				$Orderchunck  = array_chunk($ordersW, $no);
				$FailedOrders = array();
				$shipementsArray = explode(":", $shipment); 
				$shipements = array();
				
				foreach($shipementsArray as $ship) {
					array_push($shipements, $ship);
				}
				
				foreach($Orderchunck as $order_split) {
					$isEmpty = 0;
					foreach ($order_split as $order) {
						$order= new WC_Order($order->ID);
						$order_shipping_method_id = 'l ';
						$shipping_items = $order->get_items('shipping');
						
						foreach($shipping_items as $el) {
							$order_shipping_method_id = $el['method_id'];
						}
						
						$split = explode(":", $order_shipping_method_id);
						$order_shipping_method_id = $split[0];
						
					
						if($order_shipping_method_id == 'l ') $order_shipping_method_id = "disabled";
						
						if(in_array($order_shipping_method_id, $shipements)) {
							$isvalidate = true;
							$order_id = $order->get_id();
							$failed = new FailedOrder();
							$failed->set_orderID($order_id);
							$order = new WC_Order($order_id);
							
							
				$date = date_create ($order->get_date_created());
							$node = $xml->createElement('deliveryOrder');
							if(strlen($order->get_id()) == 0) {
								$failed->addError(" orderNo length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_id()) > 10) {
									$failed->addError(" orderNo length is greater than 10 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('orderNo', $order->get_id()));
							if(strlen($order->get_id()) == 0) {
								$failed->addError(" webOrderNo length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_id()) > 10) {
									$failed->addError(" webOrderNo length is greater than 10 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('webOrderNo', $order->get_id()));
							$t = time();
							$node->appendChild($xml->createElement('orderDate', date("Y-m-d")));
							$node->appendChild($xml->createElement('orderTime', date("H:i:s")));
							$node->appendChild($xml->createElement('customerNo', ''));
							$node->appendChild($xml->createElement('onlyHomeAddress', 'false'));
							$node->appendChild($xml->createElement('vendorNo', ''));
	
							
						//  shipping
							$node->appendChild($xml->createElement('shipToTitle', ''));
		
							if(strlen($order->get_shipping_last_name()) == 0) {
								$failed->addError(" shipToLastName length is null");
								
								$isvalidate = false;
							} else {
								if(strlen($order->get_shipping_last_name()) > 35) {
									$failed->addError(" shipping_last_name length is greater than 35 characters");
									
									$isvalidate = false;
								}
							}


						if ($isvalidate == false) error_log("check start");




							$node->appendChild($xml->createElement('shipToFirstName', $order->get_shipping_first_name()));
							$node->appendChild($xml->createElement('shipToLastName', $order->get_shipping_last_name()));
							$node->appendChild($xml->createElement('shipToCompanyName', $order->get_shipping_company()));
							$node->appendChild($xml->createElement('shipToBuildingName', ''));
							$node->appendChild($xml->createElement('shipToDepartment', ''));
							$node->appendChild($xml->createElement('shipToFloor', ''));
							$node->appendChild($xml->createElement('shipToDoorcode', ''));
							$node->appendChild($xml->createElement('shipToStreet', ''));
							$node->appendChild($xml->createElement('shipToHouseNo', ''));
							$node->appendChild($xml->createElement('shipToAnnex', ''));
							if(strlen($order->get_shipping_postcode()) == 0) {
								$failed->addError(" shipToPostalCode length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_shipping_postcode()) > 10) {
									$failed->addError(" shipping_postcode length is greater than 10 characters");
									$isvalidate = false;
								}
							}
							if ($isvalidate == false) error_log("check On");
							$node->appendChild($xml->createElement('shipToPostalCode', $order->get_shipping_postcode()));
							if(strlen($order->get_shipping_city()) == 0) {
								$failed->addError(" shipToCity length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_shipping_city()) > 30) {
									$failed->addError(" shipping_city length is greater than 30 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('shipToCity', $order->get_shipping_city()));
							if(strlen($order->get_shipping_country()) == 0) {
								$failed->addError(" shipToCountryCode length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_shipping_country()) > 2) {
									$failed->addError(" shipToCountryCode length is greater than 2 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('shipToCountryCode', $order->get_shipping_country()));
							if(strlen($order->get_shipping_country()) == 0) {
								$failed->addError(" shipToCountry length is null");
								$isvalidate = false;
							}
			

								if($order->get_shipping_country()) $node->appendChild($xml->createElement('shipToCountry', WC()->countries->countries[$order->get_shipping_country()]));
								else $node->appendChild($xml->createElement('shipToCountry', ''));
							if(strlen($order->get_billing_phone()) == 0) {
								$failed->addError(" shipToPhone length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_billing_phone()) > 15) {
									$failed->addError(" shipping_phone length is greater than 15 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('shipToPhone', $order->get_billing_phone()));
							if(strlen($order->get_shipping_address_1()) == 0) {
								$failed->addError(" shipping_address_1 length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_shipping_address_1() . $order->get_shipping_address_2()) > 100) {
									$failed->addError(" shipToStreetHouseNrExt length is greater than 100 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('shipToStreetHouseNrExt', $order->get_shipping_address_1() . " " . $order->get_shipping_address_2()));
							$node->appendChild($xml->createElement('shipToArea', ''));
							$woo_countries = new WC_Countries();
							$states = $woo_countries->get_states($order->get_shipping_country());
							$region = $order->get_shipping_city();
							$node->appendChild($xml->createElement('shipToRegion', ''));
							$node->appendChild($xml->createElement('shipToRemark', ''));
							if(strlen($order->get_billing_email()) == 0) {
								$failed->addError(" shipToEmail length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_billing_email()) > 50) {
									$failed->addError(" billing_email length is greater than 50 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('shipToEmail', $order->get_billing_email()));
							$node->appendChild($xml->createElement('invoiceToTitle', ''));
							if(strlen($order->get_billing_last_name()) == 0) {
								$failed->addError(" invoiceToFirstName length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_billing_last_name()) > 35) {
									$failed->addError(" billing_last_name length is greater than 35 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('invoiceToFirstName', $order->get_billing_first_name()));
							$node->appendChild($xml->createElement('invoiceToLastName', $order->get_billing_last_name()));
							$node->appendChild($xml->createElement('invoiceToCompanyName', $order->get_billing_company()));
							$node->appendChild($xml->createElement('invoiceToDepartment', ''));
							$node->appendChild($xml->createElement('invoiceToFloor', ''));
							$node->appendChild($xml->createElement('invoiceToDoorcode', ''));
							$node->appendChild($xml->createElement('invoiceToStreet', ''));
							$node->appendChild($xml->createElement('invoiceToHouseNo', ''));
							$node->appendChild($xml->createElement('invoiceToAnnex', ''));
							if(strlen($order->get_billing_postcode()) == 0) {
								$failed->addError(" invoiceToPostalCode length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_billing_postcode()) > 10) {
									$failed->addError(" billing_postcode length is greater than 10 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('invoiceToPostalCode', $order->get_billing_postcode()));
							if(strlen($order->get_billing_city()) == 0) {
								$failed->addError(" invoiceToCity length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_billing_city()) > 30) {
									$failed->addError(" shipping_city length is greater than 30 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('invoiceToCity', $order->get_billing_city()));
							if(strlen($order->get_billing_country()) == 0) {
								$failed->addError(" invoiceToCountryCode length is null");
								$isvalidate = false;
							} else {
								if(strlen($order->get_billing_country()) > 2) {
									$failed->addError(" billing_country Code length is greater than 2 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('invoiceToCountryCode', $order->get_billing_country()));
							$node->appendChild($xml->createElement('invoiceToCountry', WC()->countries->countries[$order->get_billing_country()]));
							$node->appendChild($xml->createElement('invoiceToPhone', $order->get_billing_phone()));
							if(strlen($order->get_billing_address_1()) == 0) {
								$failed->addError(" billing_address_1 length is null");
								$isvalidate = false;
							} else {
								if (strlen($order->get_billing_address_1() . $order->get_billing_address_2()) > 100) {
									$failed->addError(" BillingToStreetHouseNrExt length is greater than 100 characters");
									$isvalidate = false;
								}
							}
							
							$node->appendChild($xml->createElement('invoiceToStreetHouseNrExt', $order->get_billing_address_1() . " " . $order->get_billing_address_2()));
							$node->appendChild($xml->createElement('invoiceToArea', ''));
							$woo_countries = new WC_Countries();
							$states = $woo_countries->get_states($order->get_shipping_country());
							$region =  $order->get_shipping_city();
							$node->appendChild($xml->createElement('invoiceToRegion', ''));
					
							
							$node->appendChild($xml->createElement('invoiceToRemark', $order->get_billing_email()));
							
							if(strlen($order->get_billing_email()) == 0) {
								$failed->addError(" invoiceToEmail length is null");
								$isvalidate = false;
							}
							
							$node->appendChild($xml->createElement('invoiceToEmail', $order->get_billing_email()));
							$node->appendChild($xml->createElement('language', $newstring = substr(get_locale(), -2)));
							$node->appendChild($xml->createElement('remboursAmount', ''));
							$order_shipping_method_id = '';
							$shipping_items = $order->get_items('shipping');
							
							foreach($shipping_items as $el) {
								$order_shipping_method_id = $el['method_id'];
							}
							/*if(strlen($order_shipping_method_id) == 0) {
								$failed->addError(" order_shipping_method_id length is null");
								error_log("shipping");
								$isvalidate = false;
							}*/
							if(strlen($order_shipping_method_id) == 0) {
								 $order_shipping_method_id = "PNLP";
								 $node->appendChild($xml->createElement('shippingAgentCode', $order_shipping_method_id));
							} else {
							
							$split = explode(":", $order_shipping_method_id);
							$order_shipping_method_id = $split[0];
							if(empty($order_shipping_method_id)) $order_shipping_method_id = "PNLP";
							 $node->appendChild($xml->createElement('shippingAgentCode', $order_shipping_method_id));
							}
							
							$node->appendChild($xml->createElement('shipmentType', ''));
							$node->appendChild($xml->createElement('shipmentProductOption', ''));
							$node->appendChild($xml->createElement('shipmentOption', ''));
							$node->appendChild($xml->createElement('receiverDateOfBirth', ''));
							$node->appendChild($xml->createElement('IDExpiration', ''));
							$node->appendChild($xml->createElement('IDNumber', ''));
							$node->appendChild($xml->createElement('IDType', ''));
							$node->appendChild($xml->createElement('requestedDeliveryDate', ''));
							$node->appendChild($xml->createElement('requestedDeliveryTime', ''));
							$comment = $xml->createElement('comment');
							$comment->appendChild($xml->createCDATASection($order->get_customer_note()));
							$node->appendChild($comment);
							$node2 = $xml->createElement('deliveryOrderLines');
							$items = $order->get_items('line_item');
							
							foreach($items as $item) {
								if(strlen($item['product_id']) == 0) {
									$failed->addError(" product_id length is null");
									$isvalidate = false;
								} else {
								if(strlen($item['product_id']) > 24) {
										$failed->addError(" itemNo length is greater than 24 characters");
										$isvalidate = false;
									} 
									
									
								}
								if(strlen($item['qty']) == 0) {
									$failed->addError(" quantity length is null");
									$isvalidate = false;
								} else {
									if(strlen($item['qty']) > 5) {
										$failed->addError(" quantity length is greater than 5 characters");
										$isvalidate = false;
									}
								}
								if(strlen($item['name']) > 255) {
									$failed->addError(" Product name length is greater than 255 characters");
									$isvalidate = false;
								}
								$product = new WC_Product((int) $item['product_id']);
								if(strlen($product->get_sku()) == 0) {
									$failed->addError(" Product SKU  is null");
									$isvalidate = false;
								}
								$line = $xml->createElement('deliveryOrderLine');
								$line->appendChild($xml->createElement('itemNo', $product->get_sku()));
								$line->appendChild($xml->createElement('itemDescription', $item['name']));
								$line->appendChild($xml->createElement('quantity', $item['qty']));
								$line->appendChild($xml->createElement('singlePriceInclTax', $item['line_subtotal']));
								$node2->appendChild($line);
							}

							$node->appendChild($node2);
							if($isvalidate == true) {
								add_post_meta($order_id, 'ecsExport', 'yes');
								$orders->appendChild($node);
								$isEmpty = $isEmpty + 1;
							} else {
								
								foreach($FailedOrders as $fails) {
								foreach($fails->get_errors() as $fail) {
											error_log($fail);}}
								array_push($FailedOrders, $failed);
							}
						}
					}

					$result = count($order_split);
					if($isEmpty > 0) {
						$t = time();
						$filename = 'ORD' . date("YmdHis", $t) . '.xml';
						$Errors = '
							<!DOCTYPE html>
							<html>
								<body><p>';
									$Errors .= 'An error occurred processing  Order export file';
									$Errors .= '<br><b>Message:</b><br>';
									foreach($FailedOrders as $fails) {
										$Errors .= 'Order ID :' . $fails->get_orderID();
										$Errors .= '<br>';
										foreach($fails->get_errors() as $fail) {
											error_log($fail);
											$Errors .= $fail;
											$Errors .= '<br>';
										}
									}
								'</p></body>
							</html>';
							
						global $wpdb;
						$name = '';
						$email = '';
						// find list of states in DB
						$table_name_ecs = $wpdb->prefix . 'ecs';
						$qry = "SELECT * FROM ".$table_name_ecs." WHERE keytext ='general' ORDER BY id DESC  LIMIT 1";
						$states = $wpdb->get_results($qry);
						$settingID = '';
						
						foreach($states as $k) {
							$settingID = $k->id;
						}
						
						$table_name = $wpdb->prefix . 'ecsmeta';
						// find list of states in DB
						$qrymeta = "SELECT * FROM ".$table_name." WHERE settingid = '".$settingID."'";
						$statesmeta = $wpdb->get_results($qrymeta);
						
						foreach($statesmeta as $k) {
							if($k->keytext == "Name") {
								$name = $k->value;
							}
							if($k->keytext == "Email") {
								$email = $k->value;
							}
						}
						
						$to = $email;
						$subject = 'PostNL ECS plugin processing error';
						$body = $Errors;
						$headers = array(
							'Content-Type: text/html; charset=UTF-8'
						);
						if (count($FailedOrders) > 0) {
							wp_mail( $to, $subject, $body, $headers );
						}
			
						$xml->appendChild($message);
						$xml->save(ECS_DATA_PATH."/order.xml");
						//$t = time();
						$filename = 'ORD' . date("YmdHis", $t) . '.xml';
						$local_directory =ECS_DATA_PATH.'/order.xml';
						$remote_directory = 'woocommerce_test/Order/';
						$remote_directory = $Path . '/';
						// $remote_directory = '/';
						$success = $sftp->put($remote_directory . $filename, $local_directory, NET_SFTP_LOCAL_FILE);
						global $wpdb;
						$table_name_ecs = $wpdb->prefix . 'ecs';
						$querylast = "SELECT * FROM ".$table_name_ecs." WHERE keytext = 'lastOrdername'";
						$statesmeta = $wpdb->get_results($querylast);
						$lastname = '';
						if(count($statesmeta) > 0) {
							foreach($statesmeta as $k) {
								$wpdb->query($wpdb->prepare("UPDATE ".$table_name_ecs." SET type = '".$filename."' WHERE id= %d", $k->id));
							}
						} else {
							global $wpdb;
							$table_name_ecs = $wpdb->prefix . 'ecs';
							$wpdb->insert($table_name_ecs, array(
								'type' => $filename,
								'enable' => 'true',
								'keytext' => 'lastOrdername'
							));
						}
					}
				}
				
				if(count($FailedOrders) > 0) {
					$t = time();
					$filename = 'ORD' . date("YmdHis", $t) . '.xml';
					$Errors = '
						<!DOCTYPE html>
						<html>
							<body><p>';
								$Errors .= 'An error occurred processing  Order export file';
								$Errors .= '<br><b>Message:</b><br>';
								foreach($FailedOrders as $fails) {
									$Errors .= 'Order ID :' . $fails->get_orderID();
									$Errors .= '<br>';
									foreach($fails->get_errors() as $fail) {
										$Errors .= $fail;
										$Errors .= '<br>';
									}
								}
							'</p></body>
						</html>';
						
					global $wpdb;
					$name = '';
					$email = '';
					// find list of states in DB
					$table_name_ecs = $wpdb->prefix . 'ecs';
					$qry = "SELECT * FROM ".$table_name_ecs." WHERE keytext ='general' ORDER BY id DESC  LIMIT 1";
					$states = $wpdb->get_results($qry);
					$settingID = '';
					foreach($states as $k) {
						$settingID = $k->id;
					}
					$table_name = $wpdb->prefix . 'ecsmeta';
					// find list of states in DB
					$qrymeta = "SELECT * FROM $table_name " . "WHERE settingid = $settingID  ";
					$statesmeta = $wpdb->get_results($qrymeta);
					
					foreach($statesmeta as $k) {
						if($k->keytext == "Name") {
							$name = $k->value;
						}
						if($k->keytext == "Email") {
							$email = $k->value;
						}
					}
					
					$to = $email;
					$subject = 'PostNL ECS plugin processing error';
					$body = $Errors;
					$headers = array(
						'Content-Type: text/html; charset=UTF-8'
					);
					if(count($FailedOrders) > 0) {
						wp_mail($to, $subject, $body, $headers);
					}
				} 	
			}
		
		}
		
		function shipmentImport() {
				require_once(dirname(__DIR__) . "/ecsSftpProcess.php");
				require_once(dirname(__DIR__) . "/import/ecsShipmentSettings.php");
				$EcsSftpSettings = ecsSftpProcess::init();
				$EcsShipmentSettings = ecsShipmentSettings::init();
				$Path = '';
				$settingID = $EcsShipmentSettings->getSettingId();
				if($settingID) {
					$statesmeta = $EcsShipmentSettings->loadShipmentSettings($settingID); }
				else { 
				error_log('Shipment Settings not found'); 
				return;
				}
				
				foreach ($statesmeta as $k) {
				
				if ($k->keytext == "Path") {
					$Path = $k->value;
				}
				
				
			}
			$ftpCheck = $EcsSftpSettings->checkSftpSettings($Path);
			
			if($ftpCheck[0] != 'SUCCESS') {
			
			error_log('ERROR: POSTNL ECS Product Export: '. $ftpCheck[1]); 
			
			}  else {
				
				$sftp = $ftpCheck[1];
				global $wpdb;
				$Cron = '';
				$Path = '';
				$Inform = '';
				$tracking = '';
				$enable = '';
				$lastfile = '';
				$table_name_ecs = $wpdb->prefix . 'ecs';
							
				foreach($statesmeta as $k) {
					if($k->keytext == "Cron") {
						$Cron = $k->value;
					}
					if($k->keytext == "Path") {
						$Path = $k->value;

					}
					if($k->keytext == "tracking") {
						$tracking = $k->value;
						
					}
					if($k->keytext == "Inform") {
						$Inform = $k->value;
					}
				}
				
				global $wpdb;
				$nameRetailer = '';
				$email = '';
				$table_name_ecs = $wpdb->prefix . 'ecs';
				// find list of states in DB
				$qry = "SELECT * FROM ".$table_name_ecs." WHERE keytext ='general' ORDER BY id DESC  LIMIT 1";
				$states = $wpdb->get_results($qry);
				$settingID = '';
				foreach($states as $k) {
					$settingID = $k->id;
				}
				// find list of states in DB
				$table_name = $wpdb->prefix . 'ecsmeta';
				$qrymeta = "SELECT * FROM ".$table_name." WHERE settingid = '".$settingID."'";
				$statesmeta = $wpdb->get_results($qrymeta);

				foreach($statesmeta as $k) {
					if ($k->keytext == "Name") {
						$nameRetailer = $k->value;
												
					}
					if ($k->keytext == "Email") {
						$email = $k->value;
						
					}
				}
				
				$remote_directory = $Path . '/';
				$StartPath = $sftp->pwd();
				$sftp->chdir($Path); // open directory 'test'
				$endPath = $sftp->pwd();
				
				foreach($sftp->nlist() as $filename) {
					$codesNames = explode(".xml", $filename);
		
					if(count($codesNames) > 0) {
						if($filename == '.' || $filename == '..') {
							
						} else {
						
							$sftp->get($sftp->pwd() . '/' . $filename,ECS_DATA_PATH."/".$filename);							
							if(file_exists(ECS_DATA_PATH."/".$filename) && filesize(ECS_DATA_PATH."/".$filename) > 0) {
								$xml = simplexml_load_file(ECS_DATA_PATH."/".$filename, 'SimpleXMLElement', LIBXML_NOWARNING);
								 
								 $validate = true; 
								$inventory_errors = array();
								$xmlRetailname = (string) $xml->retailerName;
								
								if(strcmp(trim($xmlRetailname), trim($nameRetailer)) == 0) {
									
									$validate = true;

								} else {
										 						 
									$validate = false; 
									
									array_push($inventory_errors, 'The retailer name from the shipment message and the system configuration do not match');
								}
								
								
								$shippedOrders_ids = "";
								
								foreach ($xml->orderStatus as $stock) {
									$orderid  = $stock->orderNo;
									$intOrder = (int) $orderid;
									if(false === get_post_status((int) $stock->orderNo)) {
										$validate = false; 
										array_push($inventory_errors, 'Order  ID :' . $stock->orderNo . '  is not found for the shipment');
									}
									$countElement = 0;
								
									foreach($stock->orderStatusLines as $pruduct2) {
										foreach($pruduct2 as $pruduct) {
											$countElement = $countElement + 1;
										}
									}
									foreach($stock->orderStatusLines as $pruduct1) {
										foreach($pruduct1 as $pruduct) {
											$shippedOrders_ids .= $pruduct->itemNo . ":";
											$order = new WC_Order((int) $orderid);
											$items = $order->get_items('line_item');
											$productExist = "0";
											foreach($items as $item) {
												if(strlen($item['product_id']) > 0) {
													
												}
												
												$product = new WC_Product((int) $item['product_id']);
												if($product->get_sku() == $pruduct->itemNo) {
													$productExist = "1";
								
												}
											}
											if($productExist == "0") {
												$validate = false; 
												array_push($inventory_errors, 'Product  ID :' . $pruduct->itemNo . '   is not found for the shipment');
											
											}
										}
									}
								}
									
								if($validate == true) {
									
									$ship_Orders = array();
									
									
									foreach($xml->orderStatus as $stock) {
										$orderid = $stock->orderNo;
										$traclCode = $stock->trackAndTraceCode;
										$intOrder = (int) $orderid;
										$stringTrack = (string) $traclCode;
							
										///check if everything is shipped
										$order = new WC_Order((int) $orderid);
										$items = $order->get_items('line_item');
										$countElement = 0;
										
										foreach($stock->orderStatusLines as $pruduct2) {
											foreach($pruduct2 as $pruduct) {
												$countElement = $countElement + 1;
											}
										}
										if($countElement == count($items)) {
											if($Inform == '1') {
												$order = wc_get_order((int) $orderid);
												$order->update_status('completed');
											}
										} else {
											$exportedItems =get_post_meta($intOrder, 'exportedItems', true);
											if(strlen($exportedItems) !== 0) {
												$itemsExported = explode(":", $exportedItems);
												$itemsExportedNewly = explode(":", $shippedOrders_ids);
												$totalItems = count($itemsExported) + count($itemsExportedNewly) -2;
												if($totalItems == count($items)) {
													if($Inform == '1') {
														$order = wc_get_order($intOrder);
														$order->update_status('completed');
													}
												} else {
													$newExported = $exportedItems . " " . $shippedOrders_ids;
													update_post_meta($intOrder, 'exportedItems', $newExported);
												}
											} else {
												add_post_meta($intOrder, 'exportedItems', $shippedOrders_ids, yes);
											}
										}
										if(!add_post_meta($intOrder, 'trackAndTraceCode', $stringTrack, yes)) {
											update_post_meta($intOrder, 'trackAndTraceCode', $stringTrack, yes);
										}
										array_push($ship_Orders, 'Order  ID :' . $stock->orderNo . '  was successfully imported ');
									}
									$sftp->delete($sftp->pwd() . '/' . $filename);
								} else {
									$Errors = '
										<!DOCTYPE html>
										<html>
											<body><p>';
												$Errors .= 'An error occurred processing file:' . $filename . '<br>';
												$Errors .= '<b>Message:</b><br>';
												foreach($inventory_errors as $fails) {
													error_log($fails);
													$Errors .= $fails;
													$Errors .= ' <br>';
												}
											'</p></body>
										</html>';
										
									global $wpdb;
									$name = '';
									$email = '';
									// find list of states in DB
									$table_name_ecs = $wpdb->prefix . 'ecs';
									$qry = "SELECT * FROM ".$table_name_ecs." WHERE keytext ='general' ORDER BY id DESC  LIMIT 1";
									$states = $wpdb->get_results($qry);
									$settingID = '';
									
									foreach($states as $k) {
										$settingID = $k->id;
									}
									
									$table_name = $wpdb->prefix . 'ecsmeta';
									// find list of states in DB
									$qrymeta    = "SELECT * FROM ".$table_name." WHERE settingid = '".$settingID."'";
									$statesmeta = $wpdb->get_results($qrymeta);
									
									foreach($statesmeta as $k) {
										if($k->keytext == "Name") {
											$name = $k->value;
										}
										if($k->keytext == "Email") {
											$email = $k->value;
										}
									}
									
									$to = $email;
									$subject = 'PostNL ECS plugin processing error';
									$body = $Errors;
									$headers = array(
										'Content-Type: text/html; charset=UTF-8'
									);
									wp_mail($to, $subject, $body, $headers);
								}
							}
						}
					}
				}
			} 
		}
		
		function inventoryImport() {
				require_once(dirname(__DIR__) . "/ecsSftpProcess.php");
				require_once(dirname(__DIR__) . "/import/ecsInventorySettings.php");
				$EcsSftpSettings = ecsSftpProcess::init();
				$EcsInventorySettings = ecsInventorySettings::init();
				$Path = '';
				$settingID = $EcsInventorySettings->getSettingId();
				if($settingID) { 	
					$statesmeta = $EcsInventorySettings->loadInventorySettings($settingID);
		
					}

			else { 
				error_log('Stock Settings not found'); 
				return;
				}
				
				foreach ($statesmeta as $k) {
				
				if ($k->keytext == "Path") {
					$Path = $k->value;
				}
				
				
			}
			$ftpCheck = $EcsSftpSettings->checkSftpSettings($Path);
			
			if($ftpCheck[0] != 'SUCCESS') {
			
			error_log('ERROR: POSTNL ECS Product Export: '. $ftpCheck[1]); 
			
			} else {
				global $wpdb;
				$sftp =  $ftpCheck[1];
				$Cron = '';
				$Path = '';
				$informcustomer = '';
				$cron = '';
				$enable = '';
				$lastfile = '';
				// find list of states in DB
				$table_name_ecs = $wpdb->prefix . 'ecs';
				
				foreach($statesmeta as $k) {
					if($k->keytext == "Cron") {
						$Cron = $k->value;
					}
					if($k->keytext == "Path") {
						$Path = $k->value;
						
					}
				}
				
				global $wpdb;
				$nameRetailer = '';
				$email = '';
				$table_name_ecs = $wpdb->prefix . 'ecs';
				// find list of states in DB
				$qry = "SELECT * FROM ".$table_name_ecs." WHERE keytext ='inventoryImport' ORDER BY id DESC  LIMIT 1";
				$states = $wpdb->get_results($qry);
				$settingID = '';
				foreach($states as $k) {
					$settingID = $k->id;
				}
				// find list of states in DB
				$table_name = $wpdb->prefix . 'ecsmeta';
				$qrymeta = "SELECT * FROM ".$table_name." WHERE settingid = '".$settingID."'";
				$statesmeta = $wpdb->get_results($qrymeta);

				foreach($statesmeta as $k) {
					if ($k->keytext == "Name") {
						$nameRetailer = $k->value;
												
					}
					if ($k->keytext == "Email") {
						$email = $k->value;
						
					}
				}
				
				
				
				
				
				$remote_directory = $Path . '/';
				$StartPath = $sftp->pwd();
				$sftp->chdir($Path); // open directory 'test'
				$endPath = $sftp->pwd();
				// $sftp->chdir('woocommerce_test/Stockcount');
				
		
				
				foreach($sftp->nlist() as $filename) {
					
					$codesNames = explode(".xml", $filename);
					if(count($codesNames) >0) {
						if($filename == '.' || $filename == '..') {
							
						} 
						
						else { 
							
							$sftp->get($sftp->pwd() . '/' . $filename, ECS_DATA_PATH."/".$filename);
							if (file_exists(ECS_DATA_PATH."/".$filename) && filesize(ECS_DATA_PATH."/".$filename) > 0) {
								$xml = simplexml_load_file(ECS_DATA_PATH."/".$filename, 'SimpleXMLElement', LIBXML_NOWARNING);
								$valid = true;
								$inventory_errors = array();
								
						
								foreach($xml as $stock) {
									$prodduct_id = (string) $stock->stockdtl_itemnum;
									
									$Products = get_posts(array(
										'post_type' => 'product',
										'posts_per_page' => 100,
										'meta_query' => array(
											array(
												'key' => '_sku',
												'value' => (string) $stock->stockdtl_itemnum,
												'compare' => '='
											)
										)
									)); 
									if (count($Products) == 0) {
										
										$valid = false; 
										array_push($inventory_errors, "Product  SKU :" . $stock->stockdtl_itemnum . " is not found");
									} else {
										
									}
								} 
								
							
								
								if($valid == true) {
									
									
									foreach($xml as $stock) {
										if ( $stock->stockdtl_itemnum != '') {
												$Products = get_posts(array(
													'post_type' => 'product',
													'posts_per_page' => 100,
													'meta_query' => array(
														array(
															'key' => '_sku',
															'value' => (string) $stock->stockdtl_itemnum,
															'compare' => '='
														)
													)
												)); 
											
											foreach($Products as $product) {
												$product_id = $product->ID;
												
												update_post_meta((int) $product_id, '_stock', (int) $stock->stockdtl_fysstock);
											}
										}
									}
									$sftp->delete($sftp->pwd() . '/' . $filename);
								} else {
									
									if(count($inventory_errors) > 0) {
										$Errors = '
											<!DOCTYPE html>
											<html>
												<body><p>';
													$Errors .= 'An error occurred processing file:' . $filename;
													$Errors .= '<br><b>Message:</b><br>';
													foreach($inventory_errors as $fails) {
														error_log($fails);
														$Errors .= $fails;
														$Errors .= '<br>';
													}
												'</p></body>
											</html>';
											
										global $wpdb;
										$name = '';
										$email = '';
										// find list of states in DB
										$table_name_ecs = $wpdb->prefix . 'ecs';
										$qry = "SELECT * FROM ".$table_name_ecs." WHERE keytext ='inventoryImport' ORDER BY id DESC LIMIT 1";
										$states = $wpdb->get_results($qry);
										$settingID = '';
										foreach($states as $k) {
											$settingID = $k->id;
										}
										$table_name = $wpdb->prefix . 'ecsmeta';
										// find list of states in DB
										$qrymeta = "SELECT * FROM $table_name " . "WHERE settingid = $settingID  ";
										$statesmeta = $wpdb->get_results($qrymeta);
										foreach($statesmeta as $k) {
											if($k->keytext == "Name") {
												$name = $k->value;
											}
											if($k->keytext == "Email") {
												$email = $k->value;
											}
										}
										$to = $email;
										$subject = 'PostNL ECS plugin processing error';
										$body = $Errors;
										$headers = array(
											'Content-Type: text/html; charset=UTF-8'
										);
										wp_mail($to, $subject, $body, $headers);
									}
								}
							}
						}
					}
				}
			} 
		}
	}
?>