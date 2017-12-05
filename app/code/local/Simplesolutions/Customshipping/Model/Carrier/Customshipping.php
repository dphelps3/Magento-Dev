<?php

class Simplesolutions_Customshipping_Model_Carrier_Customshipping extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface {
	
	protected $_code = 'customshipping';
	
	public function getFormBlock(){
		return 'customshipping/customshipping';
	}
	
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
	
		//stock code
		if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
			return false;
		}

		$handling = Mage::getStoreConfig('carriers/'.$this->_code.'/handling');
		$result = Mage::getModel('shipping/rate_result');
		$show = true;
		if($show){ // This if condition is just to demonstrate how to return success and error in shipping methods

			$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier($this->_code);
			$method->setMethod($this->_code);
			$method->setCarrierTitle($this->getConfigData('title'));
			$method->setMethodTitle($this->getConfigData('name'));
			//$method->setPrice($this->getConfigData('price'));
			//$method->setCost($this->getConfigData('price'));
			//$result->append($method);

		}else{
			$error = Mage::getModel('shipping/rate_result_error');
			$error->setCarrier($this->_code);
			$error->setCarrierTitle($this->getConfigData('name'));
			$error->setErrorMessage($this->getConfigData('specificerrmsg'));
			$result->append($error);
		}
		//stock code end
		
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
		$residential = false;
		$optionalLiftGate = false;
		$liftGate = false;
		
		$modPrice = 0;
		
		$subtotal = null;
		if(isset($totals['subtotal'])){
			  $subtotal = $totals['subtotal']->getValue();
		}
		
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
							array_push($vendors, $productObj->getData('vendor'));
                            if($isFreight == true) {
								array_push($freightItems, $product_id);
							} else {
								array_push($standardItems, $product_id);
							}
                        }
                    }
                } else {
                    $product_id = $item->getProductId();
                    $productObj = Mage::getModel('catalog/product')->load($product_id);
                    $isFreight = $productObj->getData('is_freight'); //our shipping attribute code
					array_push($vendors, $productObj->getData('vendor'));
                    if($isFreight == true) {
						array_push($freightItems, $product_id);
					} else {
						array_push($standardItems, $product_id);
					}
                }
				$x++;
            }
        }
		// is this a residential delivery ? 
		if($cshresidential == 1) {
			$modPrice = $modPrice + 99.00; //lift gate charge
			$log['Residential'] = 'Yes';
		} else if ($cshliftgate == 1) {
			// did they ask for lift gate assistance? 
			$modPrice = $modPrice + 99.00;
			$log['cshliftgate'] = 'Yes';
			$log['Residential'] = 'No';
		} else {
			$log['Residential'] = 'No';
			$log['cshliftgate'] = 'No';
		}
		
		// is this a mixed shipment of freight and standard shipping?
		if (!empty($freightItems) && !empty($standardItems)) {
			// can we ship all items via freight?
			$i = 0;
			foreach ($vendors as $vendor) {
				if($vendors[$i-1] != $vendor) {
				  $notSameVendor = true; //no we cant
				  $log['shipment'] = 'Mixed shipment - Different Vendors';
				}
				// $freightItems holds all product ids for freight itemes
				// $standardItems holds all product ids for standard shipping items
			}
			if($notSameVendor != true) {
				// we can ship all items via freight
				$freightOnly = true;
				$log['shipment'] = 'Mixed shipment - Same Vendors';
			} else {
				// now we must split the shipment into a freight shipment and a standard shipment
			
			}
		} else if (!empty($freightItems) && empty($standardItems)) {
			$freightOnly = true;
			$log['shipment'] = 'Freight Only';
		} else if (empty($freightItems) && !empty($StandardItems)){
			$standardOnly = true;
			$log['shipment'] = 'Standard Only';
		} else {
			$log['shipment'] = 'Shipping Cannot be determined';
		}
		
		$shippingPrice;
		
		//if freight only - calculate price based off subtotal and percentage
		$shippingPrice = 0;
		
		//if freight only - calculate price based off subtotal and percentage
		if($freightOnly == true) {
			if($subtotal <=	500) {
				$shippingPrice =  $subtotal * .42;
			} else if ($subtotal <= 1000){
				$shippingPrice =  $subtotal * .19;
			} else if ($subtotal <= 1500) {
				$shippingPrice =  $subtotal * .13;
			} else if($subtotal <= 2000){
				$shippingPrice =  $subtotal * .12;
			} else if($subtotal <= 4000){
				$shippingPrice =  $subtotal * .10;
			} else {
				$shippingPrice =  $subtotal * .07;
			} 
			if ($shippingPrice < 130) {	
				$shippingPrice = 130;
			}
		}
		
		$shippingPrice = $shippingPrice + $modPrice;
        $method->setPrice($shippingPrice);
        $method->setCost(2);
		
 
        $result->append($method);
		
		//Mage::log(print_r($log, true));
		
		
		return $result;
	}
	public function getAllowedMethods()
	{
		return array('simplesolutions'=>$this->getConfigData('name'));
	}
}