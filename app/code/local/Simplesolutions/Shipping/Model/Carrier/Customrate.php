<?php
 
class Simplesolutions_Shipping_Model_Carrier_Customrate
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    public $_code = 'simplesolutions_customrate';
	
	public $minimum_freight_cost = 130;
	public $residential_cost = 99;
	public $liftgate_cost = 49;
	public $appointment_cost = 20;
	
	public function getSimpleFreightCost($freightPriceTotal) {
		if($freightPriceTotal <= 500) {
			$shippingPriceFreight =  $freightPriceTotal * .42;
			//Mage::log("Freight Shipping Portion : ".$freightPriceTotal." * .42 = ".$shippingPriceFreight);
		} else if ($freightPriceTotal <= 1000){
			$shippingPriceFreight =  $freightPriceTotal * .19;
			//Mage::log("Freight Shipping Portion : ".$freightPriceTotal." * .19 = ".$shippingPriceFreight);
		} else if ($freightPriceTotal <= 1500) {
			$shippingPriceFreight =  $freightPriceTotal * .13;
			//Mage::log("Freight Shipping Portion : ".$freightPriceTotal." * .13 = ".$shippingPriceFreight);
		} else if($freightPriceTotal <= 2000){
			$shippingPriceFreight =  $freightPriceTotal * .12;
			//Mage::log("Freight Shipping Portion : ".$freightPriceTotal." * .12 = ".$shippingPriceFreight);
		} else if($freightPriceTotal <= 4000){
			$shippingPriceFreight =  $freightPriceTotal * .10;
			//Mage::log("Freight Shipping Portion : ".$freightPriceTotal." * .10 = ".$shippingPriceFreight,null,'freight.log');
		} else {
			$shippingPriceFreight =  $freightPriceTotal * .07;
			//Mage::log("Freight Shipping Portion : ".$freightPriceTotal." * .07 = ".$shippingPriceFreight);
		} 
		if($shippingPriceFreight < $this->minimum_freight_cost) {
			$shippingPriceFreight = $this->minimum_freight_cost;
			//Mage::log('Shipping Price Lower than ' . $this->minimum_freight_cost . ', defaulting to ' . $this->minimum_freight_cost);
		}
		// Added by Levi (CSH) 4/24/13
		// Hardcode the shipping coupon - normal coupon did not work with shipments from multiple warehouses
		// EDIT: I guess we didn't want this discount, after all...
		//~ if($freightPriceTotal >= 2500) {
			//~ $shippingPriceFreight = $shippingPriceFreight - 250;
			//~ if ($shippingPriceFreight < 0) {
				//~ $shippingPriceFreight = 0;
			//~ }
		//~ }
		// End addition
		return $shippingPriceFreight;
	}
	
	public function getShippingEstimate($products,$countryId,$postcode ) {
		$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore('default')->getId());
		
		foreach($products as $product) {
			$_product = Mage::getModel('catalog/product')->load($product['id']);

			$_product->getStockItem()->setUseConfigManageStock(false);
			$_product->getStockItem()->setManageStock(false);

			$quote->addProduct($_product, $product['qty']);
			//Mage::log('Standard Item Added: '.$product['id'].' With a qty of: '.$product['qty']);
		}
		$quote->getShippingAddress()->setCountryId($countryId)->setPostcode($postcode); 
		$quote->getShippingAddress()->collectTotals();
		$quote->getShippingAddress()->setCollectShippingRates(true);
		$quote->getShippingAddress()->collectShippingRates();

		$_rates = $quote->getShippingAddress()->getShippingRatesCollection();

		$shippingRates = '';
		foreach ($_rates as $_rate):
			$code = $_rate->getCode();
			if($code == 'tablerate_bestway') {
				$shippingRates = (float)$_rate->getPrice();
			}
		endforeach;

		return $shippingRates;
	
	}
	
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
		//foreach(debug_backtrace() as $key=>$info)
		//   {
			//Mage::Log("#" . $key . 
			//" Called " . 
			//$info['function'] .
			//" in " .
			//$info['file'] .
			//" on line " .
			//$info['line']);         
		//   }
	
        if (!$this->getConfigFlag('active')) {
            return false;
        }
 
		$result = Mage::getModel('shipping/rate_result');
		
		$log['minimum_freight_cost'] = $this->minimum_freight_cost;
		$log['residential_cost'] = $this->residential_cost;
		$log['liftgate_cost'] = $this->liftgate_cost;
		$log['appointment_cost'] = $this->appointment_cost;
		
		$request2 = Mage::app()->getRequest();
		$module = $request2->getModuleName();
		$controller = $request2->getControllerName();
		$action = $request2->getActionName();
		$log['module'] = $module;
		$log['controller'] = $controller;
		$log['action'] = $action;
		
		//lets get some variables rolling 
		$log; //lets trace whats going on
		$standardItems  =  array(); //used to hold standard shipping items
		$freightItems =  array();	//used to hold freight shipping items
		$freightOnly = false; // determines if the entire shipment will occur via freight
		$standardOnly = false; // determines if the entire shipment will occur via standard shipping
		$mixedShipment = false;  // determines if the shipment is comprised of standard and freight items
		$errorCheck;  //holds custom error codes
		$x = 0; //counting var
		$vendors = array(); //holds all the vendors to determine if we can ship via freight
		$warehouses = array(); //holds all the warehouses to determine if we can ship via freight
		$residential = false;
		$optionalLiftGate = false;
		$liftGate = false;
		$shippingPrice = 0;
		$modPrice = 0;
		$shippingPriceArray= array();
		
		//get the subtotal
		$totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();  //This returns an array of totals		
		
		$cshresidential = Mage::getSingleton('checkout/session')->getQuote()->getCshResidential();
		$cshliftgate = Mage::getSingleton('checkout/session')->getQuote()->getCshLiftgate();
		$cshappointment = Mage::getSingleton('checkout/session')->getQuote()->getCshAppointment();
		
		$subtotal = null;
		if(isset($totals['subtotal'])){
			  $subtotal = $totals['subtotal']->getValue();
		}
		// subtotal end
		
		$x = 0;
		$y = 0;
		// check for freight items and gather vendors
		if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $product_id = $child->getProductId();
                            $productObj = Mage::getModel('catalog/product')->load($product_id);
                            $isFreight = $productObj->getData('is_freight'); //our shipping attribute code
							$log['freight_variable'][] = $isFreight;
							array_push($vendors, $productObj->getData('vendor'));
                            if($isFreight == '1189') {
								$freightItems[$x]['id'] = $product_id;
								$freightItems[$x]['qty'] = $child->getQty();
								$freightItems[$x]['vendor'] = $productObj->getData('vendor');
								$freightItems[$x]['warehouse'] = $productObj->getData('warehouse');
								$freightItems[$x]['price'] = $productObj->getData('price');
								$warehouses[$productObj->getData('warehouse')][] = $freightItems[$x];
								$x++;
							} else {
								$standardItems[$y]['id'] = $product_id;
								$standardItems[$y]['qty'] = $child->getQty();
								$standardItems[$y]['vendor'] = $child->getData('vendor');
								$standardItems[$y]['warehouse'] = $child->getData('warehouse');
								$standardItems[$y]['price'] = $child->getData('price');
								$y++;
							}
                        }
                    }
                } else {
                    $product_id = $item->getProductId();
                    $productObj = Mage::getModel('catalog/product')->load($product_id);
                    $isFreight = $productObj->getData('is_freight'); //our shipping attribute code
					$log['freight_variable'][] = $isFreight;
					array_push($vendors, $productObj->getData('vendor'));
                    if($isFreight == '1189') {
						$freightItems[$x]['id'] = $product_id;
						$freightItems[$x]['qty'] = $item->getQty();
						$freightItems[$x]['vendor'] = $productObj->getData('vendor');
						$freightItems[$x]['warehouse'] = $productObj->getData('warehouse');
						$freightItems[$x]['price'] = $productObj->getData('price');
						$warehouses[$productObj->getData('warehouse')][] = $freightItems[$x];
						$x++;
					} else {
						$standardItems[$y]['id'] = $product_id;
						$standardItems[$y]['qty'] = $item->getQty();
						$standardItems[$y]['vendor'] = $productObj->getData('vendor');
						$standardItems[$y]['price'] = $productObj->getData('price');
						$standardItems[$y]['warehouse'] = $productObj->getData('warehouse');
						$y++;
					}
                }
            }
        }
		// end check for freight items
		$uniqueWarehouses = $warehouses;
		$uniqueWarehouseCount = count($uniqueWarehouses);
		$log['unique_warhouses_count'] = $uniqueWarehouseCount;
		$log['unique_warhouses'] = print_r($uniqueWarehouseCount, true);
		
		//Mage::log('Standard Items: '.print_r($standardItems, true));
		//Mage::log('Freight Items: '.print_r($freightItems, true));
		
		// is this a mixed shipment of freight and standard shipping?
		if (!empty($freightItems) && !empty($standardItems)) {
			// can we ship all items via freight?
			$i = 0;
			$sameWarehouse = false;
			if($uniqueWarehouseCount > 1) {
				$sameWarehouse = true;
				$log['shipment'] = 'Mixed shipment - Different Warehouses';
			}
		} else if (!empty($freightItems) && empty($standardItems)) {
			$freightOnly = true;
			$log['shipment'] = 'Freight Only';
		} else if (empty($freightItems) && !empty($standardItems)){
			$standardOnly = true;
			$log['shipment'] = 'Standard Only';
		} else {
			$log['shipment'] = 'Shipping Cannot be determined';
		}

		//get the shipping cost of all non-freight items
		$nonFreightShipping;
		
		$warehouseRates = array();
		$shippingPriceStandard = 0;
		
		foreach ($uniqueWarehouses as $warehouse => $items) {
			$warehouseRates[$warehouse] = 0;
			foreach ($items as $item) {
				$log[$warehouse]['items'][] = print_r($item, true);
				$warehouseRates[$warehouse] += $item['price'] * $item['qty'];					
			}
		}
		
		foreach ($warehouseRates as $warehouseRate) {
			$shippingPrice += $this->getSimpleFreightCost($warehouseRate);
			$log['warehouseRates'][] = $this->getSimpleFreightCost($warehouseRate);
		}
		
		$log['freight_portion'] = $shippingPrice;
		if($freightOnly == false && $standardOnly == false) {			
			//next grab the standard shipping portion of the shipment
			$shippingPriceStandard = $this->getShippingEstimate($standardItems,$request->country_id,$request->dest_postcode);
			$log['standard_portion'] = $shippingPriceStandard;			
		}
		
		$shippingPrice += $shippingPriceStandard; // add standard price to freight shipping price after minimum applied
		
		$countryCode = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getCountryId();
		if($countryCode == "CA") {
			$shippingPrice = $shippingPrice + $shippingPrice * .13;
		}
		
		// Addition by Levi Schooley 5/7/2013
		// Hardcoded shipping discount logic, based on specific warehouse and specific coupon code
		$couponCode = Mage::helper('checkout/cart')->getQuote()->getData('coupon_code');
		if ($couponCode == 'USA2013') {
			if (array_key_exists('CONE01', $warehouseRates) && $warehouseRates['CONE01'] >= 3530) {
				$shippingPrice = $shippingPrice - 250;
			}
		}
		// End addition
		
		$shippingPriceArray['Commercial'] = $shippingPrice;
		
		//++++++++++++++++++++++++++++++++++
		//Addition by David Dzimianski (CSH) 4/2/2013
		// - Removed by Levi (CSH) 4/24/13
		// - This does not play well with non-cabinet free-freight coupons, nor does it work properly with multi-warehouse purchases
		//
		//Check for free freight coupons - if they exist, then apply them!
		//~ $freeFreight = false;
		//~ $session = Mage::getSingleton('core/session', array('name'=>'frontend'));
        //~ $couponCode = Mage::helper('checkout/cart')->getQuote()->getData('coupon_code');
				
		//~ $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
		//~ $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
				
		//~ // Is the coupon a free freight coupon?
		//~ // The maximum discount off freight is $250 - or less
		//~ if ($rule->getData('simple_free_shipping') == 1) {
			//~ //Mage::Log("TRUE");
			//~ $freeFreight = true;
			//~ if ($shippingPriceArray['Commercial'] < 251) {
				//~ $shippingPriceArray['Commercial'] = 0;
			//~ } else {
				//~ $shippingPriceArray['Commercial'] = $shippingPriceArray['Commercial'] - 250;
			//~ }
		//~ }
		//End addition
		//++++++++++++++++++++++++++++++++++
		
		//if freight only - calculate price based off subtotal and percentage
		//if($freightOnly == true) {
		//	$shippingPrice = $this->getSimpleFreightCost($subtotal);	
		//	$log['freight_portion'] = $shippingPrice;
		//}
		
		// is this a residential delivery ? 
		if($cshresidential == 1) {
			$modPrice = $modPrice + ($this->residential_cost * $uniqueWarehouseCount); //lift gate charge
			$log['cshliftgate_price'] = $this->residential_cost * $uniqueWarehouseCount;
			$log['Residential'] = 'Yes';
			
		} else {
			$log['Residential'] = 'No';
			if ($cshliftgate == 1) {
				// did they ask for lift gate assistance? 
				$modPrice = $modPrice + ($this->liftgate_cost * $uniqueWarehouseCount);
				$log['cshliftgate'] = 'Yes';
				$log['cshliftgate_price'] = $this->liftgate_cost * $uniqueWarehouseCount;				
			} else {
				$log['cshliftgate'] = 'No';
			}
			if ($cshappointment == 1) {
				$modPrice = $modPrice + ($this->appointment_cost * $uniqueWarehouseCount);
				$log['appointment'] = 'Yes';
				$log['appointment_price'] = $this->appointment_cost * $uniqueWarehouseCount;
			} else {
				$log['appointment'] = 'No';
			}
		}
		$is_checkout = false;
		if ($controller != 'cart') {
			$shippingPrice = $shippingPrice + $modPrice;
		
			$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier('simplesolutions_customrate');
			$method->setCarrierTitle('CSH Freight');
			$method->setMethod('simplesolutions_customrate');
			$method->setMethodTitle('Freight Shipping');
			
			$method->setPrice($shippingPrice);
			$method->setCost(2);
			 
			$result->append($method);
		} else {
		
			$shippingPriceArray['Residential'] = $shippingPriceArray['Commercial'] + ($this->residential_cost * $uniqueWarehouseCount);
			$log['Residential'] = $shippingPriceArray['Residential'];
			$shippingPriceArray['Commercial w/ Liftgate'] = $shippingPriceArray['Commercial'] + ($this->liftgate_cost * $uniqueWarehouseCount);
			$log['Commercial w/ Liftgate'] = $shippingPriceArray['Residential'];
			$shippingPriceArray['Commercial w/ Appointment'] = $shippingPriceArray['Commercial'] + ($this->appointment_cost * $uniqueWarehouseCount);
			$log['Commercial w/ Appointment'] = $shippingPriceArray['Residential'];
			$shippingPriceArray['Cmrcl. w/ Appointment & Liftgate'] = $shippingPriceArray['Commercial'] + ($this->appointment_cost * $uniqueWarehouseCount) + ($this->liftgate_cost * $uniqueWarehouseCount);
			$log['Commercial w/ Appointment & Liftgate'] = $shippingPriceArray['Residential'];
			
			// we're not in checkout, show all the possible rates
			foreach ($shippingPriceArray as $key => $v) {			
				$method = Mage::getModel('shipping/rate_result_method');
				$method->setCarrier('simplesolutions_customrate');
				$method->setCarrierTitle('CSH Freight');
				$method->setMethod($key);
				$method->setMethodTitle($key);
				
				$method->setPrice($v);
				$method->setCost(2);
				 
				$result->append($method);
			}
		}
		//debug
		$log['final_shipping_cost'] = $shippingPrice;
		//Mage::log($log, null, 'shippinglog.log');
		//Mage::log("Shipping Debug: ".print_r($log, true),null,'freight.log');
        return $result;
    }
 
    public function getAllowedMethods()
    {
        return array('simplesolutions_customrate' => $this->getConfigData('name'));
    }
}