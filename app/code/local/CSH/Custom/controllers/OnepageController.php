<?php
require_once 'Webshopapps/Dropship/controllers/Checkout/OnepageController.php';
//class CSH_Custom_OnepageController extends Mage_Checkout_OnepageController {
class CSH_Custom_OnepageController extends Webshopapps_Dropship_Checkout_OnepageController {
    // Removed CSH shipping step
	/*
	public function saveCshAction(){
		if ($this->_expireAjax()) {
			return;
		}
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost('csh', array());

			$result = $this->getOnepage()->saveCsh($data);
			
			$checkout_session = Mage::getSingleton('checkout/session');
			$checkout_session->getQuote()->getShippingAddress()->setCollectShippingRates(true);


			if (!isset($result['error'])) {
				$result['goto_section'] = 'shipping_method';
				$result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
				);
			}

			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	*/
	//save csh2
	public function saveCsh2Action(){
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
		   
            $data = $this->getRequest()->getPost('csh2', array());
			$result = $this->getOnepage()->saveCsh2($data);
			
             if (empty($result['error'])) {
                /*$this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );*/
				$result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            }
 
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
	//save csh2 end
	/*
	public function saveBillingAction()
	{
		if ($this->_expireAjax()) {
			return;
		}
		if ($this->getRequest()->isPost()) {
			//            $postData = $this->getRequest()->getPost('billing', array());
			//            $data = $this->_filterPostData($postData);
			$data = $this->getRequest()->getPost('billing', array());
			$customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

			if (isset($data['email'])) {
				$data['email'] = trim($data['email']);
			}
			//Mage::log('data: '.print_r($data, true));
			//Mage::log('customerAddressId: '.$customerAddressId);
			$result = $this->getOnepage()->saveBilling($data, $customerAddressId);
			//Mage::log('saveBillingAction result: '.print_r($result,true));
			
			//check for freight
			$session_new = Mage::getSingleton('checkout/session');
			$session_new->getQuote()->getShippingAddress()->setCollectShippingRates(true);
	 
			$freightflag = false;
			foreach ($session_new->getQuote()->getAllItems() as $item) {
				//Mage::log('test');
				if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
					continue;
				}

				if ($item->getHasChildren() && $item->isShipSeparately()) {
					foreach ($item->getChildren() as $child) {
						if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
							$product_id = $child->getProductId();
							$productObj = Mage::getModel('catalog/product')->load($product_id);
							$isFreight = $productObj->getData('is_freight'); //our shipping attribute code
							if($isFreight == '1189') {
								$freightflag = true;
							}
						}
					}
				} else {
					$product_id = $item->getProductId();
					$productObj = Mage::getModel('catalog/product')->load($product_id);
					$isFreight = $productObj->getData('is_freight'); //our shipping attribute code
					if($isFreight == '1189') {
						$freightflag = true;
					}
				}
			}
			if($freightflag == true) {
				$next_step = 'shipping_method';
				//Mage::log('freight is true');
			} else {
				$next_step = 'shipping_method';
				//Mage::log('freight is false');
			}
			//check for freight end
			

			if (!isset($result['error'])) {
				// check quote for virtual
				if ($this->getOnepage()->getQuote()->isVirtual()) {
					$result['goto_section'] = 'payment';
					$result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
					);
				} elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
					$result['goto_section'] = $next_step;  //Goes to our step
					$result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );
                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
					//Mage::log('Going to: '.$next_step);
				} else {
					$result['goto_section'] = 'shipping';
				}
			}

			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	} */
	// Removed CSH shipping methods 5/28/2013
	/*
	public function saveShippingAction()
	{
		if ($this->_expireAjax()) {
			return;
		}
		//check for freight
			$session_new = Mage::getSingleton('checkout/session');
			$session_new->getQuote()->getShippingAddress()->setCollectShippingRates(true);
	 
			$freightflag = false;
			foreach ($session_new->getQuote()->getAllItems() as $item) {
				//Mage::log('test');
				if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
					continue;
				}

				if ($item->getHasChildren() && $item->isShipSeparately()) {
					foreach ($item->getChildren() as $child) {
						if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
							$product_id = $child->getProductId();
							$productObj = Mage::getModel('catalog/product')->load($product_id);
							$isFreight = $productObj->getData('is_freight'); //our shipping attribute code
							if($isFreight == true) {
								$freightflag = true;
							}
						}
					}
				} else {
					$product_id = $item->getProductId();
					$productObj = Mage::getModel('catalog/product')->load($product_id);
					$isFreight = $productObj->getData('is_freight'); //our shipping attribute code
					if($isFreight == true) {
						$freightflag = true;
					}
				}
			}
			if($freightflag == true) {
				$next_step = 'csh';
				//Mage::log('freight is true');
			} else {
				$next_step = 'shipping_method';
				//Mage::log('freight is false');

			}
			//check for freight end
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost('shipping', array());
			$customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
			$result = $this->getOnepage()->saveShipping($data, $customerAddressId);

			if (!isset($result['error'])) {
				$result['goto_section'] = $next_step; //Go to our step
				$result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                );
				//Mage::log('Going to: '.$next_step);
			} else {
				Mage::log("error: ".$result['error']);
			}
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	*/
	//Begin
	
	// We have to overwrite the savePaymentAction() to direct the user to our new comments step upon seleciton of payment method
	 /*public function savePaymentAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            // set payment to quote
            $result = array();
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'csh2';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
	//End*/
	
	 /**
     * Shipping method save action
     */
    public function saveShippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
	/*
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);
            
            //$result will have erro data if shipping method is empty
            
            if(!$result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                        array('request'=>$this->getRequest(),
                            'quote'=>$this->getOnepage()->getQuote()));
                $this->getOnepage()->getQuote()->collectTotals();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                $result['goto_section'] = 'csh2';
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
	    */
	    
	parent::saveShippingMethodAction();
	$result = Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
	if($result['goto_section']) {
		$result['goto_section'] = 'csh2';
		$result['update_section'] = null;
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
    }
}