<?php
class CSH_Custom_Block_Checkout_Onepage_Csh extends Mage_Checkout_Block_Onepage_Abstract{
	protected function _construct()
	{
		$this->getCheckout()->setStepData('csh', array(
            'label'     => Mage::helper('checkout')->__('CSH Shipping'),
            'is_show'   => $this->isShow()
		));
		
        /*if ($this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('csh', 'allow', true);
            $this->getCheckout()->setStepData('billing', 'allow', false);
        }*/
		parent::_construct();
	}
}