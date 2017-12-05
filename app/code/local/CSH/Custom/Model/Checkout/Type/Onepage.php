<?php
class CSH_Custom_Model_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage{
    //Removed CSH shipping step 5/28/2013
	/*
	public function saveCsh($data){
		if (empty($data)) {
			return array('error' => -1, 'message' => $this->_helper->__('Invalid data.'));
		}
		$this->getQuote()->setCshResidential($data['residential']);
		$this->getQuote()->collectTotals();
		$this->getQuote()->save();
		
		$this->getQuote()->setCshLiftgate($data['liftgate']);
		$this->getQuote()->collectTotals();
		$this->getQuote()->save();
		
		$this->getQuote()->setCshAppointment($data['appointment']);
		$this->getQuote()->collectTotals();
		$this->getQuote()->save();

		//check for freight items
		$session_new = Mage::getSingleton('checkout/session');
	 
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
			$use_csh = true;
		} else {
			$use_csh = false;
		}
		
		$this->getCheckout()
			->setStepData('csh', 'allow', $use_csh)
			->setStepData('csh', 'complete', $use_csh)
			->setStepData('shipping_method', 'allow', true);
		
		//->setStepData('like','complete',$data['like'])
		//->setStepData('liftgate','complete',$data['liftgate']);

		//Mage::log('likedata: '.$data['like']);
		//Mage::log('liftgatedata: '.$data['liftgate']);
		//Mage::log('getlikedata: '.$this->getCheckout()->getStepData('like'));
		//Mage::log('getliftgatedata: '.$this->getCheckout()->getStepData('liftgate'));
		
		return array();
	}
	*/
	public function saveCsh2($data){
		if (empty($data)) {
            return array('error' => -1, 'message' => $this->_helper->__('Invalid data. Data'));
        }
        $this->getQuote()->setCshComments2($data['comments']);
        $this->getQuote()->collectTotals();
        $this->getQuote()->save();
		
        $this->getCheckout()
            ->setStepData('csh2', 'allow', true)
            ->setStepData('csh2', 'complete', true)
            ->setStepData('payment', 'allow', true);
	
        return array();
    }
}
