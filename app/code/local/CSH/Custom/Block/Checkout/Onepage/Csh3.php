<?php
class CSH_Custom_Block_Checkout_Onepage_Csh3 extends Mage_Checkout_Block_Onepage_Abstract{
	protected function _construct()
	{
		$this->getCheckout()->setStepData('csh3', array(
            'label'     => Mage::helper('checkout')->__('Csh  Review'),
            'is_show'   => $this->isShow()
		));
		parent::_construct();
	}
}