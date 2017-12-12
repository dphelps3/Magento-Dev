<?php

class Aitoc_Aitcheckout_Block_Checkout_Review extends Mage_Checkout_Block_Onepage_Review
{
    /**
     * Validate if order amount is allowed to purchase
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return Mage::helper('aitcheckout')->isPlaceOrderDisabled();
    }

    /**
     * @return boolean
     */
    public function isSaveOrderAction()
    {
        return (Mage::app()->getRequest()->getActionName() == 'saveOrder');
    }

    public function getReviewUrl()
    {
        return $this->getUrl(
            'aitcheckout/checkout/saveOrder',
            array('form_key' => Mage::getSingleton('core/session')->getFormKey(), '_secure' => true)
        );
    }
}
