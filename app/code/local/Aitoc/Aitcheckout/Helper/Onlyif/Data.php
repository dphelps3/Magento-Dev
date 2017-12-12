<?php

class Aitoc_Aitcheckout_Helper_Onlyif_Data extends Aitoc_Aitcheckout_Helper_Abstract
{
    public function saveBilling($currentStep, $customerAddressId)
    {
        if ($currentStep == 'payment' && Mage::helper('aitcheckout/aitconfcheckout')->isEnabled()
            && Mage::helper(
                'customer'
            )->isLoggedIn()
        ) {
            if (!Mage::getSingleton('checkout/type_onepage')->getQuote()->getBillingAddress()->getData(
                'customer_address_id'
            )
            ) {
                if ($addId = Mage::app()->getRequest()->getPost('billing_address_id', false)) {
                    $customerAddressId = $addId;
                }
                Mage::getSingleton('checkout/type_onepage')->saveBilling(
                    Mage::app()->getRequest()->getPost('billing', array()),
                    $customerAddressId
                );
            }
        }
    }
}