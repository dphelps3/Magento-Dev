<?php
class CSH_Custom_Block_Checkout_Onepage_Csh2 extends Mage_Checkout_Block_Onepage_Abstract{
	protected function _construct()
	{
		$this->getCheckout()->setStepData('csh2', array(
            'label'     => Mage::helper('checkout')->__('Comments'),
            'is_show'   => $this->isShow()
		));
		parent::_construct();
	}
}