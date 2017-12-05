<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
/**
 * One page checkout processing model
 */
class Webshopapps_Dropship_Model_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    private $_dropshipCustomerEmailExistsMessage = '';
	
 	public function _construct()
    {
    	parent::_construct();
        $this->_dropshipCustomerEmailExistsMessage = $this->_helper->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.'); 
    }
    
    
  	public function saveShippingMethod($shippingMethod)
    {
   	 	if (!Mage::getStoreConfig('carriers/dropship/active')) {
    		return parent::saveShippingMethod($shippingMethod);
    	}
    	if (empty($shippingMethod)) {
            return array('error' => -1, 'message' => $this->_helper->__('Invalid shipping method'.$shippingMethod));
        }
        $rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('error' => -1, 'message' => $this->_helper->__('Invalid shipping method b.'));
        }
        $this->getQuote()->getShippingAddress()
            ->setShippingMethod($shippingMethod);
            
        if ($rate->getWarehouseShippingDetails()!='') {
    		$this->getQuote()->getShippingAddress()
    			->setWarehouseShippingDetails($rate->getWarehouseShippingDetails())
    			->setWarehouseShippingHtml(Mage::helper('dropship')->getWarehouseShippingHtml($rate->getWarehouseShippingDetails()))
    			->save();
    	}    
    	
        $this->getQuote()->collectTotals()
            ->save();

        $this->getCheckout()
            ->setStepData('shipping_method', 'complete', true)
            ->setStepData('payment', 'allow', true);

        return array();
    }
    
    
    
    public function saveWarehouseShippingMethod($shippingMethod)
    {
    	$quote=$this->getQuote();
    	$shippingAddress=$quote->getShippingAddress();
        if (empty($shippingMethod) ) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('dropship')->__('Please specify shipping method(s) for each warehouse.')
            );
            return $res;
        }
        // put rates in more useable form
       
        
        $shippingDetails = array();
        $shipMethodCount=0;
        
        $shippingRateGroups = $shippingAddress->getGroupedAllShippingRates();
        
        $warehouseCountArry=array();
        foreach ($shippingRateGroups as $rates) {
        	foreach ($rates as $rate) {
	        	if (is_numeric($rate->getWarehouse()) && !in_array($rate->getWarehouse(),$warehouseCountArry)) {
	        		$warehouseCountArry[] = $rate->getWarehouse();
	        	}
        	}
        }
        
        // need to store each rate for each warehouse
        foreach ($shippingMethod as $indMethod=>$shipMethod) {
        	if ($indMethod=="_" || strpos($indMethod,'gift')!== false || $indMethod=="amorderattr") {
        		continue;
        	}
       		$shipMethodCount++;
        	
       	// extract warehouse from text
	        $pieces 	= explode('_',$indMethod);
	        $warehouse	= $pieces[2];
	        $rateSplit 	= explode('_',$shipMethod);
	        $methodCode = $rateSplit[0];
        
	        $found=false;
	        if (array_key_exists($methodCode,$shippingRateGroups)) {
	        	$rates = $shippingRateGroups[$methodCode];
        		foreach ($rates as $indRate) {
         			if ($warehouse==$indRate->getWarehouse() && $shipMethod==$indRate->getCode()) {
         				$rate = $indRate;
         				$found=true;
         				break;
         			}
         		}
	        }
	        
	        if (!$found) {
	            return array('error' => -1, 'message' => $this->_helper->__('Please select shipping method(s).'));
	        }
	        $rateToAdd = array (
	        	'warehouse'		=> $warehouse,
	        	'code'			=> $shipMethod,
	        	'price'			=> (float)$rate->getPrice(),
	        	'cost'			=> (float)$rate->getCost(),
	        	'carrierTitle'	=> $rate->getCarrierTitle(),
	        	'methodTitle'	=> $rate->getMethodTitle(),
	            'freightQuoteId'=> $rate->getFreightQuoteId(),
	        );
        	if (!is_null($rate->getFreightQuoteId())) {
				$rateToAdd['freightQuoteId']=$rate->getFreightQuoteId();
			}
			$shippingDetails[] = $rateToAdd;
        }
        
      	if ($shipMethodCount==0 || $shipMethodCount!= count($warehouseCountArry)) {
            	$res = array(
                    'error' => -1,
                    'message' => Mage::helper('dropship')->__('Please specify shipping method(s) for each warehouse.')
                );
                return $res;
        }
        $shippingAddress->createShippingRate($shippingDetails);
        $quote->save();
        
        $encodedShipDetails=Mage::helper('dropship')->encodeShippingDetails($shippingDetails);
        $quote->getShippingAddress()
        	->setWarehouseShippingDetails($encodedShipDetails)
    		->setWarehouseShippingHtml(Mage::helper('dropship')->getWarehouseShippingHtml($encodedShipDetails))
    		->save();
    		
    		
        $quote->collectTotals()->save();
        
        
        $this->getCheckout()
            ->setStepData('shipping_method', 'complete', true)
            ->setStepData('payment', 'allow', true);

        return array();
    }
    
   /**
     * Save billing address information to quote
     * This method is called by One Page Checkout JS (AJAX) while saving the billing information.
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function saveBilling($data, $customerAddressId)
    {
    	if (!Mage::getStoreConfig('carriers/dropship/active')) {
    		return parent::saveBilling($data, $customerAddressId);
    	}
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->_helper->__('Invalid data'));
        }

        $address = $this->getQuote()->getBillingAddress();
        
	    if  (Mage::helper('wsalogger')->getNewVersion() > 8 ) {
        	$addressForm = Mage::getModel('customer/form');
        	$addressForm->setFormCode('customer_address_edit')
	            ->setEntityType('customer_address')
	            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
	    }
                
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => $this->_helper->__('Customer Address is not valid.')
                    );
                }
                if (Mage::helper('wsalogger')->getNewVersion() > 9) {
                	$address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
	                $addressForm->setEntity($address);
	                $addressErrors  = $addressForm->validateData($address->getData());
	                if ($addressErrors !== true) {
	                    return array('error' => 2, 'message' => $addressErrors);
	                }
                } else {
                	$address->importCustomerAddress($customerAddress);
                }
            }
        } else {
	    if  (Mage::helper('wsalogger')->getNewVersion() > 8 ) {
            	$addressForm->setEntity($address);
	            // emulate request object
	            $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
	            $addressErrors  = $addressForm->validateData($addressData);
	            if ($addressErrors !== true) {
	                return array('error' => 3, 'message' => $addressErrors);
	            }
	            $addressForm->compactData($addressData);
	
	            // Additional form data, not fetched by extractData (as it fetches only attributes)
            	$address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
	            	
	    	} else {
	            unset($data['address_id']);
	            $address->addData($data);
	    	}
            //$address->setId(null);
        }

       // validate billing address
        if (($validateRes = $address->validate()) !== true) {
            return array('error' => 4, 'message' => $validateRes);
        }

        
        $address->implodeStreetAddress();
	    
	if  (Mage::helper('wsalogger')->getNewVersion() > 8 ) {
        
	     	if (true !== ($result = $this->_validateCustomerData($data))) {
				return $result;
	        }
        } else {
        	if (true !== ($result = $this->_processValidateCustomer($address))) {
				return $result;
	        }
        }
        
        if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
               /* return array('error' => 5, 'message' => $this->_dropshipCustomerEmailExistsMessage);*/
			   			   
				return array('error' => 5, 'message' => 'This email address is already in use. If you have forgotten your password, please try clicking "Log In" and then "Forgot Your Password"');
            }
        }
        

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;

            if (Mage::helper('dropship')->isMergedCheckout()) {
            	$useMerged=NULL;
            } else {
            	$useMerged=99;
            }
            
            switch($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    $shipping->setWarehouse($useMerged);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();
                    $warehouseShippingDetails = $shipping->getWarehouseShippingDetails();
                    $shipping->addData($billing->getData())
                        ->setSameAsBilling(1)
                        ->setShippingMethod($shippingMethod)
                        ->setSaveInAddressBook(0)
                        ->setWarehouse($useMerged)
                        ->setWarehouseShippingDetails($warehouseShippingDetails)
                        ->setCollectShippingRates(true);
                    $this->getCheckout()->setStepData('shipping', 'complete', true);
                    break;
            }
        }       

        $this->getQuote()->collectTotals();
        $this->getQuote()->save();

        $this->getCheckout()
            ->setStepData('billing', 'allow', true)
            ->setStepData('billing', 'complete', true)
            ->setStepData('shipping', 'allow', true);

        return array();
    }
    
    
     /**
         * Save checkout shipping address
         *
         * @param   array $data
         * @param   int $customerAddressId
         * @return  Mage_Checkout_Model_Type_Onepage
         */
        public function saveShipping($data, $customerAddressId)
        {
        	if (!Mage::getStoreConfig('carriers/dropship/active')) {
	        	return parent::saveShipping($data, $customerAddressId);
    	    }
            if (empty($data)) {
                return array('error' => -1, 'message' => $this->_helper->__('Invalid data.'));
            }
            $address = $this->getQuote()->getShippingAddress();
            
	    	if  (Mage::helper('wsalogger')->getNewVersion() > 8 ) {
	        	$addressForm    = Mage::getModel('customer/form');
	       		$addressForm->setFormCode('customer_address_edit')
		            ->setEntityType('customer_address')
		            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
	        }
    
            if (!empty($customerAddressId)) {
                $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
                if ($customerAddress->getId()) {
                    if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                        return array('error' => 1,
                            'message' => $this->_helper->__('Customer Address is not valid.')
                        );
                    }
	        		if  ( Mage::helper('wsacommon')->getVersion() == 1.10) {
	        		 	$address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
		                $addressForm->setEntity($address);
		                $addressErrors  = $addressForm->validateData($address->getData());
		                if ($addressErrors !== true) {
		                    return array('error' => 1, 'message' => $addressErrors);
		                }
	        		} else {
                    	$address->importCustomerAddress($customerAddress);
	        		}
                }
            } else {
	    	if  (Mage::helper('wsalogger')->getNewVersion() > 8 ) {
	        		$addressForm->setEntity($address);
	        		
		            // emulate request object
		            $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
		            $addressErrors  = $addressForm->validateData($addressData);
		            if ($addressErrors !== true) {
		                return array('error' => 1, 'message' => $addressErrors);
		            }
		            $addressForm->compactData($addressData);
		            
		            // Additional form data, not fetched by extractData (as it fetches only attributes)
		            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
		            $address->setSameAsBilling(empty($data['same_as_billing']) ? 0 : 1);
		             
	           } else {
	           		           	
	           		unset($data['address_id']);
	            	$address->addData($data);
	               
	        	}
            }
            $address->implodeStreetAddress();
            
         	if (Mage::helper('dropship')->isMergedCheckout()) {
                	$useMerged=NULL;
                } else {
                	$useMerged=99;
                }
            $address->setWarehouse($useMerged);
            $address->setCollectShippingRates(true);
            
            if (($validateRes = $address->validate())!==true) {
                return array('error' => 1, 'message' => $validateRes);
            }
    
            $this->getQuote()->collectTotals()->save();
    
            $this->getCheckout()
                ->setStepData('shipping', 'complete', true)
                ->setStepData('shipping_method', 'allow', true);
    
            return array();
        }
    
 	  public function savePayment($data)
	    {
	    	if  (Mage::helper('wsalogger')->getNewVersion() <= 8 ) {
	    		return parent::savePayment($data);	
	    	}
	    	
	    	
	        if (empty($data)) {
	            return array('error' => -1, 'message' => $this->_helper->__('Invalid data.'));
	        }
	        $quote = $this->getQuote();
	        if ($quote->isVirtual()) {
	            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
	        } else {
	            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
	        }
	
	        $payment = $quote->getPayment();
	        $payment->importData($data);
	
	        // shipping totals may be affected by payment method
	       /* if (!$quote->isVirtual() && $quote->getShippingAddress()) {
	            $quote->getShippingAddress()->setCollectShippingRates(true);
	            $quote->setTotalsCollectedFlag(false)->collectTotals();
	        }*/
	        $quote->save();
	
	        $this->getCheckout()
	            ->setStepData('payment', 'complete', true)
	            ->setStepData('review', 'allow', true);
	
	        return array();
	    }
        
	// Save Comments - Simple Solutions 5/28/2013
	public function saveCsh2($data){
		//Mage::log('This is the $data in saveCsh2: '.print_r($data, true), null, 'aarons_comments.log', true);
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->_helper->__('Invalid data. Data'));
        }
        $this->getQuote()->setCshComments2($data['comments']);
        $this->getQuote()->collectTotals();
        $this->getQuote()->save();
		Mage::log('This is the $getCshComments2 in saveCsh2: '.print_r($this->getQuote()->getCshComments2(), true), null, 'aarons_comments.log', true);
        $this->getCheckout()
        ->setStepData('csh2', 'allow', true)
        ->setStepData('csh2', 'complete', true)
        ->setStepData('payment', 'allow', true);
	
        return array();
    }
}