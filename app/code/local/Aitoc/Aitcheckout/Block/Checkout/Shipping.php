<?php

class Aitoc_Aitcheckout_Block_Checkout_Shipping extends Aitoc_Aitcheckout_Block_Checkout_Step
{
    protected $_stepType = 'Shipping';

    protected $_configs = array();

    public function checkFieldShow($key)
    {
        $this->_configs = Mage::helper('aitconfcheckout/onepage')->initConfigs('shipping');

        return Mage::helper('aitconfcheckout/onepage')->checkFieldShow($key, $this->_configs);
    }

    public function isShow()
    {
        return !$this->getQuote()->isVirtual();
    }

    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    public function customerHasAddresses()
    {
        if (Mage::helper('aitcheckout/adjgiftregistry')->getGiftAddressId()) {
            return true;
        }

        return parent::customerHasAddresses();
    }
}