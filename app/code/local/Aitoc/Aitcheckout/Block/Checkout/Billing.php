<?php

class Aitoc_Aitcheckout_Block_Checkout_Billing extends Aitoc_Aitcheckout_Block_Checkout_Step
{
    protected $_stepType = 'Billing';

    protected $_configs = array();

    public function checkFieldShow($key)
    {
        $this->_configs = Mage::helper('aitconfcheckout/onepage')->initConfigs('billing');

        return Mage::helper('aitconfcheckout/onepage')->checkFieldShow($key, $this->_configs);
    }

    public function isUseBillingAddressForShipping()
    {
        if (($this->getQuote()->getIsVirtual())
            || (Mage::getSingleton('checkout/session')->getConfirmSameAsBilling() == 1
                && !$this->getQuote()
                    ->getShippingAddress()
                    ->getSameAsBilling())
        ) {
            return false;
        }

        return true;
    }

    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    public function getFirstname()
    {
        $firstname = $this->getAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }

        return $firstname;
    }

    public function getLastname()
    {
        $lastname = $this->getAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }

        return $lastname;
    }

    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }
}